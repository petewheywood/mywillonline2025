<?php

/*

Check for order paid or expired status - called from wt.js via AJAX
- expect JSON data back

*/

require_once('functions.php');

session_start();

# get userid - if not there, then maybe session has expired so go to home page
if (!isset($_SESSION['userid']) || !($_SESSION['userid'] > 0)) {
  $data['result'] = "SESSIONENDED";
} else {
  # get the orderdata for the user/order
  $orderID = $_GET['orderid'];
  $dbh = dbConnect();
  $query = "SELECT o.extaxamount, o.discountamount, o.paid, o.reason, o.paiddate, CONCAT(DATE_ADD(DATE(o.paiddate), INTERVAL " . PRODUCTEXPIRY . " MONTH), ' 23:59:59') AS expirydate, AES_DECRYPT(o.orderdata,'{$_SESSION['affiliatecode']}') AS orderdata, o.productid, o.mirrorof, p.shortname, p.name, p.template FROM orders o JOIN product p ON (p.id = o.productid) WHERE o.id = ";
  $result = $dbh->query($query . $orderID);
  $row = $result->fetch(PDO::FETCH_ASSOC);
  $now = time();
  /* check if unexpired order first if expired close this window and send parent window to order page with message */
  if (isset($row)) {
    # there is an order
    $paid = $row['paid']; # 1 or 0
    $reason = $row['reason'];
    $paiddate = $row['paiddate']; # datetime when paid
    $expirydate = $row['expirydate']; # datetime when paid
    # check for paid order
    if ($paid == 0) {
      $data['result'] =  "UNPAID";
    } else {
      # this is an already PAID order
      # check whether the paiddate date is outside the expiry period
      $expirydate = strtotime($expirydate);
      if ($now > $expirydate) {
        # expired order, so check if this is the mirror or the primary order
        if ($row['mirrorof'] > '' && $orderID > $row['mirrorof']) {
          # this is the mirror so we need to get the details of the primary order - rerun query
          $orderID = $row['mirrorof'];
          $result = $dbh->query($query . $orderID);
          $row = $result->fetch(PDO::FETCH_ASSOC);
        }
        $data['result'] = "EXPIRED";
        //$data['paiddate'] = urlencode(strtotime($paiddate));
      } else {
        $data['result'] = "PAID";
      }
    }
    # load orderdata
    $_SESSION['orderData'] = unserialize(stripslashes($row['orderdata']));
    $_SESSION['orderid'] = $orderID;
    $_SESSION['orderpaid'] = $row['paid'] == 1 ? true : false;
    $_SESSION['orderextaxamount'] = $row['extaxamount'];
    $_SESSION['orderdiscountamount'] = $row['discountamount'];
    $_SESSION['productid'] = $row['productid'];
    $_SESSION['product'] = $row['shortname'];
    $_SESSION['productname'] = $row['name'];
    $_SESSION['template'] = $row['template'];
    $_SESSION['orderid'] = $orderID;
    //$data['orderID'] = $orderID;
  } else {
    $data['result'] = "NOORDER";
  }
  $dbh = null;
}
echo json_encode($data);
