<?php
session_start();
if (!isset($_SESSION['resettingPW']) or ($_SESSION['resettingPW'] != $_POST['pwuserid'])) {
  header("Location: /");
  exit;
}
$pwuserid = $_SESSION['resettingPW'];
unset($_SESSION['resettingPW']);

require_once('functions.php');

# email and password come in as a POST var

# only change the password if a new one was sent from the resetpassword.html page,
# it meets the minimum length and both fields match
if ($_POST['password'] > '' && strlen($_POST['password']) >= 8 && $_POST['password'] === $_POST['password2']) {
  $pwd = $_POST['password'];
  $dbh = dbConnect();
  $sth = $dbh->prepare("UPDATE users SET password = MD5(:pwd) WHERE id = :userid");
  $sth->execute(array(':pwd' => $pwd, ':userid' => $pwuserid));
  $dbh = null;
}
header("Location: /");
exit;
?>