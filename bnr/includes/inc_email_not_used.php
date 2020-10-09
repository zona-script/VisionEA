<?php

function fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody){	
	//PHPMailer
	include "../phpMailer/classes/class.phpmailer.php";
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
		$mail->SetFrom($GLOBALS['EMAIL_SUPPORT'], "Support VisionEA"); //sender email
	}else if ($emailFrom == "EMAIL_FINANCE"){

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


	/*
	//using MAIL function
	//This function no more used.



	// Multiple recipients
	//$to = 'johny@example.com, sally@example.com'; // note the comma
	$to	= $emailTo;
	$from = $emailFrom;
	
	// Subject
	$subject =  $emailSubject;
	
	// Message
	$message = '
	<html>
	<head>
	  <title>' . $subject . '</title>
	</head>
	<body> ' . $emailBody . 	  
	'</body>
	</html>
	';
	
	// To send HTML mail, the Content-type header must be set
	$headers[] = 'MIME-Version: 1.0';
	$headers[] = 'Content-type: text/html; charset=iso-8859-1';
	
	// Additional headers
	//headers[] = 'To: Mary <mary@example.com>, Kelly <kelly@example.com>';
	global $EMAIL_SUPPORT;
	if ($emailFrom == "") $emailFrom = $EMAIL_SUPPORT;
	$headers[] = 'From: Support <' . $emailFrom . '>';
	//$headers[] = 'Cc: birthdayarchive@example.com';
	//$headers[] = 'Bcc: birthdaycheck@example.com';
	
	// Mail it
	mail($to, $subject, $message, implode("\r\n", $headers));
	*/
	
}


function fSendNotifToEmail($q, $id){	
	global $conn, $EMAIL_SUPPORT, $COMPANY_SITE;
		
	if (strtoupper($q) == "REGISTER_SUCCESS"){
		$sql = "SELECT * FROM dtTempJoin WHERE tjUsername='" . $id . "'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$verifyCode 	= $row['tjVerifyCode'];
				$username 		= $row["tjUsername"];
				if ($verifyCode != ""){
					$verifyLink		= $COMPANY_SITE . 'member/verify/?MNav=act&q='.$username.'&code='.$verifyCode;
					$emailSubject 	= "Verify Your Email Address";
					$emailFrom		= "EMAIL_SUPPORT";
					$emailTo		= $row["tjEmail"];

		  			$emailContent  = '<p>Dear '. $username .',</p>';
		  			$emailContent  .= '<p>Thank You for signing up with VisionEA.net.</p>';
		  			$emailContent  .= '<p>To provide the best service to you, we ask you to verify your email address.<br>';
					$emailContent  .= 'If you received this email and never registered with us, please ignore this email.</p>';
					$emailContent  .= '<p>To complete your verification, please click on the link below or copy and paste it into your browser to continue with your registration: <br>';
					$emailContent  .= '<a href="'. $verifyLink . '">' . $verifyLink .'</a>';
					$emailContent  .= '</p>';

					$emailAdditionNote = '<p class="small alert-danger">*Note : The package must be activated within 24 hours</p>';
					$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
					
					if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
						return (true); 
					}else {
						fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
						return (false); //send to admin, error notification
					}
				}else{
					//$activationCode == ""
					fSendToAdmin($q, 'inc_functions.php', 'VerifyCode empty');
				}			
			}else{
				fSendToAdmin($q, "inc_functions.php", "fetch_assoc error");
			}
		} else {
			fSendToAdmin($q, "inc_functions.php", "query empty record");
			return (false);
		}
	
	
	}elseif (strtoupper($q) == "REQUEST_BUY_VOUCHER"){
		$username 	= $_SESSION['sUserName'];
		$sql = "SELECT mbrUsername, mbrEmail, finAmount, finAccType, finFromAccNo FROM dtMember INNER JOIN dtFundIn ON mbrUsername=finMbrUsername";
		$sql .= " WHERE finID='". $id . "' AND finMbrUsername ='". $username ."'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$emailSubject 	= "Request to Purchase Voucher";
				$emailFrom		= "EMAIL_SUPPORT";
				$emailTo		= $row["mbrEmail"];

				$emailContent  = '<p>Dear '. $username .',</p>';
	  			$emailContent  .= '<p>We have received your request of purchasing voucher.</p>';
	  			$emailContent  .= '<p>We are waiting for you to complete your payment by sending a payment to:</p>';
				$emailContent  .= '========================';
	  			$emailAdditionNote = '<p class="small alert-danger">*Note : If you have not made a payment within 4 hours, your request will be automatically canceled.</p>';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
				
			}
		} else {
			return (false);
		}

	}elseif (strtoupper($q) == "NEW_MEMBER_ACTIVATED"){
		$sql = "SELECT m.mbrUsername AS mbrUsername, m.mbrFirstName AS mbrFirstName, m.mbrSponsor AS mbrSponsor, s.mbrFirstName AS sponsorName, m.mbrEmail AS emailMember, s.mbrEmail AS emailSponsor, pacName FROM dtMember AS m ";
		$sql .= " INNER JOIN dtMember AS s ON m.mbrSponsor = s.mbrUsername";
		$sql .= " INNER JOIN Transaction AS t on t.trUsername = m.mbrUsername";
		$sql .= " INNER JOIN msPackage on pacID = t.trPacID";
		$sql .= " WHERE m.mbrUsername='". $id . "'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				//SEND TO NEW MEMBER
				$emailSubject 	= "Welcome New Member";
				$emailFrom		= "EMAIL_SUPPORT";
				$emailTo		= $row["emailMember"];

				$emailContent  = '<p>Dear '. $row['mbrFirstName'] . ' ('. $row['mbrUsername'] .'),</p>';
	  			$emailContent  .= '<p>Congratulations, you have successfully registered in VisionEA.</p>';
				$emailContent  .= '<p>Please login to complete your account balance and trading account data.</p>';
				$emailContent  .= '<div class="col-md-12">';
				$emailContent  .= '    <div class="row"><button class="btn btn-outline-primary" onclick="location.href='. $COMPANY_SITE . 'member/"><i class="fa fa-expeditedssl"></i>&nbsp; Member Area</button></div>';
				$emailContent  .= '    <div class="row"></div>';
				$emailContent  .= '</div>';
	  			$emailAdditionNote = '<p class="small alert-danger"></p>';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}


				//SEND TO SPONSOR
				$emailSubject 	= "New Member Registration";
				$emailFrom		= "EMAIL_SUPPORT";
				$emailTo		= $row["emailSponsor"];

				$emailContent  = '<p>Dear '. $row['sponsorName'] . ' ('. $row['mbrSponsor'] .'),</p>';
	  			$emailContent  .= '<p>Congratulations, we are pleased to inform you that you have a new member registration</p>';
	  			$emailContent  .= '<p>New member registration details:</p>';
				$emailContent  .= '<div class="row"><div class="col-md-3">Username:</div><div class="col-md-9">&nbsp;&nbsp;'. $row['mbrUsername'] . '</div></div>';
				$emailContent  .= '<div class="row"><div class="col-md-3">Package:</div><div class="col-md-9">&nbsp;&nbsp;'. $row['pacName'] . '</div></div>';
				$emailContent  .= '<div class="row"><div class="col-md-12">We thank you for partnering with VisionEA</div></div>';
				$emailContent  .= '<div class="col-md-12">';
				$emailContent  .= '    <div class="row">&nbsp;</div>';
				$emailContent  .= '    <div class="row"><button class="btn btn-outline-primary" onclick="location.href='. $COMPANY_SITE . 'member/"><i class="fa fa-expeditedssl"></i>&nbsp; Member Area</button></div>';
				$emailContent  .= '    <div class="row">&nbsp;</div>';
				$emailContent  .= '</div>';
	  			$emailAdditionNote = '<p class="small alert-danger"></p>';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
				
			}
		} else {
			return (false);
		}


	}elseif (strtoupper($q) == 'UPDATE_ACCOUNT_SUCCESS'){ //profile.php
		$emailSubject 	= "Update Account successfull";
		$emailFrom		= "EMAIL_SUPPORT";
		$arrDataMember	= fGetInfoMember($id, $GLOBALS['DEF_STATUS_ACTIVE']);
		if (isset($arrDataMember['status'])){
			echo "no record found - fail to send email";
		}else{
			$emailTo	= $arrDataMember["mbrEmail"];
			$emailContent  = "Your Account has been Updated"; //core message here
			$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent); //generate email body with format
			if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
				return (true); 
			}else {
				fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
				return (false); //send to admin, error notification
			}
		}



	}elseif (strtoupper($q) == "REQUEST_SECURITY_PIN"){
		$sql = "SELECT pinID, mbrUsername, pinWord, mbrEmail FROM trPIN INNER JOIN dtMember ON mbrUsername = pinMbrUsername ";
		$sql .= " WHERE pinMbrUsername='" . $id . "' AND pinStID='".$GLOBALS["DEF_STATUS_PENDING"]."'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$pinID 			= $row['pinID'];
				$pinWord 		= $row['pinWord']; //without encrypt, will be encrypted when activated
				$username 		= $row["mbrUsername"];
				if ($pinWord != ""){
					$verifyLink		= $COMPANY_SITE . 'member/verify/?MNav=reqPIN&q='.$username.'&code='.$pinID;
					$emailSubject 	= "Activate Your Security Password";
					$emailFrom		= "EMAIL_SUPPORT";
					$emailTo		= $row["mbrEmail"];

					$emailContent  = '<p>Dear '. $username .',</p>';
		  			$emailContent  .= '<p>You have received this email because we have received your security password activation request.</p>';
					$emailContent  .= '<p>This is your security password : <span style="font-size: xx-large; font-weight: 400; color: #000; background-color: #FFF;">' . $pinWord . '</span> <br>';
					$emailContent  .= 'click the following link to activate your security password.<br>';
					$emailContent  .= '<a href="'. $verifyLink . '">' . $verifyLink .'</a>';
					$emailContent  .= '</p>';

					$emailAdditionNote = '<p class="small alert-danger">*Note : The security password must be activated within 24 hours</p>';
					$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format

					if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
						return (true); 
					}else {
						fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
						return (false); //send to admin, error notification
					}
				}else{
					//$activationCode == ""
				}			
			}
		} else {
			return (false);
		}
	

	



	 /*elseif (strtoupper($q) == "ACTIVATION"){
		global $EMAIL_SUPPORT;  //$GLOBALS['EMAIL_SUPPORT'];
		$emailSubject 	= "New Member Activation";
		$emailFrom		= "EMAIL_SUPPORT";
		$arrDataMember	= fGetInfoMember($id, $GLOBALS['DEF_STATUS_ACTIVE']);
		if (isset($arrDataMember['status'])){
			echo "no record found - fail to send email";
		}else{
			$emailTo	= $arrDataMember["mbrEmail"];

			$emailContent  = '<p>Dear '. $arrDataMember["mbrUsername"] .',</p>';
  			$emailContent  .= '<p>Thank You for signing up with VisionEA.net. To provide the best service to you, we ask you to verify your email address.
If you received this email and never registered with us, please ignore this email.';
			$emailContent  .= 'To complete your verification, please click on the link below or copy and paste it into your browser to continue with your registration: ';
			$emailContent  .= $activationLink;  //??????????????????
			$emailContent  .= '</p>';

			$emailAdditionNote = '<p class="small alert-danger">*Note : The package must be activated within 24 hours</p>';
			$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format

			if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
				return (true); 
			}else {
				fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
				return (false); //send to admin, error notification
			}
		}
	}*/


}

function fGetEmailBody($format, $emailSubject, $emailContent, $emailAdditionNote){
	global $COMPANY_SITE;
	if ($format == 'format_general'){
		$emailHeader 	= '<img src="'.$COMPANY_SITE. 'assets/img/email/headerEmailVisionEA.png" width="100%" />';
		
		$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Best Regards,</div><div class="row">VisionEA Support Team</div></div><p>&nbsp;</p>';
	}


	$emailBody 	= '<!DOCTYPE html>'
				. '<html lang="en">'
				.'<head>'
				. '<title>'.$emailSubject .'</title>'
				.   '<meta charset="utf-8">'
				.   '<meta name="viewport" content="width=device-width, initial-scale=1">'
				.	'<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">'
				.  	'<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">'
				.  	'<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>'
				.  	'<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>'
				.  	'<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>'

				.'</head>'
				.'<body style="font-size:14px">'
				.'<div class="container col-md-6">'
				.	  $emailHeader
					. '<div class="card">'
					. '	<div class="card-header">'
				  	.	'<div class="col-md-12"><h3 class="text-center text-primary">'.$emailSubject.'</h3></div>'
				  	. '	</div>'
				  	. '	<div class="card-body"><div class="col-md-12">'
						  . $emailContent
						  . $emailSign
						  . $emailAdditionNote
				  	. ' </div></div>'
				  	. ' <div class="card-footer">'
				  	. '		<div class="col-md-12">'
					. '   		<a href="'. $DEF_LINK_FB . '"><i class="fa fa-facebook-official fa-2x"></i></a>'
					. '   		<a href="'. $DEF_LINK_IG . '"><i class="fa fa fa-instagram fa-2x"></i></a>'
					. '   	</div>'
					. '   	<div class="col-md-12" style="font-size: x-small;">'
					. '   	</div>'
					.    	'<div class="col-md-12" style="font-size: x-small;">'
					.    		'<b>Risk Notice:</b> Before you start trading, you should really understand the risks involved in the currency market and on margin trading, and consider your level of experience.<br><br>'
					.   	'</div>'
					.    	'<div class="col-md-12" style="font-size: x-small;">'
					.			'You received this e-mail because you registered in <a href="https://visionea.net">visionea.net</a>. Add our e-mail support@visionea.net into your address book, so you can get news from us on time and can check in your inbox.'
					.    	'</div>'
				  	. ' </div>'
				. '</div></body></html>';

		


	return ($emailBody);

}

?>