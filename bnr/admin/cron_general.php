<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");


//temporarily turned off
//die();


$fileName = "cron_general.php";
//https://visionea.net/bnr/admin/postPairing_Matching.php?act=posting&cat=pair&code=Rah45!a
//https://visionea.net/bnr/admin/cron_general.php?act=corn&cat=general&code=Rah45!a code=***IN_DB****

$act        =  (isset($_GET["act"]))?fValidateInput($_GET["act"]): "";
$cat        =  (isset($_GET["cat"]))?fValidateInput($_GET["cat"]): "";
$code       =  (isset($_GET["code"]))?fValidateInput($_GET["code"]): "";
$isAuth     = false;
$sql = "SELECT * FROM trPasswordBO WHERE passUsernameBO='". $USER_POSTING ."' ORDER BY DATE(passDateBO) DESC LIMIT 1";
$query = $conn->query($sql);
if ($row = $query->fetch_assoc()){
    if ($row['passWordBO'] == md5($code) && $row['passUsernameBO'] == $USER_POSTING){
        $isAuth = true;
    }
}

if ($act == "corn" && $cat == "general" && $isAuth==true){
    //corn jobs here.....

/* turn off delete temporary register
	//Temporary Registration_________________________________________________
	$timeLimit =date("Y-m-d H:i:s", strtotime("-2 days")); //Status PENDING
	$sql = "DELETE FROM dtTempJoin WHERE DATE(tjDate) < '".$timeLimit."' AND tjStID='".$DEF_STATUS_PENDING."'";
	
	if (!$conn->query($sql)){
		fSendToAdmin("Temp Register -> PENDING", $fileName, $sql);
	}

	$timeLimit =date("Y-m-d H:i:s", strtotime("-3 days")); //Status ON_PROGRESS
	$sql = "DELETE FROM dtTempJoin WHERE DATE(tjDate) < '".$timeLimit."' AND tjStID='".$DEF_STATUS_ACTIVE."'";
	if (!$conn->query($sql)){
		fSendToAdmin("Temp Register -> ONPROGRESS", $fileName, $sql);
	}
*/


	//Buy Voucher_________________________________________________
	$timeLimit 	= date("Y-m-d H:i:s", strtotime("-4 hours")); //Status PENDING
	$reason 	= "No Confirmation > 4 hours";
	$sql = "UPDATE dtFundIn SET finStatus='" . $DEF_STATUS_DECLINED . "', finDesc='".$reason."' ";
	$sql .= " WHERE finStatus='".$DEF_STATUS_PENDING."' AND finDate < '".$timeLimit."'";
	//echo $sql; die();
	if (!$conn->query($sql)){
		fSendToAdmin("Buy Voucher", $fileName, $sql);
	}


	//REQUEST_SECURITY_PIN_________________________________________________
	$timeLimit 	= date("Y-m-d H:i:s", strtotime("-24 hours")); //Status PENDING
	$sql = "UPDATE trPIN SET pinStID='".$DEF_STATUS_BLOCKED."' WHERE pinStID='".$DEF_STATUS_PENDING."' AND DATE(pinDate) < '".$timeLimit."'";
	if (!$conn->query($sql)){
		fSendToAdmin("Request Security PIN", $fileName, $sql);
	}



	//REQUEST_WD_________________________________________________
	$timeLimit 	= date("Y-m-d H:i:s", strtotime("-60 minutes")); //Status PENDING
	$reason 	= "No Confirmation > 60 minutes";
	$sql = "UPDATE dtWDFund SET wdStID='" . $DEF_STATUS_DECLINED . "', wdDesc='".$reason."' ";
	$sql .= " , wdApproveDate='".$CURRENT_TIME."', wdApprovedBy='".$USER_POSTING."'";
	$sql .= " WHERE wdStID='".$DEF_STATUS_REQUEST."' AND DATE(wdDate) < '".$timeLimit."'";
	if (!$conn->query($sql)){
		fSendToAdmin("Request WD", $fileName, $sql);
	}

    //end corn jobs.........

}else if ($act == "corn" && $cat == "bday" && $isAuth==true){
	//Update Birthday (1x a day)
	//different cron schedule
	$tgl = date("%-m-d");
	$sql = "INSERT INTO dtCornEmail (cecat, ceUsername, ceSendst, cedate) ";
	$sql .= " 	SELECT 'BDAY', mbrUsername, $DEF_STATUS_NOT_YET_SENT, CURRENT_TIME() FROM dtMember WHERE date(mbrBOD) like '$tgl'";
	//echo $sql;	die();
	if (!$conn->query($sql)){
		fSendToAdmin("Insert BOD to dtCornEmail", $fileName, $sql);
	}
	
}else if ($act == "corn" && $cat == "renew_reminder" && $isAuth==true){
	//different cron schedule
	$sql = "INSERT INTO dtCornEmail (cecat, ceUsername, cesendst, cedate) ";
	$sql .= " 	SELECT 'RENEW_REMINDER', trUsername, $DEF_STATUS_NOT_YET_SENT, CURRENT_TIME() FROM Transaction ";
	$sql .= " 	INNER JOIN dtMember ON trUsername = mbrUsername ";
	$sql .= " 	WHERE DATE(DATE_ADD(trDate, INTERVAL 11 MONTH)) = CURRENT_DATE() AND mbrStID = '$DEF_STATUS_ACTIVE'";
	//echo $sql;	die();
	if (!$conn->query($sql)){
		fSendToAdmin("Insert RENEW_REMINDER to dtCornEmail", $fileName, $sql);
	}

	//________Update expired member > 7 hari
	$sql  = " UPDATE dtMember a, (";
	$sql .= " 	SELECT mbrUsername, mbrDate, DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ) AS expiredDate, ";
	$sql .= " 	DATE_ADD( DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ), INTERVAL 7 DAY) blockedDate ";
    $sql .= " 	FROM dtMember"; 
    $sql .= " 	INNER JOIN ( ";
    $sql .= " 		SELECT * FROM Transaction AS t ";
    $sql .= " 		WHERE t.trID = (SELECT trID FROM Transaction WHERE trUsername = t.trUsername ORDER BY trDate DESC LIMIT 1) ";
	$sql .= "	) AS t ON t.trUsername = mbrUsername "; 
    $sql .= " 	WHERE mbrStID = '".$DEF_STATUS_ACTIVE."'";
	$sql .= " 	HAVING date(blockedDate) < date('".$CURRENT_TIME."') ";
	$sql .= " ) t1";
	$sql .= " SET  a.mbrStID = '".$DEF_STATUS_BLOCKED."'";
	$sql .= " WHERE a.mbrUsername = t1.mbrUsername";
	if (!$conn->query($sql)){
		fSendToAdmin("Update Blocked member failed", $fileName, $sql);
	}
	
}else{
    //not authorized
    fSendToAdmin("CRON-ERROR", $fileName, "Not authorized - " . "$act == 'corn' && $cat == 'general'" . "SQL: " . $sql);
}

fCloseConnection($conn);
?>