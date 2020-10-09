<?php
session_start();
include_once("inc_def.php");
include_once("inc_functions.php");

/*
file : .htaccess
RewriteBase / 
RewriteRule (.*) - [E=Cache-Control:no-cache] 
*/

/*
if (isset($_SERVER['HTTPS']) &&
    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  //$protocol = 'https://';
}else {
  //$protocol = 'http://';
	//header("Location: ".$COMPANY_SITE."member/?MNav=auth"); die();
}
*/


//Checking Protocol ____________________
$protocol = $_SERVER['SERVER_PROTOCOL'];
$pos = strpos($protocol, "HTTPS");
if ($pos === 0) {
    //https
}else{
    //http
    //header ("Location: ".$COMPANY_SITE."member/"); //with https
	//die();
}
//-------------------------------------------



//Checking Maintenance Schedule
//if ($CURRENT_TIME >= $startMaintenanceTime && $CURRENT_TIME <= $endMaintenanceTime){
if (fIsMaintenance()){
	// if ($_SESSION["sUserName"] != "fortune"){
		$_SESSION["sSID"] = "1";
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage
		header ("Location: ".$COMPANY_SITE."member/maintenance.php?MNav=dailyMaintenance&unxid=".md5(time()));
		die();
	// }
}

if ( 
	(empty($_SESSION["sUserName"]) || empty($_SESSION["sFirstName"])  || empty($_SESSION["sSiteSide"]) )
	||
	(isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800))
	)
{
	// last request was more than 15 minutes ago
	$_SESSION["sSID"] = "1";
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
	$typeFrame = isset($_GET["type"])?$_GET["type"]:'';
	if ($typeFrame == "iframe"){
	?>
		<script type="text/javascript">
			window.top.location.href = $COMPANY_SITE."member/?MNav=auth";
		</script>
	<?php
	}else {
		//header("Location: ./?MNav=auth");
		// echo "Location: ".$COMPANY_SITE."member/?MNav=auth&unxid=".md5(time()); die();	
		header ("Location: ".$COMPANY_SITE."member/?MNav=auth&unxid=".md5(time()));
	}
	die();
}else{
	if ($_SESSION["sSiteSide"] == "MEMBER" && $_SESSION["sSID"] == session_id()){
		$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp		
		
	}else{
		$_SESSION["sSID"] = "1";
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage
		header ("Location: ".$COMPANY_SITE."member/?MNav=auth&unxid=".md5(time()));
		die();
	}
}
?>