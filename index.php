<?php

  if(!version_compare(phpversion(), '5.0', '>=')) {
    die('<strong>Installation error:</strong> in order to run Feng Office you need PHP5. Your current PHP version is: ' . phpversion());
  } // if
  
  if (!function_exists('mysql_connect')) {
    require 'mysql_compat.php';
  }
  require 'init.php';
  
?>