<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

/*

move an order from one user to another
*/

$userFrom = $_GET['from'];
$userTo = $_GET['to'];
$orderID = $_GET['order'];

require_once('../includes/functions.php');


if ($userFrom > 0 && $userTo > 0 && $orderID > 0) {

  $dbh = dbConnect();

  // get codes
  $result = $dbh->query("SELECT affiliatecode FROM users WHERE id = $userFrom;");
  $affiliateCodeFrom = $result->fetchColumn();
  $result = $dbh->query("SELECT affiliatecode FROM users WHERE id = $userTo;");
  $affiliateCodeTo = $result->fetchColumn();
  
  // get order
  $sth = $dbh->query("SELECT AES_DECRYPT(orderdata,'$affiliateCodeFrom') FROM orders WHERE id = $orderID;");
  $orderdata = $sth->fetchColumn();
  
  $query = "
    UPDATE orders SET userid = $userTo, orderdata = AES_ENCRYPT('$orderdata','$affiliateCodeTo')  WHERE id = $orderID;
  ";
  
  $dbh->exec($query);
  echo "Done";  
  $dbh = null;
} else {
  echo "";
}

