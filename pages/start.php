<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
    header("Location: /");
    exit;
}

# check passed in GET vars
$introcode = $email = $pgref = '';
if (isset($_SESSION['getVars']['afid'])) {
    $introcode = $_SESSION['getVars']['afid'];
    unset($_SESSION['getVars']['afid']);
}

if (isset($_SESSION['getVars']['email'])) {
    $email = $_SESSION['getVars']['email'];
}
if (isset($_SESSION['getVars']['pgref'])) {
    $pgref = $_SESSION['getVars']['pgref'];
}

if (isset($_SESSION['introcode'])) {
    $introcode = $_SESSION['introcode']; # makes sure that the start page is setup for introcode if refreshed
}

if (isset($_SESSION['userid']) && $_SESSION['userid'] > 0) {
  # if the user is already logged in then send directly to their passed in or default page
    $page = $pgref > '' ? $pgref . '.html' : 'mydocs.html';
    echo "<script type='text/javascript'>window.location = '/$page';</script>";
    exit;
}

# check introcode details to make sure it's valid
$validcode = false;
if ($introcode) {
  # get affiliate's details to display to new user
  # if introcode then get discount
    $query = "SELECT u.id, u.firstname, u.surname, u.discount, o.name, o.logo, o.description, o.abn, o.weburl, u.flags FROM users u LEFT JOIN organisations o ON (o.userid = u.id) WHERE affiliatecode = '$introcode';";
    $results = $dbh->query($query);
    $row = $results->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $validcode = true;
        $_SESSION['introcode'] = $introcode;
        $_SESSION['quickwill'] = false; # no quickwill because they were introduced
        $_SESSION['affiliateName'] = $row['name'] > '' ? $row['name'] : $row['firstname'] . ' ' . $row['surname'];
        $_SESSION['affiliateID'] = $row['id'];
        $_SESSION['affiliateIsOrg'] = false;
        $specialSale = $row['flags'] == 64 ? true : false; # used for special sales to show the details of the sale below
        $discount = $_SESSION['discount'] = is_null($row['discount']) ? $_SESSION['discount'] : $row['discount'];
        # if the introducer is an organisation, then set logo, description, etc
        $orgimg = $orgdesc = $orgabn = $weburl = '';
        if ($row['name'] > '') {
            # this is an organisation
            $_SESSION['affiliateIsOrg'] = true;
            $orgimg = '<a href="' . $row['weburl'] . '" title="' . $row['name'] . '"><img src="' . $row['logo'] . '" alt="' . $name . '" class="orgimg"></a>';
            $orgdesc = $row['description'];
            $orgabn = $row['abn'];
            $referrer = '<strong><a href="' . $row['weburl'] . '" target="_blank">' . $_SESSION['affiliateName'] . '</a></strong>' . ($orgabn > '' ? " <small><em>(ABN: $orgabn)</em></small>" : '');
            $disc = percent($discount);
            $orgdesc = "<p>From " . $_SESSION['affiliateName'] . ":</p><blockquote><em>" . $row['description'] . "</em></blockquote>";
        } else {
            $referrer = $_SESSION['affiliateName'];
        }
    }
}

$title = "Start Your Will";
$desc = "Creating your will at " . SITENAME . " is straight forward and quick. Begin by entering details about you, and then move on to naming your beneficiaries and how your estate should be distributed.";
$keywords = "last will and testament, online will, online wills, internet wills, online will kit";

require("pageincludes/header.php");

?>
      <section class="container">
<?php
if (!$validcode) {
?>          
        <h1>New Customers Register or Existing Customers Sign In</h1>
<!--
        <div class="bordered">
          <h3><i class="glyphicon glyphicon-play text-primary"></i> <strong>NEW</strong> and <strong>EXISTING</strong> customers can sign in with Facebook to save time</h3>
          <button id="FBLoginButton" class="btn btn-social btn-facebook">
            <i class="fa fa-facebook"></i>Sign in with Facebook
          </button>
        </div>
-->
        <div class="row" style="margin: 0 0 30px 0;">
          <div class="bordered col-md-6">
<?php
}
?>
<?php
if ($validcode) {
?>
        <div class="row" style="margin: 20px 0 30px 0;">
          <div>
            <h1>Welcome to <?php echo $_SESSION['sitename'];?></h1>
            <br>
            <?php echo $orgimg;?>
<?php
  if ($specialSale) {
?>
            <p class="large">Take advantage of our <span class="hilite"><?php echo $referrer;?></span>. Get a <span class="hilite"><?php echo percent($discount);?> discount</span> on your Last Will and Testament.</p>
<?php
  } else {
?>
            <p class="large">
              You've been referred to <?php echo $_SESSION['sitename'];?> by <strong><?php echo $referrer;?></strong>, 
              which means you can get a <span class="hilite"><?php echo percent($discount);?> discount</span> on your 
              Last Will and Testament. The standard price is <strong><?php echo currency($_SESSION['products']['singlewill']['priceinctax']); ?></strong>
              but you would only pay <strong><?php echo currency(round($_SESSION['products']['singlewill']['priceinctax'] - $_SESSION['products']['singlewill']['priceinctax'] * $discount, 2)); ?></strong>.
            </p>
<?php
  }
?>
            <?php echo $orgdesc;?>
            <br>
            <h3><i class="glyphicon glyphicon-play text-info"></i> Please complete your details below to lock in your <?php echo percent($discount);?> discount:</h3>
<?php
} else {
?>
            <h3><i class="glyphicon glyphicon-play text-info"></i> <strong>NEW</strong> customers can sign in by completing the information below</h3>
<?php
}
?>
            <form role="form" id="registerForm" method="post" action="/notice.html" autocomplete="off">
              <input id="coupon" name="coupon" type="hidden" value="<?php echo $introcode; ?>">
              <input id="fbid" name="fbid" type="hidden">
              <div class="form-group">
                <label for="firstname">First name</label>
                <input id="firstname" name="firstname" type="text" placeholder="First name" class="form-control required propercase" <?php echo ($email > '') ? '' : 'autofocus';?>>                    
              </div>
              <div class="form-group">
                <label for="surname">Last name</label>
                <input id="surname" name="surname" type="text" placeholder="Last name" class="form-control required propercase">                    
              </div>
              <div class="form-group">
                <label for="registerEmail">Email address</label>
                <input id="registerEmail" name="email" type="email" autocomplete="off" placeholder="example@domain.com" class="form-control required email disablecopy">                    
              </div>
              <div class="form-group">
                <label for="registerEmail2">Confirm Email address</label>
                <input id="registerEmail2" name="email2" type="email2" autocomplete="off" placeholder="" class="form-control required email">                    
              </div>
              <div class="form-group">
                <label for="registerPassword">Password to login</label>
                <input id="registerPassword" name="password" type="password" autocomplete="off" class="form-control required">                    
              </div>
              <div class="form-group">
                <label for="registerPassword2">Confirm password</label>
                <input id="registerPassword2" name="password2" type="password" autocomplete="off" class="form-control required">                    
              </div>
              <button id="registerButton" class="btn btn-social btn-info">
                <i class="fa fa-sign-in"></i>New Customer Sign In
              </button>
            </form>
          </div>      

          <div <?php echo (!$validcode) ? 'class="bordered col-md-5 col-md-offset-1"' : 'style="margin-top:60px; margin-bottom: 10px;"'; ?>>
            <h3><i class="glyphicon glyphicon-play text-success"></i> Or, <strong>EXISTING</strong> customers can sign in using their email address and password</h3>
            <form role="form" id="loginForm" method="post" action="/notice.html">
              <input id="pgref" name="pgref" type="hidden" value="<?php echo $pgref;?>">
              <div class="form-group">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" class="form-control" placeholder="example@domain.com" value="<?php echo $email;?>">                    
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" class="form-control" type="password" <?php echo ($email > '') ? 'autofocus' : '';?>>
              </div>
              <button id="loginButton" class="btn btn-social btn-success">
                <i class="fa fa-sign-in"></i>Sign In with email and password
              </button>
              <br>
              <br>
              <a id="lostpw" href="" tabindex="-1">Lost Password?</a>
            </form>
          </div>
          <div style="height: 30px;"></div>

        </div>
      </section>
<?php
require("pageincludes/footer.php");
?>
