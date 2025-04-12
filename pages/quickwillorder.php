<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$qwOrderData = $_SESSION['qwOrderData'];

$title = "Purchase your Will";
$desc = "Purchase your will from {$_SESSION['sitename']} .";
$keywords = "frequently asked questions, making a will, questions about having a will";
$taxincprice = currency($_SESSION['products']['quickwill']['priceinctax']);
$taxexprice = $_SESSION['products']['quickwill']['priceinctax']/(1 + TAX);
$product = $_SESSION['products']['quickwill']['name'];
$tax = currency($taxexprice * TAX);
$requiredfield = $_SESSION['admin'] ? '' : 'required';

require("pageincludes/header.php");
?>
      <section class="container">
        <h2><i class="glyphicon glyphicon-shopping-cart"></i> QuickWill Purchase</h2>
        <fieldset>
          <table class='table'>
            <thead>
              <tr>
                <th>Product</th>
                <th class='currency'>Price</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo $product . ' for '. $qwOrderData['testatorFullName'];?></td>
                <td class='currency'><?php echo $taxincprice;?></td>
              </tr>
              <tr>
                <td class="total" colspan="2" style="padding-top: 5px;"><span class="pull-right"><em><small>Total includes GST of <?php echo "$tax ({$_SESSION['sitecompany']} - ABN: {$_SESSION['siteabn']})";?></small></em></span></td>
              </tr>
            </tbody>
          </table>
        </fieldset>
        <fieldset>
          <legend>Payment Details</legend>
          <p style="margin-bottom: 30px;">
            We accept <img src="images/masterc.gif" alt="Mastercard"> and  <img src="images/visa.gif" alt="VISA"> cards only.
          </p>
          <p class="small text-success text-center">
            <strong><em>We do not retain your card details, they are passed directly to the bank for payment processing only.</em></strong>
          </p>
          <form id="orderForm" method="post" class="form-horizontal" role="form" action="includes/processpaymentqw.php">
            <div class="form-group">
              <label for="ccnumber" class="col-sm-3 control-label">Card Number</label>
              <div class="col-sm-7">
                <input id="ccnumber" name="ccnumber" type="text" class="form-control <?php echo $requiredfield;?> ccnumber creditcard">
              </div>
            </div>
            <div class="form-group">
              <label for="ccexpiry" class="col-sm-3 control-label">Expiry Date</label>
              <div class="col-sm-7">
                <input id="ccexpiry" name="ccexpiry" type="text" class="form-control <?php echo $requiredfield;?> ccexpiry">
              </div>
            </div>              
            <div class="form-group">
              <label for="ccv2" class="col-sm-3 control-label">CCV Code (3-digit)</label>
              <div class="col-sm-7">
                <input id="ccv2" name="ccv2" type="text" class="form-control <?php echo $requiredfield;?> ccv2">
              </div>
            </div>              
            <div class="form-group">
              <label for="ccname" class="col-sm-3 control-label">Cardholder Name</label>
              <div class="col-sm-7">
                <input id="ccname" name="ccname" type="text" class="form-control <?php echo $requiredfield;?> propercase" value="">
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-7">
                <span class="text-danger"><strong><?php echo isset($_SESSION['ccfail']) ? $_SESSION['ccfail'] : '&nbsp;'; unset($_SESSION['ccfail']);?></strong></span>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-7">
                <input id="ccsubmit" name="ccsubmit" type="submit" class="btn btn-warning form-control" value="Process Payment">
                <input id="cancelOrder" type="button" class="btn btn-default" value="Cancel">                
              </div>
            </div>
          </form>
        </fieldset>
        <br>
      </section>
<?php
require("pageincludes/footer.php");
?>