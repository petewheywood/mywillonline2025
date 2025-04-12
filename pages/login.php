<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

# check passed in GET vars
$coupon = $email = $pgref = '';
if (isset($_SESSION['getVars']['afid'])) $coupon = $_SESSION['introcode'] = $_SESSION['getVars']['afid'];
if (isset($_SESSION['getVars']['email'])) $email = $_SESSION['getVars']['email'];
if (isset($_SESSION['getVars']['pgref'])) $pgref = $_SESSION['getVars']['pgref'];
unset($_SESSION['getVars']);

if (isset($_SESSION['userid'])) {
  # if the user is already logged in then send directly to their passed in or default page
  $page = $pgref > '' ? $pgref . '.html' : 'mydocs.html';
  echo "<script type='text/javascript'>window.location = '/$page';</script>";
  exit; 
}

$title = SITENAME . " - Online Wills for all Australians";
$desc = "Australia's simplest to use and popular online will creation website. Create wills, enduring guardianship and enduring power of attorney documents quickly and simply on your computer, tablet or smartphone.";
$keywords = "last will and testament, online will, online wills, internet wills, online will kit";

require("pageincludes/header.php");

?>
        <section class="container">
        <div class="row" style="margin: 0 0 30px 0;">
          <div class="bordered col-md-6">
          <h1>Existing Customers Sign In</h1>
          <div class="bordered">
            <h3><i class="glyphicon glyphicon-play text-success"></i> <strong>EXISTING</strong> customers can sign in with Facebook or by using their email address and password</h3>
            <button id="FBLoginButton" class="btn btn-social btn-facebook">
              <i class="fa fa-facebook"></i>Sign in with Facebook
            </button>
            <hr>
            <form role="form" id="loginForm" method="post" action="/">
              <input id="pgref" name="pgref" type="hidden" value="<?php echo $pgref;?>">
              <div class="form-group">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" class="form-control" placeholder="example@domain.com" value="<?php echo isset($email) ? $email : '';?>" <?php if (!isset($email)) echo "autofocus";?>>                    
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" class="form-control" type="password" <?php if (isset($email)) echo "autofocus";?>>
              </div>
              <button id="loginButton" class="btn btn-social btn-success">
                <i class="fa fa-sign-in"></i>Sign In with email and password
              </button>
              <br>
              <br>
              <a id="lostpw" href="" tabindex="-1">Lost Password?</a>
            </form>
          </div>
        </section>
<?php
  require("pageincludes/footer.php");
?>