<?php

/*

Create a new order for the logged on user. Is called by AJAX from the home_user.php page

Created: 16/5/2013
Author: Pete Heywood

*/
require_once('functions.php');

session_start();

if (!isset($_SESSION['userid']) || !($_SESSION['userid'] > 0)) {
  echo 'SESSIONEXPIRED';
} else {
  $dbh = dbConnect();
  $userid = $_GET['userid'];
  # product being ordered
  $product = $_GET['product'];
  
  # query product table for id and form to use
  $result = $dbh->query("SELECT id, shortname FROM product WHERE shortname = '$product'");
  if ($result) {
    $row = $result->fetch();
    $productid = $row[0];
    $formname = $row[1];
    # insert order row
    $query = "INSERT INTO orders (createdate, userid, productid) VALUES (NOW(), $userid, $productid);";
    $result = $dbh->exec($query);
    $newOrderID = $dbh->lastInsertId();
    if ($newOrderID > 0) {
      $data = array('id' => $newOrderID, 'formname' => $formname);
      echo json_encode($data);
    } else {
      echo 'FAIL';
    }
  } else {
    echo 'FAIL';
  }
}
?>
