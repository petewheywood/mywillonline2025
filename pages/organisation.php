<?php
# page for registering new organisation - based upon user registration
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$title = "Register Organisation as a " . SITENAME . " partner";
$desc = "Register your organsation as a partner of " . SITENAME;
$keywords = "partner organisation";

require("pageincludes/header.php");
?>
        <section class="container">
          <h1>New Organisations Register</h1>
          <div class="bordered">
            <h3><i class="glyphicon glyphicon-play text-info"></i>Register as a <?php echo $_SESSION['sitename'];?> partner by completing the information below</h3>
            <br>
            <form role="form" id="orgRegisterForm" method="post" action="includes/registerorganisation.php" autocomplete="off" enctype="multipart/form-data">
              <div class="form-group">
                <label for="firstname">Organisation Name:</label>
                <input id="orgname" name="orgname" type="text" placeholder="Organisation name" class="form-control required propercase" autofocus value="<?php echo $_SESSION['orgname'];?>">                    
              </div>
              <div class="form-group">
                <label for="abn">Australian Business number (ABN):</label>
                <input id="abn" name="abn" type="text" placeholder="ABN number" class="form-control required abn" value="<?php echo $_SESSION['abn'];?>">
              </div>
              <div class="form-group">
                <label for="phone">Phone number:</label>
                <input id="phone" name="phone" type="tel" placeholder="Phone number" class="form-control required anyphone" value="<?php echo $_SESSION['phone'];?>">
              </div>
              <hr>              
              <div class="address">
                <div class="form-group">
                  <label for="address1">Address:</label>
                  <input id="address1" name="address1" type="text" placeholder="Street address" class="form-control propercase required notpobox address" value="<?php echo $_SESSION['address1'];?>">
                </div>
                <div class="form-group">
									<input id="address2" name="address2" type="text" placeholder="Street address 2 (if needed)" class="form-control propercase notpobox address address2" value="<?php echo $_SESSION['address2'];?>">
                </div>
                <div class="form-group">
                  <label for="city">City/Suburb:</label>
                  <input id="city" name="city" type="text" placeholder="City/Suburb" class="form-control uppercase required cityauto" value="<?php echo $_SESSION['city'];?>">
                </div>
                <div class="form-group">
                  <label for="state">State:</label>
                  <input id="state" name="state" type="text" placeholder="State" class="form-control uppercase required state" value="<?php echo $_SESSION['state'];?>">
                </div>
                <div class="form-group">
                  <label for="postcode">Postcode:</label>
                  <input id="postcode" name="postcode" type="text" placeholder="Postcode" class="uppercase required form-control" value="<?php echo $_SESSION['postcode'];?>">
                </div>
                <div class="form-group">
                  <label for="country">Country:</label>
									<input id="country" name="country" type="text" class="form-control required country propercase" value="Australia" value="<?php echo $_SESSION['country'];?>">
                </div>
              </div>
              <hr>              
              <div class="form-group">
                <label for="firstname">Contact First name</label>
                <input id="firstname" name="firstname" type="text" placeholder="First name" class="form-control required propercase" value="<?php echo $_SESSION['firstname'];?>">                    
              </div>
              <div class="form-group">
                <label for="surname">Contact Last name</label>
                <input id="surname" name="surname" type="text" placeholder="Last name" class="form-control required propercase" value="<?php echo $_SESSION['surname'];?>">                    
              </div>
              <div class="form-group">
                <label for="registerEmail">Contact Email address</label>
                <input id="registerEmail" name="email" type="email" autocomplete="off" placeholder="example@domain.com" class="form-control required email disablecopy" value="<?php echo $_SESSION['email'];?>">                    
              </div>
              <div class="form-group">
                <label for="registerEmail2">Confirm Email address</label>
                <input id="registerEmail2" name="email2" type="email" autocomplete="off" placeholder="same as above" class="form-control required email">                    
              </div>
              <div class="form-group">
                <label for="registerPassword">Password to login</label>
                <input id="registerPassword" name="password" type="password" autocomplete="off" class="form-control required">                    
              </div>
              <div class="form-group">
                <label for="registerPassword2">Confirm password</label>
                <input id="registerPassword2" name="password2" type="password" autocomplete="off" class="form-control required">                    
              </div>
              <hr>
              <div class="form-group">
                <img class="pull-right" src="<?php echo $_SESSION['logo'];?>">
                <label for="logo">Logo File</label>
                <input id="logo" name="logo" type="file">
                <p class="help-block">Select the file that will be displayed as your organisation's logo. PNG or JPG - Maximum size 200kB.</p>                    
              </div>
              <div class="form-group">
                <label for="description">Organisation Brief</label>
                <textarea id="description" name="description" class="form-control" rows="5"><?php echo $_SESSION['description'];?></textarea>
                <p class="help-block">Enter information that should be included alongside the logo (HTML is OK).</p>                    
              </div>
              <div class="form-group">
                <label for="weburl">Website</label>
                <input id="weburl" name="weburl" type="text" placeholder="http://yourwebsite" class="form-control required lowercase" value="<?php echo $_SESSION['weburl'];?>">                    
              </div>
              <div class="form-group">
                <label for="autoBeneficiary" style="min-width: 260px;">
                  Make this organisation an auto generated beneficiary?
                </label>
                <input id="autoBeneficiary" name="autoBeneficiary" type="hidden" class="required" value="<?php echo isset($_SESSION['autoBeneficiary']) ? $_SESSION['autoBeneficiary'] : '';?>">
                <br>
                <div class="btn-group">
                  <button id="autoBeneficiaryYes" class="btn btn-default <?php if (isset($_SESSION['autoBeneficiary']) && $_SESSION['autoBeneficiary'] == 'Yes') echo 'active'; ?>" >Yes</button>
                  <button id="autoBeneficiaryNo" class="btn btn-default <?php if (isset($_SESSION['autoBeneficiary']) && $_SESSION['autoBeneficiary'] == 'No') echo 'active'; ?>" >No</button>
                </div>
              </div>
              
              <button id="RegisterButton" class="btn btn-social btn-primary">
                <i class="fa fa-sign-in"></i>New Organisation Registration
              </button>
              
            </form>
          </div>      
        </section>
<?php
require("pageincludes/footer.php");
?>