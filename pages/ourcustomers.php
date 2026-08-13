<?php
// get customer feedback
$query = "SELECT * FROM publishfeedback ORDER BY received DESC;";
$result = $dbh->query($query);
$feedbacks = $result->fetchAll();

$totalreviews = count($feedbacks);
$query = "SELECT count(*) as reviews, avg(easeofuse) as easeofuse, avg(valueformoney) as valueformoney, avg(wouldrecommend) as wouldrecommend FROM publishfeedback;";
$result = $dbh->query($query);
$ratings = $result->fetch();
$numreviews = $ratings['reviews'];
$avg_easeofuse = round($ratings['easeofuse'], 1);
$avg_valueformoney = round($ratings['valueformoney'], 1);
$avg_wouldrecommend = round($ratings['wouldrecommend'], 1);
$aggregaterating = round(($avg_easeofuse + $avg_valueformoney + $avg_wouldrecommend)/3,1);

$title = "Customer Reviews | " . SITENAME;
$desc = "Customer Reviews for our Online Will Kit · Free updates for life · 100% Satisfaction Guarantee · Free Enduring Power of Attorney · Provision for Your Pets · Executors Memo";
$keywords = "wills, will kit, will, online will kit, Australian Will Online, how do I make a will, feedback, rating, customers";


require("pageincludes/header.php");
?>

    <section class="hero" style="max-height: 250px;">
      <div class="caption">
        <h1>Australia's most reviewed Online Will Kit</h1>
        <a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-primary">Make A Will</a>
        <h3>Online Will Kit · Free updates for life · 100% Satisfaction Guarantee · Free Enduring Power of Attorney · Provision for Your Pets · Executors Memo</h3>
      </div>
      <img src="images/hero.jpg" class="img-responsive" alt="My Will Online">
    </section>

    <section class="container" id="reviews">
      <div itemscope itemtype="https://schema.org/Product">
        <meta content="Premium Will" itemprop="name">
        <meta content="My Will Online" itemprop="brand">
    		<meta content="singlewill" itemprop="productID">
  
        <section class="container text-center" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
          <div>
            <h2 class="text-primary">
              Overall Customer Rating <span itemprop="ratingValue"><?php echo $aggregaterating;?></span>
              out of 5 from <span itemprop="reviewCount"><?php echo $numreviews;?></span> customers reviews.</h2>
            <p>
              <strong class="text-info">Ease Of Use:</strong> <?php echo $avg_easeofuse;?> out of 5,
              <strong class="text-info">Value For Money:</strong> <?php echo $avg_valueformoney;?> out of 5,
              <strong class="text-info">Would Recommend:</strong> <?php echo $avg_wouldrecommend;?> out of 5.
            </p>        
          </div>
        </section>
        <section class="container-fluid">
<?php
foreach ($feedbacks as $feedback) {
  $location = $feedback['suburb'] > '' ? titleCase(strtolower($feedback['suburb'])) . ', ' . $feedback['state'] : $feedback['state'];
  $reviewRating = round(($feedback['easeofuse'] + $feedback['valueformoney'] + $feedback['wouldrecommend'])/3,1);
  $receivedDate = preg_replace("/\s/", "T", $feedback['received']);
?>
          <div class="feedback" itemprop="review" itemscope itemtype="https://schema.org/Review">
            <div class="row">
              <div class="col-sm-5">
                <div>
                  <span itemprop="author"><?php echo $feedback['name'];?></span><br>
                  <small><em><?php echo $location;?> - <time itemprop="datePublished" datetime="<?php echo $receivedDate;?>"><?php echo showdate($feedback['received']);?></time></em></small>
                  <div class="hidden" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
            				<meta content="5" itemprop="bestRating">
            				<meta content="1" itemprop="worstRating">
            				<meta content="<?php echo $reviewRating;?>" itemprop="ratingValue">
            			</div>
  
                </div>
                <br>
                <i class="glyphicon glyphicon-star <?php echo $feedback['easeofuse'] > 0 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['easeofuse'] > 1 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['easeofuse'] > 2 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['easeofuse'] > 3 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['easeofuse'] > 4 ? 'yellow' : '';?>"></i>
                Ease of Use
                <br>
                <i class="glyphicon glyphicon-star <?php echo $feedback['valueformoney'] > 0 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['valueformoney'] > 1 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['valueformoney'] > 2 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['valueformoney'] > 3 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['valueformoney'] > 4 ? 'yellow' : '';?>"></i>
                Value for Money
                <br>
                <i class="glyphicon glyphicon-star <?php echo $feedback['wouldrecommend'] > 0 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['wouldrecommend'] > 1 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['wouldrecommend'] > 2 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['wouldrecommend'] > 3 ? 'yellow' : '';?>"></i>
                <i class="glyphicon glyphicon-star <?php echo $feedback['wouldrecommend'] > 4 ? 'yellow' : '';?>"></i>
                Would Recommend
              </div>
              <div class="col-sm-7">
                <em itemprop="reviewBody">&ldquo;<?php echo $feedback['comments'];?>&rdquo;</em>
              </div>
            </div>
          </div>
<?php
}
?>
        </section>
      </div>
<?php
require("pageincludes/footer.php");
?>