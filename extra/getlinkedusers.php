<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once("../includes/functions.php");


/* get linked users for a given affiliatecode */


echo "<pre>\n";  

$mycode = $_GET['affiliatecode'];
$couponcode = $_GET['introcode'];
$dbh = dbConnect();
$users = array();
get_linked_users_down($mycode, 1, $users, $dbh);

foreach ($users as $user) {
  echo "Level: ${user['level']}, Email: ${user['emailaddress']}, Affiliatecode: ${user['affiliatecode']}\n";
}

echo $couponcode . " is " . (code_is_valid($mycode, $couponcode) ? 'OK' : 'Invalid') . "\n";
?>