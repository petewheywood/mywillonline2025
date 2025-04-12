<?php
require_once("../includes/functions.php");

$dbh = dbConnect();

echo "<pre>\n";

$newdir = '../../' . DATADIR . "/regen/";
chdir($newdir);

$files = glob("*.qws");
usort($files, function($a, $b) {
    return filemtime($a) < filemtime($b);
});


foreach ($files as $file) {
  
  $orderData = unserialize(base64_decode(file_get_contents($file)));
  
  $tx = $orderData['txnid'];
  
  # get current id of row
  $result = $dbh->query("SELECT id, orderdate FROM quickwillorders WHERE tx = '$tx';");
  $row = $result->fetch(PDO::FETCH_ASSOC);
  
  $orderID = $orderData['orderid'] = $row['id'];
  $tcstatus = $orderData['tcstatus'];
  $paiddate = $row['orderdate'];
  $address = addslashes($orderData['testatorAddress']);
  $willdata = base64_encode(serialize($orderData));

  $updateQuery = "UPDATE quickwillorders SET orderdata = '$willdata', address = '$address', paiddate = '$paiddate', tcstatus = '$tcstatus' WHERE id = $orderID;";
  $dbh->exec($updateQuery);
  echo $tx . ' - ' . $tcstatus . "\n\n";
}   
?>