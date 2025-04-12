<?php

if (!isset($page) || !isset($_SESSION['userid']) || !$_SESSION['admin']) {
  header("Location: /");
  exit;
}

$title = SITENAME . " - Admin";
$desc = "Administatrative services.";
$keywords = "";


require("pageincludes/header.php");

?>
    <section class="container">
      <h2>System Administration</h2>
      <hr>

      <!-- ROW 1 -->
      <div class="row">
        <div class="col-md-4">
    	    <a href="regenwills.html">QuickWill Regen</a>
        </div>
          
        <div class="col-md-4">
          <a href="" id="showdailyaverage">Show Daily Average by Month</a>
        </div>

        <div class="col-md-4">
          <a href="/commissions.html">Commission Processing</a>
        </div>
      </div>
      <!-- END ROW 1 -->
      <hr>
      <!-- ROW 2 -->
      <div class="row">
        <div class="col-md-4">
        	<form id="removeuserform" class="form-inline" action="includes/removeuser.php">
            <div class="form-group form-group-sm">
              <label class="sr-only" for="emailaddress">Email address</label>
              <div class="input-group">
                <input id="emailaddress" name="emailaddress" type="email" class="form-control email required" placeholder="Email of user to remove" aria-describedby="helpBlock">
                <span class="input-group-btn">
                  <button id="removeuser" type="submit" class="btn btn-danger btn-sm">Remove User</button>
                </span>
              </div>
              <span id="helpBlock" class="help-block small">Enter email address of user to remove.</span>
            </div>
        	</form>
        </div>
        <div class="col-md-4">

        </div>
        <div class="col-md-4">

        </div>
      </div>
      <!-- END ROW 2 -->
      
    </section>
    
    <script type="text/javascript">
      
      /* remove user from db if no orders */
      $('#removeuserform').validate({
        submitHandler: function(form) {
          spinner("Removing user...");
          $('form').ajaxSubmit({
            type: 'post',
            success: function(responseText, statusText, xhr) {
              closeSpinner();
              $('#helpBlock').html(responseText).show().parents('.form-group').removeClass('has-error');
              //$('#emailaddress').val('');
            },
            error: function(xhr, statusText, errorThrown) {
              closeSpinner();
              $('#helpBlock').html(errorThrown).show().parents('.form-group').addClass('has-error');
            }
          });
        }
      });

      /* show sales stats */
      $('#showdailyaverage').click( function(e) {
        e.preventDefault();
        $.ajax({
          url: "includes/dailyaverage.php",
          type: 'GET',
          dataType: 'json',
          success: function(results, statusText, xhr) {
            var output = '<table class="table table-condensed"><tr><th>Month</th><th class="text-right">Orders</th><th class="text-right">Total</th><th class="text-right">Average Orders/Day</th><th class="text-right">Average Amount/Day</th></tr>';
            for(var i = 0; i < results.length; i++) {
              output = output + '<tr><td>' + results[i].month + '</td><td class="text-right">' + results[i].count + '</td><td class="text-right">$' + results[i].total + '<td class="text-right">' + results[i].countavg + '</td><td class="text-right">$' + results[i].totalavg + '</td></tr>';
            }
            output = output + '</table>';
            modalMessage("Daily Average", output, 0, '', 'OK', 'modal-lg');
    
            trace(statusText);
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