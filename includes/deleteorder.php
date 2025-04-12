<?php

/*

Delete an order but only if unpaid for. Is called by AJAX from the home_user.php page

Created: 16/5/2013
Author: Pete Heywood

*/

require_once('functions.php');

$dbh = dbConnect();
$orderid = $_GET['orderid'];

$sth = $dbh->query("SELECT paid FROM orders WHERE id = $orderid;");
$paid = $sth->fetchColumn();

if ($paid != 1) {
  # delete 
  $result = $dbh->exec("DELETE FROM orders WHERE id = $orderid;");
  echo "success";
} else {
  echo "You cannot delete a paid for order.";
}


