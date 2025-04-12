<?php

/*

Used to create and display a PDF of the document package

*/

if (!isset($_SESSION['userid'])) {
  header("Location: /");
  exit;
}

# get the orderdata for the user/order
if (!($orderID = $_SESSION['orderid'])) $orderID = $_SESSION['getVars']['orderid']; 

// $dbh = dbConnect();
$result = $dbh->query("SELECT AES_DECRYPT(orderdata,'{$_SESSION['affiliatecode']}') AS orderdata FROM orders WHERE id = $orderID AND userid = {$_SESSION['userid']};");
$row = $result->fetch(PDO::FETCH_ASSOC);
$dbh = null;

/* now create the documents */
$orderData = unserialize(stripslashes($row['orderdata']));

if (!isset($orderData['person'])) {
  echo "Invalid Data<script type='text/javascript'>setTimeout('window.close();', 1000);</script>";
  exit;
}

prepareData();

# used for preview only to place watermark across document
$preview = true;

# prefix to prepend to all document names
$docprefix = str_replace(" ", "_", $orderData['person']['fullname']);
$docprefix = str_replace("'", "", $docprefix); // get rid of single quotes in name
$docprefix = str_replace(array("(", ")"), "", $docprefix);
$finaldocname =  $docprefix . "_preview.pdf";

# get the document template details and split on comma
$doctemplates = array();
$doctemplates = array_merge($doctemplates, preg_split("/\s*,\s*/", $_SESSION['products']['singlewill']['template']));

# remove Instructions
if(($key = array_search('LastWillAndTestamentInstructions.php', $doctemplates)) !== false) {
    unset($doctemplates[$key]);
}
# remove Executors Memo
if(($key = array_search('ExecutorsMemo.php', $doctemplates)) !== false) {
    unset($doctemplates[$key]);
}

# escape all TeX characters for the TeX documents
$texOrderData = texFix($orderData);

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
  	// this is a TEX type document
    # do Latex processing and convert final to PDF using pdflatex
    # The files will have PHP code in them so do a require first to run code and we will have all the output in a var called $texdoc
    require($curdir . '/templates/' . $doc);
    $docname = preg_replace('/php/', 'tex', $docname);
    file_put_contents($docpath . '/' . $docname, $texdoc);

    # run pdflatex to create PDF
    $result = exec('pdflatex ' . $docname);
    $result = exec('pdflatex ' . $docname); # . ' %>>pass.log'); # and again to sort out any cross references eg. lastpage
} # end of foreach

# merge the created PDF files together
$joincmd = 'pdftk ';
$files = glob("$fileprefix*.pdf");
rsort($files);
foreach ($files as $file) {
	$joincmd .= basename($file) . ' ';
}
$finaldoc = $fileprefix . '_preview.pdf';
//$joincmd .= "cat output $finaldoc owner_pw '$fileprefix'";
$joincmd .= "cat output $finaldoc";
$result = exec($joincmd);

# now convert to multipage tiff and then back to pdf

$tempfile = $fileprefix . '.tif';
$result = exec("gs -r300x300 -sDEVICE=tiffgray -o $tempfile $finaldoc");
$result = exec("tiff2pdf -j -q 30 -o $finaldoc $tempfile");
// $result = exec("gs -dNoOutputFonts -sDEVICE=pdfwrite -o $tempfile $finaldoc");
$result = exec("pdftk $finaldoc output $finaldocname owner_pw '$fileprefix'");


header('Content-type: application/pdf');
header('Content-Disposition: filename="' . $finaldocname . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($finaldocname));
header('Accept-Ranges: none');
readfile($finaldocname);


# clean up the files
foreach (glob("$fileprefix*") as $filename) {
  unlink($filename);
}

exit;