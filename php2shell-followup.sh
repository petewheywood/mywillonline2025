#!/usr/bin/env bash
#
# php2shell-followup.sh - follow-up to detect-php2shell.sh for Ubuntu 16.04 / Apache 2.4.
#
#   sudo ./php2shell-followup.sh              # read-only: evidence + investigation
#   sudo ./php2shell-followup.sh --apply      # also apply interim hardening (with backups + rollback)
#
# Steps:
#   1. Preserve evidence (logs, process/network state, PHP file hashes) to a root-only dir
#   2. Find EVERY vhost docroot from the Apache config and run detect-php2shell.sh on each
#   3. Look in the logs for SQL-injection probes (incl. images/quill.php?id=)
#   4. Find backup/config files under any docroot, test whether they are served over HTTP,
#      and check the logs for anyone fetching them
#   5. (--apply) Apache rule denying backup/dotfiles, PHP ini to disable session upload
#      progress, and move backup files out of the docroots
#
# It does NOT upgrade PHP or the OS - see the "Next steps" printed at the end.

SINCE="2026-10-14"
APACHE_CONF="/etc/apache2"
DETECT="$(dirname "$(readlink -f "$0")")/detect-php2shell.sh"
APPLY=0
STAMP=$(date +%Y%m%d-%H%M%S)
OUT="/root/php2shell-evidence-$STAMP"

while [ $# -gt 0 ]; do
  case "$1" in
    --apply)  APPLY=1; shift ;;
    --since)  SINCE="$2"; shift 2 ;;
    --detect) DETECT="$2"; shift 2 ;;
    --out)    OUT="$2"; shift 2 ;;
    -h|--help) sed -n '2,19p' "$0"; exit 0 ;;
    *) echo "Unknown option: $1" >&2; exit 2 ;;
  esac
done

[ "$(id -u)" -eq 0 ] || { echo "Run as root (sudo)." >&2; exit 2; }
date -d "$SINCE" >/dev/null 2>&1 || { echo "Bad --since date" >&2; exit 2; }
SINCE_KEY=$(date -d "$SINCE" +%Y%m%d)

if [ -t 1 ]; then RED=$'\e[31m'; GRN=$'\e[32m'; YLW=$'\e[33m'; BLD=$'\e[1m'; RST=$'\e[0m'
else RED=; GRN=; YLW=; BLD=; RST=; fi
section() { printf '\n%s== %s ==%s\n' "$BLD" "$1" "$RST"; }
warn()    { printf '%s[!] %s%s\n' "$RED" "$1" "$RST"; }
ok()      { printf '%s[ok] %s%s\n' "$GRN" "$1" "$RST"; }
info()    { printf '%s[..] %s%s\n' "$YLW" "$1" "$RST"; }

umask 077
mkdir -p "$OUT" || exit 2
TMP=$(mktemp -d /tmp/php2shell-fu.XXXXXX) || exit 2
trap 'rm -rf "$TMP"' EXIT

###########################################################################
section "1. Preserve evidence -> $OUT"
###########################################################################
LOGDIRS="/var/log/apache2"
tar -czf "$OUT/apache-logs.tgz" $LOGDIRS 2>/dev/null && ok "Apache logs archived"
cp -a /etc/apache2 "$OUT/apache2-config" 2>/dev/null
{ date; uname -a; echo; ps auxwwf; echo; ss -tanp 2>/dev/null; echo; last -n 50; echo; who -a; } > "$OUT/host-state.txt" 2>&1
for u in www-data root; do crontab -l -u "$u" 2>/dev/null | sed "s/^/$u: /"; done > "$OUT/crontabs.txt"
ok "Process/network/login state saved"
echo "Apache: $(apache2 -v 2>/dev/null | head -1)   PHP: $(php -v 2>/dev/null | head -1)"
echo "OS:     $(lsb_release -ds 2>/dev/null)"

###########################################################################
section "2. Every vhost docroot on this server"
###########################################################################
# prints "ServerName<TAB>DocumentRoot" for each vhost
awk '
  /<VirtualHost/            { sn=""; dr="" }
  tolower($1)=="servername" { sn=$2 }
  tolower($1)=="documentroot" { dr=$2; gsub(/"/,"",dr) }
  /<\/VirtualHost>/         { if (dr != "") print (sn==""?"-":sn) "\t" dr }
' "$APACHE_CONF"/sites-enabled/*.conf 2>/dev/null | sort -u > "$TMP/vhosts"
cat "$TMP/vhosts" | sed 's/^/   /'
cut -f2 "$TMP/vhosts" | sort -u > "$TMP/docroots"

if [ -x "$DETECT" ]; then
  while read -r root; do
    [ -d "$root" ] || continue
    name=$(echo "$root" | tr '/' '_')
    "$DETECT" --since "$SINCE" --webroot "$root" > "$OUT/detect$name.txt" 2>&1
    printf '\n   %s%s%s\n' "$BLD" "$root" "$RST"
    grep -E '^\S*\[!\]' "$OUT/detect$name.txt" | sed 's/^/      /' | cut -c1-200
    grep -q '\[!\]' "$OUT/detect$name.txt" || echo "      no flags"
  done < "$TMP/docroots"
else
  warn "detect-php2shell.sh not found at $DETECT (use --detect PATH)"
fi

# hash baseline of every PHP file, for later comparison against a clean checkout
while read -r root; do
  find "$root" -type f \( -iname '*.php' -o -iname '*.phtml' -o -iname '.htaccess' \) -print0 2>/dev/null | xargs -0 sha256sum
done < "$TMP/docroots" > "$OUT/php-file-hashes.sha256" 2>/dev/null
ok "SHA-256 of all PHP/.htaccess files saved (php-file-hashes.sha256)"

###########################################################################
section "3. SQL injection probes in access logs (since $SINCE)"
###########################################################################
ACC="$TMP/access.all"
find /var/log/apache2 -type f -name '*access*' 2>/dev/null | sort | while read -r f; do zcat -f "$f" 2>/dev/null; done \
  | awk -v since="$SINCE_KEY" '
      BEGIN { split("Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec", m, " ")
              for (i = 1; i <= 12; i++) mon[m[i]] = sprintf("%02d", i) }
      { if (match($0, /\[[0-9][0-9]\/[A-Za-z][a-z][a-z]\/[0-9][0-9][0-9][0-9]:/)) {
          split(substr($0, RSTART + 1, RLENGTH - 2), p, "/")
          if (p[3] mon[p[2]] p[1] < since) next } print }' > "$ACC"

# quill.php takes ?id=<number> (patched to cast to int). Anything else is a probe.
grep -E 'quill\.php\?([^ ]*&)?id=[0-9]*[^0-9& "]' "$ACC" > "$TMP/s1"
if [ -s "$TMP/s1" ]; then
  warn "quill.php called with a non-numeric id: $(wc -l < "$TMP/s1") request(s)"; head -n 15 "$TMP/s1" | cut -c1-250 | sed 's/^/      /'
else ok "quill.php: only numeric ids requested"; fi

grep -iE "union(%20|\+| )+(all(%20|\+| )+)?select|information_schema|sleep(\(|%28)[0-9]|benchmark(\(|%28)|updatexml(\(|%28)|extractvalue(\(|%28)|(%27|')(%20|\+| )*(or|and)(%20|\+| )+[0-9a-z]+(%3d|=)" "$ACC" > "$TMP/s2"
if [ -s "$TMP/s2" ]; then
  warn "Generic SQLi patterns in requests: $(wc -l < "$TMP/s2") request(s). Response codes:"
  grep -oE '" [0-9]{3} ' "$TMP/s2" | sort | uniq -c | sort -rn | sed 's/^/      /'
  echo "      Top sources:"; awk '{for(i=1;i<=2;i++) if ($i ~ /^[0-9.]+$/) {print $i; break}}' "$TMP/s2" | sort | uniq -c | sort -rn | head -5 | sed 's/^/        /'
  head -n 10 "$TMP/s2" | cut -c1-250 | sed 's/^/      /'
else ok "No generic SQLi patterns"; fi

###########################################################################
section "4. Backup / config files under docroots - are they served?"
###########################################################################
: > "$TMP/exposed_candidates"
while read -r root; do
  [ -d "$root" ] && find "$root" -type f \( -iname '*.bak' -o -iname '*.old' -o -iname '*.orig' -o -iname '*.save' \
     -o -iname '*.swp' -o -iname '*~' -o -iname '*.sql' -o -iname '*.sql.gz' -o -iname '*.docker' -o -iname '*.tgz' \
     -o -iname '*.zip' -o -iname '.env' -o -iname 'parms.php.*' \) -not -path '*/.git/*' 2>/dev/null
done < "$TMP/docroots" | sort -u > "$TMP/exposed_candidates"

if [ ! -s "$TMP/exposed_candidates" ]; then ok "No backup-style files under any docroot"
else
  warn "$(wc -l < "$TMP/exposed_candidates") backup-style file(s) under docroots:"
  while read -r f; do
    # which vhost serves this?
    line=$(awk -F'\t' -v f="$f" 'index(f, $2"/")==1 && $1!="-" {print $1"\t"$2; exit}' "$TMP/vhosts")
    sn=${line%%$'\t'*}; dr=${line##*$'\t'}
    if [ -n "$sn" ]; then
      path=${f#$dr}
      code=$(curl -sk -L -o /dev/null -m 10 -w '%{http_code}' \
             --resolve "$sn:443:127.0.0.1" --resolve "$sn:80:127.0.0.1" "https://$sn$path" 2>/dev/null)
    else code="n/a"; fi
    case "$code" in
      200) printf '   %s%-4s SERVED OVER HTTP%s  %s\n' "$RED" "$code" "$RST" "$f" ;;
      *)   printf '   %-4s %s\n' "$code" "$f" ;;
    esac
    echo "$code $f" >> "$OUT/exposed-files.txt"
  done < "$TMP/exposed_candidates"

fi

# has anyone fetched backup-style files? (runs even if the files have since been moved)
grep -E '\.(bak|old|orig|save|swp|sql|docker|tgz|zip)(\.gz)?[ ?"]|parms\.php\.|/\.env' "$ACC" \
  | grep -E '" 200 ' > "$TMP/s3"
if [ -s "$TMP/s3" ]; then
  warn "Successful (200) fetches of backup-style files in the logs: $(wc -l < "$TMP/s3")"
  head -n 15 "$TMP/s3" | cut -c1-250 | sed 's/^/      /'
  warn "If any are parms.php.* / .env: treat DB, mail, payment and API credentials in them as COMPROMISED -> rotate."
else ok "No 200 responses for backup-style files in the logs since $SINCE"
     info "(older logs may differ - rerun with --since 2020-01-01 to check full history)"; fi

# Customer-document leftovers (informational)
if [ -d /var/www/tmp ]; then
  n=$(find /var/www/tmp -type f -mtime +1 2>/dev/null | wc -l)
  [ "$n" -gt 0 ] && info "/var/www/tmp holds $n generated file(s) older than 1 day (wills/PDFs). Consider a cron job: find /var/www/tmp -type f -mtime +1 -delete"
fi

###########################################################################
section "5. Interim hardening"
###########################################################################
if [ "$APPLY" -ne 1 ]; then
  info "Skipped (read-only run). Re-run with --apply to install the changes described below."
  echo "   a) Apache conf-available/php2shell-hardening.conf: deny backup files, .git/.env/.svn"
  echo "   b) PHP: session.upload_progress.enabled=0 and expose_php=0 (conf.d drop-in)"
  echo "   c) Move backup-style files from section 4 into $OUT/quarantined-files"
else
  # a) Apache rule
  CONF=$APACHE_CONF/conf-available/php2shell-hardening.conf
  [ -f "$CONF" ] && cp -a "$CONF" "$OUT/php2shell-hardening.conf.prev"
  cat > "$CONF" <<'EOF'
# Added by php2shell-followup.sh - never serve backup/config leftovers
<FilesMatch "(\.(bak|old|orig|save|swp|sql|docker|dist|tgz)$|~$|^\.(git|svn|env|DS_Store))">
    Require all denied
</FilesMatch>
<DirectoryMatch "/\.(git|svn)">
    Require all denied
</DirectoryMatch>
EOF
  a2enconf php2shell-hardening >/dev/null
  if apache2ctl configtest 2>&1 | grep -q 'Syntax OK'; then
    ok "Apache hardening conf enabled (reload happens after PHP ini step)"
  else
    warn "apache2ctl configtest failed - rolling back Apache change"
    a2disconf php2shell-hardening >/dev/null; rm -f "$CONF"
  fi

  # b) PHP ini drop-in (does the app use upload progress? check first)
  if grep -rIl 'upload_progress\|UPLOAD_PROGRESS' $(cut -f2 "$TMP/vhosts" | sort -u) --include='*.php' 2>/dev/null | grep -q .; then
    warn "App code references upload progress - skipping session.upload_progress change"
  else
    for d in /etc/php/*/apache2/conf.d; do
      [ -d "$d" ] || continue
      printf '; Added by php2shell-followup.sh\nsession.upload_progress.enabled = 0\nexpose_php = 0\n' > "$d/99-php2shell-mitigation.ini"
      ok "Wrote $d/99-php2shell-mitigation.ini"
    done
  fi

  # c) quarantine backup files
  if [ -s "$TMP/exposed_candidates" ]; then
    while read -r f; do
      dest="$OUT/quarantined-files$f"; mkdir -p "$(dirname "$dest")"
      mv "$f" "$dest" && echo "   moved $f"
    done < "$TMP/exposed_candidates"
    ok "Backup-style files moved to $OUT/quarantined-files (chmod 700 dir; delete after rotating creds)"
  fi

  if apache2ctl configtest 2>&1 | grep -q 'Syntax OK'; then
    systemctl reload apache2 && ok "Apache reloaded"
  else warn "Config test failed - NOT reloading. Fix manually."; fi
  info "Verify the site still works: generate a test will, upload a document, send a test email."
fi

###########################################################################
section "Next steps (cannot be scripted safely)"
###########################################################################
cat <<EOF
 1. PATCH PHP - the only real fix. Ubuntu 16.04 + PHP 7.4 cannot receive it. Build a new
    Ubuntu 24.04 host with PHP 8.4.27+ (your Dockerfile is a starting template), test, cut over DNS.
    Check first whether a patched 7.4 is even obtainable here:  apt-cache policy php7.4
 2. Until then, put a WAF/reverse proxy (AWS WAF on an ALB, or Cloudflare) in front, and restrict
    the security group so only that proxy can reach ports 80/443.
 3. Snapshot this instance now (EBS snapshot) so you have a forensic copy:
      aws ec2 create-snapshot --volume-id <vol-id> --description "pre-php2shell-remediation $STAMP"
 4. Other sites on this box (demographicdistribution, partsghoul, dev_mywillonline) share the same
    PHP and are equally exposed; patch/migrate them together.
 5. Ubuntu 16.04 itself is past standard support; plan the OS upgrade as part of the move.
 6. Add %{Content-Length}i to your LogFormat so future oversized-POST attempts are visible.

Evidence directory: $OUT
EOF
