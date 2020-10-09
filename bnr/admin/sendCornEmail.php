<!-- <!DOCTYPE html> -->
<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_func_admin.php");
//include_once("../../includes/inc_adm_session.php");


//0,30 * * * *
//wget -O /dev/null https://visionea.net/bnr/admin/sendCornEmail.php?key=rAha$!4_corn

//verifikasi CRON that allowed
$key = "";
$key = isset($_GET['key'])?$_GET['key']:"";
if ($key == "rAha$!4_corn"){
	$sql = "SELECT * FROM dtCornEmail WHERE cesendst='".$DEF_STATUS_NOT_YET_SENT."'";
	// $sql .= " AND ceid='6914'"; //for testing only... specific id..
	// $sql .= " AND ceUsername = 'effendites3'";
	$sql .= " ORDER BY ceid ASC LIMIT 1";
	// echo $sql; die();
	$res = $conn->query($sql);
	if ($row = $res->fetch_assoc()){
	    $cat 	= $row['cecat'];
		$id 	= $row['ceid'];
		// echo fSendNotifToEmail($cat, $id); die();
		if (fSendNotifToEmail($cat, $id)){
			//update status SENT
			fUpdateStatusSendCornEmail($conn, $id, $DEF_STATUS_SENT); //Update Status "SENT"
			echo ("CRON: success, SEND EMAIL : success");
		}else{
			//SEND FAILED
			fUpdateStatusSendCornEmail($conn, $id, $DEF_STATUS_SENT_FAILED); //update status "SENT FAILED"
			echo ("CRON: success, SEND EMAIL : failed");
		}
	}else{
		echo ("no record");
	}
}else{
	echo ("not authorized, CRON sendCornEmail failed");
}

?>