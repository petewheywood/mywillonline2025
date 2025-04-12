<?php
  
/*
  This will automatically responde to bounce and complaint emails. It is called by receiving an Amazon SNS notification.
*/

# process SES bounces from Amazon SES notification

$postBody = file_get_contents('php://input');

$jsondata = json_decode($postBody);

$message = json_decode($jsondata->Message);

  $fh = fopen("bouncescopy.txt", "a");
  fwrite($fh, $postBody . "\n");
  fwrite($fh, $message . "\n");
  fclose($fh);  
