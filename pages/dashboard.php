<?php
# check tht page is not accessed directly and redirect if so
if (!isset($page) || !isset($_SESSION['userid'])) {
  header("Location: /");
  exit;
}

$pagetitle = 'Organisation Affiliate Dashboard';
require("pages/affiliateprogram.php");
?>