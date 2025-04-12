<?php
/* This will be kicked off manually for one off emails */

error_reporting(E_ALL);
ini_set('display_errors', 1);

chdir(__DIR__);
require_once("../includes/functions.php");


# make sure this can only be run from this server
$serverip = '58.163.150.63';
$remoteip = $_SERVER['REMOTE_ADDR'];
if ($remoteip != $serverip) {
  header("Location: /");
  exit;
}

$boundary = md5(uniqid(time()));
$siteemail = SITEEMAIL;
$sitename = SITENAME;
$sitecompany = SITECOMPANY;
$siteabn = SITEABN;
$siteaddress = SITEADDRESS;
$siteboss = SITEBOSS;
$sitebossrole = SITEBOSSROLE;
$sitebossmobile = SITEBOSSMOBILE;
$sitebossemail = SITEBOSSEMAIL;
$website = url_origin();
$year = date("Y");
$fromemail = "$siteboss <$sitebossemail>";


echo "<pre>" . date("c") . " Processing one off emails\n";

$dbh = dbConnect();

$type= 'review';
$query = "
SELECT 
userid, affiliatecode, firstname, surname, emailaddress, phone, MAX(paiddate) as orderdate
FROM user_orders
WHERE flags = 0 AND paid = 1 AND extaxamount is not null AND paiddate > '2011-01-01' AND userid NOT IN (SELECT userid FROM emailsent WHERE type = \"$type\")
GROUP BY 1, 2, 3, 4, 5
ORDER BY 1;
";
$result = $dbh->query($query);
while($row = $result->fetch()) {
  $firstname = ucfirst($row['firstname']);
  $surname = $row['surname'];
  $toemail = $row['emailaddress'];
  #$toemail = 'peteheywood@me.com';
  $userid = $row['userid'];
  $orderdate = showdate($row['orderdate']);
  $monthyear = monthyear($row['orderdate']);
  $affiliatecode = $row['affiliatecode'];
  
  # add the email to the emailsent table
  $query = "INSERT INTO emailsent (userid, sent, type) VALUES ($userid, NOW(), \"$type\");";
  $dbh->exec($query);
  # get id 
  $emailID = $dbh->lastInsertId();

  # prepare message and send
  $subject = "What do you think of My Will Online?";
  $body = "
  <div>
  <p>Hi $firstname,</p>
  
  <p>Thank you for being a customer of $sitename in $monthyear. We really appreciate your business, and really hope that the experience was excellent and that you&apos;re happy with your finished will documents.</p>
  
  <p>We are reminding you that you can update and re-download your will for <strong>FREE</strong> anytime your circumstances change. Once signed and witnessed, the updated will replaces any previous wills. You can login to update your will <a href=\"$website/login.html?email=$toemail\">here</a>.</p>
  
  <p><em>Until November 30, $sitename is offering Last Will and Testament and Enduring Power of Attorney documents for <strong>FREE</strong>. I know that you already have free updates for life on your will documents, but your friends and family would benefit if they don&apos;t already have a will.</em></p>
  
  <p>If you&apos;ve been happy with $sitename service, then we would really appreciate a positive rating.</p>
  
  <a target=\"_blank\" href=\"https://www.google.com.au/search?num=40&newwindow=1&ei=Bhz3W9WJGcj0vgTrtZrACg&q=my+will+online&oq=my+will+online&gs_l=psy-ab.3..0l2j0i22i30l8.32520.39358..39494...2.0..0.264.2885.0j11j4......0....1..gws-wiz.......0i71j0i131j0i67j0i10j0i22i10i30.W4BR7ZU1rMM#lrd=0x6b75f926ebc2e05d:0x3521d95cb8799789,3,,,\" class=\"button text-success\">Rate My Will Online</a>
  
  <p>If you think we could do better, then please let us know by sending us some feedback.</p>
  
  <a href=\"$website/feedback.html?code=$affiliatecode\" class=\"button text-warning\">Send Feedback</a>
  
  <p>Sincerely,</p>
  
  <p>$siteboss - $sitebossrole</p>
  </div>     
  ";
  $unsubscribe =  "| <a href=\"$website/unsubscribe.html?code=$affiliatecode\">Unsubscribe</a>";
  require("../includes/emailbody.php");
  $plaintext = strip_tags($body);
  $eol = PHP_EOL;
  $header = "From: $fromemail$eol"
           . "Reply-To: $fromemail$eol"
           . "MIME-Version: 1.0$eol"
           . "Content-Type: multipart/alternative; boundary=" . $boundary;
  $message = "--" . $boundary . "$eol"
           . "Content-type: text/plain; charset=iso-8859-1$eol"
           . "Content-Transfer-Encoding: 7bit$eol$eol"
           . $plaintext . "$eol"
           . "--" . $boundary . "$eol"
           . "Content-type: text/html; charset=iso-8859-1$eol"
           . "Content-Transfer-Encoding: 7bit$eol$eol"
           . $emailhtml . "$eol"
           . "--" . $boundary . "--"
           ;
  mail($toemail, $subject, $message, $header);
  echo "One off email sent to $toemail\n";
}

$dbh = null;
?>