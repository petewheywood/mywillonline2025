<?php
session_start();
$sessionid = session_id();
require_once('functions.php'); 

unset($_SESSION['qwOrderData']);
# store the POSTed details from form page into $_SESSION['qwOrderData'] array for logged in users
if (isset($_POST['formID'])) {
  foreach ($_POST as $key => $value) {
    # and now update it with new POST var values after stripslashes and removing empty vars/arrays
    $value = processPostVars($value);
    if (isset($value) and $value != '') $_SESSION['qwOrderData'][$key] = $value;
  }
  # save of will data
  if ($_SESSION['qwOrderData']['testatorFullName'] > '') {
    $fullname = addslashes($_SESSION['qwOrderData']['testatorFullName']);
    if ($_SESSION['admin']) {
      $_SESSION['qwOrderData']['quickwillfor'] = $fullname;
    }
    $quickwillfor = addslashes($_SESSION['qwOrderData']['quickwillfor']);
    $email = $_SESSION['qwOrderData']['testatorEmail'];
    $phone = $_SESSION['qwOrderData']['testatorPhone'];
    $address = addslashes($_SESSION['qwOrderData']['testatorAddress']);
    $orderID = $_SESSION['qwOrderData']['orderid'];
    $amount = $_SESSION['qwOrderData']['orderamount'];
    $tx = isset($_SESSION['qwOrderData']['txnid']) ? $_SESSION['qwOrderData']['txnid'] : '';
    $tcstatus = isset($_SESSION['qwOrderData']['tcstatus']) ? $_SESSION['qwOrderData']['tcstatus'] : '';

    if ($tcstatus == '' || $fullname == $quickwillfor) {
      # write to DB
      # if this is a new order there will be no orderID, therefore insert an order
      $dbh = dbConnect();
      if (!isset($orderID)) {
        $query = "INSERT INTO quickwillorders (orderdate) VALUES (NOW())";
        $dbh->exec($query);
        $_SESSION['qwOrderData']['orderid'] = $orderID = $dbh->lastInsertId();
      }
  
      # set encrypted order data into cookie and db for subsequent sessions
      $willdata = base64_encode(serialize($_SESSION['qwOrderData']));
      $query = "UPDATE quickwillorders SET fullname = '$fullname', email = '$email', phone = '$phone', address = '$address', orderdata = '$willdata' WHERE id = $orderID;";
      $dbh->exec($query);
      $dbh = null;
    }
  }

  echo $orderID;
} else {
  # go to home page if directly invoked
  echo 'failure'; // this will cause a return to the homepage
}
?>