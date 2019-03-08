<?php
require_once(dirname(__FILE__).'/config.php');

$Block=max(1, (empty($_REQUEST['block']) ? 1 : intval($_REQUEST['block'])));
$OrgBlock=$Block;
$SubBlock=max(1, (empty($_REQUEST['subblock']) ? 1 : intval($_REQUEST['subblock'])));

$pagine=array();

// get the defaults of the rule
$q=safe_r_sql("select TVRules.*, ToPrintLang from TVRules inner join Tournament on TVRTournament=ToId where TVRTournament=$TourId order by TVRId=$Rule desc, TVRId limit 1");
if(!($RULE=safe_fetch($q))) die('no rules... create one!');

@define('PRINTLANG', $RULE->ToPrintLang);

// Estraggo gli spezzoni di regola
$Select = "SELECT * FROM TVSequence "
		. "WHERE TVSRule=$Rule AND TVSTournament=$TourId "
		. "ORDER BY TVSOrder ";

$Rs=safe_r_sql($Select);
if(!safe_num_rows($Rs)) {
	die('No such rule!');
}

if($Block>safe_num_rows($Rs)) {
	$Block=1;
}

// $NextBlock=$Block+1;
$OrgBlock=$Block;

$RotMatches=false;
while($Block) {
	$r=safe_fetch($Rs);
	$Block--;
}

$tmp='';
switch($r->TVSTable) {
	case 'DB':
		$t=safe_r_sql("select * from TVParams where TVPId=$r->TVSContent AND TVPTournament=$r->TVSTournament");
		$tmp=create_Comp_rot($Page=safe_fetch($t), $RULE);
		break;
	case 'MM':
		// default id fadin/fadeout!
		$t=safe_r_sql("select * from TVContents where TVCId=$r->TVSContent AND TVCTournament=" . ($r->TVSCntSameTour==1 ? $r->TVSTournament : "-1"));
		$tmp=create_MM_rot($Page=safe_fetch($t), $r);
		break;
}

if($tmp) {
	$CSS=$tmp['CSS'];

	if(isset($_REQUEST['debug'])) {
		echo '<style>'.file_get_contents('rot.css').'</style>';
		// gets the css...
		echo getCss($TourId, $Rule);
		echo '<style>div {border:1px solid red; } #content {height:auto;}</style>';
	}


	echo getCssPage($CSS, $tmp['Block'], $tmp['BlockCss'], $RULE->TVRSettings);

	$NextBlock=$OrgBlock;
	if(intval($tmp['NextSubBlock'])>intval($tmp['SubBlocks'])) {
		$NextBlock++;
		$tmp['NextSubBlock']=1;
	}

	echo '<div id="Settings" NextBlock="'.$NextBlock.'" NextSubBlock="'.$tmp['NextSubBlock'].'" StopTime="'.$tmp['StopTime'].'" ScrollTime="'.$tmp['ScrollTime'].'"></div>';
	echo $tmp['Html'];
}
die();


function create_MM_rot($Content, $Segment) {
	global $CFG, $IsCode;
	$ret=array('Html'=>'',
			'Block' => '',
			'BlockCss' => '',
			'NextSubBlock' => 1,
			'SubBlocks' => 1,
			'CSS' => array('js-marquee-wrapper'=>'height:100%', 'js-marquee' => 'height:calc(100%)'));

	$ret['StopTime'] = intval($Segment->TVSTime*1000);
	$ret['ScrollTime'] = $Segment->TVSScroll*1000;

	$ret['Html']='<div id="ImageContent" class="'.($Segment->TVSFullScreen ? 'FullImage' : '').'">';
	switch($Content->TVCMimeType) {
		case 'image/gif':
		case 'image/jpeg':
		case 'image/png':
			$ret['Html'].='<img src="'.$CFG->ROOT_DIR.'TV/Photos/TV-'.($Content->TVCTournament==-1 ? 'BaseIanseo' : $IsCode).'-'.$Content->TVCId.'.jpg">';
			break;
		case 'text/html':
			$ret['Html'].='<div valign="middle" align="center">'.$Content->TVCContent.'</div>';
			break;
		default:
			$ret['Html'].='<div>Unknown MIME-TYPE</div>';
	}
	$ret['Html'].='</div>';
	return $ret;
}

function create_Comp_rot($TVsettings, $RULE) {
	global $Arr_Pages, $CFG, $RotMatches, $TourId;
	$ret=array();

	$ret['StopTime'] = $TVsettings->TVPTimeStop*1000;
	$ret['ScrollTime'] = $TVsettings->TVPTimeScroll*1000;

	if(!$TVsettings->TVPDefault) {
		$ST['TV_Carattere']=$TVsettings->TVP_Carattere;
		$ST['TV_TR_BGColor']=$TVsettings->TVP_TR_BGColor;
		$ST['TV_TRNext_BGColor']=$TVsettings->TVP_TRNext_BGColor;
		$ST['TV_TR_Color']=$TVsettings->TVP_TR_Color;
		$ST['TV_TRNext_Color']=$TVsettings->TVP_TRNext_Color;
		$ST['TV_Content_BGColor']=$TVsettings->TVP_Content_BGColor;
		$ST['TV_Page_BGColor']=$TVsettings->TVP_Page_BGColor;
		$ST['TV_TH_BGColor']=$TVsettings->TVP_TH_BGColor;
		$ST['TV_TH_Color']=$TVsettings->TVP_TH_Color;
		$ST['TV_THTitle_BGColor']=$TVsettings->TVP_THTitle_BGColor;
		$ST['TV_THTitle_Color']=$TVsettings->TVP_THTitle_Color;
	}


	switch($TVsettings->TVPPage) {
		case 'ALFA':
		case 'LIST':
		case 'LSPH':
		case 'QUAL':
		case 'QUALS':
		case 'QUALT';
		case 'QUALC':
		case 'ABS':
		case 'ABST':
		case 'ABSS':
		case 'ELIM':
		case 'FIN':
		case 'FINT':
		case 'BLABS':
		case 'RANK':
		case 'RANKT':
			include_once('rot-'.$TVsettings->TVPPage.'.php');
			break;
		default:
			debug_svela($TVsettings->TVPPage);
	}

	$Fun='rot'.$TVsettings->TVPPage;

	$res=$Fun($TVsettings, $RULE);

	$ret['CSS']=$res['CSS'];
	$ret['Html']=$res['html'];
	$ret['Block']=$res['Block'];
	$ret['BlockCss']=$res['BlockCss'];
	$ret['NextSubBlock']=$res['NextSubBlock'];
	$ret['SubBlocks']=$res['SubBlocks'];


// 		case 'ABST':
// 			include('Rot_abs_t.php');
// 			break;
// 		case 'ELIM':
// 			include('Rot_elim.php');
// 			$RotMatches=true;
// 			break;
// 		case 'FINT':
// 			include('Rot_fin_t.php');
// 			$RotMatches=true;
// 			break;
// 		case 'RAND':
// 			include('Rot_athl_sch.php');
// 			break;

// 		case 'F2FLST':
// 			include('Rot_StartlistF2F.php');
// 			$RotMatches=true;
// 			break;

// 		case 'F2FABS':
// 			include('Rot_ElimF2F.php');
// 			$RotMatches=true;
// 			break;

// 		case 'NLCLST':
// 			include('Rot_StartlistNLChamps.php');
// 			$RotMatches=true;
// 			break;

// 		case 'NLCABS':
// 			include('Rot_ElimNLChamps.php');
// 			$RotMatches=true;
// 			break;

// 		case 'MEDL':
// 			include('Rot_MedalList.php');
// 			$RotMatches=true;
// 			break;

// 	}

	return $ret;
}

/*

function rotQuals($TVsettings, $RULE) {
	global $CFG, $IsCode, $TourId, $SubBlock;
	$Return=array(
		'html' => '',
		'Block' => 'QualRow',
		'BlockCss' => 'height:2em; width:100%; padding-right:0.5rem; overflow:hidden; font-size:2em; display:flex; flex-direction:row; justify-content:space-between; align-items:center; box-sizing:border-box;',
		'NextSubBlock' => 1);
	$ret=array();

	$Return['html']=implode('', $ret);
	return$Return;
}

function rotQualsSettings($Settings) {
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

	foreach($PageDefaults as $key => $Value) {
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
		'Title' => '',
		'RankOld' => 'background-repeat:no-repeat; background-size: contain; background-position:center;color:#FFFFFF; font-weight:bold; font-size:60%;',
		'RankNone' => '',
		'RankUp' => 'background: url(\'' . $CFG->ROOT_DIR . 'Common/Images/Up.png\');',
		'RankDown' => 'background: url(\'' . $CFG->ROOT_DIR . 'Common/Images/Down.png\');',
		'RankMinus' => 'background: url(\'' . $CFG->ROOT_DIR . 'Common/Images/Minus.png\');',
		'Rank' => 'flex: 0 0 4rem; text-align:right;',
		'CountryCode' => 'flex: 1 0 5rem; font-size:0.5em; margin-left:-3.5rem',
		'FlagDiv' => 'flex: 0 0 3.95rem;',
		'Flag' => 'height:2.5rem; border:0.1rem solid #888;',
		'Target' => 'flex: 0 0 4rem; font-size:75%; text-align:right;',
		'Athlete' => 'flex: 1 1 3rem;',
		'CountryDescr' => 'flex: 1 1 1rem;',
		'DistScore' => 'flex: 0 0 5rem; text-align:right; font-size:0.8em;',
		'DistPos' => 'flex: 0 0 3rem; text-align:left; font-size:0.7em;',
		'Score' => 'flex: 0 0 6rem; text-align:right; font-size:1.25em;margin-right:0.5rem;',
		'Gold' => 'flex: 0 0 3rem; text-align:right; font-size:1em;',
		'XNine' => 'flex: 0 0 3rem; text-align:right; font-size:1em;',
	);
	foreach($ret as $k=>$v) {
		if(!isset($RMain[$k])) $RMain[$k]=$v;
	}
	return $ret;
}

function b() {



 * */
