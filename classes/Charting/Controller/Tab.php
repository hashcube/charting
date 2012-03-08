<?php
namespace Charting\Controller;
use Charting\Controller,
    Charting\Utils;

class Tab extends Controller
{

  function __construct()
  {
    parent::__construct();
    $this->starttime = microtime(true);
  }

  function __destruct()
  {
    $time_taken = microtime(true) - $this->starttime;
    //echo "\nTime taken : $time_taken seconds\n";
    //echo "Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\n";
    //echo "memory usage: " . (memory_get_usage(true) / 1024 / 1024) . " MB\n";
  }

  function display($page="")
  {
    if($page=="") {
      $this->showDefault();
    }
    else {
      $this->displayCharts($page);
    }
  }

  function showDefault()
  {
  }

  function displayCharts($id)
  {
    $data = $this->db->getChartsForTab($id);
    if($id != '4')
    {
      $data = Utils::fixMissingDates($data);
      $this->view->LTV_data = $this->db->payingUsersLTVQuery();
      $this->view->paying_users_segmntd_data = $this->db->payingUsersSegmentedData();
      $this->view->revenue_segment_data = $this->db->revenueSourceSegmentedQuery();
    }
    $data = Utils::formatDates($data);
    $this->view->json_data = json_encode($data, JSON_NUMERIC_CHECK);
    $this->view->tabid = $id;
    echo $this->view->render(\Charting\PROJROOT.'static/template/index.html');
  }
}

?>
