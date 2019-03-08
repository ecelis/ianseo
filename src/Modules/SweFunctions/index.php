<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/config.php');

CheckTourSession(true);

require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Lib/Obj_RankFactory.php');
include('Common/Templates/head.php');
?>
<div align="center"> 
<div>
    <h1>Support information!</h1>
    <div style="font-size: 1.2em">
    Detta är en IANSEO modul som supportas av Fredrik Larsson (fredrik@larre.nu) <br /> 
    för Svenska Bågskytteförbundets räkning.<br /><br />
    Uppstår frågor eller problem med denna modul kontakta då Fredrik Larsson, <br />
    IANSEO's support-team kan inte hjälpa till med frågor gällande denna modul.  
    </div>
    <br />
    <div>Modulversion: <?= $swe_module_version; ?> </div>
    <br /><hr /><br />
    <div style="font-size: 1.2em">
    Modulen låter dig rapportera in tävlingensresultatet direkt till den svenska resultatdatabasen.<br />
    Fyll i Tävlingsnummer och lösenord, klicka sedan på Testa inställningar. <br />
    Som svar kommer du få tävlingens namn till svar och om den tillåter att resultat kan skickas in.<br />
    Klicka i omgångarna som skall skickas in och klicka på Skicka knappen<br /><br />
    <table>
    <thead><tr><th width="20%">Fält</th><th>Beskrivning</th></tr></thead>
    <tbody>
    <tr><td>Tävlingsnummer</td><td>Är tävlingens nummer i den svenska tävlingsdatabasen.</td></tr>
    <tr><td>Lösenord</td><td>Ett lösenord satt på tävlingen, tillsvidare kan lösenord begäras av Fredrik Larsson.</td></tr>
    <tr><td>Grundomgångar</td><td>Skickar in grundomgångens resultat till resultat databasen.</td></tr>
    <tr><td>Finaler</td><td>Skickar in resultaten för final omgångarna. (kommer fungera senare).</td></tr>
    </tbody>
    </table>
    <br />
    </div>
</div>

<div width="50%" style="background-color:#004488; text-align:center; padding-left:2px; padding-right:2px; font-weight:bold; color: #F2F9ff; font-size:120%;">Skicka in resultat</div>
<br />
<table class="Tabella" id="ConfigurationSettings" style="width:50%">
<thead>
    <tr><th colspan="2">Inställningar</th></tr>
</thead>
<tbody>
    <tr><td class="Bold Right" width="30%">Tävlingsnummer</td><td><input type="text" name="s_competition_code" id="s_competition_code"></input></td></tr>
    <tr><td class="Bold Right" width="30%">Lösenord</td><td><input type="password" name="s_competition_password" id="s_competition_password"></input></td></tr>
    <tr><td colspan="2" align="center">&nbsp;<i id="status_information"></i></td></tr>
    <tr class="Divider"><td colspan=2>&nbsp;</td></tr>
    <tr><td colspan="2" class="Center"><input type="submit" onClick="test_settings();" value="Testa inställningar" /></td></tr>
</tbody>
</table>
<br /><br />
<table class="Tabella" id="CompetitonSelector" style="width:50%">
<thead>
    <tr><th colspan="2">Rapportera</th></tr>
</thead>
<tbody>
    <tr><td class="Bold Right" width="30%" rowspan=2>Individuella</td><td><input type="checkbox" name="r_select" value="ind_kval" />Grundomgångar</input></td></tr>
    <tr><td><input type="checkbox" name="r_select" value="ind_final" />Finaler</input></td></tr>
    <tr><td colspan="2" align="center">&nbsp;<i id="status_result"></i></td></tr>
    <tr class="Divider"><td colspan="2">&nbsp;</td></tr>
    <tr><td colspan="2" class="Center"><input type="submit" onClick="send_result();" value="Skicka" id="sendbutton" /></td></tr>
</tbody>
</table>
</div>
<script>
    window.onload = disableButton; 
    function disableButton() {
        document.getElementById("sendbutton").disabled = true;
        console.log("Button disabled.");
    }
    function test_settings() {
        var data = {"auth": {"competition": document.getElementById("s_competition_code").value, "password": document.getElementById("s_competition_password").value}};
        var xhr = new XMLHttpRequest();
        xhr.open("POST","testsetting.php",true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.onreadystatechange = handler;
        xhr.send(JSON.stringify(data));
    };
    function send_result() {
        var data = {"auth": {"competition": document.getElementById("s_competition_code").value, "password": document.getElementById("s_competition_password").value}};
        data["option"] = {"ind_kval": document.getElementsByName("r_select")[0].checked, "ind_final": document.getElementsByName("r_select")[1].checked};
        var xhr = new XMLHttpRequest();
        xhr.open("POST","send.php",true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.onreadystatechange = resulthandler;
        xhr.send(JSON.stringify(data));
    };
    function handler() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                var resp = JSON.parse(this.responseText);
                console.log(resp['status']);
                if (resp['status'] == 'Ready') {
                    document.getElementById("sendbutton").disabled = false;
                    console.log("Button changed. aktivated");
                } else {
                    document.getElementById("sendbutton").disabled = true;
                    console.log("button changed. disabled");
                }
                document.getElementById("status_information").innerHTML = resp['message'];
            }
        }
    };
    function resulthandler() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                var data = JSON.parse(this.response);
                if (data['status'] == 'Ready') {
                    var xc;
                    document.getElementById("status_result").innerHTML = "";
                    for (xc in data['results']) {
                        document.getElementById("status_result").innerHTML += data['results'][xc]['className'] + ' Exporterade: ' + data['results'][xc]['imported'] + ' Ej exporterade: ' + data['results'][xc]['failed'] + '<br />'; 
                    }
                    document.getElementById("status_result").innerHTML += "</tbody></table>";
                } else {
                    document.getElementById("status_result").innerHTML = data['message'];
                }
                
            }
        }
    };
</script>
<?php
	include('Common/Templates/tail.php');
?>
