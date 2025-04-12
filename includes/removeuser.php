<?php
/***************************************
 *   Title:    Remove a user and their orders from the database permanently
 *
 *   Description:
 *    This will be called via AJAX for a jQuery UI Autocomplete field. Data returned is a success/error message
 *
 *
 *  Author:    Pete Heywood
 *  Email:    peteheywood@me.com
 *
 *  Copyright Ⓒ  2012
 *
 ***************************************/
session_start();
require_once('functions.php'); 
if (!isset($_SESSION['userid']) || !$_SESSION['admin']) {
  echo "Invalid";
  exit;
}

$email = $_POST['emailaddress'];
$msg = '';
if ($email) {
  $dbh = dbConnect();
  
  $query = "
  SELECT u.id, u.firstname, u.surname, u.emailaddress, o.paid, count(o.paid) as ordercount
  FROM users u LEFT JOIN orders o on (o.userid = u.id) 
  WHERE u.emailaddress = '$email' GROUP BY u.id, u.firstname, u.surname, u.emailaddress, o.paid;
  ";
  $resultset = $dbh->query($query);
  $id = 0;
  $name = '';
  $unpaid_orders = $paid_orders = 0;
  
  while ($row = $resultset->fetch(PDO::FETCH_ASSOC)) {
      $id = $row['id'];
      $name = $row['firstname'] . ' ' . $row['surname'];
      $unpaid_orders = $row['paid'] == 0 ? $row['ordercount'] : $unpaid_orders;
      $paid_orders = $row['paid'] == 1 ? $row['ordercount'] : $paid_orders;
  }
  
  if ($id == 0) {
    header('HTTP/1.0 404 Not found in database');
    exit;
  } else {
    if ($paid_orders > 0) {
      # cannot delete the user because they have paid orders so just flag them not to receive emails
      $result = $dbh->exec("UPDATE users SET flags = flags | " . FLAG_UNSUBSCRIBE . " WHERE id = $id;");
      $msg = "$name ($email) has $paid_orders paid orders and $unpaid_orders unpaid orders. They were not deleted, just flagged to not receive emails.";
    } else {
      # delete the user and amy unpaid orders
      $result = $dbh->exec("DELETE FROM orders WHERE userid = $id;");
      $result = $dbh->exec("DELETE FROM users WHERE id = $id;");
      $result = $dbh->exec("DELETE FROM organisations WHERE userid = $id;");
      $msg = "$name ($email) has no paid orders. They have been deleted.";
    }
  }
}
echo $msg;
?>