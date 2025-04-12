<?php

/*

Update the user record for referral program join

*/

require_once('functions.php');
session_start();
$dbh = dbConnect();
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $query = "UPDATE users SET affiliateprogram = 1, joinedaffiliateprogram = NOW() WHERE id = " . $_SESSION['userid'];
  $result = $dbh->exec($query);
  $_SESSION['affiliateprogram'] = 1;
  # let admin know
  $firstname = $_SESSION['firstname'];
  $surname = $_SESSION['surname'];
  $email = $_SESSION['email'];
  $sitename = SITENAME;
  $siteemail = SITEEMAIL;
  $adminemail = "$sitename <$siteemail>";
  $subject = "$firstname $surname joined affiliate program at MWO";
  $body = "Affiliate Program join:\n$firstname $surname\n$email\n";
  adminEmail($adminemail, $subject, $body);
  echo "SUCCESS";
} catch(PDOException $e) {
  echo $e->getMessage();
}

