<?php
require_once('conf.php');
require_once('Db.php');

$db = Db::getInstance();
$data = $db->getSpecificCharts("1,20,30,35,40");
$json_data = json_encode($data, JSON_NUMERIC_CHECK);
//echo "<pre>"; print_r($data); echo "</pre>";
require_once('templates/index.html');
?>
