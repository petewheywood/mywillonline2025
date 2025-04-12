<?php

/*

This will be called when a user wants to unsubscribe from marketing emails

*/
require_once('includes/parms.php');
require_once('includes/functions.php');

$email = $_SESSION['getVars']['email'];
$id = $_SESSION['getVars']['id'];
unset($_SESSION['getVars']);

$dbh = dbConnect();

$result = $dbh->exec("UPDATE prospects SET unsubscribe = 1 WHERE emailaddress = '$email' AND id = $id;");
if($result > 0) {  
  
  # send unsub notification to admin
  $sitename = SITENAME;
  $siteemail = SITEEMAIL;
  $adminemail = "$sitename <$siteemail>";
  $subject = "$email unsubscribed from prospects at MWO";
  $body = "Email $email has unsubscribed\n";
  adminEmail($adminemail, $subject, $body);

  $title = "Unsubscribe";
  $desc = "Unsubscribe from receiving any emails from " . SITENAME;
  $keywords = "last will and testament, online will, online wills, internet wills, online will kit";

  require("pageincludes/header.php");
?>
    <section class="container">
      <section class="row">
        <section class="content">
          <article class="post">
            <header>
              <h2>You have been removed from our mailing list.</h2>
            </header>
            <p>
              Your email address (<?php echo $email;?>) will no longer receive emails from <?php echo $_SESSION['sitename'];?>.
            </p>
            <p>
              Kind regards,<br />
              <em>The Team at <?php echo $_SESSION['sitename'];?></em>
            </p>
          </article>
        </section>
      </section>
    </section>
<?php
  require("pageincludes/footer.php");
} else {
  header("Location: /");
}
