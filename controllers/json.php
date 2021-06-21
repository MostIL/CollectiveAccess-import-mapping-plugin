

<?php
	$i_label = $this->getVar('label');
    $i_code = $this->getVar('code');
    $i_table = $this->getVar('table');
    $i_settings = $this->getVar('settings');
    $i_items = $this->getVar('items');

$myArr = array($i_label,  $i_code, $i_table , $i_settings,$i_items );

$myJSON = json_encode($myArr);

echo $myJSON;
?>