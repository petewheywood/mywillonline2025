<?php
/* called from the updateprofile.html page via AJAX */

session_start();
if (!isset($_SESSION['updatingEmailPW']) or ($_SESSION['updatingEmailPW'] != $_SESSION['userid'])) header("Location: /");
unset($_SESSION['updatingEmailPW']);
require_once('functions.php'); 

# email and password come in as a POST var

$responseText = 'User details updated.<br>';

# get the vars passed through from updateemail.html

$firstname = $_SESSION['firstname'] = $_POST['firstname'];
$surname = $_SESSION['surname'] = $_POST['surname'];
$address1 = $_SESSION['address1'] = $_POST['address1'];
$address2 = $_SESSION['address2'] = $_POST['address2'];
$city = $_SESSION['city'] = $_POST['city'];
$state = $_SESSION['state'] = $_POST['state'];
$postcode = $_SESSION['postcode'] = $_POST['postcode'];
$phone = $_SESSION['phone'] = $_POST['phone'];

$updateEmail = '';
$updatePassword = '';

if ($_POST['email'] > '') {
  $email = $_SESSION['email'] = $_POST['email'];
  $responseText .= "Email address successfully changed.<br>";
  $updateEmail = "emailaddress = '$email',";
}

# only change the password if a new one was sent from the updateemail.html page
if ($_POST['password'] > '') {
  $password = $_SESSION['password'] = $_POST['password'];
  $responseText .= "Password successfully changed.<br>";
  $updatePassword = "password = MD5('$password'),";
}

# insert query
$dbh = dbConnect();
$updateQuery = "UPDATE users SET $updateEmail $updatePassword firstname = ?, surname = ?, address1 = ?, address2 = ?, city = ?, state = ?, postcode = ?, phone = ? WHERE id = " . $_SESSION['userid'] . ";";
$sth = $dbh->prepare($updateQuery);
$sth->bindParam(1, $firstname, PDO::PARAM_STR);
$sth->bindParam(2, $surname, PDO::PARAM_STR);
$sth->bindParam(3, $address1, PDO::PARAM_STR);
$sth->bindParam(4, $address2, PDO::PARAM_STR);
$sth->bindParam(5, $city, PDO::PARAM_STR);
$sth->bindParam(6, $state, PDO::PARAM_STR);
$sth->bindParam(7, $postcode, PDO::PARAM_STR);
$sth->bindParam(8, $phone, PDO::PARAM_STR);
$sth->execute();

# update organisation details if relevant
if ($_SESSION['organisation']) {
  $name = $_SESSION['orgname'] = $_POST['name'];
  $abn = $_SESSION['abn'] = $_POST['abn'];
  $weburl = $_SESSION['weburl'] = $_POST['weburl'];
  $description = $_SESSION['description'] = $_POST['description'];
  
  # get updated logo details if specified
  $updateLogo = '';
  if (count($_FILES) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
    $affcode = $_SESSION['affiliatecode'];
    $uploaddir = '../uploads';
    $namebits = explode(".", $_FILES['logo']['name']);
    $ext = $namebits[1];
    $_SESSION['logo'] = $uploadfile = "$uploaddir/$affcode.$ext";
    move_uploaded_file($_FILES['logo']['tmp_name'], $uploadfile);
    $updateLogo = "logo = '$uploadfile', ";
  }
  
  # now update organisations table
  $updateQuery = "UPDATE organisations SET $updateLogo name = ?, abn = ?, description = ?, weburl = ? WHERE userid = " . $_SESSION['userid'] . ";";
  
  $sth = $dbh->prepare($updateQuery);
  $sth->bindParam(1, $name, PDO::PARAM_STR);
  $sth->bindParam(2, $abn, PDO::PARAM_STR);
  $sth->bindParam(3, $description, PDO::PARAM_STR);
  $sth->bindParam(4, $weburl, PDO::PARAM_STR);
  $sth->execute();
  $responseText .= "Organisation details updated.";
}


$dbh = null;
  
echo $responseText;

?>