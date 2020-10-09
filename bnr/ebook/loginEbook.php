<?php
session_start();
include_once("../includes/inc_def.php");

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Ebook - Authentication</title>
	<link rel="icon" href="../../images/favicon.png" sizes="16x16 32x32" type="image/png">

	<link rel="stylesheet" href="../assets/css/bootstrap.css" />
	<link rel="stylesheet" href="../assets/css/font-awesome.css" />

	<link href="../assets/css/login.css" rel="stylesheet" />
	<link href="../assets/css/newBinary.css" rel="stylesheet" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	
	<script>
        $(document).ready(function(e) {
            $("#btnCancel").click(function(){
                location.href = "<?php echo($DOMAIN_URL); ?>";
            });

            $("#login").click(function(){
                $isValid = true;
                if ($("#uname").val() == ""){
                    $("#idfauser").attr("class", "fa fa-user text-danger");
                    $isValid = false;
                }else $("#idfauser").attr("class", "fa fa-user text-success");

                if ($("#psw").val() == ""){
                    $("#idfalock").attr("class", "fa fa-lock text-danger");
                    $isValid = false;
                }else $("#idfalock").attr("class", "fa fa-lock text-success");
                if ($isValid){
                    var username = $("#uname").val();
                    var passwd   = $("#psw").val();
                    var code   	 = $("#in_code").val();

                    $.post(
                    "cekAuthEbook.php",
                    {
                        "username": username,
                        "passwd"  : passwd,
                        "code"	  : code,
                    },
                    function(data, status){
                    	// alert(data);
                        if (data == "valid"){
                            if ($("#remember").prop("checked")){
                                location.href = "./?remember=yes";
                            }else{
                                location.href = "./";
                            }
                        }else {
                            $("#statusLogin").html("<div>Authentication Failed </div>" + data);
                            document.getElementById('siimage').src = '../captcha.php?sid=' + Math.random();
                            $("#in_code").val("");
                        }
                    });

                }
            });
        });
    </script>

	<style>
		body{
			background-image: url(../../images/mockup/BASIC_EDITION.jpg), url(../../images/mockup/PRO_EDITION.jpg);
			background-position: left top, right top;
			background-repeat:no-repeat, no-repeat;
		}
		html {
			overflow:   scroll;
		}
		::-webkit-scrollbar {
			width: 0px;
			background: transparent; /* make scrollbar transparent */
		}
	</style>
	<base target="_blank">
</head>
<body onLoad="document.getElementById('id01').style.display='block'">
	<div id="id01" class="modal">
		<form class="modal-content animate" action="" method="post" onsubmit="return false;">
			<div class="imgcontainer">
				<div class="row fa-2x text-primary">Login Ebook</div>
				<!-- <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span> -->
				<img src="../images\<?php echo $COMPANY_LOGO ?>" alt="Avatar">
				<!-- <img src="..\images<?php echo $COMPANY_LOGO ?>" alt="Avatar" class="avatar"> -->
			</div>
			<div class="container">
				<div id="statusLogin" class="row text-danger text-center">&nbsp;</div>
				<label for="uname"><b>Username</b></label>
				<div class="form-group input-group">
					<span class="input-group-addon"><i id="idfauser" class="fa fa-user"></i></span>
					<input type="text" placeholder="Enter Username" id="uname" name="uname" required>
				</div>
				<label for="psw"><b>Password</b></label>
				<div class="form-group input-group">
					<span class="input-group-addon"><i id="idfalock" class="fa fa-lock"></i></span>
					<input type="password" placeholder="Enter Password" id="psw" name="psw" required>
				</div>

				<div class="row">
					<div class="col-md-1"></div>
					<div class="col-md-11">
						<img id="siimage" align="left" src="../captcha.php?sid=<?php echo md5(time()); ?>" /> 
						<!-- pass a session id to the query string of the script to prevent ie caching -->
						<a tabindex="-1" style="border-style: none" href="#" title="Refresh Image" onclick="document.getElementById('siimage').src = '../captcha.php??sid=' + Math.random(); return false">
							<img src="../assets/img/captcha/refresh_captcha.jpg" height="50px" alt="Reload Image" border="0" onclick="this.blur()" align="bottom" /></a>
							<label for="in_code">Secure Code</label>
							<input type="text" id="in_code" style="width:25%" required>
						</div>
					</div> <!-- end row -->


					<button type="submit" class="btn-primary" id="login">Login</button>
					<label>
						<input type="checkbox"  name="remember"> Remember me
					</label>
				</div>

				<div class="container" style="background-color:#f1f1f1">
					<button type="button" id="btnCancel"  onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button> 
					<!-- <span class="psw"><a href="forgetPassword.php">Forgot password?</a></span> -->
				</div>
			</form>
		</div>
	</body>
	</html>