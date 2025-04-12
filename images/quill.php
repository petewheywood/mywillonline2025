<?php
$id = $_REQUEST['id'];
require_once("../includes/functions.php");
# log email open into emailsent
if ($id > 0) {
  $dbh = dbConnect();
  $sth = $dbh->query("SELECT IF(firstopened IS NOT NULL, 1, 0) AS newopen FROM emailsent WHERE id = $id;");
  $newopen = $sth->fetchColumn();
  if ($newopen == 0) {
    $dbh->exec("UPDATE emailsent SET firstopened = NOW(), timesopened = 1 WHERE id = $id;");
  } else {
    $dbh->exec("UPDATE emailsent SET lastopened = NOW(), timesopened = timesopened + 1 WHERE id = $id;");
  }
}
# generate image
$im = imagecreatefrompng("../images/quill.png");
imageAlphaBlending($im, true);
imageSaveAlpha($im, true);
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
?>