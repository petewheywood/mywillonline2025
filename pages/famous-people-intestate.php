<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}
# setup page data
$title = "Famous people who died without a will";
$desc = "There are plenty of examples of well known people who died without having a will. Here are several, with some information on their situation and the problems that not having a will had caused for their families.";
$keywords = "dying without a will, intestate, famous people who died intestate, well know celebrities who died without a will.";

# other vars
$bobmarleydeath = yearsDifference("1981-05-11", date("Y-m-d"));
$princedeath = yearsDifference("2016-04-27", date("Y-m-d"));
$amywinehousedeath = yearsDifference("2011-07-23", date("Y-m-d"));
$kurtcobaindeath = yearsDifference("1994-04-05", date("Y-m-d"));
$rikmayalldeath = yearsDifference("2014-06-09", date("Y-m-d"));
$jimihendrixdeath = yearsDifference("1970-09-18", date("Y-m-d"));
$barrywhitedeath = yearsDifference("2003-07-04", date("Y-m-d"));
$howardhughesdeath = yearsDifference("1976-04-05", date("Y-m-d"));

require("pageincludes/header.php");

?>
      <section class="container">
        <h1>Famous People Who All Died Without Leaving a Will</h1>
        
        <p>
          Dying without a will, or dying &ldquo;intestate&rdquo;, is more common than you may think. There are plenty of examples of well known people
          who died without leaving a will. Some of them were extremely wealthy when they died, and because they didn't have a will, their 
          surviving families often had extensive and expensive legal processes to battle through to get the persons estate sorted out. Here are some examples:
        </p>
        
        
        <div class="row>">
          <div class="col-sm-6">
            <h2>Bob Marley</h2>
            <p><img src="images/bobmarley.jpg" alt="Bob Marley"></p>
            <p>
              <a href="https://en.wikipedia.org/wiki/Bob_Marley" rel="nofollow"  rel="nofollow"  title="Bob Marley on Wikipedia" target="t1">Bob Marley</a> died <?php echo $bobmarleydeath;?> 
              from melanoma at the age of 36 years. He left behind many children, including 3 from his wife Alpharita Constantia "Rita" Anderson. 
              Even though he knew he would die from his cancer, he didn't make a will. He was worth an estimated $30 million, 
              and his death kicked off a number of complex lawsuits that are still going on today.
            </p>

            <h2>Prince</h2>
            <p><img src="images/prince.jpg" alt="Prince"></p>
            <p>
              Prince died <?php echo $princedeath;?> at the age of 57. He died with no partner, children or any surviving parents. His full sister, Tyka Nelson,
              <a href="http://money.cnn.com/2016/04/26/news/companies/prince-no-will/index.html" target="t2">filed documents
              to begin probate preceedings shortly after his death</a>. The documents stated that he died without a will.
              At least 700 people have claimed to be Prince's half-siblings and, if true, they would have a claim on his estate.
            </p>
            <p>
              Prince was worth about $300 million according to several estimates, including one by <a href="https://www.celebritynetworth.com/richest-celebrities/singers/prince-net-worth/" rel="nofollow"  rel="nofollow"  title="Prince's net worth" target="t2">Celebrity Net Worth</a>. His estate will only increase in value
              as royalties from his music continue to accrue.
            </p>
  
            <h2>Amy Winehouse</h2>
            <p><img src="images/amywinehouse.jpg" alt="Amy Winehouse"></p>
            <p>
              Amy Winehouse tragically died <?php echo $amywinehousedeath;?> at the age of 27 from alcohol poisoning. 
            </p>
            <p>
              <a href="https://www.forbes.com/sites/trialandheirs/2012/03/28/amy-winehouse-didnt-have-a-will-after-all-but-did-have-millions/" rel="nofollow"  rel="nofollow"  title="Amy Winehouse didn't have a will" target="t3">Forbes has mentioned that Amy died intestate</a> (without a will) and that her personal estate was listed at about $6.7 million US dollars. 
            </p>
  
            <h2>Martin Luther King Jr.</h2>
            <p><img src="images/martinlutherking.jpg" alt="Martin Luther King"></p>
            <p>
              Martin Luther King was assassinated in Memphis, Tennessee in 1968. King's estate was left to his four children and his wife, Coretta Scott King. She died in 2006 and their eldest daughter, Yolanda died in 2007.
            </p>
            <p>
              The three remaining children have been involved in a series of disputes over their late father’s estate for the past 10 years. The <a href="http://www.sbs.com.au/news/article/2014/02/07/martin-luther-kings-kids-estate-battle" rel="nofollow"  rel="nofollow"  title="Martin Luther King's legacy dispute" target="t4">most recent dispute</a> involving his Nobel Peace Prize medal and the bible that he carried.
            </p>
  
            <h2>Abraham Lincoln</h2>
            <p><img src="images/abrahamlincoln.jpg" alt="Abraham Lincoln"></p>
            <p>
              Despite being the 16th president of the United States of America, it is surprising to know that
              <a href="https://www.forbes.com/sites/trialandheirs/2012/12/04/are-you-better-prepared-than-abraham-lincoln-was/" rel="nofollow"  title="Are You Better Prepared Than Abraham Lincoln Was?" target="t5">Abraham Lincoln never made a will before he died.</a>
              2 years after his assassination in 1865, his estate, valued at about $USD85,000 (worth a few million in today's dollars) was
              equally divided three ways among his wife, Mary Todd Lincoln, and his two sons who were living at that time<, Robert and Thomas.
            </p>
  
            <h2>Kurt Cobain</h2>
            <p><img src="images/kurtcobain.jpg" alt="Kurt Cobain"></p>
            <p>
              Over <?php echo $kurtcobaindeath;?> the lead singer from the band Nirvana, Kurt Cobain, committed suicide. 
              He was only 27 at the time.  He did write a detailed suicide note, but he didn't write a will.
            </p>
            <p>
              Courtney Love was Kurt Cobain's wife at the time of his death, and he had a young daughter, Frances Bean Cobain. 
              In 2009 Love lost parental custody of Frances Bean, who was then aged 17. In 2010 the rights to Kurt Cobain's name,
              likeness and appearance were taken from Love and given to Frances Bean.
            </p>
            <p>
              There were many other disputes over Kurt Cobain's legacy between Courtney Love and former Nirvana band members. Much money was
              wasted and legal battles were ongoing. If Kurt had had a will, his wishes for his daughter, his former band members and his wife
              would have been clear. <a href="https://heirsandsuccesses.com/2015/10/22/kurt-cobain-and-the-perils-of-intestacy/" rel="nofollow"  title="Kurt Cobain's intestacy" target="t6">Kurt Cobain and the perils of intestacy.</a>
            </p>
            <br>  
          </div>          
  
          <div class="col-sm-6">
            <h2>Rik Mayall</h2>
            <p><img src="images/rikmayall.jpg" alt="Rik Mayall"></p>
            <p>
              <a href="https://en.wikipedia.org/wiki/Rik_Mayall" rel="nofollow"  title="Rik Mayall" target="t7">Rik Mayall</a>, a well known british comedien and actor, died of a heart attack <?php echo $rikmayalldeath;?> at the age of 56.
            </p>
            <p>
              <a href="http://www.mirror.co.uk/3am/celebrity-news/rik-mayalls-family-face-huge-5556336" rel="nofollow"  title="Rik Mayall's family face huge tax bill" target="t8">He left no will</a> which meant his wife and 3 adult children faced a huge tax bill on his estimated £1.2 million estate.
            </p>
    
            <h2>Jimi Hendrix</h2>
            <p><img src="images/jimihendrix.jpg" alt="Jimi Hendrix"><p>
            <p>
              Jimi Hendrix died at the age of 27, <?php echo $jimihendrixdeath;?> without a will. His father inherited all of his estate. When Jimi's father, Al Hendrix died in 2002, he left Jimi's estate to his adopted daughter instead of to his biological son Leon, who was Jimi's brother.
            </p>
    
            <h2>Billie Holiday</h2>
            <p><img src="images/billieholiday.jpg" alt="Billie Holiday"></p>
            <p>
              <a href="https://en.wikipedia.org/wiki/Billie_Holiday" rel="nofollow"  title="Billie Holiday" target="t9">Billie Holiday</a>, the legendary American jazz singer, had just $750 on her when she died in 1959 at the age of 44.
            </p>
            <p>
              Louis McKay, Billie's 4th husband, inherited her entire estate because she died without a will.
              The estate was not worth much at the time of her death, but over time, the royalties from her music were substantial. 
            </p>

            <h2>Barry White</h2>
            <p><img src="images/barrywhite.jpg" alt="Barry White"></p>
            <p>
              <a href="https://en.wikipedia.org/wiki/Barry_White" rel="nofollow"  title="Barry White" target="t10">Barry White</a> died <?php echo $barrywhitedeath;?> from complications associated with kidney failure. He died without a will.
            </p>
            <p>
              The famous soul singer's ex-wives, girlfriends and children were left to squabble over his multi-million dollar estate.
            </p>

            <h2>Howard Hughes</h2>
            <p><img src="images/howardhughes.jpg" alt="Howard Hughes"></p>
            <p>
              <a href="https://en.wikipedia.org/wiki/Howard_Hughes#Death" rel="nofollow"  title="Howard Hughes" target="t11">After Howard Hughes died <?php echo $howardhughesdeath;?>, a handwritten will came to light</a> that was being held by an official of the Church of Jesus Christ of Latter-day Saints
              in Salt Lake City.  This will left over $USD1.5 billion to several charities; $USD470 million to executives of Hughes' companies and his aides,
              $156 million to a first cousin, $156 million split between his two ex-wives, and $156 million to a gas-station owner named Melvin Dummar.
            </p>
            <p>
              As it turned out, Dummar claimed that he had met Hughes at his gas station in 1967, had driven the billionaire to Las Vegas and that several days later an anonymous
              man appeared at the gas station and gave a document, the will, to Dummar to keep safe. Dummar then gave the will to the church official.
            </p>
            <p>
              In 1978 a trial, held in Las Vegas, determined that the Will was a forgery.
            </p>

            <h2>Stieg Larsson</h2>
            <p><img src="images/stieglarsson.jpg" alt="Stieg Larsson"></p>
            <p>
              The Swedish author, who wrote the trilogy of books, starting with The Girl with the Dragon Tattoo, died of a heart attack in 2004.
              This was one year before his first novel was published and the series became a worldwide best seller. The 3 novels also became successful Swedish language movies that were later re-made as Hollywood movies.
            </p>
            <p>  
              His father and brother inherited his whole estate but his long-time partner Eva Gabrielsson received nothing because they weren't married and he did not have a will.
            </p>
          </div>
        </div>
        
      </section>
      <div class="text-center">
        <br>
        <a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-success"><i class="glyphicon glyphicon-pencil"></i> Make A Will</a><br>
        <br>
      </div>
      
<?php
  require("pageincludes/footer.php");
?>      