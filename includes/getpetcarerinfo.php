<?php

/*

Get pet carer organisation info to show on web page
- expect JSON data back

*/
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
  header("Location: /");
  exit;
}

session_start();

$carerID = $_GET['carerid'];

echo json_encode($_SESSION['petcareorgs'][$carerID]);

?>
