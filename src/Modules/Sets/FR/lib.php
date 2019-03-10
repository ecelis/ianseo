<?php

/*

STANDARD DEFINITIONS (Target Tournaments)

*/

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = 'FRA';
if(empty($SubRule)) $SubRule='1';

function CreateStandardDivisions($TourId, $Type='FR') {
	$i=1;
	if($Type!='3D') CreateDivision($TourId, $i++, 'CL', 'Arc Classique');
	CreateDivision($TourId, $i++, 'CO', 'Arc Poulies');
	if($Type=='FIELD') {
		CreateDivision($TourId, $i++, 'BB', 'Arc Nu');
	} elseif($Type=='3D') {
		CreateDivision($TourId, $i++, 'BB', 'Arc Nu');
		CreateDivision($TourId, $i++, 'AD', 'Arc Droit');
		CreateDivision($TourId, $i++, 'AC', 'Arc Chasse');
		CreateDivision($TourId, $i++, 'TL', 'Traditionnel');
	}
}

function CreateStandardClasses($TourId, $TourType, $SubRule) {
	$i=1;
	switch($TourType) {
		case '6': // INDOORS 18
		case '7': // INDOORS 25
		case '8': // INDOORS 25+18
			switch($SubRule) {
				case '1':
					// All classes...
					CreateClass($TourId, $i++,  1, 10, 1, 'PF', 'PF,BF', 'Poussin Fille', '1', 'CL', 'PD', 'CW');
					CreateClass($TourId, $i++,  1, 10, 0, 'PH', 'PH,BH', 'Poussin Homme', '1', 'CL', 'PH', 'CM');
					CreateClass($TourId, $i++, 11, 12, 1, 'BF', 'BF,MF', 'Benjamine Fille', '1', 'CL', 'BF', 'CW');
					CreateClass($TourId, $i++, 11, 12, 0, 'BH', 'BH,MH', 'Benjamin Homme', '1', 'CL', 'BH', 'CM');
					CreateClass($TourId, $i++, 13, 14, 1, 'MF', 'MF,CF', 'Minime Fille', '1', 'BB,CL', 'MF', 'CW');
					CreateClass($TourId, $i++, 13, 14, 0, 'MH', 'MH,CH', 'Minime Homme', '1', 'BB,CL', 'MH', 'CM');
					CreateClass($TourId, $i++, 15, 17, 1, 'CF', 'CF,JF,SF', 'Cadette Fille', '1', '', 'CF', 'CW');
					CreateClass($TourId, $i++, 15, 17, 0, 'CH', 'CH,JH,SH', 'Cadet Homme', '1', '', 'CH', 'CM');
					CreateClass($TourId, $i++, 18, 20, 1, 'JF', 'JF,SF', 'Junior Fille', '1', '', 'JF', 'JW');
					CreateClass($TourId, $i++, 18, 20, 0, 'JH', 'JH,SH', 'Junior Homme', '1', '', 'JH', 'JM');
					CreateClass($TourId, $i++, 21, 49, 1, 'SF', 'SF', 'Senior Femme', '1', '', 'SF', 'W');
					CreateClass($TourId, $i++, 21, 49, 0, 'SH', 'SH', 'Senior Homme', '1', '', 'SH', 'M');
					CreateClass($TourId, $i++, 50, 59, 1, 'VF', 'VF,SF', 'Vétéran Femme', '1', '', 'VF', 'MW');
					CreateClass($TourId, $i++, 50, 59, 0, 'VH', 'VH,SH', 'Vétéran Homme', '1', '', 'VH', 'MM');
					CreateClass($TourId, $i++, 60,100, 1, 'WF', 'WF,VF,SF', 'Super Vétéran Femme', '1', '', 'WF', 'MW');
					CreateClass($TourId, $i++, 60,100, 0, 'WH', 'WH,VH,SH', 'Super Vétéran Homme', '1', '', 'WH', 'MM');
					break;
				case '2':
					// Championships Adults...
					CreateClass($TourId, $i++, 18, 20, 1, 'JF', 'JF,SF', 'Junior Fille', '1', '', 'JF', 'JW');
					CreateClass($TourId, $i++, 18, 20, 0, 'JH', 'JH,SH', 'Junior Homme', '1', '', 'JH', 'JM');
					CreateClass($TourId, $i++, 21, 49, 1, 'SF', 'SF', 'Senior Femme', '1', '', 'SF', 'W');
					CreateClass($TourId, $i++, 21, 49, 0, 'SH', 'SH', 'Senior Homme', '1', '', 'SH', 'M');
					CreateClass($TourId, $i++, 50, 59, 1, 'VF', 'VF,SF', 'Vétéran Femme', '1', '', 'VF', 'MW');
					CreateClass($TourId, $i++, 50, 59, 0, 'VH', 'VH,SH', 'Vétéran Homme', '1', '', 'VH', 'MM');
					CreateClass($TourId, $i++, 60,100, 1, 'WF', 'WF,VF,SF', 'Super Vétéran Femme', '1', '', 'WF', 'MW');
					CreateClass($TourId, $i++, 60,100, 0, 'WH', 'WH,VH,SH', 'Super Vétéran Homme', '1', '', 'WH', 'MM');
					break;
				case '3':
					// Championships Youth...
					CreateClass($TourId, $i++, 11, 12, 1, 'BF', 'BF,MF', 'Benjamine Fille', '1', 'CL', 'BF', 'CW');
					CreateClass($TourId, $i++, 11, 12, 0, 'BH', 'BH,MH', 'Benjamin Homme', '1', 'CL', 'BH', 'CM');
					CreateClass($TourId, $i++, 13, 14, 1, 'MF', 'MF,CF', 'Minime Fille', '1', 'BB,CL', 'MF', 'CW');
					CreateClass($TourId, $i++, 13, 14, 0, 'MH', 'MH,CH', 'Minime Homme', '1', 'BB,CL', 'MH', 'CM');
					CreateClass($TourId, $i++, 15, 17, 1, 'CF', 'CF,JF,SF', 'Cadette Fille', '1', '', 'CF', 'CW');
					CreateClass($TourId, $i++, 15, 17, 0, 'CH', 'CH,JH,SH', 'Cadet Homme', '1', '', 'CH', 'CM');
					CreateClass($TourId, $i++, 18, 20, 1, 'JF', 'JF,SF', 'Junior Fille', '1', '', 'JF', 'JW');
					CreateClass($TourId, $i++, 18, 20, 0, 'JH', 'JH,SH', 'Junior Homme', '1', '', 'JH', 'JM');
					break;
			}
			break;
		case '3': // 72 arrows round
			// we create all classes anyway
			CreateClass($TourId, $i++,  1, 10, 1, 'PF', 'PF,BF', 'Poussin Fille', '1', 'CL', 'PD', '');
			CreateClass($TourId, $i++,  1, 10, 0, 'PH', 'PH,BH', 'Poussin Homme', '1', 'CL', 'PH', '');
			CreateClass($TourId, $i++, 11, 12, 1, 'BF', 'BF,MF', 'Benjamine Fille', '1', 'CL', 'BF', '');
			CreateClass($TourId, $i++, 11, 12, 0, 'BH', 'BH,MH', 'Benjamin Homme', '1', 'CL', 'BH', '');
			CreateClass($TourId, $i++, 13, 14, 1, 'MF', 'MF,CF', 'Minime Fille', '1', 'CL', 'MF', '');
			CreateClass($TourId, $i++, 13, 14, 0, 'MH', 'MH,CH', 'Minime Homme', '1', 'CL', 'MH', '');
			CreateClass($TourId, $i++, 15, 17, 1, 'CF', 'CF,JF,SF', 'Cadette Fille', '1', 'CL,CO', 'CF', 'CW');
			CreateClass($TourId, $i++, 15, 17, 0, 'CH', 'CH,JH,SH', 'Cadet Homme', '1', 'CL,CO', 'CH', 'CM');
			CreateClass($TourId, $i++, 18, 20, 1, 'JF', 'JF,SF', 'Junior Fille', '1', 'CL,CO', 'JF', 'JW');
			CreateClass($TourId, $i++, 18, 20, 0, 'JH', 'JH,SH', 'Junior Homme', '1', 'CL,CO', 'JH', 'JM');
			CreateClass($TourId, $i++, 21, 49, 1, 'SF', 'SF', 'Senior Femme', '1', 'CL,CO', 'SF', 'W');
			CreateClass($TourId, $i++, 21, 49, 0, 'SH', 'SH', 'Senior Homme', '1', 'CL,CO', 'SH', 'M');
			CreateClass($TourId, $i++, 50, 59, 1, 'VF', 'VF,SF', 'Vétéran Femme', '1', 'CL,CO', 'VF', 'MW');
			CreateClass($TourId, $i++, 50, 59, 0, 'VH', 'VH,SH', 'Vétéran Homme', '1', 'CL,CO', 'VH', 'MM');
			CreateClass($TourId, $i++, 60,100, 1, 'WF', 'WF,VF,SF', 'Super Vétéran Femme', '1', 'CL,CO', 'WF', 'MW');
			CreateClass($TourId, $i++, 60,100, 0, 'WH', 'WH,VH,SH', 'Super Vétéran Homme', '1', 'CL,CO', 'WH', 'MM');
			break;
		//default:
		//	switch($SubRule) {
		//		case '1':
		//			// All classes... need to check TourCode
		//			CreateClass($TourId, $i++, 21, 49, 0, 'M', 'M', 'Homme');
		//			CreateClass($TourId, $i++, 21, 49, 1, 'W', 'W', 'Femme');
		//			CreateClass($TourId, $i++, 18, 20, 0, 'JM', 'JM,M', 'Junior Homme');
		//			CreateClass($TourId, $i++, 18, 20, 1, 'JW', 'JW,W', 'Junior Femme');
		//			CreateClass($TourId, $i++,  1, 17, 0, 'CM', 'CM,JM,M', 'Cadet Homme');
		//			CreateClass($TourId, $i++,  1, 17, 1, 'CW', 'CW,JW,W', 'Cadet Femme');
		//			CreateClass($TourId, $i++, 50,100, 0, 'MM', 'MM,M', 'Vétéran Homme');
		//			CreateClass($TourId, $i++, 50,100, 1, 'MW', 'MW,W', 'Vétéran Femme');
		//			break;
		//		case '2':
		//		case '5':
		//			CreateClass($TourId, $i++, 1,100, 0, 'M', 'M', 'Homme');
		//			CreateClass($TourId, $i++, 1,100, 1, 'W', 'W', 'Femme');
		//			break;
		//		case '3':
		//			CreateClass($TourId, $i++, 21,100, 0, 'M', 'M', 'Homme');
		//			CreateClass($TourId, $i++, 21,100, 1, 'W', 'W', 'Femme');
		//			CreateClass($TourId, $i++, 1, 20, 0, 'JM', 'JM,M', 'Junior Homme');
		//			CreateClass($TourId, $i++, 1, 20, 1, 'JW', 'JW,W', 'Junior Femme');
		//			break;
		//		case '4':
		//			CreateClass($TourId, $i++, 18, 20, 0, 'JM', 'JM,M', 'Junior Homme');
		//			CreateClass($TourId, $i++, 18, 20, 1, 'JW', 'JW,W', 'Junior Femme');
		//			CreateClass($TourId, $i++,  1, 17, 0, 'CM', 'CM,JM,M', 'Cadet Homme');
		//			CreateClass($TourId, $i++,  1, 17, 1, 'CW', 'CW,JW,W', 'Cadet Femme');
		//			break;
		//	}
	}
}

function CreateStandardEvents($TourId, $TourType, $SubRule, $Outdoor=false) {
	global $useOldRules;
	//$TargetR=($Outdoor?5:2);
	//$TargetC=($Outdoor?9:4);
	//$TargetSizeR=($Outdoor ? 122 : 40);
	//$TargetSizeC=($Outdoor ? 80 : 40);
	//$DistanceR=($Outdoor ? 70 : 18);
	//$DistanceRcm=($Outdoor ? 60 : 18);
	//$DistanceC=($Outdoor ? 50 : 18);

	$i=1;
	switch($TourType) {
		case 6: // INDOOR 18m
			$TargetR=2;
			$TargetC=4;
			$Distance=18;
			$TargetSize4=40;
			$TargetSize6=60;

			// NEVER as Team
			switch($SubRule) {
				case '2': // Championships Adults
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetR, 5, 3, 1, 5, 3, 1, 'SFCL', 'Classique Senior Femme', 1, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'SHCL', 'Classique Senior Homme', 1, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  2, $TargetR, 5, 3, 1, 5, 3, 1, 'VFCL', 'Classique Vétéran Femme', 1, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetR, 5, 3, 1, 5, 3, 1, 'VHCL', 'Classique Vétéran Homme', 1, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  4, $TargetR, 5, 3, 1, 5, 3, 1, 'WFCL', 'Classique Super Vétéran Femme', 1, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetR, 5, 3, 1, 5, 3, 1, 'WHCL', 'Classique Super Vétéran Homme', 1, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetC, 5, 3, 1, 5, 3, 1, 'SFCO', 'Poulies Senior Femme', 0, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0, 16, $TargetC, 5, 3, 1, 5, 3, 1, 'SHCO', 'Poulies Senior Homme', 0, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  2, $TargetC, 5, 3, 1, 5, 3, 1, 'VFCO', 'Poulies Vétéran Femme', 0, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  4, $TargetC, 5, 3, 1, 5, 3, 1, 'VHCO', 'Poulies Vétéran Homme', 0, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  2, $TargetC, 5, 3, 1, 5, 3, 1, 'WFCO', 'Poulies Super Vétéran Femme', 0, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  4, $TargetC, 5, 3, 1, 5, 3, 1, 'WHCO', 'Poulies Super Vétéran Homme', 0, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  4, $TargetR, 5, 3, 1, 5, 3, 1, 'AFBB', 'Arc Nu Scratch Femme', 1, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetR, 5, 3, 1, 5, 3, 1, 'AHBB', 'Arc Nu Scratch Homme', 1, 240, 240, 0, 0, '', '', $TargetSize4, $Distance);
					break;
				case '3': // Championships YOUTH
					CreateEvent($TourId, $i++, 0, 0,  4, $TargetR, 5, 3, 1, 5, 3, 1, 'BFCL', 'Classique Benjamine Fille', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize6, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetR, 5, 3, 1, 5, 3, 1, 'BHCL', 'Classique Benjamin Homme', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize6, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetR, 5, 3, 1, 5, 3, 1, 'MFCL', 'Classique Minime Fille',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize6, $Distance);
					CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'MHCL', 'Classique Minime Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize6, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetR, 5, 3, 1, 5, 3, 1, 'CFCL', 'Classique Cadette Fille',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0, 16, $TargetR, 5, 3, 1, 5, 3, 1, 'CHCL', 'Classique Cadet Homme',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetR, 5, 3, 1, 5, 3, 1, 'JFCL', 'Classique Junior Fille',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  8, $TargetR, 5, 3, 1, 5, 3, 1, 'JHCL', 'Classique Junior Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  2, $TargetC, 5, 3, 1, 5, 3, 1, 'CFCO', 'Poulies Cadette Fille',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  4, $TargetC, 5, 3, 1, 5, 3, 1, 'CHCO', 'Poulies Cadet Homme',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  2, $TargetC, 5, 3, 1, 5, 3, 1, 'JFCO', 'Poulies Junior Fille',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  4, $TargetC, 5, 3, 1, 5, 3, 1, 'JHCO', 'Poulies Junior Homme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize4, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  2, $TargetR, 5, 3, 1, 5, 3, 1, 'YFBB', 'Arc Nu Jeune Fille',       1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize6, $Distance);
					CreateEvent($TourId, $i++, 0, 0,  4, $TargetR, 5, 3, 1, 5, 3, 1, 'YHBB', 'Arc Nu Jeune Homme',       1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', $TargetSize6, $Distance);
					break;
			}
			break;
		case 3: // Outdoor championships
			switch($SubRule) {
				case 2: // TNJ
					// Individuals
					CreateEvent($TourId, $i++, 0, 0, 14, 5, 5, 3, 1, 5, 3, 1, 'BFCL', 'Classique Benjamine Fille', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30);
					CreateEvent($TourId, $i++, 0, 0, 14, 5, 5, 3, 1, 5, 3, 1, 'BHCL', 'Classique Benjamin Homme', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30);
					CreateEvent($TourId, $i++, 0, 0, 14, 5, 5, 3, 1, 5, 3, 1, 'MFCL', 'Classique Minime Fille',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40);
					CreateEvent($TourId, $i++, 0, 0, 14, 5, 5, 3, 1, 5, 3, 1, 'MHCL', 'Classique Minime Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40);
					CreateEvent($TourId, $i++, 0, 0, 14, 5, 5, 3, 1, 5, 3, 1, 'CFCL', 'Classique Cadette Fille',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60);
					CreateEvent($TourId, $i++, 0, 0, 14, 5, 5, 3, 1, 5, 3, 1, 'CHCL', 'Classique Cadet Homme',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60);
					CreateEvent($TourId, $i++, 0, 0, 14, 5, 5, 3, 1, 5, 3, 1, 'JFCL', 'Classique Junior Fille',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0, 14, 5, 5, 3, 1, 5, 3, 1, 'JHCL', 'Classique Junior Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0, 14, 9, 5, 3, 1, 5, 3, 1, 'CFCO', 'Poulies Cadette Fille',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0, 14, 9, 5, 3, 1, 5, 3, 1, 'CHCO', 'Poulies Cadet Homme',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0, 14, 9, 5, 3, 1, 5, 3, 1, 'JFCO', 'Poulies Junior Fille',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0, 14, 9, 5, 3, 1, 5, 3, 1, 'JHCO', 'Poulies Junior Homme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'BFC2', 'Classique Benjamine Fille (5-8)', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30, 'BFCL', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'BHC2', 'Classique Benjamin Homme (5-8)', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30, 'BHCL', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'MFC2', 'Classique Minime Fille (5-8)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40, 'MFCL', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'MHC2', 'Classique Minime Homme (5-8)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40, 'MHCL', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'CFC2', 'Classique Cadette Fille (5-8)',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60, 'CFCL', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'CHC2', 'Classique Cadet Homme (5-8)',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60, 'CHCL', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'JFC2', 'Classique Junior Fille (5-8)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70, 'JFCL', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'JHC2', 'Classique Junior Homme (5-8)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70, 'JHCL', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'CFP2', 'Poulies Cadette Fille (5-8)',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'CFCO', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'CHP2', 'Poulies Cadet Homme (5-8)',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'CHCO', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'JFP2', 'Poulies Junior Fille (5-8)',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'JFCO', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'JHP2', 'Poulies Junior Homme (5-8)',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'JHCO', '0', '0', 5);
					CreateEvent($TourId, $i++, 0, 0,  4, 5, 5, 3, 1, 5, 3, 1, 'BFC3', 'Classique Benjamine Fille (9-12)', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30, 'BFCL', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 5, 5, 3, 1, 5, 3, 1, 'BHC3', 'Classique Benjamin Homme (9-12)', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30, 'BHCL', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 5, 5, 3, 1, 5, 3, 1, 'MFC3', 'Classique Minime Fille (9-12)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40, 'MFCL', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 5, 5, 3, 1, 5, 3, 1, 'MHC3', 'Classique Minime Homme (9-12)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40, 'MHCL', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 5, 5, 3, 1, 5, 3, 1, 'CFC3', 'Classique Cadette Fille (9-12)',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60, 'CFCL', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 5, 5, 3, 1, 5, 3, 1, 'CHC3', 'Classique Cadet Homme (9-12)',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60, 'CHCL', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 5, 5, 3, 1, 5, 3, 1, 'JFC3', 'Classique Junior Fille (9-12)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70, 'JFCL', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 5, 5, 3, 1, 5, 3, 1, 'JHC3', 'Classique Junior Homme (9-12)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70, 'JHCL', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 9, 5, 3, 1, 5, 3, 1, 'CFP3', 'Poulies Cadette Fille (9-12)',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'CFCO', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 9, 5, 3, 1, 5, 3, 1, 'CHP3', 'Poulies Cadet Homme (9-12)',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'CHCO', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 9, 5, 3, 1, 5, 3, 1, 'JFP3', 'Poulies Junior Fille (9-12)',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'JFCO', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  4, 9, 5, 3, 1, 5, 3, 1, 'JHP3', 'Poulies Junior Homme (9-12)',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'JHCO', '0', '0', 9);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'BFC4', 'Classique Benjamine Fille (13-16)', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30, 'BFC3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'BHC4', 'Classique Benjamin Homme (13-16)', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30, 'BHC3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'MFC4', 'Classique Minime Fille (13-16)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40, 'MFC3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'MHC4', 'Classique Minime Homme (13-16)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40, 'MHC3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'CFC4', 'Classique Cadette Fille (13-16)',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60, 'CFC3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'CHC4', 'Classique Cadet Homme (13-16)',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60, 'CHC3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'JFC4', 'Classique Junior Fille (13-16)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70, 'JFC3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 5, 5, 3, 1, 5, 3, 1, 'JHC4', 'Classique Junior Homme (13-16)',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70, 'JHC3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'CFP4', 'Poulies Cadette Fille (13-16)',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'CFP3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'CHP4', 'Poulies Cadet Homme (13-16)',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'CHP3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'JFP4', 'Poulies Junior Fille (13-16)',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'JFP3', '0', '0', 13);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'JHP4', 'Poulies Junior Homme (13-16)',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50, 'JHP3', '0', '0', 13);

					// Team
					$i=1;
					CreateEvent($TourId, $i++, 1, 1,  2, 5, 4, 4, 2, 4, 4, 2, 'DMCJ', 'Double Mixte Classique Juniors',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70);
					CreateEvent($TourId, $i++, 1, 1,  2, 5, 4, 4, 2, 4, 4, 2, 'DMCC', 'Double Mixte Classique Cadets',   1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60);
					CreateEvent($TourId, $i++, 1, 1,  2, 9, 4, 4, 2, 4, 4, 2, 'DMPJ', 'Double Mixte Poulies Juniors',    0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 1, 1,  2, 9, 4, 4, 2, 4, 4, 2, 'DMPC', 'Double Mixte Poulies Cadets',     0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50);
					// always second team!
					safe_w_sql("update Events set EvTeamCreationMode=2 where EvTeamEvent=1 and EvTournament=$TourId");
					break;
				case 3: // Championships Youth
					CreateEvent($TourId, $i++, 0, 0,  4, 5, 5, 3, 1, 5, 3, 1, 'BFCL', 'Classique Benjamine Fille', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30);
					CreateEvent($TourId, $i++, 0, 0,  8, 5, 5, 3, 1, 5, 3, 1, 'BHCL', 'Classique Benjamin Homme', 1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 30);
					CreateEvent($TourId, $i++, 0, 0,  8, 5, 5, 3, 1, 5, 3, 1, 'MFCL', 'Classique Minime Fille',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40);
					CreateEvent($TourId, $i++, 0, 0, 16, 5, 5, 3, 1, 5, 3, 1, 'MHCL', 'Classique Minime Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 40);
					CreateEvent($TourId, $i++, 0, 0,  8, 5, 5, 3, 1, 5, 3, 1, 'CFCL', 'Classique Cadette Fille',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60);
					CreateEvent($TourId, $i++, 0, 0, 16, 5, 5, 3, 1, 5, 3, 1, 'CHCL', 'Classique Cadet Homme',    1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 60);
					CreateEvent($TourId, $i++, 0, 0,  8, 5, 5, 3, 1, 5, 3, 1, 'JFCL', 'Classique Junior Fille',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0, 16, 5, 5, 3, 1, 5, 3, 1, 'JHCL', 'Classique Junior Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'CFCO', 'Poulies Cadette Fille',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0,  4, 9, 5, 3, 1, 5, 3, 1, 'CHCO', 'Poulies Cadet Homme',      0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0,  2, 9, 5, 3, 1, 5, 3, 1, 'JFCO', 'Poulies Junior Fille',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0,  4, 9, 5, 3, 1, 5, 3, 1, 'JHCO', 'Poulies Junior Homme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);

					// MIXED TEAMS and Teams
					$i=1;
					CreateEvent($TourId, $i++, 1, 1,  4, 5, 4, 4, 2, 4, 4, 2, 'DMJ', 'Double Mixte Jeunes',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60);
					CreateEvent($TourId, $i++, 1, 0,  8, 5, 4, 6, 2, 4, 6, 2, 'CJH', 'Cadet/Junior Hommes',   1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60);
					CreateEvent($TourId, $i++, 1, 0,  8, 5, 4, 6, 2, 4, 6, 2, 'CJF', 'Cadet/Junior Filles',    1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60);
					CreateEvent($TourId, $i++, 1, 0,  8, 5, 4, 6, 2, 4, 6, 2, 'BM',  'Benjamin/Minime',     1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 30);

					// drop out after 1/8
					CreateEvent($TourId, $i++, 1, 0,  4, 5, 4, 6, 2, 4, 6, 2, 'CJH3', 'Cadet/Junior Hommes (9-12)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60,'CJH', '0', '0', '9');
					CreateEvent($TourId, $i++, 1, 0,  4, 5, 4, 6, 2, 4, 6, 2, 'CJF3', 'Cadet/Junior Filles (9-12)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60,'CJF', '0', '0', '9');
					CreateEvent($TourId, $i++, 1, 0,  4, 5, 4, 6, 2, 4, 6, 2, 'BM3',  'Benjamin/Minime (9-12)',     1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 30,'BM', '0', '0', '9');

					// drop out after 1/4
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 2, 4, 6, 2, 'CJH2', 'Cadet/Junior Hommes (5-8)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60,'CJH', '0', '0', '5');
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 2, 4, 6, 2, 'CJF2', 'Cadet/Junior Filles (5-8)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60,'CJF', '0', '0', '5');
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 2, 4, 6, 2, 'BM2',  'Benjamin/Minime (5-8)',     1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 30,'BM', '0', '0', '5');

					// drop out after 1/4 of losers 1/8
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 2, 4, 6, 2, 'CJH4', 'Cadet/Junior Hommes (13-16)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60,'CJH3', '0', '0', '13');
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 2, 4, 6, 2, 'CJF4', 'Cadet/Junior Filles (13-16)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 60,'CJF3', '0', '0', '13');
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 2, 4, 6, 2, 'BM4',  'Benjamin/Minime (13-16)',     1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 30,'BM3', '0', '0', '13');
					break;
				case 4: // Championships Scratch Recurve
					CreateEvent($TourId, $i++, 0, 0, 16, 5, 5, 3, 1, 5, 3, 1, 'FCL', 'Classique Scratch Femme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0, 24, 5, 5, 3, 1, 5, 3, 1, 'HCL', 'Classique Scratch Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);

					// MIXED TEAMS
					$i=1;
					CreateEvent($TourId, $i++, 1, 1,  4, 5, 4, 4, 2, 4, 4, 2, 'DMCL', 'Double Mixte Classique',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70);
					break;
				case 5: // Championships Scratch Compound
					CreateEvent($TourId, $i++, 0, 0,  16, 9, 5, 3, 1, 5, 3, 1, 'FCO', 'Poulies Scratch Femme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0,  24, 9, 5, 3, 1, 5, 3, 1, 'HCO', 'Poulies Scratch Homme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);

					// MIXED TEAMS
					$i=1;
					CreateEvent($TourId, $i++, 1, 1,  4, 9, 4, 4, 2, 4, 4, 2, 'DMCO', 'Double Mixte Poulie',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50);
					break;
				case 6: // Championships Veterans
					CreateEvent($TourId, $i++, 0, 0,  8, 5, 5, 3, 1, 5, 3, 1, 'VFCL', 'Classique Vétéran Femme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0, 16, 5, 5, 3, 1, 5, 3, 1, 'VHCL', 'Classique Vétéran Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0,  8, 5, 5, 3, 1, 5, 3, 1, 'WFCL', 'Classique Super Vétéran Femme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0,  8, 5, 5, 3, 1, 5, 3, 1, 'WHCL', 'Classique Super Vétéran Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0,  8, 9, 5, 3, 1, 5, 3, 1, 'VFCO', 'Poulies Vétéran Femme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0, 16, 9, 5, 3, 1, 5, 3, 1, 'VHCO', 'Poulies Vétéran Homme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0,  4, 9, 5, 3, 1, 5, 3, 1, 'WFCO', 'Poulies Super Vétéran Femme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0,  8, 9, 5, 3, 1, 5, 3, 1, 'WHCO', 'Poulies Super Vétéran Homme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					break;
				case 7: // D1/DNAP
					CreateEvent($TourId, $i++, 0, 0, 16, 5, 5, 3, 1, 5, 3, 1, 'FCL', 'Classique Scratch Femme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0, 16, 5, 5, 3, 1, 5, 3, 1, 'HCL', 'Classique Scratch Homme',   1, 240, MATCH_SEP_MEDALS, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 0, 0, 16, 9, 5, 3, 1, 5, 3, 1, 'FCO', 'Poulies Scratch Femme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 0, 0, 16, 9, 5, 3, 1, 5, 3, 1, 'HCO', 'Poulies Scratch Homme',     0, 240, MATCH_SEP_MEDALS, 0, 0, '', '',  80, 50);
					// teams... prepared but management to be defined after june 2017
					$i=1;
					CreateEvent($TourId, $i++, 1, 0, 0, 5, 4, 6, 3, 4, 6, 3, 'FCL', 'Equipe Classique Femme',   1, 240, MATCH_ALL_SEP, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 1, 0, 0, 5, 4, 6, 3, 4, 6, 3, 'HCL', 'Equipe Classique Homme',   1, 240, MATCH_ALL_SEP, 0, 0, '', '', 122, 70);
					CreateEvent($TourId, $i++, 1, 0, 0, 9, 4, 6, 3, 4, 6, 3, 'FCO', 'Equipe Poulies Femme',     0, 240, MATCH_ALL_SEP, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 1, 0, 0, 9, 4, 6, 3, 4, 6, 3, 'HCO', 'Equipe Poulies Homme',     0, 240, MATCH_ALL_SEP, 0, 0, '', '',  80, 50);
					break;
				case 8: // Fédéral
					// NO EVENTS!!!
					break;
				case 9: // DR/D2
					CreateEvent($TourId, $i++, 1, 0,  12, 5, 4, 6, 3, 4, 6, 3, 'DRRF', 'Equipes DR Classique Femme',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70);
					CreateEvent($TourId, $i++, 1, 0,  12, 5, 4, 6, 3, 4, 6, 3, 'DRRH', 'Equipes DR Classique Homme',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70);
					CreateEvent($TourId, $i++, 1, 0,  12, 9, 4, 6, 3, 4, 6, 3, 'DRCF', 'Equipes DR Poulies Femme',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 1, 0,  12, 9, 4, 6, 3, 4, 6, 3, 'DRCH', 'Equipes DR Poulies Homme',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50);
					CreateEvent($TourId, $i++, 1, 0,  8, 5, 4, 6, 3, 4, 6, 3, 'D2F', 'Equipes D2 Femme',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70);
					CreateEvent($TourId, $i++, 1, 0,  8, 5, 4, 6, 3, 4, 6, 3, 'D2H', 'Equipes D2 Homme',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70);

					// losers of 1/12 brackets 1st round (all byes in 1/8 so go directly to 1/4 but need to be stated as 1/8 to work)
					CreateEvent($TourId, $i++, 1, 0,  8, 5, 4, 6, 3, 4, 6, 3, 'RF17', 'Equipes DR Classique Femme (17-20)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'DRRF', '0', '8', 17);
					CreateEvent($TourId, $i++, 1, 0,  8, 5, 4, 6, 3, 4, 6, 3, 'RH17', 'Equipes DR Classique Homme (17-20)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'DRRH', '0', '8', 17);
					CreateEvent($TourId, $i++, 1, 0,  8, 9, 4, 6, 3, 4, 6, 3, 'CF17', 'Equipes DR Poulies Femme (17-20)',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50, 'DRCF', '0', '8', 17);
					CreateEvent($TourId, $i++, 1, 0,  8, 9, 4, 6, 3, 4, 6, 3, 'CH17', 'Equipes DR Poulies Homme (17-20)',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50, 'DRCH', '0', '8', 17);

					// losers of 1/12 brackets 2nd round
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'RF21', 'Equipes DR Classique Femme (21-24)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'RF17', '0', '0', 21);
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'RH21', 'Equipes DR Classique Homme (21-24)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'RH17', '0', '0', 21);
					CreateEvent($TourId, $i++, 1, 0,  2, 9, 4, 6, 3, 4, 6, 3, 'CF21', 'Equipes DR Poulies Femme (21-24)',   0, 0, MATCH_ALL_SEP, 0, 0, '', '',   80, 50, 'CF17', '0', '0', 21);
					CreateEvent($TourId, $i++, 1, 0,  2, 9, 4, 6, 3, 4, 6, 3, 'CH21', 'Equipes DR Poulies Homme (21-24)',   0, 0, MATCH_ALL_SEP, 0, 0, '', '',   80, 50, 'CH17', '0', '0', 21);

					// losers of 1/8 brackets of main stream
					CreateEvent($TourId, $i++, 1, 0,  4, 5, 4, 6, 3, 4, 6, 3, 'RF09', 'Equipes DR Classique Femme (9-12)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'DRRF', '0', '0', 9);
					CreateEvent($TourId, $i++, 1, 0,  4, 5, 4, 6, 3, 4, 6, 3, 'RH09', 'Equipes DR Classique Homme (9-12)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'DRRH', '0', '0', 9);
					CreateEvent($TourId, $i++, 1, 0,  4, 9, 4, 6, 3, 4, 6, 3, 'CF09', 'Equipes DR Poulies Femme (9-12)',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50, 'DRCF', '0', '0', 9);
					CreateEvent($TourId, $i++, 1, 0,  4, 9, 4, 6, 3, 4, 6, 3, 'CH09', 'Equipes DR Poulies Homme (9-12)',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50, 'DRCH', '0', '0', 9);
					CreateEvent($TourId, $i++, 1, 0,  4, 5, 4, 6, 3, 4, 6, 3, 'DF09', 'Equipes D2 Femme (9-12)',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'D2F', '0', '0', 9);
					CreateEvent($TourId, $i++, 1, 0,  4, 5, 4, 6, 3, 4, 6, 3, 'DH09', 'Equipes D2 Homme (9-12)',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'D2H', '0', '0', 9);

					// losers of 1/4 brackets of main stream
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'RF05', 'Equipes DR Classique Femme (5-8)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'DRRF', '0', '0', 5);
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'RH05', 'Equipes DR Classique Homme (5-8)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'DRRH', '0', '0', 5);
					CreateEvent($TourId, $i++, 1, 0,  2, 9, 4, 6, 3, 4, 6, 3, 'CF05', 'Equipes DR Poulies Femme (5-8)',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50, 'DRCF', '0', '0', 5);
					CreateEvent($TourId, $i++, 1, 0,  2, 9, 4, 6, 3, 4, 6, 3, 'CH05', 'Equipes DR Poulies Homme (5-8)',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50, 'DRCH', '0', '0', 5);
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'DF05', 'Equipes D2 Femme (5-8)',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'D2F', '0', '0', 5);
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'DH05', 'Equipes D2 Homme (5-8)',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'D2H', '0', '0', 5);

					// losers of 1/4 brackets of 1/8 losers (go for 13-16 position)
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'RF13', 'Equipes DR Classique Femme (13-16)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'RF09', '0', '0', 13);
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'RH13', 'Equipes DR Classique Homme (13-16)', 1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'RH09', '0', '0', 13);
					CreateEvent($TourId, $i++, 1, 0,  2, 9, 4, 6, 3, 4, 6, 3, 'CF13', 'Equipes DR Poulies Femme (13-16)',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50, 'CF09', '0', '0', 13);
					CreateEvent($TourId, $i++, 1, 0,  2, 9, 4, 6, 3, 4, 6, 3, 'CH13', 'Equipes DR Poulies Homme (13-16)',  0, 0, MATCH_ALL_SEP, 0, 0, '', '',  80, 50, 'CH09', '0', '0', 13);
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'DF13', 'Equipes D2 Femme (13-16)',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'DF09', '0', '0', 13);
					CreateEvent($TourId, $i++, 1, 0,  2, 5, 4, 6, 3, 4, 6, 3, 'DH13', 'Equipes D2 Homme (13-16)',  1, 0, MATCH_ALL_SEP, 0, 0, '', '',  122, 70, 'DH09', '0', '0', 13);
					break;
			}
			break;
	}
}

function InsertStandardEvents($TourId, $TourType, $SubRule) {
	switch($TourType) {
		case '6':
			switch($SubRule) {
				case '2':
					InsertClassEvent($TourId, 0, 1,'SHCL', 'CL', 'SH');
					InsertClassEvent($TourId, 0, 1,'SFCL', 'CL', 'SF');
					InsertClassEvent($TourId, 0, 1,'VHCL', 'CL', 'VH');
					InsertClassEvent($TourId, 0, 1,'VFCL', 'CL', 'VF');
					InsertClassEvent($TourId, 0, 1,'WHCL', 'CL', 'WH');
					InsertClassEvent($TourId, 0, 1,'WFCL', 'CL', 'WF');
					InsertClassEvent($TourId, 0, 1,'SHCO', 'CO', 'SH');
					InsertClassEvent($TourId, 0, 1,'SFCO', 'CO', 'SF');
					InsertClassEvent($TourId, 0, 1,'VHCO', 'CO', 'VH');
					InsertClassEvent($TourId, 0, 1,'VFCO', 'CO', 'VF');
					InsertClassEvent($TourId, 0, 1,'WHCO', 'CO', 'WH');
					InsertClassEvent($TourId, 0, 1,'WFCO', 'CO', 'WF');
					InsertClassEvent($TourId, 0, 1,'AHBB', 'BB', 'SH');
					InsertClassEvent($TourId, 0, 1,'AFBB', 'BB', 'SF');
					InsertClassEvent($TourId, 0, 1,'AHBB', 'BB', 'VH');
					InsertClassEvent($TourId, 0, 1,'AFBB', 'BB', 'VF');
					InsertClassEvent($TourId, 0, 1,'AHBB', 'BB', 'WH');
					InsertClassEvent($TourId, 0, 1,'AFBB', 'BB', 'WF');
					break;
				case '3': // Championships YOUTH
					$TargetSizeB=60;
					InsertClassEvent($TourId, 0, 1, 'JHCL', 'CL','JH');
					InsertClassEvent($TourId, 0, 1, 'JFCL', 'CL','JF');
					InsertClassEvent($TourId, 0, 1, 'CHCL', 'CL','CH');
					InsertClassEvent($TourId, 0, 1, 'CFCL', 'CL','CF');
					InsertClassEvent($TourId, 0, 1, 'MHCL', 'CL','MH');
					InsertClassEvent($TourId, 0, 1, 'MFCL', 'CL','MF');
					InsertClassEvent($TourId, 0, 1, 'BHCL', 'CL','BH');
					InsertClassEvent($TourId, 0, 1, 'BFCL', 'CL','BF');
					InsertClassEvent($TourId, 0, 1, 'JHCO', 'CO','JH');
					InsertClassEvent($TourId, 0, 1, 'JFCO', 'CO','JF');
					InsertClassEvent($TourId, 0, 1, 'CHCO', 'CO','CH');
					InsertClassEvent($TourId, 0, 1, 'CFCO', 'CO','CF');
					InsertClassEvent($TourId, 0, 1, 'YHBB', 'BB','JH');
					InsertClassEvent($TourId, 0, 1, 'YFBB', 'BB','JF');
					InsertClassEvent($TourId, 0, 1, 'YHBB', 'BB','CH');
					InsertClassEvent($TourId, 0, 1, 'YFBB', 'BB','CF');
					InsertClassEvent($TourId, 0, 1, 'YHBB', 'BB','MH');
					InsertClassEvent($TourId, 0, 1, 'YFBB', 'BB','MF');
					break;
			}
			break;
		case 3:
			switch($SubRule) {
				case 2: // TNJ
					InsertClassEvent($TourId, 0, 1, 'JHCL', 'CL','JH');
					InsertClassEvent($TourId, 0, 1, 'JFCL', 'CL','JF');
					InsertClassEvent($TourId, 0, 1, 'CHCL', 'CL','CH');
					InsertClassEvent($TourId, 0, 1, 'CFCL', 'CL','CF');
					InsertClassEvent($TourId, 0, 1, 'MHCL', 'CL','MH');
					InsertClassEvent($TourId, 0, 1, 'MFCL', 'CL','MF');
					InsertClassEvent($TourId, 0, 1, 'BHCL', 'CL','BH');
					InsertClassEvent($TourId, 0, 1, 'BFCL', 'CL','BF');
					InsertClassEvent($TourId, 0, 1, 'JHCO', 'CO','JH');
					InsertClassEvent($TourId, 0, 1, 'JFCO', 'CO','JF');
					InsertClassEvent($TourId, 0, 1, 'CHCO', 'CO','CH');
					InsertClassEvent($TourId, 0, 1, 'CFCO', 'CO','CF');
					// Mixed Team
					InsertClassEvent($TourId, 1, 1, 'DMCJ', 'CL','JF');
					InsertClassEvent($TourId, 1, 1, 'DMCC', 'CL','CF');
					InsertClassEvent($TourId, 1, 1, 'DMPJ', 'CO','JF');
					InsertClassEvent($TourId, 1, 1, 'DMPC', 'CO','CF');
					InsertClassEvent($TourId, 2, 1, 'DMCJ', 'CL','JH');
					InsertClassEvent($TourId, 2, 1, 'DMCC', 'CL','CH');
					InsertClassEvent($TourId, 2, 1, 'DMPJ', 'CO','JH');
					InsertClassEvent($TourId, 2, 1, 'DMPC', 'CO','CH');
					break;
				case 3: // Championship Youth
					InsertClassEvent($TourId, 0, 1, 'JHCL', 'CL','JH');
					InsertClassEvent($TourId, 0, 1, 'JFCL', 'CL','JF');
					InsertClassEvent($TourId, 0, 1, 'CHCL', 'CL','CH');
					InsertClassEvent($TourId, 0, 1, 'CFCL', 'CL','CF');
					InsertClassEvent($TourId, 0, 1, 'MHCL', 'CL','MH');
					InsertClassEvent($TourId, 0, 1, 'MFCL', 'CL','MF');
					InsertClassEvent($TourId, 0, 1, 'BHCL', 'CL','BH');
					InsertClassEvent($TourId, 0, 1, 'BFCL', 'CL','BF');
					InsertClassEvent($TourId, 0, 1, 'JHCO', 'CO','JH');
					InsertClassEvent($TourId, 0, 1, 'JFCO', 'CO','JF');
					InsertClassEvent($TourId, 0, 1, 'CHCO', 'CO','CH');
					InsertClassEvent($TourId, 0, 1, 'CFCO', 'CO','CF');
					// Mixed Team
					InsertClassEvent($TourId, 1, 1, 'DMJ', 'CL','JF');
					InsertClassEvent($TourId, 1, 1, 'DMJ', 'CL','CF');
					InsertClassEvent($TourId, 2, 1, 'DMJ', 'CL','JH');
					InsertClassEvent($TourId, 2, 1, 'DMJ', 'CL','CH');
					// Teams
					InsertClassEvent($TourId, 1, 3, 'CJH', 'CL','JH');
					InsertClassEvent($TourId, 1, 3, 'CJH', 'CL','CH');
					InsertClassEvent($TourId, 1, 3, 'CJF', 'CL','JF');
					InsertClassEvent($TourId, 1, 3, 'CJF', 'CL','CF');
					InsertClassEvent($TourId, 1, 3, 'BM', 'CL','BF');
					InsertClassEvent($TourId, 1, 3, 'BM', 'CL','BH');
					InsertClassEvent($TourId, 1, 3, 'BM', 'CL','MF');
					InsertClassEvent($TourId, 1, 3, 'BM', 'CL','MH');
					break;
				case 4: // Championships Scratch Recurve
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','CF');
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','JF');
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','SF');
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','VF');
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','WF');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','CH');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','JH');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','SH');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','VH');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','WH');
					// Mixed Team
					InsertClassEvent($TourId, 1, 1, 'DMCL', 'CL','CF');
					InsertClassEvent($TourId, 1, 1, 'DMCL', 'CL','JF');
					InsertClassEvent($TourId, 1, 1, 'DMCL', 'CL','SF');
					InsertClassEvent($TourId, 1, 1, 'DMCL', 'CL','VF');
					InsertClassEvent($TourId, 1, 1, 'DMCL', 'CL','WF');
					InsertClassEvent($TourId, 2, 1, 'DMCL', 'CL','CH');
					InsertClassEvent($TourId, 2, 1, 'DMCL', 'CL','JH');
					InsertClassEvent($TourId, 2, 1, 'DMCL', 'CL','SH');
					InsertClassEvent($TourId, 2, 1, 'DMCL', 'CL','VH');
					InsertClassEvent($TourId, 2, 1, 'DMCL', 'CL','WH');
					break;
				case 5: // Championships Scratch Compound
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','CF');
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','JF');
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','SF');
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','VF');
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','WF');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','CH');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','JH');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','SH');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','VH');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','WH');
					// Mixed Team
					InsertClassEvent($TourId, 1, 1, 'DMCO', 'CO','CF');
					InsertClassEvent($TourId, 1, 1, 'DMCO', 'CO','JF');
					InsertClassEvent($TourId, 1, 1, 'DMCO', 'CO','SF');
					InsertClassEvent($TourId, 1, 1, 'DMCO', 'CO','VF');
					InsertClassEvent($TourId, 1, 1, 'DMCO', 'CO','WF');
					InsertClassEvent($TourId, 2, 1, 'DMCO', 'CO','CH');
					InsertClassEvent($TourId, 2, 1, 'DMCO', 'CO','JH');
					InsertClassEvent($TourId, 2, 1, 'DMCO', 'CO','SH');
					InsertClassEvent($TourId, 2, 1, 'DMCO', 'CO','VH');
					InsertClassEvent($TourId, 2, 1, 'DMCO', 'CO','WH');
					break;
				case 6: // Championship Veteran
					InsertClassEvent($TourId, 0, 1, 'VFCL', 'CL','VF');
					InsertClassEvent($TourId, 0, 1, 'VHCL', 'CL','VH');
					InsertClassEvent($TourId, 0, 1, 'WFCL', 'CL','WF');
					InsertClassEvent($TourId, 0, 1, 'WHCL', 'CL','WH');
					InsertClassEvent($TourId, 0, 1, 'VFCO', 'CO','VF');
					InsertClassEvent($TourId, 0, 1, 'VHCO', 'CO','VH');
					InsertClassEvent($TourId, 0, 1, 'WFCO', 'CO','WF');
					InsertClassEvent($TourId, 0, 1, 'WHCO', 'CO','WH');
					break;
				case 7: // D1/DNAP... teams next year
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','CF');
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','JF');
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','SF');
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','VF');
					InsertClassEvent($TourId, 0, 1, 'FCL', 'CL','WF');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','CH');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','JH');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','SH');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','VH');
					InsertClassEvent($TourId, 0, 1, 'HCL', 'CL','WH');
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','CF');
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','JF');
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','SF');
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','VF');
					InsertClassEvent($TourId, 0, 1, 'FCO', 'CO','WF');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','CH');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','JH');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','SH');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','VH');
					InsertClassEvent($TourId, 0, 1, 'HCO', 'CO','WH');
					// Teams
					InsertClassEvent($TourId, 1, 3, 'FCL', 'CL','CF');
					InsertClassEvent($TourId, 1, 3, 'FCL', 'CL','JF');
					InsertClassEvent($TourId, 1, 3, 'FCL', 'CL','SF');
					InsertClassEvent($TourId, 1, 3, 'FCL', 'CL','VF');
					InsertClassEvent($TourId, 1, 3, 'FCL', 'CL','WF');
					InsertClassEvent($TourId, 1, 3, 'HCL', 'CL','CH');
					InsertClassEvent($TourId, 1, 3, 'HCL', 'CL','JH');
					InsertClassEvent($TourId, 1, 3, 'HCL', 'CL','SH');
					InsertClassEvent($TourId, 1, 3, 'HCL', 'CL','VH');
					InsertClassEvent($TourId, 1, 3, 'HCL', 'CL','WH');
					InsertClassEvent($TourId, 1, 3, 'FCO', 'CO','CF');
					InsertClassEvent($TourId, 1, 3, 'FCO', 'CO','JF');
					InsertClassEvent($TourId, 1, 3, 'FCO', 'CO','SF');
					InsertClassEvent($TourId, 1, 3, 'FCO', 'CO','VF');
					InsertClassEvent($TourId, 1, 3, 'FCO', 'CO','WF');
					InsertClassEvent($TourId, 1, 3, 'HCO', 'CO','CH');
					InsertClassEvent($TourId, 1, 3, 'HCO', 'CO','JH');
					InsertClassEvent($TourId, 1, 3, 'HCO', 'CO','SH');
					InsertClassEvent($TourId, 1, 3, 'HCO', 'CO','VH');
					InsertClassEvent($TourId, 1, 3, 'HCO', 'CO','WH');
					break;
				case 8: // Fédéral
					// no events
					break;
				case 9: // DR/D2
					InsertClassEvent($TourId, 1, 3, 'DRRF', 'CL','CF');
					InsertClassEvent($TourId, 1, 3, 'DRRF', 'CL','JF');
					InsertClassEvent($TourId, 1, 3, 'DRRF', 'CL','SF');
					InsertClassEvent($TourId, 1, 3, 'DRRF', 'CL','VF');
					InsertClassEvent($TourId, 1, 3, 'DRRF', 'CL','WF');
					InsertClassEvent($TourId, 1, 3, 'DRCF', 'CO','CF');
					InsertClassEvent($TourId, 1, 3, 'DRCF', 'CO','JF');
					InsertClassEvent($TourId, 1, 3, 'DRCF', 'CO','SF');
					InsertClassEvent($TourId, 1, 3, 'DRCF', 'CO','VF');
					InsertClassEvent($TourId, 1, 3, 'DRCF', 'CO','WF');
					InsertClassEvent($TourId, 1, 3, 'D2F', 'CL','CF');
					InsertClassEvent($TourId, 1, 3, 'D2F', 'CL','JF');
					InsertClassEvent($TourId, 1, 3, 'D2F', 'CL','SF');
					InsertClassEvent($TourId, 1, 3, 'D2F', 'CL','VF');
					InsertClassEvent($TourId, 1, 3, 'D2F', 'CL','WF');
					InsertClassEvent($TourId, 1, 3, 'DRRH', 'CL','CH');
					InsertClassEvent($TourId, 1, 3, 'DRRH', 'CL','JH');
					InsertClassEvent($TourId, 1, 3, 'DRRH', 'CL','SH');
					InsertClassEvent($TourId, 1, 3, 'DRRH', 'CL','VH');
					InsertClassEvent($TourId, 1, 3, 'DRRH', 'CL','WH');
					InsertClassEvent($TourId, 1, 3, 'DRCH', 'CO','CH');
					InsertClassEvent($TourId, 1, 3, 'DRCH', 'CO','JH');
					InsertClassEvent($TourId, 1, 3, 'DRCH', 'CO','SH');
					InsertClassEvent($TourId, 1, 3, 'DRCH', 'CO','VH');
					InsertClassEvent($TourId, 1, 3, 'DRCH', 'CO','WH');
					InsertClassEvent($TourId, 1, 3, 'D2H', 'CL','CH');
					InsertClassEvent($TourId, 1, 3, 'D2H', 'CL','JH');
					InsertClassEvent($TourId, 1, 3, 'D2H', 'CL','SH');
					InsertClassEvent($TourId, 1, 3, 'D2H', 'CL','VH');
					InsertClassEvent($TourId, 1, 3, 'D2H', 'CL','WH');
					break;
			}
			break;
	}
}

/*

FIELD DEFINITIONS (Target Tournaments)

*/

require_once(dirname(__FILE__).'/lib-Field.php');

/*

3D DEFINITIONS (Target Tournaments)

*/

require_once(dirname(__FILE__).'/lib-3D.php');

