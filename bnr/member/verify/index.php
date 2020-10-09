<?PHP
include_once("../../includes/inc_def.php");
include_once("../../includes/inc_conn.php");
include_once("../../includes/inc_functions.php");

$MNav	= (isset($_GET['MNav']))?$_GET['MNav']:"";
$subNav	= (isset($_GET['subNav']))?$_GET['subNav']:"";
$q		= (isset($_GET['q']))?$_GET['q']:"";
$code	= (isset($_GET['code']))?$_GET['code']:"";

//Verify Email ____________________________________________________
if ($MNav == "act") $subject = "Verify Your Email Address";
if ($MNav == "act" && $subNav == "" && $q != "" && $code != ""){
	$q		= fValidateSQLFromInput($conn, $q);
	$code	= fValidateSQLFromInput($conn, $code);
	$sql 	= "SELECT tjUsername FROM dtTempJoin WHERE tjUsername='". $q . "' AND tjVerifyCode='" . $code . "'";
	$query  = $conn->query($sql);
	if ($query->num_rows > 0){
		$sql = "UPDATE dtTempJoin SET tjStID='" . $DEF_STATUS_ACTIVE . "' WHERE tjUsername='".$q . "' AND tjVerifyCode='".$code."'";
		if ($conn->query($sql)){
			header("Location: ./?MNav=".$MNav."&subNav=verified&q=". $q);
			die();
		}else{
			header("Location: ./?MNav=".$MNav."&subNav=failed&q=". $q);
			die();
		}
	}else{
		header("Location: ./?MNav=".$MNav."&subNav=del&q=". $q);
		die();
	}

}elseif ($MNav == "act" && $subNav == 'verified'){
	$q		= fValidateSQLFromInput($conn, $q);
	$sql 	= "SELECT tjUsername, tjSponsor, mbrEmail FROM dtTempJoin INNER JOIN dtMember ON mbrUsername=tjSponsor WHERE tjUsername='". $q . "' AND  tjStID='" . $DEF_STATUS_ACTIVE . "'";
	$query  = $conn->query($sql);
	if ($row = $query->fetch_assoc()){
		$sponsor = $row['tjSponsor'];
		$emailSponsor = $row['mbrEmail'];
		$msg	= "<p>Dear " . $q . ",</p><p>Email Anda telah berhasil diverifikasi.</p>";
		$msg	.= "<p>Anda dapat menghubungi sponsor Anda untuk melanjutkan proses aktivasi akun.</p>";
		$msg	.= "<p>Sponsor Anda : <br>";
		$msg    .= "Username : " . $sponsor . "<br>";
		$msg    .= "Email : ". $emailSponsor. "</p>";
	}

}elseif ($MNav == "act" && $subNav == 'failed'){
	$msg	= "<p>Dear " . $q . ",</p><p class='text-danger'>Verifikasi gagal.<br>Link telah kadaluarsa</p>";
	$msg	.= "Silahkan hubungi sponsor Anda untuk aktivasi ulang.";
}elseif ($MNav == "act" && $subNav == 'del'){
	$msg	= "<p>Dear " . $q . ",</p><p class='text-danger'>verifikasi gagal.>Data tidak ditemukan atau link telah kadaluarsa.</p>";
	$msg	.= "Silahkan hubungi sponsor Anda untuk aktivasi ulang.";
}





//Request PIN (security)____________________________________________
if ($MNav == "reqPIN") $subject = "Activate Security Password";
if ($MNav == "reqPIN" && $subNav == "" && $q != "" && $code != ""){
	$q		= fValidateSQLFromInput($conn, $q);
	$code	= fValidateSQLFromInput($conn, $code);
	$sql 	= "SELECT pinWord FROM trPIN WHERE pinMbrUsername='". $q . "' AND pinID='" . $code . "' AND pinStID='".$DEF_STATUS_PENDING."'";
	$query  = $conn->query($sql);
	if ($row = $query->fetch_assoc()){

		$newPinWord =  md5($row['pinWord']);
		$sql = "UPDATE trPIN SET pinWord='" . $newPinWord . "', pinStID='". $DEF_STATUS_APPROVED ."' WHERE pinMbrUsername='".$q . "' AND pinID='".$code."'";
		if ($conn->query($sql)){
			header("Location: ./?MNav=".$MNav."&subNav=activated&q=". $q);
			die();
		}else{
			header("Location: ./?MNav=".$MNav."&subNav=failed&q=". $q);
			die();
		}
	}else{
		header("Location: ./?MNav=".$MNav."&subNav=del&q=". $q);
		die();
	}

}elseif ($MNav == "reqPIN" && $subNav == 'activated'){
	$msg	= "<p>Dear " . $q . ",</p><p>Security password Anda telah diaktifkan.</p>";
	$msg	.= "<p>Silahkan untuk memperbarui Security Password Anda secara berkala.</p>";
}elseif ($MNav == "reqPIN" && $subNav == 'failed'){
	$msg	= "<p>Dear " . $q . ",</p><p class='text-danger'>Aktivasi Gagal.<br>Link Anda telah kadaluarsa.</p>";
	$msg	.= "Silahkan melakukan pengajuan ulang perubahan Security Password";
}elseif ($MNav == "reqPIN" && $subNav == 'del'){
	$msg	= "<p>Dear " . $q . ",</p><p class='text-danger'>Aktivasi Gagal.<br>Data tidak ditemukan atau link telah kadaluarsa.</p>";
	$msg	.= "Silahkan melakukan pengajuan ulang perubahan Security Password";
}





//Request Reset Password ____________________________________________
if ($MNav == "resetPW") $subject = "Reset Password";
if ($MNav == "resetPW" && $subNav == "" && $q != "" && $code != ""){
	$q		= fValidateSQLFromInput($conn, $q);
	$code	= fValidateSQLFromInput($conn, $code);
	$sql 	= "SELECT rrID, rrNote FROM dtReqReset WHERE rrUsername='". $q . "' AND rrID='" . $code . "' AND rrStID='".$DEF_STATUS_REQUEST."'";
	$query  = $conn->query($sql);
	
	if ($row = $query->fetch_assoc()){

		$newPassword = $row['rrNote'];
		
		$conn->autocommit(false);

		//Update status dtReqReset
		$table = "dtReqReset";
		$arrData = array("rrStID"=>$DEF_STATUS_APPROVED);
		$arrDataQuery = array("rrID"=>$code, "rrUsername"=>$q, "rrStID"=>$DEF_STATUS_REQUEST);
		if (fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
			//Insert into trPassword
			unset($arrData);
			unset($arrDataQuery);
			$table = "trPassword";
			$arrData = array(
				0=>array("db"=>"passMbrUsername", "val"=>$q), 
				1=>array("db"=>"passWord"		, "val"=>md5($newPassword)),
				2=>array("db"=>"passDate"		, "val"=>$CURRENT_TIME)
			);
			if (fInsert($table, $arrData, $conn)){
				//success
				$conn->commit();
				$conn->close();
				header("Location: ./?MNav=".$MNav."&subNav=activated&q=". $q);
				die();
			}else{
				$conn->rollback();
				$conn->close();
				header("Location: ./?MNav=".$MNav."&subNav=failed&q=". $q);
				die();
			}
			die();
		}
	}else{
		//error fetch_assoc();
		//fSendToAdmin("Reset Password", "index.php (verify)", $sql);
		header("Location: ./?MNav=".$MNav."&subNav=del&q=". $q);
		die();
	}

	

}elseif ($MNav == "resetPW" && $subNav == 'activated'){
	$msg	= "<p>Dear " . $q . ",</p><p>Kata sandi berhasil direset..</p>";
	$msg	.= "<p>Silahkan memperbarui kata sandi Anda secara berkala</p>";
	$msg	.= "<p>Kami sarankan untuk mengubah kata sandi Anda segera.</p>";
}elseif ($MNav == "resetPW" && $subNav == 'failed'){
	$msg	= "<p>Dear " . $q . ",</p><p class='text-danger'>Reset Kata Sandi Gagal.<br>Link telah kadaluarsa</p>";
	$msg	.= "Silahkan melakukan pengajuan ulang reset kata sandi.";
}elseif ($MNav == "resetPW" && $subNav == 'del'){
	$msg	= "<p>Dear " . $q . ",</p><p class='text-danger'>Reset Kata Sandi Gagal.<br>Data tidak ditemukan atau link telah kadaluarsa.</p>";
	$msg	.= "Silahkan melakukan pengajuan ulang reset kata sandi.";
}



//Request Withdrawal ____________________________________________
if ($MNav == "reqWD") $subject = "Confirm Withdrawal";
if ($MNav == "reqWD" && $subNav == "" && $q != "" && $code != ""){
	$q1		= fValidateSQLFromInput($conn, $q);
	$code	= fValidateSQLFromInput($conn, $code);
	session_start();
	$_SESSION['sConfirmID'] = $q; //username
	$_SESSION['sCode'] 		= $code; //transid
	header ("Location: " . $COMPANY_SITE . "member/verify/confirmCode.php?userid=".$_SESSION['sConfirmID']."&transid=".$_SESSION['sCode']."&MNav=confirm_wd");
	die();	

}elseif ($MNav == "reqWD" && $subNav == 'activated'){
	$msg	= "<p>Dear " . $q . ",</p><p>Konfirmasi Berhasil.</p>";
	
}elseif ($MNav == "reqWD" && $subNav == 'failed'){
	$msg	 = "<p>Dear " . $q . ",</p><p class='text-danger'>Konfirmasi Gagal,<br>Link Anda telah kadaluarsa</p>";
	$msg	.= "<p>Silahkan melakukan pengajuan ulang penarikan dana.</p>";
}elseif ($MNav == "reqWD" && $subNav == 'del'){
	$msg	 = "<p>Dear " . $q . ",</p><p class='text-danger'>konfirmasi Gagal.<br>Data Anda tidak ditemukan atau link tidak valid.</p>";
	$msg	.= "<p>Silahkan melakukan pengajuan ulang penarikan dana.</p>";
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Verify Email</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Favicons -->
  <link rel="icon" href="../../images/favicon.png" sizes="16x16 32x32" type="image/png">
  <!--
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
-->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>

  
</head>
<body>
<div class="container col-md-6">
	<img src="<?php echo ($COMPANY_SITE . "assets/img/email/" .$imgSrcHeaderEmail); ?>" width="100%">
	<div class="card">
		<div class="card-header">
			<h3 class="text-center text-primary"><?php echo $subject ?></h3>
		</div>
	  	<div class="body col-md-12">
	  		<?php echo $msg ?>
		    <div class="col-md-12">
		        <div class="row">Jika Anda memiliki pertanyaan, silahkan hubungi team support Kami.</div>
		        <div class="row"></div>
		    </div>
		    <p>&nbsp;</p>
		    <div class="col-md-12">
		        <div class="row"><button class="btn btn-outline-primary" onclick="location.href='../'"><i class="fa fa-home"></i>&nbsp; back to member area</button></div>
		        <div class="row"></div>
		    </div>
		    <p>&nbsp;</p>
		    <div class="col-md-12">
		        <div class="row">Sincerely,</div>
		        <div class="row"></div>
		    </div>
		    <div class="col-md-12">
		        <div class="row small">VisionEA Support Team</div>
		        <div class="row"></div>
		    </div>
		    <p>&nbsp;</p>
	  	</div>
	    <div class="card-footer">
	    	<div class="col-md-12">
	    		<a href="<?php echo $DEF_LINK_FB ?>"><i class="fa fa-facebook-official fa-2x"></i></a>
	    		<a href="<?php echo $DEF_LINK_IG ?>"><i class="fa fa fa-instagram fa-2x"></i></a>
	    	</div>
	    	<div class="col-md-12" style="font-size: x-small;">
	    	</div>
	    	<div class="col-md-12" style="font-size: x-small;">
	    		<b>Risk Notice:</b> Before you start trading, you should really understand the risks involved in the currency market and on margin trading, and consider your level of experience.<br><br>
	    	</div>
	    </div>
	</div>
</div>
</body>
</html> 

<?php fCloseConnection($conn); ?>