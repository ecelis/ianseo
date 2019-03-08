<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
define("LastSeenTO",300); 

function SendResult($Result) {
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json');

	parse_str($_SERVER["QUERY_STRING"], $headerArray);
	$headerArray = array("Request"=>basename($_SERVER["SCRIPT_NAME"],".php"), "Timestamp"=>$_SERVER['REQUEST_TIME']) + $headerArray;
	echo json_encode(array("header"=>$headerArray, "data"=>$Result));
	exit();
}
