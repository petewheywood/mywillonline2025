<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
    header("Location: /");
    exit;
}

$title = "Enduring Guardianship - " . SITENAME;
$desc = "An enduring guardianship is a document which gives someone you trust power over your personal and health care decisions if you are unable to do so yourself.";
$keywords = "enduring guardianship";

require("pageincludes/header.php");
?>
      <section class="container narrow">
        <h1></a>Enduring Guardianship</h1>
        <p>
          Appointing someone as your guardian gives them the legal authority to make personal and health care decisions on your behalf.
        </p>
        <p>
          An Enduring Guardianship relates to decisions about your lifestyle, medical treatment and welfare.
          Where you live, who you live with and what health care you receive are covered by an Enduring Guardianship.
        </p>
        <p>
          An Enduring Guardianship is referred to differently in the various states and territories of Australia:
        </p>
        <ul>
          <li><strong>Australian Capital Territory</strong> - the power to give another authority over both financial decisions and personal and health decisions is granted by completing the ACT <em>Enduring Power of Attorney</em>. There is only one document required.</li>
          <li><strong>New South Wales</strong> - the power to make personal decisions is granted by an Enduring Guardianship, and financial decisions by an <em>Enduring Power of Attorney</em>.</li>
          <li><strong>Northern Territory</strong> - the power to make personal and financial decisions is granted by completing a Northern Territory <em>Advance Personal Plan</em>. There is only one document required.</li>
          <li><strong>Queensland</strong> - the power to give another authority over both financial decisions and personal and health decisions is granted by completing the Queensland <em>Enduring Power of Attorney</em>. There is only one document required.</li>
          <li><strong>South Australia</strong> - the power to make personal decisions is granted by a South Australian <em>Advance Care Directive</em>, and financial decisions by an <em>Enduring Power of Attorney</em>.</li>
          <li><strong>Tasmania</strong> - the power to make personal decisions is granted by a Tasmanian <em>Instrument Appointing Enduring Guardians</em>, and financial decisions by a <em>General Enduring Power of Attorney</em>.</li>
          <li><strong>Victoria</strong> - the power to make personal and financial decisions is granted by completing a Victorian <em>Enduring Power of Attorney</em>. There is only one document required.</li>
          <li><strong>Western Australia</strong> - the power to make personal decisions is granted by a Western Australian <em>Enduring Power of Guardianship</em>, and financial decisions by an <em>Enduring Power of Attorney</em>.</li>
        </ul>              
        <div class="text-center">
          <p>
            <a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-success"><i class="glyphicon glyphicon-pencil"></i> Start Your Will and Enduring Guardianship</a>
          </p>  
        </div>
      </section>
<?php
  require("pageincludes/footer.php");
?>