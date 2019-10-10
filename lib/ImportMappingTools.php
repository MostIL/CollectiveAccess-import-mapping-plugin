<?php
/* ----------------------------------------------------------------------
 * ImportMapping/lib/ImportMappingTools.php
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

class ImportMappingTools{

    //-----------------------------------Header Excel------Data Excel--------------

    /*
        if('ca_object_lots')
             'idno_stub' 
        else
             'idno' 


    */

    // Get the Excl file path and return the columns header as Json object.
    public function CreateHeaderJson($filePath)
    {
        require_once('app/helpers/configurationHelpers.php');
        require_once(__CA_LIB_DIR__.'/ca/Import/DataReaders/ExcelDataReader.php');
        $header_list = array();

        $EDR = new ExcelDataReader();
        $EDR->read($filePath);
        $EDR->seek(1);
        $headerTitles = $EDR->getRow();
        //Go over the headers in the file and create header array 
       for($i = 1; $i <= count($headerTitles); $i++)
       {
            $temp = $EDR->get($i);  
            $header_list[] = array("colNum"=>$i, "title"=>$temp); 
       }
       
       return $header_list;
    }
    
    // Get the Excl file path and return the columns header as Json object.
    public function CreateDataJson($list)
    {//$t_instance->getLabelUIFields(); 
        $content_list = array();
        $tempInnerArr = array();

        //set new object to import_request_variables
        $o_dm = Datamodel::load();
        $t_instance = $o_dm->getInstanceByTableName($list, false);
        $temp = $t_instance->getLabelUIFields();   
        
        //Create subarray and insert in the end of the $data array
        if($list == "ca_object_lots")
        {
        array_push($content_list, array("element_code"=>"idno_stub", "require"=>true , "title"=>array(_t('Object identifier'))));
            array_push($content_list, array("element_code"=>"lot_status_id", "require"=>true , "title"=>array(_t('status'))));
        }else {
            array_push($content_list, array("element_code"=>"idno", "require"=>true , "title"=>array(_t('Object identifier'))));
        }
        array_push($content_list, array("element_code"=>"type_id", "require"=>true , "title"=>array(_t('Item type'))));
        
        array_push($content_list, array("element_code"=>"access" , "title"=>array(_t("access"))));              // 19.9.2019 - Add 'access' 
   
        foreach($temp as $vs_field) 
        { 
            array_push($tempInnerArr, array("element_code"=> $vs_field , "title"=>array(_t($vs_field))));     //
        }

        array_push($content_list, array("element_code"=>"preferred_labels" , "title"=>array(_t("Preferred labels")), "fields"=>$tempInnerArr));  
        
        foreach($t_instance->getApplicableElementCodes() as $vs_field) 
        {
            array_push($content_list, $this->CreateArrayTree($vs_field));       
        }
        
        return $content_list;
    }

    /* Create The multidimensional array by recursion call.
    *  return an array. 
    */
    private function CreateArrayTree($vs_field)
    {
        $temp;
        $content_list = array();
        
        $t_element = ca_metadata_elements::getInstance($vs_field);
        $va_labels = $t_element->getPreferredDisplayLabelsForIDs(array($t_element->getPrimaryKey()),array('returnAllLocales'=>true));
        $dataType =  $t_element->getAttributeNameForTypeCode($t_element->get('datatype'));

        $temp = array_shift(caExtractValuesByUserLocale($va_labels)); // Returns the attribute value by the user selected language. if the selected user language not exists return the first language in the array 
           
        if($dataType == "Container") // Childes existence
        {
            $sub_content_list = array();
            $childrenList = $t_element->getHierarchyChildren(null, array('idsOnly' => true));

            foreach ($childrenList as $value)
            {
                array_push($sub_content_list, $this->CreateArrayTree($value));    // Recursion call             
            }
            $content_list = array("element_code"=>$t_element->get('element_code') , "title"=> $temp, 'fields'=> $sub_content_list); 
        }
        else{
            $content_list = array("element_code"=>$t_element->get('element_code') , "title"=> $temp);        
        }

        return $content_list;
    }

//------------------------------------------------------------------------------------------------------------------------------------

    public function IsInList($heTitle,$enTiltle,$listOrPart)
    {
        if (is_numeric($listOrPart)){
            $t_list = new ca_lists();
            $he_id=$t_list->getItemIDFromListByLabel($listOrPart,$heTitle);
            $en_id=$t_list->getItemIDFromListByLabel($listOrPart,$enTiltle);
            if (!$he_id && !$en_id){
            return "danger";
            }
            if ($he_id === $en_id){
                return "success";
            }
            else{
                return "warning";
                }
        }
        else {
            $part='';
            if ($heTitle !=''){
                $part = $heTitle;
            }elseif ($enTiltle != '') {
                $part = $enTiltle;
            }
            $o_dm = Datamodel::load();
            $t_part = $o_dm->getInstanceByTableName($listOrPart, false);
            $t_part->load(array('preferredlabels' => $part));
            if ($t_part->getPrimaryKey()){
                return "success";
            }
            else{
                return "danger";
                }
        }

    }
}
?>