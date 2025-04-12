<?php
/* 
  process paypal payment request
  and, if successful update order and insert ordercommision rows and send email
*/
session_start();
require_once('functions.php');



# connect to DB
$dbh = dbConnect();

# listens for paypal IPN message and responds

require_once("ipn_cls.php");


# tracing
# setup object with the initial IPN POST data
$paypal_info = $_POST;
$paypal_ipn = new paypal_ipn($paypal_info);
$paypal_ipn->error_email = SITEEMAIL;
error_log("IPN from Paypal");
foreach ($paypal_ipn->paypal_post_vars as $key=>$value) {
  if (getType($key)=="string") {
    eval("\$$key=\$value;"); # setup a local variable for each POST var
    error_log($key . '=' . $value);
  }
}

# site and server
require_once("sessioninit.php"); # need $_SESSION vars set - driven by $serverName

// email header
$em_headers  = "From: " . $_SESSION['sitename'] . " <" . $_SESSION['siteemail'] . ">\n";    
$em_headers .= "Reply-To: " . $_SESSION['siteemail'] . "\n";
$em_headers .= "Return-Path: " . $_SESSION['siteemail'] . "\n";
$em_headers .= "Organization: " . $_SESSION['sitename'] . "\n";
$em_headers .= "X-Priority: 3\n";

# send an identical response back to PayPal
$paypal_ipn->send_response();

# check if bad transaction and email details
if (!$paypal_ipn->is_verified()) {
  $paypal_ipn->error_out("Bad order (PayPal says it's invalid)" . $paypal_ipn->paypal_response , $em_headers);
  die();
}

if ($txn_type == 'masspay') {
  # masspay transaction - there will be 2 IPNs first with payment_status == 'Processed' and the second with 'Completed'
	// update the commission paid table with the payment status for the user
	$paymentrequestid = $paypal_ipn->paypal_post_vars['unique_id_1'];
	$txnid = $paypal_ipn->paypal_post_vars['masspay_txn_id_1'];
	$query = "UPDATE commissionpayments SET status = '$payment_status', txnid = '$txnid', paymentdate = NOW() WHERE id = $paymentrequestid";
	error_log($query);
	$dbh->exec($query);
	//$paypal_ipn->error_out($payment_status . ' ' . $_SESSION['sitename'] . " commission payment transaction", $em_headers);
} else if (isset($item_number)) {
  # if item_number is QWILL then a quick will transaction so do nothing
  if($item_name == 'Quick Will') {
    exit;
  }
  # a payment, refund reversal or whatever that should all have item_number set to the order number
  # get amounts from order table
  $orderid = $item_number; // nothing for masspay transaction
  $result = $dbh->query("SELECT extaxamount, taxamount, discountamount, invoice FROM orders WHERE id = $orderid;");
  $row = $result->fetch(PDO::FETCH_ASSOC);
  $extaxamount = $row['extaxamount'];
  $taxamount = $row['taxamount'];
  $discamount = $row['discountamount'];
  $invoice = $row['invoice'];
  # now check payment_status and act accordingly
  switch( $payment_status )
  {
    case 'Pending':
      
      $pending_reason=$paypal_ipn->paypal_post_vars['pending_reason'];
      # update DB with pendingreason
      $dbh->exec("UPDATE orders SET paid = 0, reason = '$payment_status', updatewhen = NOW() WHERE id = $orderid");
      $paypal_ipn->error_out("Pending Payment - $pending_reason", $em_headers);
      break;
  
    case 'Completed':
  
      if ($paypal_ipn->paypal_post_vars['txn_type']=="reversal") {
        $reason_code=$paypal_ipn->paypal_post_vars['reason_code'];
        $paypal_ipn->error_out("PayPal reversed an earlier transaction.", $em_headers);
        // you should mark the payment as disputed now
        $dbh->exec("UPDATE orders SET paid = 0, reason = '$payment_status', modifydate = NOW() WHERE id = $orderid");
        $dbh->exec("DELETE FROM orders WHERE mirrorof = $orderid");
        $dbh-exec("DELETE FROM ordercommissions WHERE orderid = $orderid AND commissionpaymentid IS NULL;"); // delete any previously entered but unpaid commission rows for this order
        $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$txn_id', '$payment_status', '$invoice', 'paypal');");
      } else {
        # get totalprice of order and compare with what came back from PayPal
        $totalprice = round(($extaxamount - $discamount) * (1 + TAX), 2);
        $paypal_amt = round($mc_gross, 2);
        
        if (($receiver_id == PAYPALID) && ($mc_currency == CURRENCY) && ($paypal_amt == $totalprice)) {
          // check that order has not already had the same $txn_id entered - that would mean that this is an IPN retry (which can happen sometimes)
          $result = $dbh->query("SELECT COUNT(*) FROM orders WHERE id = $orderid AND paidtxnid = '$txn_id' AND reason = 'Successful - IPN';");
          if ($result->fetchColumn() == 0) {
            // update order as paid and add in commission records
            $updateQuery = "UPDATE orders SET paid = 1, paiddate = NOW(), paidtxnid = '$txn_id', reason = 'Successful - IPN', paymenttype = 'paypal', paymentattempts = paymentattempts + 1 WHERE id = $orderid";
            if($dbh->exec($updateQuery)) {
              $dbh->exec("UPDATE orders SET paid = 1, paiddate = NOW(), paidtxnid = '$txn_id', reason = 'Successful - IPN', paymenttype = 'paypal', paymentattempts = paymentattempts + 1 WHERE mirrorof = $orderid"); // also flag mirror as paid again - only for expired primary orders that have a mirror
              $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$txn_id', 'Successful - IPN', '$invoice', 'paypal');");
              sendOrderEmail($orderid, "paypal", $custom); // $custom has the coupon code in it from paypalorder.php - needed to show the discount row in the order email
              insertOrderCommission($orderid, $dbh); // create any ordercommission rows applicable
              //$paypal_ipn->error_out("Successful " . $_SESSION['sitename'] . " transaction", $em_headers);
            } else {
              $paypal_ipn->error_out("Database update error for orderid=$orderid", $em_headers);
            }
          } else {
            $paypal_ipn->error_out("IPN retry for orderid=$orderid with txn_id $txn_id", $em_headers);            
          }
        } else {
          $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$txn_id', 'Manipulated URL', '$invoice', 'paypal');");
          $paypal_ipn->error_out("Someone attempted a sale using a manipulated URL\n\nmc_gross=$mc_gross, totalprice=$totalprice", $em_headers);
        }
      }
      break;
      
    case 'Failed':
      // this will only happen in case of echeck.
      $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$txn_id', '$payment_status', '$invoice', 'paypal');");
      $paypal_ipn->error_out("Failed Payment", $em_headers);
      break;
  
    case 'Denied':
      // denied payment by us
      $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$txn_id', '$payment_status', '$invoice', 'paypal');");
      $paypal_ipn->error_out("Denied Payment", $em_headers);
      break;
  
    case 'Refunded':
      // payment refunded by us
      $dbh->exec("UPDATE orders SET paid = 0, reason = '$payment_status', modifydate = NOW(), mirrorof = NULL WHERE id = $orderid");
      $dbh->exec("DELETE FROM orders WHERE mirrorof = $orderid");
      $dbh-exec("DELETE FROM ordercommissions WHERE orderid = $orderid AND commissionpaymentid IS NULL;"); // delete any previously entered but unpaid commission rows for this order
      $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$txn_id', '$payment_status', '$invoice', 'paypal');");
      $paypal_ipn->error_out("Refunded Payment", $em_headers);
      break;
  
    case 'Canceled_Reversal':
      // reversal cancelled
      // mark the payment as dispute cancelled    
      $dbh->exec("UPDATE orders SET paid = 1, reason = '$payment_status', modifydate = NOW() WHERE id = $orderid");
      $dbh->exec("UPDATE orders SET paid = 1, reason = '$payment_status', modifydate = NOW() WHERE mirrorof = $orderid"); // also flag mirror as paid - only for expired primary orders that have a mirror
      $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$txn_id', '$payment_status', '$invoice', 'paypal');");
      insertOrderCommission($orderid, $dbh); // recreate ordercommision rows
      $paypal_ipn->error_out("Cancelled reversal", $em_headers);
      break;
  
    default:
      // order is not good
      $dbh->exec("INSERT INTO orderpayments (orderid, extaxamount, taxamount, discountamount, paiddate, paidtxnid, reason, invoice, paymenttype) VALUES ($orderid, '$extaxamount', '$taxamount', '$discamount', NOW(), '$txn_id', '$payment_status', '$invoice', 'paypal');");
      $paypal_ipn->error_out("Unknown Payment Status - $payment_status", $em_headers);
  
  } // end of switch
} else {
  # probably a send_money transaction
  $paypal_ipn->error_out("$txn_type IPN", $em_headers);
}



?>