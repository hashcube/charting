<?php
require_once('conf.php');
require_once('Db.php');

$db = Db::getInstance();
$ids = "1,2,300,400,500,520";
$data = $db->getSpecificCharts($ids);
$ids = explode(',', $ids);
//$data = fix_missing_dates($data, $ids);

$json_data = json_encode($data, JSON_NUMERIC_CHECK);
//echo "<pre>"; print_r($data); echo "</pre>";
require_once('templates/index.html');


function fix_missing_dates($data, $ids) 
{
  $enddate = date("Y-m-d");
  //$enddate = '2011-10-31';

  $i = current($ids);
  do {
    if(preg_match("/Segments/i", $data[$i]['title']) && 
        preg_match("/Date/i", $data[$i]['x_axis'])) {
      for($j=0; $j < count($data[$i]['charts']); $j++) {
        $startdate = "2011-10-21";
        $x = $data[$i]['charts'][$j]['result']['x'];
        $y = $data[$i]['charts'][$j]['result']['y'];
        for($k=0; $k < count($x) && datediff($enddate, $startdate)>0;  $k++) {
          if(!in_array($startdate, $x)) {
            //$x[] = $startdate;
            $xarr1 = array_slice($x, 0, $k);
            $yarr1 = array_slice($y, 0, $k);
            $xarr1[] = $startdate;
            $yarr1[] = 0;
            $xarr2 = array_slice($x, $k, count($x)-$k);
            $yarr2 = array_slice($y, $k, count($y)-$k);
            $x = array_merge($xarr1, $xarr2);
            $y = array_merge($yarr1, $yarr2);
          }
          $startdate = date("Y-m-d", strtotime("$startdate +1 day"));
        }
        $data[$i]['charts'][$j]['result']['x'] = $x;
        $data[$i]['charts'][$j]['result']['y'] = $y;
      }
    }
  }while($i = next($ids));
  return $data;
}

function datediff($enddate, $startdate){ // in days
  $secs = strtotime($enddate) - strtotime($startdate);
  return ((($secs/60)/60)/24);
}
?>
