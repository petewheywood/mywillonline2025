<?php
# check that page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$title = "Why you must have a will! · " . SITENAME;
$desc = "Dying without making a will, no matter how old you are, will leave your family and loved ones with unnecessary dramas and additional costs. Creating a valid will makes the distribution of your estate so much easier.";
$keywords = "why you need a last will and testament, why you need a will";

require("pageincludes/header.php");
?>
      <section class="container">
        <div class="row">
          <div class="col-sm-8 topmargin">
            <h1>Why you need a Last Will and Testament</h1>
            <p>
              Dying without making a will (dying intestate), no matter how old you are, will leave your family
              and loved ones with unnecessary dramas and additional costs.
            </p>
            <p>
              When a person who has a will dies, an application is made to the court in their
              state for a grant of probate. This means that the person's executor 
              (nominated in their will) applies to the court for permission to administer the deceased person's
              estate according to their wishes. The court, after verifying the authenticity
              of the will and the executor, will grant probate and the wishes written in the will
              can be followed.
            </p>
            <p>
              However, if the person who died did not have a will, an application is made
              for a court order that allows the person's estate to be administered in
              the absence of a will. The court will require proof of who the person's eligible
              relatives are. The person applying for the court order must provide appropriate
              documentation such as birth and marriage certificates, adoption certificates, and so on,
              to prove eligibility.
            </p>
            <p>
              If the deceased has eligible relatives, a state based formula is used
              to distribute their estate among those relatives. Eligible relatives include: spouses
              (and former spouses), children (and step children), parents, brothers and sisters,
              grandparents, aunts and uncles and first cousins. Generally, the order of distribution of 
              your estate is spouses, children, parents, brothers and sisters, grandparents, aunts and uncles, and
              lastly cousins.
            </p>
            <p>
              If the person has no will and no eligible relatives (or they cannot be found), the estate
              will pass to the crown.
            </p>
            <p>
              Complications often arise when the deceased has been in more than one relationship and/or
              have children to more than one partner. Also, if the deceased was in a de-facto relationship 
              before they died, the court may not recognise the de-facto partner as an eligible relative unless
              certain conditions of the relationship can be proved, and that person may receive 
              nothing from the estate.
            </p>
            <p>
              Whenever there has been a breakdown in family relationships, it's likely that administering
              the persons estate will be a difficult period for all concerned. The whole process is 
              greatly simplified when there is a valid will in the first place.
            </p>
            <div class="text-center">
              <br>
              <a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-success"><i class="glyphicon glyphicon-pencil"></i> Make Your Will</a><br>
              <br>
            </div>
          </div>
          <div class="col-sm-4 topmargin leftborder">
            <div class="sidebar">
              <img class="img-responsive" src="images/kylie.jpg" alt="Kylie died without a will">
              <h3>Estranged father receives $1 million</h3>
              <p>
                Kylie was 22 years of age when she died in a tragic accident. Kylie did not have a Will and her Estate
                received a $2M insurance payout due to her accidental death.
              </p>
              <p>
                Because she had not made a Will, Kylie’s estate was divided with 50% allocated to each of her parents.
                This was even though Kylie’s father deserted the family when Kylie was six months old and
                did not pay a cent of child support.
              </p>
              <p>
                The family claims there is no way Kylie would want her Dad to receive anything.
              </p>
              <p>
                If only Kylie had made her wishes known in a will.
              </p>
            </div>
            <div class="sidebar">
              <img class="img-responsive" src="images/james.jpg" alt="James died without a will">
              <h3>Devoted step-children lose their inheritance</h3>
              <p>
                James was born in New Zealand and immigrated to Australia where he moved to a Queensland mining town.
              </p>
              <p>
                James lived in a defacto relationship with his partner, Karen, for over twenty years and he helped
                raise Karen’s children from a previous relationship. These children lived with him until they were adults.
              </p>
              <p>
                Sadly, Karen died before James. In her Will she left him her estate.
                James continued the close relationship he shared with Karen’s children for many years.
              </p>
              <p>
                When James died without a Will, Karen’s now adult children did not receive any of his estate, including the
                family home where they had lived their entire lives.
                James’s estate was given to a brother living in New Zealand.
              </p>
              <p>
                If only James had made a Will outlining who he wanted to receive his estate.
              </p>
            </div>
          </div>
        </div>
      </section>
<?php
require("pageincludes/footer.php");
?>