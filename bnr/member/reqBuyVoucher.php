<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$responseMessage = $q = "";
$finID = $username = $accType = $fromAccNo = $IDTrans = $amount = $curr = $curs = $accName = $toAccNo = $status = $approvedBy = "";
$accType = "";
$username = $_SESSION["sUserName"];
if (!empty($_POST)) {
	$type   =  (isset($_POST["type"]))?fValidateInput($_POST["type"]): "";
	if ($type == "req"){
		$sql  = "SELECT * FROM dtFundIn";
		$sql .= " WHERE finMbrUsername = '".$username."' AND finStatus = '".$DEF_STATUS_PENDING."' ";
		$result = $conn->query($sql);
		if ($result->num_rows == 0){
			$finID    = $username.strtotime("now");  //same format used in transfer voucher (doTransfer.php)
			$accType  =  (isset($_POST["accType"]))?fValidateSQLFromInput($conn, $_POST["accType"]): "";
			$voucherType = $DEF_VOUCHER_TYPE_STD;;
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
					if (fSendNotifToEmail("REQUEST_BUY_VOUCHER", $finID)){
						//send email success
						//redirect to success page
						echo fSendStatusMessage("success", "info_request_success"); die();
					}else{
						//send email failed
						echo fSendStatusMessage("error", "Email failed to send. Please contact support"); die(); 
					}
				}else{
					echo fSendStatusMessage("error", "Submit Request to Buy PIN Failed"); die(); 
				} // end else
			}else{
				//Data not complite
				echo fSendStatusMessage("error", "Submit Request to Buy PIN Failed - Data not Complite"); die(); 
			}
		}else{
			echo fSendStatusMessage("error", "Please complete your previous request"); die();
		}
	}else if ($type== 'confirm'){
		$finID    =  (isset($_POST["finID"]))?fValidateSQLFromInput($conn, $_POST["finID"]): "";
		$IDTrans  =  (isset($_POST["IDTrans"]))?fValidateSQLFromInput($conn, $_POST["IDTrans"]): "";
		$fromAccNo=  (isset($_POST["fromAccNo"]))?fValidateSQLFromInput($conn, $_POST["fromAccNo"]): "";

		$imageFileType  = strtolower(pathinfo(basename($_FILES["filename"]["name"]),PATHINFO_EXTENSION));
		$target_dir     = "bukti_transfer/";
        $filename       = "fin"."_".$finID.".".$imageFileType;
        $target_file    = $target_dir.$filename;
        $isValid = true;
        if ($finID == "" && $fromAccNo == ""){
        	$isValid = false;
        	$responseMessage = "Incomplete Data<br>";
        }

        if (EMPTY($_FILES["filename"]["tmp_name"])){
            $isValid = false;
            $responseMessage .= "There is no file to upload.<br>";
        }
        $check = getimagesize($_FILES["filename"]["tmp_name"]);
        if ($check === false){
        	$isValid = false;
        	$responseMessage .= "File is not an image.<br>";
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
        	$isValid = false;
            $responseMessage .= "only JPG, JPEG, PNG & GIF files are allowed.<br>";
        }

		if ($isValid){
			$conn->autocommit(false);
			$table = "dtFundIn";
			$arrData = array(
				"finFromAccNo" 		=> $fromAccNo,
				"finTransactionID" 	=> $IDTrans,
				"finStatus" 		=> $DEF_STATUS_ONPROGRESS,
				"finFilename"		=> $filename
			);
			$arrDataQuery = array(
				"finID" 			=> $finID,
				"finMbrUsername"	=> $username
			);		
			if (fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){ //success update
				if (move_uploaded_file($_FILES["filename"]["tmp_name"], $target_file)){
                    $conn->commit();
                    echo fSendStatusMessage("success", "info_confirm_success"); die();
                }else{
                    $conn->rollback();
                    echo fSendStatusMessage("error", "Upload proof of payment Failed #1.<br>"); die();
                }
			}else{
				//update failed
				echo fSendStatusMessage("error", "Confirmation to Buy PIN Failed - Contact Support"); die(); 
			}
		}else{
			echo fSendStatusMessage("error", $responseMessage); die();
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
	<title>Buy PIN</title>
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
			}else if ($.trim($('select[name="accType"]').val()) == "BBCA"){
				$paymentLabel = "Rekening BCA";
			}

			$("#paymentLabel").html($paymentLabel);
		});

		$("form[name='formReqV']").on('submit', function() {
			var btn = $("button[type=submit]");
			var type = $("input[name=type]").val();
			if (type == "req"){
				var html = $("#reqBuyV").html();
				$("#reqBuyV").attr("disabled", true);
				$("#reqBuyV").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
			}else if (type == "confirm"){
				var html = $("#cnfPayment").html();
				$("#cnfPayment").attr("disabled", true);
				$("#cnfPayment").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
				//validate image
	            var input, file;
	            if (!window.FileReader){
	                demo.showNotification('top','center', 'info', "The file API isn't supported on this browser yet.");
	                btn.attr("disabled", false)
					btn.text(btn.text());
	                return false;
	            }

	            input = document.getElementById('filename');
	            if ($.trim(input) != ""){
		            if (!input){
		                demo.showNotification('top','center', 'info', "Um, couldn't find the fileinput element.");
		                btn.attr("disabled", false)
						btn.text(btn.text());
		                return false;
		            }else if (!input.files){
		                demo.showNotification('top','center', 'info', "This browser doesn't seem to support the upload file");
		                btn.attr("disabled", false)
						btn.text(btn.text());
		                return false;
		            }else if (!input.files[0]){
		                demo.showNotification('top','center', 'info', "Please select a file to upload");
		                btn.attr("disabled", false)
						btn.text(btn.text());
		                return false;
		            }else{
		                file = input.files[0];
		                if (file.size > 2097151){ // lebih besar dari 2MB
		                    demo.showNotification('top','center', 'info', "Image file exceeds the maximum upload size for this site");
		                    btn.attr("disabled", false)
						btn.text(btn.text());
		                    return false;
		                }
		            }
		        }
			}
			$.ajax({
				url     : "reqBuyVoucher.php",
				type    : "POST",
				data    : new FormData(this),
				contentType   : false,       // The content type used when sending data to the server.
				cache         : false,             // To unable request pages to be cached
				processData   : false,        // To send DOMDocument or non processed data file it is set to false
				success       : function(data, success)  // A function to be called if request succeeds
				{
					console.log(data);
					$myDataObj = JSON.parse(data);
					if ($.trim($myDataObj["status"])=="error"){
						$("#errMsg").css("display", "block");
						$("#resMsg").html('<b> Error - </b>'+$myDataObj['message']);
						btn.attr("disabled", false)
						btn.text(btn.text());
						return false;
					}else if ($.trim($myDataObj['status']) == "success"){
						location.href = "reqBuyVoucher.php?q="+$myDataObj['message'];
					}
				}
			});
		});

		$("#amount").on("change", function(){
			var amount 	= parseInt($(this).val());
			var minimum = parseInt($(this).attr("min"));
			if ($(this).val() < minimum){
				$(this).val(minimum);
			}
			var currAmountVch = amount.toLocaleString("id-ID", { style: 'currency', currency: 'IDR' });
			$("#currAmountVch").html(currAmountVch);
		});

		if ($.trim($("#q").html()) == "info_request_success"){
			demo.showNotification('top','center', 'success', 'Request Buy PIN Successfully');
		}else if ($.trim($("#q").html()) == "info_confirm_success"){
			demo.showNotification('top','center', 'success', 'Confirmation Buy PIN Successfully');
		}
		$("#q").html(''); //clear it again
	}); //end $(document).ready(function(e) {
	</script>
</head>
<body style="width: 95%;">
	<span id="q"><?php echo $q ?></span>
	<div class="card">
		<div class="card-header card-header-success card-header-icon">
			<div class="card-icon">
				<i class="fa fa-usd fa-2x"></i>
			</div>
			<div class="card-text">
				<h4 class="card-title">Buy PIN</h4>
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
										Buy PIN
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
									<div class="row" id="errMsg" style="display:none;">
										<div class="col-md-3"></div>
										<div class="col-md-6 ml-auto mr-auto">
											<div class="alert alert-info">
												<button type="button" class="close" data-dismiss="alert" aria-label="Close">
													<i class="material-icons">close</i>
												</button>
												<span id="resMsg"></span>
											</div>
										</div>
										<div class="col-md-3"></div>
									</div>
									<form method="post" name="formReqV" onsubmit="return false;" enctype="multipart/form-data">
										<div class="row">
											<?php 
											$sql  = "SELECT * FROM dtFundIn ";
											$sql .= " INNER JOIN dtPaymentAcc ON payMbrUsername = finMbrUsername";
											$sql .= " INNER JOIN msPaymentType ON ptID = finAccType";
											$sql .= " WHERE finMbrUsername='". $username ."' AND finStatus='". $DEF_STATUS_PENDING . "' ";
											$sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "' AND ptStID='".$DEF_STATUS_ACTIVE."' ";
											$query = $conn->query($sql);
											if ($query->num_rows == 0){
											?>
											<!-- request Buy Voucher -->
											<div class="col-md-8">
												<div class="row">
													<div class="col-md-12">
														<div class="form-group has-default fa-2x">
															Step 1 : Request to Buy PIN
															<input type="hidden" name="type" value="req">
														</div>
													</div>
													<div class="col-md-12 small">
														Please select the account type to be used for payment and the amount of PIN
													</div>
												</div>
												<div class="row">
													<label class="col-md-4 text-left text-vCenter">Account Type</label>
													<div class="col-md-8">
														<select class="selectpicker" name="accType" id="accType" data-style="btn btn-primary ">
															<option value="" <?php echo ($accType == "")? "selected": ""; ?>>Select Account Type</option>
														<?php 
														$sql  = "SELECT * FROM msPaymentType ";
														$sql .= " INNER JOIN dtPaymentAcc ON payPTID = ptID";
														$sql .= " WHERE ptStID = '".$DEF_STATUS_ACTIVE."' AND ptCat = '".$DEF_CATEGORY_BANK."' ";
														$sql .= " AND payStatus = '".$DEF_STATUS_ACTIVE."' AND payMbrUsername = '".$username."'";
														$result = $conn->query($sql);
														while ($row=$result->fetch_assoc()){
															$selected = ($accType == $row['ptID'])?"selected":"";
															echo '<option value="'.$row["ptID"].'" '.$selected.'>'.$row["ptDesc"].'</option>';

														}
														?>
														</select>
													</div>
												</div>
												<div class="row">
													<label class="col-md-4 text-left text-vCenter">Amount</label>
													<div class="col-md-8">
														<div class="form-group has-default">
															<!-- minimum buy voucher 50 pcs -->
															<input type="number" step="<?php echo $DEF_VOUCHER_PRICE_IDR; ?>" min="<?php echo $DEF_VOUCHER_PRICE_IDR * $DEF_MIN_BUY_VOUCHER; ?>" name="amount" id="amount" value="<?php echo $amount; ?>"  class="form-control" title="Amount of PIN" required>
															<small class="text-success" id="currAmountVch"></small>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-4"></div>
													<div class="col-md-8">
														<button type="submit" name="reqBuyV" id="reqBuyV" class="btn btn-fill btn-rose col-md-12">Request to Buy PIN </button>
													</div>
												</div>
											</div>
											<?php
											}else{
												$accType = $amount = $ptDesc = $payacc = "";
												if ($row = $query->fetch_assoc()){
													$accType = $row['finAccType'];
													$ptDesc = $row['ptDesc'];
													$amount = $row['finAmount'];
													$payacc = $row['payAcc']
											?>
											<!-- confirm buy voucher -->
											<div class="col-md-8">
												<div class="row">
													<div class="col-md-12">
														<div class="form-group has-default fa-2x">
															Step 2 : Payment Confirmation
															<input type="hidden" name="type" value="confirm">
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
															<input type="hidden" name="accType" id="accType"  class="form-control"  value="<?php echo $accType ?>">
															<input type="text"  class="form-control"  value="<?php echo $ptDesc ?>" readonly style="background-color: transparent;" title="Account Type (locked)">
														</div>
													</div>
												</div>
												<div class="row">
													<label class="col-md-4 text-left text-vCenter" id="paymentLabel">Sender Bank Account</label>
													<div class="col-md-8">
														<div class="form-group has-default">
															<input type="text" name="fromAccNo" id="fromAccNo" value="<?php echo $payacc; ?>" class="form-control" title="Bank Account" style="background-color: transparent;" readonly required>
														</div>
													</div>
												</div>
												<div class="row">
													<label class="col-md-4 text-left text-vCenter">Transaction ID</label>
													<div class="col-md-8">
														<div class="form-group has-default">
															<input type="text" name="IDTrans" id="IDTrans" value="<?php echo $IDTrans; ?>" class="form-control" title="Transaction ID / Hash" maxlength="50">
														</div>
													</div>
												</div>
												<div class="row">
													<label class="col-md-4 text-left text-vCenter">Amount</label>
													<div class="col-md-8">
														<div class="form-group has-default">
															<!-- <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" name="amount" id="amount" value="<?php //echo (numFormat($amount, 0)); ?>"  class="form-control" title="Amount of Deposit" required> -->
															<input type="text" name="amount" id="amount" value="<?php echo "Rp ".numFormat($amount,0).",00"; ?>"  class="form-control" required  readonly style="background-color: transparent;" title="Amount of PIN (locked)">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="fileinput fileinput-new text-center" data-provides="fileinput">
													<div class="fileinput-new thumbnail img-raised">
														<img src="../assets/img/add_img.jpg" alt="...">
														<h4 class="text-secondary" style="font-family: calibri;">Payment Proof</h4>
														<small class="text-danger">Maximum size 2 MB</small>
													</div>
													<div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
													<div>
														<span class="btn btn-raised btn-round btn-default btn-file">
															<span class="fileinput-new">Select image</span>
															<span class="fileinput-exists">Change</span>
															<input type="file" name="filename" id="filename" />
														</span>
														<a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
														<i class="fa fa-times"></i> Remove</a>
													</div>
												</div>
											</div>
											<div class="row  ml-auto mr-auto">												
												<div class="col-md-12">
													<button type="submit" name="submit" id="cnfPayment" class="btn btn-fill btn-rose col-md-12">Confirm Payment</button>
												</div>
											</div>
											<?php 
												}
											} 
											?>
										</div> <!-- end row -->
									</form>
								</div>
								<?php 
								$sql = "SELECT finDate, finAccType, finFromAccNo, finAmount, finTransactionID, stDesc, ptDesc FROM dtFundIn ";
								$sql .= " INNER JOIN msStatus on finStatus=stID ";
								$sql .= " INNER JOIN msPaymentType ON ptID = finAccType";
								$sql .= " WHERE (finStatus ='" . $DEF_STATUS_APPROVED . "' ";
								$sql .= " OR finStatus='" . $DEF_STATUS_PENDING . "' OR finStatus='" . $DEF_STATUS_ONPROGRESS . "' )";
								$sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
								$sql .= " AND finMbrUsername='" . $_SESSION["sUserName"] . "'";
								$sql .= " AND ptCat != '".$DEF_CATEGORY_INTERNAL_TRANSFER."'";
								$sql .= " AND date(finDate) >= '".$DEF_MUTASI_DATE."'";
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
														<td><?php echo $rowApproved["ptDesc"] ?></td>
														<td><?php echo $rowApproved["finFromAccNo"] ?></td>
														<td><?php echo "Rp ".numFormat($rowApproved["finAmount"],0).",00"; ?></td>
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
								$sql  = "SELECT finDate, finAccType, finFromAccNo, finAmount, finTransactionID, stDesc, ptDesc ";
								$sql .= " FROM dtFundIn ";
								$sql .= " INNER JOIN msStatus on finStatus=stID ";
								$sql .= " INNER JOIN msPaymentType ON ptID = finAccType";
								$sql .= " WHERE finStatus ='" . $DEF_STATUS_DECLINED . "'";
								$sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
								$sql .= " AND finMbrUsername='" . $_SESSION["sUserName"] . "'";
								$sql .= " AND ptCat != '".$DEF_CATEGORY_INTERNAL_TRANSFER."'";
								$sql .= " AND date(finDate) >= '".$DEF_MUTASI_DATE."'";
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
														<td><?php echo $rowDeclined["ptDesc"] ?></td>
														<td><?php echo $rowDeclined["finFromAccNo"] ?></td>
														<td><?php echo "Rp ".numFormat($rowDeclined["finAmount"],0).",00"; ?></td>
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
		</div> <!-- card-body -->
	</div> <!-- end card -->
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