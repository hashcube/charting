<?php
namespace Charting;

use Charting\Sql\Db,
    Charting\Template;

class Controller
{
  function __construct()
  {
     // do nothing yet
    if(defined('\Charting\PROFILING') && \Charting\PROFILING)
    {
      \xhprof_enable(XHPROF_FLAGS_NO_BUILTINS+XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
    }
    $this->user = $this->authenticate();
    $this->db = Db::getInstance();
    $this->view = new Template();
    $this->setConstants();
  }

  function __destruct()
  {
     // do nothing yet
    if(defined('\Charting\PROFILING') && \Charting\PROFILING)
    {
      $xhprof_data = \xhprof_disable();
      $profiler_namespace = 'charting';  // namespace for your application

      include_once (\Charting\PROJROOT.'xhprof/xhprof_lib/utils/xhprof_runs.php');
      include_once (\Charting\PROJROOT.'xhprof/xhprof_lib/utils/xhprof_lib.php');

      $xhprof_runs = new \XHProfRuns_Default();
      $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

      // url to the XHProf UI libraries (change the host name and path)
      $profiler_url = sprintf(\Charting\APPURL.'xhprof/xhprof_html/index.php?run=%s&source=%s', $run_id, $profiler_namespace);
      $this->view->xhprof_out = '<a href="'. $profiler_url .'" target="_blank">Profiler output</a>';
    }
  }

  protected function setConstants()
  {
    $arr = get_defined_constants(true);
    foreach ($arr['user'] as $key=>$value)
    {
      $ns_string = 'Charting\\';
      if (substr($key, 0, strlen($ns_string)) != $ns_string)
        continue;

      $key = str_replace($ns_string, '', $key);
      $key = strtolower($key);
      $this->view->$key = $value;
    }
  }

  protected function authenticate()
  {
    require_once(\Charting\PROJROOT.'php-sdk/src/facebook.php');
    $facebook = new \Facebook(array("appId"=>\Charting\APPID, "secret"=>\Charting\APPSECRET));
    $user = $facebook->getUser();
    if (!$user)
    {
      $params = array('redirect_uri' => $this->getCurrentUrl());
      $loginUrl = $facebook->getLoginUrl($params);
      echo("<script> top.location.href='$loginUrl'</script>");
      exit();
    }
    return $user;
  }

  protected function getCurrentUrl()
  {
    if (isset($_SERVER['HTTPS']) &&
      ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
      isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
      $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        $protocol = 'https://';
      }
    else {
      $protocol = 'http://';
    }
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  protected function getProjects()
  {
    $db_obj = Db::getInstance();
    $proj = array();
    $projects = $db_obj->getProjects();
    foreach($projects as $key=>$project) {
      $proj[$key]['name'] = $project['name'];
      $proj[$key]['id'] = $project['id'];
      $tabs = explode(",", $project['tabs']);
      foreach($tabs as $id=>$tab) {
        $proj[$key]['tabs'][$id] = $db_obj->getTabs($tab);
      }
    }
    return $proj;
  }
}
?>
