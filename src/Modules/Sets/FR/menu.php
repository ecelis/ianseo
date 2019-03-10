<?php
/**
 * Created by PhpStorm.
 * User: deligant
 * Date: 13/01/18
 * Time: 14.17
 */

if(!empty($on) AND $_SESSION["TourLocRule"]=='FR' AND $acl[AclCompetition] >= AclReadOnly) {
    $ret['COMP']['EXPT'][] = MENU_DIVIDER;
    $ret['COMP']['EXPT'][] = get_text('MenuLM_Export-FR-Results') . '|' . $CFG->ROOT_DIR . 'Modules/Sets/FR/exports/';
}