<?php
require_once('conf.php');
require_once('Db.php');
require_once('Utils.php');

if(isset($_GET['id']))
{
  $db = Db::getInstance();
  $id = $_GET['id'];
  $data = $db->getSpecificCharts($id);
  $data = Utils::fixMissingDates($data);
  $data = Utils::formatDates($data);
  $json_data = json_encode($data, JSON_NUMERIC_CHECK);
  require_once('templates/index.html'); 
}
?>
