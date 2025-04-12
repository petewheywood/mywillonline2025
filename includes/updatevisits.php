<?php

/* 

This is called by AJAX with the window.onunload event to write the exit date time into pagevisits


*/
session_start();
require_once('functions.php'); 
$sessionID = $_GET['sessionid'];
# update query
if (!isset($_SESSION['javascriptEnabled'])) {
  $cookiesEnabled = $_GET['cookieenabled'] == 'true' ? 1 : 0;
  $query = "UPDATE visits SET javascriptenabled = 1, cookiesenabled = $cookiesEnabled WHERE sessionid = '$sessionID';";
  //error_log($query);
  $dbh = dbConnect();
  $dbh->exec($query);
  $dbh = null;
  $_SESSION['javascriptEnabled'] = 1;
  if ($cookiesEnabled) $_SESSION['cookiesEnabled'] = 1;
}
?>