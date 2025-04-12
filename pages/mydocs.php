<?php
/*

Home page for logged on user

*/
if (!isset($_SESSION['userid'])) goHome(); # go to home page if not logged in

# if the logged on user is an organisation, then redirect to affiliateprogram.html

if ($_SESSION['organisation']) {
  echo "<script>location = '/affiliateprogram.html';</script>";
}


# unset data related to a specific order
unset($_SESSION['orderData']);
unset($_SESSION['orderid']);
unset($_SESSION['orderextaxamount']);
unset($_SESSION['orderdiscountamount']);
unset($_SESSION['orderpaid']);
unset($_SESSION['invoice']);
unset($_SESSION['ordercount']);
unset($_SESSION['product']);
unset($_SESSION['productid']);
unset($_SESSION['productname']);
unset($_SESSION['template']);

# get the order details and display a DIV for each order
# get order history details if exist
$userid = $_SESSION['userid'];
$query = "
SELECT
  o.id,
  o.createdate,
  o.modifydate,
  AES_DECRYPT(o.orderdata, u.affiliatecode) as orderdata,
  o.productid,
  p.name as productname,
  p.shortname,
  o.paid,
  o.paiddate,
  o.paidtxnid,
  o.invoice,
  o.mirrorof
FROM orders o JOIN product p ON (p.id = o.productid) JOIN users u ON (u.id = o.userid)
WHERE o.userid = $userid
ORDER BY o.id;
";
$result = $dbh->query($query);
if ($result) $orders = $result->fetchAll();
$_SESSION['ordercount']['all'] = count($orders);
$paidorders = false; # updated down below

# display page
$title = "Create Your Will - My Will Online";
$desc = "Australia's simplest to use and popular online will creation website. Create wills, enduring guardianship and enduring power of attorney documents quickly and simply on your computer, tablet or smartphone.";
$keywords = "last will and testament, online will, online wills, internet wills, online will kit";

require("pageincludes/header.php");
?>
      <section class="container">
        <input type="hidden" name="userid" value="<?php echo $_SESSION['userid'];?>">
<?php
# if the user (presumably new) has no orders then give them a heads up
if ($_SESSION['ordercount']['all'] == 0) {
?>
        <div style="max-width: 800px;">
          <h1>Welcome to <?php echo $_SESSION['sitename'];?></h1>
          
          <p class="large">
            Hi <?php echo $_SESSION['firstname'];?>,
          </p>
          <p>
            Thanks for choosing us to create your Last Will and Testament.
          </p>
          <p>
            Your Will is an important document. The following screen will guide you through the process of creating it.
            There are a number of sections (expandable by clicking on the grey buttons) that you should complete in the order they appear.
          </p>
          <p>
          	If you have any questions during the process, please contact us (using the <a href="contact.html" alt="Contact Us">Contact page</a>). 
          </p>
        <div id="continue">
          <a product="singlewill" class="createNewOrder btn btn-primary">Continue with creating your Last Will and Testament <i class="glyphicon glyphicon-chevron-right"></i></a>
        </div>
      </section>
      
<?php
} else {
?>
        <div>
          <h1>My Documents</h1>
          <p>
            You may <span class='text-danger'>Edit</span> and <span class='text-danger'>Delete</span>
            documents that you have not yet purchased by clicking the buttons to the right of the document name.
          </p>
          <p>
            For purchased documents, you may <span class='text-danger'>Edit</span>
            them if you need to update information, and you may
            <span class='text-danger'>Download</span> them to your <?php echo $_SESSION['deviceType'];?> for printing and signing.
          </p>
          <br>
          <div id='orderlist' class="panel panel-default" style="display: <?php echo $_SESSION['ordercount']['all'] > 0 ? 'block' : 'none';?>">
            <div class="panel-heading"><strong>Documents for <?php echo $_SESSION['firstname'] . ' ' . $_SESSION['surname'];?></strong></div>
            <ul class="list-group">
<?php
  foreach ($orders as $order) {
    $product = $order['shortname'];
    $paidorders = $order['paid'] == 1 ? true : $paidorders; # flag used to show social links
    isset($_SESSION['ordercount'][$product]) ? $_SESSION['ordercount'][$product]++ : $_SESSION['ordercount'][$product] = 0; // count for each type of product order
    $orderData = unserialize(stripslashes($order['orderdata']));
    if ($order['paid'] == 0) {
      $orderStatus = "IN PROGRESS";
      $paiddate = '';
    } else {
    	$paiddate = $order['paiddate']; # datetime when last paid
      $expirydate = strtotime($paiddate . ' +' . PRODUCTEXPIRY . ' month');
      if (time() > $expirydate) {
      	$orderStatus = 'EXPIRED';
      } else {
        # for paid orders we need to check that there is a matching mirrorwill order if a mirrorwill was requested and create it if it does not exist      
        if (isset($orderData['product']['mirrorwill']) && $orderData['product']['mirrorwill'] == 'Yes') {
          # this will has mirrorwill requested
          if (!$order['mirrorof']) {
            # use the data in $orderData to create a copy of the order swapping the person and spouse arrays around
            $newOrderData = $orderData; # this is a copy not a reference
            $temp = $newOrderData['person'];
            $newOrderData['person'] = $newOrderData['spouse'];
            $newOrderData['spouse'] = $temp;
            $newOrderData['spouse']['relationship'] = 'partner';
            if ($orderData['partnerExecutor'] == 'Yes') $newOrderData['executor'][1] = $temp;
            if ($orderData['partnerAttorney'] == 'Yes') $newOrderData['attorney'][1] = $temp;
            # clear the requirements, info, messages, assets and liabilities for the mirror will
            unset($newOrderData['requirement']);
            unset($newOrderData['message']);
            unset($newOrderData['info']);
            unset($newOrderData['asset']);
            unset($newOrderData['liability']);
            $product = PRODUCT;
            $newOrderDataString = addslashes(serialize($newOrderData));
            $query = "
              INSERT INTO orders (createdate, modifydate, userid, paid, paiddate, paidtxnid, invoice, orderdata, mirrorof, productid) 
              VALUES (NOW(), NOW(), {$_SESSION['userid']}, 1, '{$order['paiddate']}', '{$order['paidtxnid']}', '{$order['invoice']}', AES_ENCRYPT('$newOrderDataString','{$_SESSION['affiliatecode']}'), {$order['id']}, 1);
            ";
            $dbh->exec($query);
            $newOrderID = $dbh->lastInsertId();
            $dbh->exec("UPDATE orders SET mirrorof = $newOrderID WHERE id = {$order['id']};"); // update the original order so they are mirrors of each other
            
            # disregard all previous output - we need to refresh this page now that we have created a mirror will so that the user sees it
            echo "<script type='text/javascript'>window.location = '/mydocs.html';</script>";
            exit;
          }
        }
      	$orderStatus = 'PAID';
      }
    }
    $orderName =  isset($orderData['person']['firstname']) ? $orderData['person']['firstname'] . ' ' . $orderData['person']['surname'] : 'New Document';
?>
            <li class="list-group-item orderitem">
              <input name="orderid" type="hidden" value="<?php echo $order['id'];?>">
              <div class="ordername">
                <strong><?php echo $orderName; ?></strong>
                <?php echo (isset($_SESSION['admin']) && $_SESSION['admin']) || DEVSITE ? '<br> ' . $order['id'] . ($order['mirrorof'] ? ' mirror of ' . $order['mirrorof'] : '') . ($order['paid'] ? ' paid ' . ddmmyyyy($paiddate) : '') : ''; ?>
              </div>
              <div class="orderproduct">
<?php
  	if (isset($orderData['product'])) {
  	  //ksort($orderData['product']);
  	  $i = 0;
  		foreach($orderData['product'] as $product => $yesno) {
  		  $i++;
  		  if ($yesno == 'Yes') {
  		    echo ($i > 1) ? "                <br>" : "                ";
  		    echo $_SESSION['products'][$product]['name'] . "\n";
        }
  		}
  	} else {
    	echo $order['productname'] . "\n";
  	}
?>
              </div>
              <div class="orderbuttons">
<?php
    echo                       "                <a href='${order['shortname']}.html' class='editOrder btn btn-default btn-xs'><i class='glyphicon glyphicon-edit'></i> Edit</a>\n";
    echo $order['paid'] == 1 ? "                <a href='getdocuments.html' class='downloadOrder btn btn-primary btn-xs'><i class='glyphicon glyphicon-cloud-download'></i> Download</a>\n" : 
                               "                <a href='' class='deleteOrder btn btn-danger btn-xs'><i class='glyphicon glyphicon-trash'></i> Delete</a>\n                <a href='order.html' class='downloadOrder btn btn-warning btn-xs'><i class='glyphicon glyphicon-shopping-cart'></i> Purchase</a>\n";
?>
              </div>
            </li>
<?php
  } # end of orders DIVS
?>
            </ul>
          </div>
          <div class="text-center">
            <a product="singlewill" class="createNewOrder btn btn-primary"><i class="glyphicon glyphicon-plus"></i> Add Another Last Will and Testament</a>
          </div>
        </div>
<?php
  if ($paidorders) {
?>
        <div>          
          <div style="text-align: center; width: 80%;margin:60px auto;">
            <?php $discount = isset($_SESSION['affiliatediscount']) ? $_SESSION['affiliatediscount'] : $_SESSION['discount']; ?>
            <h2 class="text-success">
              Recommend <?php echo SITENAME;?> to family and friends and they'll get a <?php echo percent($discount); ?> discount.
            </h2>
            <span class="text-success text-right large"><strong>Share on:</strong></span>
            <input id="posttext" name="posttext" type="hidden" value="I've just used <?php echo SITENAME;?> to create my will. It's really easy and fast and great value. I recommend it if you haven't got a will or you need an updated one. If you click on the link below you'll get a <?php echo percent($discount); ?> discount when you order.">
            <input name="discount" type="hidden" value="<?php echo percent($discount);?>">
            <input name="referralURL" type="hidden" value="<?php echo $_SESSION['affiliateURL']; ?>">
            <input name="shortURL" type="hidden" value="<?php echo $_SESSION['shortURL'];?>">
            <button id="postToFB" class="btn btn-sm btn-social btn-facebook"><i class="fa fa-facebook"></i>Facebook</button>
            <button id="postToTwitter" class="btn btn-sm btn-social btn-twitter"><i class="fa fa-twitter"></i>Twitter</button>
            <a href="mailto:" id="postToEmail" class="btn btn-sm btn-social btn-vk"><i class="fa fa-envelope"></i>Email</a>
          </div>
        </div>
<?php
  }
?>
      </section>
<?php
}

require("pageincludes/footer.php");
?>