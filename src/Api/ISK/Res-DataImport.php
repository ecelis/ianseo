<?php
require_once(dirname(dirname(__FILE__)).'/config.php');
require_once(dirname(__FILE__).'/Res-ImportCommon.php');
$Error=1;

if(!CheckTourSession()) {
	header('Content-Type: text/xml');
	die('<response error="'.$Error.'"/>');
}

$Error=DoImportData();


header('Content-Type: text/xml');
die('<response error="'.$Error.'"/>');
