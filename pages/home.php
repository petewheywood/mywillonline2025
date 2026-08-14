<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
    header("Location: /");
    exit;
}

$base = url_origin();

$_SESSION['lastPage'] = basename(__FILE__);

# if referred send straight to start.html
if (isset($_SESSION['getVars']['afid'])) {
    $_SESSION['introcode'] = $_SESSION['getVars']['afid'];
    echo "<script type='text/javascript'>location = 'start.html';</script>";
    exit;
}

// get customer feedback
$query = "SELECT * FROM publishfeedback ORDER BY received DESC LIMIT 50;";
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
$aggregaterating = round(($avg_easeofuse + $avg_valueformoney + $avg_wouldrecommend)/3, 1);

// prices
$quickwillprice = $_SESSION['products']['quickwill']['priceinctax'] == 0 ? '<u>FREE</u>' : '$' . substr($_SESSION['products']['quickwill']['priceinctax'],0,-3);
$premiumwillprice = $_SESSION['products']['singlewill']['priceinctax'] == 0 ? '<u>FREE</u>' : '$' . substr($_SESSION['products']['singlewill']['priceinctax'],0,-3);
$mirrorwillprice = '$' . ($_SESSION['products']['mirrorwill']['priceinctax'] + $_SESSION['products']['singlewill']['priceinctax']);

// display meta data and headings based upon whether a product is free of not
$keywords = "wills, will kit, last will and testament, online will, online wills, internet wills, online will kit";
$desc = 'Create a legally valid Australian will online in minutes for ' . (QUICKWILL ? 'from ' . $quickwillprice : $premiumwillprice) . '. Free Enduring Power of Attorney, free lifetime updates, ' . $aggregaterating . ' stars from ' . $numreviews . ' reviews.';
if ($_SESSION['products']['singlewill']['priceinctax'] == 0 || $_SESSION['products']['quickwill']['priceinctax'] == 0) {
	$title = "FREE Online Will - Australia | " . SITENAME;
  $freewill = true;
  $bannerh1 = 'Make a Will Online for FREE<br><small>Free Last Will and Testament</small>';
	$bannerh2 = '<h2>Secure · Legal Across Australia · 100% Satisfaction Guarantee · 100% Australian</h2>';
} else {
	$title = "Make a Will Online in Australia | " . $premiumwillprice . " Legal Will Kit | " . SITENAME;
  $freewill = false;
  $bannerh1 = "Live your life to fullest. Safeguard your legacy. Create your will online today.";
// 	$bannerh2 = '<h2>Secure · Legal Across Australia · 100% Satisfaction Guarantee · 100% Australian</h2>';
	$bannerh2 = '<h2 style="margin-top: 20vw;">Free updates for life and a Free Enduring Power of Attorney.<br> <br>' . $premiumwillprice . ' for a single will or ' . $mirrorwillprice . ' for a couple.</h2>';
}

require("pageincludes/header.php");



?>
    <section class="hero">
      <div class="caption">
        <h1><?php echo $bannerh1 ;?></h1>
        <?php echo $bannerh2 ;?>
      </div>
      <img src="images/hero.jpg" class="img-responsive" alt="Make your will online to protect your family in Australia">
    </section>

    <section class="container-fluid text-center">
      <p style="padding: 20px 0 0 0; margin: 0;"><a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-success btn-lr">Start Now</a></p>
    </section>

    <section class="container-fluid" id="compare">
      <div class="container">
        <?php require_once("includes/comparison.php");?>
      </div>
    </section>
    
    <section class="container-fluid" id="reviews">  
      <div class="container" itemscope itemtype="https://schema.org/Product">
        <meta content="Online Will" itemprop="name">
        <meta content="My Will Online" itemprop="brand">
        <meta content="singlewill" itemprop="productID">
        <div class="text-center" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
          <div>
            <a href="ourcustomers.html" title="Customers Reviews"><h2>Overall Customer Rating <span itemprop="ratingValue"><?php echo $aggregaterating;?></span>
            out of 5 from <span itemprop="reviewCount"><?php echo $numreviews;?></span> real customer reviews.</h2></a>
          </div>
        </div>
        <div class="home">
          <div class="feedback">
<?php
$i = 0;
foreach ($feedbacks as $feedback) {
    $location = $feedback['suburb'] > '' ? titleCase(strtolower($feedback['suburb'])) . ', ' . $feedback['state'] : $feedback['state'];
    $reviewRating = round(($feedback['easeofuse'] + $feedback['valueformoney'] + $feedback['wouldrecommend'])/3, 1);
    $receivedDate = preg_replace("/\s/", "T", $feedback['received']);
?>
            <div class="row <?php echo $i++ == 0 ? 'active' : 'inactive';?>" itemprop="review" itemscope itemtype="https://schema.org/Review">
              <div class="col-sm-6">
                <span itemprop="author" itemscope itemtype="https://schema.org/Person"><span itemprop="name"><?php echo $feedback['name'];?></span></span>
                <br>
                <small>
                  <em>
                    <?php echo $location;?> 
                    <br>
                    <time itemprop="datePublished" datetime="<?php echo $receivedDate;?>"><?php echo showdate($feedback['received']);?></time>
                  </em>
                </small>
                <br>
                <br>
                <div class="hidden" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
                  <meta content="5" itemprop="bestRating">
                  <meta content="1" itemprop="worstRating">
                  <meta content="<?php echo $reviewRating;?>" itemprop="ratingValue">
                </div>
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
              <div class="col-sm-6 feedbackcomments">
                <em itemprop="reviewBody">
                  &ldquo;<?php echo $feedback['comments'];?>&rdquo;
                </em>
              </div>
            </div>
<?php
}
?>
          </div>
          <div class="pull-right">
            <a href="ourcustomers.html" title="Customers Reviews"><em>Read more customer reviews...</em></a>
          </div>
        </div>
      </div>
    </section>

    <section class="container-fluid">
      <div class="container" id="validwill">    
        <div class="row">
          <div class="col-sm-6">
            <h2>How To Make A Will In Australia</h1>
            <p>
              For a Will to be legally valid in Australia, the following conditions must be met:
            </p>
            <ol>
              <li>
                The person making the Will must be at least 18 years of age
              </li>
              <li>
                The person must have an intention to make the Will
              </li>
              <li>
                The Will must be made voluntarily without any pressure from another person
              </li>
              <li>
                The person must understand the contents of the Will, and approve of what is written
              </li>
              <li>
                Two witnesses (who are over 18 and who are not mentioned as a beneficiary in the Will and not the partner of a beneficiary) must sign, and acknowledge the Will in the presence of the person making the will. They must also see the Will maker sign the Will.
              </li>          
            </ol>
          </div>
          <div class="col-sm-6">
            <h2>Is This Will Kit The Best Option For Me?</h1>
              <p>
                Our Will kit is perfectly suited for most. It is flexible enough to clearly represent your wishes and it produces an
                unambiguous, plain English, legal Will that is valid in all Australian states and territories.
              </p>
              <p>
                However, if your situation is complex, such as, having one or
                more ex-partners, children from more than one relationship, family trusts, complex business arrangements,
                and so on, we suggest you consider using an estate planning solicitor to assist you with writing your will.
              </p>
          </div>
        </div>
      </div>
    </section>
    
    <section>
      <div class="text-center">
        <a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-success">Shall we continue?</a>
      </div>
    </section>

    <section class="container-fluid" id="s5">    
      <div class="container">
        <div class="row">
          <div class="col-sm-4">
            <h2>Every Australian needs a will.</h2>
            <p>
              If you die without a will, your estate will be divided according to state government rules.
              When you make a will, you get to decide how your assets and belongings will be distributed. Having a
              will makes your wishes clear and legally binding.
              We make it simple to create a will online. <a href="why-you-need-a-will.html">Read more...</a>
            </p>
          </div>
          <div class="col-sm-4">
            <h2>Enduring Power of Attorney</h2>
            <p>
              An enduring power of attorney grants someone you trust power over your financial affairs
              when you are no longer able to manage them yourself. <?php echo SITENAME;?> includes a <strong>FREE Enduring Power of Attorney</strong> with each
              <?php echo QUICKWILL && $_SESSION['quickwill'] ? 'Premium Will' : 'Will';?>.  <a href="enduring-power-of-attorney.html">Read more...</a>
            </p>
          </div>
          <div class="col-sm-4">
            <h2>Enduring Guardianship</h2>
            <p>
              An enduring guardianship (also called an Advanced Health Care Directive in some states) grants someone you trust power over your personal and health care decisions
              when you are no longer able to manage them yourself. <?php echo SITENAME;?> includes a <strong>FREE Enduring Guardianship</strong> with each
              <?php echo QUICKWILL && $_SESSION['quickwill'] ? 'Premium Will' : 'Will';?>. <a href="about-epog.html">Read more...</a>
            </p>
          </div>
        </div>
      </div>
    </section>

    <script type="application/ld+json">
      {
        "@context": "http://schema.org",
        "@type": "Organization",
        "name": "<?php echo SITENAME;?>",
        "sameAs": "<?php echo $base;?>",
        "url": "<?php echo $base;?>",
        "email": "<?php echo SITEEMAIL;?>",
        "logo": "<?php echo $base;?>/images/logo.png",
        "image": "<?php echo $base;?>/images/family.png",
        "address": "<?php echo SITEADDRESS;?>",
        "description": "<?php echo $desc;?>"
      }  
    </script>
<?php
  require("pageincludes/footer.php");
?>
