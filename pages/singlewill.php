<?php
/***************************************
 *   Title:    Online Will form
 *
 *  Description: This is the main information capture file for the online wills
 *               It is included by index.php
 *
 *  Author:    Pete Heywood
 *  Email:    peteheywood@me.com
 *
 *  Created:  16/05/2013
 *  Updated:  
 *
 *  Copyright Ⓒ  2013
 *
 *
 ***************************************/

if (!isset($_SESSION['userid'])) goHome(); # go to home page if not logged in

# keep track of which file was used in index.php
$_SESSION['lastPage'] = basename(__FILE__);

# retrieve the orderdata from the session var
$order = isset($_SESSION['orderData']) ? $_SESSION['orderData'] : '';

# if the user is logged on but never been to this page, prepopulate firstname and surname from register page - all the data is stored in SESSION vars
if (!$order['person']['firstname'] && !isset($_SESSION['ordercount']['singlewill'])) {
  $order['person']['othername'] = isset($_SESSION['othername']) ? $_SESSION['othername'] : ''; #default to the logged on user to fill in the form fields below
  $order['person']['firstname'] = isset($_SESSION['firstname']) ? $_SESSION['firstname'] : ''; #default to the logged on user to fill in the form fields below
  $order['person']['surname'] = isset($_SESSION['surname']) ? $_SESSION['surname'] : '';
  $order['person']['email'] = isset($_SESSION['email']) ? $_SESSION['email'] : '';
  $order['person']['address1'] = isset($_SESSION['address1']) ? $_SESSION['address1'] : '';
  $order['person']['address2'] = isset($_SESSION['address2']) ? $_SESSION['address2'] : '';
  $order['person']['city'] = isset($_SESSION['city']) ? $_SESSION['city'] : '';
  $order['person']['state'] = isset($_SESSION['state']) ? $_SESSION['state'] : '';
  $order['person']['postcode'] = isset($_SESSION['postcode']) ? $_SESSION['postcode'] : '';
  $order['person']['phone'] = isset($_SESSION['phone']) ? $_SESSION['phone'] : '';
  $order['person']['dob'] = isset($_SESSION['dob']) ? $_SESSION['dob'] : '';
}

# if this is a new order and they were were introduced, and referrer is an organisation, then auto add the first group beneficiary as the organisation
$isOrg = false;
if (isset($_SESSION['introcode'])) {
  # get the details of the referrer
  $query = "
    SELECT u.id, u.emailaddress, u.address1, u.address2, u.city, u.state, u.postcode, u.phone, u.flags, o.name, o.abn
    FROM users u LEFT JOIN organisations o ON (o.userid = u.id) WHERE u.affiliatecode = '{$_SESSION['introcode']}';
  ";
  $result = $dbh->query($query);
  $row = $result->fetch(PDO::FETCH_ASSOC);
  if ($row['flags'] & FLAG_BENEFICIARY) {
    $isOrg = true;
    # add in the organisation as the first group beneficiary
    $order['group'][1]['fullname'] = $row['name'] . ' (ABN: ' . $row['abn'] . ')';
    $order['group'][1]['email'] = $row['emailaddress'];
    $order['group'][1]['phone'] = $row['phone'];
    $order['group'][1]['address1'] = $row['address1'];
    $order['group'][1]['address2'] = $row['address2'];
    $order['group'][1]['city'] = $row['city'];
    $order['group'][1]['state'] = $row['state'];
    $order['group'][1]['postcode'] = $row['postcode'];
    $order['group'][1]['country'] = isset($row['country']) ? $row['country'] : 'Australia';
  }
}

# make sure none of the 0 items are set in the arrays
if (isset($order['child']) && is_array($order['child'])) unset($order['child'][0]);
if (isset($order['other']) && is_array($order['other'])) unset($order['other'][0]);
if (isset($order['group']) && is_array($order['group'])) unset($order['group'][0]);
if (isset($order['pet']) && is_array($order['pet'])) unset($order['pet'][0]);
if (isset($order['executor']) && is_array($order['executor'])) unset($order['executor'][0]);
if (isset($order['guardian']) && is_array($order['guardian'])) unset($order['guardian'][0]);
if (isset($order['legacy']) && is_array($order['legacy'])) unset($order['legacy'][0]);
if (isset($order['devise']) && is_array($order['devise'])) unset($order['devise'][0]);
if (isset($order['bequest']) && is_array($order['bequest'])) unset($order['bequest'][0]);
if (isset($order['estate']) && is_array($order['estate'])) unset($order['estate'][0]);

if (!isset($order['executor'])) $order['executorCount'] = 1;
if (!isset($order['other'])) $order['otherCount'] = 0;
if (!isset($order['group'])) $order['groupCount'] = 0;

# determine count of rows to display
$_SESSION['orderData']['childCount'] = $childCount = (!isset($order['child'])) ? 0 : count($order['child']);
$_SESSION['orderData']['otherCount'] = $otherCount = (!isset($order['other'])) ? 0 : count($order['other']);
$_SESSION['orderData']['groupCount'] = $groupCount = (!isset($order['group'])) ? 0 : count($order['group']);
$petCount = (!isset($order['pet'])) ? 0 : count($order['pet']);
$executorCount = (!isset($order['executor'])) ? 1 : count($order['executor']); // minimum of 1 executor
$legacyCount = (!isset($order['legacy'])) ? 0 : count($order['legacy']);
$deviseCount = (!isset($order['devise'])) ? 0 : count($order['devise']);
$bequestCount = (!isset($order['bequest'])) ? 0 : count($order['bequest']);
$estateCount = (!isset($order['estate'])) ? 0 : count($order['estate']);
$requirementCount = (!isset($order['requirement'])) ? 0 : count($order['requirement']);
$messageCount = (!isset($order['message'])) ? 0 : count($order['message']);
$infoCount = (!isset($order['info'])) ? 0 : count($order['info']);
$assetCount = (!isset($order['asset'])) ? 0 : count($order['asset']);
$liabilityCount = (!isset($order['liability'])) ? 0 : count($order['liability']);

# trust for minors
$order['trustForMinors'] = isset($order['trustForMinors']) ? $order['trustForMinors'] : (isset($order['hasChildrenYoung']) && $order['hasChildrenYoung'] == 'Yes' ? 'Yes' : 'No');

# default no joint executors
$order['jointExecutors'] = isset($order['jointExecutors']) ? $order['jointExecutors'] : 'No';

# default use public trustee
$order['usePublicTrustee'] = isset($order['usePublicTrustee']) ? $order['usePublicTrustee'] : 'Yes';

# products selected
$prod_mirrorwill = isset($order['product']['mirrorwill']) && $order['product']['mirrorwill'] == 'Yes' ? 'Yes' : 'No';
$prod_enduring = isset($order['product']['enduring']) && $order['product']['enduring'] == 'Yes' ? 'Yes' : 'No';

# generate a beneficiaries array for check inclusions of existing rows below
$beneficiaries = beneficiariesList();



$title = QUICKWILL && $_SESSION['quickwill'] ? "Premium Will" : "Online Will";
$desc = "Last Will and Testament creation form.";
$keywords = "last will and testament, online will, online wills, internet wills, online will kit";
require("pageincludes/header.php");

?>
      <section class="container" id="singlewill" style="visibility:hidden;">
        <div style="margin: 20px 0;">
          <p class="large">
            To complete your will, click on each of the large buttons below to enter the information for each section.
          </p>
        </div>
        <form id="singlewillform" method="post" action="" role="form">
          <input id="formID" name="formID" type="hidden" value="singlewill">
          <input id="orderID" name="orderID" type="hidden" value="<?php echo $_SESSION['orderid']; ?>">
          <input id="singlewillPrice" name="singlewillPrice" type="hidden" value="<?php echo $_SESSION['products']['singlewill']['priceinctax']; ?>">
          <input name="product[singlewill]" value="Yes" type="hidden">
          <input name="product[enduring]" value="<?php echo $prod_enduring;?>" type="hidden">
          <input name="product[mirrorwill]" value="<?php echo $prod_mirrorwill;?>" type="hidden">
          <input name="orderpaid" value="<?php echo $_SESSION['orderpaid'];?>" type="hidden">
          <input name="tabpro" id="tabpro" type="hidden">
          <div id="accordion" class="panel-group">

<!-- person -->
            <div  class="panel panel-default" id="person">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#personDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  The Testator (Will Maker)
                </h4>
              </div>
              <div id="personDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>
                    Please enter the contact details and answer the questions regarding partner and children. The answers to these questions will 
                    determine what other information is asked for.
                  </p>
                  <p>
                    Throughout the following pages, there will be space to enter email addresses and phone numbers for the various people mentioned in this will.
                    Although you do not have to enter these details, doing so will significantly help your 
                    <a data-toggle="popover" data-trigger="hover" title="Executor" data-content="The <em>executor</em> is responsible for the administration
                    of the person's estate, ensuring that funeral and
                    administration expenses are taken care of, that any debts and taxes are paid,
                    and that the assets and property are distributed to the beneficiaries according to the person's wishes.">
                    executor</a> administer this will.
                  </p>
                  <fieldset class="rowitem">
                    <legend>Contact Details</legend>
                    <div class="form-group">
                      <label for="firstname">First Name:</label>
                      <input id="firstname" name="person[firstname]" type="text" class="form-control propercase required person name" value="<?php echo isset($order['person']['firstname']) ? $order['person']['firstname'] : ''; ?>" <?php echo $_SESSION['orderpaid'] && !$_SESSION['admin'] && $order['person']['firstname'] > '' ? 'readonly' : 'autofocus';?>>
                    </div>
                    <div class="form-group">
                      <label for="othername">Middle Names:</label>
                      <input id="othername" name="person[othername]" type="text" class="form-control propercase person name" value="<?php echo isset($order['person']['othername']) ? $order['person']['othername'] : ''; ?>" <?php echo $_SESSION['orderpaid'] && !$_SESSION['admin'] && $order['person']['othername'] > '' ? 'readonly' : '';?>>
                    </div>
                    <div class="form-group">
                      <label for="surname">Last Name:</label>
                      <input id="surname" name="person[surname]" type="text" class="form-control propercase required person name" value="<?php echo isset($order['person']['surname']) ? $order['person']['surname'] : ''; ?>" <?php echo $_SESSION['orderpaid'] && !$_SESSION['admin'] && $order['person']['surname'] > '' ? 'readonly' : '';?>>
                      <input id="personName" name="person[fullname]" class="fullname" type="hidden" value="<?php echo (isset($order['person']['firstname']) ? $order['person']['firstname'] : '') . ' ' . (isset($order['person']['othername']) ? $order['person']['othername'] . ' ' : '') . (isset($order['person']['surname']) ? $order['person']['surname'] : '');?>">
                    </div>
                    <div class="form-group">
                      <label for="dob">Date of Birth:</label>
                      <input id="dob" name="person[dob]" type="text" class="form-control dateAU" value="<?php echo isset($order['person']['dob']) ? $order['person']['dob'] : ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="personemail">Email address:</label>
                      <input id="personemail" name="person[email]" type="email" class="form-control email lowercase" value="<?php echo isset($order['person']['email']) ? $order['person']['email'] : ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="personphone">Phone number:</label>
                      <input id="personphone" name="person[phone]" type="tel" class="form-control anyphone" value="<?php echo isset($order['person']['phone']) ? $order['person']['phone'] : ''; ?>">
                    </div>
                    <div class="address">
                      <div class="form-group">
                        <label for="address1">Street Address:</label>
                        <input id="address1" name="person[address1]" type="text" class="form-control propercase required notpobox address" value="<?php echo isset($order['person']['address1']) ? $order['person']['address1'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="address2">Street Address Line 2 (optional):</label>
                        <input id="address2" name="person[address2]" type="text" class="form-control propercase notpobox address address2" value="<?php echo isset($order['person']['address2']) ? $order['person']['address2'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="city">City/Suburb:</label>
                        <input id="city" name="person[city]" type="text" class="form-control uppercase required cityauto" value="<?php echo isset($order['person']['city']) ? $order['person']['city'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="state">State:</label>
                        <input id="state" name="person[state]" type="text" class="form-control uppercase required state" value="<?php echo isset($order['person']['state']) ? $order['person']['state'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="postcode">Postcode:</label>
                        <input id="postcode" name="person[postcode]" type="text" class="uppercase required form-control" value="<?php echo isset($order['person']['postcode']) ? $order['person']['postcode'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="country">Country:</label>
                        <input id="country" name="person[country]" type="text" class="form-control required country propercase" value="<?php echo isset($order['person']['country']) ? $order['person']['country'] : 'Australia'; ?>">
                      </div>
                    </div>
                  </fieldset>
                  <fieldset>
                    <legend>Personal Information</legend>
                    <div class="form-group">
                      <label for="hasSpouse" style="min-width: 260px;">
                        Do you have a <a data-toggle="popover" data-trigger="hover" data-content="<em>Partner</em> can include married or de facto spouse."> partner</a>?
                      </label>
                      <input id="hasSpouse" name="hasSpouse" type="hidden" class="required" value="<?php echo isset($order['hasSpouse']) ? $order['hasSpouse'] : '';?>">
                      <br>
                      <div class="btn-group">
                        <button id="spouseYes" class="btn btn-default <?php if (isset($order['hasSpouse']) && $order['hasSpouse'] == 'Yes') echo 'active'; ?>" >Yes</button>
                        <button id="spouseNo" class="btn btn-default <?php if (isset($order['hasSpouse']) && $order['hasSpouse'] == 'No') echo 'active'; ?>" >No</button>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="hasChildren" style="min-width: 260px;">
                        Do you have <a data-toggle="popover" data-trigger="hover" data-content="<em>Children</em> can include step-children and foster children.">children</a>?
                      </label>
                      <input id="hasChildren" name="hasChildren" type="hidden" class="required" value="<?php echo isset($order['hasChildren']) ? $order['hasChildren'] : '';?>">
                      <br>
                      <div class="btn-group">
                        <button id="hasChildrenYes" class="btn btn-default <?php if (isset($order['hasChildren']) && $order['hasChildren'] == 'Yes') echo 'active'; ?>" >Yes</button>
                        <button id="hasChildrenNo" class="btn btn-default <?php if (isset($order['hasChildren']) && $order['hasChildren'] == 'No') echo 'active'; ?>" >No</button>
                      </div>
                    </div>
                    <div class="form-group" id="hasChildrenYoungDiv" style="display: <?php echo (isset($order['hasChildren']) && $order['hasChildren'] == 'Yes') ? 'block' : 'none'; ?>;">
                      <label for="hasChildrenYoung" style="min-width: 260px;">
                        Are any of the children <a data-toggle="popover" data-trigger="hover" data-content="Children who are under 18 years of age will need a guardian identified in the will."> under 18 years</a> of age?
                      </label>
                      <input id="hasChildrenYoung" name="hasChildrenYoung" type="hidden" class="required" value="<?php echo isset($order['hasChildrenYoung']) ? $order['hasChildrenYoung'] : '';?>">
                      <br>
                      <div class="btn-group">
                        <button id="hasChildrenYoungYes" class="btn btn-default <?php if (isset($order['hasChildrenYoung']) && $order['hasChildrenYoung'] == 'Yes') echo 'active'; ?>" >Yes</button>
                        <button id="hasChildrenYoungNo" class="btn btn-default <?php if (isset($order['hasChildrenYoung']) && $order['hasChildrenYoung'] == 'No') echo 'active'; ?>" >No</button>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="hasPets" style="min-width: 260px;">
                        Do you have any pets that should be provided for under this will?
                      </label>
                      <input id="hasPets" name="hasPets" type="hidden" class="required" value="<?php echo isset($order['hasPets']) ? $order['hasPets'] : '';?>">
                      <br>
                      <div class="btn-group">
                        <button id="hasPetsYes" class="btn btn-default <?php if (isset($order['hasPets']) && $order['hasPets'] == 'Yes') echo 'active'; ?>" >Yes</button>
                        <button id="hasPetsNo" class="btn btn-default <?php if (isset($order['hasPets']) && $order['hasPets'] == 'No') echo 'active'; ?>" >No</button>
                      </div>
                    </div>
                  </fieldset>
                </div>
              </div>
            </div>

<!-- Beneficiaries -->
            <div class="panel panel-default" id="beneficiaries">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#beneficiaryDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Beneficiaries
                </h4>
              </div>
              <div id="beneficiaryDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>Your beneficiaries, are those people, groups of people or organisations who may receive a portion of your
                  <a data-toggle="popover" data-trigger="hover" title="Estate" data-content="A person's <strong>estate</strong> is their net worth at a point in time. 
                  It is the sum of a person's assets - legal rights, interests and entitlements to property
                  of any kind - less all liabilities at that time.">estate</a>.</p>
                  <p>Considering that they may need to be contacted at some future date, entering accurate information for your beneficiaries is very important. We recommend entering an email 
                  address and phone number for each beneficiary.</p>
                  <p>You can legally omit anyone from your will, however, if you have a
                  <a data-toggle="popover" data-trigger="hover" title="Partner" data-content="<em>Partner</em> can include heterosexual legal spouse(husband or wife), heterosexual de facto spouse or same sex de facto spouse.">partner</a>
                  and/or
                  <a data-toggle="popover" data-trigger="hover" title="Children" data-content="<em>Children</em> can include step-children and foster children.">children</a>
                  and do not include them, they may legally challenge your will after your death.</p>
                  
                  <fieldset id="spouseDetails" style="display: <?php echo (isset($order['hasSpouse']) && $order['hasSpouse'] == 'Yes') ? 'block' : 'none';?>;">
                    <legend>Partner Beneficiary</legend>
                    <div id="spouse" class="rowitems">
                      <div id="spouseRow" class="rowitem">
                        <div class="form-group">
                          <label for="spouseFirstname">First Name:</label>
                           <input id="spouseFirstname" name="spouse[firstname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['spouse']['firstname']) ? $order['spouse']['firstname'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="spouseOthername">Middle Names:</label>
                           <input id="spouseOthername" name="spouse[othername]" type="text" class="form-control propercase name" value="<?php echo isset($order['spouse']['othername']) ? $order['spouse']['othername'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="spouseSurname">Last Name:</label>
                           <input id="spouseSurname" name="spouse[surname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['spouse']['surname']) ? $order['spouse']['surname'] : ''; ?>">
                          <input id="spouseName" name="spouse[fullname]" class="fullname beneficiary" type="hidden" value="<?php echo isset($order['spouse']['surname']) && $order['spouse']['surname'] > '' ? (isset($order['spouse']['firstname']) ? $order['spouse']['firstname'] . ' ' : '') . (isset($order['spouse']['othername']) ? $order['spouse']['othername'] . ' ' : '') . $order['spouse']['surname'] : '';?>">
                          <input id="spouseRelationship" name="spouse[relationship]" type="hidden" value='partner'>
                        </div>
<!--
                        <div class="form-group">
                          <label for="spouseDOB">Date of Birth:</label>
                          <input id="spouseDOB" name="spouse[dob]" type="text" class="form-control"  data-provide="datepicker" placeholder="dd/mm/yyyy" title="Please enter a date of birth" value="<?php echo isset($order['spouse']['dob']) ? $order['spouse']['dob'] : ''; ?>">
                        </div>
-->
                        <div class="form-group">
                          <label for="spouseEmail">Email address:</label>
                           <input id="spouseEmail" name="spouse[email]" type="email" class="form-control email lowercase" value="<?php echo isset($order['spouse']['email']) ? $order['spouse']['email'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="spousePhone">Phone:</label>
                          <input id="spousePhone" name="spouse[phone]" type="tel" class="form-control anyphone" value="<?php echo isset($order['spouse']['phone']) ? $order['spouse']['phone'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <button class="btn btn-info btn-xs copyAddress">Use Testator Address?</button>
                        </div>
                        <div class="address">
                          <div class="form-group">
                            <label for="spouseAddressA">Street Address:</label>
                            <input id="spouseAddressA" name="spouse[address1]" type="text" class="form-control propercase notpobox address" value="<?php echo isset($order['spouse']['address1']) ? $order['spouse']['address1'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="spouseAddressB">Street Address Line 2 (optional):</label>
                            <input id="spouseAddressB" name="spouse[address2]" type="text" class="form-control propercase notpobox address address2" value="<?php echo isset($order['spouse']['address2']) ? $order['spouse']['address2'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="spouseCity">City/Suburb:</label>
                            <input id="spouseCity" name="spouse[city]" type="text" class="form-control uppercase cityauto" value="<?php echo isset($order['spouse']['city']) ? $order['spouse']['city'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="spouseState">State:</label>
                             <input id="spouseState" name="spouse[state]" type="text" class="form-control uppercase state" value="<?php echo isset($order['spouse']['state']) ? $order['spouse']['state'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="spousePostcode">Postcode:</label>
                            <input id="spousePostcode" name="spouse[postcode]" type="text" class="form-control uppercase" value="<?php echo isset($order['spouse']['postcode']) ? $order['spouse']['postcode'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="spouseCountry">Country:</label>
                            <input id="spouseCountry" name="spouse[country]" type="text" class="form-control propercase country" value="<?php echo isset($order['spouse']['country']) ? $order['spouse']['country'] : ''; ?>">
                          </div>
                        </div>
                      </div>
                    </div>
                  </fieldset>
                  
                  <fieldset id="childrenDetails" style="display: <?php echo (isset($order['hasChildren']) && $order['hasChildren'] == 'Yes') ? 'block' : 'none';?>;">
                    <legend>Children Beneficiaries</legend>
                    <div id="child" class="rowitems" style='display: <?php echo $childCount ? 'block' : 'none';?>'>
<?php
for($i = 0; $i <= $childCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
?>
                      <div id="childRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="childFirstname<?php echo $i; ?>">First Name:</label>
                          <input id="childFirstname<?php echo $i; ?>" name="child[<?php echo $i; ?>][firstname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['child'][$i]['firstname']) ? $order['child'][$i]['firstname'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="childOthername<?php echo $i; ?>">Middle Names:</label>
                          <input id="childOthername<?php echo $i; ?>" name="child[<?php echo $i; ?>][othername]" type="text" class="form-control propercase name" value="<?php echo isset($order['child'][$i]['othername']) ? $order['child'][$i]['othername'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="childSurname<?php echo $i; ?>">Last Name:</label>
                          <input id="childSurname<?php echo $i; ?>" name="child[<?php echo $i; ?>][surname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['child'][$i]['surname']) ? $order['child'][$i]['surname'] : ''; ?>">
                          <input id="childName<?php echo $i; ?>" name="child[<?php echo $i; ?>][fullname]" class="fullname beneficiary" type="hidden" value="<?php echo isset($order['child'][$i]['surname']) && $order['child'][$i]['surname'] > '' ? (isset($order['child'][$i]['firstname']) ? $order['child'][$i]['firstname'] . ' ' : '') . (isset($order['child'][$i]['othername']) ? $order['child'][$i]['othername'] . ' ' : '') . $order['child'][$i]['surname'] : '';?>">
                        </div>
                        <div class="form-group">
                          <input id="childRelation<?php echo $i; ?>" name="child[<?php echo $i; ?>][relationship]" class="required" type="hidden" value="<?php echo isset($order['child'][$i]['relationship']) ? $order['child'][$i]['relationship'] : '';?>">
                          <div class="btn-group" >
                            <button id="childRelationBtnSon<?php echo $i; ?>" class="btn btn-default<?php if (isset($order['child'][$i]['relationship']) && $order['child'][$i]['relationship'] == 'Son') echo ' active'; ?>">Son</button>
                            <button id="childRelationBtnDaughter<?php echo $i; ?>" class="btn btn-default<?php if (isset($order['child'][$i]['relationship']) && $order['child'][$i]['relationship'] == 'Daughter') echo ' active'; ?>">Daughter</button>
                          </div>
                        </div>
<!--
                        <div class="form-group">
                          <label for="childDOB<?php echo $i; ?>">Date of Birth:</label>
                          <input id="childDOB<?php echo $i; ?>" name="child[<?php echo $i; ?>][dob]" type="text" class="form-control" data-provide="datepicker" placeholder="dd/mm/yyyy" title="Please enter a date of birth" value="<?php echo isset($order['child'][$i]['dob']) ? $order['child'][$i]['dob'] : ''; ?>">
                        </div>
-->
                        <div class="form-group">
                          <label for="childEmail<?php echo $i; ?>">Email address:</label>
                          <input id="childEmail<?php echo $i; ?>" name="child[<?php echo $i; ?>][email]" type="email" class="form-control email lowercase" value="<?php echo isset($order['child'][$i]['email']) ? $order['child'][$i]['email'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="childPhone<?php echo $i; ?>">Phone:</label>
                          <input id="childPhone<?php echo $i; ?>" name="child[<?php echo $i; ?>][phone]" type="tel" class="form-control anyphone" value="<?php echo isset($order['child'][$i]['phone']) ? $order['child'][$i]['phone'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <button class="btn btn-info btn-xs copyAddress">Use Testator Address?</button><br>
                        </div>
                        <div class="address">
                          <div class="form-group">
                            <label for="childAddressA<?php echo $i; ?>">Street Address:</label>
                            <input id="childAddressA<?php echo $i; ?>" name="child[<?php echo $i; ?>][address1]" type="text" class="form-control propercase notpobox address" value="<?php echo isset($order['child'][$i]['address1']) ? $order['child'][$i]['address1'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="childAddressB<?php echo $i; ?>">Street Address Line 2 (optional):</label>
                            <input id="childAddressB<?php echo $i; ?>" name="child[<?php echo $i; ?>][address2]" type="text" class="form-control propercase notpobox address address2" value="<?php echo isset($order['child'][$i]['address2']) ? $order['child'][$i]['address2'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="childCity<?php echo $i; ?>">City/Suburb:</label>
                            <input id="childCity<?php echo $i; ?>" name="child[<?php echo $i; ?>][city]" type="text" class="form-control uppercase cityauto" value="<?php echo isset($order['child'][$i]['city']) ? $order['child'][$i]['city'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="childState<?php echo $i; ?>">State:</label>
                            <input id="childState<?php echo $i; ?>" name="child[<?php echo $i; ?>][state]" type="text" class="form-control uppercase state" value="<?php echo isset($order['child'][$i]['state']) ? $order['child'][$i]['state'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="childPostcode<?php echo $i; ?>">Postcode:</label>
                            <input id="childPostcode<?php echo $i; ?>" name="child[<?php echo $i; ?>][postcode]" type="text" class="form-control uppercase" value="<?php echo isset($order['child'][$i]['postcode']) ? $order['child'][$i]['postcode'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="childCountry<?php echo $i; ?>">Country:</label>
                            <input id="childCountry<?php echo $i; ?>" name="child[<?php echo $i; ?>][country]" type="text" class="form-control propercase country" value="<?php echo isset($order['child'][$i]['country']) ? $order['child'][$i]['country'] : ''; ?>">
                          </div>
                        </div>
                        <button class="remove btn btn-sm btn-danger" id="childRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this child beneficiary</button>
                      </div>
<?php
}
?>
                    </div>
                    <button class="add btn btn-sm btn-success"><i class="glyphicon glyphicon-plus"></i> Add child beneficiary</button>
                  </fieldset>
                  
                  
                  <fieldset id="otherDetails">
                    <legend>Individual Beneficiaries <?php if ((isset($order['hasChildren']) && $order['hasChildren'] == 'Yes') || (isset($order['hasSpouse']) && $order['hasSpouse'] == 'Yes')) echo '<span class="small"><em>(Not&nbsp;partner&nbsp;or&nbsp;children)</em></span>'; ?></legend>
                    <div id="other" class="rowitems" style='display: <?php echo $otherCount ? 'block' : 'none';?>'>
<?php
for($i = 0; $i <= $otherCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
?>
                      <div id="otherRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="otherFirstname<?php echo $i; ?>">First Name:</label>
                          <input id="otherFirstname<?php echo $i; ?>" name="other[<?php echo $i; ?>][firstname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['other'][$i]['firstname']) ? $order['other'][$i]['firstname'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="otherOthername<?php echo $i; ?>">Middle Names:</label>
                          <input id="otherOthername<?php echo $i; ?>" name="other[<?php echo $i; ?>][othername]" type="text" class="form-control propercase name" value="<?php echo isset($order['other'][$i]['othername']) ? $order['other'][$i]['othername'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="otherSurname<?php echo $i; ?>">Last Name:</label>
                          <input id="otherSurname<?php echo $i; ?>" name="other[<?php echo $i; ?>][surname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['other'][$i]['surname']) ? $order['other'][$i]['surname'] : ''; ?>">
                          <input id="otherName<?php echo $i; ?>" name="other[<?php echo $i; ?>][fullname]" class="fullname beneficiary" type="hidden" value="<?php echo isset($order['other'][$i]['surname']) && $order['other'][$i]['surname'] > '' ? (isset($order['other'][$i]['firstname']) ? $order['other'][$i]['firstname'] . ' ' : '') . (isset($order['other'][$i]['othername']) ? $order['other'][$i]['othername'] . ' ' : '') . $order['other'][$i]['surname'] : '';?>">
                        </div>
                        <div class="form-group">
                          <label for="otherRelation<?php echo $i; ?>">Relationship: <span class="small"><em>(eg. friend, cousin, grandfather)</em></span></label>
                          <input id="otherRelation<?php echo $i; ?>" name="other[<?php echo $i; ?>][relationship]" type="text" class="form-control required lowercase" value="<?php echo isset($order['other'][$i]['relationship']) ? $order['other'][$i]['relationship'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="otherEmail<?php echo $i; ?>">Email address:</label>
                          <input id="otherEmail<?php echo $i; ?>" name="other[<?php echo $i; ?>][email]" type="email" class="form-control email lowercase" value="<?php echo isset($order['other'][$i]['email']) ? $order['other'][$i]['email'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="otherPhone<?php echo $i; ?>">Phone:</label>
                          <input id="otherPhone<?php echo $i; ?>" name="other[<?php echo $i; ?>][phone]" type="tel" class="form-control anyphone" value="<?php echo isset($order['other'][$i]['phone']) ? $order['other'][$i]['phone'] : ''; ?>">
                        </div>
                        <div class="address">
                          <div class="form-group">
                            <label for="otherAddressA<?php echo $i; ?>">Street Address:</label>
                            <input id="otherAddressA<?php echo $i; ?>" name="other[<?php echo $i; ?>][address1]" type="text" class="form-control propercase notpobox address" value="<?php echo isset($order['other'][$i]['address1']) ? $order['other'][$i]['address1'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="otherAddressB<?php echo $i; ?>">Street Address Line 2 (optional):</label>
                            <input id="otherAddressB<?php echo $i; ?>" name="other[<?php echo $i; ?>][address2]" type="text" class="form-control propercase notpobox address address2" value="<?php echo isset($order['other'][$i]['address2']) ? $order['other'][$i]['address2'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="otherCity<?php echo $i; ?>">City/Suburb:</label>
                            <input id="otherCity<?php echo $i; ?>" name="other[<?php echo $i; ?>][city]" type="text" class="form-control uppercase cityauto" value="<?php echo isset($order['other'][$i]['city']) ? $order['other'][$i]['city'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="otherState<?php echo $i; ?>">State:</label>
                            <input id="otherState<?php echo $i; ?>" name="other[<?php echo $i; ?>][state]" type="text" class="form-control uppercase state" value="<?php echo isset($order['other'][$i]['state']) ? $order['other'][$i]['state'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="otherPostcode<?php echo $i; ?>">Postcode:</label>
                            <input id="otherPostcode<?php echo $i; ?>" name="other[<?php echo $i; ?>][postcode]" type="text" class="form-control" value="<?php echo isset($order['other'][$i]['postcode']) ? $order['other'][$i]['postcode'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="otherCountry<?php echo $i; ?>">Country:</label>
                            <input id="otherCountry<?php echo $i; ?>" name="other[<?php echo $i; ?>][country]" type="text" class="form-control propercase country" value="<?php echo isset($order['other'][$i]['country']) ? $order['other'][$i]['country'] : ''; ?>">
                          </div>
                        </div>
                        <button class="remove btn btn-sm btn-danger" id="otherRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this beneficiary</button>
                      </div>
<?php
}
?>
                    </div>
                    <button class="add btn btn-sm btn-success"><i class="glyphicon glyphicon-plus"></i> Add individual beneficiary</button>
                  </fieldset>
                  
                  
                  <fieldset id="groupDetails">
                    <legend>Non-Individual Beneficiaries <span class="small"><em>(Charities,&nbsp;companies,&nbsp;organisations,&nbsp;etc.)</em></span></legend>
                    <div id="group" class="rowitems" style='display: <?php echo $groupCount ? 'block' : 'none';?>'>
<?php
for($i = 0; $i <= $groupCount; $i++) {
  # hide first block (template) and second if organisation autoadded
  $display = ($i == 0 || ($isOrg && $i == 1)) ? 'none' : 'block';
  if ($isOrg && $i == 1) {
?>
                      <div class="rowitem">
                        <p>
                          <em>
                          <?php echo $order['group'][$i]['fullname'];?><br>
                          <?php echo $order['group'][$i]['address1'];?><br>
                          <?php echo isset($order['group'][$i]['address2']) && $order['group'][$i]['address2'] > '' ? $order['group'][$i]['address2'] . '<br>' : ''; ?>
                          <?php echo $order['group'][$i]['city'] . ' ' . $order['group'][$i]['state'] . ' ' . $order['group'][$i]['postcode']; ?><br>
                          Phone: <?php echo $order['group'][$i]['phone'];?>
                          </em>
                        </p>
                      </div>
<?php
  }
?>
                      <div id="groupRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="groupName<?php echo $i; ?>">Name:</label>
                          <input id="groupName<?php echo $i; ?>" name="group[<?php echo $i; ?>][fullname]" type="text" class="form-control required propercase beneficiary" value="<?php echo isset($order['group'][$i]['fullname']) ? $order['group'][$i]['fullname'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="groupEmail<?php echo $i; ?>">Email address:</label>
                          <input id="groupEmail<?php echo $i; ?>" name="group[<?php echo $i; ?>][email]" type="email" class="form-control email lowercase" value="<?php echo isset($order['group'][$i]['email']) ? $order['group'][$i]['email'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="groupPhone<?php echo $i; ?>">Phone:</label>
                          <input id="groupPhone<?php echo $i; ?>" name="group[<?php echo $i; ?>][phone]" type="tel" class="form-control anyphone" value="<?php echo isset($order['group'][$i]['phone']) ? $order['group'][$i]['phone'] : ''; ?>">
                        </div>
                        <div class="address">
                          <div class="form-group">
                            <label for="groupAddressA<?php echo $i; ?>">Street Address:</label>
                            <input id="groupAddressA<?php echo $i; ?>" name="group[<?php echo $i; ?>][address1]" type="text" class="form-control required propercase notpobox address" value="<?php echo isset($order['group'][$i]['address1']) ? $order['group'][$i]['address1'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="groupAddressB<?php echo $i; ?>">Street Address Line 2 (optional):</label>
                            <input id="groupAddressB<?php echo $i; ?>" name="group[<?php echo $i; ?>][address2]" type="text" class="form-control propercase notpobox address address2" value="<?php echo isset($order['group'][$i]['address2']) ? $order['group'][$i]['address2'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="groupCity<?php echo $i; ?>">City/Suburb:</label>
                            <input id="groupCity<?php echo $i; ?>" name="group[<?php echo $i; ?>][city]" type="text" class="form-control required uppercase cityauto" value="<?php echo isset($order['group'][$i]['city']) ? $order['group'][$i]['city'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="groupState<?php echo $i; ?>">State:</label>
                            <input id="groupState<?php echo $i; ?>" name="group[<?php echo $i; ?>][state]" type="text" class="form-control required uppercase state" value="<?php echo isset($order['group'][$i]['state']) ? $order['group'][$i]['state'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="groupPostcode<?php echo $i; ?>">Postcode:</label>
                            <input id="groupPostcode<?php echo $i; ?>" name="group[<?php echo $i; ?>][postcode]" type="text" class="form-control required" value="<?php echo isset($order['group'][$i]['postcode']) ? $order['group'][$i]['postcode'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="groupCountry<?php echo $i; ?>">Country:</label>
                            <input id="groupCountry<?php echo $i; ?>" name="group[<?php echo $i; ?>][country]" type="text" class="form-control required propercase country" value="<?php echo isset($order['group'][$i]['country']) ? $order['group'][$i]['country'] : ''; ?>">
                          </div>
                        </div>
                        <button class="remove btn btn-sm btn-danger" id="groupRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this beneficiary</button>
                      </div>
<?php
}
?>
                    </div>
                    <button class="add btn btn-sm btn-success"><i class="glyphicon glyphicon-plus"></i> Add non-individual beneficiary</button>
                  </fieldset>
                </div>
              </div>
            </div>

<!-- Executor -->
            <div class="panel panel-default" id="executors">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#executorDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Executors
                </h4>
              </div>
              <div id="executorDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>
                    In this section, you will enter details of who the <a data-toggle="popover" data-trigger="hover" title="Executor" data-content="The <em>executor</em> is responsible for the administration
                    of the person's estate, ensuring that funeral and
                    administration expenses are taken care of, that any debts and taxes are paid,
                    and that the assets and property are distributed to the beneficiaries according to the person's wishes.">
                    executors</a> are for your will. You only require one executor, but it is a good idea to have at least 2.
                  </p>
                  <p>
                    Each executor must be over 18 years of age. Executors may also be beneficiaries of your will.
                  </p>
                  <p>
                    Make sure that your executors are willing to take on the responsibility of your will, and also that they are not likely to pre-decease you.
                  </p>
                  <div class="form-group" id="partnerForExecutor" <?php if (!isset($order['hasSpouse']) || $order['hasSpouse'] == 'No') echo 'style="display: none;"'; ?>>
                    <label for="partnerExecutor" style="min-width: 280px;">
                      <a data-toggle="popover" data-trigger="hover" title="Partner as Executor?" data-content="It is fairly common for those creating a will to use their current partner as an executor. Selecting <strong><em>Yes</em></strong> here will simply copy their details into these fields to save you some time.">Make partner an executor?</a>&nbsp;
                    </label>
                    <input id="partnerExecutor" name="partnerExecutor" class="required" type='hidden' value="<?php echo isset($order['partnerExecutor']) ? $order['partnerExecutor'] : ''; ?>"/>
                    <div class="btn-group" >
                      <button id="partnerExecutorYes" class="btn btn-default<?php if (isset($order['partnerExecutor']) && $order['partnerExecutor'] == 'Yes') echo ' active'; ?>">Yes</button>
                      <button id="partnerExecutorNo" class="btn btn-default<?php if (isset($order['partnerExecutor']) && $order['partnerExecutor'] == 'No') echo ' active'; ?>">No</button>
                    </div>
                  </div>
                  <div class="form-group" id="jointExecutorsDiv">
                    <label for="jointExecutors" style="min-width: 280px;">
                      <a data-toggle="popover" data-trigger="hover" title="Joint Executors" data-content="<p>If you select <em><strong>Yes</strong></em> here, then your executors will act jointly, which means that they must agree on all decisions they make on behalf of your estate.</p><p>If you select <em><strong>No</strong></em>, then the first executor will act alone, and only if they are unavailable to act as executor will subsequent executors take on the role. </p>">Executors to act jointly?</a>&nbsp;
                    </label>
                    <input id="jointExecutors" name="jointExecutors" class="required" type='hidden' value="<?php echo isset($order['jointExecutors']) ? $order['jointExecutors'] : '';?>"/>
                    <div class="btn-group" >
                      <button id="jointExecutorsYes" class="btn btn-default<?php if (isset($order['jointExecutors']) && $order['jointExecutors'] == 'Yes') echo ' active'; ?>">Yes</button>
                      <button id="jointExecutorsNo" class="btn btn-default<?php if (isset($order['jointExecutors']) && $order['jointExecutors'] == 'No') echo ' active'; ?>">No</button>
                    </div>
                  </div>
                  <fieldset>
                    <legend>Executor Details</legend>
                    <div id="executor" class="rowitems" style='display: <?php echo $executorCount ? 'block' : 'none';?>'>
<?php
for($i = 0; $i <= $executorCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
  $disableExecutor1 = isset($order['hasSpouse']) && $order['hasSpouse'] == 'Yes' && isset($order['partnerExecutor']) && $order['partnerExecutor'] == 'Yes' && $i == 1 ? 'disabled' : '';
?>
                      <fieldset id="executorRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;" <?php echo $disableExecutor1;?>>
                        <div class="form-group">
                          <label for="executorFirstname<?php echo $i; ?>">First Name:</label>
                          <input id="executorFirstname<?php echo $i; ?>" name="executor[<?php echo $i; ?>][firstname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['executor'][$i]['firstname']) ? $order['executor'][$i]['firstname'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="executorOthername<?php echo $i; ?>">Middle Names:</label>
                          <input id="executorOthername<?php echo $i; ?>" name="executor[<?php echo $i; ?>][othername]" type="text" class="form-control propercase name" value="<?php echo isset($order['executor'][$i]['othername']) ? $order['executor'][$i]['othername'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="executorSurname<?php echo $i; ?>">Last Name:</label>
                          <input id="executorSurname<?php echo $i; ?>" name="executor[<?php echo $i; ?>][surname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['executor'][$i]['surname']) ? $order['executor'][$i]['surname'] : ''; ?>">
                          <input id="executorName<?php echo $i; ?>" name="executor[<?php echo $i; ?>][fullname]" class="fullname" type="hidden" value="<?php echo isset($order['executor'][$i]['surname']) && $order['executor'][$i]['surname'] > '' ? (isset($order['executor'][$i]['firstname']) ? $order['executor'][$i]['firstname'] . ' ' : '') . (isset($order['executor'][$i]['othername']) ? $order['executor'][$i]['othername'] . ' ' : '') . $order['executor'][$i]['surname'] : '';?>">
                        </div>
                        <div class="form-group">
                          <label for="executorEmail<?php echo $i; ?>">Email address:</label>
                          <input id="executorEmail<?php echo $i; ?>" name="executor[<?php echo $i; ?>][email]" type="email" class="form-control email lowercase" value="<?php echo isset($order['executor'][$i]['email']) ? $order['executor'][$i]['email'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="executorPhone<?php echo $i; ?>">Phone:</label>
                          <input id="executorPhone<?php echo $i; ?>" name="executor[<?php echo $i; ?>][phone]" type="tel" class="form-control anyphone" value="<?php echo isset($order['executor'][$i]['phone']) ? $order['executor'][$i]['phone'] : ''; ?>">
                        </div>
                        <div class="address">
                          <div class="form-group">
                            <label for="executorAddressA<?php echo $i; ?>">Street Address:</label>
                            <input id="executorAddressA<?php echo $i; ?>" name="executor[<?php echo $i; ?>][address1]" type="text" class="form-control required propercase notpobox address" value="<?php echo isset($order['executor'][$i]['address1']) ? $order['executor'][$i]['address1'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="executorAddressB<?php echo $i; ?>">Street Address Line 2 (optional):</label>
                            <input id="executorAddressB<?php echo $i; ?>" name="executor[<?php echo $i; ?>][address2]" type="text" class="form-control propercase notpobox address address2" value="<?php echo isset($order['executor'][$i]['address2']) ? $order['executor'][$i]['address2'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="executorCity<?php echo $i; ?>">City/Suburb:</label>
                            <input id="executorCity<?php echo $i; ?>" name="executor[<?php echo $i; ?>][city]" type="text" class="form-control required uppercase cityauto" value="<?php echo isset($order['executor'][$i]['city']) ? $order['executor'][$i]['city'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="executorState<?php echo $i; ?>">State:</label>
                            <input id="executorState<?php echo $i; ?>" name="executor[<?php echo $i; ?>][state]" type="text" class="form-control required uppercase state" value="<?php echo isset($order['executor'][$i]['state']) ? $order['executor'][$i]['state'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="executorPostcode<?php echo $i; ?>">Postcode:</label>
                            <input id="executorPostcode<?php echo $i; ?>" name="executor[<?php echo $i; ?>][postcode]" type="text" class="form-control required" value="<?php echo isset($order['executor'][$i]['postcode']) ? $order['executor'][$i]['postcode'] : ''; ?>">
                          </div>
                          <div class="form-group">
                            <label for="executorCountry<?php echo $i; ?>">Country:</label>
                            <input id="executorCountry<?php echo $i; ?>" name="executor[<?php echo $i; ?>][country]" type="text" class="form-control required propercase country" value="<?php echo isset($order['executor'][$i]['country']) ? $order['executor'][$i]['country'] : ''; ?>">
                          </div>
                        </div>
                        <button <?php if ($executorCount == 1 || $disableExecutor1 == 'disabled'):?>style="display: none;"<?php endif;?> class="remove btn btn-sm btn-danger" id="executorRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this executor</button>
                      </fieldset>
<?php
}
?>
                    </div>
                    <button class="add btn btn-sm btn-success"><i class="glyphicon glyphicon-plus"></i> Add executor</button>
                    <br> <br>
                    <div class="form-group">
                      <label for="executorConditions">
                        <a data-toggle="popover" data-trigger="hover" title="Executor Conditions" data-content="If you need to add any special requirements that apply to your executors, then you may enter them here. The content will be included in an additional paragraph after the main executor clause.">Additional Executor Conditions</a>: <small><em>(Optional)</em></small>
                      </label>
                      <textarea id="executorConditions" name='executorConditions' class="form-control" placeholder="If you wish to specify any additional conditions or requirements, then please enter them here..."><?php echo isset($order['executorConditions']) ? $order['executorConditions'] : ''; ?></textarea>
                    </div>
                  </fieldset>
                  
                  <fieldset id="trustForMinorsSection" style="display: <?php echo (isset($order['hasChildrenYoung']) && $order['hasChildrenYoung'] == 'Yes') ? 'block' : 'none';?>;">
                    <legend>Trust for Minor Beneficiaries</legend>
                    <p style="line-height: 19px;">
                    If you have beneficiaries who are under the age of majority (normally 18 years old), you can include in your Will a provision to
                    hold any gifts or share of your estate in trust for them until they reach a nominated age. Your
                    executor<?php echo isset($order['executorCount']) && $order['executorCount'] > 1 ? 's' : ''; ?>
                    will act as Trustee
                    for the purpose of support, welfare and education until they reach the age noted.
                    </p>
                    <div class="form-group">
                      <label for="trustForMinors">
                        Will you have your executor<?php echo $executorCount > 1 ? 's' : ''; ?> act as Trustee for minor beneficiaries?&nbsp;
                      </label>
                      <input id="trustForMinors" name="trustForMinors" type='hidden' class="required" value="<?php echo isset($order['trustForMinors']) ? $order['trustForMinors'] : ''; ?>"/>
                      <div class="btn-group" >
                        <button id="trustForMinorsYes" class="btn btn-default<?php if (isset($order['trustForMinors']) && $order['trustForMinors'] == 'Yes') echo ' active'; ?>">Yes</button>
                        <button id="trustForMinorsNo" class="btn btn-default<?php if (isset($order['trustForMinors']) && $order['trustForMinors'] == 'No') echo ' active'; ?>">No</button>
                      </div>
                    </div>
                    <div id="trustForMinorsSubsection" style="display: <?php echo (isset($order['trustForMinors']) && $order['trustForMinors'] == 'Yes') ? 'block' : 'none';?>;">
                      <div class="form-group">
                        <label for="ageOfInheritance">What age will you nominate as the age of inheritance?</label>
                        <input id="ageOfInheritance" name="ageOfInheritance" type="number" min="18" max="25" class="form-control required" value="<?php echo isset($order['ageOfInheritance']) ? $order['ageOfInheritance'] : '18';?>">
                      </div>
                      <div class="form-group">
                        <label for="trustForMinorsConditions">Trust conditions (optional): <small><a class="assist" assist="trust"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                        <textarea id="trustForMinorsConditions" name="trustForMinorsConditions" class="form-control" placeholder='Trust conditions or details...' ><?php echo isset($order['trustForMinorsConditions']) ? $order['trustForMinorsConditions'] : ''; ?></textarea>
                      </div>
                    </div>
                  </fieldset>
                </div>
              </div>
            </div>

<!-- Guardian -->
            <div  class="panel panel-default" id="guardians" style="display: <?php echo (isset($order['hasChildrenYoung']) && $order['hasChildrenYoung'] == 'Yes') ? 'block' : 'none';?>;">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#guardianDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Guardian of Minor Children
                </h4>
              </div>
              <div id="guardianDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>Your will can include information on who you wish to be the
                  <a data-toggle="popover" data-trigger="hover" title="Guardian" data-content="A <em>legal guardian</em> is the person (or persons - often a couple) who will have the long term responsibility of a child and who has all the rights, powers and duties of a parent.">legal guardian</a>
                  of your 
                  <a data-toggle="popover" data-trigger="hover" title="Minor children" data-content="Your <em>minor children</em>, in reference to your will, are those children who will be less than 18 years old at the time of your passing.">minor children</a>
                  after your passing. </p>
                  <p>The appointment of a guardian usually only operates if the children have no surviving parent. Although it is possible to appoint a guardian who will assist the surviving parent.</p>
                  <p>A guardian does not have to be related to the children, but it is wise to select a guardian who you know well and who you trust will care for your children as you would do. It is wise to appoint either a couple or a single person to act as guardian.</p>
                  <p>Your chosen guardian needs to agree to the role before your will is finalised.</p>
                  <fieldset class="rowitem">
                    <legend>Primary Guardian Details</legend>
                    <div class="form-group">
                      <label for="guardianFirstname1">First Name:</label>
                      <input id="guardianFirstname1" name="guardian[1][firstname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['guardian'][1]['firstname']) ? $order['guardian'][1]['firstname'] : ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="guardianOthername1">Middle Names:</label>
                      <input id="guardianOthername1" name="guardian[1][othername]" type="text" class="form-control propercase name" value="<?php echo isset($order['guardian'][1]['othername']) ? $order['guardian'][1]['othername'] : ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="guardianSurname1">Last Name:</label>
                      <input id="guardianSurname1" name="guardian[1][surname]" type="text" class="form-control required propercase name" value="<?php echo isset($order['guardian'][1]['surname']) ? $order['guardian'][1]['surname'] : ''; ?>">
                      <input id="guardianName1" name="guardian[1][fullname]" class="fullname" type="hidden" value="<?php echo isset($order['guardian'][1]['surname']) && $order['guardian'][1]['surname'] > '' ? (isset($order['guardian'][1]['firstname']) ? $order['guardian'][1]['firstname'] . ' ' : '') . (isset($order['guardian'][1]['othername']) ? $order['guardian'][1]['othername'] . ' ' : '') . $order['guardian'][1]['surname'] : '';?>">
                    </div>
                    <div class="form-group">
                      <label for="guardianEmail1">Email address:</label>
                      <input id="guardianEmail1" name="guardian[1][email]" type="email" class="form-control email lowercase" value="<?php echo isset($order['guardian'][1]['email']) ? $order['guardian'][1]['email'] : ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="guardianPhone1">Phone:</label>
                      <input id="guardianPhone1" name="guardian[1][phone]" type="tel" class="form-control anyphone" value="<?php echo isset($order['guardian'][1]['phone']) ? $order['guardian'][1]['phone'] : ''; ?>">
                    </div>
                    <div class="address">
                      <div class="form-group">
                        <label for="guardianAddressA1">Street Address:</label>
                        <input id="guardianAddressA1" name="guardian[1][address1]" type="text" class="form-control required propercase notpobox address" value="<?php echo isset($order['guardian'][1]['address1']) ? $order['guardian'][1]['address1'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianAddressB1">Street Address Line 2 (optional):</label>
                        <input id="guardianAddressB1" name="guardian[1][address2]" type="text" class="form-control propercase notpobox address address2" value="<?php echo isset($order['guardian'][1]['address2']) ? $order['guardian'][1]['address2'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianCity1">City/Suburb:</label>
                        <input id="guardianCity1" name="guardian[1][city]" type="text" class="form-control required uppercase cityauto" value="<?php echo isset($order['guardian'][1]['city']) ? $order['guardian'][1]['city'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianState1">State:</label>
                        <input id="guardianState1" name="guardian[1][state]" type="text" class="form-control required uppercase state" value="<?php echo isset($order['guardian'][1]['state']) ? $order['guardian'][1]['state'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianPostcode1">Postcode:</label>
                        <input id="guardianPostcode1" name="guardian[1][postcode]" type="text" class="form-control required" value="<?php echo isset($order['guardian'][1]['postcode']) ? $order['guardian'][1]['postcode'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianCountry1">Country:</label>
                        <input id="guardianCountry1" name="guardian[1][country]" type="text" class="form-control required propercase country" value="<?php echo isset($order['guardian'][1]['country']) ? $order['guardian'][1]['country'] : ''; ?>">
                      </div>
                    </div>
                  </fieldset>
                  <fieldset id="alternateGuardianDetails" class="rowitem">
                    <legend>Alternate Guardian Details</legend>
                    <p>
                      It is a good idea to appoint an alternate legal guardian for your children in case your first choice is unavailable or unsuitable at the time of your death. The alternate 
                      guardian needs to agree to the role before your will is finalised.
                    </p>
                    <div class="form-group">
                      <label for="guardianFirstname2">First Name:</label>
                      <input id="guardianFirstname2" name="guardian[2][firstname]" type="text" class="form-control propercase name" value="<?php echo isset($order['guardian'][2]['firstname']) ? $order['guardian'][2]['firstname'] : ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="guardianOthername2">Middle Names:</label>
                      <input id="guardianOthername2" name="guardian[2][othername]" type="text" class="form-control propercase name" value="<?php echo isset($order['guardian'][2]['othername']) ? $order['guardian'][2]['othername'] : ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="guardianSurname2">Last Name:</label>
                      <input id="guardianSurname2" name="guardian[2][surname]" type="text" class="form-control propercase name  " value="<?php echo isset($order['guardian'][2]['surname']) ? $order['guardian'][2]['surname'] : ''; ?>">
                      <input id="guardianName2" name="guardian[2][fullname]" class="fullname" type="hidden" value="<?php echo isset($order['guardian'][2]['surname']) && $order['guardian'][2]['surname'] > '' ? (isset($order['guardian'][2]['firstname']) ? $order['guardian'][2]['firstname'] . ' ' : '') . (isset($order['guardian'][2]['othername']) ? $order['guardian'][2]['othername'] . ' ' : '') . $order['guardian'][2]['surname'] : '';?>">
                    </div>
                    <div class="form-group">
                      <label for="guardianEmail2">Email address:</label>
                      <input id="guardianEmail2" name="guardian[2][email]" type="email" class="form-control email lowercase" value="<?php echo isset($order['guardian'][2]['email']) ? $order['guardian'][2]['email'] : ''; ?>">
                    </div>
                    <div class="form-group">
                      <label for="guardianPhone2">Phone:</label>
                      <input id="guardianPhone2" name="guardian[2][phone]" type="tel" class="form-control anyphone" value="<?php echo isset($order['guardian'][2]['phone']) ? $order['guardian'][2]['phone'] : ''; ?>">
                    </div>
                    <div class="address">
                      <div class="form-group">
                        <label for="guardianAddressA2">Street Address:</label>
                        <input id="guardianAddressA2" name="guardian[2][address1]" type="text" class="form-control propercase notpobox address" value="<?php echo isset($order['guardian'][2]['address1']) ? $order['guardian'][2]['address1'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianAddressB2">Street Address Line 2 (optional):</label>
                        <input id="guardianAddressB2" name="guardian[2][address2]" type="text" class="form-control propercase notpobox address address2" value="<?php echo isset($order['guardian'][2]['address2']) ? $order['guardian'][2]['address2'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianCity2">City/Suburb:</label>
                        <input id="guardianCity2" name="guardian[2][city]" type="text" class="form-control uppercase cityauto" value="<?php echo isset($order['guardian'][2]['city']) ? $order['guardian'][2]['city'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianState2">State:</label>
                        <input id="guardianState2" name="guardian[2][state]" type="text" class="form-control uppercase state" value="<?php echo isset($order['guardian'][2]['state']) ? $order['guardian'][2]['state'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianPostcode2">Postcode:</label>
                        <input id="guardianPostcode2" name="guardian[2][postcode]" type="text" class="form-control" value="<?php echo isset($order['guardian'][2]['postcode']) ? $order['guardian'][2]['postcode'] : ''; ?>">
                      </div>
                      <div class="form-group">
                        <label for="guardianCountry2">Country:</label>
                        <input id="guardianCountry2" name="guardian[2][country]" type="text" class="form-control propercase country" value="<?php echo isset($order['guardian'][2]['country']) ? $order['guardian'][2]['country'] : ''; ?>">
                      </div>
                    </div>
                  </fieldset>
                </div>
              </div>
            </div>

<!-- Provision for Pets -->
            <div  class="panel panel-default" id="pets" style="display: <?php echo (isset($order['hasPets']) && $order['hasPets'] == 'Yes') ? 'block' : 'none';?>;">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#petInfo">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Provision for your Pets
                </h4>
              </div>
              <div id="petInfo" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>Your will can include information on how your pets should be cared for after you die.</p>
                  <p>You cannot leave a gift or a share of your estate to a pet. But, you can leave a gift or share of your estate to a designated pet carer.</p>
                  <p>This section allows you to provide details about your pets including instructions for care, who the the carer of your pet will be, and a cash gift to the carer to help them with the ongoing care of your pet.</p>
                  
                  <fieldset id="petDetails">
                    <legend>Pet Information</legend>
                    <div id="pet" class="rowitems" style='display: <?php echo $petCount ? 'block' : 'none';?>'>
<?php
for($i = 0; $i <= $petCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
?>
                      <div id="petRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="petName<?php echo $i; ?>">Pet's Name:</label>
                          <input id="petName<?php echo $i; ?>" name="pet[<?php echo $i; ?>][name]" type="text" class="form-control required propercase name" value="<?php echo isset($order['pet'][$i]['name']) ? $order['pet'][$i]['name'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="petType<?php echo $i; ?>"><a data-toggle="popover" data-trigger="hover" title="Type of Pet" data-content="<p>You should enter the type of animal or its breed here.</p><p>For example, 'cat', 'female dog', 'siamese cat', 'male black labrador', etc.</p>">Type/Breed of pet:</a></label>
                          <input id="petType<?php echo $i; ?>" name="pet[<?php echo $i; ?>][type]" type="text" class="form-control required lowercase" placeholder="Type or breed of pet, eg. dog, cat, black labrador, ..." value="<?php echo isset($order['pet'][$i]['type']) ? $order['pet'][$i]['type'] : ''; ?>">
                        </div>
                        <div class="form-group">
                          <label for="petDetails<?php echo $i; ?>"><a data-toggle="popover" data-trigger="hover" title="Pet Details" data-content="<p>Enter information about your pet that would be useful to the carer of your pet, such as age, dietary requirements, sleeping arrangements, veterinary details, and so on.</p>">Details of pet:</a> <small><a class="assist" assist="pet"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="petDetails<?php echo $i; ?>" name="pet[<?php echo $i; ?>][details]" rows="4" class="form-control" placeholder='Enter important information about your pet...' ><?php echo isset($order['pet'][$i]['details']) ? $order['pet'][$i]['details'] : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                          <label for="petCarer<?php echo $i; ?>"><a data-toggle="popover" data-trigger="hover" title="Pet Carer" data-content="<p>The <em>Pet Carer</em> is the person/organisation who will care for your pet.</p><p>You can select one of your previously entered beneficiaries, or an organisation that provides legacy pet care services in your state. In the latter case, you would need to provide a gift of cash or a share of your estate to the organisation.</p><p>If you select an individual carer, then you should dicuss this with them before giving them this responsibility.</p>">Pet Carer:</a></label>
                          <select id="petCarer<?php echo $i; ?>" name="pet[<?php echo $i; ?>][carerrecipient]" class="form-control petcarer required"><?php beneficiarySelect(isset($order['pet'][$i]['carerrecipient']) ? $order['pet'][$i]['carerrecipient'] : '', 'pet');?></select>
                        </div>
                        <div id="petcarerinfo<?php echo $i;?>" class="form-group"><?php echo isset($order['pet'][$i]['carerrecipient']) && is_numeric($order['pet'][$i]['carerrecipient']) ? $_SESSION['petcareorgs'][$order['pet'][$i]['carerrecipient']]['info'] : '';?></div>
                        <div class="form-group" id="petLegacyAmountDiv<?php echo $i; ?>" style="display: <?php echo isset($order['pet'][$i]['carerrecipient']) && $order['pet'][$i]['carerrecipient'] == 'Other' ? 'none' : 'block';?>;">
                          <label for="petLegacyAmount<?php echo $i; ?>"><a data-toggle="popover" data-trigger="hover" title="Amount to Leave for Pet Care" data-content="<p>This is the legacy (cash gift) that you wish to leave to your chosen <em>pet carer</em> to assist them with the ongoing care of your pet.</p><p>You may mention an amount here or leave the chosen pet carer a gift in the <em>Specific Gifts</em> or <em>Distribution of Estate</em> sections.</p>">Amount($) to leave to carer:</a></label>
                          <input id="petLegacyAmount<?php echo $i; ?>" name="pet[<?php echo $i; ?>][amount]" type="text" class="form-control legacy <?php echo isset($order['pet'][$i]['carerrecipient']) && (is_numeric($order['pet'][$i]['carerrecipient']) || substr($order['pet'][$i]['carerrecipient'], 0, 5) == 'group') ? 'required' : '';?>" value="<?php echo isset($order['pet'][$i]['amount']) ? $order['pet'][$i]['amount'] : ''; ?>">
                        </div>                        
                        <div class="form-group">
                          <label for="petCareDetails<?php echo $i; ?>">Care Details: <small><a class="assist" assist="petcare"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="petCareDetails<?php echo $i; ?>" name="pet[<?php echo $i; ?>][caredetails]" rows="4" class="form-control" placeholder="Details of care to be provided for your pet"><?php echo isset($order['pet'][$i]['caredetails']) ? $order['pet'][$i]['caredetails'] : ''; ?></textarea>
                        </div>                        
                        <button class="remove btn btn-sm btn-danger" id="petRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this pet</button>
                      </div>
<?php
}
?>
                    </div>
                    <button class="add btn btn-sm btn-success"><i class="glyphicon glyphicon-plus"></i> Add a pet</button>
                  </fieldset>
                  
                  
                </div>
              </div>
            </div>

<!-- Gifts -->
            <div  class="panel panel-default optional" id="gifts">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#giftsDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Specific Gifts
                </h4>
              </div>
              <div id="giftsDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>
                    In this section, you can detail specific gifts (<a data-toggle="popover" data-trigger="hover" title="Legacy" data-content="A <em>legacy</em> is a gift of money by will.">legacies</a>, 
                    <a data-toggle="popover" data-trigger="hover" title="Bequest" data-content="A <em>bequest</em> is typically a gift of personal property by will.">bequests</a>
                    or <a data-toggle="popover" data-trigger="hover" title="Devise" data-content="A <em>devise</em> is typically a gift of real property by will. For example, your house.">devises</a>) that you wish to provide to specific beneficiaries.
                  </p>
                  <p>
                    These gifts may be conditional in nature (for example, Mary will receive my house providing she is married and has children). 
                    You also need to consider whether the gift will be given to another beneficiary if the selected beneficiary pre-deceases you.
                  </p>
                  <fieldset id="legacyDetails">
                    <legend>Gifts of Money (Legacies)</legend>
                    <div id="legacy" class="rowitems" style='display: <?php echo $legacyCount ? 'block' : 'none';?>'>
<?php
# display the legacy rows that are already entered if any
for($i = 0; $i <= $legacyCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
  # but only if the recipient still exists - it may have been deleted so delete the gift
  if ($i == 0 || $beneficiaries[$order['legacy'][$i]['recipient']]) {
    $altRequired = isset($order['legacy'][$i]['altrecipient']) && $order['legacy'][$i]['altrecipient'] ==  'Other' ? ' required' : '' 
?>
                      <fieldset id="legacyRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="legacyAmount<?php echo $i; ?>"><a data-toggle="popover" data-trigger="hover" title="Legacy Amount" data-content="Details of gifts of money that should be left to the recipient.">Amount ($):</a></label>
                          <input id="legacyAmount<?php echo $i; ?>" name="legacy[<?php echo $i; ?>][amount]" type="text" class="form-control legacy required" value="<?php echo isset($order['legacy'][$i]['amount']) ? $order['legacy'][$i]['amount'] : ''; ?>">
                        </div>
                        <div class="form-group">                        
                          <label for="legacyRecipient<?php echo $i; ?>">Beneficiary:</label>
                          <select id="legacyRecipient<?php echo $i; ?>" name='legacy[<?php echo $i; ?>][recipient]' class='form-control recipient required'><?php beneficiarySelect(isset($order['legacy'][$i]['recipient']) ? $order['legacy'][$i]['recipient'] : '');?></select>
                        </div>
                        <div class="form-group">                        
                          <label for="legacyAltRecipient<?php echo $i; ?>"><a data-toggle="popover" data-trigger="hover" title="Alternate Beneficiary" data-content="The <em>Alternate Beneficiary</em> is the person/organisation who will receive a nominated share of the estate if the primary beneficiary dies before (or around the same time as) the testator.">Alternate Beneficiary:</a></label>
                          <select id="legacyAltRecipient<?php echo $i; ?>" name='legacy[<?php echo $i; ?>][altrecipient]' class='form-control altrecipient required'><?php beneficiarySelect(isset($order['legacy'][$i]['altrecipient']) ? $order['legacy'][$i]['altrecipient'] : '', 'retain');?></select>
                        </div>
                        <div class="form-group">
                          <label for="legacyCondition<?php echo $i; ?>">Conditions: <small><a class="assist" assist="legacy"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="legacyCondition<?php echo $i; ?>" name='legacy[<?php echo $i; ?>][condition]' class="form-control<?php echo $altRequired;?>" placeholder='This legacy is conditional upon...' ><?php echo isset($order['legacy'][$i]['condition']) ? $order['legacy'][$i]['condition'] : ''; ?></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm remove" id="legacyRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this gift</button>
                      </fieldset>
<?php
  }
}
?>
                    </div>
                    <button class="btn btn-success btn-sm add"><i class="glyphicon glyphicon-plus"></i> Add a gift of money</button>
                  </fieldset>

                  <fieldset id="bequestDetails">
                    <legend>Gifts of Personal Property (Bequests)</legend>
                    <div id="bequest" class="rowitems" style='display: <?php echo $bequestCount ? 'block' : 'none';?>'>
<?php
# display the bequest rows that are already entered if any
for($i = 0; $i <= $bequestCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
  # but only if the recipient still exists - it may have been deleted so delete the gift
  if ($i == 0 || $beneficiaries[$order['bequest'][$i]['recipient']]) {
    $altRequired = isset($order['bequest'][$i]['altrecipient']) && $order['bequest'][$i]['altrecipient'] ==  'Other' ? ' required' : '' 
?>
                      <fieldset id="bequestRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="bequestProperty<?php echo $i; ?>">Item Details:</label>
                          <textarea id="bequestProperty<?php echo $i; ?>" name='bequest[<?php echo $i; ?>][property]' class='form-control required address'><?php echo isset($order['bequest'][$i]['property']) ? $order['bequest'][$i]['property'] : ''; ?></textarea><br>
                        </div>
                        <div class="form-group">
                          <label for="bequestRecipient<?php echo $i; ?>">Beneficiary:</label>
                          <select id="bequestRecipient<?php echo $i; ?>" name='bequest[<?php echo $i; ?>][recipient]' class='form-control recipient required'><?php beneficiarySelect(isset($order['bequest'][$i]['recipient']) ? $order['bequest'][$i]['recipient'] : '');?></select>
                        </div>
                        <div class="form-group">
                          <label for="bequestAltRecipient<?php echo $i; ?>"><a data-toggle="popover" data-trigger="hover" title="Alternate Beneficiary" data-content="The <em>Alternate Beneficiary</em> is the person/organisation who will receive a nominated share of the estate if the primary beneficiary dies before (or around the same time as) the testator.">Alternate Beneficiary:</a></label>
                          <select id="bequestAltRecipient<?php echo $i; ?>" name='bequest[<?php echo $i; ?>][altrecipient]' class='form-control altrecipient required'><?php beneficiarySelect(isset($order['bequest'][$i]['altrecipient']) ? $order['bequest'][$i]['altrecipient'] : '', 'retain');?></select>
                        </div>
                        <div class="form-group">
                          <label for="bequestCondition<?php echo $i; ?>">Conditions: <small><a class="assist" assist="bequest"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="bequestCondition<?php echo $i; ?>" name='bequest[<?php echo $i; ?>][condition]' class="form-control<?php echo $altRequired;?>" placeholder='This bequest is conditional upon...'><?php echo isset($order['bequest'][$i]['condition']) ? $order['bequest'][$i]['condition'] : ''; ?></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm remove" id="bequestRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this gift</button>
                      </fieldset>
<?php
  }
}
?>
                    </div>
                    <button class="btn btn-success btn-sm add"><i class="glyphicon glyphicon-plus"></i> Add a gift of personal property</button>
                  </fieldset>

                  <fieldset id="deviseDetails">
                    <legend>Gifts of Real Estate (Devises)</legend>
                    <div id="devise" class="rowitems" style='display: <?php echo $deviseCount ? 'block' : 'none';?>'>
<?php
# display the devise rows that are already entered if any
for($i = 0; $i <= $deviseCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
  # but only if the recipient still exists - it may have been deleted so delete the gift
  if ($i == 0 || $beneficiaries[$order['devise'][$i]['recipient']]) {
    $altRequired = isset($order['devise'][$i]['altrecipient']) && $order['devise'][$i]['altrecipient'] ==  'Other' ? ' required' : '' 
?>
                      <fieldset id="deviseRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="deviseProperty<?php echo $i; ?>">Property Address:</label>
                          <textarea id="deviseProperty<?php echo $i; ?>" name='devise[<?php echo $i; ?>][property]' class='form-control required address propercase' placeholder="Enter property address here..."><?php echo isset($order['devise'][$i]['property']) ? $order['devise'][$i]['property'] : ''; ?></textarea><br>
                        </div>
                        <div class="form-group">
                          <label for="deviseRecipient<?php echo $i; ?>">Beneficiary:</label>
                          <select id="deviseRecipient<?php echo $i; ?>" name='devise[<?php echo $i; ?>][recipient]' class='form-control recipient required'><?php beneficiarySelect(isset($order['devise'][$i]['recipient']) ? $order['devise'][$i]['recipient'] : '');?></select><br>
                        </div>
                        <div class="form-group">
                          <label for="deviseAltRecipient<?php echo $i; ?>"><a data-toggle="popover" data-trigger="hover" title="Alternate Beneficiary" data-content="The <em>Alternate Beneficiary</em> is the person/organisation who will receive a nominated share of the estate if the primary beneficiary dies before (or around the same time as) the testator.">Alternate Beneficiary:</a></label>
                          <select id="deviseAltRecipient<?php echo $i; ?>" name='devise[<?php echo $i; ?>][altrecipient]' class='form-control altrecipient required'><?php beneficiarySelect(isset($order['devise'][$i]['altrecipient']) ? $order['devise'][$i]['altrecipient'] : '', 'retain');?></select><br>
                        </div>
                        <div class="form-group">
                          <label for="deviseCondition<?php echo $i; ?>">Conditions: <small><a class="assist" assist="devise"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="deviseCondition<?php echo $i; ?>" name='devise[<?php echo $i; ?>][condition]' class="form-control<?php echo $altRequired;?>" placeholder='This devise is conditional upon...'><?php echo isset($order['devise'][$i]['condition']) ? $order['devise'][$i]['condition'] : ''; ?></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm remove" id="deviseRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this gift</button>
                      </fieldset>
<?php
  }
}
?>
                    </div>
                    <button class="btn btn-success btn-sm add"><i class="glyphicon glyphicon-plus"></i> Add a gift of real estate</button>
                  </fieldset>
                </div>
              </div>
            </div>

<!-- Estate -->
            <div  class="panel panel-default" id="estateshares">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#estatesharesDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Distribution of Estate
                </h4>
              </div>
              <div id="estatesharesDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>
                    This section is all about dividing up the
                    <a data-toggle="popover" data-trigger="hover" title="Remainder of Estate" data-content="The remainder of your estate is known as the <em>residuary</em> in your will. It is what is left of your estate after any taxes and debts are paid, and after any specific gifts are distributed.">remainder</a>
                    of your estate. Once your debts and liabilities are taken care of, and your <em>specific gifts</em> are distributed, the remainder of your estate will be divided
                    among your selected beneficiaries.
                  </p>
                  <p>
                    As you add beneficiaries to this page, the proportion of your estate to be divided will automatically be kept
                    at 100%. You may vary these percentages as you require. It is important that 100% of your remaining estate is
                    distributed.
                  </p>
                  <p>
                    Select a beneficiary and the proportion that they should receive. In the event that the
                    selected beneficiary dies before you, an alternate distribution of their proportion is important. For example, you may
                    wish that the share be distributed equally to the remaining selected beneficiaries, or even that it all be distributed to
                    a specific beneficiary.
                  </p>
                  <p class="alert alert-info onlywithchildren">
                    A very common scenario is for a testator to leave <strong>100% of the remainder of their estate to their partner</strong>, and, if their partner dies before or around the same time as them,
                    they then wish their estate to be divided equally among their surviving children. This can be accomplished very simple by allocating one (1) share of the estate - 100% to their
                    partner, and then selecting, as the Alternate Beneficiary - <em>'Divide equally to testator's children'</em>.
                  </p>
                  <p class="alert alert-info">
                    If you need more flexibility with specifying an <strong>Alternate Beneficiary</strong>, then select the <strong><em>'Specified in Conditions field below'</em></strong>
                    option, and then enter explicitly what you would like to occur if the primary beneficiary dies before the testator. There are some example condition clauses which you may use and modify.
                  </p>
                  <fieldset id="estateDetails" class="noborders">
                    <legend>Share of Estate</legend>
                    <div id="estate" class="rowitems" style='display: <?php echo $estateCount ? 'block' : 'none';?>'>
<?php
# display the rows that are already entered if any
for($i = 0; $i <= $estateCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
  # but only if the recipient still exists - it may have been deleted so delete the estate row
  if ($i == 0 || $beneficiaries[$order['estate'][$i]['recipient']]) {
    $altRequired = isset($order['estate'][$i]['altrecipient']) && $order['estate'][$i]['altrecipient'] ==  'Other' ? ' required' : '' 
?>
                      <fieldset id="estateRow<?php echo $i;?>" class="rowitem estate" style="display: <?php echo $display;?>;">
                        <legend>Share of Estate <span><?php echo $i;?></span></legend>
                        <div class="form-group">
                          <label for="estateRecipient<?php echo $i; ?>">Beneficiary:</label>
                          <select id="estateRecipient<?php echo $i; ?>" name='estate[<?php echo $i; ?>][recipient]' class='form-control recipient required'><?php beneficiarySelect(isset($order['estate'][$i]['recipient']) ? $order['estate'][$i]['recipient'] : '');?></select>
                        </div>
                        <div class="form-group">
                          <label for="estateShare<?php echo $i; ?>">Share of estate:</label>
                          <input id="estateShare<?php echo $i; ?>" name='estate[<?php echo $i; ?>][share]' type="text" class='form-control estate required' value='<?php echo isset($order['estate'][$i]['share']) ? $order['estate'][$i]['share'] : ''; ?>'>
                        </div>
                        <div class="form-group">
                          <label for="estateAltRecipient<?php echo $i; ?>"><a data-toggle="popover" data-trigger="hover" title="Alternate Beneficiary" data-content="The <em>Alternate Beneficiary</em> is the person/organisation who will receive a nominated share of the estate if the primary beneficiary dies before (or around the same time as) the testator.">Alternate Beneficiary:</a></label>
                          <select id="estateAltRecipient<?php echo $i; ?>" name='estate[<?php echo $i; ?>][altrecipient]' class='form-control altrecipient required'><?php beneficiarySelect(isset($order['estate'][$i]['altrecipient']) ? $order['estate'][$i]['altrecipient'] : '', 'divide');?></select>
                        </div>
                        <div class="form-group">
                          <label for="estateCondition<?php echo $i; ?>">Conditions: <small><a class="assist" assist="estate"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="estateCondition<?php echo $i; ?>" rows="4" name='estate[<?php echo $i; ?>][condition]' class="form-control<?php echo $altRequired;?>" placeholder='Whatever you type here will be included &lsquo;as is&rsquo; in the appropriate distribution clause....'><?php echo isset($order['estate'][$i]['condition']) ? $order['estate'][$i]['condition'] : ''; ?></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm remove" id="estateRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this share</button>
                      </fieldset>
<?php
  }
}
?>
                    </div>
                    <p id='percentAllocated' class='alert'><strong>Percentage of estate allocated to benficiaries is <span id="percentAllocatedAmount">0%</span></strong>
                    <span id="allocWarning" class="text-danger">&larr; Before continuing, this value must be 100%</span></p>
                    <button class="btn btn-success btn-sm add"><i class="glyphicon glyphicon-plus"></i> Add a Share of Estate</button>

                    <fieldset id="wipeoutClauseSection" class="noborders" style="padding-top: 30px;">
                      <div class="form-group">
                        <label for="wipeoutClause">
                          <a data-toggle="popover" data-trigger="hover" title="Wipeout Clause" data-content="A <em>wipeout clause</em> dictates what should happen to your estate if all of your named beneficiaries die before you do.</p><p>If you die and all your named beneficiaries have died before you, then the state government will decide how your estate is to be distributed according to their rules in force at the time of your death.">
                            Wipeout Clause:
                          </a>
                          <small>(optional) <a class="assist" assist="wipeout"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small>
                        </label>
                        <textarea id="wipeoutClause" rows="4" name='wipeoutClause' class="form-control" placeholder='If all of your beneficiaries pre-decease you, how would you like your estate distributed?'><?php echo isset($order['wipeoutClause']) ? $order['wipeoutClause'] : ''; ?></textarea>
                      </div>
                    </fieldset>

                    <fieldset id="finalClauseSection" class="noborders" style="padding-top: 30px;">
                      <div class="form-group">
                        <label for="finalClause">
                          <a data-toggle="popover" data-trigger="hover" title="Final Clause" data-content="<p>A <em>final clause</em> can be added as the last clause in your Will.</p><p>It can contain anything of a general nature relating to your estate or affairs.</p>">
                            Final Clause:
                          </a>
                          <small>(optional)</small>
                        </label>
                        <textarea id="finalClause" rows="4" name='finalClause' class="form-control" placeholder='Optional final clause...'><?php echo isset($order['finalClause']) ? $order['finalClause'] : ''; ?></textarea>
                      </div>
                    </fieldset>

                  </fieldset>
                </div>
              </div>
            </div>

<!-- Requirements -->
            <div  class="panel panel-default optional" id="requirements">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#requirementsDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Requirements
                </h4>
              </div>
              <div id="requirementsDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>
                    This section gives you space to let your executor and beneficiaries know of personal
                    requirements you may have, such as funeral arrangements and organ donation wishes. You 
                    may also have general requirements, for example, a song that you would like played at
                    your funeral ceremony.
                  </p>
                  <fieldset>
                    <legend>Personal Requirements</legend>
                    <div id="requirement" class="rowitems" style='display: <?php echo $requirementCount ? 'block' : 'none';?>'>
<?php
# display the rows that are already entered if any
for($i = 0; $i <= $requirementCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
?>
                      <fieldset id="requirementRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="requirementDetail<?php echo $i; ?>"><small><a class="assist" assist="requirement"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="requirementDetail<?php echo $i; ?>" name='requirement[<?php echo $i; ?>][text]' class="form-control" placeholder='Enter your personal requirement details…'><?php echo isset($order['requirement'][$i]['text']) ? $order['requirement'][$i]['text'] : ''; ?></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm remove" id="requirementRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this requirement</button>
                      </fieldset>
<?php
}
?>
                    </div>
                    <button class="btn btn-success btn-sm add"><i class="glyphicon glyphicon-plus"></i> Add a requirement</button>
                  </fieldset>
                </div>
              </div>
            </div>

<!-- Messages -->
            <div  class="panel panel-default optional" id="messages">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#messagesDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Messages
                </h4>
              </div>
              <div id="messagesDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>
                    In this section, please enter details of any 
                    personal messages you would like delivered to your beneficiaries after your death.
                  </p>
                  <fieldset>
                    <legend>Messages to Beneficiaries</legend>
                    <div id="message" class="rowitems" style='display: <?php echo $messageCount ? 'block' : 'none';?>'>
<?php
# display the rows that are already entered if any
for($i = 0; $i <= $messageCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
  # but only if the recipient still exists - it may have been deleted so delete the message row
  if ($i == 0 || $beneficiaries[$order['message'][$i]['recipient']]) {
?>
                      <fieldset id="messageRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">                      
                        <div class="form-group">
                          <label for="messageRecipient<?php echo $i; ?>">Message To:</label>
                          <select id="messageRecipient<?php echo $i; ?>" name='message[<?php echo $i; ?>][recipient]' class='form-control recipient'><?php beneficiarySelect(isset($order['message'][$i]['recipient']) ? $order['message'][$i]['recipient'] : '');?></select>
                        </div>
                        <div class="form-group">
                          <textarea id="messageDetail<?php echo $i; ?>" name='message[<?php echo $i; ?>][text]' class="form-control" placeholder='Enter your personal message'><?php echo isset($order['message'][$i]['text']) ? $order['message'][$i]['text'] : ''; ?></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm remove" id="messageRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this message</button>
                      </fieldset>
                      
<?php
  }
}
?>
                    </div>
                    <br>
                    <button class="btn btn-success btn-sm add"><i class="glyphicon glyphicon-plus"></i> Add a message</button>
                  </fieldset>
                </div>
              </div>
            </div>

<!-- Information -->
            <div  class="panel panel-default optional" id="information">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#informationDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Info, Assets and Liabilities
                </h4>
              </div>
              <div id="informationDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>
                    This section allows you to enter optional information that is not included in your will, but which would
                    be included in a <a data-toggle="popover" data-trigger="hover" title="Executor&apos;s Memorandum" data-content="<p>The <em>executor&apos;s memorandum</em> is a document that details information such as beneficiaries&apos; contact details, guardian details and any personal information you enter that is relevant to the executor&apos;s job.</p><p>It will be automatically created along with your will.</p>">executor&apos;s memorandum</a> document that should be kept with your will.
                  </p>
                  <p>
                    This information will assist your Executor/s with administering your estate and carrying out the requirements 
                    of your will after your death.
                  </p>
                  <p>
                    Please note that this information (should you choose to provide it), along with the other information 
                    you have entered, is all kept in an encrypted form in our system, and is not visible to others.
                  </p>
                  <p>
                    In the <em>assets section</em>, please enter details of any significant assets you have that will be part of your estate,
                    including superannuation investments, other financial investments, properties, and significant value items such as vehicles,
                    boats, or jewellery.
                  </p>
                  <p>
                    In the <em>liabilities section</em>, please enter details of any liabilities you have including loans,
                    debts and anything that will need to be repaid or deducted from your estate after your death.
                    Include information such as mortgage loans, personal loans, tax debts, and other debts.
                  </p>
                  <fieldset>
                    <legend>Personal Information</legend>
                    <div id="info" class="rowitems" style='display: <?php echo $infoCount ? 'block' : 'none';?>'>
<?php
# display the rows that are already entered if any
for($i = 0; $i <= $infoCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
?>
                      <fieldset id="infoRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="infoDetail<?php echo $i; ?>"><small><a class="assist" assist="info"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="infoDetail<?php echo $i; ?>" name='info[<?php echo $i; ?>][text]' class='form-control' rows="6" placeholder='Enter personal information details…'><?php echo isset($order['info'][$i]['text']) ? $order['info'][$i]['text'] : ''; ?></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm remove" id="infoRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this info</button>
                      </fieldset>
<?php
}
?>
                    </div>
                    <button class="btn btn-success btn-sm add"><i class="glyphicon glyphicon-plus"></i> Add info</button>
                  </fieldset>
                  <fieldset id="assetDetails">
                    <legend>Personal Assets</legend>
                    <div id="asset" class="rowitems" style='display: <?php echo $assetCount ? 'block' : 'none';?>'>
<?php
# display the rows that are already entered if any
for($i = 0; $i <= $assetCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
?>
                      <fieldset id="assetRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="assetDetail<?php echo $i; ?>"><small><a class="assist" assist="asset"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="assetDetail<?php echo $i; ?>" name='asset[<?php echo $i; ?>][text]' class='form-control' rows="6" placeholder='Enter asset details…'><?php echo isset($order['asset'][$i]['text']) ? $order['asset'][$i]['text'] : ''; ?></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm remove" id="assetRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this asset</button>
                      </fieldset>
<?php
}
?>
                    </div>
                    <button class="btn btn-success btn-sm add"><i class="glyphicon glyphicon-plus"></i> Add asset</button>
                  </fieldset>
                  <fieldset id="liabilityDetails">
                    <legend>Personal Liabilities</legend>
                    <div id="liability" class="rowitems" style='display: <?php echo $liabilityCount ? 'block' : 'none';?>'>
<?php
# display the rows that are already entered if any
for($i = 0; $i <= $liabilityCount; $i++) {
  $display = $i == 0 ? 'none' : 'block';
?>
                      <fieldset id="liabilityRow<?php echo $i;?>" class="rowitem" style="display: <?php echo $display;?>;">
                        <div class="form-group">
                          <label for="liabilityDetail<?php echo $i; ?>"><small><a class="assist" assist="liability"><i class="glyphicon glyphicon-circle-arrow-right"></i> See Examples</a></small></label>
                          <textarea id="liabilityDetail<?php echo $i; ?>" name='liability[<?php echo $i; ?>][text]' class='form-control' rows="6" placeholder='Enter liability details…'><?php echo isset($order['liability'][$i]['text']) ? $order['liability'][$i]['text'] : ''; ?></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm remove" id="liabilityRemove<?php echo $i; ?>"><i class="glyphicon glyphicon-trash"></i> Remove this liability</button>
                      </fieldset>
<?php
}
?>
                    </div>
                    <button class="btn btn-success btn-sm add"><i class="glyphicon glyphicon-plus"></i> Add liability</button>
                  </fieldset>
                </div>
              </div>
            </div>

<!-- Mirror Will -->            
<?php
if (!$_SESSION['orderpaid']) {
  // only display if the order is unpaid
?>
            <div  class="panel panel-default optional" id="mirrorwill" style="display: <?php echo (isset($order['hasSpouse']) && $order['hasSpouse'] == 'Yes') ? 'block' : 'none';?>;">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#mirrorwillDetails">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Mirror Will
                </h4>
              </div>
              <div id="mirrorwillDetails" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>
                    Create a will for your partner that <strong><em>mirrors</em></strong> yours
                    and pay an additional <strong><em><?php echo currency($_SESSION['products']['mirrorwill']['priceinctax']);?></em></strong>.
                    It will be created automatically from the information you&apos;ve already entered when you complete your purchase.
                    Once created, you may edit your partners will as required before downloading it.
                  </p>
                  <div class="form-group">
                    <label for="mirrorwill" style="min-width: 260px;">
                      Would you like to include a Mirror Will for your partner?
                    </label>
                    <input id="mirrorwill" name="mirrorwill" type="hidden" class="required" value="<?php echo isset($order['mirrorwill']) ? $order['mirrorwill'] : '';?>">
                    <br>
                    <div class="btn-group">
                      <button id="mirrorwillYes" class="btn btn-default <?php if (isset($order['mirrorwill']) && $order['mirrorwill'] == 'Yes') echo 'active'; ?>" >Yes</button>
                      <button id="mirrorwillNo" class="btn btn-default <?php if (isset($order['mirrorwill']) && $order['mirrorwill'] == 'No') echo 'active'; ?>" >No</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
<?php
}
?>

<!-- EPoA -->
            <div  class="panel panel-default optional" id="enduring">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#epoaInfo">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h4 class="panel-title">
                  Enduring Power of Attorney and Enduring Guardianship
                </h4>
              </div>
              <div id="epoaInfo" class="panel-collapse collapse">
                <div class="panel-body">
                  <p>
                    An <a data-toggle="popover" data-trigger="hover" title="Enduring Power of Attorney" data-content="
                    <p>An <strong>enduring power of attorney</strong> is a document which grants authority for another to act on a persons behalf, in relation to financial and property decisions, if the person loses the ability to make sound decisions due to reduced mental capacity.</p>
                    "><strong>enduring power of attorney</strong></a> is where you appoint a person to make financial and legal decisions for you if you lose the capacity to make your own decisions.
                  </p>
                  <p>
                    An <a data-toggle="popover" data-trigger="hover" title="Enduring Guardianship" data-content="
                    <p>An <strong>enduring guardianship</strong> is a document which grants authority for another to act on a persons behalf, in relation to personal and health care decisions, if the person loses the ability to make sound decisions due to reduced mental capacity.</p>
                    "><strong>enduring guardianship</strong></a> is where you appoint a person to make personal decisions (including health care, residence decisions) for you if you lose the capacity to make your own decisions.
                  </p>
                  <p>
                    Adding enduring power of attorney and guardianship documents <span class='epoapartner' style='display: <?php echo (isset($order['product']['mirrorwill']) && $order['product']['mirrorwill'] == 'Yes') ? 'inline' : 'none';?>'>
                    for you and your partner </span>
                    <?php if ($_SESSION['products']['enduring']['priceinctax'] == 0):?> is <strong>FREE</strong><?php else:?>will only cost an additional <?php echo currency($_SESSION['products']['enduring']['priceinctax']);?><?php endif;?>
                    when ordered with a will.
                  </p>
                  <div class="form-group">
                    <label for="enduring" style="min-width: 260px;">
                      Would you like to include Enduring Power of Attorney and Guardianship documents<span class='epoapartner' style='display: <?php echo (isset($order['product']['mirrorwill']) && $order['product']['mirrorwill'] == 'Yes') ? 'inline' : 'none';?>'> for you and your partner</span>?
                    </label>
                    <input id="enduring" name="enduring" type="hidden" class="required" value="<?php echo !isset($order['enduring']) ? 'No' : $order['enduring'];?>">
                    <br>
                    <div class="btn-group">
                      <button id="enduringYes" class="btn btn-default <?php if (isset($order['enduring']) && $order['enduring'] == 'Yes') echo 'active'; ?>" >Yes</button>
                      <button id="enduringNo" class="btn btn-default <?php if (!isset($order['enduring']) || $order['enduring'] == 'No') echo 'active'; ?>" >No</button>
                    </div>
                  </div>
                  <div id="epoaDetails" style="display: <?php echo (!isset($order['enduring']) || $order['enduring'] == 'No') ? 'none' : 'block';?>;">
                    <div class="form-group">
                      <label for="epoaState"><a data-toggle="popover" data-trigger="hover" title="Enduring Power of Attorney" data-content="<p><em>Enduring Power of Attorney and Guardianship</em> documents are different in each state and territory.</p><p>The state selected will default to your state, but if you want it created for a different state, you may do so here.</p>">For which state should the enduring power of attorney and guardianship documents be created?</a></label>
                      <input id="epoaState" name="epoaState" type="text" class="form-control required uppercase state" value="<?php echo isset($order['epoaState']) && $order['epoaState'] > '' ? $order['epoaState'] : (isset($order['person']['state']) ? $order['person']['state'] : ''); ?>">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
<!-- Download or Purchase-->
            <div  class="panel panel-default" id="next">
              <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#nextSteps">
                <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
                <h5 class="panel-title">
                  <?php echo ($_SESSION['orderpaid']) ? 'Download and Sign' : 'Purchase';?> Your Will
                </h5>
              </div>
              <div id="nextSteps" class="panel-collapse collapse">
                <div class="panel-body">
<?php if ($_SESSION['orderpaid']) {?>
                  <a href='getdocuments.html' class='downloadOrder btn btn-success'><i class='glyphicon glyphicon-cloud-download'></i> Download Your Will</a>
<?php } else {?>
                  <p>
                    If you have completed all the required information in the sections above, then you may order and download the Will.
                  </p>
                  <a href='order.html' class='btn btn-warning'><i class='glyphicon glyphicon-shopping-cart'></i> Purchase Your Will</a>
<?php } ?>
                  <br> <br>
                  <p><strong><em>Once you have downloaded and printed the will, it needs to be signed by the testator (the will maker) in front of two witnesses who must also sign it.</em></strong></p>
                </div>
              </div>
            </div>

<!-- End of accordion panel group div -->          
          </div> 
          <div id="formButtons" style="display: block; margin-top: 20px;">
<?php
if ($_SESSION['orderpaid'] == 0) {
?>
            <a href="" id="previewOrder" class="btn btn-info"><i class='glyphicon glyphicon-search'></i> Preview Your Will</a>
<?php
}
?>
            <button id="saveButton" class="btn btn-default"><?php echo SAVEBUTTON;?></button>
          </div>
          <br class="clear">
        </form>
      </section>
<?php
# create assistance example clauses if they exist in the database
sampleClauses('legacy');
sampleClauses('bequest');
sampleClauses('devise');
sampleClauses('estate');
sampleClauses('requirement');
sampleClauses('message');
sampleClauses('info');
sampleClauses('asset');
sampleClauses('liability');
sampleClauses('trust');
sampleClauses('wipeout');
sampleClauses('pet');
sampleClauses('petcare');

require("pageincludes/footer.php");
?>