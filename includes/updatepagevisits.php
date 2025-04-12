<?php

/* 

This is called by AJAX with the window.onunload event to write the exit date time into pagevisits


*/
require_once('functions.php'); 
$pageID = $_GET['pageID'];
$sessID = $_GET['sessID'];

# update query
$dbh = dbConnect();
$dbh->exec("UPDATE pagevisits SET leavedate = NOW() WHERE id = $pageID;");
$query = "UPDATE visits SET lastactivity = NOW() WHERE sessionid = '$sessID';";
//error_log($query);
$dbh->exec($query);
$dbh = null;
?>