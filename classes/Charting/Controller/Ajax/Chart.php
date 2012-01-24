<?php
namespace Charting\Controller\Ajax;

use Charting\Sql\Db,
    Charting\Controller;

class Chart 
{
  public function saveChart()
  {
    if(isset($_POST['chartid']))
    {
      $db_obj = Db::getInstance();
      $res = $db_obj->addChart($_POST['chartid'], $_POST['title'], $_POST['chart_type'], $_POST['x_axis'], $_POST['y_axis']);
      $queries = json_decode($_POST['query'],true);
      $y_axis_graphs = json_decode($_POST['y_axis_graph'], true);
      $graph_types = json_decode($_POST['type_graph'], true);
      $tabid = $_POST['tabid'];
      for($i=0;$i<count($queries);$i++) {
        $db_obj->addGraph($_POST['chartid'], $queries[$i], $y_axis_graphs[$i], $graph_types[$i]);
      }
      $db_obj->addToTab($tabid, $_POST['chartid']);
      echo $res;
    }
  }
}
