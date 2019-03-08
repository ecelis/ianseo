<?php
$SkipCompCode=true;
require_once(dirname(__FILE__) . '/config.php');

$JsonResponse = array();
$sql = "SELECT ToCode, ToName, ToWhere, ToWhenFrom, ToWhenTo, CURDATE() as Today, ToCategory
	FROM Tournament
	ORDER BY ToWhenFrom DESC, ToCode";
$rs = safe_r_sql($sql);
while($row = safe_fetch($rs)) {
	$JsonResponse[] = array(
		"compcode"=>$row->ToCode,
		"name"=>$row->ToName,
		"where"=>$row->ToWhere,
		"whenfrom"=>$row->ToWhenFrom,
		"whento"=>$row->ToWhenTo,
		"today"=>($row->Today < $row->ToWhenFrom ? $row->ToWhenFrom : ($row->Today > $row->ToWhenTo ? $row->ToWhenTo : $row->Today)),
		"field"=>($row->ToCategory==4 || $row->ToCategory==8 ? "1" : "0")
	);
}

SendResult($JsonResponse);

?>