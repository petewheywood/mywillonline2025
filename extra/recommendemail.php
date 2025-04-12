<?php
/* This will be kicked off using cron each day and will send emails to users based upon certain conditions */

$recommend = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

chdir(__DIR__);
require_once("../includes/functions.php");


# make sure this can only be run from this server
$serverip = gethostbyname($_SERVER['HTTP_HOST']);
$remoteip = $_SERVER['REMOTE_ADDR'];
if ($remoteip != $serverip) {
  header("Location: /");
  exit;
}


echo date("c") . " Processing test daily emails\n";

$dbh = dbConnect();


######## IMPORTANT - THE ORDER THESE EMAILS HAPPEN IS IMPORTANT ##########




# RECOMMEND
if ($recommend) {
  # send recommend emails to users 4 days after they have ordered - Note: flags = 0 means do not send to unsubscribed, notmyaccount or admin
  # also do not send them an email if they have already been sent one
  $type = "recommend";
  $days = 4;
  $query = "
    SELECT id, firstname, surname, emailaddress, affiliatecode, discount
    FROM mwo.users WHERE flags = 0 AND id IN (SELECT userid FROM mwo.orders where paiddate > '2019-03-30')
    ORDER BY id DESC;
  ";
  $result = $dbh->query($query);
  #echo $query . "\n";
  while($row = $result->fetch()) {
    $discount = $row['discount'];
    $discountperc = percent($row['discount']);
    # send an email
    sendTrackedEmail($row['id'], $row['affiliatecode'], $row['emailaddress'], $row['firstname'], $row['surname'], $type);
    // sendTrackedEmail($row['id'], $row['affiliatecode'], 'pete@itrakka.com', $row['firstname'], $row['surname'], $type);
    echo "Recommend email sent to " . $row['emailaddress'] . ".\n";
  }
}


$dbh = null;
?>