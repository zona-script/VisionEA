<?php
session_start();
include_once("../includes/inc_def.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

if (!empty($_POST)) { 
	//data are sent by JQuery of ebook/index.php NOT by submit
	$username = $passwd = $code = $privilege = "";
	$username 	= isset($_POST["username"])? fValidateSQLFromInput($conn, $_POST["username"]) : "";
	$username	= strtolower($username);
	
	$passwd		= isset($_POST["passwd"])? fValidateSQLFromInput($conn, $_POST["passwd"]) : "";
	$code		= isset($_POST["code"])? fValidateInput($_POST["code"]) : "";
	$siteSide	= "EBOOK"; //isset($_POST["siteSide"])? fValidateSQLFromInput($conn, $_POST["siteSide"]) : "MEMBER";

	if ($_SESSION["code"] != $code){
		echo "invalid secure code";	
		die();
	}

	//password encrypt and status == ACTIVE
	$sql  = "SELECT ebUsername, ebFirstName, ebLastName, pePasswd";
	$sql .= " FROM dtUserEbook";
	$sql .= " INNER JOIN trPassEbook ON peUsername = ebUsername";
	$sql .= " WHERE ebUsername ='".$username."' AND ebStatus = '".$DEF_STATUS_ACTIVE."' ";
	$sql .= " ORDER BY peDate DESC LIMIT 1";

	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		$row = $query->fetch_assoc();
		if ( (strtolower($row['ebUsername']) ==$username && $row['pePasswd'] == md5($passwd)) ){ 
    			fSetSessionLogin($row['ebUsername'], $row['ebFirstName'], $row['ebLastName'], "", $siteSide);
    			echo "valid"; //value is catch by AJAX
    			die();
		}else{
			echo "Username and password not valid";	
			die();
		}
	}else{
		echo "Username not found or unauthorized";	//Maybe there is no such username or Status account not allowed /block/declined
		die();
	}
}

?>