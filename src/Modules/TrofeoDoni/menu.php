<?php
$version='2011-05-13 08:13:00';

if(!empty($on) and $_SESSION['TourType']!=14) {
	$ret['MODS'][] = MENU_DIVIDER;
	$ret['MODS']['RenatoDoni'][] = 'Trofeo Renato Doni'.'|'.$CFG->ROOT_DIR.'Modules/TrofeoDoni/index.php|||_blank';
	$ret['MODS']['RenatoDoni'][] = get_text('MenuLM_Printout') .'|'.$CFG->ROOT_DIR.'Modules/TrofeoDoni/index.php|||_blank';
}
?>