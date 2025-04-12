<?php
$gst = (TAX == 0) ? '' : ' (including GST)';
#   '<a data-toggle="popover" data-trigger="hover" title="Telephone Support" data-content="<p>We provide telephone support for customers preparing their ' . $_SESSION['premiumwill'] . ' during Australian eastern states business hours (Mon-Fri 9AM to 5PM) on ' .  SITEPHONE . '.</p><p>Outside of these hours, or if we are busy speaking with other customers, please leave a message and we will return your call promptly.">Telephone support</a>' => [0, 1],

$keypoints = array(
  'Price' . $gst => ['<b>' . ($_SESSION['products']['quickwill']['priceinctax'] > 0 ? currency($_SESSION['products']["quickwill"]["priceinctax"]) : 'FREE <small>(For a limited time)</small>') . '</b>', '<b>' . ($_SESSION['products']['singlewill']['priceinctax'] > 0 ? currency($_SESSION['products']["singlewill"]["priceinctax"]) : 'FREE <small>(For a limited time)</small>') . '</b>', '<b>FREE</b>'],
  'Legal and valid across Australia' => [1,1,1],
  'Immediately emailed to you' => ['','',1],
  'Preview before payment' => [1,1,''],
  'Works on desktop or mobile' => [1,1,''],
  'Signing instructions included' => [1,1,''],
  'Download to your device' => [1, 1, ''],
  '<a data-toggle="popover" data-trigger="hover" title="SSL Encryption" data-content="<p>All communication between your web browser and our web server is encrypted using a Trustwave 256-bit SSL certificate.</p><p>Any information stored on the website is securely encrypted before storage.">Fully encrypted website</a>' => [1,1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Secured Data" data-content="<p>All the data that you enter is securely encrypted in the database.">Secured data</a>' => [1,1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Free Updates for Life" data-content="<p>You may login to ' . SITENAME . ' at anytime and update and re-download your Will for life. No further charges.</p><p>If you also purchased a <em>Mirror Will</em> for your partner, it can also be updated free for life.</p>">Free updates</a> for life' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="100% Satisfied Guarantee" data-content="If you purchase a ' . $_SESSION['premiumwill'] . ' from ' . SITENAME . ' and find that it doesn&apos;t fit your needs, we&apos;ll fully refund your purchase cost. Just notify us, stating the reason, within ' . GUARANTEEDAYS . ' days of purchase.">100% Satisfied Guarantee</a>' => ['',1,''],
  'Email support' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Enduring Power of Attorney" data-content="<p>An <em>enduring power of attorney</em> is a legal agreement that  enables you to appoint a trusted person (or persons) to make financial and/or property decisions on your behalf if you become unable to do so.</p>">Enduring Power of Attorney</a>' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Enduring Guardianship" data-content="<p><p>An <em>enduring guardianship</em> is a legal agreement that  enables you to appoint a trusted person (or persons) to make personal and/or health care decisions on your behalf if you become unable to do so.</p>">Enduring Guardianship</a>' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Executor&apos;s Memorandum" data-content="<p>An <em>executor&apos;s memorandum</em> includes detail, such as the names and addresses of beneficiaries and guardians, personal information such as assets and liabilities, requirements, and personal messages to your beneficiaries.</p><p>With this information, your executor&apos;s job is made considerably easier.</p>">Executors Memo</a>' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Personal Requests" data-content="<p>You can include any personal requirements you may have, such as how your funeral should be conducted, whether you are buried or cremated, and so on.</p>">Personal requests</a>' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Pet Care Details" data-content="<p>If you have pets, you can provide information about their future care and who should care for them.</p><p>You can also leave a legacy to your chosen pet carer to be used for the pet\'s care and upkeep.</p>">Provision for your pets</a>' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Assets and Liabilities" data-content="<p>A section is included within the Executor&apos;s Memo to list your physical and virtual assets, liabilities and personal information that your Executor would need.</p>">Assets and Liabilities</a>' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Personal Messages" data-content="<p>A section is included within the Executor&apos;s Memo to allow you to leave personal messages to your loved ones.</p>">Personal Messages</a>' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Social Media Details" data-content="<p>There is provision to leave details about your social media accounts, such as Facebook and Instagram as well as details of your email and other internet logins.</p><p>The information you enter will be included in the Executor&apos;s Memo and is completely optional.</p>">Social media details</a>' => ['',1,''],
  '<a data-toggle="popover" data-trigger="hover" title="Mirror Will" data-content="<p>A <em>mirror will</em> is a will that is identical to yours, except that yours and your partner&apos;s names are swapped around.</p><p>If you need a Will for your partner also, then using a Mirror Will is faster and cheaper.</p>">Mirror Will</a> (Optional)' => ['','<b>' . ($_SESSION['products']['mirrorwill']['priceinctax'] > 0 ? currency($_SESSION['products']["mirrorwill"]["priceinctax"]) : 'FREE <small>(For a limited time)</small>') . '</b>',''],
);

?>
        <div id="comparison">
          <div class="row head">
            <div class="<?php echo QUICKWILL && $_SESSION['quickwill'] ? 'col-xs-3 col-sm-3' : 'col-xs-6 col-sm-6';?>">
              <div class="item"></div>
            </div>
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <h2>Free Will</h2>
              </div>
            </div>
<?php if (QUICKWILL && $_SESSION['quickwill']) :?>
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <h2>Quick Will</h2>
              </div>
            </div>
<?php endif;?>
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <h2>Premium Will</h2>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="<?php echo QUICKWILL && $_SESSION['quickwill'] ? 'col-xs-3 col-sm-3' : 'col-xs-6 col-sm-6';?>">
              <div class="item">
                <h2>Get Started?</h2>
              </div>
            </div>
            <div class="col-xs-3 col-sm-3">
              <div class="item">
              <button class="btn btn-default freewill">OK</button>
              </div>
            </div>
<?php if (QUICKWILL && $_SESSION['quickwill']) :?>            
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <a href="quickwill.html" class="btn btn-info btn-sm">OK</a>
              </div>
            </div>
<?php endif;?>
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-primary btn-sm">OK</a>
              </div>
            </div>
          </div>

<?php
foreach ($keypoints as $point => $ticks) {
?>
          <div class="row">
            <div class="<?php echo QUICKWILL && $_SESSION['quickwill'] ? 'col-xs-3 col-sm-3' : 'col-xs-6 col-sm-6';?>">
              <div class="item">
                <?php echo $point;?> 
              </div>
            </div>
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <?php echo !is_int($ticks[2]) ? $ticks[2] : ('<span class="glyphicon ' . ($ticks[2] ? 'glyphicon-ok' : 'glyphicon-remove') . '"></span>');?> 
              </div>
            </div>
<?php if (QUICKWILL && $_SESSION['quickwill']) :?>            
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <?php echo !is_int($ticks[0]) ? $ticks[0] : ('<span class="glyphicon ' . ($ticks[0] ? 'glyphicon-ok' : 'glyphicon-remove') . '"></span>');?> 
              </div>
            </div>
<?php endif;?>
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <?php echo !is_int($ticks[1]) ? $ticks[1] : ('<span class="glyphicon ' . ($ticks[1] ? 'glyphicon-ok' : 'glyphicon-remove') . '"></span>');?> 
              </div>
            </div>
          </div>
<?php
}
?>
          <div class="row foot">
            <div class="<?php echo QUICKWILL && $_SESSION['quickwill'] ? 'col-xs-3 col-sm-3' : 'col-xs-6 col-sm-6';?>">
              <div class="item"></div>
            </div>
            <div class="col-xs-3 col-sm-3">
              <div class="item">
              <button class="btn btn-default freewill">OK</button>
              </div>
            </div>
<?php if (QUICKWILL && $_SESSION['quickwill']) :?>            
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <a href="quickwill.html" class="btn btn-info btn-sm">OK</a>
              </div>
            </div>
<?php endif;?>
            <div class="col-xs-3 col-sm-3">
              <div class="item">
                <a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-primary btn-sm">OK</a>
              </div>
            </div>
          </div>
        </div>
