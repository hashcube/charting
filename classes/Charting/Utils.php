<?php
namespace Charting;

class Utils
{
  private static $charts_not_to_be_fixed = array(1,2,350,290,330,320,530,540,526);

  /* Format dates according to d D M format*/
  public static function formatDates($data)
  {
    foreach($data as $id=>$charts){
      foreach($charts['charts'] as $i=>$chart_details){
        if(!empty($chart_details['result'])) {
          foreach($chart_details['result']['x'] as $j=>$value){
            if(self::is_date($value)){
              $value = date("d D M", strtotime($value));
              $data[$id]['charts'][$i]['result']['x'][$j] = $value;
            }
          }
        }
      }
    }
    return $data;
  }

  public static function is_date($str)
  {
    $stamp = strtotime( $str );
    if (!is_numeric($stamp))
      return FALSE;
    $month = date( 'm', $stamp );
    $day   = date( 'd', $stamp );
    $year  = date( 'Y', $stamp );
    if (checkdate($month, $day, $year))
      return TRUE;
    return FALSE;
  }

  public static function fixMissingDates($data)
  {
    /* specifying for which charts missing dates are to be filled */
    foreach($data as $id=>$charts) 
    {
      foreach($charts['charts'] as $i=>$chart_details) 
      {
        if(!empty($chart_details['result'])) 
        {
          $x = $chart_details['result']['x'];
          $y = $chart_details['result']['y'];
          if(self::is_date($x[0])) 
          {
            $start_date = $x[0];
            $res = self::fillMissingDates($x, $y, $start_date, date("Y-m-d"));
            $data[$id]['charts'][$i]['result']['x'] = $res['x'];
            $data[$id]['charts'][$i]['result']['y'] = $res['y'];
          }
        }
      }
    }
    return $data;
  }

  public static function fillMissingDates($x ,$y, $startdate, $enddate)
  {
    for($k=0; self::datediff($enddate, $startdate) > 0;  $k++) {
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
    return array('x'=>$x, 'y'=>$y);
  }

  /* returns difference between two dates in days*/
  public static function datediff($enddate, $startdate)
  {
    $secs = strtotime($enddate) - strtotime($startdate);
    return ((($secs/60)/60)/24);
  }

}

?>

