<?php

/*
header("Location: /start.html");
exit;
*/

# check for clearform
if (isset($_SESSION['getVars']['cleardata'])) {
  unset($_SESSION['qwOrderData']);
  unset($_SESSION['getVars']);
  header("Location: /quickwill.html");
  exit;
}

# look for data in the session data to prepopulate the form with
$order = $_SESSION['qwOrderData'];


if ($_SESSION['products']['quickwill']['priceinctax'] == 0) {
  $title = "Free Online Will for all Australians · " . SITENAME;
  $desc = 'Completely Free · Legal · Secure · Australian · Not A Draft · Australian Business · Highly Rated';
  $keywords = "free wills, wills, free will kit, free last will and testament, free online will, free online wills, free internet wills, free online will kit";
} else {
  $title = "Best Value Will Kit for Australians · " . SITENAME;
  $desc = 'Legal · For Australians · By Australians · Secure · Highly Rated';
  $keywords = "will, wills, will kit, last will and testament, online will, online wills, internet wills, online will kit";
}

require("pageincludes/header.php");
?>
      <section class="container">
        <h1>Quick Will for all Australians</h1>
        <p>
          This is the quickest and most comprehensive DIY Will Kit in Australia<?php echo $_SESSION['products']['quickwill']['priceinctax'] == 0 ? ", and it's <u><b>FREE</b></u>" : "" ?>.
        </p>
        <p>  
          If you know what you wish to leave to who in your will, then it should not take you longer than 10 minutes to complete.
        </p>
        <p class="highlight">
          You do not need to register and login with your details to complete our Quick Will.<br>
          Just complete this page to download (or email yourself) your will.
        </p>
      </section>
      <section class="container" id="singlewill">
        <h2>Complete Your Quick Will</h2>
        <?php //echo "<pre>" . print_r($order, true) . "</pre>";?>
        <p>
          Your will is a legal document. This Quick Will clearly lays out your wishes for how you would like your assets distributed after your death. You can nominate specific gifts to be received by beneficiaries, and 
          you can name beneficiaries who will receive a share of your remaining estate.
        </p>
        <p>
          If you need greater flexibility in creating your will, then we recommend that you try our
          <a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'products.html';?>">Premium Will</a>.
        </p>
        <p>
        	Please complete the information requested in the fields below. Fields marked with a red asterisk (<span class="text-danger">*</span>) are required fields.
        </p>
        <p>
        	At the bottom of this screen are three buttons. The first will send you an email with your completed will attached, the second will download your completed will to your computer or device, the third will clear the form to let you create another will.
        </p>
        <form id="quickwillform" method="post" action="" role="form">
          <input id="formID" name="formID" type="hidden" value="quickwill">
          <input id="orderid" name="orderid" type="hidden" value="<?php echo isset($order['orderid']) ? $order['orderid'] : '';?>">
          <input id="txnid" name="txnid" type="hidden" value="<?php echo isset($order['txnid']) ? $order['txnid'] : '';?>">
          <input id="tcstatus" name="tcstatus" type="hidden" value="<?php echo isset($order['tcstatus']) ? $order['tcstatus'] : '';?>">
          <input id="orderamount" name="orderamount" type="hidden" value="<?php echo $_SESSION['products']['quickwill']['priceinctax']; ?>">
          <input id="quickwillfor" name="quickwillfor" type="hidden" value="<?php echo isset($order['quickwillfor']) ? $order['quickwillfor'] : $order['testatorFullName'];?>">
<?php if ($_SESSION['admin']) :?>
          <input id="administrator" name="administrator" type="hidden" value="true">
<?php endif;?>
          <fieldset id="person">
            <legend>Your Details</legend>
            <div class="form-group">
              <label for="testatorFullName"><a data-toggle="popover" data-trigger="hover" title="Testator" data-content="<p>The <em>testator</em> is the person who the will is for. That is, the will maker.</p>">Testator's</a> Full Name:</label>
              <input id="testatorFullName" name="testatorFullName" type="text" class="form-control propercase required person name" value="<?php echo isset($order['testatorFullName']) ? $order['testatorFullName'] : ''; ?>" autofocus>
            </div>
            <div class="form-group">
              <label for="testatorState">
                <a data-toggle="popover" data-trigger="hover" title="State" data-content="<p>This is the state or territory where you reside. The laws of this state or territory will be applicable to and govern your Will.</p>">State:</a>
              </label>
              <input id="testatorState" name="testatorState" type="text" class="form-control uppercase required state" value="<?php echo isset($order['testatorState']) ? $order['testatorState'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="testatorDOB">Date of Birth:</label>
              <input id="testatorDOB" name="testatorDOB" type="text" class="form-control dateAU" value="<?php echo isset($order['testatorDOB']) ? $order['testatorDOB'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="testatorEmail">Email address:</label>
              <input id="testatorEmail" name="testatorEmail" placeholder="Your Will is emailed to this address..." type="email" class="form-control email required lowercase" value="<?php echo isset($order['testatorEmail']) ? $order['testatorEmail'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="testatorPhone">Phone:</label>
              <input id="testatorPhone" name="testatorPhone" placeholder="Your phone number" type="text" class="form-control fullphone" value="<?php echo isset($order['testatorPhone']) ? $order['testatorPhone'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="testatorAddress">Full Residential Address: <small>(Not a PO Box)</small></label>
              <textarea id="testatorAddress" name="testatorAddress" rows="4" class="form-control required propercase notpobox" placeholder='Full address...' ><?php echo isset($order['testatorAddress']) ? $order['testatorAddress'] : ''; ?></textarea>
            </div>
          </fieldset>

<!-- Executor -->
          <fieldset id="executor">
            <legend>Executor Details <small>(You must appoint at least one <a data-toggle="popover" data-trigger="hover" title="Executor" data-content="The <em>executor</em> is responsible for the administration
              of your estate, ensuring that funeral and
              administration expenses are taken care of, that any debts and taxes are paid,
              and that the assets and property are distributed to the beneficiaries according to your wishes.">Executor</a>)</small>
            </legend>
            <h3>1st Choice Executor:</h3>
            <div class="form-group">
              <label for="executorFullName1">Full Name:</label>
              <input id="executorFullName1" name="executorFullName1" type="text" class="form-control required propercase name" value="<?php echo isset($order['executorFullName1']) ? $order['executorFullName1'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="executorAddress1">Full Address:</label>
              <textarea id="executorAddress1" name="executorAddress1" rows="4" class="form-control required propercase"><?php echo isset($order['executorAddress1']) ? $order['executorAddress1'] : ''; ?></textarea>
            </div>
            <hr>
            <h3>2nd Choice Executor (optional):</h3>
            <div class="form-group">
              <label for="executorFullName2">Full Name:</label>
              <input id="executorFullName2" name="executorFullName2" type="text" class="form-control propercase name" value="<?php echo isset($order['executorFullName2']) ? $order['executorFullName2'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="executorAddress2">Full Address:</label>
              <textarea id="executorAddress2" name="executorAddress2" rows="4" class="form-control propercase"><?php echo isset($order['executorAddress2']) ? $order['executorAddress2'] : ''; ?></textarea>
            </div>
          </fieldset>

<!-- Guardian -->
          <fieldset>
            <legend><a data-toggle="popover" data-trigger="hover" title="Guardian" data-content="A <em>legal guardian</em> is the person (or persons - often a couple) who will have the long term responsibility of a child and who has all the rights, powers and duties of a parent.">Guardians</a> <small>To look after your children (if you have any)</small></legend>
            <div class="form-group">
              <label for="hasChildrenYoung">
                Do you have children <a data-toggle="popover" data-trigger="hover" data-content="Children who are under 18 years of age will need a guardian identified in the will.">under 18 years</a> of age?
              </label>
              <input id="hasChildrenYoung" name="hasChildrenYoung" type="hidden" class="required" value="<?php echo isset($order['hasChildrenYoung']) ? $order['hasChildrenYoung'] : '';?>">
              <br>
              <div class="btn-group">
                <button id="hasChildrenYoungYes" class="btn btn-default <?php if (isset($order['hasChildrenYoung']) && $order['hasChildrenYoung'] == 'Yes') echo 'active'; ?>" >Yes</button>
                <button id="hasChildrenYoungNo" class="btn btn-default <?php if (isset($order['hasChildrenYoung']) && $order['hasChildrenYoung'] == 'No') echo 'active'; ?>" >No</button>
              </div>
            </div>
            <div id="guardian" style="display: <?php echo (isset($order['hasChildrenYoung']) && $order['hasChildrenYoung'] == 'Yes') ? 'block' : 'none';?>;">
              <p>The appointment of a guardian usually only operates if the children have no surviving parent.</p>
              <p>A guardian does not have to be related to the children, but it is wise to select a guardian who you know well and who you trust will care for your children as you would do.</p>
              <p>Your chosen guardian needs to agree to the role before your will is finalised.</p>
              <h3>I would like the person listed below to care for my young children:</h3>
              <div class="form-group">
                <label for="guardianFullName">Guardian Full Name:</label>
                <input id="guardianFullName" name="guardianFullName" type="text" class="form-control propercase name" value="<?php echo isset($order['guardianFullName']) ? $order['guardianFullName'] : ''; ?>">
              </div>
              <div class="form-group">
                <label for="guardianAddress">Full Address:</label>
                <textarea id="guardianAddress" name="guardianAddress" rows="4" class="form-control required propercase address"><?php echo isset($order['guardianAddress']) ? $order['guardianAddress'] : ''; ?></textarea>
              </div>
            </div>
          </fieldset>

<!-- Gifts -->
          <fieldset>
            <legend>Specific Gifts <small>(optional)</small></legend>
            <p>
            	Enter each specific gift on a new line, and include details of the gift, who it should go to and who should get it if the first person dies before you.
            </p>
            <p>
            	<em>Start each additional gift on a new line.</em> You can add as many as needed.
            </p>
            <div class="form-group">
              <label for="specificGifts">
                Gift Details:
                <small><a class="assist" assist="gifts"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small>
              </label>
              <textarea id="specificGifts" name='specificGifts' class="form-control" rows="10" placeholder="Example: I leave my gold watch to my son, John Michael Smith. If John dies before I do, then it I leave to my friend Robert John James." ><?php echo isset($order['specificGifts']) ? $order['specificGifts'] : ''; ?></textarea>
            </div>
          </fieldset>


<!-- Estate -->
          <fieldset>
            <legend>Distribution of the Remainder of your Estate</legend>
            <p>
              This section is all about dividing up the
              <a data-toggle="popover" data-trigger="hover" title="Remainder of Estate" data-content="The remainder of your estate is known as the <em>residuary</em> in your will. It is what is left of your estate after any taxes and debts are paid, and after any specific gifts are distributed.">remainder</a>
              of your estate. Once your debts and liabilities are taken care of, and your <em>specific gifts</em> are distributed, the remainder of your estate will be divided
              among the following beneficiaries.
            </p>
            <p>
              <strong><em>Regardless of how many beneficiaries you enter here, make sure the total of all shares adds up to 100%.</em></strong>
            </p>
<?php
# setup the beneficiaries fields
$lastBeneficiary = count($order['estate']) ? count($order['estate']) : 1;
for($i = 1; $i < 12; $i++) {
?>
            <div id="beneficiary<?php echo $i;?>" class="beneficiary"<?php if ($i > 1 && !isset($order['estate'][$i]['recipient'])) echo ' style="display:none;"';?>>
              <h3>Beneficiary <?php echo $i;?>:<?php if ($i > 1) echo " <small>(Optional)</small>";?></h3>
              <div class="row">
                <div class="form-group col-sm-6">
                  <label for="estateRecipient<?php echo $i;?>">Beneficiary Full Name:</label>
                  <input id="estateRecipient<?php echo $i;?>" name="estate[<?php echo $i;?>][recipient]" type="text" class="form-control propercase recipient<?php if ($i == 1) echo " required";?>" value="<?php echo isset($order['estate'][$i]['recipient']) ? $order['estate'][$i]['recipient'] : ''; ?>">
                </div>
                <div class="form-group col-sm-3">
                  <label for="estateRelationship<?php echo $i;?>">Relationship:</label>
                  <input id="estateRelationship<?php echo $i;?>" name="estate[<?php echo $i;?>][relationship]" type="text" class="form-control lowercase relationship" value="<?php echo isset($order['estate'][$i]['relationship']) ? $order['estate'][$i]['relationship'] : ''; ?>">
                </div>
                <div class="form-group col-sm-3">
                  <label for="estateShare<?php echo $i;?>">Share (%):</label>
                  <input id="estateShare<?php echo $i;?>" name="estate[<?php echo $i;?>][share]" type="text" class="form-control estateshare<?php if ($i == 1) echo " required";?>" value="<?php echo isset($order['estate'][$i]['share']) ? $order['estate'][$i]['share'] : ''; ?>">
                </div>
              </div>
              <div class="form-group">
                <label for="estateShareAlternate<?php echo $i;?>">
                  What should happen if this person dies before me?:
                  <small><a class="assist" assist="estate"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small>
                </label>
                <textarea id="estateShareAlternate<?php echo $i;?>" rows="6" name="estate[<?php echo $i;?>][altrecipient]" class="form-control" placeholder="Details of what should happen if this beneficiary dies before you do."><?php echo isset($order['estate'][$i]['altrecipient']) ? $order['estate'][$i]['altrecipient'] : ''; ?></textarea>
              </div>
              <?php if ($i == $lastBeneficiary): ?>
              <div id="beneficiarycontrols">
                <a id="removethisbeneficiary" class="btn btn-default btn-xs pull-right" style="display: <?php echo ($i > 1) ? 'block' : 'none';?>">Remove this beneficiary</a>
                <a id="addanotherbeneficiary" class="btn btn-default btn-xs">Add another beneficiary</a>
              </div>
              <?php endif; ?>
              <hr>
            </div>
<?php
}
?>
          </fieldset>

<!-- Requirements -->
          <fieldset>
            <legend>Personal Requirements</legend>
            <p>
              This section gives you space to let your executor and beneficiaries know of personal
              requirements you may have, such as funeral arrangements and organ donation wishes. You 
              may also have general requirements, for example, a song that you would like played at
              your funeral ceremony.
            </p>
            <p>
            	<em>Start each additional requirement on a new line.</em> You can add as many as needed.
            </p>
            <div class="form-group">
              <label for="requirementsDetail">
                Requirements:
                <small><a class="assist" assist="requirements"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small>
              </label>
              <textarea id="requirements" name="requirements" rows="10" class="form-control" placeholder="Enter your personal requirements details…"><?php echo isset($order['requirements']) ? $order['requirements'] : ''; ?></textarea>
            </div>
          </fieldset>
          <hr>
<!-- Preview and Purchase -->          
          <div id="formButtons" style="display: block; margin-top: 20px;">
<?php
if ((isset($order['tcstatus']) && ($order['tcstatus'] == 'Approved' || $order['tcstatus'] == 'Completed')) || ($_SESSION['products']['quickwill']['priceinctax'] == 0)) {  
?>
            <button id="emailWill" class="btn btn-success" style="margin-top: 5px;"><i class='glyphicon glyphicon-envelope'></i> Email Will</button>
            <button id="downloadWill" class="btn btn-primary" style="margin-top: 5px;"><i class='glyphicon glyphicon-download-alt'></i> Download Will</button>
<?php
} else {  
?>
            <button id="previewWill" class="btn btn-info" style="margin-top: 5px;"><i class='glyphicon glyphicon-search'></i> Preview Will</button>
            <button id="purchaseWill" class='pull-right btn btn-success' style="margin: 5px 0 0 5px;"><i class='glyphicon glyphicon-shopping-cart'></i> Purchase</button>
<?php
}
?>
            <button id="clearWill" class="btn btn-danger pull-right" style="margin-top: 5px;"><i class='glyphicon glyphicon-trash'></i> Clear</button>
          </div>
          <br class="clear">
        </form>
      </section>
      <div id=estateAssistHTML style='display: none;'>
        <p class=assistclause>If [[Recipient]] dies before I do, or within 30 days of my passing, it is my wish that this share of my estate be distributed, in equal shares to NAME1, NAME2, NAME3......</p>
        <p class=assistclause>If [[Recipient]] dies before or around the same time as I do, it is my wish that this share of my estate be given instead to NAME2. And, if NAME2 dies before I do, then the share should be given to NAME3.</p>
      </div>
      <div id=giftsAssistHTML style='display: none;'>
        <p class=assistclause>I leave my gold Rolex watch to my son NAME. If NAME dies before I do, then I would like the watch to be given to my daughter NAME.</p>
        <p class=assistclause>I leave a gift of $20,000 cash to my niece NAME. If NAME dies before I do, then I would like this gift to be held in trust for her children and divided in equal shares to them when they reach the age of 18 years.</p>
        <p class=assistclause>I leave my house at 12 Somewhere Street, Somewhere QLD 4234 to my daughter, NAME. If NAME dies before I do, then this property should be included with the rest of my estate which will be divided as set out below.</p>
      </div>
      <div id=requirementsAssistHTML style='display: none;'>
        <p class=assistclause>It is my wish that my viable organs be donated and that my remains are cremated.</p>
        <p class=assistclause>I would like SONGNAME played at my funeral service.</p>
      </div>
<?php
require("pageincludes/footer.php");
?>