<?php
# check tht page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$title = "Posts - " . SITENAME;
$desc = "Social media posts from @mywillonline.";
$keywords = "social media posts";

require("pageincludes/header.php");
?>
      <section class="container">
        <h1>Social Media Posts for <?php echo SITENAME; ?></h1>
        <div id="instagram">
        	<?php echo instagram_feed('IMAGE',0); ?>
        </div>
      </section>
<?php
  require("pageincludes/footer.php");
?>