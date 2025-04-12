<?php
session_start();
require_once("functions.php");

# name, email, phone, message and if logged on antibot sum come in as a POST var
$email = $_POST['contactEmail'];
$name = $_POST['contactName'];
$themessage = stripslashes($_POST['contactMessage']);
$phone = $_POST['contactPhone'];
if ($phone > '') {
  $themessage .= "\n\n$name\nPhone: $phone\n";
} else {
  $themessage .= "\n\n$name\n";
}

# send email to sender and a bcc to admin
$sitename = $_SESSION['sitename'];
$website = url_origin();
$subject = "Thank you for your enquiry to $sitename";
$htmlmessage = str_replace("\n", "<br />\n", $themessage);
$message = "
<div>
<p>Hi $name,</p>
  
<p>Thanks for your enquiry. We will be in touch with you shortly.</p>
  
<p>Kind regards,</p>
  
<p><b><em>The Team at $sitename</em></b>
</div>
<div> 
<p><b>Your message:</b></p>
<p>$htmlmessage</p>
</div>
";
sendEmail($email, $subject, $message);

# send an email to admin
adminEmail("$name <$email>", "Contact Message", $themessage);

# send web response back to submitter
echo "Thank you. Your message has been sent.";

?>