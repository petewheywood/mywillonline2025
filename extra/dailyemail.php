<?php
/* This will be kicked off using cron each day and will send emails to users based upon certain conditions */

$firstreminder = true;
$secondreminder = true;
$feedback = true;
$recommend = true;
$firstsale = false;
$finalsale = false;


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


echo date("c") . " Processing daily emails\n";

$dbh = dbConnect();


######## IMPORTANT - THE ORDER THESE EMAILS HAPPEN IS IMPORTANT ##########



# FEEDBACK
if ($feedback) {
  # send feedback emails to users 1 day after they have ordered - Note: flags = 0 means do not send to unsubscribed, notmyaccount or admin
  # also do not send them an email if they have already been sent one
  $type = "feedback";
  $query = "
  SELECT id, firstname, surname, emailaddress, affiliatecode 
  FROM users WHERE flags = 0 AND id IN (SELECT DISTINCT userid FROM orders WHERE paiddate > SUBDATE(CURDATE(),1) AND paiddate < CURDATE())
  AND id NOT IN (SELECT userid FROM emailsent WHERE type = \"$type\");
  ";
  $result = $dbh->query($query);
  while($row = $result->fetch()) {
    # send an email
    sendTrackedEmail($row['id'], $row['affiliatecode'], $row['emailaddress'], $row['firstname'], $row['surname'], $type);
    echo "Feedback email sent to " . $row['emailaddress'] . "\n";
  }
}

# RECOMMEND
if ($recommend) {
  # send recommend emails to users 4 days after they have ordered - Note: flags = 0 means do not send to unsubscribed, notmyaccount or admin
  # also do not send them an email if they have already been sent one
  $type = "recommend";
  $days = 4;
  $query = "
  SELECT id, firstname, surname, emailaddress, affiliatecode, discount
  FROM users WHERE flags = 0 AND id IN (SELECT DISTINCT userid FROM orders WHERE DATE(paiddate) = SUBDATE(CURDATE(),$days))
  AND id NOT IN (SELECT userid FROM emailsent WHERE type = \"$type\");
  ";
  $result = $dbh->query($query);
  #echo $query . "\n";
  while($row = $result->fetch()) {
    $discount = $row['discount'];
    $discountperc = percent($row['discount']);
    # send an email
    sendTrackedEmail($row['id'], $row['affiliatecode'], $row['emailaddress'], $row['firstname'], $row['surname'], $type);
    //sendTrackedEmail($row['id'], $row['affiliatecode'], SITEEMAIL, $row['firstname'], $row['surname'], $type);
    echo "Recommend email sent to " . $row['emailaddress'] . "\n";
  }
}


# FIRST REMINDER
# send first reminder email if they registered yesterday and have not yet ordered
if ($firstreminder) {
  $type = "firstreminder";
  $days = 2;
  $query = "
  SELECT id, firstname, surname, emailaddress, affiliatecode 
  FROM users WHERE DATE(created) = SUBDATE(CURDATE(),$days) AND flags = 0 AND id NOT IN (SELECT DISTINCT userid FROM orders WHERE paid = 1)
  AND id NOT IN (SELECT userid FROM emailsent WHERE type = \"$type\");
  ";
  $result = $dbh->query($query);
  while($row = $result->fetch()) {
    # send an email
    sendTrackedEmail($row['id'], $row['affiliatecode'], $row['emailaddress'], $row['firstname'], $row['surname'], $type);
    echo "First reminder email sent to " . $row['emailaddress'] . "\n";
  }
}


# SALE COUPON
if ($firstsale) {
  $discount = 0.4; // this is the percentage discount which will show in emails
  # setup sale coupon code if 5 days before end of month
  $daysForEOM = 3;
  $daysTillEOM = daysTillEOM();
  if ($daysTillEOM == $daysForEOM) {
    $couponcode = $couponemail = strtoupper(date("MY"));
    $couponname = date("F Y") . " End of Month";
    $couponname2 = "Sale";
    echo date("c") . " Adding sale coupon code " . $couponcode . " for " . date("M Y") . "\n";
    $result = $dbh->exec("INSERT INTO users (firstname, surname, emailaddress, affiliatecode, discount, flags, created) VALUES (\"$couponname\", \"$couponname2\", \"$couponemail\", \"$couponcode\", $discount, 4, NOW());");
  }
}


# FIRST SALE EMAIL
if ($firstsale) {
  # send initial sale emails to all users that have received second reminders, have not ordered and have not received sale email yet
  if ($daysTillEOM > 0 && $daysTillEOM <= $daysForEOM) {
    $type = "endofmonth";
    $couponcode = strtoupper(date("MY"));
    $query = "
    SELECT id, firstname, surname, emailaddress, affiliatecode 
    FROM users WHERE flags = 0 AND id NOT IN (SELECT DISTINCT userid FROM orders WHERE paid = 1)
    AND id IN (SELECT userid FROM emailsent WHERE type = \"secondreminder\") AND id NOT IN (SELECT userid FROM emailsent WHERE type = \"$type\");
    ";
    $result = $dbh->query($query);
    while($row = $result->fetch()) {
      # send an email
      sendTrackedEmail($row['id'], $row['affiliatecode'], $row['emailaddress'], $row['firstname'], $row['surname'], $type);
      echo "First EOM Sale email sent to " . $row['emailaddress'] . "\n";
    }
  }
}


# SECOND REMINDER
if ($secondreminder) {
  # this comes after the first sale email so the user will not receive a SALE email and SECOND REMINDER email together
  # send second reminder email if they registered yesterday and have not yet ordered
  $type = "secondreminder";
  $days = 5;
  $query = "
  SELECT id, firstname, surname, emailaddress, affiliatecode 
  FROM users WHERE DATE(created) = SUBDATE(CURDATE(),$days) AND flags = 0 AND id NOT IN (SELECT DISTINCT userid FROM orders WHERE paid = 1)
  AND id IN (SELECT userid FROM emailsent WHERE type = \"firstreminder\") AND id NOT IN (SELECT userid FROM emailsent WHERE type = \"$type\");
  ";
  $result = $dbh->query($query);
  while($row = $result->fetch()) {
    # send an email
    sendTrackedEmail($row['id'], $row['affiliatecode'], $row['emailaddress'], $row['firstname'], $row['surname'], $type);
    echo "Second reminder email sent to " . $row['emailaddress'] . "\n";
  }
}

# FINAL SALE EMAIL
if ($finalsale) {
  # send lastday sale emails to all users that have received first sale email
  if ($daysTillEOM == 0) {
    $type = "endofmonthfinal";
    $couponcode = strtoupper(date("MY"));
    $query = "
    SELECT id, firstname, surname, emailaddress, affiliatecode 
    FROM users WHERE flags = 0 AND id NOT IN (SELECT DISTINCT userid FROM orders WHERE paid = 1)
    AND id IN (SELECT userid FROM emailsent WHERE type = \"endofmonth\") AND id NOT IN (SELECT userid FROM emailsent WHERE type = \"$type\");
    ";
    $result = $dbh->query($query);
    while($row = $result->fetch()) {
      # send an email
      sendTrackedEmail($row['id'], $row['affiliatecode'], $row['emailaddress'], $row['firstname'], $row['surname'], $type);
      echo "Final EOM Sale email sent to " . $row['emailaddress'] . "\n";
    }
  }
}


$dbh = null;
?>