<?php 
$status = $message = "";
if (!empty($_GET)) { 
	$status		= (isset($_GET["status"]))? fValidateInput($_GET["status"]) : "";
	$message	= (isset($_GET["message"]))? fValidateInput($_GET["message"]) : "";
}
?>
<script>
	$(document).ready(function(){


		$('form[name="formPasswd"]').on('submit', function() {
		//$('#submitPasswd').on('click', function(){
			if ($("#passwd").val() != $("#passwd2").val() ){
				//$("#notif").html("Your new password not match");
				//$("#notif").addClass("alert alert-warning");
				$("#notif").html("<span class='alert alert-warning'>Your new password not match</span");
				//return(false);
			}else{
				if ($("#passwd").val() != $("#currPasswd").val() ){
					$("#submitPasswd").attr("disabled", true);
					var html = $("#submitPasswd").html();
					$("#submitPasswd").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
					var currPasswd = $("#currPasswd").val();
					var newPasswd = $("#passwd").val();
					$.post("json.php",
	    					{
	    						"q"		: "changePasswd",
	    						"currPasswd": currPasswd,
	    						"newPasswd"	: newPasswd,

	    					},
	    					function(data, status){
	    						$myDataObj  = JSON.parse(data);
	    						if ($.trim($myDataObj["status"]) == "success"){
	        						location.href = "./?menu=changePasswd&status=success&message="+$.trim($myDataObj["message"]);
	        					}else{
	        						//$("#notif").html($.trim($myDataObj["message"]));
									//$("#notif").addClass("alert alert-danger");
									$("#notif").html("<span class='alert alert-danger'>" + $.trim($myDataObj["message"]) + "</span");
									$("#submitPasswd").html("Change Password");
									$("#submitPasswd").attr("disabled", false);
	        					}
	    					}
	    			);
				}else{
					$("#notif").html("<span class='alert alert-warning'>The new password can not be the same as the old password</span");
				}
			}
			return (false);
		});


		$('form[name="formSecurity"]').on('submit', function() {
		//$('#submitSecurity').on('click', function(){
			if ($("#securePasswd").val() != $("#securePasswd2").val() ){
				//$("#notif").html("Your new password not match");
				//$("#notif").addClass("alert alert-warning");
				$("#notif").html("<span class='alert alert-warning'>Your new security password not match</span");
				//return(false);
			}else{
				$("#submitSecurity").attr("disabled", true);
				var html = $("#submitSecurity").html();
				$("#submitSecurity").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
				var currSecure = $("#currSecure").val();
				var securePasswd = $("#securePasswd").val();
				$.post("json.php",
    					{
    						"q"		: "changeSecurity",
    						"currSecure"	: currSecure,
    						"securePasswd"	: securePasswd,

    					},
    					function(data, status){
    						$myDataObj  = JSON.parse(data);
    						if ($.trim($myDataObj["status"]) == "success"){
        						location.href = "./?menu=changePasswd&status=success&message="+$.trim($myDataObj["message"]);
        					}else{
        						//$("#notif").html($.trim($myDataObj["message"]));
								//$("#notif").addClass("alert alert-danger");
								$("#notif").html("<span class='alert alert-danger'>" + $.trim($myDataObj["message"]) + "</span");
								$("#submitSecurity").html("Change Security Password");
								$("#submitSecurity").attr("disabled", false);
        					}
    					}
    			);
			}
			return (false);
		});


	});
</script>
<div class="col-sm-12">
	<div class="well">
		<div class="row">
			<div class="col-md-3 col-sd-1">&nbsp;</div>
			<div class="col-md-6 col-sd-10" id="notif">
			<?php  
				if ($status == "success") echo ("<span class='alert alert-success'>".$message."</span>");
			?>
			</div>
			<div class="col-md-3 col-sd-1">&nbsp;</div>
		</div>
	    <div class="row">
	    	<div class="col-md-6">
	    		<h4>Change Password</h4>
	    		<div class="row">
	    			<form name="formPasswd" method="post" action="changePasswd.php">
	    				<div class="col-md-8">
			    			<div class="form-group">
							  <label for="currPasswd">Current Password:</label>
							  <input type="password" class="form-control" id="currPasswd" required>
							</div>
							<div class="form-group">
							  <label for="passwd">New Password:</label>
							  <input type="password" class="form-control" id="passwd" required>
							</div> 
							<div class="form-group">
							  <label for="passwd2">Retype Password:</label>
							  <input type="password" class="form-control" id="passwd2" required>
							</div> 
							<div class="form-group">
			    				<button type="submit" class="form-control btn btn-primary" id="submitPasswd">Change Password</button>
			    			</div>
		    			</div>
	    			</form>
	    		</div>
	    	</div>
	    	<div class="col-md-6">
	    		<h4>Change Security Password</h4>
	    		<div class="row">
	    			<form name="formSecurity" method="post" action="changePasswd.php">
	    				<div class="col-md-8">
			    			<div class="form-group">
							  <label for="currSecure">Current Security Password</label>
							  <input type="password" class="form-control" id="currSecure" required>
							</div>
							<div class="form-group">
							  <label for="securePasswd">New Security Password:</label>
							  <input type="password" class="form-control" id="securePasswd" required>
							</div> 
							<div class="form-group">
							  <label for="securePasswd2">Retype Security Password:</label>
							  <input type="password" class="form-control" id="securePasswd2" required>
							</div> 
							<div class="form-group">
			    				<button type="submit" class="form-control btn btn-primary" id="submitSecurity">Change Security Password</button>
			    			</div>
		    			</div>
		    		</form>
	    		</div>
	    	</div>
	    </div>
	</div>
</div>