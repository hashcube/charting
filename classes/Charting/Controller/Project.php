<?php
namespace Charting\Controller;
use Charting\Controller;

class Project extends Controller
{

  function __construct()
  {
    parent::__construct();
  }

  function display($page="")
  {
    if($page=="") {
      $this->listProjects();
    }
  }

  function listProjects()
  {
    echo "nothing here yet";
    //print_r($this->db->getProjects());
  }

}
?>