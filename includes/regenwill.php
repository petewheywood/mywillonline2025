<?php
session_start();
require_once("functions.php");
$dbh = dbConnect();
require_once("sessioninit.php");
$orderID =  $_GET['id']; 

// read data into session and then goto will
$result = $dbh->query("SELECT * FROM quickwillorders WHERE id = $orderID;");
$row = $result->fetch(PDO::FETCH_ASSOC);
$qwOrderData = $row['orderdata'];
$txnid = $row['tx'];
$tcstatus = $row['tcstatus'];
$_SESSION['qwOrderData'] = unserialize(base64_decode($qwOrderData));
if ($tcstatus) {
  $_SESSION['qwOrderData']['tcstatus'] = $tcstatus;
  $_SESSION['qwOrderData']['txnid'] = $txnid;
}

header("Location: /quickwill.html");
?>
