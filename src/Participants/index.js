

function getRows(obj) {
	var filter='';
	$('.filter').each(function (index, value) {
		if(value.value!='')  {
			filter += '&filter['+value.id+']='+value.value;
		}
	});
	if(obj) {
		$('#Accreditation').attr('sort', obj.id);
		$('#Accreditation').attr('sortorder', obj.getAttribute('status'));
	}
	var queryString='?AllTargets='+($('#AllTargets')[0].checked ? 1 : 0)
		+ '&ShowTourCode='+($('#ShowTourCode')[0].checked ? 1 : 0)
		+ '&ShowLocalBib='+($('#ShowLocalBib')[0].checked ? 1 : 0)
		+ '&ShowEmail='+($('#ShowEmail')[0].checked ? 1 : 0)
		+ '&ShowCountry2='+($('#ShowCountry2')[0].checked ? 1 : 0)
		+ '&ShowCountry3='+($('#ShowCountry3')[0].checked ? 1 : 0)
		+ '&ShowCaption='+($('#ShowCaption')[0].checked ? 1 : 0)
		+ '&ShowDisable='+($('#ShowDisable')[0].checked ? 1 : 0)
		+ '&ShowAgeClass='+($('#ShowAgeClass')[0].checked ? 1 : 0)
		+ '&ShowSubClass='+($('#ShowSubClass')[0].checked ? 1 : 0)
		+ '&sort=' + $('#Accreditation').attr('sort')
		+ '&sortOrder=' + $('#Accreditation').attr('sortorder')
		+ filter;
	history.pushState({}, "", queryString);
	$.getJSON('./getRows.php'+queryString, function(data) {
		$('#Rows').html(data.html);

		$('.Delete').hover(function(){
		    $(this).parent().toggleClass('active');
		});

		$('.EntryRow').bind('click', function() {
			$(this).unbind('click');
			createEdits($(this));
			var EditCell=$(this).find('td.edit');
			EditCell.html('<input type="button" value="'+EditCell.attr('close')+'" onclick="closeRow(this)">');
		});

		$('td.edit').children.bind('click', function(event) {
			event.stopPropagation();
			popUp(this);
		});
	});
}

function createEdits(obj, nosave) {
	obj.children().each(function() {
		var cell=$(this);

		switch(cell.attr('field')) {
		case 'code':
		case 'locCode':
		case 'firstname':
		case 'name':
		case 'email':
		case 'dob':
		case 'caption':
		case 'targetno':
		case 'country_code':
		case 'country_name':
		case 'country_code2':
		case 'country_name2':
		case 'country_code3':
		case 'country_name3':
			createTextField(this, cell.attr('field'), nosave);
			break;
		case 'tourcode':
		case 'session':
		case 'sex':
		case 'wc':
		case 'division':
		case 'class':
		case 'ageclass':
		case 'subclass':
		case 'targetface_name':
			createComboField(this, cell.attr('field'), nosave);
			break;
		case 'qutargetno':
		case 'tourid':
		case 'key':
		case 'id':
		case 'ioccode':
		case 'status':
		case 'sex_id':
		case 'ctrl_code':
		case 'country_id':
		case 'sub_team':
		case 'country_id2':
		case 'country_id3':
		case 'targetface':
		case 'indcl':
		case 'teamcl':
		case 'indfin':
		case 'teamfin':
		case 'mixteamfin':
		case 'double':

			break;
		}
	});

}
//function insertInput(cell, what) {
//	var url='';
//	switch(what) {
//	case 'subclass':
//		url='Get-Subclasses.php';
//		break;
//	}
//
//	if(url>'') { // only combos have to get the correct data!
//	} else {
//		createTextField(cell, what);
//	}
//}

function createTextField(cell, what, nosave) {
	var field=document.createElement('input');
	field.value=$(cell).html();
	field.defaultValue=field.value;
	field.name=what;
	$(cell).html(field);
	if(!nosave) {
		$(field).change(function () {
			$.getJSON('./updateField.php?'+cell.parentNode.id+'&field='+what+'&value='+this.value, function(data) {
				if(data.error==0) {
					if(data.newvalue) {
						$(cell).css('background-color', 'green');
					}
				} else {
					alert(data.msg);
				}
			});
		});
	}

//	field.focus();
}

function createComboField(cell, what, nosave) {
	// tourcode selector ONLY for new entries...
	if(what=='tourcode') return;
	$.getJSON('getCombo.php?'+cell.parentNode.id+'&field='+what, function(data) {
		if(data.error==1) return;
		var field=document.createElement('select');
		$.each(data.rows, function(key, row) {
			$(field).append('<option value="'+row.key+'">'+row.value+'</option>');
		});
		field.value=$(cell).attr('val');
		field.defaultValue=field.value;
		field.name=what;
		$(cell).html(field);
		if(!nosave) {
			$(field).change(function () {
				$.getJSON('./updateField.php?'+cell.parentNode.id+'&field='+what+'&value='+this.value, function(data) {
					if(data.error==0) {
						if(data.newvalue) {
							$(cell).css('background-color', 'green');
						}
					} else {
						alert(data.msg);
					}
				});
			});
		}
	});
}

function popUp(obj) {
	alert('edit '+obj.parentNode.id);
}

function addRow() {
	var rowClone=$('#RowFilter').next().clone();

	var tourcode=rowClone.attr('id').match(/ToId=([0-9]+)&/)[1];

	rowClone.attr('id', rowClone.attr('id').replace(/EnId=[0-9]+$/, 'EnId='));
	rowClone.find('td')
		.html('')
		.attr('val', '');

	var CellTour;
	if(CellTour=rowClone.find("td[field='tourcode']")) {
		CellTour.attr('val', tourcode);
		$.getJSON('getCombo.php?'+CellTour.parent().attr('id')+'&field=tourcode', function(data) {
			if(data.error==1) return;
			if(data.rows.length==1) {
				CellTour.html(data.rows[0]);
				return;
			}
			// more than 1 competition code
			var field=document.createElement('select');
			$.each(data.rows, function(key, row) {
				$(field).append('<option value="'+row.key+'">'+row.value+'</option>');
			});
			field.value=CellTour.attr('val');
			field.defaultValue=field.value;
			field.name='tourid';
			CellTour.html(field);
			// only changes the ID of the row to point to the new tourid
			$(field).change(function () {
					CellTour.parent().attr('id', CellTour.parent().attr('id').replace(/ToId=[0-9]*&/, 'ToId='+this.value+'&'));
			});
		});
	}
	createEdits(rowClone, true);
	var EditCell=rowClone.find('td.edit');
	EditCell.html('<input type="button" value="'+EditCell.attr('save')+'" onclick="saveRow(this)"><input type="button" value="'+EditCell.attr('abort')+'" onclick="removeRow(this)">');
	$('#RowFilter').after(rowClone);
}

function saveRow(obj) {
	// saves the whole row at once
	QueryString='';
	var Row=$(obj).parent().parent();
	Row.find('input').each(function(index, field) {
		if($(field).attr('name')) QueryString += '&field['+$(field).attr('name')+']='+$(field).val();
	});
	Row.find('select').each(function(index, field) {
		QueryString += '&field['+$(field).attr('name')+']='+$(field).val();
	});
	$.getJSON('saveRow.php?'+Row.attr('id')+QueryString, function(data) {
		// rewrites the ID with the EnId received, then acts as if it was an "old row"
		Row.attr('id', Row.attr('id').replace(/EnId=[0-9]*$/, 'EnId='+data.enid));
		$.each(data.rows, function(index, row) {
			Row.find('[field="'+row.field+'"]').html(row.value);
			if(row.extra) {
				Row.find('[field="'+row.field+'"]').attr('val', row.extra);
			}
		});
	});
}

function removeRow(obj) {
	$(obj).parent().parent().remove();
}

function closeRow(obj) {
	var EditCell=$(obj).parent();
	$(obj).parent().parent().find('td').each(function(index, cell) {
		var field=$(cell).find('select option:selected');
		if(field && field.length>0) {
			var val=field.val();
			var text=val;
			switch($(cell).attr('field')) {
				case 'sex':
					text=field.text();
					break;
				case 'wc':
					text=(val==1 ? 'X' : '');
					break;
				case 'subclass':
				case 'targetface_name':
					text=field.text();
					break;
			}
			if(val==='') text='';
			$(cell).html(text).attr('val', val);
		}
		var field=$(cell).find('input');
		if(field && field.length>0) {
			var val=field.val();
			$(cell).html(val);
		}
	});
	EditCell.html('<input type="button" value="'+EditCell.attr('edit')+'" onclick="popUp(this)">');
}