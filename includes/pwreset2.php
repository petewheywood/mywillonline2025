<?php
session_start();
if (!isset($_SESSION['resettingPW']) or ($_SESSION['resettingPW'] != $_POST['pwuserid'])) header("Location: /");
unset($_SESSION['resettingPW']);

require_once('functions.php'); 

# email and password come in as a POST var

# only change the password if a new one was sent from the resetpassword.html page
if ($_POST['password'] > '') {
  $pwd = $_POST['password'];
  $dbh = dbConnect();
  $dbh->exec("UPDATE users SET password = MD5('$pwd') WHERE id = " . $_POST['pwuserid']);
  $dbh = null;
}
header("Location: /");
?>