<?php
//session_start();
include_once("../includes/inc_def.php"); //before inc_session
include_once("../includes/inc_session.php"); //after inc_session
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

if (!empty($_POST)) { 
//data are sent by JQuery of networkTree.php NOT by submit
//$("#actButton").on('click', function(){ ... }

	$actUsername = $actUpline = $actPos = $actPackage = "";
	$actUsername 	= isset($_POST["actUsername"])? fValidateSQLFromInput($conn, $_POST["actUsername"]) : "";
	$actUsername	= strtolower($actUsername);

	$actUpline		= isset($_POST["actUpline"])? fValidateSQLFromInput($conn, $_POST["actUpline"]) : "";
	$actUpline		= strtolower($actUpline);

	$actPos			= isset($_POST["actPos"])? fValidateInput($_POST["actPos"]) : "";
	$actPos			= strtolower($actPos);

	$actPackage		= isset($_POST["actPackage"])? fValidateInput($_POST["actPackage"]) : "";
	$actPackage		= strtolower($actPackage);


	if ($actUsername == "" || $actUpline == "" || $actPackage == "" || $actPos == ""){
		//error, incomplete data
		echo (fSendStatusMessage("error", "Incomplete Data")); die();	
	}

	//Rechecking position (left/right)
	$sql	= "SELECT mbrUsername from dtMember WHERE mbrUpline='" . $actUpline . "' and mbrPos='" . $actPos . "'";
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		//position has been taken
		echo (fSendStatusMessage("error", "position has been taken"));
		die();
	}

	//1.1 Get Package Price
	$pacPrice = $numOfVoucherRequired = 0;
	$sql = "SELECT pacPrice FROM msPackage WHERE pacID='" . $actPackage . "'";
	if ($query = $conn->query($sql)){
		if ($query->num_rows > 0){
			$row = $query->fetch_assoc();
			$pacPrice = $row["pacPrice"];
		}
	}else{
		echo (fSendStatusMessage("error", mysqli_error($conn)));
		die();
	}

	//1.2 Checking number of voucher Required AND Voucher Balance
	if ($pacPrice > 0){
		$numOfVoucherRequired = ceil($pacPrice / $DEF_VOUCHER_PRICE);	//Number of Voucher Required (@2800)
		//checking Voucher Balance
		//$sql = "SELECT count(fivVCode) VoucherBalance FROM ((dtFundIn ";
		$sql = "SELECT fivFinID, fivVCode FROM ((dtFundIn ";
		$sql .= " inner join dtFundInVoucher on finID = fivFinID and finStatus='" . $DEF_STATUS_APPROVED . "')";
		$sql .= " inner join dtVoucher on vCode = fivVCode and vStatus = '" . $DEF_STATUS_USED . "'";
		$sql .= " and fivStatus = '" . $DEF_STATUS_ACTIVE ."')";
		$sql .= " WHERE finMbrUsername='" . $_SESSION['sUserName'] . "'";
		$arrVoucher = array();
		if ($query = $conn->query($sql)){
			if ($query->num_rows > 0){
				while ($row = $query->fetch_assoc()){
					//$VoucherBalance	= $row["VoucherBalance"];
					$arrVoucher[] = array("fivFinID" => $row["fivFinID"], "fivVCode" => $row["fivVCode"]);	
				}
			}
		}else{
			echo (fSendStatusMessage("error", $conn->error));
			die();
		}

		$VoucherBalance = sizeof($arrVoucher);
		if ($numOfVoucherRequired > $VoucherBalance){ //VoucherBalance not enough
			echo (fSendStatusMessage("error", "Your Balance is not enough"));
			die();
		}
	}


	$conn->autocommit(false);
	//1.3 Insert dtMember (move from tempJoin)
	$sql = "SELECT tjSponsor, tjPasswd, trPacID, tjFirstName, tjLastName, tjIDType, tjIDN, tjEmail, tjMobileCode, tjMobile, tjBOD, tjAddr, tjCountry, tjState, tjCity, tjStID FROM ( ";
	$sql .= " SELECT * FROM dtMember INNER JOIN Transaction ON mbrUsername = trUsername )A ";
	$sql .= " INNER JOIN dtTempJoin ON mbrUsername=tjSponsor ";
	$sql .= " WHERE tjUsername = '" . $actUsername . "' ";
	$sql .= " ORDER BY trDate DESC LIMIT 1";		
	//$sql	= "SELECT * FROM dtTempJoin WHERE tjUsername ='" . $actUsername . "'";
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		if ($row	= $query->fetch_assoc()){
			if ($row['tjStID'] != $DEF_STATUS_ACTIVE){
				echo (fSendStatusMessage("error", "Email address has not been verified")); die();
			}else{ //boleh insert
				if (!fCekVerificationID($conn, $actUsername, $row["tjIDN"])){
					echo (fSendStatusMessage("error", "ID Number has been used.")); die();
				}
				$passWord			= $row["tjPasswd"];
				$sponsorUsername	= $row["tjSponsor"];
				$pacIDSponsor		= $row["trPacID"];
				$mbrEmail 			= $row["tjEmail"];
				$mbrFirstName 		= $row["tjFirstName"];
				$mbrLastName 		= $row["tjLastName"];

				$arrData = array(
					0 => array ("db" => "mbrUsername"	, "val" => $actUsername),
					1 => array ("db" => "mbrSponsor"	, "val" => $row["tjSponsor"]),
					2 => array ("db" => "mbrUpline"		, "val" => $actUpline),
					3 => array ("db" => "mbrPos"		, "val" => $actPos),
					4 => array ("db" => "mbrFirstName"	, "val" => $row["tjFirstName"]),
					5 => array ("db" => "mbrLastName"	, "val" => $row["tjLastName"]),
					6 => array ("db" => "mbrIDType"		, "val" => $row["tjIDType"]),
					7 => array ("db" => "mbrIDN"		, "val" => $row["tjIDN"]),
					8 => array ("db" => "mbrEmail"		, "val" => $row["tjEmail"]),
					9 => array ("db" => "mbrMobileCode"	, "val" => $row["tjMobileCode"]),
					10 => array ("db" => "mbrMobile"	, "val" => $row["tjMobile"]),
					11 => array ("db" => "mbrBOD"		, "val" => $row["tjBOD"]),
					12 => array ("db" => "mbrAddr"		, "val" => $row["tjAddr"]),
					13 => array ("db" => "mbrCountry"	, "val" => $row["tjCountry"]),
					14 => array ("db" => "mbrState"		, "val" => $row["tjState"]),
					15 => array ("db" => "mbrCity"		, "val" => $row["tjCity"]),
					16 => array ("db" => "mbrStID"		, "val" => $DEF_STATUS_ACTIVE),
					17 => array ("db" => "mbrDate"		, "val" => "CURRENT_TIME()")
				);
				if (!fInsert("dtMember", $arrData, $conn)) {
					echo (fSendStatusMessage("error", "<b>Record Member - </b>" . $conn->error));
					$conn->rollback();
					die();
				}
				unset($arrData);
			}					
		}else{
			echo (fSendStatusMessage("error", "<b>Record Member - Fetch row failed</b>"));
			$conn->rollback();
			die();
		}
	}else{ //end dtTempJoin to dtMember
		echo (fSendStatusMessage("error", $conn->error));
		$conn->rollback();
		die();	
	}


	//trPassword, 
	$arrData = array(
		0 => array ("db" => "passMbrUsername"	, "val" => $actUsername),
		1 => array ("db" => "passDate"			, "val" => "CURRENT_TIME()"),
		2 => array ("db" => "passWord"			, "val" => $passWord)
	);

	if (!fInsert("trPassword", $arrData, $conn)) {
		echo (fSendStatusMessage("error", "<b>Update Passwd - </b>" . $conn->error));
		$conn->rollback();
		die();
	}
	unset($arrData);

	//Transaction, 
	$arrData = array(
		0 => array ("db" => "trUsername"	, "val" => $actUsername),
		1 => array ("db" => "trPacID"			, "val" => $actPackage),
		2 => array ("db" => "trDate"			, "val" => "CURRENT_TIME()"),
		3 => array ("db" => "trStatus"			, "val" => $DEF_STATUS_NEW)
	);

	if (!fInsert("Transaction", $arrData, $conn)) {
		echo (fSendStatusMessage("error", "<b>Update Transaction - </b>" . mysqli_error($conn)));
		$conn->rollback();
		die();
	}
	unset($arrData);

	//dtBnsSponsor, 
	$sponsorBonus = fGetBonus("SPONSOR", $actPackage, $pacIDSponsor, $conn);
	if ($sponsorBonus > 0){
		$arrData = array(
			0 => array ("db" => "bnsSpUsername"		, "val" => $sponsorUsername),
			1 => array ("db" => "bnsSpTrUsername"	, "val" => $actUsername),
			2 => array ("db" => "bnsSpTrPacID"		, "val" => $actPackage),
			3 => array ("db" => "bnsSpDate"			, "val" => "CURRENT_TIME()"),
			4 => array ("db" => "bnsSpAmount"		, "val" => $sponsorBonus)
		);

		if (!fInsert("dtBnsSponsor", $arrData, $conn)) {
			echo (fSendStatusMessage("error", "<b>Update Bonus Sponsor - </b>" . $conn->error));
			$conn->rollback();
			die();
		}
		unset($arrData);
	}else{
	//if sponsor bonus == 0, means error
		echo (fSendStatusMessage("error", "<b>Get Bonus Sponsor Failed</b>"));
		$conn->rollback();
		die();
	}

	//dtBnsPassedUp //dimatikan atau tidak digunakan lagi
	/*
	$passedUpBonus = fGetBonus("PASSED-UP", $actPackage, $pacIDSponsor, $conn);
	if ($passedUpBonus > 0){
	$vipMembership	= fGetVIPUsername($sponsorUsername, 'vip', $conn);
	if ($vipMembership != ""){
	$arrData = array(
	0 => array ("db" => "bnsPUUsername"		, "val" => $vipMembership), //VIP membership
	1 => array ("db" => "bnsPUTrUsername"	, "val" => $actUsername),
	2 => array ("db" => "bnsPUTrPacID"		, "val" => $actPackage),
	3 => array ("db" => "bnsPUDate"			, "val" => "CURRENT_TIME()"),
	4 => array ("db" => "bnsPUAmount"		, "val" => $passedUpBonus)
	);

	if (!fInsert("dtBnsPassedUp", $arrData, $conn)) {
	echo (fSendStatusMessage("error", "<b>Update Bonus Passed-Up - </b>" . mysqli_error($conn)));
	$conn->rollback();
	die();
	}
	unset($arrData);
	}
	} //if $passedUpBonus == 0, no need to save bonus. Not all member get passed up bonus, only VIP membership
	*/


	//1.3.2. Delete record dtTempJoin
		$arrDataQuery = array(
			"tjUsername" => $actUsername
		); //define your query in the arrData
		if (!fDeleteRecord("dtTempJoin", $arrDataQuery, $conn)){
			echo (fSendStatusMessage("error", "<b>Delete TempJoin - </b>" . $conn->error));
			$conn->rollback();
			die();
		}
		unset($arrDataQuery);

	//1.4 Update dtFundInVoucher (status="USED", usedFor="ACTIVATION", usedOn=USERNAME, fivDate="CURRENT_TIME()")
	$arrData	= array(
		"fivStatus" 	=> $DEF_STATUS_USED,
		"fivUsedFor" 	=> $DEF_VOUCHER_USED_FOR_ACTIVATION,
		"fivUserOn" 	=> $actUsername,
		"fivDate"       => "CURRENT_TIME()"
	);

	$arrDataQuery = array();
	$counter = 0;
	//moving some data of arrVoucher to arrDataQuery 
	foreach ($arrVoucher as $key => $value){
		if ($counter >= $numOfVoucherRequired) {
			break;
		}else{
			$arrDataQuery = array (
				"fivFinID" => $value["fivFinID"], 
				"fivVCode" => $value["fivVCode"]
			);
			$counter++;
			if (!fUpdateRecord("dtFundInVoucher", $arrData, $arrDataQuery, $conn)){
				echo (fSendStatusMessage("error", "<b>Update FundInVoucher - </b>" . $conn->error));
				$conn->rollback();
				die();
			}
			unset($arrDataQuery);
		}
	}
	unset($arrData);

	// //Create Information of Sponsorship's Generation
	// $username   = $actUsername;
	// $sqlInsert = "INSERT INTO dtGenSponsorship (genMbrUsername, genSPUsername, genLevel) VALUES (?,?,?)";
	// $queryInsert = $conn->prepare($sqlInsert);
	// $queryInsert->bind_param("sss", $genMbrUsername, $genSPUsername, $genLevel);

	// //looking for number of level/generation of Matching of sponsor.
	// //get Level of generation
	// //$myDataObj  = json_decode(fGetDataPackage($conn, $actUsername));
	// //$numOfMatchingGen   = $myDataObj->{"pacMatchingGen"};
	// //for ($i = 0; ($i <7 && $i<$numOfMatchingGen); $i++){
	// for ($i = 0; $i <7; $i++){
	// 	$sql = "SELECT mbrUsername, mbrSponsor FROM dtMember ";
	// 	$sql .= " WHERE mbrUsername='".$username."' AND mbrUsername <> mbrSponsor";

	// 	if ($query = $conn->query($sql)){
	// 		if ($row=$query->fetch_assoc()){
	// 			$genMbrUsername   = $actUsername;
	// 			$genSPUsername    = $row['mbrSponsor'];
	// 			$genLevel         = $i+1;
	// 			$myDataObj  = json_decode(fGetDataPackage($conn, $genSPUsername));
	// 			$matchingGen   = $myDataObj->{"pacMatchingGen"};
	// 			if ($matchingGen >= $genLevel){
	// 				if ($queryInsert->execute()){
	// 					//success
	// 					$username = $genSPUsername; //move sponsor to next username to get higher level of sponsorship
	// 				}else{
	// 					echo (fSendStatusMessage("error", "<b>Create Sponsorship's Generation Failed - </b>" . $conn->error));
	// 					$conn->rollback();
	// 					die();
	// 				}
	// 			}else{
	// 				//package not qualified for matching bonus
	// 				//but keep to move to next sponsor
	// 				$username = $genSPUsername; //move sponsor to next username to get higher level of sponsorship
	// 			}
	// 		}else{
	// 			break;
	// 		}
	// 	}else{
	// 		break;
	// 	}
	// }
	//insert to trProduct dan trProDetail
	$sql  = " SELECT * FROM msProduct";
	$sql .= " WHERE proID = '".$DEF_EBOOK_BASIC."' ";
	$result = $conn->query($sql);
	$trProTransID = strtotime("+0");
	if ($row = $result->fetch_assoc()){
		$table = "trProduct";
		$arrData = array(
	        array ("db" => "trProTransID"		, "val" => $trProTransID),
	        array ("db" => "trProUsername"		, "val" => $sponsorUsername),
	        array ("db" => "trProUserBeli"		, "val" => $actUsername),
	        array ("db" => "trProType"			, "val" => $DEF_TYPE_PURCHASE_ACT),
	        array ("db" => "trProDate"			, "val" => "CURRENT_TIME()"),
	        array ("db" => "trProAmount"		, "val" => $row['proPrice']),
	        array ("db" => "trProDisc"			, "val" => $row['proPrice']),
	        array ("db" => "trProUpdateDate"	, "val" => "CURRENT_TIME()"),
	        array ("db" => "trProActiveDate"  	, "val" => "CURRENT_TIME()"),
	        array ("db" => "trProStatus"		, "val" => $DEF_STATUS_APPROVED)                
	    );
	    if (!fInsert($table, $arrData, $conn)){
			$conn->rollback();
			fSendToAdmin("Activate Member", "activateMember.php", "Insert data to trProduct failed");
			echo (fSendStatusMessage("error", "<b>Record produk - </b>" . mysqli_error($conn)));
			die();
		}else{
			$table = "trProDetail";
			$arrData = array(
	            array ("db" => "trPDTransID"	, "val" => $trProTransID),
	            array ("db" => "trPDProID"		, "val" => $DEF_EBOOK_BASIC),
	            array ("db" => "trPDPrice"		, "val" => $row['proPrice']),
	            array ("db" => "trPDQty"		, "val" => "1"),
	            array ("db" => "trPDDisc"		, "val" => $row['proPrice']),
	            array ("db" => "trPDSubTotal"	, "val" => "0")                
	        );
	        if (!fInsert($table, $arrData, $conn)){
				$conn->rollback();
				fSendToAdmin("Activate Member", "activateMember.php", "Insert data to trProDetail failed");
				echo (fSendStatusMessage("error", "<b>Record produk detail - </b>" . mysqli_error($conn)));
				die();
			}
		}		
	}else{
		$conn->rollback();
		fSendToAdmin("Activate Member", "activateMember.php", "Insert data product & produk detail failed");
		echo (fSendStatusMessage("error", "<b>Record produk & produk detail - </b>" . mysqli_error($conn)));
		die();
	}
		
	unset($arrData);
	//insert to dtUserEbook
	$table = "dtUserEbook";
	$arrData = array(
		0 => array ("db" => "ebproTransID"	, "val" => $trProTransID), //samakan dengan tabel order (trProduct)	
		1 => array ("db" => "ebUsername"	, "val" => $actUsername),
		2 => array ("db" => "ebEmail"		, "val" => $mbrEmail), // from dtTempJoin
		3 => array ("db" => "ebFirstName"	, "val" => $mbrFirstName), // from dtTempJoin
		4 => array ("db" => "ebLastName"	, "val" => $mbrLastName), // from dtTempJoin
		5 => array ("db" => "ebDate"		, "val" => "CURRENT_TIME()"),
		6 => array ("db" => "ebStatus"		, "val" => $DEF_STATUS_ACTIVE)
	);
	if (!fInsert($table, $arrData, $conn)){
		$conn->rollback();
		fSendToAdmin("Activate Member", "activateMember.php", "Insert data to dtUserEbook failed");
		echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . mysqli_error($conn)));
		die();
	}
	unset($arrData);

	//insert to trPassEbook
	$table = "trPassEbook";
	$pePasswd = substr($trProTransID, -6);
	$arrData = array(
		0 => array ("db" => "peID"			, "val" => $trProTransID),
		1 => array ("db" => "peUsername"	, "val" => $actUsername),
		2 => array ("db" => "pePasswd"		, "val" => md5($pePasswd)),
		3 => array ("db" => "peDate"		, "val" => "CURRENT_TIME()")
	);
	if (!fInsert($table, $arrData, $conn)){
		$conn->rollback();
		fSendToAdmin("Activate Member", "activateMember.php", "Insert data to trPassEbook failed");
		echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . mysqli_error($conn)));
		die();
	}
	unset($arrData);	

	//Save to dtCornEmail
	savetoCornEmail($conn, "ACTIVATION_MBR_TO_SP", $actUsername);
	savetoCornEmail($conn, "SEND_EBOOK_DATA", $actUsername);

	$query->close();
	// $conn->rollback();
	$conn->commit();

	fSendNotifToEmail("NEW_MEMBER_ACTIVATED", $actUsername);

	//insert default data to mymac -> result curl
	$sendData = array(
		"action" 	=> "new_member_activated",
		"email"		=> $mbrEmail,
		"username"	=> $actUsername,
		"passwd"	=> $passWord // password from dtTempJoin already md5 format
	);
	$Rcurl = fCurl($DEF_URL_MYMAC_API, $sendData);
	$resultJSON = json_decode($Rcurl);
	if ($resultJSON->status == "error"){
		//return error message from mymac
		flogMyMac($actUsername, "Activate Member", "activateMember.php", $resultJSON->message, $DEF_STATUS_PENDING); 
		$conn->commit();
	}
	echo (fSendStatusMessage("success", $actUpline));
	die();
}
?>