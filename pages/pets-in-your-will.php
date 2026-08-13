<?php
# check tht page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$title = "Pets in Your Will | " . SITENAME;
$desc = "Provide for your pets in your will. Appoint a carer and leave a gift for their ongoing care. My Will Online makes it easy to include pets in your will.";
$keywords = "enduring power of attorney";

require("pageincludes/header.php");
?>
      <section class="container">
        <h1>Providing for Your Pets in Your Will</h1>
      </section>
<?php
  require("pageincludes/footer.php");
?>