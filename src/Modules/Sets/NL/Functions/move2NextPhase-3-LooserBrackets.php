<?php
//debug_svela($coppie);

moveToNextPhase_3_LooserBrackets($coppie, $TourId);

function moveToNextPhase_3_LooserBrackets($coppie,$TourId) {

	$phases = getStandardPhases();
	foreach($coppie as $value) {
		$subEv = array();
		
		list($ev,$ph) = explode('@',$value);
		if($ph==0) {
			continue;
		}
		$phNew = $phases[array_search($ph, $phases)+1];
		$Sql = "SELECT EvCode FROM Events WHERE EvCodeParent='{$ev}' AND EvFinalFirstPhase='{$phNew}' AND EvTournament='{$TourId}' AND EvTeamEvent=0";
		$q=safe_r_SQL($Sql);
		while($r=safe_fetch($q)) {
			$subEv[] = $r->EvCode;
		}
		
		if(count($subEv)) {
		//GetMatchNo of winners
			$Sql = "SELECT fl.FinMatchNo as Looser, fl.FinAthlete as Athlete
				FROM Finals fl
				INNER JOIN Finals fw ON fl.FinEvent=fw.FinEvent AND fl.FinMatchNo=fw.FinMatchNo + IF(fl.FinMatchNo % 2,+1,-1) AND fl.FinTournament=fw.FinTournament
				INNER JOIN Grids on fl.FinMatchNo=GrMatchNo 
				WHERE fl.FinEvent='{$ev}' AND GrPhase='{$ph}' AND fl.FinTournament={$TourId} AND fw.FinWinLose=1";
//			echo $Sql . "<br>";
			$q=safe_r_SQL($Sql);
			while($r=safe_fetch($q)) {
				foreach ($subEv as $subEvent) {
					$Sql = "UPDATE Finals SET FinAthlete={$r->Athlete}, FinDateTime=NOW() 
						WHERE FinEvent='{$subEvent}' AND FinMatchNo='". intval($r->Looser/2) . "' AND FinTournament={$TourId}";
					safe_w_SQL($Sql);
//					echo $Sql . "<br>";
				}
			}
			$Sql = "UPDATE Events SET EvShootOff = 1 WHERE EvCode IN ('" . implode("','",$subEv). "')  AND EvTournament={$TourId} AND EvTeamEvent=0";
//			echo $Sql . "<br>";
			safe_w_SQL($Sql);
		}
		
	}
}
