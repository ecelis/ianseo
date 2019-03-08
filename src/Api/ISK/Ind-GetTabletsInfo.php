<?php
	require_once(dirname(dirname(__FILE__)) . '/config.php');

	if (!CheckTourSession())
		exit;

	$error=0;
	$xml='';

	$OrderBy='IskDvTarget+0, IskDvTargetReq, IskDvCode';

	$Colors=array('#FFFFFF', '#FFCCCC', '#FF7777', '#FF0000');

	$Select
		= "SELECT IskDevices.*, if(IskDvState=1, least(3, round((time_to_sec(utc_timestamp())-time_to_sec(IdLastSeen))/65)), 0) as Difference, time_to_sec(utc_timestamp())-time_to_sec(IdLastSeen) as Seconds
			FROM IskDevices ORDER BY IskDvTournament={$_SESSION['TourId']} desc, $OrderBy";
	$Rs=safe_r_sql($Select);
	if ($Rs && safe_num_rows($Rs)>0) {
		while ($myRow=safe_fetch($Rs)) {
			$xml.='<tablet '.
				'device="' . $myRow->IskDvDevice . '" ' .
				'tournament="' . $myRow->IskDvTournament . '" ' .
				'code="' . $myRow->IskDvCode . '" ' .
				'target="' . $myRow->IskDvTarget . '" ' .
				'reqtarget="' . $myRow->IskDvTargetReq . '" ' .
				'state="' . $myRow->IskDvState . '" ' .
				'appversion="' . $myRow->IskDvVersion . '" ' .
				'appdevversion="' . $myRow->IskDvAppVersion . '" ' .
				'battery="' . $myRow->IskDvBattery . '" ' .
				'authrequest="' . $myRow->IskDvAuthRequest . '" ' .
				'ip="' . $myRow->IskDvIpAddress . '" ' .
				'online="' . $Colors[$myRow->Difference] . '" ' .
				'seconds="' . ($myRow->IskDvTournament==$_SESSION['TourId'] ? $myRow->Seconds : '') . '" ' .
				'lastseen="' . $myRow->IdLastSeen . '"/>';
		}
	}


	header('Content-Type: text/xml');
	print '<response error="' . $error . '">';
	print $xml;
	print '</response>';
