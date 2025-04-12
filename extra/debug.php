<?php
session_start();
$sessionid = session_id();
$sessionname = session_name();
echo "<pre>session_id : $sessionid\n" . print_r($_SESSION, true) . "\n\n" . print_r($_COOKIE, true) . "\n</pre>";