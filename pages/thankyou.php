<?php
# check that page is not accessed directly and redirect if so
if (!isset($_SESSION['userid']) || (!isset($_SESSION['getVars']['item_number']) && !isset($_SESSION['orderid']))) goHome(); # go to home page if not logged in

/* 

this page will be called from Paypal, from processpayment.php - COMMWEB or internal $0)

if from Paypal it will have the following getVars set

           [tx] => 89710519063099314
           [st] => Completed
           [amt] => 54.30
           [cc] => AUD
           [cm] => 
           [item_number] => 

if from POLi the following getVars are set
  
           [token] => 
           [item_number] =>
           [paymentgw] => poli

*/

$paymentgw = (isset($_SESSION['getVars']['paymentgw'])) ? $_SESSION['getVars']['paymentgw'] : 'paypal';
$orderid = isset($_SESSION['getVars']['item_number']) ? $_SESSION['getVars']['item_number'] : $_SESSION['orderid'];

# get orderData, etc
$query = "SELECT o.extaxamount, o.discountamount, o.paid, AES_DECRYPT(o.orderdata,'{$_SESSION['affiliatecode']}') AS orderdata, o.productid, p.shortname, p.name, p.template FROM orders o JOIN product p ON (p.id = o.productid) WHERE o.id = $orderid;";
$result = $dbh->query($query);
$row = $result->fetch(PDO::FETCH_ASSOC);
$_SESSION['orderData'] = unserialize(stripslashes($row['orderdata']));
$_SESSION['orderpaid'] = $row['paid'] == 1 ? 1 : 0;
$_SESSION['orderextaxamount'] = $row['extaxamount'];
$_SESSION['orderdiscountamount'] = $row['discountamount'];
$_SESSION['productid'] = $row['productid'];
$_SESSION['product'] = $row['shortname'];
$_SESSION['productname'] = $row['name'];
$_SESSION['template'] = $row['template'];
$_SESSION['orderid'] = $orderid;

$extaxamount = $_SESSION['orderextaxamount'];
$discamount = $_SESSION['orderdiscountamount'];
$extaxtotal = round($extaxamount - $discamount,2);
$taxamount = round($extaxtotal * TAX, 2);
$totalamount = round(($extaxamount - $discamount) * (1 + TAX), 2);

if ($paymentgw == 'paypal') {
  // get details of the transaction and update database if ipnlistener.php has not yet done so
  $amount = round($_SESSION['getVars']['amt'], 2); 
  $cc = $_SESSION['getVars']['cc'];  // should be AUD
  $txnid = $_SESSION['getVars']['tx'];
  $tcstatus = $_SESSION['getVars']['st'];
  $item_number = $_SESSION['getVars']['item_number'];
  
  if (!$_SESSION['orderpaid']) {
  	# the order has not been flagged as paid, so ipnlistener has not completed yet
  	# so update the order here provided everything is correct
  	if ($totalamount != $amount) {
  		# different amounts DANGER WILL ROBINSON - DANGER
  		echo "<script type='text/javascript'>modalMessage('Payment Issue', 'The amount for the order should be " . currency($totalamount) . " but Paypal reported " . currency($amount) . "', 0, 'location = \"/\";', 400); </script>";
  		exit;
  	} else {
    	$dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paidtxnid = '$txnid' WHERE id = $orderid;");
    	$dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paidtxnid = '$txnid' WHERE mirrorof = $orderid;"); // also flag mirror as paid if it exists
    	insertOrderCommission($orderid, $dbh);
    }
  }
}

if ($paymentgw == 'poli') {
  if (!$_SESSION['orderpaid']) {
  	// the order has not been flagged as paid, so polinudge has not completed yet
    $token = $_SESSION['getVars']['token']; 
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

    $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'poli', paymentattempts = paymentattempts + 1, paidtxnid = '$transactionRefNo', reason = 'POLI - $transactionStatus' WHERE id = $orderid");
    $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paymenttype = 'poli', paymentattempts = paymentattempts + 1, paidtxnid = '$transactionRefNo', reason = 'POLI - $transactionStatus' WHERE mirrorof = $orderid");
    $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$transactionRefNo', 'POLI - $transactionStatus', '$invoice', 'poli')");
    insertOrderCommission($orderid, $dbh);
    sendOrderEmail($orderid, 'poli', $_SESSION['introcode']);

    // output log
    $date = date("D M j G:i:s T Y", time());
    $message = "\n[$date] The following data was received from POLi by " . __FILE__ . ":\n";
    $fh = fopen('poli.log','a');
    fwrite($fh, $message . print_r($jsonResponse, true));
    fclose($fh);
  }
}

$title = "Thank You for Your Order";
$desc = "Thank you for your order.";
$keywords = "last will and testament, online will, online wills, internet wills, online will kit";
require("pageincludes/header.php");
?>
    <section class="container">
      <h1 class="text-success">Thank you for your order</h1>
<?php
if ($extaxamount - $discamount > 0) {
?>
      <p>
        You have successfully paid for your order. 
      </p>
      <p>
        Thank you for your payment. Your transaction has been completed and a receipt for your purchase has been emailed to you.
      </p>
<?php
}
if ($_SESSION['orderData']['product']['mirrorwill'] == 'Yes') {
?>
      <p>
      	<i class="glyphicon glyphicon-warning-sign text-warning"></i> <b>Please note:</b> You requested a mirror will for your partner
      	and a new Will has been created for them.
      	Because	they are likely to have <b><em>different</em></b> personal requirements, information and messages than you, we suggest that you go
      	in and edit your partner&apos;s will before you download it.
      </p>
<?php
}

if ($_SESSION['orderData']['product']['enduring'] == 'Yes') {
?>
      <p>
      	<i class="glyphicon glyphicon-warning-sign text-warning"></i> <b>Please note:</b> You requested an Enduring Power of Attorney document.
      	This is included, along with guidelines for completion, in the document package that you may now download.
      	Once downloaded, please read the guidelines and adjust the Enduring Power of Attorney to suit your circumstances if needed.
      </p>
<?php
}
?>
			<p>
				You may download the document package for <?php echo $_SESSION['orderData']['person']['fullname'];?> right now, or return to the
				home screen to make changes to the information entered and then download. <em class="highlight2">You may edit the information and 
				download the documents as many times as you require</em>.
			</p>
      <div>
        <a id="downloadDocs" href="download.html" target="Download" class="pull-right btn btn-success">Download Documents for <?php echo $_SESSION['orderData']['person']['fullname'];?></a>
        <a href="/mydocs.html" class="btn btn-default">Return to Edit Information</a>
      </div>
    </section>
    
    <script type="text/javascript">
    /* <![CDATA[ */
    var google_conversion_id = 1034765843;
    var google_conversion_language = "en";
    var google_conversion_format = "3";
    var google_conversion_color = "ffffff";
    var google_conversion_label = "L8wYCKjYiXQQk4y17QM";
    var google_remarketing_only = false;
    /* ]]> */
    </script>
    <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
    </script>    

<?php
require("pageincludes/footer.php");
?>