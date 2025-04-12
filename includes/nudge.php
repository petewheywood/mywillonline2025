<?php
session_start();
require_once('functions.php');  
$dbh = dbConnect();
require_once("sessioninit.php"); # need $_SESSION vars set - driven by $serverName

$introcode = $_GET['introcode'];
$token = $_POST["Token"];
if(is_null($token)) {
	$token = $_GET["token"];
}
$auth = base64_encode(POLIMERCHANTCODE . ':' . POLIAUTHCODE);
$header = array();
$header[] = 'Authorization: Basic '.$auth;

$ch = curl_init("https://poliapi.apac.paywithpoli.com/api/v2/Transaction/GetTransaction?token=".urlencode($token));
curl_setopt( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_POST, 0);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec( $ch );
curl_close ($ch);

$jsonResponse = json_decode($response, true);

$transactionRefNo = $jsonResponse['TransactionRefNo'];
$transactionStatus = $jsonResponse['TransactionStatusCode'];
$amountPaid = $jsonResponse['AmountPaid'];
$bankRecipt = $jsonResponse['BankReceipt'];
$paidDate = $jsonResponse['BankReceiptDateTime'];
$invoice = $jsonResponse['MerchantReference'];
$invoiceparts = explode("-", $invoice);
$orderid = $invoiceparts[0];

if ($transactionStatus == 'Completed') {
  # get orderData, etc
  $result = $dbh->query("SELECT extaxamount, taxamount, discountamount, invoice, paid FROM orders WHERE id = $orderid;");
  $row = $result->fetch(PDO::FETCH_ASSOC);
  $extaxamount = $row['extaxamount'];
  $taxamount = $row['taxamount'];
  $discamount = $row['discountamount'];
  $invoice = $row['invoice'];
  $orderpaid = $row['paid'];
  
  if (!$orderpaid) {
    $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'poli', paymentattempts = paymentattempts + 1, paidtxnid = '$transactionRefNo', reason = 'POLI - $transactionStatus' WHERE id = $orderid");
    $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'poli', paymentattempts = paymentattempts + 1, paidtxnid = '$transactionRefNo', reason = 'POLI - $transactionStatus' WHERE mirrorof = $orderid");
    $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$transactionRefNo', 'POLI - $transactionStatus', '$invoice', 'poli')");
    insertOrderCommission($orderid, $dbh);
    sendOrderEmail($orderid, 'poli', $introcode);
  }
  $dbh = null;
}

// output log
$date = date("D M j G:i:s T Y", time());
$message = "\n[$date] The following data was received from POLi by " . __FILE__ . ":\n";
$fh = fopen('poli.log','a');
fwrite($fh, $message . print_r($jsonResponse, true));
fclose($fh);

?>