<?php
function fBrowserDetect(){
	if (file_exists("./includes/inc_browser_detector.php")){
		include_once ("./includes/inc_browser_detector.php");
	}else if (file_exists("../includes/inc_browser_detector.php")){
		include_once ("../includes/inc_browser_detector.php");
	}else if (file_exists("../../includes/inc_browser_detector.php")){
		include_once ("../../includes/inc_browser_detector.php");
	}
	$browser = new Browser();
	$hasil = $browser->getBrowser()." ver ".$browser->getVersion()." on ".$browser->getPlatform()." ".$browser->getPlatformVersion();
	return $hasil;
}

function savetoCornEmail ($conn, $q, $username){
	global $DEF_STATUS_NOT_YET_SENT;
	$table = "dtCornEmail";
	$arrData = array(
		0 => array ("db" => "cecat"			, "val" => $q), 
		1 => array ("db" => "ceUsername"	, "val" => $username),
		2 => array ("db" => "ceUniqID"		, "val" => ""),
		3 => array ("db" => "cesendst"		, "val" => $DEF_STATUS_NOT_YET_SENT),
		4 => array ("db" => "cedate"		, "val" => "CURRENT_TIME()")
	);

	if (!fInsert($table, $arrData, $conn)){
		fSendToAdmin("Activate Member", "activateMember.php", "Insert data to dtCornEmail failed");
		echo (fSendStatusMessage("error", "<b>Send email to Sponsor failed - </b>" . mysqli_error($conn)));
		return false;
	}else{
		return true;
	}
	unset($arrData);
}

function fSaveHistoryLogin($conn, $username, $platform){
	global $CURRENT_TIME;
	$arrDataInsert = array(
        0 => array("db" => "hlUsername"     , "val"     => $username),
        1 => array("db" => "hlDate"         , "val"     => $CURRENT_TIME),
        2 => array("db" => "hlPlatform"     , "val"     => $platform)
    );

    $table = 'dtHistoryLogin';
    if (fInsert($table, $arrDataInsert, $conn)) {
    	return true;
    }else{
    	fSendToAdmin("fsaveHistoryLogin", "inc_functions.php", "Insert data to dtHistoryLogin failed");
    	return false;
    }
}

function fCekVerificationID($conn, $username, $inputID){
	$sql  = " SELECT mbrIDN AS noID FROM dtMember";
	$sql .= " WHERE (mbrIDN ='".$inputID."' AND mbrUsername != '".$username."' ) AND mbrStID = '".$GLOBALS['DEF_STATUS_ACTIVE']."' ";
	$sql .= " UNION";
	$sql .= " SELECT vrIDNum AS noID FROM dtVerify";
	$sql .= " WHERE vrIDNum ='".$inputID."' AND vrStatus ='".$GLOBALS['DEF_STATUS_APPROVED']."' ";
	$result = $conn->query($sql);
	if ($result->num_rows > 0){
		if ($row= $result->fetch_assoc()){
			if ($row['noID'] == $inputID){
				return (false);
			}
		}
	}
	return (true);
}

function fCekVerification($conn, $username, $inputFullName=""){
	$sql = "SELECT mbrUsername, mbrFirstName, mbrLastName FROM dtVerify INNER JOIN dtMember ON vrUsername=mbrUsername";
	$sql .= " WHERE mbrStID='". $GLOBALS['DEF_STATUS_ACTIVE'] . "' AND vrStatus='" .$GLOBALS['DEF_STATUS_APPROVED']. "'";
	$sql .= " AND mbrUsername='" . $username . "'";
	$query = $conn->query($sql);
	if ($query->num_rows > 0){ // id found
		if ($row=$query->fetch_assoc()){
			if ($inputFullName != ""){ // untuk verifikasi nama
				if ($row['mbrLastName'] != ""){
					$mbrFullName = $row['mbrFirstName']." ".$row['mbrLastName'];
				}else{
					$mbrFullName = $row['mbrFirstName'];
				}
				$mbrFullName = strtolower($mbrFullName);
				if (strcmp($mbrFullName, strtolower($inputFullName)) === 0){
					return (true);
				}	
			}else{
				return (true);
			}
		}
		
	}
	return (false);
}

function resultJSON($status, $message, $array){
	$arrData = array(
		"status"	=>$status, 
		"message"	=>$message, 
		"data"		=>$array
	);
	$dataJSON = json_encode($arrData);
	return ($dataJSON);
}

function fCurl($url, $arrData){
	$STRarrData = http_build_query ($arrData); //url-ify the data for the POST
	// echo $STRarrData; die();
	// persiapkan curl
	$ch = curl_init(); 

	curl_setopt($ch, CURLOPT_URL, $url); // set url 
	// set user agent    
	// curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return the transfer as a string  
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $STRarrData);

	// $output contains the output string 
	$output = curl_exec($ch);
	if (!curl_errno($ch)){
		$info = curl_getinfo($ch);
		if ($info['http_code'] == 200){
			return $output;
		}else{
			$output =  resultJSON("error", "Directory Error - HTTP Code : ".$info['http_code'], "");
		}
	}else{
		$output =  resultJSON("error", "Domain Error - ".curl_errno($ch), "");
	}
	// tutup curl 
	curl_close($ch);      
	return $output;
}

//General functions
function fPrint ($txt){
	echo ("<p>". $txt . "</p>");

}

function fPrintErr($errMsg){
	echo "<div style='color:red; background-color:yellow'>".$errMsg."</div>";
}

function fPrintImportant($msg){
	echo "<div style='color:Black; background-color:yellow; font-weight: bold;'>".$errMsg."</div>";
}

function numFormat($value, $digit){
	return (number_format($value,$digit,",","."));
}

function fTruncateSentence($sentence, $maxWords){
	if (strlen($sentence) > $maxWords){
		$sentence   = substr( $sentence, 0, $maxWords);
		$pos		= strrpos($sentence , ' ');
		if ($pos > 0)
		$sentence 	= substr( $sentence, 0, $pos ); 
	}
	return 	($sentence);
}

function fValidateInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return ($data);
}

function fValidateSQLFromInput($conn, $data){
	$data = fValidateInput($data);
	return ($conn->real_escape_string($data));	
}

function fSetSessionLogin($username, $firstName, $lastName, $privilege, $siteSide){		
	$_SESSION["sSID"] = session_id();
	if ($siteSide == "EBOOK"){
		$_SESSION["sEBUserName"]  = strtolower($username);
	}else{
		$_SESSION["sUserName"]  = strtolower($username);
	}
	$_SESSION["sFirstName"] = $firstName;
	$_SESSION["sLastName"]  = $lastName;
	$_SESSION["sPrivilege"] = $privilege;
	$_SESSION["sSiteSide"]  = $siteSide;
}

//function fSetCookies($sid, $cUserName){
function fSetCookies($cookieName, $cookieValue, $minute){
	if ($minute == 0)
		$expire 	= strtotime( '+30 days' ); //time() + (86400 * 30);
	else $expire = time() + $minute;

	$path		= "/";
	$domain 	= "";
	$secure 	= false; //https only
	$httponly 	= false; //protocl http only

	//setcookie("sid", $sid, $expire, $path, $domain, $secure, $httponly);
	//setcookie("$cUserName", $cUserName, $expire, $path, $domain, $secure, $httponly);
	setcookie($cookieName, $cookieValue, $expire, $path, $domain, $secure, $httponly);
}


/*
function fSetCookies($cookieName, $cookieValue, $minute){
	if ($minute == 0)
		$minute = 86400 * 30; // 86400 = 1 day
	setcookie($cookieName, $cookieValue, time() + $minute, "/"); 
}
*/

function fDeleteCookies($cookieName){
	setcookie($cookieName, "", time() - 86400);
}


function fSetCookiesLogin($username){
	$minute = 86400 * 7; // 7 days
	$username = strtolower($username);
	if ($username == $_SESSION["sUserName"]){
		fSetCookies("cUserName", $username, $minute);
		fSetCookies("cFirstName", $_SESSION["sFirstName"], $minute);
		fSetCookies("cPrivilege", $_SESSION["sPrivilege"], $minute);
		fSetCookies("cSiteSide", $_SESSION["sSiteSide"], $minute);
	}

	//continue to set and checking on session checking time.
}


function fIsMaintenance(){
	// return true; die(); //enabled maintenance time
    global $CURRENT_TIME;
    global $startMaintenanceTime;
    global $endMaintenanceTime;
    
    $isMaintenance = false;
    if (strtotime($CURRENT_TIME) >= strtotime($startMaintenanceTime) && strtotime($CURRENT_TIME) <= strtotime($endMaintenanceTime)){
    	//echo strtotime($CURRENT_TIME). "<br>" . strtotime($startMaintenanceTime) . "<br>" . strtotime($endMaintenanceTime);
    //$duration = strtotime($endMaintenanceTime) - strtotime($startMaintenanceTime);
    //$disparity = strtotime($CURRENT_TIME) - strtotime($startMaintenanceTime);
    //if ($disparity >= 0 &&  $disparity < $duration){
        $isMaintenance = true;
    }
    //echo "current " . $CURRENT_TIME . "<br>";
    //echo "start " . $startMaintenanceTime . "<br>";
    //echo "end ". $endMaintenanceTime . "<br>";


	//$isMaintenance = true; //force to maintenance mode
    //$isMaintenance = false; //turn off maintenance for testing reason
    
    return ($isMaintenance);
}


function fLogout(){
	//Set empty
	fSetSessionLogin ("", "", "", "","");

	// remove all session variables
	session_unset(); 

	// destroy the session 
	session_destroy(); 	

}


//Database functions
function fCloseConnection($conn){
	$conn->close();	
}


function fSendToAdmin($issue, $onFile, $desc){
	global $conn;
	$issue 	= fValidateSQLFromInput($conn, $issue);
	$onFile = fValidateSQLFromInput($conn, $onFile);
	$desc 	= fValidateSQLFromInput($conn, $desc);
	$sUsername = isset($_SESSION['sUserName'])?$_SESSION['sUserName']:'no-session';
	$arrData = array(
				0 => array ("db" => "logUsername"	, "val" => $sUsername),
				1 => array ("db" => "logIssue"		, "val" => $issue),
				2 => array ("db" => "logOnFile"		, "val" => $onFile),
				3 => array ("db" => "logDesc"		, "val" => $desc),
				4 => array ("db" => "logDate"		, "val" => "CURRENT_TIME()")
			);
	return (fInsert("dtLog", $arrData, $conn));

}

function flogMyMac ($username, $type, $onFile, $desc, $status){
	global $conn;
	$type 	= fValidateSQLFromInput($conn, $type);
	$onFile = fValidateSQLFromInput($conn, $onFile);
	$desc 	= fValidateSQLFromInput($conn, $desc);
	$arrData = array(
		0 => array ("db" => "logmUsername"	, "val" => $username),
		1 => array ("db" => "logmType"		, "val" => $type),
		2 => array ("db" => "logmOnfile"	, "val" => $onFile),
		3 => array ("db" => "logmDesc"		, "val" => $desc),
		4 => array ("db" => "logmDate"		, "val" => "CURRENT_TIME()"),
		5 => array ("db" => "logmStatus"	, "val" => $status)
	);

	return (fInsert("dtLogMymac", $arrData, $conn));
}


function fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody){	
	//PHPMailer
	if (file_exists("./phpMailer/classes/class.phpmailer.php")){
		include_once ("./phpMailer/classes/class.phpmailer.php");
	}else if (file_exists("../phpMailer/classes/class.phpmailer.php")){
		include_once ("../phpMailer/classes/class.phpmailer.php");
	}else if (file_exists("../../phpMailer/classes/class.phpmailer.php")){
		include_once ("../../phpMailer/classes/class.phpmailer.php");
	}
	$mail = new PHPMailer;
	//echo ("dalam 2: " . $emailTo);
    //return (true); die();
	$mail->IsSMTP();
	$mail->SMTPSecure = 'STARTTLS';//'tls'; //'ssl'; //'ssl'; //
	//$mail->Host = "mail.autobotfx.pro"; //"smtp.gmail.com"; 
	//$mail->SMTPDebug = 2;
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
	}else if ($emailFrom == "EMAIL_NO_REPLY"){
		$mail->Host 	= $GLOBALS['EMAIL_HOST'];
		$mail->Username = $GLOBALS['EMAIL_NO_REPLY'];
		$mail->Password = $GLOBALS['NO_REPLY_AUTH']; 
		$mail->SetFrom($GLOBALS['EMAIL_NO_REPLY'], "VisionEA"); //sender email
	}else if ($emailFrom == "EMAIL_TEST"){
	    $mail->Host 	= $GLOBALS['EMAIL_HOST'];
		$mail->Username = "test@visionea.net";
		$mail->Password = "test123"; 
		$mail->SetFrom("test@visionea.net", "VisionEA Test"); //sender email
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
		$emailToName = "Member of VisionEA";
	}

	$emailTo = "effendi94.autobot@gmail.com";
	$emailToName = "Effendi Testing";
    
	$mail->Subject = "Testing -".$emailSubject; //subject of email
	$mail->AddAddress($emailTo, $emailToName);  //mail to 
	$mail->MsgHTML($emailBody);
	//echo $GLOBALS['EMAIL_HOST']." || ".$mail->Host." || ".$mail->Username." || ".$mail->Password ; return (true); die();
	if($mail->Send()) {
		// Clear all addresses and attachments for next loop
	    $mail->ClearAddresses();
	    $mail->ClearAttachments();
		return (true); //echo "Message has been sent";
	}else {
		// Clear all addresses and attachments for next loop
	    $mail->ClearAddresses();
	    $mail->ClearAttachments();
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
					$emailSubject 	= "Aktivasi Registrasi VisionEA";
					$emailFrom		= "EMAIL_NO_REPLY";
					$emailTo		= $row["tjEmail"];

		  			$emailContent  = '<p>Salam Hangat,</p>';
		  			$emailContent  .= '<p>Terima kasih telah melakukan registrasi di VisionEA.</p>';
		  			$emailContent  .= '<p><b>Dari sini, langkah sukses anda dimulai !</b><br>';
					$emailContent  .= '<p>Untuk menyelesaikan proses registrasi anda , silahkan klik tautan dibawah ini :<br>';
					$emailContent  .= '<a href="'. $verifyLink . '">' . $verifyLink .'</a>';
					$emailContent  .= '</p>';
					$emailContent  .= '<p>Jika butuh bantuan, silakan hubungi kami kapan saja di support-id@visionea.net.</p>';

					$emailAdditionNote = '<p  style="font-size: 10px; color:#000; background-color: #ff99dd; height: 28px; padding:5px">&nbsp;*Note : Tautan diatas akan kadaluwarsa 24jam setelah email ini dikirimkan.</p>';
					$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
					
					if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
						return (true); 
					}else {
						fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . ' Email Content: '.$emailBody);
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
	
	}elseif (strtoupper($q) == "VERIFY_ID_APPROVED"){
		$sql  = "SELECT * FROM dtMember";
		$sql .= " INNER JOIN dtVerify ON mbrUsername = vrUsername";
		$sql .= " INNER JOIN msIDType ON idtCode = vrType";
		$sql .= " WHERE mbrUsername = '$id' ORDER BY vrUpdateDate DESC LIMIT 1";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$emailSubject 	= "Verifikasi ID Berhasil";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["mbrEmail"]; 

				$emailContent  = '<p>Dear '. $row['mbrUsername'] .',</p>';
				$emailContent .= '<p><table>';
				$emailContent .= '<tr><td>Nomor ID</td><td> : </td><td> '.$row['vrIDNum'].'</td></tr>';
				$emailContent .= '<tr><td>Tipe ID</td><td> : </td><td> '.$row['idtType'].'</td></tr>';
				$emailContent .= '</table></p>';
				$emailContent .= '<p>ID anda telah berhasil diverifikasi.</p>';

				$emailAdditionNote = "";

				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $row['mbrUsername'] . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		}

	}elseif (strtoupper($q) == "VERIFY_ID_DECLINED"){
		$sql  = "SELECT mbrEmail, mbrUsername, vrIDNum, idtType, dcMsg FROM dtMember";
		$sql .= " INNER JOIN dtVerify ON mbrUsername = vrUsername";
		$sql .= " INNER JOIN msIDType ON idtCode = vrType";
		$sql .= " INNER JOIN dtDecline ON dcTransid = mbrUsername";
		$sql .= " WHERE mbrUsername = '$id'";
		$sql .= " ORDER BY dcDate DESC LIMIT 1";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$emailSubject 	= "Verifikasi ID Ditolak";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["mbrEmail"]; 

				$emailContent  = '<p>Dear '. $row['mbrUsername'] .',</p>';
				$emailContent .= '<p>Verifikasi ID belum berhasil dikarenakan tidak memenuhi kriteria.</p>';
				$emailContent .= '<p><table>';
				$emailContent .= '<tr><td>Nomor ID</td><td> : </td><td> '.$row['vrIDNum'].'</td></tr>';
				$emailContent .= '<tr><td>Tipe ID</td><td> : </td><td> '.$row['idtType'].'</td></tr>';
				$emailContent .= '<tr><td valign="top">Perihal</td><td> : </td><td> '.$row['dcMsg'].'</td></tr>';
				$emailContent .= '</table></p>';
				$emailContent  .= "<p>Silahkan unggah kembali ID Anda dengan memenuhi kriteria sebagai berikut :";
	  			$emailContent  .= "<ol>";
	  			$emailContent  .= "<li>Kartu ID valid (KTP/SIM/PASPOR)</li>";
	  			$emailContent  .= "<li>Kartu ID dapat dibaca dan dilihat dengan jelas.</li>";
	  			$emailContent  .= "</ol></p>";
				$emailAdditionNote = "";

				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $row['mbrUsername'] . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		}
	
	}elseif (strtoupper($q) == "REQUEST_BUY_VOUCHER"){
		// $username 	= $_SESSION['sUserName'];
		$sql = "SELECT mbrUsername, mbrEmail, finAmount, finAccType, finFromAccNo FROM dtMember INNER JOIN dtFundIn ON mbrUsername=finMbrUsername";
		$sql .= " WHERE finID='". $id . "' AND finVoucherType='". $GLOBALS['DEF_VOUCHER_TYPE_STD'] ."'";
		// return $sql; die();
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$username 		= $row['mbrUsername'];
				$emailSubject 	= "Permintaan Pembelian PIN";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["mbrEmail"]; 

				$emailContent 	 = '<p>Dear '. $username .',</p>';
	  			$emailContent 	.= '<p>Kami telah menerima permintaan pembelian PIN Anda.</p>';
	  			$emailContent	.= '<p>Untuk melanjutkan proses pembelian silahkan melakukan pembayaran ke :</p>';
				$emailContent 	.= '<p>BANK : '.$GLOBALS['DEF_BANK_BCA'].'<br>Nomor Rekening : '.$GLOBALS['DEF_BANK_BCA_ACC'].'<br>Atas Nama : '.$GLOBALS['DEF_BANK_BCA_ACC_NAME'].'</p>';

				$emailContent	.= '<br>';
				//$emailContent  .= '<p>After transfer, please send proof of transfer / screenshot to support (email: ' . $GLOBALS['EMAIL_SUPPORT'] . ')</p>';
	  			$emailAdditionNote = '<p  style="font-size: 10px; color:#000; background-color: #ff99dd; height: 28px; padding:5px">&nbsp;*Note : Jika Anda belum melakukan pembayaran dalam waktu 4 (empat) jam, maka permintaan Anda akan dibatalkan secara otomatis.</p>';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
				
			}
		}else {
			return (false);
		}

	}elseif (strtoupper($q) == "REQUEST_BUY_VOUCHER_VPS"){
		// $username 	= $_SESSION['sUserName'];
		$sql = "SELECT mbrUsername, mbrEmail, finAmount, finAccType, finFromAccNo FROM dtMember INNER JOIN dtFundIn ON mbrUsername=finMbrUsername";
		$sql .= " WHERE finID='". $id . "' AND finVoucherType='". $GLOBALS['DEF_VOUCHER_TYPE_VPS'] ."'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$username 		= $row['mbrUsername'];
				$emailSubject 	= "Request to Purchase Voucher VPS";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["mbrEmail"]; 

				$emailContent  = '<p>Dear '. $username .',</p>';
	  			$emailContent  .= '<p>We have received your request of purchasing voucher VPS.</p>';
	  			$emailContent  .= '<p>We are waiting for you to complete your payment by sending a payment to:</p>';
				$emailContent  .= 'BTC ADDRESS : ' . $GLOBALS['DEF_BTC_ADDR_1'];
				$emailContent  .= '<br>';
				//$emailContent  .= '<p>After transfer, please send proof of transfer / screenshot to support (email: ' . $GLOBALS['EMAIL_SUPPORT'] . ')</p>';
	  			$emailAdditionNote = '<p  style="font-size: 10px; color:#000; background-color: #ff99dd; height: 28px; padding:5px">&nbsp;*Note : If you have not made a payment within 4 hours, your request will be automatically canceled.</p>';
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
	}elseif (strtoupper($q) == "BUY_VOUCHER_APPROVED"){
		$sql = "SELECT mbrUsername, mbrEmail, finAmount, finAccType, finFromAccNo FROM dtMember INNER JOIN dtFundIn ON mbrUsername=finMbrUsername";
		$sql .= " WHERE finID='". $id . "' AND finVoucherType='". $GLOBALS['DEF_VOUCHER_TYPE_STD'] ."' AND finStatus = '".$GLOBALS['DEF_STATUS_APPROVED']."'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$username 		= $row['mbrUsername'];
				$emailSubject 	= "Pembelian PIN Berhasil";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["mbrEmail"]; 

				$emailContent	 = '<p>Dear '. $username .',</p>';
	  			$emailContent	.= '<p>Pembelian PIN Anda telah berhasil.</p>';
	  			$emailContent	.= '<p>Silahkan login ke member untuk informasi lebih detail.</p>';
				$emailContent	.= '<a href="'. $COMPANY_SITE . 'member/" class="button" style="color:#FFFFFF;">Member Area</a>';
				$emailAdditionNote = "";
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		}else {
			fSendToAdmin($q, 'inc_functions.php', "query no record");
			return (false);
		}

	}elseif (strtoupper($q) == "BUY_VOUCHER_DECLINED"){
		$sql  = "SELECT mbrUsername, mbrEmail, finAmount, finAccType, finFromAccNo, dcMsg ";
		$sql .= " FROM dtMember ";
		$sql .= " INNER JOIN dtFundIn ON mbrUsername=finMbrUsername";
		$sql .= " INNER JOIN (SELECT dcTransID, dcmsg FROM dtDecline ORDER BY dcDate DESC LIMIT 1) AS dc ON dcTransID = finID";
		$sql .= " WHERE finID='". $id . "' AND finVoucherType='". $GLOBALS['DEF_VOUCHER_TYPE_STD'] ."' AND finStatus = '".$GLOBALS['DEF_STATUS_DECLINED']."'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$username 		= $row['mbrUsername'];
				$emailSubject 	= "Pembelian PIN Ditolak";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["mbrEmail"]; 
				$reason 		= $row["dcMsg"];

				$emailContent 	 = '<p>Dear '. $username .',</p>';
	  			$emailContent 	.= '<p>Pembelian PIN Anda ditolak.</p>';
	  			$emailContent 	.= '<p>Adapun alasan penolakan adalah : <span style="color:red; font-weight: bold;">'.$reason.'</span></p>';
	  			$emailContent	.= '<p>Silahkan login ke <b>Member Area</b> untuk mengunggah kembali bukti pembayaran Anda.</p>';
				$emailContent   .= '<a href="'. $COMPANY_SITE . 'member/" class="button" style="color:#FFFFFF;">Member Area</a>';
				$emailAdditionNote = "";
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		}else {
			fSendToAdmin($q, 'inc_functions.php', "query no record");
			return (false);
		}

	}elseif (strtoupper($q) == "NEW_MEMBER_ACTIVATED"){
		$sql = "SELECT m.mbrUsername AS mbrUsername, m.mbrFirstName AS mbrFirstName, m.mbrSponsor AS mbrSponsor, s.mbrFirstName AS sponsorName, m.mbrEmail AS emailMember, s.mbrEmail AS emailSponsor, pacName FROM dtMember AS m ";
		$sql .= " INNER JOIN dtMember AS s ON m.mbrSponsor = s.mbrUsername";
		$sql .= " INNER JOIN Transaction AS t on t.trUsername = m.mbrUsername";
		$sql .= " INNER JOIN msPackage on pacID = t.trPacID";
		$sql .= " WHERE m.mbrUsername='". $id . "' ORDER BY t.trDate DESC LIMIT 1";
		//echo $sql; die();
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				//SEND TO NEW MEMBER
				$emailSubject 	= "Welcome to VisionEA";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["emailMember"];

				$emailContent   = '<p>Selamat Datang '. $row['mbrFirstName'] . ' ('. $row['mbrUsername'] .'),</p>';
	  			$emailContent  .= '<p>Terima kasih atas kepercayaan anda memilih VisionEA.</p>';
				$emailContent  .= '<p>Kami memberikan peluang besar bagi Anda untuk meraih sukses dengan pendapatan yang menarik melalui penjualan e-book pembuatan aplikasi perdagangan otomatis yang dipasarkan melalui sistem penjualan langsung.</p>';
				$emailContent  .= '<p><b>Dari sini, Langkah Sukses anda dimulai !</b></p>';
				$emailContent  .= 'Itulah tagline Perusahaan VisionEA, dan kami memiliki visi menjadi perusahaan pemimpin dunia dalam Industri Penjualan Langsung dan menjadi nomor satu di industri Platform Edukasi Keuangan sekaligus memiliki misi untuk bisa menciptakan jutaan peluang dan menciptakan sumber penghasilan yang berkelanjutan dengan fokus pada perubahan jaman.';
				$emailContent  .= 'Sekali lagi, Selamat datang buat Anda.';
	  			$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					//return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					//return (false); //send to admin, error notification
				}
				
			}
		} else {
			fSendToAdmin($q, 'inc_functions.php', $sql);
			return (false);
		}

	}elseif (strtoupper($q) == "ACTIVATION_MBR_TO_SP"){
		//sent by sendCornEmail.php
		//sent to email sponsor, where id is member's username
		$username = "";
		$sql = "SELECT m.mbrUsername AS mbrUsername, m.mbrFirstName AS mbrFirstName, m.mbrSponsor AS mbrSponsor, s.mbrFirstName AS sponsorName, m.mbrEmail AS emailMember, s.mbrEmail AS emailSponsor, pacName FROM dtMember AS m ";
		$sql .= " INNER JOIN dtMember AS s ON m.mbrSponsor = s.mbrUsername";
		$sql .= " INNER JOIN Transaction AS t ON t.trUsername = m.mbrUsername";
		$sql .= " INNER JOIN msPackage ON pacID = t.trPacID";
		$sql .= " INNER JOIN dtCornEmail ON ceUsername = m.mbrUsername";
		$sql .= " WHERE ceid='". $id . "' ORDER BY t.trDate DESC LIMIT 1";
		//echo $sql; die();
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$username = $row['mbrUsername'];
				//SEND TO SPONSOR
				$emailSubject 	= "Registrasi Member Baru - " . $row['mbrUsername'];
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["emailSponsor"];

				$emailContent   = '<p>Dear '. $row['sponsorName'] . ' ('. $row['mbrSponsor'] .'),</p>';
	  			$emailContent  .= '<h2>Selamat !</h2>';
	  			$emailContent  .= '<p>Dengan senang hati kami menginformasikan bahwa Anda memiliki anggota baru.</p>';
	  			$emailContent  .= '<p>Berikut detail anggota baru yang terdaftar :<br>';
				$emailContent  .= ' &nbsp;&nbsp;&nbsp; Username: ' . $row['mbrUsername'] . '<br>';
				$emailContent  .= ' &nbsp;&nbsp;&nbsp; Name: ' . $row['mbrFirstName'] . '<br>';
				// $emailContent  .= ' &nbsp;&nbsp;&nbsp; Package: ' . $row['pacName'] . '<br>';
				$emailContent  .= '</p>';
				$emailContent  .= '<p>Kami mengucapkan terima kasih telah memilih VisionEA sebagai partner Anda.</p>';
				$emailContent  .= '<a href="'. $COMPANY_SITE . 'member/" class="button" style="color:#FFFFFF;">Member Area</a>';
	  			$emailAdditionNote = '';
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

	}elseif (strtoupper($q) == "MEMBER_UPGRADE_PACKAGE"){

		$sql = "SELECT m.mbrUsername AS mbrUsername, m.mbrFirstName AS mbrFirstName, m.mbrSponsor AS mbrSponsor, s.mbrFirstName AS sponsorName, m.mbrEmail AS emailMember, s.mbrEmail AS emailSponsor, pacName FROM dtMember AS m ";
		$sql .= " INNER JOIN dtMember AS s ON m.mbrSponsor = s.mbrUsername";
		$sql .= " INNER JOIN Transaction AS t on t.trUsername = m.mbrUsername";
		$sql .= " INNER JOIN msPackage on pacID = t.trPacID";
		$sql .= " WHERE m.mbrUsername='". $id . "' ORDER BY t.trDate DESC LIMIT 1";
		//echo $sql; die();
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				//SEND TO MEMBER
				$emailSubject 	= "Upgrade Package";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["emailMember"];

				$emailContent  = '<p>Dear '. $row['mbrFirstName'] . ' ('. $row['mbrUsername'] .'),</p>';
	  			$emailContent  .= '<p>Congratulations, Your package has been upgraded to '. $row['pacName'] .'.</p>';
				$emailContent  .= '<p></p>';
				$emailContent  .= '<div class="col-md-12">';
				$emailContent  .= '    <div class="row"><button class="btn btn-outline-primary" onclick="location.href='. $COMPANY_SITE . 'member/"><i class="fa fa-expeditedssl"></i>&nbsp; Member Area</button></div>';
				$emailContent  .= '    <div class="row"></div>';
				$emailContent  .= '</div>';
	  			$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					//return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					//return (false); //send to admin, error notification
				}


				//SEND TO SPONSOR
				$emailSubject 	= "Member - Upgrade Package";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["emailSponsor"];

				$emailContent  = '<p>Dear '. $row['sponsorName'] . ' ('. $row['mbrSponsor'] .'),</p>';
	  			$emailContent  .= '<p>Congratulations, we are pleased to inform you that your member has just upgraded the package</p>';
	  			$emailContent  .= '<p>Member\'s details:</p>';
				$emailContent  .= '<div class="row"><div class="col-md-3">Username:</div><div class="col-md-9">&nbsp;&nbsp;'. $row['mbrUsername'] . '</div></div>';
				$emailContent  .= '<div class="row"><div class="col-md-3">Package:</div><div class="col-md-9">&nbsp;&nbsp;'. $row['pacName'] . '</div></div>';
				$emailContent  .= '<div class="row"><div class="col-md-12">We thank you for partnering with VisionEA</div></div>';
				$emailContent  .= '<div class="col-md-12">';
				$emailContent  .= '    <div class="row">&nbsp;</div>';
				$emailContent  .= '    <div class="row"><button class="btn btn-outline-primary" onclick="location.href='. $COMPANY_SITE . 'member/"><i class="fa fa-expeditedssl"></i>&nbsp; Member Area</button></div>';
				$emailContent  .= '    <div class="row">&nbsp;</div>';
				$emailContent  .= '</div>';
	  			$emailAdditionNote = '';
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

	}elseif (strtoupper($q) == "MEMBER_RENEW_PACKAGE"){

		$sql = "SELECT m.mbrUsername AS mbrUsername, m.mbrFirstName AS mbrFirstName, m.mbrSponsor AS mbrSponsor, s.mbrFirstName AS sponsorName, m.mbrEmail AS emailMember, s.mbrEmail AS emailSponsor, pacName FROM dtMember AS m ";
		$sql .= " INNER JOIN dtMember AS s ON m.mbrSponsor = s.mbrUsername";
		$sql .= " INNER JOIN Transaction AS t on t.trUsername = m.mbrUsername";
		$sql .= " INNER JOIN msPackage on pacID = t.trPacID";
		$sql .= " WHERE m.mbrUsername='". $id . "' ORDER BY t.trDate DESC LIMIT 1";
		//echo $sql; die();
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				//SEND TO MEMBER
				$emailSubject 	= "Perpanjang Keanggotaan Berhasil";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["emailMember"];

				$emailContent  = '<p>Dear '. $row['mbrFirstName'] . ' ('. $row['mbrUsername'] .'),</p>';
	  			$emailContent  .= '<p>Selamat, keanggotaan Anda berhasil diperpanjang.</p>';
				$emailContent  .= '<p>Untuk informasi detail mengenai keanggotaan Anda silahkan login ke <b>Member Area</b></p>';
				$emailContent  .= '<a href="'. $COMPANY_SITE . 'member/" class="button" style="color:#FFFFFF;">Member Area</a>';
	  			$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					//return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					//return (false); //send to admin, error notification
				}
				
			}
		} else {
			return (false);
		}

	}elseif (strtoupper($q) == 'UPDATE_PAYMENT_ACCOUNT'){ //profile.php
		$arrDataMember	= fGetInfoMember($id, $GLOBALS['DEF_STATUS_ACTIVE']);
		if (isset($arrDataMember['status'])){
			echo "no record found - fail to send email";
		}else{
			$emailSubject 	= "Memperbarui Akun Pembayaran";
			$emailFrom		= "EMAIL_NO_REPLY";
			$emailTo		= $arrDataMember["mbrEmail"];

			$sql = "SELECT pay.*, pt.ptCat, pt.ptDesc FROM dtPaymentAcc pay INNER JOIN msPaymentType pt on payPTID=ptID";
			$sql .= " WHERE payMbrUsername='" . $id . "' AND payStatus='" . $GLOBALS['DEF_STATUS_ACTIVE'] . "'";
			$query = $conn->query($sql);
			if ($row = $query->fetch_assoc()){
				$emailContent  = '<p>Dear '. $id .',</p>';
	  			$emailContent  .= '<p>Akun pembayaran Anda berhasil diperbarui.</p>';
	  			$emailContent  .= '<p><b>Data akun pembayaran :</b></p>';
	  			$emailContent  .= '<table border="0">';
				$emailContent  .= '<tr><td width="150px">Tipe Akun</td><td width="250px">: '.$row['ptDesc'].'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Nomor Akun</td><td width="250px">: '.$row['payAcc'].'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Nama Akun</td><td width="250px">: '.$row['payAccName'].'</td></tr>';
				if ($row['payCode'] != "") { 
					$emailContent  .= '<tr><td width="150px">SWIFT/BIC Code</td><td width="250px">: '.$row['payCode'].'</td></tr>';
				} 
				
				$emailContent  .= '</table>';
				$emailContent  .= '<p>If you do not change your account data, immediately contact support.</p>';
	  			//$emailContent  .= '<div class="row"><div class="col-md-3">Username:</div><div class="col-md-9">'.$username.'</div></div>';
	  			//$emailContent  .= '<div class="row"><div class="col-md-3">Time:</div><div class="col-md-9">'.$passDate.'</div></div>';
	  			//$emailContent  .= '<div class="row"><div class="col-md-12">If you do not change the security password, immediately contact support.</div></div>';
	  			
				$emailAdditionNote = '';
			}
			
			$emailBody		= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //generate email body with format
			if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
				return (true); 
			}else {
				fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
				return (false); //send to admin, error notification
			}
		}

	}elseif (strtoupper($q) == 'UPDATE_ACCOUNT_TRADING'){ //accTrading.php
		$arrDataMember	= fGetInfoMember($id, $GLOBALS['DEF_STATUS_ACTIVE']);
		if (isset($arrDataMember['status'])){
			echo "no record found - fail to send email";
		}else{
			$emailSubject 	= "Update Trading Account";
			$emailFrom		= "EMAIL_NO_REPLY";
			$emailTo		= $arrDataMember["mbrEmail"];

			$sql = "SELECT tradeAccOrder, tradeUsername, tradeAccNo, tradeName, tradeServer, EAName, pairName FROM dtTradingAcc ";
			$sql .= " INNER JOIN msEA ON EAID=tradeEANum";
			$sql .= " INNER JOIN msPair ON pairID = tradePair";
			$sql .= " WHERE tradeUsername='" . $id . "' ORDER BY DATE(tradeDate) DESC LIMIT 1";
			$query = $conn->query($sql);
			if ($row = $query->fetch_assoc()){
				$emailContent  = '<p>Dear '. $id .',</p>';
	  			$emailContent  .= '<p>Your Trading Account has been Updated.</p>';
	  			$emailContent  .= '<p><b>Your Trading Account Data :</b></p>';
	  			$emailContent  .= '<table border="0">';
	  			$emailContent  .= '<tr><td width="150px">Order of Account</td><td width="250px">: #'.$row['tradeAccOrder'].'</td></tr>';
	  			$emailContent  .= '<tr><td width="150px">Expert Advisor / Pair</td><td width="250px">: '.$row['EAName']. ' / ' .$row['pairName'].'</td></tr>';

				$emailContent  .= '<tr><td width="150px">Account Number</td><td width="250px">: '.$row['tradeAccNo'].'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Account Name</td><td width="250px">: '.$row['tradeName'].'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Server</td><td width="250px">: '.$row['tradeServer'].'</td></tr>';
				$emailContent  .= '</table>';
				$emailContent  .= '<p>By updating your trading account data, you want us to immediately activate the VisionEA robot with this trading account number. <br>And you also realize and accept all the risks that can arise due to trading using this VisionEA robot. <br>Any risk incurred is entirely your own responsibility.</p>';
				$emailContent  .= '<p>Please read the terms and conditions for more details or you can contact support for help.</p>';
				$emailContent  .= '<p>&nbsp;</p><p>If you do not change your trading account data, immediately contact support.</p>';
	  			//$emailContent  .= '<div class="row"><div class="col-md-3">Username:</div><div class="col-md-9">'.$username.'</div></div>';
	  			//$emailContent  .= '<div class="row"><div class="col-md-3">Time:</div><div class="col-md-9">'.$passDate.'</div></div>';
	  			//$emailContent  .= '<div class="row"><div class="col-md-12">If you do not change the security password, immediately contact support.</div></div>';
	  			
				$emailAdditionNote = '';
			}
			
			$emailBody		= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //generate email body with format
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
					$emailSubject 	= "Aktivasi Security Password";
					$emailFrom		= "EMAIL_NO_REPLY";
					$emailTo		= $row["mbrEmail"];

					$emailContent  = '<p>Dear '. $username .',</p>';
		  			$emailContent  .= '<p>Kami telah menerima permintaan <b>Aktivasi Security Password</b> Anda.</p>';
					$emailContent  .= '<p>Security password Anda adalah : <span style="font-size: xx-large; font-weight: 400; color: #000; background-color: #FFF;">' . $pinWord . '</span> </p>';
					$emailContent  .= '<p>Untuk melanjutkan proses aktivasi silahkan klik tombol berikut.</p>';
					$emailContent  .= '<a href="'.$verifyLink.'" class="button" style="color:#FFFFFF;" alt="Aktivasi">Aktivasi</a>';
					$emailContent  .= '<p>atau, klik link berikut : <a href="'.$verifyLink.'">'.$verifyLink.'</a></p>';

					$emailAdditionNote = '<p  style="font-size: 10px; color:#000; background-color: #ff99dd; height: 28px; padding:5px">&nbsp;*Note : Security Password harus diaktivasi dalam waktu 24 jam</p>';
					
					//there are some email format
					$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); 

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

	}elseif (strtoupper($q) == "RESET_PASSWORD"){ //resetPassword.php (forgetpassword.php->resetPassword.php)
		$sql 	= "SELECT mbrUsername, mbrEmail, rrID, rrNote FROM dtMember ";
		$sql 	.= " INNER JOIN dtReqReset ON mbrUsername = rrUsername";
		$sql 	.= " WHERE mbrUsername='".$id."' AND rrCategory='".$GLOBALS['DEF_CATEGORY_RESET_PASSWORD']."'";
		$sql 	.= " AND mbrStID = '".$GLOBALS['DEF_STATUS_ACTIVE']."' AND rrStID='".$GLOBALS['DEF_STATUS_REQUEST']."'";
		$sql 	.= " ORDER BY DATE(rrDate) DESC LIMIT 1";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$username 		= $row['mbrUsername'];
				$emailSubject 	= "Reset Password";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["mbrEmail"];
				$randPasswd 	= $row["rrNote"];
				$resetPasswdLink = $GLOBALS["COMPANY_SITE"].'member/verify/?MNav=resetPW&q='.$username.'&code='.$row["rrID"];

	  			$emailContent  = '<p>Dear '. $username .',</p>';
	  			$emailContent  .= '<p>Anda telah mengirim permintaan untuk mereset <b>Password</b> Anda.</p>';
	  			$emailContent  .= '<p>Password Anda :<span style="font-size: xx-large; font-weight: 400; color: #000; background-color: #FFF;">&nbsp;&nbsp;' . $randPasswd . '</span></p>';
	  			$emailContent  .= '<p>Untuk melanjutkan proses reset password silahkan klik tombol berikut.</p>';
	  			$emailContent  .= '<a href="'.$resetPasswdLink.'" class="button" style="color:#FFFFFF;">Konfirmasi Reset Password</a>';
				
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some 
				//echo $emailBody; die();
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
					
			}else{
				fSendToAdmin($q, "inc_functions.php", "fetch_assoc error");
			}
		} else {
			fSendToAdmin($q, "inc_functions.php", "query empty record");
			return (false);
		}	
	
	}elseif (strtoupper($q) == "CHANGE_PASSWORD"){ //changePassword.php
		$sql 	= "SELECT mbrUsername, mbrEmail, passDate FROM dtMember inner join trPassword";
		$sql	.= " ON mbrUsername = passMbrUsername";
		$sql	.= " WHERE mbrUsername='" . $id . "' ORDER BY passDate DESC LIMIT 1";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$username 		= $row['mbrUsername'];
				$passDate 		= $row["passDate"];
				
				$emailSubject 	= "Password VisionEA Anda sudah berubah !";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["mbrEmail"];

	  			$emailContent  = '<p>Dear '. $username .',</p>';
	  			$emailContent  .= '<p>Melalui email ini , kami ingin mengkonfirmasi bahwa password akun login VisionEA Anda telah berubah.</p>';
	  			$emailContent  .= '<table border="0">';
				$emailContent  .= '<tr><td width="100px">Username</td><td width="250px">: '.$username.'</td></tr>';
				$emailContent  .= '<tr><td width="100px">Time</td><td width="250px">: '.$passDate.'</td></tr>';
				$emailContent  .= '</table>';
				$emailContent  .= '<p>Jika ini adalah akun VisionEA Anda, dan Anda tidak pernah melakukan request penggantian password, Anda bisa langsung menghubungi <a href="mailto:support-id@visionea.net">support-id@visionEA.net</a></p>';
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some 
				//echo $emailBody; die();
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
					
			}else{
				fSendToAdmin($q, "inc_functions.php", "fetch_assoc error");
			}
		} else {
			fSendToAdmin($q, "inc_functions.php", "query empty record");
			return (false);
		}
	
	}elseif (strtoupper($q) == "CHANGE_SECURITY"){ //changePassword.php
		$sql 	= "SELECT pinMbrUsername, pinDate, mbrEmail FROM trPIN INNER JOIN dtMember ON mbrUsername=pinMbrUsername";
		$sql	.= " WHERE pinMbrUsername='".$id. "'";
		$sql	.= " ORDER BY pinDate DESC LIMIT 1";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$username 		= $row['pinMbrUsername'];
				$passDate 		= $row["pinDate"];
				
				$emailSubject 	= "Change Security Password";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $row["mbrEmail"];

	  			$emailContent  = '<p>Dear '. $username .',</p>';
	  			$emailContent  .= '<p>Anda baru saja mengubah <b>security password</b> Anda.</p>';
	  			$emailContent  .= '<table border="0">';
				$emailContent  .= '<tr><td width="100px">Username</td><td width="250px">: '.$username.'</td></tr>';
				$emailContent  .= '<tr><td width="100px">Time</td><td width="250px">: '.$passDate.'</td></tr>';
				$emailContent  .= '</table>';
				$emailContent  .= '<p>Jika Anda tidak mengubah security password, segera hubungi tim support kami.</p>';
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some 
				//echo $emailBody; die();
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'inc_functions.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
					
			}else{
				fSendToAdmin($q, "inc_functions.php", "fetch_assoc error");
			}
		} else {
			fSendToAdmin($q, "inc_functions.php", "query empty record");
			return (false);
		}
		
	}elseif (strtoupper($q) == "REQUEST_WD"){
		$sql = "SELECT wdID, wdMbrUsername, wdAmount, wdFee, wdTax, wdCode, wdDate, wdPayAcc, stDesc, mbrEmail, mbrFirstName, ptDesc FROM dtWDFund ";
		$sql .= " INNER JOIN dtMember ON wdMbrUsername = mbrUsername ";
		$sql .= " INNER JOIN msStatus ON stID = wdStID ";
		$sql .= " INNER JOIN ( SELECT * FROM dtPaymentAcc INNER JOIN msPaymentType ON payPTID=ptID ) AS payment ON payment.payMbrUsername=wdMbrUsername ";
		$sql .= " WHERE wdID='".$id."' AND wdStID='".$GLOBALS['DEF_STATUS_REQUEST']."'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$wdID 			= $row['wdID'];
				$wdAmount 		= $row['wdAmount'];
				$wdTax 			= $row['wdTax'];
				$wdNett 		= $wdAmount - $wdTax;
				$username 		= $row["wdMbrUsername"];
				$wdDate 		= $row["wdDate"];
				$wdPayAcc 		= $row["wdPayAcc"]; 
				$stDesc 		= $row["stDesc"];
				$mbrEmail 		= $row["mbrEmail"];
				$wdCode 		= $row["wdCode"];
				$ptDesc 		= $row["ptDesc"]; //Type of payment
				if ($wdID == $id){
					$verifyLink		= $COMPANY_SITE . 'member/verify/?MNav=reqWD&q='.$username.'&code='.$wdID;
					$emailSubject 	= "Konfirmasi Permintaan Penarikan";
					$emailFrom		= "EMAIL_NO_REPLY"; //"EMAIL_FINANCE";
					$emailTo		= $mbrEmail;

					$emailContent  = '<p>Dear '. $username .',</p>';
		  			$emailContent  .= '<p>Anda menerima pesan ini sebagai tanggapan atas permintaan pernarikan dana di situs VisionEA.net, dengan detail sebagai berikut :</p>';

		  			$emailContent  .= '<table border="0">';
					$emailContent  .= '<tr><td width="250px">Tipe account 			</td><td width="300px">: '.$ptDesc.'</td></tr>';
					$emailContent  .= '<tr><td width="250px">Akun Bank	</td><td width="300px">: '.$wdPayAcc.'</td></tr>';
					$emailContent  .= '<tr><td width="250px">Total penarikan 	</td><td width="300px">: '.$wdAmount.'</td></tr>';
					$emailContent  .= '<tr><td width="250px">Pajak 					</td><td width="300px">: '.$wdTax.'</td></tr>';
					$emailContent  .= '<tr><td width="250px">Total bersih			</td><td width="300px">: '.$wdNett.'</td></tr>';
					$emailContent  .= '<tr><td width="250px">Tanggal permintaan penarikan</td><td width="300px">: '.$wdDate.'</td></tr>';
					$emailContent  .= '</table>';
					$emailContent  .= '<p>Konfirmasi kode Anda adalah : <b>'.$wdCode.'</b></p>';
					
					$emailContent  .= 'Silahkan klik tombol berikut untuk memasukkan kode konfirmasi Anda<br>';
					$emailContent  .= '<a href="'. $verifyLink . '" class="button" style="color:#FFFFFF">Konfirmasi Penarikan Dana</a>';
					$emailContent  .= '<p><span style="color:red; font-weight: 600;">Perhatian !</span> Permintaan Anda akan dibatalkan jika tidak melakukan konfirmasi dalam <span style="color:blue">60 menit</span>.</p>';
					$emailContent  .= '<p>Proses penarikan dana akan di proses setiap hari <b>Selasa</b> maksimal 1 x 24 jam.<br>Jika Anda melakukan permintaan penarikan dana pada hari <b>Selasa</b> diatas jam <b>11.00 WIB</b>, maka proses penarikan dana akan diproses di minggu berikutnya.</p>';
					
					$emailAdditionNote = '';
					$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
					if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
						return (true); 
					}else {
						fSendToAdmin($q, 'inc_email.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
						return (false); //send to admin, error notification
					}
				}else{
					//$activationCode == ""
					return(false);
				}			
			}
		} else {
			return (false);
		}

	}elseif (strtoupper($q) == "WITHDRAWAL_CONFIRMED"){
		$sql  = "SELECT wdID, wdMbrUsername, wdAmount, wdFee, wdTax, wdNett, wdPayAcc, mbrEmail, mbrFirstName, ptDesc FROM dtWDFund INNER JOIN msStatus ON wdStID=stID ";
		$sql  .= " INNER JOIN dtMember ON wdMbrUsername = mbrUsername ";
		$sql  .= " INNER JOIN dtPaymentAcc ON payMbrUsername=wdMbrUsername ";
		$sql  .= " INNER JOIN msPaymentType ON ptID = payPTID ";
        $sql  .= " WHERE wdStID='".$GLOBALS['DEF_STATUS_APPROVED']."' AND wdID='".$id."'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$wdID 			= $row['wdID'];
				$wdAmount 		= $row['wdAmount'];
				$wdTax 			= $row['wdTax'];
				$wdNett 		= $row['wdNett'];
				$wdFee 			= $row['wdFee'];
				$username 		= $row["wdMbrUsername"];
				$wdPayAcc 		= $row["wdPayAcc"]; 
				$mbrEmail 		= $row["mbrEmail"];
				$ptDesc 		= $row["ptDesc"]; //Type of Payment
				if ($wdID == $id && ($wdAmount - $wdTax - $wdFee) == $wdNett){
					$emailSubject 	= "Proses Penarikan Berhasil : ".$wdID;
					$emailFrom		= "EMAIL_NO_REPLY"; //"EMAIL_FINANCE";
					$emailTo		= $mbrEmail;

					$emailContent  = '<p>Dear '. $username .',</p>';
		  			$emailContent  .= '<p>Proses penarikan dana telah berhasil.</p>';
		  			$emailContent  .= '<p>Dana telah dikirim ke sistem pembayaran dengan detail sebagai berikut :</p>';
		  			$emailContent  .= '<table border="0">';
					$emailContent  .= '<tr><td width="100px">No Transaksi 			</td><td width="300px">: '.$wdID.'</td></tr>';
					$emailContent  .= '<tr><td width="100px">Tipe Akun 			</td><td width="300px">: '.$ptDesc.'</td></tr>';
					$emailContent  .= '<tr><td width="100px">Akun Bank	</td><td width="300px">: '.$wdPayAcc.'</td></tr>';
					$emailContent  .= '<tr><td width="100px">Total  				</td><td width="300px">: '.$wdAmount.'</td></tr>';
					$emailContent  .= '<tr><td width="100px">Pajak  				</td><td width="300px">: '.$wdTax.'</td></tr>';
					$emailContent  .= '<tr><td width="100px">Total Bersih  				</td><td width="300px">: '.$wdNett.'</td></tr>';
					$emailContent  .= '</table>';
					$emailContent  .= '<p>Ini adalah email pemberitahuan tentang penarikan dana dari saldo komisi Anda</p>';
					$emailContent  .= '<p>&nbsp;</p><p>Jika Anda memiliki pertanyaan, silahkan hubungi team support Kami.</p>';
					
					$emailAdditionNote = '';
					$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
					if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
						return (true); 
					}else {
						fSendToAdmin($q, 'inc_email.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
						return (false); //send to admin, error notification
					}
				}else{
					//$activationCode == ""
					return(false);
				}			
			}
		} else {
			return (false);
		}
		
	}elseif (strtoupper($q) == "WITHDRAWAL_DECLINED"){
		$sql  = "SELECT wdID, wdMbrUsername, wdAmount, wdTax, wdNett, wdPayAcc, mbrEmail, mbrFirstName, ptDesc, wdDesc FROM dtWDFund INNER JOIN msStatus ON wdStID=stID ";
		$sql  .= " INNER JOIN dtMember ON wdMbrUsername = mbrUsername ";
		$sql  .= " INNER JOIN dtPaymentAcc ON payMbrUsername=wdMbrUsername ";
		$sql  .= " INNER JOIN msPaymentType ON ptID = payPTID ";
        $sql  .= " WHERE wdStID='".$GLOBALS['DEF_STATUS_DECLINED']."' AND wdID='".$id."'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$wdID 			= $row['wdID'];
				$wdAmount 		= $row['wdAmount'];
				$wdTax 			= $row['wdTax'];
				$wdNett 		= $row['wdNett'];
				$username 		= $row["wdMbrUsername"];
				$wdPayAcc 		= $row["wdPayAcc"]; 
				$mbrEmail 		= $row["mbrEmail"];
				$ptDesc 		= $row["ptDesc"]; //Type of Payment
				$reason 		= $row['wdDesc'];
				if ($wdID == $id){
					$emailSubject 	= "Permintaan Penarikan Dana Ditolak : ".$wdID;
					$emailFrom		= "EMAIL_NO_REPLY"; //"EMAIL_FINANCE";
					$emailTo		= $mbrEmail;

					$emailContent  = '<p>Dear '. $username .',</p>';
		  			$emailContent  .= '<p>Permintaan penarikan dana Anda telah ditolak.</p>';
		  			
		  			//$emailContent  .= '<p>The funds have been sent to the payment system as follows:</p>';
		  			$emailContent  .= '<table border="0">';
					$emailContent  .= '<tr><td width="100px">No Transaksi 			</td><td width="300px">: '.$wdID.'</td></tr>';
					$emailContent  .= '<tr><td width="100px">Tipe Akun 			</td><td width="300px">: '.$ptDesc.'</td></tr>';
					$emailContent  .= '<tr><td width="100px">Akun Bank	</td><td width="300px">: '.$wdPayAcc.'</td></tr>';
					$emailContent  .= '<tr><td width="100px">Total  				</td><td width="300px">: '.$wdAmount.'</td></tr>';
					$emailContent  .= '</table>';
					$emailContent  .= '<p>Adapun alasan penolak adalah :</p>';
		  			$emailContent  .= '<span style="color:Black; background-color:yellow; font-weight: bold;">'.$reason.'</span>';
					
					//$emailContent  .= '<p>This mail notifies you of funds withdrawal from your commission balance</p>';
					$emailContent  .= '<p>&nbsp;</p><p>Jika Anda memiliki pertanyaan, silahkan hubungi team support Kami.</p>';
					
					$emailAdditionNote = '';
					$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
					if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
						return (true); 
					}else {
						fSendToAdmin($q, 'inc_email.php', "username: " . $username . " Email: " . $emailTo . 'Email Content: '.$emailBody);
						return (false); //send to admin, error notification
					}
				}else{
					//$activationCode == ""
					return(false);
				}			
			}
		} else {
			return (false);
		}
	
	}elseif (strtoupper($q) == "TRADE_ACC_PENDING"){ //invalid trading account, sendCornEmail.php -> json.php -> SaveDataTradeAcc -> tradeAcc.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail, tradeID, tradeUsername, tradeAccNo, tradeAccPasswd, tradeServer FROM dtTradingAcc";
		$sql .= " INNER JOIN dtMember ON tradeUsername=mbrUsername";
		$sql .= " INNER JOIN dtCornEmail ON ceUniqID=tradeID";
		$sql .= " AND tradeStID='" . $GLOBALS['DEF_STATUS_PENDING'] . "'";
		$sql .= " AND ceid='" . $id . "'";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];
				$tradeAccNo 		= $row["tradeAccNo"]; 
				$tradeAccPasswd 	= $row["tradeAccPasswd"];
				$tradeServer 		= $row["tradeServer"];
				
				$emailSubject 	= "Invalid Trading Account - ". $tradeAccNo;
				$emailFrom		= "EMAIL_SUPPORT";
				$emailTo		= $mbrEmail;

				$emailContent  = '<p>Dear '.$mbrFirstName . " (". $mbrUsername .'),</p>';
	  			$emailContent  .= '<p>Sorry, we cannot activate your trading robot because your trading account information is still incorrect.</p>';
	  			$emailContent  .= '<p>Following is your trading account data:</p>';
	  			$emailContent  .= '<table border="0">';
				$emailContent  .= '<tr><td width="150px">Account Number 	</td><td width="200px">: '.$tradeAccNo.'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Trade Password	</td><td width="200px">: '.$tradeAccPasswd.'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Server			</td><td width="200px">: '.$tradeServer.'</td></tr>';
				$emailContent  .= '</table>';

	  			$emailContent  .= '<p></p>';
	  			$emailContent  .= '<p>We have reset your trading account data at this time,</p>';
	  			$emailContent  .= '<p>Please update the correct data again.</p>';

				
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		} else {
			return (false);
		}

	}elseif (strtoupper($q) == "TRADE_ACC_RESET_BY_REQUEST"){ //Reset Trading Account (reset by admin due to member request), sendCornEmail.php -> json.php -> SaveDataTradeAcc -> tradeAcc.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail, tradeID, tradeUsername, tradeAccNo, tradeAccPasswd, tradeServer FROM dtTradingAcc";
		$sql .= " INNER JOIN dtMember ON tradeUsername=mbrUsername";
		$sql .= " INNER JOIN dtCornEmail ON ceUniqID=tradeID";
		$sql .= " AND tradeStID='" . $GLOBALS['DEF_STATUS_PENDING'] . "' ";
		$sql .= " AND ceid='" . $id . "' ";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];
				$tradeAccNo 		= $row["tradeAccNo"]; 
				$tradeAccPasswd 	= $row["tradeAccPasswd"];
				$tradeServer 		= $row["tradeServer"];
				
				$emailSubject 	= "Reset Trading Account - ". $tradeAccNo;
				$emailFrom		= "EMAIL_SUPPORT";
				$emailTo		= $mbrEmail;

				$emailContent  = '<p>Dear '.$mbrFirstName . " (". $mbrUsername .'),</p>';
	  			$emailContent  .= '<p>Your trading account has been reset successfully.</p>';
	  			$emailContent  .= '<p>Following is your trading account data:</p>';
	  			$emailContent  .= '<table border="0">';
				$emailContent  .= '<tr><td width="150px">Account Number 	</td><td width="200px">: '.$tradeAccNo.'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Trade Password	</td><td width="200px">: '.$tradeAccPasswd.'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Server			</td><td width="200px">: '.$tradeServer.'</td></tr>';
				$emailContent  .= '</table>';

	  			$emailContent  .= '<p></p>';
	  			

				
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		} else {
			return (false);
		}		
		
	}elseif (strtoupper($q) == "TRADE_ACC_CHANGE_PASSWD"){ //Change Trading Password, sendCornEmail.php -> json.php -> SaveDataTradeAcc -> tradeAcc.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail, tradeID, tradeUsername, tradeAccNo, tradeAccPasswd, tradeServer FROM dtTradingAcc";
		$sql .= " INNER JOIN dtMember ON tradeUsername=mbrUsername";
		$sql .= " INNER JOIN dtCornEmail ON ceUniqID=tradeID";
		//$sql .= " AND tradeStID='" . $GLOBALS['DEF_STATUS_PENDING'] . "'"; // status can be any
		$sql .= " AND ceid='" . $id . "'";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];
				$tradeAccNo 		= $row["tradeAccNo"]; 
				$tradeAccPasswd 	= $row["tradeAccPasswd"];
				$tradeServer 		= $row["tradeServer"];
				
				$emailSubject 	= "Change Password of Trading Account - ". $tradeAccNo;
				$emailFrom		= "EMAIL_SUPPORT";
				$emailTo		= $mbrEmail;

				$emailContent  = '<p>Dear '.$mbrFirstName . " (". $mbrUsername .'),</p>';
	  			$emailContent  .= '<p>Your trading account password has been successfully changed.</p>';
	  			$emailContent  .= '<p>Following is your trading account data:</p>';
	  			$emailContent  .= '<table border="0">';
				$emailContent  .= '<tr><td width="150px">Account Number 	</td><td width="200px">: '.$tradeAccNo.'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Trade Password	</td><td width="200px">: '.$tradeAccPasswd.'</td></tr>';
				$emailContent  .= '<tr><td width="150px">Server			</td><td width="200px">: '.$tradeServer.'</td></tr>';
				$emailContent  .= '</table>';

	  			$emailContent  .= '<p></p>';
	  			

				
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		} else {
			return (false);
		}

	}elseif (strtoupper($q) == "BDAY"){ //sendCornEmail.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail FROM dtCornEmail INNER JOIN dtMember ON mbrUsername = ceUsername ";
		$sql .= " WHERE ceid='$id' AND cecat ='$q'";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];
				
				$emailSubject 	= "Happy Birthday";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $mbrEmail;

				/*
				$emailContent  = '<p>Dear '.$mbrFirstName . " (". $mbrUsername .'),</p>';
				$emailContent  = '<p>Happy Birthday</p>';
				
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				*/

				$emailBody = fGetEmailBody_BDAY();
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		} else {
			return (false);
		}
	}elseif (strtoupper($q) == "RENEW_REMINDER"){ //sendCornEmail.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail FROM dtCornEmail INNER JOIN dtMember ON mbrUsername = ceUsername ";
		$sql .= " WHERE ceid='$id' AND cecat ='$q'";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];

				$sql = "SELECT Date(DATE_ADD(mbrDate, INTERVAL trThn YEAR)) AS renewDate FROM Transaction ";
				$sql .= " INNER JOIN dtMember ON mbrUsername = trUsername";
				$sql .= " WHERE trUsername = '" . $mbrUsername . "' ORDER BY trDate DESC LIMIT 1";
				$result = $conn->query($sql);
				if ($row = $result->fetch_assoc()){

					$renewDate 		= date_create($row['renewDate']);
					$renewDate 		= date_format($renewDate, "d M Y");

					$emailSubject 	= "REMINDER : Perpanjang Keanggotaan";
					$emailFrom		= "EMAIL_NO_REPLY";
					$emailTo		= $mbrEmail;

					$emailContent  = '<p>Dear '.$mbrFirstName . " (". $mbrUsername .'),</p>';
					//$emailContent  .= '<p>Renew Package Reminder</p>';

					$emailContent .= '<p>Keanggotaan Anda akan segera berakhir, silahkan memperbarui keanggotaan Anda sebelum tanggal '. $renewDate . '.<br>';
					$emailContent .= '<p>Untuk memperbarui keanggotaan Anda,<br>';
					$emailContent .= 'Silahkan login ke <b>Member Area</b> dan buka menu <b>My Profile</b>. Untuk memperbarui keanggotaan Anda harus memiliki Pin untuk aktivasi.</p>';
					$emailContent .= '<p>Jika Anda memiliki pertanyaan, silahkan hubungi <a href="https://api.whatsapp.com/send?phone=6285245069512&text=&source=&data=">Customer Service</a> Kami.</p>';
					$emailAdditionNote = '';
					$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
					
					if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
						return (true); 
					}else {
						fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "SEND EMAIL FAILED >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
						return (false); //send to admin, error notification
					}
				}else{
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "Record not found >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		} else {
			return (false);
		}
	}elseif (strtoupper($q) == "PROMO_CNY_2019"){ //sendCornEmail.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail FROM dtCornEmail INNER JOIN dtMember ON mbrUsername = ceUsername ";
		$sql .= " WHERE ceid='$id' AND cecat ='$q'";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];
				
				$emailSubject 	= "New Year Campaign 2019";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $mbrEmail;

				//$url_img		= "https://visionea.net/bnr/". "images/promoMaterials/campaign 201902.jpg"; //$GLOBALS['COMPANY_SITE'] . "images/promoMaterials/campaign 201902.jpg";
				$url_img		= $GLOBALS['COMPANY_SITE'] . "images/promoMaterials/campaign_201902.jpg";
				$alt_img 		= $emailSubject;
				$width_img 		= "80%";

				$emailContent  = "<div style='margin:10px'>";
				$emailContent  .= "<p>Dear ". $mbrFirstName . " (". $mbrUsername ."),</p>";
				$emailContent  .= "<p>2019 is going to be the best year for all of us, especially for you who will be a great network team leader.</p>";

				$emailContent  .= "<p>We appreciate you as a VisionEA development partner throughout the world.<br>";
				$emailContent  .= "That is why VisionEA gives extra commissions to you who keep moving forward with us.</p>";
				
				$emailContent  .= "<p>When you successfully recruit new members with a total turnover of $5,000, you have the right to get an additional commission of $1,000 and multiply it.</p>";

				$emailContent  .= "<p>Starting from 1 February - 31 March 2019<br>";
				$emailContent  .= "Every member who is qualified, extra commission will be sent directly to you.</p>";

				$emailContent  .= "<p>&nbsp;</p>";
				$emailContent  .= "<img src='". $url_img . "' alt='". $alt_img. "' width='". $width_img. "' style='margin:10px'>";
				$emailContent  .= "</div>";

				
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_promo', $emailSubject, $emailContent, $emailAdditionNote, $emailFrom); //there are some email format
				//echo $emailBody; die();
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		} else {
			return (false);
		}
	}elseif (strtoupper($q) == "BONUS_SP_UP_20"){ //sendCornEmail.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail FROM dtCornEmail INNER JOIN dtMember ON mbrUsername = ceUsername ";
		$sql .= " WHERE ceid='$id' AND cecat ='$q'";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];
				
				$emailSubject 	= "Wow, The Sponsor Commission increased by 100%";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $mbrEmail;

				//$url_img		= "https://visionea.net/bnr/". "images/promoMaterials/campaign 201902.jpg"; //$GLOBALS['COMPANY_SITE'] . "images/promoMaterials/campaign 201902.jpg";
				$url_img		= $GLOBALS['COMPANY_SITE'] . "images/promoMaterials/bonus_up.jpeg";
				$alt_img 		= $emailSubject;
				$width_img 		= "80%";

				$emailContent  = "<div style='margin:10px'>";
				$emailContent  .= "<p>Dear ". $mbrFirstName . " (". $mbrUsername ."),</p>";
				$emailContent  .= "<p>We are very happy to let you know that BIG changes have been taken for you.</p>";

				$emailContent  .= "<p>As of today, we have increased 100% of the sponsor commission and pass-up commission from the previous commission amount.</p>";

				$emailContent  .= "<p>Enjoy the benefits of increasing this commission and growing your network faster.</p>";

				$emailContent  .= "<p>&nbsp;</p>";
				$emailContent  .= "<img src='". $url_img . "' alt='". $alt_img. "' width='". $width_img. "' style='margin:10px'>";
				$emailContent  .= "</div>";

				
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_promo', $emailSubject, $emailContent, $emailAdditionNote, $emailFrom); //there are some email format
				//echo $emailBody; die();
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		} else {
			return (false);
		}
	}elseif (strtoupper($q) == "INFO_NEW_EMAIL"){ //sendCornEmail.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail FROM dtCornEmail INNER JOIN dtMember ON mbrUsername = ceUsername ";
		$sql .= " WHERE ceid='$id' AND cecat ='$q'";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];
				
				$emailSubject 	= "Support Indonesia";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $mbrEmail;

				//$url_img		= "https://visionea.net/bnr/". "images/promoMaterials/campaign 201902.jpg"; //$GLOBALS['COMPANY_SITE'] . "images/promoMaterials/campaign 201902.jpg";
				
				/*
				$url_img		= $GLOBALS['COMPANY_SITE'] . "images/promoMaterials/bonus_up.jpeg";
				$alt_img 		= $emailSubject;
				$width_img 		= "80%";
				*/

				$emailContent  = "<div style='margin:10px'>";
				$emailContent  .= "<p>Dear ". $mbrFirstName . " (". $mbrUsername ."),</p>";
				$emailContent  .= "<p>Selamat datang di VisionEA Indonesia</p>";
				$emailContent  .= "<p>Untuk layanan di Indonesia, kami memberikan dukungan layanan dalam bahasa Indonesia.</p><p>Jika Anda memerlukan bantuan, jangan ragu untuk mengirim email Anda ke support-id@visionea.net</p>";

				$emailContent  .= "<p>&nbsp;<br>-----------------------------------------------------------------------------</p>";

				$emailContent  .= "<p>Welcome VisionEA Indonesia</p>";
				$emailContent  .= "<p>For services in Indonesia, we provide support in bahasa Indonesia.</p><p>If you need assist/support, please don't hesitate to send your email to support-id@visionea.net</p>";
				
				/*
				$emailContent  .= "<img src='". $url_img . "' alt='". $alt_img. "' width='". $width_img. "' style='margin:10px'>";
				$emailContent  .= "</div>";
				*/

				
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote, $emailFrom); //there are some email format
				//echo $emailBody; die();
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		} else {
			return (false);
		}
	}elseif (strtoupper($q) == "EXTEND_EXPIRE"){ //sendCornEmail.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail FROM dtCornEmail INNER JOIN dtMember ON mbrUsername = ceUsername ";
		$sql .= " WHERE ceid='$id' AND cecat ='$q'";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];

				$emailSubject 	= "Perpanjang Masa kadaluarsa";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $mbrEmail;

				$emailContent 	 = '<p>Dear,</p>';
				$emailContent 	.= '<p>Berikut kami informasikan masa perpanjang keanggotaan VisionEA.</p>';
				$emailContent 	.= '<p>Kepada Klien yang masa aktif keanggotaan sudah kadaluarsa dan telah lebih dari 1 bulan dari tanggal kadaluarsa tetapi kurang dari 3 bulan, kami memberi kesempatan terbatas untuk segera memperpanjang keanggotaan selambat-lambatnya tanggal 14 Maret 2020.</p>';
				$emailContent 	.= '<p>Setelah tanggal 14 Maret 2020, masa toleransi perpanjang (renew) adalah 1 bulan (30 hari kalender) dari tanggal kadaluarsa.</p>';
				$emailContent 	.= '<p>Jika ada hal yang kurang jelas, silahkan hubungi Customer Service kami.<br>Whatsapp : <a href="https://api.whatsapp.com/send?phone=6285245069512&text=Dear Customer Service, Mohon infonya mengenai perpanjang masa kadaluarsa. %0a‎username : '.$mbrUsername.' &source=&data=">+62 852-4506-9512</a></p>';
				$emailContent 	.= '<br><br>';
				$emailContent 	.= '<p>Dear,</p>';
				$emailContent 	.= '<p>Here we inform about the extension of VisionEA membership.</p>';
				$emailContent 	.= '<p>To Clients whose membership has expired and have been more than 1 month from the expiry date but less than 3 months, we provide limited opportunities to immediately renew membership no later than March 14, 2020.</p>';
				$emailContent 	.= '<p>After March 14, 2020, the tolerance period for renewal is 1 month (30 calendar days) from the expiration date</p>';
				$emailContent 	.= '<p>If there is anything unclear, please contact our Customer Service. <br>Whatsapp : <a href="https://api.whatsapp.com/send?phone=6285245069512&text=Dear Customer Service, i want to ask about extend the expiration period. %0a‎username : '.$mbrUsername.' &source=&data=">+62 852-4506-9512</a></p>';
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "SEND EMAIL FAILED >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}
		} else {
			return (false);
		}
	}elseif (strtoupper($q) == "CASHBACK_RENEW"){ //sendCornEmail.php
		$sql = "SELECT mbrUsername, mbrFirstName, mbrEmail FROM dtCornEmail INNER JOIN dtMember ON mbrUsername = ceUsername ";
		$sql .= " WHERE ceid='$id' AND cecat ='$q'";
		//echo $sql;
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 		= $row['mbrUsername'];
				$mbrFirstName 		= $row['mbrFirstName'];
				$mbrEmail 			= $row["mbrEmail"];

				$emailSubject 	= "Promo VisionEA : Cashback Renew";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $mbrEmail;

				// $emailContent  = '<p>Dear '.$mbrFirstName . " (". $mbrUsername .'),</p>';
				//$emailContent  .= '<p>Renew Package Reminder</p>';

				$emailContent  = '<p>Buat kamu yang ingin terus menikmati keuntungan menggunakan robot trading VisionEA yang terbukti, teruji konsisten memberikan profit, bisa segera lakukan perpanjang masa keanggotaan dan mendapatkan <b>CASHBACK 10%</b>.</p>';
				$emailContent .= '<p><b>Syaratnya gampang :</b>';
				$emailContent .= '
				<ol>
					<li>Lakukan perpanjang (renew) sekarang atau selambat-lambatnya tanggal 14 Maret 2020.</li>
					<li>Berlaku untuk semua paket keanggotaan.</li>
					<li>Masa expired keanggotaan tidak lebih dari 1 tahun.</li>
				</ol></p>';
				$emailContent .= '<p><b>Ketentuan :</b>';
				$emailContent .= '
				<ol>
					<li>Pengajuan Klaim Promo Cashback Renew dilakukan antar tanggal 15 Maret - 21 Maret 2020.</li>
					<li>Klaim Promo Cashback Renew dengan mengirimkan email ke support dengan data :
						<ul>
							<li>Subject Email : Klaim CashBack <b style="font-style : italic;">[Username Renew]</b></li>
							<li>Isi Email : 
								<ul style="font-style : italic;">
									<li>Saya mau klaim cashback 10% renew.</li>
									<li>Nomor Rekening BCA : xxx-xxx-xxx</li>
									<li>Nama di Rekening : xxx-xxx-xxx</li>
								</ul>
							</li>
						</ul>
					</li>
					<li>Nama di rekening WAJIB sama dengan data keanggotaan Anda, jika terdapat perbedaan silakan hubungi CS kami.</li>
					<li>Telah lolos verifikasi ID (KTP).</li>
					<li>Dana Cashback akan ditransfer ke rekening Anda selambat-lambatnya tanggal 24 Maret 2020.</li>
				</ol>';
				$emailContent .= '<p>Jika ada hal yang ingin ditanyakan, silakan hubungi CS kami.<br>Email : <a href="mailto:support-id@visionea.net">support-id@visionEA.net</a><br>Whatsapp : <a href="https://api.whatsapp.com/send?phone=6285245069512&text=&source=&data=">+62 852-4506-9512</a></p>';
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "SEND EMAIL FAILED >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}else{
				fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "Record not found >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
				return (false); //send to admin, error notification
			}
		}else{
			return (false);
		}
	}elseif (strtoupper($q) == "BUY_PRODUCT"){ //sendCornEmail.php
		$sql  = "SELECT ebUsername, ebEmail, ebFirstName, trProTransID";
		$sql .= " FROM dtCornEmail";
		$sql .= " INNER JOIN trProduct ON trProTransID = ceUniqID";
		$sql .= " INNER JOIN dtUserEbook ON ebProTransID  = ceUniqID";
		$sql .= " WHERE ceid='$id' AND cecat ='$q' AND trProStatus = '".$GLOBALS['DEF_STATUS_APPROVED']."'";
		// return $sql; die();
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()){
				$ebUsername 	= $row['ebUsername'];
				$ebFirstName 	= $row['ebFirstName'];
				$ebEmail 		= $row["ebEmail"];
				$OrderID 		= $row['trProTransID'];
				$passEbook 		= substr($OrderID, -6); 
				$emailSubject 	= "Order No : ".$OrderID." - Purchased";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $ebEmail;

				$emailContent 	 = '<p>Dear '.$ebUsername.',</p>';
				$emailContent 	.= '<p>Pembelian Anda berhasil.</p>';
				$emailContent 	.= '<p>Silahkan klik tombol berikut untuk mengakses produk yang sudah dibeli.</p>';
				$emailContent 	.= '<a href="https://visionea.net/bnr/ebook" class="button" style="color:#FFFFFF">Login Ebook</a>';
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "SEND EMAIL FAILED >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}else{
				fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "Record not found >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
				return (false); //send to admin, error notification
			}
		}else{
			return (false);
		}
	}elseif (strtoupper($q) == "BUY_PRODUCT_RS"){ //sendCornEmail.php
		$sql  = "SELECT ebUsername, ebEmail, ebFirstName, trProTransID";
		$sql .= " FROM dtCornEmail";
		$sql .= " INNER JOIN trProduct ON trProTransID = ceUniqID";
		$sql .= " INNER JOIN dtUserEbook ON ebProTransID = trProTransID";
		$sql .= " WHERE ceid='$id' AND cecat ='$q' AND trProStatus = '".$GLOBALS['DEF_STATUS_APPROVED']."'";
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$ebUsername 	= $row['ebUsername'];
				$ebFirstName 	= $row['ebFirstName'];
				$ebEmail 		= $row["ebEmail"];
				$OrderID 		= $row['trProTransID'];
				$passEbook 		= substr($OrderID, -6); 
				$emailSubject 	= "Order No : ".$OrderID." - Purchased";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $ebEmail;

				$emailContent 	 = '<p>Dear '.$ebUsername.',</p>';
				$emailContent 	.= '<p>Pembelian Anda berhasil.</p>';
				$emailContent 	.= '<p>Data Login Ebook :</p>';
				$emailContent 	.= '<table><tr><td>Username</td><td>:</td><td>'.$ebUsername.'</td></tr><tr><td>Password</td><td>:</td><td>'.$passEbook.'</td></tr></table>';
				$emailContent 	.= '<p>Silahkan klik tombol berikut untuk mengakses produk yang sudah dibeli.</p>';
				$emailContent 	.= '<a href="https://visionea.net/bnr/ebook" class="button" style="color:#FFFFFF">Login Ebook</a>';
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "SEND EMAIL FAILED >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}else{
				fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "Record not found >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
				return (false); //send to admin, error notification
			}
		}else{
			return (false);
		}		
	}elseif (strtoupper($q) == "SEND_EBOOK_DATA"){ //sendCornEmail.php
		$sql  = "SELECT ebUsername, ebEmail, ebFirstName, trProTransID";
		$sql .= " FROM dtCornEmail";
		$sql .= " INNER JOIN trProduct ON trProUserBeli = ceUsername";
		$sql .= " INNER JOIN dtUserEbook ON ebUsername = ceUsername";
		$sql .= " WHERE ceid='$id' AND cecat ='$q' AND trProStatus = '".$GLOBALS['DEF_STATUS_APPROVED']."'";
		// return $sql; die();
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$ebUsername 	= $row['ebUsername'];
				$ebFirstName 	= $row['ebFirstName'];
				$ebEmail 		= $row["ebEmail"];
				$OrderID 		= $row['trProTransID'];
				$passEbook 		= substr($OrderID, -6); 
				$emailSubject 	= "E-Book Account";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $ebEmail;

				$emailContent 	 = '<p>Dear, '.$ebUsername.'</p>';
				$emailContent 	.= '<p>Terima kasih Anda telah melakukan pembelian E-book.</p>';
				$emailContent 	.= '<p>Berikut data Login E-book Anda :</p>';
				$emailContent 	.= '<table><tr><td>Username</td><td>:</td><td>'.$ebUsername.'</td></tr><tr><td>Password</td><td>:</td><td>'.$passEbook.'</td></tr></table>';
				$emailContent 	.= '<p>Silahkan klik tombol berikut untuk mengakses E-book Anda.</p>';
				$emailContent 	.= '<a href="https://visionea.net/bnr/ebook" class="button" style="color:#FFFFFF">Login Ebook</a>';
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "SEND EMAIL FAILED >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}else{
				fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "Record not found >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
				return (false); //send to admin, error notification
			}
		}else{
			return (false);
		}
	}elseif (strtoupper($q) == "SEND_MYMAC_DATA"){ //sendCornEmail.php
		$sql  = "SELECT mbrUsername, mbrEmail, mbrFirstName";
		$sql .= " FROM dtCornEmail";
		$sql .= " INNER JOIN dtMember ON mbrUsername = ceUsername";
		$sql .= " WHERE ceid='$id' AND cecat ='$q' AND mbrStID = '".$GLOBALS['DEF_STATUS_ACTIVE']."'";
		// return $sql; die();
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			if($row = $result->fetch_assoc()) {
				$mbrUsername 	= $row['mbrUsername'];
				$mbrFirstName 	= $row['mbrFirstName'];
				$mbrEmail 		= $row["mbrEmail"];
				$emailSubject 	= "Mymac Account";
				$emailFrom		= "EMAIL_NO_REPLY";
				$emailTo		= $mbrEmail;

				$emailContent 	 = '<p>Dear, '.$mbrUsername.'</p>';
				$emailContent 	.= '<p>Selamat akun Mymac Anda telah aktif.</p>';
				$emailContent 	.= '<p>Silahkan login menggunakan username dan password yang terdaftar melalui link <a href="'.$GLOBALS['DEF_URL_MYMAC'].'">'.$GLOBALS['DEF_URL_MYMAC'].'</a>.</p>';
				$emailAdditionNote = '';
				$emailBody	= fGetEmailBody('format_general', $emailSubject, $emailContent, $emailAdditionNote); //there are some email format
				
				if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
					return (true); 
				}else {
					fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "SEND EMAIL FAILED >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
					return (false); //send to admin, error notification
				}
			}else{
				fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "Record not found >> username: " . $mbrUsername . " Email: " . $emailTo . 'Email Content: '.$emailBody);
				return (false); //send to admin, error notification
			}
		}else{
			return (false);
		}
	}elseif (strtoupper($q) == "SEND_NEW_GENERAL"){
		$emailSubject 	= "Mymac Account";
		$emailContent 	= '';
		$emailAdditionNote = '';
		$emailFrom		= "EMAIL_NO_REPLY";
		$emailTo		= '';
		$emailBody		= fGetEmailBody('format_genereal_new', $emailSubject, $emailContent, $emailAdditionNote);
		if (fSendEmail($emailFrom, $emailTo, $emailSubject, $emailBody)) {
			return (true); 
		}else {
			fSendToAdmin($q, 'sendCornEmail->inc_functions.php', "SEND EMAIL FAILED >> SEND_NEW_GENERAL");
			return (false); //send to admin, error notification
		}
	}
}

function fGetEmailBody($format, $emailSubject, $emailContent, $emailAdditionNote, $emailFrom = "EMAIL_SUPPORT"){
	global $COMPANY_SITE, $DEF_LINK_FB, $DEF_LINK_IG;

	if ($format == 'format_general'){
		// $emailHeader 	= '<img src="'.$COMPANY_SITE. 'assets/img/email/headerEmailVisionEA.jpg" width="100%" />';
		// untuk localhost biar gambar bisa di tampilkan
		$emailHeader 	= '<img src="visionea.net/bnr/assets/img/email/headerEmailVisionEA.jpg" width="100%" />';
		if ($emailFrom=="EMAIL_SUPPORT"){
			$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Salam,</div><div class="row">Tim VisionEA</div></div><p>&nbsp;</p>';
		}else if ($emailFrom == "EMAIL_FINANCE"){
			$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Salam,</div><div class="row">Dept. Keuangan VisionEA</div></div><p>&nbsp;</p>';
		}else if ($emailFrom == "EMAIL_NO_REPLY"){
			$emailSign 		= '<p>&nbsp;</p><div class="col-md-12"><div class="row">Salam,</div><div class="row">Tim VisionEA</div></div><p>&nbsp;</p>';
		}	


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
		. 	'<style type="text/css"> .button {border: none;background: #E91E63;color: #ffffff;padding: 10px;display: inline-block;margin: 10px 0px;font-family: Helvetica, Arial, sans-serif;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;text-decoration: none;}.button:hover {color: #ffffff;background: #666;text-decoration: none;}</style>'
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
			.   '<div class="col-md-12 small">'
			.	'You are receiving this email because you are listed on <a href="https://visionea.net">VisionEA.net</a> membership. PLEASE DO NOT REPLY TO THIS EMAIL. '
			.	'This is an auto generated mail and replies to this email id are not attended to. For any questions you can contact ' . $GLOBALS['EMAIL_SUPPORT']
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
		

		$emailBody 	= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns:v="urn:schemas-microsoft-com:vml"><head><meta charset="utf-8">';
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

	}else if ($format == 'format_genereal_new'){
		$emailBody 	 = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns:v="urn:schemas-microsoft-com:vml">
<head style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <meta charset="utf-8" style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <meta name="viewport" content="width=device-width" style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <title style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"></title>

    <href="https://t11media.s3.amazonaws.com/email/transactional/fonts/fonts.css" rel="stylesheet" type="text/css" style="-ms-text-size-adjust: 100%;-webkit-text-size-adjuslinkt: 100%;">
    <style type="text/css" style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
        html,
        body {
            Margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
        }
        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        div[style*="margin: 16px 0"] {
            margin: 0 !important;
        }
        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }
        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            Margin: 0 auto !important;
        }
        table table table {
            table-layout: auto;
        }
        img {
            -ms-interpolation-mode: bicubic;
        }
        .yshortcuts a {
            border-bottom: none !important;
        }
        .mobile-link--footer a,
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: underline !important;
        }
    </style>
    <style style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
        .button-td,
        .button-a {
            transition: all 100ms ease-in;
        }
        .button-td:hover,
        .button-a:hover {
            background: #000000 !important;
            border-color: #000000 !important;
        }
        @media screen and (max-width: 480px) {
            .fluid,
            .fluid-centered {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                Margin-left: auto !important;
                Margin-right: auto !important;
            }
            .fluid-centered {
                Margin-left: auto !important;
                Margin-right: auto !important;
            }
            .stack-column,
            .stack-column-center {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                direction: ltr !important;
            }
            .stack-column-center {
                text-align: center !important;
            }
            .center-on-narrow {
                text-align: center !important;
                display: block !important;
                Margin-left: auto !important;
                Margin-right: auto !important;
                float: none !important;
            }
            table.center-on-narrow {
                display: inline-block !important;
            }
            .heading-text {
                font-size: 36px!important;
                line-height: 36px!important;
            }
        }
    </style>
</head>
<body width="100%" bgcolor="#131416" style="margin: 0 !important;background-color: #131416;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;padding: 0 !important;height: 100% !important;width: 100% !important;">
    <div style="background: #131416;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
        <table cellpadding="0" cellspacing="0" border="0" height="100%" width="100%" bgcolor="#131416" style="border-collapse: collapse!important;border-spacing: 0!important;margin: 0 auto;table-layout: fixed!important;background: url(https://cdn.shopify.com/s/files/1/0013/7332/files/trans_email_footer.jpg?317488461733349908) no-repeat bottom center/100% auto;max-width: 1000px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
            <tbody>
                <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <td valign="top" style="background: url(https://cdn.shopify.com/s/files/1/0013/7332/files/trans_email_header.jpg?317488461733349908) no-repeat top center/100% auto;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
                        <center style="width: 100%;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                            <div style="display: none;font-size: 1px;line-height: 1px;max-height: 0px;max-width: 0px;opacity: 0;overflow: hidden;mso-hide: all;font-family: sans-serif;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                Click the link in this email to confirm your subscription to the theory11 newsletter
                            </div>
                            <div style="max-width: 680px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">

                                <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;border-spacing: 0 !important;border-collapse: collapse !important;table-layout: fixed !important;margin: 0 auto !important;">
                                    <tbody>
                                        <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                            <td style="padding: 20px;text-align: center;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
                                                <a style="border: none;display: block;padding: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" href="https://www.theory11.com" target="_blank">
                                                    <img src="https://t11media.s3.amazonaws.com/email/transactional/logo_top.png" width="100%" style="width: 100%;height: auto;border: none;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;-ms-interpolation-mode: bicubic;" border="0" alt="theory11">
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;border-spacing: 0 !important;border-collapse: collapse !important;table-layout: fixed !important;margin: 0 auto !important;">
                                    <tbody>
                                        <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                            <td id="heading" class="heading-text" style="padding: 25px 20px 8px 20px;text-align: center;color: #b78846;font-family: minion-pro, Minion Pro, Georgia, serif;text-transform: uppercase;font-weight: 500;font-size: 42px;line-height: 46px;letter-spacing: 4px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
                                                Newsletter
                                            </td>
                                        </tr>
                                        <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                            <td id="subheading" style="padding: 0 20px 10px 20px;text-align: center;color: #FFFFFF;text-transform: uppercase;font-family: brandon-grotesque, Brandon Grotesque, helvetica, arial, sans-serif;font-size: 18px;font-weight: 700;line-height: 24px;letter-spacing: 4px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
                                                Please Confirm Subscription
                                            </td>
                                        </tr>
                                        <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                            <td style="padding: 0 0 10px 0;text-align: center;color: #b78846;text-transform: uppercase;font-family: arial, helvetica, sans-serif;font-size: 12px;font-weight: 700;line-height: 12px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
                                                ♦ ♦ ♦
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;border-spacing: 0 !important;border-collapse: collapse !important;table-layout: fixed !important;margin: 0 auto !important;">
                                    <tbody>
                                        <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                            <td style="font-family: brandon-grotesque, Brandon Grotesque, helvetica, arial, sans-serif;color: #FFFFFF;font-size: 18px;line-height: 22px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
                                                <table cellspacing="0" cellpadding="0" border="0" align="center" style="padding-top: 30px;margin: auto;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;table-layout: fixed !important;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;border-spacing: 0 !important;border-collapse: collapse !important;">
                                                    <tbody>
                                                        <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                            <td style="background: #b78846;text-align: center;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;transition: all 100ms ease-in;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;" class="button-td">
                                                                <a href="https://www.theory11.com/" style="background: #b78846;border: 15px solid #b78846;padding: 0 10px;color: #ffffff;font-family: brandon-grotesque, Brandon Grotesque, helvetica, arial, sans-serif;text-transform: uppercase;letter-spacing: 2px;font-size: 16px;line-height: 1.1;text-align: center;text-decoration: none;display: block;font-weight: bold;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;transition: all 100ms ease-in;"
                                                                class="button-a" target="_blank">
                                                                Yes, sign me up!
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <table border="0" cellpadding="0" cellspacing="0" align="center" width="100%" style="max-width: 660px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;table-layout: fixed !important;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;border-spacing: 0 !important;border-collapse: collapse !important;margin: 0 auto !important;">
                                                <tbody>
                                                    <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                        <td align="center" valign="top" style="font-size: 0;padding: 10px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
                                                            <p style="font-family: brandon-grotesque, Brandon Grotesque, helvetica, arial, sans-serif;color: #FFFFFF;font-size: 20px;line-height: 32px;margin: 10px 0;display: block;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">Click the confirmation link above to subscribe and <strong style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">learn a free trick immediately!</strong> Instructions on how to watch your first free video will
                                                            be sent as soon as you hit the button.</p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;border-spacing: 0 !important;border-collapse: collapse !important;table-layout: fixed !important;margin: 0 auto !important;">
                                <tbody>
                                    <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                        <td style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
                                            <a style="border: none;display: block;padding: 0;width: 112px;margin: 50px auto 0px auto;text-align: center;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" href="https://www.theory11.com" target="_blank">
                                                <img src="https://t11media.s3.amazonaws.com/email/transactional/logo_txi.png" width="80" style="width: 80px;height: auto;border: none;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;-ms-interpolation-mode: bicubic;" border="0" alt="theory11">
                                            </a>
                                        </td>
                                    </tr>
                                    <tr style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                        <td style="padding: 20px 20px 40px 20px;width: 100%;font-size: 16px;font-family: brandon-grotesque, Brandon Grotesque, helvetica, arial, sans-serif;mso-height-rule: exactly;line-height: 20px;text-align: center;color: #FFFFFF;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;mso-table-lspace: 0pt !important;mso-table-rspace: 0pt !important;">
                                            <div style="max-width: 320px;margin: 0 auto;text-align: center;display: inline-block;position: relative;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                If you have any questions about this email, contact <a href="https://www.theory11.com/support" style="margin: 0;padding: 0;color: #b78846;text-decoration: none;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" target="_blank">theory11 support</a>                          or reply to this message.
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </center>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>';
	}
	return ($emailBody);
}


function fGetEmailBody_BDAY(){
	global $COMPANY_SITE, $DEF_LINK_FB, $DEF_LINK_IG;
	$emailBody = "";
	$emailBody = '<html><head><meta charset="utf-8">';
$emailBody .= '<title>Happy Birthday</title>';
$emailBody .= '<style>';
$emailBody .= '.container,body{min-width:992px!important}';
$emailBody .= '.navbar{display:none}.badge{border:1px solid #000}.table{border-collapse:collapse!important}.table td,.table th{background-color:#fff!important}.table-bordered td,.table-bordered th{border:1px solid #ddd!important}}article,aside,dialog,figcaption,figure,footer,header,hgroup,legend,main,nav,section{display:block}label,output{display:inline-block}*,::after,::before{box-sizing:border-box}html{font-family:sans-serif;line-height:1.15;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;-ms-overflow-style:scrollbar;-webkit-tap-highlight-color:transparent}@-ms-viewport{width:device-width}body{margin:0;font-size:1rem}[tabindex="-1"]:focus{outline:0!important}abbr[data-original-title],abbr[title]{-webkit-text-decoration:underline dotted;text-decoration:underline dotted;cursor:help;border-bottom:0}.btn:focus,.btn:hover,a{text-decoration:none}.btn:not(:disabled):not(.disabled),.navbar-toggler:not(:disabled):not(.disabled),.page-link:not(:disabled):not(.disabled),summary{cursor:pointer}address{font-style:normal}ol ol,ol ul,ul ol,ul ul{margin-bottom:0}dt{font-weight:500}dd{margin-left:0}blockquote,figure{margin:0 0 1rem}dfn{font-style:italic}b,strong{font-weight:bolder}small{font-size:80%}sub,sup{position:relative;font-size:75%;line-height:0}sub{bottom:-.25em}sup{top:-.5em}a{background-color:transparent;-webkit-text-decoration-skip:objects}a:not([href]):not([tabindex]),a:not([href]):not([tabindex]):focus,a:not([href]):not([tabindex]):hover{color:inherit;text-decoration:none}a:not([href]):not([tabindex]):focus{outline:0}code,kbd,pre,samp{font-size:1em}pre{-ms-overflow-style:scrollbar}img{vertical-align:middle;border-style:none}svg:not(:root){overflow:hidden}table{border-collapse:collapse}caption{padding-top:.75rem;padding-bottom:.75rem;color:#6c757d;caption-side:bottom}th{text-align:inherit}button{border-radius:0}button,input,optgroup,select,textarea{margin:0;font-family:inherit;font-size:inherit;line-height:inherit}button,input{overflow:visible}button,select{text-transform:none}[type=reset],[type=submit],button,html [type=button]{-webkit-appearance:button}[type=button]::-moz-focus-inner,[type=reset]::-moz-focus-inner,[type=submit]::-moz-focus-inner,button::-moz-focus-inner{padding:0;border-style:none}input[type=radio],input[type=checkbox]{box-sizing:border-box;padding:0}input[type=time],input[type=datetime-local],input[type=month],input[type=date]{-webkit-appearance:listbox}fieldset{min-width:0;padding:0;margin:0;border:0}legend{width:100%;max-width:100%;padding:0;margin-bottom:.5rem;font-size:1.5rem;color:inherit;white-space:normal}.badge,.btn,.dropdown-header,.dropdown-item,.input-group-text,.navbar-brand{white-space:nowrap}[type=number]::-webkit-inner-spin-button,[type=number]::-webkit-outer-spin-button{height:auto}[type=search]{outline-offset:-2px;-webkit-appearance:none}[type=search]::-webkit-search-cancel-button,[type=search]::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{font:inherit;-webkit-appearance:button}.display-1,.display-2,.display-3,.display-4{line-height:1.2}summary{display:list-item}template{display:none}[hidden]{display:none!important}.h1,.h2,.h3,.h4,.h5,.h6,h1,h2,h3,h4,h5,h6{margin-bottom:.5rem;font-family:inherit;font-weight:400;line-height:1.2;color:inherit}.blockquote,hr{margin-bottom:1rem}.display-1,.display-2,.display-3,.display-4,.lead{font-weight:300}.lead{font-size:1.25rem}.display-1{font-size:7rem}.display-2{font-size:3.5rem}.display-3{font-size:2.8125rem}.display-4{font-size:2.125rem}hr{box-sizing:content-box;height:0;overflow:visible;margin-top:1rem;border:0;border-top:1px solid rgba(0,0,0,.1)}.img-fluid,.img-thumbnail{max-width:100%;height:auto}.small,small{font-size:80%;font-weight:400}.mark,mark{padding:.2em;background-color:#fcf8e3}.list-inline,.list-unstyled{padding-left:0;list-style:none}.list-inline-item{display:inline-block}.list-inline-item:not(:last-child){margin-right:.5rem}.initialism{font-size:90%;text-transform:uppercase}.blockquote{font-size:1.25rem}.blockquote-footer{display:block;font-size:80%;color:#6c757d}.blockquote-footer::before{content:"\2014 \00A0"}.img-thumbnail{padding:.25rem;background-color:#fafafa;border:1px solid #dee2e6;box-shadow:0 1px 2px rgba(0,0,0,.075)}.figure{display:inline-block}.figure-img{margin-bottom:.5rem;line-height:1}.figure-caption{font-size:90%;color:#6c757d}code,kbd{font-size:87.5%}a>code,pre code{color:inherit}code,kbd,pre,samp{font-family:SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace}code{color:#e91e63;word-break:break-word}kbd{padding:.2rem .4rem;color:#fff;background-color:#212529;border-radius:.2rem;box-shadow:inset 0 -.1rem 0 rgba(0,0,0,.25)}kbd kbd{padding:0;font-size:100%;font-weight:500;box-shadow:none}';

$emailBody .= '.container,.container-fluid{padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto;width:100%}pre{display:block;font-size:87.5%;color:#212529}pre code{font-size:inherit;word-break:normal}.pre-scrollable{max-height:340px;overflow-y:scroll}@media (min-width:576px){.container{max-width:540px}}@media (min-width:768px){.container{max-width:720px}}@media (min-width:992px){.container{max-width:960px}}@media (min-width:1200px){.container{max-width:1140px}}.row{display:flex;flex-wrap:wrap;margin-right:-15px;margin-left:-15px}.no-gutters{margin-right:0;margin-left:0}.no-gutters>.col,.no-gutters>[class*=col-]{padding-right:0;padding-left:0}.col,.col-1,.col-10,.col-11,.col-12,.col-2,.col-3,.col-4,.col-5,.col-6,.col-7,.col-8,.col-9,.col-auto,.col-lg,.col-lg-1,.col-lg-10,.col-lg-11,.col-lg-12,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-5,.col-lg-6,.col-lg-7,.col-lg-8,.col-lg-9,.col-lg-auto,.col-md,.col-md-1,.col-md-10,.col-md-11,.col-md-12,.col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7,.col-md-8,.col-md-9,.col-md-auto,.col-sm,.col-sm-1,.col-sm-10,.col-sm-11,.col-sm-12,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-5,.col-sm-6,.col-sm-7,.col-sm-8,.col-sm-9,.col-sm-auto,.col-xl,.col-xl-1,.col-xl-10,.col-xl-11,.col-xl-12,.col-xl-2,.col-xl-3,.col-xl-4,.col-xl-5,.col-xl-6,.col-xl-7,.col-xl-8,.col-xl-9,.col-xl-auto{position:relative;width:100%;min-height:1px;padding-right:15px;padding-left:15px}.col{flex-basis:0;flex-grow:1;max-width:100%}';

$emailBody .= '.col-auto{flex:0 0 auto;width:auto;max-width:none}.col-1{flex:0 0 8.33333%;max-width:8.33333%}.col-2{flex:0 0 16.66667%;max-width:16.66667%}.col-3{flex:0 0 25%;max-width:25%}.col-4{flex:0 0 33.33333%;max-width:33.33333%}.col-5{flex:0 0 41.66667%;max-width:41.66667%}.col-6{flex:0 0 50%;max-width:50%}.col-7{flex:0 0 58.33333%;max-width:58.33333%}.col-8{flex:0 0 66.66667%;max-width:66.66667%}.col-9{flex:0 0 75%;max-width:75%}.col-10{flex:0 0 83.33333%;max-width:83.33333%}.col-11{flex:0 0 91.66667%;max-width:91.66667%}.col-12{flex:0 0 100%;max-width:100%}.order-first{order:-1}.order-last{order:13}.order-0{order:0}.order-1{order:1}.order-2{order:2}.order-3{order:3}.order-4{order:4}.order-5{order:5}.order-6{order:6}.order-7{order:7}.order-8{order:8}.order-9{order:9}.order-10{order:10}.order-11{order:11}.order-12{order:12}.offset-1{margin-left:8.33333%}.offset-2{margin-left:16.66667%}.offset-3{margin-left:25%}.offset-4{margin-left:33.33333%}.offset-5{margin-left:41.66667%}.offset-6{margin-left:50%}.offset-7{margin-left:58.33333%}.offset-8{margin-left:66.66667%}.offset-9{margin-left:75%}.offset-10{margin-left:83.33333%}.offset-11{margin-left:91.66667%}@media (min-width:576px){.col-sm{flex-basis:0;flex-grow:1;max-width:100%}.col-sm-auto{flex:0 0 auto;width:auto;max-width:none}.col-sm-1{flex:0 0 8.33333%;max-width:8.33333%}.col-sm-2{flex:0 0 16.66667%;max-width:16.66667%}.col-sm-3{flex:0 0 25%;max-width:25%}.col-sm-4{flex:0 0 33.33333%;max-width:33.33333%}.col-sm-5{flex:0 0 41.66667%;max-width:41.66667%}.col-sm-6{flex:0 0 50%;max-width:50%}.col-sm-7{flex:0 0 58.33333%;max-width:58.33333%}.col-sm-8{flex:0 0 66.66667%;max-width:66.66667%}.col-sm-9{flex:0 0 75%;max-width:75%}.col-sm-10{flex:0 0 83.33333%;max-width:83.33333%}.col-sm-11{flex:0 0 91.66667%;max-width:91.66667%}.col-sm-12{flex:0 0 100%;max-width:100%}.order-sm-first{order:-1}.order-sm-last{order:13}.order-sm-0{order:0}.order-sm-1{order:1}.order-sm-2{order:2}.order-sm-3{order:3}.order-sm-4{order:4}.order-sm-5{order:5}.order-sm-6{order:6}.order-sm-7{order:7}.order-sm-8{order:8}.order-sm-9{order:9}.order-sm-10{order:10}.order-sm-11{order:11}.order-sm-12{order:12}.offset-sm-0{margin-left:0}.offset-sm-1{margin-left:8.33333%}.offset-sm-2{margin-left:16.66667%}.offset-sm-3{margin-left:25%}.offset-sm-4{margin-left:33.33333%}.offset-sm-5{margin-left:41.66667%}.offset-sm-6{margin-left:50%}.offset-sm-7{margin-left:58.33333%}.offset-sm-8{margin-left:66.66667%}.offset-sm-9{margin-left:75%}.offset-sm-10{margin-left:83.33333%}.offset-sm-11{margin-left:91.66667%}}@media (min-width:768px){.col-md{flex-basis:0;flex-grow:1;max-width:100%}.col-md-auto{flex:0 0 auto;width:auto;max-width:none}.col-md-1{flex:0 0 8.33333%;max-width:8.33333%}.col-md-2{flex:0 0 16.66667%;max-width:16.66667%}.col-md-3{flex:0 0 25%;max-width:25%}.col-md-4{flex:0 0 33.33333%;max-width:33.33333%}.col-md-5{flex:0 0 41.66667%;max-width:41.66667%}.col-md-6{flex:0 0 50%;max-width:50%}.col-md-7{flex:0 0 58.33333%;max-width:58.33333%}.col-md-8{flex:0 0 66.66667%;max-width:66.66667%}.col-md-9{flex:0 0 75%;max-width:75%}.col-md-10{flex:0 0 83.33333%;max-width:83.33333%}.col-md-11{flex:0 0 91.66667%;max-width:91.66667%}.col-md-12{flex:0 0 100%;max-width:100%}.order-md-first{order:-1}.order-md-last{order:13}.order-md-0{order:0}.order-md-1{order:1}.order-md-2{order:2}.order-md-3{order:3}.order-md-4{order:4}.order-md-5{order:5}.order-md-6{order:6}.order-md-7{order:7}.order-md-8{order:8}.order-md-9{order:9}.order-md-10{order:10}.order-md-11{order:11}.order-md-12{order:12}.offset-md-0{margin-left:0}.offset-md-1{margin-left:8.33333%}.offset-md-2{margin-left:16.66667%}.offset-md-3{margin-left:25%}.offset-md-4{margin-left:33.33333%}.offset-md-5{margin-left:41.66667%}.offset-md-6{margin-left:50%}.offset-md-7{margin-left:58.33333%}.offset-md-8{margin-left:66.66667%}.offset-md-9{margin-left:75%}.offset-md-10{margin-left:83.33333%}.offset-md-11{margin-left:91.66667%}}@media (min-width:992px){.col-lg{flex-basis:0;flex-grow:1;max-width:100%}.col-lg-auto{flex:0 0 auto;width:auto;max-width:none}.col-lg-1{flex:0 0 8.33333%;max-width:8.33333%}.col-lg-2{flex:0 0 16.66667%;max-width:16.66667%}.col-lg-3{flex:0 0 25%;max-width:25%}.col-lg-4{flex:0 0 33.33333%;max-width:33.33333%}.col-lg-5{flex:0 0 41.66667%;max-width:41.66667%}.col-lg-6{flex:0 0 50%;max-width:50%}.col-lg-7{flex:0 0 58.33333%;max-width:58.33333%}.col-lg-8{flex:0 0 66.66667%;max-width:66.66667%}.col-lg-9{flex:0 0 75%;max-width:75%}.col-lg-10{flex:0 0 83.33333%;max-width:83.33333%}.col-lg-11{flex:0 0 91.66667%;max-width:91.66667%}.col-lg-12{flex:0 0 100%;max-width:100%}.order-lg-first{order:-1}.order-lg-last{order:13}.order-lg-0{order:0}.order-lg-1{order:1}.order-lg-2{order:2}.order-lg-3{order:3}.order-lg-4{order:4}.order-lg-5{order:5}.order-lg-6{order:6}.order-lg-7{order:7}.order-lg-8{order:8}.order-lg-9{order:9}.order-lg-10{order:10}.order-lg-11{order:11}.order-lg-12{order:12}';

$emailBody .= '.container,.navbar>.container-fluid{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between}.navbar-brand{display:inline-block;padding-top:.3125rem;padding-bottom:.3125rem;margin-right:1rem;font-size:1.25rem;line-height:inherit}.navbar-brand:focus,.navbar-brand:hover{text-decoration:none}.navbar-nav{display:flex;flex-direction:column;padding-left:0;margin-bottom:0}.navbar-nav .nav-link{padding-right:0;padding-left:0}.navbar-nav .dropdown-menu{position:static;float:none}.navbar-text{display:inline-block;padding-top:.5rem;padding-bottom:.5rem}.navbar-collapse{flex-basis:100%;flex-grow:1;align-items:center}.navbar-toggler{padding:.25rem .75rem;font-size:1.25rem;line-height:1;background-color:transparent;border:1px solid transparent;border-radius:.25rem}.navbar-toggler:focus,.navbar-toggler:hover{text-decoration:none}.navbar-toggler-icon{display:inline-block;width:1.5em;height:1.5em;vertical-align:middle;content:"";background:center center no-repeat;background-size:100% 100%}@media (max-width:575.98px){.navbar-expand-sm>.container,.navbar-expand-sm>.container-fluid{padding-right:0;padding-left:0}}@media (min-width:576px){.navbar-expand-sm{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand-sm .navbar-nav{flex-direction:row}.navbar-expand-sm .navbar-nav .dropdown-menu{position:absolute}.navbar-expand-sm .navbar-nav .dropdown-menu-right{right:0;left:auto}.navbar-expand-sm .navbar-nav .nav-link{padding-right:.5rem;padding-left:.5rem}.navbar-expand-sm>.container,.navbar-expand-sm>.container-fluid{flex-wrap:nowrap}.navbar-expand-sm .navbar-collapse{display:flex!important;flex-basis:auto}.navbar-expand-sm .navbar-toggler{display:none}.navbar-expand-sm .dropup .dropdown-menu{top:auto;bottom:100%}}@media (max-width:767.98px){.navbar-expand-md>.container,.navbar-expand-md>.container-fluid{padding-right:0;padding-left:0}}@media (min-width:768px){.navbar-expand-md{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand-md .navbar-nav{flex-direction:row}.navbar-expand-md .navbar-nav .dropdown-menu{position:absolute}.navbar-expand-md .navbar-nav .dropdown-menu-right{right:0;left:auto}.navbar-expand-md .navbar-nav .nav-link{padding-right:.5rem;padding-left:.5rem}.navbar-expand-md>.container,.navbar-expand-md>.container-fluid{flex-wrap:nowrap}.navbar-expand-md .navbar-collapse{display:flex!important;flex-basis:auto}.navbar-expand-md .navbar-toggler{display:none}.navbar-expand-md .dropup .dropdown-menu{top:auto;bottom:100%}}@media (max-width:991.98px){.navbar-expand-lg>.container,.navbar-expand-lg>.container-fluid{padding-right:0;padding-left:0}}@media (min-width:992px){.navbar-expand-lg{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand-lg .navbar-nav{flex-direction:row}.navbar-expand-lg .navbar-nav .dropdown-menu{position:absolute}.navbar-expand-lg .navbar-nav .dropdown-menu-right{right:0;left:auto}.navbar-expand-lg .navbar-nav .nav-link{padding-right:.5rem;padding-left:.5rem}.navbar-expand-lg>.container,.navbar-expand-lg>.container-fluid{flex-wrap:nowrap}.navbar-expand-lg .navbar-collapse{display:flex!important;flex-basis:auto}.navbar-expand-lg .navbar-toggler{display:none}.navbar-expand-lg .dropup .dropdown-menu{top:auto;bottom:100%}}@media (max-width:1199.98px){.navbar-expand-xl>.container,.navbar-expand-xl>.container-fluid{padding-right:0;padding-left:0}}@media (min-width:1200px){.navbar-expand-xl{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand-xl .navbar-nav{flex-direction:row}.navbar-expand-xl .navbar-nav .dropdown-menu{position:absolute}.navbar-expand-xl .navbar-nav .dropdown-menu-right{right:0;left:auto}.navbar-expand-xl .navbar-nav .nav-link{padding-right:.5rem;padding-left:.5rem}.navbar-expand-xl>.container,.navbar-expand-xl>.container-fluid{flex-wrap:nowrap}.navbar-expand-xl .navbar-collapse{display:flex!important;flex-basis:auto}.navbar-expand-xl .navbar-toggler{display:none}.navbar-expand-xl .dropup .dropdown-menu{top:auto;bottom:100%}}.navbar-expand{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand>.container,.navbar-expand>.container-fluid{padding-right:0;padding-left:0}.navbar-expand .navbar-nav{flex-direction:row}.navbar-expand .navbar-nav .dropdown-menu{position:absolute}.navbar-expand .navbar-nav .dropdown-menu-right{right:0;left:auto}.navbar-expand .navbar-nav .nav-link{padding-right:.5rem;padding-left:.5rem}.navbar-expand>.container,.navbar-expand>.container-fluid{flex-wrap:nowrap}.navbar-expand .navbar-collapse{display:flex!important;flex-basis:auto}.navbar-expand .navbar-toggler{display:none}.navbar-expand .dropup .dropdown-menu{top:auto;bottom:100%}.navbar-light .navbar-brand,.navbar-light .navbar-brand:focus,.navbar-light .navbar-brand:hover{color:rgba(0,0,0,.9)}.navbar-light .navbar-nav .nav-link{color:rgba(0,0,0,.5)}.navbar-light .navbar-nav .nav-link:focus,.navbar-light .navbar-nav .nav-link:hover{color:rgba(0,0,0,.7)}.navbar-light .navbar-nav .nav-link.disabled{color:rgba(0,0,0,.3)}.navbar-light .navbar-nav .active>.nav-link,.navbar-light .navbar-nav .nav-link.active,.navbar-light .navbar-nav .nav-link.show,.navbar-light .navbar-nav .show>.nav-link{color:rgba(0,0,0,.9)}.navbar-light .navbar-toggler{color:rgba(0,0,0,.5);border-color:rgba(0,0,0,.1)}.navbar-light .navbar-light .navbar-text{color:rgba(0,0,0,.5)}.navbar-light .navbar-text a,.navbar-light .navbar-text a:focus,.navbar-light .navbar-text a:hover{color:rgba(0,0,0,.9)}.navbar-dark .navbar-brand,.navbar-dark .navbar-brand:focus,.navbar-dark .navbar-brand:hover{color:#fff}.navbar-dark .navbar-nav .nav-link{color:rgba(255,255,255,.5)}.navbar-dark .navbar-nav .nav-link:focus,.navbar-dark .navbar-nav .nav-link:hover{color:rgba(255,255,255,.75)}.navbar-dark .navbar-nav .nav-link.disabled{color:rgba(255,255,255,.25)}.navbar-dark .navbar-nav .active>.nav-link,.navbar-dark .navbar-nav .nav-link.active,.navbar-dark .navbar-nav .nav-link.show,.navbar-dark .navbar-nav .show>.nav-link{color:#fff}.navbar-dark .navbar-toggler{color:rgba(255,255,255,.5);border-color:rgba(255,255,255,.1)}.navbar-dark .navbar-dark .navbar-text{color:rgba(255,255,255,.5)}.navbar-dark .navbar-text a,.navbar-dark .navbar-text a:focus,.navbar-dark .navbar-text a:hover{color:#fff}';

$emailBody .= '.card{position:relative;display:flex;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:border-box}.card>hr{margin-right:0;margin-left:0}.card>.list-group:first-child .list-group-item:first-child{border-top-left-radius:.25rem;border-top-right-radius:.25rem}.card>.list-group:last-child .list-group-item:last-child{border-bottom-right-radius:.25rem;border-bottom-left-radius:.25rem}.card-body{flex:1 1 auto;padding:1.25rem}.card-footer,.card-header{padding:.75rem 1.25rem;background-color:#fff}.card-title{margin-bottom:.75rem}.card-header,.card-subtitle,.card-text:last-child{margin-bottom:0}.card-subtitle{margin-top:-.375rem}.card-link:hover{text-decoration:none}.card-link+.card-link{margin-left:1.25rem}.card-header-pills,.card-header-tabs{margin-right:-.625rem;margin-left:-.625rem}.card-header{border-bottom:1px solid #eee}.card-header:first-child{border-radius:calc(.25rem - 1px) calc(.25rem - 1px) 0 0}.card-header+.list-group .list-group-item:first-child{border-top:0}.card-footer{border-top:1px solid #eee}.card-footer:last-child{border-radius:0 0 calc(.25rem - 1px) calc(.25rem - 1px)}.card-header-tabs{margin-bottom:-.75rem;border-bottom:0}.card-img-overlay{position:absolute;top:0;right:0;bottom:0;left:0;padding:1.25rem}.card-img{width:100%;border-radius:calc(.25rem - 1px)}.card-img-top{width:100%;border-top-left-radius:calc(.25rem - 1px);border-top-right-radius:calc(.25rem - 1px)}.card-img-bottom{width:100%;border-bottom-right-radius:calc(.25rem - 1px);border-bottom-left-radius:calc(.25rem - 1px)}.card-deck{display:flex;flex-direction:column}.card-deck .card{margin-bottom:15px}@media (min-width:576px){.card-deck{flex-flow:row wrap;margin-right:-15px;margin-left:-15px}.card-deck .card{display:flex;flex:1 0 0%;flex-direction:column;margin-right:15px;margin-bottom:0;margin-left:15px}}.card-group{display:flex;flex-direction:column}.card-group>.card{margin-bottom:15px}@media (min-width:576px){.card-group{flex-flow:row wrap}.card-group>.card{flex:1 0 0%;margin-bottom:0}.card-group>.card+.card{margin-left:0;border-left:0}.card-group>.card:first-child{border-top-right-radius:0;border-bottom-right-radius:0}.card-group>.card:first-child .card-header,.card-group>.card:first-child .card-img-top{border-top-right-radius:0}.card-group>.card:first-child .card-footer,.card-group>.card:first-child .card-img-bottom{border-bottom-right-radius:0}.card-group>.card:last-child{border-top-left-radius:0;border-bottom-left-radius:0}.card-group>.card:last-child .card-header,.card-group>.card:last-child .card-img-top{border-top-left-radius:0}.card-group>.card:last-child .card-footer,.card-group>.card:last-child .card-img-bottom{border-bottom-left-radius:0}.card-group>.card:only-child{border-radius:.25rem}.card-group>.card:only-child .card-header,.card-group>.card:only-child .card-img-top{border-top-left-radius:.25rem;border-top-right-radius:.25rem}.card-group>.card:only-child .card-footer,.card-group>.card:only-child .card-img-bottom{border-bottom-right-radius:.25rem;border-bottom-left-radius:.25rem}.card-group>.card:not(:first-child):not(:last-child):not(:only-child),.card-group>.card:not(:first-child):not(:last-child):not(:only-child) .card-footer,.card-group>.card:not(:first-child):not(:last-child):not(:only-child) .card-header,.card-group>.card:not(:first-child):not(:last-child):not(:only-child) .card-img-bottom,.card-group>.card:not(:first-child):not(:last-child):not(:only-child) .card-img-top{border-radius:0}.card-columns{-webkit-column-count:3;column-count:3;-webkit-column-gap:1.25rem;column-gap:1.25rem}.card-columns .card{display:inline-block;width:100%}}.breadcrumb,.pagination{border-radius:.25rem;list-style:none}.card-columns .card{margin-bottom:.75rem}.breadcrumb{display:flex;flex-wrap:wrap;padding:.75rem 1rem;margin-bottom:1rem;background-color:#e9ecef}';

$emailBody .= '</style>';
$emailBody .= '</head>';
$emailBody .= '<body style="background: #303030;">';
$emailBody .= '<div class="container" style="padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto;width:80%"><div class="container-fluid" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between"><div class="card col-md-12 col-sm-12 col-lg-6" style="background: #303030; position:relative;display:flex;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:border-box; width; flex:0 0 100%;max-width:70%; ">';
$emailBody 		.= '<img src="'.$COMPANY_SITE. 'assets/img/email/happy-bday.png" width="100%" ><div class="card-body text-center" style="background: white; flex:1 1 auto;padding:1.25rem">';
$emailBody .= '<h3>Happy Birthday!</h3>';
$emailBody .= '<h5>';
$emailBody .= 'A heartily wish from all of us for a wonderful birthday ahead with peace and prosperity, hope you spend the most wonderful birthday this year. May all your trades be profitable, your business keep growing well. Let success always accompany you, and only reliable people be your companions.</h5><hr>';

//Additional Information / promotion
/*
$emailBody .= '<h5 style="color:#1E90FF">';
$emailBody .= 	'<p>In our turn to present the best gift to you, we inform you that at this time the ';
$emailBody .= 	'<b>sponsor bonus, pass-up and matching bonus</b> has been increased by 100% for you.</p>';
$emailBody .= '</h5>';
*/
//-----------------------------------------------------------------------------------

$emailBody .= '<h5>We believe that our further cooperation will be effective and fruitful. Hopefully, you will celebrate your future birthdays with VisionEA.</h5>';
$emailBody .= '<h5>We thank you and appreciate your trust for choosing us as your forex trading partner and business partner. We promise to keep give our best services to you.</h5>';
$emailBody .= '<h5>Happy birthday once again! We wish you all the best and hope for broad and productive cooperation.</h5>';
$emailBody .= '<div class="text-left" style="padding-top: 50px"><div class="row"><div class="col-sm-12 col-md-12 col-lg-7" style="flex:0 0 100%;max-width:70%">Sincerely,<br>Client Relations Department</div>';
$emailBody .= '<div class="col-md-5" style="text-align: right;"><img src="'.$COMPANY_SITE. 'assets/img/email/bday-present.png" width="50%" ></div>';
$emailBody .= '</div></div></div>';
$emailBody .= '<div class="small" style="color: white;">If you have any questions, you can email: '.$GLOBALS["EMAIL_SUPPORT"].'</div><br>
			</div></div></div>';
$emailBody .= '</body></html>';

	return ($emailBody);
}


function fGetInfoMember($username, $status){
	global $conn;
	$sql = "SELECT * FROM dtMember WHERE mbrUsername='" . $username . "' AND mbrStID='" . $status . "'";
	if ($query = $conn->query($sql)){
		if ($row = $query->fetch_assoc()){
			$arrData  = array (	"mbrUsername"	=> $row['mbrUsername'],
								"mbrSponsor"	=> $row['mbrSponsor'],
								"mbrUpline"		=> $row['mbrUpline'],
								"mbrPos"		=> $row['mbrPos'],
								"mbrFirstName"	=> $row['mbrFirstName'],
								"mbrEmail"		=> $row['mbrEmail'],
								"mbrMobileCode"	=> $row['mbrMobileCode'],
								"mbrMobile"		=> $row['mbrMobile']
							);
			return ($arrData);
		}
	}
	$arrData = array('status' => 'err');
	return ($arrData);
	
}


function fInsert($table, $arrData, $conn){
	$sql = "";
	$result = false;
	if (isset($table) && isset($arrData) && $table != ""){
		$sqlHeader 	= "INSERT INTO " . $table . " (";
		$sqlField	= "";
		$sqlValue	= ") VALUES (";
		$sqlFooter 	= ")";
		
		foreach($arrData as $key => $value){
			//echo ("<p>" . $value["val"] . ": " . $value["db"] . "</p>");
			$sqlField .= $value["db"] . ", ";
			
			if ($value["val"] == "CURRENT_TIME()" || $value["val"] == "NOW()"){
				//$sqlValue .= "NOW(), ";
				$sqlValue .= "'" . $GLOBALS['CURRENT_TIME'] . "', ";
			}else{
				$sqlValue .= "'" . $value["val"] . "', ";
			}
		}
		
		if ($sqlField != ""){
			$sqlField = substr($sqlField, 0, -2);
			$sqlValue = substr($sqlValue, 0, -2);
			$sql	= $sqlHeader . $sqlField . $sqlValue . $sqlFooter;

			//echo $sql; die();
		}
	}

	if ($conn->query($sql) === true) {
		$result = true; //echo "New record created successfully";
	}else{
		$result = false; //echo "Error: " . $sql . "<br>" . $conn->error; die();	
	}
	
	if ($result == false) fSendToAdmin("fInsert", "inc_functions.php", $sql);
	return ($result);
}


function fDeleteRecord($table, $arrDataQuery, $conn){
	$sql = "DELETE FROM " . $table;// . " WHERE " . $field . " ='" . $id . "'";
	
	$sqlWhere = "";
	foreach($arrDataQuery as $key => $value){
		if ($sqlWhere == "") $sqlWhere .= " WHERE "; 
		else $sqlWhere .= " AND ";

		if ($value == "CURRENT_TIME()" || $value == "NOW()"){
			$sqlWhere .= " " . $key . " = '" . $GLOBALS['CURRENT_TIME'] . "'";
		}else{
			$sqlWhere .= " " . $key . " = '" . $value . "'";
		}
	}
	$sql .= $sqlWhere;
	
	if ($conn->query($sql) === TRUE){
		$result = true;
	}else{
		//echo "Error deleting record: " . $conn->error;
		$result = false;
	}

	if ($result == false) fSendToAdmin("fDeleteRecord", "inc_functions.php", $sql);
	return ($result);
}

function fUpdateRecord($table, $arrData, $arrDataQuery, $conn){
	$sql = "UPDATE " . $table . " SET ";
	
	$sqlValue = "";
	foreach($arrData as $key => $value){
		if ($sqlValue != "") $sqlValue .= ", ";
		

		if ($value == "CURRENT_TIME()" || $value == "NOW()"){
			$sqlValue .= " " . $key . " = '" . $GLOBALS['CURRENT_TIME'] . "'";
		}else{
			$sqlValue .= " " . $key . " = '" . $value . "'";
		}
	}
	
	$sqlWhere = "";
	foreach($arrDataQuery as $key => $value){
		if ($sqlWhere == "") $sqlWhere .= " WHERE "; 
		else $sqlWhere .= " AND ";
		
		if ($value == "CURRENT_TIME()" || $value == "NOW()"){
			$sqlWhere .= " " . $key . " = '" . $GLOBALS['CURRENT_TIME'] . "'";
		}else{
			$sqlWhere .= " " . $key . " = '" . $value . "'";
		}

	}
	
	$sql .= $sqlValue . $sqlWhere;
	
	if ($conn->query($sql) === TRUE){
		$result = true;
	}else{
		//echo "Error deleting record: " . $conn->error;
		$result = false;
	}
	//echo $sql; die();
	if ($result == false) fSendToAdmin("fUpdateRecord", "inc_functions.php", $sql);
	return ($result);
}


//Return data JSON
function fSendStatusMessage($status, $message){
	$arrData = array("status"=>$status, "message"=>$message);
	$dataJSON = json_encode($arrData);
	return ($dataJSON);
}

//used in networkTree.php
function fGetDataMember($conn, $username, $headNode){
	//check Genealogy Tree, username must under the network tree.
	if (fCheckGenealogyTree($username, $headNode, $conn)){
		//$sql = "SELECT * FROM dtMember WHERE mbrUsername='" . $username . "'";
		$sql = "SELECT m.*, t.trPacID FROM dtMember m ";
		$sql .= " INNER JOIN ( SELECT trPacID, trUsername FROM Transaction t ";
		$sql .= " WHERE trID = (SELECT trID FROM Transaction WHERE trUsername=t.trUsername ORDER BY trID DESC LIMIT 1) ) t ";
		$sql .= " ON mbrUsername = t.trUsername ";
		$sql .= " WHERE mbrUsername='".$username ."'";
		//echo $sql;
		$query = $conn->query($sql);
		if ($query->num_rows > 0) {
			// output data of each row
			if($row = $query->fetch_assoc()) {
				$arrData = array (
							"username" => $row["mbrUsername"],
							"name" => $row["mbrFirstName"],
							"upline" => $row["mbrUpline"],
							"pos" => $row["mbrPos"],
							"package" => $row["trPacID"]
							);
				return $arrData;
			}
		} 
	}
	return ("");
}


//Return 0: no data; $arrData : if data exist
function fGetDataMemberByUpline_Pos($conn, $upline, $pos){
	$sql = "SELECT mbrUsername, mbrFirstName, mbrUpline, mbrPos, trPacID FROM dtMember ";
	$sql .= " INNER JOIN ( SELECT trPacID, trUsername FROM Transaction t ";
	$sql .= " WHERE trID = (SELECT trID FROM Transaction WHERE trUsername=t.trUsername ORDER BY trID DESC LIMIT 1) ) t ";
	$sql .= " ON mbrUsername = t.trUsername ";
	$sql .= " WHERE mbrUpline='" . $upline . "' AND mbrPos='" . $pos . "' AND mbrSponsor<>mbrUsername";
	//echo $sql;
	$query = $conn->query($sql);
	if ($row = $query->fetch_assoc()){
		$arrData = array (
						"username" => $row["mbrUsername"],
						"name" => $row["mbrFirstName"],
						"upline" => $row["mbrUpline"],
						"pos" => $row["mbrPos"],
						"package" => $row["trPacID"]
						);
		return ($arrData);
	}
	return ("");
}

function fGetDataSponsor($conn, $username){
	$sql = "SELECT mbrID, mbrUsername, mbrFirstName, mbrEmail, mbrSponsor, mbrUpline FROM dtMember "
			. " WHERE mbrUsername = ("
			. " SELECT mbrSponsor FROM dtMember WHERE mbrUsername='" . $username . "' )";
	$query = $conn->query($sql);
	if ($query->num_rows > 0) {
		// output data of each row
		if($row = $query->fetch_assoc()) {
			$arrData = array (
						"username" => $row["mbrUsername"],
						"name" => $row["mbrFirstName"],
						"email" => $row["mbrEmail"],
						"sponsor" => $row["mbrSponsor"],
						"upline" => $row["mbrUpline"]
						);
			$dataJSON = json_encode($arrData);
			
		}
	} else {
		$dataJSON = fSendStatusMessage("failed", "No Record");
	}
	return ($dataJSON);
}

function fGetDataPackage($conn, $username){
	$sql = " SELECT t.*, p.pacID, p.pacName, p.pacMatchingGen FROM Transaction t "
      . " INNER JOIN msPackage p ON p.pacID = t.trPacID "
      . " WHERE t.trID = (SELECT trID FROM Transaction where trUsername='".$username."' ORDER BY trDate DESC LIMIT 1) ";
    $query = $conn->query($sql);
	if ($query->num_rows > 0) {
		// output data of each row
		if($row = $query->fetch_assoc()) {
			$arrData = array (
						"pacID" 	=> $row["pacID"],
						"pacName" 	=> $row["pacName"],
						"trStatus" 	=> $row["trStatus"],
						"trDate"	=> $row["trDate"],
						"pacMatchingGen" => $row["pacMatchingGen"]
						);
			$dataJSON = json_encode($arrData);
		}
	} else {
		$dataJSON = fSendStatusMessage("failed", "No Record");
	}
	return ($dataJSON);
}


function fGetBonus($bnsCategory, $pacMember, $pacSponsor, $conn){
	$bonus = 0;
	$sql	= "SELECT tyBnsPrice FROM msTypeBonus WHERE tyBnsCategory='" . $bnsCategory . "' AND tyBnsPacSponsor='" . $pacSponsor . "' AND tyBnsPacMember='" . $pacMember . "'";
	//echo "<br><br>" . $sql;
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		if ($row = $query->fetch_assoc()){
			$bonus	= $row["tyBnsPrice"];
		}
	}
	return ($bonus);
}


//Used in activateMember.php
function fGetVIPUsername($username, $pacIDTarget, $conn){
	$vipUsername	= "";
	$username		= strtolower($username);
	$pacIDTarget	= strtolower($pacIDTarget);
		
	//$sql	= "SELECT up.mbrUsername, trPacID FROM dtMember m ";
	$sql	= "SELECT sp.mbrUsername, trPacID FROM dtMember m ";
	//$sql	.= " INNER JOIN dtMember up  on m.mbrUpline=up.mbrUsername";
	//$sql	.= " INNER JOIN Transaction on trUsername=up.mbrUsername";
	$sql	.= " INNER JOIN dtMember sp  on m.mbrSponsor=sp.mbrUsername";
	$sql	.= " INNER JOIN Transaction on trUsername=sp.mbrUsername";
	$sql	.= " WHERE m.mbrUsername = '" . $username . "'";
	$sql 	.= " AND m.mbrUsername != m.mbrSponsor"; //company node : username=sponsor
	$sql	.= " ORDER BY trDate DESC LIMIT 1"; 
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		if ($row = $query->fetch_assoc()){
			if (strtolower($row["trPacID"]) == $pacIDTarget){
				$vipUsername	= $row["mbrUsername"];;	
			}else{
				$vipUsername 	= fGetVIPUsername($row["mbrUsername"], $pacIDTarget, $conn);
			}
		}
	}
	return ($vipUsername);
}

//used in getData.php
function fCheckGenealogyTree($upline, $sponsorName, $conn){
	$isFound	= false;
	$upline		= strtolower($upline);
	$sponsorName	= strtolower($sponsorName);
	
	if ($upline == $sponsorName){
		$isFound = true;	
	}else{
		$sql	= "SELECT up.mbrUsername FROM dtMember m ";
		$sql	.= " INNER JOIN dtMember up  on m.mbrUpline=up.mbrUsername";
		$sql	.= " WHERE m.mbrUsername = '" . $upline . "'";
		$sql 	.= " AND m.mbrUsername != m.mbrSponsor"; //company node : username=sponsor
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			if ($row = $query->fetch_assoc()){
				if (strtolower($row["mbrUsername"]) == $sponsorName){
					$isFound	= true;	
				}/*else if ($row["mbrUsername"] == "VISIONEA"){
					$isFound = false;
				}*/else{
					//if ($row["mbrUsername"] == "corp"){
					//	$isFound = false;
					//}else{
						$isFound = fCheckGenealogyTree($row["mbrUsername"], $sponsorName, $conn);
					//}
				}
			}
		}else{
			$isFound = false;
		} 
	}
	return ($isFound);
}


function fCheckSponsorGenealogyTree($sponsor, $headNode, $conn){
	$isFound	= false;
	$sponsor	= strtolower($sponsor);
	$headNode	= strtolower($headNode);
	
	if ($sponsor == $headNode){
		$isFound = true;	
	}else{
		$sql	= "SELECT m.mbrSponsor FROM dtMember m ";
		//$sql	.= " INNER JOIN dtMember sp  on m.mbrSponsor=sp.mbrUsername";
		$sql	.= " WHERE m.mbrUsername = '" . $sponsor . "'";
		$sql 	.= " AND m.mbrUsername != m.mbrSponsor"; //company node : username=sponsor
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			if ($row = $query->fetch_assoc()){
				if (strtolower($row["mbrSponsor"]) == $headNode){
					$isFound	= true;	
				}/*else if ($row["mbrUsername"] == "VISIONEA"){
					$isFound = false;
				}*/else{
					//if ($row["mbrUsername"] == "corp"){
					//	$isFound = false;
					//}else{
						$isFound = fCheckSponsorGenealogyTree($row["mbrSponsor"], $headNode, $conn);
					//}
				}
			}
		}else{
			$isFound = false;
		} 
	}
	return ($isFound);
}

//used by getData.php <-- dsl.php
function fDirectDownline($conn, $sponsor, $gen){
	$sql = "SELECT mbrUsername, mbrFirstName FROM dtMember WHERE mbrSponsor='".$sponsor."' AND mbrUsername <> '".$sponsor."'";
	$sql .= " ORDER BY mbrDate ASC";
	$i = 0;
	$msgContent = $msgFooter = "";
	if ($query = $conn->query($sql)){
		$i++;
		$msgGen = 'Gen-'.$gen . ' : ';
		if ($query->num_rows > 0){
			while($row = $query->fetch_assoc()){
				//calculate turnover here....
				$username	= $row['mbrUsername'];
				$msgContent  .= '<tr><td style="vertical-align:top" width="60">'.$msgGen.'</td><td style="vertical-align:top">';
				$msgContent  .= '<a href="#" name="'.$username . '" title="'. $gen . '" onClick="var username = $(this).attr(\'name\');
				var gen = $(this).attr(\'title\');
				$.get(\'getData.php?q=getDirectDownline&sponsor=\' + username + \'&gen=\' + gen, function(data, success){
					$(\'#dsl\'+username).html(data);
				});
				
				">'.$username . '</a><div id="dsl'. $username. '"></div></td></tr>';
				$msgGen = ''; //1x for each gen
			}
			//$msgGen = '<table><tr><td style="vertical-align:top" width="20">Gen-' . $gen . ': </td><td style="vertical-align:top"></td></tr>';
			//$msgGen = '<table>';
			//$msgFooter = '</table>';
			$msg = $msgGen. $msgContent . $msgFooter;
		}else{
			$msg = $msgGen . "no record found";
		}
	}
	
	echo $msg;	
}


function fGetPayAcc($username, $conn){
	$payAcc = $payAccName = $payAccDesc = "";
	// $sql  = "SELECT payAcc, payAccName, ptDesc FROM dtPaymentAcc AS p";
	// $sql .= " INNER JOIN msPaymentType ON ptID = p.payPTID";
	// $sql .= " WHERE payMbrUsername = ";
	// $sql .= " (SELECT payMbrUsername FROM dtPaymentAcc ";
 // 	$sql .= " WHERE payMbrUsername= p.payMbrUsername AND payMbrUsername='".$username."' AND payStatus ='".$GLOBALS['DEF_STATUS_ACTIVE']."'";
 // 	$sql .= " ORDER BY payDate DESC LIMIT 1)";

	$sql  = "SELECT payAcc, payAccName, ptDesc FROM dtPaymentAcc AS p";
	$sql .= " INNER JOIN msPaymentType ON ptID = p.payPTID ";
	$sql .= " WHERE payMbrUsername='".$username."' AND payStatus ='".$GLOBALS['DEF_STATUS_ACTIVE']."'";

 	$query = $conn->query($sql);
 	if($row = $query->fetch_assoc()){
 		$payAcc 	= $row['payAcc'];
 		$payAccName = $row['payAccName'];
 		$payAccDesc = $row['ptDesc'];
 	}
 	$arrData = array (
 		"status" 		=> "success",
 		"payAcc" 		=> $payAcc,
 		"payAccName"	=> $payAccName,
 		"payAccDesc"	=> $payAccDesc
 	);
 	$dataJSON = json_encode($arrData);
 	return ($dataJSON);
}


function fCheckSecurityPassword($username, $securityPassword, $conn){
	$isValid = false;
	$sql = "SELECT * FROM trPIN INNER JOIN dtMember ON mbrUsername=pinMbrUsername ";
	$sql .= " WHERE pinMbrUsername= '". $username ."' AND pinStID='".$GLOBALS['DEF_STATUS_APPROVED']."' ORDER BY DATE(pinDate) DESC LIMIT 1";
	$query = $conn->query($sql);
	if ($row = $query->fetch_assoc()){
		//fPrint ($row['pinWord']);
		//fPrint (md5($securityPassword) );
		//fPrint ($securityPassword);
		if (strtolower($row['pinMbrUsername']) == $username && $row['pinWord'] == md5($securityPassword) && $row['pinStID'] == $GLOBALS['DEF_STATUS_APPROVED']){
			$isValid = true;
		}
	}
	//return ($sql);
	return ($isValid);
	
}

function fCheckSecurityPasswordBO($usernameBO, $securityPasswordBO, $conn){
	$isValid = false;
	$sql = "SELECT * FROM trPinBO INNER JOIN dtBackOffice ON boUsername=pinMbrUsernameBO ";
	$sql .= " WHERE pinMbrUsernameBO= '". $usernameBO ."' AND boStID='".$GLOBALS['DEF_STATUS_ACTIVE']."' ORDER BY DATE(pinDateBO) DESC LIMIT 1";
	$query = $conn->query($sql);
	if ($row = $query->fetch_assoc()){
		if (strtolower($row['pinMbrUsernameBO']) == $usernameBO && $row['pinWordBO'] == md5($securityPasswordBO)){
			$isValid = true;
		}
	}
	return ($isValid);
}

function fCheckEmail($username, $email, $conn){
	$isValid = false;
	$sql = "SELECT mbrEmail FROM dtMember WHERE mbrUsername= '". $username ."' AND mbrEmail='" . $email . "'";
	$query = $conn->query($sql);
	if ($query->num_rows>0){
		$isValid = true;
	}
	return ($isValid);
}


function fCheckONProgressWD($username, $conn){
	$isValid = false;
	$sql = "SELECT wdMbrUsername FROM dtWDFund WHERE wdMbrUsername= '". $username ."' AND wdStID='" . $GLOBALS['DEF_STATUS_REQUEST'] . "'";
	$query = $conn->query($sql);
	if ($query->num_rows>0){
		$isValid = true;
	}
	return ($isValid);
}

function fGetSponsorUsername($u, $conn){
	$username = "";
	$sql = "SELECT mbrSponsor FROM dtMember WHERE mbrUsername= '". $u ."'";
	$query = $conn->query($sql);
	if ($row = $query->fetch_assoc()){
		$username = $row['mbrSponsor'];
	}
	return ($username);
}

function fGetName($u, $conn){
	$name = "";
	$sql = "SELECT mbrFirstName FROM dtMember WHERE mbrUsername= '". $u ."'";
	$query = $conn->query($sql);
	if ($row = $query->fetch_assoc()){
		$name = $row['mbrFirstName'];
	}
	
	return ($name);
}


function fCheckUsername($u, $conn){
	$isValid = false;
	$sql = "SELECT mbrFirstName FROM dtMember WHERE mbrUsername= '$u'";
	$query = $conn->query($sql);
	if ($row = $query->fetch_assoc()){
		$isValid = true;
	}
	return ($isValid);
}


function fGenPassword(){
	$randPasswd1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; // Initializing PHP variable with string
	$randPasswd2 = '123456789'; // Initializing PHP variable with string
	$randPasswd3 = '!@#$%&*'; // Initializing PHP variable with string
	$randPasswd = substr(str_shuffle($randPasswd1), 0, 7) . substr(str_shuffle($randPasswd2), 0, 2) . substr(str_shuffle($randPasswd3), 0, 1); 
	$randPasswd = str_shuffle($randPasswd);
	return ($randPasswd);
}


function getSrcIconPackage($pacID){
	$src = "";
	$pacID = strtoupper($pacID);
	if ($pacID == "ST"){
		$src = $GLOBALS['imgSrcUserST'];
	}
	if ($pacID == "PR"){
		$src = $GLOBALS['imgSrcUserPR'];
	}
	if ($pacID == "VIP"){
		$src = $GLOBALS['imgSrcUserVIP'];
	}
	
	return ($src);
}

function fToCornEmail($conn, $cat, $username, $uniqid){
	$table = "dtCornEmail";
	$arrData = array(
		0 => array ("db" => "cecat"		, "val" => $cat),
		1 => array ("db" => "ceUsername"	, "val" => $username), //not always filled
		2 => array ("db" => "ceUniqID"		, "val" => $uniqid), //not always filled
		3 => array ("db" => "cesendst"	, "val" => $GLOBALS['DEF_STATUS_NOT_YET_SENT']),
		4 => array ("db" => "cedate"	, "val" => "CURRENT_TIME()"),
	);
	if (!fInsert($table, $arrData, $conn)) {
	    return false;
		fSendToAdmin("fToCornEmail", "inc_functions.php", "category : " . $cat . ", Fail to Save");
	}else{
	    return true;
	}
}

function fCekStatusUsage($conn, $username, &$ExpiredDate){
	$sql = "SELECT mbrUsername, DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ) expiredDate, ";
	$sql .= " IF(DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ) >= CURRENT_DATE(), 'active', 'expired') AS status FROM dtMember";
    $sql .= " INNER JOIN (";
    $sql .= "     SELECT * FROM Transaction AS t ";
    $sql .= "     WHERE t.trID = (SELECT trID FROM Transaction WHERE trUsername = t.trUsername ORDER BY trDate DESC LIMIT 1)";
    $sql .= "    ) AS t ON t.trUsername = mbrUsername";
    $sql .= " WHERE mbrStID = '". $GLOBALS['DEF_STATUS_ACTIVE'] . "' AND mbrUsername = '". $username . "'";
    //$sql .= " AND DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ) >= CURRENT_DATE()";
    //echo $sql;
    $status = "";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()){
    	$ExpiredDate = $row['expiredDate'];
    	$status 	= $row['status'];
    }

    return ($status);
}

function random_strings($length_of_string) 
{ 
  
    // String of all alphanumeric character 
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
  
    // Shufle the $str_result and returns substring 
    // of specified length 
    return substr(str_shuffle($str_result), 0, $length_of_string); 
}

?>