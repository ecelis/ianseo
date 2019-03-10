<?php
include_once('F2FGrid.inc.php');
include_once('UpdateFunctions.inc.php');

/*
ogni step viene salvato separatamente al proprio numero di versione...
creato un numero di versione DB apposito...
Se la versione è troppo vecchia include i vecchi file

*/

if($version <= '2011-01-01 00:00:00') require_once('Common/UpdateDb-2010.inc.php');
if($version <= '2012-01-01 00:00:00') require_once('Common/UpdateDb-2011.inc.php');
if($version <= '2013-01-01 00:00:00') require_once('Common/UpdateDb-2012.inc.php');
if($version <= '2014-01-01 00:00:00') require_once('Common/UpdateDb-2013.inc.php');
if($version <= '2015-01-01 00:00:00') require_once('Common/UpdateDb-2014.inc.php');
if($version <= '2016-01-01 00:00:00') require_once('Common/UpdateDb-2015.inc.php');
if($version <= '2017-01-01 00:00:00') require_once('Common/UpdateDb-2016.inc.php');
if($version <= '2018-01-01 00:00:00') require_once('Common/UpdateDb-2017.inc.php');
if($version <= '2019-01-01 00:00:00') require_once('Common/UpdateDb-2018.inc.php');

if($version<'2019-01-14 12:29:02') {
	$q="ALTER TABLE `AvailableTarget` ADD AtSession tinyint unsigned NOT NULL, ADD AtTarget int not null, add AtLetter varchar(1) not null, add index (AtTournament, AtSession, AtTarget, AtLetter)";
	$r=safe_w_sql($q,false,array(1146, 1060));

	// updates the existant things
	safe_w_sql("update AvailableTarget set AtSession=left(AtTargetNo,1), AtTarget=substr(AtTargetNo, 2, 3), AtLetter=right(AtTargetNo,1)");

	db_save_version('2019-01-14 12:29:02');
}

/*

// TEMPLATE
if($version<'2019-01-14 12:29:02') {
	$q="ALTER TABLE `Finals` ADD `FinShootFirst` tinyint NOT NULL after FinStatus";
	$r=safe_w_sql($q,false,array(1146, 1060));

	db_save_version('2019-01-14 12:29:02');
}

IMPORTANT: InfoSystem related things MUST be changed in the lib.php file!!!
REMEMBER TO CHANGE ALSO Common/Lib/UpdateTournament.inc.php!!!

*/

db_save_version($newversion);

function db_save_version($newversion) {
	global $CFG;
	//Aggiorno alla versione attuale SOLO le gare che erano alla versione immediatamente precedente
	$oldDbVersion = GetParameter('DBUpdate');
	safe_w_sql("UPDATE Tournament SET ToDbVersion='{$newversion}' WHERE ToDbVersion='{$oldDbVersion}'");

	SetParameter('DBUpdate', $newversion);
	SetParameter('SwUpdate', ProgramVersion);

	foreach(glob($CFG->DOCUMENT_PATH.'TV/Photos/*.ser') as $file) {
		@unlink($file);
		@unlink(substr($file, 0, -3).'check');
	}
}
?>