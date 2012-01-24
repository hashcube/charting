<?php
namespace Charting\Sql;


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
      $this->conf_connfb = \mysql_connect(\Charting\DBHOST, \Charting\USER, \Charting\PASS) or die ('Error connecting to mysql');
      if(!$this->conf_connfb)
      {
        exit("connecting to mysql failed");
      }
      $set_time_zone = "SET time_zone = 'PST8PDT'";
      mysql_query($set_time_zone, $this->conf_connfb);
      print_r(mysql_error());
      mysql_select_db(\Charting\CONFDB, $this->conf_connfb);
    }
    //$this->app_db_connect();
  }

  function app_db_connect($db)
  {
    if(!$this->connfb)
    {
      $this->connfb = \mysql_connect(\Charting\DBHOST, \Charting\USER, \Charting\PASS, true) or die ('Error connecting to mysql');
      if(!$this->connfb)
      {
        exit("connecting to mysql failed");
      }
      mysql_select_db($db,$this->connfb);
    }
  }

  public function addChart($chartid, $title, $type, $x_axis, $y_axis)
  {
    $this->db_connect();
    $chartid = mysql_real_escape_string($chartid);
    $title = mysql_real_escape_string($title);
    $type= mysql_real_escape_string($type);
    $x_axis= mysql_real_escape_string($x_axis);
    $y_axis= mysql_real_escape_string($y_axis);
    $query = "INSERT INTO charts VALUES ('$chartid', '$title', '$type', '$x_axis', '$y_axis')";
    $res = mysql_query($query, $this->conf_connfb);
    return $res;
  }

  public function addGraph($chartid, $query_text, $y_axis, $type)
  {
    $this->db_connect();
    $query = "INSERT INTO chart_info VALUES ('$chartid', '$query_text', '$y_axis', '$type')";
    mysql_query($query, $this->conf_connfb);
  }

  public function addToTab($tabid, $chartid) 
  {
    $this->db_connect();
    $query = "UPDATE tabs SET charts=CONCAT_WS(',', charts, '$chartid') where id='$tabid'";
    mysql_query($query, $this->conf_connfb);
  }

  public function getProjects()
  {
    $this->db_connect();
    $query = "SELECT * FROM projects";
    $res = mysql_query($query, $this->conf_connfb);
    $projects = array();
    $i = 0;
    while($row = mysql_fetch_array($res,MYSQL_ASSOC))
    {
      $projects[$i]['id'] = $row['id'];
      $projects[$i]['name'] = $row['name'];
      $projects[$i]['tabs'] = $row['tabs'];
      $i++;
    }
    return $projects;
  }

  public function getTabs($id)
  {
    $this->db_connect();
    $query = "SELECT * FROM tabs WHERE id=$id";
    $res = mysql_query($query, $this->conf_connfb);
    while($row = mysql_fetch_array($res, MYSQL_ASSOC))
    {
      $tab['name'] = $row['name'];
      $tab['id'] = $row['id'];
    }
    return $tab;
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

  public function getActiveUsersData()
  {
    $app_ids = $this->getAppIds();
    $charts = array();
    $metrics = array('dau' => 'DAU', 'wau' => 'WAU', 'mau' => 'MAU', 'dau*100/wau' => 'Weekly Engagement',
                        'dau*100/mau' => 'Monthly Engagement');
    foreach($app_ids as $app_id => $app_name)
    {
      $charts[$app_id]['title'] = "$app_name Active Users";
      $charts[$app_id]['chart_type'] = 'line';
      $charts[$app_id]['x_axis'] = 'Date';
      $charts[$app_id]['y_axis'] = 'No Of Users';
      $charts[$app_id]['type'] = 'result';
      
      foreach($metrics as $metric => $metric_rep)
      {
        $data = $this->getActiveUsersChartData($metric, $app_id, $metric_rep);
        $charts[$app_id]['charts'][] = $data;
      }
    }
    return $charts;
  }

  private function getAppIds()
  {
    $query = "SELECT * FROM app_details";
    $result = mysql_query($query);
    $app_details = array();
    while($row = mysql_fetch_array($result))
    {
      $app_details[$row['app_id']] = $row['app_name'];
    }
    return $app_details;
  }

  private function getActiveUsersChartData($metric, $app_id, $metric_rep)
  {
    $chart_info = array();
    $query = "SELECT DATE(`date`) as x, $metric as y FROM `active_users` WHERE app_id = $app_id GROUP BY x ORDER BY x ASC";
    $chart_info['result'] = $this->executeQuery($query, $app_id);
    $chart_info['y_axis'] = $metric_rep;
    return $chart_info;
  }

  public function getChartsForTab($id)
  {
    $data = $this->getTabData($id);
    $ids = $data['chart_ids'];
    $db = $data['db'];
    $this->app_db_connect($db);
    if($id == '4')
    {
      return $this->getActiveUsersData();
    }
    return $this->getSpecificCharts($ids);
  }

  private function getTabData($id)
  {
    $this->db_connect();
    $query = "SELECT * from tabs where id = $id";
    $res = mysql_query($query, $this->conf_connfb);
    while($row = mysql_fetch_array($res, MYSQL_ASSOC))
    {
      $ids=$row['charts'];
      $db = $row['db'];
    }
    return(array("chart_ids" => $ids, "db" => $db));
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
    $data = $this->getQueryData($query);
    return $data;
  }

  public function payingUsersSegmentedData()
  {
    $this->db_connect();
    $query = "SELECT max_milestone, starttime, DATE_FORMAT(date, '%d %b %T'), gender, country, amount, source, credits.uid "
      . "FROM credits, user_game,  user_info WHERE date >= NOW() - INTERVAL 5 day AND "
      . "credits.uid=user_info.uid AND credits.uid=user_game.uid ORDER BY date;";
    $data = $this->getQueryData($query);
    return $data;

  }

  private function getQueryData($query)
  {
    $this->db_connect();    
    $res = mysql_query($query, $this->connfb);
    $data = array();
    $i = 0;
    while($row = mysql_fetch_array($res, MYSQL_ASSOC))
    {
      foreach($row as $key=>$value)
      {
        $data[$i][$key] = $value;
      }
      $i++;
    }
    return $data;
  }

  public function executeQuery($query, $id)
  {
    $x = $y = $result = array();
    if($id == '800') {
      $uids = $this->getPayingUsers();
      $query = preg_replace('/var/',"$uids", $query);
    }
    $res = mysql_query($query, $this->connfb);
    while($row = mysql_fetch_array($res, MYSQL_ASSOC))
    {
      $x[] = $row['x'];
      $y[] = (is_numeric($row['y'])) ? round($row['y'], 2) : $row['y'];
    }
    if(!empty($x)) {
      $result['x'] = $x;
      $result['y'] = $y;
    }
    return $result;
  }

  public function db_close()
  {
    mysql_close($this->conf_connfb);
  }
}

?>
