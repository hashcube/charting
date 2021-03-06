<?php

namespace Charting;

class Dispatcher
{
  public static function dispatch($path_info="")
  {
    if(strlen($path_info) <= 1)
      $path_info = "";
    if($path_info=="") {
      $controller_class = 'Charting\Controller\Main';
      $page = "";
    }
    else {
      $path_info = explode('/',$path_info);
      array_shift($path_info);
      if(count($path_info)<=2) {
        if($path_info[0] == "project") {
          $controller_class = 'Charting\Controller\Project';
        }
        $page = (isset($path_info[1]))?$path_info[1]:"";
      }
      else if(count($path_info)<=4) {
        if($path_info[2] == "tab") {
          $controller_class = 'Charting\Controller\Tab';
        }
        $page = (isset($path_info[3]))?$path_info[3]:"";

        if($path_info[0] == "ajax"){
          $controller_class = 'Charting\Controller\Ajax\\'.ucfirst($path_info[1]);
          $method = $path_info[2];
        }
      }
      //$controller_class = 'Charting\\Controller\\'.ucfirst($path_info[0]);
      //$page = $path_info[1];
    }

    $controller = new $controller_class();
    if (method_exists($controller,"display")) {
      $controller->display($page);
    }
    else if(method_exists($controller,$method)) {
      $controller->$method();
    }
    else {
      echo "method doesn't exist - controller_class: $controller_class  - method $page";
    }
  }
}
?>
