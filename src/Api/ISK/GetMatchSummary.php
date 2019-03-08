<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once('Common/Lib/Obj_RankFactory.php');
	require_once('Common/Lib/Fun_Phases.inc.php');

	list($Event,$EventTypeLetter,$MatchNo) = explode("|",(!empty($_GET['matchid']) ? $_GET['matchid'] : "0|0|0"));
	$EventType=($EventTypeLetter=='T' ? 1 : 0);
	$Phase=getPhase($MatchNo);

	$JsonResult=array();

	$options['tournament']=$CompId;
	$options['events']=array();
	$options['events'][] =  $Event . '@' . $Phase;

	$rank=null;
	if($EventType)
		$rank=Obj_RankFactory::create('GridTeam',$options);
	else
		$rank=Obj_RankFactory::create('GridInd',$options);

	$rank->read();
	$Data=$rank->getData();
	foreach($Data['sections'] as $kSec=>$vSec) {
		$json_array=array();
		$json_array['matchtype'] = ($vSec['meta']['matchMode'] ? "S":"C");
		$json_array['matchover'] = false;
		foreach($vSec['phases'] as $kPh=>$vPh) {
			$objParam=getEventArrowsParams($Event,$kPh,$EventType,$CompId);
			foreach($vPh['items'] as $kItem=>$vItem) {
				if($vItem['matchNo']!=$MatchNo && $vItem['oppMatchNo']!=$MatchNo)
					continue;

				$firstTmpEnd = 0;
				$SQL = "SELECT DISTINCT IskDtEndNo
					FROM IskData
					WHERE IskDtTournament={$CompId} AND IskDtMatchNo IN (".$vItem['matchNo'].",".$vItem['oppMatchNo'].") AND IskDtEvent='{$Event}' AND IskDtTeamInd={$EventType} AND IskDtType='{$EventTypeLetter}' AND IskDtTargetNo='' AND IskDtDistance=0
					ORDER BY IskDtEndNo ASC";
				$q=safe_r_SQL($SQL);
				if($r=safe_fetch($q)) {
					$firstTmpEnd=$r->IskDtEndNo;
				}

				$json_array['matchover'] = ($vItem['winner'] or $vItem['oppWinner']);
				$end = array();
				$oppEnd = array();
				if($vSec['meta']['matchMode']) {
					$tmp0 = explode("|",$vItem['setPoints']);
					$tmp1 = explode("|",$vItem['oppSetPoints']);
					for($i=0; $i<$objParam->ends; $i++){
						if($firstTmpEnd && $firstTmpEnd<=$i+1) {
							$aTmp = getMatchTotals($Event, $vItem['matchNo'], $EventType, $i+1, $objParam->arrows, $objParam->ends, $objParam->so);
							$bTmp = getMatchTotals($Event, $vItem['oppMatchNo'], $EventType, $i+1, $objParam->arrows, $objParam->ends, $objParam->so);
							if($aTmp['curendscore'] || $bTmp['curendscore']) {
								$end[]=array('endnum'=>$i+1, 'endscore'=>$aTmp['curendscore'], 'points'=>($aTmp['curendscore']==$bTmp['curendscore'] ? 1 : ($aTmp['curendscore']>$bTmp['curendscore'] ? 2 : 0)));
								$oppEnd[]=array('endnum'=>$i+1, 'endscore'=>$bTmp['curendscore'], 'points'=>($bTmp['curendscore']==$aTmp['curendscore'] ? 1 : ($bTmp['curendscore']>$aTmp['curendscore'] ? 2 : 0)));
							}
						} elseif($tmp0[$i] || $tmp1[$i]) {
							$end[]=array('endnum'=>$i+1, 'endscore'=>$tmp0[$i], 'points'=>($tmp0[$i]>$tmp1[$i] ? 2 : ($tmp0[$i]==$tmp1[$i] ? 1 : 0)));
							$oppEnd[]=array('endnum'=>$i+1, 'endscore'=>$tmp1[$i], 'points'=>($tmp1[$i]>$tmp0[$i] ? 2 : ($tmp0[$i]==$tmp1[$i] ? 1 : 0)));
						}
					}
					if($firstTmpEnd && $firstTmpEnd<=$objParam->ends+1) {
						$aTmp = getMatchTotals($Event, $vItem['matchNo'], $EventType, $objParam->ends+1, $objParam->arrows, $objParam->ends, $objParam->so);
						$bTmp = getMatchTotals($Event, $vItem['oppMatchNo'], $EventType, $objParam->ends+1, $objParam->arrows, $objParam->ends, $objParam->so);
						if($aTmp['curendscore'] || $bTmp['curendscore']) {
							$end[]=array('endnum'=>'S.O.', 'endscore'=>$aTmp['curendscore'], 'points'=>'-');
							$oppEnd[]=array('endnum'=>'S.O.', 'endscore'=>$bTmp['curendscore'], 'points'=>'-');
						}
					}elseif($vItem['tiebreakDecoded'] && $vItem['oppTiebreakDecoded']) {
						$end[]=array('endnum'=>'S.O.', 'endscore'=>$vItem['tiebreakDecoded'], 'points'=>($vItem['tie'] ? 1:0));
						$oppEnd[]=array('endnum'=>'S.O.', 'endscore'=>$vItem['oppTiebreakDecoded'], 'points'=>($vItem['oppTie'] ? 1:0));
					}
				} else {
					$running=array(0,0);
					for($i=0; $i<$objParam->ends; $i++){
						$tmp=array(ValutaArrowString(substr($vItem['arrowstring'],$i*$objParam->arrows, $objParam->arrows)), ValutaArrowString(substr($vItem['oppArrowstring'],$i*$objParam->arrows, $objParam->arrows)));
						$running[0]+=$tmp[0];
						$running[1]+=$tmp[1];
						if($firstTmpEnd && $firstTmpEnd<=$i+1) {
							$aTmp = getMatchTotals($Event, $vItem['matchNo'], $EventType, $i+1, $objParam->arrows, $objParam->ends, $objParam->so);
							$bTmp = getMatchTotals($Event, $vItem['oppMatchNo'], $EventType, $i+1, $objParam->arrows, $objParam->ends, $objParam->so);
							if($aTmp['curendscore'] || $bTmp['curendscore']) {
								$end[]=array('endnum'=>$i+1, 'endscore'=>$aTmp['curendscore'], 'points'=>$aTmp['curscoreatend']);
								$oppEnd[]=array('endnum'=>$i+1, 'endscore'=>$bTmp['curendscore'], 'points'=>$bTmp['curscoreatend']);
							}
						} elseif($tmp[0] || $tmp[1]) {
							$end[]=array('endnum'=>$i+1, 'endscore'=>$tmp[0], 'points'=>$running[0]);
							$oppEnd[]=array('endnum'=>$i+1, 'endscore'=>$tmp[1], 'points'=>$running[1]);
						}
					}
					if($firstTmpEnd && $firstTmpEnd<=$objParam->ends+1) {
						$aTmp = getMatchTotals($Event, $vItem['matchNo'], $EventType, $objParam->ends+1, $objParam->arrows, $objParam->ends, $objParam->so);
						$bTmp = getMatchTotals($Event, $vItem['oppMatchNo'], $EventType, $objParam->ends+1, $objParam->arrows, $objParam->ends, $objParam->so);
						if($aTmp['curendscore'] || $bTmp['curendscore']) {
							$end[]=array('endnum'=>'S.O.', 'endscore'=>$aTmp['curendscore'], 'points'=>'-');
							$oppEnd[]=array('endnum'=>'S.O.', 'endscore'=>$bTmp['curendscore'], 'points'=>'-');
						}
					}elseif($vItem['tiebreakDecoded'] && $vItem['oppTiebreakDecoded']) {
						$end[]=array('endnum'=>'S.O.', 'endscore'=>$vItem['tiebreakDecoded'], 'points'=>$running[0]);
						$oppEnd[]=array('endnum'=>'S.O.', 'endscore'=>$vItem['oppTiebreakDecoded'], 'points'=>$running[1]);
					}
				}

				$json_array['competitors'] = Array();
				$json_array['competitors'][] = Array('winner'=>(int)$vItem['winner'], 'matchid'=>$kSec . "|" . ($EventType ? "T" : "I") . "|" . $vItem['matchNo'], 'score'=>($vSec['meta']['matchMode'] ? $vItem['setScore'] : $vItem['score']), 'ends'=>$end);
				$json_array['competitors'][] = Array('winner'=>(int)$vItem['oppWinner'], 'matchid'=>$kSec . "|" . ($EventType ? "T" : "I") . "|" . $vItem['oppMatchNo'], 'score'=>($vSec['meta']['matchMode'] ? $vItem['oppSetScore'] : $vItem['oppScore']), 'ends'=>$oppEnd);

				if($firstTmpEnd!=0) {
					$json_array['competitors'][0]['score']="--";
					$json_array['competitors'][1]['score']="--";
				}

				$JsonResult[] = $json_array;
			}
		}
	}
	// Return the json structure with the callback function that is needed by the app
	SendResult($JsonResult);

