<?php

/*

This will either update order as paid directly if $0 or take CC details and connect to payment gateway via POST, wait for response and display either a thank you page or an error page.

*/
session_start();
require_once('functions.php');

# get the order data
$qwOrderData = &$_SESSION['qwOrderData']; // get pointer
$orderid = $qwOrderData['orderid'];
$orderamount = $qwOrderData['orderamount'];
$extaxamount = $orderamount/(1 + TAX);
$taxamount = $extaxamount * TAX;
$successurl = url_origin() . '/thankyouqw.html';

$dbh = dbConnect();

if ($orderamount == 0 || $_SESSION['admin'] == 1) {
  # this is an admin transaction or $0 transaction
  # update the order as paid and go to thank you page
  $qwOrderData['tcstatus'] = 'Approved';
  $qwOrderData['txnid'] = 'internal';
  $willdata = base64_encode(serialize($qwOrderData));
  $dbh->exec("UPDATE quickwillorders SET paiddate = NOW(), tx = '$receiptNo', tcstatus = '$message', amount = '$orderamount', extaxamount = '$extaxamount', taxamount = '$taxamount', orderdata = '$willdata' WHERE id = $orderid");
  setcookie('localstorage', $willdata, time()+1800, "/");  
  header("Location: /thankyouqw.html");
} else {
  # this is a COMMWEB transaction so go to payment gateway

  # get the POST vars from the form
  $ccnumber = preg_replace('/ /', '', $_POST['ccnumber']);
  $ccexpiry = substr($_POST['ccexpiry'], 3, 2) . substr($_POST['ccexpiry'], 0, 2);
  $ccv2 = $_POST['ccv2'];
  $ccname = $_POST['ccname'];
  
  # merchant data
  $accesscode = CBA_ACCESSCODE;
  $merchantid = CBA_MERCHANTID;
  $gatewayurl = CBA_URL;
  
  # set the vars for the POST to gateway
  $amountincents = round(round($orderamount,2) * 100, 0); // amount in cents
  $data = array(
    'vpc_Version' => '1',
    'vpc_Command' => 'pay',
    'vpc_MerchTxnRef' => $orderid,
    'vpc_AccessCode' => $accesscode,
    'vpc_Merchant' => $merchantid,
    'vpc_OrderInfo' => $orderid,
    'vpc_Amount' => $amountincents,
    'vpc_CardNum' => $ccnumber,
    'vpc_CardExp' => $ccexpiry,
    'vpc_CardNum' => $ccnumber,
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
  $message         = null2unknown($map, "vpc_Message");
  $merchantID      = null2unknown($map, "vpc_Merchant");
  $authorizeID     = null2unknown($map, "vpc_AuthorizeId");
  $transactionNr   = null2unknown($map, "vpc_TransactionNo");
  $acqResponseCode = null2unknown($map, "vpc_AcqResponseCode");
  $txnResponseCode = null2unknown($map, "vpc_TxnResponseCode");

  //$txnResponseCode = '0';

  $txnResponseDesc = getResponseDescription($txnResponseCode);
  
  // CSC Receipt Data
  $cscResultCode   = null2unknown($map, "vpc_CSCResultCode");
  $cscACQRespCode  = null2unknown($map, "vpc_AcqCSCRespCode");
  $cscDesc         = displayCSCResponse($cscResultCode);
  
  //error_log(print_r($map, true));
  
  # update the order as paid or not and redirect to appropriate page
  if ($txnResponseCode == '0') {
    $qwOrderData['tcstatus'] = $message;
    $qwOrderData['txnid'] = $receiptNo;
    $willdata = base64_encode(serialize($qwOrderData));
    $dbh->exec("UPDATE quickwillorders SET paiddate = NOW(), tx = '$receiptNo', tcstatus = '$message', amount = '$orderamount', extaxamount = '$extaxamount', taxamount = '$taxamount', orderdata = '$willdata' WHERE id = $orderid");
    setcookie('localstorage', $willdata, time()+1800, "/");


    //add email address to feedback email list and orders
    $record = $txnid . "|" . $qwOrderData['testatorFullName'] . "|" . $qwOrderData['testatorEmail'] . "|" . $qwOrderData['testatorState'];
    file_put_contents("../../" . DATADIR . "/feedback.todo", base64_encode($record) . PHP_EOL, FILE_APPEND | LOCK_EX);

    # send receipt to customer
    # get details
    $name = $qwOrderData['testatorFullName'];
    $orderid = $qwOrderData['orderid'];
    $invoice = "QW" . str_pad($orderid, 8, "0", STR_PAD_LEFT);
    $email = DEVSITE ? SITEEMAIL : $qwOrderData['testatorEmail'];
    $serverName = url_origin();
    $orderamount = $qwOrderData['orderamount'];
    $extaxamount = $orderamount/(1 + TAX);
    $taxamount = $extaxamount * TAX;
    $productname = $_SESSION['products']['quickwill']['name'];
    
    # make email
    $sitename = SITENAME;
    $siteemail = SITEEMAIL;
    $adminemail = "$sitename <$siteemail>";
    $siteabn = SITEABN;
    $sitecompany = SITECOMPANY;
    $invdate = date("jS F Y");
    $tax = (TAX != 0) ? 'TAX ' : '';
    	$invoiceline = "
    <h3>${tax}INVOICE</h3>
    
    <p>Invoice number: $invoice</p>
    
    <p>Invoice date: $invdate</p>
    ";
    
    $message = "<div>$invoiceline\n<table><tr><th style='text-align: left; vertical-align: top;'>Product</th> <th style='text-align: right; vertical-align: top;'>Price</th></tr>\n";
    $message .= "<tr><td style='text-align: left; vertical-align: top;padding-right: 15px;'>1 x $productname for $name</td> <td style='text-align: right;vertical-align: top;'>" . currency($orderamount) . "</td></tr>\n";
    $message .= "<tr><td style='text-align: left; border-top: 1px solid #ddd; vertical-align: top;padding-right: 15px;'><b>Total</b></td> <td style='text-align: right; border-top: 1px solid #ddd; vertical-align: top;'>" . currency($orderamount) . "</td></tr>\n";
    if (TAX != 0) {
    	$message .= "<tr><td style='text-align: left; vertical-align: top;padding-right: 15px;padding-top: 50px;'><em>Total includes " . TAXNAME . " of " . currency($taxamount) . " (" . SITECOMPANY . " - ABN: " . SITEABN . ")</em></td></tr>\n";
    }
    $message .= "</table></div>\n";
    # send the email
    sendEmail($email, "Thank you for your QuickWill Order", $message);
    
    # send order received email to admin
    $body = "$name - $email - ordered a $productname\nOrderID: $orderid\nAmount: " . currency($orderamount);
    $subject = "MWO $productname order - " . currency($orderamount);
    adminEmail($adminemail, $subject, $body);
    # send order SMS
    global $smsrecipients;
    sendSMS($smsrecipients, "MWO $productname ORDER\nAmount: " . currency($orderamount) . "\n$name ($email)\nOrderID: $orderid");


    header("Location: /thankyouqw.html");
  } else {
    $_SESSION['ccfail'] = "Your payment request was unsuccessful - $txnResponseDesc";
    header("Location: /quickwillorder.html");
  }
  
/*
  # TEMPORARY until I work out what to do with an error response
  echo "<pre>\n";
  echo http_build_query($data) . "\n";
  echo "\n";
  print_r($response);
  echo "\n";
  print_r($map);
  echo "\n$txnResponseDesc\n";
*/
  
}


?>      
