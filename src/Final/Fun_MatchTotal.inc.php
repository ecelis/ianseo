<?php
require_once('Common/Lib/ArrTargets.inc.php');
require_once('Common/Fun_Phases.inc.php');
require_once('Fun_ChangePhase.inc.php');
require_once('Common/Lib/Fun_Modules.php');

function UpdateArrowPosition($MatchNo, $EvCode, $TeamEvent, $ArrowPosX, $ArrowPosY, $ArrowDiam=0, $ArrowPos='')
{
	$retValue = null;
	$Select = '';

	$TablePrefix = "Fin";
	$Select
		= "SELECT "
		. "FinEvent as EvCode, FinMatchNo as MatchNo, FinArrowString as ArString, FinTieBreak as TbString, FinArrowPosition as ArPos, FinTiePosition as TbPos, GrPhase "
		. "FROM Finals "
		. "INNER JOIN Grids ON FinMatchNo=GrMatchNo "
		. "WHERE FinTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND FinMatchNo=" . StrSafe_DB($MatchNo) . " AND FinEvent=" . StrSafe_DB($EvCode);
	if($TeamEvent) {
		$TablePrefix = "Tf";
		$Select
			= "SELECT "
			. "TfEvent as EvCode, TfMatchNo as MatchNo, TfArrowString as ArString, TfTieBreak as TbString, TfArrowPosition as ArPos, TfTiePosition as TbPos, GrPhase "
			. "FROM TeamFinals "
			. "INNER JOIN Grids ON TfMatchNo=GrMatchNo "
			. "WHERE TfTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND TfMatchNo=" . StrSafe_DB($MatchNo) . " AND TfEvent=" . StrSafe_DB($EvCode);
	}
	$Rs=safe_r_sql($Select);
	if (safe_num_rows($Rs)==1)
	{
		$MatchUpdated=false; // server per aggiornare il timestamp

		$MyRow=safe_fetch($Rs);

		$obj=getEventArrowsParams($MyRow->EvCode,$MyRow->GrPhase,$TeamEvent);
		$maxArrows=$obj->ends*$obj->arrows;
		$maxSoArrows=$obj->so;

		$isShootOff = 0;
		$ArrowNumber = -1;
		if(preg_match('/^([01])[|]([0-9]+)$/i',$ArrowPos,$found)) {
			$isShootOff=$found[1];
			$ArrowNumber = $found[2];
		} elseif(preg_match('/^([0-9]+)$/i',$ArrowPos)) {
			if($ArrowPos>$maxArrows) {
				$isShootOff = 1;
				$ArrowNumber = $ArrowPos-$maxArrows-1;
			} else {
				$isShootOff=0;
				$ArrowNumber = $ArrowPos-1;
			}
		} else {
			$ArrowNumber = strpos(str_pad($MyRow->ArString,$maxArrows," ",STR_PAD_RIGHT)," ");
			if($ArrowNumber===false || $ArrowNumber>=$maxArrows) {
				$isShootOff = 1;
				$ArrowNumber = strpos(str_pad($MyRow->TbString,$maxSoArrows," ",STR_PAD_RIGHT)," ");
				if($ArrowNumber===false || $ArrowNumber >= $maxSoArrows)
					$ArrowNumber = -1;
			}
		}

		if($ArrowNumber != -1) {
			$retValue = $isShootOff . "|" . $ArrowNumber;
			$arrArrows = unserialize(($isShootOff ? $MyRow->TbPos : $MyRow->ArPos));
			if(!is_array($arrArrows))
				$arrArrows = array();
			if($ArrowPosX == '' || $ArrowPosY == '') {
				unset($arrArrows[$ArrowNumber]);
			} else {
				$arrArrows[$ArrowNumber] = array($ArrowPosX, $ArrowPosY, $ArrowDiam);
				if(!$ArrowDiam)
					unset($arrArrows[$ArrowNumber][2]);
			}
			$Sql = "UPDATE "
				. ($TeamEvent==0 ? "Finals" : "TeamFinals") . " "
				. "SET "
				. $TablePrefix . ($isShootOff==0 ? "ArrowPosition" : "TiePosition") . "=" . StrSafe_DB(count($arrArrows) ? serialize($arrArrows) : '') . ", "
				. "{$TablePrefix}DateTime={$TablePrefix}DateTime "
				. "WHERE "
				. "{$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
				. "AND {$TablePrefix}MatchNo=". StrSafe_DB($MyRow->MatchNo) . " "
				. "AND {$TablePrefix}Tournament=". StrSafe_DB($_SESSION['TourId']);
			safe_w_sql($Sql);

			$MatchUpdated = ($MatchUpdated or safe_w_affected_rows());

			if($MatchUpdated) {
				$Sql = "UPDATE "
					. ($TeamEvent==0 ? "Finals" : "TeamFinals") . " "
					. "SET "
					. "{$TablePrefix}DateTime='".date('Y-m-d H:i:s')."' "
					. "WHERE "
					. " {$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
					. "AND {$TablePrefix}MatchNo = ". StrSafe_DB($MyRow->MatchNo). " "
					. "AND {$TablePrefix}Tournament=". StrSafe_DB($_SESSION['TourId']);
				safe_w_sql($Sql);
			}
		}
	}
	return $retValue;
}

function UpdateArrowString($MatchNo, $EvCode, $TeamEvent, $ArrowString, $ArrowStart, $ArrowEnd, $ToId=0) {
	$CompId = $ToId;
	if(empty($CompId) && !empty($_SESSION['TourId']))
		$CompId = $_SESSION['TourId'];

	global $CFG;
	$Select ='';

	$TablePrefix = "Fin";
	$Select
		= "SELECT "
		. "FinEvent as EvCode, FinMatchNo as MatchNo, FinArrowString as ArString, FinTieBreak as TbString, FinConfirmed as Confirmed, "
		. "EvMatchMode, EvMatchArrowsNo, GrPhase, FinLive as LiveMatch "
		. "FROM Finals "
		. "INNER JOIN Grids ON FinMatchNo=GrMatchNo "
		. "INNER JOIN Events ON FinEvent=EvCode AND FinTournament=EvTournament AND EvTeamEvent=0 "
		. "WHERE FinTournament={$CompId} AND FinMatchNo=" . StrSafe_DB($MatchNo) . " AND FinEvent=" . StrSafe_DB($EvCode);
	if($TeamEvent) {
		$TablePrefix = "Tf";
		$Select
			= "SELECT "
			. "TfEvent as EvCode, TfMatchNo as MatchNo, TfArrowString as ArString, TfTieBreak as TbString, TfConfirmed as Confirmed, "
			. "EvMatchMode, EvMatchArrowsNo, GrPhase, TfLive as LiveMatch "
			. "FROM TeamFinals "
			. "INNER JOIN Grids ON TfMatchNo=GrMatchNo "
			. "INNER JOIN Events ON TfEvent=EvCode AND TfTournament=EvTournament AND EvTeamEvent=1 "
			. "WHERE TfTournament={$CompId} AND TfMatchNo=" . StrSafe_DB($MatchNo) . " AND TfEvent=" . StrSafe_DB($EvCode);
	}

	$Rs=safe_r_sql($Select);
	if (safe_num_rows($Rs)==1)
	{
		$MyRow=safe_fetch($Rs);

		$obj=getEventArrowsParams($MyRow->EvCode,$MyRow->GrPhase,$TeamEvent,$CompId);
		$maxArrows=$obj->ends*$obj->arrows;
		$maxSoArrows=$obj->so;

		$ArrowStart--;
		$Len=$ArrowEnd-$ArrowStart;
		$Offset=($ArrowStart<$maxArrows ? 0 : $maxArrows);

		$SubArrowString=substr($ArrowString,0,$Len);
		$tmpArrowString=str_pad(($Offset==0 ? $MyRow->ArString : $MyRow->TbString),($Offset==0 ? $maxArrows : $maxSoArrows)," ",STR_PAD_RIGHT);
		$tmpArrowString=substr_replace($tmpArrowString,$SubArrowString,$ArrowStart-$Offset,$Len);

		$tmpArrowString=substr($tmpArrowString, 0, ($Offset==0 ? $maxArrows : $maxSoArrows));

		$query="UPDATE "
			. ($TeamEvent==0 ? "Finals" : "TeamFinals") . " "
			. "SET "
			. $TablePrefix . ($Offset==0 ? "ArrowString" : "Tiebreak") . "=" . StrSafe_DB($tmpArrowString) . ", "
			. "{$TablePrefix}DateTime={$TablePrefix}DateTime "
			. "WHERE "
			. "{$TablePrefix}Tie!=2 "
			. "AND {$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
			. "AND {$TablePrefix}MatchNo=". StrSafe_DB($MyRow->MatchNo) . " "
			. "AND {$TablePrefix}Tournament=". StrSafe_DB($CompId);

		safe_w_sql($query);
		if(safe_w_affected_rows()) {
			$m=array($MyRow->MatchNo, $MyRow->MatchNo%2 ? $MyRow->MatchNo-1 : $MyRow->MatchNo+1);
			$query="UPDATE "
				. ($TeamEvent==0 ? "Finals" : "TeamFinals") . " "
				. "SET "
				. "{$TablePrefix}DateTime='".date('Y-m-d H:i:s')."', "
				. "{$TablePrefix}Status=2 " // means that the arrows have changed, requests confirmation
				. "WHERE "
				. " {$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
				. "AND {$TablePrefix}MatchNo = ". StrSafe_DB($MyRow->MatchNo). " "
				. "AND {$TablePrefix}Tournament=". StrSafe_DB($CompId);
			safe_w_sql($query);

			// The Winner status must be reset and the match switches back to not confirmed
			safe_w_sql("update ". ($TeamEvent==0 ? "Finals" : "TeamFinals") ." set {$TablePrefix}Confirmed=0,{$TablePrefix}WinLose=0,{$TablePrefix}Tie=if({$TablePrefix}Tie=1,0,{$TablePrefix}Tie) WHERE "
				. " {$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
				. "AND {$TablePrefix}MatchNo in ($m[0], $m[1]) "
				. "AND {$TablePrefix}Tournament=". StrSafe_DB($CompId));
			if($MyRow->Confirmed) {
				// the match was confirmed so status to 3 of the other match
				safe_w_sql("update ". ($TeamEvent==0 ? "Finals" : "TeamFinals") ." set {$TablePrefix}Status=({$TablePrefix}Status | 2) WHERE "
					. " {$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
					. "AND {$TablePrefix}MatchNo = $m[1] "
					. "AND {$TablePrefix}Tournament=". StrSafe_DB($CompId));
			}

/*
//if match is live writes the xml for boinx!
 			if($MyRow->LiveMatch) {
				require_once('Common/Lib/Fun_Modules.php');
				if(getModuleParameter('Boinx', 'Write', '', $CompId)) {
					$TourCode=getCodeFromId($CompId);
					require_once('Common/Lib/Obj_RankFactory.php');
					require_once('Common/Fun_FormatText.inc.php');
					$file=$CFG->DOCUMENT_PATH.'Modules/Boinx/XML/'.$TourCode.'-Scores.xml';
					if($dir=getModuleParameter('Boinx', 'SavePath', '', $CompId) and is_dir($dir) and is_writable($dir)) {
						$file=$dir.'/'.$TourCode.'-Scores.xml';
					}
					$opts=array('tournament' => $CompId, 'liveFlag'=>1);
					$FILTER=($TeamEvent ? "f.TfLive='1'" : "f.FinLive='1'");
					$EXCLUDE_HEADER=true; // stops outputting the file!!!!
					$TourId=$CompId;
					$TourCodeSafe=$CompId;

					include("Modules/Boinx/IanseoScores-".($TeamEvent ? 1 : 0).".php");

					$XML= $XmlDoc->SaveXML();
					file_put_contents($file, $XML);
					chmod($file, 0666);
				}
			}
*/
		}
		//print $query;
		return MatchTotal($MatchNo, $EvCode, $TeamEvent, $CompId);
	}
}

function MatchTotal($MatchNo, $EvCode, $TeamEvent=0, $ToId=0) {
	$CompId = $ToId;
	if(empty($CompId) && !empty($_SESSION['TourId']))
		$CompId = $_SESSION['TourId'];

	if(is_null($MatchNo) || is_null($EvCode))	//Devono esistere sia il MatchNo che l'evento
		return;

	$MatchFinished=false; // serve per vedere se il match è finito
	$TablePrefix = "Fin";
	$Select
		= "SELECT "
		. "f.FinEvent as EvCode, f.FinMatchNo as MatchNo, f2.FinMatchNo as OppMatchNo, EvMatchMode, EvMatchArrowsNo, "
		. "IF(f.FinDateTime>=f2.FinDateTime, f.FinDateTime, f2.FinDateTime) AS DateTime,"
		. "f.FinScore AS Score, f.FinSetScore AS SetScore, f.FinTie as Tie, IFNULL(f.FinArrowString,'') as ArString, IFNULL(f.FinTieBreak,'') as TbString, "
		. "f2.FinScore AS OppScore, f2.FinSetScore AS OppSetScore, f2.FinTie as OppTie, IFNULL(f2.FinArrowString,'') as OppArString, IFNULL(f2.FinTieBreak,'') as OppTbString, "
		. "GrPhase "
		. "FROM Finals AS f "
		. "INNER JOIN Finals AS f2 ON f.FinEvent=f2.FinEvent AND f.FinMatchNo=IF((f.FinMatchNo % 2)=0,f2.FinMatchNo-1,f2.FinMatchNo+1) AND f.FinTournament=f2.FinTournament "
		. "INNER JOIN Events ON f.FinEvent=EvCode AND f.FinTournament=EvTournament AND EvTeamEvent=0 "
		. "INNER JOIN Grids ON f.FinMatchNo=GrMatchNo "
		. "WHERE f.FinTournament=" . StrSafe_DB($CompId) . " AND (f.FinMatchNo % 2)=0 AND GrMatchNo=" . StrSafe_DB(($MatchNo % 2 == 0 ? $MatchNo:$MatchNo-1)) . " AND f.FinEvent=" . StrSafe_DB($EvCode) . " "
		. "ORDER BY f.FinEvent, f.FinMatchNo ";

	if($TeamEvent) {
		$TablePrefix = "Tf";
		$Select
			= "SELECT "
			. "f.TfEvent as EvCode, f.TfMatchNo as MatchNo, f2.TfMatchNo as OppMatchNo, EvMatchMode, EvMatchArrowsNo, "
			. "IF(f.TfDateTime>=f2.TfDateTime, f.TfDateTime, f2.TfDateTime) AS DateTime,"
			. "f.TfScore AS Score, f.TfSetScore AS SetScore, f.TfTie as Tie, IFNULL(f.TfArrowString,'') as ArString, IFNULL(f.TfTieBreak,'') as TbString, "
			. "f2.TfScore AS OppScore, f2.TfSetScore AS OppSetScore, f2.TfTie as OppTie, IFNULL(f2.TfArrowString,'') as OppArString, IFNULL(f2.TfTieBreak,'') as OppTbString, "
			. "GrPhase "
			. "FROM TeamFinals AS f "
			. "INNER JOIN TeamFinals AS f2 ON f.TfEvent=f2.TfEvent AND f.TfMatchNo=IF((f.TfMatchNo % 2)=0,f2.TfMatchNo-1,f2.TfMatchNo+1) AND f.TfTournament=f2.TfTournament "
			. "INNER JOIN Events ON f.TfEvent=EvCode AND f.TfTournament=EvTournament AND EvTeamEvent=1 "
			. "INNER JOIN Grids ON f.TfMatchNo=GrMatchNo "
			. "WHERE f.TfTournament=" . StrSafe_DB($CompId) . " AND (f.TfMatchNo % 2)=0 AND GrMatchNo=" . StrSafe_DB(($MatchNo % 2 == 0 ? $MatchNo:$MatchNo-1)) . " AND f.TfEvent=" . StrSafe_DB($EvCode) . " "
			. "ORDER BY f.TfEvent, f.TfMatchNo ";
	}

	//print $Select . "<br>";exit;
	$MatchUpdated=false; // serve per aggiornare il timestamp
	$Rs=safe_r_sql($Select);
	if (safe_num_rows($Rs)==1) {
		$MyRow=safe_fetch($Rs);
		$obj=getEventArrowsParams($MyRow->EvCode,$MyRow->GrPhase,$TeamEvent,$CompId);
		$TotArrows=$obj->ends*$obj->arrows;
		$Winner=-1;

		// set winner... of Ties
		if($MyRow->Tie) {
			$Winner=$MyRow->MatchNo;
			$MatchFinished=true;
		} elseif ($MyRow->OppTie) {
			$Winner=$MyRow->OppMatchNo;
			$MatchFinished=true;
		}

		$Score=ValutaArrowString(substr($MyRow->ArString, 0, $TotArrows));
		$OppScore=ValutaArrowString(substr($MyRow->OppArString, 0, $TotArrows));


		if($MyRow->EvMatchMode==0) {
			$SetPointsAth=array();
			$SetPointsOpp=array();
			for($i=0; $i<$TotArrows; $i=$i+$obj->arrows) {
				//Cicla per tutte le volee dell'incontro
				$SetPointsAth[] = ValutaArrowString(substr($MyRow->ArString, $i, $obj->arrows));
				$SetPointsOpp[] = ValutaArrowString(substr($MyRow->OppArString, $i, $obj->arrows));
			}
			//Sistema Cumulativo
			if(($a1=strlen(str_replace(' ', '', $MyRow->ArString)))==$TotArrows
				and ($a2=strlen(str_replace(' ', '', $MyRow->OppArString)))==$TotArrows
				and ($t1=strlen(str_replace(' ', '', $MyRow->TbString))) == ($t2=strlen(str_replace(' ', '', $MyRow->OppTbString)))
				) {
				// all arrows have been shot from both sides...


				// if match is over establish the winner
				// only if not already decided by the tie
				// and if there are no doubts
				// and no SO are going on
				if($Winner==-1) {
					// No winner decided yet...
					$Proceed=(ctype_upper($MyRow->ArString.$MyRow->OppArString));
					//Da Remmare dopo ANKARA
					$Proceed=true;
					if(!$Proceed) {
						// check if the stars would make any change
						$Regexp='';
						$RaisedScore=$Score+RaiseStars(substr($MyRow->ArString, 0, $TotArrows), $Regexp, $MyRow->EvCode, $TeamEvent, $ToId);
						$RaisedOppScore=$OppScore+RaiseStars(substr($MyRow->OppArString, 0, $TotArrows), $Regexp, $MyRow->EvCode, $TeamEvent, $ToId);
						if($RaisedScore < $OppScore or $RaisedOppScore < $Score) {
							// Even with all stars "in" the ath will not make more than the opponent
							$Proceed=true;
						}
					}
					if($Proceed) {
						if($Score>$OppScore) {
							$Winner=$MyRow->MatchNo;
							$MatchFinished=true;
						} elseif($Score<$OppScore) {
							$Winner=$MyRow->OppMatchNo;
							$MatchFinished=true;
						} else {
							if( strlen(str_replace(' ', '', $MyRow->TbString))==$obj->so
								and strlen(str_replace(' ', '', $MyRow->TbString))==strlen(str_replace(' ', '', $MyRow->OppTbString))
								) {
								// Verifico le stringhe CASE INSENSITIVE - in questo momento me ne frego degli "*"
								list($AthTbValue, $AthWeight, $AthStars, $AthNumX, $AthArrows) = ValutaArrowStringSO($MyRow->TbString);
								list($OppTbValue, $OppWeight, $OppStars, $OppNumX, $OppArrows) = ValutaArrowStringSO($MyRow->OppTbString);

								$MatchFinished=true;

								if($AthStars > $OppStars) {
									// Athlete 1 has at least one arrow set as closest to center
									$Winner = $MyRow->MatchNo;
									$WinnerId = $MyRow->MatchNo;
								} elseif($AthStars < $OppStars) {
									// Athlete 2 has one arrow closer to center
									$Winner = $MyRow->OppMatchNo;
									$WinnerId = $MyRow->OppMatchNo;
								} elseif($AthTbValue > $OppTbValue) {
									//TbString è maggiore di OppTbString --> il primo vince
									$Winner = $MyRow->MatchNo;
									$WinnerId = $MyRow->MatchNo;
								} elseif($AthTbValue < $OppTbValue) {
									//OppTbString è maggiore di TbString --> il secondo vince
									$Winner = $MyRow->OppMatchNo;
									$WinnerId = $MyRow->OppMatchNo;
								} elseif($AthNumX > $OppNumX) {
									// Athlete 1 has more Xs than Athlete 2
									$Winner = $MyRow->MatchNo;
									$WinnerId = $MyRow->MatchNo;
								} elseif($AthNumX < $OppNumX) {
									// Athlete 2 has more Xs than Athlete 1
									$Winner = $MyRow->OppMatchNo;
									$WinnerId = $MyRow->OppMatchNo;
								} else {
									$MatchFinished=false;
									foreach($AthArrows as $k => $v) {
										if($v > $OppArrows[$k]) {
											$Winner = $MyRow->MatchNo;
											$WinnerId = $MyRow->MatchNo;
											$MatchFinished=true;
											break;
										}
										if($v < $OppArrows[$k]) {
											$Winner = $MyRow->OppMatchNo;
											$WinnerId = $MyRow->OppMatchNo;
											$MatchFinished=true;
											break;
										}
									}

								}
							}

						}
					}
				}
			} else {
				// match is not over, so if no byes reset the winner!
				if($MyRow->Tie!=2 and $MyRow->OppTie!=2) {
					$Winner=-1;
				}
			}
			$query="UPDATE "
				. ($TeamEvent==0 ? "Finals" : "TeamFinals") . " "
				. "SET "
				. "{$TablePrefix}WinLose=" . ($Winner==$MyRow->MatchNo ? '1' : '0') . ", "
				. "{$TablePrefix}Score=" . $Score . ", "
				. "{$TablePrefix}SetScore=0, "
				. "{$TablePrefix}SetPoints=" . StrSafe_DB(implode('|', $SetPointsAth)) . ", "
				. "{$TablePrefix}Tie=" . (($Score==$OppScore and $Winner==$MyRow->MatchNo) ? '1' : '0') . ", "
				. "{$TablePrefix}DateTime={$TablePrefix}DateTime "
				. "WHERE "
				. " {$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
				. " AND {$TablePrefix}MatchNo=". StrSafe_DB($MyRow->MatchNo) . " "
				. " AND {$TablePrefix}Tournament=". StrSafe_DB($CompId);

			safe_w_sql($query);
			$MatchUpdated = ($MatchUpdated or safe_w_affected_rows());

			//print $query.'<br><br>';

			$query="UPDATE "
				. ($TeamEvent==0 ? "Finals" : "TeamFinals") . " "
				. "SET "
				. "{$TablePrefix}WinLose=" . ($Winner==$MyRow->OppMatchNo ? '1' : '0') . ", "
				. "{$TablePrefix}Score=" . $OppScore . ", "
				. "{$TablePrefix}SetScore=0, "
				. "{$TablePrefix}SetPoints=" . StrSafe_DB(implode('|', $SetPointsOpp)) . ", "
				. "{$TablePrefix}Tie=" . (($Score==$OppScore and $Winner==$MyRow->OppMatchNo) ? '1' : '0') . ", "
				. "{$TablePrefix}DateTime={$TablePrefix}DateTime "
				. "WHERE "
				. " {$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
				. " AND {$TablePrefix}MatchNo=". StrSafe_DB($MyRow->OppMatchNo) . " "
				. " AND {$TablePrefix}Tournament=". StrSafe_DB($CompId);

			safe_w_sql($query);
			$MatchUpdated = ($MatchUpdated or safe_w_affected_rows());

			//print $query.'<br><br>';
		} else {
			//Sistema a Set
			$SetPointsAth=array();
			$SetPointsOpp=array();
			$AthSpBe=array();
			$OppSpBe=array();
			$SetAth=0;
			$SetOpp=0;
			$SetAthWin=0;
			$SetOppWin=0;
			$WinnerId=-1;
			for($i=0; $i<$TotArrows; $i=$i+$obj->arrows) {
				//Cicla per tutte le volee dell'incontro
				$AthEndString=substr($MyRow->ArString, $i, $obj->arrows);
				$OppEndString=substr($MyRow->OppArString, $i, $obj->arrows);
				$MatchString=$AthEndString.$OppEndString;
				$AthSetPoints=ValutaArrowString($AthEndString);
				$OppSetPoints=ValutaArrowString($OppEndString);
				$SetPointsAth[] = $AthSetPoints;
				$SetPointsOpp[] = $OppSetPoints;


				if(strpos($MatchString, ' ')===false and strlen($AthEndString) and strlen($AthEndString)==strlen($OppEndString)) {
					// All arrows have been shot
					$Proceed=ctype_upper($MatchString); // check if there are stars
					//Da Remmare dopo ANKARA
					$Proceed=true;
					if(!$Proceed) {
						// check if stars can change result
						$RegExp='';
						$AthSetPointsUpper=$AthSetPoints+RaiseStars($AthEndString, $RegExp, $MyRow->EvCode, $TeamEvent, $ToId);
						$OppSetPointsUpper=$OppSetPoints+RaiseStars($OppEndString, $RegExp, $MyRow->EvCode, $TeamEvent, $ToId);
						if($AthSetPointsUpper < $OppSetPoints or $OppSetPointsUpper < $AthSetPoints) {
							// even with all stars as higher points will the score beat the opponent's score
							$Proceed=true;
						}
// 						debug_svela(array($AthSetPoints, $AthSetPointsUpper, ));
					}
					if($Proceed) {
						if($AthSetPoints>$OppSetPoints) {
							$SetAth += 2;
							$SetAthWin++;
							$AthSpBe[]=2;
							$OppSpBe[]=0;
						} elseif($AthSetPoints<$OppSetPoints) {
							$SetOpp += 2;
							$SetOppWin++;
							$AthSpBe[]=0;
							$OppSpBe[]=2;
						} else {
							$SetAth++;
							$SetOpp++;
							$AthSpBe[]=1;
							$OppSpBe[]=1;
						}
					}
				}
			}

			if($SetAth > $obj->ends+2 or $SetOpp > $obj->ends+2) {
				$SetAth=0;
				$SetOpp=0;
			}

			if($SetAth==$SetOpp
				and strlen(str_replace(' ', '', $MyRow->TbString))==$obj->so
				and strlen(trim($MyRow->TbString))==strlen(trim($MyRow->OppTbString))
				) {
				// Verifico le stringhe CASE INSENSITIVE - in questo momento me ne frego degli "*"
				list($AthTbValue, $AthWeight, $AthStars, $AthNumX, $AthArrows) = ValutaArrowStringSO($MyRow->TbString);
				list($OppTbValue, $OppWeight, $OppStars, $OppNumX, $OppArrows) = ValutaArrowStringSO($MyRow->OppTbString);


				if($AthStars > $OppStars) {
					// Athlete 1 has at least one arrow set as closest to center
					$Winner = $MyRow->MatchNo;
					$WinnerId = $MyRow->MatchNo;
					$SetAth++;
				} elseif($AthStars < $OppStars) {
					// Athlete 2 has one arrow closer to center
					$Winner = $MyRow->OppMatchNo;
					$WinnerId = $MyRow->OppMatchNo;
					$SetOpp++;
				} elseif($AthTbValue > $OppTbValue) {
					//TbString è maggiore di OppTbString --> il primo vince
					$Winner = $MyRow->MatchNo;
					$WinnerId = $MyRow->MatchNo;
					$SetAth++;
				} elseif($AthTbValue < $OppTbValue) {
					 //OppTbString è maggiore di TbString --> il secondo vince
					 $Winner = $MyRow->OppMatchNo;
					 $WinnerId = $MyRow->OppMatchNo;
					 $SetOpp++;
				} elseif($AthNumX > $OppNumX) {
					// Athlete 1 has more Xs than Athlete 2
					$Winner = $MyRow->MatchNo;
					$WinnerId = $MyRow->MatchNo;
					$SetAth++;
				} elseif($AthNumX < $OppNumX) {
					// Athlete 2 has more Xs than Athlete 1
					$Winner = $MyRow->OppMatchNo;
					$WinnerId = $MyRow->OppMatchNo;
					$SetOpp++;
				} else {
					foreach($AthArrows as $k => $v) {
						if($v > $OppArrows[$k]) {
							$Winner = $MyRow->MatchNo;
							$WinnerId = $MyRow->MatchNo;
							$SetAth++;
							break;
						}
						if($v < $OppArrows[$k]) {
							$Winner = $MyRow->OppMatchNo;
							$WinnerId = $MyRow->OppMatchNo;
							$SetOpp++;
							break;
						}
					}
				}
			} elseif($SetAth>=$obj->winAt) {
				$Winner = $MyRow->MatchNo;
			} elseif($SetOpp>=$obj->winAt) {
				$Winner = $MyRow->OppMatchNo;
			}

			$query="UPDATE "
				. ($TeamEvent==0 ? "Finals" : "TeamFinals") . " "
				. "SET "
				. "{$TablePrefix}WinLose=" . ($Winner==$MyRow->MatchNo ? '1' : '0') . ", "
				. "{$TablePrefix}Score=" . $Score . ", "
				. "{$TablePrefix}SetScore=" . $SetAth . ", "
				. "{$TablePrefix}SetPoints=" . StrSafe_DB(implode('|', $SetPointsAth)) . ", "
				. "{$TablePrefix}SetPointsByEnd=" . StrSafe_DB(implode('|', $AthSpBe)) . ", "
				. "{$TablePrefix}WinnerSet=" . $SetAthWin . ", "
				. "{$TablePrefix}Tie=" . ($WinnerId == $MyRow->MatchNo ? '1':'0') . ", "
				. "{$TablePrefix}DateTime={$TablePrefix}DateTime "
				. "WHERE "
				. "{$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
				. "AND {$TablePrefix}MatchNo=". StrSafe_DB($MyRow->MatchNo) . " "
				. "AND {$TablePrefix}Tournament=". StrSafe_DB($CompId);
			safe_w_sql($query);
			$MatchUpdated = ($MatchUpdated or safe_w_affected_rows());

			$query="UPDATE "
				. ($TeamEvent==0 ? "Finals" : "TeamFinals") . " "
				. "SET "
				. "{$TablePrefix}WinLose=" . ($Winner==$MyRow->OppMatchNo ? '1' : '0') . ", "
				. "{$TablePrefix}Score=" . $OppScore . ", "
				. "{$TablePrefix}SetScore=" . $SetOpp . ", "
				. "{$TablePrefix}SetPoints=" . StrSafe_DB(implode('|', $SetPointsOpp)) . ", "
				. "{$TablePrefix}SetPointsByEnd=" . StrSafe_DB(implode('|', $OppSpBe)) . ", "
				. "{$TablePrefix}WinnerSet=" . $SetOppWin . ", "
				. "{$TablePrefix}Tie=" . ($WinnerId == $MyRow->OppMatchNo ? '1':'0') . ", "
				. "{$TablePrefix}DateTime={$TablePrefix}DateTime "
				. "WHERE "
				. "{$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
				. "AND {$TablePrefix}MatchNo=". StrSafe_DB($MyRow->OppMatchNo) . " "
				. "AND {$TablePrefix}Tournament=". StrSafe_DB($CompId);
			safe_w_sql($query);
			$MatchUpdated = ($MatchUpdated or safe_w_affected_rows());

			if($SetAth >= $obj->winAt || $SetOpp >= $obj->winAt) {
				$MatchFinished=true;
			}
		}

		if($MatchUpdated) {
			$query="UPDATE "
				. ($TeamEvent==0 ? "Finals" : "TeamFinals") . " "
				. "SET "
				. "{$TablePrefix}DateTime='".date('Y-m-d H:i:s')."' "
				. "WHERE "
				. " {$TablePrefix}Event=". StrSafe_DB($MyRow->EvCode) . " "
				. "AND {$TablePrefix}MatchNo in (". StrSafe_DB($MyRow->MatchNo) . ',' . StrSafe_DB($MyRow->OppMatchNo) . ") "
				. "AND {$TablePrefix}Tournament=". StrSafe_DB($CompId);
			safe_w_sql($query);
		}
		//Serve per ricalcolare le ranking, solo medaglie


		if($MatchNo < 4 and $MatchFinished) {
			if($TeamEvent) {
				move2NextPhaseTeam(NULL, $EvCode, $MatchNo, $CompId);
			} else {
				move2NextPhase(NULL, $EvCode, $MatchNo, $CompId);
			}
		}
		runJack("FinArrUpdate", $CompId, array("Event"=>$EvCode ,"Team"=>$TeamEvent,"MatchNo"=>$MatchNo ,"TourId"=>$CompId));

	}
	return $MatchFinished;
}
?>