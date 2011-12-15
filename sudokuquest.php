<?php
require_once('Db.php');
require_once('Utils.php');

$db = Db::getInstance();
$ids = "1,2,300,400,500,520";
$data = $db->getSpecificCharts($ids);
foreach($data as $id=>$charts){
  if($id != 1 && $id != 2) {
    foreach($charts['charts'] as $i=>$chart_details){
      $x = $chart_details['result']['x'];
      $y = $chart_details['result']['y'];
      $res = Utils::fillMissingDates($x, $y, '2011-10-21', date("Y-m-d"));
      $data[$id]['charts'][$i]['result']['x'] = $res['x'];
      $data[$id]['charts'][$i]['result']['y'] = $res['y'];
    }
  }
}
$data = Utils::formatDates($data);

$json_data = json_encode($data, JSON_NUMERIC_CHECK);
//echo "<pre>"; print_r($data); echo "</pre>";
require_once('templates/index.html');

?>
