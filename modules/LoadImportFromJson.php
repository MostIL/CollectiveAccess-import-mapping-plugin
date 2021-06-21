<?php
class LoadImportFromJson{

public function LoadImportFromJsonoo($va_mapping, $va_settings, &$pa_errors, $pa_options=null)
	{

		//$pa_errors = array();
	//	$t_importer = new ca_data_importers();
	//	$t_importer->setMode(ACCESS_WRITE);
   // $va_code='TurnDateTimeToNumStr';
   // $this-> $va_code();
        $t_fun= new LoadImportFromJson();
        $va_code=$t_fun->TurnDateTimeToNumStr();
       
		
		// global $g_ui_locale_id;
		// $vn_locale_id = (isset($pa_options['locale_id']) && (int)$pa_options['locale_id']) ? (int)$pa_options['locale_id'] : $g_ui_locale_id;

		
	//	if (!($t_instance = Datamodel::getInstanceByTableName($va_settings['table']))) {
		//	$pa_errors[] = _t("Mapping target table %1 is invalid\n", $va_settings['table']);
		// 	if ($o_log) {  $o_log->logError(_t("[loadImporterFromFile:%1] Mapping target table %2 is invalid\n", $ps_source, $va_settings['table'])); }
		// 	return;
		// }
		return $va_code;
		// if (!$va_settings['nametamplet']) { $va_settings['nametamplet'] = $va_settings['code']; }
		
		
		// $t_importer = new ca_data_importers();
		// $t_importer->setMode(ACCESS_WRITE);
		
		// // Remove any existing mapping
		// if ($t_importer->load(array('importer_code' => $va_settings['code']))) {
		// 	$t_importer->delete(true, array('hard' => true));
		// 	if ($t_importer->numErrors()) {
		// 		$pa_errors[] = _t("Could not delete existing mapping for %1: %2", $va_settings['code'], join("; ", $t_importer->getErrors()))."\n";
		// 		if ($o_log) { $o_log->logError(_t("[loadImporterFromFile:%1] Could not delete existing mapping for %2: %3", $ps_source, $va_settings['code'], join("; ", $t_importer->getErrors()))); }
		// 		return null;
		// 	}
		// }

		// // Create new mapping
		// $t_importer->set('importer_code', $va_settings['code']);
		// $t_importer->set('table_num', $t_instance->tableNum());   
		// $t_importer->set('rules', array('rules' => $va_rules, 'environment' => $va_environment));
		
		// unset($va_settings['code']);
		// unset($va_settings['table']);
		// foreach($va_settings as $vs_k => $vs_v) {
		// 	$t_importer->setSetting($vs_k, $vs_v);
		// }

		// global $AUTH_CURRENT_USER_ID;
		// $vn_user_id = caGetOption('user_id', $pa_options, $AUTH_CURRENT_USER_ID, array('castTo' => 'int'));
		// 	$t_user = new ca_users($vn_user_id);
		// 	if ($t_user->getPrimaryKey()) {
		// 		$t_importer->set('museum_id', $t_user->get('museum_id'));
		// 	}


		// $t_importer->insert();
		
		// if ($t_importer->numErrors()) {
		// 	$pa_errors[] = _t("Error creating mapping: %1", join("; ", $t_importer->getErrors()))."\n";
		// 	if ($o_log) { $o_log->logError(_t("[loadImporterFromFile:%1] Error creating mapping: %2", $ps_source, join("; ", $t_importer->getErrors()))); }
		// 	return null;
		// }
		
		
		// $t_importer->addLabel(array('name' => $va_settings['nametamplet']), $vn_locale_id, null, true);
		
		// if ($t_importer->numErrors()) {
		// 	$pa_errors[] = _t("Error creating mapping name: %1", join("; ", $t_importer->getErrors()))."\n";
		// 	if ($o_log) {  $o_log->logError(_t("[loadImporterFromFile:%1] Error creating mapping: %2", $ps_source, join("; ", $t_importer->getErrors()))); }
		// 	return null;
		// }
		
		// $t_importer->set('worksheet', $ps_source);
		// $t_importer->update();
		
		// if ($t_importer->numErrors()) {
		// 	$pa_errors[] = _t("Could not save worksheet for future download: %1", join("; ", $t_importer->getErrors()))."\n";
		// 	if ($o_log) {  $o_log->logError(_t("[loadImporterFromFile:%1] Error saving worksheet for future download: %2", $ps_source, join("; ", $t_importer->getErrors()))); }
		// }
		
		// foreach($va_mapping as $vs_group => $va_mappings_for_group) {

		// 	$va_mapping_temp = json_encode($va_mappings_for_group, true);
		// 	$va_mapping_arr = json_decode($va_mapping_temp,true);			// Turn to associative array

		// 	$vs_group_dest = $this->searchObjectTree($va_mapping_arr, 'destenation');
		// 	if (!$vs_group_dest) {
		// 		$va_item = array_shift(array_shift($va_mappings_for_group));
		// 		$pa_errors[] = _t("Skipped items for %1 because no common grouping could be found", $va_item['destination'])."\n";
		// 		if ($o_log) { $o_log->logWarn(_t("[loadImporterFromFile:%1] Skipped items for %2 because no common grouping could be found", $ps_source, $va_item['destination'])); }
		// 		continue;
		// 	}
			
		// 	$t_group = $t_importer->addGroup($vs_group, $vs_group_dest, array(), array('returnInstance' => true));
		// 	if(!$t_group) {
		// 		$pa_errors[] = _t("There was an error when adding group %1", $vs_group);
		// 		if ($o_log) { $o_log->logError(_t("[loadImporterFromFile:%1] There was an error when adding group %2", $ps_source, $vs_group)); }
		// 		return;
		// 	}
			
		// 	// Add items
		// 	foreach($va_mappings_for_group as $vs_source => $va_mappings_for_source) {
		// 		//foreach($va_mappings_for_source as $va_row) {
		// 			$va_item_settings = array();
		// 			$va_item_settings['refineries'] = array($va_mappings_for_source->refinery);//array($va_row['refinery']);
				
		// 			$va_item_settings['original_values'] = $va_mappings_for_source->original_values;//$va_row['original_values'];
		// 			$va_item_settings['replacement_values'] = $va_mappings_for_source->replacement_values;//$va_row['replacement_values'];
				
		// 			$arr = (array)$va_mappings_for_source->options;
		// 			if (/*is_array($va_mappings_for_source->options)*/sizeOf($arr) > 0) {//is_array($va_row['options'])) {
		// 				foreach($va_mappings_for_source->options as $vs_k => $vs_v) {
		// 					if ($vs_k == 'restrictToRelationshipTypes') { $vs_k = 'filterToRelationshipTypes'; }	// "restrictToRelationshipTypes" is now "filterToRelationshipTypes" but we want to support old mappings so we translate here
		// 					if($vs_v != "")
		// 					{
		// 						$va_item_settings[$vs_k] = $vs_v;
		// 					}
		// 				}
		// 			}
		// 			if (is_array($va_row->refinery_options)) {//is_array($va_row['refinery_options'])) {
		// 				/*foreach($va_row['refinery_options'] as $vs_k => $vs_v) {
		// 					$va_item_settings[$va_row['refinery'].'_'.$vs_k] = $vs_v;
		// 				}*/
		// 			}
					
		// 			$t_group->addItem($vs_source, $va_mappings_for_source->destenation/*$va_row['destination']*/, $va_item_settings, array('returnInstance' => true));
		// 		//}
		// 	}
		// }
		
		// if(sizeof($pa_errors)) {
		// 	foreach($pa_errors as $vs_error) {
		// 		$t_importer->postError(1100, $vs_error, 'ca_data_importers::loadImporterFromFile');
		// 	}
		// }
		// //error_log(print_r($t_importer,true),3,"C:/inetpub/wwwroot/log/importer_log.log");
		// return $t_importer;

	}
    function TurnDateTimeToNumStr()
   {

    // $currentDateTime = '08/04/2010 22:15:00';
    // $newDateTime = date('h:i A', strtotime($currentDateTime));

    $dateStr = date("dmYHis");
  //  $dateStr = date("dmYhisa");
    //        // var regExp = /[0-9]/g;
//       // $res = dateStr.match(regExp);
//       // $res = dateStr.match('/[0-9]/g');
        $res=$dateStr;
        return  $res;
//        //return $res.join("");
    }
 }
?>