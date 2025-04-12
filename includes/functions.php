<?php
# get the parms
require_once('parms.php');

function url_origin() {
  $hostname = $_SERVER['HTTP_HOST'];
  $protocol = ($_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
  return $protocol . $hostname;
}

function full_url() {
  return url_origin() . $_SERVER['REQUEST_URI'];
}

# call Google's shorten URL service
/*
function shortenUrl($longUrl) {
  $ch = curl_init(sprintf('%s/url?key=%s', GOOGLE_ENDPOINT, GOOGLE_API_KEY)); // initialize the cURL connection
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // tell cURL to return the data rather than outputting it
  $requestData = array('longUrl' => $longUrl);  // create the data to be encoded into JSON
  curl_setopt($ch, CURLOPT_POST, true);  // change the request type to POST
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));  // set the form content type for JSON data
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));  // set the post body to encoded JSON data
  $result = curl_exec($ch);  // perform the request
  curl_close($ch);
  $response = json_decode($result, true);  // decode and return the JSON response
  return $response['id']; // the Google URL Shortener returns the short url as 'id' 
}
*/

function shortenUrl($URL) {
  return $URL;
}

# national locale
setlocale(LC_MONETARY, 'en_AU');

# check email
function checkEmail( $email ) {
  return filter_var( $email, FILTER_VALIDATE_EMAIL );
}

# formatting functions
function showdate($datetime) {
  if ($datetime <= '0000-00-00 00:00:01') { 
    return '';
  } else {
    return strftime("%B %e, %Y", strtotime($datetime));
  }
}

function showdatetime($datetime) {
  if ($datetime <= '0000-00-00 00:00:01') { 
    return '';
  } else {
    return strftime("%B %e, %Y %l:%M %p", strtotime($datetime));
  }
}

function monthyear($datetime) {
  if ($datetime <= '0000-00-00 00:00:01') { 
    return '';
  } else {
    return strftime("%B %Y", strtotime($datetime));
  }
}

function ddmmyyyy($datetime) {
  if ($datetime == '0000-00-00' || $datetime == '') { 
    return '';
  } else {
    return strftime("%d/%m/%Y", strtotime($datetime));
  }
}

function textdate($ddmmyyyy) {
  return strftime("%B %e, %Y", strtotime(isoDate($ddmmyyyy)));
}

function yearsDifference($date_1 , $date_2 ) {
  $datetime1 = date_create($date_1);
  $datetime2 = date_create($date_2);
  
  $interval = date_diff($datetime1, $datetime2);
  
  $years = $interval->format("%y");
  $months = $interval->format("%m");
  if ($years == 0) {
    return "less than a year ago";
  }  else if ($years == 1) {
    return "less than 2 years ago";
  } else {
    return round($years + ($months/12), 0) . " years ago"; 
  }
}

function percent($floatvalue) {
  return round($floatvalue * 100) . '%';
}

function currency($floatvalue) {
  //return money_format("%i", $floatvalue);
  return "$" . number_format($floatvalue, 2);
}

# connect to DB
function dbConnect() {
  try {
      $dbh = new PDO('mysql:host=' . DBHOST . ';dbname=' . DB, DBUSER, DBPW);
  } catch (Exception $e) {
      $dbh = null;
  }  
  return $dbh; 
}

# isoDate from dd/mm/yyyy
function isoDate($ddmmyy) {
  $d = date_create_from_format('d/m/Y', $ddmmyy);
  $isoDate = '';
  if ($d) $isoDate = $d->format('Y-m-d');
  return $isoDate;
}

/*
function australianStateSelect($fieldName, $fieldLabel, $spaces = 20) {
  # displays an Australian states select list
  $spacer = '';

  for ($i = 1; $i <= $spaces; $i++) {
    $spacer .= ' ';
  }
  $state = $_SESSION['orderData'][$fieldName];
  echo $spacer . "<label for=\"" . $fieldName . "\">" . $fieldLabel . "</label>\n";
  echo $spacer . "<select id=\"" . $fieldName . "\" name=\"" . $fieldName . "\" class=\"required wide\">\n";
  echo $spacer . "  <option value=\"\">--- Select Your State ---</option>\n";
  echo $spacer . "  <option value=\"ACT\"" .  (($state == 'ACT') ? ' selected' : '') . ">Australian Capital Territory</option>\n";
  echo $spacer . "  <option value=\"NSW\"" .  (($state == 'NSW') ? ' selected' : '') . ">New South Wales</option>\n";
  echo $spacer . "  <option value=\"NT\"" .  (($state == 'NT') ? ' selected' : '') . ">Northern Territory</option>\n";
  echo $spacer . "  <option value=\"QLD\"" .  (($state == 'QLD') ? ' selected' : '') . ">Queensland</option>\n";
  echo $spacer . "  <option value=\"SA\"" .  (($state == 'SA') ? ' selected' : '') . ">South Australia</option>\n";
  echo $spacer . "  <option value=\"TAS\"" .  (($state == 'TAS') ? ' selected' : '') . ">Tasmania</option>\n";
  echo $spacer . "  <option value=\"VIC\"" .  (($state == 'VIC') ? ' selected' : '') . ">Victoria</option>\n";
  echo $spacer . "  <option value=\"WA\"" .  (($state == 'WA') ? ' selected' : '') . ">Western Australia</option>\n";
  echo $spacer . "</select>\n";
}
*/

# statelookup hash
$states = array(
  'ACT' => 'Australian Capital Territory',
  'NSW' => 'New South Wales',
  'NT' => 'Northern Territory',
  'QLD' => 'Queensland',
  'SA' => 'South Australia',
  'TAS' => 'Tasmania',
  'VIC' => 'Victoria',
  'WA' => 'Western Australia'
);

# beneficiaries lookup array - used in gifts, estate and requirements
function beneficiariesList() {
  $beneficiaries = array();
  if (isset($_SESSION['orderData']['hasSpouse']) && $_SESSION['orderData']['hasSpouse'] == 'Yes') {
    $beneficiaries['spouse'] = $_SESSION['orderData']['spouse']['fullname'];
  }
  $theArrays = array(
    'child' => isset($_SESSION['orderData']['child']) ? $_SESSION['orderData']['child'] : array(), 
    'other' => isset($_SESSION['orderData']['other']) ? $_SESSION['orderData']['other'] : array(),
    'group' => isset($_SESSION['orderData']['group']) ? $_SESSION['orderData']['group'] : array()
  );
  foreach($theArrays as $type => $theArray) {
    if (is_array($theArray)) {
      # it won't be an array if it is empty, just a null string - ie. groupCount = 0, then null string
      for($i = 1; $i <= count($theArray); $i++) {
        $beneficiaries[$type . $i] = $theArray[$i]['fullname'];
      }
    }
  }
  return $beneficiaries;
}

function beneficiarySelect($selected = '', $alt = false) {
  # displays a beneficiary select list
  $output = $alt == 'pet' ? "<option value=''>--- Select a Pet Carer ---</option>\n" : "<option value=''>--- Select a Beneficiary ---</option>\n";

  # alternative recipient

  if ($alt == 'retain' || $alt == 'divide') {
    $output .= "<optgroup label='" . ($alt == 'retain' ? 'Retain or ': '') . "Divide to Other Beneficiaries'>\n";
    if ($alt == 'retain') {
      if (!$selected) $selected = 'Estate';
      $output .= "  <option value='Estate'" . (($selected == 'Estate') ? ' selected' : '') . ">Retain within my estate</option>\n";          
    } else {
      if (!$selected) $selected = 'Divide';
      $output .= "  <option value='Divide'" . (($selected == 'Divide') ? ' selected' : '') . ">Divide to other beneficiaries according to their share</option>\n";
    }
    $output .= "  <option value='TestatorsChildren'" . (($selected == 'TestatorsChildren') ? ' selected' : '') . ">Divide equally to testator's children</option>\n";
    $output .= "  <option value='Children'" . (($selected == 'Children') ? ' selected' : '') . ">Divide equally to primary beneficiaries' children</option>\n";
    $output .= "  <option value='Other'" . (($selected == 'Other') ? ' selected' : '') . ">Specified in Conditions field below</option>\n";
    $output .= "</optgroup>\n";
  }
  
  # spouse
  if (isset($_SESSION['orderData']['hasSpouse']) && $_SESSION['orderData']['hasSpouse'] == 'Yes') {
    $output .= "<optgroup label='Partner' spouse rebuild>\n";
    $output .= "  <option value='spouse'" . (($selected == 'spouse') ? ' selected' : '') . ">" . $_SESSION['orderData']['spouse']['fullname'] . "</option>\n";
    $output .= "</optgroup>\n";
  }
  
  # children
  if (isset($_SESSION['orderData']['hasChildren']) && $_SESSION['orderData']['hasChildren'] == 'Yes') {
    $output .= "<optgroup label='Children' child rebuild>\n";
    for($i = 1; $i <= $_SESSION['orderData']['childCount']; $i++) {
      $output .= "  <option value='child" . $i . "'" . (($selected == 'child' . $i) ? ' selected' : '') . ">" . $_SESSION['orderData']['child'][$i]['fullname'] . " (" . $_SESSION['orderData']['child'][$i]['relationship'] . ")</option>\n";
    }
    $output .= "</optgroup>\n";
  }
  
  # others
  if ($_SESSION['orderData']['otherCount'] > 0) {
    $output .= "<optgroup label='Other Individuals' other rebuild>\n";
    for($i = 1; $i <= $_SESSION['orderData']['otherCount']; $i++) {
      $output .= "  <option value='other" . $i . "'" . (($selected == 'other' . $i) ? ' selected' : '') . ">" . $_SESSION['orderData']['other'][$i]['fullname'] . " (" . $_SESSION['orderData']['other'][$i]['relationship'] . ")</option>\n";
    }
    $output .= "</optgroup>\n";
  }
  
  # groups
  if ($_SESSION['orderData']['groupCount'] > 0) {
    $output .= "<optgroup label='Groups/Organisations' group rebuild>\n";
    for($i = 1; $i <= $_SESSION['orderData']['groupCount']; $i++) {
      $output .= "  <option value='group" . $i . "'" . (($selected == 'group' . $i) ? ' selected' : '') . ">" . $_SESSION['orderData']['group'][$i]['fullname'] . "</option>\n";
    }
    $output .= "</optgroup>\n";
  }

  # pets carer info
  if ($alt == 'pet') {
    # display all pet carer orgs (probably need to only show ones in the will makers state
    $petCarers = $_SESSION['petcareorgs'];
    if (count($petCarers) > 0) {
      $output .= "<optgroup label='Pet Legacy Program provider'>\n";
      foreach($petCarers as $id => $petCarer) {
        # $id is an integer so do integer comparison against $selected in this case
        $output .= "  <option value='$id'" . (($selected == $id) ? ' selected' : '') . ">" . $petCarer['organisation'] . "</option>\n";
      }
      $output .= "</optgroup>\n";
      $output .= "<optgroup label='Other'>\n";
      $output .= "  <option value='Other'" . (($selected == 'Other') ? ' selected' : '') . ">Specified in Care Details field below</option>\n";
      $output .= "</optgroup>\n";
    }
  }
  echo $output;
}

# recursively stripslashes if an array is passed through
function processPostVars($value){
  if (is_array($value)) {
    foreach ($value as $key => $value2) {
      if ($value2 != '' && $key != '0') {
        $value[$key] = processPostVars($value2);
        if (count($value[$key]) == 0) unset($value[$key]); # delete any empty arrays
      } else {
        unset($value[$key]); # delete empty scalars
      }
    }
    return count($value) ? $value : NULL;
  } else {
    // remove trailing and leading whitespace and multiple spaces replaced with one space
    //return trim(stripslashes(preg_replace('/\s+/', ' ', $value))); 
    return trim(stripslashes($value)); 
  }
}

# update the database with encrypted orderdata, use their password as encryption key
function updateOrderData() {
  # update the order table with the orderdata so far
  $dbh = dbConnect();
  $orderdata = isset($_SESSION['orderData']) ? $_SESSION['orderData'] : '';
  $orderID = $orderdata['orderID'];
  #error_log("Updating order $orderID");
  $tabprogress = isset($orderdata['tabpro']) ? $orderdata['tabpro'] : '';
  # reverse sort $orderdata['product']
	if (isset($orderdata['product']) && is_array($orderdata['product'])) krsort($orderdata['product']);
  # check for any addon products selected and update $priceextax
  $priceextax = 0;
  foreach ($orderdata['product'] as $product => $yesno) {
    if ($yesno == 'Yes') {
      $priceextax += $_SESSION['products'][$product]['priceinctax'] / (1 + TAX);
/*
      $query = "INSERT INTO productorders (orderid, productid, priceextax) VALUES (" . $orderID . ", " . $_SESSION['products'][$product]['productid'] . ", '" . $_SESSION['products'][$product]['priceinctax'] / (1 + TAX) . "');";
      $result = $dbh->exec($query); # insert will fail if it is already there
*/
    } else {
/*
      $deletequery = "DELETE FROM productorders WHERE orderid = " . $orderID . " AND productid = " . $_SESSION['products'][$product]['productid'] . ";";
      $result = $dbh->exec($deletequery);
*/
    }
  }
  $orderdata = addslashes(serialize($orderdata));
  # update session copy of extaxprice
  $_SESSION['orderextaxamount'] = $priceextax;
  # calculate discount if they have an introcode
  $discamount = 0;
  if ($_SESSION['introcode'] > '') $discamount = round($priceextax * $_SESSION['discount'], 2);
  # update session discount amount
  $_SESSION['orderdiscountamount'] = $discamount;
  $updateQuery = "UPDATE orders SET orderdata = AES_ENCRYPT('$orderdata','{$_SESSION['affiliatecode']}'), modifydate = NOW(), tabprogress = '$tabprogress' WHERE id = " . $orderID . ";";
  $result = $dbh->exec($updateQuery);
  # update the financial data but only if not already paid for
  $extaxtotal = round($priceextax - $discamount,2);
  $taxamount = $extaxtotal * TAX;
  # only update the priceinfo if it is an unpaid order, or it is expired and it is not the mirror copy of another order
  $updateQuery = "UPDATE orders SET extaxamount = '$priceextax', taxamount = '$taxamount', discountamount = '$discamount' WHERE (paid = 0 OR (paiddate <= DATE_SUB(NOW(), INTERVAL " . PRODUCTEXPIRY . " MONTH) AND (mirrorof is NULL OR id < mirrorof))) AND id = " . $orderID . ";";
  $result = $dbh->exec($updateQuery);
  $dbh = null;
}

# read in example clauses for type
function sampleClauses($type) {
  $clauses = array();
  $dbh = dbConnect();
  $selectQuery = "SELECT clause FROM clauses WHERE type = '$type';";
  $result = $dbh->query($selectQuery);
  foreach ($result as $row) {
    $clauses[] = $row['clause'];
	}
  $dbh = null;
  # if there are rows returned then output required HTML for typeAssistHTML
  if (count($clauses)) {
    echo "      <div id=" . $type . "AssistHTML style='display: none;'>\n";
    foreach($clauses as $clause) {
      echo "        <p class=assistclause>" . $clause . "</p>\n";
    }
    echo "      </div>\n";
  }
}


# password reset request handler
function passwordReset($uniqueKey) {
  $dbh = dbConnect();
  $query = "SELECT userid, expires FROM pwreset WHERE uniquekey = '$uniqueKey'";
  $sth = $dbh->query($query);
  $row = $sth->fetch(PDO::FETCH_ASSOC);
  $userid = $row['userid'];
  $expires = $row['expires'];
  $expiretime = strtotime($expires);
  if (time() < $expiretime) {
    # OK within expires time so logon and redirect to resetpassword.html
    $query = "
    SELECT id, firstname, surname 
    FROM users WHERE id = $userid;
    ";
    $sth = $dbh->query($query);
    if ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
      $_SESSION['pwresetdata']['userid'] = $result['id']; # this is what indicates we have a logged on user
      $_SESSION['pwresetdata']['firstname'] = $result['firstname'];
      $_SESSION['pwresetdata']['surname'] = $result['surname'];
    }
  }
  $dbh = null;
  header("Location: /resetpassword.html");
}


# generate unique affiliate code
function affiliateCode($userid = 753) {
  # generate a unique affiliate code for user
  list($secs,$usecs) = preg_split("/\./", microtime(true));
  return alphaID($userid . $secs);
}



/**
 * Translates a number to a short alhanumeric version
 *
 * Translated any number up to 9007199254740992
 * to a shorter version in letters e.g.:
 * 9007199254740989 --> PpQXn7COf
 *
 * specifiying the second argument true, it will
 * translate back e.g.:
 * PpQXn7COf --> 9007199254740989
 *
 * this function is based on any2dec && dec2any by
 * fragmer[at]mail[dot]ru
 * see: http://nl3.php.net/manual/en/function.base-convert.php#52450
 *
 * If you want the alphaID to be at least 3 letter long, use the
 * $pad_up = 3 argument
 *
 * In most cases this is better than totally random ID generators
 * because this can easily avoid duplicate ID's.
 * For example if you correlate the alpha ID to an auto incrementing ID
 * in your database, you're done.
 *
 * The reverse is done because it makes it slightly more cryptic,
 * but it also makes it easier to spread lots of IDs in different
 * directories on your filesystem. Example:
 * $part1 = substr($alpha_id,0,1);
 * $part2 = substr($alpha_id,1,1);
 * $part3 = substr($alpha_id,2,strlen($alpha_id));
 * $destindir = "/".$part1."/".$part2."/".$part3;
 * // by reversing, directories are more evenly spread out. The
 * // first 26 directories already occupy 26 main levels
 *
 * more info on limitation:
 * - http://blade.nagaokaut.ac.jp/cgi-bin/scat.rb/ruby/ruby-talk/165372
 *
 * if you really need this for bigger numbers you probably have to look
 * at things like: http://theserverpages.com/php/manual/en/ref.bc.php
 * or: http://theserverpages.com/php/manual/en/ref.gmp.php
 * but I haven't really dugg into this. If you have more info on those
 * matters feel free to leave a comment.
 *
 * @author  Kevin van Zonneveld <kevin@vanzonneveld.net>
 * @author  Simon Franz
 * @author  Deadfish
 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version   SVN: Release: $Id: alphaID.inc.php 344 2009-06-10 17:43:59Z kevin $
 * @link    http://kevin.vanzonneveld.net/
 *
 * @param mixed   $in    String or long input to translate
 * @param boolean $to_num  Reverses translation when true
 * @param mixed   $pad_up  Number or boolean padds the result up to a specified length
 * @param string  $passKey Supplying a password makes it harder to calculate the original ID
 *
 * @return mixed string or long
 */
function alphaID($in, $to_num = false, $pad_up = false, $passKey = null)
{
  //$index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  //$index = "abcdefghijkmnopqrstuvwxyz234679ACDEFGHJKLMNPQRTUVWXYZ";
  $index = "abcdefghijkmnopqrstuvwxyz023456789";
  if ($passKey !== null) {
    // Although this function's purpose is to just make the
    // ID short - and not so much secure,
    // with this patch by Simon Franz (http://blog.snaky.org/)
    // you can optionally supply a password to make it harder
    // to calculate the corresponding numeric ID
 
    for ($n = 0; $n<strlen($index); $n++) {
      $i[] = substr( $index,$n ,1);
    }
 
    $passhash = hash('sha256',$passKey);
    $passhash = (strlen($passhash) < strlen($index))
      ? hash('sha512',$passKey)
      : $passhash;
 
    for ($n=0; $n < strlen($index); $n++) {
      $p[] =  substr($passhash, $n ,1);
    }
 
    array_multisort($p,  SORT_DESC, $i);
    $index = implode($i);
  }
 
  $base  = strlen($index);
 
  if ($to_num) {
    // Digital number  <<--  alphabet letter code
    $in  = strrev($in);
    $out = 0;
    $len = strlen($in) - 1;
    for ($t = 0; $t <= $len; $t++) {
      $bcpow = bcpow($base, $len - $t);
      $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
    }
 
    if (is_numeric($pad_up)) {
      $pad_up--;
      if ($pad_up > 0) {
        $out -= pow($base, $pad_up);
      }
    }
    $out = sprintf('%F', $out);
    $out = substr($out, 0, strpos($out, '.'));
  } else {
    // Digital number  -->>  alphabet letter code
    if (is_numeric($pad_up)) {
      $pad_up--;
      if ($pad_up > 0) {
        $in += pow($base, $pad_up);
      }
    }
 
    $out = "";
    for ($t = floor(log($in, $base)); $t >= 0; $t--) {
      $bcp = bcpow($base, $t);
      $a   = floor($in / $bcp) % $base;
      $out = $out . substr($index, $a, 1);
      $in  = $in - ($a * $bcp);
    }
    $out = strrev($out); // reverse
  }
 
  return $out;
}

// force file download - include full local path
function FileDownload($filename){
  
  // required for IE, otherwise Content-disposition is ignored
  if(ini_get('zlib.output_compression'))
  ini_set('zlib.output_compression', 'Off');
  
  // addition by Jorg Weske
  $file_extension = strtolower(substr(strrchr($filename,"."),1));
  
  if( $filename == "" ) {
    echo "download file NOT SPECIFIED.";
    exit;
  } elseif (!file_exists( $filename )) {
    echo "File not found.";
    exit;
  }
  
  switch ($file_extension) {
    case "pdf": $ctype="application/pdf"; break;
    case "mp3": $ctype="audio/x-mp3"; break;
    //case "mp3": $ctype="application/octet-stream"; break;
    case "zip": $ctype="application/zip"; break;
    case "rar": $ctype="application/zip"; break;
    case "tar": $ctype="application/zip"; break;
    case "sit": $ctype="application/zip"; break;
    case "doc": $ctype="application/msword"; break;
    case "xls": $ctype="application/vnd.ms-excel"; break;
    case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
    case "gif": $ctype="image/gif"; break;
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpg"; break;
    default: $ctype="application/force-download";
  }

  header("Pragma: public"); // required
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: private",false); // required for certain browsers 
  header("Content-Type: $ctype");
  header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";" );
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: " . filesize($filename));
  readfile("$filename");
}

# write HTTP requests received to a log file for research
function captureHTTPReq() {
  $myFile = '../mworequests.log';
  $now = date("r");
  $fh = fopen($myFile, 'a') or die("Cannot open file");
  fwrite($fh, "\n\n$now\n---------------------------------------------------------------\n");
  foreach($_SERVER as $h=>$v) fwrite($fh, "$h = $v\n");
  fwrite($fh, "\nContent:\n");
  fwrite($fh, file_get_contents('php://input'));
  fclose($fh);
}

# recursive function to escape all TeX special characters
function texFix($value) {
  if (is_array($value)) {
    foreach ($value as $key => $value2) {
      $value[$key] = texFix($value2);
    }
    return $value;
  } else {
	  $value = preg_replace("/(\r\n|\n|\r)/", '\\\\\\\\[0cm]', $value); # newlines 4 backslashes needed to make 1 backslashes - need to add [0cm] meaning do not indent on the next line - solves a problem with an immediately following [
		$value = preg_replace("/\\$/", '\\\\$', $value); # escape $ signs - complicated
		$value = preg_replace("/([%}{&_#])/", "\\\\$1", $value); # escape % _ } { # &
		return $value;
  }
}

# email functions
function adminEmail($from, $subject, $body) {
  # send an email
  $siteemail = SITEEMAIL;
  $sitename = SITENAME;
  $plaintext = strip_tags($body);
  $eol = PHP_EOL;
  $header = "From: $sitename <$siteemail>$eol"
           . "Reply-To: $from$eol"
           . "Content-type: text/plain; charset=iso-8859-1$eol"
           . "Content-Transfer-Encoding: 7bit";
  mail("$sitename <$siteemail>", $subject, $plaintext, $header);
}

function sendEmail($toemail, $subject, $message) {
  # send an email
  $boundary = md5(uniqid(time()));
  $sitename = SITENAME;
  $sitecompany = SITECOMPANY;
  $siteabn = SITEABN;
  $website = url_origin();
  $siteemail = SITEEMAIL;
  $siteaddress = SITEADDRESS;
  $fromemail = "$sitename <$siteemail>";
  $year = date("Y");
  $emailID = 0;
  $unsubscribe = '';
  $plaintext = strip_tags($message);
  $eol = PHP_EOL;
  $emailhtml = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <title>$sitename</title>
    <style type="text/css">
      html, body {font-family: "Trebuchet MS", Helvetica, sans-serif; color: #666666; font-size: 1.0em;}
      .footer {font-size: 0.7em; color: #888888; }
    </style>
  </head>
  <body>
    <div>
      $message
      <hr>
      <div class="footer">
        <p>
          $sitecompany &copy; 2011-$year &bull; ABN: $siteabn<br>
          <a href="$website"><img src="$website/images/feather.png"></a>
        </p>
      </div>
    </div>
  </body>
</html>
EOT;
  $header = "From: $fromemail$eol"
           . "Reply-To: $fromemail$eol"
           . "MIME-Version: 1.0$eol"
           . "Content-Type: multipart/alternative; boundary=" . $boundary;
  $message = "--" . $boundary . "$eol"
           . "Content-type: text/plain; charset=iso-8859-1$eol"
           . "Content-Transfer-Encoding: 7bit$eol$eol"
           . $plaintext . "$eol"
           . "--" . $boundary . "$eol"
           . "Content-type: text/html; charset=iso-8859-1$eol"
           . "Content-Transfer-Encoding: 7bit$eol$eol"
           . $emailhtml . "$eol"
           . "--" . $boundary . "--"
           ;
  $toemail = DEVSITE ? $fromemail : $toemail; // send to admin in DEV
  mail($toemail, $subject, $message, $header);
}



function sendTrackedEmail($userid, $affiliatecode, $toemail, $firstname, $surname, $emailtype, $message = '') {
  global $fblogon, $password, $couponcode, $discount, $discountperc, $paymentamount; // used for some
  $boundary = md5(uniqid(time()));
  $siteemail = SITEEMAIL;
  $sitename = SITENAME;
  $sitecompany = SITECOMPANY;
  $siteabn = SITEABN;
  $siteaddress = SITEADDRESS;
  $siteboss = SITEBOSS;
  $sitebossrole = SITEBOSSROLE;
  $sitebossmobile = SITEBOSSMOBILE;
  $sitebossemail = SITEBOSSEMAIL;
  $website = url_origin();
  $year = date("Y");
  $fromemail = "$sitename <$siteemail>";
  # add the email to the emailsent table
  $dbh = dbConnect();
  $query = "INSERT INTO emailsent (userid, sent, type) VALUES ($userid, NOW(), \"$emailtype\");";
  $dbh->exec($query);
  # get id 
  $emailID = $dbh->lastInsertId();
  # prepare message and send
  require("trackedemailbody.php"); // source of body of message - sets $subject, $body and $emailhtml
  $dbh = null;
  if (!$sendemail) return;
  $plaintext = strip_tags($body);
  $eol = PHP_EOL;
  $header = "From: $fromemail$eol"
           . "Reply-To: $fromemail$eol"
           . "MIME-Version: 1.0$eol"
           . "Content-Type: multipart/alternative; boundary=" . $boundary;
  $message = "--" . $boundary . "$eol"
           . "Content-type: text/plain; charset=iso-8859-1$eol"
           . "Content-Transfer-Encoding: 7bit$eol$eol"
           . $plaintext . "$eol"
           . "--" . $boundary . "$eol"
           . "Content-type: text/html; charset=iso-8859-1$eol"
           . "Content-Transfer-Encoding: 7bit$eol$eol"
           . $emailhtml . "$eol"
           . "--" . $boundary . "--"
           ;
  $toemail = DEVSITE ? $fromemail : $toemail; // send to admin in DEV
  mail($toemail, $subject, $message, $header);
}

// SMS function
require_once("telstrasms.php");
function sendSMS($recipients, $msg) {
  // $recipients is an array of recipient mobile numbers
  if (strlen($msg) > 0) {
    $config = array(
      "client_id"  => TELSTRA_APPKEY,
      "client_secret"  => TELSTRA_APPSECRET,
    );
    $telstra = new telstra($config);
    $prov = $telstra->provision();
    if (is_array($prov)) {
      foreach ($recipients as $recipient) {
        $result = $telstra->sendsms(array(
        'to' => $recipient,
        'body' => $msg,
        'from' => $prov['destinationAddress'],
        'validity' => 1,
        'scheduledDelivery' => 1,
        'notifyURL' => '',
        'replyRequest' => false
        ));
        return "success";
      }
    } else {
      return $prov;
    }
  } else {
    return 'NOMSG';
  }
}

function sendOrderEmail($orderid, $paymentgw, $couponcode = '') {
  //if ($_SESSION['admin'] == 1) return; // do not send an order email if admin processes order
  $serverName = url_origin();
  $products = isset($_SESSION['products']) ? $_SESSION['products'] : '';
	$dbh = dbConnect();
	$query = "SELECT o.id as orderid, u1.id as userid, o.invoice, o.paymenttype, u1.firstname, u1.surname, u1.affiliatecode, u1.emailaddress, u2.discount, CONCAT(u2.firstname, ' ', u2.surname) as affiliatename FROM orders o JOIN users u1 ON (o.userid = u1.id) LEFT JOIN users u2 ON (u2.affiliatecode = u1.introcode) WHERE o.id = $orderid;";
	$result = $dbh->query($query);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$affiliatecode = $row['affiliatecode'];
	$discount = $row['discount'];
	$affiliatename = $row['affiliatename'];
	if ($couponcode > '') {
  	# a different coupon code came through - may be a SALE code so use it to display discount row
    $query = "SELECT * FROM users WHERE affiliatecode = '$couponcode'";
    $affresult = $dbh->query($query);
    $affrow = $affresult->fetch(PDO::FETCH_ASSOC);
    if ($affrow) {
      $affiliatename = $affrow['firstname'] . ' ' . $affrow['surname'];
      $discount = $affrow['discount'];
    }
	}
	$userid = $row['userid'];
	$invoice = $row['invoice'];
	$firstname = rtrim($row['firstname']);
	$surname = rtrim($row['surname']);
	$email = $row['emailaddress'];
	$query = "SELECT extaxamount, taxamount, discountamount, AES_DECRYPT(orderdata,'$affiliatecode') AS orderdata FROM orders WHERE id = $orderid;";
	$result = $dbh->query($query);
  $row = $result->fetch(PDO::FETCH_ASSOC);
	$orderData = unserialize(stripslashes($row['orderdata']));
	$priceextax = $row['extaxamount'];
	$discamount = $row['discountamount'];
	$extaxtotal = $priceextax - $discamount;
	$taxamount = $extaxtotal * TAX;
	$inctaxtotal = $extaxtotal + $taxamount;
	$mirrorwill = false;
	# make email
	$sitename = SITENAME;
	$siteemail = SITEEMAIL;
	$adminemail = "$sitename <$siteemail>";
	$months = PRODUCTEXPIRY;
	$siteabn = SITEABN;
	$sitecompany = SITECOMPANY;
  $invdate = date("jS F Y");
	$tax = (TAX != 0) ? 'TAX ' : '';
 	$invoiceline = "
<h3>${tax}INVOICE</h3>

<p>Invoice number: $invoice</p>

<p>Invoice date: $invdate</p>
";

	$message = "<div>$invoiceline\n<table><tr><th style='text-align: left; vertical-align: top;'>Product</th> <th style='text-align: right; vertical-align: top;'>Price</th></tr>\n";
	# product ordered display
	foreach ($orderData['product'] as $product => $yesno) {
	  if ($yesno == 'Yes') {
	    $mirrorwill = $product == 'mirrorwill' ? true : $mirrorwill;
	    $productname = $products[$product]['name'] . ' for ' . ($product == 'mirrorwill' ? $orderData['spouse']['fullname'] : $orderData['person']['fullname']);
	    #$itemprice = $products[$product]['priceinctax'] / (1 + TAX);
	    $itemprice = $products[$product]['priceinctax'];
	    if ($product == 'enduring') {
  	    $qty = $mirrorwill ? '2' : '1';
	      $message .= "<tr><td style='text-align: left; vertical-align: top;padding-right: 15px;'>$qty x " . $products[$product]['name'] . "</td> <td style='text-align: right;vertical-align: top;'>" . currency($itemprice) . "</td></tr>\n";
	    } else {
	      $message .= "<tr><td style='text-align: left; vertical-align: top;padding-right: 15px;'>1 x $productname</td> <td style='text-align: right;vertical-align: top;'>" . currency($itemprice) . "</td></tr>\n";
	    }
	  }
	}
	if ($discount > 0) {
 	  $discountreason = strpos(strtoupper($affiliatename), 'SALE') === false ? "Introduced to " . SITENAME . " by " . $affiliatename : $affiliatename;
		$message .= "<tr><td style='text-align: left; vertical-align: top;padding-right: 15px;'>" . percent($discount) . " discount <em>($discountreason)</em></td> <td style='text-align: right;vertical-align: top;'>-" . currency($discamount) . "</td></tr>\n";
	}

	$message .= "<tr><td style='text-align: left; vertical-align: top;padding-right: 15px;'><b>Total</b></td> <td style='text-align: right; border-top: 1px solid #ddd;vertical-align: top;'>" . currency($inctaxtotal) . "</td></tr>\n";
	if (TAX != 0) {
		$message .= "<tr><td style='text-align: left; vertical-align: top;padding-right: 15px;padding-top: 50px;'><em>This total includes " . TAXNAME . " of " . currency($taxamount) . " (" . SITECOMPANY . " - ABN: " . SITEABN . ")</em></td></tr>\n";
	}
	$message .= "</table></div>\n";
	# send the email
	if ($paymentgw != 'free') {
  	sendTrackedEmail($userid, $affiliatecode, $email, $firstname, $surname, "order", $message);
  }
	
	# send order received email to admin
	$body = "$firstname $surname - $email - ordered via $paymentgw\nOrderID: $orderid\nAmount: " . currency($inctaxtotal);
	$subject = strtoupper($paymentgw) . " MWO order - " . currency($inctaxtotal);
	adminEmail($adminemail, $subject, $body);
}

function sendEmailAttachment($toemail, $subject, $message, $file, $filename) {
  // $file should include path and filename
  $sitename = SITENAME;
  $sitecompany = SITECOMPANY;
  $siteabn = SITEABN;
  $website = url_origin();
  $siteemail = SITEEMAIL;
  $fromemail = "$sitename <$siteemail>";
  $year = date("Y");
  $file_size = filesize($file);
  $content = chunk_split(base64_encode(file_get_contents($file))); 
  $boundary = md5(uniqid(time()));
  $unsubscribe = '';
  $plaintext = strip_tags($message);
  $eol = PHP_EOL;
  $emailhtml = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <title>$sitename</title>
    <style type="text/css">
      html, body {font-family: "Trebuchet MS", Helvetica, sans-serif; color: #666666; font-size: 1.0em;}
      .footer {font-size: 0.7em; color: #888888; }
    </style>
  </head>
  <body>
    <div>
      $message
      <hr>
      <div class="footer">
        <p>
          $sitecompany &copy; $year &bull; ABN: $siteabn<br>
          <a href="$website"><img src="$website/images/feather.png"></a>
        </p>
      </div>
    </div>
  </body>
</html>
EOT;
  $header = "From: $fromemail$eol"
           . "Reply-To: $fromemail$eol"
           . "MIME-Version: 1.0$eol"
           . "Content-Type: multipart/mixed; boundary=" . $boundary;
  $message = "--" . $boundary . "$eol"
          . "Content-type: text/html; charset=iso-8859-1$eol"
          . "Content-Transfer-Encoding: 7bit$eol$eol"
          . $emailhtml . "$eol$eol"
           . "--" . $boundary . "$eol"
          . "Content-Type: application/octet-stream; name=\"" . $filename . "\"$eol"
          . "Content-Transfer-Encoding: base64$eol"
          . "Content-Disposition: attachment; filename=\"" . $filename . "\"$eol$eol"
          . $content . "$eol"
          . "--" . $boundary . "--"
          ;
    $toemail = DEVSITE ? $fromemail : $toemail; // send to admin in DEV
    mail($toemail, $subject, $message, $header);
    //file_put_contents("mail.out", $header . $message);
}

function check_bit($value,$n=1)
{
	$value = (int)$value;
	if ($value & (1 << ($n - 1))) { return true; }
	else { return false; }
}

function goHome() {
	echo "<script type='text/javascript'>location = '/';</script>";
}

# used to get the full url of a running script
/**
 * Helper function to get the URI for the script
 */
function get_script_uri( $script = 'index.php' ){
	// IIS Fix
	if( empty( $_SERVER['REQUEST_URI'] ) )
		$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];

	// Strip off query string
	$url = preg_replace( '/\?.*$/', '', $_SERVER['REQUEST_URI'] );
	//$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.ltrim(dirname($url), '/').'/';
	$url = 'http://'.$_SERVER['HTTP_HOST'].implode( '/', ( explode( '/', $_SERVER['REQUEST_URI'], -1 ) ) ) . '/';

	return $url . $script;
}

/* functions to prepare data for preview and download */

# replace whitespace with underscore
function replace_ws($str) {
	$whitespace = array("\r\n", "\n", "\r", "\t", "  ");
	return str_replace($whitespace, " ", $str);
}

# prepare the data to be written into the will
function prepareData() {
  # all the data to merge into the document is in $_SESSION['orderData'] vars
	# 1. get the orderData array into its own var
	global $orderData, $createDate;
	
	$stateLookup = array(
    'ACT' => 'Australian Capital Territory',
    'NSW' => 'New South Wales',
    'NT' => 'Northern Territory',
    'QLD' => 'Queensland',
    'SA' => 'South Australia',
    'TAS' => 'Tasmania',
    'VIC' => 'Victoria',
    'WA' => 'Western Australia'
  );

  $createDate = date("jS F Y"); # used to merge into document
  $orderData = isset($_SESSION['orderData']) ? $_SESSION['orderData'] : ''; 
  $orderData['person']['fullstate'] = isset($orderData['person']['state']) ? $stateLookup[trim($orderData['person']['state'])] : '';
  if ($orderData['person']['fullstate'] == '') {
    $orderData['person']['fullstate'] = $orderData['person']['country'];
  }
  # and one line address
  $orderData['person']['onelineaddress'] = replace_ws(isset($orderData['person']['address1']) ? $orderData['person']['address1'] : '') . (isset($orderData['person']['address2']) && $orderData['person']['address2'] > '' ? ' ' . $orderData['person']['address2'] : '') . ', ' . ucwords(strtolower(isset($orderData['person']['city']) ? $orderData['person']['city'] : '')) . ' ' . (isset($orderData['person']['state']) ? $orderData['person']['state'] : '') . ' ' . (isset($orderData['person']['postcode']) ? $orderData['person']['postcode'] : '');
  $orderData['person']['fulladdress'] = replace_ws(isset($orderData['person']['address1']) ? $orderData['person']['address1'] : '') . (isset($orderData['person']['address2']) && $orderData['person']['address2'] > '' ? ' \newline ' . $orderData['person']['address2'] : '') . ' \newline ' . ucwords(strtolower(isset($orderData['person']['city']) ? $orderData['person']['city'] : '')) . ' ' . (isset($orderData['person']['state']) ? $orderData['person']['state'] : '') . ' ' . (isset($orderData['person']['postcode']) ? $orderData['person']['postcode'] : '') . ' \newline ' . (isset($orderData['person']['country']) ? $orderData['person']['country'] : '');
  $orderData['spouse']['onelineaddress'] = replace_ws(isset($orderData['spouse']['address1']) ? $orderData['spouse']['address1'] : '') . (isset($orderData['spouse']['address2']) && $orderData['spouse']['address2'] > '' ? ' ' . $orderData['spouse']['address2'] : '') . ', ' . ucwords(strtolower(isset($orderData['spouse']['city']) ? $orderData['spouse']['city'] : '')) . ' ' . (isset($orderData['spouse']['state']) ? $orderData['spouse']['state'] : '') . ' ' . (isset($orderData['spouse']['postcode']) ? $orderData['spouse']['postcode'] : '');
  $orderData['spouse']['fulladdress'] = replace_ws(isset($orderData['spouse']['address1']) ? $orderData['spouse']['address1'] : '') . (isset($orderData['spouse']['address2']) && $orderData['spouse']['address2'] > '' ? ' \newline ' . $orderData['spouse']['address2'] : '') . ' \newline ' . ucwords(strtolower(isset($orderData['spouse']['city']) ? $orderData['spouse']['city'] : '')) . ' ' . (isset($orderData['spouse']['state']) ? $orderData['spouse']['state'] : '') . ' ' . (isset($orderData['spouse']['postcode']) ? $orderData['spouse']['postcode'] : '') . ' \newline ' . (isset($orderData['spouse']['country']) ? $orderData['spouse']['country'] : '');
  if (isset($orderData['executor'])) {
    foreach($orderData['executor'] as &$row) {
    	$row['onelineaddress'] = replace_ws(isset($row['address1']) ? $row['address1'] : '') . (isset($row['address2']) && $row['address2'] > '' ? ' ' . $row['address2'] : '')  . ', ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . ' ' . (isset($row['country']) ? $row['country'] : '');
    	$row['address1'] = isset($row['address1']) ? $row['address1'] : '-';
    	$row['fulladdress'] = replace_ws($row['address1']) . (isset($row['address2']) && $row['address2'] > '' ? ' \newline ' . $row['address2'] : '')  . ' \newline ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . '\newline ' . (isset($row['country']) ? $row['country'] : '');
    }
  }
  if (isset($orderData['guardian'])) {
    foreach($orderData['guardian'] as &$row) {
    	$row['onelineaddress'] = replace_ws(isset($row['address1']) ? $row['address1'] : '') . (isset($row['address2']) && $row['address2'] > '' ? ' ' . $row['address2'] : '')  . ', ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . ' ' . (isset($row['country']) ? $row['country'] : '');
    	$row['address1'] = isset($row['address1']) ? $row['address1'] : '-';
    	$row['fulladdress'] = replace_ws($row['address1']) . (isset($row['address2']) && $row['address2'] > '' ? ' \newline ' . $row['address2'] : '')  . ' \newline ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . '\newline ' . (isset($row['country']) ? $row['country'] : '');
    }
  }
  if (isset($orderData['child'])) {
    foreach($orderData['child'] as &$row) {
    	$row['onelineaddress'] = replace_ws($row['address1']) . (isset($row['address2']) && $row['address2'] > '' ? ' ' . $row['address2'] : '')  . ', ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . ', ' . (isset($row['country']) ? $row['country'] : '');
    	$row['address1'] = isset($row['address1']) ? $row['address1'] : '-';
    	$row['fulladdress'] = replace_ws($row['address1']) . (isset($row['address2']) && $row['address2'] > '' ? ' \newline ' . $row['address2'] : '')  . ' \newline ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . '\newline ' . (isset($row['country']) ? $row['country'] : '');
    }
  }
  if (isset($orderData['other'])) {
    foreach($orderData['other'] as &$row) {
    	$row['onelineaddress'] = replace_ws(isset($row['address1']) ? $row['address1'] : '') . (isset($row['address2']) && $row['address2'] > '' ? ' ' . $row['address2'] : '')  . ', ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . ' ' . (isset($row['country']) ? $row['country'] : '');
    	$row['address1'] = isset($row['address1']) ? $row['address1'] : '-';
    	$row['fulladdress'] = replace_ws(isset($row['address1']) ? $row['address1'] : '') . (isset($row['address2']) && $row['address2'] > '' ? ' \newline ' . $row['address2'] : '')  . ' \newline ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . '\newline ' . (isset($row['country']) ? $row['country'] : '');
    }
  }
  if (isset($orderData['group'])) {
    foreach($orderData['group'] as &$row) {
    	$row['onelineaddress'] = replace_ws($row['address1']) . (isset($row['address2']) && $row['address2'] > '' ? ' ' . $row['address2'] : '')  . ', ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . ' ' . (isset($row['country']) ? $row['country'] : '');
    	$row['address1'] = isset($row['address1']) ? $row['address1'] : '-';
    	$row['fulladdress'] = replace_ws($row['address1']) . (isset($row['address2']) && $row['address2'] > '' ? ' \newline ' . $row['address2'] : '')  . ' \newline ' . ucwords(strtolower(isset($row['city']) ? $row['city'] : '')) . ' ' . (isset($row['state']) ? $row['state'] : '') . ' ' . (isset($row['postcode']) ? $row['postcode'] : '') . '\newline ' . (isset($row['country']) ? $row['country'] : '');
    }
  }

  # guardians - look for an 'and' to see if a couple
  $orderData['guardian'][1]['couple'] = 0;
  if (isset($orderData['guardian'][1]['fullname']) && preg_match('/and/i', $orderData['guardian'][1]['fullname'])) $orderData['guardian'][1]['couple'] = 1;
  $orderData['guardian'][2]['couple'] = 0;
  if (isset($orderData['guardian'][2]['fullname']) && preg_match('/and/i', $orderData['guardian'][2]['fullname'])) $orderData['guardian'][2]['couple'] = 1;

  # unset guardian 2 if $orderData['guardian'][2]['fullname'] empty
  if (!isset($orderData['guardian'][2]['fullname'])) unset($orderData['guardian'][2]);

	# pets, legacies, bequests, devises, estate share, info, requirements, messages, assets and liabilities counts
	$orderData['executorCount'] = isset($orderData['executor']) && is_array($orderData['executor']) ? count($orderData['executor']) : 0;
	$orderData['childCount'] = isset($orderData['child']) && is_array($orderData['child']) ? count($orderData['child']) : 0;
	$orderData['otherCount'] = isset($orderData['other']) && is_array($orderData['other']) ? count($orderData['other']) : 0;
	$orderData['groupCount'] = isset($orderData['group']) && is_array($orderData['group']) ? count($orderData['group']) : 0;
	$orderData['petCount'] = isset($orderData['pet']) && is_array($orderData['pet']) ? count($orderData['pet']) : 0;
	$orderData['legacyCount'] = isset($orderData['legacy']) && is_array($orderData['legacy']) ? count($orderData['legacy']) : 0;
	$orderData['bequestCount'] = isset($orderData['bequest']) && is_array($orderData['bequest']) ? count($orderData['bequest']) : 0;
	$orderData['deviseCount'] = isset($orderData['devise']) && is_array($orderData['devise']) ? count($orderData['devise']) : 0;
	$orderData['estateCount'] = isset($orderData['estate']) && is_array($orderData['estate']) ? count($orderData['estate']) : 0;
	$orderData['requirementCount'] = isset($orderData['requirement']) && is_array($orderData['requirement']) ? count($orderData['requirement']) : 0;
	$orderData['messageCount'] = isset($orderData['message']) && is_array($orderData['message']) ? count($orderData['message']) : 0;
	$orderData['infoCount'] = isset($orderData['info']) && is_array($orderData['info']) ? count($orderData['info']) : 0;
	$orderData['assetCount'] = isset($orderData['asset']) && is_array($orderData['asset']) ? count($orderData['asset']) : 0;
	$orderData['liabilityCount'] = isset($orderData['liability']) && is_array($orderData['liability']) ? count($orderData['liability']) : 0;
	
	# age of inheritance
	if (!isset($orderData['ageOfInheritance'])) $orderData['ageOfInheritance'] = 18;
	
	# young children
	if (isset($orderData['hasChildrenYoung']) && $orderData['hasChildrenYoung'] == 'Yes') $hasChildrenYoung = true;
	else $hasChildrenYoung = false;

  # create recipient relationship lookup hash
  $relationship = array();
  if (isset($orderData['hasSpouse']) && $orderData['hasSpouse'] == 'Yes') {
    $relationship['spouse'] = array('fullname' => $orderData['spouse']['fullname'], 'relationship' => $orderData['spouse']['relationship'] );
  }
  $theArrays = array(
    'child' => isset($orderData['child']) ? $orderData['child'] : array(),
    'other' => isset($orderData['other']) ? $orderData['other'] : array(),
    'group' => isset($orderData['group']) ? $orderData['group'] : array()
  );
  foreach($theArrays as $type => $theArray) {
    if(is_array($theArray)) {
      # if there are none (eg. groupCount = 0 then will be null string
      for($i = 1; $i <= count($theArray); $i++) {
        $relationship[$type . $i] = array('fullname' => $theArray[$i]['fullname'], 'relationship' => strtolower($theArray[$i]['relationship']));
      }
    }
  }

	# pet array
	for($i = 1; $i <= count($orderData['pet']); $i++) {
  	if (preg_match('/(spouse|child|group|other)/', $orderData['pet'][$i]['carerrecipient'])) {
      $orderData['pet'][$i]['carerfullname'] = $relationship[$orderData['pet'][$i]['carerrecipient']]['fullname'];
      if (substr($orderData['pet'][$i]['carerrecipient'], 0, 5) != 'group') {
        $orderData['pet'][$i]['carerrelationship'] = 'my ' . $relationship[$orderData['pet'][$i]['carerrecipient']]['relationship'];
      } else {
        if (preg_match("/^(The|the)/", $orderData['pet'][$i]['carerfullname'])) {
          $orderData['pet'][$i]['carerrelationship'] = $relationship[$orderData['pet'][$i]['carerrecipient']]['relationship'];    
        } else {
          $orderData['pet'][$i]['carerrelationship'] = 'the ' . $relationship[$orderData['pet'][$i]['carerrecipient']]['relationship'];    
        }
      }
  	} else if ($orderData['pet'][$i]['carerrecipient'] == 'Other') {
    	// nothing for Other - specified in conditions field
  	} else {
    	# not one of the listed benficiaries so it must be one of the pet carer orgs
      $orderData['pet'][$i]['carerfullname'] = $_SESSION['petcareorgs'][$orderData['pet'][$i]['carerrecipient']]['organisation'];
      $orderData['pet'][$i]['carerphone'] = $_SESSION['petcareorgs'][$orderData['pet'][$i]['carerrecipient']]['phone'];
      $orderData['pet'][$i]['careremail'] = $_SESSION['petcareorgs'][$orderData['pet'][$i]['carerrecipient']]['email'];
      $orderData['pet'][$i]['carerfulladdress'] = $_SESSION['petcareorgs'][$orderData['pet'][$i]['carerrecipient']]['fulladdress'];
      $orderData['pet'][$i]['carerabn'] = $_SESSION['petcareorgs'][$orderData['pet'][$i]['carerrecipient']]['abn'];
      $orderData['pet'][$i]['carerrelationship'] = 'the ';	
  	}
	}
	
	# legacies, bequests, devises and estate shares have alternate recipients so add a phrase that will work in the document
	$theArrays = array('legacy' => &$orderData['legacy'], 'bequest' => &$orderData['bequest'], 'devise' => &$orderData['devise'], 'share of my estate' => &$orderData['estate']);
	foreach($theArrays as $name => &$theArray) {
    if(is_array($theArray)) {
      for($i = 1; $i <= count($theArray); $i++) {
        # if the recipient is a group, then there is no altrecipient so have an empty phrase
        if ($theArray[$i]['altrecipient']) {
          # possible values are 'Divide', 'Estate', childN, otherN or groupN
          $altrecipient = $theArray[$i]['altrecipient'];
          if ($altrecipient == 'Divide') {
          	if (count($theArray) > 1) {
	            $theArray[$i]['altrecipientphrase'] = 'Should my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] . ' not survive me, it is my wish that this ' . $name . ' be divided among the other residuary beneficiaries in proportion to their nominated share.';
	          } else {
	          	// if there are no other estate beneficiaries, then it should be determined by estate law
	            $theArray[$i]['altrecipientphrase'] = 'Should my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] . ' not survive me, it is my wish that this ' . $name . ' be retained within my estate.';
	          }
          } else if ($altrecipient == 'Estate') {
            $theArray[$i]['altrecipientphrase'] = 'Should my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] . ' not survive me, it is my wish that this ' . $name . ' be retained within my estate.';
          } else if ($altrecipient == 'Children') {
            $theArray[$i]['altrecipientphrase'] = 'Should my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] . ' not survive me, it is my wish that this ' . $name . ' be given, in equal shares, to the children of my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] .
            (($theArray[$i]['recipient'] == 'spouse' && !$hasChildrenYoung) ? '' : '. If any of these children are under ' . $orderData['ageOfInheritance'] . ' years of age at the time of my death, then this ' . $name . ' should be held in trust for them until they reach the age of ' . $orderData['ageOfInheritance'] . ' years') .
            '. If there are no living children of my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] .
            ' then this ' . $name . ' should be divided among the other residuary beneficiaries in proportion to their nominated share.' ;
          } else if ($altrecipient == 'TestatorsChildren') {
            $children = $orderData['child'];
            $childcount = count($children);
            if ($childcount > 0) $childrennames = ' ';
            $primaryBeneficiaryChild = $theArray[$i]['recipient'];
            $childNum = 0;
            if (preg_match('/child(\d+)$/', $theArray[$i]['recipient'], $matches)) {
              $childNum = $matches[1];
            }
            for ($k = 1; $k < $childcount + 1; $k++) {
              if ($k == $childNum) continue; # if the primary beneficiary is one of the children, then we need to exclude that one from the alternatebeneficiary
              if ($k == $childcount || ($childNum == $childcount && $k == $childcount - 1)) {
                if ($childcount == 1) {
                  $childrennames .= $children[$k]['fullname'];
                } else {
                  $childrennames .= ' and ' . $children[$k]['fullname'];
                }
              } else {
                $childrennames .= $children[$k]['fullname'] . ', ';
              }
            }
            $theArray[$i]['altrecipientphrase'] = 'Should my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] . ' not survive me, it is my wish that this ' . $name . ' be given' . ($childcount == 1 ? ' to my ' . strtolower($children[1]['relationship']) : ', in equal shares, to my ' . ($childNum > 0 ? 'surviving ' : '') . 'children') . $childrennames . '.' .
            (!$hasChildrenYoung ? '' : ' If any of these children are under ' . $orderData['ageOfInheritance'] . ' years of age at the time of my death, then this ' . $name . ' should be held in trust for them until they reach the age of ' . $orderData['ageOfInheritance'] . ' years');
          } else if (substr($altrecipient,0,5) == 'child') {
            $theArray[$i]['altrecipientphrase'] = 'Should my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] . ' not survive me, it is my wish that this ' . $name . ' be given to my ' . $relationship[$theArray[$i]['altrecipient']]['relationship'] . " " . $relationship[$theArray[$i]['altrecipient']]['fullname'] . '.';
          } else if (substr($altrecipient,0,5) == 'other') {
            $theArray[$i]['altrecipientphrase'] = 'Should my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] . ' not survive me, it is my wish that this ' . $name . ' be given to my ' . $relationship[$theArray[$i]['altrecipient']]['relationship'] . " " . $relationship[$theArray[$i]['altrecipient']]['fullname'] . '.';
          } else if (substr($altrecipient,0,5) == 'group') {
            $theArray[$i]['altrecipientphrase'] = 'Should my ' . $relationship[$theArray[$i]['recipient']]['relationship'] . " " . $relationship[$theArray[$i]['recipient']]['fullname'] . ' not survive me, it is my wish that this ' . $name . ' be given to ' . $relationship[$theArray[$i]['altrecipient']]['fullname'] . '.';
          }
          # set the relationship var which is used in the doc
          $theArray[$i]['relationship'] = ' my ' . $relationship[$theArray[$i]['recipient']]['relationship'];
        } else {
          $theArray[$i]['relationship'] = '';
          # if the beneficiary is a group then add a standard clause
          # $theArray[$i]['altrecipientphrase'] =  'I declare that the receipt of the secretary or other proper officer of ' . $relationship[$theArray[$i]['recipient']]['fullname'] . ' shall be a full discharge for my trustees for this gift.';
        }
        # set the recipient name
        $theArray[$i]['fullname'] = $relationship[$theArray[$i]['recipient']]['fullname'];
        # for devise make the property field one line - ie remove newlines
        if ($name == 'devise') $theArray[$i]['property'] = replace_ws($theArray[$i]['property']);
        # make condition have a value if it does not exist and add a period at the end if not there.
        if (!isset($theArray[$i]['condition'])) {
          $theArray[$i]['condition'] = '';
        } else {
          if (preg_match('/\.$/', $theArray[$i]['condition']) == 0) $theArray[$i]['condition'] .= '.';
          $theArray[$i]['condition'] .= ' ';
        }
      }
    }
	}

  # remove any trailing newlines from personal requirements, messages, assets and liabilities and add a period if not there.
	$theArrays = array('requirement' => &$orderData['requirement'], 'message' => &$orderData['message'], 'info' => &$orderData['info'], 'asset' => &$orderData['asset'], 'liability' => &$orderData['liability']);
	foreach($theArrays as $name => &$theArray) {
    for($i = 1; $i <= count($theArray); $i++) {
      # set the relationship var which is used in the doc
      if ($name == 'message') {
        $theArray[$i]['relationship'] = ' my ' . $relationship[$theArray[$i]['recipient']]['relationship'];
        $theArray[$i]['fullname'] = $relationship[$theArray[$i]['recipient']]['fullname'];
      }
    }
  }

/*
  # set date format for DOB
  if (isset($orderData['child'])) {
    for($i = 1; $i <= count($orderData['child']); $i++) {
    	preg_match("/(\d+)\/(\d+)\/(\d+)/", $orderData['child'][$i]['dob'], $dob);
      $orderData['child'][$i]['dateofbirth'] = strftime("%B %e, %Y ", strtotime($dob[3] . '-' . $dob[2] . '-' . $dob[1])); 
      //error_log($orderData['child'][$i]['dateofbirth']);
    }
  }
*/

} # end of function prepareData()

# prepare the data to be written into the will
function prepareQuickWillData() {
  # all the data to merge into the document is in $_SESSION['qwOrderData'] vars
	# 1. get the qwOrderData array into its own var
	global $qwOrderData, $createDate;
	
	$stateLookup = array(
    'ACT' => 'Australian Capital Territory',
    'NSW' => 'New South Wales',
    'NT' => 'Northern Territory',
    'QLD' => 'Queensland',
    'SA' => 'South Australia',
    'TAS' => 'Tasmania',
    'VIC' => 'Victoria',
    'WA' => 'Western Australia'
  );

  $createDate = date("jS F Y"); # used to merge into document
  $qwOrderData = isset($_SESSION['qwOrderData']) ? $_SESSION['qwOrderData'] : ''; 
  $qwOrderData['testatorState'] = isset($qwOrderData['testatorState']) ? $stateLookup[trim($qwOrderData['testatorState'])] : '';
  # and one line address
  $qwOrderData['testatorOneLineAddress'] = replace_ws(isset($qwOrderData['testatorAddress']) ? $qwOrderData['testatorAddress'] : '');
  $qwOrderData['executorOneLineAddress1'] = replace_ws(isset($qwOrderData['executorAddress1']) ? $qwOrderData['executorAddress1'] : '');
  $qwOrderData['executorOneLineAddress2'] = replace_ws(isset($qwOrderData['executorAddress2']) ? $qwOrderData['executorAddress2'] : '');
  $qwOrderData['guardianOneLineAddress'] = replace_ws(isset($qwOrderData['guardianAddress']) ? $qwOrderData['guardianAddress'] : '');

  # guardians - look for an 'and' to see if a couple
  $qwOrderData['guardianCouple'] = 0;
  if (isset($qwOrderData['guardianFullName']) && preg_match('/and/i', $qwOrderData['guardianFullName'])) $qwOrderData['guardianCouple'] = 1;

	# young children
	if (isset($qwOrderData['hasChildrenYoung']) && $qwOrderData['hasChildrenYoung'] == 'Yes') $hasChildrenYoung = true;
	else $hasChildrenYoung = false;

  # specific gifts split on line break into array
  $qwOrderData['specificGifts'] = isset($qwOrderData['specificGifts']) ? preg_split("/\n/", $qwOrderData['specificGifts']) : array();
  $count = count($qwOrderData['specificGifts']);
  for($i = 0; $i < $count; $i++) {
    if (trim($qwOrderData['specificGifts'][$i]) == '') {
      unset($qwOrderData['specificGifts'][$i]);
    }
  }
  $qwOrderData['specificGifts'] = array_values($qwOrderData['specificGifts']);
  if (!isset($qwOrderData['specificGifts'][0]) || $qwOrderData['specificGifts'][0] == '') unset($qwOrderData['specificGifts']);
  
  
  # requirements split on line break into array
  $qwOrderData['requirements'] = isset($qwOrderData['requirements']) ? preg_split("/\n/", $qwOrderData['requirements']) : array();
  $count = count($qwOrderData['requirements']);
  for($i = 0; $i < $count; $i++) {
    if (trim($qwOrderData['requirements'][$i]) == '') {
      unset($qwOrderData['requirements'][$i]);
    }
  }
  $qwOrderData['requirements'] = array_values($qwOrderData['requirements']);
  if (!isset($qwOrderData['requirements'][0]) || $qwOrderData['requirements'][0] == '') unset($qwOrderData['requirements']);
  
} # end of function prepareQuickWillData()



# ordinal number function
function ordinal($number) {
  $ends = array('th','st','nd','rd','th','th','th','th','th','th');
  if ((($number % 100) >= 11) && (($number%100) <= 13))
    return $number. 'th';
  else
    return $number. $ends[$number % 10];
}

function RandomString() {
  $characters = '0123456789%^&*()-+abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randstring = '';
  for ($i = 0; $i < 10; $i++) {
      $randstring .= $characters[rand(0, strlen($characters))];
  }
  return $randstring;
}


# get linked users for commission details
function get_linked_users($introcode, $level, &$userlevel, &$dbh) {
  $result = $dbh->query("
    SELECT id, introcode, affiliatecode, emailaddress
    FROM users
    WHERE affiliatecode ='$introcode';
  ");
  $row = $result->fetch(PDO::FETCH_ASSOC);
  if (isset($row)) {
    $userlevel[] = array('id' => $row['id'], 'introcode' => $row['introcode'], 'affiliatecode' => $row['affiliatecode'], 'emailaddress' => $row['emailaddress']);
    if ($level > 1 && $row['introcode'] > '') get_linked_users($row['introcode'], $level - 1, $userlevel, $dbh);
  }
}

# get linked users down stream
function get_linked_users_down($code, $startlevel, &$theusers, &$dbh) {
  $result = $dbh->query("
    SELECT id, introcode, affiliatecode, emailaddress
    FROM users
    WHERE introcode ='$code';
  ");
  $rows = $result->fetchAll(PDO::FETCH_ASSOC);
  foreach ($rows as $row) {
    $theusers[] = array('level' => $startlevel, 'id' => $row['id'], 'introcode' => $row['introcode'], 'affiliatecode' => $row['affiliatecode'], 'emailaddress' => $row['emailaddress']);
    get_linked_users_down($row['affiliatecode'], $startlevel + 1, $theusers, $dbh);
  }
}

# get downlinked affiliatecodes
function get_down_linked_codes($thecode) {
  $dbh = dbConnect();
  $users = array();
  $codes = array();
  get_linked_users_down($thecode, 1, $users, $dbh);
  foreach ($users as $user) {
    $codes[] = $user['affiliatecode'];
  }
  $dbh = null;
  return $codes;
}

# check for valid introcode
function code_is_valid($affiliatecode, $introcode) {
  return ($affiliatecode != $introcode) && !in_array($introcode, get_down_linked_codes($affiliatecode));
}

function insertOrderCommission($orderid, &$dbh) {
  # track back up to create an ordercommision row for each linked affiliate 
  # create an ordercommission row for each affiliated user for this order
  # if introcode then get discount
    
  $result = $dbh->query("
    SELECT u.id AS userid, u.introcode, o.extaxamount, o.taxamount, o.discountamount, o.paiddate FROM orders o JOIN users u ON (u.id = o.userid) WHERE o.id = $orderid;
  ");
  $info = $result->fetch();
  
  if (isset($info) && $info['introcode'] > '') {
    $commissionamount = ($info['extaxamount'] - $info['discountamount']) * COMMISSION;
    $commissiondate = $info['paiddate'];
    $orderamount = $info['extaxamount'] - $info['discountamount'];
    $userid = $info['userid'];
    $users = array();
    # get the link chain going up to top level
    get_linked_users($info['introcode'], LEVELS, $users, $dbh);
    foreach ($users as $level => $userdetails) {
      $level += 1; // set the level of commission based upon array index and add one
      $commissionuserid = $userdetails['id'];
      # don't let users get commission for their own orders
      if ($commissionuserid != $userid) {
        $query = "INSERT INTO ordercommissions (orderid, orderuserid, commissionuserid, level, commissionamount, orderamount, commissiondate) VALUES ($orderid, $userid, $commissionuserid, $level, $commissionamount, $orderamount, '$commissiondate');";
        $dbh->exec($query);
      }
    }
  }
}

function number_to_words($number) {
    
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );
    
    if (!is_numeric($number)) {
        return false;
    }
    
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }
    
    $string = $fraction = null;
    
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }
    
    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }
    
    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }
    
    return $string;
}

function generateRandomString($length = 10) {
  //$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

function daysTillEOM() {
  return ceil((strtotime(date("Y-m-t")) - time())/(60*60*24));
}


//original Title Case script © John Gruber <daringfireball.net>
//javascript port © David Gouch <individed.com>
//PHP port of the above by Kroc Camen <camendesign.com>

function titleCase ($title) {
	//remove HTML, storing it for later
	//       HTML elements to ignore    | tags  | entities
	$regx = '/<(code|var)[^>]*>.*?<\/\1>|<[^>]+>|&\S+;/';
	preg_match_all ($regx, $title, $html, PREG_OFFSET_CAPTURE);
	$title = preg_replace ($regx, '', $title);
	
	//find each word (including punctuation attached)
	preg_match_all ('/[\w\p{L}&`\'‘’"“\.@:\/\{\(\[<>_]+-? */u', $title, $m1, PREG_OFFSET_CAPTURE);
	foreach ($m1[0] as &$m2) {
		//shorthand these- "match" and "index"
		list ($m, $i) = $m2;
		
		//correct offsets for multi-byte characters (`PREG_OFFSET_CAPTURE` returns *byte*-offset)
		//we fix this by recounting the text before the offset using multi-byte aware `strlen`
		$i = mb_strlen (substr ($title, 0, $i), 'UTF-8');
		
		//find words that should always be lowercase…
		//(never on the first word, and never if preceded by a colon)
		$m = $i>0 && mb_substr ($title, max (0, $i-2), 1, 'UTF-8') !== ':' && 
			!preg_match ('/[\x{2014}\x{2013}] ?/u', mb_substr ($title, max (0, $i-2), 2, 'UTF-8')) && 
			 preg_match ('/^(a(nd?|s|t)?|b(ut|y)|en|for|i[fn]|o[fnr]|t(he|o)|vs?\.?|via)[ \-]/i', $m)
		?	//…and convert them to lowercase
			mb_strtolower ($m, 'UTF-8')
			
		//else:	brackets and other wrappers
		: (	preg_match ('/[\'"_{(\[‘“]/u', mb_substr ($title, max (0, $i-1), 3, 'UTF-8'))
		?	//convert first letter within wrapper to uppercase
			mb_substr ($m, 0, 1, 'UTF-8').
			mb_strtoupper (mb_substr ($m, 1, 1, 'UTF-8'), 'UTF-8').
			mb_substr ($m, 2, mb_strlen ($m, 'UTF-8')-2, 'UTF-8')
			
		//else:	do not uppercase these cases
		: (	preg_match ('/[\])}]/', mb_substr ($title, max (0, $i-1), 3, 'UTF-8')) ||
			preg_match ('/[A-Z]+|&|\w+[._]\w+/u', mb_substr ($m, 1, mb_strlen ($m, 'UTF-8')-1, 'UTF-8'))
		?	$m
			//if all else fails, then no more fringe-cases; uppercase the word
		:	mb_strtoupper (mb_substr ($m, 0, 1, 'UTF-8'), 'UTF-8').
			mb_substr ($m, 1, mb_strlen ($m, 'UTF-8'), 'UTF-8')
		));
		
		//resplice the title with the change (`substr_replace` is not multi-byte aware)
		$title = mb_substr ($title, 0, $i, 'UTF-8').$m.
			 mb_substr ($title, $i+mb_strlen ($m, 'UTF-8'), mb_strlen ($title, 'UTF-8'), 'UTF-8')
		;
	}
	
	//restore the HTML
	foreach ($html[0] as &$tag) $title = substr_replace ($title, $tag[0], $tag[1], 0);
	return $title;
}

/* get linked affiliates count and details */
function getAffiliates($userid, $levelsdeep, &$dbh) {
  if (!isset($dbh)) $dbh = dbConnect();
  $sql = "SELECT affiliatecode FROM users WHERE id = $userid;";
  $sth = $dbh->query($sql);
  $introcode = $sth->fetchColumn();
  $sql = "SELECT id, affiliatecode, emailaddress FROM users WHERE introcode = '$introcode';";
  $sth = $dbh->query($sql);
  $users = $sth->fetchAll();
  $level = LEVELS - $levelsdeep + 1;
  $count = 0;
  $affiliates = array();
  foreach ($users as $user) {
    $affiliates[$user['affiliatecode']] = $level;
    // recursive call
    if ($levelsdeep > 1) {
      $affiliates = array_merge($affiliates, getAffiliates($user['id'], $levelsdeep - 1, $dbh));
    }
  }
  return $affiliates;
}

function showAffiliates() {
  $affiliatelist = getAffiliates(1, LEVELS, $dbh);  
  $affiliatelevels = array();
  foreach ($affiliatelist as $affiliatecode => $level) {
    $affiliatelevels[$level][] = $affiliatecode; 
  }
  return $affiliatelevels;
}

/* Paypal masspay function for paying affiliate commissions */
function MassPayment($recipientemail, $paymentamount, $paymentrequestid, $note) {
  try {
    $bodyparams = array (
      'USER' => PAYPAL_API_USERNAME,
      'PWD' => PAYPAL_API_PASSWORD,
      'SIGNATURE' => PAYPAL_API_SIGNATURE,
      'METHOD' => 'MassPay',
      'VERSION' => '204',
      'RECEIVERTYPE' => 'EmailAddress',
      'CURRENCYCODE' => 'AUD',
      'EMAILSUBJECT' => SITENAME . ' Commission payment request',
      'L_EMAIL0' => $recipientemail,
      'L_AMT0' => $paymentamount,
      'L_NOTE0' => $note,
      'L_UNIQUEID0' => $paymentrequestid
    );
    $body_data = http_build_query($bodyparams);
    $url = "https://api-3t" . (DEVSITE ? '.sandbox' : '') . ".paypal.com/nvp";
    $params =  array("http" => array( 
                                      "header" => "Content-Type: application/x-www-form-urlencoded",
                                      "protocol_version" => 1.1,
                                      "method" => "POST",
                                      "content" => $body_data
                                    )
                    );
    //create stream context
    $ctx = stream_context_create($params);
    
    error_log(print_r($params,true));
    
    $response = file_get_contents($url, false, $ctx);
 
    error_log($response);
    
    //check to see if stream is open
    if ($response === false) {
      throw new Exception("response is false");
    }

    //parse the ap key from the response
    $keyArray = explode("&", $response);  
    foreach ($keyArray as $rVal){
      list($qKey, $qVal) = explode ("=", $rVal);
      $kArray[$qKey] = $qVal;
    }

    if ($kArray["ACK"] == "Success") {
      return true;
    } else {
      $errorcode = $kArray['L_ERRORCODE0'];
      $shortmsg = urldecode($kArray['L_SHORTMESSAGE0']);
      $longmsg = urldecode($kArray['L_LONGMESSAGE0']);
      $severitycode = $kArray['L_SEVERITYCODE0'];
      error_log("Masspay error: $severitycode $errorcode - $shortmsg - $longmsg");
      return false;
    }
  
  } catch(Exception $e) {
    error_log("Masspay Exception Message: " . $e->getMessage());
    return false;
  }
}

/* functions used only for COMMWEB */
function getResponseDescription($responseCode) {
  switch ($responseCode) {
    case "0" : $result = "Transaction Successful"; break;
    case "?" : $result = "Transaction status is unknown"; break;
    case "1" : $result = "Unknown Error"; break;
    case "2" : $result = "Bank Declined Transaction"; break;
    case "3" : $result = "No Reply from Bank"; break;
    case "4" : $result = "Expired Card"; break;
    case "5" : $result = "Insufficient funds"; break;
    case "6" : $result = "Error Communicating with Bank"; break;
    case "7" : $result = "Payment Server System Error"; break;
    case "8" : $result = "Transaction Type Not Supported"; break;
    case "9" : $result = "Bank declined transaction (Do not contact Bank)"; break;
    case "A" : $result = "Transaction Aborted"; break;
    case "C" : $result = "Transaction Cancelled"; break;
    case "D" : $result = "Deferred transaction has been received and is awaiting processing"; break;
    case "F" : $result = "3D Secure Authentication failed"; break;
    case "I" : $result = "Card Security Code verification failed"; break;
    case "L" : $result = "Shopping Transaction Locked (Please try the transaction again later)"; break;
    case "N" : $result = "Cardholder is not enrolled in Authentication scheme"; break;
    case "P" : $result = "Transaction has been received by the Payment Adaptor and is being processed"; break;
    case "R" : $result = "Transaction was not processed - Reached limit of retry attempts allowed"; break;
    case "S" : $result = "Duplicate SessionID (OrderInfo)"; break;
    case "T" : $result = "Address Verification Failed"; break;
    case "U" : $result = "Card Security Code Failed"; break;
    case "V" : $result = "Address Verification and Card Security Code Failed"; break;
    default  : $result = "Unable to be determined"; 
  }
  return $result;
}

function displayCSCResponse($cscResultCode) {
  if ($cscResultCode != "") {
    switch ($cscResultCode) {
      Case "Unsupported" : $result = "CSC not supported or there was no CSC data provided"; break;
      Case "M"  : $result = "Exact code match"; break;
      Case "S"  : $result = "Merchant has indicated that CSC is not present on the card (MOTO situation)"; break;
      Case "P"  : $result = "Code not processed"; break;
      Case "U"  : $result = "Card issuer is not registered and/or certified"; break;
      Case "N"  : $result = "Code invalid or not matched"; break;
      default   : $result = "Unable to be determined"; break;
    }
  } else {
    $result = "null response";
  }
  return $result;
}

function null2unknown($map, $key) {
  if (array_key_exists($key, $map)) {
    if (!is_null($map[$key])) {
      return $map[$key];
    }
  } 
  return "No Value Returned";
} 


?>