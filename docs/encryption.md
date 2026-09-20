# Order data encryption

`orders.orderdata` (the actual will/EPoA content a customer enters) is
envelope-encrypted. This replaces the old scheme of
`AES_ENCRYPT(orderdata, affiliatecode)`, where the "key" (a user's
affiliate code) was a plaintext column sitting right next to the
ciphertext in the same database - a stolen DB backup alone defeated it
completely.

## How it works

- Every order gets its own random 256-bit **DEK** (Data Encryption Key)
  when it's saved.
- The order's serialized data is encrypted with its DEK using an
  authenticated cipher (XChaCha20-Poly1305, via libsodium).
- The DEK itself is then "wrapped" (encrypted) with a single **Master
  Key**, using `sodium_crypto_secretbox`.
- The Master Key lives only in `includes/parms.php`
  (`ORDERDATA_MASTER_KEY_B64`) - a git-ignored file that never touches
  MySQL. A stolen database dump therefore contains only locked boxes with
  no usable key alongside them.

Columns added to `orders` (see `migrations/001_orderdata_envelope_encryption.sql`):

| column             | contents                                   |
|--------------------|---------------------------------------------|
| `orderdata`        | ciphertext (reused existing BLOB column)   |
| `orderdata_nonce`  | nonce for the data ciphertext              |
| `dek_wrapped`      | the DEK, wrapped with the Master Key       |
| `dek_nonce`        | nonce for the wrapped DEK                  |

All of the crypto lives in `includes/crypto.php`:

- `encryptOrderData($plaintext)` - encrypts a serialized orderData string,
  returns the four raw-binary values above.
- `decryptOrderData(...)` - reverses it; throws if the Master Key is wrong
  or the data has been tampered with.
- `resolveOrderData($row, $dbh, $auditContext)` - the function every read
  site actually calls. Given a fetched row, it decrypts via the new scheme
  if `dek_wrapped` is set, otherwise falls back to the row's
  `legacy_orderdata` column (still-unmigrated rows, decrypted the old way
  via `AES_DECRYPT(...)` in the SQL query itself - see below).

## Who can decrypt

The crypto itself doesn't distinguish between the will's owner and an
admin - **that's an application-layer authorization decision**, made by
which rows a query is allowed to fetch (e.g. `WHERE userid = $_SESSION['userid']`).

This codebase's admin support mechanism is a shared master password
(the `ADMIN` constant in `parms.php`): staff log in as a customer using
that password instead of the customer's real one
(`includes/loguserin.php`). There's no separate admin identity - once
logged in, an impersonated session is indistinguishable from the
customer's own, except for a `$_SESSION['adminImpersonation']` flag set at
login time.

Whenever `resolveOrderData()` runs during an `adminImpersonation` session,
it writes a row to `decrypt_audit` (orderid, ownerid, timestamp). Because
the master password is shared, this can only prove *that* it was used to
view an order, not *which* staff member did it - genuine per-staff
accountability would need staff to have their own individual admin logins
instead of one shared password.

## Legacy rows / migration

New columns are nullable so existing rows keep working via the old
`AES_DECRYPT` path (computed as `legacy_orderdata` in every read query)
until they're next saved, at which point `updateOrderData()` re-encrypts
them under the new scheme automatically.

`migrations/backfill_orderdata.php` is a one-time CLI script that migrates
every remaining legacy row in one pass instead of waiting for organic
re-saves:

```bash
php migrations/backfill_orderdata.php
```

Safe to re-run - it only touches rows where `dek_wrapped IS NULL`.

## Deploying this

Before this can go live:

1. **Add `ORDERDATA_MASTER_KEY_B64` to production's `includes/parms.php`.**
   Generate one with:
   ```bash
   php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
   ```
   Back this value up securely (e.g. in a password manager) somewhere
   other than the server - if it's lost, every order becomes permanently
   unreadable, since there is deliberately no copy of it in the database.
2. Run `migrations/001_orderdata_envelope_encryption.sql` against the
   production database.
3. Deploy the code.
4. Run `php migrations/backfill_orderdata.php` on the production server to
   migrate existing orders (optional - organic re-saves will get there
   eventually, but this closes the gap immediately).

## Out of scope for this change

`quickwillorders.orderdata` has a separate, weaker problem - it's only
`base64_encode()`d, not encrypted at all. It wasn't part of this change but
would be a reasonable follow-up using the same `encryptOrderData()` /
`decryptOrderData()` functions.
