<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
CheckTourSession(true);
require_once('Common/Lib/Fun_Final.local.inc.php');
require_once('Common/Lib/Fun_Modules.php');

$Event = isset($_REQUEST['Event']) ? $_REQUEST['Event'] : null;
$TeamEvent = isset($_REQUEST['Team']) ? $_REQUEST['Team'] : null;
$MatchId = isset($_REQUEST['MatchId']) ? $_REQUEST['MatchId'] : null;
$Arrows = isset($_REQUEST['ArrowPosition']);

$JSON=array('error'=>1, 'isLive' => 0, 'isAlternate' => 0, 'winner'=>'', 'msg'=>'', 'config'=>array(), 'nameL' => '', 'nameR'=>'', 'scoreL'=>'', 'scoreR' => '', 'target' => '', 'targetSize' => 0);

checkACL(($TeamEvent ? AclTeams : AclIndividuals), AclReadWrite, false);

$options=array();
$options['tournament']=$_SESSION['TourId'];
$options['events']=$Event;
$options['matchno']=$MatchId;
$options['extended']=$Arrows;

if($TeamEvent) {
	$rank=Obj_RankFactory::create('GridTeam',$options);
} else {
	$rank=Obj_RankFactory::create('GridInd',$options);
}
$rank->read();
$Data=$rank->getData();

if(empty($Data['sections'])) {
	JsonOut($JSON);
}
$Section=end($Data['sections']);

if(empty($Section['phases'])) {
	JsonOut($JSON);
}
$Phase=end($Section['phases']);

if(empty($Phase['items'])) {
	JsonOut($JSON);
}
$Match=end($Phase['items']);

$MatchIdL=$MatchId;
$MatchIdR=$MatchId+1;

$JSON['error']=0;
$JSON['winner']=$Match['winner'] ? 'L' : ($Match['oppWinner'] ? 'R' : '');
$JSON['confirmed']=($Match['status']==1 and $Match['oppStatus']==1);

$JSON['isAlternate']=($Match['shootFirst'] or $Match['oppShootFirst']);
$JSON['isLive']=($Match['liveFlag']>0);

if($TeamEvent) {
	$JSON['nameL']=$Match['countryName'];
	$JSON['nameR']=$Match['oppCountryName'];
} else {
	$JSON['nameL']=$Match['athlete'].' ('.$Match['countryCode'].')';
	$JSON['nameR']=$Match['oppAthlete'].' ('.$Match['oppCountryCode'].')';
}

$JSON['config']['arrows']=$Section['meta'][$Phase['meta']['FinElimChooser'] ? 'elimArrows' : 'finArrows'];
$JSON['config']['ends']=$Section['meta'][$Phase['meta']['FinElimChooser'] ? 'elimEnds' : 'finEnds'];
$JSON['config']['so']=$Section['meta'][$Phase['meta']['FinElimChooser'] ? 'elimSO' : 'finSO'];
$JSON['config']['soEnds']=(ceil(min(strlen(trim($Match['tiebreak'])), strlen(trim($Match['oppTiebreak'])))/$JSON['config']['so']))+1;

// build score grids
$JSON['scoreL']='<table class="Scorecard"><tr><th class="Alternate AlternateTitle">'.get_text('ShootsFirst', 'Tournament').'</th><th></th>';
$JSON['scoreR']='<table class="Scorecard"><tr><th class="Alternate AlternateTitle">'.get_text('ShootsFirst', 'Tournament').'</th><th></th>';

for($i=1; $i<=$JSON['config']['arrows']; $i++) {
	$JSON['scoreL'].='<th>'.$i.'</th>';
	$JSON['scoreR'].='<th>'.$i.'</th>';
}

if($Section['meta']['matchMode']) {
	$JSON['scoreL'].='<th>'.get_text('SetTotal','Tournament').'</th>';
	$JSON['scoreR'].='<th>'.get_text('SetTotal','Tournament').'</th>';
	$JSON['scoreL'].='<th>' . get_text('SetPoints','Tournament'). '</th>';
	$JSON['scoreR'].='<th>' . get_text('SetPoints','Tournament'). '</th>';
	$JSON['scoreL'].='<th>' . get_text('TotalShort','Tournament'). '</th>';
	$JSON['scoreR'].='<th>' . get_text('TotalShort','Tournament'). '</th>';
} else {
	$JSON['scoreL'].='<th>'.get_text('TotalProg','Tournament').'</th>';
	$JSON['scoreR'].='<th>'.get_text('TotalProg','Tournament').'</th>';
	$JSON['scoreL'].='<th>'.get_text('RunningTotal','Tournament').'</th>';
	$JSON['scoreR'].='<th>'.get_text('RunningTotal','Tournament').'</th>';
}
$JSON['scoreL'].='</tr>';
$JSON['scoreR'].='</tr>';

$Match['arrowstring']=str_pad($Match['arrowstring'],$JSON['config']['ends']*$JSON['config']['arrows'], ' ', STR_PAD_RIGHT);
$Match['oppArrowstring']=str_pad($Match['oppArrowstring'],$JSON['config']['ends']*$JSON['config']['arrows'], ' ', STR_PAD_RIGHT);
$Match['setPoints']=array_pad(explode('|', $Match['setPoints']), $JSON['config']['ends'],'');
$Match['oppSetPoints']=array_pad(explode('|', $Match['oppSetPoints']), $JSON['config']['ends'],'');
$Match['setPointsByEnd']=array_pad(explode('|', $Match['setPointsByEnd']), $JSON['config']['ends'],'');
$Match['oppSetPointsByEnd']=array_pad(explode('|', $Match['oppSetPointsByEnd']), $JSON['config']['ends'],'');
$totL=0;
$totR=0;

$TabIndexOffset=100;

for($i=0;$i<$JSON['config']['ends'];$i++) {
	$ShootsFirstL=$Match['shootFirst'] & pow(2, $i);
	$ShootsFirstR=$Match['oppShootFirst'] & pow(2, $i);
	$JSON['scoreL'].='<tr so="0" end="'.$i.'">';
	$JSON['scoreR'].='<tr so="0" end="'.$i.'">';
	$JSON['scoreL'].='<th class="Alternate"><input class="ShootsFirst" so="0" type="radio" id="first['.$TeamEvent.']['.$Event.']['.$MatchIdL.']['.$i.']" name="first['.$i.']" onclick="setShootingFirst(this)" '.($ShootsFirstL ? 'checked="checked"' : '').'></th>';
	$JSON['scoreR'].='<th class="Alternate"><input class="ShootsFirst" so="0" type="radio" id="first['.$TeamEvent.']['.$Event.']['.$MatchIdR.']['.$i.']" name="first['.$i.']" onclick="setShootingFirst(this)" '.($ShootsFirstR ? 'checked="checked"' : '').'></th>';
	$JSON['scoreL'].='<th>'.($i+1).'</th>';
	$JSON['scoreR'].='<th>'.($i+1).'</th>';
	for($j=0;$j<$JSON['config']['arrows'];$j++) {
		if($JSON['isAlternate']) {
			if(empty($Section['meta']['maxTeamPerson'])){
				if($ShootsFirstR) {
					$tabIndexL=$i*$JSON['config']['arrows']*2 + $j*2 + 2;
					$tabIndexR=$i*$JSON['config']['arrows']*2 + $j*2 + 1;
				} else {
					$tabIndexL=$i*$JSON['config']['arrows']*2 + $j*2 + 1;
					$tabIndexR=$i*$JSON['config']['arrows']*2 + $j*2 + 2;
				}
			} else {
				if($ShootsFirstR) {
					$tabIndexL=$i*$JSON['config']['arrows']*2 + intval($j/$Section['meta']['maxTeamPerson'])*$JSON['config']['arrows'] + $Section['meta']['maxTeamPerson'] + $j%$Section['meta']['maxTeamPerson'] + 1;
					$tabIndexR=$i*$JSON['config']['arrows']*2 + intval($j/$Section['meta']['maxTeamPerson'])*$JSON['config']['arrows'] + $j%$Section['meta']['maxTeamPerson'] + 1;
				} else {
					$tabIndexL=$i*$JSON['config']['arrows']*2 + intval($j/$Section['meta']['maxTeamPerson'])*$JSON['config']['arrows'] + $j%$Section['meta']['maxTeamPerson'] + 1;
					$tabIndexR=$i*$JSON['config']['arrows']*2 + intval($j/$Section['meta']['maxTeamPerson'])*$JSON['config']['arrows'] + $Section['meta']['maxTeamPerson'] + $j%$Section['meta']['maxTeamPerson'] + 1;
				}
			}
		} else {
			$tabIndexL=$i*$JSON['config']['arrows'] + $j + 1;
			$tabIndexR=$JSON['config']['arrows']*$JSON['config']['ends'] + 3*$JSON['config']['so'] + $i*$JSON['config']['arrows'] + $j + 1;
		}
		$arIndex=$i*$JSON['config']['arrows'] + $j;
		$JSON['scoreL'].='<td><input type="text" tabindex="'.($TabIndexOffset + $tabIndexL).'" id="Arrow['.$MatchIdL.'][0]['.$i.']['.$j.']" onfocus="selectArrow(this)" onblur="updateArrow(this)" value="'.trim(DecodeFromLetter($Match['arrowstring'][$arIndex])).'"></td>';
		$JSON['scoreR'].='<td><input type="text" tabindex="'.($TabIndexOffset + $tabIndexR).'" id="Arrow['.$MatchIdR.'][0]['.$i.']['.$j.']" onfocus="selectArrow(this)" onblur="updateArrow(this)" value="'.trim(DecodeFromLetter($Match['oppArrowstring'][$arIndex])).'"></td>';
	}
	$JSON['scoreL'].='<td id="EndTotalL_'.$i.'">'.$Match['setPoints'][$i].'</td>';
	$JSON['scoreR'].='<td id="EndTotalR_'.$i.'">'.$Match['oppSetPoints'][$i].'</td>';
	if($Section['meta']['matchMode']) {
		$JSON['scoreL'].='<td id="EndSetL_'.$i.'">'.$Match['setPointsByEnd'][$i].'</td>';
		$JSON['scoreR'].='<td id="EndSetR_'.$i.'">'.$Match['oppSetPointsByEnd'][$i].'</td>';
		$totL+=($Match['setPointsByEnd'][$i] ? $Match['setPointsByEnd'][$i] : 0);
		$totR+=($Match['oppSetPointsByEnd'][$i] ? $Match['oppSetPointsByEnd'][$i] : 0);
	} else {
		$totL+=($Match['setPoints'][$i] ? $Match['setPoints'][$i] : 0);
		$totR+=($Match['oppSetPoints'][$i] ? $Match['oppSetPoints'][$i] : 0);
	}
	$JSON['scoreL'].='<td id="TotalL_'.$i.'">'.$totL.'</td>';
	$JSON['scoreR'].='<td id="TotalR_'.$i.'">'.$totR.'</td>';
	$JSON['scoreL'].='</tr>';
	$JSON['scoreR'].='</tr>';
}

// Shoot Offs
$Match['tiebreak']=str_pad($Match['tiebreak'],3*$JSON['config']['so'], ' ', STR_PAD_RIGHT);
$Match['oppTiebreak']=str_pad($Match['oppTiebreak'],3*$JSON['config']['so'], ' ', STR_PAD_RIGHT);
$Match['tiebreakDecoded']=array_pad(explode(',', $Match['tiebreakDecoded']), 3,'');
$Match['oppTiebreakDecoded']=array_pad(explode(',', $Match['oppTiebreakDecoded']), 3,'');
$totL=0;
$totR=0;
$ShootsFirstL=$Match['shootFirst'] & pow(2, $JSON['config']['ends']);
$ShootsFirstR=$Match['oppShootFirst'] & pow(2, $JSON['config']['ends']);
/*
$JSON['scoreL'].='<tr class="SO" so="1" end="0">';
$JSON['scoreR'].='<tr class="SO" so="1" end="0">';
$JSON['scoreL'].='<th class="Alternate" rowspan="'.($JSON['config']['soEnds']).'"><input class="ShootsFirst" so="1" type="radio" id="first[' . $TeamEvent.']['.$Event.']['.$MatchIdL.']['.$JSON['config']['ends'].']" name="first[so]" onclick="setShootingFirst(this)" '.($ShootsFirstL ? 'checked="checked"' : '').'></th>';
$JSON['scoreR'].='<th class="Alternate" rowspan="'.($JSON['config']['soEnds']).'"><input class="ShootsFirst" so="1" type="radio" id="first[' . $TeamEvent.']['.$Event.']['.$MatchIdR.']['.$JSON['config']['ends'].']" name="first[so]" onclick="setShootingFirst(this)" '.($ShootsFirstR ? 'checked="checked"' : '').'></th>';
$JSON['scoreL'].='<th rowspan="'.($JSON['config']['soEnds']).'">S.O.</th>';
$JSON['scoreR'].='<th rowspan="'.($JSON['config']['soEnds']).'">S.O.</th>';
$JSON['scoreL'].='<td class="Center" colspan="' . $JSON['config']['arrows'] . '">';
$JSON['scoreR'].='<td class="Center" colspan="' . $JSON['config']['arrows'] . '">';
for($i = 0; $i < $JSON['config']['so']; ++$i) {
	if($JSON['isAlternate']) {
		if($ShootsFirstR) {
			$tabIndexL=$JSON['config']['ends']*$JSON['config']['arrows']*2 + $i*2 + 2;
			$tabIndexR=$JSON['config']['ends']*$JSON['config']['arrows']*2 + $i*2 + 1;
		} else {
			$tabIndexL=$JSON['config']['ends']*$JSON['config']['arrows']*2 + $i*2 + 1;
			$tabIndexR=$JSON['config']['ends']*$JSON['config']['arrows']*2 + $i*2 + 2;
		}
	} else {
        $tabIndexL=$JSON['config']['ends']*$JSON['config']['arrows']*2 + 3*$JSON['config']['so'] + $i + 1;
		$tabIndexR=$JSON['config']['ends']*$JSON['config']['arrows']*2 + 3*$JSON['config']['so'] + $i + 2;
	}
	$arIndex=$i;
	$JSON['scoreL'].='<div><input type="text" id="Arrow['.$MatchIdL.'][1][0]['.$i.']" tabindex="'.($TabIndexOffset + $tabIndexL).'" onfocus="selectArrow(this)" onblur="updateArrow(this)" value="'.trim(DecodeFromLetter($Match['tiebreak'][$arIndex])).'"></div>';
	$JSON['scoreR'].='<div><input type="text" id="Arrow['.$MatchIdR.'][1][0]['.$i.']" tabindex="'.($TabIndexOffset + $tabIndexR).'" onfocus="selectArrow(this)" onblur="updateArrow(this)" value="'.trim(DecodeFromLetter($Match['oppTiebreak'][$arIndex])).'"></div>';
}
$JSON['scoreL'].='</td>';
$JSON['scoreR'].='</td>';
$JSON['scoreL'] .= '<td class="Bold" id="EndTotalL_SO_0">'.$Match['tiebreakDecoded'][0].'</td>';
$JSON['scoreR'] .= '<td class="Bold" id="EndTotalR_SO_0">'.$Match['oppTiebreakDecoded'][0].'</td>';
if ($Section['meta']['matchMode']) {
	$JSON['scoreL'] .= '<td class="Right" rowspan="'.($JSON['config']['soEnds']).'">' . get_text('Total') . '</td>';
	$JSON['scoreR'] .= '<td class="Right" rowspan="'.($JSON['config']['soEnds']).'">' . get_text('Total') . '</td>';
	$JSON['scoreL'] .= '<td class="Bold" rowspan="'.($JSON['config']['soEnds']).'" id="EndSetL_SO">'.$Match['setScore'].'</td>';
	$JSON['scoreR'] .= '<td class="Bold" rowspan="'.($JSON['config']['soEnds']).'" id="EndSetR_SO">'.$Match['oppSetScore'].'</td>';
} else {
	$JSON['scoreL'] .= '<td class="Bold" rowspan="'.($JSON['config']['soEnds']).'" id="TotalL_SO"></td>';
	$JSON['scoreR'] .= '<td class="Bold" rowspan="'.($JSON['config']['soEnds']).'" id="TotalR_SO"></td>';
}
$JSON['scoreL'].='</tr>';
$JSON['scoreR'].='</tr>';
*/
for($pSo=0; $pSo<$JSON['config']['soEnds']; $pSo++ ) {

	$JSON['scoreL'].='<tr class="SO" so="1" end="'.$pSo.'">';
	$JSON['scoreR'].='<tr class="SO" so="1" end="'.$pSo.'">';
	if($pSo==0) {
        $JSON['scoreL'].='<th class="Alternate" rowspan="'.($JSON['config']['soEnds']).'"><input class="ShootsFirst" so="1" type="radio" id="first[' . $TeamEvent.']['.$Event.']['.$MatchIdL.']['.$JSON['config']['ends'].']" name="first[so]" onclick="setShootingFirst(this)" '.($ShootsFirstL ? 'checked="checked"' : '').'></th>';
        $JSON['scoreR'].='<th class="Alternate" rowspan="'.($JSON['config']['soEnds']).'"><input class="ShootsFirst" so="1" type="radio" id="first[' . $TeamEvent.']['.$Event.']['.$MatchIdR.']['.$JSON['config']['ends'].']" name="first[so]" onclick="setShootingFirst(this)" '.($ShootsFirstR ? 'checked="checked"' : '').'></th>';
        $JSON['scoreL'].='<th rowspan="'.($JSON['config']['soEnds']).'">S.O.</th>';
        $JSON['scoreR'].='<th rowspan="'.($JSON['config']['soEnds']).'">S.O.</th>';

    }

	$JSON['scoreL'].='<td class="Center" colspan="' . $JSON['config']['arrows'] . '">';
	$JSON['scoreR'].='<td class="Center" colspan="' . $JSON['config']['arrows'] . '">';
	for ($i = 0; $i < $JSON['config']['so']; $i++) {
		if($JSON['isAlternate']) {
			if($ShootsFirstR) {
				$tabIndexL=$JSON['config']['ends']*$JSON['config']['arrows']*2 + $pSo*$JSON['config']['so']*2 + $i*2 + 2;
				$tabIndexR=$JSON['config']['ends']*$JSON['config']['arrows']*2 + $pSo*$JSON['config']['so']*2 + $i*2 + 1;
			} else {
				$tabIndexL=$JSON['config']['ends']*$JSON['config']['arrows']*2 + $pSo*$JSON['config']['so']*2 + $i*2 + 1;
				$tabIndexR=$JSON['config']['ends']*$JSON['config']['arrows']*2 + $pSo*$JSON['config']['so']*2 + $i*2 + 2;
			}
		} else {
            $tabIndexL=$JSON['config']['ends']*$JSON['config']['arrows']*2 + 3*$JSON['config']['so'] + $pSo*$JSON['config']['so'] + $i +1;
            $tabIndexR=$JSON['config']['ends']*$JSON['config']['arrows']*2 + 3*$JSON['config']['so'] + $pSo*$JSON['config']['so'] + $i + $JSON['config']['so'] +1;
		}
		$arIndex=$pSo*$JSON['config']['so'] + $i;
		$JSON['scoreL'].='<div><input type="text" id="Arrow['.$MatchIdL.'][1]['.$pSo.']['.$i.']" tabindex="'.($TabIndexOffset + $tabIndexL).'" onfocus="selectArrow(this)" onblur="updateArrow(this)" value="'.(strlen($Match['tiebreak'])>$arIndex ? trim(DecodeFromLetter($Match['tiebreak'][$arIndex])) : '').'">'.
            ($pSo == ($JSON['config']['soEnds']-1) ? '<div class="newSoNeeded" style="display: none;"><input type="button" value="*"  onclick="addStar(\'Arrow['.$MatchIdL.'][1]['.$pSo.']['.$i.']\')"><input type="button" value="+1" onclick="addPoint(\'Arrow['.$MatchIdL.'][1]['.$pSo.']['.$i.']\')"></div>' : ''). '</div>';
		$JSON['scoreR'].='<div><input type="text" id="Arrow['.$MatchIdR.'][1]['.$pSo.']['.$i.']" tabindex="'.($TabIndexOffset + $tabIndexR).'" onfocus="selectArrow(this)" onblur="updateArrow(this)" value="'.(strlen($Match['oppTiebreak'])>$arIndex ? trim(DecodeFromLetter($Match['oppTiebreak'][$arIndex])):'').'">'.
            ($pSo == ($JSON['config']['soEnds']-1) ? '<div class="newSoNeeded" style="display: none;"><input type="button" value="*"  onclick="addStar(\'Arrow['.$MatchIdR.'][1]['.$pSo.']['.$i.']\')"><input type="button" value="+1"  onclick="addPoint(\'Arrow['.$MatchIdR.'][1]['.$pSo.']['.$i.']\')"></div>' : ''). '</div>';
	}
	$JSON['scoreL'] .= '<td class="Bold" id="EndTotalL_SO_'.$pSo.'">'.(array_key_exists($pSo,$Match['tiebreakDecoded']) ? $Match['tiebreakDecoded'][$pSo] : '').'</td>';
	$JSON['scoreR'] .= '<td class="Bold" id="EndTotalR_SO_'.$pSo.'">'.(array_key_exists($pSo,$Match['oppTiebreakDecoded']) ? $Match['oppTiebreakDecoded'][$pSo] : '').'</td>';
	$JSON['scoreL'].='</td>';
	$JSON['scoreR'].='</td>';
	$JSON['scoreL'].='</tr>';
	$JSON['scoreR'].='</tr>';
}

$JSON['scoreL'].='<tr><td align="center" colspan="'.(6+$JSON['config']['arrows']).'">'.
	'<input '.(($Match['status'] & 1) ? 'disabled="disabled"' : '').' type="button" id="confirm['.$MatchIdL.']" onclick="ConfirmEnd(this)" value="'.get_text('ConfirmEnd', 'Tournament').'">'.
	'<input class="newSoNeeded" style="display: none; margin-left: 10px;" type="button" onclick="buildScorecard()" value="'.get_text('NewSORequired', 'Tournament').'"></td>'.
	'</td></tr>';
$JSON['scoreR'].='<tr><td align="center" colspan="'.(6+$JSON['config']['arrows']).'">'.
	'<input '.(($Match['oppStatus'] & 1) ? 'disabled="disabled"' : '').' type="button" id="confirm['.$MatchIdR.']" onclick="ConfirmEnd(this)" value="'.get_text('ConfirmEnd', 'Tournament').'">'.
	'<input class="newSoNeeded" style="display: none; margin-left: 10px;" type="button" onclick="buildScorecard()" value="'.get_text('NewSORequired', 'Tournament').'"></td>'.
	'</td></tr>';

$JSON['scoreL'].='</table>';
$JSON['scoreR'].='</table>';

if($Arrows) {
	// builds an empty target
	require_once('Common/Obj_Target.php');
	$target = new Obj_Target();

// we already have most of the data needed for the target!
	$target->initSVG($_SESSION['TourId'], $Event, $MatchId, $TeamEvent);
	$target->setSVGHeader('', '');
	$target->setTarget();

	for($i=0;$i<$JSON['config']['ends'];$i++) {
		$tmpL=array();
		$tmpR=array();
		for($j=0;$j<$JSON['config']['arrows'];$j++) {
			if(empty($Match['arrowPosition'][$i*$JSON['config']['arrows']+$j])) {
				$tmpL['SvgArrow['.$MatchIdL.'][0]['.$i.']['.$j.']']=array('D' => 999, 'X'=>-2000, 'Y'=>-2000, 'R'=>3);
			} else {
				$tmpL['SvgArrow['.$MatchIdL.'][0]['.$i.']['.$j.']']=$Match['arrowPosition'][$i*$JSON['config']['arrows']+$j];
			}
			if(empty($Match['oppArrowPosition'][$i*$JSON['config']['arrows']+$j])) {
				$tmpR['SvgArrow['.$MatchIdR.'][0]['.$i.']['.$j.']']=array('D' => 999, 'X'=>-2000, 'Y'=>-2000, 'R'=>3);
			} else {
				$tmpR['SvgArrow['.$MatchIdR.'][0]['.$i.']['.$j.']']=$Match['oppArrowPosition'][$i*$JSON['config']['arrows']+$j];
			}
		}
		$target->drawSVGArrowsGroups('SvgEndL_'.$i, $tmpL);
		$target->drawSVGArrowsGroups('SvgEndR_'.$i, $tmpR);
	}
	for($i=0;$i<$JSON['config']['soEnds'];$i++) {
		$tmpL=array();
		$tmpR=array();
		for($j=0;$j<$JSON['config']['so'];$j++) {
			if(empty($Match['tiePosition'][$i*$JSON['config']['so']+$j])) {
				$tmpL['SvgArrow['.$MatchIdL.'][1]['.$i.']['.$j.']']=array('D' => 999, 'X'=>-2000, 'Y'=>-2000,'R'=>3);
			} else {
				$tmpL['SvgArrow['.$MatchIdL.'][1]['.$i.']['.$j.']']=$Match['tiePosition'][$i*$JSON['config']['so']+$j];
			}
			if(empty($Match['oppTiePosition'][$i*$JSON['config']['so']+$j])) {
				$tmpR['SvgArrow['.$MatchIdR.'][1]['.$i.']['.$j.']']=array('D' => 999, 'X'=>-2000, 'Y'=>-2000,'R'=>3);
			} else {
				$tmpR['SvgArrow['.$MatchIdR.'][1]['.$i.']['.$j.']']=$Match['oppTiePosition'][$i*$JSON['config']['so']+$j];
			}
		}
		$target->drawSVGArrowsGroups('SvgEndL_SO_'.$i, $tmpL);
		$target->drawSVGArrowsGroups('SvgEndR_SO_'.$i, $tmpR);
	}

	$JSON['targetSize']=$target->Diameter;
	$JSON['targetZoom']=round(sqrt($target->TargetRadius)/7, 1);
	$JSON['target']=$target->OutputStringSVG();
}

JsonOut($JSON);