<?php
# check tht page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$title = "Terms of Use | " . SITENAME;
$desc = "Terms of use and terms of service for " . SITENAME . ".";
$keywords = "last will and testament, online will, online wills, internet wills, online will kit";

require("pageincludes/header.php");
?>
      <section class="container">
        <a name="termsofuse"></a><h1>Terms of Use</h1>
        <p>
          This web site will produce a valid legal will for use in Australian states and territories. The Wills we create are
          suitable for the majority of individuals. However, if you have a complex situation (for example family trusts, overseas property or 
          complex private company structures in place) we strongly encourage you to seek professional legal advice.
        </p>
        <p>
          The information provided on this website is not a substitute for legal advice. If you have any questions relating to
          details of your estate administration, we suggest that you request the advice of a solicitor.
        </p>
        <p>
          <?php echo $_SESSION['sitename']; ?> has made every effort to ensure the information in the Wills is consistent with the laws of each
          State and Territory of Australia as at the time of purchase; however, those laws may change after the date of purchase of your will.
          <?php echo $_SESSION['sitename']; ?> accepts no responsibility for the manner in which you complete your Will,
          nor whether your Will is appropriate for your circumstances.
        </p>
        <p>
          We are not lawyers and do not offer legal opinion or advice. Our Wills are created automatically using standard clauses merged with
          the information that you provide within the online forms on this website. Our final documents have been reviewed by legal 
          professionals, but we make no guarantees that your Will is un-contestable.
        </p>
        <p>
          In using this website, you acknowledge that you are not receiving legal, financial or tax advice from <?php echo $_SESSION['sitename']; ?>.
        </p>
        <p>
          <strong>Refund Policy:</strong> If you purchase a Will from <?php echo $_SESSION['sitename']; ?> and then find that it doesn't adequately meet your needs,
          we'll fully refund your purchase cost if you notify us stating the reason, within <?php echo GUARANTEEDAYS;?> days of purchase.
        </p>
        <p>
          <strong>Data Encryption:</strong> The <?php echo $_SESSION['sitename']; ?> website uses SSL encryption to ensure that all information entered is secure.
          All payments are processed via Paypal Australia who also use SSL encryption to keep your payment details secure.
        </p>
        <p>
          <strong>Dispatch and Delivery:</strong> Please note that <?php echo $_SESSION['sitename']; ?> does not physically send completed documents. Once payment for your order is confirmed,
          the documents can be downloaded to your computer (or other device) immediately.
          Once downloaded, you would need to print a copy and have them signed.
        </p>
        <p>
          <strong>Physical Location:</strong> <?php echo $_SESSION['sitename']; ?> is an Australian owned and operated business, with the head office located in <?php echo $_SESSION['siteaddress']; ?>.
        </p>
        <p>
          <strong>Unlimited updates for <?php echo PRODUCTEXPIRY < 99 ? PRODUCTEXPIRY . ' months' : 'life'; ?>.</strong> Once you have purchased a Will from <?php echo $_SESSION['sitename']; ?>, you'll have full access to modify and recreate your Will for 
          <?php echo PRODUCTEXPIRY < 99 ? PRODUCTEXPIRY . " months. After that time, you'll need to pay again for your Will" : 'life'; ?>.
        </p>
        <hr>
        <h2>Terms of Service</h2>
        <p><strong>Use of this website requires that you read and agree to the following numbered points. If you do not agree to any of these points, then you must not use this website.</strong></p>
    
        <ol>
          <li>I understand and agree that <?php echo SITENAME;?> is not a legal
          firm or a legal professional, and may not perform services normally
          performed by a legal professional, and is not a substitute for the advice
          or services of a legal professional. Rather, I am representing myself in
          this legal matter. No lawyer-client relationship or privilege is created
          with <?php echo SITENAME;?>.</li>
    
          <li>If, prior to my purchase, I believe that <?php echo SITENAME;?> gave
          me any legal advice, opinion or recommendation about my legal rights,
          remedies, options, selection of forms or strategies, I will not proceed
          with this purchase, and any purchase that I do make will be null and
          void.</li>
    
          <li>Limitation of liability and indemnification. Except as prohibited by
          law, I will hold <?php echo SITENAME;?> and its officers, directors,
          employees, and agents harmless for any indirect, punitive, special,
          incidental, or consequential damage, however it arises (including legal
          fees and all related costs and expenses of litigation and arbitration, or
          at trial or on appeal, if any, whether or not litigation or arbitration
          is instituted), whether in an action of contract, negligence, or other
          tortious action, or arising out of or in connection with this agreement,
          including without limitation any claim for personal injury or property
          damage, arising from this agreement and any violation by me of any
          federal, state, or local laws, statutes, rules, or regulations, even if
          <?php echo SITENAME;?> has been previously advised of the possibility of
          such damage. Except as prohibited by law, if there is liability found on
          the part of <?php echo SITENAME;?>, it will be limited to the amount paid
          for the products and/or services and under no circumstances will there be
          consequential or punitive damages.</li>
    
          <li>Terms of Use. I understand that the Site's general terms of use (the
          "Terms of Use") also apply to these Terms of Service and in agreeing to
          these Terms of Service, I acknowledge that I have read and agree to those
          <a href="#termsofuse">Terms of Use</a>, which are incorporated herein.
          </li>
    
          <li>Future Products and Services. If I choose to add a product or service
          to my order subsequent to this initial purchase, these Terms of Service
          will apply to that additional product or service purchase as well.</li>
    
          <li>Suspended Accounts. If <?php echo SITENAME;?> encounters evidence of
          suspicious activity in connection with my account, including, but not
          limited to, evidence that my account is being used by someone who is not
          authorized to do so, I acknowledge that <?php echo SITENAME;?>, in its
          sole discretion, may opt to temporarily disable my account for a
          reasonable amount of time in order to investigate. In the event that
          <?php echo SITENAME;?> disables my account, I understand that, absent a
          subpoena or court order, no information about my account will be provided
          to anyone outside <?php echo SITENAME;?>, including me or any authorized
          contact, until the investigation is complete. Additionally, I understand
          that <?php echo SITENAME;?>, in its sole discretion, may decide not to
          send any documents associated with my account to me or file any such
          documents with any government authority, while my account is disabled. I
          acknowledge that <?php echo SITENAME;?> will not be liable for any delays
          caused by these policies and procedures.</li>
    
          <li>Access to World Wide Web; Internet Delays. To use
          <?php echo SITENAME;?> services, I must obtain access to the World Wide
          Web, either directly or through devices that access web-based content,
          and pay any service fees associated with such access. I am responsible
          for providing all equipment necessary to make such connection to the
          World Wide Web, including a computer and Internet access. Access to
          certain <?php echo SITENAME;?> services may be limited or delayed based
          on problems inherent in the use of Internet and electronic
          communications. I understand that <?php echo SITENAME;?> is not
          responsible for delays, delivery failures, or other damage resulting from
          such problems.</li>
    
          <li>Right to refuse. I acknowledge that <?php echo SITENAME;?> reserves
          the right to refuse service to anyone.</li>
    
          <li>I acknowledge that I will have the opportunity to review a preview
          of my will created at <?php echo SITENAME;?> and may contact
          <?php echo SITENAME;?> customer service via the contact page with
          questions or for assistance.</li>
    
          <li>I understand that these terms affect my legal rights and obligations.
          If I do not agree to be bound by all of these terms, I will not use this
          service. By proceeding with my purchase, I agree to these Terms of
          Service.</li>
          
        </ol>
      </section>
<?php
require("pageincludes/footer.php");
?>