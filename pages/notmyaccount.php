<?php

/*

This will be called when a user did not request a registration (ie. somebody used their email address and the click on the link in the email

*/
require_once('includes/parms.php');
require_once('includes/functions.php');

$affcode = $_SESSION['getVars']['code'];

$dbh = dbConnect();
$query = "SELECT emailaddress FROM users WHERE affiliatecode = '$affcode';";
$row = $dbh->query($query);
$email = $row->fetchColumn();

if ($email) {
  $randompw = RandomString();
  $query = "UPDATE users SET emailaddress = CONCAT(emailaddress, '_', '$randompw'), password = '$randompw', flags = flags | " . FLAG_NOTMYACCOUNT . " WHERE affiliatecode = '$affcode'";
  $result = $dbh->exec($query);
  
  
 $title = SITENAME;
 $desc = "Use this page to remove registration details from " - SITENAME;
 $keywords = "last will and testament, online will, online wills, internet wills, online will kit";
 
 require("pageincludes/header.php");

?>
    <section class="container" style="margin-bottom: 25vh;">
      <section class="row">
        <section class="content">
          <article class="post">
            <header>
              <h1>Thank you for letting us know that you didn't register here.</h1>
            </header>
            <p>
              Please accept our apologies. It seems someone must have used your email address (<?php echo $email;?>) to register at <?php echo $_SESSION['sitename'];?>.
            </p>
            <p>
              We will remove the registration details.
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
