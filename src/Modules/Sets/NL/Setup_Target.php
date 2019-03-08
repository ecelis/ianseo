<?php
/*
Common Setup for "Target" Archery
*/

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once('Common/F2FGrid.inc.php');

// default Divisions
CreateStandardDivisions($TourId);

// default SubClasses
CreateStandardSubClasses($TourId);

// default Classes
CreateStandardClasses($TourId, $SubRule);

// default Distances
switch($TourType) {
	case 3:
		CreateDistance($TourId, $TourType, 'R%', '70m-1', '70m-2');
		CreateDistance($TourId, $TourType, 'C%', '50m-1', '50m-2');
		break;
	case 6:
		CreateDistance($TourId, $TourType, '%', '18m-1', '18m-2');
		break;
}

if($TourType==6 || $TourType==3) {
	// default Events
	CreateStandardEvents($TourId, $SubRule, $TourType!=6);

	// Classes in Events
	InsertStandardEvents($TourId, $SubRule, $TourType!=6);

	//Add Looser Backets if needed
	if($TourType == 3 || $SubRule==2) {
		$i=5;
		CreateEvent($TourId, $i++, 0, 0,4, 5, 5, 3, 1, 5, 3, 1, 'RH-1',  'Recurve Heren - 9e t/n 12e plaats', 1, 240, 240, 0, 0, '', '', 122, 70, 'RH');
		CreateEvent($TourId, $i++, 0, 0,2, 5, 5, 3, 1, 5, 3, 1, 'RH-2',  'Recurve Heren - 5e t/n 8e plaats', 1, 240, 240, 0, 0, '', '', 122, 70, 'RH');
		CreateEvent($TourId, $i++, 0, 0,2, 5, 5, 3, 1, 5, 3, 1, 'RH-3',  'Recurve Heren - 13e t/n 16e plaats', 1, 240, 240, 0, 0, '', '', 122, 70, 'RH-1');
		InsertClassEvent($TourId, 0, 1, 'RH-1',  'R',  'H');
		InsertClassEvent($TourId, 0, 1, 'RH-2',  'R',  'H');
		InsertClassEvent($TourId, 0, 1, 'RH-3',  'R',  'H');

		CreateEvent($TourId, $i++, 0, 0,4, 5, 5, 3, 1, 5, 3, 1, 'RD-1',  'Recurve Dames - 9e t/n 12e plaats', 1, 240, 240, 0, 0, '', '', 122, 70, 'RD');
		CreateEvent($TourId, $i++, 0, 0,2, 5, 5, 3, 1, 5, 3, 1, 'RD-2',  'Recurve Dames - 5e t/n 8e plaats', 1, 240, 240, 0, 0, '', '', 122, 70, 'RD');
		CreateEvent($TourId, $i++, 0, 0,2, 5, 5, 3, 1, 5, 3, 1, 'RD-3',  'Recurve Dames - 13e t/n 16e plaats', 1, 240, 240, 0, 0, '', '', 122, 70, 'RD-1');
		InsertClassEvent($TourId, 0, 1, 'RD-1',  'R',  'D');
		InsertClassEvent($TourId, 0, 1, 'RD-2',  'R',  'D');
		InsertClassEvent($TourId, 0, 1, 'RD-3',  'R',  'D');

		CreateEvent($TourId, $i++, 0, 0,4, 9, 5, 3, 1, 5, 3, 1, 'CH-1',  'Compound Heren - 9e t/n 12e plaats', 0, 240, 240, 0, 0, '', '', 80, 50, 'CH');
		CreateEvent($TourId, $i++, 0, 0,2, 9, 5, 3, 1, 5, 3, 1, 'CH-2',  'Compound Heren - 5e t/n 8e plaats', 0, 240, 240, 0, 0, '', '', 80, 50, 'CH');
		CreateEvent($TourId, $i++, 0, 0,2, 9, 5, 3, 1, 5, 3, 1, 'CH-3',  'Compound Heren - 13e t/n 16e plaats', 0, 240, 240, 0, 0, '', '', 80, 50, 'CH-1');
		InsertClassEvent($TourId, 0, 1, 'CH-1',  'C',  'H');
		InsertClassEvent($TourId, 0, 1, 'CH-2',  'C',  'H');
		InsertClassEvent($TourId, 0, 1, 'CH-3',  'C',  'H');

		CreateEvent($TourId, $i++, 0, 0,2, 9, 5, 3, 1, 5, 3, 1, 'CD-1',  'Compound Dames - 5e t/n 8e plaats', 0, 240, 240, 0, 0, '', '', 80, 50, 'CD');
		InsertClassEvent($TourId, 0, 1, 'CD-1',  'C',  'D');

		safe_w_sql("UPDATE Events SET EvMedals=0 WHERE EvCode IN ('RH-1','RH-2','RH-3','RD-1','RD-2','RD-3','CH-1','CH-2','CH-3','CD-1') AND EvTournament=$TourId");
	}

	// Finals & TeamFinals
	CreateFinals($TourId);
}

// Default Target
switch($TourType) {
	case 3:
		CreateTargetFace($TourId, 1, '~Default', '%', '1', 5, 122, 5, 122);
		CreateTargetFace($TourId, 2, '~DefaultCO', 'C%', '1',  9, 80, 9, 80);
		break;
	case 6:
		CreateTargetFace($TourId, 1, '~Default', '%', '1', 2, 40, 2, 40);
		CreateTargetFace($TourId, 2, '~DefaultCO', 'C%', '1', 4, 40, 4, 40);
		// optional target faces
		CreateTargetFace($TourId, 3, '~Option1', 'R%', '',  1, 40, 1, 40);
		break;

}



// popolo la griglia f2f
if($TourType == 6 || $SubRule==2) {
	safe_w_sql(insertIntoGridForF2F_NL_6_Champs($TourId));
}

// create a first distance prototype
CreateDistanceInformation($TourId, $DistanceInfoArray, 16, 4);

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
//	'ToIocCode'	=> $tourDetIocCode,
	);
UpdateTourDetails($TourId, $tourDetails);

?>