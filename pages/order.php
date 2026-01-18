<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

error_log("Order attempt by userid " . $_SESSION['userid'] . " with orderid " . $_SESSION['orderid']);
if (!isset($_SESSION['userid']) || !($_SESSION['userid'] > 0) || !isset($_SESSION['orderid']) || !($_SESSION['orderid'] > 0)) {
  goHome(); # go to home page if not logged in or missing orderid
  exit;
}

$_SESSION['lastPage'] = basename(__FILE__);

/* displays the payments page */

$orderData = $_SESSION['orderData'];

# do a check for all important information entered and display warning if not
$willDataIncomplete = false;
if (!isset($orderData['estate'][1]['recipient']) || $orderData['executor'][1]['fullname'] == '')
  $willDataIncomplete = true;

# make sure pricing is correct
updateOrderData();

$orderid = $_SESSION['orderid'];
$userid = $_SESSION['userid'];
$fullname = $_SESSION['othername'] > '' ? $_SESSION['firstname'] . ' ' . $_SESSION['othername'] . ' ' . $_SESSION['surname'] : $_SESSION['firstname'] . ' ' . $_SESSION['surname'];

# get more order details 
$query = "SELECT id, createdate, paid, paiddate, reason, modifydate FROM orders WHERE id = $orderid;";
$result = $dbh->query($query);
$row = $result->fetch(PDO::FETCH_ASSOC);
$reason = $row['reason'];
$paid = $row['paid'];
$paiddate = $row['paiddate']; # datetime when paid
$modifydate = strtotime($row['modifydate']);
$createdate = strtotime($row['createdate']);
$invoice = $_SESSION['invoice'] = $orderid . '-' . date('sdB');
$dbh->exec("UPDATE orders SET invoice = '$invoice' WHERE id = $orderid;");
if ($paid == 0 && $reason > '') {
  if ($modifydate == '')
    $modifydate = $createdate;
  # there was a problem with the order at the last payment attempt or it has been reversed or something or pending or it hasn't updated from Paypal yet
  $unpaid = substr($reason, 0, 7) == 'COMMWEB' ? '' : $reason;
  $lastupdate = strftime("%B %e, %Y", $modifydate);
}
# calculate pricing
$priceextax = $_SESSION['orderextaxamount'];
$discamount = $_SESSION['orderdiscountamount'];
$displaydiscamount = $_SESSION['orderdiscountamount'] * (1 + TAX);
$extaxtotal = $_SESSION['extaxtotal'] = round($priceextax - $discamount, 2);
$taxamount = $extaxtotal * TAX;
$inctaxtotal = $_SESSION['inctaxtotal'] = $extaxtotal + $taxamount;


# hide or show discount sections based upon whether a discount is 0 or not
$displayCouponBlock = true;
$displayDiscountRow = $discamount != 0;

# check for expired order
$expirydate = strtotime($paiddate . ' +' . PRODUCTEXPIRY . ' month');
if ($paid && time() > $expirydate) {
  # order is expired show a message explaining that the time period is up and they will need to pay again.
  $displayexpired = strftime("%B %e, %Y", strtotime($paiddate));
}

# if the payment amount is zero don't bother going to payment provider just locally update the order and go to download
$pricezero = ($inctaxtotal == 0 || $_SESSION['admin'] == 1) ? true : false;

# setup the CBA session.js file to use for commweb payment
$sessionjs = "https://paymentgateway.commbank.com.au/form/version/100/merchant/" . CBA_MERCHANTID . "/session.js";

# initialize CBA session and get session ID
initiateCBASession();

$title = "Purchase Your Will Documents";
$desc = "Order your will from " . SITENAME . ".";
$keywords = "purchase will, order last will and testament, online will, online wills, internet wills, online will kit";

require("pageincludes/header.php");

?>
<section class="container">
  <h1><i class="glyphicon glyphicon-shopping-cart"></i> Purchase</h1>
  <?php if ($willDataIncomplete && $_SESSION['product'] == 'singlewill') { ?>
    <p class="alert alert-warning">
      <b>Please note</b> You may go ahead and complete payment for your Will now, but before you download the will
      documents after payment,
      you will need to complete the required information for the Will. This includes adding:<br> <br>
      - all the beneficiaries,<br>
      - at least one executor,<br>
      - and the portions of your estate that will go to your beneficiairies.<br> <br>
      <a href="singlewill.html" class="btn btn-default"><i class="glyphicon glyphicon-edit"></i> Edit Now</a>
    </p>
  <?php } ?>
  <?php if (false && isset($displayexpired)) { ?>
    <p class="alert alert-warning">
      Your last order was placed on <span class='highlight'><?php echo $displayexpired; ?></span> and
      it has now passed the <?php echo PRODUCTEXPIRY; ?> month expiry time.
      To download another copy of your will, you will need to pay again. Thank you.
    </p>
  <?php } ?>
  <?php if (false && isset($unpaid) && $unpaid > '') { ?>
    <p class="alert alert-warning">
      Your order currently has a status of <span class='text-primary'><?php echo $unpaid; ?></span>.
      The order was last updated <span class='text-primary'><?php echo $lastupdate; ?></span>.
      Before you can download your will, the payment will have to be successfully completed. Thank you.
    </p>
  <?php } ?>
  <form id="orderForm" method="post" class="form-horizontal" role="form" action="includes/processpayment.php">
    <!--           <input id="paytype" type="hidden" name="paytype" value="<?php echo isset($_SESSION['paytype']) ? $_SESSION['paytype'] : ''; ?>"> -->
    <input id="paytype" type="hidden" name="paytype" value="commweb">
    <span id="orderid" style="display: none;"><?php echo $_SESSION['orderid']; ?></span>
    <fieldset>
      <legend>Order Details</legend>
      <table class='table'>
        <thead>
          <tr>
            <th>Product</th>
            <th class='currency'>Price</th>
          </tr>
        </thead>
        <tbody>
          <?php
          # product ordered display
          $mirrorwill = false;
          krsort($orderData['product']);
          foreach ($orderData['product'] as $product => $yesno) {
            if ($yesno == 'Yes') {
              $mirrorwill = $product == 'mirrorwill' ? true : $mirrorwill;
              $productname = $_SESSION['products'][$product]['name'] . ' for ' . ($product == 'mirrorwill' ? $orderData['spouse']['fullname'] : $orderData['person']['fullname']);
              $productname .= ($mirrorwill && $product == 'enduring') ? ' and ' . $orderData['spouse']['fullname'] : '';
              #$itemprice = $_SESSION['products'][$product]['priceinctax'] / (1 + TAX);
              $itemprice = $_SESSION['products'][$product]['priceinctax'];
              ?>
              <tr>
                <td><?php echo $productname; ?></td>
                <td class='currency'><?php echo $itemprice > 0 ? currency($itemprice) : 'FREE'; ?></td>
              </tr>
              <?php
            }
          }
          ?>
          <?php
          if ($displayDiscountRow) {
            $discountreason = strpos(strtoupper($_SESSION['affiliateName']), 'SALE') === false ? "Introduced to " . SITENAME . " by " . $_SESSION['affiliateName'] : $_SESSION['affiliateName'];
            ?>
            <tr>
              <td><?php echo percent($_SESSION['discount']); ?> discount <em>(<?php echo $discountreason; ?>)</em></td>
              <td class='currency'>-<?php echo currency($displaydiscamount); ?></td>
            </tr>
            <?php
          }
          ?>
          <tr>
            <th class="total">Total</th>
            <td class="currency total"><?php echo currency($inctaxtotal); ?></td>
          </tr>
          <?php
          if (TAX != 0) {
            ?>
            <tr>
              <td class="total" colspan="2" style="padding-top: 50px;"><em>This total includes <?php echo TAXNAME; ?> of
                  <?php echo currency($taxamount); ?> (<?php echo SITECOMPANY; ?> - ABN: <?php echo SITEABN; ?>)</em></td>
            </tr>
            <?php
          }
          ?>
        </tbody>
      </table>
      <?php if (!$pricezero) { ?>
        <hr>
        <?php if (CBA_MERCHANTID > '') { ?>
          <!--
            <h2>Please select a payment method:</h2>
            <div class="row">
              <div id="polibtn" class="paybtn col-sm-6"></div>
              <div id="cbabtn" class="paybtn col-sm-6"></div>
              <div id="paypalbtn" class="paybtn col-sm-4"></div>
            </div>
-->
          <br id="paymentmethod">
          <script type="text/javascript" src="<?php echo $sessionjs; ?>"></script>
          <style id="antiClickjack">
            body {
              display: none !important;
            }
          </style>
          <div id="cc">
            <p style="margin-bottom: 30px;">
              Please enter <img src="images/visa.gif" alt="VISA"> or <img src="images/masterc.gif" alt="Mastercard"> details
              in the secure form below.
            </p>
            <p style="color: #6f3269; font-style: italic; margin-bottom: 25px;">
              Please note that we do not retain your card details, they are passed directly to the bank for payment
              processing only.
            </p>
            <?php if (isset($_SESSION['ccfail']) && $_SESSION['ccfail'] != '') { ?>
              <p class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['ccfail']); ?>
              </p>
            <?php } ?>
            <div class="form-group">
              <label for="card-number" class="control-label col-sm-3">Card Number</label>
              <div class="col-sm-9" style="margin-top: 5px; height: 30px;">
                <input type="text" id="card-number" class="input-field" title="card number"
                  aria-label="enter your card number" value="" tabindex="1" readonly>
              </div>
            </div>
            <div class="form-group">
              <label for="expiry-month" class="control-label col-sm-3">Expiry Month</label>
              <div class="col-sm-9" style="margin-top: 5px; height: 30px;">
                <input type="text" id="expiry-month" class="input-field" title="expiry month"
                  aria-label="two digit expiry month" value="" tabindex="2" readonly>
              </div>
            </div>
            <div class="form-group">
              <label for="expiry-year" class="control-label col-sm-3">Expiry Year</label>
              <div class="col-sm-9" style="margin-top: 5px; height: 30px;">
                <input type="text" id="expiry-year" class="input-field" title="expiry year"
                  aria-label="two digit expiry year" value="" tabindex="3" readonly>
              </div>
            </div>
            <div class="form-group">
              <label for="security-code" class="control-label col-sm-3">CCV Code (3-digit)</label>
              <div class="col-sm-9" style="margin-top: 5px; height: 30px;">
                <input type="text" id="security-code" class="input-field" title="security code"
                  aria-label="three digit CCV security code" value="" tabindex="4" readonly>
              </div>
            </div>
            <div class="form-group">
              <label for="cardholder-name" class="control-label col-sm-3">Cardholder Name</label>
              <div class="col-sm-9" style="margin-top: 5px; height: 30px;">
                <input type="text" id="cardholder-name" class="input-field" title="cardholder name"
                  aria-label="enter name on card" value="" tabindex="5" readonly>
              </div>
            </div>
            <div class="form-group">
              <label for="ccsubmit" class="control-label col-sm-3"></label>
              <div class="col-sm-2">
                <button type="button" class="btn btn-warning form-control" id="payButton" onclick="pay(event);">Process
                  Payment</button>
              </div>
            </div>
          </div>

          <script type="text/javascript">

            if (self === top) {
              var antiClickjack = document.getElementById("antiClickjack");
              antiClickjack.parentNode.removeChild(antiClickjack);
            } else {
              top.location = self.location;
            }

            PaymentSession.configure({
              session: '<?php echo $_SESSION['sessionId']; ?>',
              interaction: {
                merchant: {
                  name: "<?php echo SITENAME; ?>",
                }
              },
              fields: {
                // ATTACH HOSTED FIELDS TO YOUR PAYMENT PAGE FOR A CREDIT CARD
                card: {
                  number: "#card-number",
                  securityCode: "#security-code",
                  expiryMonth: "#expiry-month",
                  expiryYear: "#expiry-year",
                  nameOnCard: "#cardholder-name"
                }
              },
              //SPECIFY YOUR MITIGATION OPTION HERE
              frameEmbeddingMitigation: ["javascript"],
              callbacks: {
                initialized: function (response) {
                  // HANDLE INITIALIZATION RESPONSE
                },
                formSessionUpdate: function (response) {
                  if (response.status) {
                    if ("ok" == response.status) {
                      // Submit the form to proceed with payment
                      document.getElementById('orderForm').submit();
                    } else if ("fields_in_error" == response.status) {
                      console.log("Session update failed with field errors.");
                      if (response.errors.cardNumber) {
                        console.log("Card number invalid or missing.");
                      }
                      if (response.errors.expiryYear) {
                        console.log("Expiry year invalid or missing.");
                      }
                      if (response.errors.expiryMonth) {
                        console.log("Expiry month invalid or missing.");
                      }
                      if (response.errors.securityCode) {
                        console.log("Security code invalid.");
                      }
                    } else if ("request_timeout" == response.status) {
                      console.log("Session update failed with request timeout: " + response.errors.message);
                    } else if ("system_error" == response.status) {
                      console.log("Session update failed with system error: " + response.errors.message);
                    }
                  } else {
                    console.log("Session update failed: " + response);
                  }
                },
              },
              interaction: {
                displayControl: {
                  formatCard: "EMBOSSED",
                  invalidFieldCharacters: "REJECT"
                }
              }

            });

            function pay(e) {
              e.preventDefault();
              PaymentSession.updateSessionFromForm('card');
            }

          </script>
          <br>
        <?php } else { ?>
          <br>
          <button id="paypalbtn" class="btn btn-default">Pay with &nbsp;<img src="images/paypallogo.png"
              alt="Pay with Paypal"></button>
        <?php } ?>
      <?php } else { ?>
        <input type="submit" class="btn btn-success" value="Download Documents">
      <?php } ?>
      <input type="button" id="cancelOrder" class="btn btn-default" value="Cancel">
      <br>
      <?php if ($displayCouponBlock) { ?>
        <hr>
        <p>
          <small><em>If you have a valid discount code, you may enter it here and click the button to recalculate the
              total.</em></small>
        </p>
        <div class="input-group input-group-sm col-sm-2">
          <input id="couponCode" name="couponCode" type="text" class="form-control" placeholder="coupon code">
          <span class="input-group-btn">
            <button id="couponCalc" name="couponCalc" class="btn btn-default">OK</button>
          </span>
        </div>
      <?php } ?>
      <br>
    </fieldset>
  </form>
</section>
<?php
if (isset($_SESSION['ccfail'])) {
  unset($_SESSION['ccfail']);
  echo "<script>$('html,body').animate({ scrollTop: $('#paymentmethod').offset().top }, 'fast');</script>";
}
require("pageincludes/footer.php");
?>