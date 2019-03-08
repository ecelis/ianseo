<?php
$SkipDeviceCheck=true;
$SkipCompCode=true;
require_once(dirname(__FILE__) . '/config.php');

$JsonResponse=array("auth"=>"NO","code"=>"");

$q=safe_r_sql("SELECT * FROM IskDevices WHERE IskDvDevice='{$DeviceId}'");
if(safe_num_rows($q)==0) {
	$iskCode="a0";
	$q=safe_r_sql("SELECT IskDvCode FROM IskDevices ORDER BY IskDvCode DESC");
	if(safe_num_rows($q)) {
		$r=safe_fetch($q);
		$iskCode = base_convert(base_convert($r->IskDvCode,36,10)+1,10,36);
	}
	safe_w_SQL("INSERT INTO IskDevices
		(IskDvTournament, IskDvDevice, IskDvCode, IskDvAppVersion, IskDvState, IskDvIpAddress, IdLastSeen, IskDvAuthRequest) VALUES
		('0', '{$DeviceId}', '{$iskCode}', 1, 0, '" . $_SERVER["REMOTE_ADDR"] . "', '".date('Y-m-d H:i:s')."', 1)");
	$JsonResponse["code"] = $iskCode;
} else {
	safe_w_SQL("UPDATE IskDevices SET
		IskDvIpAddress='" . $_SERVER["REMOTE_ADDR"] . "', IskDvAppVersion=1, IdLastSeen='".date('Y-m-d H:i:s')."', IskDvAuthRequest=1
		WHERE IskDvDevice='{$DeviceId}'");
	$r = safe_fetch($q);
	$JsonResponse["code"] = $r->IskDvCode;
	if($r->IskDvState!=0)
		$JsonResponse["auth"] = "OK";
}

SendResult($JsonResponse);