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
    $this->view->projects = json_encode($this->getProjects());
    echo $this->view->render('static/template/default.html');
  }
}
?>
