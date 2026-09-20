<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

/*

move an order from one user to another
*/

$userFrom = $_GET['from'];
$userTo = $_GET['to'];
$orderID = $_GET['order'];

require_once('../includes/functions.php');


if ($userFrom > 0 && $userTo > 0 && $orderID > 0) {

  $dbh = dbConnect();

  // get order - the DEK is wrapped with the Master Key, not tied to either
  // user, so a row already on the new scheme just needs its userid changed.
  // A row still on the legacy AES_ENCRYPT(affiliatecode) scheme needs to be
  // migrated to the new scheme now, since it will no longer have a valid
  // affiliatecode to decrypt with once it belongs to a different user.
  $result = $dbh->query("SELECT affiliatecode FROM users WHERE id = $userFrom;");
  $affiliateCodeFrom = $result->fetchColumn();

  $sth = $dbh->query("SELECT orderdata, orderdata_nonce, dek_wrapped, dek_nonce, AES_DECRYPT(orderdata,'$affiliateCodeFrom') AS legacy_orderdata FROM orders WHERE id = $orderID;");
  $row = $sth->fetch(PDO::FETCH_ASSOC);

  if (!empty($row['dek_wrapped'])) {
    // already on the new scheme - just move ownership, ciphertext untouched
    $dbh->exec("UPDATE orders SET userid = $userTo WHERE id = $orderID;");
  } else {
    // legacy row - decrypt with the old owner's key and re-encrypt under the new scheme
    $plaintext = stripslashes($row['legacy_orderdata']);
    $enc = encryptOrderData($plaintext);
    $updateStmt = $dbh->prepare("UPDATE orders SET userid = :userid, orderdata = :ciphertext, orderdata_nonce = :data_nonce, dek_wrapped = :dek_wrapped, dek_nonce = :dek_nonce WHERE id = :orderid;");
    $updateStmt->bindValue(':userid', $userTo, PDO::PARAM_INT);
    $updateStmt->bindValue(':ciphertext', $enc['ciphertext'], PDO::PARAM_LOB);
    $updateStmt->bindValue(':data_nonce', $enc['data_nonce'], PDO::PARAM_LOB);
    $updateStmt->bindValue(':dek_wrapped', $enc['dek_wrapped'], PDO::PARAM_LOB);
    $updateStmt->bindValue(':dek_nonce', $enc['dek_nonce'], PDO::PARAM_LOB);
    $updateStmt->bindValue(':orderid', $orderID, PDO::PARAM_INT);
    $updateStmt->execute();
  }

  echo "Done";
  $dbh = null;
} else {
  echo "";
}

