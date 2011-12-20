<?php
require_once('conf.php');
require_once('Db.php');
require_once('Utils.php');

$GLOBALS['DB'] = "facebookstats";

$db = Db::getInstance();

$data = $db->getSpecificCharts('1001,620');
$data = Utils::formatDates($data);
$json_data = json_encode($data, JSON_NUMERIC_CHECK);
require_once('templates/index.html');

?>
