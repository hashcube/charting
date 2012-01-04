<?php
namespace Charting;

use Charting\Sql\Db,
    Charting\Template;

class Controller
{
  function __construct()
  {
     // do nothing yet
    $this->db = Db::getInstance();
    $this->view = new Template();
    $this->setConstants();
  }

  function __destruct()
  {
     // do nothing yet
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