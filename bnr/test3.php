<?php 
include_once("./includes/inc_def.php");
include_once("./includes/inc_conn.php");
include_once("./includes/inc_functions.php");

$emailSubject = "";
$emailHeader = "";
$emailContent = "";
$emailSign ="";
$emailAdditionNote = "";
$emailFrom = "EMAIL_SUPPORT";
$emailHeader 	= '<img src="../bnr/assets/img/email/headerEmailVisionEA.jpg" width="100%" />';
		if ($emailFrom=="EMAIL_SUPPORT"){
			$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Salam,</div><div class="row">Tim VisionEA</div></div><p>&nbsp;</p>';
		}else if ($emailFrom == "EMAIL_FINANCE"){
			$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Salam,</div><div class="row">Dept. Keuangan VisionEA</div></div><p>&nbsp;</p>';
		}else if ($emailFrom == "EMAIL_NO_REPLY"){
			$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Salam,</div><div class="row">Tim VisionEA</div></div><p>&nbsp;</p>';
		}

$emailContent  = '<p>Dear ,</p>';
	  			$emailContent  .= '<h2>Congratulations</h2>';
	  			$emailContent  .= '<p>We are pleased to inform you that you have a new member registration.</p>';
	  			$emailContent  .= '<p>New member registration details:<br>';
				$emailContent  .= ' &nbsp;&nbsp;&nbsp; Username: <br>';
				$emailContent  .= ' &nbsp;&nbsp;&nbsp; Name:<br>';
				// $emailContent  .= ' &nbsp;&nbsp;&nbsp; Package: ' . $row['pacName'] . '<br>';
				$emailContent  .= '</p>';
				$emailContent  .= '<p>We thank you for partnering with VisionEA</p>';
				$emailContent  .= '<a href="'. $COMPANY_SITE . 'member/" class="button">Member Area</a>';

$emailBody 	= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns:v="urn:schemas-microsoft-com:vml">'
		.'<head>'
		. '<title>'.$emailSubject .'</title>'
		.   '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'
		.	'<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">'
		.  	'<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">'
		//.  	'<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>'
		//.  	'<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>'
		//.  	'<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>'
		. 	'<style type="text/css"> .button {border: none;background: #E91E63;color: #fff;padding: 10px;display: inline-block;margin: 10px 0px;font-family: Helvetica, Arial, sans-serif;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;text-decoration: none;}.button:hover {color: #fff;background: #666;text-decoration: none;}</style>'
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
			.   '<div class="col-md-12 small">'
			.	'You are receiving this email because you are listed on <a href="https://visionea.net">VisionEA.net</a> membership. PLEASE DO NOT REPLY TO THIS EMAIL. '
			.	'This is an auto generated mail and replies to this email id are not attended to. For any questions you can contact ' . $GLOBALS['EMAIL_SUPPORT']
			.  '</div>'
		  	. ' </div>'
		. '</div></body></html>';

		// echo $emailBody;
	if (fSendNotifToEmail("REQUEST_WD", "159453916953704")){
		echo "berhasil";
	}else{
		echo "gagal";
	}
?>