<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
    header("Location: /");
    exit;
}

$base = url_origin();

$_SESSION['lastPage'] = basename(__FILE__);

// get customer feedback stats
$query = "SELECT count(*) as reviews, avg(easeofuse) as easeofuse, avg(valueformoney) as valueformoney, avg(wouldrecommend) as wouldrecommend FROM publishfeedback;";
$result = $dbh->query($query);
$ratings = $result->fetch();
$numreviews = $ratings['reviews'];
$avg_easeofuse = round($ratings['easeofuse'], 1);
$avg_valueformoney = round($ratings['valueformoney'], 1);
$avg_wouldrecommend = round($ratings['wouldrecommend'], 1);
$aggregaterating = round(($avg_easeofuse + $avg_valueformoney + $avg_wouldrecommend)/3, 1);

$keywords = "free wills, wills, free will kit, last will and testament, online will, online wills, internet wills, online will kit";
$desc = 'We make it so easy to make your will online. Our customers rate us ' . $aggregaterating . ' from ' . $numreviews . ' reviews · Free Will available · Free updates for life · 100% Satisfaction guarantee · Free Enduring Power of Attorney · Provision for your pets . Executors Memorandum';
$title = "How to make a will online";

require("pageincludes/header.php");

?>
    <section class="hero" style="max-height: 130px;">
      <div class="caption" style="padding-top: 20px;">
        <h1>How to make a will online</h1>
      </div>
      <img src="images/hero.jpg" class="img-responsive" alt="Importance of a Will">
    </section>

    <section class="container-fluid" id="validwill">    
      <div class="container narrow">
        <h2 class="text-center">How To Make A Will</h2>
        <p>
          Making a will (your Last Will and Testament) is not as difficult as you may think. But you've probably put it off because you believed you'd need a lawyer to write it and would have to pay a minimum of $500 to get started. It doesn't have to be that way.
        </p>
        <p>
          Nevertheless, there are a few important factors that must be considered for a will to be valid and legally binding in Australia. They are:
        </p>
        <ul class="special check">
          <li>
            The person making the Will must be at least 18 years of age
          </li>
          <li>
            The person must have a full intention to make the Will
          </li>
          <li>
            The Will must be made voluntarily without any sort of coercion or pressure from another person or persons
          </li>
          <li>
            The person must understand the contents of the Will, and approve of what is written
          </li>
          <li>
            Two witnesses (who are over 18 and who are not mentioned as a beneficiary in the Will and not the partner of a beneficiary) must sign, and acknowledge the Will in the presence of the person making the will. They must also see the Will maker sign the Will.
          </li>          
        </ul>
        <p>
          If all the above are true, then the will is legal in Australia.
        </p>

        <br />
        <p class="text-center">
         	<a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-success">Make Your Will</a>
        </p>

      </div>  
    </section>

    <section class="container-fluid">  
      <div class="container narrow">
        <h3 class="text-center">Making a Will at <?php echo SITENAME;?> - Australia's easiest to use Will Kit</h3>
        <p>
          Our customers tell us that our online will creation process is really easy to use and really great value for money. But please don't take our word for it, read their reviews:
          <a href="ourcustomers.html" title="Customers Reviews" target="reviews">Overall Customer Rating <span itemprop="ratingValue"><?php echo $aggregaterating;?></span>
          out of 5 from <span itemprop="reviewCount"><?php echo $numreviews;?></span> customers reviews.</a>
        </p>
        <p>
        	The process is really very simple:
        </p>
        <ul class="special edit">
          <li>
            First, create an account by registering (you can click the Get Started button below)
          </li>
          <li>
            Then enter your personal information: full name, address, whether you have a partner, children, pets...
          </li>
          <li>
            Enter details about your beneficiaries (those who you wish to receive something in your Will)
          </li>
          <li>
            Complete the details for who will be your Executor or Executors - the person who will read and act on your will after your death
          </li>
          <li>
            Then the important part is to allocate your remaining property (estate) to your beneficiaries.
          </li>
          <li>
            There are some other sections that will appear based upon information you previously entered, such as pet details and guardian details.
          </li>
          <li>
            Optional sections: Personal messages to beneficiaries, assets and liabilities, personal details...
          </li>      
        </ul>
        <p>
        	<em>You can preview your will at any time during the entry of your information to see how it will look as a formatted document.</em>
        </p>
        <ul class="special check">
          <li>
            You may optionally include our Free Enduring Power of Attorney and Free Enduring Guardianship (sometimes called Advanced Health Care Directive) documents with your Will. These will appear in the downloaded document package once payment is complete.
          </li>
          <li>
            Then go ahead and Purchase you Will. We charge <strong><?php echo currency($_SESSION['products']["singlewill"]["priceinctax"]);?></strong> for a will for a single person. If you want to include a will for your partner (mirror will) you will pay <strong><?php echo currency($_SESSION['products']["mirrorwill"]["priceinctax"]);?></strong> extra.
          </li>
          <li>
            After a successful payment, you can download your Will document package directly to your computer or device. 
          </li>
          <li>
            Once you have read through the documents and are happy with everything, you need to sign and date the document in the presence of two witnesses who must also sign where required. 
          </li>
          <li>
            Make as many copies of the signed Will as required, but make sure you keep the original signed copy somewhere safe. It is the original copy that will be needed after your death so your executor can request what is called a 'grant of probate' so that your remaining estate can be distributed to beneficiaries.
          </li>
        </ul>
        
        <br />
        <p class="text-center">
        	<a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-success">Make Your Will</a>
        </p>
        
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