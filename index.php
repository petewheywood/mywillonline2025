<?php
/* 
My Will Online
Author: Pete Heywood 
*/

//error_reporting(E_ALL);
ini_set('display_errors', 0);

# start a PHP session
session_start();
$sessionID = session_id();

# get called page details - page, extension and anything after ?
$uri = $_SESSION['uri'] = $_SERVER['REQUEST_URI'];
$page = '';
$extension = '';
$querystring = '';
if (preg_match('/^\/([^\?.]*)([^\?]*)\?*(.*)/', $uri, $matches)) {
  $page = $matches[1];
  $extension = $matches[2];
  $querystring = $matches[3];
}

$_SESSION['QUERY_STRING'] = $querystring;

# get some constants
require_once('includes/functions.php');

$dbh = dbConnect();

if ($dbh) {

  # log visit info and setup session variables - once for each session
  if (!isset($_SESSION['serverName'])) {
    require_once("includes/sessioninit.php");
  }
  
  # check for any passed in GET vars - coupon, expired or pwreset
  if ($querystring > '') {
    unset($_SESSION['getVars']);
    parse_str($querystring, $_SESSION['getVars']);
    //error_log(print_r($_SESSION['getVars'], true));
    # redirect to this page again without GET vars
    list($theuri, $getVars) = explode("?", $uri);
    header("Location: $theuri");
    exit;
  }
  
  
  # products
  if (QUICKWILL && (isset($_SESSION['quickwill']) && $_SESSION['quickwill'])) {
    $_SESSION['premiumwill'] = 'Premium Will';
  } else {
    $_SESSION['premiumwill'] = 'Will';
  }
  
  
  # check for special pages
  if ($page == 'logout') {
    # just pull in the logout page - no need to do the rest below - the included page will kill session and redirect to /
    require_once('includes/logout.php');
    exit;    
  }
  /* preview order */
  if ($page == 'preview') {
    # just pull in the preview page - no need for anything below
    require('includes/preview.php');
    exit;
  }
  if ($page == 'quickwillpreview') {
    # just pull in the preview page - no need for anything below
    require('includes/quickwillpreview.php');
    exit;
  }
  /* new download mechanism */
  if ($page == 'download') {
    # just pull in the download page - no need for anything below
    require('includes/download.php');
    exit;
  }
  if ($page == 'quickwilldownload') {
    # just pull in the download page - no need for anything below
    require('includes/quickwilldownload.php');
    exit;
  }
  
  
  # OK normal site page
  
  # make sure that the extension is always html and that their is actually a page name if an extension is given
  $exists = file_exists("pages/$page.php"); # does the actual page exist in the pages directory
  if ($page != '' && (!$exists || $extension != '.html')) {
    $newurl = $exists ? $page . '.html' : '/';
    header("Location: $newurl");
    //echo "newurl: >$newurl< uri: >$uri< page: >$page< ext: >$extension< " . ($exists ? 'exists' : 'missing');
    exit;
  }
  
  # OK the page is valid so continue

  
  # check for IE earlier than 8.0 and show non-support screen
  if (floatval($_SESSION['IE']) < 7.0 && floatval($_SESSION['IE']) > 0) {
    include("pageincludes/ie.html");
    exit;
  }
  
  # this is the requested file for every request. If the URI was other than HTTP_HOST or index.php is set to the page requested
  # apache's mod_rewrite does this via .htaccess
  
  if ($page > '') {
  	$maincontent = 'pages/' . $page . '.php';
  	$pageTitlePrefix = ucfirst($page) . ' - ';
  } else {
    $page = 'home';
  	$maincontent = 'pages/home.php';
  	$pageTitlePrefix = '';
  }

  # cache control overrides
/*
  $cacheable_pages = array('home', 'faq', 'terms', 'privacy', 'why-you-need-a-will', 'enduring-power-of-attorney', 'how-to-make-a-will-online', 'changewill', 'questions', 'admin');
  if (in_array($page, $cacheable_pages)) {
    session_cache_limiter('private_no_expire');
  }
*/
    
  # define list of pages that a logged on user can see and a list they can't and vice versa
  $non_logged_on_pages = array('resetpassword');
  $logged_on_pages = array('singlewill', 'logout', 'updateprofile', 'order');
  
  $userid = 0;
  if (isset($_SESSION['userid'])) {
    # a logged on user
    $userid = $_SESSION['userid'];
    if (in_array($page, $non_logged_on_pages)) header("Location: /");
  } else {
    # non-logged on user - visitor
    if (in_array($page, $logged_on_pages)) header("Location: /");
  }
  
  # store the POSTed details into $_SESSION['orderData'] array for logged in users with an orderid
  # $_POST will only have data if a form was submitted not a link
  # and we don't want the fields from the order page stored
  if (isset($_SESSION['orderid']) && count($_POST) > 0 && (isset($lastPage) && $lastPage != 'order.php')) {
    foreach ($_POST as $key => $value) {
      # first unset to clear out any now empty values (mainly for array POST vars)
      unset($_SESSION['orderData'][$key]);
      # and now update it with new POST var values after stripslashes and removing empty vars/arrays
      $value = processPostVars($value);
      if (isset($value) and $value != '') $_SESSION['orderData'][$key] = $value;
    }
    # add the userdata as encrypted content to DB
    updateOrderData();  # store in DB  
  }
  
  # check for password reset request
  if (isset($_SESSION['getVars']['pwreset'])) {
    passwordReset($_SESSION['getVars']['pwreset']);
    unset($_SESSION['getVars']['pwreset']);
  }
  
  # what was the last page we displayed (used to return to previous page links)
  $lastPage = isset($_SESSION['lastPage']) ? $_SESSION['lastPage'] : ''; # can be overriden within a page
  
  # update db visits with last page time and name
  $dbh->exec("INSERT INTO pagevisits (visitid, userid, visitdate, uri) VALUES ({$_SESSION['visit_id']}, $userid, NOW(), '$uri');");
  $pageID = $dbh->lastInsertId();
  
  # cookie check by seeing if last_visit_id == visit_id. Will be different if no cookies enabled
  if (isset($_SESSION['last_visit_id']) && $_SESSION['visit_id'] == $_SESSION['last_visit_id'] && !$_SESSION['cookiesEnabled']) {
    $query = "UPDATE visits SET cookiesenabled = 1 WHERE id = '{$_SESSION['visit_id']}';";
    //error_log($query);
    $dbh->exec($query);
    $_SESSION['cookiesEnabled'] = true;
  }
  $_SESSION['last_visit_id'] = $_SESSION['visit_id'];

  # display site
  require($maincontent);
  
  $dbh = null;
  
  // write visit data to CSV file
/*
  $reqtime = date("Y-m-d H:i:s", $_SERVER["REQUEST_TIME"]);
  $method = $_SERVER["REQUEST_METHOD"];
  $url =$_SERVER["SCRIPT_URL"];
  $remoteaddr = $_SERVER["REMOTE_ADDR"];
  $useragent = $_SERVER["HTTP_USER_AGENT"];
  $referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '-';  
  $outfile = '../mwo_' . date("Y-m", $_SERVER["REQUEST_TIME"]) . '.csv';
  $fh = fopen($outfile, 'a');
  $outputdata = [ $reqtime, $remoteaddr, $method, $url, $referer, $useragent ];
  fputcsv($fh, $outputdata);
  fclose($fh);
*/
  
} else {
  include_once("includes/offline.html");
}

?>

