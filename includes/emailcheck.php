<?php

# check for an email address already in use - called by yourdetails when validating the email field
require_once('functions.php'); 

# email comes in as a POST var
$email = $_POST['email'];
list($name,$domain) = preg_split("/@/", $email);

$dbh = dbConnect();
$result = $dbh->query("SELECT COUNT(*) FROM users WHERE emailaddress = '" . $email . "';");
if ($result->fetchColumn() > 0) {
  echo 'false';
} else {
  echo 'true';
}

?>