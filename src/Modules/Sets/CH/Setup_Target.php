<?php
/*
1 	Type_FITA

$TourId is the ID of the tournament!
$SubRule is the eventual subrule (see sets.php for the order)
$TourType is the Tour Type (1)

*/

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');


// default Divisions
CreateStandardDivisions($TourId, $TourType);

// default Classes
CreateStandardClasses($TourId, $SubRule, '', $TourType);

// default Distances
switch($TourType) {
	case 1:
		CreateDistance($TourId, $TourType, 'RH', '90 m', '70 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CH', '90 m', '70 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RJH', '90 m', '70 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CJH', '90 m', '70 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RD', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CD', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RJD', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CJD', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RCH', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CCH', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RVH', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CVH', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RCD', '60 m', '50 m', '40 m', '30 m');
		CreateDistance($TourId, $TourType, 'CCD', '60 m', '50 m', '40 m', '30 m');
		CreateDistance($TourId, $TourType, 'RVD', '60 m', '50 m', '40 m', '30 m');
		CreateDistance($TourId, $TourType, 'CVD', '60 m', '50 m', '40 m', '30 m');
		CreateDistance($TourId, $TourType, 'RE_', '50 m', '40 m', '30 m', '20 m');
		CreateDistance($TourId, $TourType, 'CE_', '50 m', '40 m', '30 m', '20 m');
		CreateDistance($TourId, $TourType, 'BB_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BH_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LB_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BBJ_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BHJ_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LBJ_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BBC_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BHC_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LBC_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BBE_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BHE_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LBE_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BBV_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BHV_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LBV_', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, '%MI', '-', '-', '25 m', '20 m');
		CreateDistance($TourId, $TourType, '%PI', '-', '-', '25 m', '20 m');
		break;
	case 2:
		CreateDistance($TourId, $TourType, 'RH', '90 m', '70 m', '50 m', '30 m', '90 m', '70 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CH', '90 m', '70 m', '50 m', '30 m', '90 m', '70 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RJH', '90 m', '70 m', '50 m', '30 m', '90 m', '70 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CJH', '90 m', '70 m', '50 m', '30 m', '90 m', '70 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RD', '70 m', '60 m', '50 m', '30 m', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CD', '70 m', '60 m', '50 m', '30 m', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RJD', '70 m', '60 m', '50 m', '30 m', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CJD', '70 m', '60 m', '50 m', '30 m', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RCH', '70 m', '60 m', '50 m', '30 m', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CCH', '70 m', '60 m', '50 m', '30 m', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RVH', '70 m', '60 m', '50 m', '30 m', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'CVH', '70 m', '60 m', '50 m', '30 m', '70 m', '60 m', '50 m', '30 m');
		CreateDistance($TourId, $TourType, 'RCD', '60 m', '50 m', '40 m', '30 m', '60 m', '50 m', '40 m', '30 m');
		CreateDistance($TourId, $TourType, 'CCD', '60 m', '50 m', '40 m', '30 m', '60 m', '50 m', '40 m', '30 m');
		CreateDistance($TourId, $TourType, 'RVD', '60 m', '50 m', '40 m', '30 m', '60 m', '50 m', '40 m', '30 m');
		CreateDistance($TourId, $TourType, 'CVD', '60 m', '50 m', '40 m', '30 m', '60 m', '50 m', '40 m', '30 m');
		CreateDistance($TourId, $TourType, 'RE_', '50 m', '40 m', '30 m', '20 m', '50 m', '40 m', '30 m', '20 m');
		CreateDistance($TourId, $TourType, 'CE_', '50 m', '40 m', '30 m', '20 m', '50 m', '40 m', '30 m', '20 m');
		CreateDistance($TourId, $TourType, 'BB_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BH_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LB_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BBJ_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BHJ_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LBJ_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BBC_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BHC_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LBC_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BBE_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BHE_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LBE_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BBV_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'BHV_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, 'LBV_', '40 m', '40 m', '30 m', '30 m', '40 m', '40 m', '30 m', '30 m');
		CreateDistance($TourId, $TourType, '%MI', '-', '-', '25 m', '20 m', '-', '-', '25 m', '20 m');
		CreateDistance($TourId, $TourType, '%PI', '-', '-', '20 m', '15 m', '-', '-', '20 m', '15 m');
		break;
	case 3:
		CreateDistance($TourId, $TourType, 'RV_', '60m-1', '60m-2');
		CreateDistance($TourId, $TourType, 'R_', '70m-1', '70m-2');
		CreateDistance($TourId, $TourType, 'RJ_', '70m-1', '70m-2');
		CreateDistance($TourId, $TourType, 'RC_', '60m-1', '60m-2');
		CreateDistance($TourId, $TourType, 'RE_', '40m-1', '40m-2');

		CreateDistance($TourId, $TourType, 'CV_', '50m-1', '50m-2');
		CreateDistance($TourId, $TourType, 'C_', '50m-1', '50m-2');
		CreateDistance($TourId, $TourType, 'CJ_', '50m-1', '50m-2');
		CreateDistance($TourId, $TourType, 'CC_', '50m-1', '50m-2');
		CreateDistance($TourId, $TourType, 'CE_', '40m-1', '40m-2');

		CreateDistance($TourId, $TourType, 'BBV_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'BHV_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'LBV_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'BB_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'BH_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'LB_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'BBJ_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'BHJ_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'LBJ_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'BBC_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'BHC_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'LBC_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'BBE_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'BHE_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, 'LBE_', '30m-1', '30m-2');
		CreateDistance($TourId, $TourType, '%MI', '25m-1', '25m-2');
		CreateDistance($TourId, $TourType, '%PI', '15m-1', '15m-2');
		break;
	case 6:
		CreateDistance($TourId, $TourType, '%D', '18m-1', '18m-2');
		CreateDistance($TourId, $TourType, '%H', '18m-1', '18m-2');
		CreateDistance($TourId, $TourType, '%MI', '18m-1', '18m-2');
		CreateDistance($TourId, $TourType, '%PI', '15m-1', '15m-2');
		break;
	case 9:
	case 11:
	case 13:
		if($tourDetNumDist==2)
			CreateDistance($TourId, $TourType, '%', 'Course 1', 'Course 2');
		else
			CreateDistance($TourId, $TourType, '%', 'Course');
		break;
}

if($TourType==3 or $TourType==6) {
	// default Events
	CreateStandardEvents($TourId, $TourType, $SubRule, $TourType!=6);

	// Classes in Events
	InsertStandardEvents($TourId, $TourType, $SubRule, $TourType!=6);

	// Finals & TeamFinals
	CreateFinals($TourId);
}

// Default Target
$i=1;
switch($TourType) {
	case 1:
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^(R)[ECJV]{0,1}[HD]{1,1}', '1', 5, 122, 5, 122, 5, 80, 9, 80);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^(C)[ECJV]{0,1}[HD]{1,1}', '1', 5, 122, 5, 122, 9, 80, 9, 80);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^((LB)|(BB)|(BH))[E][HD]{1,1}', '1', 5, 122, 5, 122, 5, 122, 5, 122);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^(BB)[CJV]{0,1}[HD]{1,1}', '1', 5, 80, 5, 80, 5, 80, 5, 80);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^((LB)|(BH))[CJV]{0,1}[HD]{1,1}', '1', 5, 122, 5, 122, 5, 122, 5, 122);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-(MI|PI)$', '1', 5, 80, 5, 80, 5, 80, 5, 80);
		break;
	case 2:
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^(R)[ECJV]{0,1}[HD]{1,1}', '1', 5, 122, 5, 122, 5, 80, 9, 80, 5, 122, 5, 122, 5, 80, 9, 80);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^(C)[ECJV]{0,1}[HD]{1,1}', '1', 5, 122, 5, 122, 9, 80, 9, 80, 5, 122, 5, 122, 9, 80, 9, 80);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^((LB)|(BB)|(BH))[E][HD]{1,1}', '1', 5, 122, 5, 122, 5, 122, 5, 122, 5, 122, 5, 122, 5, 122, 5, 122);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^(BB)[CJV]{0,1}[HD]{1,1}', '1', 5, 80, 5, 80, 5, 80, 5, 80, 5, 80, 5, 80, 5, 80, 5, 80);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^((LB)|(BH))[CJV]{0,1}[HD]{1,1}', '1', 5, 122, 5, 122, 5, 122, 5, 122, 5, 122, 5, 122, 5, 122, 5, 122);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-(MI|PI)$', '1', 5, 80, 5, 80, 5, 80, 5, 80, 5, 80, 5, 80, 5, 80, 5, 80);
		break;
	case 3:
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^((R)|(LB)|(BH))[CJV]{0,1}[HD]{1,1}', '1', 5, 122, 5, 122);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^(BB)[CJV]{0,1}[HD]{1,1}', '1', 5, 80, 5, 80);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-^[C][CJV]{0,1}[HD]{1,1}', '1', 9, 80, 9, 80);

		CreateTargetFace($TourId, $i++, '~Default', 'REG-(E)[HD]{1,1}$', '1', 5, 122, 5, 122);
		CreateTargetFace($TourId, $i++, '~Default', 'REG-(MI|PI)$', '1', 5, 80, 5, 80);
		break;
	case 6:
		CreateTargetFace($TourId, $i++, 'Standard 40cm', '%', '1', 1, 40, 1, 40); // big 10
		CreateTargetFace($TourId, $i++, 'Trispot Comp 40cm', 'REG-^C[HDJVC]', '1', 4, 40, 4, 40);  // small 10
		CreateTargetFace($TourId, $i++, 'Standard 60cm', 'REG-(^BH|^L|^RE|^BBE).*[^I]$', '1', 1, 60, 1, 60);
		CreateTargetFace($TourId, $i++, 'Standard 60cm', 'CE%', '1', 3, 60, 3, 60);
		CreateTargetFace($TourId, $i++, 'Standard 80cm', 'REG-^C.I$', '1', 3, 80, 3, 80);
		CreateTargetFace($TourId, $i++, 'Standard 80cm', 'REG-^[^C].+I', '1', 1, 80, 1, 80);
		// optional target faces
		CreateTargetFace($TourId, $i++, 'Trispot Rec 40cm', 'REG-^R[HDJVC]', '',  2, 40, 2, 40);
		break;
	case 9:
		CreateTargetFace($TourId, $i++, 'Rot / Rouge', 'REG-^((R)|(C))[JV]{0,1}[HD]{1,1}', '1', 6, 0, ($tourDetNumDist==2 ? 6 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Blau / Bleu', 'REG-^((BB)|(BH))[JV]{0,1}[HD]{1,1}', '1', 6, 0, ($tourDetNumDist==2 ? 6 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Blau / Bleu', 'REG-((R)|(C))[C][HD]{1,1}$', '1', 6, 0, ($tourDetNumDist==2 ? 6 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Gelb / Jaune', 'REG-^(LB)[JV]{0,1}[HD]{1,1}', '1', 6, 0, ($tourDetNumDist==2 ? 6 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Gelb / Jaune', 'REG-((BB)|(LB)|(BH))[C][HD]{1,1}$', '1', 6, 0, ($tourDetNumDist==2 ? 6 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Gelb / Jaune', 'REG-[CR](E)[HD]{1,1}$', '1', 6, 0, ($tourDetNumDist==2 ? 6 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Grün / Vert', 'REG-(E)[HD]{1,1}$', '1', 6, 0, ($tourDetNumDist==2 ? 6 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Grün / Vert', 'REG-[^CR](E)[HD]{1,1}$', '1', 6, 0, ($tourDetNumDist==2 ? 6 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Grün / Vert', 'REG-(MI|PI)$', '1', 6, 0, ($tourDetNumDist==2 ? 6 : 0), 0);
		break;
	case 11:
	case 13:
		CreateTargetFace($TourId, $i++, 'Rot / Rouge', 'REG-^((R)|(C))[CJV]{0,1}[HD]{1,1}', '1', 8, 0, ($tourDetNumDist==2 ? 8 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Blau / Bleu', 'REG-^((LB)|(BB)|(BH))[CJV]{0,1}[HD]{1,1}', '1', 8, 0, ($tourDetNumDist==2 ? 8 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Blau / Bleu', 'REG-[CR](E)[HD]{1,1}$', '1', 8, 0, ($tourDetNumDist==2 ? 8 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Grün / Vert', 'REG-[^CR](E)[HD]{1,1}$', '1', 8, 0, ($tourDetNumDist==2 ? 8 : 0), 0);
		CreateTargetFace($TourId, $i++, 'Grün / Vert', 'REG-(MI|PI)$', '1', 8, 0, ($tourDetNumDist==2 ? 8 : 0), 0);
		break;
}

// create a first distance prototype
CreateDistanceInformation($TourId, $DistanceInfoArray, 32, 4);

// Update Tour details
$tourDetails=array(
	'ToCollation' => $tourCollation,
	'ToTypeName' => $tourDetTypeName,
	'ToNumDist' => $tourDetNumDist,
	'ToNumEnds' => $tourDetNumEnds,
	'ToMaxDistScore' => $tourDetMaxDistScore,
	'ToMaxFinIndScore' => $tourDetMaxFinIndScore,
	'ToMaxFinTeamScore' => $tourDetMaxFinTeamScore,
	'ToCategory' => $tourDetCategory,
	'ToElabTeam' => $tourDetElabTeam,
	'ToElimination' => $tourDetElimination,
	'ToGolds' => $tourDetGolds,
	'ToXNine' => $tourDetXNine,
	'ToGoldsChars' => $tourDetGoldsChars,
	'ToXNineChars' => $tourDetXNineChars,
	'ToDouble' => $tourDetDouble,
	'ToIocCode'	=> $tourDetIocCode,
	);
UpdateTourDetails($TourId, $tourDetails);

?>