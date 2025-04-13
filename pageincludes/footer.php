    </div>
    <!-- end of page content -->

    <!-- info and links -->
    <div id="foot">
      <div id="bottom">
        <div class="row footerlinks">
<!--         <div class="row" style="width: 80%; margin: 20px auto;"> -->
          <div class="col-sm-4">
            <h2>Links</h2>
  <?php if (QUICKWILL && $_SESSION['quickwill']) {?>
            <a href="start.html">Create a Premium Will</a><br>
            <a href="quickwill.html">Create a Quick Will</a><br>
            <a href="products.html">Quick Will compared to Premium Will</a><br>
  <?php } else {?>
            <a href="start.html">Create a Will</a><br>
  <?php }?>
            <a href="contact.html">Contact Us</a><br>
            <a href="ourcustomers.html">Our customer feedback</a><br>
  <?php if (false && $_SESSION['products']['singlewill']['priceinctax'] != 0) { ?>            
            <a href="affiliateprogram.html">Affiliate Program</a><br>
  <?php }?>
            <a href="terms.html">Terms of Use</a><br>
            <a href="privacy.html">Privacy Statement</a>
          </div>
          <div class="col-sm-4">
            <h2>More information</h2>
            <a href="faq.html">Frequently Asked Questions</a><br>
            <a href="how-to-make-a-will-online.html">How to make a Will online</a><br>
            <a href="why-you-need-a-will.html">Why do I need a Will?</a><br>
            <a href="changewill.html">When should I change my Will?</a><br>
            <a href="willguidelines.html">Guidelines for writing a Will</a><br>
            <a href="enduring-power-of-attorney.html">Enduring Power of Attorney</a><br>
            <a href="about-epog.html">Enduring Guardianships</a><br>
            <a href="famous-people-intestate.html">Famous people with no Will</a>
          </div>
          <div class="col-sm-4"><img style="margin-top:60px;" src="images/mc_visa.png" alt="Pay with Mastercard and VISA" /></div>
        </div>        
<?php if (false && $_SESSION['products']['singlewill']['priceinctax'] != 0) { ?>
        <div class="row paytypes">
          <div class="col-sm-4 text-center"><a href="https://www.polipayments.com/Buy" rel="nofollow" title="How POLi works" target="poli"><img src="images/poli.png" alt="Pay with POLi" /></a></div>
          <div class="col-sm-4 text-center"><img src="images/mc_visa.png" alt="Pay with Mastercard and VISA" /></div>
          <div class="col-sm-4 text-center"><a href="https://www.paypal.com/au/webapps/mpp/paypal-popup" rel="nofollow" title="How PayPal Works" target="paypal"><img src="images/paypal.png" alt="Pay with Paypal" /></a></div>
        </div>
<?php }?>
      </div>
  
      <!-- footer -->
      <div id="footer">
        <div class="navbar navbar-inverse">
          <p class="navbar-text text-center" style="width: 100%;"><a href="/" title="<?php echo SITECOMPANY;?>"><?php echo SITECOMPANY; ?></a> Copyright &copy; <?php echo date("Y");?></p>
        </div>
      </div>
    </div>
      <!-- end of footer -->
<?php
if (!isset($_SESSION['clientCountry']) || $_SESSION['clientIP'] != $_SERVER['REMOTE_ADDR']) {
  // have they just connected or is the IP different
  $_SESSION['clientIP'] = $_SERVER['REMOTE_ADDR'];
}
?>
  </body>
</html>
