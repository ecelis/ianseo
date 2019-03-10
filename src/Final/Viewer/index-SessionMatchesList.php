<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
CheckTourSession(true);

$JSON=array('error'=>1, 'data'=>array());

if(empty($_REQUEST['Session']) or !preg_match("/^[EF][0-9]+$/i", $_REQUEST['Session'])) {
	JsonOut($JSON);
}

$SesType=$_REQUEST['Session'][0];
$SesOrder=intval(substr($_REQUEST['Session'], 1));

checkACL(array(AclIndividuals,AclTeams, AclOutput), AclReadOnly, false);
require_once('Common/Lib/CommonLib.php');
require_once('Common/Lib/Obj_RankFactory.php');

// get all matches in that session
$Sql = "SELECT SesName, SesDtStart, SesDtEnd FROM Session WHERE SesTournament=".$_SESSION['TourId'] . " AND SesType='$SesType' and SesOrder=$SesOrder";
$q=safe_r_SQL($Sql);
$Sessions = array();
$whereCond=array();
$cnt=1;
while($r=safe_fetch($q)) {
	$Sessions[] = array("Name"=>$r->SesName, "Start"=>$r->SesDtStart, "End"=>$r->SesDtEnd);
	$whereCond[$cnt++] = "()";
}

$Sql = "SELECT FsEvent, FsTeamEvent, FsMatchNo, EvElimType
	FROM FinSchedule
	inner join Session on SesTournament=FSTournament and CONCAT(FsScheduledDate, ' ', FsScheduledTime) BETWEEN SesDtStart AND SesDtEnd
	inner join Events on EvTournament=FSTournament and EvTeamEvent=FSTeamEvent and EvCode=FSEvent
	WHERE FsTournament=".$_SESSION['TourId'] ." AND (FsMatchNo%2=0) and SesType='$SesType' and SesOrder=$SesOrder
	ORDER BY FsScheduledDate, FsScheduledTime";
$q=safe_r_SQL($Sql);
while($r=safe_fetch($q)) {
	$opts=array('matchno'=>$r->FsMatchNo, 'events'=>$r->FsEvent);
	$rank=Obj_RankFactory::create(($r->FsTeamEvent ? 'GridTeam':'GridInd'), $opts);
	$rank->read();
	$rankData=$rank->getData();

	$ChangePage=false;
	$Continue='';

	$item=$rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0];
	$ExtraLineHeight=0;
	$AthlBorder=1;
	if($isTeam and $PrintNames) {
		$ExtraLineHeight=3*$rankData["sections"][$eventCode]['meta']['maxTeamPerson'];
		$AthlBorder='LTR';
	}

	if(!$i) {
		if(!$pdf->samePage(3, CellH, '', false)
			or (!$pdf->samePage($NumItems, CellH, '', false))) {
			// first item in a block... needs at least 3 rows to print the sessions data
			// not able to split in 3+3
			$ChangePage=true;
			if($runningDay == $item["scheduledDate"]) $Continue=' (Cont.)';
		}
	} elseif (($NumItems-$i == 4 and !$pdf->samePage(3, CellH, '', false))
		or !$pdf->samePage(1, CellH, '', false)) {
		// needs to have room for printing the last 3 rows
		$ChangePage=true;
		$Continue=' (Cont.)';
	}

	if($runningDay != $item["scheduledDate"]
		or $ChangePage) {
		// close the cell...
		if(!$FirstPage) $pdf->Line(OrisPDF::leftMargin, $y1=$pdf->GetY(), OrisPDF::leftMargin+25, $y1);

		$pdf->AddPage();

		$pdf->SetXY(OrisPDF::leftMargin, OrisPDF::topStart);
		$pdf->SetFont('','B');
		$pdf->Cell(25, CellH, "Date/Session", 1, 0, 'L', 0);
		$pdf->Cell(10, CellH/2, "Start", 'TLR', 0, 'C', 0);
		$pdf->SetXY($pdf->GetX()-10, $pdf->GetY()+CellH/2);
		$pdf->Cell(10, CellH/2, "Time", 'BLR', 0, 'C', 0);
		$pdf->SetXY($pdf->GetX(), $pdf->GetY()-CellH/2);
		$pdf->Cell(31, CellH, "Event", 1, 0, 'L', 0);
		$pdf->Cell(10, CellH, "Round", 1, 0, 'L', 0);

		$pdf->Cell(7, CellH, "WR", 1, 0, 'L', 0);
		$pdf->Cell(8, CellH/2, "R.R.", 'TLR', 0, 'C', 0);
		$pdf->SetXY($pdf->GetX()-8, $pdf->GetY()+CellH/2);
		$pdf->Cell(8, CellH/2, "Rank", 'BLR', 0, 'C', 0);
		$pdf->SetXY($pdf->GetX(), $pdf->GetY()-CellH/2);
		$pdf->Cell(42, CellH, "Participant 1 (Target 1)", 1, 0, 'L', 0);

		$pdf->Cell(7, CellH, "WR", 1, 0, 'L', 0);
		$pdf->Cell(8, CellH/2, "R.R.", 'TLR', 0, 'C', 0);
		$pdf->SetXY($pdf->GetX()-8, $pdf->GetY()+CellH/2);
		$pdf->Cell(8, CellH/2, "Rank", 'BLR', 0, 'C', 0);
		$pdf->SetXY($pdf->GetX(), $pdf->GetY()-CellH/2);
		$pdf->Cell(42, CellH, "Participant 2 (Target 2)", 1, 1, 'L', 0);
		$pdf->SetFont('','');
		if($runningDay != $item["scheduledDate"]) {
			$sesInDay=0;
		} else {
			$evInSession=-1;
// 				$pdf->dy(1);
		}

		$runningDay = $item["scheduledDate"];
	}
	$FirstPage=false;
	if($lastSes != $vSes) {
		$evInSession=0;
		$sesInDay++;
		$sesCnt++;
		$pdf->Line(OrisPDF::leftMargin, $y1=$pdf->GetY(), OrisPDF::leftMargin+25, $y1);
		$pdf->dy(1);
	} else {
		$evInSession++;
	}

	$OrgY=$pdf->getY();

	$pdf->Cell(25, CellH+$ExtraLineHeight, ($evInSession==0 ? (new DateTime($runningDay))->format('D j M').$Continue:($evInSession==1 ? "Session ".$sesInDay:($evInSession==2 ? (in_array($sesCnt,$Sessions) ? $Sessions[$sesCnt]['Name']:''):''))), 'LR'.($evInSession==0 ? 'T':''), 0, 'L', 0);
	$pdf->Cell(10, CellH+$ExtraLineHeight, (new DateTime($item["scheduledTime"]))->format('H:i'), 1, 0, 'C', 0);
	$pdf->Cell(31, CellH+$ExtraLineHeight, $rankData["sections"][$eventCode]["meta"]["eventName"], 1, 0, 'L', 0);
	$pdf->Cell(10, CellH+$ExtraLineHeight, $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["meta"]["phaseName"], 1, 0, 'L', 0);

	$SqlWR = "SELECT RankRanking
		FROM Rankings
		WHERE RankTournament={$_SESSION["TourId"]} AND
			RankCode='".$item[$isTeam ? "countryCode":"bib"]."' AND
			RankIocCode='FITA' AND
			RankTeam={$isTeam} AND
			RankEvent='{$eventCode}'";
	$wrQ = safe_r_SQL($SqlWR);
	$wrank = 'NR';
	if(safe_num_rows($wrQ)) {
		$wrank = (safe_fetch($wrQ)->RankRanking);
	}

	$pdf->Cell(7, CellH+$ExtraLineHeight, $wrank, 1, 0, 'R', 0);
	$pdf->Cell(8, CellH+$ExtraLineHeight, $item["qualRank"], 1, 0, 'R', 0);
	$pdf->Cell(34, CellH, $item[$isTeam ? "countryName":"athlete"], $AthlBorder, 0, 'L', 0);
	$pdf->Cell(8, CellH+$ExtraLineHeight, $item["countryCode"], 1, 0, 'L', 0);


	$SqlWR = "SELECT RankRanking
		FROM Rankings
		WHERE RankTournament={$_SESSION["TourId"]} AND
		RankCode='".$item[$isTeam ? "oppCountryCode":"oppBib"]."' AND
		RankIocCode='FITA' AND
		RankTeam={$isTeam} AND
		RankEvent='{$eventCode}'";
	$wrQ = safe_r_SQL($SqlWR);
	$wrank = 'NR';
	if(safe_num_rows($wrQ)) {
		$wrank = (safe_fetch($wrQ)->RankRanking);
	}
	$pdf->Cell(7, CellH+$ExtraLineHeight, $wrank, 1, 0, 'R', 0);
	$pdf->Cell(8, CellH+$ExtraLineHeight, $item["oppQualRank"], 1, 0, 'R', 0);
	$pdf->Cell(34, CellH, $item[$isTeam ? "oppCountryName":"oppAthlete"], $AthlBorder, 0, 'L', 0);
	$pdf->Cell(8, CellH+$ExtraLineHeight, $item["oppCountryCode"], 1, 1, 'L', 0);

	if($isTeam and $PrintNames) {
		$OrgX=$pdf->getX()+95;
		$Font=$pdf->getFontSizePt();
		$pdf->SetFontSize(8);
		foreach($rankData["sections"][$eventCode]['athletes'][$item['teamId']][0] as $k => $Component) {
			$pdf->setxy($OrgX, 3*$k + $OrgY+8);
			$pdf->Cell(30, 3, $Component['athlete'], '', 0, 'L', 0);
		}
		$pdf->Line($OrgX-4, $OrgY+CellH+$ExtraLineHeight, $OrgX+30, $OrgY+CellH+$ExtraLineHeight);
		$OrgX+=57;
		foreach($rankData["sections"][$eventCode]['athletes'][$item['oppTeamId']][0] as $k => $Component) {
			$pdf->setxy($OrgX, 3*$k + $OrgY+8);
			$pdf->Cell(30, 3, $Component['athlete'], '', 0, 'L', 0);
		}
		$pdf->Line($OrgX-4, $OrgY+CellH+$ExtraLineHeight, $OrgX+30, $OrgY+CellH+$ExtraLineHeight);
		$pdf->SetY($OrgY+CellH+$ExtraLineHeight);
		$pdf->SetFontSize($Font);
	}
	$lastSes=$vSes;
}
$lastSes=0;
$evInSession=0;
$runningDay='';
$sesInDay=0;
$sesCnt=-1;
$pdf->SetFont('','');
$FirstPage=true;
foreach($SessionMatches as $vSes => $items) {
	$NumItems=count($items);
	foreach($items as $i => $kSes) {
		list($eventCode,$isTeam,$matchNo) = explode('|',$kSes);
	}
}
$pdf->Cell(25, CellH, "", 'T', 0, 'L', 0);





$options=array();

$Prefix=array();

if(is_numeric($_REQUEST['Phase'])) {
	$options['events']=array($EvCode . '@' . intval($_REQUEST['Phase']));
} else {
	$options['events']=array($EvCode);
	$PhId=-1;
	$Matches=array();
	// no valid phases check if it could be a WG or Field/3D pool system
	$q=safe_r_sql("select EvElimType from Events where EvTeamEvent=$TeamEvent and EvCode=".StrSafe_DB($EvCode)." and EvTournament={$_SESSION['TourId']}");
	if($r=safe_fetch($q)) {
		switch($r->EvElimType) {
			case '3':
				if($_REQUEST['Phase']=='A' or $_REQUEST['Phase']=='B' or $_REQUEST['Phase']=='C') {
					$options['matchnoArray']=getPoolMatchNos($_REQUEST['Phase']);
					$Prefix=getPoolMatchesShort();
				}
				break;
			case '4':
				if($_REQUEST['Phase']=='A' or $_REQUEST['Phase']=='B' or $_REQUEST['Phase']=='C' or $_REQUEST['Phase']=='D') {
					$options['matchnoArray']=getPoolMatchNosWA($_REQUEST['Phase']);
					$Prefix=getPoolMatchesShortWA();
				}
				break;
			default:
				// dies here as nothing meaningfull detected
				JsonOut($JSON);
		}
	}
}

$JSON['error']=0;

$rank=null;
if($TeamEvent) {
	$rank=Obj_RankFactory::create('GridTeam',$options);
} else {
	$rank=Obj_RankFactory::create('GridInd',$options);
}
$rank->read();
$Data=$rank->getData();

foreach($Data['sections'] as $kSec=>$vSec) {
	foreach($vSec['phases'] as $kPh=>$vPh) {
		foreach($vPh['items'] as $kItem=>$vItem) {
			$tmpL = array();
			$tmpR = array();
			if($TeamEvent==0) {
				$tmpL += array("Id"=>$vItem["bib"], "FamilyName"=>$vItem["familyName"], "GivenName"=>$vItem["givenName"], "NameOrder"=>$vItem["nameOrder"], "Gender"=>$vItem["gender"]);
				$tmpR += array("Id"=>$vItem["oppBib"], "FamilyName"=>$vItem["oppFamilyName"], "GivenName"=>$vItem["oppGivenName"], "NameOrder"=>$vItem["oppNameOrder"], "Gender"=>$vItem["oppGender"]);
			}
			$tmpL += array("TeamCode"=>$vItem["countryCode"], "TeamName"=>$vItem["countryName"], "Target"=>ltrim($vItem["target"],"0"),
				"Score"=>$vItem[($vSec['meta']['matchMode']==1 ?  'setScore': 'score')], "TieBreak"=>$vItem['tiebreakDecoded'], "Winner"=>($vItem['winner']? true:false));
			$tmpR += array("TeamCode"=>$vItem["oppCountryCode"], "TeamName"=>$vItem["oppCountryName"], "Target"=>ltrim($vItem["oppTarget"],"0"),
					"Score"=>$vItem[($vSec['meta']['matchMode']==1 ?  'oppSetScore': 'oppScore')], "TieBreak"=>$vItem['oppTiebreakDecoded'], "Winner"=>($vItem['oppWinner']? true:false));
			$JSON['data'][] = Array('Prefix' => (isset($Prefix[$vItem['matchNo']]) ? $Prefix[$vItem['matchNo']] : ''), "Event"=>$EvCode, "Type"=>$TeamEvent, "MatchId"=>$vItem['matchNo'], "ScheduledDateTime"=>date("Y-m-d H:i",strtotime($vItem["scheduledDate"] . " ". $vItem["scheduledTime"])), "LeftOpponent"=>$tmpL, "RightOpponent"=>$tmpR);
		}
	}
}

JsonOut($JSON);

