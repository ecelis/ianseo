<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/Lib/CommonLib.php');

CheckTourSession(true);

$Value=array('error' => 1);

if($FopLocations=Get_Tournament_Option('FopLocations')) {
	if(!empty($_GET['row']) and $Loc=intval(substr($_GET['row'],3))) {
		unset($FopLocations[$Loc]);
		Set_Tournament_Option('FopLocations', $FopLocations);
		$Value['error']=0;
	}
}



header('Content-Type: text/xml');

echo '<response>';
foreach($Value as $fld => $data) {
	echo "<$fld><![CDATA[$data]]></$fld>";
}
echo '</response>';
