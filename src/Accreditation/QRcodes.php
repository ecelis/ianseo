<?php

/*
IanseoServer: '',

  enableWIFIManagement: false,
  WifiSearch: 60,
  WifiResetCounter: 5,
  WifiDELETE: false,

  WifiSSID: [],
  WifiPWD: [],

  showPictures: false,
*/

require_once(dirname(dirname(__FILE__)).'/config.php');
require_once('Common/Lib/CommonLib.php');
require_once('Common/Lib/Fun_Modules.php');

CheckTourSession(true);
checkACL(AclRoot, AclReadWrite);


if(!empty($_REQUEST['items'])) {

	// Include the main TCPDF library (search for installation path).
	require_once('Common/pdf/ResultPDF.inc.php');

	// create new PDF document
	$pdf = new ResultPDF('QrCode');//TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set style for barcode
	$style = array(
			'border' => 2,
			'vpadding' => 'auto',
			'hpadding' => 'auto',
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255)
			'module_width' => 1, // width of a single module in points
			'module_height' => 1 // height of a single module in points
	);

	foreach($_REQUEST['items']['WifiSSID'] as $k=>$v) {
		if(empty($v)) {
			unset($_REQUEST['items']['WifiSSID'][$k]);
			unset($_REQUEST['items']['WifiPWD'][$k]);
			$_REQUEST['items']['WifiSSID'] = array_values($_REQUEST['items']['WifiSSID']);
			$_REQUEST['items']['WifiPWD'] = array_values($_REQUEST['items']['WifiPWD']);
		}
	}

	for($i=count($_REQUEST['items']['WifiSSID'])-1; $i>=0; $i--) {
		if(empty($_REQUEST['items']['WifiSSID'][$i])) {
			unset($_REQUEST['items']['WifiSSID'][$i]);
			unset($_REQUEST['items']['WifiPWD'][$i]);
		}
	}

	$WiFi = array();
	for($i=0; $i<count($_REQUEST['items']['WifiSSID']); $i++) {
	    $tmp = array('ssid'=>$_REQUEST['items']['WifiSSID'][$i], 'pwd'=>$_REQUEST['items']['WifiPWD'][$i]);
		if(!empty($_REQUEST['items']['WifiPWD'][$i])) {
            $tmp['pwd'] = base64_encode($_REQUEST['items']['WifiPWD'][$i]);
		}
        $WiFi[]=$tmp;
	}

    if (empty($_REQUEST['items']['serverAddress'])) {
        $_REQUEST['items']['serverAddress'] = getMyScheme() . '://' . gethostbyname($_SERVER['HTTP_HOST']) . $CFG->ROOT_DIR;
	}
    if (substr($_REQUEST['items']['serverAddress'], -1) != '/') {
        $_REQUEST['items']['serverAddress'] = $_REQUEST['items']['serverAddress'] . '/';
	}
    if (substr($_REQUEST['items']['serverAddress'], 0, 4) != 'http') {
        $_REQUEST['items']['serverAddress'] = 'http://' . $_REQUEST['items']['serverAddress'];
	}
    if(empty($_REQUEST['items']['wifiSearchTO'])) {
        $_REQUEST['items']['wifiSearchTO'] = 60;
    } else {
        $_REQUEST['items']['wifiSearchTO'] = intval($_REQUEST['items']['wifiSearchTO']);
    }
	if(!empty($_REQUEST['items']['wifiDelete'])) {
		$_REQUEST['items']['wifiDelete'] = true;
	} else {
		$_REQUEST['items']['wifiDelete'] = false;
	}
	if(!empty($_REQUEST['items']['wifiControl'])) {
		$_REQUEST['items']['wifiControl'] = true;
	} else {
		$_REQUEST['items']['wifiControl'] = false;
	}
	if(!empty($_REQUEST['items']['accCompeting'])) {
		$_REQUEST['items']['accCompeting'] = true;
	} else {
		$_REQUEST['items']['accCompeting'] = false;
	}
    if(!empty($_REQUEST['items']['accGate'])) {
        $_REQUEST['items']['accGate'] = true;
    } else {
        $_REQUEST['items']['accGate'] = false;
    }
	if(!empty($_REQUEST['items']['accValidated'])) {
		$_REQUEST['items']['accValidated'] = true;
	} else {
		$_REQUEST['items']['accValidated'] = false;
	}
    if(!empty($_REQUEST['items']['accShowPicture'])) {
        $_REQUEST['items']['accShowPicture'] = true;
    } else {
        $_REQUEST['items']['accShowPicture'] = false;
    }

	// Setup of the parameters alone...
	setModuleParameter('AccessApp', 'QRCode-Setup', $_REQUEST['items']);
    $Code = $_REQUEST['items'];
    unset($Code['WifiSSID']);
    unset($Code['WifiPWD']);
    $Code['wifiSSID']=$WiFi;

	$Code=json_encode($Code);

	$Y=35;
	$VBlock=($pdf->getPageHeight()-$Y-30);
	$Size=min(100, $VBlock-12);
	$X=($pdf->getPageWidth()-$Size)/2;

	$ActY=$Y ;
	$pdf->SetFontSize(12);

	$pdf->SetY($ActY-6);
	$pdf->SetFont('', 'B', 20);
	$pdf->Cell(0, 6, 'Gate Control Setup', 0, 1, 'C');
	$pdf->SetFont('', '', 12);
	$pdf->write2DBarcode($Code, 'QRCODE,L', $X, $ActY+12, $Size, $Size, $style, 'N');
	$ActY+= $VBlock;
	$pdf->Ln(10);
    $pdf->Cell(0, 6, get_text('ISK-ServerUrl', 'Api') . ": " . $_REQUEST['items']['serverAddress'], 0, 1, 'L');
	if($_REQUEST['items']['wifiControl']) {
		$pdf->Cell(0, 6, get_text('ISK-enableWIFIManagement','Api'), 0, 1, 'L');
		for($i=0; $i<count($_REQUEST['items']['WifiSSID']); $i++) {
			$pdf->setX($pdf->getX()+5);
			$pdf->Cell(0, 6, get_text('ISK-WifiSSID','Api') . ": " . $_REQUEST['items']['WifiSSID'][$i], 0, 1, 'L');
		}
        $pdf->setX($pdf->getX()+5);
		$pdf->Cell(0, 6, get_text('ISK-WifiSearch','Api') . ": " . $_REQUEST['items']['wifiSearchTO'], 0, 1, 'L');

        if($_REQUEST['items']['wifiDelete']) {
			$pdf->setX($pdf->getX()+5);
			$pdf->Cell(0, 6, get_text('ISK-WifiDELETE','Api'), 0, 1, 'L');
		}
	}
    if($_REQUEST['items']['accValidated']) {
        $pdf->Cell(0, 6, get_text('ISK-onlyAccreditated','Api'), 0, 1, 'L');
    }
    if($_REQUEST['items']['accGate']) {
        $pdf->Cell(0, 6, get_text('ISK-checkGate','Api'), 0, 1, 'L');
    }
    if($_REQUEST['items']['accCompeting']) {
        $pdf->Cell(0, 6, get_text('ISK-checkCompeting','Api'), 0, 1, 'L');
    }
    if($_REQUEST['items']['accShowPicture']) {
        $pdf->Cell(0, 6, get_text('ISK-showPictures','Api'), 0, 1, 'L');
    }


// JSON String
    if(!empty($_REQUEST["JSON"])) {
        $pdf->Ln(10);
        $pdf->SetMargins(15, 10, 15);
        $pdf->Cell(0, 8, "JSON", 0, 1, 'L');
        $pdf->SetFontSize(10);
        $pdf->MultiCell(0, 6, $Code, 0, 'L');
    }
    // -------------------------------------------------------------------

	//Close and output PDF document
	$pdf->Output('QrCode.pdf', 'I');
	die();
} else {
    $_REQUEST['items'] = getModuleParameter('AccessApp', 'QRCode-Setup', array('serverAddress' => getMyScheme() . '://' . gethostbyname($_SERVER['HTTP_HOST']) . $CFG->ROOT_DIR));
}
$PAGE_TITLE=get_text('MenuLM_QrCodesGates');
$ONLOAD =' onload="showWifiPart();"';
$JS_SCRIPT=array(
		phpVars2js(array(
			'WifiSSID' => get_text('ISK-WifiSSID','Api'),
			'WifiPWD' => get_text('ISK-WifiPWD','Api'),
            'WifiTargetRange' => get_text('ISK-WifiTargetRange','Api'),
		)),
		'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/jQuery/jquery-2.1.4.min.js"></script>',
		'<script type="text/javascript" src="./QRcodes.js"></script>',
);

include('Common/Templates/head.php');

echo '<form method="get" target="qrcode">';
echo '<table class="Tabella" style="width:auto;margin:auto;">';
echo '<tr><th class="Title" colspan="3">' . get_text('Areas', 'Tournament') . '</th></tr>';

echo '<tr>';
echo '<th colspan="2">' . get_text('ISK-ServerUrl','Api') . '</th>';
echo '<td><input type="text" name="items[serverAddress]" value="' . (empty($_REQUEST['items']['serverAddress']) ? '' : $_REQUEST['items']['serverAddress']) . '"></td>';
echo '</tr>';

echo '<tr class="divider"></tr>';

echo '<tr><th class="Title" colspan="3">' . get_text('ISK-WiFi','Api') . '</th></tr>';
echo '<tr>';
echo '<th colspan="2">' . get_text('ISK-enableWIFIManagement','Api') . '</th>';
echo '<td><input type="checkbox" id="wifiControl" onClick="showWifiPart();" name="items[wifiControl]" value="1" '. (!empty($_REQUEST['items']['wifiControl']) ? 'checked="checked"' : '') .'></td>';
echo '</tr>';

echo '<tr class="hideWifi" id="wifi0_0">';
echo '<th class="Title" rowspan="2" id="th_0">1<br><a style="text-decoration: none; color:#FFFFFF" href="javascript:addWifi();">[+]</a></th>';
echo '<th>' . get_text('ISK-WifiSSID','Api') . '</th>';
echo '<td><input type="text" name="items[WifiSSID][]" value="'. (!empty($_REQUEST['items']['WifiSSID'][0]) ? $_REQUEST['items']['WifiSSID'][0] : '') .'"></td>';
echo '</tr>';
echo '<tr class="hideWifi" id="wifi1_0">';
echo '<th>' . get_text('ISK-WifiPWD','Api') . '</th>';
echo '<td><input type="text" name="items[WifiPWD][]" value="'. (!empty($_REQUEST['items']['WifiPWD'][0]) ? $_REQUEST['items']['WifiPWD'][0] : '') .'"></td>';
echo '</tr>';

if(isset($_REQUEST['items']['WifiSSID'])) {
	for($i=1; $i<count($_REQUEST['items']['WifiSSID']); $i++) {
		echo '<tr class="hideWifi" id="wifi0_'.$i.'">';
		echo '<th class="Title" rowspan="2" id="th_'.$i.'">'.($i+1).'<br><a style="text-decoration: none; color:#FFFFFF" href="javascript:delWifi('.$i.');">[-]</a></th>';
		echo '<th>' . get_text('ISK-WifiSSID','Api') . '</th>';
		echo '<td><input type="text" name="items[WifiSSID][]" value="'. (!empty($_REQUEST['items']['WifiSSID'][$i]) ? $_REQUEST['items']['WifiSSID'][$i] : '') .'"></td>';
		echo '</tr>';
		echo '<tr class="hideWifi" id="wifi1_'.$i.'">';
		echo '<th>' . get_text('ISK-WifiPWD','Api') . '</th>';
		echo '<td><input type="text" name="items[WifiPWD][]" value="'. (!empty($_REQUEST['items']['WifiPWD'][$i]) ? $_REQUEST['items']['WifiPWD'][$i] : '') .'"></td>';
		echo '</tr>';
	}
}

echo '<tr class="hideWifi">';
echo '<th colspan="2">' . get_text('ISK-WifiSearch','Api') . '</th>';
echo '<td><input type="text" name="items[wifiSearchTO]" value="'. (!empty($_REQUEST['items']['wifiSearchTO']) ? $_REQUEST['items']['wifiSearchTO'] : '60') .'"></td>';
echo '</tr>';

echo '<tr class="hideWifi">';
echo '<th colspan="2">' . get_text('ISK-WifiDELETE','Api') . '</th>';
echo '<td><input type="checkbox" name="items[wifiDelete]" value="1" '. (!empty($_REQUEST['items']['wifiDelete']) ? 'checked="checked"' : '') .'"></td>';
echo '</tr>';

echo '<tr class="divider hideWifi"></tr>';

echo '<tr><th class="Title" colspan="3">' . get_text('ISK-Options','Api') . '</th></tr>';

echo '<tr>';
echo '<th colspan="2">' . get_text('ISK-onlyAccreditated','Api') . '</th>';
echo '<td><input type="checkbox" name="items[accValidated]" value="1" ' . (!empty($_REQUEST['items']['accValidated']) ? 'checked="checked"' : '') . '"></td>';
echo '</tr>';

echo '<tr>';
echo '<th colspan="2">' . get_text('ISK-checkGate','Api') . '</th>';
echo '<td><input type="checkbox" name="items[accGate]" value="1" ' . (!empty($_REQUEST['items']['accGate']) ? 'checked="checked"' : '') . '"></td>';
echo '</tr>';


echo '<tr>';
echo '<th colspan="2">' . get_text('ISK-checkCompeting','Api') . '</th>';
echo '<td><input type="checkbox" name="items[accCompeting]" value="1" ' . (!empty($_REQUEST['items']['accCompeting']) ? 'checked="checked"' : '') . '"></td>';
echo '</tr>';

echo '<tr>';
echo '<th colspan="2">' . get_text('ISK-showPictures','Api') . '</th>';
echo '<td><input type="checkbox" name="items[accShowPicture]" value="1" ' . (!empty($_REQUEST['items']['accShowPicture']) ? 'checked="checked"' : '') . '"></td>';
echo '</tr>';

/*
echo '<tr>';
echo '<th colspan="2">' . get_text('ISK-kioskMode','Api') . '</th>';
echo '<td><input type="checkbox" name="items[kioskMode]" value="1" '. (!empty($_REQUEST['items']['kioskMode']) ? 'checked="checked"' : '') .'"></td>';
echo '</tr>';
*/
echo '<tr class="divider"></tr>';
echo '<tr colspan="2">';
echo '<td colspan="3" align="center"><input type="submit"></td>';
echo '</tr>';
echo '</table>';
echo '</form>';

//echo '<div>'.$Code.'</div>';

include('Common/Templates/tail.php');
