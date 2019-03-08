<?php


function DoImportData() {
	require_once(dirname(dirname(__FILE__)).'/config.php');
	require_once('Qualification/Fun_Qualification.local.inc.php');
	require_once('Final/Fun_MatchTotal.inc.php');

	$Error=1;

	if(!CheckTourSession()) {
		header('Content-Type: text/xml');
		die('<response error="'.$Error.'"/>');
	}

	$CompId = $_SESSION["TourId"];
	$Sequence=$_REQUEST['ses'];
	$Dist=intval($_REQUEST['dist']);
	$End=intval($_REQUEST['end']);
	$Filtre='';
	if(!empty($_REQUEST['target'])) {
		$Filtre=' AND substr(IskDtTargetNo, -4, 3)+0 = ' . intval($_REQUEST['target']);
	}
	if(isset($_REQUEST['event']) or isset($_REQUEST['matchno'])) {
		$Filtre=" AND IskDtEvent = '{$_REQUEST['event']}' and IskDtMatchNo in ({$_REQUEST['matchno']}) and IskDtTeamInd=".($Sequence[0]=='T' ? 1 : 0);
	}
	switch($Sequence[0]) {
		case 'Q':
			$qSes=substr($Sequence,2);
			$SQL="SELECT QuId, QuTargetNo, QuD{$Dist}Arrowstring as Arrowstring, IskDtArrowstring, IskDtEndNo, DIDistance, DIEnds, DIArrows, ToGoldsChars, ToXNineChars from Qualifications
				INNER JOIN Entries ON QuId=EnId
				INNER JOIN Tournament ON ToId=EnTournament
				INNER JOIN DistanceInformation ON DITournament=EnTournament AND DISession=QuSession AND DIDistance={$Dist} AND DIType='Q'
				INNER JOIN IskData ON iskDtTournament=EnTournament AND IskDtMatchNo=0 AND IskDtEvent='' AND IskDtTeamInd=0 AND IskDtType='Q' AND IskDtTargetNo=QuTargetNo AND IskDtDistance={$Dist} AND IskDtEndNo={$End}
					$Filtre
				WHERE EnTournament={$CompId} and QuSession={$qSes}";
			$updated=array();
			$q=safe_r_sql($SQL);
			while($r=safe_fetch($q)) {
				$arrowString = str_pad($r->Arrowstring,$r->DIArrows*$r->DIEnds);
				for($i=0; $i<$r->DIArrows; $i++){
					if($r->IskDtArrowstring[$i]!=' '){
						$arrowString[($r->IskDtEndNo-1)*$r->DIArrows+$i]=$r->IskDtArrowstring[$i];
					}
				}
				$Score=0;
				$Gold=0;
				$XNine=0;
				list($Score,$Gold,$XNine)=ValutaArrowStringGX($arrowString,$r->ToGoldsChars,$r->ToXNineChars);

				$Update = "UPDATE Qualifications SET
					QuD{$Dist}Score={$Score}, QuD{$Dist}Gold={$Gold}, QuD{$Dist}Xnine={$XNine}, QuD{$Dist}ArrowString='{$arrowString}', QuD{$Dist}Hits=LENGTH(RTRIM(QuD{$Dist}ArrowString)),
					QuScore=QuD1Score+QuD2Score+QuD3Score+QuD4Score+QuD5Score+QuD6Score+QuD7Score+QuD8Score,
					QuGold=QuD1Gold+QuD2Gold+QuD3Gold+QuD4Gold+QuD5Gold+QuD6Gold+QuD7Gold+QuD8Gold,
					QuXnine=QuD1Xnine+QuD2Xnine+QuD3Xnine+QuD4Xnine+QuD5Xnine+QuD6Xnine+QuD7Xnine+QuD8Xnine,
					QuHits=QuD1Hits+QuD2Hits+QuD3Hits+QuD4Hits+QuD5Hits+QuD6Hits+QuD7Hits+QuD8Hits,
					QuTimestamp=" . StrSafe_DB(date('Y-m-d H:i:s')) . "
					WHERE QuId={$r->QuId}";
				safe_w_SQL($Update);
				if(safe_w_affected_rows()) {
					$updated[] = $r->QuId;
				}
				$Update = "DELETE FROM IskData
					WHERE IskDtTournament={$CompId} AND IskDtMatchNo=0 AND IskDtEvent='' AND IskDtTeamInd=0 AND IskDtType='Q'
					AND IskDtTargetNo='{$r->QuTargetNo}' AND IskDtDistance={$Dist} AND IskDtEndNo={$End} AND IskDtArrowstring='{$r->IskDtArrowstring}'";
				safe_w_SQL($Update);
			}
			if(count($updated)) {
				$SQL = "SELECT DISTINCT EnClass, EnDivision, MAX(EnIndClEvent) as IndCl, Max(EnTeamClEvent) as TeamCl, MAX(EnIndFEvent) as IndFE, MAX(EnTeamFEvent+EnTeamMixEvent) as TeamFE
					FROM Entries
					WHERE EnTournament={$CompId} AND EnId IN (" . implode(",",$updated) . ")
					group by EnClass, EnDivision";
				$q=safe_r_sql($SQL);
				while($r=safe_fetch($q)) {
					if($r->IndCl!=0) {
						Obj_RankFactory::create('DivClass',array('tournament'=>$CompId,'events'=>$r->EnDivision.$r->EnClass,'dist'=>$Dist))->calculate();
						Obj_RankFactory::create('DivClass',array('tournament'=>$CompId,'events'=>$r->EnDivision.$r->EnClass,'dist'=>0))->calculate();
					}
					if($r->TeamCl!=0) {
						MakeTeams(NULL, $r->EnDivision.$r->EnClass);
					}
					$SQL = "SELECT DISTINCT EvCode, EvTeamEvent
						FROM Events
						INNER JOIN EventClass ON EvCode=EcCode AND if(EvTeamEvent=0, EvTeamEvent=EcTeamEvent, EcTeamEvent>0) AND EvTournament=EcTournament
						WHERE EvTournament={$CompId} AND EcClass='{$r->EnClass}' AND EcDivision='{$r->EnDivision}'
						ORDER BY EvTeamEvent, EvCode";
					$q2=safe_r_sql($SQL);
					while($r2=safe_fetch($q2)) {
						if($r2->EvTeamEvent==0) {
							if($r->IndFE!=0) {
								Obj_RankFactory::create('Abs',array('tournament'=>$CompId,'events'=>$r2->EvCode,'dist'=>$Dist))->calculate();
								Obj_RankFactory::create('Abs',array('tournament'=>$CompId,'events'=>$r2->EvCode,'dist'=>0))->calculate();
								ResetShootoff($r2->EvCode,0,0);
							}
						} else {
							if($r->TeamFE!=0) {
								MakeTeamsAbs(NULL, $r->EnDivision, $r->EnClass);
							}
						}
					}
				}
			}
			$Error=0;
			break;
		case 'I':
		case 'T':
			$fSes=substr($Sequence,1);
			$IndTeam = ($Sequence[0]=='I' ? 0:1);
			$tblHead = ($IndTeam==0 ? 'Fin' : 'Tf');

			$SQL="SELECT FSEvent, FSMatchNo, FSTeamEvent, {$tblHead}Arrowstring as Arrowstring, {$tblHead}Tiebreak as TieBreak, IskDtArrowstring, IskDtEndNo, GrPhase
				FROM FinSchedule
				INNER JOIN Grids ON FSMatchNo=GrMatchNo
				INNER JOIN IskData ON IskDtTournament=FsTournament AND IskDtMatchNo=FsMatchNo AND IskDtEvent=FSEvent AND IskDtTeamInd=FsTeamEvent AND IskDtType='" . ($IndTeam==0 ? 'I':'T') . "' AND IskDtTargetNo='' AND IskDtDistance=0 AND IskDtEndNo={$End}
					$Filtre
				INNER JOIN " . ($IndTeam==0 ? 'Finals' : 'TeamFinals') . " ON FsTournament={$tblHead}Tournament AND FsMatchNo={$tblHead}MatchNo AND FSEvent={$tblHead}Event
				WHERE FSTournament={$CompId} AND FsTeamEvent={$IndTeam}
				AND CONCAT(FSScheduledDate,FSScheduledTime)=" . StrSafe_DB($fSes);
			$q=safe_r_SQL($SQL);
			while($r=safe_fetch($q)){
				$obj=getEventArrowsParams($r->FSEvent,$r->GrPhase,$r->FSTeamEvent,$CompId);
				$isSO = ($End > $obj->ends);

				$arrowString = ($isSO ? str_pad($r->TieBreak,$obj->so) : str_pad($r->Arrowstring,$obj->arrows));
				for($i=0; $i<($isSO ? $obj->so : $obj->arrows); $i++){
					if($r->IskDtArrowstring[$i]!=' '){
						$arrowString[($isSO ? 0 : ($r->IskDtEndNo-1)*$obj->arrows)+$i]=$r->IskDtArrowstring[$i];
					}
				}
				$startPos = (($isSO ? ($obj->arrows*$obj->ends) : 0) +1);
				UpdateArrowString($r->FSMatchNo, $r->FSEvent, $IndTeam, $arrowString, $startPos, ($startPos+($isSO ? $obj->so : $obj->arrows*$obj->ends)-1), $CompId);

				$Update = "DELETE FROM IskData
					WHERE IskDtTournament={$CompId} AND IskDtMatchNo={$r->FSMatchNo} AND IskDtEvent='{$r->FSEvent}' AND IskDtTeamInd={$IndTeam} AND IskDtType='" . ($IndTeam==0 ? 'I':'T') . "'
					AND IskDtTargetNo='' AND IskDtDistance=0 AND IskDtEndNo={$r->IskDtEndNo} AND IskDtArrowstring='{$r->IskDtArrowstring}'";
				safe_w_SQL($Update);
			}
			$Error=0;
			break;
	}
	return $Error;
}