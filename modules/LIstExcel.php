<?php


class ListExcel{
// Get the Excl file path and return the columns header as Json object.
    public function CreateHeaderJson($filePath)
    {
        require_once('app/helpers/configurationHelpers.php');
        require_once(__CA_LIB_DIR__.'/Import/DataReaders/ExcelDataReader.php');

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
}
    ?>