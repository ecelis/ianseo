<?php
$version='2013-03-24 14:13:00';

if($on) {
	$ret['QUAL']['SCOR'][] = MENU_DIVIDER;
	$ret['QUAL']['SCOR'][] = get_text('MenuLM_GetScoreBarcode') .'|'.GetWebDirectory(__FILE__).'/GetScoreBarCode.php';
	$ret['QUAL']['SCOR'][] = get_text('MenuLM_GetScoreBarcodeReport') .'|'.GetWebDirectory(__FILE__).'/GetScoreBarCodeReport.php|||_blank';

	if(!empty($ret['ELIM'])) {
		$ret['ELIM'][] = MENU_DIVIDER;
		$ret['ELIM']['SCOR'][] = get_text('MenuLM_GetScoreBarcode') .'|'.GetWebDirectory(__FILE__).'/GetElimScoreBarCode.php';
		$ret['ELIM']['SCOR'][] = get_text('MenuLM_GetScoreBarcodeReport') .'|'.GetWebDirectory(__FILE__).'/GetScoreBarCodeReport.php|||_blank';
	}

	if(!empty($ret['FINI'])) {
		$ret['FINI'][] = MENU_DIVIDER;
		$ret['FINI']['SCOR'][] = get_text('MenuLM_Input Score') .'|'.GetWebDirectory(__FILE__).'/GetFinScoreBarCode.php';
		$ret['FINI']['SCOR'][] = get_text('MenuLM_GetScoreBarcode') .'|'.GetWebDirectory(__FILE__).'/GetFinScoreBarCode.php';
		$ret['FINI']['SCOR'][] = get_text('MenuLM_GetScoreBarcodeReport') .'|'.GetWebDirectory(__FILE__).'/GetScoreBarCodeReport.php|||_blank';
	}

	if(!empty($ret['FINT'])) {
		$ret['FINT'][] = MENU_DIVIDER;
		$ret['FINT']['SCOR'][] = get_text('MenuLM_Input Score') .'|'.GetWebDirectory(__FILE__).'/GetFinScoreBarCode.php';
		$ret['FINT']['SCOR'][] = get_text('MenuLM_GetScoreBarcode') .'|'.GetWebDirectory(__FILE__).'/GetFinScoreBarCode.php';
		$ret['FINT']['SCOR'][] = get_text('MenuLM_GetScoreBarcodeReport') .'|'.GetWebDirectory(__FILE__).'/GetScoreBarCodeReport.php|||_blank';
	}
}