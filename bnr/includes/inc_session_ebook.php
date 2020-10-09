<?php
session_start();
include_once("inc_def.php");
include_once("inc_functions.php");

//Checking Protocol ____________________
$protocol = $_SERVER['SERVER_PROTOCOL'];
$pos = strpos($protocol, "HTTPS");
if ($pos === false) {
    //https
}else{
    //http
    //header ("Location: ".$COMPANY_SITE."member/"); //with https
	//die();
}
//-------------------------------------------

//Checking Maintenance Schedule
if (fIsMaintenance()){
	$_SESSION["sSID"] = "1";
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
	header ("Location: ".$COMPANY_SITE."member/maintenance.php?MNav=dailyMaintenance&unxid=".md5(time()));
	die();
}

//if ( (empty($_SESSION["sUserName"]) || empty($_SESSION["sFirstName"])  || empty($_SESSION["sSiteSide"]) ) || ( isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) ){
//sesion firstname bisa kosong karena firstname di dtUserEbook bisa kosong
if ( (empty($_SESSION["sEBUserName"])  || empty($_SESSION["sSiteSide"]) ) || ( isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) ){
	// echo "1"; die();
	// last request was more than 15 minutes ago
	$_SESSION["sSID"] = "1";
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
	
	$typeFrame = isset($_GET["type"])?$_GET["type"]:'';
	if ($typeFrame == "iframe"){

	?>
		<script type="text/javascript">
			window.top.location.href = $DOMAIN_URL."bnr/ebook/";
		</script>
	<?php
	}else {
		// echo "2"; die();
		//header("Location: ./?MNav=auth");	
		header ("Location: ".$DOMAIN_URL."bnr/ebook/loginEbook.php?unxid=".md5(time()));
	}
	die();
}else{
	if ($_SESSION["sSiteSide"] == "EBOOK" && $_SESSION["sSID"] == session_id()){
		$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp		
	}else{
		$_SESSION["sSID"] = "1";
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage
		header ("Location: ".$DOMAIN_URL."bnr/ebook/loginEbook.php?unxid=".md5(time()));
		die();
	}
}
?>