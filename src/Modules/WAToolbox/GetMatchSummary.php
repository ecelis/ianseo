<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once('Common/Lib/Obj_RankFactory.php');
	require_once('Common/Lib/Fun_Phases.inc.php');

	$Event = (!empty($_GET['event']) ? $_GET['event'] : '');
	$EventType = (!empty($_GET['type']) && $_GET['type']=='T' ? 1 : 0);
	$MatchNo = (isset($_GET['matchid']) && is_numeric($_GET['matchid']) ? $_GET['matchid'] : -1);
	$Phase = 0;

	if(empty($Event) || $MatchNo<0)
		SendResult(array('error' => get_text('WAToolbox-MissingParams', 'Api')));

	//Get the phase relatedto the matchno
	$SQL="select GrPhase from Grids where GrMatchNo=$MatchNo";
	$Rs=safe_r_sql($SQL);
	if($r=safe_fetch($Rs))
		$Phase = $r->GrPhase;

	$json_array=array();

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
		$json_array['matchtype'] = ($vSec['meta']['matchMode']==1 ? "S" : "C");
		$json_array['matchlive'] = false;
		$json_array['matchfinished'] = false;
		$json_array['matchrunningend'] = "0";
		foreach($vSec['phases'] as $kPh=>$vPh) {
			$objParam=getEventArrowsParams($Event,$kPh,$EventType,$CompId);
			foreach($vPh['items'] as $kItem=>$vItem) {
				if($vItem['matchNo']!=$MatchNo && $vItem['oppMatchNo']!=$MatchNo)
					continue;

				$json_array['matchlive'] = ($vItem['liveFlag'] ? true : false);
				$json_array['matchfinished'] = ($vItem['winner'] or $vItem['oppWinner']);
				$end = array();
				$oppEnd = array();
				$runningEnd = 0;
				if($vSec['meta']['matchMode']) {
					$tmp0 = explode("|",$vItem['setPoints']);
					$tmp1 = explode("|",$vItem['oppSetPoints']);
					$score0 = 0;
					$score1 = 0;
					for($i=0; $i<$objParam->ends; $i++){
						if($tmp0[$i] || $tmp1[$i]) {
							$score0 += ($tmp0[$i]>$tmp1[$i] ? 2 : ($tmp0[$i]==$tmp1[$i] ? 1 : 0));
							$score1 += ($tmp1[$i]>$tmp0[$i] ? 2 : ($tmp0[$i]==$tmp1[$i] ? 1 : 0));
							$arrValue = DecodeFromString(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows),false);
							if(!is_array($arrValue))
								$arrValue = array($arrValue);
							$oppArrValue = DecodeFromString(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows),false);
							if(!is_array($oppArrValue))
								$oppArrValue = array($oppArrValue);
							$end[]=array('endnum'=>strval($i+1), 'endscore'=>$tmp0[$i], 'pointassigned'=>strval($tmp0[$i]>$tmp1[$i] ? 2 : ($tmp0[$i]==$tmp1[$i] ? 1 : 0)), 'runningscore'=>strval($score0), 'arrowvalues'=>$arrValue);
							$oppEnd[]=array('endnum'=>strval($i+1), 'endscore'=>$tmp1[$i], 'pointassigned'=>strval($tmp1[$i]>$tmp0[$i] ? 2 : ($tmp0[$i]==$tmp1[$i] ? 1 : 0)), 'runningscore'=>strval($score1), 'arrowvalues'=>$oppArrValue);
							if(strpos(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows),' ') || strpos(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows),' '))
								$runningEnd = $i;
							else
								$runningEnd = $i+1;
						}
					}
					if($vItem['tiebreakDecoded'] || $vItem['oppTiebreakDecoded']) {
						$arrValue = DecodeFromString(substr($vItem['tiebreak'],0,$objParam->so),false);
						if(!is_array($arrValue))
							$arrValue = array($arrValue);
						$oppArrValue = DecodeFromString(substr($vItem['oppTiebreak'],0,$objParam->so),false);
						if(!is_array($oppArrValue))
							$oppArrValue = array($oppArrValue);
						$end[]=array('endnum'=>'S.O.', 'endscore'=>$vItem['tiebreakDecoded'], 'pointassigned'=>strval($vItem['tie'] ? 1:0), 'runningscore'=>$vItem['setScore'] , 'arrowvalues'=>$arrValue);
						$oppEnd[]=array('endnum'=>'S.O.', 'endscore'=>$vItem['oppTiebreakDecoded'], 'pointassigned'=>strval($vItem['oppTie'] ? 1:0), 'runningscore'=>$vItem['oppSetScore'] , 'arrowvalues'=>$oppArrValue);
					}
				} else {
					$running=array(0,0);
					for($i=0; $i<$objParam->ends; $i++){
						$tmp=array(ValutaArrowString(substr($vItem['arrowstring'],$i*$objParam->arrows, $objParam->arrows)), ValutaArrowString(substr($vItem['oppArrowstring'],$i*$objParam->arrows, $objParam->arrows)));
						$running[0]+=$tmp[0];
						$running[1]+=$tmp[1];
						if($tmp[0] || $tmp[1]) {
							$arrValue = DecodeFromString(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows),false);
							if(!is_array($arrValue))
								$arrValue = array($arrValue);
							$oppArrValue = DecodeFromString(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows),false);
							if(!is_array($oppArrValue))
								$oppArrValue = array($oppArrValue);
							$end[]=array('endnum'=>strval($i+1), 'endscore'=>strval($tmp[0]), 'pointassigned'=>strval($tmp[0]), 'runningscore'=>strval($running[0]), 'arrowvalues'=>$arrValue);
							$oppEnd[]=array('endnum'=>strval($i+1), 'endscore'=>strval($tmp[1]), 'pointassigned'=>strval($tmp[1]), 'runningscore'=>strval($running[1]), 'arrowvalues'=>$oppArrValue);
							if(strpos(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows),' ') || strpos(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows),' '))
								$runningEnd = $i;
							else
								$runningEnd = $i+1;
						}
					}
					if($vItem['tiebreakDecoded'] || $vItem['oppTiebreakDecoded']) {
						$arrValue = DecodeFromString(substr($vItem['tiebreak'],0,$objParam->so),false);
						if(!is_array($arrValue))
							$arrValue = array($arrValue);
						$oppArrValue = DecodeFromString(substr($vItem['oppTiebreak'],0,$objParam->so),false);
						if(!is_array($oppArrValue))
							$oppArrValue = array($oppArrValue);
						$end[]=array('endnum'=>'S.O.', 'endscore'=>strval($vItem['tiebreakDecoded']), 'pointassigned'=>"0", 'runningscore'=>strval($running[0]) , 'arrowvalues'=>$arrValue);
						$oppEnd[]=array('endnum'=>'S.O.', 'endscore'=>strval($vItem['oppTiebreakDecoded']), 'pointassigned'=>"0", 'runningscore'=>strval($running[1]) , 'arrowvalues'=>$oppArrValue);
					}
				}
				if(!$json_array['matchfinished'])
					$json_array['matchrunningend'] = ($runningEnd < $objParam->ends)  ? strval($runningEnd+1) : 'SO';
				$json_array['competitors'] = Array();
				$json_array['competitors'][] = Array('winner'=>strval($vItem['winner']), 'event'=>$kSec, 'type'=>($EventType ? "T" : "I"), 'matchid'=>$vItem['matchNo'], 'score'=>($vSec['meta']['matchMode'] ? $vItem['setScore'] : $vItem['score']), 'ends'=>$end);
				$json_array['competitors'][] = Array('winner'=>strval($vItem['oppWinner']), 'event'=>$kSec, 'type'=>($EventType ? "T" : "I"), 'matchid'=>$vItem['oppMatchNo'], 'score'=>($vSec['meta']['matchMode'] ? $vItem['oppSetScore'] : $vItem['oppScore']), 'ends'=>$oppEnd);
			}
		}
	}
	SaveLog("GetMatchSummary", $_SERVER["QUERY_STRING"]);
	// Return the json structure with the callback function that is needed by the app
	SendResult($json_array);

