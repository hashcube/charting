<?php
class Db
{
  protected static $instance;
  protected $conf_connfb;
  protected $connfb;
  protected $mysql_f8slog;

  protected function __construct ()
  {
    //A dummy private constructor to prevent instantiation
    //of this class
  }

  public static function getInstance()
  {
    if(!isset(self::$instance))
    {
      $c= __CLASS__;
      self::$instance = new $c();
    }
    return self::$instance;
  }


  function db_connect()
  {
    if(!$this->conf_connfb)
    {
      $this->conf_connfb = \mysql_connect(DBHOST, USER, PASS) or die ('Error connecting to mysql');
      if(!$this->conf_connfb)
      {
        exit("connecting to mysql failed");
      }
      mysql_select_db(CONFDB, $this->conf_connfb);
    }
    $this->app_db_connect();
  }

  function app_db_connect()
  {
    if(!$this->connfb)
    {
      $this->connfb = \mysql_connect(DBHOST, USER, PASS, true) or die ('Error connecting to mysql');
      if(!$this->connfb)
      {
        exit("connecting to mysql failed");
      }
      mysql_select_db(DB,$this->connfb);
    }
  }

  private function execGetCharts($query)
  {
    $this->db_connect();
    $res = mysql_query($query, $this->conf_connfb);
    print_r(mysql_error());
    $charts = array();
    while($row = mysql_fetch_array($res, MYSQL_ASSOC))
    {
      $id = $row['chartid'];
      $charts[$id]['title']=$row['title'];
      $charts[$id]['chart_type']=$row['chart_type'];
      $charts[$id]['x_axis'] = $row['x_axis'];
      $charts[$id]['y_axis'] = $row['y_axis'];
    }

    foreach($charts as $id=>$values)
    {
      $query = "SELECT * from chart_info where chartid=$id";
      $res = mysql_query($query, $this->conf_connfb);
      print_r(mysql_error());
      $chart_info = array();
      while($row = mysql_fetch_array($res, MYSQL_ASSOC))
      {
        $id = $row['chartid'];
        $chart_info['y_axis'] = $row['y_axis'];
        $query=$row['query_text'];
        $chart_info['result'] = $this->executeQuery($query);
        $charts[$id]['charts'][] = $chart_info;
      }
    }
    return $charts;
  }

  public function getSpecificCharts($ids)
  {
    $query = "SELECT * from charts where chartid in($ids) order by chartid";
    return $this->execGetCharts($query);
  }

  public function getCharts()
  {
    $query = "SELECT * from charts order by chartid";
    return $this->execGetCharts($query);
  }

  public function executeQuery($query)
  {
    $res = mysql_query($query, $this->connfb);
    while($row = mysql_fetch_array($res, MYSQL_ASSOC))
    {
      $x[]=$row['x'];
      $y[]=$row['y'];
    }
    $result['x'] = $x;
    $result['y'] = $y;
    return $result;
  }
}

?>
