<?php

/*

This will either update order as paid directly if $0 or take CC details and connect to payment gateway via POST, wait for response and display either a thank you page or an error page.

*/
error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
if (!$_SESSION['userid']) header("Location: /"); # only logged on users
chdir("..");
require_once('includes/functions.php');

# get the order data
$orderid = $_SESSION['orderid'];
$priceextax = $_SESSION['orderextaxamount'];
$discamount = $_SESSION['orderdiscountamount'];
$extaxtotal = round($priceextax - $discamount,2);
$taxamount = $extaxtotal * TAX;
$inctaxtotal = $extaxtotal + $taxamount;
$orderamount = round(round($inctaxtotal,2) * 100, 0); // amount in cents
$taxrate = round(TAX * 100,2);
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
    # this is a COMMWEB transaction so go to payment gateway
  
    # get the POST vars from the form
    $_SESSION['ccnumber'] = $ccnumber = preg_replace('/ /', '', $_POST['ccnumber']);
    $_SESSION['ccexpiry'] = $_POST['ccexpiry'];
    $ccexpiry = substr($_SESSION['ccexpiry'], 3, 2) . substr($_SESSION['ccexpiry'], 0, 2); // commweb needs YYMM
    $_SESSION['ccv2'] = $ccv2 = $_POST['ccv2'];
    $ccname = $_POST['ccname'];
    
    # merchant data
    $accesscode = CBA_ACCESSCODE;
    $merchantid = CBA_MERCHANTID;
    $gatewayurl = CBA_URL;
    
    # set the vars for the POST to gateway
    $data = array(
      'vpc_Version' => '1',
      'vpc_Command' => 'pay',
      'vpc_MerchTxnRef' => $invoice,
      'vpc_AccessCode' => $accesscode,
      'vpc_Merchant' => $merchantid,
      'vpc_OrderInfo' => $orderid,
      'vpc_Amount' => $orderamount,
      'vpc_CardNum' => $ccnumber,
      'vpc_CardExp' => $ccexpiry,
      'vpc_CardSecurityCode' => $ccv2
    );
    
    # send the POST
    $options = array(
      'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
      )
    );
    $context  = stream_context_create($options);
    $response = file_get_contents($gatewayurl, false, $context);
    
    # get response from POST
    $map = array();
    $pairArray = explode("&", $response);
    foreach ($pairArray as $pair) {
      $param = explode("=", $pair);
      $map[urldecode($param[0])] = urldecode($param[1]);
    }
    
    $merchTxnRef     = $vpc_MerchTxnRef; # merchTxnRef not always returned in response if no receipt so get input
    $amount          = null2unknown($map, "vpc_Amount");
    $locale          = null2unknown($map, "vpc_Locale");
    $batchNo         = null2unknown($map, "vpc_BatchNo");
    $command         = null2unknown($map, "vpc_Command");
    $version         = null2unknown($map, "vpc_Version");
    $cardType        = null2unknown($map, "vpc_Card");
    $orderInfo       = null2unknown($map, "vpc_OrderInfo");
    $receiptNo       = null2unknown($map, "vpc_ReceiptNo");
    $merchantID      = null2unknown($map, "vpc_Merchant");
    $authorizeID     = null2unknown($map, "vpc_AuthorizeId");
    $transactionNr   = null2unknown($map, "vpc_TransactionNo");
    $acqResponseCode = null2unknown($map, "vpc_AcqResponseCode");
    $txnResponseCode = null2unknown($map, "vpc_TxnResponseCode");
    $txnResponseDesc = getResponseDescription($txnResponseCode);
    
    // CSC Receipt Data
    $cscResultCode   = null2unknown($map, "vpc_CSCResultCode");
    $cscACQRespCode  = null2unknown($map, "vpc_AcqCSCRespCode");
    $cscDesc         = displayCSCResponse($cscResultCode);
    
    //error_log(print_r($map, true));
    
    # update the order as paid or not and redirect to appropriate page
    if ($txnResponseCode == '0') {
      $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'commweb', paymentattempts = paymentattempts + 1, paidtxnid = '$receiptNo', reason = 'COMMWEB - Successful Payment' WHERE id = $orderid");
      $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'commweb', paymentattempts = paymentattempts + 1, paidtxnid = '$receiptNo', reason = 'COMMWEB - Successful Payment' WHERE mirrorof = $orderid");
      $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$priceextax', '$taxamount', '$discamount', NOW(), '$receiptNo', 'COMMWEB - Successful Payment', '$invoice', 'commweb')");
      insertOrderCommission($orderid, $dbh);
      sendOrderEmail($orderid, 'commweb', $introcode);
      header("Location: /thankyou.html");
    } else {
      $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$priceextax', '$taxamount', '$discamount', NOW(), '$receiptNo', 'COMMWEB - $txnResponseDesc', '$invoice', 'commweb')");
      $_SESSION['ccfail'] = "Your payment request was unsuccessful - $txnResponseDesc";
      header("Location: /order.html");
    }
        
  } else {
    # this is a POLi transaction
    $json_builder = '{
      "Amount":"' . round($inctaxtotal,2) . '",
      "CurrencyCode":"AUD",
      "MerchantReference":"' . $invoice . '",
      "MerchantHomepageURL":"' . SITEURL . '",
      "SuccessURL":"' . $successurl . '?item_number=' . $orderid . '&paymentgw=poli",
      "FailureURL":"' . $failureurl . '",
      "CancellationURL":"' . $cancelurl . '",
      "NotificationURL":"' . $notificationurl . '?introcode=' . $introcode . '" 
    }';
    
    $auth1 = POLIMERCHANTCODE . ':' . POLIAUTHCODE;
    $auth = base64_encode(POLIMERCHANTCODE . ':' . POLIAUTHCODE);
    $header = array();
    $header[] = 'Content-Type: application/json';
    $header[] = 'Content-length: ' . strlen($json_builder);
    $header[] = 'Authorization: Basic '.$auth;
    
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, "https://poliapi.apac.paywithpoli.com/api/v2/Transaction/Initiate");
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_builder);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch); 
    
    $jsonResponse = json_decode($response, true);
    
    $success = $jsonResponse['Success'];
    $errorCode = $jsonResponse['ErrorCode'];
    $errorMsg = $jsonResponse['ErrorMessage'];
    $transactionRefNo = $jsonResponse['TransactionRefNo'];
    $navigateURL = $jsonResponse['NavigateURL'];
    
    if ($success) {
      header("Location: $navigateURL");
    } else {
      $_SESSION['polifail'] = $errorCode . " - " . $errorMsg;
      header("Location: /order.html");
    }
  }
}

$dbh = null;

?>      
