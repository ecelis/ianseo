<?php
require_once(dirname(__FILE__) . '/config.php');
require_once('Common/Lib/Obj_RankFactory.php');

$TourId = 0;
if(isset($_REQUEST['CompCode']) && preg_match("/^[a-z0-9_.-]+$/i", $_REQUEST['CompCode'])) {
	$TourId = getIdFromCode($_REQUEST['CompCode']);
}

$EvType = -1;
if(isset($_REQUEST['Type']) && preg_match("/^[01]$/", $_REQUEST['Type'])) {
	$EvType = $_REQUEST['Type'];
}

$EvCode = '....';
if(isset($_REQUEST['Event']) && preg_match("/^[a-z0-9_-]+$/i", $_REQUEST['Event'])) {
	$EvCode = $_REQUEST['Event'];
}

$MatchId = -1;
if(isset($_REQUEST['MatchId']) && preg_match("/^[0-9]+$/", $_REQUEST['MatchId'])) {
	$MatchId = $_REQUEST['MatchId'];
}

$json_array=array();

$options['tournament']=$TourId;
$options['events']=$EvCode;
$options['matchno']=$MatchId;

$rank=null;
if($EvType) {
	$rank=Obj_RankFactory::create('GridTeam',$options);
} else {
	$rank=Obj_RankFactory::create('GridInd',$options);
}
$rank->read();
$Data=$rank->getData();

//debug_svela($Data);

foreach($Data['sections'] as $kSec=>$vSec) {
	if(!empty($vSec['phases'])) {
		foreach($vSec['phases'] as $kPh=>$vPh) {
			$json_array = Array("Event"=>$EvCode, "Type"=>$EvType, "MatchId"=>$MatchId, "MatchLive"=>false, "MatchFinished"=>false, "MatchRunningEnd"=>"0", "MatchConfirmed"=>false, "ScoreCanChange"=>0);
			$objParam=getEventArrowsParams($kSec,$kPh,$EvType,$TourId);
			$json_array['Mode'] = Array("ScoringMode"=>($vSec["meta"]["matchMode"]==1 ? "S" : "C"), "Arrows"=>strval($objParam->arrows), "Ends"=>strval($objParam->ends), "ShootoffArrows"=>strval($objParam->so));
			foreach($vPh['items'] as $kItem=>$vItem) {
				$json_array['MatchFinished'] = ($vItem['winner'] or $vItem['oppWinner']);
				$json_array['MatchConfirmed'] = ($vItem['status']==1 &&  $vItem['oppStatus']==1);
				$json_array["MatchLive"] = ($vItem['liveFlag'] ? true : false);
				$tmpL = array();
				$tmpR = array();
				if($EvType==0) {
					$tmpL += array("Id"=>$vItem["bib"], "FamilyName"=>$vItem["familyName"], "GivenName"=>$vItem["givenName"], "NameOrder"=>$vItem["nameOrder"], "Gender"=>$vItem["gender"]);
					$tmpR += array("Id"=>$vItem["oppBib"], "FamilyName"=>$vItem["oppFamilyName"], "GivenName"=>$vItem["oppGivenName"], "NameOrder"=>$vItem["oppNameOrder"], "Gender"=>$vItem["oppGender"]);
				}
				$tmpL += array("TeamCode"=>$vItem["countryCode"], "TeamName"=>$vItem["countryName"]);
				$tmpR += array("TeamCode"=>$vItem["oppCountryCode"], "TeamName"=>$vItem["oppCountryName"]);
				$tmpL += array("EndConfirmed"=>($vItem['status']==3 || $vItem['status']==1), "Winner"=>($vItem["winner"]? true:false) , 'ToWin' => '', 'Score'=>($vSec['meta']['matchMode'] ? $vItem['setScore'] : $vItem['score']));
				$tmpR += array("EndConfirmed"=>($vItem['oppStatus']==3 || $vItem['oppStatus']==1), "Winner"=>($vItem["oppWinner"]? true:false), 'ToWin' => '', 'Score'=>($vSec['meta']['matchMode'] ? $vItem['oppSetScore'] : $vItem['oppScore']));

				$end = array();
				$oppEnd = array();
				$endScore = explode("|",$vItem['setPoints']);
				$oppEndScore = explode("|",$vItem['oppSetPoints']);
				$running=array(0,0);
				$runningEnd = 0;
				$vItem['arrowstring']=str_pad($vItem['arrowstring'], $objParam->arrows*$objParam->ends, ' ', STR_PAD_RIGHT);
				$vItem['oppArrowstring']=str_pad($vItem['oppArrowstring'], $objParam->arrows*$objParam->ends, ' ', STR_PAD_RIGHT);
				if($vSec['meta']['matchMode']) {
					$setAssPoint = explode("|",$vItem['setPointsByEnd']);
					$oppSetAssPoint = explode("|",$vItem['oppSetPointsByEnd']);
					for($i=0; $i<$objParam->ends; $i++){
						$running[0] += (!empty($setAssPoint[$i]) ? $setAssPoint[$i]:0);
						$running[1] += (!empty($oppSetAssPoint[$i]) ? $oppSetAssPoint[$i]:0);
						//if((!empty($endScore[$i]) && $endScore[$i]) || (!empty($oppEndScore[$i]) && $oppEndScore[$i])) {
						{
							$arrValue = DecodeFromString(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows),false);
							$regExp = '';
							$pointStar = (!empty($endScore[$i])?$endScore[$i]:0);
							$pointRaiseStar = $pointStar;
							if(!ctype_upper(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows))) {
								$pointRaiseStar += RaiseStars(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows), $regExp, $EvCode, $EvType, $TourId);
							}
							if(!is_array($arrValue)) {
								$arrValue = array($arrValue);
							} elseif(count($arrValue)==0) {
								$arrValue = array_fill(0,$objParam->arrows,'');
							}
							$arrValue = array_map('trim',$arrValue);
							$oppArrValue = DecodeFromString(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows),false);
							if(!is_array($oppArrValue)) {
								$oppArrValue = array($oppArrValue);
							} elseif(count($oppArrValue)==0) {
								$oppArrValue = array_fill(0,$objParam->arrows,'');
							}
							$oppArrValue = array_map('trim',$oppArrValue);
							$oppPointStar = (!empty($oppEndScore[$i])?$oppEndScore[$i]:0);
							$oppPointRaiseStar = $oppPointStar;
							if(!ctype_upper(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows))) {
								$oppPointRaiseStar += RaiseStars(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows), $regExp, $EvCode, $EvType, $TourId);
							}
							$end[]=array('EndNum'=>strval($i+1), 'EndScore'=>(!empty($endScore[$i])?$endScore[$i]:0), 'PointAssigned'=>strval((!empty($setAssPoint[$i])?$setAssPoint[$i]:0)), 'RunningScore'=>strval($running[0]), 'ShootFirst'=>($vItem["shootFirst"] & pow(2,$i))!=0, 'Arrows'=>$arrValue);
							$oppEnd[]=array('EndNum'=>strval($i+1), 'EndScore'=>(!empty($oppEndScore[$i])?$oppEndScore[$i]:0), 'PointAssigned'=>strval((!empty($oppSetAssPoint[$i])?$oppSetAssPoint[$i]:0)), 'RunningScore'=>strval($running[1]), 'ShootFirst'=>($vItem["oppShootFirst"] & pow(2,$i))!=0, 'Arrows'=>$oppArrValue);
							if(!empty($endScore[$i]) || !empty($oppEndScore[$i])) {
								if(strpos(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows),' ') || strpos(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows),' ')) {
									$runningEnd = $i;
								} else {
									$runningEnd = $i+1;
								}
							}
							if(($pointStar != $pointRaiseStar) || ($oppPointStar != $oppPointRaiseStar)) {
								if(($pointStar == $oppPointStar && ($pointStar < $oppPointRaiseStar or $pointRaiseStar > $oppPointStar)) or ($pointStar > $oppPointStar && $pointStar <= $oppPointRaiseStar) || ($pointStar < $oppPointStar && $pointRaiseStar >= $oppPointStar)) {
// 								debug_svela(array($pointStar , $pointRaiseStar,$oppPointStar ,$oppPointRaiseStar));
									$tmp=0;
									if($pointStar!=$pointRaiseStar) $tmp+=1;
									if($oppPointStar!=$oppPointRaiseStar) $tmp+=2;
									$json_array["ScoreCanChange"] = $tmp;
								}
							}
						}
					}
					//if($vItem['tiebreakDecoded'] || $vItem['oppTiebreakDecoded']) {
					{
						$arrValue = DecodeFromString(substr($vItem['tiebreak'],0,$objParam->so),false);
						if(!is_array($arrValue)) {
							$arrValue = array($arrValue);
						} elseif(count($arrValue)==0) {
							$arrValue = array_fill(0,$objParam->so,'');
						}
						$arrValue = array_map('trim',$arrValue);
						$oppArrValue = DecodeFromString(substr($vItem['oppTiebreak'],0,$objParam->so),false);
						if(!is_array($oppArrValue)) {
							$oppArrValue = array($oppArrValue);
						} elseif(count($oppArrValue)==0) {
							$oppArrValue = array_fill(0,$objParam->so,'');
						}
						$oppArrValue = array_map('trim',$oppArrValue);
						$end[]=array('EndNum'=>'SO', 'EndScore'=>$vItem['tiebreakDecoded'], 'PointAssigned'=>strval($vItem['tie'] ? 1 : 0), 'RunningScore'=>$vItem['setScore'], 'ShootFirst'=>($vItem["shootFirst"] & pow(2,$objParam->arrows))!=0, 'Arrows'=>$arrValue);
						$oppEnd[]=array('EndNum'=>'SO', 'EndScore'=>$vItem['oppTiebreakDecoded'], 'PointAssigned'=>strval($vItem['oppTie'] ? 1 : 0), 'RunningScore'=>$vItem['oppSetScore'], 'ShootFirst'=>($vItem["oppShootFirst"] & pow(2,$objParam->arrows))!=0, 'Arrows'=>$oppArrValue);
					}
				} else {
					for($i=0; $i<$objParam->ends; $i++){
						$running[0] += (!empty($endScore[$i]) ? $endScore[$i]:0);
						$running[1] += (!empty($oppEndScore[$i]) ? $oppEndScore[$i]:0);
						//if((!empty($endScore[$i]) && $endScore[$i]) || (!empty($oppEndScore[$i]) && $oppEndScore[$i])) {
						{
							$arrValue = DecodeFromString(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows),false);
							$regExp = '';
							$pointStar = $running[0];
							$pointRaiseStar = $pointStar;
							if(!ctype_upper(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows))) {
								$pointRaiseStar += RaiseStars(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows), $regExp, $EvCode, $EvType, $TourId);
							}
							if(!is_array($arrValue)) {
								$arrValue = array($arrValue);
							} elseif(count($arrValue)==0) {
								$arrValue = array_fill(0,$objParam->arrows,'');
							}
							$arrValue = array_map('trim',$arrValue);
							$oppArrValue = DecodeFromString(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows),false);
							$oppPointStar =  $running[1];
							$oppPointRaiseStar = $oppPointStar;
// 							debug_svela($vItem['oppArrowstring']);
							if(!ctype_upper(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows))) {
								$oppPointRaiseStar += RaiseStars(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows), $regExp, $EvCode, $EvType, $TourId);
							}
							if(!is_array($oppArrValue)) {
								$oppArrValue = array($oppArrValue);
							} elseif(count($oppArrValue)==0) {
								$arrValue = array_fill(0,$objParam->arrows,'');
							}
							$oppArrValue = array_map('trim',$oppArrValue);
							$end[]=array('EndNum'=>strval($i+1), 'EndScore'=>strval(!empty($endScore[$i])?$endScore[$i]:0), 'RunningScore'=>strval($running[0]), 'ShootFirst'=>($vItem["shootFirst"] & pow(2,$i))!=0, 'Arrows'=>$arrValue);
							$oppEnd[]=array('EndNum'=>strval($i+1), 'EndScore'=>strval(!empty($oppEndScore[$i])?$oppEndScore[$i]:0), 'RunningScore'=>strval($running[1]), 'ShootFirst'=>($vItem["oppShootFirst"] & pow(2,$i))!=0, 'Arrows'=>$oppArrValue);

							if(!empty($endScore[$i]) || !empty($oppEndScore[$i])) {
								if(strpos(substr($vItem['arrowstring'],$i*$objParam->arrows,$objParam->arrows),' ') || strpos(substr($vItem['oppArrowstring'],$i*$objParam->arrows,$objParam->arrows),' ')) {
									$runningEnd = $i;
								} else {
									$runningEnd = $i+1;
								}
							}

							if(($pointStar != $pointRaiseStar) || ($oppPointStar != $oppPointRaiseStar)) { 
								if(($pointStar > $oppPointStar && $pointStar <= $oppPointRaiseStar) || ($pointStar < $oppPointStar && $pointRaiseStar >= $oppPointStar) || ($pointStar == $oppPointStar &&  ($pointStar != $oppPointRaiseStar || $pointRaiseStar != $oppPointStar))) {
									$tmp=0;
									if($pointStar!=$pointRaiseStar) $tmp+=1;
									if($oppPointStar!=$oppPointRaiseStar) $tmp+=2;
									$json_array["ScoreCanChange"] = $tmp;
								}
							}
						}
					}
					//if($vItem['tiebreakDecoded'] || $vItem['oppTiebreakDecoded']) {
					{
						$arrValue = DecodeFromString(substr($vItem['tiebreak'],0,$objParam->so),false);
						if(!is_array($arrValue)) {
							$arrValue = array($arrValue);
						} elseif(count($arrValue)==0) {
							$arrValue = array_fill(0,$objParam->so,'');
						}
						$arrValue = array_map('trim',$arrValue);
						$oppArrValue = DecodeFromString(substr($vItem['oppTiebreak'],0,$objParam->so),false);
						if(!is_array($oppArrValue)) {
							$oppArrValue = array($oppArrValue);
						} elseif(count($oppArrValue)==0) {
							$oppArrValue = array_fill(0,$objParam->so,'');
						}
						$oppArrValue = array_map('trim',$oppArrValue);
						$end[]=array('EndNum'=>'SO', 'EndScore'=>strval($vItem['tiebreakDecoded']), 'RunningScore'=>strval($running[0]), 'ShootFirst'=>($vItem["shootFirst"] & pow(2,$objParam->arrows))!=0, 'Arrows'=>$arrValue);
						$oppEnd[]=array('EndNum'=>'SO', 'EndScore'=>strval($vItem['oppTiebreakDecoded']), 'RunningScore'=>strval($running[1]), 'ShootFirst'=>($vItem["oppShootFirst"] & pow(2,$objParam->arrows))!=0, 'Arrows'=>$oppArrValue);
					}
				}

				$IsSO=false;
				if($runningEnd < $objParam->ends) {
					$endOrg0=strlen(rtrim($vItem['arrowstring']));
					$endOrg1=strlen(rtrim($vItem['oppArrowstring']));
					$NumArrows=$vSec['meta']['finEnds']*$vSec['meta']['finArrows'];
				} else {
					$IsSO=true;
					$endOrg0=strlen(rtrim($vItem['tiebreak']));
					$endOrg1=strlen(rtrim($vItem['oppTiebreak']));
					$NumArrows=$vSec['meta']['finSO'];
				}
				// X to win appears if
				// * cumulative
				// - 1 arrow left to shoot
				// * set system
				// - 1 point to win the match
// 						debug_svela($runningEnd );
				if(abs( $dif = $endOrg0-$endOrg1 )==1
						and ($endOrg0==$NumArrows
								or $endOrg1==$NumArrows
								or ($vSec['meta']['matchMode']
										and (strlen(rtrim(substr($vItem['arrowstring'], $runningEnd*$vSec['meta']['finArrows'], $vSec['meta']['finArrows'])))==$vSec['meta']['finArrows'] or strlen(rtrim(substr($vItem['oppArrowstring'], $runningEnd*$vSec['meta']['finArrows'], $vSec['meta']['finArrows'])))==$vSec['meta']['finArrows'])
										and ($vItem['oppSetScore']>=$vSec['meta']['finEnds']-1
												or $vItem['setScore']>=$vSec['meta']['finEnds']-1)))) {
					if(!$IsSO and $vSec['meta']['matchMode']) {
						// Set Score
						if(($dif==1 and $vItem['oppSetScore']>=$vSec['meta']['finEnds']-1) or ($dif==-1 and $vItem['setScore']>=$vSec['meta']['finEnds']-1)) {
							// can win the match
							$ToWin=($endScore[$runningEnd]-$oppEndScore[$runningEnd])*$dif;
							if(($dif==-1 and $vItem['setScore']==$vSec['meta']['finEnds']-1) or ($dif==1 and $vItem['oppSetScore']==$vSec['meta']['finEnds']-1)) {
								$ToWin++;
							}

							// check if any stars
							if(!ctype_upper($vItem['arrowstring']) or !ctype_upper($vItem['oppArrowstring'])) {
								if($dif==1) {
									$ToWin+=RaiseStars($vItem['arrowstring'], $regExp);
								} elseif($dif==-1) {
									$ToWin+=RaiseStars($vItem['oppArrowstring'], $regExp);
								}
							}
							if($ToWin<=$vSec['meta']['maxPoint'] and $ToWin>0) {
								if($ToWin>=$vSec['meta']['minPoint']) {
									if($dif==1) {
										$tmpR['ToWin']=$ToWin.' to win';
									} else {
										$tmpL['ToWin']=$ToWin.' to win';
									}
								} else {
									if($dif==1) {
										$tmpR['ToWin']='Hit to win';
									} else {
										$tmpL['ToWin']='Hit to win';
									}
								}
							}
						}
					} else {
						// Cumulative
						$ToWin=($vItem['score']-$vItem['oppScore'])*$dif +1;
						if($IsSO) {
// 							debug_svela($vItem);
							if($vSec['meta']['finSO']>1) {
								$ToWin=(intval(ValutaArrowString($vItem['tiebreak']))-intval(ValutaArrowString($vItem['oppTiebreak'])))*$dif +1;
								rsort($arrValue);
								rsort($oppArrValue);
								if($dif==1 and $arrValue[0]<$oppArrValue[0]) {
									$ToWin--;
								} elseif($dif==-1 and $arrValue[0]>$oppArrValue[0]) {
									$ToWin--;
								}
							} else {
								$ToWin=(intval(ValutaArrowString($vItem['tiebreak']))-intval(ValutaArrowString($vItem['oppTiebreak'])))*$dif +1;
							}
						} else {
							$scoreStar = $vItem['score'] + RaiseStars($vItem['arrowstring'], $regExp, $EvCode, $EvType, $TourId);
							$oppScoreStar = $vItem['oppScore'] + RaiseStars($vItem['oppArrowstring'], $regExp, $EvCode, $EvType, $TourId);
							$scoreToWin=($vItem['score']-$oppScoreStar)*$dif +1;
							$oppScoreToWin=($scoreStar-$vItem['oppScore'])*$dif +1;
							$ToWin=max($scoreToWin,$oppScoreToWin);
						}
// 						debug_svela($dif);
						if($ToWin<=$vSec['meta']['maxPoint'] and $ToWin>0) {
							if($ToWin>=$vSec['meta']['minPoint']) {
								if($dif==1) {
									$tmpR['ToWin']=$ToWin.' to win';
								} else {
									$tmpL['ToWin']=$ToWin.' to win';
								}
							} else {
								if($dif==1) {
									$tmpR['ToWin']='Hit to win';
								} else {
									$tmpL['ToWin']='Hit to win';
								}
							}
						}
					}
				}


				if(!$json_array['MatchFinished'])
					$json_array['MatchRunningEnd'] = ($runningEnd < $objParam->ends)  ? strval($runningEnd+1) : 'SO';
				$tmpL["Ends"] = $end;
				$tmpR["Ends"] = $oppEnd;

				$json_array['LeftOpponent'] = $tmpL;
				$json_array['RightOpponent'] = $tmpR;
			}
		}
	}
}

// Return the json structure with the callback function that is needed by the app
SendResult($json_array);
