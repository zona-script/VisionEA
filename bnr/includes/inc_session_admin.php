<?php
session_start();


if (isset($_SERVER['HTTPS']) &&
    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  //$protocol = 'https://';
}else {
  //$protocol = 'http://';
	//header("Location: ".$COMPANY_SITE."admin/?menu=auth"); die();
}


if (
	(empty($_SESSION["sUserName"]) || empty($_SESSION["sFirstName"]) || empty($_SESSION["sPrivilege"]) || empty($_SESSION["sSiteSide"]))
	||
	(isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800))
	)
{
	
	
	// last request was more than 15 minutes ago
	$_SESSION["sSID"] = "1";
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
	
	$type = isset($_GET["type"])?$_GET["type"]: "";
	if ($type == "iframe"){
	?>
		<script type="text/javascript">
			window.top.location.href = "./?menu=auth";
		</script>
	<?php
	}else {
		header("Location: ".$COMPANY_SITE."admin/?menu=auth&unxid=".md5(time()));	
		die();
	}
	die();
}else{
	if ($_SESSION["sPrivilege"] != "" && $_SESSION["sSiteSide"] == "BACK-OFFICE" ){
		$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp		
	}else{
		$_SESSION["sSID"] = "1";
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage
		header ("Location: ".$COMPANY_SITE."admin/?menu=auth&unxid=".md5(time()));
		die();
	}
}
?>