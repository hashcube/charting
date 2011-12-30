<?php
require_once('conf.php');
require_once('Db.php');
require_once('Utils.php');

$DB='f8sqanalytics';
$db = Db::getInstance();
$ids = "51,52,53,54,55";
$data = $db->getSpecificCharts($ids);

/* specifying for which charts missing dates are to be filled */
foreach($data as $id=>$charts) {
  foreach($charts['charts'] as $i=>$chart_details) {
    $x = $chart_details['result']['x'];
    $y = $chart_details['result']['y'];
    if(true){
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
