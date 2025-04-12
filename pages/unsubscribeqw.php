<?php

/*

This will be called when a quickwill user wants to unsubscribe from marketing emails

*/
require_once('includes/parms.php');
require_once('includes/functions.php');

$id = $_SESSION['getVars']['id'];

$dbh = dbConnect();

$query = "SELECT email FROM quickwillorders WHERE id = $id;";
$row = $dbh->query($query);
$email = $row->fetchColumn();

$result = $dbh->exec("DELETE FROM quickwillorders WHERE id = $id;");

# send unsub notification to admin
$sitename = SITENAME;
$siteemail = SITEEMAIL;
$adminemail = "$sitename <$siteemail>";
$subject = "$email unsubscribed at MWO";
$body = "Email $email has unsubscribed from QuickWills\n";
adminEmail($adminemail, $subject, $body);

$title = "Unsubscribe";
$desc = "Unsubscribe from receiving any emails from " . SITENAME;
$keywords = "last will and testament, online will, online wills, internet wills, online will kit";

require("pageincludes/header.php");
?>
    <section class="container" style="padding-bottom: 200px;">
      <section class="row">
        <section class="content">
          <article class="post">
            <header>
              <h2>You <?php echo ($email > '' ? '(' . $email . ')' : '');?> have been removed from our system.</h2>
            </header>
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
