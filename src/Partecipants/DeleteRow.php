<?php
/*
													- DeleteRow.php -
	Elimina un partecipante e ritorna il suo id
*/

	define('debug',false);

	require_once(dirname(dirname(__FILE__)) . '/config.php');
	require_once('Qualification/Fun_Qualification.local.inc.php');
	require_once('Partecipants/Fun_Partecipants.local.inc.php');

	if (!isset($_REQUEST['id']) || !CheckTourSession())
	{
		print get_text('CrackError');
		exit;
	}

	if (!IsBlocked(BIT_BLOCK_PARTICIPANT))
	{
		$indFEvent=$teamFEvent=$country=$div=$cl=$zero=null;
		$recalc=Params4Recalc($_REQUEST['id']);
		if ($recalc!==false)
		{
			$recalc=true;
			list($indFEvent,$teamFEvent,$country,$div,$cl,$zero)=$recalc;
		}

		if($Id=intval($_REQUEST['id'])) {
			if($Where=GetAccBoothEnWhere($Id, true, true)) {
				LogAccBoothQuerry("DELETE FROM Qualifications WHERE QuId=(select EnId from Entries where $Where)");
				LogAccBoothQuerry("delete from AccEntries where AEId=(select EnId from Entries where $Where)");
				LogAccBoothQuerry("delete from Photos where PhEnId=(select EnId from Entries where $Where)");
				LogAccBoothQuerry("DELETE FROM Qualifications WHERE QuId=(select EnId from Entries where $Where)");
				LogAccBoothQuerry("DELETE FROM ElabQualifications WHERE EqId=(select EnId from Entries where $Where)");
				LogAccBoothQuerry("DELETE FROM ExtraData WHERE EdId=(select EnId from Entries where $Where)");
				LogAccBoothQuerry("DELETE FROM Entries WHERE $Where");
			}

			safe_w_sql("DELETE FROM Entries WHERE EnId=$Id");
			safe_w_sql("DELETE FROM Qualifications WHERE QuId=$Id");
			safe_w_sql("delete from AccEntries where AEId=$Id");
			safe_w_sql("delete from Photos where PhEnId=$Id");
			safe_w_sql("DELETE FROM Qualifications WHERE QuId=$Id");
			safe_w_sql("DELETE FROM ElabQualifications WHERE EqId=$Id");
			safe_w_sql("DELETE FROM ExtraData WHERE EdId=$Id");

		}

	// ricalcolo
		if ($recalc)
		{
			RecalculateShootoffAndTeams($indFEvent,$teamFEvent,$country,$div,$cl,$zero);

		// rank di classe x tutte le distanze
			$q="SELECT ToNumDist FROM Tournament WHERE ToId={$_SESSION['TourId']}";
			$r=safe_r_sql($q);
			$tmpRow=safe_fetch($r);
			for ($i=0; $i<$tmpRow->ToNumDist;++$i)
			{
				CalcQualRank($i,$div.$cl);
			}

		// rifaccio gli assoluti
			$Errore=MakeIndAbs();
		}
	}

	header('Location: index.php?ord=' . $_REQUEST['ord'].'&dir='.$_REQUEST['dir'].'&AllTargets='.$_REQUEST['AllTargets']);
	exit;
?>