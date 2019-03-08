<?php

require_once(dirname(dirname(__FILE__)).'/config.php');
$Error=1;

if(!CheckTourSession()) {
	header('Content-Type: text/xml');
	die('<response error="'.$Error.'"/>');
}

require_once('Common/Lib/Fun_Modules.php');

$Out='';

$Sequence=$_REQUEST['ses'];
$Dist=intval($_REQUEST['dist']);
$StickyField=each($_REQUEST['sticky']);

$Sticky=getModuleParameter('ISK', 'StickyEnds', array('SeqCode'=>$Sequence, 'Distance'=>$Dist, 'Ends'=>array()));

// can only modify sticky ends in the current session and distance
if($Sticky['SeqCode']==$Sequence and $Sticky['Distance']==$Dist) {
	if($StickyField['value'] and !in_array($StickyField['key'], $Sticky['Ends'])) {
		$Sticky['Ends'][]=$StickyField['key'];
	} elseif(in_array($StickyField['key'], $Sticky['Ends'])) {
		unset($Sticky['Ends'][array_search($StickyField['key'], $Sticky['Ends'])]);
	}
	if(empty($Sticky['Ends'])) {
		delModuleParameter('ISK', 'StickyEnds');
	} else {
		setModuleParameter('ISK', 'StickyEnds', $Sticky);
	}
	$Out.='<sm><![CDATA[]]></sm>';
	$Error=0;
} else {
	$Out.='<sm><![CDATA['.get_text('StickyAlreadySet', 'Api').']]></sm>';
}

header('Content-Type: text/xml');
echo '<response error="'.$Error.'">';
echo $Out;
echo '</response>';
