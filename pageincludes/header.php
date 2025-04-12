<!DOCTYPE html>
<html lang="en-AU">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes" />
    <link rel="canonical" href="<?php echo url_origin() . (($page != 'home') ? '/' . $page . '.html' : '');?>" />
    <meta name="google-site-verification" content="<?php echo GOOGLESV; ?>" />
    <meta name="msvalidate.01" content="<?php echo MSSV; ?>" />
    <meta property="og:url" content="<?php echo url_origin(); ?>" />
    <meta id="fbtitle" property="og:title" content="<?php echo SITENAME;?> - Wills for all Australians" />
    <meta property="og:type" content="website" />
    <meta id="fbappid" property="fb:app_id" content="<?php echo FBAPPID; ?>" />
    <meta id="fbsite" property="og:site_name" content="<?php echo SITENAME; ?>" />
    <meta id="fbpost" property="og:image" content="<?php echo url_origin(); ?>/images/fbpost.jpg" />
    <meta id="fbdesc" property="og:description" content="<?php echo $desc; ?>" />
    <link href="css/jquery-ui-1.10.4.custom.css" rel="stylesheet"/>
    <link href="css/bs.css" rel="stylesheet"/>
    <link href="css/bootstrap-social.css" rel="stylesheet"/>
    <link href="css/font-awesome.css" rel="stylesheet"/>
    <link href="css/style.css" rel="stylesheet"/>
    <meta name="description" content="<?php echo $desc; ?>" />
    <meta name="keywords" content="<?php echo $keywords; ?>" />
    <title><?php echo $title; ?></title>
    <style type="text/css">
      .fouc {visibility: hidden;}
      <?php echo isset($_SESSION['loggedin']) ? ".menutext {display: none;}" : "";?> 
    </style>
    <script type="text/javascript">
      document.documentElement.className = 'fouc';
      var pageID = <?php echo $pageID;?>;
      var sessID = '<?php echo $sessionID;?>';
      var jsE = <?php echo isset($_SESSION['javascriptEnabled']) ? $_SESSION['javascriptEnabled'] : 0;?>;
      var ckE = <?php echo isset($_SESSION['cookiesEnabled']) ? $_SESSION['cookiesEnabled'] : 0;?>;
    </script>
    <!-- scripts -->
    <script src="js/stay_standalone.js"></script>
    <script src="js/jquery-1.10.2.min.js"></script>
    <script src="js/jquery-ui-1.10.4.custom.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/jquery.form.js"></script>
    <script src="js/jshashtable.min.js"></script>
    <script src="js/jquery.numberformatter.min.js"></script>
    <script src="js/jquery.maskedinput.min.js"></script>
    <script src="js/date.js"></script>
    <script src="js/bs.js"></script>
    <script src="js/site.js"></script>   
    <!-- favicon -->
    <link href="images/favicon.ico" rel="shortcut icon" type="image/x-icon">
    <link href="images/favicon.ico" rel="icon" type="image/x-icon">
    <!-- mobile setup -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="white">
    <meta name="apple-mobile-web-app-title" content="MyWillOnline">
    <link rel="apple-touch-startup-image" href="/images/apple-launch.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/images/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/images/apple-icon-167x167.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/images/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/images/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
  </head>
  <body>
    <!-- FB code -->
    <div id="fb-root"></div>
    <!-- header -->      
    <div id="header" class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="navbar-header">
        <button type="button" class="btn navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a style='text-decoration: none;' href="/">
          <img id="logo" src="images/logo.svg" alt="Logo" title="<?php echo $_SESSION['sitename']; ?>">
        </a>
      </div>
      <div class="collapse navbar-collapse" id="navbar-collapse-1">
        <ul id="topmenu" class="nav navbar-nav navbar-right">
<?php
if (isset($_SESSION['loggedin'])) {
  $page = $page . '-on';
  if (isset($_SESSION['admin']) && $_SESSION['admin']) {
?>
          <li id="admin"><a href="admin.html"><i class="fa fa-lg fa-cog"></i></a></li>
<?php    
  }
  if (isset($_SESSION['organisation']) && $_SESSION['organisation']) {
?>
          <li id="dashboard"><a href="affiliateprogram.html" title="Dashboard"><i class="fa fa-lg fa-dashboard"></i> <span class="menutext">Dashboard</span></a></li>
<?php
  } else {
?>
          <li id="mydocs"><a href="mydocs.html" title="My Documents"><i class="fa fa-lg fa-file-text-o"></i> <span class="menutext">My Docs</span></a></li>
<?php
  }
}
?>
          <li id="contact"><a href="contact.html" title="Contact Us"><i class="fa fa-lg fa-envelope-o"></i> <span class="menutext">Contact Us</span></a></li>
<?php

if (isset($_SESSION['loggedin'])) {
?>
          <li id="updateprofile" title="Profile"><a href="updateprofile.html"><i class="fa fa-lg <?php echo $_SESSION['organisation'] ? 'fa-gears' : 'fa-user';?>"></i> <span class="menutext">Update Details</span></a></li>
          <li id="logout"><a href="logout.html" title="Sign Out"><i class="fa fa-lg fa-sign-out"></i> <span class="menutext">Sign Out</span></a></li>
<?php
} else {
?>
          <li id="start"><a href="start.html" title="Log In"><i class="fa fa-lg fa-sign-in"></i> <span class="menutext">Log In</span></a></li>
<?php
}
?>
        </ul>
      </div>
    </div>
    <!-- end of header -->
    <!-- start of page content -->
    <div id="pagecontent">
