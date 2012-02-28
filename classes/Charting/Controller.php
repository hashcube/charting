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


}
?>
