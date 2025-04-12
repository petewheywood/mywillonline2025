<?php
session_start();

require_once('functions.php'); 

unset($_SESSION['orderData']);
# store the POSTed details from form page into $_SESSION['orderData'] array for logged in users
if (isset($_SESSION['userid']) && isset($_POST['formID'])) {
  foreach ($_POST as $key => $value) {
    # and now update it with new POST var values after stripslashes and removing empty vars/arrays
    $value = processPostVars($value);
    if (isset($value) and $value != '') $_SESSION['orderData'][$key] = $value;
  }
  if (count($_SESSION['orderData']) > 5) {
    // only save data if there is data there, otherwise what was there will be wiped out - this should prevent the lost orderdata problem that sometimes happens with session expiry or form resets.
    updateOrderData();
  }
  //error_log("Updated order data for orderid " . $_SESSION['orderid']);
  echo 'success';
} else {
  # go to home page if directly invoked
  //header("Location: {$_SESSION['landingpage']}");
  //error_log("Did not update order data for orderid " . $_SESSION['orderid']);
  echo 'failure'; // this will cause a return to the homepage
}
?>