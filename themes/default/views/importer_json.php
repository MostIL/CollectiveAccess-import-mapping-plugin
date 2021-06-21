<?php
header("Content-Type: application/json; charset=UTF-8");
$data = $this->getVar('data');

$res =  json_encode(mb_convert_encoding($data, 'UTF-8', 'UTF-8'));
print_r($res);

//print_r($data);
?>
<div>tehila tehila tehila tehila

<?php print_r($data);
?>
</div>
