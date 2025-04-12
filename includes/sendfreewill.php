<?php
# send free will to user after logging them in the database


session_start();
require_once("functions.php");

# name, email, phone, message and if logged on antibot sum come in as a POST var
$firstname = $_REQUEST['firstname'];
$surname = $_REQUEST['surname'];
$emailaddress = $_REQUEST['emailaddress'];
$sitename = SITENAME;
$siteemail = SITEEMAIL;
$willname = (QUICKWILL && $_SESSION['quickwill']) ? 'Premium Will' : 'Online Will';

$query = "INSERT INTO prospects (firstname, surname, emailaddress, created) VALUES (?, ?, ?, NOW());";

$dbh = dbConnect();
$sth = $dbh->prepare($query);
$sth->bindParam(1, $firstname, PDO::PARAM_STR);
$sth->bindParam(2, $surname, PDO::PARAM_STR);
$sth->bindParam(3, $emailaddress, PDO::PARAM_STR);
$sth->execute();

# send email to requestor
$message = "
<div>
<p>Hi $firstname,</p>
  
<p>Your basic will is attached to this email.</p>

<p>If the basic will doesn't meet your needs, then please try our $willname.</p>

<p>Our $willname includes:</p>
<ul>
  <li>Free updates for life</li>
  <li>Executors and alternate executors</li>
  <li>Free Enduring Power of Attorney and Guardianship</li>
  <li>Unlimited and flexible beneficiaries</li>
  <li>Guardians for young children</li>
  <li>Executors memo</li>
  <li>Provision for your pets in your will</li>
  <li>Telephone support</li>
  <li>Provision for your social media details</li>
  <li>Personal messages to beneficiaries</li>
  <li>Assets and Liabilities</li>
  <li>Optional Mirror Will for your partner</li>
</ul>
  
<p>Kind regards,</p>
  
<p><b><em>The Team at $sitename</em></b>
</div>
";

sendEmailAttachment($emailaddress, 'Your Will', $message, '../templates/FreeWillForm.pdf', 'BasicWill.pdf');
$adminemail = "$sitename <$siteemail>";
$subject = "$firstname $surname requested a free will";
$body = "Free Will request:\n$firstname $surname\n$emailaddress\n";
adminEmail($adminemail, $subject, $body);

# send web response back to submitter
echo 'success';
?>