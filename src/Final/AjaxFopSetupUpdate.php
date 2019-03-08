<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Common/Lib/CommonLib.php');

CheckTourSession(true);

$Value=array('error' => 1);

if($FopLocations=Get_Tournament_Option('FopLocations')) {
	if(!empty($_GET['Location']) and $Loc=each($_GET['Location'])) {
		$FopLocations[$Loc[0]]->Loc=$Loc[1];
		Set_Tournament_Option('FopLocations', $FopLocations);
		$Value['error']=0;
	} elseif(!empty($_GET['Start']) and $Loc=each($_GET['Start'])) {
		$FopLocations[$Loc[0]]->Tg1=$Loc[1];
		Set_Tournament_Option('FopLocations', $FopLocations);
		$Value['error']=0;
	} elseif(!empty($_GET['End']) and $Loc=each($_GET['End'])) {
		$FopLocations[$Loc[0]]->Tg2=$Loc[1];
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
