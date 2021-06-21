<?php
/* ----------------------------------------------------------------------
 * app/views/admin/access/museum_edit_html.php :
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

	$i_label = $this->getVar('label');
  $i_code = $this->getVar('code');
  $i_table = $this->getVar('table');
  $i_settings = $this->getVar('settings');
  $i_items = $this->getVar('items');

?>
            <div class ="container">
            <h1 class="display-4"><?php print $i_label[name]; ?></h1>
            <p class="lead"><?php print $i_code; ?> | <?php print $i_table; ?></p>
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
			      <li class="nav-item">
              <a class="nav-link active" id="pills-Settings-tab" data-toggle="pill" href="#pills-Settings" role="tab" aria-controls="pills-Settings" aria-selected="true">Settings</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="pills-Items-tab" data-toggle="pill" href="#pills-Items" role="tab" aria-controls="pills-Items" aria-selected="false">Items</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="pills-contact-tab" data-toggle="pill" href="#pills-contact" role="tab" aria-controls="pills-contact" aria-selected="false">Contact</a>
            </li>
          </ul>
          <div class="" id="pills-tabContent">
            <div class="" id="pills-Settings" role="tabpanel" aria-labelledby="pills-Settings-tab">
			
            <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">Name</th>
                <th scope="col">value</th>
              </tr>
            </thead>
            <tbody>
            <?php
              foreach ($i_settings as $key => $value) {
                    $s_print = (is_array($value)?implode(",",$value):$value);
                    ?>
                  <tr>
                  <th scope="row"><?php print $key; ?></th>
                  <td><?php print $s_print; ?></td>
                  </tr>
              <?php
            }
            ?>
              </tbody>
            </table>
            <button type="button" class="btn btn-primary">+</button>
          </div>
            <div class="" id="pills-Items" role="tabpanel" aria-labelledby="pills-profItemsile-tab">';
            <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">item_id</th>
                <th scope="col">group</th>
                <th scope="col">source</th>
                <th scope="col">destination</th>
                <th scope="col">settings</th>
              </tr>
            </thead>
            <tbody>
            <?php
            foreach ($i_items as $name => $item) {
              ?>
                <tr>
                <th scope="row"><?php print $name; ?></th>
                <td><?php print $item["group_id"]; ?></td>
                <td><?php print $item["source"]; ?></td>
                <td><?php print $item["destination"]; ?></td>
                <td><?php print_r($item["settings"]);?></td>
                </tr>
                <?php 
            }
            ?>
              </tbody>
                </table>
            </div>
            <div class="" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">...</div>
          </div>

			</div>