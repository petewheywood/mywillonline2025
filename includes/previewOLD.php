<?php

/*

Used to create and display a PDF preview of the Will

*/

if (!$_SESSION['userid']) {
  header("Location: /");
  exit;
}

# get the orderdata for the user/order
$orderID = $_SESSION['orderid']; 

$dbh = dbConnect();
$result = $dbh->query("SELECT AES_DECRYPT(orderdata,'{$_SESSION['affiliatecode']}') AS orderdata FROM orders WHERE id = $orderID AND userid = {$_SESSION['userid']};");
$row = $result->fetch(PDO::FETCH_ASSOC);
$orderData = unserialize(stripslashes($row['orderdata']));

if (!isset($orderData['person']) || !isset($orderData['executor'][1])) {
  echo "<script type='text/javascript'>alert('Incomplete Data, Please enter all the required information before requesting a preview.');window.close();</script>";
  exit;
}



prepareData();

# used for preview only to place watermark across document
$preview = true;

# prefix to prepend to all document names
$docprefix = str_replace(" ", "_", $orderData['person']['fullname']);
$docprefix = str_replace("'", "", $docprefix); // get rid of single quotes in name
$docprefix = str_replace(array("(", ")"), "", $docprefix);

# get the will document template
$doctemplates = array();
$doctemplates = array_merge($doctemplates, preg_split("/\s*,\s*/", $_SESSION['products']['singlewill']['template']));
$doc = $doctemplates[0]; # just the LastWillAndTestament.php

# escape all TeX characters for the TeX documents
$texOrderData = texFix($orderData);

$doc = trim($doc);
$docname = $docprefix . '-' . $doc;
$docname = preg_replace('/php/', 'pdf', $docname); # change the extension to .pdf
$fileprefix = uniqid(PREFIX);
$tempfile = $fileprefix . '.tex';

# change to /tmp
$curdir = getcwd();
chdir('/tmp');

# do Latex processing and convert final to PDF using pdflatex
# The files will have PHP code in them so do a require first to run code and we will have all the output in a var called $texdoc
require($curdir . '/templates/' . $doc);
file_put_contents($tempfile, $texdoc);
# run pdflatex to create PDF
$result = exec('pdflatex ' . $tempfile);
$result = exec('pdflatex ' . $tempfile); # . ' %>>pass.log'); # and again to sort out any cross references eg. lastpage

# secure the PDF so it cannot be copied from - use gs to convert to paths is the simplest, albeit a large file
$tempfile = preg_replace('/tex/', 'pdf', $tempfile);
$tempfile2 = $fileprefix . '_temp' . '.pdf';
$finalfile = $fileprefix . '-Will-Preview.pdf';
$finalfilename = $docprefix . '-Will-Preview.pdf';
$result = exec("gs -dNoOutputFonts -sDEVICE=pdfwrite -o $tempfile2 $tempfile");
$result = exec("pdftk $tempfile2 output $finalfile owner_pw hornet18");
# now display the PDF
header('Content-type: application/pdf');
header('Content-Disposition: filename="' . $finalfilename . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($finalfile));
header('Accept-Ranges: none');
readfile($finalfile);

# clean up the files
foreach (glob("$fileprefix*") as $filename) {
  unlink($filename);
}

exit;