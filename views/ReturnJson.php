<?php
/* ----------------------------------------------------------------------
 * ImportMapping/views/ReturnJson.php : 
 * ----------------------------------------------------------------------
 * Israel Ministry of Sports and Culture 
 * 
 * Plugin for CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * For more information about Israel Ministry of Sports and Culture visit:
 * https://www.gov.il/en/Departments/ministry_of_culture_and_sport
 *
 * For more information about CollectiveAccess visit:
 * http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license.
 *
 * This plugin for CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details. 
 * ----------------------------------------------------------------------
 */

header("Content-Type: application/json; charset=UTF-8");

$res = json_encode($this->getVar('data'), JSON_UNESCAPED_UNICODE);  // parse the array into json
print_r($res);

?>