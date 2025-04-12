<?php

# creates a pending payment request from an affiliate

# returns 'success' on success
session_start();
if (!isset($_SESSION['userid'])) header("Location: /");
require_once('functions.php'); 

# OK details are all here so process payment request
$dbh = dbConnect();
$userid = $_SESSION['userid'];
$paymentemail = $_SESSION['email'];
$firstname = $_SESSION['firstname'];
$surname = $_SESSION['surname'];
$affiliatecode = $_SESSION['affiliatecode'];
$query = "
  SELECT SUM(commissionamount) 
  FROM ordercommissions c
  WHERE commissionuserid = $userid AND commissionpaymentid is NULL AND commissiondate < DATE_SUB(NOW(), INTERVAL " . COMMISSIONPAYABLEDAYS . " DAY) AND level <= " . LEVELS;
$result = $dbh->query($query);
$paymentamount = $result->fetchColumn();

/*
error_log("Payment amount is $paymentamount");
echo "Payment request problem. Please <a href='contact.html'>contact support</a>";
exit;
*/

if ($paymentamount > 0) {
  $insertQuery = "
    INSERT INTO commissionpayments (userid, requestdate, paymentamount, status, emailaddress)
    VALUES ($userid, NOW(), $paymentamount, 'Pending', '$paymentemail');
  ";
  $result = $dbh->exec($insertQuery);
  if ($result) {
    $paymentid = $dbh->lastInsertID();
    #if (MassPayment($paymentemail, $paymentamount, $paymentid, "Your commission payment of $paymentamount has now been paid. Thank you.")) {
    if (true) {
      $result = $dbh->exec("
        UPDATE ordercommissions
        SET commissionpaymentid = $paymentid 
        WHERE commissionuserid = $userid 
          AND commissionpaymentid is NULL 
          AND commissiondate < DATE_SUB(NOW(), INTERVAL " . COMMISSIONPAYABLEDAYS . " DAY)
          AND level <= " . LEVELS ."
        ;
      ");
      # send tracked email to member and then to admin
      sendTrackedEmail($userid, $affiliatecode, $paymentemail, $firstname, $surname, "paymentrequest", '');
    	$sitename = SITENAME;
    	$siteemail = SITEEMAIL;
    	$adminemail = "$sitename <$siteemail>";
      sendTrackedEmail($userid, $affiliatecode, $adminemail, $firstname, $surname, "paymentrequest", '');
      # send payment request email to admin
      $body = "$firstname $surname - $paymentemail - requested a commission payment of " . currency($paymentamount);
      $subject = "Pending commission payment request";
      adminEmail($adminemail, $subject, $body);
      echo "success";    
    } else {
      $result = $dbh->exec("DELETE FROM commissionpayments WHERE id = $paymentid;");
      echo "Payment request problem.  Please <a href='contact.html'>contact support</a>";
    }
  } else {
    echo "Payment request problem. Please <a href='contact.html'>contact support</a>";
  }
} else {
  echo "You have no payable commissions available.";
}
?>