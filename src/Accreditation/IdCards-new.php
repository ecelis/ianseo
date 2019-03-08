<?php

require_once(dirname(dirname(__FILE__)) . '/config.php');
CheckTourSession(true);
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Fun_Sessions.inc.php');
require_once('IdCardEmpty.php');

$CardType=(empty($_REQUEST['CardType']) ? 'A' : $_REQUEST['CardType']);
$CardNumber=(empty($_REQUEST['CardNumber']) ? 0 : intval($_REQUEST['CardNumber']));

$GlobalLink="CardType={$CardType}&CardNumber={$CardNumber}";

$TourId=$_SESSION['TourId'];

if(!empty($_REQUEST['delete'])) {
	safe_w_sql("delete from IdCards where IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");
	safe_w_sql("delete from IdCardElements where IceTournament={$_SESSION['TourId']} and IceCardType='$CardType' and IceCardNumber=$CardNumber");
	$imgs=glob($CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-'.$CardType.'-'.$CardNumber.'-*');
	foreach($imgs as $file) unlink($imgs);
	cd_redirect(basename(__FILE__).go_get('delete', '', true));
}

if(!empty($_FILES['ImportBackNumbers']['size'])) {
	require_once('Common/CheckPictures.php');
	if($Layout=unserialize(gzuncompress(file_get_contents($_FILES['ImportBackNumbers']['tmp_name'])))) {
		// before deleting gets the name of the badge
		$Name=get_text($CardType.'-Badge', 'BackNumbers');
		$q=safe_r_sql("select IcName from IdCards where IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");
		if($r=safe_fetch($q)) $Name=$r->IcName;
		safe_w_sql("delete from IdCards where IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");
		safe_w_sql("delete from IdCardElements where IceTournament={$_SESSION['TourId']} and IceCardType='$CardType' and IceCardNumber=$CardNumber");
		$SQL=array("IcTournament={$_SESSION['TourId']}");
		$SQL[]="IcType='$CardType'";
		$SQL[]="IcNumber=$CardNumber";
		$SQL[]="IcName='$Name'";
		foreach($Layout['IdCards'] as $f => $v) {
			if(in_array($f, array('IcTournament', 'IcType', 'IcName', 'IcNumber'))) continue;
			$SQL[]=$f.'='.StrSafe_DB($v);
		}
		safe_w_sql("insert ignore into IdCards set ".implode(',', $SQL));

		foreach($Layout['IdCardElements'] as $Record => $Fields) {
			$SQL=array("IceTournament={$_SESSION['TourId']}");
			$SQL[]="IceCardType='$CardType'";
			$SQL[]="IceCardNumber=$CardNumber";
			foreach($Fields as $f => $v) {
				if(in_array($f, array('IceTournament', 'IceCardType', 'IceCardNumber'))) continue;
				$SQL[]=$f.'='.StrSafe_DB($v);
			}
			safe_w_sql("insert ignore into IdCardElements set ".implode(',', $SQL));
			CheckPictures();
		}
	}
}

if(!empty($_REQUEST['ExportLayout'])) {
	$Layout=array();
	$q=safe_r_SQL("select * from IdCards where IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");
	if($r=safe_fetch_assoc($q)) {
		$Layout['IdCards']=$r;

		$q=safe_r_SQL("select * from IdCardElements where IceTournament={$_SESSION['TourId']} and IceCardType='$CardType' and IceCardNumber=$CardNumber");
		while($r=safe_fetch_assoc($q)) {
			$Layout['IdCardElements'][]=$r;
		}

		// We'll be outputting a gzipped TExt File in UTF-8 pretending it's binary
		header('Content-type: application/octet-stream');

		// It will be called ToCode-IdCard.ianseo
		header("Content-Disposition: attachment; filename=\"{$_SESSION['TourCode']}-{$CardType}-{$CardNumber}-IdCard.ianseo\"");

		ini_set('memory_limit',sprintf('%sM',512));

		echo gzcompress(serialize($Layout),9);
		die();
	}
}


$Badges=array();
$t=safe_r_sql("SELECT * FROM IdCards WHERE IcTournament={$_SESSION['TourId']} and IcType='$CardType' and IcNumber=$CardNumber");

if(safe_num_rows($t)) {
	$Badges['CardCustom.php']=get_text('BadgeCustom', 'BackNumbers');
}
$Badges['Card.php']=get_text('BadgeStandard', 'Tournament');
$Badges['Cardx6.php']=get_text('BadgeStandard6', 'Tournament');

// select sessions
$Qsessions=GetSessions('Q',true);
$Esessions=GetSessions('E',true);
$SesQNo=count($Qsessions);
$SesENo=count($Esessions);


$JS_SCRIPT = array(
	'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/ajax/ObjXMLHttpRequest.js"></script>',
	'<script type="text/javascript" src="Fun_AJAX_IdCards.js"></script>',
	);
	$JS_SCRIPT[]='<script type="text/javascript">';
	$JS_SCRIPT[]='	var SesQNo='.$SesQNo.';';
	$JS_SCRIPT[]='	var SesENo='.$SesENo.';';
	$JS_SCRIPT[]='</script>';

$PAGE_TITLE=get_text($CardType.'-Badge', 'BackNumbers');

$ONLOAD=' onload="ShowEntries()"';

include('Common/Templates/head.php');

/** CHECK IF THE REQUESTED BADGE EXISTS **/
$IdCards=safe_r_sql("select * from IdCards where IcTournament={$_SESSION['TourId']} and IcType='$CardType' order by IcNumber");

echo '<form method="POST" target="Badges" enctype="multipart/form-data">';
echo '<table class="Tabella">' ;
echo '<tr><th class="Title" colspan="2">' . $PAGE_TITLE  . '</th></tr>' . "\n";
echo '<tr>';
echo '<td width="50%">';

	// little table with the badge selector...
	echo '<table class="Tabella" style="margin-bottom:2em;">';
	echo '<tr><th>'.get_text('BadgeType', 'Tournament').'</th>
		<td><select id="BadgeType" name="CardType" onchange="location.href=\'?CardType=\'+this.value">';
	$TypeArray=array('A', 'Q');
	$q=safe_r_sql("Select distinct EvElim2, EvTeamEvent from Events where EvTournament={$_SESSION['TourId']} and EvFinalFirstPhase>0 order by EvElim2=0");
	while($r=safe_fetch($q)) {
		if($r->EvElim2>0 and !in_array('E', $TypeArray)) $TypeArray[]='E';
		if(!$r->EvTeamEvent and !in_array('I', $TypeArray)) $TypeArray[]='I';
		if($r->EvTeamEvent and !in_array('T', $TypeArray)) $TypeArray[]='T';
	}
		foreach($TypeArray as $Type) {
			echo '<option value="'.$Type.'"'.($CardType==$Type ? ' selected="selected"' : '').'>'.get_text($Type.'-Badge', 'BackNumbers').'</options>';
		}
		echo '</select></td></tr>';
	if(safe_num_rows($IdCards)) {
		echo '<tr><th>'.get_text('BadgeName', 'BackNumbers').'</th>
			<td><select name="CardNumber" id="BadgeNumber" onchange="location.href=\'?CardType='.$CardType.'&CardNumber=\'+this.value">';
			while($r=safe_fetch($IdCards)) {
				echo '<option value="'.$r->IcNumber.'"'.($CardNumber==$r->IcNumber ? ' selected="selected"' : '').'>'.$r->IcName.'</options>';
			}
			echo '</select></td></tr>';
	}
	echo '<tr><th>' . get_text('NewBadgeName', 'BackNumbers') . '</th>
		<td><input type="text" id="newBadgeName">
			<input type="button" value="'.get_text('BadgeCreate', 'BackNumbers').'" onclick="CreateNewBadge()">';
		if(safe_num_rows($IdCards)) echo ' <input type="button" value="'.get_text('BadgeDelete', 'BackNumbers').'" onclick="if(confirm(\''.get_text('MsgAreYouSure').'\')) {location.href=location.href+\'&delete=1\'}">';
		echo '</td></tr>';
	echo '</table>';

	if(!safe_num_rows($IdCards)) {
		// nothing else to show!
		echo '</td></tr></table></form>';
		include('Common/Templates/tail.php');
		die();
	}

/** Show all the options for this badge type and number **/
echo '<input name="BadgeDraw" type="radio" value="Complete" checked>&nbsp;' . get_text('BadgeComplete', 'BackNumbers') . '<br>';
echo '<input name="BadgeDraw" type="radio" value="Test">&nbsp;' . get_text('BadgeTest', 'BackNumbers') . '<br><br>';

// tipo di badge
echo '<div style="margin-bottom:1em"><b>'.get_text('BadgeType', 'Tournament').'</b>'."\n";
foreach($Badges as $BadgePage=>$Badge) {
	echo '<br/><input type="radio" name="BadgeType" onclick="this.form.action=\''.$BadgePage.'\'; document.getElementById(\'print_button\').style.display=\'inline\';document.getElementById(\'confirm_button\').style.display=\'none\'">'.$Badge."\n";
	if($BadgePage=='Card.php') {
		echo '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="BadgePerPage">'
			. '<option value="4">'.get_text('Badge4PerPage', 'Tournament').'</option>'
			. '<option value="2">'.get_text('Badge2PerPage', 'Tournament').'</option>'
			. '<option value="1">'.get_text('Badge1PerPage', 'Tournament').'</option>'
			. '</select>';
	} elseif($BadgePage=='CardCustom.php') {
		$RowBn=emptyIdCard(safe_fetch($t));
		echo '<table align="center">
			<tr align="center">
				<th colspan="2">&nbsp;</th>
				<th>'.get_text('IdCardOffsets', 'BackNumbers') . '</th>
				<th>'.get_text('PaperDimention', 'BackNumbers') . '</th>
			</tr>
			<tr align="center">
				<th>'.get_text('Width', 'BackNumbers') . '</th>
				<td>' . $RowBn->Settings["Width"] . '</td>
				<td>' . $RowBn->Settings["OffsetX"] . '</td>
				<td>' . $RowBn->Settings["PaperWidth"] . '</td>
			</tr>
			<tr align="center">
				<th>'.get_text('Heigh', 'BackNumbers') . '</th>
				<td>' . $RowBn->Settings["Height"] . '</td>
				<td>' . $RowBn->Settings["OffsetY"] . '</td>
				<td>' . $RowBn->Settings["PaperHeight"] . '</td>
			</tr>
			</table>';
	}
}
echo '</div>'."\n";
echo '</td>';

//Header e Immagini
// immagine fittizia del badge
	echo '<td width="50%" align="center"><br/>';
	if(safe_num_rows($t)) echo '<img src="ImgIdCard.php?'.$GlobalLink.'"><br/><br/>';
	echo '<input type="button" value="' . get_text('BadgeEdit', 'BackNumbers') . '" onClick="window.open(\''.$CFG->ROOT_DIR.'Accreditation/IdCardEdit.php?'.$GlobalLink.'\')">';
	echo '<br />';
	echo '<input type="submit" name="ExportLayout" value="' . get_text('BadgeExportLayout', 'BackNumbers') . '" onclick="this.form.target=\'\'; this.form.action=\''.basename(__FILE__).'\'">';
	echo '<br />';
	echo '<input type="file" name="ImportBackNumbers" />&nbsp;&nbsp;&nbsp;';
	echo '<input type="submit" name="ImportLayout" value="' . get_text('BadgeImportLayout', 'BackNumbers') . '" onclick="this.form.target=\'\'; this.form.action=\''.basename(__FILE__).'\'">';
	echo '</td>';
	echo '</tr>';
	echo '</table>';

	echo '<table class="Tabella">'  . "\n";
	echo '<tr><th class="Title" colspan="'.(5+($CardType=='I' or $CardType=='T')).'">' . get_text('BadgePrintout','Tournament')  . '</th></tr>' . "\n";
	echo '<tr>' . "\n";
	echo '<th class="Title">'.get_text('BadgeOptions','Tournament').'</th>' . "\n";
	echo '<th class="Title">'.get_text('Country').' (<span id="CountriesLeft"></span>)</th>' . "\n";
	if($CardType=='I' or $CardType=='T') {
		echo '<th class="Title">'.get_text('Phase').'</th>' . "\n";
	} else {
		echo '<th class="Title">'.get_text('Division').'</th>' . "\n";
		echo '<th class="Title">'.get_text('Class').'</th>' . "\n";
	}
	echo '<th class="Title">'.get_text('BadgeNames','Tournament').' (<span id="PicturesLeft"></span>)</th>' . "\n";
	echo '</tr>' . "\n";


	echo '<tr valign="top">' . "\n";

	// Elenco opzioni
	echo '<td nowrap="nowrap">';

	// Specific Options
	switch($CardType) {
		case 'A':
			if($_SESSION['AccreditationTourIds']) {
				$TourId=$_SESSION['AccreditationTourIds'];
			}
			if($_SESSION['AccBooth']) {
				echo '<div style="margin-bottom:1em"><b>'.get_text('Depot', 'BackNumbers').'</b>'."\n";
				echo '<br/><input type="checkbox" name="HasPlastic" id="HasPlastic" onclick="ShowEntries()">'.get_text('PrintHasPlastic', 'BackNumbers')."\n";
				echo '</div>';
			}
		case 'Q':
			echo '<div style="margin-bottom:1em"><b>'.get_text('BadgeSessions', 'Tournament').'</b>'."\n";
			foreach ($Qsessions as $s)
			{
				echo '<br/><input type="checkbox" onclick="ShowEntries()" id="d_QSession_'.$s->SesOrder.'" name="Session[]" value="' . $s->SesOrder . '" onclick="hide_confirm(this.form)">Session ' . $s->Descr ."\n";
			}
			echo '<br/><input type="checkbox" name="SortByTarget" id="SortByTarget"'.($CardType=='A' ? '' : ' checked="checked"').' onclick="ShowEntries()">'.get_text('SortByTarget', 'Tournament')."\n";
			// break is left out on purpose!
			if($CardType=='A') {
				echo '</div>'."\n";
				echo '<div style="margin-bottom:1em"><b>'.get_text('BadgeOptions', 'Tournament').'</b>'."\n";
				// badges devono includere la foto?
				echo '<br/><input type="checkbox" name="IncludePhoto" id="IncludePhoto" checked="checked" onclick="hide_confirm(this.form)">'.get_text('BadgeIncludePhoto', 'Tournament')."\n";
				// solo badges con foto?
				echo '<br/><input type="checkbox" name="PrintPhoto" id="PrintPhoto" checked="checked" onclick="ShowEntries()">'.get_text('BadgeOnlyPrintPhoto', 'Tournament')."\n";
				// solo accreditati?
				echo '<br/><input type="checkbox" name="PrintAccredited" id="PrintAccredited" onclick="ShowEntries()">'.get_text('BadgeOnlyPrintAccredited', 'Tournament')."\n";
			}
			// solo i non stampati precedentemente?
			echo '<br/><input type="checkbox" name="PrintNotPrinted" id="PrintNotPrinted" checked="checked" onclick="ShowEntries()">'.get_text('BadgeOnlyNotPrinted', 'Tournament')."\n";
			echo '</div>';
			break;
		case 'E':
			echo '<div style="margin-bottom:1em"><b>'.get_text('BadgeSessions', 'Tournament').'</b>'."\n";
			foreach ($Esessions as $s)
			{
				echo '<br/><input type="checkbox" onclick="ShowEntries()" id="d_ESession_'.$s->SesOrder.'" name="ESession[]" value="' . $s->SesOrder . '" onclick="hide_confirm(this.form)">Session ' . $s->Descr ."\n";
			}
			echo '<br/><input type="checkbox" name="SortByTarget" id="SortByTarget"'.($CardType=='A' ? '' : ' checked="checked"').' onclick="ShowEntries()">'.get_text('SortByTarget', 'Tournament')."\n";
			// solo i non stampati precedentemente?
			echo '<br/><input type="checkbox" name="PrintNotPrinted" id="PrintNotPrinted" checked="checked" onclick="ShowEntries()">'.get_text('BadgeOnlyNotPrinted', 'Tournament')."\n";
			echo '</div>'."\n";
			break;
		case 'I':
		case 'T':
			echo '<div style="margin-bottom:1em"><b>'.get_text('Events', 'Tournament').'</b>'."\n";
			$q=safe_r_sql("select * from Events where EvTournament={$_SESSION['TourId']} and EvTeamEvent=".intval($CardType=='T')." and EvFinalFirstPhase>0 and EvShootOff=1 order by EvProgr");
			while ($r=safe_fetch($q)) {
				echo '<br/><input type="checkbox" onclick="ShowEntries()" id="Event['.$r->EvCode.']" name="Event[]" value="'.$r->EvCode.'">' . $r->EvEventName ."\n";
			}
// 			echo '<br/><input type="checkbox" name="SortByTarget" id="SortByTarget"'.($CardType=='A' ? '' : ' checked="checked"').' onclick="ShowEntries()">'.get_text('SortByTarget', 'Tournament')."\n";
			// solo i non stampati precedentemente?
			echo '<br/><input type="checkbox" name="PrintNotPrinted" id="PrintNotPrinted" checked="checked" onclick="ShowEntries()">'.get_text('BadgeOnlyNotPrinted', 'Tournament')."\n";
			echo '</div>'."\n";
			break;
	}

	echo '</td>';

	// elenco Countries
	echo '<td align="center"><select onchange="ShowEntries()" name="Country[]" id="d_Country" multiple="multiple" title="'.get_text('PressCtrl2SelectAll').'" onclick="hide_confirm(this.form)" size="10">';
	$Sql = "SELECT distinct CoId, CoCode, CoName From Entries left join Countries on EnCountry=CoId WHERE EnTournament in ($TourId) order by CoCode, CoName";
	$Rs = safe_r_sql($Sql);
	while($r=safe_fetch($Rs)) {
		echo '<option value="'.$r->CoId.'">'.$r->CoCode.'-'.substr($r->CoName, 0, 30).'</option>';
	}
	echo '</select></td>';

	if($CardType=='I' or $CardType=='T') {
		// elenco Phases
		echo '<td align="center"><select onchange="ShowEntries()" name="Phase" id="d_Phase" title="'.get_text('PressCtrl2SelectAll').'" onclick="hide_confirm(this.form)" size="10">';
		echo '<option value="-1"></option>';
		$q=safe_r_sql("SELECT max(EvFinalFirstPhase) as MaxPhase from Events where EvTeamEvent=".($CardType=='T' ? 1 : 0)." and EvTournament in ($TourId)");
		if($Rs = safe_fetch($q)) {
			$Phase=$Rs->MaxPhase*2;
			while($Phase) {
				$Phase=intval($Phase/2);
				if($Phase==12) $Phase=16;
				echo '<option value="'.$Phase.'">'.get_text($Phase.'_Phase').'</option>';
			}
		}
		echo '</select></td>';
	} else {
		// elenco Divisions
		echo '<td align="center"><select onchange="ShowEntries()" name="Division[]" id="d_Division" multiple="multiple" title="'.get_text('PressCtrl2SelectAll').'" onclick="hide_confirm(this.form)" size="10">';
		$Sql = "SELECT distinct EnDivision From Entries WHERE EnTournament in ($TourId) order by EnDivision";
		$Rs = safe_r_sql($Sql);
		while($r=safe_fetch($Rs)) {
			echo '<option value="'.$r->EnDivision.'">'.$r->EnDivision.'</option>'."\n";
		}
		echo '</select></td>'."\n";

		// elenco Classes
		echo '<td align="center"><select onchange="ShowEntries()" name="Class[]" id="d_Class" multiple="multiple" title="'.get_text('PressCtrl2SelectAll').'" onclick="hide_confirm(this.form)" size="10">';
		$Sql = "SELECT distinct EnClass From Entries WHERE EnTournament in ($TourId) order by EnClass";
		$Rs = safe_r_sql($Sql);
		while($r=safe_fetch($Rs)) {
			echo '<option value="'.$r->EnClass.'">'.$r->EnClass.'</option>';
		}
		echo '</select></td>';
	}

	// elenco Entries
	echo '<td align="center"><select name="Entries[]" id="p_Entries" multiple="multiple" title="'.get_text('PressCtrl2SelectAll').'"  size="10">';
	// $Sql = "SELECT distinct EnId, EnDivision, EnClass, concat(EnFirstname, ' ', EnName) Name, (EnBadgePrinted is not null and EnBadgePrinted!='0000-00-00 00:00:00') Printed From Entries WHERE EnTournament = " . StrSafe_DB($_SESSION['TourId']) . " order by Printed, Name";
	// $Rs = safe_r_sql($Sql);
	// while($r=safe_fetch($Rs)) {
	// 	echo '<option value="'.$r->EnId.'"'.($r->Printed?' style="color:green"':' style="color:red"').'>'.$r->Name.' ('.$r->EnDivision.$r->EnClass.')</option>';
	// }
	echo '</select></td>';

	echo '</tr>'."\n";

	echo '<tr><td colspan="'.(5+($CardType=='I' or $CardType=='T')).'" align="center">'."\n";
	echo '<input type="button" style="display:none;margin-left:2em" id="confirm_button" name="DoPrint" title="'.get_text('BadgeConfirmPrintedDescr','Tournament').'" value="'.get_text('BadgeConfirmPrinted','Tournament').'" onclick="ConfirmPrinted()">'."\n";
	echo '<input type="submit" style="display:none" id="print_button" value="'.get_text('Print','Tournament').'" onclick="activate_confirm(this.form)">'."\n";

echo '</td></tr>'."\n";
echo '</table></form>'."\n";

echo '<script>
function activate_confirm(form) {
	form.target=\'Badges\';
	document.getElementById(\'confirm_button\').style.display=\'inline\';
}

function hide_confirm(form) {
	document.getElementById(\'confirm_button\').style.display=\'none\';
}

function check_confirm(form) {
	form.target=\'\';
	form.action=\'\'
}

</script>';

include('Common/Templates/tail.php');
?>