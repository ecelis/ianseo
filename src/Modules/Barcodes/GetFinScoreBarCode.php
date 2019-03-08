<?php
define('IN_PHP', true);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('Common/Fun_Number.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Fun_Sessions.inc.php');
require_once('Final/Fun_Final.local.inc.php');
require_once('Common/Lib/ArrTargets.inc.php');

CheckTourSession(true);
$Match='';

// Check the correct separator (as barcode reader may interpret «-» as a «'» !)
//
if(empty($_SESSION['BarCodeSeparator'])) {
	require_once('./GetBarCodeSeparator.php');
	die();
}

$ShowMiss=(!empty($_GET['ShowMiss']));
$T=0;
$Turno='';

if($_GET) {
	if(!empty($_GET['BARCODESEPARATOR'])) {
		unset($_SESSION['BarCodeSeparator']);
		CD_redirect($_SERVER['PHP_SELF']);
	}
/*

Aggiunto il campo FinConfirm e TfConfirm (int(4)) nelle rispettive tabelle per confermare i match!

*/
	if(!empty($_GET['T'])) $Turno='&T='.($T=$_GET['T']);

	// sets the autoedit feature
	if(!empty($_GET['AutoEdit']) and empty($_GET['return']) and empty($_GET['C'])) $_GET['C']='EDIT';
	unset($_GET['return']);

	if(!empty($_GET['B'])) {
		// get the match
		$Match=getScore($_GET['B']);
		if(!empty($Match->FsDate1) and !empty($Match->FsTime1)) $_GET['T']=$Match->FsDate1.'|'.$Match->FsTime1;

		// if we have a "C" input (beware of autoedit!) then do the action
		if(!empty($_GET['C'])) {
			$C=$_GET['C'];
			unset($_GET['C']);
			if($Match and !IsBlocked(BIT_BLOCK_QUAL)) {
				switch(strtoupper($C)) {
					case 'EDIT':
						$GoBack=$_SERVER['SCRIPT_NAME'].go_get().'&return=1';

						// edit the scorecard
						$_REQUEST['Team']=$Match->teamEvent;
						$_REQUEST['d_Event']=$Match->event;
						$_REQUEST['d_Match']=$Match->match1;
						require_once('Final/WriteScoreCard.php');
						die();
						break;
// 					case 'EDIT2':
// 						$GoBack=$_SERVER['SCRIPT_NAME'].go_get().'&return=1';
// 						TODO: go to the edit page;

// 						// edit the scorecard
// 						$_REQUEST['Command']='OK';
// 						$_REQUEST['x_Session']=$archer->QuTargetNo[0];
// 						$_REQUEST['x_Dist']=$D;
// 						$_REQUEST['x_From']=substr($archer->QuTargetNo, 1, -1);
// 						$_REQUEST['x_To']=substr($archer->QuTargetNo, 1, -1);
// 						if(count($archers)==1) $_REQUEST['x_Target']=$archer->QuTargetNo;
// 						$_REQUEST['x_Gold']=1;

// 						require_once('Qualification/index.php');
// 						die();
// 						break;
					case strtoupper($_GET['B']):
						ConfirmMatch($Match);
						unset($_GET['B']);
						cd_redirect(basename(__FILE__).go_get());
						break;
					default:
						// reads another barcode
						$_GET['B']=$C;
						cd_redirect(basename(__FILE__).go_get());
				}
			} elseif(getScore($C)) {
				// reads another barcode
				$_GET['B']=$C;
				cd_redirect(basename(__FILE__).go_get());
			}
		}
	}
}

$ONLOAD=' onLoad="javascript:document.Frm.bib.focus()"';
$JS_SCRIPT=array('<style>');
if($ShowMiss) {
	$JS_SCRIPT[]='
		form.ShowMiss {position:absolute;left:0;right:200px;}
		div.ShowMiss {position:absolute;width:190px;top:0;right:0;bottom:0;overflow:hide;}
		';
}
$JS_SCRIPT[]='
	.selected td {background-color:#d0d0d0;font-weight:bold}
	';
$JS_SCRIPT[]='</style>';

include('Common/Templates/head.php');

?>
<form name="Frm" method="get" action="" class="ShowMiss">
<table class="Tabella2 half">
	<tr>
		<th class="Title" colspan="4"><?php print get_text('CheckScorecards','Tournament');?></th>
	</tr>
	<?php
		echo '<tr>';
		echo '<th colspan="3">' . get_text('BarcodeSeparator','BackNumbers') . ': <span style="font-size:150%">' . $_SESSION['BarCodeSeparator'] . '</span>' . '</th>';
		echo '<th colspan="1"><a href="' . $_SERVER["PHP_SELF"]. '?BARCODESEPARATOR=1">' . get_text('ResetBarcodeSeparator','BackNumbers') . '</a></th>';
		echo '</tr>';
	?>
	<tr>
		<th><?php print get_text('AutoEdits','Tournament');?></th>
		<th><?php print get_text('ShowMissing','Tournament');?></th>
		<th><?php print get_text('Barcode','BackNumbers');?></th>
		<th><?php print get_text('Session');?></th>
	</tr>
	<tr>
		<td class="Center"><input type="checkbox" onclick="document.Frm.bib.focus()" name="AutoEdit"  <?php echo (!empty($_GET['AutoEdit']) ? ' checked="checked"' : ''); ?>></td>
		<td class="Center"><input type="checkbox" onclick="document.Frm.bib.focus()" name="ShowMiss"  <?php echo (!empty($_GET['ShowMiss']) ? ' checked="checked"' : ''); ?>></td>
		<td class="Center"><?php
if(!empty($_GET['B'])) {
	echo '<input type="hidden" name="B" value="'.$_GET['B'].'">';
	echo '<input type="text" name="C" id="bib" tabindex="1">';
} else {
	echo '<input type="text" name="B" id="bib" tabindex="1">';
}


?></td>
		<td class="Center"><select id="Session" name="T"  onclick="document.Frm.bib.focus()"><option value="0"></option><?php
$q=safe_r_sql("Select distinct group_concat(distinct FSEvent ORDER BY FSEvent SEPARATOR '-') Event, FSScheduledDate, FSScheduledTime from FinSchedule where FsTournament={$_SESSION['TourId']} and FSScheduledDate>0 group by FSScheduledDate,FSScheduledTime order by FSScheduledDate,FSScheduledTime");
while($r=safe_fetch($q)) echo '<option value="'.$r->FSScheduledDate.'|'.$r->FSScheduledTime.'" '.(!empty($_GET['T']) && $_GET['T']==$r->FSScheduledDate.'|'.$r->FSScheduledTime ? ' selected="selected"' : '').'>'.($r->FSScheduledDate.' '.$r->FSScheduledTime. ' ('.$r->Event.')').'</option>';
?></select></td>
</tr>
	<tr>
		<td class="Center" colspan="2"><input type="submit" value="<?php print get_text('CmdGo','Tournament');?>" id="Vai" onClick="javascript:SendBib();"></td>
		<td class="Center" colspan="2"><input type="button" value="<?php print get_text('BarcodeMissing','Tournament');?>" onClick="window.open('./GetScoreBarCodeMissing.php?T='+document.getElementById('Session').value);"></td>
	</tr>
	<?php 
	if(!$Match){
		echo '<tr class="divider"><td colspan="4"></td></tr>
		<tr><th colspan="4"><img src="beiter.png" width="80" hspace="10" alt="Beiter Logo" border="0"/><br>' . get_text('Credits-BeiterCredits', 'Install') . '</th></tr>';
	}
	?>
</table>
<?php

if($Match) {
/*
[name1] => Bucki Mateusz (POL)
[name2] => Van Der Ven Rick (NED)
[match1] => 62
[match2] => 63
[event] => RM
[phase] => 16
[live] => 0
[teamEvent] => 0
[mixedTeam] => 0
[LastUpdate] => 2013-02-28 08:02:55
[matchMode] => 1
[matchArrowsNo] => 240
[score1] => 81
[setScore1] => 0
[setPoints1] => 28|26|27|0|0
[tie1] => 0
[arrowString1] => LJJLJHLJI
[tiebreak1] =>
[score2] => 87
[setScore2] => 6
[setPoints2] => 30|28|29|0|0
[tie2] => 0
[arrowString2] => LLLLJJLLJ
[tiebreak2] =>
*/
	echo '<table class="Tabella2" style="font-size:150%">';
	echo '<tr><th class="Title" colspan="5">'.get_text('Archer').'</th></tr>';
	echo '<tr><th class="Title" colspan="5">'.get_text('Target'). ' ' . $Match->target1 . ($Match->target1!=$Match->target2 ? ' - ' . $Match->target2 : '') . '</th></tr>';
	echo '<tr>';
	echo '<th colspan="2">'.$Match->name1.'</th>';
	echo '<th>&nbsp;</th>';
	echo '<th colspan="2">'.$Match->name2.'</th>';
	echo '</tr>';

	echo '<tr>';
	echo '<th>'.get_text('Score', 'Tournament').'</th>';
	echo '<td class="LetteraGrande" align="right">'.($Match->matchMode ? $Match->setScore1:$Match->score1).'</td>';
	echo '<th>&nbsp;</th>';
	echo '<th>'.get_text('Score', 'Tournament').'</th>';
	echo '<td class="LetteraGrande" align="right">'.($Match->matchMode ? $Match->setScore2:$Match->score2).'</td>';
	echo '</tr>';

	if($Match->matchMode) {
		echo '<tr>';
		echo '<td colspan="2" class="LetteraGrande" align="right">'.str_replace("|",",&nbsp;",$Match->setPoints1).'</td>';
		echo '<th>&nbsp;</th>';
		echo '<td colspan="2" class="LetteraGrande" align="right">'.str_replace("|",",&nbsp;",$Match->setPoints2).'</td>';
		echo '</tr>';

	}
	echo '<tr>';
	echo '<th>'.get_text('ShotOffShort', 'Tournament').'</th>';
	echo '<td class="LetteraGrande" align="right">'.(!empty($Match->tiebreak1) ? (strlen($Match->tiebreak1)>1 ? implode(DecodeFromString($Match->tiebreak1, false),',') : DecodeFromString($Match->tiebreak1, false)):'&nbsp;').'</td>';
	echo '<th>&nbsp;</th>';
	echo '<th>'.get_text('ShotOffShort', 'Tournament').'</th>';
	echo '<td class="LetteraGrande" align="right">'.(!empty($Match->tiebreak2) ? (strlen($Match->tiebreak2)>1 ? implode(DecodeFromString($Match->tiebreak2, false),',') : DecodeFromString($Match->tiebreak2, false)):'&nbsp;').'</td>';
	echo '</tr>';

	echo '<tr>';
		echo '<td colspan="2" align="center" style="font-size:80%"><b><a href="'.go_get(array('C'=>$_REQUEST['B'])).'">CONFIRM</a></b></td>';
		echo '<th>&nbsp;</th>';
		echo '<td colspan="2" align="center" style="font-size:80%"><b><a href="'.go_get(array('C'=> 'EDIT')).'">Edit arrows</a>';
// 		echo '<br/><a href="'.go_get(array('C' => 'EDIT2')).'">Edit totals</a></b>';
		echo '</td>';
		echo '</tr>';
	echo '</table>';
}


?>
</form>
<?php
if($ShowMiss and !empty($_GET['T'])) {
	list($FsDate, $FsTime)=explode('|', $_GET['T']);
	echo '<div class="ShowMiss"><table>';
	$cnt = 0;
	$tmpRow = '';
	$Q=GetFinMatches_sql(" and fs1.FSScheduledDate='$FsDate' and fs1.FSScheduledTime='$FsTime' and f1.FinConfirmed=0", 0, ' target1');
	while($r=safe_fetch($Q)) {
		if(!$r->familyName1 or !$r->familyName2) continue;
		$tmpRow .= '<tr><td>'.ltrim($r->target1,'0').($r->target1!=$r->target2 ? '-'.ltrim($r->target2,'0') : '').'</td><td nowrap="nowrap">'.$r->familyName1.'</td><td nowrap="nowrap">'.$r->familyName2.'</td></tr>';
		$cnt++;
	}
	$Q=GetFinMatches_sql(" and fs1.FSScheduledDate='$FsDate' and fs1.FSScheduledTime='$FsTime' and tf1.TfConfirmed=0", 1, ' target1');
	while($r=safe_fetch($Q)) {
		if(!$r->familyName1 or !$r->familyName2) continue;
		$tmpRow .= '<tr><td nowrap="nowrap">'.ltrim($r->target1,'0').($r->target1!=$r->target2 ? '-'.ltrim($r->target2,'0') : '').'</td><td nowrap="nowrap">'.$r->familyName1.'</td><td nowrap="nowrap">'.$r->familyName2.'</td></tr>';
		$cnt++;
	}
	echo '<tr><th colspan="3">' . get_text('TotalMissingScorecars','Tournament',$cnt) . '</th></tr>';
	echo $tmpRow;
	echo '</table></div>';
}
?>
<div id="idOutput"></div>
<?php
include('Common/Templates/tail.php');


function getScore($barcode, $strict=false) {
	@list($matchno, $team, $event) = @explode($_SESSION['BarCodeSeparator'], $barcode, 3);
	$event=str_replace($_SESSION['BarCodeSeparator'], "-", $event);
// 	debug_svela($event);
	$rs=GetFinMatches($event, null, $matchno, $team, false);
	return safe_fetch($rs);
}

function ConfirmMatch($Match) {
	require_once('Final/Fun_ChangePhase.inc.php');
	$prefix=($Match->teamEvent ? 'Tf' : 'Fin');
	$SQL= "update ".($Match->teamEvent ? 'Team' : '')."Finals
		set {$prefix}Confirmed=1,
		{$prefix}Status=1
		where {$prefix}Tournament={$_SESSION['TourId']}
			and {$prefix}Event='$Match->event'
			and {$prefix}Matchno in ($Match->match1, $Match->match2) ";
	safe_w_sql($SQL);

	// promote the winner to the next phase
	if($Match->teamEvent) {
		move2NextPhaseTeam(null, $Match->event, $Match->match1);
	} else {
		move2NextPhase(null, $Match->event, $Match->match1);
	}
}