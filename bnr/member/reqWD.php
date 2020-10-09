<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

//NB:
//Total of Balance will be reduced when status of WD is : ON_Progress or Approved
$responseMessage = "";
$username = $amount = $email = $secPasswd = $payAcc = "";
$wdtax = $nettWD = "";
if (!empty($_POST)) { 
	// print_r($_POST); die();
	$username	  = $_SESSION["sUserName"];
	//$email	    =  (isset($_POST["email"]))?fValidateSQLFromInput($conn, $_POST["email"]): "";
	$secPasswd	=  (isset($_POST["secPasswd"]))?fValidateSQLFromInput($conn, $_POST["secPasswd"]): "";
	$amount		=  (isset($_POST["amount"]))?fValidateSQLFromInput($conn, $_POST["amount"]): "0";
	$wdtax     	=  (isset($_POST["wdtax"]))?fValidateSQLFromInput($conn, $_POST["wdtax"]): "0";

	$myDataObj = json_decode(fGetPayAcc($username, $conn));
	if ($myDataObj->status == "success"){
		$payAcc = $myDataObj->payAcc;
	}
	//Validation inputs
	if ($payAcc != "" && $username != "" && $secPasswd != "" && $amount != ""  && $wdtax != "" && $amount > 0  && $wdtax > 0 ){
		
		//Checking data before save the request
		//check email, security password and existing ttlCommission
		$ttlCommission = 0;
		//Check Verified ID
		if (fCekVerification($conn, $username)){ //Sudah Verifikasi ID/KTP
		//check ONProgress Request WD
			if (!fCheckONProgressWD($username, $conn)){
				//check Email 
				//if (!fCheckEmail($username, $email, $conn)){
				//  $responseMessage .= "Invalid Email Address<br>";
				//}else{
					//Check Security Password
				if (!fCheckSecurityPassword($username, $secPasswd, $conn)){
					$responseMessage .= "Security Password not match<br>";
				}else{
						//existing Balance
					include_once("../includes/inc_commission.php");
					$myDataObj  = json_decode(fGetBalance($username, $conn));
					if ($myDataObj->{"status"} == "success"){
						$ttlCommission     = $myDataObj->{'ttlCommission'};
						if ($ttlCommission >= $amount && $amount > 0){
							if ($payAcc != ""){
								$conn->autocommit(false);
								$wdID   = strtotime("now").rand(10000, 99999); //length 15
								$wdID   = substr($wdID, 0, 15); //make sure max length = 15
								$wdCode = rand(1000, 9999);
								$arrData = array(
									0 => array ("db" => "wdID"           , "val" => $wdID),
									1 => array ("db" => "wdMbrUsername"  , "val" => $username),
									2 => array ("db" => "wdDate"         , "val" => "CURRENT_TIME()"),
									3 => array ("db" => "wdAmount"       , "val" => $amount),
									4 => array ("db" => "wdTax"          , "val" => $wdtax),
									5 => array ("db" => "wdPayAcc"       , "val" => $payAcc),
									6 => array ("db" => "wdCode"         , "val" => $wdCode),
									7 => array ("db" => "wdStID"         , "val" => $DEF_STATUS_REQUEST) 
								);
								$table  = "dtWDFund"; 
								if (fInsert($table, $arrData, $conn)){
									//insert success
									//Send confirmation email
									$conn->commit();
									if (fSendNotifToEmail("REQUEST_WD", $wdID)){
										//redirect to success page
										header("Location: reqWD.php?q=info-success");
									}else{
										$responseMessage .= "You have made inquiry of withdrawal, but email failed to send<br>";
									}
								}else{
									$responseMessage .= "Submit Request Withdrawal Failed<br>";
								} // end else
							}else{
								$responseMessage .= "Your balance account does not exist yet<br>"; 
							}
						}//end $ttlCommission >= $amount
						else{
							$responseMessage .= "your balance is not enough<br>";  
						}
					}else{
						$responseMessage .= "error getting balance<br>";
					}
				}
				//}
			}else{
				$responseMessage .= "You can not submit new withdrawal while your request on progress<br>";  
			}
		}else{
			$responseMessage .= "Anda belum melakukan verifikasi ID/KTP<br>"; 
		}
	}else{
		//Data not complite
		$responseMessage .= "Incomplete data<br>";	
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Withdrawal</title>
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
	<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
	<link rel="stylesheet" href="../assets/css/newBinary.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
	<script>
	$(document).ready(function() {
		$("#amount").on('change', function(){
			var ttlCommission = parseInt($(this).attr("data-ttlcomm"));
			var amount 	= parseInt($(this).val());
			var minWD = parseInt($(this).attr("min"));
			var currAmount = amount.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' });
			var step 	= parseInt($(this).attr("step"));
			var modulus = amount % step;
			var wdTax = amount * 3/100;
			var currwdTax = wdTax.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' });
			var nettWD = amount - wdTax;
			$("#currAmount").html(currAmount);
			$("#currAmount").css("display", "block");
			$("#wdtax").val(wdTax);
			$("#currwdTax").val(currwdTax);
			$("#nettWD").val(nettWD.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));
			if (ttlCommission < amount || amount < minWD){
				$('button[name="submit"]').attr("disabled", true);
				if (amount < minWD){
					$("#amountErr").html("Min. Withdrawal amount Rp 1.000.000,00");
				}else{
					$("#amountErr").html("Insufficient Amount");
				}
				$("#amountErr").css("display", "block");
				$("#currAmount").css("display", "none");
				return false;
			}else{
				if (modulus != 0){
					$('button[name="submit"]').attr("disabled", true);
					$("#amountErr").html("Amount must a multiple of Rp 100.000,00");
					$("#amountErr").css("display", "block");
					$("#currAmount").css("display", "none");
					return false;
				}else{
					$('button[name="submit"]').attr("disabled", false);
					$("#amountErr").css("display", "none");
				}
			}
		});

		//new property method
		$('#reqWD').on("submit", function(){
			//check package
			//($.trim($('input[name="email"]').val()) == '') ||
			$('button[name="submit"]').attr("disabled", true);
			var html = $('button[name="submit"]').html();
			$('button[name="submit"]').html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
			if (($.trim($('input[name="amount"]').val()) == '') || ($.trim($('input[name="secPasswd"]').val()) == '')){
				demo.showNotification('top','center', 'info', '<b>incomplete data</b>');
				return false;
			}
		});

		$("#q").html('<?php echo (isset($_GET["q"])?$_GET["q"]:""); ?>');
		if ($.trim($("#q").html()) == "info-success"){
			$("#q").html(''); //clear it again
			demo.showNotification('top','center', 'success', '<b>WITHDRAWAL EMAIL SENT</b><p>To complete the withdrawal process look for an email in your inbox that provides further instructions.</p>');
		}
	});
	</script>
</head>
<body style="width: 95%;">
	<span id="q"></span>
	<div class="card">
		<div class="card-header card-header-success card-header-icon">
			<div class="card-icon">
				<i class="material-icons">local_atm</i>
			</div>
			<div class="card-text">
				<h4 class="card-title">Withdrawal</h4>
			</div>
		</div>

		<div class="card-body card-fix">
			<div class="container">
				<div class="container-fluid">
					<div class="card">
						<div class="card-body"> 
							<ul class="nav nav-pills nav-pills-rose" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" data-toggle="tab" href="#linkWD" role="tablist">
										Request Withdrawal
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
								<div class="tab-pane active" id="linkWD">
									<?php
									$ExpiredDate = "";
									if (fCekStatusUsage($conn, $_SESSION['sUserName'], $ExpiredDate) == "active"){
																	//WD allowed, not expired yet
										?>
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
												<form method="post" action="reqWD.php" id="reqWD">
													<div class="row">
														<?php
														include_once("../includes/inc_commission.php");
														$username = $_SESSION['sUserName'];
														$myDataObj = json_decode(fGetPayAcc($username, $conn));
														if ($myDataObj->{"status"} == "success"){
															$balanceAcc 	= $myDataObj->{'payAcc'};
															$balanceAccName	= $myDataObj->{'payAccName'};
															$balanceAccDesc = $myDataObj->{'payAccDesc'};
														}
														if ($balanceAcc == "") $balanceAcc = "No Account / BCA ADDRESS";
														$myDataObj  = json_decode(fGetBalance($username, $conn));
														if ($myDataObj->{"status"} == "success"){
															$ttlCommission     = $myDataObj->{'ttlCommission'};
														}
														?>
														<label class="col-md-4 text-left text-vCenter">Amount <span class="small">(<?php echo ("Rp ".numFormat($ttlCommission,0)) ?>)</span></label>
														<div class="col-md-8">
															<div class="form-group has-default">
																<input type="number" step="100000" name="amount" id="amount" value="0" min="<?php echo ($DEF_MININUM_WITHDRAWAL); ?>" data-ttlcomm="<?php echo ($ttlCommission); ?>"  class="form-control" title="amount of funds to be withdrawn" required>
																<small class="text-danger" id="amountErr" style="display: none"></small>
																<small class="text-success" id="currAmount" style="display: none"></small>
															</div>
														</div>
													</div>
													<div class="row">
														<label class="col-md-4 text-left text-vCenter" id="">Bank Account</label>
														<div class="col-md-8">
															<div class="form-group has-default">
																<input type="text" name="balanceAcc" id="balanceAcc" value="<?php echo $balanceAcc; ?>" class="form-control" title="balanceAcc"  style="background-color:transparent;" readonly required>
																<?php
																$textAcc = ""; 
																if ($balanceAccDesc != ""){
																	$textAcc = $balanceAccDesc;
																	if ($balanceAccName != ""){
																		$textAcc .= " / $balanceAccName"; 
																	}
																?>
																<small class="text-info"><?php echo $textAcc; ?></small>
																<?php 
																}
																?>
															</div>
														</div>
													</div>
													<div class="row">
														<label class="col-md-4 text-left text-vCenter" id="">Tax</label>
														<div class="col-md-8">
															<div class="form-group has-default">
																<input type="hidden" name="wdtax" id="wdtax" value="<?php echo $wdtax; ?>" class="form-control" title="withdrawal wdtax"  style="background-color:transparent;" readonly required>
																<input type="text" id="currwdTax" class="form-control" title="withdrawal wdtax"  style="background-color:transparent;" readonly>
															</div>
														</div>
													</div>
													<div class="row">
														<label class="col-md-4 text-left text-vCenter">Nett Received Amount</label>
														<div class="col-md-8">
															<div class="form-group has-default">
																<input type="text" name="nettWD" id="nettWD" value="<?php echo $nettWD; ?>" class="form-control" readonly required style="background-color: transparent;">
															</div>
														</div>
													</div>
													<div class="row">
														<label class="col-md-4 text-left text-vCenter">Security Password</label>
														<div class="col-md-8">
															<div class="form-group has-default">
																<input type="password" name="secPasswd" id="secPasswd" value="<?php echo $secPasswd; ?>" class="form-control" title="Your security password" maxlength="30" required>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-4"></div>
														<div class="col-md-8">
															<button type="submit" name="submit" id="submit" class="btn btn-fill btn-rose col-md-12">Submit</button>
														</div>
													</div>
												</form>
											</div><!-- end col -->
										</div> <!-- end row -->
										<?php
									}else{
										echo ("The active period of your account has expired on ". $ExpiredDate . ", please renew to reactivate this module.");
									}
									?>
								</div>
								<?php 
								$sql = "SELECT wdMbrUsername, wdAmount, wdPayAcc, stDesc, ptDesc, wdDate, wdTax FROM dtWDFund ";
								$sql .= " INNER JOIN dtPaymentAcc ON payMbrUsername=wdMbrUsername AND payAcc=wdPayAcc ";
								$sql .= " INNER JOIN msPaymentType ON ptID = payPTID ";
								$sql .= " INNER JOIN msStatus ON stID = wdStID ";
								$sql .= " WHERE wdMbrUsername='".$_SESSION["sUserName"]."' ";
								$sql .= " AND (wdStID='".$DEF_STATUS_ONPROGRESS."' OR wdStID='".$DEF_STATUS_APPROVED . "' OR wdStID ='".$DEF_STATUS_REQUEST."')";
								$sql .= " AND date(wdDate) > '".$DEF_MUTASI_DATE."' ";
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
													<th>Tax</th>
													<th>Status</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th>Date</th>
													<th>Account Type</th>
													<th>ID/Email/Addr</th>
													<th>Amount</th>
													<th>Tax</th>
													<th>Status</th>
												</tr>
											</tfoot>
											<tbody>
												<?php
												while ($rowApproved = $queryApproved->fetch_assoc()){
													?>
													<tr>
														<td><?php echo $rowApproved["wdDate"] ?></td>
														<td><?php echo $rowApproved["ptDesc"] ?></td>
														<td><?php echo $rowApproved["wdPayAcc"] ?></td>
														<td><?php echo $rowApproved["wdAmount"] ?></td>
														<td><?php echo $rowApproved["wdTax"] ?></td>
														<td><?php echo $rowApproved["stDesc"] ?></td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>

								<!--- decline -->
								<?php 
								$sql = "SELECT wdMbrUsername, wdAmount, wdPayAcc, stDesc, ptDesc, wdDate, wdDesc FROM dtWDFund ";
								$sql .= " INNER JOIN dtPaymentAcc ON payMbrUsername=wdMbrUsername AND payAcc=wdPayAcc ";
								$sql .= " INNER JOIN msPaymentType ON ptID = payPTID ";
								$sql .= " INNER JOIN msStatus ON stID = wdStID ";
								$sql .= " WHERE wdMbrUsername='".$_SESSION["sUserName"]."' AND wdStID='".$DEF_STATUS_DECLINED."' ";
								$sql .= " AND date(wdDate) > '".$DEF_MUTASI_DATE."'";
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
													<th>Status</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th>Date</th>
													<th>Account Type</th>
													<th>ID/Email/Addr</th>
													<th>Amount</th>
													<th>Status</th>
												</tr>
											</tfoot>
											<tbody>
												<?php
												while ($rowDeclined = $queryDeclined->fetch_assoc()){
													?>
													<tr>
														<td><?php echo $rowDeclined["wdDate"] ?></td>
														<td><?php echo $rowDeclined["ptDesc"] ?></td>
														<td><?php echo $rowDeclined["wdPayAcc"] ?></td>
														<td><?php echo $rowDeclined["wdAmount"] ?></td>
														<td><?php echo ($rowDeclined["stDesc"]."<br>". $rowDeclined["wdDesc"]) ?></td>
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
		</div> <!-- card-body> -->
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
			order: [[ 0, "desc" ]],
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
			order: [[ 0, "desc" ]],
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