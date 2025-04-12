<?php

/*

Delete the logged on user and all their order details
Created: 30/12/2014
Author: Pete Heywood

*/
session_start();
require_once('functions.php'); 

$userid = $_SESSION['userid'];
$email = $_SESSION['email'];

if ($userid) {
  $dbh = dbConnect();
  $result = $dbh->exec("DELETE FROM orders WHERE userid = $userid;");
  $result = $dbh->exec("DELETE FROM users WHERE id = $userid;");
  $result = $dbh->exec("DELETE FROM organisations WHERE userid = $userid;");
  session_unset();
  mail(SITEEMAIL, "user deletion", "$email deleted");
  echo "success";
} else {
  echo "Failed to delete null user.";
}


