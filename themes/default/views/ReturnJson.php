
<?php
//header("Content-Type: application/json; charset=UTF-8");
 header('Content-type: application/json');

$resp = json_encode($this->getVar('data'), JSON_UNESCAPED_UNICODE);  // parse the array into json

print_r($resp);
//print json_encode($resp);
//echo json_encode($resp);

//$i_data = $this->getVar('data');
//$data =array("a"=>"a","b"=>"b","c"=>"c","d"=>"d","h"=>"h");


//$myArr=array("data"=>$i_data);
//print_r($myArr);
//print json_encode($data);
//echo html_entity_decode($data);
//echo json_encode($resp);

//$resp = array_replace(array("ok" => true), caSanitizeArray($this->getVar('data'),array('allowStdClass' => true)));


	// if($this->getVar('pretty_print')){
	// 	print caFormatJson(json_encode($resp));
	// } else {
	// 	print json_encode($resp);
	// }


// header('Content-type: application/json');
// 	$va_return = array_replace(array("ok" => true), caSanitizeArray($this->getVar('content'),array('allowStdClass' => true)));

// 	if($this->getVar('pretty_print')){
// 		print caFormatJson(json_encode($va_return));
// 	} else {
// 		print json_encode($va_return);
// 	}
?>