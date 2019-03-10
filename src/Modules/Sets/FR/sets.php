<?php
require_once('Common/Fun_Modules.php');
$version='2017-11-23 18:13:00';

//$AllowedTypes=array(1,2,3,4,5,6,7,8,9,10,11,12,13,18);
$AllowedTypes=array(3, 6, 7, 8);

$SetType['FR']['descr']=get_text('Setup-FR', 'Install');
$SetType['FR']['types']=array();
$SetType['FR']['rules']=array();

foreach($AllowedTypes as $val) {
	$SetType['FR']['types']["$val"]=$TourTypes[$val];
}

// BUILD ONE PER TIME... When finished we can group
// 18m have championships
$SetType['FR']['rules']["6"][0]='SetAllClass';
$SetType['FR']['rules']["6"][1]='SetFRChampionshipSen';
$SetType['FR']['rules']["6"][2]='SetFRChampionshipJun';

// 70m round have several championship
$SetType['FR']['rules']["3"][0]='SetAllClass';
$SetType['FR']['rules']["3"][1]='SetFRChampsTNJ';
$SetType['FR']['rules']["3"][2]='SetFRChampionshipJun';
$SetType['FR']['rules']["3"][3]='SetFRChampsScratchR';
$SetType['FR']['rules']["3"][4]='SetFRChampsScratchC';
$SetType['FR']['rules']["3"][5]='SetFRChampsVet';
$SetType['FR']['rules']["3"][6]='SetFRChampsD1DNAP';
$SetType['FR']['rules']["3"][7]='SetFRChampsFederal';
$SetType['FR']['rules']["3"][8]='SetFRFinDRD2';

// FITA, 2x FITA, 1/2 FITA, 70m Round, 18m
//foreach(array(1, 2, 3, 4, 6, 18) as $val) {
//	$SetType['FR']['rules']["$val"]=array(
//		'SetAllClass',
//		'SetOneClass',
//		'SetJ-SClass',
//		'SetJ-CClass',
//		);
//}


//// HD (all 3 types)
//foreach(array(9, 10, 12) as $val) {
//	$SetType['FR']['rules']["$val"]=array(
//		'SetAllClass',
//		'SetJ-SClass',
//		);
//}
//
//// 3D (both types)
//foreach(array(11, 13) as $val) {
//	$SetType['FR']['rules']["$val"]=array(
//		'SetAllClass',
//		'SetOneClass',
//		);
//}
//
//
