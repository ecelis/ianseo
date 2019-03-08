<?php
// this code-bit goes into the Tournament/index.php

require_once('Common/Lib/Fun_Modules.php');

$ISKServerUrl=getModuleParameter('ISK', 'ServerUrl', '');
$ISKMode=getModuleParameter('ISK', 'Mode', '');

echo '<tr>
		<th class="TitleLeft" width="15%">'.get_text('ISK-ServerUrl','Api').'</th>
		<td>
		<input type="text" name="Module[ISK][ServerUrl]" value="'.$ISKServerUrl.'">
		</td>
		</tr>';

echo '<tr>
		<th class="TitleLeft" width="15%">'.get_text('ISK-EnableScore','Api').'</th>
			<td>
				<select name="Module[ISK][Mode]">
					<option value=""'.(empty($ISKMode) ? ' selected="selected"' : '').'>'.get_text('No').'</option>
					<option value="lite"'.($ISKMode=='lite' ? ' selected="selected"' : '').'>'.get_text('ISK-Name', 'Api').'</option>
					<option value="pro"'.($ISKMode=='pro' ? ' selected="selected"' : '').'>'.get_text('ISK-Pro-Name', 'Api').'</option>
					<option value="live"'.($ISKMode=='live' ? ' selected="selected"' : '').'>'.get_text('ISK-Live-Name', 'Api').'</option>
				</select>
			</td>
		</tr>';
