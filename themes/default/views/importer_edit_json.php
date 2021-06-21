<?php
  $i_label = $this->getVar('label');
  $i_code = $this->getVar('code');
  $i_table = $this->getVar('table');
  $i_settings = $this->getVar('settings');
  $i_items = $this->getVar('items');
 
 $myArr=array("label"=>$i_label[name],"code" => $i_code,"table" =>$i_table,"settings" =>$i_settings,"items" =>$i_items);
 //print_r($myArr);
  echo json_encode($myArr);

  ?>
