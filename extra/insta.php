<?php
require_once("../includes/functions.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
    <section class="container-fluid">
      <div id="instagram">
      	<?php echo instagram_feed('IMAGE',8); ?>
      </div>
    </section>

