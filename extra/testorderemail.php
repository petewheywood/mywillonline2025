<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once("../includes/functions.php");
$dbh = dbConnect();
$sessionID = session_id();
require_once("../includes/sessioninit.php");

session_start();
$sessionname = session_name();
//echo "<pre>session_id : $sessionID\n" . print_r($_SESSION, true) . "\n\n" . print_r($_COOKIE, true) . "\n</pre>";

sendOrderEmail(206240, 'commweb', 'AUG2017');