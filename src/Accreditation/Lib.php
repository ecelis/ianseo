<?php

function SetAccreditation($Id, $SetRap=0, $return='RicaricaOpener', $TourId=0, $AccOp=0) {
	$RicaricaOpener=false;
	if(!$TourId)
		$TourId=$_SESSION['TourId'];
	if(!$AccOp)
		$AccOp=$_SESSION['AccOp'];
	/*
	 * Devo prevenire l'insert se l'id è in stato 7.
	* Per farlo cerco lo stato del tizio.
	* Se è 7 vuol dire che uno ha cliccato sul bottone dopo aver aperto il popup e io non scrivo in db
	*/
	$Select = "SELECT EnId FROM Entries
		WHERE EnId="  . StrSafe_DB($Id) . " AND EnTournament=$TourId AND EnStatus='7' ";
	$Rs=safe_r_sql($Select);
	//TODO Patchare la query per supportare bene IpV6
	if (safe_num_rows($Rs)==0) {
		$Insert = "INSERT INTO AccEntries
			(AEId,AEOperation,AETournament,AEWhen,AEFromIp,AERapp)
			VALUES(
				$Id,"
				. StrSafe_DB($AccOp) . ","
				. StrSafe_DB($TourId) . ","
				. StrSafe_DB(date('Y-m-d H:i')) . ","
				. "INET_ATON('" . ($_SERVER['REMOTE_ADDR']!='::1' ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1') . "'), "
				. StrSafe_DB($SetRap) . ""
			. ") ON DUPLICATE KEY UPDATE "
				. "AEWhen=" . StrSafe_DB(date('Y-m-d H:i')) . ","
				. "AEFromIp=INET_ATON('" . ($_SERVER['REMOTE_ADDR']!='::1' ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1') . "') ";
		$RsIns=safe_w_sql($Insert);
		$RicaricaOpener=($return=='RicaricaOpener' ? true : (safe_w_affected_rows() ? 'AccreditationOK' : 'AccreditationTwice'));
	}
	return $RicaricaOpener;
}

function getAccrQuery($Id=0) {
	$Where=array();
	if($_SESSION['chk_Turni']) {
		$Where[]="QuSession IN (".implode(',', StrSafe_DB($_SESSION['chk_Turni'])).")";
	}
	if($Id) {
		$Where[]="EnId=$Id";
	} else {
		if(!empty($_REQUEST['txt_Cognome'])) {
			$Where[]="EnFirstName LIKE '%" . $_REQUEST['txt_Cognome'] . "%'";
		}
		if(!empty($_REQUEST['txt_Societa'])) {
			$Where[]="(CoCode LIKE '%" . $_REQUEST['txt_Societa'] . "%' OR CoName LIKE '%" . $_REQUEST['txt_Societa'] . "%')";
		}
		if(!empty($_REQUEST['RemoveAcc'])) {
			if($_SESSION['AccOp'] == -1) {
				$Where[]="PhEnId IS NULL ";
			} else {
				$Where[]="m.AEOperation IS NULL ";
			}
		}
	}
	return "Select EnId,EnTournament,EnDivision,EnClass,EnCountry,CoCode,CoName,EnCode,EnName,EnFirstName,EnStatus,
			EnIndClEvent,EnTeamClEvent,EnIndFEvent,EnTeamFEvent,EnTeamMixEvent,EnPays,QuSession,SUBSTRING(QuTargetNo,2) As TargetNo,
			m.AEOperation, PhEnId
			, ".($_SESSION['chk_Photo'] ? 'PhEnId is not null and PhPhoto!=""' : '1')." as HasPhoto
			, ".($_SESSION['chk_Paid']==1 ? 'p.AEId is not null' : '1')." as HasPaid
			, ".($_SESSION['chk_Accredited']==1 ? 'a.AEId is not null' : '1')." as IsAccredited
		FROM Entries
		LEFT JOIN Countries ON EnCountry=CoId
		INNER JOIN Qualifications ON EnId=QuId AND EnTournament=" . StrSafe_DB($_SESSION['TourId']) . "
		LEFT JOIN AccEntries m ON EnId=m.AEId AND EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND m.AEOperation=" . StrSafe_DB($_SESSION['AccOp']) . "
		LEFT JOIN AccEntries p ON EnId=p.AEId AND EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND p.AEOperation=3
		LEFT JOIN AccEntries a ON EnId=a.AEId AND EnTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND a.AEOperation=1
		LEFT JOIN AccOperationType ON m.AEOperation=AOTId
		LEFT JOIN Photos ON EnId=PhEnId
		WHERE ".implode(' AND ', $Where). "
		ORDER BY HasPhoto desc, HasPaid desc, IsAccredited desc, QuSession ASC, TargetNo ASC, EnFirstName ASC , EnName ASC , CoCode ASC ";
}


