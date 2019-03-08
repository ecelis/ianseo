<?php

require_once(dirname(__FILE__) . '/cfg.php');

// Check the coherence of Qualifications and Entries tables
safe_w_sql("insert ignore into Qualifications (QuId) select EnId from Entries left join Qualifications on EnId=QuId where QuId is null and EnTournament in ($TourId)");

// $=(isset($_REQUEST['']) ? intval($_REQUEST['']) : 0);
// $=(isset($_REQUEST['']) ? intval($_REQUEST['']) : 0);

$PAGE_TITLE=get_text('TourPartecipants','Tournament');

$JS_SCRIPT=array(
	phpVars2js(array(
		'StrAreYouSure'=>get_text('MsgAreYouSure')
	)),
	'<script type="text/javascript" src="'.$CFG->ROOT_DIR.'Common/jQuery/jquery-2.1.4.min.js"></script>',
	'<script type="text/javascript" src="index.js"></script>',
);

$ONLOAD=' onload="getRows()"';

include('Common/Templates/head.php');
?>
<div id="Accreditation" sort="<?php echo $Sort; ?>" sortorder="<?php echo $SortOrder ;?>">
<div class="Title"><?php echo get_text('TourPartecipants','Tournament') ?></div>
<div class="GenFlex">
	<div><input type="button" onclick="addRow()" value="<?php print get_text('CmdAdd','Tournament'); ?>"></div>
	<div><input onclick="getRows()" type="checkbox" id="AllTargets"   <?php echo ($AllTargets   ? ' checked="checked"' : ''); ?>><?php print get_text('AllTargets','Tournament');?></div>
	<div><input onclick="getRows()" type="checkbox" id="ShowTourCode" <?php echo ($ShowTourCode ? ' checked="checked"' : ''); ?>><?php print get_text('ShowTourCode','Tournament');?></div>
	<div><input onclick="getRows()" type="checkbox" id="ShowLocalBib" <?php echo ($ShowLocalBib ? ' checked="checked"' : ''); ?>><?php print get_text('ShowLocalCode','Tournament');?></div>
	<div><input onclick="getRows()" type="checkbox" id="ShowEmail"    <?php echo ($ShowEmail    ? ' checked="checked"' : ''); ?>><?php print get_text('ShowEmail','Tournament');?></div>
	<div><input onclick="getRows()" type="checkbox" id="ShowCaption"  <?php echo ($ShowCaption  ? ' checked="checked"' : ''); ?>><?php print get_text('ShowCaption','Tournament');?></div>
	<div><input onclick="getRows()" type="checkbox" id="ShowCountry2" <?php echo ($ShowCountry2 ? ' checked="checked"' : ''); ?>><?php print get_text('ShowCountry2','Tournament');?></div>
	<div><input onclick="getRows()" type="checkbox" id="ShowCountry3" <?php echo ($ShowCountry3 ? ' checked="checked"' : ''); ?>><?php print get_text('ShowCountry3','Tournament');?></div>
	<div><input onclick="getRows()" type="checkbox" id="ShowDisable"  <?php echo ($ShowDisable  ? ' checked="checked"' : ''); ?>><?php print get_text('ShowDisable','Tournament');?></div>
	<div><input onclick="getRows()" type="checkbox" id="ShowAgeClass"  <?php echo ($ShowAgeClass  ? ' checked="checked"' : ''); ?>><?php print get_text('ShowAgeClass','Tournament');?></div>
	<div><input onclick="getRows()" type="checkbox" id="ShowSubClass"  <?php echo ($ShowSubClass  ? ' checked="checked"' : ''); ?>><?php print get_text('ShowSubClass','Tournament');?></div>
</div>
<div id="Rows"></div>
</div>
<?php
	include('Common/Templates/tail.php');
?>