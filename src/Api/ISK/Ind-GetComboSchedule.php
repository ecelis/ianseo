<?php
	require_once(dirname(dirname(__FILE__)) . '/config.php');
	require_once('Common/Lib/Fun_Modules.php');
	if (!CheckTourSession())
		exit;

	$xml='';
	$error=0;

	$IskSequence=getModuleParameter('ISK', 'Sequence', array('type' => '', 'session'=>'', 'distance'=>0, 'maxdist'=>0, 'end'=>0));
	$MaxEnds=0;

	$Select = "(SELECT DISTINCT CONCAT(SesType,ToNumDist,SesOrder) as keyValue, SesType as Type, if(SesName='', SesOrder, SesName) as Description, IFNULL(CONCAT(SchDay, ' ', SchStart), concat('0000-00-00 00:00:', SesOrder)) as dtOrder, group_concat(DiEnds order by DiDistance) MaxEnds
			FROM Session
			INNER JOIN Tournament ON SesTournament=ToId
			LEFT JOIN DistanceInformation on DiTournament=SesTournament and DiType=SesType and DiSession=SesOrder
			LEFT JOIN Scheduler ON SchTournament=SesTournament AND SchSesType=SesType AND SchSesOrder=SesOrder
			WHERE SesTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND SesType IN ('Q','E')
			" . (isset($_REQUEST["onlyToday"]) && $_REQUEST["onlyToday"] ? "AND SchDay=UTC_DATE()" : "") ."
			GROUP BY SesOrder, SesType
		) UNION ALL (
			SELECT DISTINCT CONCAT(IF(FSTeamEvent=0,'I','T'), FSScheduledDate, FSScheduledTime) AS keyValue, FSTeamEvent as Type, CONCAT(FSScheduledDate,' ',FSScheduledTime) AS Description, CONCAT(FSScheduledDate,' ',FSScheduledTime) as dtOrder, max(if(GrPhase>4, EvElimEnds, EvFinEnds)) MaxEnds
			FROM FinSchedule
			inner join Grids on GrMatchNo=FsMatchNo
			inner join Events on EvCode=FsEvent and EvTournament=FsTournament and EvTeamEvent=FsTeamEvent
			WHERE FSTournament=" . StrSafe_DB($_SESSION['TourId']) . " and FSScheduledDate>0
			" . (isset($_REQUEST["onlyToday"]) && $_REQUEST["onlyToday"] ? "AND FSScheduledDate=UTC_DATE()" : "") ."
			GROUP BY CONCAT(IF(FSTeamEvent=0,'I','T'), FSScheduledDate, FSScheduledTime)
		) ORDER BY dtOrder ASC, Description ";
	$Rs=safe_r_sql($Select);
	if ($Rs && safe_num_rows($Rs)>0) {
		while ($myRow=safe_fetch($Rs)) {
			$MaxEnds=$myRow->MaxEnds;
			$desc='';
			$selected=($myRow->keyValue==$IskSequence['type'].$IskSequence['maxdist'].$IskSequence['session'] ? '1' : '0');
			$active='0';
			switch($myRow->Type) {
				case 'Q':
					$desc = get_text('QualRound');
					break;
				case 'E':
					$desc = get_text('Elimination');
					break;
				case '0':
					$desc = get_text('I-Session', 'Tournament');
					break;
				case '1':
					$desc = get_text('T-Session', 'Tournament');
					break;
			}
			$xml.='<schedule>
				<val selected="'.$selected.'" active="'.$active.'" maxends="'.($MaxEnds).'">' . $myRow->keyValue . '</val>
				<display><![CDATA[' . $desc . ": " . $myRow->Description . ']]></display>
			</schedule>';
		}
	}

	header('Content-Type: text/xml');
	print '<response error="' . $error . '" distance="'.$IskSequence['distance'].'" end="'.$IskSequence['end'].'">';
	print $xml;
	print '</response>';
?>