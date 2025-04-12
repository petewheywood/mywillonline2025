<?php
session_start();
$sessionID = session_id();
require_once('functions.php'); 

$dbh = dbConnect();

# get the vars passed through from register.html
$firstname = $_SESSION['firstname'] = rtrim($_POST['firstname']);
$surname = $_SESSION['surname'] = rtrim($_POST['surname']);
$email = $_SESSION['email'] = $_POST['email'];
$password = $_SESSION['password'] = $_POST['password'];
$coupon = $_SESSION['introcode'] = $_POST['coupon'];
$fbid = $_SESSION['fbid'] = $_POST['fbid'];

$_SESSION['affiliatediscount'] = $_SESSION['discount'];
$discount = DISCOUNT;

# insert query
$insertQuery = "
  INSERT INTO users (firstname, surname, emailaddress, password, created, introcode, discount, fbid) 
  VALUES (?, ?, ?, MD5('$password'), NOW(), '$coupon', $discount, ?);
";

$sth = $dbh->prepare($insertQuery);
$sth->bindParam(1, $firstname, PDO::PARAM_STR);
$sth->bindParam(2, $surname, PDO::PARAM_STR);
$sth->bindParam(3, $email, PDO::PARAM_STR);
$sth->bindParam(4, $fbid, PDO::PARAM_STR);
$sth->execute();

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


# add userid to visits table session record
$dbh->exec("
    UPDATE visits SET userid = '$userid', logindate = NOW() WHERE sessionid = '$sessionID';
");
$_SESSION['userid'] = $userid;
if ($userid > 0) {
  $_SESSION['loggedin'] = true;
  
  //$_SESSION['quickwill'] = false; # turn off quickwill for all logged in users

  # send an email
  sendTrackedEmail($userid, $affcode, $email, $firstname, $surname, "register");
  # let admin know
  $sitename = SITENAME;
  $siteemail = SITEEMAIL;
  $adminemail = "$sitename <$siteemail>";
  $subject = "$firstname $surname registered at MWO";
  $body = "New user registration:\n$firstname $surname\n$email\n";
  
  # if introcode then get discount
  if ($_SESSION['introcode'] > '') {
    # get user who introduced them
    $results = $dbh->query("SELECT u.id, u.firstname, u.surname, u.discount, u.emailaddress, o.name, u.flags FROM users u LEFT JOIN organisations o ON (o.userid = u.id) WHERE affiliatecode = '" . $_SESSION['introcode'] . "';");
    $row = $results->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $_SESSION['affiliateName'] = $row['name'] > '' ? $row['name'] : $row['firstname'] . ' ' . $row['surname'];
      $body .= "Referred by " . $_SESSION['affiliateName'] . "\n";
      $_SESSION['discount'] = is_null($row['discount']) ? $_SESSION['discount'] : $row['discount'];

      # if an organisation referred them, send an email to referrer
      if ($row['name'] > '') {
        sendEmail($row['emailaddress'], "My Will Online referral user registration", "$firstname $surname ($email) was referred from ${row['name']} and registered at My Will Online.");
      }
    }   
  }
  
  adminEmail($adminemail, $subject, $body);
}  

# now we are registered and logged on redirect to mydocs page
header("Location: /mydocs.html"); 
?>