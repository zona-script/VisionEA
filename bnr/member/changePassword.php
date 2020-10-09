<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$q = "";
$q = isset($_GET["q"])?$_GET["q"]:"";

$changePasswordMessage = $changeSecurityMessage = $emailResetMessage = "";
$reqSec			= (isset($_GET["reqSec"]))? fValidateInput($_GET["reqSec"]) : "";
if (!empty($_POST)) { 
	$act			= (isset($_POST["act"]))? fValidateInput($_POST["act"]) : "";
	
	$currPassword 	= (isset($_POST["currPassword"]))? fValidateSQLFromInput($conn, $_POST["currPassword"]) : "";
	$newPassword	= (isset($_POST["newPassword"]))? fValidateSQLFromInput($conn, $_POST["newPassword"]) : "";
	$reNewPassword 	= (isset($_POST["reNewPassword"]))? fValidateSQLFromInput($conn, $_POST["reNewPassword"]) : "";

	$currSecurity 	= (isset($_POST["currSecurity"]))? fValidateSQLFromInput($conn, $_POST["currSecurity"]) : "";
	$newSecurity	= (isset($_POST["newSecurity"]))? fValidateSQLFromInput($conn, $_POST["newSecurity"]) : "";
	$reNewSecurity 	= (isset($_POST["reNewSecurity"]))? fValidateSQLFromInput($conn, $_POST["reNewSecurity"]) : "";

	$username	= $_SESSION["sUserName"];
	if ($act == "changePasswd" && $currPassword != "" && ($newPassword == $reNewPassword)){ //Change Password
		//checking previous password can't same with new password
		$sql 	= "SELECT mbrUsername, passID, passWord FROM dtMember inner join trPassword";
		$sql	.= " WHERE mbrUsername = passMbrUsername";
		$sql	.= " and mbrUsername='" . $username . "' and passWord='" . md5($newPassword) . "'";
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			$changePasswordMessage = "<b>Change Password Failed</b><br>Password has been used. Use a password that has never been used.";
		}else{
		    $sql 	= "SELECT mbrUsername, passID, passWord, mbrStID FROM dtMember inner join trPassword";
    		$sql	.= " WHERE mbrUsername = passMbrUsername";
    		$sql	.= " and mbrUsername='" . $username . "'";
    		$sql	.= " order by passID desc limit 1";
    		
    		$query = $conn->query($sql);
    		if ($query->num_rows > 0){
    			if ($row = $query->fetch_assoc()){
    			    if ($row["mbrStID"] == $DEF_STATUS_ACTIVE){
        				if ($row["passWord"] == md5($currPassword)){					
        					$arrData = array(
        						0 => array ("db" => "passMbrUsername"	, "val" => $username),
        						1 => array ("db" => "passWord"			, "val" => md5($newPassword)),
        						2 => array ("db" => "passDate"			, "val" => "CURRENT_TIME()")
        					);
        					if (fInsert("trPassword", $arrData, $conn)){
        						//send email for activation
        						fSendNotifToEmail("CHANGE_PASSWORD", $username);
        						
        						//fCloseConnection($conn);
        						$conn->close();
        
        						//redirect to success page
        						header("Location: changePassword.php?q=password-success");
        						die();
        						
        					}else{
        						//insert fail	
        						//back for re-register
        						$changePasswordMessage = "<b>Change Password Failed</b><br>Contact Support for help";
        					}
        					
        				}else{
        				    $changePasswordMessage = "<b>Change Password Failed</b><br>Incorrect Current Password";
        				}
    			    }else{
    			        $changePasswordMessage = "<b>Change Password Failed</b><br>Your membership not active any more";
    			    }
    			}
    		}else {
    			$changePasswordMessage = "<b>Change Password Failed</b><br>Username not found";
    		}
		}
		
	}else if ($act == "changeSec" && $currSecurity != "" && ($newSecurity == $reNewSecurity)){ //Change Security Password
		//checking previous security passwd, can't same with new security passwd
		$sql 	= "SELECT mbrUsername, pinID, pinWord FROM dtMember inner join trPIN";
		$sql	.= " ON mbrUsername = pinMbrUsername";
		$sql	.= " WHERE mbrUsername='" . $username . "'  AND pinWord = '".md5($newSecurity)."' ";
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			$changeSecurityMessage = "<b>Change Security Password Failed</b><br>Security Password has been used. Use a security password that has never been used.";
		}else{
			$sql 	= "SELECT mbrUsername, pinID, pinWord FROM dtMember inner join trPIN";
			$sql	.= " ON mbrUsername = pinMbrUsername";
			$sql	.= " WHERE mbrUsername='" . $username . "' AND pinStID='". $DEF_STATUS_APPROVED ."'";
			$sql	.= " order by DATE(pinDate) desc limit 1";
			$query = $conn->query($sql);
			if ($query->num_rows > 0){
				if ($row = $query->fetch_assoc()){
					if ($row["pinWord"] == md5($currSecurity)){		
						$conn->autocommit(false);
						//Block previous pin
						$pinID = $row['pinID'];
						$sql = "UPDATE trPIN SET pinStID='" . $DEF_STATUS_BLOCKED . "' WHERE pinID='" . $pinID . "'";		
						$query = $conn->query($sql);
						
						//Insert New PIN
						$pinID = strtotime("now");
						$arrData = array(
							0 => array ("db" => "pinID"				, "val" => $pinID),
							1 => array ("db" => "pinMbrUsername"	, "val" => $username),
							2 => array ("db" => "pinWord"			, "val" => md5($newSecurity)),
							3 => array ("db" => "pinDate"			, "val" => "CURRENT_TIME()"),
							4 => array ("db" => "pinStID"			, "val" => $DEF_STATUS_APPROVED)
						);
						if (fInsert("trPIN", $arrData, $conn)){
							$conn->commit();
							
							//send email for activation
							fSendNotifToEmail("CHANGE_SECURITY", $username);
							
							//fCloseConnection($conn);
							$conn->close();

							//redirect to success page
							header("Location: changePassword.php?q=security-success");
							die();
							
						}else{
							$conn->rollback();
							//insert fail	
							//back for re-register
							$changeSecurityMessage = "<b>Change Security Password Failed</b><br>Contact Support for help";
						}
					}else{
						$changeSecurityMessage = "<b>Change Security Password Failed</b><br>Your current security password not match";
					}
				}
			}else{
				$changeSecurityMessage = "<b>Change Security Password Failed</b><br>username not found, <br>Contact support for help";
			}
		}
	}else if ($act == "reqSec"){
		//check sent security password
		$sql = "SELECT * FROM trPIN WHERE pinMbrUsername='$username' ORDER BY pinDate DESC LIMIT 1";
		$query = $conn->query($sql);
		$row = $query->fetch_assoc();
		if ($query->num_rows > 0 && ( ($row['pinStID'] == $DEF_STATUS_PENDING) || ($row['pinStID'] == $DEF_STATUS_APPROVED)) ){
			if ($row['pinStID'] == $DEF_STATUS_PENDING){
				//exist, so resend activation of security password
				//send email for activation
				if (fSendNotifToEmail("REQUEST_SECURITY_PIN", $_SESSION["sUserName"])){
					//success
					//redirect to success page
					header("Location: changePassword.php?q=security-success&reqSec=resend");
					die();
				}else{
					//error sending email
				}
			}else if ($row['pinStID'] == $DEF_STATUS_APPROVED){
				header("Location: changePassword.php"); die();
			}
		}else{
			//1. Generate new pin, save with status pending
			$newPinWord = str_shuffle (strtotime("now"));
			//Insert New PIN
			$pinID = strtotime("now");
			$arrData = array(
				0 => array ("db" => "pinID"				, "val" => $pinID),
				1 => array ("db" => "pinMbrUsername"	, "val" => $_SESSION["sUserName"]),
				2 => array ("db" => "pinWord"			, "val" => $newPinWord), //don't encrypt first, because not yet activated.
				3 => array ("db" => "pinDate"			, "val" => "CURRENT_TIME()"),
				4 => array ("db" => "pinStID"			, "val" => $DEF_STATUS_PENDING)
			);
			
			if (fInsert("trPIN", $arrData, $conn)){
				//send email for activation
				if (fSendNotifToEmail("REQUEST_SECURITY_PIN", $_SESSION["sUserName"])){
					//success
					//redirect to success page
					header("Location: changePassword.php?q=security-success&reqSec=reqSec");
					die();
				}else{
					//error sending email

				}
				
				//NB:
				//2. send link to client to activate pin (expired in 24 hours)
				//2.1 activate pin by updating status to approved
				
			}else{
				//insert fail	
				//back for re-register
				$changeSecurityMessage = "<b>Change Security Password Failed</b><br>Contact Support for help";
				
				//send notif to admin
				//if (fSendNotifToEmail("CHANGE SECURITY-FAILED", "")){ //success 
				//}
			}
		}
	}else if ($act == "resetSec"){ //Reset Security Password
		$emailReset 	= (isset($_POST["emailReset"]))? fValidateSQLFromInput($conn, $_POST["emailReset"]) : "";
		$emailReset 	= strtolower($emailReset);
		if ($emailReset != ""){
			//Check your email
			$sql = "SELECT mbrEmail FROM dtMember WHERE mbrUsername='$username'";
			//echo $sql; die();
			$query = $conn->query($sql);
			if ($row = $query->fetch_assoc()){
				if (strtolower($row['mbrEmail']) == $emailReset){
					$conn->autocommit(false);
					//1. Update Status Previous Security password to BLOCKED
					//Update table trPIN
					$arrData = array("pinStID" => $DEF_STATUS_BLOCKED);
					$arrDataQuery = array("pinMbrUsername" => $username, "pinStID" => $DEF_STATUS_APPROVED);
					if (!fUpdateRecord("trPIN", $arrData, $arrDataQuery, $conn)){
						echo (fSendStatusMessage("error", $conn->error));
						$conn->rollback();	
						die();
					}
					unset($arrData);
					unset($arrDataQuery);

					//NB: 2nd phase, has same code with request security password
					//2. Generate new pin, save with status pending 
					$newPinWord = str_shuffle (strtotime("now"));
					//Insert New PIN
					$pinID = strtotime("now");
					$arrData = array(
						0 => array ("db" => "pinID"				, "val" => $pinID),
						1 => array ("db" => "pinMbrUsername"	, "val" => $_SESSION["sUserName"]),
						2 => array ("db" => "pinWord"			, "val" => $newPinWord), //don't encrypt first, because not yet activated.
						3 => array ("db" => "pinDate"			, "val" => "CURRENT_TIME()"),
						4 => array ("db" => "pinStID"			, "val" => $DEF_STATUS_PENDING)
					);
					
					if (fInsert("trPIN", $arrData, $conn)){
						$conn->commit();

						//send email for activation
						if (fSendNotifToEmail("REQUEST_SECURITY_PIN", $_SESSION["sUserName"])){
							//success
							//redirect to success page
							header("Location: changePassword.php?q=security-success&reqSec=resetSec");
							die();
						}else{
							//error sending email

						}
						
						//NB:
						//2. send link to client to activate pin (expired in 24 hours)
						//2.1 activate pin by updating status to approved
						
					}else{
						//insert fail	
						$conn->rollback();	

						//back for re-register
						$emailResetMessage = "<b>Reset Security Password Failed</b><br>Contact Support for help";
						
						//send notif to admin
						//if (fSendNotifToEmail("CHANGE SECURITY-FAILED", "")){ //success 
						//}
					}
					//end of 2nd phase

				}else{
					$emailResetMessage = "<b>Reset Security Password Failed</b><br>Invalid Email address";
				}
			}else{
				$emailResetMessage = "<b>Reset Security Password Failed</b><br>Invalid Username, please relogin";
			}
		}else{
			$emailResetMessage = "<b>Reset Security Password Failed</b><br>Incomplete Data";
		}

	}//end 
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Change Password</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script>
	$(document).ready(function(e) {
		//check new password and re-new password
		$("#submitPassword").on('click', function(){
	        if ($("input[name='newPassword']").val() == $("input[name='reNewPassword']").val() ){
	        	if ($("input[name='newPassword']").val() == $("input[name='currPassword']").val() ){
	        		demo.showNotification('top','center', 'info', 'The new password should not be the same as the current password.');
					$("input[name='newPassword']").focus();
					return false;
	        	}else{
	        		return true;
	        	}
			}else {
				demo.showNotification('top','center', 'info', 'Your new password not match');
				$("input[name='newPassword']").focus();
				return false;
			}
		});
		
		//Check new security and re-security
		$("#submitSecurity").on('click', function(){
			if ($("#actchangeSec").val() == "changeSec"){
				if ($("input[name='newSecurity']").val() == $("input[name='reNewSecurity']").val() ){
		        	if ($("input[name='newSecurity']").val() == $("input[name='currSecurity']").val() ){
		        		demo.showNotification('top','center', 'info', 'The new security password should not be the same as the current security password.');
						$("input[name='newSecurity']").focus();
						return false;
		        	}else{
		        		return true;
		        	}
				}else {
					demo.showNotification('top','center', 'info', 'Your new Security Password not match');
					$("input[name='newSecurity']").focus();
					return false;
				}
			}
		});

		//Check tick agreement to reset security password
		$("#submitResetSecurity").on('click', function(){
			if ($("#agreeReset").prop("checked")){
				return true;
			}else{
				demo.showNotification('top','center', 'info', 'tick the approval checkbox to reset your security password');
				return false;
			}
		});
		
		
		
		$(document).ready(function(){
			$("#q").html('<?php echo ($q); ?>');
			if ($.trim($("#q").html()) == "password-success"){
				demo.showNotification('top','center', 'success', 'Password Changed Successfully');

			}
			if ($.trim($("#q").html()) == "security-success"){
				$reqSec = $.trim($("input[name='reqSec']").val());
				if ($reqSec =="reqSec"){
					demo.showNotification('top','center', 'success', 'Your request has been sent<br>Check your email inbox/spam box to activate your security password');
				}else if ($reqSec =="resend"){
					demo.showNotification('top','center', 'success', 'Your request has been resent<br>Check your email inbox/spam box to activate your security password');
				}else if ($reqSec =="resetSec"){
					demo.showNotification('top','center', 'success', 'Security Password has been reset<br>Check your email inbox/spam box to activate your security password');
				}else{
					demo.showNotification('top','center', 'success', 'Security Password Changed Successfully');
				}
			}

			$("#q").html(''); //clear it again
		});

		
		
		$("form[name='formChangePassword']").on('submit', function() {
			$("#submitPassword").attr("disabled", true);
			var html = $("#submitPassword").html();
			$("#submitPassword").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
		});

		$("form[name='formReqSec']").on('submit', function() {
			$("#submitSecurity").attr("disabled", true);
			var html = $("#submitSecurity").html();	
			$("#submitSecurity").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
			//alert ("ss");
		});

		$("form[name='formResetSecurity']").on('submit', function() {
			$("#submitResetSecurity").attr("disabled", true);
			var html = $("#submitResetSecurity").html();	
			$("#submitResetSecurity").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
			//alert ("ss");
		});

		
                  
    });


</script>

</head>
<body>
<span id="q"></span>
    <div class="card">
        <div class="card-header card-header-success card-header-icon">
            <div class="card-icon">
              <i class="fa fa-key fa-2x" aria-hidden="true"></i>
            </div>
            <div class="card-text">
               <h4 class="card-title">Change My [Security] Password</h4>
            </div>
        </div> <!-- end card-header -->
        <!---- left card ------>
        <div class="card-body card-fix">
            <div class="content">
                <div class="container-fluid">
                	<div class="row">
                    	<div class="col-md-12">
                    	<?php if ($changePasswordMessage != "" || $changeSecurityMessage != "" || $emailResetMessage != ""){ ?>
                            <div class="row">
                                <div class="col-md-3"></div>
                                <div class="col-md-6 text-danger">
                                  <div class="alert alert-warning">
                                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                          <i class="material-icons">close</i>
                                      </button>
                                      <span><?php echo $changePasswordMessage . $changeSecurityMessage . $emailResetMessage ?></span>
                                  </div>
                                </div>
                                <div class="col-md-3"></div>
                            </div>
                         <?php } ?>
                         </div>
                    </div>
                    <div class="row">
                         <div class="col-md-6">
                            <form action="changePassword.php" method="POST" name="formChangePassword">
                            	<input type="hidden" name="act" id="actchangePasswd" value="changePasswd">
                                <div class="card ">
                                    <div class="card-header card-header-rose card-header-icon">
                                        <h4 class="card-title"><b>Change Password</b></h4>
                                    </div>
                                    <div class="card-body ">
                                        
                                            <div class="form-group">
                                                <label for="currPassword" class="bmd-label-floating">Current Password</label>
                                                <input type="password" class="form-control" name="currPassword" id="currPassword" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="newPassword" class="bmd-label-floating">New Login Password</label>
                                                <input type="password" class="form-control" name="newPassword" id="newPassword" pattern=".{6,12}" title="6 to 12 characters" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="reNewPassword" class="bmd-label-floating">Retype New Password</label>
                                                <input type="password" class="form-control" name="reNewPassword" id="reNewPassword" pattern=".{6,12}" title="6 to 12 characters" required>
                                            </div>

                                     </div>
                                         <div class="card-footer ">
                                            <button type="submit" id="submitPassword" name="submitPassword" class="btn btn-fill btn-rose col-md-12">Update Password</button>
                                         </div>
                                    </div>
                                 </form>
                            </div> <!-- end col-md-6 -->
                        
                    	
                         <div class="col-md-6">
                         	<?php
							$sUserName = $_SESSION['sUserName'];
							$sql = "SELECT * FROM trPIN WHERE pinMbrUsername = '" . $sUserName . "'"; 
							$sql .= " AND (pinStID='". $DEF_STATUS_APPROVED . "' OR pinStID='". $DEF_STATUS_PENDING . "')";
							$sql .= " ORDER BY pinDate DESC LIMIT 1";
							//NB: as long as has pending request or existing pin, member can not do request new security password.
							$query = $conn->query($sql);
							$pinID = "";
							//if ($query->num_rows > 0){
    						if ($row = $query->fetch_assoc()){
    							if ($row['pinStID'] == $DEF_STATUS_APPROVED){
    								$pinID = $row['pinID'];
    								$titleSecurityPasswd = "Change Security Password";
    								$tombol = "Update Security Password";
    							}else if ($row['pinStID'] == $DEF_STATUS_PENDING){
    								$titleSecurityPasswd = "Resend Activation Email";
    								$tombol = "Resend Activation Email";
    							}else if ($row['pinStID'] == $DEF_STATUS_BLOCKED){
    								$titleSecurityPasswd = "Request Security Password";
    								$tombol = "Request Security Password";
    							}
							}else{
							    $titleSecurityPasswd = "Request Security Password";
    							$tombol = "Request Security Password";
							}
							?>
                             <form action="changePassword.php" name="formReqSec" method="POST">
                             	<div class="card">
                                    <div class="card-header card-header-rose card-header-icon">
	                                    <h4 class="card-title"><b><?php echo $titleSecurityPasswd ?></b></h4>
	                                </div>
                                    <div class="card-body ">
                                    	<?php
										if ($row['pinStID'] == $DEF_STATUS_APPROVED){
										?>
                                        <input type="hidden" name="act" id="actchangeSec" value="changeSec">
                                        <input type="hidden" name="reqSec" id="reqSec" value="<?php echo $reqSec ?>">
                                        <div class="form-group">
                                            <label for="currSecurity" class="bmd-label-floating">Current Security Password</label>
                                            <input type="password" class="form-control" name="currSecurity" id="currSecurity" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="newSecurity" class="bmd-label-floating">New Security Password</label>
                                            <input type="password" class="form-control" name="newSecurity" id="newSecurity" pattern=".{6,10}" title="6 to 10 characters" maxlength="10" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="reNewSecurity" class="bmd-label-floating">Confirm New  Security Password</label>
                                            <input type="password" class="form-control"  name="reNewSecurity" id="reNewSecurity" pattern=".{6,10}" title="6 to 10 characters" maxlength="10" required>
                                        </div>
                                        <?php
										}else{
										?>
                                        <input type="hidden" name="act" value="reqSec"> 
                                        <input type="hidden" name="reqSec" id="reqSec" value="<?php echo $reqSec ?>">
                                        	<?php
                                        	if ($row['pinStID'] == $DEF_STATUS_PENDING){
                                        		echo ("A new security password has been sent to your email, if you are sure you have not received it, please click button below to Resend Activation Email.");
                                        	}else{
                                        		echo ("Click button below to request your security password.<br>A security password will be sent to your email.");
                                        	}
                                        	?>
                                        <?php 
										}
										?>
                                    </div>
                                    <div class="card-footer ">
                                    	<button type="submit" id="submitSecurity" name="submitSecurity" class="btn btn-fill btn-rose col-md-12"><?php echo $tombol ?></button>
                                    </div>
                                </div> <!-- end card -->
                            </form>
                            <form action="changePassword.php" name="formResetSecurity" method="POST">
                            	<input type="hidden" id="actresetSec" name="act" value="resetSec">
                             	<div class="card">
                                    <!-- Reset security password -->
                                    <?php
                                    if ($pinID != ""){
                                    ?>
                                    <div class="card-header card-header-rose card-header-icon">
	                                    <h4 class="card-title"><b>Reset Security Password</b></h4>
	                                </div>
                                    <div class="card-body ">
                                    	<div class="form-group">
                                            <label for="emailReset" class="bmd-label-floating">Email</label>
                                            <input type="email" class="form-control" name="emailReset" id="emailReset" required>
                                        </div>
                                    	<input type=checkbox id="agreeReset"> I agree to reset my security password.
                                    	<br><br>
                                    	<p>A new Security Password will be sent to your email.</p>
                                    </div>
                                    <div class="card-footer ">
                                    	<button type="submit" id="submitResetSecurity" name="submitResetSecurity" class="btn btn-fill btn-warning col-md-12">Reset Security Password</button>
                                    </div>
                                    <?php
                                	}
                                    ?>
                                </div> <!-- end card -->
                            </form>
                         </div> <!-- end col -->
                	</div> <!-- end row -->
                 </div> <!-- end container fluid -->
            </div> <!-- end content -->
        </div> <!-- end card-body card-fix -->
    
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

<?php fCloseConnection($conn); ?>
</html>