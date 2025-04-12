<?php

# processes a pending payment request from an affiliate

session_start();
require_once('functions.php'); 

if (!isset($_SESSION['userid']) || !$_SESSION['admin']) {
  header('HTTP/1.0 403 Forbidden');
  exit;
}


$dbh = dbConnect();
$payid = $_GET['payid'];
$query = "SELECT emailaddress, paymentamount FROM commissionpayments WHERE id = $payid;";
$result = $dbh->query($query);
$row = $result->fetch(PDO::FETCH_ASSOC);

$emailaddress = $row['emailaddress'];
$paymentamount = $row['paymentamount'];

$query = "UPDATE commissionpayments SET status = 'Completed' WHERE id = $payid;";
$result = $dbh->exec($query);
$dbh = null;

MassPayment($emailaddress, $paymentamount, $payid, "Your commission payment of $paymentamount has now been paid. Thank you.");
?>