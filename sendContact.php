<?php

//require("../project/includes/inc_def.php");
//require("../project/includes/inc_functions.php");

require("./bnr/includes/inc_def.php");
require("./bnr/includes/inc_functions.php");


$name = (isset($_POST['name']))? $_POST['name'] : "";
$email = (isset($_POST['email']))? $_POST['email'] : "";
$subject = (isset($_POST['subject']))? $_POST['subject'] : "";
$message = (isset($_POST['message']))? $_POST['message'] : "";

if ($name != "" && $email != "" && $subject != "" && $message != ""){
    
	$emailFrom 		= "EMAIL_SUPPORT";
	$emailTo   		= "EMAIL_SUPPORT";
	$emailSubject 	= "CONTACT US :: " . $subject;
	$emailBody		= "<h2>CONTACT US</h2>";
	$emailBody		.= "<p>From : " . $name . "</p>";
	$emailBody		.= "<p>Email : " . $email . "</p>";
	$emailBody		.= "<p>Subject : " . $subject . "</p>";
	$emailBody		.= "<p>Message : " . $message . "</p>";
	$emailBody		.= "<p>__________________________________________________</p>";

	if (fSendEmailContactUs($emailFrom, $emailTo, $emailSubject, $emailBody)) {
		//header ("Location: ".$DOMAIN_URL."?ct=0#get-in-touch");
		echo (fSendStatusMessage("success", "<p class='text-success'>Thank you for contact us. <br>Your inquiry has been sent successfully</p>"));
		die();
	}else {
		//header ("Location: ".$DOMAIN_URL."?ct=1#get-in-touch");
		echo (fSendStatusMessage("error", "<p class='text-danger'>Message/Inquiry failed to send. <br>Please REFRESH and try again</p>"));
		die();
	}
}else{
	echo (fSendStatusMessage("error", "Incomplete data")); 
	die();
}



function fSendEmailContactUs($emailFrom, $emailTo, $emailSubject, $emailBody){	
	//PHPMailer
	include "./bnr/phpMailer/classes/class.phpmailer.php";
	$mail = new PHPMailer; 
	$mail->IsSMTP();
	$mail->SMTPSecure = 'tls'; //'ssl'; //'ssl'; //
	//$mail->Host = "mail.autobotfx.pro"; //"smtp.gmail.com"; 
	$mail->SMTPDebug = 1;
	$mail->Port = 587; //465; //465;  
	$mail->SMTPAuth = true;

	if ($emailFrom == "EMAIL_SUPPORT"){
		$mail->Host 	= $GLOBALS['EMAIL_HOST'];
		$mail->Username = $GLOBALS['EMAIL_SUPPORT'];
		$mail->Password = $GLOBALS['SUPPORT_AUTH']; 
		$mail->SetFrom($GLOBALS['EMAIL_SUPPORT'], "VisionEA Support"); //sender email
	}else if ($emailFrom == "EMAIL_FINANCE"){
		$mail->Host 	= $GLOBALS['EMAIL_HOST'];
		$mail->Username = $GLOBALS['EMAIL_FINANCE'];
		$mail->Password = $GLOBALS['FINANCE_AUTH']; 
		$mail->SetFrom($GLOBALS['EMAIL_FINANCE'], "VisionEA Finance"); //sender email
	}else{
		$mail->Host 	= "smtp.gmail.com";
		$mail->Username = "erikaglobal889@gmail.com"; //user email
		$mail->Password = ""; //banana889888					//password of email 
		$mail->SetFrom("erikaglobal889@gmail.com","From Tester"); 
	}

	if ($emailTo == "EMAIL_SUPPORT"){
		$emailTo = $GLOBALS['EMAIL_SUPPORT'];
		$emailToName = "Support";
	}else{
		$emailToName = "Member of Visionea";
	}

	$mail->Subject = $emailSubject; //subject of email
	$mail->AddAddress($emailTo, $emailToName);  //mail to 
	$mail->MsgHTML($emailBody);
	if($mail->Send()) {
		return (true); //echo "Message has been sent";
	}else {
		return (false); //echo "Failed to sending message";
	}

	
}

?>