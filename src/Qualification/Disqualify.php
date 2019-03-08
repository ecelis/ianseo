<?php
/*
													- Went2Home.php -
	Ritira una persona
*/

	define('debug',false);

	require_once(dirname(dirname(__FILE__)) . '/config.php');
	require_once('Fun_Qualification.local.inc.php');
	require_once('Common/Fun_Sessions.inc.php');
	require_once('Common/Fun_FormatText.inc.php');
	require_once('Common/Lib/Obj_RankFactory.php');

	if (!CheckTourSession() || !isset($_REQUEST['Id']))
	{
		print get_text('CrackError');
		exit;
	}

	$Errore=0;
	$Atleta = 0;

	$Select = "SELECT QuClRank, IndRank from Qualifications inner join Individuals on QuId=IndId where IndTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND QuId=" . StrSafe_DB($_REQUEST['Id']) . " ";

	$Rs = safe_r_sql($Select);
	$Update="";
	$Retired=1;
	$NewStatus=0;

	if (!IsBlocked(BIT_BLOCK_QUAL)) {
		while($Row=safe_fetch($Rs)) {
			if($Row->QuClRank==9999) {
				// reverts from the disqualification
				$Update = "UPDATE Qualifications Inner Join Individuals on QuId=IndId set
						QuClRank=0,
						IndRank=0,
						IndRankFinal=0

					WHERE IndTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND QuId=" . StrSafe_DB($_REQUEST['Id']);
				$NewStatus=1;
				$Retired=0;
			} else {
				$Update = "UPDATE Qualifications Inner Join Individuals on QuId=IndId set ";
// 				for($i=1; $i<9; $i++) $Update.= "QuD{$i}Rank=0, IndD{$i}Rank=0, ";
				$Update.= "IndSO=0,
						QuClRank=9999,
						IndRank=9999,
						IndRankFinal=9999
					WHERE IndTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND QuId=" . StrSafe_DB($_REQUEST['Id']);
			}
			if (debug)
				print $Update . '<br>';

			safe_w_sql($Update);
			// if($Dist) $Errore=CalcRank($Dist); // recalculate distance?
			$Errore=($Errore or CalcRank(0)); // recalculate final rank
			$Errore=($Errore or MakeTeams(NULL, NULL));
			$Errore=($Errore or MakeTeamsAbs(NULL,null,null));
		}
	} else {
		$Errore=1;
	}

// produco l'xml di ritorno
	if (!debug)
		header('Content-Type: text/xml');

	print '<response>' . "\n";
	print '<newstatus>' . $NewStatus . '</newstatus>' . "\n";
	print '<retired>' . $Retired . '</retired>' . "\n";
	print '<error>' . ($Errore ? 1 : 0) . '</error>' . "\n";
	print '<msg><![CDATA['.($Errore ? get_text('ErrorIndTeamsRank') : get_text('RecalcIndTeamsRank')).']]></msg>';
	print '<ath>' . $_REQUEST['Id'] . '</ath>' . "\n";
	print '</response>' . "\n";
?>