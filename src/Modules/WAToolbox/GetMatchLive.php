<?php
	require_once(dirname(__FILE__) . '/config.php');

	$json_array=array();
	
	$Sql = "(SELECT 'I' Team, FinDateTime DateTime, FinEvent AS Event, FinMatchNo AS MatchNo
		FROM Finals 
		WHERE FinTournament=" . StrSafe_DB($CompId) . " AND FinLive='1') 
		UNION 
		(SELECT '1' Team, TfDateTime DateTime, TfEvent AS Event, TfMatchNo AS MatchNo 
		FROM TeamFinals 
		WHERE TfTournament=" . StrSafe_DB($CompId) . " AND TfLive='1') 
		ORDER BY Team, DateTime DESC, Event ASC, MatchNo ASC ";

	$Rs=safe_r_sql($Sql);
	if (safe_num_rows($Rs)<2) {
		$json_array["livematch"] = false;
	} else {
		$r=safe_fetch($Rs);
		$json_array["livematch"] = true;
		$json_array["event"] = $r->Event;
		$json_array["type"] = ($r->Team==1 ? 'T' : 'I');
		$json_array["matchid"] = $r->MatchNo;
	}
	SaveLog("GetMatchLive", $_SERVER["QUERY_STRING"]);
	// Return the json structure with the callback function that is needed by the app
	SendResult(array($json_array));

