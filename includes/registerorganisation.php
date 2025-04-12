<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once('functions.php'); 

$dbh = dbConnect();

# get the vars passed through from register.html
$firstname = $_SESSION['firstname'] = rtrim($_POST['firstname']);
$surname = $_SESSION['surname'] = rtrim($_POST['surname']);
$email = $_SESSION['email'] = $_POST['email'];
$password = $_SESSION['password'] = $_POST['password'];

$orgname = $_SESSION['orgname'] = $_POST['orgname'];
$description = $_SESSION['description'] = $_POST['description'];
$weburl = $_SESSION['weburl'] = $_POST['weburl'];
$address1 = $_SESSION['address1'] = $_POST['address1'];
$address2 = $_SESSION['address2'] = $_POST['address2'];
$city = $_SESSION['city'] = $_POST['city'];
$state = $_SESSION['state'] = $_POST['state'];
$postcode = $_SESSION['postcode'] = $_POST['postcode'];
$country = $_SESSION['country'] = $_POST['country'];

$abn = $_SESSION['abn'] = $_POST['abn'];
$phone = $_SESSION['phone'] = $_POST['phone'];

$autoBeneficiary = $_SESSION['autoBeneficiary'] = $_POST['autoBeneficiary'];

$orgdiscount = ORGDISCOUNT;

# insert user with flag = 4 bit 3 set to indicate organsation.
if ($autoBeneficiary == 'Yes') {
  $orgflag = FLAG_ORGANISATION + FLAG_BENEFICIARY;
} else {
  $orgflag = FLAG_ORGANISATION;
}

$insertQuery = "
  INSERT INTO users (firstname, surname, emailaddress, phone, address1, address2, city, state, postcode, password, created, discount, flags) 
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, MD5('$password'), NOW(), $orgdiscount, $orgflag);
";
$sth = $dbh->prepare($insertQuery);
$sth->bindParam(1, $firstname, PDO::PARAM_STR);
$sth->bindParam(2, $surname, PDO::PARAM_STR);
$sth->bindParam(3, $email, PDO::PARAM_STR);
$sth->bindParam(4, $phone, PDO::PARAM_STR);
$sth->bindParam(5, $address1, PDO::PARAM_STR);
$sth->bindParam(6, $address2, PDO::PARAM_STR);
$sth->bindParam(7, $city, PDO::PARAM_STR);
$sth->bindParam(8, $state, PDO::PARAM_STR);
$sth->bindParam(9, $postcode, PDO::PARAM_STR);

if (!$sth->execute()) {
    error_log(print_r($sth->errorInfo(),true));
    exit;
}

$userid = $dbh->lastInsertID();

# generate a unique affiliatecode for the new user
do {
  $affcode = affiliateCode($userid);
  $result = $dbh->query("SELECT COUNT(*) FROM users WHERE affiliatecode = '$affcode';");
} while ($result->fetchColumn() > 0);
$_SESSION['affiliatecode'] = $affcode;
$_SESSION['affiliateURL'] = url_origin() . "?afid=" . $_SESSION['affiliatecode'];
$_SESSION['shortURL'] = shortenUrl($_SESSION['affiliateURL']);
$dbh->exec("UPDATE users SET affiliatecode = '$affcode' WHERE id = $userid;");

// move file and get contents
if ($_FILES['logo']['error'] == UPLOAD_ERR_OK) {
  $uploaddir = '../uploads';
  $namebits = explode(".", $_FILES['logo']['name']);
  $ext = $namebits[1];
  $_SESSION['logo'] = $uploadfile = "$uploaddir/$affcode.$ext";
  move_uploaded_file($_FILES['logo']['tmp_name'], $uploadfile);
}

# add in organisation table details linked to user table
$insertQuery = "
  INSERT INTO organisations (userid, name, description, abn, logo, weburl) 
  VALUES (?, ?, ?, ?, ?, ?);
";


$sth = $dbh->prepare($insertQuery);
$sth->bindParam(1, $userid, PDO::PARAM_INT);
$sth->bindParam(2, $orgname, PDO::PARAM_STR);
$sth->bindParam(3, $description, PDO::PARAM_STR);
$sth->bindParam(4, $abn, PDO::PARAM_STR);
$sth->bindParam(5, $uploadfile, PDO::PARAM_STR);
$sth->bindParam(6, $weburl, PDO::PARAM_STR);
if (!$sth->execute()) {
    error_log(print_r($sth->errorInfo(),true));
    exit;
}

$_SESSION['userid'] = $userid;
$_SESSION['organisation'] = true;
if ($userid > 0) {
  $_SESSION['loggedin'] = true;

  # send an email
  #sendTrackedEmail($userid, $affcode, $email, $firstname, $surname, "register");
  # let admin know
  $sitename = SITENAME;
  $siteemail = SITEEMAIL;
  $adminemail = "$sitename <$siteemail>";
  $subject = "$orgname ($firstname $surname) registered at MWO";
  $body = "New organisation registration:\n$orgname ($firstname $surname)\n$email\n";
  adminEmail($adminemail, $subject, $body);
}  

# now we are registered and logged on redirect to mydocs page
header("Location: /dashboard.html"); 
?>