<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
    header("Location: /");
    exit;
}

$base = url_origin();

$_SESSION['lastPage'] = basename(__FILE__);

$keywords = "free wills, wills, free will kit, last will and testament, online will, online wills, internet wills, online will kit";
$desc = 'We make it so easy to make your will online. Our customers rate us ' . $aggregaterating . ' from ' . $numreviews . ' reviews · Free Will available · Free updates for life · 100% Satisfaction guarantee · Free Enduring Power of Attorney · Provision for your pets . Executors Memorandum';
$title = "Quick Will compared to Premium Will";

require("pageincludes/header.php");
?>
  <section class="container">
    <h1>Quick Will compared to Premium Will</h1>
  </section>
   <section class="container-fluid" id="compare">
      <div class="container" <?php echo (QUICKWILL && $_SESSION['quickwill']) ? '' : 'style="padding-left: 10%;"';?>>
        <?php require_once("includes/comparison.php");?>
      </div>
    </section>
<?php
require("pageincludes/footer.php");
?>