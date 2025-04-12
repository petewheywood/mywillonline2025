<?php

/*

Delete auser and all their order information

Called via AJAX from updateprofile page when user selects Permanently Delete account

Created: 04/11/2014
Author: Pete Heywood

*/

require_once('functions.php');
session_start();
$dbh = dbConnect();
$userid = $_SESSION['userid'];

if ($userid > 0) {
  $result = $dbh->exec("DELETE FROM orders WHERE userid = $userid;");
  $result = $dbh->exec("DELETE FROM users WHERE id = $userid;");
  $result = $dbh->exec("DELETE FROM organisations WHERE userid = $userid;");  
  echo "success";
} else {
  echo "Failed to delete your data. Userid = $userid. Please contact customer service using the contact page.";
}


