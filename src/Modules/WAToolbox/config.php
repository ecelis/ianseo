<?php

$AppMinVersionWA='2.0.0';
$AppMinVersionFE='0.0.1';

require_once(dirname(dirname(__FILE__)) . '/config.php');

$FeTargetHeader = "WA-T-";
$FETargetTail = array(
	2 => "C_SEM",
	4 => "C_SEM",
	9 => "C-10-6",
	10 => "C-10-5",
	);

$CompCode = (empty($_REQUEST['compcode']) ? '' : $_REQUEST['compcode']);

// should it be worth to send back an error to the device?
if(!$CompCode) {
	if(empty($SkipCompCode)) SendResult(array('error' => get_text('WAToolbox-NoCompCode', 'Api')));
} else {
	$CompId=getIdFromCode($CompCode);
}

function SendResult($Result) {
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json');

	echo json_encode($Result);
	exit();
}

function SaveLog($Page, $Url) {
	global $CFG;	
	if(is_array($Url)) {
		$Url = implode(',',$Url);
	}
	$fileName = date('Ymd'). ".log";
	$path = dirname(__FILE__) . "/Log/";
	file_put_contents($path . $fileName, date('c')."\t".$Page."\t".$_SERVER['REMOTE_ADDR']."\t".$Url."\n",FILE_APPEND);
}