<?php
$uri = $_SERVER['REQUEST_URI'];
$pos = strpos($uri, "/", 0);
$len = strlen($uri);
//$refUsername = fValidateSQLFromInput($conn, substr($uri, $pos+1));
$data = substr($uri, $pos+1);
$data = trim($data);
$data = stripslashes($data);
$data = htmlspecialchars($data);
$refUsername = $data;

//if (fCheckUsername($refUsername, $conn)){
  //header("Location: ".$COMPANY_SITE."member/?me=".$refUsername);
	header("Location: https://visionea.net/bnr/member/?me=".$refUsername);
  die();
  
  /*
include_once("https://visionea.net/bnr/includes/inc_def.php");
include_once("https://visionea.net/bnr/includes/inc_conn.php");
include_once("https://visionea.net/bnr/includes/inc_functions.php");
$uri = $_SERVER['REQUEST_URI'];
$pos = strpos($uri, "/", 0);
$len = strlen($uri);
echo "ada"; 
$refUsername = fValidateSQLFromInput($conn, substr($uri, $pos+1));
echo ("adfaddd"); die();
if (fCheckUsername($refUsername, $conn)){
    echo ("Location: ".$COMPANY_SITE."member/?me=".$refUsername); die();
 	//header("Location: ".$COMPANY_SITE."member/?me=".$refUsername);
  die();
}else if (strcmp(strtoupper($refUsername), "REGISTER")){
  //header("Location: ".$COMPANY_SITE."member/regLink.php?unxid=".md5(time()));
  echo ("Location: ".$COMPANY_SITE."member/regLink.php".$refUsername);
  die();
}

echo ("adfadfa");
*/
?>