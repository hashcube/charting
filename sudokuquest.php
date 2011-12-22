<?php
require_once('conf.php');
require_once('Db.php');
require_once('Utils.php');

$db = Db::getInstance();
$ids = "100,200,290,5,300,320,330,350,400,500,520,550,570,580,610,620,630,640,700,800";
$data = $db->getSpecificCharts($ids);

/* specifying for which charts missing dates are to be filled */
foreach($data as $id=>$charts) {
  foreach($charts['charts'] as $i=>$chart_details) {
    $x = $chart_details['result']['x'];
    $y = $chart_details['result']['y'];
    if($id!=1 && $id!=2 && $id!=290 && $id!=350 && $id!=320 && $id!=330) {
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
