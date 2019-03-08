<?php
require_once(dirname(__FILE__) . '/config.php');
require_once('Final/Fun_MatchTotal.inc.php');

CreateTourSession($CompId);
$Event = (!empty($_REQUEST['event']) ? $_REQUEST['event'] : '');
$EventType = (!empty($_REQUEST['type']) && $_REQUEST['type']=='T' ? 1 : 0);
$MatchNo = (isset($_REQUEST['matchid']) && is_numeric($_REQUEST['matchid']) ? $_REQUEST['matchid'] : -1);
$arrowIndex = (isset($_REQUEST['arrowindex']) && is_numeric($_REQUEST['arrowindex']) ? $_REQUEST['arrowindex']+1 : 0);
$PosX = (isset($_REQUEST['posx']) && is_numeric($_REQUEST['posx']) ? $_REQUEST['posx'] : '');
$PosY = (isset($_REQUEST['posy']) && is_numeric($_REQUEST['posy']) ? $_REQUEST['posy'] : '');
$Size = (isset($_REQUEST['size']) && is_numeric($_REQUEST['size']) ? $_REQUEST['size'] : '');

if(empty($Event) || empty($arrowIndex) || $MatchNo<0)
	SendResult(array('error' => get_text('WAToolbox-MissingParams', 'Api')));

$JsonResult=array();

$tgtType=0;
$Error = 1;
$SQL = "SELECT EvFinalTargetType FROM Events WHERE EvCode='" . $Event . "' AND EvTeamEvent=$EventType AND EvTournament=$CompId";
$Rs=safe_r_sql($SQL);
if($r=safe_fetch($Rs)) {
	$tgtType = $r->EvFinalTargetType;
	$Error = 0;
}
if($_REQUEST['arrowsymbol']=='0')
	$_REQUEST['arrowsymbol']='M';

if($_REQUEST['arrowsymbol']=='10*')
	$_REQUEST['arrowsymbol']='10';

if(empty($_REQUEST['arrowsymbol'])) {
	$tmpLetter = ' ';
	$PosX='';
	$PosY='';
} else {
	$tmpLetter=GetLetterFromPrint($_REQUEST['arrowsymbol'], 'T', $tgtType);
	if($tmpLetter==' ') {
		$Error = 1;
		$_REQUEST['arrowsymbol'] = '';
	}
}
if(!$Error) {
	UpdateArrowString($MatchNo, $Event, $EventType, $tmpLetter, $arrowIndex, $arrowIndex, $CompId);
	UpdateArrowPosition($MatchNo, $Event, $EventType, intval($PosX), intval($PosY), intval($Size), $arrowIndex);
	runJack("FinArrUpdate", $CompId, array("Event"=>$Event ,"Team"=>$EventType ,"MatchNo"=>$MatchNo ,"TourId"=>$CompId));
$Error=1;
}

$JsonResult=array();
$JsonResult['error'] = $Error;
$JsonResult['event'] = $Event;
$JsonResult['type'] = ($EventType ? "T" : "I");
$JsonResult['matchid'] = strval($MatchNo);
$JsonResult['arrowindex'] = strval($arrowIndex-1);
$JsonResult['arrowsymbol']= strval($_REQUEST['arrowsymbol']);
$JsonResult['posx'] = strval($PosX);
$JsonResult['posy'] = strval($PosY);
$JsonResult['size'] = strval($Size);

SaveLog("SetArrowValue", $_SERVER["QUERY_STRING"]);

SendResult($JsonResult);
