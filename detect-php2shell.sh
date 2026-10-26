#!/usr/bin/env bash
#
# detect-php2shell.sh - read-only triage for signs of attempted/successful
# exploitation of PHP2Shell (CVE-2026-109618 / CVE-2026-109619) and generic
# PHP webshell / RCE activity. Written for Ubuntu 16.04 + Apache 2.4 (bash 4.3).
#
# It changes nothing. Run as root (sudo) so it can read all logs, process
# info and other users' crontabs:
#
#   sudo ./detect-php2shell.sh [--since YYYY-MM-DD] [--webroot DIR] [--limit N]
#
# A hit is a lead to investigate, not proof. No hit does NOT prove you are
# clean: Apache access logs do not record POST bodies, so the main PHP2Shell
# trigger (malformed multipart/urlencoded bodies) is largely invisible to them.

SINCE="2026-10-14"            # exploitation attempts reported from ~17 Oct
WEBROOT="/var/www/mywillonline"
LIMIT=15                      # lines shown per finding
APACHE_CONF="/etc/apache2"

while [ $# -gt 0 ]; do
  case "$1" in
    --since)   SINCE="$2"; shift 2 ;;
    --webroot) WEBROOT="$2"; shift 2 ;;
    --limit)   LIMIT="$2"; shift 2 ;;
    -h|--help) sed -n '2,15p' "$0"; exit 0 ;;
    *) echo "Unknown option: $1" >&2; exit 2 ;;
  esac
done

if ! date -d "$SINCE" >/dev/null 2>&1; then
  echo "Bad --since date: $SINCE (use YYYY-MM-DD)" >&2; exit 2
fi
SINCE_KEY=$(date -d "$SINCE" +%Y%m%d)
[ "$(id -u)" -eq 0 ] || echo "WARNING: not running as root; some checks will be incomplete." >&2
[ -d "$WEBROOT" ] || { echo "Webroot not found: $WEBROOT" >&2; exit 2; }

TMP=$(mktemp -d /tmp/php2shell.XXXXXX) || exit 2
trap 'rm -rf "$TMP"' EXIT
FINDINGS=0

if [ -t 1 ]; then RED=$'\e[31m'; GRN=$'\e[32m'; YLW=$'\e[33m'; BLD=$'\e[1m'; RST=$'\e[0m'
else RED=; GRN=; YLW=; BLD=; RST=; fi

section() { printf '\n%s== %s ==%s\n' "$BLD" "$1" "$RST"; }

# finding TITLE FILE  - FILE holds the matching lines; prints count + sample.
finding() {
  local title="$1" file="$2" n
  n=$(wc -l < "$file" | tr -d ' ')
  if [ "$n" -gt 0 ]; then
    FINDINGS=$((FINDINGS + 1))
    printf '%s[!] %s: %s hit(s)%s\n' "$RED" "$title" "$n" "$RST"
    head -n "$LIMIT" "$file" | cut -c1-300 | sed 's/^/      /'
    [ "$n" -gt "$LIMIT" ] && printf '      ... %s more\n' "$((n - LIMIT))"
  else
    printf '%s[ok] %s%s\n' "$GRN" "$title" "$RST"
  fi
}

###########################################################################
# 1. Locate every Apache log (all vhosts) and split access vs error
###########################################################################
section "Apache logs"

LOGDIRS="$TMP/logdirs"
{
  echo /var/log/apache2
  # Any CustomLog/ErrorLog/TransferLog path declared in any vhost/conf.
  grep -rhE '^\s*(CustomLog|ErrorLog|TransferLog)\s' "$APACHE_CONF" 2>/dev/null \
    | awk '{print $2}' | tr -d '"' \
    | sed 's#\${APACHE_LOG_DIR}#/var/log/apache2#g' \
    | grep '^/' | while read -r p; do dirname "$p"; done
} | sort -u > "$LOGDIRS"

ALL_LOGS="$TMP/all_logs"
: > "$ALL_LOGS"
while read -r d; do
  [ -d "$d" ] && find "$d" -maxdepth 2 -type f \( -name '*log*' \) 2>/dev/null
done < "$LOGDIRS" | sort -u > "$ALL_LOGS"

ERR_LOGS="$TMP/err_logs"; ACC_LOGS="$TMP/acc_logs"
grep -i 'error' "$ALL_LOGS" > "$ERR_LOGS"
grep -vi 'error' "$ALL_LOGS" > "$ACC_LOGS"
echo "Log directories: $(tr '\n' ' ' < "$LOGDIRS")"
echo "Access logs: $(wc -l < "$ACC_LOGS" | tr -d ' ')   Error logs: $(wc -l < "$ERR_LOGS" | tr -d ' ')   (rotated .gz included)"
echo "Access-log entries before $SINCE are ignored."

# Concatenate (zcat -f handles plain and gzip) and drop entries older than SINCE.
ACC="$TMP/access.all"
while read -r f; do zcat -f "$f" 2>/dev/null; done < "$ACC_LOGS" | awk -v since="$SINCE_KEY" '
  BEGIN { split("Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec", m, " ")
          for (i = 1; i <= 12; i++) mon[m[i]] = sprintf("%02d", i) }
  {
    if (match($0, /\[[0-9][0-9]\/[A-Za-z][a-z][a-z]\/[0-9][0-9][0-9][0-9]:/)) {
      split(substr($0, RSTART + 1, RLENGTH - 2), p, "/")
      if (p[3] mon[p[2]] p[1] < since) next
    }
    print
  }' > "$ACC"

ERR="$TMP/error.all"
while read -r f; do zcat -f "$f" 2>/dev/null; done < "$ERR_LOGS" > "$ERR"

###########################################################################
# 2. Error logs - the most reliable PHP2Shell signal
###########################################################################
section "Error logs (PHP2Shell crash / OOM signatures)"

# OOM during request init: "Allowed memory size ... exhausted ... Unknown on line 0"
grep -E 'Allowed memory size of [0-9]+ bytes exhausted' "$ERR" \
  | grep -E 'Unknown on line 0' > "$TMP/f1"
finding "PHP memory exhaustion before any script ran ('Unknown on line 0')" "$TMP/f1"

# Worker crashes (memory corruption)
grep -E 'AH00052|exit signal (Segmentation fault|Bus error|Aborted|Illegal instruction)' "$ERR" > "$TMP/f2"
finding "Apache child crashed (segfault/abort)" "$TMP/f2"

# Any other memory exhaustion - weaker, but note bursts
grep -E 'Allowed memory size of [0-9]+ bytes exhausted' "$ERR" \
  | grep -vE 'Unknown on line 0' > "$TMP/f3"
finding "PHP memory exhaustion inside scripts (weak signal; check for bursts)" "$TMP/f3"

# Things that look like command execution / shell attempts in PHP errors
grep -iE 'sh: [0-9]+: |/bin/(ba)?sh|base64_decode\(|eval\(\)''d code|Cannot execute|proc_open|shell_exec|passthru' "$ERR" > "$TMP/f4"
finding "Shell / eval related PHP errors" "$TMP/f4"

###########################################################################
# 3. Access logs
###########################################################################
section "Access logs (since $SINCE)"

# POSTs that ended abnormally (server error, timeout, too large, client abort)
grep -E '"POST [^"]*" (5[0-9][0-9]|408|413|499) ' "$ACC" > "$TMP/a1"
finding "POSTs ending in 5xx/408/413/499" "$TMP/a1"

if [ -s "$TMP/a1" ]; then
  echo "      Top source IPs for those:"
  awk '{print $1}' "$TMP/a1" | sort | uniq -c | sort -rn | head -n 10 | sed 's/^/        /'
fi

# Session upload-progress field names / abnormal session ids in the URL
grep -E 'PHP_SESSION_UPLOAD_PROGRESS|PHPSESSID=[^ &";]{64,}' "$ACC" > "$TMP/a2"
finding "Session-upload-progress / oversized PHPSESSID in request line" "$TMP/a2"

# CVE-2024-4577 style & CGI argument injection (PHP 7.4 is unpatched for these too)
grep -iE '%ad|php://input|allow_url_include|auto_prepend_file|cgi-bin/php|\?(-|%2d)[a-z]([ =+]|%20|$)' "$ACC" > "$TMP/a3"
finding "PHP-CGI argument injection / php:// wrappers" "$TMP/a3"

# Classic webshell / command-exec request content
grep -iE 'whoami|/etc/passwd|base64_decode|eval\(|eval%28|system\(|system%28|passthru|shell_exec|/bin/(ba)?sh|(wget|curl)(%20|\+| )https?|c99|r57|b374k|wso|filesman' "$ACC" \
  | grep -vE '"(GET|HEAD) [^"]*\.(css|js|png|jpe?g|gif|woff2?|ico|svg) ' > "$TMP/a4"
finding "Command-exec / webshell strings in requests" "$TMP/a4"

# Direct execution of PHP inside upload-like dirs (adjust names to taste)
grep -iE '"(GET|POST) /[^" ]*(upload|files|images|tmp|cache|img)[^" ]*\.(php[0-9]?|phtml|phar|inc)([?" ])' "$ACC" > "$TMP/a5"
finding "Requests executing PHP from upload-style directories" "$TMP/a5"

# Very long request lines (encoded payloads)
awk 'length($0) > 4000' "$ACC" > "$TMP/a6"
finding "Abnormally long request lines (>4000 chars)" "$TMP/a6"

# Context only: heaviest POSTers (not counted as a finding)
echo "      Context - top 10 POST source IPs:"
grep '"POST ' "$ACC" | awk '{print $1}' | sort | uniq -c | sort -rn | head -n 10 | sed 's/^/        /'

###########################################################################
# 4. Webroot filesystem
###########################################################################
section "Webroot: $WEBROOT"

# Files changed since SINCE (content)
find "$WEBROOT" -path "$WEBROOT/.git" -prune -o -type f -newermt "$SINCE" \
  -printf '%TY-%Tm-%Td %TH:%TM  %u:%g  %m  %p\n' 2>/dev/null | sort > "$TMP/w1"
finding "Files modified since $SINCE (expected only if you deployed - compare to deploy dates)" "$TMP/w1"

# ctime recent but mtime old: classic timestomping, or files copied in with old mtimes
find "$WEBROOT" -path "$WEBROOT/.git" -prune -o -type f -newerct "$SINCE" ! -newermt "$SINCE" \
  -printf '%TY-%Tm-%Td (mtime)  %CY-%Cm-%Cd (ctime)  %u  %p\n' 2>/dev/null > "$TMP/w2"
finding "Files with recent ctime but old mtime (possible timestomping)" "$TMP/w2"

# PHP-executable files in places that should hold user content
UPL="$TMP/w3"; : > "$UPL"
for d in uploads images css js fonts; do
  [ -d "$WEBROOT/$d" ] && find "$WEBROOT/$d" -type f \
    \( -iname '*.php' -o -iname '*.php[0-9]' -o -iname '*.phtml' -o -iname '*.phar' -o -iname '*.inc' -o -iname '.htaccess' -o -iname '.user.ini' \) 2>/dev/null >> "$UPL"
done
finding "PHP/.htaccess/.user.ini inside uploads/images/css/js/fonts" "$UPL"

# Double extensions, hidden PHP, odd names
find "$WEBROOT" -path "$WEBROOT/.git" -prune -o -type f \
  \( -iname '*.php.*' -o -iname '.*.php' -o -iname '*.php;*' -o -iname '*.jpg.php' -o -iname '*.png.php' -o -iname '*.gif.php' \) -print 2>/dev/null > "$TMP/w4"
finding "Double-extension / hidden PHP file names" "$TMP/w4"

# Non-PHP files that actually contain PHP code (image/txt webshells)
find "$WEBROOT" -path "$WEBROOT/.git" -prune -o -type f \
  \( -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.png' -o -iname '*.gif' -o -iname '*.txt' -o -iname '*.ico' -o -iname '*.pdf' \) \
  -size -2M -print0 2>/dev/null | xargs -0 grep -lE '<\?php|<\?=' 2>/dev/null > "$TMP/w5"
finding "Non-PHP files containing PHP tags" "$TMP/w5"

# Webshell / obfuscation signatures inside PHP files
find "$WEBROOT" -path "$WEBROOT/.git" -prune -o -type f \
  \( -iname '*.php' -o -iname '*.phtml' -o -iname '*.php[0-9]' -o -iname '*.inc' -o -iname '*.phar' \) -print0 2>/dev/null \
  | xargs -0 grep -nIE \
  'eval\s*\(\s*(base64_decode|gzinflate|gzuncompress|str_rot13|stripslashes\s*\(\s*\$_)|(eval|assert|system|passthru|shell_exec|exec|popen|proc_open)\s*\(\s*\$_(GET|POST|REQUEST|COOKIE|SERVER)|preg_replace\s*\(\s*.([/#~]).*\1[a-z]*e[a-z]*.\s*,|\$\{?_(GET|POST|REQUEST|COOKIE)\}?\s*\[[^]]+\]\s*\(|FilesMan|c99shell|r57shell|b374k|WSO [0-9]|Shell by|fromCharCode\(.*\beval\b|create_function\s*\(\s*.{0,10}\$_(GET|POST|REQUEST)' \
  2>/dev/null | cut -c1-220 > "$TMP/w6"
finding "Webshell / obfuscation signatures in PHP files" "$TMP/w6"

# Generic obfuscation: very long single lines of PHP (packed payloads)
find "$WEBROOT" -path "$WEBROOT/.git" -prune -o -type f -iname '*.php' -print0 2>/dev/null \
  | xargs -0 awk 'length($0) > 5000 { print FILENAME ": line " FNR " is " length($0) " chars"; nextfile }' 2>/dev/null > "$TMP/w7"
finding "PHP files with extremely long lines (packed/obfuscated code)" "$TMP/w7"

# Config persistence
find "$WEBROOT" -path "$WEBROOT/.git" -prune -o -type f \( -name '.htaccess' -o -name '.user.ini' -o -name 'php.ini' \) -print0 2>/dev/null \
  | xargs -0 grep -nIiE 'auto_prepend_file|auto_append_file|php_value\s+auto_|AddType\s+application/x-httpd-php|SetHandler\s+application/x-httpd-php|allow_url_include' 2>/dev/null > "$TMP/w8"
finding "auto_prepend/append or PHP handler overrides in .htaccess/.user.ini" "$TMP/w8"

# World-writable files/dirs
find "$WEBROOT" -path "$WEBROOT/.git" -prune -o \( -type f -o -type d \) -perm -0002 ! -type l -printf '%m %u %p\n' 2>/dev/null > "$TMP/w9"
finding "World-writable files/dirs" "$TMP/w9"

# SUID/SGID
find "$WEBROOT" -type f \( -perm -4000 -o -perm -2000 \) -printf '%m %u %p\n' 2>/dev/null > "$TMP/w10"
finding "SUID/SGID files in webroot" "$TMP/w10"

# Git comparison, if the webroot is a checkout
if [ -d "$WEBROOT/.git" ] && command -v git >/dev/null 2>&1; then
  git -C "$WEBROOT" status --porcelain 2>/dev/null \
    | grep -E '\.(php|phtml|phar|inc|htaccess|ini)$|\.htaccess' > "$TMP/w11"
  finding "Git: modified/untracked PHP & config files vs. repo (review with git diff)" "$TMP/w11"
else
  echo "[--] $WEBROOT is not a git checkout; skipping repo comparison."
  echo "     Tip: diff against a clean copy of your repo: diff -rq <clean> $WEBROOT"
fi

###########################################################################
# 5. Host-level post-exploitation signs
###########################################################################
section "Host: processes, persistence, staging dirs"

# Web workers should not have shells or network tools as children / run them
WEBUSER=$(ps -eo user,comm 2>/dev/null | awk '$2 ~ /^apache2$|^httpd$/ && $1 != "root" {print $1; exit}')
WEBUSER=${WEBUSER:-www-data}
echo "Apache worker user: $WEBUSER"

ps -eo user:20,pid,ppid,etime,args 2>/dev/null \
  | awk -v u="$WEBUSER" '$1 == u' \
  | grep -E ' (sh|bash|dash|zsh|nc|ncat|netcat|socat|wget|curl|python[0-9.]*|perl|ruby|php[0-9.]*|telnet|nmap|base64|chmod|crontab) ' \
  | grep -vE 'grep -E' > "$TMP/h1"
finding "Processes owned by $WEBUSER running shells/network/script tools" "$TMP/h1"

# Outbound connections held by the web user
if command -v ss >/dev/null 2>&1; then
  ss -tnpH state established 2>/dev/null | grep -E "users:\(\(\"(sh|bash|dash|nc|ncat|socat|python[0-9.]*|perl|php[0-9.]*|curl|wget)\"" > "$TMP/h2"
  finding "Established connections owned by shells/tools (reverse shell?)" "$TMP/h2"
fi

# Persistence
{
  [ -f "/var/spool/cron/crontabs/$WEBUSER" ] && sed "s#^#crontab($WEBUSER): #" "/var/spool/cron/crontabs/$WEBUSER"
  find /etc/cron.d /etc/cron.hourly /etc/cron.daily /var/spool/cron/crontabs -type f -newermt "$SINCE" -printf 'modified since %TY-%Tm-%Td: %p\n' 2>/dev/null
  find /etc/systemd/system /lib/systemd/system -type f -newermt "$SINCE" -printf 'modified since %TY-%Tm-%Td: %p\n' 2>/dev/null
  WH=$(getent passwd "$WEBUSER" | cut -d: -f6)
  [ -n "$WH" ] && find "$WH" -maxdepth 2 \( -name authorized_keys -o -name '.bashrc' -o -name '.profile' \) -type f -printf 'webuser home file: %p (mtime %TY-%Tm-%Td)\n' 2>/dev/null
} > "$TMP/h3"
finding "Persistence: web-user crontab, recently changed cron/systemd, web-user home dotfiles" "$TMP/h3"

# Staging areas: files created by the web user in tmp dirs since SINCE
find /tmp /var/tmp /dev/shm /var/www/tmp -xdev -type f -user "$WEBUSER" -newermt "$SINCE" \
  -printf '%TY-%Tm-%Td %TH:%TM  %m  %s bytes  %p\n' 2>/dev/null \
  | grep -vE '/sess_[a-z0-9,-]+$' > "$TMP/h4"
finding "Files created by $WEBUSER in /tmp, /var/tmp, /dev/shm, /var/www/tmp (excluding PHP sessions)" "$TMP/h4"

find /tmp /var/tmp /dev/shm -xdev -type f -perm /111 -user "$WEBUSER" \
  -printf '%TY-%Tm-%Td  %m  %p\n' 2>/dev/null > "$TMP/h5"
finding "Executable files owned by $WEBUSER in tmp dirs" "$TMP/h5"

# Recently modified system binaries / new accounts (post-exploitation privesc)
find /usr/bin /usr/sbin /bin /sbin -xdev -type f -newermt "$SINCE" \
  -printf '%TY-%Tm-%Td  %p\n' 2>/dev/null > "$TMP/h6"
finding "System binaries modified since $SINCE (expected only after apt upgrades)" "$TMP/h6"

awk -F: '$3 == 0 && $1 != "root"' /etc/passwd > "$TMP/h7"
finding "Extra UID-0 accounts" "$TMP/h7"

###########################################################################
echo
if [ "$FINDINGS" -eq 0 ]; then
  printf '%sNo indicators found by these checks.%s This is not proof of a clean host (see header notes).\n' "$GRN" "$RST"
else
  printf '%s%d check(s) flagged.%s Preserve evidence BEFORE cleaning up: copy /var/log/apache2 and the flagged files off-box,\n' "$RED" "$FINDINGS" "$RST"
  echo "snapshot the server if you can, and rotate DB/app/API credentials if shell-level activity is confirmed."
fi
exit "$((FINDINGS > 0 ? 1 : 0))"
