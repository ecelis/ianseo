<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once('Fun_Partecipants.local.inc.php');
require_once('Qualification/Fun_Qualification.local.inc.php');
require_once('Tournament/Fun_ManSessions.inc.php');
require_once('Common/Lib/Fun_DateTime.inc.php');
require_once('Common/Lib/Fun_Entries.inc.php');
require_once('Common/Fun_FormatText.inc.php');
require_once('Common/Fun_Various.inc.php');

CheckTourSession(true);

// 	define('debug',false);

$DataSource="";
$ImportResult=array(
		'Refused'=>array(),
		'Updated'=>array(),
		'Blocked'=>array(),
		'Inserted'=>array(),
		'Unchanged'=>0,
		'Imported'=>0,
		);
$TeamUpdate=(empty($_REQUEST['NoTeamUpdate']));

// Check if a file has been uploaded
if(!empty($_FILES["UploadedFile"]["name"]) and strlen($_FILES["UploadedFile"]["name"]) and $_FILES["UploadedFile"]["error"]==UPLOAD_ERR_OK) {
	$DataSource = file_get_contents($_FILES["UploadedFile"]["tmp_name"]);
	unlink($_FILES["UploadedFile"]["tmp_name"]);
} else if(isset($_REQUEST["TextList"])) {
	$DataSource = $_REQUEST["txtList"];
}

if($DataSource) {
	$DataSource = str_replace("\r","",$DataSource);
	$DataSource = str_replace(";","\t",$DataSource);
	$tmpRequest = explode("\n", trim($DataSource));

	$OldTrace=$CFG->TRACE_QUERRIES;
	$CFG->TRACE_QUERRIES=false;
	if(!empty($_REQUEST['DeletePreviousArchers'])) {
		// remove all the entries and associated info
		if($Where=GetAccBoothEnWhere($Id, true, true)) {
			LogAccBoothQuerry("DELETE FROM Entries WHERE EnTournament=§TOCODETOID§");
			LogAccBoothQuerry("DELETE FROM Qualifications WHERE QuId not in (select EnId from Entries)");
			LogAccBoothQuerry("delete from AccEntries where AEId not in (select EnId from Entries)");
			LogAccBoothQuerry("delete from Photos where PhEnId not in (select EnId from Entries)");
			LogAccBoothQuerry("DELETE FROM Qualifications WHERE QuId not in (select EnId from Entries)");
			LogAccBoothQuerry("DELETE FROM ElabQualifications WHERE EqId not in (select EnId from Entries)");
			LogAccBoothQuerry("DELETE FROM ExtraData WHERE EdId not in (select EnId from Entries)");
		}
		safe_w_sql("delete from Entries where EnTournament={$_SESSION['TourId']}");
		safe_w_sql("delete from AccEntries where AEId not in (select EnId from Entries)");
		safe_w_sql("delete from Photos where PhEnId not in (select EnId from Entries)");
		safe_w_sql("DELETE FROM Qualifications WHERE QuId NOT IN (SELECT EnId From Entries)");
		safe_w_sql("DELETE FROM ElabQualifications WHERE EqId NOT IN (SELECT EnId From Entries)");
		safe_w_sql("DELETE FROM ExtraData WHERE EdId NOT IN (SELECT EnId From Entries)");
	}
	if(!empty($_REQUEST['DeletePreviousTeams'])) {

	}
	$ImportResult['Head']= '<tr><th>Result</th>
		<th>'. get_text('Code','Tournament') . '</th>
		<th>'. get_text('Session') . '</th>
		<th>'. get_text('Division') . '</th>
		<th>'. get_text('Class') . '</th>
		<th>'. get_text('Target') . '</th>
		<th>'. get_text('IndQual', 'Tournament') . '</th>
		<th>'. get_text('TeamQual', 'Tournament') . '</th>
		<th>'. get_text('IndFinEvent', 'Tournament') . '</th>
		<th>'. get_text('TeamFinEvent', 'Tournament') . '</th>
		<th>' . get_text('MixedTeamFinEvent', 'Tournament') . '</th>
		<th>' . get_text('FamilyName','Tournament') . '</th>
		<th>' . get_text('Name','Tournament') . '</th>
		<th>' . get_text('Sex','Tournament') . '</th>
		<th>' . get_text('Country') . '</th>
		<th>' . get_text('Nation') . '</th>
		<th>' . get_text('DOB','Tournament') . '</th>
		<th>' . get_text('SubClass','Tournament') . '</th>
		<th>' . get_text('Country') . ' 2</th>
		<th>' . get_text('Nation') . ' 2</th>
		<th>' . get_text('Country') . ' 3</th>
		<th>' . get_text('Nation') . ' 3</th>
		</tr>';

	$t=safe_r_sql("select IFnull(ToIocCode,'') as nocCode from Tournament WHERE ToId={$_SESSION['TourId']}");
	$u=safe_fetch($t);
	$nocCode=$u->nocCode;

	foreach($tmpRequest as $Line => $Value) {
		$Value=trim($Value);
		if(!$Value) continue;

		//Split and trim the line
		$tmpString = array();
		foreach(explode("\t", stripslashes($Value)) as $k=>$v) $tmpString[$k]=trim($v);

		if(preg_match('/^##([a-z0-9-]+)##$/i',$tmpString[0])) {
			if($tmpString[0]== "##NOC##") {
				if(count($tmpString)!=3) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				//get the CoId of the country with that CoCode
				$q=safe_r_SQL("select CoId from Countries where CoCode=".StrSafe_DB($tmpString[2])." and CoTournament={$_SESSION['TourId']}");
				if(safe_num_rows($q)==1) {
					$r=safe_fetch($q);
					$CoId = $r->CoId;
					// gets the EnIds of the archer with that EnCode
					$q=safe_r_SQL("select EnId from Entries where EnCode=".StrSafe_DB($tmpString[1])." and EnTournament={$_SESSION['TourId']}");
					if(safe_num_rows($q)) {
						while($r=safe_fetch($q)) {
							if($Where=GetAccBoothEnWhere($r->EnId, true, true)) {
								LogAccBoothQuerry("UPDATE Entries SET EnCountry = (select CoId from Countries where CoCode=".StrSafe_DB($tmpString[2])." and CoTournament=§TOCODETOID§) WHERE $Where");
							}
							safe_w_sql("UPDATE Entries SET EnCountry =".intval($CoId)." WHERE EnId=$r->EnId");
							$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td></tr>';
							$ImportResult['Imported']++;
						}
					} else {
						$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
						continue;
					}
				} else {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid NOC Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
			}elseif($tmpString[0]== "##WHEELCHAIR##") {
				if(count($tmpString)!=3) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				// gets the EnIds of the archer with that EnCode
				$q=safe_r_SQL("select EnId from Entries where EnCode=".StrSafe_DB($tmpString[1])." and EnTournament={$_SESSION['TourId']}");
				if(safe_num_rows($q)) {
					while($r=safe_fetch($q)) {
						if($Where=GetAccBoothEnWhere($r->EnId, true, true)) {
							LogAccBoothQuerry("UPDATE Entries SET EnWChair =".intval($tmpString[2]).", EnTimestamp=EnTimestamp WHERE $Where");
						}
						safe_w_sql("UPDATE Entries SET EnWChair =".intval($tmpString[2]).", EnTimestamp=EnTimestamp WHERE EnId=$r->EnId");
						$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td></tr>';
						$ImportResult['Imported']++;
					}
				} else {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
			} elseif($tmpString[0]== "##ADDRESS##") {
				/*
				 * ADDRESS LINE IS
				 * ##ADDRESS##[tab]ENCODE[tab]ADDRESS[tab]ZIP[tab]CITY[tab]PROVINCE[tab]COUNTRY
				 *
				 * */
				if(count($tmpString)<7) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				// gets the EnIds of the archer with that EnCode
				$q=safe_r_SQL("select EnId, EdExtra from Entries left join ExtraData on EdId=EnId where EnCode=".StrSafe_DB($tmpString[1])." and EnTournament={$_SESSION['TourId']}");
				if(safe_num_rows($q)) {
					while($r=safe_fetch($q)) {
						$Data=new stdClass();
						if($r->EdExtra) $Data=unserialize($r->EdExtra);
						$Data->Address=new StdClass();
						$Data->Address->Address=$tmpString[2];
						$Data->Address->ZIP=$tmpString[3];
						$Data->Address->City=$tmpString[4];
						$Data->Address->Province=$tmpString[5];
						$Data->Address->Country=$tmpString[6];
						safe_w_sql("insert into ExtraData set EdId=$r->EnId, EdType='E', EdExtra=".StrSafe_DB(serialize($Data))." on duplicate key update EdExtra=".StrSafe_DB(serialize($Data))."");
						$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td><td>'.$tmpString[3].'</td><td>'.$tmpString[4].'</td><td>'.$tmpString[5].'</td><td>'.$tmpString[6].'</td></tr>';
						$ImportResult['Imported']++;
						if($Where=GetAccBoothEnWhere($r->EnId, true, true)) {
							LogAccBoothQuerry("insert into ExtraData set EdId=(select EnId from Entries where $Where), EdType='E', EdExtra=".StrSafe_DB(serialize($Data))." on duplicate key update EdExtra=".StrSafe_DB(serialize($Data))."");
						}
					}
				} else {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
			} elseif($tmpString[0]== "##CAPTION##") {
				if(count($tmpString)!=3) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				// gets the EnIds of the archer with that EnCode
				$q=safe_r_SQL("select EnId from Entries where EnCode=".StrSafe_DB($tmpString[1])." and EnTournament={$_SESSION['TourId']}");
				if(safe_num_rows($q)) {
					while($r=safe_fetch($q)) {
						safe_w_sql("insert into ExtraData set EdId=$r->EnId, EdType='C', EdExtra=".StrSafe_DB($tmpString[2])." on duplicate key update EdExtra=".StrSafe_DB($tmpString[2])."");
						$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td></tr>';
						$ImportResult['Imported']++;
						if($Where=GetAccBoothEnWhere($r->EnId, true, true)) {
							LogAccBoothQuerry("insert into ExtraData set EdId=(select EnId from Entries where $Where), EdType='C', EdExtra=".StrSafe_DB($tmpString[2])." on duplicate key update EdExtra=".StrSafe_DB($tmpString[2])."");
						}
					}
				} else {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
			} elseif($tmpString[0]== "##EMAIL##") {
				if(count($tmpString)!=3) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				if(!preg_match('/^[a-z0-9._#-]+@[a-z0-9._-]+$/sim', $tmpString[2])) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid email<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				// gets the EnIds of the archer with that EnCode
				$q=safe_r_SQL("select EnId from Entries where EnCode=".StrSafe_DB($tmpString[1])." and EnTournament={$_SESSION['TourId']}");
				if(safe_num_rows($q)) {
					while($r=safe_fetch($q)) {
						safe_w_sql("insert into ExtraData set EdId=$r->EnId, EdType='E', EdEmail=".StrSafe_DB($tmpString[2])." on duplicate key update EdEmail=".StrSafe_DB($tmpString[2])."");
						$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td></tr>';
						$ImportResult['Imported']++;
						if($Where=GetAccBoothEnWhere($r->EnId, true, true)) {
							LogAccBoothQuerry("insert into ExtraData set EdId=(select EnId from Entries where $Where), EdType='E', EdEmail=".StrSafe_DB($tmpString[2])." on duplicate key update EdEmail=".StrSafe_DB($tmpString[2])."");
						}
					}
				} else {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
			} elseif($tmpString[0]== "##TARGET##") {
				if(count($tmpString)!=5) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				if(!preg_match('/^[a-z0-9]+$/sim', $tmpString[2]) || !preg_match('/^[a-z0-9]+$/sim', $tmpString[3])) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Class/Division reference<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				if(!preg_match('/^[0-9]+$/sim', $tmpString[4])) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid target reference<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				// gets the EnIds, class and division of the archer with that EnCode
				$Sql="
					SELECT EnId, TfId
					FROM Entries
					INNER JOIN `TargetFaces` ON EnTournament=TfTournament
					WHERE EnCode=".StrSafe_DB($tmpString[1])." AND EnTournament={$_SESSION['TourId']}
					AND EnClass=".StrSafe_DB($tmpString[3])." AND EnDivision=".StrSafe_DB($tmpString[2])."
					AND (CONCAT(EnDivision,EnClass) like TfClasses OR CONCAT(EnDivision,EnClass) REGEXP TfRegExp)
					ORDER BY TfId
					LIMIT " . $tmpString[4] . ",1";
				$q=safe_r_SQL($Sql);
				if(safe_num_rows($q)) {
					while($r=safe_fetch($q)) {
						safe_w_sql("Update Entries SET EnTargetFace=$r->TfId WHERE EnId=$r->EnId");
						$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td><td>'.$tmpString[3].'</td><td>'.$tmpString[4].'</td></tr>';
						$ImportResult['Imported']++;
// 						if($Where=GetAccBoothEnWhere($r->EnId, true, true)) {
// 							LogAccBoothQuerry("Update Entries SET EnTargetFace=$r->TfId WHERE");
// 						}
					}
				} else {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
			} elseif($tmpString[0]== "##DOB##") {
				if(count($tmpString)!=3) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				if(!preg_match('/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/', $tmpString[2])) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Date Format<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				// gets the EnIds of the archer with that EnCode
				$q=safe_r_SQL("select EnId from Entries where EnCode=".StrSafe_DB($tmpString[1])." and EnTournament={$_SESSION['TourId']}");
				if(safe_num_rows($q)) {
					while($r=safe_fetch($q)) {
						safe_w_sql("UPDATE Entries SET EnDob =".StrSafe_DB($tmpString[2])." WHERE EnId=$r->EnId");
						checkAgainstLUE($r->EnId);
						$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td></tr>';
						$ImportResult['Imported']++;
					}
				} else {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
			} elseif($tmpString[0]== "##SESSION##") {
				if(count($tmpString)!=4) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				if(!preg_match('/^[0-9]+$/sim', $tmpString[1]) || !preg_match('/^[a-z0-9]+$/sim', $tmpString[3])) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid data<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				// Check if session exists
				$Sql="SELECT *
					FROM Session
					WHERE SesTournament={$_SESSION['TourId']} AND SesOrder={$tmpString[1]} AND SesType='Q'";
				$q=safe_r_SQL($Sql);
				if(safe_num_rows($q)) {
					updateSession($_SESSION['TourId'],$tmpString[1],'Q',$tmpString[2],$tmpString[3],4,1,0,0,0,false);
				} else {
					insertSession($_SESSION['TourId'],$tmpString[1],'Q',$tmpString[2],$tmpString[3],4,1,0,0,0);
				}
				$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td><td>'.$tmpString[3].'</td></tr>';
				$ImportResult['Imported']++;

			} elseif($tmpString[0]== "##ID-OC##") {
				//  format is ##ID-OC##[tab]EnCode[tab]LocalID
				if(count($tmpString)!=3) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				if(!preg_match('/^[a-z0-9_.-]+$/sim', $tmpString[1]) || !preg_match('/^[a-z0-9_.-]+$/sim', $tmpString[2])) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid data<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				// gets the EnIds of the archer with that EnCode
				$q=safe_r_SQL("select EnId from Entries where EnCode=".StrSafe_DB($tmpString[1])." and EnTournament={$_SESSION['TourId']}");
				if(safe_num_rows($q)) {
					while($r=safe_fetch($q)) {
						safe_w_sql("insert into ExtraData set EdId=$r->EnId, EdType='Z', EdExtra=".StrSafe_DB($tmpString[2])." on duplicate key update EdExtra=".StrSafe_DB($tmpString[2])."");
						$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td></tr>';
						$ImportResult['Imported']++;
					}
				} else {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
			} elseif($tmpString[0]== "##OC-PRACTICE##") {
				//  format is ##ID-OC##[tab]EnCode[tab]LocalID
				if(count($tmpString)!=3) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, wrong number of fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				if(!preg_match('/^[a-z0-9_.-]+$/sim', $tmpString[1]) || !preg_match('/^[a-z0-9_.-]+$/sim', $tmpString[2])) {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid data<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
				// gets the EnIds of the archer with that OC COde
				$q=safe_r_SQL("select EnId from Entries INNER JOIN ExtraData ON EnId=EdId AND EdType='Z' where EdExtra=".StrSafe_DB($tmpString[1])." and EnTournament={$_SESSION['TourId']}");
				if(safe_num_rows($q)) {
					while($r=safe_fetch($q)) {
						safe_w_sql("insert into ExtraData set EdId=$r->EnId, EdType='P', EdExtra=".StrSafe_DB($tmpString[2])." on duplicate key update EdExtra=".StrSafe_DB($tmpString[2])."");
						$ImportResult['Inserted'][]='<tr><td>Inserted/updated</td><td>'.$tmpString[1].'</td><td>'.$tmpString[2].'</td></tr>';
						$ImportResult['Imported']++;
					}
				} else {
					$ImportResult['Refused'][]='<tr class="error"><td>Row ' . $Line  . ' incorrect, Invalid Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					continue;
				}
			}
		} else {
			if(empty($tmpString[0])) {
				$ImportResult['Refused'][]= '<tr class="error"><td>Row ' . $Line  . ' missing mandatory fields Entry Code<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
				continue;
			}

			if(count($tmpString)<4) {
				$ImportResult['Refused'][]= '<tr class="error"><td>Row ' . $Line  . ' too short, missing fields<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
				continue;
			}

			if(count($tmpString)>=14 && strlen($tmpString[13])>5) {
				$ImportResult['Refused'][]= '<tr class="error"><td>Country Code ['.$tmpString[13].'] too long (max 5 characters)<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
				continue;
			}

			if(count($tmpString)>=19 && strlen($tmpString[17])>5) {
				$ImportResult['Refused'][]= '<tr class="error"><td>Country Code ['.$tmpString[17].'] too long (max 5 characters)<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
				continue;
			}

			if(count($tmpString)>=21 && strlen($tmpString[19])>5) {
				$ImportResult['Refused'][]= '<tr class="error"><td>Country Code ['.$tmpString[19].'] too long (max 5 characters)<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
				continue;
			}

			$Tournament2Save = StrSafe_DB($_SESSION['TourId']);
			$NameOrder=0; // Given Name + Family Name
			/* BibNumber         */ $Code2Save = UpperText($tmpString[0]);
			/* Session           */ $Session2Save = intval($tmpString[1]);
			/* Division          */ $Division2Save = $tmpString[2];
			/* Class             */ $Class2Save = $tmpString[3];
			/* Target            */ $Target2Save = ((count($tmpString)>=5 && preg_match('/^[0-9]+[a-z]{0,1}$/i', trim($tmpString[4]))) ? intval($tmpString[1]) . strtoupper(str_pad($tmpString[4],(TargetNoPadding+1),"0",STR_PAD_LEFT)) : "0");
			/* IndQual           */ $ShootInd2Save = (count($tmpString)<=5 || !empty($tmpString[5]) ? '1' : '0');
			/* TeamQual          */ $ShootTeam2Save = (count($tmpString)<=6 || !empty($tmpString[6]) ? '1' : '0');
			/* IndFinEvent       */ $ShootFinInd2Save = (count($tmpString)<=7 || !empty($tmpString[7]) ? '1' : '0');
			/* TeamFinEvent      */ $ShootFinTeam2Save = (count($tmpString)<=8 || !empty($tmpString[8]) ? '1' : '0');
			/* MixedTeamFinEvent */ $ShootFinMixTeam2Save = (count($tmpString)<=9 || !empty($tmpString[9]) ? '1' : '0');
			/* FamilyName        */ $FirstName2Save = (count($tmpString)>=11 && $tmpString[10] ? AdjustCaseTitle($tmpString[10]) : '');
			/* Name              */ $Name2Save = (count($tmpString)>=12 && $tmpString[11] ? AdjustCaseTitle($tmpString[11]) : '');
			/* Sex               */ $Sex2Save = (count($tmpString)>=13 && (intval($tmpString[12]) || $tmpString[12] !='M') ? "1" : "0");
			/* Country           */	$Country2Save = (count($tmpString)>=14 && $tmpString[13] ? UpperText($tmpString[13]) : '');
			/* Nation            */	$Nation2Save = (count($tmpString)>=15 && $tmpString[14] ? AdjustCaseTitle($tmpString[14]) : '');
			/* DOB               */ $DoB2Save = (count($tmpString)>=16 ? ConvertDateLoc($tmpString[15]) : "0000-00-00");
			/* SubClass          */ $SubClass2Save = (count($tmpString)>=17 ? $tmpString[16] : "");
			/* Country 2         */ $SecondCountry2Save = (count($tmpString)>=18 ? UpperText($tmpString[17]) : "");
			/* Nation 2          */ $SecondNation2Save = (count($tmpString)>=19 ? AdjustCaseTitle($tmpString[18]) : "");
			/* Country 3         */ $ThirdCountry2Save = (count($tmpString)>=20 ? UpperText($tmpString[19]) : "");
			/* Nation 3          */ $ThirdNation2Save = (count($tmpString)>=21 ? AdjustCaseTitle($tmpString[20]) : "");

			$CtrlCode2Save = "";
			$AgeClass2Save = $Class2Save;
			$Status2Save = "0";
			$IdCountry2Save = "0";
			$NationComplete2Save = $Nation2Save;
			$SecondIdCountry2Save = "0";
			$SecondNationComplete2Save = $SecondNation2Save;
			$ThirdIdCountry2Save = "0";
			$ThirdNationComplete2Save = $ThirdNation2Save;

			if($nocCode) {
				$Select = "SELECT * "
					. "FROM LookUpEntries "
					. "WHERE LueCode=" . StrSafe_DB($tmpString[0]) . ' '
					. 'AND LueIocCode=' . StrSafe_DB(stripslashes($nocCode)) . ' '
					. 'ORDER BY LueDefault DESC';

				$Rs=safe_r_sql($Select);

				if (safe_num_rows($Rs)!=0) {
					// found in LookupEntries!
					$MyRow=safe_fetch($Rs);
					// campi che non riguardano la nazione
					$Name2Save = AdjustCaseTitle($MyRow->LueName);
					$FirstName2Save = AdjustCaseTitle($MyRow->LueFamilyName);
					$CtrlCode2Save = $MyRow->LueCtrlCode;

					if($CtrlCode2Save and $CtrlCode2Save!='0000-00-00') $DoB2Save= $CtrlCode2Save;
					$Sex2Save = $MyRow->LueSex;

					//Divisione
					$Division2Save = (!empty($tmpString[2]) ? $tmpString[2] : $MyRow->LueDivision);
					//Classe
					$Class2Save = (!empty($tmpString[3]) ? $tmpString[3] : $MyRow->LueClass);

					$AgeClass2Save = $MyRow->LueClass;
					if($DoB2Save and $DoB2Save!='0000-00-00') {
						$tmpAgeClass = calculateAgeClass($DoB2Save, $Sex2Save, $Division2Save);
						if(count($tmpAgeClass)==1) {
							$AgeClass2Save = $tmpAgeClass[0];
	// 						$Class2Save = $tmpAgeClass[0];
						} else if (in_array($Class2Save,$tmpAgeClass)) {
							$AgeClass2Save = $Class2Save;
						} else {
							$ImportResult['Refused'][]= '<tr class="error"><td>Row ' . $Line  . ' Class '.$Class2Save.' is not defined<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
							continue;
						}
					}

					$SubClass2Save = $MyRow->LueSubClass;
					$Status2Save = $MyRow->LueStatus;

					// campi nazione
					if(!isset($tmpString[13])) {
						$Country2Save = UpperText($MyRow->LueCountry);
						$Nation2Save = AdjustCaseTitle($MyRow->LueCoShort);
						$NationComplete2Save = AdjustCaseTitle($MyRow->LueCoDescr);
					}

					if(!isset($tmpString[17])) {
						$SecondCountry2Save = UpperText($MyRow->LueCountry2);
						$SecondNation2Save = AdjustCaseTitle($MyRow->LueCoShort2);
						$SecondNationComplete2Save = AdjustCaseTitle($MyRow->LueCoDescr2);
					}

					if(!isset($tmpString[19])) {
						$ThirdCountry2Save = UpperText($MyRow->LueCountry3);
						$ThirdNation2Save = AdjustCaseTitle($MyRow->LueCoShort3);
						$ThirdNationComplete2Save = AdjustCaseTitle($MyRow->LueCoDescr3);
					}
					$NameOrder=$MyRow->LueNameOrder;
				}
			}

			if(!$Division2Save or !$Class2Save) {
				$ImportResult['Refused'][]= '<tr class="error"><td>Row ' . $Line  . ' missing Division or Class<br/>Row not imported</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
				continue;
			}

			/*
				Cerco il codice di nazione trovato nella tabella di lookup.
			Se non lo trovo, lo aggiungo con le altre info altrimenti
			prendo i dati dalla tabella delle nazioni
			*/
			$TeamsFromDb=array();
			$TeamsFromDb['1']=array('id'=>'0', 'code'=>$Country2Save, 'short'=>$Nation2Save, 'long'=>$NationComplete2Save, 'dbshort'=>'', 'dblong'=>'');
			$TeamsFromDb['2']=array('id'=>'0', 'code'=>$SecondCountry2Save, 'short'=>$SecondNation2Save, 'long'=>$SecondNationComplete2Save, 'dbshort'=>'', 'dblong'=>'');
			$TeamsFromDb['3']=array('id'=>'0', 'code'=>$ThirdCountry2Save, 'short'=>$ThirdNation2Save, 'long'=>$ThirdNationComplete2Save, 'dbshort'=>'', 'dblong'=>'');


			foreach ($TeamsFromDb as $i => $v) {
				if(!$v['code']) continue; // if there is no country go to the next country!

				$SelCountry = "SELECT CoId,CoName,CoNameComplete "
					. "FROM Countries "
					. "WHERE CoCode=" . StrSafe_DB($v['code']) . " AND CoTournament=" . StrSafe_DB($_SESSION['TourId']) . " ";
				$RsC=safe_r_sql($SelCountry);

				if ($RowC=safe_fetch($RsC)) {
					$TeamsFromDb[$i]['id']=$RowC->CoId;
					$TeamsFromDb[$i]['dbshort']=$RowC->CoName;
					$TeamsFromDb[$i]['dblong']=$RowC->CoNameComplete;
				} else {
					/** NO COUNTRY WITH THAT CODE, SO INSERT IT **/
					$Insert
					= "INSERT INTO Countries set
					CoTournament='{$_SESSION['TourId']}'
					, CoCode=".StrSafe_DB($v['code'])."
					, CoName=".StrSafe_DB($v['short'])."
					, CoNameComplete=".StrSafe_DB($v['long'])."";

					$RsI=safe_w_sql($Insert);
					$TeamsFromDb[$i]['id']=safe_w_last_id();
					$TeamsFromDb[$i]['dbshort']=$v['short'];
					$TeamsFromDb[$i]['dblong']=$v['long'];

					LogAccBoothQuerry("INSERT INTO Countries set CoTournament=§TOCODETOID§ , CoCode=".StrSafe_DB($v['code'])." , CoName=".StrSafe_DB($v['short'])." , CoNameComplete=".StrSafe_DB($v['long']));
				}
			}

			$EntrySQL="EnTournament=§TOCODETOID§
					, EnIocCode=".StrSafe_DB($nocCode)."
					, EnDivision=".StrSafe_DB($Division2Save)."
					, EnClass=".StrSafe_DB($Class2Save)."
					, EnSubClass=".StrSafe_DB($SubClass2Save)."
					, EnAgeClass=".StrSafe_DB($AgeClass2Save)."
					, EnCtrlCode=".($DoB2Save && !$CtrlCode2Save ? "IFNULL(DATE_FORMAT(" . StrSafe_DB($DoB2Save) . "," . StrSafe_DB(get_text('DateFmtDB')) . "),'')" : StrSafe_DB($CtrlCode2Save))."
					, EnCode=".StrSafe_DB($Code2Save)."
					, EnName=".StrSafe_DB($Name2Save)."
					, EnFirstName=".StrSafe_DB($FirstName2Save)."
					, EnNameOrder=".intval($NameOrder)."
					, EnSex=$Sex2Save
					, EnIndClEvent=$ShootInd2Save
					, EnTeamClEvent=$ShootTeam2Save
					, EnIndFEvent=$ShootFinInd2Save
					, EnTeamFEvent=$ShootFinTeam2Save
					, EnTeamMixEvent=$ShootFinMixTeam2Save
					, EnStatus=$Status2Save
					, EnDOB=".StrSafe_DB($DoB2Save)."
					";

			if($TeamUpdate) {
				$EntrySQL.=", EnCountry=-Country-, EnCountry2=-Country2-, EnCountry3=-Country3- ";
			}

			if(!empty($_REQUEST['OverwritePreviousArchers'])) {
				// checks the changes
				$q=safe_r_sql("select Entries.*, QuSession, QuTargetNo from Entries left join Qualifications on EnId=QuId where EnCode=".StrSafe_DB($Code2Save)." and EnTournament=" . StrSafe_DB($_SESSION['TourId']) . "");
				if($r=safe_fetch($q)) {
					if($Where=GetAccBoothEnWhere($r->EnId, true, true)) {
						LogAccBoothQuerry(str_replace(
							array('-Country-','-Country2-','-Country3-'),
							array(  $TeamsFromDb['1']['id'] ? "(select CoId from Countries where CoCode='{$TeamsFromDb['1']['code']}' and CoTournament=§TOCODETOID§)" : 0,
									$TeamsFromDb['2']['id'] ? "(select CoId from Countries where CoCode='{$TeamsFromDb['2']['code']}' and CoTournament=§TOCODETOID§)" : 0,
									$TeamsFromDb['3']['id'] ? "(select CoId from Countries where CoCode='{$TeamsFromDb['3']['code']}' and CoTournament=§TOCODETOID§)" : 0,
							),
							"update Entries set $EntrySQL, EnTimestamp=EnTimestamp where $Where"));
					}
					// has an entry... makes the comparision ONLY if the update made some changes
					safe_w_sql(str_replace(
							array('§TOCODETOID§', '-Country-','-Country2-','-Country3-'),
							array(StrSafe_DB($_SESSION['TourId']), $TeamsFromDb['1']['id'], $TeamsFromDb['2']['id'], $TeamsFromDb['3']['id']),
							"update Entries set $EntrySQL, EnTimestamp=EnTimestamp where EnId=$r->EnId"));
					$tmp=safe_w_affected_rows();
					// update the qualification too
					safe_w_sql("update Qualifications set QuSession='$Session2Save', QuTargetNo='$Target2Save', QuTarget='".intval(substr($Target2Save, 1))."', QuLetter='".substr($Target2Save, -1)."', QuTimestamp=QuTimestamp where QuId=$r->EnId");
					if($tmp or safe_w_affected_rows()) {
						if(safe_w_affected_rows()) {
							safe_w_sql("update Qualifications set QuBacknoPrinted=0 where QuId=$r->EnId");
							LogAccBoothQuerry("update Qualifications set QuBacknoPrinted=0 where QuId=(select EnId from Entries where $Where)");
						}
						LogAccBoothQuerry("update Qualifications set QuSession='$Session2Save', QuTargetNo='$Target2Save', QuTarget='".intval(substr($Target2Save, 1))."', QuLetter='".substr($Target2Save, -1)."', QuTimestamp=QuTimestamp where QuId=(select EnId from Entries where $Where)");
						checkAgainstLUE($r->EnId);

						safe_w_sql("Update Entries set EnBadgePrinted=0, EnTimestamp='".date('Y-m-d H:i:s')."' where EnId=$r->EnId");
						LogAccBoothQuerry("update Entries set EnBadgePrinted=0, EnTimestamp='".date('Y-m-d H:i:s')."' where $Where");
						if($Session2Save!=$r->QuSession) $tmpString[1]="<del>$r->QuSession</del><br/>$Session2Save";
						if($Division2Save!=$r->EnDivision) $tmpString[2]="<del>$r->EnDivision</del><br/>$Division2Save";
						if($Class2Save!=$r->EnClass) $tmpString[3]="<del>$r->EnClass</del><br/>$Class2Save";
						$switch=count($tmpString);
						switch(true) {
							case ($switch>=21 and $TeamUpdate): if($TeamsFromDb['3']['id']!=$r->EnCountry3) {
													$t=safe_r_sql("select * from Countries where CoId=$r->EnCountry3");
													$u=safe_fetch($t);
													$tmpString[19]="<del>$u->CoCode</del><br/>{$TeamsFromDb['3']['code']}";
													$tmpString[20]="<del>$u->CoName</del><br/>{$TeamsFromDb['3']['dbshort']}";
												}
							case ($switch>=19 and $TeamUpdate): if($TeamsFromDb['2']['id']!=$r->EnCountry2) {
													$t=safe_r_sql("select * from Countries where CoId=$r->EnCountry2");
													$u=safe_fetch($t);
													$tmpString[17]="<del>$u->CoCode</del><br/>{$TeamsFromDb['2']['code']}";
													$tmpString[18]="<del>$u->CoName</del><br/>{$TeamsFromDb['3']['dbshort']}";
												}
							case ($switch>=17): if($SubClass2Save!=$r->EnSubClass) $tmpString[16]="<del>$r->EnSubClass</del><br/>$SubClass2Save";
							case ($switch>=16): if($DoB2Save!=$r->EnDob) $tmpString[15]="<del>$r->EnDob</del><br/>$DoB2Save";
							case ($switch>=14 and $TeamUpdate): if($TeamsFromDb['1']['id']!=$r->EnCountry) {
													$t=safe_r_sql("select * from Countries where CoId=$r->EnCountry");
													$u=safe_fetch($t);
													$tmpString[13]="<del>$u->CoCode</del><br/>{$TeamsFromDb['1']['code']}";
													$tmpString[14]="<del>$u->CoName</del><br/>{$TeamsFromDb['1']['dbshort']}";
												}
							case ($switch>=13): if($Sex2Save!=$r->EnSex) $tmpString[12]="<del>$r->EnSex</del><br/>$Sex2Save";
							case ($switch>=12): if($Name2Save!=$r->EnName) $tmpString[11]="<del>$r->EnName</del><br/>$Name2Save";
							case ($switch>=11): if($FirstName2Save!=$r->EnFirstName) $tmpString[10]="<del>$r->EnFirstName</del><br/>$FirstName2Save";
							case ($switch>=10): if($ShootFinMixTeam2Save!=$r->EnTeamMixEvent) $tmpString[9]="<del>$r->EnTeamMixEvent</del><br/>$ShootFinMixTeam2Save";
							case ($switch>=9): if($ShootFinTeam2Save!=$r->EnTeamFEvent) $tmpString[8]="<del>$r->EnTeamFEvent</del><br/>$ShootFinTeam2Save";
							case ($switch>=8): if($ShootFinInd2Save!=$r->EnIndFEvent) $tmpString[7]="<del>$r->EnIndFEvent</del><br/>$ShootFinInd2Save";
							case ($switch>=7): if($ShootTeam2Save!=$r->EnTeamClEvent) $tmpString[6]="<del>$r->EnTeamClEvent</del><br/>$ShootTeam2Save";
							case ($switch>=6): if($ShootInd2Save!=$r->EnIndClEvent) $tmpString[5]="<del>$r->EnIndClEvent</del><br/>$ShootInd2Save";
							case ($switch>=5): if(substr($Target2Save,1)!=substr($r->QuTargetNo,1)) $tmpString[4]="<del>".substr($r->QuTargetNo,1)."</del><br/>".substr($Target2Save,1)."";
						}
						$ImportResult['Updated'][] = '<tr><td>&nbsp;</td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					} elseif(!$TeamUpdate and ($TeamsFromDb['1']['id']!=$r->EnCountry or $TeamsFromDb['2']['id']!=$r->EnCountry2 or $TeamsFromDb['3']['id']!=$r->EnCountry3)) {
						if($TeamsFromDb['1']['id']!=$r->EnCountry) {
							$t=safe_r_sql("select * from Countries where CoId=$r->EnCountry");
							$u=safe_fetch($t);
							$tmpString[13]="<del>{$TeamsFromDb['1']['code']}</del><br/>$u->CoCode";
							$tmpString[14]="<del>{$TeamsFromDb['1']['dbshort']}</del><br/>$u->CoName";
						}
						if($TeamsFromDb['2']['id']!=$r->EnCountry2) {
							$t=safe_r_sql("select * from Countries where CoId=$r->EnCountry2");
							$u=safe_fetch($t);
							$tmpString[13]="<del>{$TeamsFromDb['2']['code']}</del><br/>$u->CoCode";
							$tmpString[14]="<del>{$TeamsFromDb['2']['dbshort']}</del><br/>$u->CoName";
						}
						if($TeamsFromDb['3']['id']!=$r->EnCountry3) {
							$t=safe_r_sql("select * from Countries where CoId=$r->EnCountry3");
							$u=safe_fetch($t);
							$tmpString[13]="<del>{$TeamsFromDb['3']['code']}</del><br/>$u->CoCode";
							$tmpString[14]="<del>{$TeamsFromDb['3']['dbshort']}</del><br/>$u->CoName";
						}
						$ImportResult['Blocked'][] = '<tr><td><a href="#" onclick="window.open(\'./ForceUpdate.php?EnId='.$r->EnId.'&EnCo1='.$TeamsFromDb['1']['id'].'&EnCo2='.$TeamsFromDb['2']['id'].'&EnCo3='.$TeamsFromDb['3']['id'].'\')"" target="ForceUpdate">Force Update</a></td><td>'.implode('</td><td>', $tmpString)."</td></tr>";
					} else {
						$ImportResult['Unchanged']++;
					}
					continue; // goes to the next line to import
				}
			}

			$ImportResult['Imported']++;
			// Inserts the entry, so check the inital SQL adn adds the teams
			if(!$TeamUpdate) {
				$EntrySQL.=", EnCountry=-Country-, EnCountry2=-Country2-, EnCountry3=-Country3- ";
			}
			$Insert = "INSERT INTO Entries set $EntrySQL";

			LogAccBoothQuerry(str_replace(
				array('-Country-','-Country2-','-Country3-'),
				array(  $TeamsFromDb['1']['id'] ? "(select CoId from Countries where CoCode='{$TeamsFromDb['1']['code']}' and CoTournament=§TOCODETOID§)" : 0,
						$TeamsFromDb['2']['id'] ? "(select CoId from Countries where CoCode='{$TeamsFromDb['2']['code']}' and CoTournament=§TOCODETOID§)" : 0,
						$TeamsFromDb['3']['id'] ? "(select CoId from Countries where CoCode='{$TeamsFromDb['3']['code']}' and CoTournament=§TOCODETOID§)" : 0,
				),
				$Insert));

			$Rs=safe_w_sql(str_replace(
							array('§TOCODETOID§', '-Country-','-Country2-','-Country3-'),
							array(StrSafe_DB($_SESSION['TourId']), $TeamsFromDb['1']['id'], $TeamsFromDb['2']['id'], $TeamsFromDb['3']['id']),
							$Insert));

			$idNewRow = safe_w_last_id();

			// aggiungo la riga in Qualifications
			$Insert = "INSERT INTO Qualifications (QuId,QuSession,QuTargetNo,QuTarget,QuLetter) "
				. "VALUES("
				. StrSafe_DB($idNewRow) . ","
				. StrSafe_DB($Session2Save) . ","
				. StrSafe_DB($Target2Save) . ","
				. intval(substr($Target2Save, 1)) . ","
				. StrSafe_DB(substr($Target2Save, -1)) . ") ";
			$Rs=safe_w_sql($Insert);

			if($Where=GetAccBoothEnWhere($idNewRow, true, true)) {
				LogAccBoothQuerry("INSERT INTO Qualifications
						set QuSession=".StrSafe_DB($Session2Save).",
						QuTargetNo=".StrSafe_DB($Target2Save).",
						QuTarget=".intval(substr($Target2Save, 1)).",
						QuLetter=".StrSafe_DB(substr($Target2Save, -1)).",
						QuId=(select EnId from Entries Where $Where)");
			}
			checkAgainstLUE($idNewRow);

			$ImportResult['Inserted'][]= "<tr><td>OK</td>
				<td>$Code2Save</td>
				<td>$Session2Save</td>
				<td>$Division2Save</td>
				<td>$Class2Save</td>
				<td>$Target2Save</td>
				<td>$ShootInd2Save</td>
				<td>$ShootTeam2Save</td>
				<td>$ShootFinInd2Save</td>
				<td>$ShootFinTeam2Save</td>
				<td>$ShootFinMixTeam2Save</td>
				<td>$FirstName2Save</td>
				<td>$Name2Save</td>
				<td>$Sex2Save</td>
				<td>{$TeamsFromDb['1']['code']}</td>
				<td>{$TeamsFromDb['1']['short']}</td>
				<td>$DoB2Save</td>
				<td>$SubClass2Save</td>
				<td>{$TeamsFromDb['2']['code']}</td>
				<td>{$TeamsFromDb['2']['short']}</td>
				<td>{$TeamsFromDb['3']['code']}</td>
				<td>{$TeamsFromDb['3']['short']}</td></tr>";
		}

	}
	// updates the Athlete status in Entries
	$Now=date('Y-m-d H:i:s');
	safe_w_sql("update Entries left join Divisions on EnDivision=DivId and EnTournament=DivTournament left join Classes on EnClass=ClId and EnTournament=ClTournament
			set
			EnTimestamp=if(EnAthlete=if(DivAthlete is null,0,if(ClAthlete is null,0,DivAthlete and ClAthlete)), EnTimestamp, '$Now'),
			EnAthlete=if(DivAthlete is null,0,if(ClAthlete is null,0,DivAthlete and ClAthlete))
			where EnTournament={$_SESSION['TourId']}");

	$q=safe_r_sql("select EnId, EnAthlete from Entries where EnTimestamp='$Now'");
	while($r=safe_fetch($q)) {
		if($Where=GetAccBoothEnWhere($r->EnId, true, true)) {
			LogAccBoothQuerry("update Entries set EnAthlete='$r->EnAthlete', EnTimestamp='$Now' where $Where");
		}
	}
	//	// deletes the qualifications entry for non athletes
	//	safe_w_sql("delete from Qualifications where QuId in (select EnId from Entries where EnAthlete!='1' and EnTournament={$_SESSION['TourId']})");

	MakeIndAbs();

	$CFG->TRACE_QUERRIES=$OldTrace;
}

$PAGE_TITLE=get_text('ListLoad','Tournament');

include('Common/Templates/head.php');

if(!$DataSource) {
?>
<form name="FrmList" method="POST" action="" enctype="multipart/form-data">
<table class="Tabella">
<tr><th class="Title" colspan="3"><?php print get_text('ListLoad', 'Tournament');?></th></tr>
<tr>
<th class="SubTitle" width="50%"><?php print get_text('AthleteList', 'Tournament');?></th>
<th class="SubTitle" width="50%" colspan="2"><?php print get_text('AthleteFile', 'Tournament');?></th>
</tr>
<tr>
<td class="Center" rowspan="2">
<textarea name="txtList" cols="80" rows="30" id="txtList"></textarea><input name="TextList" type="hidden" value="1">
<p><input name="UploadedFile" type="file" size="30"></p>
</td>
<td>
<?php
echo "1)&nbsp;" . get_text('Code','Tournament') . "<br>";
echo "2)&nbsp;" . get_text('Session') . "<br>";
echo "3)&nbsp;" . get_text('Division') . "<br>";
echo "4)&nbsp;" . get_text('Class') . "<br>";
echo "5)&nbsp;" . get_text('Target') . "<br>";
echo "6)&nbsp;" . get_text('IndQual', 'Tournament') . "<br>";
echo "7)&nbsp;" . get_text('TeamQual', 'Tournament') . "<br>";
echo "8)&nbsp;" . get_text('IndFinEvent', 'Tournament') . "<br>";
echo "9)&nbsp;" . get_text('TeamFinEvent', 'Tournament') . "<br>";
echo "10)&nbsp;" . get_text('MixedTeamFinEvent', 'Tournament') . "<br>";
echo "11)&nbsp;" . get_text('FamilyName','Tournament') . "<br>";
echo "12)&nbsp;" . get_text('Name','Tournament') . "<br>";
echo "13)&nbsp;" . get_text('Sex','Tournament') . "<br>";
echo "14)&nbsp;" . get_text('Country') . "<br>";
echo "15)&nbsp;" . get_text('Nation') . "<br>";
echo "16)&nbsp;" . get_text('DOB','Tournament') . "<br>";
echo "17)&nbsp;" . get_text('SubClass','Tournament') . "<br>";
echo "18)&nbsp;" . get_text('Country') . " 2<br>";
echo "19)&nbsp;" . get_text('Nation') . " 2<br>";
echo "20)&nbsp;" . get_text('Country') . " 3<br>";
echo "21)&nbsp;" . get_text('Nation') . " 3<br>";

echo "</td><td>";

echo '<div>'.get_text('SpecialImports', 'Tournament').'</div>';
foreach(array('Wheelchair', 'Address' , 'Email', 'Target', 'DOB', 'NOC', 'Session', 'Caption', 'ID-OC') as $Special) {
	echo '<div><br/><b>##'.strtoupper($Special).'##</b><br/>'.get_text('Desc'.$Special, 'Tournament').'</div>';
}
?>
</td>
</tr>
<tr>
	<td colspan="2">
	<input type="checkbox" name="DeletePreviousArchers" disabled="disabled"><?php echo get_text('DeletePreviousArchers','Tournament'); ?>
	<br/><input type="checkbox" name="DeletePreviousTeams" disabled="disabled"><?php echo get_text('DeletePreviousTeams','Tournament'); ?>
	<br/><input type="checkbox" name="OverwritePreviousArchers" checked="checked"><?php echo get_text('OverwritePreviousArchers','Tournament'); ?>
	<br/><input type="checkbox" name="NoTeamUpdate" ><?php echo get_text('NoTeamUpdate','Tournament'); ?>
	</td>
</tr>
<tr>
	<td colspan="3" class="Center"><input name="Command" type="submit" value="<?php echo get_text('AthleteList', 'Tournament');?>"></td>
</tr>
</table>
</form>
<?php
}
else
{
	$Cols=22;
	echo '<table class="Tabella">';
	echo '<tr><th class="Title" colspan="'.$Cols.'">'.get_text('ListLoad', 'Tournament').'</th></tr>';
	// Refused rows
	if(!empty($ImportResult['Refused'])) {
		echo '<tr><th class="Head" colspan="'.($Cols-1).'">'.get_text('ListLoadRefused', 'Tournament').'</th>
			<th class="Head">'.count($ImportResult['Refused']).'</th></tr>';
		echo $ImportResult['Head'];
		echo implode('', $ImportResult['Refused']);
	}
	// Blocked rows
	if(!empty($ImportResult['Blocked'])) {
		echo '<tr><th class="Head" colspan="'.($Cols-1).'">'.get_text('ListLoadBlocked', 'Tournament').'</th>
				<th class="Head">'.count($ImportResult['Blocked']).'</th></tr>';
		echo $ImportResult['Head'];
		echo implode('', $ImportResult['Blocked']);
	}
	// Updated rows
	if(!empty($ImportResult['Updated'])) {
		echo '<tr><th class="Head" colspan="'.($Cols-1).'">'.get_text('ListLoadUpdated', 'Tournament').'</th>
			<th class="Head">'.count($ImportResult['Updated']).'</th></tr>';
		echo $ImportResult['Head'];
		echo implode('', $ImportResult['Updated']);
	}
	// Inserted rows
	if($ImportResult['Imported']) {
		echo '<tr><th class="Head" colspan="'.($Cols-1).'">'.get_text('ListLoadInserted', 'Tournament').'</th>
			<th class="Head">'.$ImportResult['Imported'].'</th></tr>';
		echo implode('', $ImportResult['Inserted']);
	}
	// Unchanged rows
	if($ImportResult['Unchanged']) {
		echo '<tr><th class="Head" colspan="'.($Cols-1).'">'.get_text('ListLoadUnchanged', 'Tournament').'</th>
			<th class="Head">'.$ImportResult['Unchanged'].'</th></tr>';
	}

	echo '</table>';
}

include('Common/Templates/tail.php');
function UpperText($text) {
	return mb_convert_case(trim(stripslashes($text)), MB_CASE_UPPER, "UTF-8");
}
