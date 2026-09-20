<?php

/*

Read the orderData from orders into $_SESSION['orderData']

*/
require_once('functions.php');

session_start();

if (!isset($_SESSION['userid']) || !($_SESSION['userid'] > 0)) {
  $data['result'] = "SESSIONENDED";
} else {
  $orderID = $_GET['orderid'] > '' ? $_GET['orderid'] : $orderid;
  require_once('functions.php');
  $dbh = dbConnect();
  $query = "SELECT o.extaxamount, o.discountamount, o.paid, o.orderdata, o.orderdata_nonce, o.dek_wrapped, o.dek_nonce, AES_DECRYPT(o.orderdata,'{$_SESSION['affiliatecode']}') AS legacy_orderdata, o.productid, p.shortname, p.name, p.template FROM orders o JOIN product p ON (p.id = o.productid) WHERE o.id = $orderID;";
  $result = $dbh->query($query);
  if ($result) {
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $_SESSION['orderData'] = resolveOrderData($row, $dbh, ['orderid' => $orderID, 'ownerid' => $_SESSION['userid']]);
    $_SESSION['orderpaid'] = $row['paid'] == 1 ? 1 : 0;
    $_SESSION['orderextaxamount'] = $row['extaxamount'];
    $_SESSION['orderdiscountamount'] = $row['discountamount'];
    $_SESSION['productid'] = $row['productid'];
    $_SESSION['product'] = $row['shortname'];
    $_SESSION['productname'] = $row['name'];
    $_SESSION['template'] = $row['template'];
    $_SESSION['orderid'] = $orderID;
    $data['result'] = "OK";
  } else {
    error_log("There was a failure in the readorderdata SQL query:");
    error_log($query);
    // the query failed so no data  
    $data['result'] = "ERROR";
  }
  $dbh = null;
}
echo json_encode($data);


