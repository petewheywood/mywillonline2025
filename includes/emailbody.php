<?php
/* email template */

$registereduser = isset($userid) ? " as a registered user of <a href='$website'>$sitename</a>" : "";

$emailhtml = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <title>$sitename</title>
    <style type="text/css">
      html, body {font-family: "Trebuchet MS", Helvetica, sans-serif; color: #666666; font-size: 1.0em;}
      * {-webkit-text-size-adjust: none}
      body {margin:0;padding:0;background-color:#ffffff; width: 100%;}
      div {max-width: 600px; background-color: white; padding: 10px 0;}
      div.header img {padding-left: 20px;}
      div.footer {text-align: center; font-size: 0.7em; color: #888888;background-color: transparent; }
      h1,h2,h3,p,table {padding: 0 20px; margin: 10px 0;}
      table {width: 100%;}
      hr {border-color: #e5e5e5; border-style: solid; border-width: 1px 0 0 0; width: 95%; margin: 20px auto;}
      small {font-size: 0.8em;}
      .text-left {text-align: left;}
      .text-right {text-align: right;}
      .text-center {text-align: center;}
      .text-justify {text-align: justify;}
      .text-muted {color: #999;}
      .text-primary {color: #428bca;}
      a, a:hover {color: #3071a9;}
      a.text-primary:hover {color: #3071a9;}
      .text-success {color: #2b652b;}
      a.text-success:hover {color: #3e723e;}
      .text-info {color: #31708f;}
      a.text-info:hover {color: #245269;}
      .text-warning {color: #ff8500;}
      a.text-warning:hover {color: #ff8500;}
      .text-danger {color: #a94442;}
      a.text-danger:hover {color: #843534;}
      a {text-decoration: none;}
      .indent {padding-left: 40px;}
      ul {list-style-type: none;}
      ul li:before{ content:"-"; position:relative; left:-5px;}
      ul li{ text-indent:-5px; }
      div.footer a:hover {text-decoration: underline;}
      a.button {color: #ffffff;padding: 10px 30px;font-size: 1.2em;border-radius:5px;margin:30px auto;display:block;text-align:center;width:25%;}
      a.button:hover {color: #ffffff;opacity: 0.8;}
      a.button.text-success {background-color: #16741b;}
      a.button.text-info {background-color: #1cb4b2;}
      a.button.text-primary {background-color: #3071a9;}
      a.button.text-warning {background-color: #ff8500;}
      a.button.text-danger {background-color: #a94442;}
      @media screen and (max-device-width: 1024px){
        div {width: 100%;}
        html, body {font-size: 90%;}
        div.header img {width: 220px;}
        a.button {width: 50%;}
      }
    </style>
  </head>
  <body>
    <div>
      $body
      <div class="footer">
        <p>This email was sent to <span class="text-success">$toemail</span>$registereduser.</p>
        <p>
          <a href="$website/terms.html">Terms</a> | <a href="$website/privacy.html">Privacy</a> $unsubscribe
        </p>
        <p>$sitecompany &copy; $year &bull; ABN: $siteabn &bull; $siteaddress</p>
        <p class="text-center"><img style="opacity: 0.5; width: 40px;" src="$website/images/quill.php?id=$emailID">
        </p>
      </div>
    </div>
  </body>
</html>
EOT;
?>