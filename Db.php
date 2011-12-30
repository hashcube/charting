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
      $set_time_zone = "SET time_zone = 'PST8PDT'";
      mysql_query($set_time_zone, $this->conf_connfb);
      print_r(mysql_error());
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
      mysql_select_db($GLOBALS['DB'],$this->connfb);
    }
  }

  public function listAllCharts()
  {
    $this->db_connect();
    $query = "SELECT * FROM charts ORDER BY chartid";
    $res = mysql_query($query, $this->conf_connfb);
    $charts = array();
    $i = 0;
    while($row = mysql_fetch_array($res,MYSQL_ASSOC))
    {
      $charts[$i]['id'] = $row['chartid'];
      $charts[$i]['title'] = $row['title'];
      $charts[$i]['type'] = $row['chart_type'];
      $charts[$i]['xAxis'] = $row['x_axis'];
      $charts[$i]['yAxis'] = $row['y_axis'];
      $i++;
    }
    return $charts;
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
        $chart_info['type'] = $row['type'];
        $query=$row['query_text'];
        $chart_info['result'] = $this->executeQuery($query, $id);
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

  public function getPayingUsers()
  {
    $this->db_connect();
    $q1 = "SELECT DISTINCT uid FROM credits";
    $res = mysql_query($q1, $this->connfb);
    while($row = mysql_fetch_array($res, MYSQL_ASSOC))
    {
      $uids[]=$row['uid'];
    }
    $uids = implode(',',$uids);
    return $uids;
  }

  public function payingUsersLTVQuery()
  {
    $this->db_connect();
    $query = "SELECT gold, SUM(amount) as LTV, credits.uid, country, gender, source, DATE(starttime), DATE(lasttime), max_milestone FROM "
             ."credits, user_info, user_game WHERE credits.uid=user_info.uid AND credits.uid=user_game.uid GROUP BY uid ORDER BY LTV DESC";
    $res = mysql_query($query, $this->connfb);
    $data = array();
    $i = 0;
    while($row = mysql_fetch_array($res, MYSQL_ASSOC))
    {
      foreach($row as $key=>$value){
        $data[$i][$key] = $value;
      }
      $i++;
    }
    return $data;
  }

  public function executeQuery($query, $id)
  {
    if($id == '800') {
      $uids = $this->getPayingUsers();
      $query = preg_replace('/var/',"$uids", $query);
    }
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

  public function db_close()
  {
    mysql_close($this->conf_connfb);
  }
}

?>
