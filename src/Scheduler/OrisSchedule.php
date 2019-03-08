<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/pdf/OrisPDF.inc.php');
require_once('Common/Lib/Obj_RankFactory.php');

define("CellH",10);

CheckTourSession(true);

$pdf = new OrisPDF('C58', 'DETAILED COMPETITION SCHEDULE');

$pdf->SetTopMargin(OrisPDF::topStart);

$Sql = "SELECT SesName, SesDtStart, SesDtEnd FROM Session WHERE SesTournament=".$_SESSION['TourId'] . " AND SesType='F' ORDER BY SesDtStart, SesDtEnd";
$q=safe_r_SQL($Sql);
$Sessions = array();
$whereCond=array();
$cnt=1;
while($r=safe_fetch($q)) {
	$Sessions[] = array("Name"=>$r->SesName, "Start"=>$r->SesDtStart, "End"=>$r->SesDtEnd);
	$whereCond[$cnt++] = "(CONCAT(FsScheduledDate, ' ', FsScheduledTime) BETWEEN '{$r->SesDtStart}' AND '{$r->SesDtEnd}')";
}

$Sql = "SELECT CONCAT(FsEvent, '|', FsTeamEvent, '|', FsMatchNo) as SesKey, ";
if($whereCond) {
	$tmp=array();
	foreach($whereCond as $kWhere=>$vWhere) {
		$tmp[] = "IF({$vWhere},$kWhere,0)";
	}
	$Sql.='('.implode('+',$tmp).')';
} else {
	$Sql.=' 0 ';
}
$Sql .= " as SesNumber
	FROM FinSchedule
	WHERE FsTournament=".$_SESSION['TourId'] ." AND (FsMatchNo%2=0)".($whereCond ? " AND (" . implode(' OR ', $whereCond). ")" : '')."
	ORDER BY FsScheduledDate, FsScheduledTime";
$q=safe_r_SQL($Sql);
$SessionMatches = array();
while($r=safe_fetch($q)) {
	$SessionMatches[$r->SesNumber][] = $r->SesKey;
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
		$opts=array('matchno'=>$matchNo, 'events'=>$eventCode);
		$rank=Obj_RankFactory::create(($isTeam ? 'GridTeam':'GridInd'), $opts);
		$rank->read();
		$rankData=$rank->getData();

		$ChangePage=false;
		$Continue='';

		if(!$i) {
			if(!$pdf->samePage(3, CellH, '', false)
					or (!$pdf->samePage($NumItems, CellH, '', false))) {
				// first item in a block... needs at least 3 rows to print the sessions data
				// not able to split in 3+3
				$ChangePage=true;
				if($runningDay == $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0]["scheduledDate"]) $Continue=' (Cont.)';
			}
		} elseif (($NumItems-$i == 4 and !$pdf->samePage(3, CellH, '', false))
				or !$pdf->samePage(1, CellH, '', false)) {
			// needs to have room for printing the last 3 rows
			$ChangePage=true;
			$Continue=' (Cont.)';
		}

		if($runningDay != $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0]["scheduledDate"]
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
			if($runningDay != $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0]["scheduledDate"]) {
				$sesInDay=0;
			} else {
				$evInSession=-1;
// 				$pdf->dy(1);
			}

			$runningDay = $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0]["scheduledDate"];
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

		$pdf->Cell(25, CellH, ($evInSession==0 ? (new DateTime($runningDay))->format('D j M').$Continue:($evInSession==1 ? "Session ".$sesInDay:($evInSession==2 ? $Sessions[$sesCnt]['Name']:''))), 'LR'.($evInSession==0 ? 'T':''), 0, 'L', 0);
		$pdf->Cell(10, CellH, (new DateTime($rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0]["scheduledTime"]))->format('H:i'), 1, 0, 'C', 0);
		$pdf->Cell(31, CellH, $rankData["sections"][$eventCode]["meta"]["eventName"], 1, 0, 'L', 0);
		$pdf->Cell(10, CellH, $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["meta"]["phaseName"], 1, 0, 'L', 0);

		$SqlWR = "SELECT RankRanking
			FROM Rankings
			WHERE RankTournament={$_SESSION["TourId"]} AND
				RankCode='".$rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0][$isTeam ? "countryCode":"bib"]."' AND
				RankIocCode='FITA' AND
				RankTeam={$isTeam} AND
				RankEvent='{$eventCode}'";
		$wrQ = safe_r_SQL($SqlWR);
		$wrank = 'NR';
		if(safe_num_rows($wrQ)) {
			$wrank = (safe_fetch($wrQ)->RankRanking);
		}

		$pdf->Cell(7, CellH, $wrank, 1, 0, 'R', 0);
		$pdf->Cell(8, CellH, $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0]["qualRank"], 1, 0, 'R', 0);
		$pdf->Cell(34, CellH, $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0][$isTeam ? "countryName":"athlete"], 1, 0, 'L', 0);
		$pdf->Cell(8, CellH, $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0]["countryCode"], 1, 0, 'L', 0);

		$SqlWR = "SELECT RankRanking
			FROM Rankings
			WHERE RankTournament={$_SESSION["TourId"]} AND
			RankCode='".$rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0][$isTeam ? "oppCountryCode":"oppBib"]."' AND
			RankIocCode='FITA' AND
			RankTeam={$isTeam} AND
			RankEvent='{$eventCode}'";
		$wrQ = safe_r_SQL($SqlWR);
		$wrank = 'NR';
		if(safe_num_rows($wrQ)) {
			$wrank = (safe_fetch($wrQ)->RankRanking);
		}
		$pdf->Cell(7, CellH, $wrank, 1, 0, 'R', 0);
		$pdf->Cell(8, CellH, $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0]["oppQualRank"], 1, 0, 'R', 0);
		$pdf->Cell(34, CellH, $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0][$isTeam ? "oppCountryName":"oppAthlete"], 1, 0, 'L', 0);
		$pdf->Cell(8, CellH, $rankData["sections"][$eventCode]["phases"][key($rankData["sections"][$eventCode]["phases"])]["items"][0]["oppCountryCode"], 1, 1, 'L', 0);

		$lastSes=$vSes;
	}
}
$pdf->Cell(25, CellH, "", 'T', 0, 'L', 0);



$pdf->Output();