<?php

if (!isset($page) || !isset($_SESSION['userid']) || !$_SESSION['admin']) {
  header("Location: /");
  exit;
}

$title = "Affiliate Commissions";
$desc = "Admin commission processing page.";


require("pageincludes/header.php");
?>
    <section class="container">
      <h1>Commission Processing</h1>
      
      <h2>Pending Commission Payment Requests</h2>
      <table class="table table-condensed noborder">
        <tbody>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th class="text-right">Payment</th>
            <th>Requested</th>
            <th class="text-right">Order</th>
            <th class="text-right">Commission</th>
            <th>Order Date</th>
            <th>Orderer</th>
            <th>Level</th>
            <th></th>
          </tr>
<?php

# get pending commission payment request data
$query = "
SELECT
  cp.id AS payid,
  CONCAT(cu.firstname, ' ', cu.surname) AS commissionfullname,
  cp.emailaddress,
  cu.phone,
  cu.affiliatecode,
  cp.requestdate,
  cp.paymentamount,
  oc.orderamount,
  oc.commissionamount,
  oc.level,
  oc.commissiondate,
  CONCAT(ou.firstname, ' ', ou.surname) AS orderfullname
FROM 
  commissionpayments cp JOIN
  users cu ON (cu.id = cp.userid) JOIN
  ordercommissions oc ON (oc.commissionpaymentid = cp.id) JOIN
  users ou ON (ou.id = oc.orderuserid)
WHERE
  cp.status = 'Pending'
ORDER BY cp.requestdate, oc.commissiondate
;";
$result = $dbh->query($query);
$rows = $result->fetchAll(PDO::FETCH_ASSOC);

$lastid = 0;
foreach ($rows as $row) {
  $samerequest = ($lastid == $row['payid']);
?>
          <tr>
            <?php echo $samerequest ? '<td></td>' : "<td class='border'>${row['commissionfullname']}</td>";?>
            <?php echo $samerequest ? '<td></td>' : "<td class='border'>${row['emailaddress']}</td>";?>
            <?php echo $samerequest ? '<td></td>' : "<td class='border'>${row['phone']}</td>";?>
            <?php echo $samerequest ? '<td></td>' : "<td class='text-right border'>" . currency($row['paymentamount']) . "</td>";?>
            <?php echo $samerequest ? '<td></td>' : "<td class='border'>" . ddmmyyyy($row['requestdate']) . "</td>";?>
            <td class="text-right <?php echo $samerequest ? '' : 'border';?>"><?php echo currency($row['orderamount']);?></td>
            <td class="text-right <?php echo $samerequest ? '' : 'border';?>"><?php echo currency($row['commissionamount']);?></td>
            <td<?php echo $samerequest ? '' : ' class="border"';?>><?php echo ddmmyyyy($row['commissiondate']);?></td>
            <td<?php echo $samerequest ? '' : ' class="border"';?>><?php echo $row['orderfullname'];?></td>
            <td<?php echo $samerequest ? '' : ' class="border"';?>><?php echo $row['level'];?></td>
            <?php echo $samerequest ? '<td></td>' : "<td class='border'><a href='' class='drilldown btn btn-xs btn-info' payid='${row['payid']}'>&nbsp;&nbsp;Pay&nbsp;&nbsp;</a></td>";?>
          </tr>
<?php
  $lastid = $row['payid'];
}
?>
        </tbody>
      </table>
      <br> <br>
    </section>
    <script type="text/javascript">
      /* process payment request */
      $(document).on('click', 'a.drilldown', function(e) {
        e.preventDefault();
        var payid = $(this).attr('payid');
        $.ajax({
          url: "includes/processpaymentrequest.php?payid=" + payid,
          type: 'GET',
          async: false,
          dataType: 'html',
          success: function(results, statusText, xhr) {
            // trace(results);
            window.location = '/commissions.html';
          },
          error: function(xhr, statusText, errorThrown) {
            trace(errorThrown);
          }
        });
      });    
    </script>
    
<?php
  require("pageincludes/footer.php");
?>