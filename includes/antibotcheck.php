<?php

# check that the POSTed sum is equal to the session var
session_start();

if ($_SESSION['contactSum'] == $_POST['contactSum']) {
  echo 'true';
} else {
  echo 'false';
}

?>