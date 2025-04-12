<?php
# check tht page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$title = "Including Pets in your Will - " . SITENAME;
$desc = "It is important to consider what happens to your pets when you die. They cannot receive an inheritance, but you can provide a gift to someone you trust to care for your pets.";
$keywords = "enduring power of attorney";

require("pageincludes/header.php");
?>
      <section class="container">
        <h1>Providing for Your Pets in Your Will</h1>
      </section>
<?php
  require("pageincludes/footer.php");
?>