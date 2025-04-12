<?php
session_start();

if ($_GET) {
    $_SESSION['clientCountry'] = $_GET['country'];
    $_SESSION['clientRegion'] = $_GET['region'];
    echo "success";
} else {
    echo "fail";
}
