<?php

$SkipCompCode=true;

require_once(dirname(__FILE__) . '/config.php');

$JsonResponse=array(
	'compatible' => true,
	'ianseoversion' => ProgramVersion,
	'software' => '',
	'minswversion' => "0.0.0"
	);

if(empty($_REQUEST['version']) || empty($_REQUEST['software'])) {
	$JsonResponse['compatible']=false;
} else {
	$AppMinVersion=explode('.', "0.0.0");
	$JsonResponse['software'] = $_REQUEST['software'];
	if($_REQUEST['software']=="WA") {
		$AppMinVersion=explode('.', $AppMinVersionWA);
		$JsonResponse['minswversion'] = $AppMinVersionWA;
	} else if($_REQUEST['software']=="FE") {
		$AppMinVersion=explode('.', $AppMinVersionFE);
		$JsonResponse['minswversion'] = $AppMinVersionFE;
	}
	
	$AppMinVersion=sprintf('%03s-%03s-%04s-', $AppMinVersion[0], $AppMinVersion[1], $AppMinVersion[2]);
	$AppVersion=explode('.', $_REQUEST['version']);
	while(count($AppVersion)<3) $AppVersion[]=0;
	$AppVersion=sprintf('%03s-%03s-%04s-', $AppVersion[0], $AppVersion[1], $AppVersion[2]);

	if($AppVersion<$AppMinVersion) {
		$JsonResponse['compatible']=false;
	}
}

SendResult($JsonResponse);