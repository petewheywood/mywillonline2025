<?php
session_start();
require_once('functions.php'); 

if (!isset($_SESSION['userid']) || !$_SESSION['admin']) {
  header('HTTP/1.0 403 Forbidden');
  exit;
}

$dbh = dbConnect();
  
$query = "
SELECT month, monthlycount as count, monthlytotal as total, dailycountaverage as countavg, dailytotalaverage as totalavg FROM dailyaverage LIMIT 12
";
$resultset = $dbh->query($query);
$results = $resultset->fetchAll(PDO::FETCH_ASSOC);
$jsonresults = json_encode($results);  
$dbh = null;

echo $jsonresults;
?>