<?php

/*

Envelope encryption for order data (wills / EPoA content stored in the
`orders.orderdata` column).

How it works (see docs/encryption.md for the full write-up):

  - Every record gets its own random Data Encryption Key (DEK).
  - The record's plaintext is encrypted with its DEK using an authenticated
    cipher (XChaCha20-Poly1305).
  - The DEK itself is then "wrapped" (encrypted) with a single Master Key
    using a second authenticated cipher (secretbox / XSalsa20-Poly1305).
  - The Master Key lives ONLY in includes/parms.php (git-ignored, outside
    MySQL) - never in the database. A stolen database backup therefore
    yields locked ciphertext with no usable key alongside it.

Who can decrypt is purely an application-layer authorization check (see
callers of decryptOrderData()) - the crypto itself does not distinguish
between the will's owner and an admin.

*/

define('CRYPTO_VERSION', 1); // bump if the scheme ever changes, to support future migrations

/**
 * Returns the raw 32-byte Master Key, decoded once from parms.php.
 */
function getMasterKey(): string
{
  static $key = null;
  if ($key === null) {
    if (!defined('ORDERDATA_MASTER_KEY_B64') || ORDERDATA_MASTER_KEY_B64 === '') {
      throw new Exception('ORDERDATA_MASTER_KEY_B64 is not configured in parms.php');
    }
    $key = base64_decode(ORDERDATA_MASTER_KEY_B64, true);
    if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
      throw new Exception('ORDERDATA_MASTER_KEY_B64 in parms.php is not a valid 32-byte base64 key');
    }
  }
  return $key;
}

/**
 * Encrypts $plaintext (a serialized orderData array, as a string) with a
 * fresh per-record DEK, then wraps that DEK with the Master Key.
 *
 * Returns an assoc array of raw binary strings, ready to bind directly into
 * a BLOB/VARBINARY column via PDO (PARAM_LOB):
 *   ciphertext, data_nonce, dek_wrapped, dek_nonce
 */
function encryptOrderData(string $plaintext): array
{
  $dek = sodium_crypto_secretbox_keygen(); // 32 random bytes, one-off, never stored

  $dataNonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
  $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($plaintext, '', $dataNonce, $dek);

  $dekNonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
  $dekWrapped = sodium_crypto_secretbox($dek, $dekNonce, getMasterKey());

  sodium_memzero($dek);

  return [
    'ciphertext' => $ciphertext,
    'data_nonce' => $dataNonce,
    'dek_wrapped' => $dekWrapped,
    'dek_nonce' => $dekNonce,
  ];
}

/**
 * Reverses encryptOrderData(). $ciphertext/$dataNonce/$dekWrapped/$dekNonce
 * are the raw binary column values as fetched from the DB (no base64).
 *
 * Throws an Exception if the Master Key is wrong or the data has been
 * tampered with (both unwrap and decrypt are authenticated).
 */
function decryptOrderData(string $ciphertext, string $dataNonce, string $dekWrapped, string $dekNonce): string
{
  $dek = sodium_crypto_secretbox_open($dekWrapped, $dekNonce, getMasterKey());
  if ($dek === false) {
    throw new Exception('Could not unwrap order DEK - wrong master key or corrupted data');
  }

  $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($ciphertext, '', $dataNonce, $dek);
  sodium_memzero($dek);

  if ($plaintext === false) {
    throw new Exception('Could not decrypt order data - corrupted or tampered data');
  }

  return $plaintext;
}

/**
 * Given a fetched DB row that contains the encrypted-orderdata columns
 * (orderdata, orderdata_nonce, dek_wrapped, dek_nonce) PLUS a
 * `legacy_orderdata` column (the result of the old
 * AES_DECRYPT(orderdata, affiliatecode) done in SQL, kept for backwards
 * compatibility with rows that have not been re-saved since this scheme
 * was introduced), return the unserialized orderData array.
 *
 * This lets every read site keep selecting `legacy_orderdata` alongside the
 * new columns without needing to know which format a given row is in.
 *
 * If $auditContext is given (['orderid' => ..., 'ownerid' => ...]) and the
 * current session is an admin-impersonation session (see loguserin.php),
 * the access is written to decrypt_audit.
 */
function resolveOrderData(array $row, ?PDO $dbh = null, ?array $auditContext = null)
{
  if (!empty($row['dek_wrapped'])) {
    $plaintext = decryptOrderData($row['orderdata'], $row['orderdata_nonce'], $row['dek_wrapped'], $row['dek_nonce']);
  } else {
    // legacy row: not re-saved since the switch to envelope encryption yet -
    // legacy_orderdata was produced by MySQL's AES_DECRYPT(...) using the
    // owning user's affiliatecode, the same way it always was.
    $plaintext = isset($row['legacy_orderdata']) ? stripslashes($row['legacy_orderdata']) : false;
  }

  if ($dbh !== null && $auditContext !== null && !empty($_SESSION['adminImpersonation'])) {
    logAdminOrderAccess($dbh, $auditContext['orderid'], $auditContext['ownerid']);
  }

  return $plaintext === false ? false : unserialize($plaintext);
}

/**
 * Records that an admin-impersonation session decrypted a given order, for
 * accountability. See loguserin.php for where adminImpersonation is set.
 *
 * NOTE: because admin support access currently works by logging in as the
 * customer with the shared ADMIN master password (see loguserin.php), there
 * is no individual staff identity to record here - only that *some* holder
 * of the master password accessed this order, and when. If you need
 * per-staff accountability, that requires giving staff their own admin
 * logins instead of a shared master password.
 */
function logAdminOrderAccess(PDO $dbh, int $orderId, int $ownerId): void
{
  $stmt = $dbh->prepare("INSERT INTO decrypt_audit (orderid, ownerid, accesstime) VALUES (?, ?, NOW())");
  $stmt->execute([$orderId, $ownerId]);
}
