<?php
# setup the various session info - at session establishment 

/* Retrieve visit info for user and log to database */  
# retrieve info from the HTTP request
$clientIP = $_SESSION['clientIP'] = $_SERVER['REMOTE_ADDR'];

$userAgent = $_SERVER['HTTP_USER_AGENT'];
$serverName = $_SERVER['SERVER_NAME'];
$referer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : '';
$uri = $_SERVER['REQUEST_URI'];
$querystring = isset($_SESSION['QUERY_STRING']) ? $_SESSION['QUERY_STRING'] : '';
// Log to a database for our reference

# check if this sessionid is already in the database and then update, else insert, if it is not there we have come from regenwills and we don't want to update db
if (isset($sessionID)) {
  $result = $dbh->query("SELECT id FROM visits WHERE sessionid = '$sessionID';");
  if ($visitid = $result->fetchColumn()) {
    $dbh->exec("UPDATE visits SET lastactivity = NOW(), lasturi = '$uri' WHERE sessionid = '$sessionID';");
    $_SESSION['visit_id'] = $visitid;
  } else {
    $sql = "INSERT INTO visits (sessionid, visitdate, ipaddress, useragent, servername, referer, uri, querystring) VALUES ('$sessionID', NOW(), '$clientIP', '$userAgent', '$serverName', '$referer', '$uri', '$querystring');";
    $dbh->exec($sql);
    $_SESSION['visit_id'] = $dbh->lastInsertId();
  }
}

# add in some mobile/tablet checking
require_once ('Mobile_Detect.php');
$detect = new Mobile_Detect;
$_SESSION['deviceType'] = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

$_SESSION['serverName'] = $serverName;
$_SESSION['IE'] = preg_match("/MSIE (\S+)/", $userAgent, $browser) ? $browser[1] : '0';

# set session variables from parameters set in parms.php
$_SESSION['discount'] = DISCOUNT;
$_SESSION['sitename'] = SITENAME; # default set in parms.php
$_SESSION['sitecompany'] = SITECOMPANY; # default set in parms.php
$_SESSION['siteemaildomain'] = SITEEMAILDOMAIN; # default set in parms.php
$_SESSION['siteabn'] = SITEABN; # default set in parms.php
$_SESSION['siteemail'] = SITEEMAIL; # default set in parms.php
$_SESSION['siteaddress'] = SITEADDRESS; # default set in parms.php
$_SESSION['sitephone'] = SITEPHONE; # default set in parms.php
$_SESSION['quickwill'] = QUICKWILL; # session boolean to turn on quickwills

  

/* This will retrieve product information */
$query = "SELECT p.name, p.shortname, p.template, p.id as productid, p.priceinctax FROM product p;";
$sth = $dbh->query($query);
$rows = $sth->fetchAll(PDO::FETCH_ASSOC);
$products = array();
foreach($rows as $row) {
  $shortname = $row['shortname'];
  $products[$shortname]['name'] = $row['name'];
  $products[$shortname]['template'] = $row['template'];
  $products[$shortname]['priceinctax'] = $row['priceinctax'];
  $products[$shortname]['productid'] = $row['productid'];
}
$_SESSION['products'] = $products;

# add in FREE word if singlewill is free
$free = $products['singlewill']['priceinctax'] > 0 ? '' : 'FREE ';

# retrieve pet carer organisations
$result = $dbh->query("SELECT * FROM petcarers ORDER BY organisation;");
$rows = $result->fetchAll();
foreach($rows as $row) {
  $petcarer = array();
  $petcarer['organisation'] = $row['organisation'];
  $petcarer['email'] = $row['emailaddress'];
  $petcarer['phone'] = $row['phone'];
  $webphone = preg_replace('/[^\d]+/', '', $row['phone']);
  $webphone = "+61" . preg_replace('/^0*/', '', $webphone);
  $petcarer['webphone'] = $webphone;
  $petcarer['abn'] = $row['abn'];
  $petcarer['weburl'] = $row['weburl'];
  if (in_array($row['state'], array('NSW', 'VIC','SA','NT','WA','QLD','ACT'))) $country = 'Australia';
  $petcarer['onelineaddress'] = replace_ws(isset($row['address1']) ? $row['address1'] : '') . (isset($row['address2']) && $row['address2'] > '' ? ' ' . $row['address2'] : '') . ', ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '');
  $petcarer['fulladdress'] = replace_ws(isset($row['address1']) ? $row['address1'] : '') . (isset($row['address2']) && $row['address2'] > '' ? ' \newline ' . $row['address2'] : '') . ' \newline ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . ' \newline ' . $country;
  $petcarer['info'] = '<p>More information on the <strong>' . $row['organisation'] . '</strong> <em>(ABN: ' . $row['abn'] . ')</em> pet legacy program can be found on their <a href="' . $row['weburl'] . '" target="_blank" alt="' . $row['organisation'] . '">web site</a> or by contacting them during business hours on <a href="tel:' . $webphone . '" alt="Call ' . $row['organisation'] . '">' . $row['phone'] . '</a>.</p>';
  $_SESSION['petcareorgs'][$row['id']] = $petcarer;
}  


?>