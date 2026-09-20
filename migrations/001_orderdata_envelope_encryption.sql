-- Adds envelope-encryption columns for orders.orderdata (see
-- includes/crypto.php), and an audit table for admin-impersonation access
-- to a customer's order data.
--
-- NOTE: quickwillorders.orderdata has a separate, weaker problem (it's only
-- base64-encoded, not encrypted at all) and is NOT covered by this
-- migration - it's a reasonable follow-up but wasn't part of this change.
--
-- The existing `orderdata` column is reused for the new ciphertext (it's
-- raw bytes either way). New columns are nullable so existing rows keep
-- working via the old AES_ENCRYPT/AES_DECRYPT path until they are next
-- saved, at which point the application re-encrypts them under the new
-- scheme. Run backfill.php afterwards to migrate everything in one pass
-- instead of waiting for organic re-saves.

ALTER TABLE orders
  ADD COLUMN orderdata_nonce VARBINARY(24) NULL AFTER orderdata,
  ADD COLUMN dek_wrapped     VARBINARY(64) NULL AFTER orderdata_nonce,
  ADD COLUMN dek_nonce       VARBINARY(24) NULL AFTER dek_wrapped;

CREATE TABLE IF NOT EXISTS decrypt_audit (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  orderid INT NOT NULL,
  ownerid INT NOT NULL,
  accesstime DATETIME NOT NULL,
  KEY idx_orderid (orderid),
  KEY idx_ownerid (ownerid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
