<?php

/*

This will either update order as paid directly if $0 or take CC details and connect to payment gateway via POST, wait for response and display either a thank you page or an error page.

*/
error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
if (!$_SESSION['userid'])
  header("Location: /"); # only logged on users
chdir("..");
require_once('includes/functions.php');

# get the order data
$orderid = $_SESSION['orderid'];
$priceextax = $_SESSION['orderextaxamount'];
$discamount = $_SESSION['orderdiscountamount'];
$extaxtotal = round($priceextax - $discamount, 2);
$taxamount = $extaxtotal * TAX;
$inctaxtotal = $extaxtotal + $taxamount;
$orderamount = round($inctaxtotal, 2);
$taxrate = round(TAX * 100, 2);
$productname = $_SESSION['productname'];
$invoice = $_SESSION['invoice'];

$successurl = url_origin() . '/thankyou.html';
$failureurl = url_origin() . '/order.html';
$cancelurl = url_origin() . '/mydocs.html';
$notificationurl = url_origin() . '/includes/nudge.php';

$introcode = $_SESSION['introcode']; // may be empty

$paytype = $_SESSION['paytype'] = $_POST['paytype'];

$dbh = dbConnect();

if ($inctaxtotal == 0 || $_SESSION['admin'] == 1) {
  //if ($inctaxtotal == 0) {
  # this is an admin transaction or $0 transaction
  # update the order as paid and go to thank you page
  $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'internal', paymentattempts = paymentattempts + 1, paidtxnid = 'internal' WHERE id = $orderid");
  $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'internal', paymentattempts = paymentattempts + 1, paidtxnid = 'internal' WHERE mirrorof = $orderid");
  $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$priceextax', '$taxamount', '$discamount', NOW(), 'internal', '', '$invoice', 'internal')");
  # insertOrderCommission($orderid, $dbh);
  sendOrderEmail($orderid, 'free', $introcode);
  header("Location: /thankyou.html");
} else {
  if ($paytype == 'commweb') {
    updateCBASession($_SESSION['sessionId'], $orderamount);
    $response = doCBAPayment($invoice, $invoice, $_SESSION['sessionId']);
    // echo "<pre>" . print_r($response, true) . "</pre>";

    # get receipt number and result from response array
    $receiptNo = isset($response['transaction']['receipt']) ? $response['transaction']['receipt'] : '';
    $result = isset($response['result']) ? $response['result'] : '';
    $reason = '';
    # check for error or gateway code for reason
    if ($result == 'FAILURE') {
      $reason = isset($response['response']['gatewayCode']) ? $response['response']['gatewayCode'] : substr($response['error']['explanation'], 0, 100);
    } else if ($result == 'ERROR') {
      $reason = isset($response['error']['explanation']) ? substr($response['error']['explanation'], 0, 100) : 'Unknown error';
    }

    // echo the result for debugging
    // echo "Result: " . htmlspecialchars($result) . "<br>";
    // echo "Receipt No: " . htmlspecialchars($receiptNo) . "<br>";

    # update the order as paid or not and redirect to appropriate page
    if ($result == 'SUCCESS') {
      $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'commweb', paymentattempts = paymentattempts + 1, paidtxnid = '$receiptNo', reason = 'COMMWEB - Successful Payment' WHERE id = $orderid");
      $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'commweb', paymentattempts = paymentattempts + 1, paidtxnid = '$receiptNo', reason = 'COMMWEB - Successful Payment' WHERE mirrorof = $orderid");
      $stmt = $dbh->prepare("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?)");
      $stmt->execute([$orderid, $priceextax, $taxamount, $discamount, $receiptNo, "COMMWEB - $result", $invoice, 'commweb']);
      insertOrderCommission($orderid, $dbh);
      sendOrderEmail($orderid, 'commweb', $introcode);
      header("Location: /thankyou.html");
    } else {
      $stmt = $dbh->prepare("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?)");
      $stmt->execute([$orderid, $priceextax, $taxamount, $discamount, '', "COMMWEB - $reason", $invoice, 'commweb']);
      $_SESSION['ccfail'] = "Your payment request was unsuccessful - $reason";
      header("Location: /order.html");
    }

  }
}

$dbh = null;

?>