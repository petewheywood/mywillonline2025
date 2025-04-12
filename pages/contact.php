<?php
# check tht page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

if (isset($_SESSION['getVars']['email'])) {
  $email = $_SESSION['email'] = $_SESSION['getVars']['email'];
  $query = "SELECT firstname, surname, emailaddress, phone FROM users WHERE emailaddress = '$email';";
  $result = $dbh->query($query);
  $row = $result->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $_SESSION['firstname'] = $row['firstname'];
    $_SESSION['surname'] = $row['surname'];
    $_SESSION['phone'] = $row['phone'];
  }
}



$title = "Contact " . SITENAME;
$desc = "Contact Us - You may contact " . SITENAME . " using this contact form.";
$keywords = "Contact " . SITENAME;

require("pageincludes/header.php");
?>
      <section class="container">
        <h1>Contact Details</h1>
        <div class="row">
          <div class="col-sm-6 topmargin">
            <h2><i class="glyphicon glyphicon-send text-primary"></i> Email</h2>
            <form role="form" id="contactform" method="post" action="/notice.html">
              <input name="referrer" type="hidden" value="<?php echo $_SERVER['HTTP_REFERER'];?>">
              <div class="form-group">
                <label for="contactName">Your Name</label>
                <input id="contactName" name="contactName" type="text" class="form-control long required propercase" placeholder="Your Name" value="<?php echo isset($_SESSION['firstname']) ? $_SESSION['firstname'] . ' ' .$_SESSION['surname'] : ''; ?>"  <?php echo !isset($_SESSION['firstname']) ? 'autofocus' : ''; ?>>
              </div>
              <div class="form-group">
                <label for="contactEmail">Email address</label>
                <input id="contactEmail" name="contactEmail" class="form-control required" type="email" placeholder="example@domain.com" value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>">                    
              </div>
              <div class="form-group">
                <label for="contactPhone">Phone</label>
                <input id="contactPhone" name="contactPhone" class="form-control" type="tel" placeholder="Eg. (02) 8888 9999" value="<?php echo isset($_SESSION['phone']) ? $_SESSION['phone'] : ''; ?>">
              </div>
              <div class="form-group">
                <label for="contactMessage">Your Message</label>
                <textarea id="contactMessage" name="contactMessage" rows="10" placeholder="Type your message here" class="form-control required" <?php echo isset($_SESSION['email']) ? 'autofocus' : ''; ?>></textarea>
              </div>
              <input type="submit" value="Send Message" class="btn btn-default" />
            </form>
            <br>
          </div>
          <div class="col-sm-2"></div>
          <div class="col-sm-4 topmargin">
            <h2><i class="glyphicon glyphicon-envelope text-primary"></i> Postal Address</h2>
            <h3><?php echo SITECOMPANY;?><br><?php echo MAILADDRESS;?></h3>
            <p class="small">(ABN <?php echo SITEABN;?>)</p>
          </div>
        </div>
      </section>      
      
<?php
  require("pageincludes/footer.php");
?>