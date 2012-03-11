<?php
  const DBHOST = 'localhost';
  const USER = 'myuser';
  const PASS = 'mypass';
  const CONFDB = 'mycharts';

  define('Charting\APPURL','http://localhost/charting/');
  define('Charting\APPID','fb-app-id');
  define('Charting\APPSECRET','fb-app-secret');
  define('Charting\PROJROOT', dirname(__FILE__) . '/' );

  // Set autoload classpath for loading classes when object is created, avoiding
  // the creation of include/require
  $class_root = PROJROOT . 'classes';
  require_once($class_root.'/Charting/ClassLoader.php');
  ClassLoader::register($class_root);
?>
