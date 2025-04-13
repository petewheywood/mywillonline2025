#/bin/sh
FILE='/var/www/mywillonline/includes/parms.php'
CURRENT_TOKEN=`perl -ne "s/define\('INSTA_ACCESS_TOKEN', \"(.+)\"\);$/\1/ && print" ${FILE}`
REFRESH_URL='https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token='${CURRENT_TOKEN}
NEWTOKEN=`curl -X GET $REFRESH_URL 2>/dev/null | jq '.access_token'`
perl -pi.bak -e "s/define\('INSTA_ACCESS_TOKEN', \".+\"\);$/define('INSTA_ACCESS_TOKEN', ${NEWTOKEN});/" ${FILE}