<?php
# stop logged on users using this link
if (isset($_SESSION['userid'])) goHome(); # go to home page if logged in

$fullname = $_SESSION['pwresetdata']['firstname'] . ' ' . $_SESSION['pwresetdata']['surname'];
$userid = $_SESSION['pwresetdata']['userid'];
unset($_SESSION['pwresetdata']);
$_SESSION['resettingPW'] = $userid; # security check on pwreset2.php

$title = "Password Reset";
$desc = "Reset your password at " . SITENAME . " using this form.";
$keywords = "password reset";

require("pageincludes/header.php");
?>
      <section class="container">
        <h3>Password Reset</h3>
        <form id="resetPasswordForm" role="form" method="post" action="includes/pwreset2.php">
          <input name='pwuserid' type='hidden' value='<?php echo $userid; ?>'>
          <p>
            Please enter a <span class="highlight2">NEW password</span> of at least 8 characters.
          </p>
          <div class="form-group">
              <label for="password">Password to login</label>
              <input id="password" name="password" type="password" class="form-control">                    
          </div>
          <div class="form-group">
              <label for="password2">Confirm password</label>
              <input id="password2" name="password2" type="password" class="form-control">                    
          </div>
          <input id="resetSubmit" type="submit" value="Reset Password" class="btn btn-default" />
        </form>
      </section>
<?php
require("pageincludes/footer.php");
?>