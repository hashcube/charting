<?php 
namespace Charting\Controller;

use Charting\Controller,
    Charting\Sql\Db;

class Main extends Controller
{
  private $db_obj;

  function __construct()
  {
    parent::__construct();
  }

  public function display()
  {
    $this->db_obj = Db::getInstance();  
    $this->view->projects = array();
    $projects = $this->db_obj->getProjects();
    foreach($projects as $key=>$project) {
      $this->view->projects[$key]['name'] = $project['name'];
      $tabs = explode(",", $project['tabs']);
      foreach($tabs as $id=>$tab) {
        $this->view->projects[$key]['tabs'][$id] = $this->db_obj->getTabs($tab);
      }
    }
    $this->view->projects = json_encode($this->view->projects);
    echo $this->view->render('static/template/default.html');
  }
}
?>
