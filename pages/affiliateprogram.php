<?php
/* Affiliate program details */

$affiliateprogram = !isset($_SESSION['affiliateprogram']) || $_SESSION['affiliateprogram'] == 0 ? false : true;

# numbers
$discount = isset($_SESSION['affiliatediscount']) ? $_SESSION['affiliatediscount'] : $_SESSION['discount'];

# singlewill
$extaprice = $_SESSION['products']['singlewill']['priceinctax'] / (1 + TAX);
$discountamt = round($extaprice * $discount, 2);
$discountedprice = $extaprice - $discountamt;
$commissionamount = round($discountedprice * COMMISSION, 2);
# mirrorwill
$mwextaprice = $_SESSION['products']['mirrorwill']['priceinctax'] / (1 + TAX);
$mwdiscountamt = round($mwextaprice * $discount, 2);
$mwdiscountedprice = $mwextaprice - $mwdiscountamt;
$mwcommissionamount = round($mwdiscountedprice * COMMISSION, 2);

$affiliatename = isset($_SESSION['organisation']) && $_SESSION['organisation'] ? $_SESSION['orgname'] : $_SESSION['firstname'] . " " . $_SESSION['surname'];
 

$title = "Affiliate Program for " . SITENAME;
$desc = "The " . SITENAME . " affiliate program is a referral program where you and those you refer are generously rewarded for encouraging others to purchase their Last Will and Testament. You earn revenue, those you refer get a discount and earn revenue (if they refer others), and the " . SITENAME . " business makes more sales (albeit at a reduced amount for each sale). You will be promoting a valuable product that is needed by everyone over the age of 18.";
$keywords = "affiliate program, affiliate marketing, affiliates";
require("pageincludes/header.php");
?>
      <section class="container"><h1>Affiliate Program</h1>

<?php
if (isset($_SESSION['userid'])) {
    $_SESSION['shortURL'] = isset($_SESSION['shortURL']) ? $_SESSION['shortURL'] : shortenUrl($_SESSION['affiliateURL']);

  # this is the logged on Affiliate program member section
    $userid = $_SESSION['userid'];
  # get details of any commissions that may be owing for the user
  # tallies for order counts and commission amounts
    $pendingcommissionA = array();
    $pendingcountA = array();
    $payablecommissionA = array();
    $payablecountA = array();
    $paidcommissionA = array();
    $paidcountA = array();
    $pendingcount = $payablecount = $paidcount = '';
    $pendingcommission = $payablecommission = $paidcommission = 0;
    $paidcountTotal = $payablecountTotal = $pendingcountTotal = 0;
  
    $query = "SELECT level, commissionamount, commissiondate, commissionpaymentid FROM ordercommissions WHERE commissionuserid = $userid AND commissionamount > 0;";
    $result = $dbh->query($query);
    $rows = $result->fetchAll();
    $levelcount = 0; // used to display order count and amount for each level
    foreach ($rows as $row) {
        $levelcount = $row['level'] > $levelcount ? $row['level'] : $levelcount;
        if ($row['commissionpaymentid'] > 0) {
            # commission has already been paid
            $paidcommissionA[$row['level']] += $row['commissionamount'];
            $paidcountA[$row['level']]++;
        } elseif ($row['level'] <= LEVELS) {
            if (strtotime("-" . COMMISSIONPAYABLEDAYS . " days") > strtotime($row['commissiondate'])) {
                # commission is payable because the order is older than payment elapsed time
                $payablecommissionA[$row['level']] += $row['commissionamount'];
                $payablecountA[$row['level']]++;
            } else {
                # pending commissions -> pay date less than required days
                $pendingcommissionA[$row['level']] += $row['commissionamount'];
                $pendingcountA[$row['level']]++;
            }
        }
    }
  
    for ($i = 1; $i <= $levelcount; $i++) {
        if (LEVELS > 1) {
            $paidcount .= "<br>L$i: " . number_format($paidcountA[$i]) . ($i < $levelcount ? ", " :"");
            $payablecount .= "<br>L$i: " . number_format($payablecountA[$i]) . ($i < $levelcount ? ", " :"");
            $pendingcount .= "<br>L$i: " . number_format($pendingcountA[$i]) . ($i < $levelcount ? ", " :"");
        }
        $paidcountTotal += $paidcountA[$i];
        $payablecountTotal += $payablecountA[$i];
        $pendingcountTotal += $pendingcountA[$i];
        $paidcommission += $paidcommissionA[$i];
        $payablecommission += $payablecommissionA[$i];
        $pendingcommission += $pendingcommissionA[$i];
    }
    $paidcount = number_format($paidcountTotal) . $paidcount;
    $payablecount = number_format($payablecountTotal) . $payablecount;
    $pendingcount = number_format($pendingcountTotal) . $pendingcount;

  # get past commission payment details
    $sum = $dbh->query("SELECT SUM(paymentamount) FROM commissionpayments WHERE userid = $userid");
    $total = $sum->fetchColumn();
    $query = "SELECT * FROM commissionpayments WHERE userid = $userid ORDER BY requestdate DESC";
    $result = $dbh->query($query);
    $requests = $result->fetchAll();
  
  # check to see if the affiliate has ordered their own yet
    $orders = $dbh->query("SELECT COUNT(id) FROM orders WHERE paid = 1 AND userid = $userid");
    $ordercount = $orders->fetchColumn();
  
  # get details of how many downstream affiliates
    $affiliates = getAffiliates($_SESSION['userid'], LEVELS, $dbh);
    $affiliatelevels = array_count_values($affiliates);
  # get first level name for display
    $level1names = array();
  
    foreach ($affiliates as $affcode => $level) {
        if ($level == 1) {
            $sth = $dbh->query("SELECT CONCAT(u1.firstname, ' ', u1.surname, ' (', (SELECT COUNT(*) FROM users u2 WHERE introcode = '$affcode'), ')') as nameandcount FROM users u1 WHERE affiliatecode = '$affcode';");
            //$sth = $dbh->query("SELECT affiliatecode FROM users WHERE affiliatecode = '$affcode';");
            $level1names[] = $sth->fetchColumn();
        }
    }
    $level1namesS = implode("<br>", $level1names);

?>
        <div class="stats">
          <input id="payableCount" name="payableCount" type="hidden" value="<?php echo number_format($payablecountTotal);?>">
          <input id="payableAmount" name="payableAmount" type="hidden" value="<?php echo currency($payablecommission);?>">
          
          <hr>
          <h2>Affiliate Statistics for <?php echo $affiliatename; ?></h3>
          <span id="level1names" style="display: none;"><?php echo $level1namesS;?></span>

<?php
if (LEVELS > 1) {
?>
      <table class="table table-bordered">
        <tr>
          <th style="width: 80%;"><a data-toggle="popover" data-trigger="hover" title="Referee Links" data-content="<em>Referee Links</em> are <?php echo $_SESSION['sitename']; ?> customers that have been referred by you (Level 1), or referred by those you have referred (up to <?php echo LEVELS;?> levels down).">Referee Links</a></th>
          <th style="width: 20%;" class="text-right"><a data-toggle="popover" data-trigger="hover" title="Referee Count" data-content="<em>Referee Count</em> is the number of <?php echo $_SESSION['sitename']; ?> customers that have been referred by you (Level 1), or referred by those you have referred (subsequent levels).">Count</a></th>
        </tr>
<?php
foreach ($affiliatelevels as $level => $levelcount) {
?>
      <tr>
        <th><?php echo $level == 1 ? "<a id='l1lnks' href=''>Level 1</a>" : "Level " . $level;?></th>
        <td class="text-right"><?php echo number_format($levelcount); ?></td>
      </tr>
<?php
}
if (count($affiliatelevels) == 0) {
?>
      <tr>
        <th>Level 1</th>
        <td class="text-right">0</td>
      </tr>
<?php
}
?>
      </table>
<?php
}
?>
          <table class="table table-bordered">
            <tr>
              <th style="width: 40%;"><a data-toggle="popover" data-trigger="hover" title="Commissionable Orders" data-content="<em>Commissionable Orders</em> are orders from <?php echo $_SESSION['sitename']; ?> customers who were referred by you<?php echo LEVELS > 1 ? ' directly or indirectly.' : '.';?>">Commissionable Orders</a></th>
              <th style="width: 20%;" class="text-right"><a data-toggle="popover" data-trigger="hover" title="Recent Orders" data-content="<em>Recent Orders</em> are orders that were created less than <?php echo COMMISSIONPAYABLEDAYS; ?> days ago. They are not eligible for commission payment yet.">Recent Orders</a></th>
              <th style="width: 20%;" class="text-right"><a data-toggle="popover" data-trigger="hover" title="Payable Orders" data-content="<em>Payable Orders</em> are orders that are eligible for commission payment. You may request payment for the amount shown.">Payable Orders</a></th>
              <th style="width: 20%;" class="text-right"><a data-toggle="popover" data-trigger="hover" title="Payment Requested Orders" data-content="<em>Payment Requested Orders</em> are orders that have commission paid or are in the process of having commission paid.">Payment Requested Orders</a></th>
            </tr>
            <tr>
              <th>Order Count<?php echo LEVELS > 1 ? '<br>By Level' : ''?></th>
              <td class="text-right"><?php echo $pendingcount; ?></td>
              <td class="text-right"><?php echo $payablecount; ?></td>
              <td class="text-right"><?php echo $paidcount; ?></td>
            </tr>
            <tr>
              <th>Commission Value to You</th>
              <td class="text-right"><?php echo currency($pendingcommission); ?></td>
              <td class="text-right"><?php echo currency($payablecommission); ?></td>
              <td class="text-right"><?php echo currency($paidcommission); ?></td>
            </tr>
<?php if ($affiliateprogram) { ?>
            <tr>
              <td></td>
              <td></td>
              <td class="text-right"><button id="requestPayment" class="btn btn-sm btn-default">Request Payment</button></td>
              <td class="text-right"><button id="viewPaymentHistory" class="btn btn-sm btn-default">View Payment History</button></td>
            </tr>
<?php } ?>
          </table>
        </div>
<?php if (!$affiliateprogram) { ?>
        <p class="small">
          *
          <em>
            Although your earnings to date are displayed above, you aren't
            eligible to be paid the commissions until you <a href="#join" title="Join the Program">join the affiliate program</a>.
          </em>
        </p>
<?php } ?>
        <div id="paymentHistory" style="display: none;">
          <h4>Payment History for <?php echo $affiliatename; ?></h4>
          <p><strong>Total commission paid to date: &nbsp; &nbsp;</strong><em><?php echo currency($total); ?></em></p>
          <table class="table table-bordered">
            <tr>
              <th>Payment Request Date</th>
              <th>Payment Completed Date</th>
              <th class="text-right">Amount</th>
              <th>Status</th>
            </tr>
<?php foreach ($requests as $request) { ?>
            <tr>
              <td><?php echo showdatetime($request['requestdate']); ?></td>
              <td><?php echo showdatetime($request['paymentdate']); ?></td>
              <td class="text-right"><?php echo currency($request['paymentamount']); ?></td>
              <td><?php echo $request['status']; ?></td>
            </tr>
<?php } ?>
          </table>
        </div>
        <br>        
        <hr>
        <h2>How to earn Affiliate Program income.</h2>
        <p>
          We've created a unique affiliate code for you - 
          <a data-toggle="popover" data-trigger="hover" title="Your unique affiliate code" data-content="This is your unique affiliate code that you may share with others to give them a discount and earn you income."><?php echo $_SESSION['affiliatecode']; ?></a> -
          that will link your friends, family and associates to your 
            <?php echo $_SESSION['sitename']; ?> affiliate account once they register. They
          will be linked to your affiliate account when they access <?php echo $_SESSION['sitename']; ?> using this link - 
          <a data-toggle="popover" data-trigger="hover" title="Your unique affiliate link" data-content="This is your unique affiliate website link that you may share with others so they get a discount at <?php echo $_SESSION['sitename']; ?> and so that you earn your affiliate income when they purchase a Will or other documents."><?php echo $_SESSION['affiliateURL']; ?></a>
          - which has your code incorporated in
          it. 
          Or, if you prefer, you can give them the affiliate code directly and they can enter it into the 'coupon code' field on their order screen.
        </p>
        <h3>You may post referrals to your friends and family using the buttons below -</h3>
        <div class="form-group">
          <label>Suggested wording for your post:</label>
          <textarea id="posttext" name='posttext' class="form-control" style="height: 60px; background-color: #eee;">I've just used this online will service. It's easy to use, great value and now I have a valid Will. I recommend it if you haven't got a will or you need an updated one. If you click on the link below you'll get a <?php echo percent($discount); ?> discount when you order.</textarea>
        </div>
        <p>
          <input name="discount" type="hidden" value="<?php echo percent($discount); ?>">
          <input name="affiliateURL" type="hidden" value="<?php echo $_SESSION['affiliateURL']; ?>">
          <input name="shortURL" type="hidden" value="<?php echo $_SESSION['shortURL'];?>">
          <button id="postToFB" class="btn btn-sm btn-social btn-facebook"><i class="fa fa-facebook"></i>Facebook</button>
          <button id="postToTwitter" class="btn btn-sm btn-social btn-twitter"><i class="fa fa-twitter"></i>Twitter</button>
          <a href="mailto:" id="postToEmail" class="btn btn-sm btn-social btn-vk"><i class="fa fa-envelope"></i>Email</a>
        </p>
        <br>
        <p class="alert alert-warning">
          It's very important that they register on <?php echo $_SESSION['sitename']; ?> when they've clicked on the link that you provide,
          or alternatively enter your affiliate code in the <u>coupon code</u> field when they are ready to pay. 
          If they don't do either, then they'll pay full price and you won't receive
          a commission on their purchase.
        </p>
        <hr>
<?php
}
?>
        <h2>How it Works</h2>
        <p>
          For every new customer that you refer to 
            <?php echo $_SESSION['sitename']; ?>, they will get a 
          <strong><?php echo percent($discount); ?></strong> discount off the price of a <?php echo $_SESSION['premiumwill'];?>
          and you will be paid <strong><?php echo percent(COMMISSION); ?></strong> of what they spend. The normal price of a <?php echo $_SESSION['premiumwill'];?>
          is <?php echo currency($extaprice); ?><?php echo (TAX > 0) ? ' (ex. GST)' : '';?>, so, the customers you refer will pay <?php echo currency($discountedprice); ?> and you will earn <?php echo currency($commissionamount); ?> for each <?php echo $_SESSION['premiumwill'];?> ordered.
          Or, if they order a will and mirror will, you'll earn <?php echo currency($mwcommissionamount + $commissionamount);?>.
        </p>

<?php
if (LEVELS > 1) {
?>

        <p>
          Not only will you get a payment for what they
          spend, you will also be paid <?php echo percent(COMMISSION); ?> of what each person they recommend spends, and also for each person who is recommended by who
          they recommend.
            <?php echo $_SESSION['sitecompany']; ?> will pay you a <?php echo percent(COMMISSION); ?> commission on any <?php echo $_SESSION['sitename']; ?> purchase from anyone that is directly or indirectly recommended to <?php echo $_SESSION['sitename']; ?>
          up to a maximum of <?php echo number_to_words(LEVELS) . " (" . LEVELS . ")";?> levels down.
        </p>
<?php
$intros = 2;
$indirectintros = 0;
$indirectintroslong = '';
for ($i = 2; $i <= LEVELS; $i++) {
    $indirectintros += pow($intros, $i);
    $and = $i == LEVELS ? ' and ' : '';
    $indirectintroslong .= $and . number_format(pow($intros, $i)) . ' (' . ordinal($i) . ' level), ';
}
$indirectintroslong = rtrim($indirectintroslong, " ,");
?>     
        <p>
          For example, if you introduce <?php echo $intros;?> people (1st level), who each in turn introduce <?php echo $intros;?> (2nd level), 
          who each introduce <?php echo $intros;?> (3rd level) and so on.
          There would be <?php echo $intros;?> (1st level),
            <?php echo $indirectintroslong;?>
          customers introduced. If each of these <?php echo number_format($indirectintros + $intros);?> customers
          purchase a will at the discounted price of <strong><?php echo currency($discountedprice); ?></strong><?php echo (TAX > 0) ? ' <small>(ex. GST)</small>' : '';?>, then you will have
          <strong><?php echo currency($commissionamount); ?> x <?php echo number_format($indirectintros + $intros);?> = <?php echo currency($commissionamount * ($indirectintros + $intros));?></strong> credited to your 
            <?php echo $_SESSION['sitename']; ?> account ready to claim when you want it.
        </p>
<?php
}
?>
        <br>        
<?php
if (!isset($_SESSION['userid'])) {
?>
        <h2>How Do I Get Started?</h2>
        <p>
          The first thing to do, is to register at <?php echo $_SESSION['sitename']; ?>.
          Once you've registered and logged in, click on the Affiliate Program link (in the footer of the website) to get all the information
          you need to begin referring your friends and family to <?php echo $_SESSION['sitename']; ?> and to start receiving commission payments.
        </p>
        <br>
        <h3><a id="affiliateTermsAndConditions" href=""><i class="glyphicon glyphicon-file"></i> Affiliate Program Terms and Conditions</a></h3>
        <div style="height: 200px;"></div>
<?php
} else {
    if (!$affiliateprogram) {
        # logged on but has not agreed to t and c
    ?>
        <hr>
        <a name="join"></a>
        <h2>Join the Affiliate Program</h2>
        <form id="AffiliateForm" method="post" role="form">
          <div class="form-group">
            <p class="large">
              By joining the <?php echo $_SESSION['sitename']; ?> Affiliate Program you agree to be bound by the program <strong><a id="affiliateTermsAndConditions" href="">Terms and Conditions</a></strong>
            <p>
            <button id="joinAffiliateProgram" class="btn btn-primary">I agree to the T&Cs. Enrol me in the Affiliate Program</button>
          </div>
        </form>
    <?php
    } else {
    ?>
        <h3><a id="affiliateTermsAndConditions" href=""><i class="glyphicon glyphicon-file"></i> Affiliate Program Terms and Conditions</a></h3>
        <br>
    <?php
    }
}
?>
        <div id='affiliateTerms' style="display: none;">
          <p>
            Before you can enrol as a member in the <?php echo $_SESSION['sitename']; ?> Affiliate Program, you
            must agree to be bound by the following terms and conditions.
          </p>
          <ol>
            <li>
              The Affiliate Program is a program primarily aimed at rewarding <?php echo $_SESSION['sitename']; ?> 
              customers to promote the use of <?php echo $_SESSION['sitename']; ?> to their
              family, friends and associates.
            </li>
            <li>
              The Affiliate Program provides a <?php echo percent($discount); ?> discount on our <?php echo $_SESSION['premiumwill'];?> product to customers referred by members of the affiliate program. This
              discount amount can be varied at any time without prior notice to members of the Affiliate Program.
            </li>
<?php
if (LEVELS > 1) {
?>
            <li>
              The Affiliate Program provides a <?php echo percent(COMMISSION); ?> commission payment for all
                <?php echo $_SESSION['premiumwill'];?> sales by directly or indirectly referred customers of <?php echo $_SESSION['sitename']; ?> 
              up to a maximum depth of <?php echo number_to_words(LEVELS) . " (" . LEVELS . ")";?> referees. 'Depth' in this clause, is defined as follows: If a customer refers a new customer directly, then the depth
              is one (1), if that new customer refers another customer, then the depth is two (2) and so on.
            </li>
            <li>
              Members of the Affiliate Program will see details of orders placed by directly and indirectly referred customers on their Affiliate Program page. These details
              will include the level of referee and the amount owing to the member.
            </li>
<?php
} else {
?>
            <li>
              The Affiliate Program provides a <?php echo percent(COMMISSION); ?> commission payment for all
                <?php echo $_SESSION['premiumwill'];?> sales by referred customers of <?php echo $_SESSION['sitename']; ?>.
            </li>
            <li>
              Members of the Affiliate Program will see details of orders placed by referred customers on their Affiliate Program page.
            </li>
<?php
}
?>
            <li>
              Commission payments become eligible for payment <?php echo COMMISSIONPAYABLEDAYS;?> days after a commissionable order has been placed. This covers situations where the payment amount has been reversed,
              refunded or otherwise disputed.
            </li>
            <li>
              Commission payments will not be processed for payment unless the member requests payment by using the Request Payment
              button on their Affiliate Program page.
              <ol style="list-style-type: lower-roman;">
               <li>
                  Clicking the Request Payment button, will initiate a payment request from <?php echo SITENAME;?> to the member using Paypal Australia's send money functionality.
               </li>
               <li>
                  Assuming there are no problems, payment to the member will occur within <span id="paymentdays"><?php echo COMMISSIONPAYREQUESTDAYS;?></span> business days if the member already has a Paypal account linked to their email address.
                  If the member does not have a Paypal account linked to their email address, they will be sent an email by Paypal inviting them to setup an account to receive the payment.
               </li>
               <li>
                  When the payment is successfully requested, the amount owing to the member will be adjusted by the amount paid and a record
                  of the payment transaction and the status of the payment will be shown within their View Commission Payments area on the Affiliate Program page.
               </li>
              </ol>
            </li>
            <li>
                <?php echo $_SESSION['sitename']; ?> will make direct payments to the member without any deductions for
              any taxes that may or may not be owed by the member. The member of the Affiliate Program is solely responsible and liable for
              any sort of tax liability that they may incur from receiving commission payments.
            </li>
            <li>
              If the operators of <?php echo $_SESSION['sitename']; ?> believe that the amount requested for payment and/or
              the amount identified as owing to the member is erroneous in any way, then they, the operators of <?php echo $_SESSION['sitename']; ?>,
              at their sole discretion may refuse to make the payment and may adjust the amount owing to correct the error. This will apply whatever the cause of the error;
              whether it be due to fraudulent activity, software programming errors, or any other manipulation of, or tampering with this Affiliate Program.
            </li>
            <li>
              The Affiliate Program may be discontinued at any time by the operators of
                <?php echo $_SESSION['sitename']; ?>. If the Affiliate Program
              is discontinued by the operators of <?php echo $_SESSION['sitename']; ?>, all monies
              owing to those enrolled in the Affiliate Program will be paid according to these
              terms and conditions. 
            </li>
            <li>
              You can use your Affiliate Program link anywhere you believe it’s proper to use, however, we will not tolerate any sort of spamming via email, blogsites, forums or any other method.
              You may only use the promotion techniques, which are legal and comply with these Terms and Conditions.
            </li>
            <li>
              If the operators of <?php echo $_SESSION['sitename']; ?> suspect that a member of the Affiliate program is using any sort of SPAM technique or other illegal methods to promote <?php echo $_SESSION['sitename']; ?>, they will be immediately
              removed as a member of the Affiliate program and will have all commissions owing to them forfeited.
            </li>
            <li>
              You may advertise using any PPC (pay per click) or PPV (pay per view) advertising service, such as Google Adwords using your unique Affiliate Program link. You may also use social media sites, for example Facebook and Twitter, to promote <?php echo $_SESSION['sitename']; ?> using your unique Affiliate link.
            </li>
            <li>
              These terms and conditions may be altered at any time by the operators of <?php echo $_SESSION['sitename']; ?>.
              If these terms and conditions are altered at all, then to continue as a member of the Affiliate Program, you will have to agree to the new
              terms and conditions before any additional commissions are credited to your <?php echo $_SESSION['sitename']; ?> rewards account.
            </li>
          </ol>
        </div>
      </section>
<?php
require("pageincludes/footer.php");
?>