<?php

/*

Used to create and display a PDF preview of the Will

*/

/* stop people going here with out having qwOrderData */
if (!isset($_SESSION['qwOrderData'])) {
  header("Location: /");
  exit;
}

require_once('includes/functions.php');


# get the orderdata for the user/order
$qwOrderData = $_SESSION['qwOrderData'];

prepareQuickWillData();

/*
header "<pre>" . print_r($qwOrderData, true) . "</pre>";
exit;
*/

# used for preview only to place watermark across document
$preview = true;

# prefix to prepend to all document names
$docprefix = str_replace(" ", "_", $qwOrderData['testatorFullName']);
$docprefix = str_replace("'", "", $docprefix); // get rid of single quotes in name
$docprefix = str_replace(array("(", ")"), "", $docprefix);

# get the will document template
$doc = "QuickLastWillAndTestament.php";

# escape all TeX characters for the TeX documents
$texOrderData = texFix($qwOrderData);

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
$tempfile2 = $fileprefix . '.pdf';
$tempfile3 = $fileprefix . '.tif';
$finaldocname =  $docprefix . "_preview.pdf";

# now convert to multipage tiff and then back to pdf

#$result = exec("gs -r150x150 -sDEVICE=tiffgray -o $tempfile3 $tempfile2");
#$result = exec("tiff2pdf -j -q 30 -o $tempfile2 $tempfile3");
$result = exec("pdftk $tempfile2 output $finaldocname owner_pw '$fileprefix'");

# now display the PDF
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