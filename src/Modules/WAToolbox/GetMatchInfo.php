<?php
	require_once(dirname(__FILE__) . '/config.php');
	require_once('Common/Fun_FormatText.inc.php');
	require_once('Common/Fun_Sessions.inc.php');
	require_once('Common/Lib/Fun_Phases.inc.php');
	require_once('Common/Lib/ArrTargets.inc.php');
	require_once('Common/Lib/Obj_RankFactory.php');
	require_once('Common/Lib/Fun_Final.local.inc.php');


	$Event = (!empty($_GET['event']) ? $_GET['event'] : '');
	$EventType = (!empty($_GET['type']) && $_GET['type']=='T' ? 1 : 0);
	$MatchNo = (isset($_GET['matchid']) && is_numeric($_GET['matchid']) ? $_GET['matchid'] : -1);

	if(empty($Event) || $MatchNo<0)
		SendResult(array('error' => get_text('WAToolbox-MissingParams', 'Api')));

	$json_array = array();
	$json_array['falcoeye']="";
	$json_array['targetface']=array();
	$json_array['mode']=array();
	$json_array['archers']=array();

	//get the archers
	$options['tournament']=$CompId;
	$options['events']=$Event;
	$options['matchno']=$MatchNo;

	//$options['events'][] =  $Event . '@' . $Phase;
	$rank=null;
	if($EventType)
		$rank=Obj_RankFactory::create('GridTeam',$options);
	else
		$rank=Obj_RankFactory::create('GridInd',$options);

	$rank->read();
	$Data=$rank->getData();

	foreach($Data['sections'] as $kSec=>$vSec) {
		$json_array['targetface'] = GetTargetInfo($vSec['meta']['targetTypeId'], $vSec['meta']['targetSize']);
		$json_array['falcoeye'] = $FeTargetHeader.$vSec['meta']['targetSize'].(empty($FETargetTail[$vSec['meta']['targetTypeId']]) ? '' : $FETargetTail[$vSec['meta']['targetTypeId']]);
		foreach($vSec['phases'] as $kPh=>$vPh) {
			$objParam=getEventArrowsParams($kSec,$kPh,$EventType,$CompId);
			$json_array['mode'] = Array("matchtype"=>($vSec["meta"]["matchMode"]==1 ? "S" : "C"), "arrows"=>strval($objParam->arrows), "ends"=>strval($objParam->ends), "shootoff"=>strval($objParam->so));
			foreach($vPh['items'] as $kItem=>$vItem) {
				if($vItem['matchNo']==$MatchNo) {
					//debug_svela($vItem);
					$row_array=array();
					$row_array["event"] = $kSec;
					$row_array["type"] = ($EventType ? "T" : "I");
					$row_array["matchid"] = $vItem['matchNo'];
					$row_array["name"] = ($EventType ? $vItem['countryName'] : $vItem['athlete']);
					$row_array["shortname"] = ($EventType ? $vItem['countryCode'] : substr($vItem['familyNameUpper'],0,3) . " " . substr($vItem['givenName'],0,1));
					$row_array["placement"] = ltrim($vItem['target'],'0');
					$row_array["info1"] = $vItem['countryCode']. ', ' . $vItem['countryName'];
					$row_array["info2"] = $vSec['meta']['eventName'];
					$row_array["flag"] = "";
					if(is_file($f=$CFG->DOCUMENT_PATH.'TV/Photos/'.$CompCode.'-Fl-'.$vItem['countryCode'].'.jpg')) {
						$row_array["flag"] = "data:image/jpeg;base64," . base64_encode(file_get_contents($f));
					}
					$json_array['archers'][]=$row_array;

					$row_array=array();
					$row_array["event"] = $kSec;
					$row_array["type"] = ($EventType ? "T" : "I");
					$row_array["matchid"] = $vItem['oppMatchNo'];
					$row_array["name"] = ($EventType ? $vItem['oppCountryName'] : $vItem['oppAthlete']);
					$row_array["shortname"] = ($EventType ? $vItem['oppCountryCode'] : substr($vItem['oppFamilyNameUpper'],0,3) . " " . substr($vItem['oppGivenName'],0,1));
					$row_array["placement"] = ltrim($vItem['oppTarget'],'0');
					$row_array["info1"] = $vItem['oppCountryCode']. ', ' . $vItem['oppCountryName'];
					$row_array["info2"] =$vSec['meta']['eventName'];
					$row_array["flag"] = "";
					if(is_file($f=$CFG->DOCUMENT_PATH.'TV/Photos/'.$CompCode.'-Fl-'.$vItem['oppCountryCode'].'.jpg')) {
						$row_array["flag"] = "data:image/jpeg;base64," . base64_encode(file_get_contents($f));
					}
					$json_array['archers'][]=$row_array;

					setLiveSession($EventType, $Event, $MatchNo, $CompId, false);
					SaveLog("GetMatchInfo", $_SERVER["QUERY_STRING"]);
				}
			}
		}
	}
	// Return the json structure with the callback function that is needed by the app
	SendResult($json_array);

