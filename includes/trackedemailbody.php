<?php
/* trackedemail logic - included by the function sendTrackedEmail */

$unsubscribe = "";
$sendemail = true;
$body = "";
$subject = "";

$willproduct = QUICKWILL ? 'Premium' : 'Online';

# depending upon type of email, setup the subject and message
  
switch ($emailtype) {

  case "recommend":  
    $commission = percent(COMMISSION);
    $subject = "Thanks for being a $sitename customer.";
    $fromemail = "$siteboss <$sitebossemail>";
    /* This will retrieve product information */
    $query = "SELECT p.name, p.shortname, p.template, p.id as productid, p.priceinctax FROM product p;";
    $sth = $dbh->query($query);
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    $products = array();
    foreach($rows as $row) {
      $shortname = $row['shortname'];
      $products[$shortname]['name'] = $row['name'];
      $products[$shortname]['template'] = $row['template'];
      $products[$shortname]['priceinctax'] = $row['priceinctax'];
      $products[$shortname]['productid'] = $row['productid'];
    }
    $pwname = $products['singlewill']['name'];
    $pwextaxprice = $products['singlewill']['priceinctax'] / (1 + TAX);
    $discountprice = $pwextaxprice * (1 - $discount);
    $rewardamt = round($discountprice * COMMISSION, 0);
    $discountprice = currency($discountprice);
    $pwextaxprice = currency($pwextaxprice);
    $body = "
<div>
<p>Hi $firstname,</p>

<p>Thank you so much for using our service to create your Will.</p>

<p>If you'd like to help out your family and friends, then please recommend $sitename to them.</p>

<p>We will give them a $discountperc discount when they order a Will from us, and you will get $commission of what they spend as a rebate.</p>

<p>It is quite simple, just copy the link below and send it to them:</p>

<p><a href=\"$website/?afid=$affiliatecode\">$website?afid=$affiliatecode</a></p>

<p>You'll need to join our referal program by accepting our terms and conditions to claim your rebate. For details, login and check out our <a href=\"$website/start.html?pgref=affiliateprogram&email=$toemail\">referal program</a>.</p>

<p>Thank you again, and please take care in these difficult times,</p>

<p><em><b>$siteboss</b><br>My Will Online</em></p>
</div>     
    ";
    $unsubscribe =  "| <a href=\"$website/unsubscribe.html?code=$affiliatecode\">Unsubscribe</a>";
    break;


  case "paymentrequest":
    $subject = "Your commission payment request has been received";
    $commissiondays = COMMISSIONPAYREQUESTDAYS;
    $commissionamount = currency($paymentamount);
    $body = "
<div>
<p>Hi $firstname,</p>

<p>Your request from $sitename for your commission payment of $commissionamount is being verified and should be paid within $commissiondays business days.</p>

<p>If for some reason your payment does not arrive, please <a href=\"$website/contact.html\">contact us</a>.

<p>Thank you again for using $sitename and recommending it to others.</p>

<p>Kind regards,</p>

<p><b><em>The Team at $sitename</em></b></p>
</div>     
    ";
    break;
    
    
  case "register":
    $subject = "Welcome to $sitename";
    $fromemail = "$siteboss <$sitebossemail>";

    $body = "
<div>
<p>Hi $firstname,</p>

<p>Welcome to <a href='$website'>$sitename</a>.</p>

<p>If you need any assistance in setting up your Will, then please reply to this email with your questions.</p>

<p>If you received this message in error, and you didn't register at <a href='$website'>$sitename</a>, then please click <a href='$website/notmyaccount.php?code=$affiliatecode'>not my account</a> and all your details will be removed from our system.</p>

<p>Kind regards,</p>

<p><b><em>$siteboss</em></b></p>
</div>     
    ";
    break;
    
    
  case "order":
    $subject = "Thank you for your order";
    $productexpiry = PRODUCTEXPIRY < 99 ? PRODUCTEXPIRY . ' months from today' : 'life';
    $body = "
<div>

<p>Hi $firstname,</p>

<p>Thank you for using <a href='$website'>$sitename</a>.</p>

<p>You've successfully completed payment for your order.</p>

</div>
    ";
    $body .= $message;
    $body .= "
<div>

<p>You may make changes to your documents as often as you require and download further copies as required for " . $productexpiry . ".</p>

<p>To download your documents please go to <a href='$website'>$sitename</a> and login. You will see <em><b>edit</b></em> and <em><b>download</b></em> buttons on your home page.</p>

<p>Should you need any assistance while using <a href='$website'>$sitename</a> then please reply to this email with your questions and we will do our best to answer them.</p>

<p>Thank you and kind regards,</p>

<p><b><em>The Team at $sitename</em></b>

</div>
    ";
    break;


  case "feedback":
    $subject = "We would love to hear about your $sitename experience";
    $fromemail = "$siteboss <$sitebossemail>";
    $body = "
<div>
<p>Hi $firstname,</p>

<p>Thank you so much for using our service. We really appreciate you choosing us to create your Will.</p>

<p>If you have time (about 30 seconds) then please <a href=\"$website/feedback.html?code=$affiliatecode\">rate our service</a> and let us know how we can improve.</p>

<p><a class=\"button text-success\" href=\"$website/feedback.html?code=$affiliatecode\">Rate Us</a></p>

<p>Thank you and kind regards,</p>

<p><b><em>$siteboss</em></b></p>
</div>     
    ";
    $unsubscribe =  "| <a href=\"$website/unsubscribe.html?code=$affiliatecode\">Unsubscribe</a>";
    break;


  case "firstreminder":
    $subject = "You've taken the first step";
    $fromemail = "$siteboss <$sitebossemail>";
    $body = "
<div>
<p>Hi $firstname,</p>

<p>You've started the process of creating your Last Will and Testament at <a href='$website'>$sitename</a>.</p>

<p>Please reply to this email if you need some assistance in creating your will.</p>

<p><a class=\"button text-success\" href=\"$website/start.html?email=$toemail\">Continue With Your Will</a></p>

<p>Thanks and kind regards,</p>

<p><b><em>$siteboss</em></b></p>
</div>     
    ";
    $unsubscribe =  "| <a href=\"$website/unsubscribe.html?code=$affiliatecode\">Unsubscribe</a>";
    break;


  case "secondreminder":
    $subject = "Your completed Will is so close";
    $fromemail = "$siteboss <$sitebossemail>";
    $body = "
<div>
<p>Hi $firstname,</p>

<p>You've started the process of creating your Last Will and Testament at <a href='$website'>$sitename</a>, but you need to complete the process to have a valid Will.</p>

<p>About 10 to 20 minutes may be all you need to finish entering your information and to have a will ready for download and signing.</p>

<p>If you're having trouble using $sitename, then please reply to this email and we will respond with answers to your questions.</p>

<p>If you are ready to continue? Login to <a href=\"$website/start.html?email=$toemail\">My Will Online</a> to finish your Will.</p>

<p>But, if you've changed your mind and don't want to use our service, then you can remove your registration details from our system by clicking the <em>Unsubscribe</em> link below.</p>

<p>Kind regards,</p>

<p><b><em>$siteboss</em></b></p>
</div>     
    ";
    $unsubscribe =  "| <a href=\"$website/unsubscribe.html?code=$affiliatecode\">Unsubscribe</a>";
    break;



  case "endofmonth":
    $lastday = date("tS F Y",strtotime(date("Y-m-t")));
    $discountperc = percent($discount);
    $subject = "$discountperc discount on all documents at $sitename";
    $body = "
<div>
<p>Hi $firstname,</p>

<p>Up until midnight on $lastday, you will save $discountperc on all orders at $sitename.</p>

<ul>
";
    /* This will retrieve product pricing information */
    $dbh = dbConnect();
    $query = "SELECT name, priceinctax FROM product WHERE active = 1 AND priceinctax > 0 AND shortname <> 'quickwill';";
    $sth = $dbh->query($query);
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    $products = array();
    foreach($rows as $row) {
      $body .= "
<li>" . $row['name'] . " - normally " . currency($row['priceinctax']) . " - now " . currency($row['priceinctax'] - round($row['priceinctax'] * $discount, 2)) . ".</li>";
    }
    $dbh = null;
    $body .= "
</ul>

<p>At order time, use the special coupon code <strong>$couponcode</strong> to recalculate your total order amount and save.</p>

<p>This <strong>\"$discountperc off\"</strong> offer <strong>expires at midnight on $lastday</strong>.</p>

<p><a class=\"button text-success\" href=\"$website/start.html?email=$toemail\">Save $discountperc On Your $willproduct Will</a></p>

<p>If you need help while working on your Will, please reply with your questions.</p>

<p>Kind regards,</p>

<p><b><em>The Team at $sitename</em></b></p>
</div>     
    ";
    $unsubscribe =  "| <a href=\"$website/unsubscribe.html?code=$affiliatecode\">Unsubscribe</a>";
    break;


  case "endofmonthfinal":
    $discountperc = percent($discount);
    $subject = "$discountperc discount on all documents at $sitename - ends tonight";
    $body = "
<div>
<p>Hi $firstname,</p>

<p><strong>Last chance to save $discountperc on your Will.</strong></p>

<p>Up until midnight tonight, you will save $discountperc on your $willproduct Will order at $sitename.</p>

<p>At order time, use the special coupon code <strong>$couponcode</strong> to recalculate your total order amount and save.</p>

<p>This <strong>\"$discountperc off\"</strong> offer <strong>expires tonight at midnight</strong>.</p>

<p><a class=\"button text-success\" href=\"$website/start.html?email=$toemail\">Save $discountperc On Your $willproduct Will</a></p>

<p>If you need help while working on your Will, please reply with your questions.</p>

<p>Kind regards,</p>

<p><b><em>The Team at $sitename</em></b></p>
</div>     
    ";
    $unsubscribe =  "| <a href=\"$website/unsubscribe.html?code=$affiliatecode\">Unsubscribe</a>";
    break;

  default:
    $sendemail = false;
}

require("emailbody.php");

?>