<?php

$email = $_SESSION['email'] = isset($_SESSION['getVars']['email']) ? $_SESSION['getVars']['email'] : '';
$query = "SELECT firstname, surname, emailaddress, phone, MAX(paiddate) as orderdate FROM user_orders WHERE emailaddress = '$email' AND paid = 1 AND extaxamount is not null GROUP BY 1, 2, 3, 4;";
$result = $dbh->query($query);
$row = $result->fetch(PDO::FETCH_ASSOC);
if ($row) {
  $name = $row['firstname'] . ' ' . $row['surname'];
  $orderdate = showdate($row['orderdate']);
  $firstname = $_SESSION['firstname'] = ucfirst($row['firstname']);
  $surname = $_SESSION['surname'] = ucfirst($row['surname']);
  $email = $_SESSION['email'] = $row['emailaddress'];
  $phone = $_SESSION['phone'] = $row['phone'];
} else {
  # redirect if not found
  echo "<script type='text/javascript'>window.location = \"/\";</script>\n";
}

$title = "Customer Review - " . SITENAME;
$desc = "Please rate your experience with " . SITENAME;
$keywords = "recommend " . SITENAME;

# display the feedback form for the user
require("pageincludes/header.php");

?>
      <section class="container">
        <h1>What do you think of <?php echo SITENAME; ?>?</h1>
        <p>Hi <?php echo $firstname;?>,</p>
        
        <p>Thank you for being a past customer of <?php echo SITENAME; ?>. We really appreciate your business, and really hope that the experience was excellent and that you're happy with your finished will documents.</p>
        
        <p>You ordered your Will on <?php echo $orderdate; ?>, and we are just reminding you that you can update and re-download your will for no charge if your circumstances change. Once signed and witnessed, this new will replaces any previous ones. You can login to update <a href="login.html">here</a>.</p>
        
        <p><em><strong>For a limited time, <?php echo SITENAME; ?> is offering Last Will and Testament and Enduring Power of Attorney documents for FREE. I know that you already have free updates for life on your will documents, but your friends and family would benefit if they don't already have a will.</strong></em></p>
        
        <p>If you've been happy with <?php echo SITENAME; ?> service, then we would really appreciate a positive review.</p>
        
        <a target="_blank" style="margin: 10px 0 40px 0;" href="https://www.google.com.au/search?num=40&newwindow=1&ei=Bhz3W9WJGcj0vgTrtZrACg&q=my+will+online&oq=my+will+online&gs_l=psy-ab.3..0l2j0i22i30l8.32520.39358..39494...2.0..0.264.2885.0j11j4......0....1..gws-wiz.......0i71j0i131j0i67j0i10j0i22i10i30.W4BR7ZU1rMM#lrd=0x6b75f926ebc2e05d:0x3521d95cb8799789,3,,," class="btn btn-success">Rate My Will Online</a>
        
        <p>If you think we could do better, then please let us know by sending us some feedback.</p>
        
        <a href="contact.html"  style="margin: 10px 0;" class="btn btn-warning">Send Feedback</a>
        
      </section>
<?php
  require("pageincludes/footer.php");
?>