<?PHP
include("../includes/inc_def.php");
include_once("../includes/inc_conn.php");
include("../includes/inc_functions.php");
//https://visionea.net/bnr/admin/tryCron.php?act=posting&cat=pair&code=VisionEA
$act	    =  (isset($_GET["act"]))?fValidateInput($_GET["act"]): "";
$cat	    =  (isset($_GET["cat"]))?fValidateInput($_GET["cat"]): "";
$code	    =  (isset($_GET["code"]))?fValidateInput($_GET["code"]): "";

if ($act == "posting" && $cat == "pair" && $code == "VisionEA"){
	if (fIsMaintenance()){
		fSendToAdmin("cron-AUTH", "tryCron.php", "singapore: " . $CURRENT_TIME);
	}else{
		fSendToAdmin("ERROR-cron-AUTH", "tryCron.php", "Sistem not in maintenance. Singapore: " . $CURRENT_TIME);
	}
}else{
	fSendToAdmin("cron-ERROR", "tryCron.php", "ERROR: " . $CURRENT_TIME);
}
?>