<?php
namespace Charting\Controller;
use Charting\Controller,
    Charting\Utils;

class Tab extends Controller
{

  function __construct()
  {
    parent::__construct();
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
    $data = Utils::fixMissingDates($data);
    $data = Utils::formatDates($data);

    $this->view->LTV_data = $this->db->payingUsersLTVQuery();
    $this->view->json_data = json_encode($data, JSON_NUMERIC_CHECK);
    //echo "<pre>"; print_r($LTV_data); echo "</pre>";
    echo $this->view->render(\Charting\PROJROOT.'static/template/index.html');
  }

}

?>