<?php
# get the hidden input fields for a paypal order

session_start();
require_once('functions.php');


$paypalid = PAYPALID;
$paypalurl = PAYPAL;
# calculate pricing
$orderamount = round($_SESSION['extaxtotal'],2);
$taxrate = round(TAX * 100,2);
$orderid = $_SESSION['orderid'];
$productname = $_SESSION['productname'];
$invoice = $_SESSION['invoice'];
$returnurl = url_origin() . '/thankyou.html';
$cancelurl = url_origin() . '/mydocs.html';
$email = $_SESSION['email'];
$phone = $_SESSION['phone'];
$firstname = $_SESSION['firstname'];
$surname = $_SESSION['surname'];
$address = $_SESSION['address1'];
$city = $_SESSION['city'];
$state = $states[$_SESSION['state']];
$postcode = $_SESSION['postcode'];
$country = COUNTRY;
$couponcode = $_SESSION['introcode'];
$servername = url_origin();

$inputfields = <<<EOF
<input type="hidden" name="business" value="$paypalid">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="bn" value="MyWillOnline_BuyNow_Will_AU">
<input type="hidden" name="orderid" value="$orderid">
<input type="hidden" name="item_number" value="$orderid">
<input type="hidden" name="item_name" value="$productname">
<input type="hidden" name="amount" value="$orderamount">
<input type="hidden" name="tax_rate" value="$taxrate">
<input type="hidden" name="invoice" value="$invoice">
<input type="hidden" name="currency_code" value="AUD"> 
<input type="hidden" name="no_ird" value="1"> 
<input type="hidden" name="lc" value="AU"> 
<input type="hidden" name="return" value="$returnurl"> 
<input type="hidden" name="cancel_return" value="$cancelurl"> 
<input type="hidden" name="email" value="$email">
<input type="hidden" name="night_phone_b" value="$phone">
<input type="hidden" name="first_name" value="$firstname">
<input type="hidden" name="last_name" value="$surname">
<input type="hidden" name="address1" value="$address">
<input type="hidden" name="city" value="$city">
<input type="hidden" name="state" value="$state">
<input type="hidden" name="zip" value="$postcode">
<input type="hidden" name="country" value="$country">         
<input type="hidden" name="custom" value="$couponcode">
EOF;

echo json_encode(array('inputfields' => $inputfields, 'paypalurl' => $paypalurl));
?>