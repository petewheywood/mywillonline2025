<?php
# used to create an image asking for the sum of 2 integers - for antibot protection
session_start();
$intA = rand(1,5);
$intB = rand(1,5);
$_SESSION['contactSum'] = $sum = $intA + $intB;
$im = imagecreate(110, 35);
$bg = imagecolorallocate($im, 255, 255, 255);
$textcolor = imagecolorallocate($im, 102, 102, 102);
imagestring($im, 3, 0, 7, "Sum of $intA and $intB?", $textcolor);
header('Content-Type: image/jpg');
imagejpeg($im);
imagedestroy($im);
?>