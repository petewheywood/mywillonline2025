<?php
session_start();
$sitename = $_SESSION['sitename'];
$siteemail = $_SESSION['siteemail'];

# generate a unique link so that a user can reset their password

require_once('functions.php'); 

# email comes in as a REQUEST var

if ($_REQUEST['emailaddress'] > '') {
  $email = $_REQUEST['emailaddress'];
  if (checkEmail($email)) {
    $dbh = dbConnect();
    $result = $dbh->query("SELECT id FROM users WHERE emailaddress = '$email';");
    if ($userid = $result->fetchColumn()) {
      # valid email address so generate a unique url that expires in 1 hour 
      $expires = date('Y-m-d H:i:s', strtotime("+1 hour"));
      $key = affiliateCode(); # use the same function we use to generate aff code even though this is not one
      $result = $dbh->exec("INSERT INTO pwreset (userid, uniquekey, expires) VALUES ($userid, '$key', '$expires');");
      $reseturl = url_origin() . "/?pwreset=" . $key;
      
      # send email with reset passwd url
      $subject = "$sitename - Password reset request";
      $message = "
<div>
<p>Hi,</p>

<p>A password reset request was submitted at $sitename for email address $email.</p>

<p>If you did not request this password reset, then please delete this email. Your password will not be changed.</p>

<p>If you did request the password reset, then clicking on the link below will allow you to reset your password.</p>

<p>To continue with resetting your password, please click on this link, or copy and paste it into your web browser - </p>

<p><a href='$reseturl'></a>$reseturl</p>

<p>Kind regards,</p>

<p><b><em>The Team at $sitename</em></b></p>
      ";
			sendEmail($email, $subject, $message);
      echo "success";
    } else {
      echo 'Email address is not registered here';
    }
    $dbh = null;
  } else {
    echo "An invalid email address was entered";
  }
} else {
  echo "Please enter an email address";
}

?>