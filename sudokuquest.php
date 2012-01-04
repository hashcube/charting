<?php
namespace Charting;
use Charting\Sql\Db;
require_once('conf.php');
require_once('Db.php');
require_once('Utils.php');

$db = Db::getInstance();
$ids = "100,200,290,5,300,320,330,350,400,500,520,550,570,580,610,620,630,650,660,700,800,900";
$LTV_data = $db->payingUsersLTVQuery();
$data = $db->getSpecificCharts($ids);

$data = Utils::fixMissingDates($data);
$data = Utils::formatDates($data);

$json_data = json_encode($data, JSON_NUMERIC_CHECK);
//echo "<pre>"; print_r($LTV_data); echo "</pre>";
require_once('templates/index.html');
?>
