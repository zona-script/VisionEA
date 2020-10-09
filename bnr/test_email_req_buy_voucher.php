<?php
// die();
include_once("./includes/inc_def.php"); 
include_once("./includes/inc_conn.php");
include_once("./includes/inc_functions.php");

$sql = "SELECT mbrUsername, mbrEmail, finAmount, finAccType, finFromAccNo FROM dtMember INNER JOIN dtFundIn ON mbrUsername=finMbrUsername";
$sql .= " WHERE finID='vea-l8011589963744' AND finVoucherType='". $GLOBALS['DEF_VOUCHER_TYPE_STD'] ."'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	if($row = $result->fetch_assoc()) {
		$username 		= $row['mbrUsername'];
		$emailSubject 	= "Request to Purchase PIN";
		$emailFrom		= "EMAIL_NO_REPLY";
		$emailTo		= $row["mbrEmail"]; 

		$emailContent 	 = '<p>Dear '. $username .',</p>';
		$emailContent 	.= '<p>We have received your request of purchasing PIN.</p>';
		$emailContent	.= '<p>We are waiting for you to complete your payment by sending a payment to:</p>';
		$emailContent 	.= "<div style='display: inline-block; width:25%; vertical-align:top; text-align:justify;'>";
		$emailContent 	.= "	<div style='margin:10px;text-align:center;'>";
		$emailContent	.= '		<img src="./assets/img/email/logo-bca.png" style="width:80%; height:20%;"><br>';
		$emailContent 	.= 			$DEF_BANK_BCA.'<br>'.$DEF_BANK_BCA_ACC.'<br>'.$DEF_BANK_BCA_ACC_NAME;
		$emailContent 	.= '	</div>';
		$emailContent 	.= '</div>';
		$emailContent 	.= "<div style='display: inline-block; width:25%; vertical-align:top; text-align:justify;'>";
		$emailContent 	.= "	<div style='margin:10px;text-align:center;'>";
		$emailContent	.= '		<img src="./assets/img/email/logo-bri.png" style="width:80%; height:20%;"><br>';
		$emailContent 	.= 			$DEF_BANK_BRI.'<br>'.$DEF_BANK_BRI_ACC.'<br>'.$DEF_BANK_BRI_ACC_NAME;
		$emailContent 	.= '	</div>';
		$emailContent 	.= '</div>';
		$emailContent 	.= "<div style='display: inline-block; width:25%; vertical-align:top; text-align:justify;'>";
		$emailContent 	.= "	<div style='margin:10px;text-align:center;'>";
		$emailContent	.= '		<img src="./assets/img/email/logo-btc.png" style="width:80%; height:20%;"><br>';
		$emailContent 	.= ' 		BTC<br>'.$DEF_BTC_ADDR_1;
		$emailContent 	.= '	</div>';
		$emailContent 	.= '</div>';
		$emailContent	.= '<br>';
		//$emailContent  .= '<p>After transfer, please send proof of transfer / screenshot to support (email: ' . $GLOBALS['EMAIL_SUPPORT'] . ')</p>';
			$emailAdditionNote = '<p  style="font-size: 10px; color:#000; background-color: #ff99dd; height: 28px; padding:5px">&nbsp;*Note : If you have not made a payment within 4 hours, your request will be automatically canceled.</p>';
		$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote);
		echo $emailContent;
	}
}

// echo (fSendNotifToEmail("REQUEST_BUY_VOUCHER", "fortune1585036778")); die();
// if (fSendNotifToEmail("REQUEST_BUY_VOUCHER", "fortune1585036778")){
// 	echo "success";
// }else{
// 	echo "failed";
// }
?>