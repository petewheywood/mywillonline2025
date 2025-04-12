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


/*
echo "<pre>";
print_r($orderData);
exit;
*/


# prefix to prepend to all document names
$docprefix = str_replace(" ", "_", $orderData['person']['fullname']);
$docprefix = str_replace("'", "", $docprefix); // get rid of single quotes in name
$docprefix = str_replace(array("(", ")"), "", $docprefix);
$finaldocname =  "$docprefix.pdf";

# get the document template details and split on comma
$doctemplates = array();
foreach($orderData['product'] as $product => $yesno) {
  if ($product != 'mirrorwill' && $yesno == 'Yes') {
    // mirrorwills are a special case
    // split templates on comma and merge to doctemplates
    $doctemplates = array_merge($doctemplates, preg_split("/\s*,\s*/", $_SESSION['products'][$product]['template']));
  }
}

# escape all TeX characters for the TeX documents
$texOrderData = texFix($orderData);

# temp file prefix
$fileprefix = uniqid(PREFIX);

# document processing folder
$curdir = getcwd();
$docpath = '/var/www/tmp';
chdir($docpath);

$createDate = date("jS F Y"); # used to merge into document

# main document processing loop
foreach($doctemplates as $doc) {
  $doc = trim($doc);
  $docname = $fileprefix . '_' . $doc;
  # determine type of document  .php(tex), .pdf
  $ext = pathinfo($doc, PATHINFO_EXTENSION);

  if ($ext == 'pdf') {
    # check if we need to merge XFDF data with PDF
    if (substr($doc, 0, 4) == 'EPoA' || substr($doc, 0, 4) == 'EPoG') {
      # EPoA and EPoG documents state specific
      $state = isset($orderData['epoaState']) ? $orderData['epoaState'] : $orderData['person']['state']; // use testator state if for some reason epoa state is not set
      $filepath = $curdir . '/templates/' . $state . '_' . $doc;
      if (file_exists($filepath)) {
        copy($filepath, $docname);
      }
    } else {
      # no processing, just copy template to fullpath
      copy($curdir . '/templates/' . $doc, $docname);
    }
    # end of .pdf section
  } elseif ($ext == 'php') {
  	// this is a TEX type document
    # do Latex processing and convert final to PDF using pdflatex
    # The files will have PHP code in them so do a require first to run code and we will have all the output in a var called $texdoc
    require($curdir . '/templates/' . $doc);
    $docname = preg_replace('/php/', 'tex', $docname);
    file_put_contents($docpath . '/' . $docname, $texdoc);
    # run pdflatex to create PDF
    $result = exec('pdflatex ' . $docname);
    $result = exec('pdflatex ' . $docname); # . ' %>>pass.log'); # and again to sort out any cross references eg. lastpage
    error_log($result);
    #$docname = preg_replace('/tex/', 'pdf', $docname);
    # next two lines convert fonts to paths to stop copying
    #rename($docname, 'temp_' . $docname);
    #$result = exec("gs -dNoOutputFonts -sDEVICE=pdfwrite -o $docname temp_" . $docname);
  } else {
    # unsupported filetype so do nothing
  }
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

exit;