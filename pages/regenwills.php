<?php
if (!isset($page) || !isset($_SESSION['userid']) || !$_SESSION['admin']) {
  header("Location: /");
  exit;
}

$title = SITENAME . " - Re-generate Wills";
$desc = "Admin regeneration page.";
$keywords = "";


require("pageincludes/header.php");
?>
    <section class="container">
      <h1 class="text-success">Re Gen Wills</h1>
      <div class="row">
        <div class="col-xs-3">
          <b>Will Maker</b>
        </div>
        <div class="col-xs-3">
          <b>Order Created</b>
        </div>
        <div class="col-xs-3">
          <b>Transaction ID</b>
        </div>
        <div class="col-xs-3">
          <b>Date Paid</b>
        </div>
      </div>
<?php

$query = "SELECT id, tx, fullname, phone, email, orderdate, paiddate FROM quickwillorders ORDER BY id DESC;";
$result = $dbh->query($query);
$rows = $result->fetchAll();
foreach ($rows as $row) {
?>
      <div class="row">
        <div class="col-xs-3">
          <a href="includes/regenwill.php?id=<?php echo $row['id'];?>"><?php echo $row['fullname'] > '' ? $row['fullname'] : 'NONAME';?></a>
        </div>
        <div class="col-xs-3">
          <?php echo $row['orderdate'];?>
        </div>
        <div class="col-xs-3">
          <?php echo $row['tx'];?>
        </div>
        <div class="col-xs-3">
          <?php echo $row['paiddate'];?>
        </div>
      </div>
<?php
}
?>
      <br> <br>
    </section>
<?php
  require("pageincludes/footer.php");
?>