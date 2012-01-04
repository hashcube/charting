<?php

 /************************************************************
  *
  * index.php
  ************************************************************/

namespace Charting;

require_once dirname(__FILE__).'/conf.php';
Dispatcher::dispatch($_SERVER['PATH_INFO']);

?>