<?php
	define('debug',false);	// settare a true per l'output di debug

	require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
	require_once('Common/Fun_Number.inc.php');
	require_once('Common/Fun_FormatText.inc.php');
	require_once('Common/Lib/ArrTargets.inc.php');
	require_once('Common/Lib/Obj_RankFactory.php');
	require_once('Common/Fun_Phases.inc.php');
	require_once('Final/Fun_ChangePhase.inc.php');

	CheckTourSession(true);
    checkACL(AclIndividuals, AclReadWrite);

	$Error=false;
	$EventList=array();

	$Events=array();
	if (!empty($_REQUEST['EventCode']))
	{
		$Events = explode('|',$_REQUEST['EventCode']);
	}
	if(!empty($_REQUEST['EventCodeMult'])) {
		$Events=$_REQUEST['EventCodeMult'];
    }

    if(!$Events) {
	    CD_redirect('./AbsIndividual1.php');
    }

	$rank=Obj_RankFactory::create('Abs',array('events'=>$Events,'dist'=>0));

	$IdAffected = array();
	$NotResolvedMsg=array();

// scrivo
	if (isset($_REQUEST['Ok']) && $_REQUEST['Ok']=='OK' && !IsBlocked(BIT_BLOCK_IND))
	{
		$Ties=array();
		$NotResolved=array();
		$MaxRank=array();

	// penso alle rank
		$Events=array_keys($_REQUEST['R']);
		foreach($_REQUEST['R'] as $Event => $EnIds)
		{
			$q=safe_r_sql("select EvFinalFirstPhase, EvMatchMode, EvNumQualified, EvFirstQualified from Events where EvCode='$Event' and EvTeamEvent='0' and EvTournament='{$_SESSION['TourId']}'");
			$r=safe_fetch($q);
			$MaxRank[$Event]=$r->EvNumQualified;
			//if($r->EvFinalFirstPhase==24 or $r->EvFinalFirstPhase==48) $MaxRank[$Event]+=8; // salva i primi 8
			$NotResolved[$Event]=false;

			asort($EnIds);

		// controlla che tutti gli spareggi siano stati fatti
			$TrueRank=1;
			foreach($EnIds as $EnId => $AssignedRank)
			{
//print $EnId . ' - ' . $AssignedRank . ' - ' . $TrueRank.'<br>';
				if($AssignedRank!=$TrueRank && $AssignedRank<=$MaxRank[$Event])
				{

					$NotResolved[$Event]=true;
				}
				$TrueRank++;
			}

		// assegna le rank SOLO se tutto è a posto
			if(!$NotResolved[$Event])
			{
				foreach($EnIds as $EnId => $AssignedRank)
				{
					$x=$rank->setRow(array(
						array(
							'ath' => $EnId,
							'event' => $Event,
							'dist'	=> 0,
							'rank' => $AssignedRank
						)
					));
//print $EnId .' - ' . $x.' - ' . $AssignedRank . '<br>';
					if ($x==1)
					{
						$IdAffected[]= strsafe_db($EnId);
					}

				}
			}
			else
			{
				$NotResolvedMsg[]=$Event;
			}
		}
		//exit;
	// penso ai tiebreak
		foreach ($_REQUEST['T'] as $EventKey => $Event)
		{
			foreach ($Event as $id => $TieArrows)
			{
				foreach($TieArrows as $index => $Value)
				{
					if (!array_key_exists($EventKey.'_'.$id, $Ties)) $Ties[$EventKey.'_'.$id]=str_pad('',3,' ');

					$v=GetLetterFromPrint($Value);

					$Ties[$EventKey.'_'.$id]=substr_replace($Ties[$EventKey.'_'.$id], $v, $index, 1);
				}
			}
		}

		if (count($Ties)>0)
		{
			foreach ($Ties as $Key=>$Value)
			{
				$tmp=explode('_', $Key);
				$ath=array_pop($tmp);
				$ev=implode('_', $tmp);

//				list($ev,$ath)=explode('_',$Key);

				$x=$rank->setRow(array(
					array(
						'ath' => $ath,
						'event' => $ev,
						'dist'	=> 0,
						'tiebreak' => $Value
					)
				));
			}
		}

		if (count($IdAffected)>0)
		{
		// Distruggo le griglie basandomi su $IdAffected
			$Select
				= "SELECT DISTINCT EvCode "
				. "FROM "
					. "Events INNER JOIN Individuals ON EvCode=IndEvent AND EvTournament=IndTournament AND EvTeamEvent=0 "
				. "WHERE "
					. "IndId IN(" . implode(',',$IdAffected). ") AND IndTournament=" . StrSafe_DB($_SESSION['TourId']) . " "
			;
			$Rs=safe_r_sql($Select);
			if (safe_num_rows($Rs)>0)
			{
//				$Ev2Delete = array();
//				while ($Row=safe_fetch($Rs))
//					$Ev2Delete[]=StrSafe_DB($Row->EvCode);

				$Delete
					= "DELETE FROM Finals "
					. "WHERE FinTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND FinEvent IN ('" . implode("','",$Events) . "') ";
				$Rs=safe_w_sql($Delete);

			// ricreo la griglia distrutta
				$Insert = "INSERT INTO Finals (FinEvent,FinMatchNo,FinTournament,FinDateTime)
				  SELECT EvCode,GrMatchNo," . StrSafe_DB($_SESSION['TourId']) . "," . StrSafe_DB(date('Y-m-d H:i:s')) . " 
				  FROM Events 
				  Inner join Phases on PhId=EvFinalFirstPhase and (PhIndTeam & 1)=1
				  INNER JOIN Grids ON GrPhase<=greatest(PhId, PhLevel) AND EvTeamEvent='0' 
				  AND EvTournament=" . StrSafe_DB($_SESSION['TourId']) . " 
				  WHERE EvCode IN ('" . implode("','",$Events) . "') ";
				$RsIns=safe_w_sql($Insert);
			}

		// importo i nomi nelle griglie
			$VetoEvents=array();
//			print'<pre>';
//			print_r($NotResolved);
//			print'</pre>';exit;
			foreach($NotResolved as $Event => $veto) {
				if($veto) $VetoEvents[]=$Event;
			}
			sort($VetoEvents);
//print '<pre>';
//print_r($VetoEvents);
//print '</pre>';
			$Select = "SELECT IndId,IndRank, IndEvent,GrMatchNo,EvFinalFirstPhase
                FROM Individuals 
                INNER JOIN Events ON IndTournament=EvTournament AND IndEvent=EvCode AND EvTeamEvent=0 
                INNER JOIN Phases ON PhId=EvFinalFirstPhase and (PhIndTeam & 1) = 1 
                INNER JOIN Grids ON GrPhase=greatest(PhId,PhLevel) AND IndRank=IF(EvFinalFirstPhase=48,GrPosition2, if(GrPosition>EvNumQualified, 0, GrPosition)) 
                WHERE IndRank between EvFirstQualified and EvNumQualified+EvFirstQualified-1 
                    and IndTournament=" . StrSafe_DB($_SESSION['TourId']) . " " . (count($Events)>0 ? " 
                    AND IndEvent IN('" . implode("','",$Events). "')" : ""). " 
                    " . ($VetoEvents?" AND EvCode not in ('".implode("','", $VetoEvents)."')":'') . "
                ORDER BY EvCode,IndRank ASC,GrMatchNo ASC ";
			;
//print $Select;exit;
//exit;
			$RsSel=safe_r_sql($Select);

			if (safe_num_rows($RsSel))
			{
				while ($MyRow=safe_fetch($RsSel))
				{
					if(!array_key_exists($MyRow->IndEvent, $EventList)) {
						$EventList[$MyRow->IndEvent]=valueFirstPhase($MyRow->EvFinalFirstPhase);
					}

					if($MyRow->IndRank<=$MaxRank[$MyRow->IndEvent]) {
						$Update
							= "UPDATE Finals SET "
							. "FinAthlete='" . $MyRow->IndId . "', "
							. "FinDateTime='" . date('Y-m-d H:i:s') . "' "
							. "WHERE FinEvent='" . $MyRow->IndEvent . "' AND "
							. "FinMatchNo='" . $MyRow->GrMatchNo . "' AND "
							. "FinTournament=" . StrSafe_DB($_SESSION['TourId']) . " ";
						$RsUp=safe_w_sql($Update);
					}
					//print $Update . '<br>';
				}
			// setto a 1 i flags che dicono che ho fatto gli spareggi per gli eventi
//				$Update
//					= "UPDATE Events SET "
//					. "EvShootOff='1' "
//					. "WHERE EvTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND EvCode IN('" . implode("','",$Events) . "')  AND EvTeamEvent='0' ";
//				$RsUp=safe_w_sql($Update);

				$Update
					= "UPDATE Events SET "
					. "EvShootOff='1' "
					. "WHERE EvTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND EvTeamEvent='0' " . (count($Events)>0 ? " AND EvCode IN('" . implode("','",$Events). "')" : ""). " "
					. ($VetoEvents?" AND EvCode not in ('".implode("','", $VetoEvents)."')":'')
				;
				$RsUp=safe_w_sql($Update);
				set_qual_session_flags();

			// calcolo la finalrank di quelli che si son fermati alle quelifiche
				$coppie=array();
				$q="SELECT EvCode FROM Events WHERE EvTournament={$_SESSION['TourId']} AND EvTeamEvent=0 AND EvCode NOT IN ('" . implode(',',$VetoEvents). "')" . (count($Events)>0 ? " AND EvCode IN('" . implode("','",$Events). "') " : '');
				$r=safe_r_sql($q);
				while ($rr=safe_fetch($r))
				{
					$coppie[$rr->EvCode]= $rr->EvCode . "@-3";
				}
				/*foreach ($Events as $e)
				{
					$coppie[$e]= $e . "@-3";
				}*/
				//print_r($coppie);exit;
				Obj_RankFactory::create('FinalInd',array('eventsC'=>$coppie))->calculate();

				foreach($EventList as $key=>$value)
				{
					move2NextPhase($value,$key,null);
				}
			}
		}

		//exit;
	}

	include('Common/Templates/head.php');
?>
<table class="Tabella">
<TR><TH class="Title"><?php print get_text('ShootOff4Final') . ' - ' . get_text('Individual');?></TH></TR>
<?php
if (count($NotResolvedMsg)>0) {
    $msg=array();
    foreach($NotResolvedMsg as $m) {
        $msg[]='<a href="#r'.$m.'">'.$m.'</a>';
    }
    ?>
	<tr class="warning"><td><?php print get_text('NotAllShootoffResolved','Tournament',implode('</a>, <a href="#">',$msg));?></td></tr>
<?php } ?>
</table>
<?php
	if (!$Error)
	{
		$rank->read();
		$data=$rank->getData();
		$NumDist=$data['meta']['numDist'];

		$curEvent='';

		if(count($data['sections'])>0) {
			print '<form name="Frm" method="post" action="">' . "\n";
				if (isset($_REQUEST['EventCode'])) {
					print '<input type="hidden" name="EventCode" value="' . $_REQUEST['EventCode'] . '">' . "\n";
                } elseif (!empty($_REQUEST['EventCodeMult'])) {
				    foreach($_REQUEST['EventCodeMult'] as $val) {
					    echo '<input type="hidden" name="EventCodeMult[]" value="' . $val . '">' . "\n";
                    }
                }

				foreach ($data['sections'] as $section)
				{
					$Colonne = 8 + $NumDist;
					$PercPunti = NumFormat(55/($NumDist+3));
                    echo '<a name="r'.$section['meta']['event'].'"></a>';
					print '<table class="Tabella">' . "\n";
						print '<tr class="Divider"><td colspan="' . $Colonne . '"></td></tr>' . "\n";
						print '<tr><th class="Title" colspan="' . $Colonne . '">' . $section['meta']['descr']. ' (' . $section['meta']['event'] . ')</th></tr>';
						print '<tr>';
							print '<th width="5%">' . get_text('Rank') . '</th>';
							print '<th width="20%">' . get_text('Archer') . '</th>';
							print '<th width="20%" colspan="2">' . get_text('Country') . '</th>';
							for ($i=1;$i<=$NumDist;++$i)
								print '<th width="8%">Score ' . $i . '</th>';
							print '<th width="8%">' . get_text('Total') . '</th>';
							print '<th width="8%">G</th>';
							print '<th width="8%">X</th>';
							print '<th>' . get_text('TieArrows') . '</th>';
						print '</tr>' . "\n";

						foreach ($section['items'] as $item)
						{
						// fermo appena trovo una rank > di quelle che passano e una riga con so=0
							if ($item['rank']>$section['meta']['qualifiedNo'] && $item['so']==0)
								break;

							$style="";
							/*if ($item['ct']>1)		// ho qualche rank pari
							{
								if ($item['so']==0)		// ho un giallo
								{
									$style="warning";
								}
								else					// ho un rosso
								{
									$style="error";
								}
							}*/

							if ($item['so']==0)	// potrei avere un giallo
							{
								if ($item['ct']>1)		// ho un giallo
								{
									$style="warning";
								}
								else	// no pari
								{
									$style="";
								}
							}
							else	// rossi
							{
								$style="error";
							}

							print '<tr class="' . $style . '">';
								print '<th class="Title">';
									echo $item['rank'] . '&nbsp;';
							/*
							 * Devo gestire la tendina.
							 * Partendo dalla rank so fino a dove devo arrivare perchè me lo
							 * dice il campo ct
							 */
									$endRank = $item['rankBeforeSO']+$item['ct']-1;
									$nn='['.$section['meta']['event'].'][' . $item['id'] . ']';
									if($item['rankBeforeSO']!=$endRank) {
										echo '<select name="R' . $nn . '">';
										for ($i=$item['rankBeforeSO'];$i<=$endRank;++$i) {
											echo '<option value="' . $i . '"' . ($i==$item['rank'] || (isset($_REQUEST["R"][$section['meta']['event']][$item['id']]) and $i==$_REQUEST["R"][$section['meta']['event']][$item['id']]) ? ' selected' : '') . '>' . $i . '</option>';
										}
										echo '</select>';
									} else {
										echo '<input type="hidden" name="R' . $nn . '" value="' . $item['rankBeforeSO'] . '">';
									}


								print '</th>';
								print '<td>' . $item['athlete'] . '</td>';
								print '<td width="5%" class="Center">' . $item['countryCode'] . '</td>';
								print '<td width="15%">' . ($item['countryName']!='' ? $item['countryName'] : '&nbsp') . '</td>';

								for ($i=1;$i<=$NumDist;++$i)
								{
									$tmp=explode('|',$item['dist_'.$i]);
									print '<td class="Center">' . $tmp[1] . '</td>';
								}
								print '<td class="Center">' . $item['score'] . '</td>';
								print '<td class="Center">' . $item['gold']  . '</td>';
								print '<td class="Center">' . $item['xnine']  . '</td>';
								print '<td>';
									for ($i=0;$i<3;++$i)
									{
										print '<input type="text" maxlength="3" size="1" name="T' . $nn . '[' . $i . ']" value="' . (strlen($item['tiebreak'])>$i ? DecodeFromLetter($item['tiebreak'][$i]) : ''). '">&nbsp;';
									}
								print '</td>';
							print '</tr>' . "\n";
						}
						print '<tr><td class="Center" colspan="' . $Colonne . '"><input type="hidden" name="Ok" value="OK"><input type="submit" value="' . get_text('CmdOk') . '"></td></tr>' . "\n";
					print '</table>' . "\n";
					print '<br>';
				}

			print '</form>';
		}
	}

	include('Common/Templates/tail.php');
