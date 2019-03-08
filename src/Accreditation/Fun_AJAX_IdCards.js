function ShowEntries() {
	var comboCountry=document.getElementById('d_Country');
	var comboDivision=document.getElementById('d_Division');
	var comboClass=document.getElementById('d_Class');
	var comboEntries=document.getElementById('p_Entries');
	var Events=document.getElementsByName('Event[]');
	var comboPhase=document.getElementById('d_Phase');
	if(comboPhase!=undefined) {
		comboPhase='&Phase='+comboPhase.value;
	} else {
		comboPhase='';
	}
	var HasPlastic=document.getElementById('HasPlastic');
	if(HasPlastic!=undefined && HasPlastic.checked) {
		HasPlastic='&HasPlastic=1';
	} else {
		HasPlastic='';
	}

	var PrintAccr=document.getElementById('PrintAccredited');
	var PrintPhot=document.getElementById('PrintPhoto');
	var PrintNoPr=document.getElementById('PrintNotPrinted');
	var PrintAccr=document.getElementById('PrintAccredited');
	var SortTargt=document.getElementById('SortByTarget');

	var query='?type='
		+ '&PrintAccredited=' + (PrintAccr && PrintAccr.checked ? '1' : '0')
		+ '&PrintPhoto=' + (PrintPhot && PrintPhot.checked ? '1' : '0')
		+ '&PrintNotPrinted=' + (PrintNoPr && PrintNoPr.checked ? '1' : '0')
		+ '&SortByTarget=' + (SortTargt && SortTargt.checked ? '1' : '0')
		+ '&CardType='+document.getElementById('BadgeType').value
		+ '&CardNumber='+document.getElementById('BadgeNumber').value
		+ comboPhase
		+ HasPlastic
		;

	for(var n=0; n<comboCountry.length; n++) {
		if(comboCountry.options[n].selected) query+='&Country[]='+comboCountry.options[n].value;
	}

	if(comboDivision!=undefined) {
		for(n=0; n<comboDivision.length; n++) {
			if(comboDivision.options[n].selected) query+='&Division[]='+comboDivision.options[n].value;
		}
	}
	if(comboClass!=undefined) {
		for(n=0; n<comboClass.length; n++) {
			if(comboClass.options[n].selected) query+='&Class[]='+comboClass.options[n].value;
		}
	}
	for(n=1; n<=SesQNo; n++) {
		var Qsess=document.getElementById('d_QSession_'+n);
		if(Qsess && Qsess.checked) query+='&QSession[]='+n;
	}

	for(n=1; n<=SesENo; n++) {
		var Esess=document.getElementById('d_ESession_'+n);
		if(Esess && Esess.checked) query+='&ESession[]='+n;
	}
	if(Events!=undefined) {
		for(var n=0; n<Events.length; n++) {
			if(Events.item(n).checked) query+='&'+Events.item(n).name+'='+Events.item(n).value;
		}
	}

	var XMLHttp=CreateXMLHttpRequestObject();
	if (XMLHttp) {
		try {
			if ((XMLHttp.readyState==XHS_COMPLETE || XMLHttp.readyState==XHS_UNINIT)) {
				XMLHttp.open("POST",'GetEntries.php'+query, true);
				XMLHttp.onreadystatechange=function() {
					if (XMLHttp.readyState!=XHS_COMPLETE) return;
					if (XMLHttp.status!=200) return;
					try {
						var XMLResp=XMLHttp.responseXML;
						// intercetto gli errori di IE e Opera
						if (!XMLResp || !XMLResp.documentElement) throw(XMLResp.responseText);

						// Intercetto gli errori di Firefox
						var XMLRoot;
						if ((XMLRoot = XMLResp.documentElement.nodeName)=="parsererror") throw("ParseError");

						XMLRoot = XMLResp.documentElement;

						// clean the Selector
						while (comboEntries.options.length>0) {
							comboEntries.remove(0);
						}

						var Entries=XMLRoot.getElementsByTagName('entry');
						if(Entries) {
							for(var i=0; i<Entries.length; i++) {
								var descr=Entries.item(i).getAttribute('option');
								var code=Entries.item(i).getAttribute('id');
								var style=Entries.item(i).getAttribute('style');

								comboEntries.options[i]=new Option(descr,code);
								comboEntries.options[i].style.color = style;
							}
						}

//						// clean the Selector
						var Countries=document.getElementById('d_Country');
//						while (Countries.options.length>0) {
//							Countries.remove(0);
//						}
//
						var Entries=XMLRoot.getElementsByTagName('country');
						if(Entries) {
							for(var i=0; i<Entries.length; i++) {
								var row=Countries.querySelector('option[value="'+Entries.item(i).getAttribute('id')+'"]');
								row.style.color = Entries.item(i).getAttribute('style');
							}
						}

						var Countries=

						hide_confirm();
					} catch(e) {
						//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
					}

				};
				XMLHttp.send();
			}
		} catch (e) {
			//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
		}
	}
}

function ConfirmPrinted() {
	var XMLHttp=CreateXMLHttpRequestObject();
	if (XMLHttp) {
		try {
			if ((XMLHttp.readyState==XHS_COMPLETE || XMLHttp.readyState==XHS_UNINIT)) {
				XMLHttp.open("POST",'ConfirmPrinted.php?CardType='+document.getElementById('BadgeType').value
						+ '&CardNumber='+document.getElementById('BadgeNumber').value, true);
				XMLHttp.onreadystatechange=function() {
					if (XMLHttp.readyState!=XHS_COMPLETE) return;
					if (XMLHttp.status!=200) return;
					try {
						var XMLResp=XMLHttp.responseXML;
						// intercetto gli errori di IE e Opera
						if (!XMLResp || !XMLResp.documentElement) throw(XMLResp.responseText);

						// Intercetto gli errori di Firefox
						var XMLRoot;
						if ((XMLRoot = XMLResp.documentElement.nodeName)=="parsererror") throw("ParseError");

						XMLRoot = XMLResp.documentElement;

						var Error=XMLRoot.getElementsByTagName('error').item(0).value;
						if(Error) {
							alert('Error');
						}

						ShowEntries();
					} catch(e) {
						//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
					}

				};
				XMLHttp.send();
			}
		} catch (e) {
			//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
		}
	}
}

function selectEntries(selected)
{
	var comboCountry=document.getElementById('d_Country');
	var comboDivision=document.getElementById('d_Division');
	var comboClass=document.getElementById('d_Class');

	query='?type=';

	for(n=0; n<comboCountry.length; n++) {
		if(comboCountry.options[n].selected) query+='&Country[]='+comboCountry.options[n].value;
	}
	for(n=0; n<comboDivision.length; n++) {
		if(comboDivision.options[n].selected) query+='&Division[]='+comboDivision.options[n].value;
	}
	for(n=0; n<comboClass.length; n++) {
		if(comboClass.options[n].selected) query+='&Class[]='+comboClass.options[n].value;
	}

	for(n=1; n<=SesNo; n++) {
		if(document.getElementById('d_Session_'+n).checked) query+='&Session[]='+n;
	}

	var o=
	{
		url: 'GetIdCardEntries.php'+query,
		method: 'POST',
		success: function(response)
		{
			var xmlResp={};

			try
			{
				xmlResp=response.responseXML;
				xmlRoot=Ext.util.xmlResponse(xmlResp);

				//var combo=document.getElementById('d_Rule');
				var combo=Ext.get('p_Entries');

				var i=0;

			// pulisco
				for (i=combo.dom.options.length-1;i>=0;--i)
				{
					combo.dom.remove(i);
				}

				var rules=xmlRoot.getElementsByTagName('entry');

				i=0;
				Ext.each
				(
					rules,
					function(el)
					{
						var code=el.getElementsByTagName('id').item(0).firstChild.data;
						var descr=el.getElementsByTagName('option').item(0).firstChild.data;
						var style=el.getElementsByTagName('style').item(0).firstChild.data;

						combo.dom.options[i]=new Option(descr,code);
						combo.dom.options[i++].style.color = style;
					}
				);


				if (selected)
				{
					combo.dom.value=selected;
				}

			}
			catch (e)
			{
				alert(e.toString());
			}
		}
	};

	Ext.Ajax.request(o);
}

function CreateNewBadge() {
	var XMLHttp=CreateXMLHttpRequestObject();
	if (XMLHttp) {
		try {
			if ((XMLHttp.readyState==XHS_COMPLETE || XMLHttp.readyState==XHS_UNINIT)) {
				XMLHttp.open("POST",'IdCardCreate.php?CardType='+document.getElementById('BadgeType').value+'&CardName='+document.getElementById('newBadgeName').value, true);
				XMLHttp.onreadystatechange=function() {
					if (XMLHttp.readyState!=XHS_COMPLETE) return;
					if (XMLHttp.status!=200) return;
					try {
						var XMLResp=XMLHttp.responseXML;
						// intercetto gli errori di IE e Opera
						if (!XMLResp || !XMLResp.documentElement) throw(XMLResp.responseText);

						// Intercetto gli errori di Firefox
						var XMLRoot;
						if ((XMLRoot = XMLResp.documentElement.nodeName)=="parsererror") throw("ParseError");

						XMLRoot = XMLResp.documentElement;

						var Error=XMLRoot.getAttribute('error');
						if(Error==0) {
							location.href=XMLRoot.getAttribute('page');
						} else {
							alert(Error);
						}
					} catch(e) {
					}
				};
				XMLHttp.send();
			}
		} catch (e) {
		}
	}

}