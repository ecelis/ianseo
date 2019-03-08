<?php

/*

STANDARD THINGS

*/

// these go here as it is a "global" definition, used or not
$tourCollation = '';
$tourDetIocCode = 'SUI';
if(empty($SubRule)) $SubRule='1';

global $ChDivisions,$ChClasses;

$ChDivisions=array(
	'R' => 'Recurve',
	'C' => 'Compound',
	'BB'=> 'Barebow',
	'BH'=> 'Bowhunter',
	'LB'=> 'Longbow',
	'PR' => 'Recurve Para',
	'PC' => 'Compound Para',
	);

$ChClasses=array(
	'H' => 'Hommes',
	'D' => 'Dames',
	'JH'=> 'Junior Hommes',
	'JD'=> 'Junior Dames',
	'VH'=> 'Master Hommes',
	'VD'=> 'Master Dames',
	'CH'=> 'Cadet Hommes',
	'CD'=> 'Cadet Dames',
	'EH'=> 'Jeunesse Hommes',
	'ED'=> 'Jeunesse Dames',
	'MI'=> 'Mini',
	'PI'=> 'Piccolo',
	);

function CreateStandardDivisions($TourId, $Type=1, $SubRule=0) {
	global $ChDivisions;
	$i=1;
	foreach($ChDivisions as $C => $D) {
		CreateDivision($TourId, $i++, $C, $D);
	}
	CreateDivision($TourId, $i++, 'GR', 'Guests Recurve');
	CreateDivision($TourId, $i++, 'GC', 'Guests Compound');
	CreateDivision($TourId, $i++, 'GB', 'Guests without sight');
}

function CreateStandardClasses($TourId, $SubRule, $Field='', $Type=0) {
	$i=1;
	CreateClass($TourId, $i++, 21, 49, 0, 'H', 'H', 'Hommes');
	CreateClass($TourId, $i++, 21, 49, 1, 'D', 'D', 'Dames');
	CreateClass($TourId, $i++, 18, 20, 0, 'JH', 'JH,H', 'Junior Hommes');
	CreateClass($TourId, $i++, 18, 20, 1, 'JD', 'JD,D', 'Junior Dames');
	CreateClass($TourId, $i++, 50, 99, 0, 'VH', 'VH,H', 'Master Hommes');
	CreateClass($TourId, $i++, 50, 99, 1, 'VD', 'VD,D', 'Master Dames');
	CreateClass($TourId, $i++, 15, 17, 0, 'CH', 'CH,JH,H', 'Cadet Hommes');
	CreateClass($TourId, $i++, 15, 17, 1, 'CD', 'CD,JD,D', 'Cadet Dames');
	CreateClass($TourId, $i++, 13, 14, 0, 'EH', 'EH,CH,JH,H', 'Jeunesse Hommes');
	CreateClass($TourId, $i++, 13, 14, 1, 'ED', 'ED,CD,JD,D', 'Jeunesse Dames');
	CreateClass($TourId, $i++, 11, 12, -1, 'MI', 'MI', 'Mini');
	CreateClass($TourId, $i++,  1, 10, -1, 'PI', 'PI,MI', 'Piccolo');
}

function CreateStandardSubClasses($TourId) {
//	$i=1;
//	CreateSubClass($TourId, $i++, '01', '01');
//	CreateSubClass($TourId, $i++, '02', '02');
//	CreateSubClass($TourId, $i++, '03', '03');
//	CreateSubClass($TourId, $i++, '04', '04');
}

function CreateStandardEvents($TourId, $TourType, $SubRule, $Outdoor=true) {
	global $ChDivisions, $ChClasses;
	$i=1;
	$TargetR=($Outdoor ? 5 : 2);
	$TargetC=($Outdoor ? 9 : 4);

	$TgtSize =  ($Outdoor ?  122 : 40);
	$Distance =  ($Outdoor ?  70 : 18);
	$Set = 0;

	foreach($ChDivisions as $cD => $D) {
		if($cD=='PR' or $cD=='PC') continue; // not for the para divisions...
		$Target=($cD=='C' ? $TargetC : $TargetR);
		$Set=($cD=='C' ? 0 : 1);
		if($cD=='BH' || $cD=='LB') {
			$TgtSize = ($Outdoor ?  122 : 60);
			$Distance = ($Outdoor ?  30 : 18);
		} else if ($cD=='C') {
			$TgtSize = ($Outdoor ?  80 : 40);
			$Distance = ($Outdoor ?  50 : 18);
		} else if ($cD=='BB') {
			$TgtSize = ($Outdoor ?  80 : 40);
			$Distance = ($Outdoor ?  30 : 18);
		}
		foreach($ChClasses as $cC => $C) {
			if($cD=='R' && ($cC=='CH' || $cC=='CD' || $cC=='VH' || $cC=='VD')) {
				$Distance = ($Outdoor ?  60 : 18);
			}
			if($cC=='EH' || $cC=='ED') {
				$TgtSize = ($Outdoor ?  122 : 60);
				$Distance = ($Outdoor ?  (($cD=='C' || $cD=='R') ? 40:30) : 18);
			}
			if($cC=='PI' || $cC=='MI') {
				$TgtSize=80;
				$Distance = ($Outdoor ?  ($cC=='PI' ? 15 : 25) : ($cC=='PI' ? 15 : 18));
			}


			CreateEvent($TourId, $i++, 0, 0, 16, $Target, 5, 3, 1, 5, 3, 1, $cD.$cC,  "$D $C", $Set, 240, 0, 0, 0, '', '', $TgtSize, $Distance);
		}
	}
	CreateEvent($TourId, $i++, 0, 0, 0, $Target, 5, 3, 1, 5, 3, 1, 'GW',  "Guests with sight", $Set, 240, 0, 0, 0, '', '', $TgtSize, $Distance);
	CreateEvent($TourId, $i++, 0, 0, 0, $Target, 5, 3, 1, 5, 3, 1, 'GO',  "Guests without sight", $Set, 240, 0, 0, 0, '', '', $TgtSize, $Distance);

	if($TourType==6) {
		$i=1;
		foreach($ChDivisions as $cD => $D) {
			if($cD=='PR' or $cD=='PC') continue; // not for the para divisions...
			$Target=($D=='C' ? $TargetC : $TargetR);
			CreateEvent($TourId, $i++, 1, 0, 0, $Target, 4, 6, 3, 4, 6, 3, $cD.'T',  $D . ' Team', ($D=='C' ? 0 : 1), 240, 0, 0, 0, '', '', ($cD=='BH' || $cD=='LB' ? 60 : 40), 18);
			CreateEvent($TourId, $i++, 1, 0, 0, $Target, 4, 6, 3, 4, 6, 3, $cD.'JT', $D . ' Jeunesse Team', ($D=='C' ? 0 : 1), 240, 0, 0, 0, '', '', 60, 18);
		}
	}

	// create "standard" Para events: CMO, CWO, RMO, RWO, WW1, MW1
	$TargetR=($Outdoor ? 5 : 2);
	$TargetC=($Outdoor ? 9 : 4);

	$TgtSize =  ($Outdoor ?  122 : 40);
	$Distance =  ($Outdoor ?  70 : 18);

	CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RMO',  "Recurve Men Open", 1, 240, 0, 0, 0, '', '', $TgtSize, $Distance);
	CreateEvent($TourId, $i++, 0, 0, 0, $TargetR, 5, 3, 1, 5, 3, 1, 'RWO',  "Recurve Women Open", 1, 240, 0, 0, 0, '', '', $TgtSize, $Distance);

	$TgtSize =  ($Outdoor ?  80 : 40);
	$Distance =  ($Outdoor ?  50 : 18);
	CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CMO',  "Compound Men Open", 0, 240, 0, 0, 0, '', '', $TgtSize, $Distance);
	CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'CWO',  "Compound Women Open", 0, 240, 0, 0, 0, '', '', $TgtSize, $Distance);
	CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'MW1',  "Men W1", 0, 240, 0, 0, 0, '', '', $TgtSize, $Distance);
	CreateEvent($TourId, $i++, 0, 0, 0, $TargetC, 5, 3, 1, 5, 3, 1, 'WW1',  "Women W1", 0, 240, 0, 0, 0, '', '', $TgtSize, $Distance);
}

function InsertStandardEvents($TourId, $TourType, $SubRule, $Outdoor=true) {
	Global $ChDivisions, $ChClasses;
	if($TourType==6) {
		// Indoor 18m
		foreach($ChDivisions as $cD => $D) {
			foreach($ChClasses as $cC => $C) {
				// individuals
				InsertClassEvent($TourId, 0, 1, $cD.$cC, $cD, $cC);



				if(in_array($cC, array('H','D','JH','JD','VH','VD','CH','CD'))) InsertClassEvent($TourId, 1, 3, $cD.'T', $cD, $cC);
				elseif(in_array($cC, array('EH','ED','MI','PI'))) InsertClassEvent($TourId, 1, 3, $cD.'JT', $cD, $cC);
			}
		}
	} else {
		foreach($ChDivisions as $cD => $D) {
			foreach($ChClasses as $cC => $C) {
				InsertClassEvent($TourId, 0, 1, $cD.$cC, $cD, $cC);
				switch(true) {
					case (in_array($cC, array('EH','ED','MI','PI'))):
						InsertClassEvent($TourId, 1, 3, $cD.'JT', $cD, $cC);
						break;
					case ($cD=='C'):
						InsertClassEvent($TourId, 1, 3, $cD.'T', $cD, $cC);
						break;
					case ($cD!='R'):
						InsertClassEvent($TourId, 1, 3, $cD.'T', $cD, $cC);
						break;
					case (in_array($cC, array('H','D','JH','JD'))):
						InsertClassEvent($TourId, 1, 3, $cD.'70T', $cD, $cC);
						break;
					default:
						InsertClassEvent($TourId, 1, 3, $cD.'60T', $cD, $cC);
						break;
				}
			}
		}
	}
	foreach($ChClasses as $ClCode => $clDesc) {
		InsertClassEvent($TourId, 0, 1, 'GW', 'GR', $ClCode);
		InsertClassEvent($TourId, 0, 1, 'GW', 'GC', $ClCode);
		InsertClassEvent($TourId, 0, 1, 'GO', 'GB', $ClCode);
	}
}

