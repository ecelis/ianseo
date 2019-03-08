<?php

require_once('Common/Lib/Obj_RankFactory.php');

function rotFint($TVsettings, $RULE) {
	global $CFG, $IsCode, $SubBlock;
	$CSS=unserialize($TVsettings->TVPSettings);
	getPageDefaults($CSS);
	$Return=array(
		'CSS' => $CSS,
		'html' => '',
		'Block' => '',
		'BlockCss' => '',
		'NextSubBlock' => 1,
		'SubBlocks' => 1
	);
	$ret=array();

	$Columns=(isset($TVsettings->TVPColumns) && !empty($TVsettings->TVPColumns) ? explode('|',$TVsettings->TVPColumns) : array());
	$ViewTeams=(in_array('TEAM', $Columns) or in_array('ALL', $Columns));
	$ViewFlag=(in_array('FLAG', $Columns) or in_array('ALL', $Columns));
	$ViewCode=(in_array('CODE', $Columns) or in_array('ALL', $Columns));
	$ViewAths=(in_array('ATHL', $Columns) or in_array('ALL', $Columns));
	$ViewByes=(in_array('BYE', $Columns) or in_array('ALL', $Columns));
	$Title2Rows=(in_array('TIT2ROWS', $Columns) ? '<br/>' : ': ');


	$options=array('tournament' => $RULE->TVRTournament);
	$Arr_Ev = array();
	$Arr_Ph = array();
	if($TVsettings->TVPEventTeam) $Arr_Ev = explode('|', $TVsettings->TVPEventTeam);
	if(strlen($TVsettings->TVPPhasesTeam)) {
		$Arr_Ph = explode('|', $TVsettings->TVPPhasesTeam);
	}

	if($Arr_Ev and count($Arr_Ph)) {
		$options['events']=array();
		foreach($Arr_Ph as $p) {
			foreach($Arr_Ev as $e) $options['events'][] = $e . '@' . $p;
		}
	} elseif(count($Arr_Ph)) {
		$options['events']=array();
		if(strstr($Arr_Ph[0], '+')) {
			foreach($Arr_Ph as $p) {
				$t=explode('+',$p);
				$l=array_shift($t);
				foreach($t as $e) $options['events'][] = $l . '@' . $e;
			}
		} else {
			foreach($Arr_Ph as $p) {
				$options['events'][] = '@' . $p;
			}
		}
	} elseif($Arr_Ev) {
		$options['events'] = $Arr_Ev;
	}

	$rank=Obj_RankFactory::create('GridTeam',$options);
	$rank->read();
	$rankData=$rank->getData();

	if(count($rankData['sections'])==0) return $Return;

	$Return['SubBlocks']=count($rankData['sections']);
	$Return['NextSubBlock']=$SubBlock+1;

	if($SubBlock>count($rankData['sections'])) $SubBlock=1;

	while($SubBlock) {
		list($IdEvent, $section)=each($rankData['sections']);
		$SubBlock--;
	}

	$Columns=(isset($TVsettings->TVPColumns) && !empty($TVsettings->TVPColumns) ? explode('|',$TVsettings->TVPColumns) : array());
	$TotalColWidth=7;
	foreach($Columns as $sub) if(substr($sub,0,5)=='WIDTH') $TotalColWidth=substr($sub,6);

	$NumCol=3 + $ViewTeams ;

	if($TVsettings->TVPViewIdCard) {
		// opponents view with picture

		// check the pictures
		$fotow=min(200,intval($_SESSION['WINHEIGHT']/6)*4/3);

		foreach($section['phases'] as $IdPhase => $phase) {
			// Titolo della tabella
			$ret[] = '<div class="Title" >' . $section['meta']['eventName'] . $Title2Rows . $phase['meta']['phaseName'] . '</div>' ;

			foreach($phase['items'] as $key => $item) {
				if(($IsBye=($item['tie']==2 or $item['oppTie']==2) or (!$item['countryCode'] or !$item['oppCountryCode'])) and !$ViewByes) continue;

				// 1a riga, è il target
				$Tgt=get_text('Target') . ' ' . ltrim($item['target'], '0');
				if($IsBye) {
					$Tgt=get_text('Bye');
					if($item['tie']==2 and $item['target']) $Tgt.= ' (' . get_text('Target') . ' ' . ltrim($item['target'], '0') . ')';
					elseif($item['oppTarget']) $Tgt.= ' (' . get_text('Target') . ' ' . ltrim($item['oppTarget'], '0') . ')';
				} elseif($item['target']!=$item['oppTarget']) {
					$Tgt=get_text('Targets','Tournament') . ' ' . ltrim($item['target'], '0') . '-' . ltrim($item['oppTarget'], '0');
				}
				$ret[] = '<div class="GridId"><div class="IdTarget Back2e Font2e" align="center">' . $Tgt . '</div></div>';

				$NamSx=$item['athlete'];
				$NamDx=$item['oppAthlete'];

				if(!$TVsettings->TVPNameComplete) {
					$NamSx=$item['familyName'] . ' ' . FirstLetters($item['givenName']);
					$NamDx=$item['oppFamilyName'] . ' ' . FirstLetters($item['oppGivenName']);
				}

				// 2a riga, sono i nomi degli atleti
				$ret[] = '<div class="GridId"><div class="IdAthletes">' . $NamSx . '</div><div class="IdScore"></div><div class="IdAthletes">' . $NamDx . '</div></div>';

				// 3a riga, nazioni
				$tmp1='';
				$tmp2='';
				if($ViewCode) {
					$tmp1.='<div class="CountryCode Rotate Rev1e">'.$item['countryCode'].'</div>';
					$tmp2.='<div class="CountryCode Rotate Rev1e">'.$item['oppCountryCode'].'</div>';
				}
				if($ViewFlag) {
					$tmp1.='<div class="FlagDiv">'.get_flag_ianseo($item['countryCode'], '', '', $IsCode).'</div>';
					$tmp2.='<div class="FlagDiv">'.get_flag_ianseo($item['oppCountryCode'], '', '', $IsCode).'</div>';
				}
				if($ViewTeams) {
					$tmp1.='<div class="CountryName">'.$item['countryName'].'</div>';
					$tmp2.='<div class="CountryName">'.$item['oppCountryName'].'</div>';
				}

				if($tmp1) $ret[] = '<div class="GridId"><div class="IdPanel">'.$tmp1.'</div><div class="IdScore"></div><div class="IdPanel">'.$tmp2.'</div></div>';

				$Score='&nbsp;';
				if($IsBye) {
					$Score = '<i style="font-size:75%">'.get_text('Bye').'</i>';
				} else {
					$SxFinScore = trim($section['meta']['matchMode'] ? $item['setScore']    : $item['score']);
					$DxFinScore = trim($section['meta']['matchMode'] ? $item['oppSetScore'] : $item['oppScore']);
					$TieBreak='';
					if($item['tie']==1 or $item['oppTie']==1) {
						if($item['tiebreakDecoded'] or $item['oppTiebreakDecoded']) {
							$TieBreak = '<div style="font-size:60%">&nbsp;T.&nbsp;'
								. $item['tiebreakDecoded']
								. '-'
								. $item['oppTiebreakDecoded']
								. '</div>';
						} elseif($item['tie']==1) {
							$SxFinScore .= '*';
						} else {
							$DxFinScore .= '*';
						}
					}
					$Score= $SxFinScore . '-' . $DxFinScore;
					if($TieBreak) $Score .= $TieBreak;
				}

				// 4a riga, le fotografie

				$ret[] = '<div class="GridId"><div class="IdPicture">'.get_photo_ianseo($item['id'], '', '', 'class="IdPictureImg"', '', $IsCode).'</div>'
					. '<div class="IdScore">' . $Score . '</div>'
					. '<div class="IdPicture">'.get_photo_ianseo($item['oppId'], '', '', 'class="IdPictureImg"', '', $IsCode).'</div>'
					. '</div></div>';
			}
		}

		$Return['Block']= 'GridId';
		$Return['BlockCss']='display:flex; flex-direction:row; justify-content:center; align-items:center; box-sizing:border-box; overflow:hidden; white-space: nowrap; font-size:2em; margin:auto; box-sizing:border-box;';
	} else {
		// Grid view

		$ret[] = '<div class="Title">' . $section['meta']['eventName'] . '</div>';
		$ret[]='<div id="content" data-direction="up">';
		$NumColBase = 3 + $ViewTeams;
			$RowIndex=0;
		foreach($section['phases'] as $IdPhase => $phase) {

			// Titolo della tabella
			$ret[] = '<div class="Title">' . $phase['meta']['phaseName'] . '</div>';

			foreach($phase['items'] as $key => $item) {
				$Class=($RowIndex++%2 ? 'e' : 'o');
				$tmp1 ='<div class="Grid Font1'.$Class.' Back1'.$Class.' TopRow'.($item['oppWinner'] ? ' Loser' : '').'">';
				$tmp2 ='<div class="Grid Font1'.$Class.' Back1'.$Class.' BottomRow'.($item['winner'] ? ' Loser' : '').'">';

				$tmp1.='<div class="Target Rev1'.$Class.'">'.ltrim($item['target'],'0').'</div>';
				$tmp2.='<div class="Target Rev1'.$Class.'">'.ltrim($item['oppTarget'],'0').'</div>';

				if($ViewCode) {
					$tmp1.='<div class="CountryCode Rotate Rev1'.$Class.'">'.$item['countryCode'].'</div>';
					$tmp2.='<div class="CountryCode Rotate Rev1'.$Class.'">'.$item['oppCountryCode'].'</div>';
				}

				if($ViewFlag) {
					$tmp1.='<div class="FlagDiv">'.get_flag_ianseo($item['countryCode'], '', '', $IsCode).'</div>';
					$tmp2.='<div class="FlagDiv">'.get_flag_ianseo($item['oppCountryCode'], '', '', $IsCode).'</div>';
				}

				$tmp1.='<div class="CountryDescr">'.$item['countryName'].'</div>';
				$tmp2.='<div class="CountryDescr">'.$item['oppCountryName'].'</div>';

				if($ViewAths) {
					$tmp1 .= '<div class="Athlete">';
					if($item['teamId']) {
						foreach($section['athletes'][$item['teamId']][$item['subTeam']] as $ath) {
							$tmp1.=$ath['athlete'].'<br/>';
						}
					}
					$tmp1 .= '</div>';
					$tmp2 .= '<div class="Athlete">';
					if($item['oppTeamId']) {
						foreach($section['athletes'][$item['oppTeamId']][$item['oppSubTeam']] as $ath) {
							$tmp2.=$ath['athlete'].'<br/>';
						}
					}
					$tmp2 .= '</div>';
				}

				if($item['tie']==2) {
					// it is a bye
					$Score=$rankData['meta']['fields']['bye'];
				} elseif($item['countryName']) {
					// archer is there
					$Score=  $item[$section['meta']['matchMode'] ? 'setScore' : 'score'] . ($item['tie']==1 ? '*' : ($item['oppTie']==1 ? '<tt>&nbsp;</tt>' : ''));
				} else {
					// it is the bye's opponent?
					$Score='';
				}
				$tmp1.='<div class="Score">'.$Score.'</div>';

				if($item['oppTie']==2) {
					// it is a bye
					$Score=$rankData['meta']['fields']['bye'];
				} elseif($item['oppCountryName']) {
					// archer is there
					$Score= $item[$section['meta']['matchMode'] ? 'oppSetScore' : 'oppScore'] . ($item['oppTie']==1 ? '*' : ($item['tie']==1 ? '<tt>&nbsp;</tt>' : ''));
				} else {
					// it is the bye's opponent?
					$Score='';
				}
				$tmp2.='<div class="Score">'.$Score.'</div>';

				$tmp1.='</div>';
				$tmp2.='</div>';

				$ret[] = $tmp1;
				$ret[] = '<div class="Divider"></div>';
				$ret[] = $tmp2;
			}
		}
		$ret[]='</div>';
		$Return['Block']='Grid';
		$Return['BlockCss']='height:2em; width:100%; padding-right:0.5rem; overflow:hidden; font-size:2em; display:flex; flex-direction:row; justify-content:left; align-items:center; box-sizing:border-box;';
	}
	$Return['html']=implode('', $ret);
	return $Return;
}

function rotFintSettings($Settings) {
	global $CFG;
	$ret='<br/>';
	$ret.= '<table class="Tabella Css3">';
	$ret.= '<tr><th colspan="3">'.get_text('TVCss3SpecificSettings','Tournament').'</th></tr>';

	// defaults for fonts, colors, size
	$RMain=array();
	if(!empty($Settings)) {
		$RMain=unserialize($Settings);
	}

	$PageDefaults=getPageDefaults($RMain);

// 	if(!isset($RMain[''])) $RMain['']='';
	// if(!isset($RMain[''])) $RMain['']='';

	foreach($PageDefaults as $key => $Value) {
		if(!isset($PageDefaults[$key])) continue;
		$ret.= '<tr>
			<th nowrap="nowrap" class="Right">'.get_text('TVCss3'.$key,'Tournament').' <input type="button" value="reset" onclick="document.getElementById(\'P-Main['.$key.']\').value=\''.$Value.'\'"></th>
			<td width="100%"><input type="text" name="P-Main['.$key.']" id="P-Main['.$key.']" value="'.$RMain[$key].'"></td>
			</tr>';
	}
	return $ret;
}


function getPageDefaults(&$RMain) {
	global $CFG;
	$ret=array(
			'MainContent' => 'left:15%;width:50%',
			'Title' => 'margin-top:2rem',
			'TopRow' => 'margin-top:1rem',
			'Divider' => 'display:block;height:0.5rem; background-color:gray;',
			'BottomRow' => '',
			'Loser' => 'opacity:0.3;',
// 			'RankUp' => 'background: url(\'' . $CFG->ROOT_DIR . 'Common/Images/Up.png\');',
// 			'RankDown' => 'background: url(\'' . $CFG->ROOT_DIR . 'Common/Images/Down.png\');',
// 			'RankMinus' => 'background: url(\'' . $CFG->ROOT_DIR . 'Common/Images/Minus.png\');',
// 			'Rank' => 'flex: 0 0 1.75em; text-align:right;',
			'CountryCode' => 'flex: 0 0 5rem; font-size:0.5em; margin-left:-3.5rem',
			'FlagDiv' => 'flex: 0 0 3.95rem; ',
			'Flag' => 'height:2.5rem; border:0.1rem solid #888;',
			'Target' => 'flex: 0 0 4ch; font-size:75%; text-align:right;',
			'Athlete' => 'flex: 1 1 15em;font-size:50%;overflow:hidden;white-space:nowrap;',
			'CountryDescr' => 'flex: 1 1 15em;',
			'DistScore' => 'flex: 0 0 3ch; text-align:right; font-size:0.8em;',
			'DistPos' => 'flex: 0 0 3ch; text-align:left; font-size:0.7em;',
			'Score' => 'flex: 0 0 4ch; text-align:right; font-size:1.25em;',
			'Gold' => 'flex: 0 0 3ch; text-align:right; font-size:1em;',
			'XNine' => 'flex: 0 0 3ch; text-align:right; font-size:1em;',
			'IdTarget' => 'border-top-left-radius:1em; border-top-right-radius:1em; width:100%; margin-top:1em; padding:0.5em; box-sizing:border-box; font-size:1em;',
			'IdAthletes' => 'flex:2 0 20%; font-size:1.5em; text-align: center; padding:0 0.5em; ',
			'IdPanel' => 'flex:2 0 20%; display:flex;flex-flow:row nowrap; justify-content:center;align-items:center',
			'IdPicture' => 'flex:2 0 33%; text-align:center; ',
			'IdPictureImg' => 'height:5em; padding:1rem; ',
			'IdScore' => 'flex:0 0 auto; text-align:center; font-size:4rem; ',
		);
	foreach($ret as $k=>$v) {
		if(!isset($RMain[$k])) $RMain[$k]=$v;
	}
	return $ret;
}