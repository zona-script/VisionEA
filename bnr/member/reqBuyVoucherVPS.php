<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$responseMessage = $q = "";
$finID = $username = $accType = $fromAccNo = $IDTrans = $amount = $curr = $curs = $accName = $toAccNo = $status = $approvedBy = "";
$accType = "btc";
$username = $_SESSION["sUserName"];
if (!empty($_POST)) {
  $step   =  (isset($_POST["step"]))?fValidateInput($_POST["step"]): "";
  if ($step == "req"){
    $finID    = $username.strtotime(now);  //same format used in transfer voucher (doTransfer.php)
    $accType  =  (isset($_POST["accType"]))?fValidateSQLFromInput($conn, $_POST["accType"]): "";
    $voucherType = $DEF_VOUCHER_TYPE_VPS;
    $fromAccNo= ""; // (isset($_POST["fromAccNo"]))?fValidateSQLFromInput($conn, $_POST["fromAccNo"]): "";
    $amount   =  (isset($_POST["amount"]))?fValidateSQLFromInput($conn, $_POST["amount"]): "0";
    $curr     = "IDR";
    $curs     = "1";
    $accName  = "";
    $toAccNo  = ""; 
    $IDTrans  = "";
    $status   = $DEF_STATUS_PENDING;
    $approvedBy = "";
    //Validation inputs
    if ($username != "" && $accType != ""  && $amount != ""){
      $arrData = array(
            0 => array ("db" => "finID"       , "val" => $finID),
            1 => array ("db" => "finMbrUsername"  , "val" => $username),
            2 => array ("db" => "finAmount"     , "val" => $amount),
            3 => array ("db" => "finCurr"     , "val" => $curr),
            4 => array ("db" => "finCurs"     , "val" => $curs),
            5 => array ("db" => "finAccName"    , "val" => $accName),
            6 => array ("db" => "finAccType"    , "val" => $accType),
            7 => array ("db" => "finVoucherType"    , "val" => $voucherType),
            8 => array ("db" => "finFromAccNo"    , "val" => $fromAccNo),
            9 => array ("db" => "finToAccNo"    , "val" => $toAccNo),
            10 => array ("db" => "finTransactionID"  , "val" => $IDTrans),
            11 => array ("db" => "finDate"     , "val" => "CURRENT_TIME()"),
            12 => array ("db" => "finStatus"     , "val" => $status),
            13 => array ("db" => "finApprovedBy"   , "val" => $approvedBy)
            );
            
      //insert success
      $table  = "dtFundIn"; 
      if (fInsert($table, $arrData, $conn)){
        //send invoice (confirmation) to client's email
        if (fSendNotifToEmail("REQUEST_BUY_VOUCHER_VPS", $finID)){
          //send email success
          //redirect to success page
          header("Location: reqBuyVoucherVPS.php?q=info_request_success");
          die();
        }else{
          //send email failed
          $responseMessage = "Email failed to send. Please contact support"; 
        }
      }else{
        $responseMessage = "Submit Request to Buy VPS PIN Failed"; 
      } // end else
    }else{
      //Data not complite
      $responseMessage = "Submit Request to Buy VPS PIN Failed - Data not Complite"; 
    }


  }else if ($step== 'confirm'){
    $finID    =  (isset($_POST["finID"]))?fValidateSQLFromInput($conn, $_POST["finID"]): "";
    $IDTrans  =  (isset($_POST["IDTrans"]))?fValidateSQLFromInput($conn, $_POST["IDTrans"]): "";
    $fromAccNo=  (isset($_POST["fromAccNo"]))?fValidateSQLFromInput($conn, $_POST["fromAccNo"]): "";

    if ($finID != "" && $IDTrans != "" && $fromAccNo != ""){
    //if ($finID != ""  && $fromAccNo != ""){
      $sql = "UPDATE dtFundIn SET finFromAccNo='" . $fromAccNo . "', finTransactionID='" . $IDTrans . "', finStatus='".$DEF_STATUS_ONPROGRESS."'";
      $sql .= " WHERE finID='".$finID."' AND finMbrUsername='". $username . "'";
      if ($query = $conn->query($sql)){
        //success
        header("Location: reqBuyVoucherVPS.php?q=info_confirm_success");
        die();
      }else{
        //update failed
        $responseMessage = "Confirmation to Buy VPS PIN Failed - Contact Support"; 
      }
    }else{
      //validation failed
      $responseMessage = "incomplete data"; 
    }

  }
}else{
  $q = (isset($_GET["q"]))?$_GET["q"]:'';
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Buy VPS PIN</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" href="../assets/css/newBinary.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
<script>
	$(document).ready(function(e) {
        //new property method
		$('button[name="reqBuyV"]').click(function(){
			//check package
			if ($.trim($('select[name="accType"]').val()) == ''){
				demo.showNotification('top','center', 'info', 'Select your <b>Account Type</b>');
				return false;
			}
		});
		
		$('select[name="accType"]').change( function(){
			$paymentLabel = "";
			if ($.trim($('select[name="accType"]').val()) == ""){
				$paymentLabel = "Payment ID/email";
			}else if ($.trim($('select[name="accType"]').val()) == "paypal"){
				$paymentLabel = "Paypal ID/email";
			}else if ($.trim($('select[name="accType"]').val()) == "btc"){
				$paymentLabel = "BTC Address";
			}
			
			$("#paymentLabel").html($paymentLabel);
		});
		
		$(document).ready(function(){
			if ($.trim($("#q").html()) == "info_request_success"){
				demo.showNotification('top','center', 'success', 'Request Buy VPS PIN Successfully');
			}else if ($.trim($("#q").html()) == "info_confirm_success"){
        demo.showNotification('top','center', 'success', 'Confirmation Buy VPS PIN Successfully');
      }
      $("#q").html(''); //clear it again
		});


    $("form[name='formReqV']").on('submit', function() {
      var html = $("#reqBuyV").html();
      $("#reqBuyV").attr("disabled", true);
      $("#reqBuyV").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
      
      html = "";
      $("#submit").attr("disabled", true);
      html = $("#submit").html();
      $("#submit").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
    });

    $("#amount").on("change", function(){
      if ($(this).val() < <?php echo $DEF_VOUCHER_PRICE_VPS ?>){
        $(this).val(<?php echo $DEF_VOUCHER_PRICE_VPS ?>);
      }
    });


		
  }); //end $(document).ready(function(e) {
</script>
</head>
<body>
<span id="q"><?php echo $q ?></span>
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="fa fa-usd fa-2x"></i>
        </div>
		 <div class="card-text">
           <h4 class="card-title">Buy VPS PIN</h4>
        </div>
    </div>
	
    <div class="card-body card-fix">
        <div class="container">
            <div class="container-fluid">
                <div class="card" >
                    <div class="card-body"> 
                        <ul class="nav nav-pills nav-pills-rose" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#linkDeposit" role="tablist">
                                    Buy VPS PIN
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#linkApproved" role="tablist">
                                    Approved / Pending
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#linkDeclined" role="tablist">
                                    Declined
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content tab-space">
                            <div class="tab-pane active" id="linkDeposit">
                                <?php if ($responseMessage != ""){ ?>
                                    <div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-6 text-danger">
                                          <div class="alert alert-info">
                                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                  <i class="material-icons">close</i>
                                              </button>
                                              <span><b> Info - </b> <?php echo $responseMessage ?></span>
                                          </div>
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>
                                 <?php } ?>
                                 <div class="row">
                                    <div class="col-md-8" >
                                      <form method="post" name="formReqV" action="reqBuyVoucherVPS.php">
                                        <?php 
                                        $sql = "SELECT * FROM dtFundIn WHERE finMbrUsername='". $username ."' AND finStatus='". $DEF_STATUS_PENDING . "' AND finVoucherType='" . $DEF_VOUCHER_TYPE_VPS . "'";
                                        $query = $conn->query($sql);
                                        if ($query->num_rows == 0){
                                        ?>
                                        <!-- request Buy Voucher -->
                                        <div class="row">
                                            <div class="col-md-12">
                                              <div class="form-group has-default fa-2x">
                                                Step 1 : Request to Buy VPS PIN
                                                <input type="hidden" name="step" value="req">
                                              </div>
                                            </div>
                                            <div class="col-md-12 small">
                                              Please select the account type to be used for payment and the amount of PIN
                                            </div>
                                        </div>
                                        <div class="row">
                                          <label class="col-md-4 text-left text-vCenter">Account Type</label>
                                          <div class="col-md-8">
                                              <select class="selectpicker" name="accType" id="accType" data-size="5" data-style="btn btn-primary " title "Account Type">
                                                  <option value="" <?php echo ($accType == "")? "selected": ""; ?>>Select Account Type</option>
                                                  <!-- <option value="paypal" <?php echo ($accType == "paypal")? "selected": ""; ?>>Paypal</option> -->
                                                  <option value="btc" <?php echo ($accType == "btc")? "selected": ""; ?>>Bitcoin</option>
                                              </select>
                                          </div>
                                        </div>
                                        <!--
                                        <div class="row">
                                          <label class="col-md-4 text-left text-vCenter" id="paymentLabel">Payment ID/Email</label>
                                          <div class="col-md-8">
                                              <div class="form-group has-default">
                                                  <input type="text" name="fromAccNo" id="fromAccNo" value="<?php echo $fromAccNo; ?>" class="form-control" title="Payment Account ID/email/address" required>
                                              </div>
                                            </div>
                                        </div>
                                      -->
                                        <div class="row">
                                            <label class="col-md-4 text-left text-vCenter">Amount ($)</label>
                                            <div class="col-md-8">
                                                <div class="form-group has-default">
                                                    <!-- <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" name="amount" id="amount" value="<?php //echo $amount; ?>"  class="form-control" title="Amount of Deposit" required> -->
                                                    <input type="number" step="<?php echo $DEF_VOUCHER_PRICE_VPS ?>" name="amount" id="amount" value="<?php echo $amount; ?>"  class="form-control" title="Amount of VPS PIN" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4"></div>
                                            <div class="col-md-8">
                                                <button type="submit" name="reqBuyV" id="reqBuyV" class="btn btn-fill btn-rose col-md-12">Request to Buy VPS PIN </button>
                                            </div>
                                        </div> 
                                        <?php
                                        } else {
                                          $accType = $amount = "";
                                          if ($row = $query->fetch_assoc()){
                                            $accType = $row['finAccType'];
                                            $amount = $row['finAmount'];
                                        ?>
                                        <!-- confirm buy voucher -->
                                        <div class="row">
                                            <div class="col-md-12">
                                              <div class="form-group has-default fa-2x">
                                                Step 2 : Payment Confirmation
                                                <input type="hidden" name="step" value="confirm">
                                                <input type="hidden" name="finID" value="<?php echo $row['finID'] ?>">
                                              </div>
                                            </div>
                                            <div class="col-md-12 small">
                                              Check your email for more detailed information of your request.<br>
                                              After making payment, please complete the following data for your payment confirmation process.
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-md-4 text-left text-vCenter">Account Type</label>
                                            <div class="col-md-8">
                                              <div class="form-group has-default">
                                                <input type="text" name="accType" id="accType"  class="form-control"  value="<?php echo $accType ?>" readonly style="background-color: transparent;" title="Account Type (locked)">
                                              </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-md-4 text-left text-vCenter" id="paymentLabel">Your BTC Address</label>
                                            <div class="col-md-8">
                                                <div class="form-group has-default">
                                                    <input type="text" name="fromAccNo" id="fromAccNo" value="<?php echo $fromAccNo; ?>" class="form-control" title="Payment Account ID/email/address" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-md-4 text-left text-vCenter">Transaction ID / Hash</label>
                                            <div class="col-md-8">
                                                <div class="form-group has-default">
                                                    <input type="text" name="IDTrans" id="IDTrans" value="<?php echo $IDTrans; ?>" class="form-control" title="Transaction ID / Hash" maxlength="50" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-md-4 text-left text-vCenter">Amount ($)</label>
                                            <div class="col-md-8">
                                                <div class="form-group has-default">
                                                    <!-- <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" name="amount" id="amount" value="<?php //echo (numFormat($amount, 0)); ?>"  class="form-control" title="Amount of Deposit" required> -->
                                                    <input type="number" step="200" name="amount" id="amount" value="<?php echo ($amount); ?>"  class="form-control" required  readonly style="background-color: transparent;" title="Amount of PIN (locked)">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4"></div>
                                            <div class="col-md-8">
                                                <button type="submit" name="submit" id="submit" class="btn btn-fill btn-rose col-md-12">Confirm Payment</button>
                                            </div>
                                        </div> <!-- end row -->

                                        <?php 
                                          }
                                        } 
                                        ?>

                                      </form>
                                    </div><!-- end col -->
                                </div> <!-- end row -->
                                    
                            </div>
                            <?php 
                                $sql = "SELECT finDate, finAccType, finFromAccNo, finAmount, finTransactionID, stDesc FROM dtFundIn ";
								                $sql .= " INNER JOIN msStatus on finStatus=stID WHERE (finStatus ='" . $DEF_STATUS_APPROVED . "' ";
                                $sql .= " OR finStatus='" . $DEF_STATUS_PENDING . "' OR finStatus='" . $DEF_STATUS_ONPROGRESS . "' )";
                                $sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_VPS . "'";
                                $sql .= " AND finMbrUsername='" . $_SESSION["sUserName"] . "'";
                                $queryApproved = $conn->query($sql);
                            ?>
                            <div class="tab-pane" id="linkApproved">
                              <div class="material-datatables">
                                  <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                      <thead>
                                          <tr>
                                              <th>Date</th>
                                              <th>Account Type</th>
                                              <th>ID/Email/Addr</th>
                                              <th>Amount</th>
                                              <th>Description</th>
                                              <th>Status</th>
                                          </tr>
                                      </thead>
                                      <tfoot>
                                          <tr>
                                              <th>Date</th>
                                              <th>Account Type</th>
                                              <th>ID/Email/Addr</th>
                                              <th>Amount</th>
                                              <th>Description</th>
                                              <th>Status</th>
                                          </tr>
                                      </tfoot>
                                      <tbody>
                                          <?php
                                            while ($rowApproved = $queryApproved->fetch_assoc()){
                                          ?>
                                          <tr>
                                              <td><?php echo $rowApproved["finDate"] ?></td>
                                              <td><?php echo $rowApproved["finAccType"] ?></td>
                                              <td><?php echo $rowApproved["finFromAccNo"] ?></td>
                                              <td><?php echo $rowApproved["finAmount"] ?></td>
                                              <td><?php echo $rowApproved["finTransactionID"] ?></td>
                                              <td><?php echo $rowApproved["stDesc"] ?></td>
                                          </tr>
                                          <?php } ?>
                                      </tbody>
                                  </table>
                               </div>
                            </div>
                            <!--- decline ---------->
                            <?php 
                                $sql 	= "SELECT finDate, finAccType, finFromAccNo, finAmount, finTransactionID, stDesc FROM dtFundIn inner join msStatus on finStatus=stID WHERE finStatus ='" . $DEF_STATUS_DECLINED . "'";
                                $sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_VPS . "'";
                                $sql .= " AND finMbrUsername='" . $_SESSION["sUserName"] . "'";
                                $queryDeclined = $conn->query($sql);
                            ?>
                            <div class="tab-pane" id="linkDeclined">
                                <div class="material-datatables">
                                  <table id="datatablesDeclined" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                      <thead>
                                          <tr>
                                              <th>Date</th>
                                              <th>Account Type</th>
                                              <th>ID/Email/Addr</th>
                                              <th>Amount</th>
                                              <th>Description</th>
                                              <th>Status</th>
                                          </tr>
                                      </thead>
                                      <tfoot>
                                          <tr>
                                              <th>Date</th>
                                              <th>Account Type</th>
                                              <th>ID/Email/Addr</th>
                                              <th>Amount</th>
                                              <th>Description</th>
                                              <th>Status</th>
                                          </tr>
                                      </tfoot>
                                      <tbody>
                                      <?php
                                        while ($rowDeclined = $queryDeclined->fetch_assoc()){
                                      ?>
                                          <tr>
                                              <td><?php echo $rowDeclined["finDate"] ?></td>
                                              <td><?php echo $rowDeclined["finAccType"] ?></td>
                                              <td><?php echo $rowDeclined["finFromAccNo"] ?></td>
                                              <td><?php echo $rowDeclined["finAmount"] ?></td>
                                              <td><?php echo $rowDeclined["finTransactionID"] ?></td>
                                              <td><?php echo $rowDeclined["stDesc"] ?></td>
                                          </tr>
                                          <?php } ?>
                                      </tbody>
                                  </table>
                               </div>
                            </div>
                        </div>
                    </div><!-- end card- Body -->
            	</div><!-- end card -->
            </div> <!-- end container-fluid -->
	 	</div> <!-- end container -->
     </div> <!-- card-body>
</div><!-- end card -->
        
</body>

    <!--   Core JS Files   -->
<script src="../assets/js/core/jquery.min.js"></script>
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/bootstrap-material-design.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>

<!--  Google Maps Plugin  -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB2Yno10-YTnLjjn_Vtk0V8cdcY5lC4plU"></script>

<!--  Plugin for Date Time Picker and Full Calendar Plugin  -->
<script src="../assets/js/plugins/moment.min.js"></script>

<!--	Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
<script src="../assets/js/plugins/bootstrap-datetimepicker.min.js"></script>

<!--	Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="../assets/js/plugins/nouislider.min.js"></script>

<!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/bootstrap-selectpicker.js"></script>

<!--	Plugin for Tags, full documentation here: http://xoxco.com/projects/code/tagsinput/  -->
<script src="../assets/js/plugins/bootstrap-tagsinput.js"></script>

<!--	Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
<script src="../assets/js/plugins/jasny-bootstrap.min.js"></script>

<!-- Plugins for presentation and navigation  -->
<script src="../assets/assets-for-demo/js/modernizr.js"></script>

<!-- Material Kit Core initialisations of plugins and Bootstrap Material Design Library -->
<script src="../assets/js/material-dashboard.js?v=2.0.0"></script>

<!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>

<!-- Library for adding dinamically elements -->
<script src="../assets/js/plugins/arrive.min.js" type="text/javascript"></script>

<!-- Forms Validations Plugin -->
<script src="../assets/js/plugins/jquery.validate.min.js"></script>

<!--  Charts Plugin, full documentation here: https://gionkunz.github.io/chartist-js/ -->
<script src="../assets/js/plugins/chartist.min.js"></script>

<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="../assets/js/plugins/jquery.bootstrap-wizard.js"></script>

<!--  Notifications Plugin, full documentation here: http://bootstrap-notify.remabledesigns.com/    -->
<script src="../assets/js/plugins/bootstrap-notify.js"></script>

<!-- Vector Map plugin, full documentation here: http://jvectormap.com/documentation/ -->
<script src="../assets/js/plugins/jquery-jvectormap.js"></script>

<!--  Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/jquery.select-bootstrap.js"></script>

<!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
<script src="../assets/js/plugins/jquery.datatables.js"></script>

<!-- Sweet Alert 2 plugin, full documentation here: https://limonte.github.io/sweetalert2/ -->
<script src="../assets/js/plugins/sweetalert2.js"></script>

<!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
<script src="../assets/js/plugins/fullcalendar.min.js"></script>

<!-- demo init -->
<script src="../assets/js/plugins/demo.js"></script>


<script type="text/javascript">

	$(document).ready(function(){	
	  //init DateTimePickers
	  md.initFormExtendedDatetimepickers();
	
	  // Sliders Init
	  md.initSliders();
	});
</script>

<script type="text/javascript">

$(document).ready(function() {
    $('#datatables').DataTable({
        "pagingType": "full_numbers",
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records",
        }

    });


    var table = $('#datatables').DataTable();

    // Edit record
    table.on('click', '.edit', function() {
        $tr = $(this).closest('tr');

        var data = table.row($tr).data();
        alert('You press on Row: ' + data[0] + ' ' + data[1] + ' ' + data[2] + '\'s row.');
    });

    // Delete a record
    table.on('click', '.remove', function(e) {
        $tr = $(this).closest('tr');
        table.row($tr).remove().draw();
        e.preventDefault();
    });

    //Like record
    table.on('click', '.like', function() {
        alert('Yo	u clicked on Like button');
    });
	
	//for Declined
		$('#datatablesDeclined').DataTable({
			"pagingType": "full_numbers",
			"lengthMenu": [
				[10, 25, 50, -1],
				[10, 25, 50, "All"]
			],
			responsive: true,
			language: {
				search: "_INPUT_",
				searchPlaceholder: "Search records",
			}
	
		});
	
	
		var tableDeclined = $('#datatablesDeclined').DataTable();
	
		// Edit record
		tableDeclined.on('click', '.edit', function() {
			$tr = $(this).closest('tr');
	
			var data = tableDeclined.row($tr).data();
			alert('You press on Row: ' + data[0] + ' ' + data[1] + ' ' + data[2] + '\'s row.');
		});
	
		// Delete a record
		tableDeclined.on('click', '.remove', function(e) {
			$tr = $(this).closest('tr');
			tableDeclined.row($tr).remove().draw();
			e.preventDefault();
		});
	
		//Like record
		tableDeclined.on('click', '.like', function() {
			alert('Yo	u clicked on Like button');
		});
	
	//end of declined

    $('.card .material-datatables label').addClass('form-group');
});

</script>

<?php fCloseConnection($conn); ?>
</html>