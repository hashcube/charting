<?php
require_once('conf.php');
require_once('Db.php');

$db = Db::getInstance();
$data = $db->getCharts();
//print_r($data);
$json_data = json_encode($data, JSON_NUMERIC_CHECK);
//$json_data = json_encode($data, 1);
//echo "<pre>"; print_r($data); echo "</pre>";
require_once('templates/index.html');
?>
