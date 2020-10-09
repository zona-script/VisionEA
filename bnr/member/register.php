<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$registerMessage = "";
$country = $package = $username = $name = $IDType = $IDN = $BOD = $sponsorUsername = $sponsorName = $password = $rePassword = $mblCountryCode = $mobile = $codeMobile = $email = $state = $city = $address = $agree = "";

$q = isset($_GET['q'])?$_GET['q'] : "";

$sponsorUsername = $_SESSION["sUserName"]; //default 

$code = $secureCodeMessage = "";
$BOD = date("Y-m-d");  //today
if (!empty($_POST)) { 
	$code = fValidateInput($_POST["code"]); //secure code
	$package = fValidateSQLFromInput($conn, $_POST["package"]); 
	$username = fValidateSQLFromInput($conn, $_POST["username"]); 
	$name = fValidateSQLFromInput($conn, $_POST["name"]);
	$IDType =  fValidateSQLFromInput($conn, $_POST["IDType"]);
	$IDN = fValidateSQLFromInput($conn, $_POST["IDN"]); 
	$BOD = fValidateSQLFromInput($conn, $_POST["BOD"]);
	$sponsorUsername = fValidateSQLFromInput($conn, $_POST["sponsorUsername"]); 
	// $sponsorName = fValidateSQLFromInput($conn, $_POST["sponsorName"]);
	$password = md5(fValidateSQLFromInput($conn, $_POST["password"])); 
	$rePassword = fValidateSQLFromInput($conn, $_POST["rePassword"]); 
	$mblCountryCode = fValidateSQLFromInput($conn, $_POST["mblCountryCode"]); 
	$codeMobile	= fValidateSQLFromInput($conn, $_POST["codeMobile"]);
	$mobile = fValidateSQLFromInput($conn, $_POST["mobile"]); 
	$email = fValidateSQLFromInput($conn, $_POST["email"]); 
	$country = fValidateSQLFromInput($conn, $_POST["country"]); 
	$state = fValidateSQLFromInput($conn, $_POST["state"]); 
	$city = fValidateSQLFromInput($conn, $_POST["city"]); 
	$address = fValidateSQLFromInput($conn, $_POST["address"]); 
	$agree = fValidateSQLFromInput($conn, $_POST["agree"]); 
	$tjVerifyCode = uniqid(); //strtotime(now);

	if ($package != "" && $username != "" && $name != "" && $IDType != "" && $IDN != "" && $sponsorUsername != "" && $password != "" && $codeMobile != "" && $mobile != "" && $email != "" && $country != "" && $state != "" && $city != "" && $address != ""){
		// echo fCekVerificationID($conn , $username, $IDN); die();
		$isValid = true;
		$umurMember = date_create($BOD);
		$umurMember = date_format($umurMember, "Y-m-d");
		$currDate = strtotime($CURRENT_TIME);
		$minUmur = strtotime("$umurMember +18 years");
		// cek minimal umur registrasi
		if ($currDate <= $minUmur){
			$isValid = false;
			$registerMessage = "Min. 18 years old";
		}else{
			// cek nomor ktp
			if (!fCekVerificationID($conn, $username, $IDN)){
				$isValid = false;
				$registerMessage = "ID Number has been used.";
			}
		}
		if ($isValid){
			if ($code != $_SESSION["code"]){
				$secureCodeMessage = "Invalid Secure Code";
			}else {
				$arrData = array(
						0 => array ("db" => "tjUsername"	, "val" => $username),
						1 => array ("db" => "tjSponsor"		, "val" => $sponsorUsername),
						2 => array ("db" => "tjFirstName"	, "val" => $name),
						3 => array ("db" => "tjPasswd"		, "val" => $password),
						4 => array ("db" => "tjIDType"		, "val" => $IDType),
						5 => array ("db" => "tjIDN"			, "val" => $IDN),
						6 => array ("db" => "tjEmail"		, "val" => $email),
						7 => array ("db" => "tjMobileCode"	, "val" => $mblCountryCode),
						8 => array ("db" => "tjMobile"		, "val" => $mobile),
						9 => array ("db" => "tjBOD"			, "val" => $BOD),
						10 => array ("db" => "tjAddr"		, "val" => $address),
						11 => array ("db" => "tjCountry"	, "val" => $country),
						12 => array ("db" => "tjState"		, "val" => $state),
						13 => array ("db" => "tjCity"		, "val" => $city),
						14 => array ("db" => "tjPackage"	, "val" => $package),
						15 => array ("db" => "tjStID"		, "val" => $DEF_STATUS_PENDING),
						16 => array ("db" => "tjDate"		, "val" => "CURRENT_TIME()"),
						17 => array ("db" => "tjVerifyCode", "val" => $tjVerifyCode)
						);
					
				//check sponsor name
				//check existing username before insert
				$sql = "SELECT mbrUsername, mbrStID FROM dtMember WHERE mbrUsername='" . $sponsorUsername . "'";
				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				if ($query->num_rows == 0){
					$registerMessage = "<b>Register Failed</b><br>Sponsor name not found";
				}else {
					if ($row['mbrStID'] != $DEF_STATUS_ACTIVE){
						$registerMessage = "<b>Register Failed</b><br>Sponsor not active";
					}else{
						//check existing username before insert
						$sql = "SELECT mbrUsername, mbrEmail FROM dtMember WHERE mbrUsername='" . $username . "' or mbrEmail ='" . $email ."'";
						$sql .= " UNION select tjUsername, tjEmail from dtTempJoin WHERE tjUsername = '". $username . "' or tjEmail='" . $email . "'";
						$sql .= " UNION SELECT trProUserBeli, '' FROM trProduct WHERE trProUserBeli = '".$username."'";
						$query = $conn->query($sql);
						if ($query->num_rows > 0){
							while($row = $query->fetch_assoc()) {
								if ($row["mbrUsername"] == $username && $row["mbrEmail"] == $email )
									$registerMessage = "Register Failed - Username and email have been used";
								else if ($row["mbrUsername"] == $username )	
									$registerMessage = "Register Failed - Username has been used";
								else if ($row["mbrEmail"] == $email )	
									$registerMessage = "Register Failed - Email has been used";
							}
						}else {
							if (fInsert("dtTempJoin", $arrData, $conn)){
								//insert success
								
								//send email for activation
								if (!fSendNotifToEmail("REGISTER_SUCCESS", $username)){
									//fail sending email
									fSendToAdmin('REGISTER_NEW_MEMBER', 'register.php', 'send email failed');
								}else{
									//success send email
								}
								
								//fSendToAdmin('REGISTER_NEW_MEMBER', 'register.php', 'send success');
								//redirect to success page
								header("Location: register.php?q=info-success");
								die();
							}else{
								//insert fail	
								//back for re-register
								$registerMessage = "Register Failed";
								
								//send notif to admin
								fSendToAdmin('REGISTER_NEW_MEMBER', 'register.php', 'sql: insert failed');
							} // end else
						} //end else
					}
				} // end else
			} //end validasi secure code
		}
	}else{
		$registerMessage = "Incomplete Data";
		//fSendToAdmin('REGISTER_NEW_MEMBER', 'newRegLink.php', 'sql: ' . $registerMessage);
	}
} //end $_POST
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Register New Account</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
	function getDataSponsor(){
		//alert ("on blur");
		$id = $("#sponsorUsername").val();
		/*
		$.ajax({url: "./getData.php?q=sponsor&id=" + $id, 
				success: function(result){
					alert (result);
					//$("#sponsorName").val(result);
				}
		})
		*/
		if ($id != ""){
			$.get("getData.php?q=sponsor&id="+$id, function(data, status){
				//alert("Data: " + data + "\nStatus: " + status);
				$("#sponsorName").val(data);
				if ((data == "wrong sponsor's username")  || (data == "not under your genealogy tree" || (data == "this sponsor not active"))){
					$("#sponsorName").attr("class", "form-control text-danger");
				}else{
					$("#sponsorName").attr("class", "form-control text-success");
				}
			});
		 }else {
				$("#sponsorName").val("");
		};
	}

	function ChechExistingUsername(){
		$result = -1;
		$id = $("#username").val().toLowerCase();
		if ($.trim($id) != ""){
			//from dtMember and dtTempJoin
			$.get("getData.php?q=username&id="+$id, function(data, status){
				if (data == "exist" || $id=="bnr"  || $id=="admin" || $id=="support" || $id=="supports" || $id=="vision"  || $id=="visions"  || $id=="visionea"  || $id=="visonrobot"  || $id=="eavision"  || $id=="vision_ea" || $id=="ea_vision"){ //bnr is folder name, could not be used
					
					demo.showNotification('top','center', 'warning', "<strong>"+$id+"</strong>" + ' has been used');
					$result = 1;
					$("#username").val("").focus();
				}else $result = 0
			});
		}	
		return ($result);
	}

	$(document).ready(function(e) {
		$("#agree").prop("checked", localStorage.getItem("tnc"));

		//Get Sponsor Name
		getDataSponsor();

		//Get Sponsor Name
		$("#sponsorUsername").on('blur', function() {
			getDataSponsor();
		});
		
		//Check Existing username
		$("#username").on('blur', function() {
			if (ChechExistingUsername() == 1){
				demo.showNotification('top','center', 'info', $id + ' has been used');
				$("#username").val("");
			}
		});
		
		//Clear RePassword
		$("#password").on('blur', function(){
			$("#rePassword").val("");	
		});
		
		$("#rePassword").on('blur', function(){
			if ($("#password").val() == $("#rePassword").val()){
				//same	
			}else{
				demo.showNotification('top','center', 'info', '<b>Info - </b> Confirmation password not same');
			}
		});
		
		//new property method
		$("#submit").click(function(){
			//check ID Type
			if ($.trim($('select[name="IDType"]').val()) == ""){
				demo.showNotification('top','center', 'info', 'Select your <b>ID Type</b>');
				return false;
			}
			//check Mobilde Country Code
			if ($.trim($('select[name="mblCountryCode"]').val()) == ""){
				demo.showNotification('top','center', 'info', 'Select your <b>Mobile Country Code</b>');
				return false;
			}
			//check Country
			if ($.trim($('select[name="country"]').val()) == ""){
				demo.showNotification('top','center', 'info', 'Select your <b>Country</b>');
				return false;
			}
			if ($('#agree').prop('checked')){
				localStorage.setItem('tnc', $('#agree').prop('checked')); // save to localStorage term and cond (checkbox)
				//alert ("true");
			}else{
				//alert ("false");
				//type = ['', 'info', 'success', 'warning', 'danger', 'rose', 'primary'];
				demo.showNotification('top','center', 'warning', 'Read and agree the <b>term and condition</b> to continue');
				return (false);	
			}
		});
		
		//Mobil Country Code
		$("#mblCountryCode").on('change', function(data, status){
			$id = $("#mblCountryCode").val();
			/*
			$.get('getData.php?q=mobilecountrycode&id=' + $id
					, function(data, status){
						$("#codeMobile").val("+" + data);
					});
					*/
			$("#codeMobile").val("+" + $id);
			
		});
		

		$("form[name='formReg']").on('submit', function() {
			$("#submit").attr("disabled", true);
			var html = $("#submit").html();
			$("#submit").html(html + '&nbsp; <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
			
		});
		
		$("#showPasswd").on("click", function(){
			var x = document.getElementById("password");
			var y = document.getElementById("rePassword");
		    if (x.type === "password") {
		        x.type = "text";
		        y.type = "text";
		    } else {
		        x.type = "password";
		        y.type = "password";
		    }
		});

		var q = $("#q").attr("value");
		if ($.trim(q) == "info-success"){
			demo.showNotification('top','center', 'success', 'Register Successfully');
			localStorage.removeItem("tnc"); // remove specific local storage
			//$("#q").html('');
		}
		$("#q").attr("value", "");

		$("#agree").on("click", function(){
			if ($('#agree').prop("checked") === true){	
				window.open("./?MNav=terms");
			}
		});

		//for each left nav a on click
	});
</script>

</head>
<body>
<span id="q" value="<?php echo ($q); ?>"></span>
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="fa fa-user-plus fa-2x" aria-hidden="true"></i>
        </div>
		 <div class="card-text">
           <h4 class="card-title">REGISTER NEW ACCOUNT</h4>
        </div>
       
    </div>
    <div class="card-body card-fix">
        <div class="content">
			<div class="container-fluid">
            <form action="register.php" method="post" name="formReg">
            	<input type="hidden" name="package" id="package" value="st">
            	<?php if ($secureCodeMessage != "" || $registerMessage != ""){ ?>
            	<div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6 text-danger">
                      <div class="alert alert-info">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <i class="material-icons">close</i>
                          </button>
                          <span><b> Info - </b> <?php echo $secureCodeMessage . $registerMessage ?></span>
                      </div>
                    </div>
                    <div class="col-md-3"></div>
                </div>
                <?php } ?>
            	<div class="row">
                	<div class="col-md-6">
                    	<div class="row">
                        	<div class="col-md-3" style="display: none;">
                                 <label class="col-form-label">Package</label>
                            </div>
	                        <div class="form-group col-md-9" style="display: none;">
                          </div>
                        </div>
                        <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">Username</label>
                            </div>
	                        <div class="form-group col-md-9">
                                 <input id="username" name="username" type="text" title="Username" class="form-control" value="<?php echo("$username"); ?>" minlength="4" maxlength='15' required>
                                 <span class="bmd-help">Username / Login ID</span>
                            </div>
                        </div>
                        <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">Name</label>
                            </div>
	                        <div class="form-group col-md-9">
                             <input id="name" name="name" type="text" class="form-control" value="<?php echo("$name"); ?>" required>
                             <span class="bmd-help" required>Your Full Name</span>
                          </div>
                        </div>
                        <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">ID Type</label>
                            </div>
	                        <div class="form-group col-md-9">
                            <select class="selectpicker" data-size="7" data-style="btn btn-primary" name="IDType" title = "Identification Type">
                                <option disabled selected>Select ID Type</option>
                                <?php 
									$sql = "SELECT * FROM msIDType";
									$query = $conn->query($sql);
									if ($query->num_rows > 0){
										while ($row = $query->fetch_assoc()){
											$selected =  ($IDType == $row["idtCode"])?" selected " : "";
											echo ("<option value='".$row["idtCode"]."' " . $selected .">".$row["idtType"]."</option>");
										}
									}
								?>
                            </select>
                          </div>
                        </div>
                        <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">ID Number</label>
                            </div>
	                        <div class="form-group col-md-9">
    	                         <input id="IDN" name="IDN" type="text"  class="form-control" value="<?php echo($IDN); ?>" required>
        	                     <span class="bmd-help">Your Identification Number</span>
            	            </div>
                		</div>   
                        <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">Birth of Date</label>
                            </div>
                            <div class="form-group col-md-9">
                                    <input type="date" class="form-control" name="BOD" value="<?php echo $BOD ?>">
                             </div>
                		</div>          
                        <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">Sponsor</label>
                            </div>
                        	<div class="form-group col-md-4">
                                <input id="sponsorUsername" name="sponsorUsername" type="text" class="form-control" value="<?php echo $sponsorUsername ?>" required>
                            	<span class="bmd-help">Sponsor's Username</span>
                            </div>
                            <div class="form-group col-md-5">
                                <input type="text" class="form-control text-primary" id="sponsorName" name="sponsorName"  value="<?php echo $sponsorName; ?>" disabled>
                            </div>
                        </div>   
                        
                         <div class="row">
                          	<div class="col-md-3">
                                 <label class="col-form-label">Password</label>
                            </div>
 	                        <div class="form-group col-sm-9">
                                 <input id="password" name="password" type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,12}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 6 and max 12 characters" class="form-control" value="<?php echo $rePassword ?>" required>
                                 <span class="bmd-help">6-12 Chars, combine uppercase, lowercase letter and number</span>	
                              </div>
                          </div>
                          <div class="row">
                          	<div class="col-md-3">
                                 <label class="col-form-label" style="text-align:left">Confirm Password</label>
                            </div>
                        	<div class="form-group col-sm-9">
                             <input id="rePassword" name="rePassword" type="password" class="form-control" value="<?php echo $rePassword ?>" required>
                          	</div>
                          </div>
                          <div class="row">
                          	<div class="col-md-3"></div>
                          	<div class="col-md-9">
                          		<div class="form-check col-md-12">
			                        <label class="form-check-label">
				                        <input class="form-check-input" type="checkbox" id="showPasswd" name="showPasswd" value="">
				                        <span class="form-check-sign">
				                            <span class="check"></span>
				                        </span>
			                          	Show Password
			                        </label>
			                    </div>
                          	</div>
                          </div>
                    </div>


                    
                    <div class="col-md-6">
                    
                    	<div class="row">
                        	<div class="col-md-3" >
                                 <label class="col-form-label" style="text-align:left">Mobile Country</label>
                            </div>
	                        <div class="form-group col-md-9">
                                <select class="selectpicker" data-size="7" data-style="btn btn-primary" name="mblCountryCode" id="mblCountryCode" title = "Single Select">
                                    <option disabled selected>Mobile Country Code</option>
                                    <?php 
									$sql = "SELECT * FROM msCountry WHERE countryStID='".$DEF_STATUS_ACTIVE."'";
									$query = $conn->query($sql);
									if ($query->num_rows > 0){
										while ($row = $query->fetch_assoc()){
											$selected =  ($mblCountryCode == $row["countryMobileCode"])?" selected " : "";
											echo ("<option value='".$row["countryMobileCode"]."' " . $selected . ">".$row["countryDesc"] . " (" . $row["countryMobileCode"] . ")</option>");
										}
									}
									?>
                                </select>
                            </div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">Mobile</label>
                            </div>
                            <div class="form-group col-md-2">
                                <input type="text" id="codeMobile" class="form-control" style="background:none" name="codeMobile" value="<?php echo $codeMobile ?>" readonly>
                            </div>
                            <div class="form-group col-md-5">
                                <input type="number" class="form-control" name="mobile" value="<?php echo $mobile ?>" required>
                                <span class="bmd-help">Prefix + Number</span>
                            </div>
                        </div>     
                              								
                        <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">Email</label>
                            </div>
	                        <div class="form-group col-md-9">
                             <input id="email" name="email" type="email" class="form-control" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" value="<?php echo $email ?>" required>
                            </div>
                        </div>
                        <!--
                         <div class="col-md-12">
                            <select class="selectpicker" data-size="7" data-style="btn btn-primary " title "Single Select">
                                <option disabled selected>State</option>
                                <option value="2">..</option>
                                <option value="3">..</option>
                            </select>
                         </div>
                         <div class="col-md-12">
                            <select class="selectpicker" data-size="7" data-style="btn btn-primary " title "Single Select">
                                <option disabled selected>City</option>
                                <option value="2">..</option>
                                <option value="3">..</option>
                            </select>
                         </div>
                         -->
                         <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">Country</label>
                            </div>
	                        <div class="form-group col-md-9">
                                <select class="selectpicker" data-size="7" data-style="btn btn-primary" name="country" title = "Country">
                                    <option disabled selected>Select Your Country</option>
                                    <?php 
									$sql = "SELECT * FROM msCountry WHERE countryStID='".$DEF_STATUS_ACTIVE."'";
									$query = $conn->query($sql);
									if ($query->num_rows > 0){
										while ($row = $query->fetch_assoc()){
											$selected =  ($country == $row["countryID"])?" selected " : "";
											echo ("<option value='".$row["countryID"]."' " . $selected . ">".$row["countryDesc"]."</option>");
										}
									}
									?>
                                </select>
                            </div>
                         </div>
                         <div class="row">
                         	<div class="col-md-3">
                                 <label class="col-form-label">Address</label>
                            </div>
	                        <div class="form-group col-md-9">
                             <input id="address" name="address" type="text" class="form-control" value="<?php echo $address; ?>" required>
                            </div>
                         </div> 
                         <div class="row">
                         	<div class="col-md-3">
                                 <label class="col-form-label">State</label>
                            </div>
	                        <div class="form-group col-md-9">
                             <input id="state" name="state" type="text" class="form-control" value="<?php echo $state; ?>" required>
                            </div>
                        </div>
                        <div class="row">
                        	<div class="col-md-3">
                                 <label class="col-form-label">City</label>
                            </div>
	                        <div class="form-group col-md-9">
                             <input id="city" name="city" type="text" class="form-control" value="<?php echo $city; ?>" required>
                            </div>
                        </div>
                        <div class="row">
                        	<div class="col-md-3"></div>
                             <div class="col-md-5">
	                             <img id="siimage" align="left" border= "0" src="../captcha.php?sid=<?php echo md5(time()); ?>" /> 
                                 <!-- pass a session id to the query string of the script to prevent ie caching -->                            
                             
                                 <a tabindex="-1" style="border-style: none" href="#" title="Refresh Image" onclick="document.getElementById('siimage').src = '../captcha.php?sid=' + Math.random(); return false">
                                <img src="../assets/img/captcha/refresh_captcha.jpg" height="40px" alt="Reload Image" border="0" onclick="this.blur()" align="bottom" />
                                </a>
                             </div>
                         </div>
                         <div class="row">
                         	<div class="col-md-3">
                                 <label class="col-form-label">Secure Code</label>
                            </div>   
                            <div class="form-group col-md-9">
                                	<input class="form-control" type="text" name="code" id="in_code" required>
                            </div>
                         </div>
                         <div class="form-check col-md-12">
                            <label class="form-check-label">
                                  <input class="form-check-input" type="checkbox" id="agree" name="agree" value="">
                                  <span class="form-check-sign">
                                      <span class="check"></span>
                                  </span>
                                  I have read and agree to <a href="./?MNav=terms" target="_BLANK">the terms & conditions</a> of <?php echo $COMPANY_NAME ?> 
                              </label>
                        </div>
                        <div>
                            <button type="submit" id="submit" class="btn btn-fill btn-rose col-md-12">Submit</button>
                        </div>
                      </div>
                         		
                        
					</div> <!-- end col -->
                </form>
                </div> <!-- end row -->
             </div> <!-- end body card -->
        </div>
              
        
         <!-- end card -->
                     
        <hr />
        
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


<?php fCloseConnection($conn); ?>
</html>