<?php
include_once("../includes/inc_def.php");
//include_once("../includes/inc_session.php");
//include_once("../includes/inc_conn.php");
//include_once("../includes/inc_functions.php");
function fGetEmailBody($format, $emailSubject, $emailContent, $emailAdditionNote, $emailFrom = "EMAIL_SUPPORT"){
	global $COMPANY_SITE, $DEF_LINK_FB, $DEF_LINK_IG;

	if ($format == 'format_general'){
		// $emailHeader 	= '<img src="'.$COMPANY_SITE. 'assets/img/email/headerEmailVisionEA.jpg" width="100%" />';
		// untuk localhost biar gambar bisa di tampilkan
		$emailHeader 	= '<img src="visionea.net/bnr/assets/img/email/headerEmailVisionEA.jpg" width="100%" />';
		if ($emailFrom=="EMAIL_SUPPORT"){
			$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Best Regards,</div><div class="row">VisionEA Support Team</div></div><p>&nbsp;</p>';
		}else if ($emailFrom == "EMAIL_FINANCE"){
			$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Best Regards,</div><div class="row">VisionEA Finance Dept.</div></div><p>&nbsp;</p>';
		}else if ($emailFrom == "EMAIL_NO_REPLY"){
			$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Best Regards,</div><div class="row">VisionEA</div></div><p>&nbsp;</p>';
		}	


		$emailBody 	= '<html lang="en">'
		.'<head>'
		. '<title>'.$emailSubject .'</title>'
		.   '<meta charset="utf-8">'
		.   '<meta name="viewport" content="width=device-width, initial-scale=1">'
		.	'<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">'
		.  	'<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">'
		//.  	'<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>'
		//.  	'<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>'
		//.  	'<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>'

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
		  	. ' <div style="card-footer">'
		  	. '		<div class="col-md-12">'
			. '   		<a href="'. $DEF_LINK_FB . '"><i class="fa fa-facebook-official fa-2x"></i></a>'
			. '   		<a href="'. $DEF_LINK_IG . '"><i class="fa fa fa-instagram fa-2x"></i></a>'
			. '   	</div>'
			. '   	<div class="col-md-12" style="font-size: small;">'
			. '   	</div>'
			.    	'<div class="col-md-12" style="font-size: small;">'
			.    		'<b>Risk Notice:</b> Before you start trading, you should really understand the risks involved in the currency market and on margin trading, and consider your level of experience.<br><br>'
			.   	'</div>'
			.   '<div class="col-md-12 small">'
			.	'You are receiving this email because you are listed on <a href="https://visionea.net">VisionEA.net</a> membership. PLEASE DO NOT REPLY TO THIS EMAIL. '
			.	'This is an auto generated mail and replies to this email id are not attended to. For any questions you can contact ' . $GLOBALS['EMAIL_SUPPORT']
			.  '</div>'
		  	. ' </div>'
		. '</div></body></html>';



			//.    	'<div class="col-md-12" style="font-size: x-small;">'
			//.			'You received this e-mail because you registered in <a href="https://visionea.net">visionea.net</a>. Add our e-mail support@visionea.net into your address book, so you can get news from us on time and can check in your inbox.'
			//.    	'</div>'
	
	}else if ($format == 'format_promo'){
		$emailHeader 	= '<img src="'.$COMPANY_SITE. 'assets/img/email/headerEmailVisionEA.jpg" width="100%" />';
		
		$emailSign 	= "<div style='margin:20px'><p>&nbsp;</p>";
		$emailSign .= "<div class='col-md-12'>";
		if ($emailFrom == "EMAIL_NO_REPLY"){
			$emailSign .= "<div class='row'>Best Regards,</div>";
			$emailSign .= "<div class='row'>Client Relations Department</div></div>";
			
		}else{
			$emailSign .= "<div class='row'>Best Regards,</div>";
			$emailSign .= "<div class='row'>Support VisionEA</div></div>";
		}
		$emailSign .= "<p>&nbsp;</p></div>";
		

		$emailBody 	= '<!doctype html><html><head><meta charset="utf-8">';
		$emailBody 	.= '<title>'. $emailSubject. '</title>';
		$emailBody 	.= '<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />';
		$emailBody 	.= '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />';
		//$emailBody 	.= '<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">';
		//$emailBody 	.= '<link rel="stylesheet" href="../assets/css/newBinary.css">';
		//$emailBody 	.= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';
		//$emailBody 	.= '<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>';


		$emailBody 	.= '</head>';
		$emailBody 	.= '<body style="background: #fff; padding:5px; font-family:arial">'
	. '<div  style="margin: 10px;">'
	. '	<div style="">'
	. '		<div style="background: #303030">'
	. '&nbsp;'
	. '			<div style="background: white; margin:10px;">'
					. $emailHeader
					. $emailContent
				  	. $emailSign
				  	. $emailAdditionNote	
		. '	</div>'
		. ' <div style="margin:10px; padding:20px; color:white; font-size:11px">'
		. ' 	<div class="row">'
		. '		<div class="col-md-12 text-left">'
			. '   		<a href="'. $DEF_LINK_FB . '"><i class="fa fa-facebook-official fa-2x"></i></a>'
			. '   		<a href="'. $DEF_LINK_IG . '"><i class="fa fa fa-instagram fa-2x"></i></a>'
			. '   	</div>'
			. '   	<div class="col-md-12" style="font-size: small;">&nbsp;'
			. '   	</div>'
			. '     <div class="col-md-12 text-danger text-left" style="font-size: small;">'
			.    		'<b>Risk Notice:</b> Before you start trading, you should really understand the risks involved in the currency market and on margin trading, and consider your level of experience.<br><br>'
			.   	'</div>'
			. '		<div class="col-md-12 small text-left text-white">'
			. '			You are receiving this email because you are listed on <a href="https://visionea.net">VisionEA.net</a> membership. PLEASE DO NOT REPLY TO THIS EMAIL. '
			. '			This is an auto generated mail and replies to this email id are not attended to. For any questions you can contact support@visionea.net'
			. '		</div>'
	. '         </div>'
	. ' 		</div>'
	. ' 	</div>'
	.'	</div>'
	.'</div>'
	.'</body>'
	.'</html>';

	}
	return ($emailBody);
}

function fSendNotifToEmail($q, $id){
	$emailFrom = "support@autobotfx.pro";
	if (strtoupper($q) == "TEST_EMAIL"){
		$emailSubject 	= "Request to Purchase Voucher";
		$emailFrom		= "EMAIL_NO_REPLY";
		$emailTo		= ""; 

		$emailContent  = '<p>Dear fortune,</p>';
			$emailContent  .= '<p>We have received your request of purchasing voucher.</p>';
			$emailContent  .= '<p>We are waiting for you to complete your payment by sending a payment to:</p>';
			$emailContent  .= '<img src="./assets/img/email/logo-btc.png">'; 
			$emailContent  .= 'BTC ADDRESS : ' . $GLOBALS['DEF_BTC_ADDR_1'];
			$emailContent  .= '<img src="./assets/img/email/logo-bca.png">';
			$emailContent  .= '<img src="./assets/img/email/logo-bni.png">';
			$emailContent  .= '<br>';
			//$emailContent  .= '<p>After transfer, please send proof of transfer / screenshot to support (email: ' . $GLOBALS['EMAIL_SUPPORT'] . ')</p>';
			$emailAdditionNote = '<p  style="font-size: 10px; color:#000; background-color: #ff99dd; height: 28px; padding:5px">&nbsp;*Note : If you have not made a payment within 4 hours, your request will be automatically canceled.</p>';
		$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
		fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody); //send to admin, error notification
	}
}



function fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody){
	
	$to	= "effendi.nugraha94@gmail.com";
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
	

	global $EMAIL_SUPPORT;
	if ($emailFrom == "") $emailFrom = $EMAIL_SUPPORT;
	$headers[] = 'From: Support <' . $emailFrom . '>';

	
	// Mail it
	mail($to, $subject, $message, implode("\r\n", $headers));
	
}


echo "here we go..<br>";
fSendNotifToEmail("TEST_EMAIL", "userNameHere");




?>


<?php
include "classes/class.phpmailer.php";
/*
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->CharSet="UTF-8";
$mail->SMTPSecure = 'ssl'; //'tls';
$mail->Host = 'smtp.gmail.com';
$mail->Port = 465; //587;
$mail->Username = 'erikaglobal889@gmail.com';
$mail->Password = 'banana889';
$mail->SMTPAuth = true;

$mail->From = 'erikaglobal889@gmail.com';
$mail->FromName = 'Erika';
$mail->AddAddress('forex.autobots@gmail.com', "forex Autobot");
//$mail->AddReplyTo('phoenixd110@gmail.com', 'Information');

$mail->IsHTML(true);
$mail->Subject    = "PHPMailer Test Subject via Sendmail, basic";
$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";
$mail->Body    = "Hello";

if(!$mail->Send())
{
  echo "Mailer Error: " . $mail->ErrorInfo;
}
else
{
  echo "Message sent!";
}
*/


$mail = new PHPMailer; 
$mail->IsSMTP();
$mail->SMTPSecure = 'ssl'; //'tls'; //'ssl'; //
$mail->Host = "smtp.hostinger.co.id"; //"smtp.gmail.com"; //host masing2 provider email
$mail->SMTPDebug = 1;
$mail->Port = 465; //587; //465;  
$mail->SMTPAuth = true;
$mail->Username = "support@visionea.net"; // "erikaglobal889@gmail.com"; //user email
$mail->Password = "T3amVisionEA"; //"banana889"; //password email 
$mail->SetFrom("support@autobotfx.pro","support autobotfx"); //set email pengirim
$mail->Subject = "Testing"; //subyek email
$mail->AddAddress("forex.autobots@gmail.com","nama email tujuan");  //tujuan email
$mail->MsgHTML("Testing...");
if($mail->Send()) echo "Message has been sent";
else echo "Failed to sending message";

?>