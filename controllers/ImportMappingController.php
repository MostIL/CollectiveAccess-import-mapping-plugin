<?php
/* ----------------------------------------------------------------------
 * ImportMapping\controllers\ImportMappingController.php :
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

	header('Content-Type: text/html; charset=utf-8');
	 require_once('app/models/ca_data_importers.php');
	 require_once(__CA_LIB_DIR__.'/core/Configuration.php');

 	class ImportMappingController extends ActionController {

		
 		# -------------------------------------------------------
 		protected $opo_config;		// plugin configuration file
 		# -------------------------------------------------------
 		#
 		# -------------------------------------------------------
 		public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
 			parent::__construct($po_request, $po_response, $pa_view_paths);
 			$this->opo_config = Configuration::load($ps_plugin_path . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'ImportMapping.conf');
		 }
		 # -------------------------------------------------------
		 private function printError($errorText){
			$this->view->setVar('error',$errorText);

			$this->render('error_html.php');
		}
 		# --------------------------------------------------------
 		public function Index() {
 			$this->render('Index_html.php');
		 }
		 # -------------------------------------------------------
 		public function ImportMappingFieldsList($pa_options=null) {
			require_once(__CA_APP_DIR__.'/plugins/ImportMapping/lib/ImportMappingTools.php');
			$errorMessage = "";
				if ($this->ChackFile($_FILES["excelfile"], $errorMessage))
				{
					try{
						$t = new ImportMappingTools();
						$data = $t->CreateHeaderJson($_FILES["excelfile"]["tmp_name"]); // Create header and data json object.
						if(sizeof($data) > 0)
						{
							$this->view->setVar('data',$data);
							$this->render('ReturnJson.php');
						}
						//return json_decode("false");
						$this->view->setVar('data', array("id"=>"", "status"=>"Error"));
					}
					catch (exception $ex)
					{
						$this->printError($ex->errorMessage);
						$this->view->setVar('data', array("id"=>"", "status"=>"Error"));
					}			
				}
			else {
				$this->printError("Error");
				//return json_decode($errorMessage);  // Later i need to handle the error information
				$this->view->setVar('data', array("id"=>"", "status"=>"Error"));
			}
		}

		public function ImportMappingModel($pa_options=null) {
			require_once(__CA_APP_DIR__.'/plugins/ImportMapping/lib/ImportMappingTools.php');
			if(!isset($_POST["submit"])) {
				try{
					$t = new ImportMappingTools();
					$data = $t->CreateDataJson($_POST["Module"]); // ca_objects
					$this->view->setVar('data',$data);
					$this->render('ReturnJson.php');
					$this->view->setVar('data',array("id"=>"", "status"=>"success"));
				}
				catch(exception $ex)
				{
					$this->printError($ex->errorMessage);
					$this->view->setVar('data', array("id"=>"", "status"=>"Error"));
				}		
			}
			else {
				$this->printError("Error");	// TODO - Need to show information error 
				$this->view->setVar('data', array("id"=>"", "status"=>"Error"));
			}
		}

		public function SaveImportMapping($pa_options=null)
		{
			$pa_errors = "";
			$pa_options = "";

			if(!isset($_POST["submit"])) {
				//print_r($_POST["map"]);
				$data_mapping  = $_POST["map"]; // ca_objects  
				$data_settings  = $_POST["set"]; 
				//$this->view->setVar('data',$data);			
				
				if($data_mapping != null && $data_settings != null)
				{
					$objMapping = json_decode($data_mapping);
					$objSettings = json_decode($data_settings,true);
					$objSettings["inputFormats"] = explode(",", $objSettings["inputFormats"]);  		// If there is more than one format it will turn into an array
					$res = $this->LoadImportFromJson($objMapping, $objSettings, $pa_errors, $pa_options);

					if($res != "")
					{					 
						$this->view->setVar('data',array("id"=>$res->getPrimaryKey(), "status"=>"success"));
					}
					else
					{
						$this->view->setVar('data',array("id"=>"", "status"=>"Error"));
					}			
				}
			}
			else {
				$this->printError("Error");	// TODO - Need to show information error 
				$this->view->setVar('data',array("id"=>"", "status"=>"Error"));
			}
			$this->render('ReturnJson.php');
		}

		//----------------------Load import from Json-------------------------------------
		public function LoadImportFromJson($va_mapping, $va_settings, &$pa_errors, $pa_options=null)
		{

			$pa_errors = array();
			$t_importer = new ca_data_importers();
			$t_importer->setMode(ACCESS_WRITE);
			
			global $g_ui_locale_id;
			$vn_locale_id = (isset($pa_options['locale_id']) && (int)$pa_options['locale_id']) ? (int)$pa_options['locale_id'] : $g_ui_locale_id;

			$o_dm = Datamodel::load();
			if (!($t_instance = $o_dm->getInstanceByTableName($va_settings['table']))) {
				$pa_errors[] = _t("Mapping target table %1 is invalid\n", $va_settings['table']);
				if ($o_log) {  $o_log->logError(_t("[loadImporterFromFile:%1] Mapping target table %2 is invalid\n", $ps_source, $va_settings['table'])); }
				return;
			}
			
			if (!$va_settings['name']) { $va_settings['name'] = $va_settings['code']; }
			
			
			$t_importer = new ca_data_importers();
			$t_importer->setMode(ACCESS_WRITE);
			
			// Remove any existing mapping
			if ($t_importer->load(array('importer_code' => $va_settings['code']))) {
				$t_importer->delete(true, array('hard' => true));
				if ($t_importer->numErrors()) {
					$pa_errors[] = _t("Could not delete existing mapping for %1: %2", $va_settings['code'], join("; ", $t_importer->getErrors()))."\n";
					if ($o_log) { $o_log->logError(_t("[loadImporterFromFile:%1] Could not delete existing mapping for %2: %3", $ps_source, $va_settings['code'], join("; ", $t_importer->getErrors()))); }
					return null;
				}
			}

			// Create new mapping
			$t_importer->set('importer_code', $va_settings['code']);
			$t_importer->set('table_num', $t_instance->tableNum());   // "57" - $this->_DATAMODEL->getTableNum($this->TABLE);     
			$t_importer->set('rules', array('rules' => $va_rules, 'environment' => $va_environment));
			
			unset($va_settings['code']);
			unset($va_settings['table']);
			foreach($va_settings as $vs_k => $vs_v) {
				$t_importer->setSetting($vs_k, $vs_v);
			}
			$t_importer->insert();
			
			if ($t_importer->numErrors()) {
				$pa_errors[] = _t("Error creating mapping: %1", join("; ", $t_importer->getErrors()))."\n";
				if ($o_log) { $o_log->logError(_t("[loadImporterFromFile:%1] Error creating mapping: %2", $ps_source, join("; ", $t_importer->getErrors()))); }
				return null;
			}
			
			
			$t_importer->addLabel(array('name' => $va_settings['name']), $vn_locale_id, null, true);
			
			if ($t_importer->numErrors()) {
				$pa_errors[] = _t("Error creating mapping name: %1", join("; ", $t_importer->getErrors()))."\n";
				if ($o_log) {  $o_log->logError(_t("[loadImporterFromFile:%1] Error creating mapping: %2", $ps_source, join("; ", $t_importer->getErrors()))); }
				return null;
			}
			
			$t_importer->set('worksheet', $ps_source);
			$t_importer->update();
			
			if ($t_importer->numErrors()) {
				$pa_errors[] = _t("Could not save worksheet for future download: %1", join("; ", $t_importer->getErrors()))."\n";
				if ($o_log) {  $o_log->logError(_t("[loadImporterFromFile:%1] Error saving worksheet for future download: %2", $ps_source, join("; ", $t_importer->getErrors()))); }
			}
			
			foreach($va_mapping as $vs_group => $va_mappings_for_group) {

				$va_mapping_temp = json_encode($va_mappings_for_group, true);
				$va_mapping_arr = json_decode($va_mapping_temp,true);			// Turn to associative array

				$vs_group_dest = $this->searchObjectTree($va_mapping_arr, 'destenation');
				if (!$vs_group_dest) {
					$va_item = array_shift(array_shift($va_mappings_for_group));
					$pa_errors[] = _t("Skipped items for %1 because no common grouping could be found", $va_item['destination'])."\n";
					if ($o_log) { $o_log->logWarn(_t("[loadImporterFromFile:%1] Skipped items for %2 because no common grouping could be found", $ps_source, $va_item['destination'])); }
					continue;
				}
				
				$t_group = $t_importer->addGroup($vs_group, $vs_group_dest, array(), array('returnInstance' => true));
				if(!$t_group) {
					$pa_errors[] = _t("There was an error when adding group %1", $vs_group);
					if ($o_log) { $o_log->logError(_t("[loadImporterFromFile:%1] There was an error when adding group %2", $ps_source, $vs_group)); }
					return;
				}
				
				// Add items
				foreach($va_mappings_for_group as $vs_source => $va_mappings_for_source) {
					//foreach($va_mappings_for_source as $va_row) {
						$va_item_settings = array();
						$va_item_settings['refineries'] = array($va_mappings_for_source->refinery);//array($va_row['refinery']);
					
						$va_item_settings['original_values'] = $va_mappings_for_source->original_values;//$va_row['original_values'];
						$va_item_settings['replacement_values'] = $va_mappings_for_source->replacement_values;//$va_row['replacement_values'];
					
						$arr = (array)$va_mappings_for_source->options;
						if (/*is_array($va_mappings_for_source->options)*/sizeOf($arr) > 0) {//is_array($va_row['options'])) {
							foreach($va_mappings_for_source->options as $vs_k => $vs_v) {
								if ($vs_k == 'restrictToRelationshipTypes') { $vs_k = 'filterToRelationshipTypes'; }	// "restrictToRelationshipTypes" is now "filterToRelationshipTypes" but we want to support old mappings so we translate here
								if($vs_v != "")
								{
									$va_item_settings[$vs_k] = $vs_v;
								}
							}
						}
						if (is_array($va_row->refinery_options)) {//is_array($va_row['refinery_options'])) {
							/*foreach($va_row['refinery_options'] as $vs_k => $vs_v) {
								$va_item_settings[$va_row['refinery'].'_'.$vs_k] = $vs_v;
							}*/
						}
						
						$t_group->addItem($vs_source, $va_mappings_for_source->destenation/*$va_row['destination']*/, $va_item_settings, array('returnInstance' => true));
					//}
				}
			}
			
			if(sizeof($pa_errors)) {
				foreach($pa_errors as $vs_error) {
					$t_importer->postError(1100, $vs_error, 'ca_data_importers::loadImporterFromFile');
				}
			}
			//error_log(print_r($t_importer,true),3,"C:/inetpub/wwwroot/log/importer_log.log");
			return $t_importer;

		}

		// Get array tree and key. searchthe key in the tree and return it's value  
		public function SearchObjectTree($treeObj, $searchKey) // Run on depthe - n
		{
			$res;
			$counter = 0;

			foreach($treeObj as $val => $son)
			{			
				$tempArr = "";
				$tempArr = explode(".", $son['destenation']);

				if($tempArr != "")
				{
					if($counter > 0)
					{
						if($res != ($tempArr[0] . ".". $tempArr[1]))
						{
							return $empty;
						}
					}
					else
					{
						$res = $tempArr[0] . ".". $tempArr[1];
					}
				}	
				$counter++;		
			}

			return $res;
		}

		

 		# -------------------------------------------------------
		 private function ChackFile($fileDate, &$errorMessage){
			
			$target_file = basename($fileDate["name"]);
			$uploadOk = 1;
			$FileType = pathinfo($target_file,PATHINFO_EXTENSION);
			// Check if file already exists
			if (file_exists($target_file)) {
				$errorMessage = "Sorry, file already exists.";
				$this->printError($errorMessage);
				return false;
			}
			// Check file size
			if ($fileDate["size"] > 25000000) {
				$errorMessage = "Sorry, your file is too large.";
				$this->printError($errorMessage);
				return false;
			}
			// Allow certain file formats
			if(strtolower($FileType) != "xls" && strtolower($FileType) != "xlsx" ) {
				$errorMessage = "Sorry, only excel files are allowed.";
				$this->printError($errorMessage);
				return false;
			}

			return true;
		}
 	}
 ?>
