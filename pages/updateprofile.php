<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}
if (!isset($_SESSION['loggedin'])) echo "<script>location = '/';</script>";

$_SESSION['updatingEmailPW'] = isset($_SESSION['userid']) ? $_SESSION['userid'] : ''; # security check on updateemailandpassword.php

$title = "Update Your Profile";
$desc = "Update your profile details at " . SITENAME;
$keywords = "last will and testament, online will, online wills, internet wills, online will kit";

require("pageincludes/header.php");
?>
      <section class="container">
        <h1>Update Your Details</h1>
        <form id="updateProfileForm" method="post" action="/" enctype="multipart/form-data">
<?php
if ($_SESSION['organisation']) {
?>
          <h2>Organisation</h2>
          <div class="form-group">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" class="form-control propercase" value="<?php echo isset($_SESSION['orgname']) ? $_SESSION['orgname'] : '';?>">
          </div>
          <div class="form-group">
            <label for="abn">ABN</label>
            <input id="abn" name="abn" type="text" class="form-control abn" value="<?php echo isset($_SESSION['abn']) ? $_SESSION['abn'] : '';?>">
          </div>
          <div class="form-group">
            <label for="weburl">Website</label>
            <input id="weburl" name="weburl" type="text" class="form-control" value="<?php echo isset($_SESSION['weburl']) ? $_SESSION['weburl'] : '';?>">
          </div>
          <div class="form-group">
            <img class="pull-right" style="border: 1px dashed #ddd; padding: 5px;" src="<?php echo $_SESSION['logo'];?>">
            <label for="logo">Logo File</label>
            <input id="logo" name="logo" type="file">
            <p class="help-block">Select the file that will be displayed as your organisation's logo.  PNG or JPG - Maximum size 200kB.</p>                    
          </div>
          <div class="form-group">
            <label for="description">Organisation Brief</label>
            <textarea id="description" name="description" class="form-control" rows="5"><?php echo $_SESSION['description'];?></textarea>
            <p class="help-block">Enter information that should be included alongside the logo (HTML is OK).</p>                    
          </div>
          <hr>
          <h2>Contact Person</h2>
<?php
}
?>
          <div class="form-group">
            <label for="firstname">First name</label>
            <input id="firstname" name="firstname" type="text" class="form-control propercase" value="<?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] : '';?>">
          </div>
          <div class="form-group">
            <label for="surname">Family name</label>
            <input id="surname" name="surname" type="text" class="form-control propercase" value="<?php echo isset($_SESSION['surname']) ? $_SESSION['surname'] : '';?>">                    
          </div>
          <hr>
          <div class="form-group">
						<label for="address1">Address</label>
            <input id="address1" name="address1" type="text" class="form-control propercase" value="<?php echo isset($_SESSION['address1']) ? $_SESSION['address1'] : '';?>"><br>
            <input id="address2" name="address2" type="text" class="form-control propercase" value="<?php echo isset($_SESSION['address2']) ? $_SESSION['address2'] : '';?>">
          </div>
          <div class="form-group">
            <label for="city">City</label>
            <input id="city" name="city" type="text" class="form-control propercase cityauto" value="<?php echo isset($_SESSION['city']) ? $_SESSION['city'] : '';?>">                    
          </div>
          <div class="form-group">
            <label for="state">State</label>
            <input id="state" name="state" type="text" class="form-control uppercase state" value="<?php echo isset($_SESSION['state']) ? $_SESSION['state'] : '';?>">                    
          </div>
          <div class="form-group">
            <label for="postcode">Postcode</label>
            <input id="postcode" name="postcode" type="number" class="form-control" value="<?php echo isset($_SESSION['postcode']) ? $_SESSION['postcode'] : '';?>">                    
          </div>
          <div class="form-group">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" type="tel" class="form-control" value="<?php echo isset($_SESSION['phone']) ? $_SESSION['phone'] : '';?>">                    
          </div>
          <p style="margin-top: 40px;">
            You can update both your email address and password, or just your email address, or just your password.
            If you wish to change your password, enter a new password of at least 8 characters.
          </p>
          <div class="form-group">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" class="form-control email disablecopy" autocomplete="off" placeholder="only if changing">
          </div>
          <div class="form-group">
            <label for="email2">Confirm Email address</label>
            <input id="email2" name="email2" type="email" class="form-control email" autocomplete="off" placeholder="only if changing">
          </div>
          <div class="form-group">
            <label for="password">New password</label>
            <input id="password" name="password" type="password" class="form-control" placeholder="only if changing">
          </div>
          <div class="form-group">
            <label for="password2">Confirm password</label>
            <input id="password2" name="password2" type="password" class="form-control" placeholder="only if changing">                    
          </div>
          <input id="updateSubmit" type="submit" value="Update" class="btn btn-default" />
        </form>
        <hr>
        <div class="small" style="margin: 20px 0 20px 0;">
          If you would like to remove all your information from our database, then please click this link. However, be aware that,
          all your login, contact and order information will be permanently removed.<br>
          <a href="" id="deleteAccount">Permanently Delete My Account</a>
        </div>
      </section>
<?php
require("pageincludes/footer.php");
?>