<?php
/*
This sets some parameters
*/

define('DEVSITE', (strpos(__DIR__, 'dev') === FALSE) ? false : true);
define('DOMAIN', 'my-will-online.com.au');

/* QuickWill or not */
define('QUICKWILL', false);

/* general information */
define('COUNTRY','Australia');
define('CURRENCY','AUD');
define('ADMIN', 'SuperHornet18!');
define('PAYMENTGATEWAY','PayPal');
define('PAYMENTGATEWAYFULLNAME', 'PayPal Australia Pty Limited');
define('PRODUCTID', 1);
define('PRODUCT', 'singlewill');
define('DISCOUNT', 0.2); # standard discount for affiliates
define('ORGDISCOUNT', 0.2); # standard discount for organisations who link back
define('TAXNAME', 'GST'); 
define('TAX', 0); # cancelled GST registration due to low turnover
define('PRODUCTEXPIRY', 99); # months from payment for product to expire
define('GUARANTEEDAYS', 7); # guarantee period
define('GOOGLE_API_KEY', 'AIzaSyC3qxfbTFCel-tnCnyv7pnj5HwLSqDxwwg');
define('GOOGLE_ENDPOINT', 'https://www.googleapis.com/urlshortener/v1');

/* PAYPAL, CBA, GOOGLE and FACEBOOK parms */
define('CBA_URL', 'https://migs.mastercard.com.au/vpcdps');

if (DEVSITE) {
  define('SITEURL', 'http://dev.' . DOMAIN);
	define('PAYPALSITE', 'www.sandbox.paypal.com');
	define('PAYPALID', 'E7KR8A9MRZNDC'); // email to login is pete@my-will-online.com.au to www.sandbox.paypal.com
  define('PAYPAL_API_USERNAME', 'pete-facilitator_api1.my-will-online.com.au');
  define('PAYPAL_API_PASSWORD', 'RZCDDPDNSBNMH3RQ');
  define('PAYPAL_API_SIGNATURE', 'AFcWxV21C7fd0v3bYYYRCpSSRl31ADUEqTyLFWaoKxdHPObaRmmf0WFJ');
  define('FBAPPID', '830229183701182');
  define('GOOGLESV', 'gxvOcm2nPJpyqvyro8sjOsLha5kPrTXesYA5zF_mRgw');
  define('MSSV', '');
  define('CBA_MERCHANTID', 'TESTMYWONLCOM01');
  define('CBA_ACCESSCODE', 'EB4A4E41');
  define('DB', 'mwo_dev');
  define('SITEPHONE', "1300 DEV SITE");
  define('DATADIR',"mwo-dev-data");
  define('POLIMERCHANTCODE', 'S6102974');
  define('POLIAUTHCODE', '64HB1DB5');
} else {
  define('SITEURL', 'https://' . DOMAIN);
  define('PAYPALSITE', 'www.paypal.com');
  define('PAYPALID', 'MTR7EKSJVC7G2'); # login pete@my-will-online.com.au
  define('PAYPAL_API_USERNAME', 'orders_api2.my-will-online.com.au');
  define('PAYPAL_API_PASSWORD', 'SL4F9ZCZXVMNHVFK');
  define('PAYPAL_API_SIGNATURE', 'AFcWxV21C7fd0v3bYYYRCpSSRl31A5ugXCFn4Of15T3MlQnb4enkHOLz');
  define('FBAPPID', '827914033932697');
  define('GOOGLESV', 'gxvOcm2nPJpyqvyro8sjOsLha5kPrTXesYA5zF_mRgw');
  define('MSSV', 'E89EFCC4CA445C55B863EBCB9499D25B');
  define('CBA_MERCHANTID', 'MYWONLCOM01');
  define('CBA_ACCESSCODE', '54A6768C');
  define('DB', 'mwo');
  define('SITEPHONE', "1300&nbsp;678&nbsp;181");
  define('DATADIR',"mwo-data");
  define('POLIMERCHANTCODE', 'S6102974');
  define('POLIAUTHCODE', '64HB1DB5');
}

define('PAYPAL', 'https://' . PAYPALSITE . '/cgi-bin/webscr');


# DB
define('DBHOST', 'itrakkadb');
define('DBUSER','mmw');
define('DBPW','_}uG@8m4=R^w');

# commission details
define('LEVELS', 3); # levels deep to pay commissions
define('COMMISSION', 0.2); # percentage paid to uplines
define('COMMISSIONPAYABLEDAYS', 28); # days before commission can be paid
define('COMMISSIONPAYREQUESTDAYS', 3); # days before commission is paid after request for payment

# contact
define('SITEADDRESS', "Darlinghurst, NSW 2010, Australia");
define('MAILADDRESS', "404/172-190 Riley Street<br>Darlinghurst NSW 2010<br>Australia");
define('SITENAME', 'My Will Online');
define('SITENAMEC', '<span style="color: #c34542;">My</span><span style="color: #2B669A;">Will</span><span style="color: #c34542;">Online</span>');
define('SITEEMAIL', "queries@mywillonline.com.au");
define('SITEEMAILDOMAIN', "mywillonline.com.au");
define('PREFIX', "mwo"); # used as a temp file prefix
define('SITEABN', '12 916 391 937');
#define('SITEABN', '24 161 199 604');
define('SITECOMPANY', 'My Will Online Australia');
#define('SITECOMPANY', 'iTrakka Software Pty Ltd');
define('SITEBOSS', 'Pete Heywood');
define('SITEBOSSROLE', 'Operations Manager');
define('SITEBOSSMOBILE', '0488 484 884');
define('SITEBOSSEMAIL', 'pete@mywillonline.com.au');
define('SITEDESCRIPTION', 'Easy to use, fast and inexpensive online will kit for all Australians. 100% customer satisfaction guarantee. Free Enduring Power of Attorney and Guardianship included. Unlimited updates for life. And, provision for your pets in your will.');
define('GOOGLETRACKINGCODE', 'UA-57643755-1');

# set the locale for the site
define('LOCALE', "en-AU");
$oldLocale = setlocale(LC_ALL, LOCALE);

# text constants
define('SAVEBUTTON', '<i class="glyphicon glyphicon-save"></i> Save and Return');

# users flags
# bit 1 - notmyaccount - unauthorised rego request
define('FLAG_NOTMYACCOUNT', 1);
# bit 2 - unsubscribe - from marketing emails
define('FLAG_UNSUBSCRIBE', 2);
# bit 3 - sale code bit 
define('FLAG_SALECODE', 4);
# bit 4 - organisation 
define('FLAG_ORGANISATION', 8);
# bit 5 - bounce 
define('FLAG_BOUNCE', 16);
# bit 6 - auto beneficiary 
define('FLAG_BENEFICIARY', 32);
# bit 7 - spare 
define('FLAG_SPARE', 64);
# bit 8 - admin user
define('FLAG_ADMIN', 128);

# telstra appkey and secret
define('TELSTRA_APPKEY', 'sXAABNerU5xPsNnhSGOrsQyCTFN7S2yh');
define('TELSTRA_APPSECRET', 'm4LbfK4W8dfGGAeO');
$smsrecipients = array('0488484884');

?>