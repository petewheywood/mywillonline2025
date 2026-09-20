<?php

/*

One-time maintenance script: re-encrypts every order still on the old
AES_ENCRYPT(orderdata, affiliatecode) scheme into the new envelope-encryption
scheme (see includes/crypto.php). Safe to re-run - only touches rows where
dek_wrapped IS NULL.

Run from the project root, CLI only:
  php migrations/backfill_orderdata.php

*/

if (php_sapi_name() !== 'cli') {
  http_response_code(403);
  exit("This script must be run from the command line, not over HTTP.\n");
}

require_once(__DIR__ . '/../includes/functions.php');

$dbh = dbConnect();
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$batchSize = 200;
$migrated = 0;
$skipped = 0;

echo "Starting backfill...\n";

while (true) {
  $stmt = $dbh->prepare("
    SELECT o.id, o.orderdata, AES_DECRYPT(o.orderdata, u.affiliatecode) AS legacy_orderdata
    FROM orders o
    JOIN users u ON (u.id = o.userid)
    WHERE o.dek_wrapped IS NULL
    LIMIT :batchsize
  ");
  $stmt->bindValue(':batchsize', $batchSize, PDO::PARAM_INT);
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (count($rows) === 0) {
    break; // nothing left to migrate
  }

  $updateStmt = $dbh->prepare("
    UPDATE orders
    SET orderdata = :ciphertext, orderdata_nonce = :data_nonce, dek_wrapped = :dek_wrapped, dek_nonce = :dek_nonce
    WHERE id = :orderid
  ");

  foreach ($rows as $row) {
    if ($row['orderdata'] === null || $row['legacy_orderdata'] === null) {
      // nothing to migrate (e.g. an abandoned order with no data ever saved) -
      // give it empty envelope columns so it doesn't get selected again
      $emptyEnc = encryptOrderData(serialize(null));
      $updateStmt->execute([
        ':ciphertext' => $emptyEnc['ciphertext'],
        ':data_nonce' => $emptyEnc['data_nonce'],
        ':dek_wrapped' => $emptyEnc['dek_wrapped'],
        ':dek_nonce' => $emptyEnc['dek_nonce'],
        ':orderid' => $row['id'],
      ]);
      $skipped++;
      continue;
    }

    $plaintext = stripslashes($row['legacy_orderdata']);
    $enc = encryptOrderData($plaintext);
    $updateStmt->execute([
      ':ciphertext' => $enc['ciphertext'],
      ':data_nonce' => $enc['data_nonce'],
      ':dek_wrapped' => $enc['dek_wrapped'],
      ':dek_nonce' => $enc['dek_nonce'],
      ':orderid' => $row['id'],
    ]);
    $migrated++;
  }

  echo "Migrated $migrated (skipped $skipped empty)...\n";
}

echo "Done. Migrated $migrated orders, skipped $skipped with no data.\n";
