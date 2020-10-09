<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");
include_once("../includes/inc_commission.php");

$q      =  (isset($_GET["q"]))?fValidateInput($_GET["q"]): "";

$username   = $_SESSION['sUserName'];
$currPac = $currPacName = $price = "";
$trThn = 0;

$sql = "SELECT trPacID, pacName ,pacPrice , mbrSponsor, trThn  FROM Transaction INNER JOIN dtMember ON trUsername = mbrUsername INNER JOIN msPackage ON pacID = trPacID WHERE trUsername ='".$username."' ORDER BY trDate DESC LIMIT 1";
$query = $conn->query($sql);
if ($row = $query->fetch_assoc()){
	$currPac = $row["trPacID"];
	$currPacName = $row["pacName"];
	$currPacPrice = $row["pacPrice"];
	$sponsor = $row['mbrSponsor'];
	$trThn = $row['trThn'];

	$sql = "SELECT trPacID FROM Transaction WHERE trUsername ='".$sponsor."' ORDER BY trDate DESC LIMIT 1";
	$query = $conn->query($sql);
	if ($row = $query->fetch_assoc()){
		$pacIDSponsor       = $row["trPacID"];
	}else{
		echo (fSendStatusMessage("error", "Sponsor's Pac ID not found"));
		die();
	}
}

	


// hitung jumlah voucher tersedia
$numOfVoucher = $VoucherBalance = 0;
$arrVoucher = "";
$myDataObj  = json_decode(fSumAvailableVoucher($username, $conn));
if ($myDataObj->{"status"} == "success"){
	$numOfVoucher = sizeof($myDataObj->data);
	$VoucherBalance = $numOfVoucher * $DEF_VOUCHER_PRICE_IDR;
	$arrVoucher = $myDataObj->data;
}

// Hitung Jumlah Wallet
$myDataObj  = json_decode(fGetBalance($username, $conn));
if ($myDataObj->{"status"} == "success"){
	$WalletBalance  = ceil($myDataObj->{'wallet'});
	$numOfWallet    = floor($WalletBalance / $DEF_VOUCHER_PRICE_IDR);
}

$msg = "";
//$accNumber = $accName = $accType = $accCode = "";
if (!empty($_POST)) { 
	// echo (fSendStatusMessage("error", print_r($_POST))); die();
	$renewPac = $securityPasswd =  $actUsername =  $allowRenewDate = "";

	$actUsername    = $_SESSION["sUserName"];
	$securityPasswd      = isset($_POST["securityPasswd"])? fValidateSQLFromInput($conn, $_POST["securityPasswd"]) : "";
	$renewPac     = isset($_POST["renewPac"])? fValidateInput($_POST["renewPac"]) : "";
	$renewPac     = 'st'; //strtolower($renewPac); set default package standart 
	$pacPrice = isset($_POST["defaultPacPrice"])? fValidateInput($_POST["defaultPacPrice"]) : "";

	$isAllowRenew = true;
	$sql = "SELECT mbrDate, (DATE_ADD(DATE_ADD( DATE(mbrDate), INTERVAL ".$trThn." YEAR ), INTERVAL -7 MONTH)) AS allowRenewDate, ";
	$sql .= " IF( DATE_ADD(DATE_ADD( DATE(mbrDate), INTERVAL ". $trThn . " YEAR ), INTERVAL -7 MONTH) <= CURRENT_DATE() , 'allowed', 'notallowed') renew ";
	$sql .= " FROM dtMember WHERE mbrUsername='" . $_SESSION["sUserName"] . "'";
	$result = $conn->query($sql);
	if ($row=$result->fetch_assoc()){
		if ($row['renew'] != "allowed"){
			$allowRenewDate = date_create($row['allowRenewDate']);
			$allowRenewDate = date_format("Y-m-d", $allowRenewDate);
			$isAllowRenew = false;
		}
	}

	if ($actUsername != "" && $securityPasswd != "" && $renewPac != "" && $pacPrice != ""){
		if ($isAllowRenew){
			//Check Security Password
			if (!fCheckSecurityPassword($actUsername, $securityPasswd, $conn)){
				echo (fSendStatusMessage("error", "Security Password not match<br>")); die();
			}else{
				//Get Package Price
				/* disabled because use default package (st)
				$pacPrice = $numOfVoucherRequired = 0;
				$sql = "SELECT pacPrice FROM msPackage WHERE pacID='" . $renewPac . "'";
				if ($query = $conn->query($sql)){
					if ($query->num_rows > 0){
						$row = $query->fetch_assoc();
						$pacPrice =  $row["pacPrice"];
					}
				}else{
					echo (fSendStatusMessage("error", mysqli_error($conn))); die();
				}
				*/
				$additionFee = $pacPrice;
				if ($additionFee > 0){
					$vAmount  = (isset($_POST["vAmount"])? fValidateInput($_POST["vAmount"]) : 0);
					// $wAmount  = (isset($_POST["wAmount"])? fValidateInput($_POST["wAmount"]) : 0);
					$numOfVoucherRequired = ceil($additionFee / $DEF_VOUCHER_PRICE_IDR);   //Number of Voucher Required (@200)
					//checking Voucher Balance

					$myDataObj  = json_decode(fSumAvailableVoucher($actUsername, $conn));
					if ($myDataObj->{"status"} == "success"){
						$numOfVoucher = sizeof($myDataObj->data);
						$VoucherBalance = $numOfVoucher * $DEF_VOUCHER_PRICE_IDR;
						$arrVoucher = $myDataObj->data;
					}
					if ($numOfVoucherRequired <= $numOfVoucher){ // if true Total Amount not enough
						// echo (fSendStatusMessage("error", "$totalAmount || $additionFee || save to table")); die();
						$conn->autocommit(false);

						//Transaction, 
						$trThn = $trThn + 1;				
						$arrData = array(
							0 => array ("db" => "trUsername"    , "val" => $actUsername),
							1 => array ("db" => "trPacID"       , "val" => $renewPac),
							2 => array ("db" => "trDate"        , "val" => "CURRENT_TIME()"),
							3 => array ("db" => "trStatus"      , "val" => $DEF_STATUS_UPGRADE),
							4 => array ("db" => "trThn"         , "val" => $trThn)
						);
						
						if (!fInsert("Transaction", $arrData, $conn)) {
							echo (fSendStatusMessage("error", "<b>Update Transaction - </b>" . mysqli_error($conn)));
							$conn->rollback();
							die();
						}
						unset($arrData);

						//dtBnsSponsor, 
						$sponsorUsername    = fGetSponsorUsername($actUsername, $conn);
						$myDataObj  = json_decode(fGetDataPackage($conn, $sponsorUsername));
						$spPacID    = $myDataObj->{"pacID"};

						/*
						//get Level of generation
						$myDataObj  = json_decode(fGetDataPackage($conn, $actUsername));
						$numOfMatchingGen   = $myDataObj->{"pacMatchingGen"};
						*/

						//$currSponsorBonus   = fGetBonus("SPONSOR", $currPacID, $spPacID, $conn);
						$newSponsorBonus    = fGetBonus("SPONSOR", 'st', $spPacID, $conn);
						$sponsorBonus       = $newSponsorBonus;
						if ($sponsorBonus > 0){
							$arrData = array(
								0 => array ("db" => "bnsSpUsername"     , "val" => $sponsorUsername),
								1 => array ("db" => "bnsSpTrUsername"   , "val" => $actUsername),
								2 => array ("db" => "bnsSpTrPacID"      , "val" => $renewPac),
								3 => array ("db" => "bnsSpDate"         , "val" => "CURRENT_TIME()"),
								4 => array ("db" => "bnsSpAmount"       , "val" => $sponsorBonus),
								5 => array ("db" => "bnsSpThn"          , "val" => $trThn)
							);
							// echo (fSendStatusMessage("error", "$actUsername || $sponsorUsername || $renewPac || $sponsorBonus")); $conn->rollback(); die();
							if (!fInsert("dtBnsSponsor", $arrData, $conn)) {
								echo (fSendStatusMessage("error", "<b>Update Bonus Sponsor - </b>" . $conn->error));
								$conn->rollback();
								die();
							}
							unset($arrData);
						}else{
							//if sponsor bonus == 0, means error
							echo (fSendStatusMessage("error", "<b>Get Bonus Sponsor Failed</b>"));
							$conn->rollback();
							die();
						}

						//Update dtFundInVoucher (status="USED", usedFor="ACTIVATION", usedOn=USERNAME)
						$arrData    = array(
							"fivDate" 		=> "CURRENT_TIME()",
							"fivStatus"     => $DEF_STATUS_USED,
							"fivUsedFor"    => $DEF_VOUCHER_USED_FOR_ACTIVATION,
							"fivUserOn"     => $actUsername
						);
						
						$arrDataQuery = array();
						$counter = 0;
						//moving some data of arrVoucher to arrDataQuery 
						foreach ($arrVoucher as $key => $value){
							// if ($counter >= $numOfVoucherRequired) {
							if ($counter >= $numOfVoucherRequired) {
								break;
							}else{
								$arrDataQuery = array (
									"fivFinID" 	=> $value->fivFinID,
									"fivStatus" => $DEF_STATUS_ACTIVE,
									"fivVCode" 	=> $value->fivVCode
								);
								$counter++;
								
								if (!fUpdateRecord("dtFundInVoucher", $arrData, $arrDataQuery, $conn)){
									echo (fSendStatusMessage("error", "<b>Update FundInVoucher - </b>" . $conn->error));
									$conn->rollback();
									die();
								}
								unset($arrDataQuery);
							}
						}
						unset($arrData);

						$sql  = " SELECT * FROM msProduct";
						$sql .= " WHERE proID = '".$DEF_EBOOK_BASIC."' ";
						$result = $conn->query($sql);
						if ($row = $result->fetch_assoc()){
							$trProTransID = strtotime("+0");
							$table = "trProduct";
							$arrData = array(
								array ("db" => "trProTransID"   	, "val" => $trProTransID),
								array ("db" => "trProUsername"  	, "val" => $sponsorUsername),
								array ("db" => "trProUserBeli"  	, "val" => $actUsername),
								array ("db" => "trProType"  		, "val" => $DEF_TYPE_PURCHASE_RENEW),
								array ("db" => "trProDate"      	, "val" => "CURRENT_TIME()"),
								array ("db" => "trProAmount"    	, "val" => $row['proPrice']),
								array ("db" => "trProDisc"      	, "val" => $row['proPrice']),
								array ("db" => "trProUpdateDate"  	, "val" => "CURRENT_TIME()"),
								array ("db" => "trProActiveDate"  	, "val" => "CURRENT_TIME()"),
								array ("db" => "trProStatus"    	, "val" => $DEF_STATUS_APPROVED)                
							);
							if (!fInsert($table, $arrData, $conn)){
								$conn->rollback();
								fSendToAdmin("Activate Member", "activateMember.php", "Insert data to trProduct failed ".mysqli_error($conn));
								$conn->commit();
								echo (fSendStatusMessage("error", "Renew failed, please contact support if usee this message #1"));
								die();
							}else{
								$table = "trProDetail";
								$arrData = array(
									array ("db" => "trPDTransID"    , "val" => $trProTransID),
									array ("db" => "trPDProID"      , "val" => $DEF_EBOOK_BASIC),
									array ("db" => "trPDPrice"      , "val" => $row['proPrice']),
									array ("db" => "trPDQty"        , "val" => "1"),
									array ("db" => "trPDDisc"       , "val" => $row['proPrice']),
									array ("db" => "trPDSubTotal"   , "val" => "0")                
								);
								if (!fInsert($table, $arrData, $conn)){
									$conn->rollback();
									fSendToAdmin("Renew Member", "renewPac.php", "Insert data to trProDetail failed ".mysqli_error($conn));
									$conn->commit();
									echo (fSendStatusMessage("error", "Renew failed, please contact support if usee this message #1" ));
									die();
								}
							}       
						}
						$conn->commit();
						fSendNotifToEmail("MEMBER_RENEW_PACKAGE", $actUsername);
						if ($_SESSION["sPrivilege"] != ""){
							$_SESSION["sPrivilege"] =  "";
						}
						echo (fSendStatusMessage("success", "")); die();
					}else{
						echo (fSendStatusMessage("error", "Your PIN Balance is not enough")); die();
					}
				}
			}//end checking security password
		}else{
			echo (fSendStatusMessage("error", "Renew Not Allowed, you can renew again start from $allowRenewDate")); die();	
		}     
	}else{
		echo (fSendStatusMessage("error", "Incomplete Data")); die();
	}
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Renew Package</title>
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
	<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
	<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
	<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script>
		$(document).ready(function(e) {
			var defaultPacPrice = parseInt($("#defaultPacPrice").val());
			var tvBalance   	= parseInt($("#tvBalance").val().replace(/[^0-9,-]+/g,""));
			var vAmount 		= parseInt($("#vAmount").val().replace(/[^0-9,-]+/g,""));
			if (vAmount != defaultPacPrice){
				$("#renewPac").attr("disabled", true);
				$("#loadMsg").html('<b>Amount Not Match</b>');
			}
			if (tvBalance < vAmount){
				$("#renewPac").attr("disabled", true);
				$("#loadMsg").html('<b>Your balance is not enough</b>');
			}

			$("form[name='formProfile']").on('submit', function() {
				var html = $("#renewPac").html();
				$("#renewPac").attr("disabled", true);
				$("#renewPac").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
				$.ajax({
					url     : "renewPac.php",
					type    : "POST",
					data    : new FormData(this),
					contentType   : false,       // The content type used when sending data to the server.
					cache         : false,             // To unable request pages to be cached
					processData   : false,        // To send DOMDocument or non processed data file it is set to false
					success       : function(data, success)  // A function to be called if request succeeds
					{
						// console.log(data);
						$myDataObj = JSON.parse(data);
						if ($.trim($myDataObj["status"])=="error"){
							$("#wAmount").attr("disabled", true);
							$("#errBox").show();
							$("#errMsg").html('<b> Error - </b>'+$myDataObj['message']);
							$("#renewPac").attr("disabled", false);
							$("#renewPac").html(html);
							return false;
						}else if ($.trim($myDataObj['status']) == "success"){
							$("#succesMsg").show();
							$("#notrenewyet").hide();
							location.href="profile.php?q=renew-success";
						}
					}
				});
			});

			if ($.trim($("#q").html()) == "info-success"){
				demo.showNotification('top','center', 'success', 'Update Account Successfully');
				$("#q").html('');
			}

			$(".alert").on("click", function(){
				$("#errBox").hide();
			});

		});
	</script>

</head>
<body>
	<span id="q" style="display:none;"><?php echo $q; ?></span>
	<div class="card">
		<div class="card-header card-header-success card-header-icon">
			<div class="card-icon">
				<i class="material-icons">control_point</i>
			</div>
			<div class="card-text"><h4 class="card-title">Renew Package</h4></div>
		</div>
		<?php
		$sql = "SELECT m.*, s.mbrUsername as spUsername, s.mbrFirstName as spName, u.mbrUsername as upUsername, u.mbrFirstName as upName, c.countryDesc, pacID, pacName, trThn FROM dtMember m ";
		$sql .= " INNER JOIN dtMember s on m.mbrSponsor = s.mbrUsername ";
		$sql .= " INNER JOIN dtMember u on m.mbrUpline = u.mbrUsername ";
		$sql .= " INNER JOIN msCountry c on m.mbrCountry = c.countryID ";
		$sql .= " INNER JOIN (SELECT * FROM Transaction WHERE trID = (SELECT trID FROM Transaction WHERE trUsername='". $_SESSION["sUserName"] . "' ORDER BY trDate DESC LIMIT 1)) t ON m.mbrUsername = t.trUsername ";
		$sql .= " INNER JOIN msPackage ON pacID = t.trPacID";
		$sql .= " WHERE m.mbrUsername = '" . $_SESSION["sUserName"] . "'";
		// echo $sql;
		if ($query = $conn->query($sql)){
			if ($row = $query->fetch_assoc()){  
				$trThn = $row['trThn'];
				?>
		<div class="card-body card-fix">
			<div class="row">
				<div class="col-md-6">
					<div class="row">
						<!-- <div class="col-md-4">Upline</div><div class="col-md-8"><?php //echo $row["upName"] . "(" . $row["upUsername"] . ")" ?></div> -->
						<div class="card col-md-11">
							<div class="card-body card-fix col-md-11">
								<h4 class="profile">Renew Package
								</h4>
								<hr>
								<div class="row">
									<form action="" method="post" name="formProfile" class="col-md-12" onsubmit="return false;">
										<div class="row" id="errBox" style="display: none;">
											<div class="col-md-2"></div>
											<div class="col-md-8 text-danger">
												<div class="alert alert-warning">
													<button type="button" class="close" aria-label="Close">
														<i class="material-icons">close</i>
													</button>
													<span id="errMsg"></span>
												</div>
											</div>
											<div class="col-md-2"></div>
										</div>
										<?php
										$sql = "SELECT mbrDate, IF( DATE_ADD(DATE_ADD( DATE(mbrDate), INTERVAL ". $trThn . " YEAR ), INTERVAL -7 MONTH) <= CURRENT_DATE() , 'renew', 'no') renew  ";
										$sql .= " FROM dtMember WHERE mbrUsername='" . $_SESSION["sUserName"] . "'";
										if ($query = $conn->query($sql)){
											$row = $query->fetch_assoc();
											if ($row['renew'] == 'renew'){
										?>
										<div class="row">
											<!-- <div class="col-md-5 profile">Voucher Balance</div>
											<div class="col-md-7 profile-val">Rp <?php echo numFormat($VoucherBalance,0).",00"; ?></div>
											<div class="col-md-5 profile">Required Amount</div>
											<div class="col-md-7 profile-val">Rp <?php echo numFormat($DEF_VOUCHER_PRICE_IDR,0).",00"; ?></div> -->
											<div class="form-group col-md-12">
												<div class="row">
													<div class="col-md-5 profile">PIN Balance</div>
													<div class="col-md-7 font-weight-bold">
														Rp <?php echo numFormat($VoucherBalance,0).",00"; ?>
														<input type="hidden" name="tvBalance" id="tvBalance"value="<?php echo $VoucherBalance; ?>">
													</div>
												</div>
											</div>
											<div class="form-group col-md-12">
												<div class="row">
													<div class="col-md-5 profile">Required Amount</div>
													<div class="col-md-7 font-weight-bold">
														Rp <?php echo numFormat($DEF_VOUCHER_PRICE_IDR,0).",00"; ?>
														<input type="hidden" name="vAmount" id="vAmount" value="<?php echo $DEF_VOUCHER_PRICE_IDR; ?>">
													</div>
												</div>
											</div>
											<input type="hidden" name="renewPac" value="<?php echo ($currPac);?>">
                                            <input type="hidden" name="currPacPrice" value="<?php echo ($currPacPrice);?>">
											<input type="hidden" name="defaultPacPrice" id="defaultPacPrice" value="<?php echo $DEF_VOUCHER_PRICE_IDR; ?>">
											<div class="col-md-5 profile">&nbsp;</div><div class="col-md-7 profile-val">&nbsp;</div>
											<div class="col-md-5 profile">Security Password</div>
											<div class="col-md-7"><input type="password" name="securityPasswd" id="securityPasswd"  value="" class="form-control"></div> 
											<div class="col-md-12"><small id="loadMsg" class="text-danger"></small></div>
										</div>

										<div class="footer">
											<button type="submit" id="renewPac" name="renewPac" class="btn btn-fill btn-rose col-md-12">Membership Renewal</button>
										</div>
										<?php
											}else{

										?>
										<div class="row" id="notrenewyet">
											<div class='col-md-12 profile'>Your package can be extended 1 month before expiration</div>
										</div>
										<div class="row" id="succesMsg" style="display: none;">
											<div class="col-md-2"></div>
											<div class="col-md-8 text-success">
												<div class="alert alert-success">
													<button type="button" class="close" aria-label="Close">
														<i class="material-icons">close</i>
													</button>
													<span>The usage period of your package has been successfully extended</span>
												</div>
											</div>
											<div class="col-md-2"></div>
										</div>
										<?php
											}
										}
										?>
									</form>
								</div> <!-- end class=row -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> <!-- end card -->
		<?php
			}
		}
		?>
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

<!--    Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
<script src="../assets/js/plugins/bootstrap-datetimepicker.min.js"></script>

<!--    Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="../assets/js/plugins/nouislider.min.js"></script>

<!--    Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/bootstrap-selectpicker.js"></script>

<!--    Plugin for Tags, full documentation here: http://xoxco.com/projects/code/tagsinput/  -->
<script src="../assets/js/plugins/bootstrap-tagsinput.js"></script>

<!--    Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
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

<?php fCloseConnection($conn); ?>
</html>