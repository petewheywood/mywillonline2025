<?php
# check tht page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$title = "Product Comparison · " . SITENAME;
if (QUICKWILL && $_SESSION['quickwill']) {
  $desc = SITENAME . " provides three options to customers for their Last Will and Testament. A Free Basic Will, our Quick Will and our comprehensive Premium Will. They are all legally valid Wills. They differ in the inclusions, options and the ability to do regular updates and downloads.";
  $keywords = "last will and testament, online will, online wills, internet wills, online will kit, freewill, quickwill, premium will";
} else {
  $desc = SITENAME . " provides two options to customers for their Last Will and Testament. A Free Basic Will and our comprehensive Premium Will. They are both legally valid Wills. The Free Will is a simple will template that will be emailed to you and that must be completed by hand. The Premium Will is a fully online will template that saves your information. The latter includes significant benefits over the Free Will.";
  $keywords = "last will and testament, online will, online wills, internet wills, online will kit, freewill, premium will";
}

require_once("pageincludes/header.php");
?>
      <div class="container-fluid">
        <div class="container" style="padding-bottom: 20px;">
<?php
if (QUICKWILL && $_SESSION['quickwill']) {
?>
          <h1>Will Comparison</h1>
          <div class="row">
            <div class="col-sm-6 text-center">
              <img src="images/electronic-devices.png" style="width: 100%; max-width: 360px;" title="Devices for <?php echo SITENAME;?>">
            </div>
            <div class="col-sm-6">
              <p>
              	<?php echo SITENAME;?> has three different Will products. They are all legally valid.
              </p>
              <p>
              	The differences are the inclusions, the available options and whether or not the Will can be updated online
              	for no additional cost. The table below shows these differences. 
              </p>
            </div>
          </div>
<?php
} else {
?>
          <h1>Last Will and Testament Features and Pricing</h1>
          <div class="row">
            <div class="col-sm-6 text-center">
              <img src="images/electronic-devices.png" style="width: 100%; max-width: 360px;" title="Devices for <?php echo SITENAME;?>">
            </div>
            <div class="col-sm-6">
            </div>
          </div>
<?php
}
require_once("includes/comparison.php");
?>
        </div>
      </div>
<?php
require_once("pageincludes/footer.php");
?>