<?php

# check that page is not accessed directly and redirect if so
$qwOrderData = $_SESSION['qwOrderData'];
$txnid = $qwOrderData['txnid'];
$name = $qwOrderData['testatorFullName'];
if (!isset($txnid)) goHome(); # go to home page if no valid txnid
  
require("pageincludes/header.php");
?>
    <section class="container">
      <h1 class="text-success">Thank you for your order</h1>
      <p>
        You have successfully paid for your order.
      </p>
      <p>
        Thank you for your payment. Your transaction has been completed and a receipt for your purchase has been emailed to you.
      </p>
			<p>
				You may download the document package for <?php echo $name;?> right now, or return to 
				make changes to the information entered and then download. <em class="highlight2">You may edit the information and 
				download the documents as required during this session.</em>.
			</p>
			<p class="text-success">
  			<strong><em>
  			  Please make sure that you click the download button on this screen or on the bottom of the edit screen to get your Will.
  			</em></strong>
			</p>
      <div class="row" style="margin-bottom: 120px;">
        <div class="col-sm-6"  style="margin-bottom: 20px;">
          <a id="downloadWill" href="quickwilldownload.html" target="Download" class="btn btn-success">Download Will for <?php echo $name;?></a>
        </div>
        <div class="col-sm-6">
          <a href="/quickwill.html" class="btn btn-default">Return to Edit Information</a>
        </div>
      </div>
    </section>
    <script type="text/javascript">
    /* <![CDATA[ */
    var google_conversion_id = 1034765843;
    var google_conversion_language = "en";
    var google_conversion_format = "3";
    var google_conversion_color = "ffffff";
    var google_conversion_label = "T5r7CPfEmnEQk4y17QM";
    var google_remarketing_only = false;
    /* ]]> */
    </script>
    <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
    </script>        
<?php
require("pageincludes/footer.php");


?>