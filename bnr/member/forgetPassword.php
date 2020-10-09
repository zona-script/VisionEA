<?php
include_once("../includes/inc_def.php");
//include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>VisionEA - Forget Password</title>
	<link rel="icon" href="../images/favicon.png" sizes="16x16 32x32" type="image/png">
	<!-- BOOTSTRAP STYLES-->
	<link href="../assets/css/bootstrap.css" rel="stylesheet" />
	<link href="../assets/css/login.css" rel="stylesheet" />
	<link href="../assets/css/newBinary.css" rel="stylesheet" />
	<!-- FONTAWESOME STYLES-->
	<link href="../assets/css/font-awesome.css" rel="stylesheet" />
	<!-- CUSTOM STYLES-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


	<script>
		$(document).ready(function(e) {
			$("#btnCancel").click(
				function(){
					location.href = "login.php";
				});
			
			$("#login").click(function(){
				$isValid = true;
				if ($("#uname").val() == ""){
					$("#idfauser").attr("class", "fa fa-user text-danger");
					$isValid = false;
				}else $("#idfauser").attr("class", "fa fa-user text-success");
				
				if ($("#email").val() == ""){
					$("#idemail").attr("class", "fa fa-lock text-danger");
					$isValid = false;
				}else $("#idemail").attr("class", "fa fa-lock text-success");
				
				/*
				if ($isValid){
					var username = $("#uname").val();
					var passwd   = $("#psw").val();
					var xhttp = new XMLHttpRequest();
					xhttp.open("POST", "CekAuth.php", true, username, passwd);
					xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					xhttp.send();
				}
				*/
				if ($isValid){
					var username = $("#uname").val();
					var email   = $("#email").val();
					var code   	 = $("#in_code").val();
					$.post(
						"resetPassword.php",
						{
							"username": username,
							"email": email,
							"code"	: code,
						},
						function(data, status){
							if (data == "valid"){
								$("#statusforgetPassword").html("<h4><b>Reset Password</b></h4><h5>To complete the reset password process <br>look for an email in your inbox that provides further instructions.</h5>");
								$("#statusforgetPassword").attr("class", "row text-success text-center");
								
								$("#uname").val("");
								$("#email").val("");
								$("#in_code").val("");
								
								$("#inputContainer").attr("hidden", "true");
							}else {
								$("#statusforgetPassword").html(data);
								document.getElementById('siimage').src = '../captcha.php?sid=' + Math.random();
								$("#in_code").val("");

								$("#login").attr("disabled", false);
								$("#login").html("Reset Password");
							}

						}
						);
					
				}
			});

			$("#formLogin").on('submit', function(){
				$("#login").attr("disabled", true);
				var html = $("#login").html();
				$("#login").html(html + '&nbsp; <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
			});
		});
	</script>
	<style>
		body {
			/* Location of the image */
			background-image: url(../images/bg-Login03.jpg);

			/* Background image is centered vertically and horizontally at all times */
			background-position: center center;

			/* Background image doesn't tile */
			background-repeat: no-repeat;

        /* Background image is fixed in the viewport so that it doesn't move when 
        the content's height is greater than the image's height */
        background-attachment: fixed;

        /* This is what makes the background image rescale based
        on the container's size */
        background-size: cover;

        /* Set a background color that will be displayed
        while the background image is loading */
        background-color: #464646;
    }
</style>
</head>
<body onLoad="document.getElementById('id01').style.display='block'">
	<div id="id01" class="modal">
		<form class="modal-content animate" id="formLogin" action="login.php" method="post" onSubmit="return false;" >
			<div class="imgcontainer">
				<!-- <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span> -->
				<img src="..\images\<?php echo $COMPANY_LOGO ?>" alt="Avatar" class="">
				<H4>FORGET PASSWORD</H4>
				<div id="statusforgetPassword" class="row text-danger text-center">&nbsp;</div>
			</div>
			<div class="container" id="inputContainer" >
				<label for="uname"><b>Username</b></label>
				<div class="form-group input-group">
					<span class="input-group-addon"><i id="idfauser" class="fa fa-user"></i></span>
					<input type="text" placeholder="Enter Username" id="uname" name="uname" required>
				</div>
				<label for="email"><b>Email</b></label>
				<div class="form-group input-group">
					<span class="input-group-addon"><i id="idemail" class="fa fa-envelope-square"></i></span>
					<input type="text" placeholder="Enter Email" id="email" name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" required>
				</div>
				<div class="col-md-6">
					<img id="siimage" align="left" border: "0" src="../captcha.php?sid=<?php echo md5(time()); ?>" /> 
					<!-- pass a session id to the query string of the script to prevent ie caching -->
					<a tabindex="-1" style="border-style: none" href="#" title="Refresh Image" onclick="document.getElementById('siimage').src = '../captcha.php?sid=' + Math.random(); return false">
						<img src="../assets/img/captcha/refresh_captcha.jpg" height="50px" alt="Reload Image" border="0" onclick="this.blur()" align="bottom" />
					</a>
				</div>
				<div class="col-md-6">
					<label for="in_code">Secure Code</label>
					<input type="text" id="in_code" style="width:100px" required>
				</div>				
				<button type="submit" id="login">Reset Password</button>
			</div>
			<div class="container" style="background-color:#f1f1f1">
				<button type="button" id="btnCancel"  onclick="document.getElementById('id01').style.display='none'" class="cancelbtn"><< Back to Login</button>
			</div>
		</form>
	</div>
</body>
<?php fCloseConnection($conn); ?>
</html>