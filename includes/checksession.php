<?php

/*

check session is still active

*/

require_once('functions.php');

session_start();

if (!isset($_SESSION['userid']) || !($_SESSION['userid'] > 0)) {
  $data['result'] = "SESSIONENDED";
} else {
  $data['result'] = "OK";
}
echo json_encode($data);


