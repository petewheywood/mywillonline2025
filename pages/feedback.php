<?php
# This is a feedback page that is linked to by an email to a user.
# It is passed in an affiliatecode for the user. If this is not a valid code, then redirect to home page.


if ($email = $_SESSION['getVars']['email']) {
  # quickwill feedback
  # retrieve their details
  $state = $_SESSION['getVars']['st'];
  $fullname = $_SESSION['getVars']['nm'];
  $tx = $_SESSION['getVars']['tx'];
  $words = explode(' ',trim($fullname));
  $firstname = $words[0];
  $lastname = end($words);
  $surnamechar = substr($lastname, 0, 1);
  $name = $firstname . ' ' . $surnamechar;
  $userid = 0;
} else {
  # normal feedback
  $affcode = isset($_SESSION['getVars']['code']) ? $_SESSION['getVars']['code'] : '';
  $query = "SELECT id, firstname, surname, emailaddress FROM users WHERE affiliatecode = '$affcode' AND id in (SELECT userid FROM orders WHERE paid = 1);";
  $result = $dbh->query($query);
  $row = $result->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $userid = $row['id'];
    $name = $row['firstname'] . ' ' . $row['surname'];
    $firstname = $row['firstname'];
    $email = $row['emailaddress'];
    $state = '';
    $tx = '';
  } else {
    # redirect if not found
    echo "<script type='text/javascript'>window.location = \"/\";</script>\n";
  }
}

$title = "Customer Feedback - " . SITENAME;
$desc = "Please rate your experience with ease of use, value for money, and whether you would recommend " . SITENAME;
$keywords = "recommend " . SITENAME;

# display the feedback form for the user
require("pageincludes/header.php");

?>
      <section class="container">
        <h1>What do you think of <?php echo SITENAME; ?>?</h1>
        <h3>Hi <?php echo $firstname;?>,<br> <br>Thank you for ordering from <?php echo SITENAME; ?> and for taking the time to complete this really quick survey.<br>We really appreciate you rating our service.</h3>
        <hr>
        <form role="form" id="feedbackform" method="post" action="/">
          <input name="userid" type="hidden" value="<?php echo $userid;?>">
          <input name="state" type="hidden" value="<?php echo $state;?>">
          <input name="name" type="hidden" value="<?php echo $name;?>">
          <input name="email" type="hidden" value="<?php echo $email;?>">
          <input name="tx" type="hidden" value="<?php echo $tx;?>">
          <h2 class="text-primary">Is <?php echo SITENAME;?> easy to use?</h2>
        	<div class="form-group">
          	<div class="stars">
          		<input type="radio" name="easeOfUse" class="star-1" id="easeOfUse-1" value="1"/>
          		<label class="star-1" for="easeOfUse-1">1</label>
          		<input type="radio" name="easeOfUse" class="star-2" id="easeOfUse-2" value="2"/>
          		<label class="star-2" for="easeOfUse-2">2</label>
          		<input type="radio" name="easeOfUse" class="star-3" id="easeOfUse-3" value="3"/>
          		<label class="star-3" for="easeOfUse-3">3</label>
          		<input type="radio" name="easeOfUse" class="star-4" id="easeOfUse-4" value="4"/>
          		<label class="star-4" for="easeOfUse-4">4</label>
          		<input type="radio" name="easeOfUse" class="star-5" id="easeOfUse-5" value="5"/>
          		<label class="star-5" for="easeOfUse-5">5</label>
          		<span></span>
          	</div>
        	</div>
          <p class="starsdesc">1 <i class="glyphicon glyphicon-star"></i> = 'difficult to use' &nbsp;&nbsp; 5 <i class="glyphicon glyphicon-star"></i> = 'very easy to use'.</p>

          <h2 class="text-primary">Does <?php echo SITENAME;?> give good value for money?</h2>
        	<div class="form-group">
          	<div class="stars">
          		<input type="radio" name="valueForMoney" class="star-1" id="valueForMoney-1" value="1"/>
          		<label class="star-1" for="valueForMoney-1">1</label>
          		<input type="radio" name="valueForMoney" class="star-2" id="valueForMoney-2" value="2"/>
          		<label class="star-2" for="valueForMoney-2">2</label>
          		<input type="radio" name="valueForMoney" class="star-3" id="valueForMoney-3" value="3"/>
          		<label class="star-3" for="valueForMoney-3">3</label>
          		<input type="radio" name="valueForMoney" class="star-4" id="valueForMoney-4" value="4"/>
          		<label class="star-4" for="valueForMoney-4">4</label>
          		<input type="radio" name="valueForMoney" class="star-5" id="valueForMoney-5" value="5"/>
          		<label class="star-5" for="valueForMoney-5">5</label>
          		<span></span>
          	</div>
        	</div>
          <p class="starsdesc">1 <i class="glyphicon glyphicon-star"></i> = 'poor value' &nbsp;&nbsp; 5 <i class="glyphicon glyphicon-star"></i> = 'very good value'.</p>

          <h2 class="text-primary">Would you recommend <?php echo SITENAME;?> to others?</h2>
        	<div class="form-group">
          	<div class="stars">
          		<input type="radio" name="wouldRecommend" class="star-1" id="wouldRecommend-1" value="1"/>
          		<label class="star-1" for="wouldRecommend-1">1</label>
          		<input type="radio" name="wouldRecommend" class="star-2" id="wouldRecommend-2" value="2"/>
          		<label class="star-2" for="wouldRecommend-2">2</label>
          		<input type="radio" name="wouldRecommend" class="star-3" id="wouldRecommend-3" value="3"/>
          		<label class="star-3" for="wouldRecommend-3">3</label>
          		<input type="radio" name="wouldRecommend" class="star-4" id="wouldRecommend-4" value="4"/>
          		<label class="star-4" for="wouldRecommend-4">4</label>
          		<input type="radio" name="wouldRecommend" class="star-5" id="wouldRecommend-5" value="5"/>
          		<label class="star-5" for="wouldRecommend-5">5</label>
          		<span></span>
          	</div>
        	</div>
          <p class="starsdesc">1 <i class="glyphicon glyphicon-star"></i> = 'I would not recommend' &nbsp;&nbsp; 5 <i class="glyphicon glyphicon-star"></i> = 'I will certainly recommend'.</p>

          <h2 class="text-primary">Your comments</h2>
          <div class="form-group"> 
            <textarea id="comment" name="comment" rows="5" placeholder="Please include your comments here" class="form-control required" autofocus></textarea>
          </div>
          <br>
          <input type="submit" value="Send Feedback" class="btn btn-default" />
        </form>
        <hr>
      </section>
<?php
  require("pageincludes/footer.php");
?>