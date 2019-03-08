<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once('Common/Lib/Fun_Phases.inc.php');

	$json_array=array();

	$SQL="SELECT EvCode, EvEventName, EvTeamEvent, EvFinalFirstPhase FROM Events WHERE EvTournament=$CompId AND EvFinalFirstPhase!=0 order by EvTeamEvent, EvProgr";

	// Retrieve the Event List
	$q=safe_r_sql($SQL);
	while($r=safe_fetch($q)) {
		$tmpPhases = Array();
		$phases = getPhasesId($r->EvFinalFirstPhase);
		foreach ($phases as $ph) {
			$tmpPhases[]=Array("code"=>strval(bitwisePhaseId($ph)), "name"=>get_text($ph."_Phase"));
		}
		$json_array[] = Array("event"=>$r->EvCode, "name"=>$r->EvEventName, "type"=>($r->EvTeamEvent==0 ? 'I':'T') , "phases"=>$tmpPhases);
	}


	// Return the json structure with the callback function that is needed by the app
	SendResult($json_array);

