<?php
# check tht page is not accessed directly and redirect if so
if (!isset($page)) {
    header("Location: /");
    exit;
}

$title = "Enduring Power of Attorney | " . SITENAME;
$desc = "An enduring power of attorney lets someone you trust manage your financial affairs if you lose capacity. Free with every will at My Will Online.";
$keywords = "enduring power of attorney";

require("pageincludes/header.php");
?>
      <section class="container narrow">
        <h1></a>Enduring Power of Attorney</h1>

        <p class="text-center"><strong><?php echo SITENAME;?> includes a FREE Enduring Power of Attorney with each Will</strong></p>

        <p>
          Appointing someone as your power of attorney gives them the legal authority to look after your affairs on your behalf.
        </p>
        <p>
          A power of attorney is a legal document made by one person, who is called the ‘principal’, that allows
          another person to do things with the principal’s money, bank accounts, shares, real estate and other
          assets. This can include spending and managing the principal’s money, buying or selling shares for
          the principal or buying, selling, leasing or mortgaging the principal’s house or other real estate
          investments. The person who does these things for the principal is called the ‘attorney’.
        </p>
        <p>
          The person appointed as attorney can be any person over the age of 18 years who is able to assist
          the principal with money or property – a relative, friend or professional adviser.
        </p>

        <div class="text-center">
          <p><a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-success">Get Your Free Enduring Power of Attorney</a><br>&nbsp;</p>
        </div>

        <h2>Enduring power of attorney</h2>
        <p>
          An enduring power of attorney, unlike a general power of attorney, is one which continues
          to operate after the principal has lost mental capacity. An enduring power of attorney is
          normally used if the principal wishes their attorney to make decisions for them in the long term.
          An enduring power of attorney can be useful because you may become unable to look after things
          for yourself at some stage in the future. This could be due to physical problems, loss of
          mental capacity or something unforeseen such as an accident.
        </p>

        <p>
          An General Power of Attorney is only legal while you have capacity. If you want to make sure
          your attorney can still operate if you lose capacity, you will need to appoint an
          Enduring Power of Attorney.
          An Enduring Power of Attorney will continue to have effect after you have lost your capacity
          to self-manage. This is important for everyone, but particularly for elderly people.
        </p>
        <p>
          A Power of Attorney does not relate to decisions about your lifestyle, medical treatment or welfare.
          Where you live, who you live with and what health care you receive are covered by Enduring Guardianship.
        </p>
        <p>
          Any Power of Attorney ceases when you die. The executor named in your Will then takes over the
          responsibility of administering your estate.
        </p>
        <p>
          An enduring power of attorney can be tailored to meet your circumstances. You can make special directions
          in the power of attorney document about what you want your attorney to be able to do or impose limits on
          what they can do.
        </p>
        <h3>When does an Enduring Power of Attorney become effective?</h3>
        <p>
          When you complete your enduring power of attorney form, you have choices about when your attorney can start
          to make your property and financial decisions. It could start immediately or it could start only when you have
          lost the mental capacity to make your own decisions. You decide. 
        </p>
        <h3>Do I have to register my enduring power of attorney?</h3>
        <p>
          You will only need to register your power of attorney if you intend to have your attorney make decisions 
          relating to real estate transactions. The power of attorney needs to be registered with the appropriate
          authority in your state.
        </p>
        <h3>Who can I appoint as my attorney?</h3>
        <p>
          Any person over the age of 18 years can act as your attorney. It can be a close family member or a friend
          who you trust. You should ask the person you want to appoint if they will agree to be your attorney and
          look after your money and property. Only appoint a person you can trust to look after your affairs.
        </p>
        <h3>Who needs to sign my power of attorney as a witness?</h3>
        <p>
          In order to be a valid Enduring Power of Attorney, your signing of the document must be witnessed by a 
          &ldquo;prescribed person&rdquo;. Prescribed persons include: barristers, solicitors, licensed conveyancers,
          registrars of the local court, and many other individuals. There are differences between states in who can
          witness the documents. Guidelines included with the enduring power of attorney documents produced by
            <?php echo SITENAME;?> will provide full details of valid witnesses.
        </p>
        <h3>What should I do with my enduring power of attorney when I have completed it?</h3>
        <p>
          Your original enduring power of attorney should be stored in a safe 
          place, possibly with other important documents you have made, such 
          as your enduring power of guardianship, advance health directive and 
          will. It is also highly recommended that you tell your attorney where 
          your original enduring power of attorney is stored, so they can access it 
          if required.
        </p>
        <h3>Related Information</h3>
        <ul>
          <li><a href="about-epog.html">Enduring Guardianship</a> — for personal and health care decisions</li>
          <li><a href="faq.html">Frequently Asked Questions</a> — common questions about wills and estate planning</li>
          <li><a href="how-to-make-a-will-online.html">How to make a will online</a> — step-by-step guide</li>
        </ul>
      </section>
<?php
  require("pageincludes/footer.php");
?>