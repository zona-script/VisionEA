<?php
session_start();
include_once("../includes/inc_def.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

if (!empty($_POST)) { 
	//data are sent by JQuery of login.php NOT by submit
	$username = $email = $code = "";
	$username 	= isset($_POST["username"])? fValidateSQLFromInput($conn, $_POST["username"]) : "";
	$email	= isset($_POST["email"])? fValidateSQLFromInput($conn, $_POST["email"]) : "";
	$code	= isset($_POST["code"])? fValidateInput($_POST["code"]) : "";

	if ($_SESSION["code"] != $code){
		echo "Invalid Secure Code";	
		die();
	}
	
	$sql	 = "SELECT mbrUsername, mbrFirstName, mbrEmail FROM dtMember ";
	$sql 	.= " WHERE mbrUsername='".$username. "' AND mbrStID = '".$DEF_STATUS_ACTIVE."'";
	//echo $sql; die();
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		$row = $query->fetch_assoc();
		$emailFrom	= "EMAIL_SUPPORT";
		$emailTo 	= $row['mbrEmail'];
		if ($emailTo != $email){
			echo "Your email address does not match"; die();
		}

		$randPasswd = fGenPassword();

		//save to dtReqReset
		$nmTable	= "dtReqReset";
		$cat		= $DEF_CATEGORY_RESET_PASSWORD;
		$rrID 		= strtotime("now");
		$arrData 	= array(
			0 => array("db"=> "rrID"		, "val"=>$rrID),
			1 => array("db"=> "rrUsername"	, "val"=>$username),
			2 => array("db"=> "rrDate"		, "val"=>"CURRENT_TIME()"),
			3 => array("db"=> "rrCategory"	, "val"=>$cat),
			4 => array("db"=> "rrNote"		, "val"=>$randPasswd),
			5 => array("db"=> "rrStID"		, "val"=>$DEF_STATUS_REQUEST)
		);
		if (fInsert($nmTable, $arrData, $conn)){
			fSendNotifToEmail("RESET_PASSWORD", $username);
			$conn->close();
			echo "valid"; //response
			die();
		}
		
	}else{
		echo "Username not found";	
		die();
	}
}

?>