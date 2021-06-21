<?php

require_once(__CA_APP_DIR__."/lib/Service/BaseJSONService.php");
require_once(__CA_APP_DIR__."/models/ca_lists.php");
require_once(__CA_APP_DIR__."/models/ca_relationship_types.php");


//class ListFieldService  {
	class ListFieldService extends BaseJSONService {
	// # -------------------------------------------------------
	public function __construct($po_request,$ps_table=""){
		parent::__construct($po_request,$ps_table);
	}
	# -------------------------------------------------------
	public function dispatch(){
		$va_post = $this->getRequestBodyArray();

		switch($this->getRequestMethod()){
			case "GET":
				if(sizeof($va_post)==0){
					return $this->getModelInfoForTypes();
				} else {
					if(is_array($va_post["types"])){
						return $this->getModelInfoForTypes($va_post["types"]);
					} else {
						$this->addError(_t("Invalid request body format"));
						return false;
					}
				}
				break;
			default:
				$this->addError(_t("Invalid HTTP request method for this service"));
				return false;
		}
	}
	# -------------------------------------------------------
	private function getModelInfoForTypes($pa_types=null){
		$va_post = $this->getRequestBodyArray();
		$t_instance = $this->_getTableInstance($this->getTableName());
		$va_return = array();

		if(is_null($pa_types)){
			$va_types = $t_instance->getTypeList();
				//foreach($va_types as $va_type){
			//	$va_return[$va_type["idno"]] = $this->getModelInfoForType($va_type["idno"]);
			//	$va_return = $this->getModelInfoForType($va_type["idno"]);
				array_push($va_return, $this->getModelInfoForType($va_types));
		//	}
		} else if(is_array($pa_types)){
			//foreach($pa_types as $vs_type){
				array_push($va_return, $this->getModelInfoForType($pa_types));
			//	$va_return[$vs_type] = $this->getModelInfoForType($vs_type);
			//	$va_return = $this->getModelInfoForType($vs_type);
			//}
		} else {
			$this->addError(_t("Invalid request body format"));
		}

		return $va_return;
	}
	# -------------------------------------------------------
	private function getModelInfoForType($ps_type){
		$t_instance = $this->_getTableInstance($this->getTableName());
		$t_list = new ca_lists();
		$va_return = array();
		$vs_type_list_code = $t_instance->getTypeListCode();
		$tempInnerArr = array();
		$va_elements = array();
		$tempInnerArrField = array();
        $va_item = $t_list->getItemFromList($vs_type_list_code,$ps_type);
		$List = $this->getTableName($va_item['parent_id']);

// insert require field
		// if($vs_type_list_code=='object_lot_types')ca_object_lots
		// 	{	
		// 	array_push($$va_return, array("element_code"=>"idno_stub", "require"=>true , "title"=>array(_t('Object identifier'))));
        //     array_push($$va_return, array("element_code"=>"lot_status_id", "require"=>true , "title"=>array(_t('status'))));
        // 	}
		// else {
        //     array_push($va_return, array("element_code"=>"idno", "require"=>true , "title"=>array(_t('Object identifier'))));
        // }
//
//insert data json

//$tmpFile = __CA_APP_DIR__.'/lib/Service/data.json';
$tmpFile = 'app/plugins/importer/themes/default/lib/data.json';
$tmpString = file_get_contents($tmpFile);
$data = json_decode($tmpString,true);

foreach($data[$List] as $keyData =>$valData)
{
   
		 foreach($valData as $innerFields)
		 {
			foreach($innerFields[0] as $innerKey1 => &$innerVal)
			{
				if($innerKey1 == "title")
				{
					$innerVal[0] = _t($innerVal[0]);            // Change the the title value from the object name to its translation in hebrew.
				}
			}       
			array_push($va_return, $innerFields[0]);
		
		 }            
   
}
//

//insert preferred_labels שם	
		$labelField = $t_instance->getLabelUIFields();  
	
		foreach($labelField as $vs_field) 
        { 
		array_push($tempInnerArr, array("element_code"=> $vs_field , "title"=>array(_t($vs_field))));     
       }
	    array_push($va_return, array("element_code"=>"preferred_labels" , "title"=>array(_t("Preferred labels")), "fields"=>$tempInnerArr));  
//
//insert Everything Else

		$va_codes = $t_instance->getApplicableElementCodes($va_item["item_id"]);
		foreach($va_codes as $vs_code => $va_junk){

			// subelements
			//$counter++;
			$t_element = ca_metadata_elements::getInstance($vs_code);
			
 			foreach($t_element->getElementsInSet() as $va_element_in_set){
 				if($va_element_in_set["datatype"]==0) continue; // don't include sub-containers
 				$va_element_in_set["datatype"] = ca_metadata_elements::getAttributeNameForTypeCode($va_element_in_set["datatype"]);
			 	$va_elements[$vs_code]["element_code"] = $va_element_in_set['element_code'];
				$va_elements[$vs_code]["title"] =  $va_element_in_set["display_label"];
			 	$va_elements[$vs_code]["list_id"]=$va_element_in_set['list_id'];

					// array_push($va_elements,array("element_code" => $va_element_in_set['element_code'],"title"=>$va_element_in_set["display_label"],"cc"=>$count));

/////////////////////
               if($va_element_in_set['parent_id']!='')
 				 {
					if(!isset($va_elements[$vs_code]["fields"]))
						{$va_elements[$vs_code]["fields"]=array();}

					array_push(	$va_elements[$vs_code]["fields"], array('element_code'=> $va_element_in_set["element_code"],'title'=> $va_element_in_set["display_label"],"list_id"=>$va_element_in_set["list_id"])); 
					$va_elements[$vs_code]["element_code"] = $va_codes[$va_element_in_set['parent_id']];
					$va_label = $t_instance->getAttributeLabelAndDescription($vs_code);
					$va_elements[$vs_code]["title"]=  $va_label['name'];
				  }
			
			
		

				}	



		//	$va_elements[$vs_code]["title"] = $va_label["name"];
	//		if(isset($va_label["description"])){
		//		$va_elements[$vs_code]["description"] = $va_label["description"];
		//	}
		

}

foreach($va_elements as $elements)
	{
		array_push($va_return,$elements);
	}

//


		return $va_return;
	}
	
}
