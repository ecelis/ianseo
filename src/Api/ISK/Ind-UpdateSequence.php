<?php
	require_once(dirname(dirname(__FILE__)) . '/config.php');
	require_once('Common/Fun_Modules.php');

	if (!CheckTourSession())
		exit;

	$ses = (!empty($_REQUEST["session"]) ? $_REQUEST["session"] : "");
	$dist = (!empty($_REQUEST["distance"]) ? $_REQUEST["distance"] : 0);
	$end = (!empty($_REQUEST["end"]) ? $_REQUEST["end"] : 0);
	$type = 0;
	$maxDist = '';
	if($ses != "") {
		$type = substr($ses,0,1);
		if($type=='Q' || $type=='E') {
			$maxDist = substr($ses,1,1);
			$ses = substr($ses,2);
		} else
			$ses = substr($ses,1);
		setModuleParameter('ISK', 'Sequence', array("type"=>$type, "session"=>$ses, "distance"=>$dist, "maxdist"=>$maxDist, "end"=>$end));
		delModuleParameter('ISK', 'StickyEnds');
		safe_w_SQL("UPDATE IskDevices SET `IskDvState`=2 WHERE IskDvTournament=" . StrSafe_DB($_SESSION["TourId"]) . " AND `IskDvState`=1 ");
	}

	$tmp = getModuleParameter('ISK', 'Sequence', array("type"=>'', "session"=>'', "distance"=>'',  "maxdist"=>'', "end"=>''));

	header('Content-Type: text/xml');
	print '<response error="0">';
	print '<session>' . $tmp["type"] . $tmp["maxdist"] . $tmp["session"] . '</session>';
	print '<distance>' . $tmp["distance"] . '</distance>';
	print '<end>' . $tmp["end"] . '</end>';
	print '</response>';
?>