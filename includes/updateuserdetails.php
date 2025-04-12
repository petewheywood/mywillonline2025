<?php

/* 

This is called by AJAX from singlewill.html when the user blurs out of the person postcode field

It will update the USER record with address details if the $_SESSION['postcode'] var is empty. This
means that it is the first time a user has created a will, so if the user firstname and surname
are the same as the person firstname and surname, we will update the USER table row. Otherwise
we just return

*/

session_start();

if (isset($_SESSION['postcode']) && strlen($_SESSION['postcode']) > 0) exit; // no need to update because already done

require_once('functions.php'); 

# get the person address, phone and dob details

$firstname = $_REQUEST['firstname'];
$surname = $_REQUEST['surname'];

/*
# check to make sure the person name is actually the user name
if ($firstname != $_SESSION['firstname'] || $surname != $_SESSION['surname']) {
	echo "person not user";
	exit;
}
*/

# get the details and set session vars
$othername = $_SESSION['othername'] = $_REQUEST['othername'];
$address1 = $_SESSION['address1'] = $_REQUEST['address1'];
$address2 = $_SESSION['address2'] = $_REQUEST['address2'];
$city = $_SESSION['city'] = $_REQUEST['city'];
$state = $_SESSION['state'] = $_REQUEST['state'];
$postcode = $_SESSION['postcode'] = $_REQUEST['postcode'];
$phone = $_SESSION['phone'] = $_REQUEST['phone'];
$dob = $_SESSION['dob'] = $_REQUEST['dob'];

# insert query
$updateQuery = "UPDATE users SET othername = ?, address1 = ?, address2 = ?, city = ?, state = ?, postcode = ?, phone = ?, dateofbirth = ? WHERE id = " . $_SESSION['userid'] . ";";
$dbh = dbConnect();
$sth = $dbh->prepare($updateQuery);
$sth->bindParam(1, $othername, PDO::PARAM_STR);
$sth->bindParam(2, $address1, PDO::PARAM_STR);
$sth->bindParam(3, $address2, PDO::PARAM_STR);
$sth->bindParam(4, $city, PDO::PARAM_STR);
$sth->bindParam(5, $state, PDO::PARAM_STR);
$sth->bindParam(6, $postcode, PDO::PARAM_STR);
$sth->bindParam(7, $phone, PDO::PARAM_STR);
$sth->bindParam(8, isoDate($dob), PDO::PARAM_STR);
$sth->execute();
$dbh = null;
echo "success";
?>