<?php
  
/*
  This will automatically responde to bounce and complaint emails. It is called by receiving an Amazon SNS notification.
*/

require_once("../includes/functions.php");

# process SES bounces from Amazon SES notification

$postBody = file_get_contents('php://input');

$jsondata = json_decode($postBody);

$message = json_decode($jsondata->Message);

if ($message->notificationType == 'Bounce' ) {
  $bouncetype = $message->bounce->bounceType;
  $emailaddress = $message->bounce->bouncedRecipients[0]->emailAddress;
  $subject = $message->mail->commonHeaders->subject;
  error_log("$bouncetype bounce of email address $emailaddress for email subject '$subject'");
  $fh = fopen("bounces.txt", "a");
  fwrite($fh, $postBody . "\n");
  fclose($fh);  
  # set the bounce flag for this user if in the users table
  if ($bouncetype = 'Permanent') {
    $dbh = dbConnect();
    $result = $dbh->exec("UPDATE users SET flags = flags | " . FLAG_BOUNCE . " WHERE emailaddress = '$emailaddress';");
    $dbh = null;
  }
}

if ($message->notificationType == 'Complaint') {
  $emailaddress = $message->complaint->complainedRecipients[0]->emailAddress;
  $subject = $message->mail->commonHeaders->subject;
  error_log("Complaint from email address $emailaddress for email subject '$subject'");
  $fh = fopen("complaints.txt", "a");
  fwrite($fh, $postBody . "\n");
  fclose($fh);
  # remove ability to login and delete email so no potential to email them
  # if they are not in the users table, the query will do nothing
  $dbh = dbConnect();
  $randompw = RandomString();
  $result = $dbh->exec("UPDATE users SET flags = flags | " . FLAG_BOUNCE . ", emailaddress = CONCAT(emailaddress, '_', '$randompw'), password = '$randompw' WHERE emailaddress = '$emailaddress';");
  $dbh = null;
}