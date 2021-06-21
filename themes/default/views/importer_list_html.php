<?php
/* ----------------------------------------------------------------------
 * app/views/admin/access/museum_list_html.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2008-2016 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
	$importers = $this->getVar('importers');

	//$myArryy = array($importers);
	//$myJSON2 = json_encode($myArryy);
//	$myJSON2=var_dump(json_encode($myArryy));    

	print_r($importers);

	echo json_encode($importers);


/*

print_r(json_encode($myArryy));
$stack = array("");
array_push($stack,$importers);
$myJSON2 = json_encode($stack,true);

print_r($myJSON2);
	array_push($myArr,$i_label);

	$myArryy = array($importers);

$myJSON2 = json_encode($myArryy);
echo $myArryy;
*/


  foreach ($importers as $key => $importer) {
	  ?>
			<h4><?php print $key;
			print caNavButton($this->request, __CA_NAV_ICON_EDIT__, _t("Edit"), '', 'importer', 'importer', 'Edit', array('importer' => $key), array(), array('icon_position' => __CA_NAV_ICON_ICON_POS_LEFT__, 'use_class' => 'list-button', 'no_background' => true, 'dont_show_content' => true));
					 ?></h4>
			<table class="table">
            <thead>
              <tr>
                <th scope="col">Name</th>
                <th scope="col">value</th>
				
              </tr>
            </thead>
            <tbody>
				<?php
					foreach ($importer as $key => $value) {
					if( $key <> 'settings' && $key <> 'rules' && !empty($value)){
						$v_print = (is_array($value)?implode(",",$value):$value);
				?>
                    <tr>
                    <th scope="row"><?php print $key; ?></th>
                    <td><?php print $v_print; ?></td>
					 </tr>
                    <tr>
				<?php
					}
				}
				foreach ($importer['settings'] as $key => $value) {
					if( !empty($value)){
				?>
                    <tr>
                    <th scope="row"><?php print $key; ?></th>
                    <td><?php print $value; ?></td>
					</tr>
                    <tr>
				<?php 
					}
				}
				?>
            	</tbody>
            </table>
	<?php 
		}
	?>
