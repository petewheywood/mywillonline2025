<?php
session_start();
# check for a valid coupon code and update the users record introcode field and redisplay order.html
require_once('functions.php'); 

# coupon code comes in as a POST var
$couponCode = $_POST['couponCode'];

# check to make sure user has not entered their own code or a downstream code
if (code_is_valid($_SESSION['affiliatecode'], $couponCode)) {
  $dbh = dbConnect();
  $result = $dbh->query("SELECT u.id, u.firstname, u.surname, u.discount, o.name, u.flags FROM users u LEFT JOIN organisations o ON (o.userid = u.id) WHERE affiliatecode = '" . $couponCode . "';");
  $row = $result->fetch(PDO::FETCH_ASSOC);
  if ($row['id'] > 0) {
    # valid code some update the users record with introcode and set the session var introcode
    session_start();
    $dbh->exec("UPDATE orders SET discountamount = extaxamount * " . $row['discount'] . " WHERE userid = " . $_SESSION['userid'] . ";");
    $_SESSION['introcode'] = $couponCode;
    $_SESSION['discount'] = $row['discount'] > DISCOUNT ? $row['discount'] : DISCOUNT;
    $_SESSION['orderdiscountamount'] = round($_SESSION['orderextaxamount'] * $_SESSION['discount'], 2);
    $_SESSION['affiliateName'] = $row['name'] > '' ? $row['name'] : $row['firstname'] . ' ' . $row['surname'];
    
    $isSaleCoupon = check_bit($row['flags'], 3); //check if SALE code bit is set
    if (!$isSaleCoupon) {
      # only update the introcode in the users record if it is an affiliate code - not a SALE code, then it will only apply for the session and not muck up affiliate chain
      $dbh->exec("UPDATE users SET introcode = '$couponCode' WHERE id = " . $_SESSION['userid'] . ";");
    }
    echo 'true';
  } else {
    echo 'false';
  } 
} else {
  echo 'false';
}
?>