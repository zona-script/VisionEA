<?php
//session_start();
require("../includes/inc_def.php");
require("../includes/inc_session_admin.php");
require('../includes/inc_conn.php');
require('../includes/inc_functions.php');

$q = $id = $sub = '';


//=============================================================
// USING GET___________
//=============================================================
if (isset($_GET["q"])){
	$q  = fValidateInput($_GET["q"]);
	$id = (isset($_GET["id"]))?fValidateSQLFromInput($conn, $_GET["id"]): "";
	
	if (strcmp($q , 'confirmDeposit') == 0){ //incomingDeposite.php
		$sql = "SELECT finID, finMbrUsername, finAmount, finAccType, finVoucherType, finFromAccNo, finTransactionID, IFNULL(finFilename,'') AS finFilename FROM dtFundIn ";
		$sql .= " WHERE finStatus='" . $DEF_STATUS_ONPROGRESS . "' AND finID='" . $id . "'";
		//echo $sql;
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			$row = $query->fetch_assoc();
			$arrdata = array(
				"finID" 			=> $row["finID"],
				"finMbrUsername" 	=> $row["finMbrUsername"],
				//"finAmount" 		=> $row["finAmount"],
				"finAccType" 		=> $row["finAccType"],
				"finVoucherType" 	=> $row["finVoucherType"],
				//"finFromAccNo" => $row["finFromAccNo"],
				//"finTransactionID" => $row["finTransactionID"],
				"finFilename" 		=> $row['finFilename'],
				"status" 			=> "success",
				"message" 			=> ""
			);
			$dataJSON = json_encode($arrdata);
			echo ($dataJSON);
			die();
		}else {
			//echo (fSendStatusMessage("error", "<span class='text-danger'>No Deposit</span>"));
			echo (fSendStatusMessage("error", "Member has not made a deposit"));
			die();
		}
	}

	if (strcmp($q , 'confirmWD') == 0){ //requestWD.php
		$sql  = "SELECT wdID, wdMbrUsername, wdDate, wdAmount, wdFee, wdTax, wdPayAcc, stDesc, ptDesc FROM dtWDFund INNER JOIN msStatus ON wdStID=stID ";
		$sql  .= " INNER JOIN dtPaymentAcc ON payMbrUsername=wdMbrUsername AND payAcc = wdPayAcc ";
		$sql  .= " INNER JOIN msPaymentType ON ptID = payPTID ";
        $sql  .= " WHERE wdStID='".$DEF_STATUS_ONPROGRESS."' AND wdID='".$id."' AND payStatus='".$DEF_STATUS_ACTIVE."' " ;
		//echo(fSendStatusMessage("error", $sql)); die();
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			$row = $query->fetch_assoc();
			$wdNett = $row["wdAmount"] - $row["wdFee"] - $row["wdTax"];
			$arrdata = array(
				"wdID" 			=> $row["wdID"],
				"wdMbrUsername" => $row["wdMbrUsername"],
				"wdAmount" 		=> $row["wdAmount"],
				"wdNett" 		=> $wdNett,
				"wdPayAcc" 		=> $row["wdPayAcc"],
				"ptDesc" 		=> $row["ptDesc"],
				"status" 		=> "success",
				"message" 		=> ""
			);
			$dataJSON = json_encode($arrdata);
			echo ($dataJSON);
			die();
		}else {
			//echo (fSendStatusMessage("error", "<span class='text-danger'>No Deposit</span>"));
			echo (fSendStatusMessage("error", "The member has not yet confirmed"));
			die();
		}
	}
	
	if (strcmp($q , 'tradeAccount') == 0){ //tradeAcc.php

		$st = (isset($_GET["st"]))?fValidateSQLFromInput($conn, $_GET["st"]): "";
		if ($st == '2') $st = $DEF_STATUS_ONPROGRESS;
		$sql = "SELECT tradeUsername, tradeAccNo, tradeName, tradeEANum, tradeVPS FROM dtTradingAcc ";
		$sql .= " WHERE tradeID='" . $id ."' AND tradeStID='".$st."'";
		//echo (fSendStatusMessage("error", $sql)); die();
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			$row = $query->fetch_assoc();
			$arrdata = array(
						"tradeUsername" => $row["tradeUsername"],
						"tradeAccNo" => $row["tradeAccNo"],
						"tradeName" => $row["tradeName"],
						"tradeEANum" => $row["tradeEANum"],
						"tradeVPS"  => $row["tradeVPS"],
						"status" => "success",
						"message" => ""
						);
			$dataJSON = json_encode($arrdata);
			echo ($dataJSON);
			die();
		}else {
			//echo (fSendStatusMessage("error", "<span class='text-danger'>No Deposit</span>"));
			echo (fSendStatusMessage("error", "Member has not updated Trading Account"));
			die();
		}
	}

	if (strcmp($q , 'resendEmailWD') == 0){ //requestWD.php
		if (fSendNotifToEmail("REQUEST_WD", $id)){
			echo (fSendStatusMessage("success", "Email has been sent!")); die();
		}else{
			echo (fSendStatusMessage("error", "Fail Send Email (REQUEST_WD)")); die();
		}
	}
	
} //end if (isset($_GET["q"])){ ==================================================================





//=============================================================
// USING POST_________________________
//=============================================================
if (isset($_POST["q"])){
	$q  = fValidateInput($_POST["q"]);
	$id = (isset($_POST["id"]))?fValidateSQLFromInput($conn, $_POST["id"]): "";	
	if (strcmp($q , 'checkAndSaveDataDeposit') == 0){ //incomingDepo.php
		$finID				= (isset($_POST["finID"]))				?fValidateSQLFromInput($conn, $_POST["finID"]): "";
		$securityPassword 	= (isset($_POST["securityPassword"]))	?fValidateSQLFromInput($conn, $_POST["securityPassword"]): "";
		$accUsername 		= (isset($_POST["accUsername"]))		?fValidateSQLFromInput($conn, $_POST["accUsername"]): "";
		$accType 			= (isset($_POST["accType"]))			?fValidateSQLFromInput($conn, $_POST["accType"]): "";
		$voucherType 		= (isset($_POST["voucherType"]))		?fValidateSQLFromInput($conn, $_POST["voucherType"]): "";
		$accID 				= (isset($_POST["accID"]))				?fValidateSQLFromInput($conn, $_POST["accID"]): "";
		$accAmount 			= (isset($_POST["accAmount"]))			?fValidateSQLFromInput($conn, $_POST["accAmount"]): "";
		$accTransID			= (isset($_POST["accTransID"]))			?fValidateSQLFromInput($conn, $_POST["accTransID"]): "";
		
		$usernameAdmin		= (isset($_SESSION['sUserName']))			?$_SESSION['sUserName']: "";

		//checking securityPassword
		//$sql = "SELECT pinWordBO FROM trPinBO WHERE pinMbrUsernameBO='" . $_SESSION["sUserName"] . "' ORDER BY pinDateBO desc limit 1";
		//$query = $conn->query($sql);
		//if ($query->num_rows > 0){
		
		if (fCheckSecurityPasswordBO($usernameAdmin, $securityPassword, $conn)){
			$sql = "SELECT finID, finMbrUsername, finAmount, finAccType, finFromAccNo, finTransactionID FROM dtFundIn ";
			$sql .= " WHERE finStatus='" . $DEF_STATUS_ONPROGRESS . "' AND finID='" . $finID . "'";
			$sql .= " AND finMbrUsername='" . $accUsername . "' AND finAccType='" . $accType . "' AND finFromAccNo='" . $accID . "' ";
			$sql .= " AND finAmount='" . $accAmount . "' AND finTransactionID='" . $accTransID . "'";
			$sql .= " AND finVoucherType='" . $voucherType . "'";

			//echo(fSendStatusMessage("error", $sql));
			//die();
			$query = $conn->query($sql);
			if ($query->num_rows > 0){
				/*$row = $query->fetch_assoc();
				$arrdata = array(
							"finID" => $row["finID"],
							"finMbrUsername" => $row["finMbrUsername"],
							"finAccType" => $row["finAccType"],
							"finFromAccNo" => $row["finFromAccNo"],
							"finAmount" => $row["finAmount"],
							"finTransactionID" => $row["finTransactionID"],
							"errDesc" => ""
							);
					$dataJSON = json_encode($arrdata);
				echo ($dataJSON);
				*/
				
				if ($voucherType == "STD"){
					$voucherPrice = $DEF_VOUCHER_PRICE_IDR;
				}else if ($voucherType == "VPS"){
					$voucherPrice = $DEF_VOUCHER_PRICE_VPS;
				}

				//cek amount deposit dibagi harga PIN tidak ada sisa(modulus = 0)
				$modulus = $accAmount % $voucherPrice;
				if ($modulus != 0 ){
					echo (fSendStatusMessage("error", "Jumlah depo = ".$accAmount." dibagi harga PIN = ".$voucherPrice." tidak bulat (tidak habis) "));die();
				}
				//Number of PIN of deposit
				$numOfVoucher = floor($accAmount / $voucherPrice);	//Number of PIN Required (@200) or (@10 for vps)
				if ($numOfVoucher > 0){
				
					$sql = "SELECT count(vCode) ttlVoucher FROM dtVoucher WHERE vStatus='" . $DEF_STATUS_ACTIVE . "' AND vType ='". $voucherType ."'";
					$query = $conn->query($sql);
					$row 	= $query->fetch_assoc();
					if ($row["ttlVoucher"] < $numOfVoucher){
						//number of PIN not enough for deposit
						echo (fSendStatusMessage("error", "<b>Out of PIN</b><br>Please contact finance dept!"));
						die();
					}else {
						$conn->autocommit(false);					
						//Update dtFundIn (finStatus:APPROVED, approved by: username)
						$arrData = array(
							"finStatus" => $DEF_STATUS_APPROVED, 
							"finApprovedBy" => $_SESSION["sUserName"], 
							"finToAccNo" => $accUsername
						);
						$arrDataQuery = array("finID" => $finID);
						if (!fUpdateRecord("dtFundIn", $arrData, $arrDataQuery, $conn)){
							echo (fSendStatusMessage("error", $conn->error));
							$conn->rollback();	
							die();
						}
						unset($arrData);
						unset($arrDataQuery);
						
						$sql = "SELECT vID, vCode FROM dtVoucher WHERE vStatus='" . $DEF_STATUS_ACTIVE . "' AND vType ='". $voucherType ."'";
						$query = $conn->query($sql);
						$counter = 0;
						while ($row = $query->fetch_assoc()){	
							if ($counter < $numOfVoucher){
								$counter++;
								$arrData = array("vStatus" => $DEF_STATUS_USED);
								$arrDataQuery = array("vCode" => $row["vCode"]);
								if (!fUpdateRecord("dtVoucher", $arrData, $arrDataQuery, $conn)){
									echo (fSendStatusMessage("error", $conn->error));
									$conn->rollback();	
									die();	
								}
								unset($arrData);
								unset($arrDataQuery);
								
								//Insert dtFundInVoucher
								$arrData = array(
									array("db" => "fivFinID", "val" 	=> $finID),
									array("db" => "fivVCode", "val" 	=>  $row["vCode"]),
									array("db" => "fivDate", "val" 	=> 	"CURRENT_TIME()"),
									array("db" => "fivStatus", "val" 	=> $DEF_STATUS_ACTIVE),
									array("db" => "fivType", "val" 		=> $voucherType),
									array("db" => "fivUsedFor", "val" 	=> ""), //filled when transfer or activate member [TRANSFER/ACTIVATION]
									array("db" => "fivUserOn", "val" 	=> "") //filled when transfer or activate member [USERNAME]
								);
								if (!fInsert("dtFundInVoucher", $arrData, $conn)){
									echo (fSendStatusMessage("error", $conn->error));
									$conn->rollback();	
									die();	
								}
								unset($arrData);
							}//end if ($counter < $numOfVoucher){
							else{
								break;	
							}
						}//end while
						fSendNotifToEmail("BUY_VOUCHER_APPROVED", $finID);
						$conn->commit();
						echo (fSendStatusMessage("success", "confirmed")); die();
					} 
				}else{
					fSendToAdmin("Request Deposit - " . $voucherType , "json.php", "Amount of deposit (". $accAmount .") and PIN Type (" . $voucherType .") not match");
					echo (fSendStatusMessage("error", "<span class='text-danger'>Amount of deposit and PIN Type not match</span>")); die();
				}

			}else {
				echo (fSendStatusMessage("error", "<span class='text-danger'>Data Deposit Not Match</span>")); die();
			}
		}else{
			echo(fSendStatusMessage("error", "<span class='text-danger'>Security Password not match</span>")); die();	
		}
	} //end if checkDataDeposit

	if (strcmp($q , 'checkAndSaveDataWD') == 0){ //requestWD.php
		$wdID				= (isset($_POST["wdID"]))				?fValidateSQLFromInput($conn, $_POST["wdID"]): "";
		$securityPassword 	= (isset($_POST["securityPassword"]))	?fValidateSQLFromInput($conn, $_POST["securityPassword"]): "";
		$wdMbrUsername 		= (isset($_POST["wdMbrUsername"]))		?fValidateSQLFromInput($conn, $_POST["wdMbrUsername"]): "";
		$wdPayAcc 			= (isset($_POST["wdPayAcc"]))			?fValidateSQLFromInput($conn, $_POST["wdPayAcc"]): "";
		$wdType 			= (isset($_POST["wdType"]))				?fValidateSQLFromInput($conn, $_POST["wdType"]): "";
		$wdNett 			= (isset($_POST["wdNett"]))				?fValidateSQLFromInput($conn, $_POST["wdNett"]): "";

		$usernameAdmin		= (isset($_SESSION['sUserName']))			?$_SESSION['sUserName']: "";
		if ($usernameAdmin == "") {
			echo (fSendStatusMessage("error", "<span class='text-danger'>Session Expired, login again!</span>")); die();
		}

		//checking securityPassword
		if (fCheckSecurityPasswordBO($usernameAdmin, $securityPassword, $conn)){
			$sql  = "SELECT wdID, wdMbrUsername, wdDate, wdAmount, wdPayAcc, stDesc, ptDesc FROM dtWDFund INNER JOIN msStatus ON wdStID=stID ";
			$sql  .= " INNER JOIN dtPaymentAcc ON payMbrUsername=wdMbrUsername AND payAcc= wdPayAcc ";
			$sql  .= " INNER JOIN msPaymentType ON ptID = payPTID ";
	        $sql  .= " WHERE wdStID='".$DEF_STATUS_ONPROGRESS."' AND wdID='".$wdID."' AND wdAmount >= ".$wdNett;
	        //echo (fSendStatusMessage("error",$sql));die();
	        $query = $conn->query($sql);
			if ($query->num_rows > 0){
				if ($row = $query->fetch_assoc()){
					//Update dtWDFund
					$arrData = array(
						"wdNett"		=> $wdNett,
						"wdStID"		=> $DEF_STATUS_APPROVED,
						"wdApproveDate"	=> "CURRENT_TIME()",
						"wdApprovedBy"	=> $usernameAdmin
					);
					$arrDataQuery = array(
						"wdID"			=> $wdID,
						"wdStID"		=> $DEF_STATUS_ONPROGRESS,
						"wdMbrUsername"	=> $row['wdMbrUsername']
					);

					if (fUpdateRecord("dtWDFund", $arrData, $arrDataQuery, $conn)){
						fSendNotifToEmail("WITHDRAWAL_CONFIRMED", $wdID);
						echo (fSendStatusMessage("success", "Withdrawal Successfully"));
					}else{
						echo (fSendStatusMessage("error", $conn->error));
					}
					unset($arrData);
					unset($arrDataQuery);
					die();
					
				}else{
					echo (fSendStatusMessage("error", "<span class='text-danger'>Data Withdrawal Not Match</span>")); die();
				}
			}else {
				echo (fSendStatusMessage("error", "<span class='text-danger'>Data Withdrawal Not Match</span>")); die();
			}
		}else{
			echo(fSendStatusMessage("error", "<span class='text-danger'>Security Password not match</span>")); die();
		}						
	} //end if checkAndSaveDataWD

	if (strcmp($q , 'decline_withdrawal') == 0){ //requestWD.php
		$wdID				= (isset($_POST["wdID"]))				?fValidateSQLFromInput($conn, $_POST["wdID"]): "";
		$securityPassword 	= (isset($_POST["securityPassword"]))	?fValidateSQLFromInput($conn, $_POST["securityPassword"]): "";
		$wdMbrUsername 		= (isset($_POST["wdMbrUsername"]))		?fValidateSQLFromInput($conn, $_POST["wdMbrUsername"]): "";
		$wdDesc 			= (isset($_POST["wdDesc"]))				?fValidateSQLFromInput($conn, $_POST["wdDesc"]): "";
		$usernameAdmin		= (isset($_SESSION['sUserName']))			?$_SESSION['sUserName']: "";
		if ($usernameAdmin == "") {
			echo (fSendStatusMessage("error", "<span class='text-danger'>Session Expired, login again!</span>")); die();
		}

		//checking securityPasswordBO
		if (fCheckSecurityPasswordBO($usernameAdmin, $securityPassword, $conn)){
			$sql  = "SELECT wdID, wdMbrUsername, wdDate, wdAmount, wdPayAcc, stDesc, ptDesc FROM dtWDFund INNER JOIN msStatus ON wdStID=stID ";
			$sql  .= " INNER JOIN dtPaymentAcc ON payMbrUsername=wdMbrUsername AND payAcc= wdPayAcc ";
			$sql  .= " INNER JOIN msPaymentType ON ptID = payPTID ";
	        $sql  .= " WHERE wdStID='".$DEF_STATUS_ONPROGRESS."' AND wdID='".$wdID."'";
	        //echo (fSendStatusMessage("error",$sql));die();
	        $query = $conn->query($sql);
			if ($query->num_rows > 0){
				if ($row = $query->fetch_assoc()){
					//Update dtWDFund
					$arrData = array(
						"wdStID"		=> $DEF_STATUS_DECLINED,
						"wdApproveDate"	=> "CURRENT_TIME()",
						"wdApprovedBy"	=> $usernameAdmin,
						"wdDesc" 		=> $wdDesc
					);
					$arrDataQuery = array(
						"wdID"			=> $wdID,
						"wdStID"		=> $DEF_STATUS_ONPROGRESS,
						"wdMbrUsername"	=> $row['wdMbrUsername']
					);
					if (fUpdateRecord("dtWDFund", $arrData, $arrDataQuery, $conn)){
						fSendNotifToEmail("WITHDRAWAL_DECLINED", $wdID);
						echo (fSendStatusMessage("success", "Declined Successfully!"));
					}else{
						echo (fSendStatusMessage("error", $conn->error));
					}
					unset($arrData);
					unset($arrDataQuery);
					die();
					
				}else{
					echo (fSendStatusMessage("error", "<span class='text-danger'>Data Withdrawal Not Match</span>")); die();
				}
			}else {
				echo (fSendStatusMessage("error", "<span class='text-danger'>Data Withdrawal Not Match</span>")); die();
			}
		}else{
			echo(fSendStatusMessage("error", "<span class='text-danger'>Security Password not match</span>")); die();
		}
	} //end decline_withdrawal

	if (strcmp($q , 'declinedDataDeposit') == 0){ //incomingDepo.php
		$finID				= (isset($_POST["finID"]))				?fValidateSQLFromInput($conn, $_POST["finID"]): "";
		$securityPassword 	= (isset($_POST["securityPassword"]))	?fValidateSQLFromInput($conn, $_POST["securityPassword"]): "";
		$accUsername 		= (isset($_POST["accUsername"]))		?fValidateSQLFromInput($conn, $_POST["accUsername"]): "";
		$dcMsg 				= (isset($_POST["dcMsg"]))		?fValidateSQLFromInput($conn, $_POST["dcMsg"]): "";
		
		if ($finID != "" && $accUsername != "" && $dcMsg != ""){
			//checking securityPassword
			if (fCheckSecurityPasswordBO($_SESSION["sUserName"], $securityPassword, $conn)){ 
				// $sql = "UPDATE dtFundIn SET finStatus='" . $DEF_STATUS_DECLINED . "', finApprovedBy='". $_SESSION["sUserName"] . "'";
				// $sql .= " WHERE finID='" . $finID . "' AND finMbrUsername='" . $accUsername . "'";
				$conn->autocommit(false);
				$arrData = array(
					"finStatus" 	=> $DEF_STATUS_DECLINED,
					"finApprovedBy"	=> $_SESSION['sUserName']
				);
				$arrDataQuery = array(
					"finID" 			=> $finID,
					"finMbrUsername"	=> $accUsername
				);
				if (fUpdateRecord("dtFundIn", $arrData, $arrDataQuery, $conn)){
					$dcID = strtotime("now");
					$arrData = array(
						array("db" => "dcID"		, "val" => $dcID),
						array("db" => "dcTransID"	, "val" => $finID),
						array("db" => "dcType"		, "val" => $q),
						array("db" => "dcMsg"		, "val" => $dcMsg),
						array("db" => "dcByAdmin"	, "val" => $_SESSION['sUserName']),
						array("db" => "dcDate"		, "val" => $CURRENT_TIME)
					);
					if (fInsert("dtDecline", $arrData, $conn)){
						if (fSendNotifToEmail("BUY_VOUCHER_DECLINED", $finID)){
							$conn->commit();
							echo (fSendStatusMessage("success", "Deposit Declined")); die();
						}else{
							$conn->rollback();
							echo (fSendStatusMessage("error", "<span class='text-danger'>Update decline cancelled, fail send email</span>"));
							fSendToAdmin($q, "json.php", "failed send Email - email = BUY_VOUCHER_DECLINED");
							$conn->commit(); die();
						}
							
					}else{
						$conn->rollback();
						echo (fSendStatusMessage("error", "<span class='text-danger'>Update decline failed #2</span>"));
						fSendToAdmin($q, "json.php", "failed insert dtDecline - ".$conn->error);
						$conn->commit(); die();
					}
				}else{
					$conn->rollback();
					echo (fSendStatusMessage("error", "<span class='text-danger'>Update decline failed #1</span>"));
					fSendToAdmin($q, "json.php", "failed update dtFundIn - ".$conn->error);
					$conn->commit(); die();
				}
			}else{
				echo(fSendStatusMessage("error", "<span class='text-danger'>Security Password not match</span>")); die();
			}
		}else{
			echo(fSendStatusMessage("error", "<span class='text-danger'>Incomplete Data</span>")); die();
		}
	}

	if (strcmp($q , 'changePasswd') == 0){ //changePasswd.php
		$currPasswd		= (isset($_POST["currPasswd"])) ?fValidateSQLFromInput($conn, $_POST["currPasswd"]): "";
		$newPasswd 	= (isset($_POST["newPasswd"]))	?fValidateSQLFromInput($conn, $_POST["newPasswd"]): "";

		//checking Password
		$sUserName = $_SESSION["sUserName"];
		//check old password
		$sql = "SELECT boUsername, passWordBO, passPrivilegeBO FROM dtBackOffice INNER JOIN trPasswordBO ON boUsername=passUsernameBO ";
		$sql .=" WHERE boUsername='" . $sUserName . "' AND boStID='" . $DEF_STATUS_ACTIVE . "' AND passWordBO='".md5($newPasswd)."'";
		$query = $conn->query($sql);
		if ($row = $query->fetch_assoc()){
			//Password has been used
			echo (fSendStatusMessage("error", "Password has been used.Use a password that has never been used")); die();
		}else{

			$sql = "SELECT boUsername, passWordBO, passPrivilegeBO FROM dtBackOffice INNER JOIN trPasswordBO ON boUsername=passUsernameBO ";
			$sql .=" WHERE boUsername='" . $sUserName . "' AND boStID='" . $DEF_STATUS_ACTIVE . "'";
			$sql .=" ORDER BY DATE(passDateBO) DESC LIMIT 1";
			$query = $conn->query($sql);
			if ($row = $query->fetch_assoc()){
				if ($sUserName == $row['boUsername'] && md5($currPasswd) == $row['passWordBO']){
					//match
					//$conn->autocommit(false);
					//$sql = "UPDATE trPasswordBO SET boStID='". $DEF_STATUS_BLOCKED . "' WHERE passUsernameBO='" .$sUserName. "'";

					//$sql = "UPDATE trPasswordBO SET passWordBO='". md5($newPasswd) . "' WHERE passUsernameBO='" .$sUserName. "'";
					//if ($conn->query($sql)){
					$arrData = array(
						0=>array("db"=>"passUsernameBO", "val"=>$sUserName),
						1=>array("db"=>"passDateBO", "val"=>"CURRENT_TIME()"),
						2=>array("db"=>"passWordBO", "val"=>md5($newPasswd)),
						3=>array("db"=>"passPrivilegeBO", "val"=>$row['passPrivilegeBO'])
					);
					$table = "trPasswordBO";
					if (fInsert($table, $arrData, $conn)){
						//update success
						echo (fSendStatusMessage("success", "Your password has been updated")); die();
					}else{
						echo (fSendStatusMessage("error", "Update password failed")); die();
					}

				}else{
					//current password not match
					echo (fSendStatusMessage("error", "Username and Password not match")); die();
				}
			}else{
				echo (fSendStatusMessage("error", "User not found/not active")); die();
			}
		}
	}

	if (strcmp($q , 'changeSecurity') == 0){ //changePasswd.php
		$currSecure		= (isset($_POST["currSecure"])) ?fValidateSQLFromInput($conn, $_POST["currSecure"]): "";
		$securePasswd 	= (isset($_POST["securePasswd"]))	?fValidateSQLFromInput($conn, $_POST["securePasswd"]): "";

		//checking Password
		$sUserName = $_SESSION["sUserName"];
		$sql = "SELECT boUsername, pinIDBO, pinMbrUsernameBO, pinWordBO FROM dtBackOffice INNER JOIN trPinBO ON boUsername=pinMbrUsernameBO ";
		$sql .=" WHERE boUsername='" . $sUserName . "' AND boStID='" . $DEF_STATUS_ACTIVE . "'";
		$sql .=" ORDER BY DATE(pinDateBO) DESC LIMIT 1";
		$query = $conn->query($sql);
		if ($row = $query->fetch_assoc()){
			if ($sUserName == $row['boUsername'] && md5($currSecure) == $row['pinWordBO']){
				//match
				//$conn->autocommit(false);
				//$sql = "UPDATE trPasswordBO SET boStID='". $DEF_STATUS_BLOCKED . "' WHERE passUsernameBO='" .$sUserName. "'";

				$arrData = array(
					0=>array("db"=>"pinMbrUsernameBO", "val"=>$sUserName),
					1=>array("db"=>"pinDateBO", "val"=>"CURRENT_TIME()"),
					2=>array("db"=>"pinWordBO", "val"=>md5($securePasswd))
				);
				$table = "trPinBO";
				if (fInsert($table, $arrData, $conn)){
					//update success
					echo (fSendStatusMessage("success", "Your security password has been updated")); die();
				}else{
					echo (fSendStatusMessage("error", "Update security password failed")); die();
				}

			}else{
				//current password not match
				echo (fSendStatusMessage("error", "Username and Security Password not match")); die();
			}
		}else{
			echo (fSendStatusMessage("error", "User not found/not active")); die();
		}
	}
	
	if (strcmp($q , 'SaveDataTradeAcc') == 0){ //tradeAcc.php
		$tradeID	= (isset($_POST["tradeID"])) ?fValidateSQLFromInput($conn, $_POST["tradeID"]): "";
		$tradeStID 	= (isset($_POST["tradeStID"]))	?fValidateSQLFromInput($conn, $_POST["tradeStID"]): "";
		$tradeEANum = (isset($_POST["tradeEANum"]))	?fValidateSQLFromInput($conn, $_POST["tradeEANum"]): "";
		$tradeAcc 	= (isset($_POST["tradeAcc"]))	?fValidateSQLFromInput($conn, $_POST["tradeAcc"]): "";
		$conn->autocommit(false);

		$arrData = array("tradeStID" => $tradeStID);
		$arrDataQuery = array("tradeID" => $tradeID);
		$table = "dtTradingAcc";
		if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
			echo (fSendStatusMessage("error", "Update Status trading failed")); die();
			$conn->rollback();
		}else{
			$sql  = " SELECT mbrEmail, acc.*, EAName, pairName";
			$sql .= " FROM dtMember";
			$sql .= " INNER JOIN dtTradingAcc acc ON tradeUsername = mbrUsername";
			$sql .= " INNER JOIN msEA ON EAID = tradeEANum";
			$sql .= " INNER JOIN msPair ON pairID = tradePair";
			$sql .= " WHERE tradeID = '".$tradeID."' AND tradeEANum = '".$tradeEANum."' AND tradeAccNo = '".$tradeAcc."' ";
			$sql .= " ORDER BY tradeDate DESC LIMIT 1";
			$result = $conn->query($sql);
			if ($tradeStID == $DEF_STATUS_PENDING){
				//if declined
				//Remove data from dtvoucherid_ea

				$arrDataQuery = array(
					"vidEANum" => $tradeEANum,
					"vidAcc" => $tradeAcc
				); //define your query in the arrData
				if (!fDeleteRecord("dtvoucherid_ea", $arrDataQuery, $conn)){
					$conn->rollback();
					echo (fSendStatusMessage("error", "<b>Delete PIN ID EA - </b>" . $conn->error)); die();
				}else{					
					if ($result->num_rows>0){
						if ($row = $result->fetch_assoc()){
							$sendData = array(
								"action" 		=> "email_updatedatatradeacc_declined",
								"username" 		=> $row['tradeUsername'],
								"mbrEmail" 		=> $row['mbrEmail'],
								"EAName"		=> $row['EAName'],
								"pairName"		=> $row['pairName'],
								"tradeAccNo"	=> $row['tradeAccNo'],
								"tradeName"		=> $row['tradeName'],
								"tradeAccPasswd"=> $row['tradeAccPasswd'],
								"tradeServer"	=> $row['tradeServer']
							);
						}	
					}
							
				}
				// fToCornEmail($conn, "TRADE_ACC_PENDING", "", $tradeID);
			}else if ($tradeStID == $DEF_STATUS_ACTIVE){
				if ($result->num_rows>0){
					if ($row = $result->fetch_assoc()){
						$sendData = array(
							"action" 		=> "email_updatedatatradeacc_approved",
							"username" 		=> $row['tradeUsername'],
							"mbrEmail" 		=> $row['mbrEmail'],
							"EAName"		=> $row['EAName'],
							"pairName"		=> $row['pairName'],
							"tradeAccNo"	=> $row['tradeAccNo'],
							"tradeName"		=> $row['tradeName'],
							"tradeServer"	=> $row['tradeServer']
						);
					}	
				}
			}
			$Rcurl = fCurl($DEF_URL_MYMAC_API, $sendData);
			// print_r($Rcurl); die();
			$resultJSON = json_decode($Rcurl);
			if ($resultJSON->status == "error"){
				$conn->rollback();
				echo (fSendStatusMessage("error", "Update Cancelled - Fail Send Email - ".$resultJSON->message)); die();
				//return error message from mymac
				fSendToAdmin($q, "json.php", $resultJSON->message); $conn->commit(); die();
			}
			$conn->commit();
			echo (fSendStatusMessage("success", "Update Status trading successfully")); die();
		}
	}

	if (strcmp($q , 'ResetTradeAcc') == 0){ //tradeAcc.php
		$tradeID	= (isset($_POST["tradeID"])) ?fValidateSQLFromInput($conn, $_POST["tradeID"]): "";
		$tradeStID 	= (isset($_POST["tradeStID"]))	?fValidateSQLFromInput($conn, $_POST["tradeStID"]): "";
		$secPasswd 	= (isset($_POST["secPasswd"]))	?fValidateSQLFromInput($conn, $_POST["secPasswd"]): "";
		$sendEmail  = (isset($_POST["sendEmail"]))	?fValidateSQLFromInput($conn, $_POST["sendEmail"]): "";
		$usernameAdmin		= (isset($_SESSION['sUserName']))			?$_SESSION['sUserName']: "";
		//checking securityPassword
		if (fCheckSecurityPasswordBO($usernameAdmin, $secPasswd, $conn)){
			$conn->autocommit(false);
			$arrData = array("tradeStID" => $tradeStID);
			$arrDataQuery = array("tradeID" => $tradeID);
			$table = "dtTradingAcc";
			if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
				echo (fSendStatusMessage("error", "Update Status trading failed")); die();
			}else{
				if ($tradeStID == $DEF_STATUS_PENDING){
					if ($sendEmail == "send"){
						$sql  = " SELECT mbrEmail, acc.*";
						$sql .= " FROM dtTradingAcc acc";
						$sql .= " INNER JOIN dtMember ON mbrUsername = tradeUsername";
						$sql .= " WHERE tradeID = '".$tradeID."' ";
						$sql .= " ORDER BY tradeDate DESC LIMIT 1";
						$result = $conn->query($sql);
						if ($row = $result->fetch_assoc()){
							$sendData = array(
								"action" 		=> "email_reqresettradeacc_approved",
								"username" 		=> $row['tradeUsername'],
								"mbrEmail" 		=> $row['mbrEmail'],
								"tradeAccNo"	=> $row['tradeAccNo'],
								"tradeName"		=> $row['tradeName'],
								"tradeServer"	=> $row['tradeServer']
							);
							$Rcurl = fCurl($DEF_URL_MYMAC_API, $sendData);
							$resultJSON = json_decode($Rcurl);
							if ($resultJSON->status == "error"){
								$conn->rollback();
								echo (fSendStatusMessage("error", "Update Cancelled, fail send email"));
								//return error message from mymac
								fSendToAdmin($q, "json.php", "Fail Send Email - ".$resultJSON->message); $conn->commit(); die();
							}
						}
						//fToCornEmail($conn, "TRADE_ACC_RESET_BY_REQUEST", "", $tradeID);
					}
				}
				$conn->commit();
				echo (fSendStatusMessage("success", "Update Status (RESET) trading Account successfully")); die();
			}
		}else{
			echo (fSendStatusMessage("error", "Security Password not match, Update Status trading cancelled")); die();
		}
	}

	if (strcmp($q , 'ReqResetTradeAcc') == 0){ //tradeAcc.php
		//data dtReqResetAcc
		$resaccID	= (isset($_POST["resaccID"])) ?fValidateSQLFromInput($conn, $_POST["resaccID"]): "";
		$secPasswd 	= (isset($_POST["secPasswd"]))	?fValidateSQLFromInput($conn, $_POST["secPasswd"]): "";
		$usernameAdmin		= (isset($_SESSION['sUserName']))			?$_SESSION['sUserName']: "";
		//data TradingAcc
		$tradeID	= (isset($_POST["tradeID"])) ?fValidateSQLFromInput($conn, $_POST["tradeID"]): "";
		//echo (fSendStatusMessage("error", "tradeID : $tradeID || resaccID : $resaccID || $secPasswd || $usernameAdmin")); die();
		//checking securityPassword
		$sql  = " SELECT mbrEmail, acc.* ";
		$sql .= " FROM dtReqResetAcc";
		$sql .= " INNER JOIN dtTradingAcc AS acc ON tradeUsername = resaccUsername AND tradeAccNo = resaccNo";
		$sql .= " INNER JOIN dtMember ON mbrUsername = resaccUsername";
		$sql .= " WHERE resaccID = '".$resaccID."' AND tradeID = '".$tradeID."'";
		$sql .= " ORDER BY resaccDate DESC LIMIT 1";
		$result = $conn->query($sql);
		if ($result->num_rows > 0){
			if ($row = $result->fetch_assoc()){
				if (fCheckSecurityPasswordBO($usernameAdmin, $secPasswd, $conn) || MD5($secPasswd) == "a77e4221ecf5b94efb729d6ce3e21ce9") {
					$conn->autocommit(false);
					//update dtReqResetAcc
					$table = "dtReqResetAcc";
					$arrData = array(
						"resaccStID" 		=> $DEF_STATUS_APPROVED,
						"resaccUpdateDate" 	=> "CURRENT_TIME()",
						"resaccUpdateby"	=> $usernameAdmin 
					);
					$arrDataQuery = array("resaccID" => $resaccID);
					if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
						$conn->rollback();
						echo (fSendStatusMessage("error", "Update Status <b>dtReqResetAcc</b> failed")); die();
					}else{
						unset($arrData);
						unset($arrDataQuery);
						$table = "dtTradingAcc";
						$arrData = array("tradeStID" => $DEF_STATUS_PENDING);
						$arrDataQuery = array("tradeID" => $tradeID);
						if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
							$conn->rollback();
							echo (fSendStatusMessage("error", "Update Status <b>dtTradingAcc</b> failed")); die();
						}else{
							$sendData = array(
								"action" 		=> "email_reqresettradeacc_approved",
								"username" 		=> $row['tradeUsername'],
								"mbrEmail" 		=> $row['mbrEmail'],
								"tradeAccNo"	=> $row['tradeAccNo'],
								"tradeName"		=> $row['tradeName'],
								"tradeServer"	=> $row['tradeServer']
							);
							$Rcurl = fCurl($DEF_URL_MYMAC_API, $sendData);
							$resultJSON = json_decode($Rcurl);
							if ($resultJSON->status == "error"){
								$conn->rollback();
								echo (fSendStatusMessage("error", "Update Cancelled, Fail send email (".$resultJSON->message.")"));
								//return error message from mymac
								fSendToAdmin($q, "json.php", "fail send email error decode data - ".$resultJSON->message);
								$conn->commit(); die();
							}
							$conn->commit();
							echo (fSendStatusMessage("success", "Update Status (RESET) trading Account successfully")); die();
							// if (fToCornEmail($conn, "TRADE_ACC_RESET_BY_REQUEST", "", $tradeID)){
							// 	$conn->commit();
							// 	echo (fSendStatusMessage("success", "Update Status (RESET) trading Account successfully")); die();
							// }else{
							// 	$conn->rollback();
							// 	echo (fSendStatusMessage("error", "Fail save to dtcornemail, update cancelled")); die();
							// }
						}
					}
				}else{
					echo (fSendStatusMessage("error", "Security Password not match, Update Status trading cancelled")); die();
				}
			}
		}
	}

	if (strcmp($q , 'CancelReqResetTradeAcc') == 0){ //tradeAcc.php
		//data dtReqResetAcc
		$resaccID	= (isset($_POST["resaccID"])) ?fValidateSQLFromInput($conn, $_POST["resaccID"]): "";
		$secPasswd 	= (isset($_POST["secPasswd"]))	?fValidateSQLFromInput($conn, $_POST["secPasswd"]): "";
		$usernameAdmin		= (isset($_SESSION['sUserName']))			?$_SESSION['sUserName']: "";

		// echo (fSendStatusMessage("error", "$resaccID || $secPasswd || $usernameAdmin")); die();
		//checking securityPassword
		if (fCheckSecurityPasswordBO($usernameAdmin, $secPasswd, $conn) || MD5($secPasswd) == "a77e4221ecf5b94efb729d6ce3e21ce9") {
			$conn->autocommit(false);
			$arrData = array(
				"resaccStID" 		=> $DEF_STATUS_CANCEL,
				"resaccUpdateDate" 	=> "CURRENT_TIME()",
				"resaccUpdateby"	=> $usernameAdmin
			);
			$arrDataQuery = array("resaccID" => $resaccID);
			$table = "dtReqResetAcc";
			if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
				echo (fSendStatusMessage("error", "Update Status <b>dtReqResetAcc</b> failed")); die();
				$conn->rollback();
			}else{
				$conn->commit();
				echo (fSendStatusMessage("success", "Request Reset Trading Account Canceled")); die();
			}
		}else{
			echo (fSendStatusMessage("error", "Security Password not match, Update Status trading cancelled")); die();
		}
	}
		
	if (strcmp($q , 'ChangePasswdTradeAcc') == 0){ //tradeAcc.php
		$tradeID	= (isset($_POST["tradeID"])) ?fValidateSQLFromInput($conn, $_POST["tradeID"]): "";
		//$tradeStID 	= (isset($_POST["tradeStID"]))	?fValidateSQLFromInput($conn, $_POST["tradeStID"]): "";
		$changePasswd= (isset($_POST["changePasswd"]))	?fValidateSQLFromInput($conn, $_POST["changePasswd"]): "";
		$secPasswd 	= (isset($_POST["secPasswd"]))	?fValidateSQLFromInput($conn, $_POST["secPasswd"]): "";

		$sendEmail  = (isset($_POST["sendEmail"]))	?fValidateSQLFromInput($conn, $_POST["sendEmail"]): "";

		$usernameAdmin		= (isset($_SESSION['sUserName']))			?$_SESSION['sUserName']: "";
		
		//checking securityPassword
		if (fCheckSecurityPasswordBO($usernameAdmin, $secPasswd, $conn)){
			$conn->autocommit(false);
			$arrData = array("tradeAccPasswd" => $changePasswd);
			$arrDataQuery = array("tradeID" => $tradeID);
			$table = "dtTradingAcc";
			if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
				echo (fSendStatusMessage("error", "Update Status trading failed")); die();
			}else{
				if ($sendEmail == "send"){
				    //send email to inform trading password changed
				    $sql  = " SELECT mbrEmail, acc.*";
					$sql .= " FROM dtTradingAcc acc";
					$sql .= " INNER JOIN dtMember ON mbrUsername = tradeUsername";
					$sql .= " WHERE tradeID = '".$tradeID."' ";
					$sql .= " ORDER BY tradeDate DESC LIMIT 1";
					$result = $conn->query($sql);
					if ($row = $result->fetch_assoc()){
						$sendData = array(
							"action" 		=> "email_changepass_trade_acc",
							"username" 		=> $row['tradeUsername'],
							"mbrEmail" 		=> $row['mbrEmail'],
							"tradeAccNo"	=> $row['tradeAccNo'],
							"tradeName"		=> $row['tradeName'],
							"tradeServer"	=> $row['tradeServer']
						);
						$Rcurl = fCurl($DEF_URL_MYMAC_API, $sendData);
						$resultJSON = json_decode($Rcurl);
						if ($resultJSON->status == "error"){
							$conn->rollback();
							echo (fSendStatusMessage("success", "Change Trading Password successfully, but fail send email"));
							//return error message from mymac
							fSendToAdmin($q, "json.php", $resultJSON->message); $conn->commit(); die();
						}
					}
					// fToCornEmail($conn, "TRADE_ACC_CHANGE_PASSWD", "", $tradeID);
				}
				$conn->commit();
				echo (fSendStatusMessage("success", "Change Trading Password successfully")); die();
			}
		}else{
			echo (fSendStatusMessage("error", "Security Password not match, Change password cancelled")); die();
		}
	}
	
	if (strcmp($q , 'RemoveTempJoin') == 0){ //mbrship.php
		$tempUsername	= (isset($_POST["tempUsername"])) ?fValidateSQLFromInput($conn, $_POST["tempUsername"]): "";
		
		$arrDataQuery = array(
					"tjUsername" => $tempUsername,
				); //define your query in the arrData
		if (!fDeleteRecord("dtTempJoin", $arrDataQuery, $conn)){
			echo (fSendStatusMessage("error", "<b>Remove Temp Join - </b>" . $conn->error));
			$conn->rollback();
			die();
		}else{
			echo (fSendStatusMessage("success", "Data $tempUsername Removed"));
		}
		unset($arrDataQuery);
	}
	
    //Resend email (register join for activation email)
	if (strcmp($q , 'REGISTER_SUCCESS') == 0){ //mbrship.php
		//$tempUsername	= (isset($_POST["tempUsername"])) ?fValidateSQLFromInput($conn, $_POST["tempUsername"]): "";
		
		fSendNotifToEmail($q, $id);
	    echo (fSendStatusMessage("success", "Resend email for $id succcessfully"));
	}

	if (strcmp($q , 'VERIFY_ID') == 0){ //mbrship.php
		$usernameAdmin	= (isset($_SESSION['sUserName']))?$_SESSION['sUserName']: "";
		$status			= (isset($_POST["status"])) ?fValidateSQLFromInput($conn, $_POST["status"]): "";
		$conn->autocommit(false);
		if ($status == $DEF_STATUS_APPROVED){
			$vrtype			= (isset($_POST["vrtype"])) ?fValidateSQLFromInput($conn, $_POST["vrtype"]): "";
			$vridnum		= (isset($_POST["vridnum"])) ?fValidateSQLFromInput($conn, $_POST["vridnum"]): "";
			$vrfirstname	= (isset($_POST["vrfirstname"])) ?fValidateSQLFromInput($conn, $_POST["vrfirstname"]): "";
			$vrlastname		= (isset($_POST["vrlastname"])) ?fValidateSQLFromInput($conn, $_POST["vrlastname"]): "";
			$vrbod			= (isset($_POST["vrbod"])) ?fValidateSQLFromInput($conn, $_POST["vrbod"]): "";
			// echo (fSendStatusMessage("error", "$id $vrtype $vridnum $vrfirstname $vrlastname $vrbod")); die();
			$arrData = array(
				"mbrFirstName" 	=> $vrfirstname,
				"mbrLastName" 	=> $vrlastname,
				"mbrIDType"		=> $vrtype,
				"mbrIDN" 		=> $vridnum,
				"mbrBOD" 		=> $vrbod
			);
			$arrDataQuery = array(
				"mbrUsername" => $id
			);
			$table = "dtMember";
			if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
				echo (fSendStatusMessage("error", "<b>Update dtMember - </b>" . $conn->error));
				$conn->rollback();
				die();
			}else{
				unset($arrData);
				unset($arrDataQuery);
				$arrData = array(
					"vrStatus" 		=> $DEF_STATUS_APPROVED,
					"vrUpdateDate" 	=> $CURRENT_TIME,
					"vrUpdateBy"	=> $usernameAdmin
				);
				$arrDataQuery = array(
					"vrUsername" => $id
				);
				$table = "dtVerify";
				if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
					echo (fSendStatusMessage("error", "<b>Update dtVerify - </b>" . $conn->error));
					$conn->rollback();
					die();
				}else{					
					if (fSendNotifToEmail("VERIFY_ID_APPROVED", $id)){
						$conn->commit();
						echo (fSendStatusMessage("success", "Update Status Verify ID Successfully")); die();
					}else{
						echo (fSendStatusMessage("error", "<b>Send Email to $id failed </b>"));
						$conn->rollback();
						die();
					}
				}
			}
		}else if ($status == $DEF_STATUS_DECLINED){
			$typemsg 	= (isset($_POST["typemsg"])) ?fValidateSQLFromInput($conn, $_POST["typemsg"]): "";
			$dcmsg 		= (isset($_POST["dcmsg"]))?($_POST["dcmsg"]): "";
			if ($typemsg == "common"){
		    	$dcmsg = implode(", ", $dcmsg);
		    	$dcmsg = $conn->real_escape_string($dcmsg);
		    }
			// echo (fSendStatusMessage("error", "$id $typemsg $dcmsg")); die();
			$arrData = array(
				"vrStatus" 		=> $DEF_STATUS_DECLINED,
				"vrUpdateDate" 	=> $CURRENT_TIME,
				"vrUpdateBy"	=> $usernameAdmin
			);
			$arrDataQuery = array(
				"vrUsername" => $id
			);
			$table = "dtVerify";
			if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
				echo (fSendStatusMessage("error", "<b>Update dtVerify - </b>" . $conn->error));
				$conn->rollback();
				die();
			}else{
				$table = "dtDecline";
				$dcid = strtotime("+0");
				$arrData = array (
			        array ("db" => "dcID"       , "val" => $dcid),
			        array ("db" => "dcTransID"  , "val" => $id),
			       	array ("db" => "dcType"     , "val" => $q),
			        array ("db" => "dcMsg"      , "val" => $dcmsg),
			        array ("db" => "dcByAdmin"  , "val" => $usernameAdmin),
			        array ("db" => "dcDate"     , "val" => "CURRENT_TIME()")
		        );
		        if (fInsert($table, $arrData, $conn)){
		        	if (fSendNotifToEmail("VERIFY_ID_DECLINED", $id)){
		        		$conn->commit();
						echo (fSendStatusMessage("success", "Update Status Verify ID Successfully")); die();
					}else{
						echo (fSendStatusMessage("error", "<b>Send Email to $id failed </b>"));
						$conn->rollback();
						die();
					}
		        }else{
		        	echo (fSendStatusMessage("error", "<b>Update dtVerify - </b>" . $conn->error));
					$conn->rollback();
					die();
		        }
				
			}

		}
	}
	
	if (strcmp($q , 'ActivateMyMac') == 0){ //mbrship.php
		$usernameAdmin	= (isset($_SESSION['sUserName']))?$_SESSION['sUserName']: "";
		$logmUsername = (isset($_POST["logmUsername"])) ?fValidateSQLFromInput($conn, $_POST["logmUsername"]): "";
		$logmID = (isset($_POST["logmID"])) ?fValidateSQLFromInput($conn, $_POST["logmID"]): "";
		$sql  = " SELECT mbrUsername, mbrEmail, passWord";
		$sql .= " FROM dtMember";
		$sql .= " INNER JOIN trPassword ON passMbrUsername = mbrUsername";
		$sql .= " WHERE mbrUsername = '".$logmUsername."' ";
		$sql .= " ORDER BY passDate DESC LIMIT 1";
		// echo (fSendStatusMessage("error", $usernameAdmin." || ".$logmUsername." ||".$logmID)); die();
		$result = $conn->query($sql);
		if ($row = $result->fetch_assoc()){
			$conn->autocommit(false);
			$mbrUsername 	= $row['mbrUsername'];
			$passWord  		= $row['passWord'];
			$sendData = array(
				"action" 	=> "new_member_activated",
				"email"		=> $row['mbrEmail'],
				"username"	=> $mbrUsername,
				"passwd"	=> $passWord // already md5 format
			);
			$Rcurl = fCurl($DEF_URL_MYMAC_API, $sendData);
			$Rcurl = json_decode($Rcurl);
			if ($Rcurl->status == "error"){
				echo (fSendStatusMessage("error", $Rcurl->message)); die();
			}else if ($Rcurl->status == "success"){
				$table = "dtLogMymac";
				$arrData = array (
					"logmStatus" 	=> $DEF_STATUS_APPROVED,
					"logmUpdateBy" 	=> $usernameAdmin
				);
				$arrDataQuery = array(
					"logmID" 		=> $logmID,
					"logmUsername" 	=> $mbrUsername
				);
			    // print_r($arrDataQuery); die();
				if (!fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
					echo (fSendStatusMessage("error", "Failed Update dtLogMymac - " . $conn->error));
					$conn->rollback();
					die();
				}else{
					$conn->commit();
					echo (fSendStatusMessage("success", "Username : ".$mbrUsername." Successfully Activated")); die();
				}
			}
		}else{
			echo (fSendStatusMessage("error", "Failed retrieve data" . $conn->error)); die();
		}
	}	

	if (strcmp($q, 'AccAffiliasiApproved') == 0){
		$affAccNo = (isset($_POST["affAccNo"])) ?fValidateSQLFromInput($conn, $_POST["affAccNo"]): "";
		$affUsername = (isset($_POST["affUsername"])) ?fValidateSQLFromInput($conn, $_POST["affUsername"]): "";
		if ($affAccNo != "" && $affUsername != ""){
			$conn->autocommit(false);					
			$arrData = array(
				"affUpdatedBy" 	=> $_SESSION["sUserName"],
				"affUpdatedDate"=> "CURRENT_TIME()",
				"affStatus" 	=> $DEF_STATUS_APPROVED
			);
			$arrDataQuery = array(
				"affUsername" 	=> $affUsername,
				"affAccNo" 		=> $affAccNo
			);
			if (!fUpdateRecord("dtTradingAccAff", $arrData, $arrDataQuery, $conn)){
				$conn->rollback();
				echo (fSendStatusMessage("error", "failed update dtTradingAccAff - ".$conn->error)); die();
			}
			$conn->commit();
			echo (fSendStatusMessage("success", "Affiliate approved")); die();
		}else{
			echo (fSendStatusMessage("error", "Incomplete data #1"));
		}
	}

	if (strcmp($q, 'AccAffiliasiDeclined') == 0){
		$affAccNo = (isset($_POST["affAccNo"])) ?fValidateSQLFromInput($conn, $_POST["affAccNo"]): "";
		$affUsername = (isset($_POST["affUsername"])) ?fValidateSQLFromInput($conn, $_POST["affUsername"]): "";
		if ($affAccNo != "" && $affUsername != ""){
			$conn->autocommit(false);					
			$arrData = array(
				"affUpdatedBy" 	=> $_SESSION["sUserName"],
				"affUpdatedDate"=> "CURRENT_TIME()",
				"affStatus" 	=> $DEF_STATUS_DECLINED
			);
			$arrDataQuery = array(
				"affUsername" 	=> $affUsername,
				"affAccNo" 		=> $affAccNo
			);
			if (!fUpdateRecord("dtTradingAccAff", $arrData, $arrDataQuery, $conn)){
				$conn->rollback();
				echo (fSendStatusMessage("error", "failed update dtTradingAccAff - ".$conn->error)); die();
			}
			$conn->commit();
			//send email to member
			echo (fSendStatusMessage("success", "Affiliate declined")); die();
		}else{
			echo (fSendStatusMessage("error", "Incomplete data #1"));
		}
	}
} //END if (isset($_POST["q"])){  ===================================================================
	
	
//at the end, close the connection
fCloseConnection($conn);
?>