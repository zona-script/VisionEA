<?php
session_start();
include_once("../../includes/inc_def.php");
include_once("../../includes/inc_conn.php");
include_once("../../includes/inc_functions.php");

$sConfirmID = (isset($_SESSION['sConfirmID']))?fValidateSQLFromInput($conn, $_SESSION['sConfirmID']):""; //=username
$sCode = (isset($_SESSION['sCode']))?fValidateSQLFromInput($conn, $_SESSION['sCode']):""; //=unique code
$MNav     = (isset($_POST['MNav']))?$_POST['MNav']:"";

$confirmCode 	= (isset($_POST['confirmCode']))?fValidateSQLFromInput($conn, $_POST['confirmCode']):""; //=input code

//echo (fSendStatusMessage("failed", ">>".$sConfirmID . "/". $sCode . "/".$confirmCode));  die();

if ($sConfirmID == "" || $sCode == "" || $confirmCode == ""){
	//no session
	echo (fSendStatusMessage("failed", "Update failed - Incomplete Data"));  die();
}else{

	if ($MNav == "confirm_wd"){ //confirmCode.php
		$sql = "SELECT wdID, wdMbrUsername, wdAmount, wdCode, wdDate, wdPayAcc, stDesc, mbrEmail, mbrFirstName, ptDesc FROM dtWDFund ";
		$sql .= " INNER JOIN dtMember ON wdMbrUsername = mbrUsername ";
		$sql .= " INNER JOIN msStatus ON stID = wdStID ";
		$sql .= " INNER JOIN ( SELECT * FROM dtPaymentAcc INNER JOIN msPaymentType ON payPTID=ptID ) AS payment ON payment.payMbrUsername=wdMbrUsername ";
		$sql .= " WHERE wdID='".$sCode."' AND wdMbrUsername='".$sConfirmID."' AND wdStID='".$DEF_STATUS_REQUEST."'";

		$query  = $conn->query($sql);
		if ($row = $query->fetch_assoc()){
			if ($row['wdID'] == $sCode && $row['wdMbrUsername'] == $sConfirmID && $row['wdCode'] == $confirmCode){
				//success
				$arrData = array(
					"wdStID"=> $DEF_STATUS_ONPROGRESS
				);
				$arrDataQuery = array(
					"wdID"=> $sCode,
					"wdMbrUsername" => $sConfirmID,
					"wdStID" => $DEF_STATUS_REQUEST
				);
				$table = "dtWDFund";

				if (fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
					//update success
					echo (fSendStatusMessage("success", "Confirmation Successfully"));  die();
				}else{
					echo (fSendStatusMessage("failed", "Update failed"));  die();
				}
			}else{
				//invalid code
				echo (fSendStatusMessage("failed", "Kode Konfirmasi Salah"));  die();
			}
		}else{
			echo (fSendStatusMessage("failed", "Link tidak valid"));  die();
		}

	}//end if $MNav == confirm_wd

}
?>