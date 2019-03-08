<?php

require_once(dirname(dirname(__FILE__)).'/config.php');
$Error=1;
$Out='';

if(!CheckTourSession()) {
	header('Content-Type: text/xml');
	die('<response error="'.$Error.'"/>');
}

require_once('Common/Lib/Fun_Modules.php');
require_once('Common/Lib/ArrTargets.inc.php');

// data[23][RW][0][I][010B][0][6]=J
// $popId="data[$r->IskDtMatchNo][$r->IskDtEvent][$r->IskDtTeamInd][$r->IskDtType][$r->IskDtTargetNo][$r->IskDtDistance][$r->IskDtEndNo]=$r->IskDtArrowstring";

$MatchNo    = (isset($_REQUEST['matchno']) ? intval($_REQUEST['matchno']) : '0');
$Event      = (isset($_REQUEST['event']) ? $_REQUEST['event'] : '');
$Team       = (isset($_REQUEST['ses']) ? intval($_REQUEST['ses'][0]=='T') : '0');
$Type       = (isset($_REQUEST['ses']) ? $_REQUEST['ses'][0] : '0');
$Target     = (isset($_REQUEST['target']) ? $_REQUEST['ses'][2].str_pad($_REQUEST['target'], 3, '0', STR_PAD_LEFT) : '');
$Distance   = (isset($_REQUEST['dist']) ? $_REQUEST['dist'] : '0');
$End        = (isset($_REQUEST['end']) ? $_REQUEST['end'] : '0');
$Arrowstring='';


if(isset($_REQUEST['data'])) {
	$SQL='';
	$tmp=each($_REQUEST['data']);
	$MatchNo=$tmp['key'];	$tmp=each($tmp['value']);
	$Event=$tmp['key']; 	$tmp=each($tmp['value']);
	$Team=$tmp['key'];		$tmp=each($tmp['value']);
	$Type=$tmp['key'];		$tmp=each($tmp['value']);
	$Target=$tmp['key'];	$tmp=each($tmp['value']);
	$Distance=$tmp['key'];	$tmp=each($tmp['value']);
	$End=$tmp['key'];
	$Arrowstring=$tmp['value'];

	if($Event==':::') $Event='';
	if($Target==':::') $Target='';
}

switch($Type) {
	case 'Q':
		// fetches all the scorings of the target for that end, distance and session
		$SQL="select IskData.*, EnFirstName, substring(AtTargetNo, -4, 3)+0 Target, right(AtTargetNo, 1) Letter, AtTargetNo
			from AvailableTarget
			left join IskData on IskDtTargetNo=AtTargetNo and IskDtTournament={$_SESSION['TourId']} and IskDtType='Q' and IskDtDistance=$Distance and IskDtEndNo=$End
			left join (select EnFirstName, QuTargetNo from Qualifications inner join Entries on EnId=QuId and EnTournament={$_SESSION['TourId']}) Ent on AtTargetNo=QuTargetNo
			where AtTournament={$_SESSION['TourId']} and AtTargetNo+0=".intval($Target)."
			order by AtTargetNo
			";
		$q=safe_r_sql($SQL);
		if($r=safe_fetch($q)) {
			$popId='';
			$SpanArrows='';
			if($r->IskDtEndNo) {
				$popId="data[][$r->IskDtMatchNo][".($r->IskDtEvent ? $r->IskDtEvent : ':::')."][$r->IskDtTeamInd][$r->IskDtType][".($r->IskDtTargetNo ? $r->IskDtTargetNo : ':::')."][$r->IskDtDistance][$r->IskDtEndNo]=$r->IskDtArrowstring";
				$arrows=(DecodeFromString($r->IskDtArrowstring));
				if(!is_array($arrows)) $arrows=array($arrows);
				$SpanArrows='<span class="Let-Z">'.implode('</span><span class="Let-Z">', $arrows).'</span>';
			}
			$Out.='<div class="PopUpEvent">'.get_text('PopupStatusSession', 'Api', '<b>'.$Target[0].'</b>')
				.' - '.get_text('PopupStatusDistance', 'Api', '<b>'.$Distance.'</b>')
				.' - '.get_text('PopupStatusEnd', 'Api', '<b>'.$End.'</b>')
				.'</div>';
			$Out.='<div class="PopUpSpot" value="'.$popId.'"><span class="Let-G">'.$r->Letter.'</span><span class="SpanName">'.$r->EnFirstName.'</span> '.$SpanArrows.'</div>';
			while($r=safe_fetch($q)) {
				$SpanArrows='';
				$popId='';
				if($r->IskDtEndNo) {
					$popId="data[][$r->IskDtMatchNo][".($r->IskDtEvent ? $r->IskDtEvent : ':::')."][$r->IskDtTeamInd][$r->IskDtType][".($r->IskDtTargetNo ? $r->IskDtTargetNo : ':::')."][$r->IskDtDistance][$r->IskDtEndNo]=$r->IskDtArrowstring";
					$arrows=(DecodeFromString($r->IskDtArrowstring));
					if(!is_array($arrows)) $arrows=array($arrows);
					$SpanArrows='<span class="Let-Z">'.implode('</span><span class="Let-Z">', $arrows).'</span>';
				}
				$Out.='<div class="PopUpSpot" value="'.$popId.'"><span class="Let-G">'.$r->Letter.'</span><span class="SpanName">'.$r->EnFirstName.'</span> '.$SpanArrows.'</div>';
			}
			$Error=0;
		}
		break;
	case 'E':
		break;
	case 'I':
		// fetches all the scorings regarding that event and matchno and teamevent and end
		$m=$MatchNo.','.($MatchNo%2 ? $MatchNo-1 : $MatchNo+1);
		$SQL="select IskData.*, EnFirstName, GrPhase, FsTarget+0 Target, substr(FsLetter, length(FsTarget)+1, 1) Letter

			from FinSchedule
			left join IskData on IskDtEvent=FsEvent and IskDtTournament=FsTournament and IskDtType='I' and IskDtMatchNo=FsMatchNo and IskDtEndNo=$End
			left join Finals on FsEvent=FinEvent and FinMatchNo=FsMatchNo and FinTournament={$_SESSION['TourId']}
			left join Entries on FinAthlete=EnId
			left join Grids on FsMatchNo=GrMatchNo
			where FsTournament={$_SESSION['TourId']} and FsEvent='$Event' and FsTeamEvent=$Team and FsMatchNo in ($m)
			order by FsMatchNo";
		$q=safe_r_sql($SQL);
		if($r=safe_fetch($q)) {
			$SpanArrows='';
			$popId='';
			if($r->IskDtEndNo) {
				$popId="data[][$r->IskDtMatchNo][".($r->IskDtEvent ? $r->IskDtEvent : ':::')."][$r->IskDtTeamInd][$r->IskDtType][".($r->IskDtTargetNo ? $r->IskDtTargetNo : ':::')."][$r->IskDtDistance][$r->IskDtEndNo]=$r->IskDtArrowstring";
				$arrows=(DecodeFromString($r->IskDtArrowstring));
				if(!is_array($arrows)) $arrows=array($arrows);
				$SpanArrows='<span class="Let-Z">'.implode('</span><span class="Let-Z">', $arrows).'</span>';
			}
			$Out.='<div class="PopUpEvent">'.get_text('PopupStatusEvent', 'Api', '<b>'.$Event.'</b>')
				.' - '.get_text('PopupStatusPhase', 'Api', '<b>'.get_text($r->GrPhase.'_Phase').'</b>')
				.' - '.get_text('PopupStatusEnd', 'Api', '<b>'.$End.'</b>')
				.'</div>';
			$Let='A';
			$Out.='<div class="PopUpSpot" value="'.$popId.'"><span class="Let-G">'.($Let++).'</span><span class="SpanName">'.$r->EnFirstName.'</span> '.$SpanArrows.'</div>';
			while($r=safe_fetch($q)) {
				$SpanArrows='';
				$popId='';
				if($r->IskDtEndNo) {
					$popId="data[][$r->IskDtMatchNo][".($r->IskDtEvent ? $r->IskDtEvent : ':::')."][$r->IskDtTeamInd][$r->IskDtType][".($r->IskDtTargetNo ? $r->IskDtTargetNo : ':::')."][$r->IskDtDistance][$r->IskDtEndNo]=$r->IskDtArrowstring";
					$arrows=(DecodeFromString($r->IskDtArrowstring));
					if(!is_array($arrows)) $arrows=array($arrows);
					$SpanArrows='<span class="Let-Z">'.implode('</span><span class="Let-Z">', $arrows).'</span>';
				}
				$Out.='<div class="PopUpSpot" value="'.$popId.'"><span class="Let-G">'.($Let++).'</span><span class="SpanName">'.$r->EnFirstName.'</span> '.$SpanArrows.'</div>';
			}
			$Error=0;
		}
		break;
	case 'T':
		$m=$MatchNo.','.($MatchNo%2 ? $MatchNo-1 : $MatchNo+1);
		$SQL="select IskData.*, concat(CoCode,'-',CoName) Country, GrPhase, FsTarget+0 Target, substr(FsLetter, length(FsTarget)+1, 1) Letter

			from FinSchedule
			left join IskData on IskDtEvent=FsEvent and IskDtTournament=FsTournament and IskDtType='T' and IskDtMatchNo=FsMatchNo and IskDtEndNo=$End
			left join TeamFinals on FsEvent=TfEvent and TfMatchNo=FsMatchNo and TfTournament={$_SESSION['TourId']}
			left join Countries on TfTeam=CoId and COTournament={$_SESSION['TourId']}
			left join Grids on FsMatchNo=GrMatchNo
			where FsTournament={$_SESSION['TourId']} and FsEvent='$Event' and FsTeamEvent=$Team and FsMatchNo in ($m)
			order by FsMatchNo";
		$q=safe_r_sql($SQL);
		if($r=safe_fetch($q)) {
			$SpanArrows='';
			$popId='';
			if($r->IskDtEndNo) {
				$popId="data[][$r->IskDtMatchNo][".($r->IskDtEvent ? $r->IskDtEvent : ':::')."][$r->IskDtTeamInd][$r->IskDtType][".($r->IskDtTargetNo ? $r->IskDtTargetNo : ':::')."][$r->IskDtDistance][$r->IskDtEndNo]=$r->IskDtArrowstring";
				$arrows=(DecodeFromString($r->IskDtArrowstring));
				if(!is_array($arrows)) $arrows=array($arrows);
				$SpanArrows='<span class="Let-Z">'.implode('</span><span class="Let-Z">', $arrows).'</span>';
			}
			$Out.='<div class="PopUpEvent">'.get_text('PopupStatusEvent', 'Api', '<b>'.$Event.'</b>')
				.' - '.get_text('PopupStatusPhase', 'Api', '<b>'.get_text($r->GrPhase.'_Phase').'</b>')
				.' - '.get_text('PopupStatusEnd', 'Api', '<b>'.$End.'</b>')
				.'</div>';
			$Let='A';
			$Out.='<div class="PopUpSpot" value="'.$popId.'"><span class="Let-G">'.($Let++).'</span><span class="SpanName">'.$r->Country.'</span> '.$SpanArrows.'</div>';
			while($r=safe_fetch($q)) {
				$SpanArrows='';
				$popId='';
				if($r->IskDtEndNo) {
					$popId="data[][$r->IskDtMatchNo][".($r->IskDtEvent ? $r->IskDtEvent : ':::')."][$r->IskDtTeamInd][$r->IskDtType][".($r->IskDtTargetNo ? $r->IskDtTargetNo : ':::')."][$r->IskDtDistance][$r->IskDtEndNo]=$r->IskDtArrowstring";
					$arrows=(DecodeFromString($r->IskDtArrowstring));
					if(!is_array($arrows)) $arrows=array($arrows);
					$SpanArrows='<span class="Let-Z">'.implode('</span><span class="Let-Z">', $arrows).'</span>';
				}
				$Out.='<div class="PopUpSpot" value="'.$popId.'"><span class="Let-G">'.($Let++).'</span><span class="SpanName">'.$r->Country.'</span> '.$SpanArrows.'</div>';
			}
			$Error=0;
		}
		break;
}


header('Content-Type: text/xml');
echo '<response error="0">';
echo '<html><![CDATA['.$Out.']]></html>';
echo '</response>';
