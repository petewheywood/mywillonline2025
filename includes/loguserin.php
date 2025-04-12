<?php
session_start();
$sessionID = session_id();
# log user in assuming the email and password are correct - called from the login.html page
require_once('functions.php'); 

# email and password come in as a POST var
$pwd = $_SESSION['password'] = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
$email = $_SESSION['email'] = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
$fbid = $_SESSION['fbid'] = isset($_REQUEST['fbid']) ? $_REQUEST['fbid'] : '';

if ($pwd == '') $pwd = $fbid; // fbid used as pwd

$dbh = dbConnect();
$altpw = ADMIN;

$query = "SELECT u.id, u.firstname, u.othername, u.surname, u.introcode, u.affiliatecode, u.shorturl, u.affiliateprogram, u.discount, u.address1, u.address2, u.city, u.state, u.postcode, u.phone, u.dateofbirth, u.flags, u.fbid, o.name, o.abn, o.description, o.weburl, o.logo FROM users u LEFT JOIN organisations o ON (o.userid = u.id) WHERE ";

if ($pwd == $fbid) {
  $query .= "emailaddress = '$email';";
} else {
  $query .= "emailaddress = '$email' and (password = PASSWORD('$pwd') OR password = MD5('$pwd') OR ('$pwd' = '$altpw'));";
}

error_log($query);

$resultset = $dbh->query($query);


# if they were referred and already are registered, we can set their introcode in the db if they do not already have one.
$referrerIntrocode = isset($_SESSION['introcode']) ? $_SESSION['introcode'] : '';

if ($result = $resultset->fetch(PDO::FETCH_ASSOC)) {
  $userid = $_SESSION['userid'] = $result['id']; # this is what indicates we have a logged on user
  $_SESSION['loggedin'] = true;
  $_SESSION['firstname'] = $result['firstname'];
  $_SESSION['othername'] = $result['othername'];
  $_SESSION['surname'] = $result['surname'];  
  $_SESSION['introcode'] = $result['introcode'];  
  $_SESSION['affiliatecode'] = $result['affiliatecode'];
  $_SESSION['affiliatediscount'] = $result['discount'];
  $_SESSION['affiliateprogram'] = $result['affiliateprogram'];
  $_SESSION['affiliateURL'] = url_origin() . "?afid=" . $_SESSION['affiliatecode'];
  $_SESSION['shortURL'] = shortenUrl($_SESSION['affiliateURL']);
  //$_SESSION['shortURL'] = $result['shorturl'];
  $_SESSION['address1'] = $result['address1']; 
  $_SESSION['address2'] = $result['address2']; 
  $_SESSION['city'] = $result['city']; 
  $_SESSION['state'] = $result['state']; 
  $_SESSION['postcode'] = $result['postcode']; 
  $_SESSION['phone'] = $result['phone'];
  $_SESSION['dob'] = ddmmyyyy($result['dateofbirth']);
  $_SESSION['admin'] = check_bit($result['flags'], 8); //check if leftmost bit is set (1 to 8)
  $_SESSION['flags'] = $result['flags'];
  $_SESSION['fbid'] = $result['fbid'];
  # organisation
  $_SESSION['organisation'] = check_bit($_SESSION['flags'],4) ? true : false;
  $_SESSION['orgname'] = $result['name'];
  $_SESSION['abn'] = $result['abn'];
  $_SESSION['description'] = $result['description'];
  $_SESSION['weburl'] = $result['weburl'];
  $_SESSION['logo'] = $result['logo'];
  
  //$_SESSION['quickwill'] = false; # turn off quickwill for all logged in users

  # update fbid in DB if session fbid != $fbid
  if ($fbid != $_SESSION['fbid']) {
    $dbh->exec("UPDATE users SET fbid = '$fbid' WHERE id = $userid;");    
    $_SESSION['fbid'] = $fbid;
  }

  # update the users introcode if this is a referral and they do not already have an introcode but not their own code
  if ($_SESSION['introcode'] == '' && $referrerIntrocode > '' && $referrerIntrocode != $_SESSION['affiliatecode']) {
    $dbh->exec("UPDATE users SET introcode = '$referrerIntrocode' WHERE id = $userid;");
    $_SESSION['introcode'] = $referrerIntrocode;
  }
  
  # if introcode then get discount
  if ($_SESSION['introcode'] > '') {
    # get user who introduced them
    $results = $dbh->query("SELECT u.id, u.firstname, u.surname, u.discount, o.name, u.flags FROM users u LEFT JOIN organisations o ON (o.userid = u.id) WHERE affiliatecode = '" . $_SESSION['introcode'] . "';");
    $row = $results->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $_SESSION['affiliateName'] = $row['name'] > '' ? $row['name'] : $row['firstname'] . ' ' . $row['surname'];
      $_SESSION['discount'] = is_null($row['discount']) ? $_SESSION['discount'] : $row['discount'];
    } else {
      # user with discount code no longer has it so remove it
      $_SESSION['introcode'] = '';
      $dbh->exec("UPDATE users SET introcode = '' WHERE id = $userid;");
    }    
  }
  
  # update visit info
  $dbh->exec("
      UPDATE visits SET userid = '$userid', logindate = NOW() WHERE sessionid = '$sessionID';
  ");
  
  # set admin bit if pwd = altpw
  $_SESSION['admin'] = $_SESSION['admin'] || $pwd == $altpw ? 1 : 0;
  echo 'true';
} else {
  echo 'false';
}
$dbh = null;
?>