<?php
/* This will be kicked off using cron each day and will send emails to users based upon certain conditions */

$feedback = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../includes/functions.php");

# make sure this can only be run from this server
$serverip = gethostbyname($_SERVER['HTTP_HOST']);
$remoteip = $_SERVER['REMOTE_ADDR'];
if ($remoteip != $serverip) {
  header("Location: /");
  exit;
}

echo "\n" . date("c") . " Processing daily emails\n";

# FEEDBACK
if ($feedback) {
  # send feedback emails to users who are in the list
  $todolist = file("../../" . DATADIR . "/feedback.todo", FILE_IGNORE_NEW_LINES);
  $cmd = "cp /dev/null ../../" . DATADIR . "/feedback.todo";
  `$cmd`;
  $website = url_origin();
  $sitename = SITENAME;
  $siteboss = SITEBOSS;
  $sitebossemail = SITEBOSSEMAIL;
  $sitebossphone = SITEBOSSMOBILE;
  $emails = array();
  foreach ($todolist as $todo) {
    $data = base64_decode($todo);
    $thefeedback = preg_split("/\|/", $data);
    $txnid = $thefeedback[0];
    $name = $thefeedback[1];
    $words = explode(' ',trim($name));
    $encodedname = urlencode($name);
    $firstname = $words[0];
    $email = $thefeedback[2];
    $state = $thefeedback[3];
    # don't send email twice to same person if they have ordered more than one
    if (in_array($email, $emails)) continue; 
    $emails[] = $email;
    
    echo "Email: $email, Name: $name, State: $state, TX: $txnid\n";
    
    $subject = "We would love to hear about your $sitename experience";
    $body = "
<div>
<p>Hi $firstname,</p>

<p>Thank you so much for using our service. We really appreciate you choosing us to create your Will.</p>

<p>If you have time (about 30 seconds) then please rate our service and let us know if we can improve.</p>

<p><a href=\"$website/feedback.html?email=$email&st=$state&tx=$txnid&nm=$encodedname\">Rate Us</a></p>

<p>Thank you and kind regards,</p>

<p>
  <em>$siteboss</em><br>
  Em. $sitebossemail
</p>
</div>     
    ";
    sendEmail("$name <$email>", $subject, $body);
    //sendEmail("Peter Heywood <peteheywood@me.com>", $subject, $body);
  }
}

?>