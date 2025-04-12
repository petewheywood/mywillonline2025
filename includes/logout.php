<?php

# set logoutdate field in visits table

$dbh->exec("
    UPDATE visits SET logoutdate = NOW() WHERE sessionid = '$sessionID';
");
$dbh = null;

# clear all session variables and end session which effectively logs the user out

session_unset();

# Now redirect to home - this will create a new virgin session
header("Location: /");

?>      
