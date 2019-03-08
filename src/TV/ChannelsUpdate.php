<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');

$JSON=array('error' => 1);

if(empty($_REQUEST['act'])) {
	header('Content-type: application/javascript');
	echo json_encode($JSON);
}

switch($_REQUEST['act']) {
	case 'save':
		if(!empty($_REQUEST['name'])) {
			safe_w_sql("insert into TVOut set
				TVOName=".StrSafe_DB($_REQUEST['name']).",
				TVOMessage=".StrSafe_DB($_REQUEST['msg']).",
				TVOUrl=".StrSafe_DB($_REQUEST['url']).",
				TVOTourCode=".StrSafe_DB($_REQUEST['tour']).",
				TVORuleId=".intval($_REQUEST['rule']).",
				TVORuleType=".intval($_REQUEST['status']).",
				TVOLastUpdate=now()");
			$JSON['newID']=safe_w_last_id();
		}
		$JSON['error']=0;
		break;
	case 'update':
		$SQL='';
		switch(true) {
			case !empty($_REQUEST['Name']):
				$tmp=each($_REQUEST['Name']);
				$SQL="update TVOut set TVOName=".StrSafe_DB($tmp[1])." where TVOId=".intval($tmp[0]);
				safe_w_sql($SQL);
				$JSON['error']=0;
				break;
			case !empty($_REQUEST['Message']):
				$tmp=each($_REQUEST['Message']);
				$SQL="update TVOut set TVOMessage=".StrSafe_DB($tmp[1])." where TVOId=".intval($tmp[0]);
				safe_w_sql($SQL);
				$JSON['error']=0;
				break;
			case !empty($_REQUEST['Url']):
				$tmp=each($_REQUEST['Url']);
				$SQL="update TVOut set TVOUrl=".StrSafe_DB($tmp[1])." where TVOId=".intval($tmp[0]);
				safe_w_sql($SQL);
				$JSON['error']=0;
				break;
			case !empty($_REQUEST['Tournament']):
				$tmp=each($_REQUEST['Tournament']);
				$SQL="update TVOut set TVOTourCode=".StrSafe_DB($tmp[1])." where TVOId=".intval($tmp[0]);
				safe_w_sql($SQL);
				$JSON['error']=0;
				$JSON['TVRules']=array();
				$q=safe_r_sql("select TVRId, TVRName from TVRules where TVRTournament=".intval(getIdFromCode($tmp[1]))." order by TVRId");
				while($r=safe_fetch($q)) {
					$JSON['TVRules'][$r->TVRId]=$r->TVRName;
				}
				break;
			case !empty($_REQUEST['Rule']):
				$tmp=each($_REQUEST['Rule']);
				$SQL="update TVOut set TVORuleId=".intval($tmp[1])." where TVOId=".intval($tmp[0]);
				safe_w_sql($SQL);
				$JSON['error']=0;
				break;
			case !empty($_REQUEST['Status']):
				$tmp=each($_REQUEST['Status']);
				$SQL="update TVOut set TVORuleType=".intval($tmp[1])." where TVOId=".intval($tmp[0]);
				safe_w_sql($SQL);
				$JSON['error']=0;
				break;
			case !empty($_REQUEST['Reload']):
				require_once('Common/Lib/Fun_Modules.php');
				setModuleParameter('TVOUT', 'Reload', 1);
				$JSON['error']=0;
				break;
		}

		break;
}

header('Content-type: application/javascript');
echo json_encode($JSON);

/*
 *

 => name
[msg] => free text
[url] => url
[tour] => 16WAIC
[rule] => 1
[status] => 0


TVOId (Primaria)	tinyint(4)	No
TVOName	varchar(50)	No
TVOUrl	text	No
TVOMessage	text	No
TVORuleId	int(11)	No
TVOTourCode	varchar(8)	No
TVORuleType	tinyint(4)	No
TVOLastUpdate	datetime


*/