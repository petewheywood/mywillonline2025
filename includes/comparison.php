<?php
$gst = (TAX == 0) ? '' : ' (including GST)';
#   '<a data-toggle="popover" data-trigger="hover" title="Telephone Support" data-content="<p>We provide telephone support for customers preparing their ' . $_SESSION['premiumwill'] . ' during Australian eastern states business hours (Mon-Fri 9AM to 5PM) on ' .  SITEPHONE . '.</p><p>Outside of these hours, or if we are busy speaking with other customers, please leave a message and we will return your call promptly.">Telephone support</a>' => [0, 1],

$keypoints = array(
  '<a data-toggle="popover" data-trigger="hover" title="SSL Encryption" data-content="<p>All communication between your web browser and our web server is encrypted using a Trustwave 256-bit SSL certificate.</p><p>Any information stored on the website is securely encrypted before storage.">Fully encrypted website</a>',
  '<a data-toggle="popover" data-trigger="hover" title="Secured Data" data-content="<p>All the data that you enter is securely encrypted in the database.">Secured data</a>' => [1,1,''],
  'Email support',
 '<a data-toggle="popover" data-trigger="hover" title="100% Satisfaction Guarantee" data-content="If you purchase a ' . $_SESSION['premiumwill'] . ' from ' . SITENAME . ' and find that it doesn&apos;t fit your needs, we&apos;ll fully refund your purchase cost. Just notify us, stating the reason, within ' . GUARANTEEDAYS . ' days of purchase.">100% Satisfaction Guarantee</a>',
 'Preview before payment',
 '<a data-toggle="popover" data-trigger="hover" title="Free Updates for Life" data-content="<p>You may login to ' . SITENAME . ' at anytime and update and re-download your Will for life. No further charges.</p><p>If you also purchased a <em>Mirror Will</em> for your partner, it can also be updated free for life.</p>">Free updates</a> for life',
  '<a data-toggle="popover" data-trigger="hover" title="Enduring Power of Attorney" data-content="<p>An <em>enduring power of attorney</em> is a legal agreement that  enables you to appoint a trusted person (or persons) to make financial and/or property decisions on your behalf if you become unable to do so.</p>">Enduring Power of Attorney</a>',
  '<a data-toggle="popover" data-trigger="hover" title="Enduring Guardianship" data-content="<p><p>An <em>enduring guardianship</em> is a legal agreement that  enables you to appoint a trusted person (or persons) to make personal and/or health care decisions on your behalf if you become unable to do so.</p>">Enduring Guardianship</a>',
  '<a data-toggle="popover" data-trigger="hover" title="Executor&apos;s Memorandum" data-content="<p>An <em>executor&apos;s memorandum</em> includes detail, such as the names and addresses of beneficiaries and guardians, personal information such as assets and liabilities, requirements, and personal messages to your beneficiaries.</p><p>With this information, your executor&apos;s job is made considerably easier.</p>">Executors Memo</a>',
  '<a data-toggle="popover" data-trigger="hover" title="Personal Requests" data-content="<p>You can include any personal requirements you may have, such as how your funeral should be conducted, whether you are buried or cremated, and so on.</p>">Personal requests</a>',
  '<a data-toggle="popover" data-trigger="hover" title="Pet Care Details" data-content="<p>If you have pets, you can provide information about their future care and who should care for them.</p><p>You can also leave a legacy to your chosen pet carer to be used for the pet\'s care and upkeep.</p>">Provision for your pets</a>',
  '<a data-toggle="popover" data-trigger="hover" title="Assets and Liabilities" data-content="<p>A section is included within the Executor&apos;s Memo to list your physical and virtual assets, liabilities and personal information that your Executor would need.</p>">Assets and Liabilities</a>',
  '<a data-toggle="popover" data-trigger="hover" title="Personal Messages" data-content="<p>A section is included within the Executor&apos;s Memo to allow you to leave personal messages to your loved ones.</p>">Personal Messages</a>',
  '<a data-toggle="popover" data-trigger="hover" title="Social Media Details" data-content="<p>There is provision to leave details about your social media accounts, such as Facebook and Instagram as well as details of your email and other internet logins.</p><p>The information you enter will be included in the Executor&apos;s Memo and is completely optional.</p>">Social media details</a>',
  '<a data-toggle="popover" data-trigger="hover" title="Mirror Will" data-content="<p>A <em>mirror will</em> is a will that is identical to yours, except that yours and your partner&apos;s names are swapped around.</p><p>If you need a Will for your partner also, then using a Mirror Will is faster and cheaper.</p>">Mirror Will</a> (Optional)',
  'Legal and valid across Australia',
  'Works on desktop or mobile',
  'Signing instructions included',
  'Download to your device',
);

for ($i = 0; $i < count($keypoints) - 1; $i += 2) {
?>
        <div class="row" style="margin-left: 30px;">
          <div class="col-sm-6">
            <div class="item">
              <span class="glyphicon glyphicon-ok" style="padding-right: 15px;"></span><?php echo $keypoints[$i]; ?> 
            </div>
          </div>
          <div class="col-sm-6">
            <div class="item">
              <span class="glyphicon glyphicon-ok" style="padding-right: 15px;"></span><?php echo $keypoints[$i + 1]; ?> 
            </div>
          </div>
        </div>
<?php
}
