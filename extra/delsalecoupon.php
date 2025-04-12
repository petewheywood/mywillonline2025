<?php
/* This will be kicked off using cron at midnight at end of month to delete any sale coupons */

error_reporting(E_ALL);
ini_set('display_errors', 1);

chdir(__DIR__);
require_once("../includes/functions.php");

# make sure this can only be run from this server
$serverip = gethostbyname($_SERVER['HTTP_HOST']);
$remoteip = $_SERVER['REMOTE_ADDR'];
if ($remoteip != $serverip) {
  header("Location: /");
  exit;
}

$dbh = dbConnect();
$result = $dbh->exec("DELETE FROM users WHERE flags = 4;"); # only SALE users have flags = 4
$dbh = null;
?>