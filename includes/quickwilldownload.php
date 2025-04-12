<?php

/*

Used to create and display a PDF of the document package

*/

session_start();
/* stop people going here with out having qwOrderData */
if (!isset($_SESSION['qwOrderData'])) {
  header("Location: /");
  exit;
}

require_once('functions.php');

$sessionID = session_id();

$qwOrderData = $_SESSION['qwOrderData'];


/*
echo "<pre>" . print_r($qwOrderData, true) . "</pre>";
exit;
*/


if (!(isset($qwOrderData['tcstatus']) && ($qwOrderData['tcstatus'] == 'Approved' || $qwOrderData['tcstatus'] == 'Completed')) && $qwOrderData['orderamount'] > 0 ) {
  header("Location: /");
  exit;
}

prepareQuickWillData();
/*
echo "<pre>" . print_r($qwOrderData, true) . "</pre>";
exit;
*/

# prefix to prepend to all document names
$docprefix = str_replace(" ", "_", $qwOrderData['testatorFullName']);
$docprefix = str_replace("'", "", $docprefix); // get rid of single quotes in name
$docprefix = str_replace(array("(", ")"), "", $docprefix);
$finaldocname =  "$docprefix.pdf";

# get the document template details and split on comma
$doctemplates = array();
$doctemplates = array_merge($doctemplates, preg_split("/\s*,\s*/", "QuickLastWillAndTestament.php,QuickLastWillAndTestamentInstructions.php"));

# escape all TeX characters for the TeX documents
$texOrderData = texFix($qwOrderData);

# temp file prefix
$fileprefix = uniqid(PREFIX);

# document processing folder
$curdir = getcwd();
$docpath = '/tmp';
chdir($docpath);

$createDate = date("jS F Y"); # used to merge into document

# main document processing loop
foreach($doctemplates as $doc) {
  $doc = trim($doc);
  $docname = $fileprefix . '_' . $doc;
  # determine type of document  .php(tex), .pdf
  $ext = pathinfo($doc, PATHINFO_EXTENSION);

  # do Latex processing and convert final to PDF using pdflatex
  # The files will have PHP code in them so do a require first to run code and we will have all the output in a var called $texdoc
  require($curdir . '/templates/' . $doc);
  $docname = preg_replace('/php/', 'tex', $docname);
  file_put_contents($docpath . '/' . $docname, $texdoc);
  # run pdflatex to create PDF
  $result = exec('pdflatex ' . $docname);
  $result = exec('pdflatex ' . $docname); # . ' %>>pass.log'); # and again to sort out any cross references eg. lastpage
  $docname = preg_replace('/tex/', 'pdf', $docname);
  # next two lines convert fonts to paths to stop copying
  #rename($docname, 'temp_' . $docname);
  #$result = exec("gs -dNoOutputFonts -sDEVICE=pdfwrite -o $docname temp_" . $docname);
} # end of foreach

# merge the created PDF files together
$joincmd = 'pdftk ';
$files = glob("$fileprefix*.pdf");
rsort($files);
foreach ($files as $file) {
	$joincmd .= basename($file) . ' ';
}
$finaldoc = $fileprefix . '_final.pdf';
# using a password will also stop the EPoA forms being interactive
#$joincmd .= "cat output $finaldoc owner_pw 'hornet18' allow Printing";
$joincmd .= "cat output $finaldoc";
//error_log($joincmd);
$result = exec($joincmd);

# now display the PDF
header('Content-type: application/pdf');
header('Content-Disposition: filename="' . $finaldocname . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($finaldoc));
header('Accept-Ranges: none');
readfile($finaldoc);


# clean up the files
foreach (glob("$fileprefix*") as $filename) {
  unlink($filename);
}

# send order received email to admin
require_once('parms.php');
$for = $qwOrderData['testatorFullName'];
$email = $qwOrderData['testatorEmail'];
$orderid = $qwOrderData['orderid'];
$body = "$for - $email - downloaded\nOrderID: $orderid";
$subject = "QuickWill Download - $for";
adminEmail(SITEEMAIL, $subject, $body);

exit;