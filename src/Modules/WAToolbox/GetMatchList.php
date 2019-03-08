<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once('Common/Lib/Obj_RankFactory.php');

	$json_array=array();

	$Event = (!empty($_GET['event']) ? $_GET['event'] : '');
	$EventType = (!empty($_GET['type']) && $_GET['type']=='T' ? 1 : 0);
	$EventPhase = (isset($_GET['phase']) && is_numeric($_GET['phase']) ? $_GET['phase'] : -1);
	
	if(empty($Event) || $EventPhase<0)
		SendResult(array('error' => get_text('WAToolbox-MissingParams', 'Api')));
	
	$options['tournament']=$CompId;
	$options['events']=array();
	$options['events'][] =  $Event . '@' . $EventPhase;

	$nameField = ($EventType ? 'countryName' : 'familyName');
	$oppNameField = ($EventType ? 'oppCountryName' : 'oppFamilyName');

	$rank=null;
	if($EventType)
		$rank=Obj_RankFactory::create('GridTeam',$options);
	else
		$rank=Obj_RankFactory::create('GridInd',$options);

	$rank->read();
	$Data=$rank->getData();
	foreach($Data['sections'] as $kSec=>$vSec) {
		foreach($vSec['phases'] as $kPh=>$vPh) {
			foreach($vPh['items'] as $kItem=>$vItem) {
				$json_array[] = Array("event"=>$Event, "type"=>($EventType ? "T":"I"), "matchid"=>$vItem['matchNo'], "leftopponent"=>$vItem[$nameField], "rightopponent"=>$vItem[$oppNameField]);
			}
		}
	}

	// Return the json structure with the callback function that is needed by the app
	SendResult(Array("matches"=>$json_array));

