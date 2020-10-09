<?php
session_start();
include_once("../includes/inc_def.php");
//DO NOT ACTIVATE includes/inc_session_admin.php
//include_once("../includes/inc_session_admin.php"); //when checking username and password, checking session not active yet.
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

if (!empty($_POST)) { 
	//data are sent by JQuery of login.php NOT by submit
	$username = $passwd = $code = $siteSide = "";
	$username 	= isset($_POST["username"])? fValidateSQLFromInput($conn, $_POST["username"]) : "";
	$username	= strtolower($username);
	
	$passwd		= isset($_POST["passwd"])? fValidateSQLFromInput($conn, $_POST["passwd"]) : "";
	$code		= isset($_POST["code"])? fValidateInput($_POST["code"]) : "";
	$siteSide	= isset($_POST["siteSide"])? fValidateSQLFromInput($conn, $_POST["siteSide"]) : "";
	
	$session_code = isset($_SESSION["code"])?$_SESSION["code"]: "";

	//if ($_SESSION["code"] != $code){
	if ($session_code != $code){
		echo "invalid secure code";	
		die();
	}
	
	if ($siteSide != "BACK-OFFICE"){
		echo "login side not found";
		die();	
	}
	
	//password encrypt and status == ACTIVE
	$sql	= "select boUsername, boName, passWordBO, passPrivilegeBO from dtBackOffice inner join trPasswordBO ";
	$sql	.= " on boUsername = passUsernameBO";
	$sql 	.= " where boUsername='".$username. "' and boStID = '" . $DEF_STATUS_ACTIVE . "'";
	$sql 	.= " order by passDateBO Desc limit 1";
	
	//echo $sql;
	//die();
			
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		$row = $query->fetch_assoc();
		if (strtolower($row['boUsername']) == $username && $row['passWordBO'] == md5($passwd)){
			//echo ">>" . $row['mbrUsername'] . " >> " . $row['passWord'] . " >> " . $passwd;
			fSetSessionLogin($row['boUsername'], $row['boName'], $row['boName'], $row['passPrivilegeBO'], $siteSide);
			echo "valid"; //value is catch by AJAX
			//header("Location: index.php");
			die();	
		} else {
			echo "Username and password not valid";	
			die();
		}
	}else{
		echo "Username not found or unauthorized";	//Maybe there is no such username or Status account not allowed /block/declined
		die();
	}
}
?>