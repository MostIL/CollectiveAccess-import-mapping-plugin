<?php
/* ----------------------------------------------------------------------
 * app/plugins/museumAdmin/controllers/ManageController.php :
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




require_once(__CA_APP_DIR__.'/plugins/importer/modules/importer.php');
require_once(__CA_MODELS_DIR__."/ca_data_importers.php");

class ImporterController extends ActionController {
	# -------------------------------------------------------
	private $pt_museum;
	private $opo_app_plugin_manager;
	# -------------------------------------------------------
	#
	# -------------------------------------------------------
	public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {

		// Set view path for plugin views directory
		if (!is_array($pa_view_paths)) { $pa_view_paths = array(); }
		$pa_view_paths[] = __CA_APP_DIR__."/plugins/importer/themes/default/views";

		parent::__construct($po_request, $po_response, $pa_view_paths);


	   
		
	}
	# -------------------------------------------------------
	public function Index() {
		if (!$this->request->user->canDoAction('can_manage_importer')) { return; }
		$va_importers = ca_data_importers::getImporters();
		if(sizeof($va_importers) == 0) {
			$this->render('importer_no_list_html.php');
		} else {
			$this->view->setVar('data',$va_importers);
			$this->render('importer_index.php');
		}
	}
	# -------------------------------------------------------
	public function Get() {
		$e_importer = $this->request->getParameter('importer', pInteger);
		$t_importer = new ca_data_importers($e_importer);

		$this->view->setVar('label',array_shift(array_shift(array_shift($t_importer->getPreferredLabels()))));
		$this->view->setVar('code',$t_importer->get('importer_code'));
		$this->view->setVar('table',$t_importer->get('table_num'));
		$this->view->setVar('settings',$t_importer->get('settings'));
		$this->view->setVar('items',$t_importer->getItems());

		$this->render('importer_get_html.php');
		
	}
	# -------------------------------------------------------
	public function Edit() {
		$this->render('importer_edit_html.php');
	}
	
	# -------------------------------------------------------
	public function Save() {
	//	if (!$this->request->user->canDoAction('can_manage_importer')) { return; }
	//	require_once(__CA_APP_DIR__."/plugins/importer/modules/ListFieldService.php");
	//	$ps_table =  $_GET['table'];  
	//require_once(__CA_APP_DIR__."/plugins/importer/modules/LoadImportFromJson.php");

	$pa_errors = "";
	$pa_options = "";

	if(isset($_POST["map"])) {
		$data_mapping  = $_POST["map"]; // ca_objects  
		$data_settings  = $_POST["set"]; 
		//$this->view->setVar('data',$data);			
		
		if($data_mapping != null && $data_settings != null)
		{
			$objMapping = json_decode($data_mapping);
			$objSettings = json_decode($data_settings,true);
			$objSettings["inputFormats"] = explode(",", $objSettings["inputFormats"]);  		// If there is more than one format it will turn into an array
		
		//	$fieldJson = new LoadImportFromJson();
		//	$res = $fieldJson->LoadImportFromJsonoo($objMapping, $objSettings);
		
		//$res = $fieldJson->LoadImportFromJsonoo($objMapping, $objSettings, $pa_errors, $pa_options);
		$res = $this->LoadImportFromJson($objMapping, $objSettings, $pa_errors, $pa_options);
			//$this->view->setVar('data',array("id"=>$res/*->getPrimaryKey()*/, "status"=>"success"));
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
	//	$this->printError("Error");	// TODO - Need to show information error 
		$this->view->setVar('data',array("id"=>"", "status"=>"Error1"));
	}
	$this->render('ReturnJson.php');
			
	}
	# -------------------------------------------------------
	public function ListImporter() {
		if (!$this->request->user->canDoAction('can_manage_importer')) { return; }
		$va_importers = ca_data_importers::getImporters();
		if(sizeof($va_importers) == 0) {
			$this->render('importer_no_list_html.php');
		} else {
			$this->view->setVar('data',$va_importers);
			$this->render('importer_index.php');
		}
        
	}
	

# -------------------------------------------------------


//public function ListField($ps_table, $pa_args) {
	public function ListField(/*$ps_table*/) {
		if (!$this->request->user->canDoAction('can_manage_importer')) { return; }
		require_once(__CA_APP_DIR__."/plugins/importer/modules/ListFieldService.php");
		$ps_table =  $_GET['table'];  

			try{
					$listField = new ListFieldService($this->getRequest(), $ps_table);
					$data = $listField->dispatch();
					 $this->view->setVar('data',$data);	 

			 }
			 catch(exception $ex)
				 {
				 //	$this->printError($ex->errorMessage);
				 	$this->view->setVar('data', array("id"=>"", "status"=>"Error"));
				 }		
		
				 $this->render('ReturnJson.php');
		
				
		}
	# -------------------------------------------------------
	public function ExcelList() {
		if (!$this->request->user->canDoAction('can_manage_importer')) { return; }
		require_once(__CA_APP_DIR__."/plugins/importer/modules/ListExcel.php");
		$errorMessage = "";
				if ($this->ChackFile($_FILES["excelfile"], $errorMessage))
				{
					try{
						$excelList = new ListExcel();
						$data = $excelList->CreateHeaderJson($_FILES["excelfile"]["tmp_name"]); // Create header and data json object.
						if(sizeof($data) > 0)
						{
							$this->view->setVar('data',$data);
						
						}
						
						else{//return json_decode("false");
						$this->view->setVar('data', array("id"=>"", "status"=>"Error1"));
						}
					}
					catch (exception $ex)
					{
						//$this->printError($ex->errorMessage);
						$this->view->setVar('data', array("id"=>"", "status"=>"Error2"));
						
					}			
				}
			else {
				//$this->printError("Error");
				//return json_decode($errorMessage);  // Need to handle the error information
				$this->view->setVar('data', array("id"=>"", "status"=>"Error3"));
				
			}
			
	$this->render('ReturnJson.php');
		// //להשלים פונקציה של שדות מהאקסל
		// if(sizeof($va_importers) == 0) {
		// 	$this->render('importer_no_list_html.php');
		// } else {
		// 	$this->view->setVar('data',$va_importers);
		// 	$this->render('importer_index.php');
		// }
		
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
	# -------------------------------------------------------
	public function ListID() {
		if (!$this->request->user->canDoAction('can_manage_importer')) { return; }
		require_once("app\models\ca_lists.php");
	    $pm_list_name_or_id =  $_GET['listId'];  

		try{

			$t_list = new ca_lists();                         
			$data = $t_list->getItemsForList($pm_list_name_or_id, array('extractValuesByUserLocale'=>true));
			 $this->view->setVar('data',$data);	 

	 		}
		 catch(exception $ex)
		 {
			 $this->view->setVar('data', array("id"=>"", "status"=>"Error"));
		 }		

		 $this->render('ReturnJson.php');



		}
	# -------------------------------------------------------
	public function LoadImportFromJson($va_mapping, $va_settings, &$pa_errors, $pa_options=null)
	{

		$pa_errors = array();
		$t_importer = new ca_data_importers();
		$t_importer->setMode(ACCESS_WRITE);
		$va_settings['code']= date("dmYHis");

		
		if (!($t_instance = Datamodel::getInstanceByTableName($va_settings['table']))) {
			$pa_errors[] = _t("Mapping target table %1 is invalid\n", $va_settings['table']);
			if ($o_log) {  $o_log->logError(_t("[loadImporterFromFile:%1] Mapping target table %2 is invalid\n", $ps_source, $va_settings['table'])); }
			return;
		}
		
		if (!$va_settings['nametamplet']) { $va_settings['nametamplet'] = $va_settings['code']; }
		
		
		$t_importer = new ca_data_importers();
		$t_importer->setMode(ACCESS_WRITE);
		
		// Remove any existing mapping
		if ($t_importer->load(array('importer_code' => $va_settings['code']))) {
			$t_importer->delete(true, array('hard' => true));
			if ($t_importer->numErrors()) {
				$pa_errors[] = _t("Could not delete existing mapping for %1: %2", $va_settings['code'], join("; ", $t_importer->getErrors()))."\n";
				if ($o_log) { $o_log->logError(_t("[loadImporterFromFile:%1] Could not delete existing mapping for %2: %3", $ps_source,$va_settings['code'], join("; ", $t_importer->getErrors()))); }
				return null;
			}
		}

		// Create new mapping
		$t_importer->set('importer_code', $va_settings['code']);
		$t_importer->set('table_num', $t_instance->tableNum());   
		$t_importer->set('rules', array('rules' => $va_rules, 'environment' => $va_environment));
		
		unset($va_settings['code']);
		unset($va_settings['table']);
		foreach($va_settings as $vs_k => $vs_v) {
			$t_importer->setSetting($vs_k, $vs_v);
		}

		global $AUTH_CURRENT_USER_ID;
		$vn_user_id = caGetOption('user_id', $pa_options, $AUTH_CURRENT_USER_ID, array('castTo' => 'int'));
			$t_user = new ca_users($vn_user_id);
			if ($t_user->getPrimaryKey()) {
				$t_importer->set('museum_id', $t_user->get('museum_id'));
			}


		$t_importer->insert();
		
		if ($t_importer->numErrors()) {
			$pa_errors[] = _t("Error creating mapping: %1", join("; ", $t_importer->getErrors()))."\n";
			if ($o_log) { $o_log->logError(_t("[loadImporterFromFile:%1] Error creating mapping: %2", $ps_source, join("; ", $t_importer->getErrors()))); }
			return null;
		}
		
		
		$vn_locale_id = $t_user->getPreferredUILocaleID();

		
		$t_importer->addLabel(array('name' => $va_settings['nametamplet']), $vn_locale_id, null, true);
		
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
		
		$countConst=0;
		foreach($va_mapping as $vs_group => $va_mappings_for_group) {

			$va_mapping_temp = json_encode($va_mappings_for_group, true);
			$va_mapping_arr = json_decode($va_mapping_temp,true);
			error_log(print_r($va_mapping_arr,true),3,"C:\\inetpub\\wwwroot\\mana\\app\\log\\importlog.log");
				//	}			// Turn to associative array
		


		//fields addGroup
		$t_group = $t_importer->addGroup($va_mapping_arr['element_code'].$vs_group, $va_mapping_arr['destenation'], array(), array('returnInstance' => true));
			if(!$t_group) {
				$pa_errors[] = _t("There was an error when adding group %1", $vs_group);
				if ($o_log) { $o_log->logError(_t("[loadImporterFromFile:%1] There was an error when adding group %2", $ps_source, $vs_group)); }
				return;
			}
		
		
			// Add items		

				if(isset($va_mapping_arr['fields'])){
					foreach($va_mapping_arr['fields'] as $key => $va_fields){
					
						if($va_fields['const'] == 1){
							$va_fields['value']='_CONSTANT_:'.$countConst.':'.$va_fields['value'];
							$countConst+=1;
						}
						if($va_fields['value']!=''){
						$va_item_options= $this->Options($va_fields);
						$t_group->addItem($va_fields['value'], $va_fields['destenation'],$va_item_options, array('returnInstance' => true));		
						}
					}	
				}
				else{
					$va_item_options= $this->Options($va_mapping_arr);
					if($va_mapping_arr['const'] == 1)
					{$va_mapping_arr['value']='_CONSTANT_:'.$countConst.':'.$va_mapping_arr['value'];}
					$va_mapping_arr['value']!=''?$t_group->addItem($va_mapping_arr['value'], $va_mapping_arr['destenation'], $va_item_options, array('returnInstance' => true)):null;
					$countConst+=1;
				}
			
		}
		
		if(sizeof($pa_errors)) {
			foreach($pa_errors as $vs_error) {
				$t_importer->postError(1100, $vs_error, 'ca_data_importers::loadImporterFromFile');
			}
		}
		// //error_log(print_r($t_importer,true),3,"C:/inetpub/wwwroot/log/importer_log.log");
		 return $t_importer;
		// //}
	 }

public function Options($va_mapping){
	
			$va_item_options = array();
			$va_item_options['refineries'] = array($va_mapping['refinery']);
			$va_item_options['original_values'] = $va_mapping['original_values'];
			$va_item_options['replacement_values'] = $va_mapping['replacement_values'];

		//	$arr = (array)$va_mappings_for_source->options;
			//if (/*is_array($va_mappings_for_source->options)*/sizeOf($arr) > 0) {//is_array($va_row['options'])) {
				foreach($va_mapping['options'] as $vs_k => $vs_v) {

					if ($vs_k == 'restrictToRelationshipTypes') { $vs_k = 'filterToRelationshipTypes'; }	// "restrictToRelationshipTypes" is now "filterToRelationshipTypes" but we want to support old mappings so we translate here
					if($vs_v != "")
					{
						//error_log(print_r($vs_k,true),3,"C:\\inetpub\\wwwroot\\mana\\app\\log\\importlog.log");
					//	error_log(print_r($vs_v,true),3,"C:\\inetpub\\wwwroot\\mana\\app\\log\\importlog.log");
						$va_item_options[$vs_k] = $vs_v;
					}
				}
			//}
			
		//}
		return $va_item_options;
	}

	
	# -------------------------------------------------------

	
	// public function Delete() {
	// 	$t_museum = $this->getMuseumObject();
	// 	if ($this->request->getParameter('confirm', pInteger)) {
	// 		$t_museum->setMode(ACCESS_WRITE);
	// 		$t_museum->delete(false);

	// 		if ($t_museum->numErrors()) {
	// 			foreach ($t_museum->errors() as $o_e) {
	// 			   $this->request->addActionError($o_e, 'general');
	// 		   }
	// 		} else {
	// 			$this->notification->addNotification(_t("Deleted museum"), __NOTIFICATION_TYPE_INFO__);
	// 		}
	// 		$this->ListMuseums();
	// 		return;
	// 	} else {
	// 		$this->render('museum_delete_html.php');
	// 	}
	// }
# -------------------------------------------------------
		# Utilities
		# -------------------------------------------------------
		private function getImporterInstance($pb_set_view_vars=true, $pn_importer_id=null) {
			if (!($vn_importer_id = $this->request->getParameter('importer_id', pInteger))) {
				$vn_importer_id = $pn_importer_id;
			}
			$t_importer = new ca_data_importers($vn_importer_id);
			if ($pb_set_view_vars){
				$this->view->setVar('importer_id', $vn_importer_id);
				$this->view->setVar('t_importer', $t_importer);
			}
			return $t_importer;
		}
		# -------------------------------------------------------
 		/**
 		 *
 		 */
		  public function Delete($pa_values=null) {
			$t_importer = $this->getImporterInstance();
			if ($this->request->getParameter('confirm', pInteger)) {
				$t_importer->setMode(ACCESS_WRITE);
				$t_importer->delete(true);

				if ($t_importer->numErrors()) {
					foreach ($t_importer->errors() as $o_e) {
						$this->request->addActionError($o_e, 'general');
						$this->notification->addNotification($o_e->getErrorDescription(), __NOTIFICATION_TYPE_ERROR__);
					}
				} else {
					$this->notification->addNotification(_t("Deleted importer"), __NOTIFICATION_TYPE_INFO__);
				}

				$this->Index(); 	
				return;
			} else {
				$this->view->setVar("t_importer",$t_importer);
				$this->render('importer_delete_html.php');
			}
		}

}
