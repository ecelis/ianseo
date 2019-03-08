var ComboScheduleLoaded=false;
var timerReference;

function saveSequence(save) {
	var curSession = document.getElementById('x_Session').value;
	var curDistance = document.getElementById('x_Distance').value;
	var curEnd = document.getElementById('x_End').value;
	var XMLHttp=CreateXMLHttpRequestObject(); // private ajax value, overrides the global and eliminates the need for a queue management!
	if (XMLHttp.readyState==XHS_COMPLETE || XMLHttp.readyState==XHS_UNINIT) {
		XMLHttp.open("GET", 'Ind-UpdateSequence.php'+(save ? '?session='+curSession+'&distance='+curDistance+'&end='+curEnd : ''),true);
		XMLHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		XMLHttp.onreadystatechange=function() {
			if (XMLHttp.readyState==XHS_COMPLETE && XMLHttp.status==200) {
				try {
					// leggo l'xml
					var XMLResp=XMLHttp.responseXML;

					// intercetto gli errori di IE e Opera
					if (!XMLResp || !XMLResp.documentElement) {
						throw("XML not valid:\n"+XMLResp.responseText);
					}

					// Intercetto gli errori di Firefox
					var XMLRoot;
					if ((XMLRoot = XMLResp.documentElement.nodeName)=="parsererror") {
						throw("XML not valid:\n");
					}

					XMLRoot = XMLResp.documentElement;

					if (XMLRoot.getAttribute('error')==0) {
						document.getElementById('x_Session').value = XMLRoot.getElementsByTagName('session').item(0).firstChild.data;
						loadComboDistanceEnd();
						document.getElementById('x_Distance').value = XMLRoot.getElementsByTagName('distance').item(0).firstChild.data;
						document.getElementById('x_End').value = XMLRoot.getElementsByTagName('end').item(0).firstChild.data;
						loadDevices();
					} else {
					}
				} catch(e) {
					//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
				}
			}
		};
		XMLHttp.send(null);
	}
}

function loadComboDistanceEnd(distance) {
	var curSession = document.getElementById('x_Session').value;
	var MaxEnds=document.getElementById('x_Session').options[document.getElementById('x_Session').selectedIndex].maxEnds
	var Combo = document.getElementById('x_Distance');
	for(i=Combo.length-1; i>=0; --i) {
		Combo.remove(i);
	}
	if(curSession.charAt(0)=='Q') {
		for(i=0; i<curSession.charAt(1); i++) {
			Combo.options[i] = new Option(i+1,i+1);
			if(distance && distance==i+1) {
				Combo.options[i].selected=true;
			}
		}
		var Ends=MaxEnds.split(',');
		var End=Ends[0]*1;
	} else {
		Combo.options[0] = new Option("--","");
		var End=MaxEnds*1;
	}
	document.getElementById('x_End').max=End+(curSession.charAt(0)=='Q' ? 0 : 1);
	document.getElementById('x_End').value=0;
}

function adjustMaxEnd() {
	var curSession = document.getElementById('x_Session').value;
	var MaxEnds=document.getElementById('x_Session').options[document.getElementById('x_Session').selectedIndex].maxEnds
	var curDistance=document.getElementById('x_Distance').value;
	if(curDistance>0) {
		var Ends=MaxEnds.split(',');
		var End=Ends[curDistance-1];
	} else {
		var End=MaxEnds;
	}
	document.getElementById('x_End').max=End;
	document.getElementById('x_End').value=0;
}

function loadComboSchedule() {
	var onlyToday = (document.getElementById('x_onlyToday').checked==true ? 1:0);
	var XMLHttp=CreateXMLHttpRequestObject(); // private ajax value, overrides the global and eliminates the need for a queue management!
	if (XMLHttp.readyState==XHS_COMPLETE || XMLHttp.readyState==XHS_UNINIT) {
		XMLHttp.open("GET", 'Ind-GetComboSchedule.php?onlyToday='+onlyToday,true);
		XMLHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		XMLHttp.onreadystatechange=function() {
			if (XMLHttp.readyState==XHS_COMPLETE && XMLHttp.status==200) {
				try {
					// leggo l'xml
					var XMLResp=XMLHttp.responseXML;

					// intercetto gli errori di IE e Opera
					if (!XMLResp || !XMLResp.documentElement) {
						throw("XML not valid:\n"+XMLResp.responseText);
					}

					// Intercetto gli errori di Firefox
					var XMLRoot;
					if ((XMLRoot = XMLResp.documentElement.nodeName)=="parsererror") {
						throw("XML not valid:\n");
					}

					XMLRoot = XMLResp.documentElement;

					if (XMLRoot.getAttribute('error')==0) {
						var Combo = document.getElementById('x_Session');

						var Arr_Code = XMLRoot.getElementsByTagName('val');
						var Arr_Name = XMLRoot.getElementsByTagName('display');

						for(var i=Combo.length-1; i>=0; --i) {
							Combo.remove(i);
						}

						Combo.options[0] = new Option("--","");
						for (var i=0;i<Arr_Code.length;++i) {
							Combo.options[i+1] = new Option(Arr_Name.item(i).firstChild.data,Arr_Code.item(i).firstChild.data);

							// adding maxEnds support
							var MaxEnds=Arr_Code.item(i).getAttribute('maxends');
							Combo.options[i+1].maxEnds=MaxEnds;

							if(Arr_Code.item(i).getAttribute('selected')=='1') {
								Combo.options[i+1].selected=true;
								var Ends=MaxEnds.split(',');
								var End=Ends[0];
								if(XMLRoot.getAttribute('end')>0) {
									End=Ends[XMLRoot.getAttribute('end')-1];
								}
								document.getElementById('x_End').max=End;
							}
						}
						loadComboDistanceEnd(XMLRoot.getAttribute('distance'));
						document.getElementById('x_End').value=XMLRoot.getAttribute('end');

						ComboScheduleLoaded=true;
					} else {
					}
				} catch(e) {
					//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
				}
			}
		};
		XMLHttp.send(null);
		return XMLHttp;
	}
}

function loadDevices(OrderBy) {
	var XMLHttp=CreateXMLHttpRequestObject(); // private ajax value, overrides the global and eliminates the need for a queue management!
	if (XMLHttp.readyState==XHS_COMPLETE || XMLHttp.readyState==XHS_UNINIT) {
		XMLHttp.open("GET", 'Ind-GetTabletsInfo.php?ord='+OrderBy,true);
		XMLHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		XMLHttp.onreadystatechange=function() {
			if (XMLHttp.readyState==XHS_COMPLETE && XMLHttp.status==200) {
				try {
					// leggo l'xml
					var XMLResp=XMLHttp.responseXML;

					// intercetto gli errori di IE e Opera
					if (!XMLResp || !XMLResp.documentElement) {
						throw("XML not valid:\n"+XMLResp.responseText);
					}

					// Intercetto gli errori di Firefox
					var XMLRoot;
					if ((XMLRoot = XMLResp.documentElement.nodeName)=="parsererror") {
						throw("XML not valid:\n");
					}

					XMLRoot = XMLResp.documentElement;

					if (XMLRoot.getAttribute('error')==0) {
						var Arr_Tablets = XMLRoot.getElementsByTagName('tablet');
						var objTbody = document.getElementById('tablets');
						var arrTablets=new Array();
						for(var i=objTbody.rows.length-1; i>=0; --i) {
							arrTablets[i]=1;
//							objTbody.deleteRow(i);
						}
						var row;
						for(var i=0; i<Arr_Tablets.length; i++) {
							var device = Arr_Tablets.item(i).getAttribute('device');
							if(row=document.getElementById('row_'+device)) {
								// TargetNo
								var TgtRequested=Arr_Tablets.item(i).getAttribute('reqtarget');
								var TgtAssigned=Arr_Tablets.item(i).getAttribute('target');
								var tmp = row.cells[0];
								tmp.innerHTML = Arr_Tablets.item(i).getAttribute('target')
								if(Arr_Tablets.item(i).getAttribute('target')!=Arr_Tablets.item(i).getAttribute('reqtarget')){
									tmp.innerHTML += '&nbsp;('+Arr_Tablets.item(i).getAttribute('reqtarget')+')';
									tmp.className = "TargetRequested";
								} else {
									tmp.className = "TargetAssigned";
								}
								row.cells[0].style.backgroundColor = Arr_Tablets.item(i).getAttribute('online');

								// auth request
								tmp=row.cells[1];
								tmp.innerHTML = (Arr_Tablets.item(i).getAttribute('authrequest')==1 ? '<img src="'+imgPath+'status-couldshoot.gif">' : '');

								// tournament
								tmp = row.cells[2];
								tmp.innerHTML = '<img src="'+imgPath+'status-'+(Arr_Tablets.item(i).getAttribute('tournament')==TourId ? 'ok':'noshoot')+'.gif" onClick="setCompetitionDevice(\''+device+'\','+(Arr_Tablets.item(i).getAttribute('tournament')==TourId ? 'false':'true')+')">';

								//Application Pro/Lite and device Code
								row.cells[3].innerHTML = Arr_Tablets.item(i).getAttribute('code');
								row.cells[4].innerHTML = (Arr_Tablets.item(i).getAttribute('appdevversion')==1 ? Pro : Lite);
								row.cells[5].innerHTML = Arr_Tablets.item(i).getAttribute('appversion');

								//Device ID
								row.cells[6].innerHTML = device;

/* SE LA RIACCENDI ANCORA SENZA MIA AUTORIZZAZIONE TI TOLGO I PRIVILEGI DI SCRITTURA - Teo
 * 								//Device Alive
								if(Arr_Tablets.item(i).getAttribute('tournament')==TourId) {
									if(row.cells[7].childElementCount>0) {
										row.cells[7].firstChild.src='Ind-GetTabletOnline.php?device='+Arr_Tablets.item(i).getAttribute('code');
									} else {
										row.cells[7].innerHTML='<img src="Ind-GetTabletOnline.php?device='+Arr_Tablets.item(i).getAttribute('code')+'" height="16" width="16">';
									}
								}
*/
								row.cells[7].innerHTML=Arr_Tablets.item(i).getAttribute('seconds');

								//Device Status
								tmp = row.cells[8];
								if(Arr_Tablets.item(i).getAttribute('tournament')==TourId) {
									switch(Arr_Tablets.item(i).getAttribute('state')) {
									case "0":
										tmp.innerHTML = '<img src="'+imgPath+'status-noshoot.gif">';
										tmp.className = "ToBeEnabled";
										break;
									case "1":
										tmp.innerHTML = '<img src="'+imgPath+'status-ok.gif">';
										tmp.className = "Center";
										break;
									case "2":
										tmp.innerHTML = '<img src="'+imgPath+'status-unknown.gif">';
										tmp.className = "BarcodeRequested";
										break;
									case "3":
										tmp.innerHTML = '<img src="'+imgPath+'status-ok-gray.gif">';
										tmp.className = "BarcodeRequested";
										break;
									}
								}

								// Change status bar
								tmp = row.cells[9];
								if(Arr_Tablets.item(i).getAttribute('tournament')==TourId) {
									tmp.innerHTML = '<img src="'+imgPath+'status-noshoot.gif" onClick="setStatusDevice(\''+device+'\',0)">';
									tmp.innerHTML += '&nbsp;&nbsp;&nbsp;<img src="'+imgPath+'status-unknown.gif" onClick="setStatusDevice(\''+device+'\',2)">';
									tmp.innerHTML += '&nbsp;&nbsp;&nbsp;<img src="'+imgPath+'status-ok-gray.gif" onClick="setStatusDevice(\''+device+'\',3)">';
									tmp.innerHTML += '&nbsp;&nbsp;&nbsp;<img src="'+imgPath+'status-ok.gif" onClick="setStatusDevice(\''+device+'\',1)">';
								}

								//Battery
								row.cells[10].innerHTML = Arr_Tablets.item(i).getAttribute('battery');

								//Ip Address
								row.cells[11].innerHTML = Arr_Tablets.item(i).getAttribute('ip');

								//Last Seen
								row.cells[12].innerHTML = Arr_Tablets.item(i).getAttribute('lastseen');
								row.cells[12].style.backgroundColor = Arr_Tablets.item(i).getAttribute('online');

								// remove is already set...
							} else {
								var row = objTbody.insertRow(i);
								row.id='row_'+device;
								//TargetNo
								var cellIndex=0;
								var tmp = row.insertCell(cellIndex++);
								var TgtRequested=Arr_Tablets.item(i).getAttribute('reqtarget');
								var TgtAssigned=Arr_Tablets.item(i).getAttribute('target');
								tmp.id = 'tgt_'+device;
								tmp.setAttribute('onClick', 'manageTargetNo(\''+device+'\',0'+(TgtAssigned!=TgtRequested && TgtRequested!=0 ? TgtRequested : TgtAssigned)+');');
								tmp.innerHTML = Arr_Tablets.item(i).getAttribute('target')
								if(Arr_Tablets.item(i).getAttribute('target')!=Arr_Tablets.item(i).getAttribute('reqtarget')){
									tmp.innerHTML += '&nbsp;('+Arr_Tablets.item(i).getAttribute('reqtarget')+')';
									tmp.className = "TargetRequested";
								} else {
									tmp.className = "TargetAssigned";
								}
								// auth request
								tmp = row.insertCell(cellIndex++);
								tmp.innerHTML = (Arr_Tablets.item(i).getAttribute('authrequest')==1 ? '<img src="'+imgPath+'status-couldshoot.gif">' : '');
								tmp.className = "Center";

								// tournament
								tmp = row.insertCell(cellIndex++);
								tmp.innerHTML = '<img src="'+imgPath+'status-'+(Arr_Tablets.item(i).getAttribute('tournament')==TourId ? 'ok':'noshoot')+'.gif" onClick="setCompetitionDevice(\''+device+'\','+(Arr_Tablets.item(i).getAttribute('tournament')==TourId ? 'false':'true')+')">';
								tmp.className = "Center";
								//Application Pro/Lite and device Code
								(row.insertCell(cellIndex++)).innerHTML = Arr_Tablets.item(i).getAttribute('code');
								(row.insertCell(cellIndex++)).innerHTML = (Arr_Tablets.item(i).getAttribute('appdevversion')==1 ? Pro : Lite);
								(row.insertCell(cellIndex++)).innerHTML = Arr_Tablets.item(i).getAttribute('appversion');
								//Device ID
								(row.insertCell(cellIndex++)).innerHTML = device;
								//Device Alive
								tmp = row.insertCell(cellIndex++);
								tmp.className = "Center";
//							if(Arr_Tablets.item(i).getAttribute('tournament')==TourId) {
//								tmp.innerHTML = '<img src="Ind-GetTabletOnline.php?device='+Arr_Tablets.item(i).getAttribute('code')+'" height="16" width="16">';
//							}
								//Device Status
								tmp = row.insertCell(cellIndex++);
								tmp.className = "Center";
								if(Arr_Tablets.item(i).getAttribute('tournament')==TourId) {
									switch(Arr_Tablets.item(i).getAttribute('state')) {
									case "0":
										tmp.innerHTML = '<img src="'+imgPath+'status-noshoot.gif">';
										tmp.className = "ToBeEnabled";
										break;
									case "1":
										tmp.innerHTML = '<img src="'+imgPath+'status-ok.gif">';
										break;
									case "2":
										tmp.innerHTML = '<img src="'+imgPath+'status-unknown.gif">';
										tmp.className = "BarcodeRequested";
										break;
									case "3":
										tmp.innerHTML = '<img src="'+imgPath+'status-ok-gray.gif">';
										tmp.className = "BarcodeRequested";
										break;
									}
								}
								// Change status bar
								tmp = row.insertCell(cellIndex++);
								tmp.className = "Center";
								tmp.style.whiteSpace='nowrap';
								if(Arr_Tablets.item(i).getAttribute('tournament')==TourId) {
									tmp.innerHTML = '<img src="'+imgPath+'status-noshoot.gif" onClick="setStatusDevice(\''+device+'\',0)">';
									tmp.innerHTML += '&nbsp;&nbsp;&nbsp;<img src="'+imgPath+'status-unknown.gif" onClick="setStatusDevice(\''+device+'\',2)">';
									tmp.innerHTML += '&nbsp;&nbsp;&nbsp;<img src="'+imgPath+'status-ok-gray.gif" onClick="setStatusDevice(\''+device+'\',3)">';
									tmp.innerHTML += '&nbsp;&nbsp;&nbsp;<img src="'+imgPath+'status-ok.gif" onClick="setStatusDevice(\''+device+'\',1)">';
								}
								//Battery
								(row.insertCell(cellIndex++)).innerHTML = Arr_Tablets.item(i).getAttribute('battery');
								//Ip Address
								tmp = row.insertCell(cellIndex++);
								tmp.innerHTML = Arr_Tablets.item(i).getAttribute('ip');
								tmp.className = "Right";
								//Last Seen
								tmp = row.insertCell(cellIndex++);
								tmp.innerHTML = Arr_Tablets.item(i).getAttribute('lastseen');
								tmp.className = "Right";
								tmp.style.backgroundColor = Arr_Tablets.item(i).getAttribute('online');

								// remove device from DB
								tmp = row.insertCell(cellIndex++);
								tmp.innerHTML = '<input type="button" value="'+msgRemove+'" onclick="if(confirm(\''+MsgConfirm+'\')) {window.location.href=\'?remove='+device+'\'}">';
								tmp.style.textAlign = "center";
							}
						}
						timerReference = setTimeout(function(){ loadDevices(); }, 5000);
					} else {
					}
				} catch(e) {
					//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
				}
			}
		};
		XMLHttp.send(null);
		return XMLHttp;
	}
}

function manageTargetNo(device, defValue) {
	clearTimeout(timerReference);
	objText = document.getElementById('targetno_'+device);
	if(objText == undefined) {
		document.getElementById('tgt_'+device).setAttribute('onClick','');
		document.getElementById('tgt_'+device).innerHTML=
			'<input type="number" id="targetno_'+device+'" min="1" max="999" value="'+defValue+'">' +
			'<input type="button" value="'+MsgOk+'" onClick="manageTargetNo(\''+device+'\');">';
	} else {
		var XMLHttp=CreateXMLHttpRequestObject(); // private ajax value, overrides the global and eliminates the need for a queue management!
		if (XMLHttp.readyState==XHS_COMPLETE || XMLHttp.readyState==XHS_UNINIT) {
			XMLHttp.open("GET", 'Ind-SetDeviceInfo.php?device='+device+'&setTarget='+objText.value,true);
			XMLHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			XMLHttp.onreadystatechange=function() {
				if (XMLHttp.readyState==XHS_COMPLETE && XMLHttp.status==200) {
					try {
						// leggo l'xml
						var XMLResp=XMLHttp.responseXML;

						// intercetto gli errori di IE e Opera
						if (!XMLResp || !XMLResp.documentElement) {
							throw("XML not valid:\n"+XMLResp.responseText);
						}

						// Intercetto gli errori di Firefox
						var XMLRoot;
						if ((XMLRoot = XMLResp.documentElement.nodeName)=="parsererror") {
							throw("XML not valid:\n");
						}

						XMLRoot = XMLResp.documentElement;
						loadDevices();
					} catch(e) {
						//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
					}
				}
			};
			XMLHttp.send(null);
			return XMLHttp;
		}
	}
}

function setStatusDevice(device, newStatus) {
	if(newStatus==0 || confirm(MsgConfirm) ) {
		clearTimeout(timerReference);
		var XMLHttp=CreateXMLHttpRequestObject(); // private ajax value, overrides the global and eliminates the need for a queue management!
		if (XMLHttp.readyState==XHS_COMPLETE || XMLHttp.readyState==XHS_UNINIT) {
			XMLHttp.open("GET", 'Ind-SetDeviceInfo.php?device='+device+'&setStatus='+newStatus,true);
			XMLHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			XMLHttp.onreadystatechange=function() {
				if (XMLHttp.readyState==XHS_COMPLETE && XMLHttp.status==200) {
					try {
						// leggo l'xml
						var XMLResp=XMLHttp.responseXML;

						// intercetto gli errori di IE e Opera
						if (!XMLResp || !XMLResp.documentElement) {
							throw("XML not valid:\n"+XMLResp.responseText);
						}

						// Intercetto gli errori di Firefox
						var XMLRoot;
						if ((XMLRoot = XMLResp.documentElement.nodeName)=="parsererror") {
							throw("XML not valid:\n");
						}

						XMLRoot = XMLResp.documentElement;
						if (XMLRoot.getAttribute('error')==0) {
							loadDevices();
						} else {
							timerReference = setTimeout(function(){ loadDevices(); }, 5000)
						}
					} catch(e) {
						//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
					}
				}
			};
			XMLHttp.send(null);
			return XMLHttp;
		}
	}
}

function setCompetitionDevice(device,enable) {
	if(enable || confirm(MsgConfirm) ) {
		clearTimeout(timerReference);
		var XMLHttp=CreateXMLHttpRequestObject(); // private ajax value, overrides the global and eliminates the need for a queue management!
		if (XMLHttp.readyState==XHS_COMPLETE || XMLHttp.readyState==XHS_UNINIT) {
			XMLHttp.open("GET", 'Ind-SetDeviceInfo.php?device='+device+'&setCompetition='+enable,true);
			XMLHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			XMLHttp.onreadystatechange=function() {
				if (XMLHttp.readyState==XHS_COMPLETE && XMLHttp.status==200) {
					try {
						// leggo l'xml
						var XMLResp=XMLHttp.responseXML;

						// intercetto gli errori di IE e Opera
						if (!XMLResp || !XMLResp.documentElement) {
							throw("XML not valid:\n"+XMLResp.responseText);
						}

						// Intercetto gli errori di Firefox
						var XMLRoot;
						if ((XMLRoot = XMLResp.documentElement.nodeName)=="parsererror") {
							throw("XML not valid:\n");
						}

						XMLRoot = XMLResp.documentElement;
						if (XMLRoot.getAttribute('error')==0) {
							loadDevices();
						} else {
							timerReference = setTimeout(function(){ loadDevices(); }, 5000)
						}
					} catch(e) {
						//document.getElementById('idOutput').innerHTML='Errore: ' + e.toString();
					}
				}
			};
			XMLHttp.send(null);
			return XMLHttp;
		}
	}
}
