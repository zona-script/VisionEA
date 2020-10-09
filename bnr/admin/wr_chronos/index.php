<?php
include_once("../../includes/inc_conn.php");
include_once("../../includes/inc_def.php");
include_once("../../includes/inc_functions.php");
include_once("./inc_functions_wr.php"); //beda folder

if (!empty($_POST)) {
	$params 	= isset($_POST["params"])? fValidateInput($_POST["params"]) : "";
	$coreParams = fDecyptParams($params);
	echo $coreParams; die();
	if ($coreParams === false){
		echo response("error", "invalid params"); die();
	}
	$obj = json_decode($coreParams);
	$action 	= isset($obj->{"ACTION"}) ? $obj->{'ACTION'} : "";
	$EAID 		= isset($obj->{"EA_ID"}) ? $obj->{'EA_ID'} : "";
	$acc 		= isset($obj->{"ACC"}) ? $obj->{'ACC'} : "";
	$pair		= isset($obj->{"PAIR"}) ? $obj->{'PAIR'} : "";

	$vid 		= isset($obj->{"VID"}) ? $obj->{'VID'} : ""; //dari Generator utk Add
	$exp 		= isset($obj->{"TGL_EXPIRY"}) ? $obj->{'TGL_EXPIRY'} : "";//yyyy.mm.dd

	$equity     = isset($obj->{'EQUITY'}) ? $obj->{'EQUITY'} : "";
    $credit     = isset($obj->{'CREDIT'}) ? $obj->{'CREDIT'} : "";
    $balance    = isset($obj->{'BALANCE'}) ? $obj->{'BALANCE'} : "";
    $profit     = isset($obj->{'PROFIT'}) ? $obj->{'PROFIT'} : "";
    $server     = isset($obj->{'SERVER'}) ? $obj->{'SERVER'} : "";

	$lastOPTime = isset($obj->{'lastOPTime'}) ? $obj->{'lastOPTime'} : ""; //yyyy.mm.dd

	$isConnected = $isHistoryOn = $$isAutoTrading = $isInvestorAcc = $isAuth = "";

	//$pacCode 			= isset($_POST["pacCode"])? fValidateSQLFromInput($conn, $_POST["pacCode"]) : "";

	// echo ("Action:" . $action . " acc: " . $acc." || ".$pair. " ||". $vid. " || ".$EAID);
	if ($action == "GENVID" && $EAID != "" && $acc != "" && $pair != "" && $vid != ""){
		//echo (fAddVoucherID($conn, $EAID, $acc, $pair, $vid, $exp));
		$status = fAddVoucherID($conn, $EAID, $acc, $pair, $vid, $exp);
		echo ($status); die();
	}else if ($action == "update_status" && $EAID != "" && $acc != "" && $pair != "" && $isConnected != "" && $isAutoTrading != "" && $isHistoryOn != "" && $isInvestorAcc != "" && $isAuth != ""){
		//echo (fUpdateStatus($conn, $EAID, $acc, $pair, $isConnected, $isAutoTrading, $isHistoryOn, $isInvestorAcc, $isAuth, $lastOPTime));
		$status = fUpdateStatus($conn, $EAID, $acc, $pair, $isConnected, $isAutoTrading, $isHistoryOn, $isInvestorAcc, $isAuth, $balance, $profit, $credit, $equity, $lastOPTime);
		echo ($status); die();
	}else if ($action == "AUTH" && $EAID != "" && $acc != "" && $pair != "" /*&& $pacCode != ""*/){
		//get VoucherCode
		// $VoucherCode = fGetVoucherCode($conn, $EAID, $acc, $pair, $pacCode);
		// //return VoucherCode
		// echo ($VoucherCode);
		$VoucherCode = fGetVoucherCode($conn, $EAID, $acc, $pair);
		//return VoucherCode
		echo ($VoucherCode); die();
	}else{
		echo response("error", "ERR=200"); die();
	}
}else{
	//echo ("ERR=100"); //error method
	echo response("error", "ERR=100"); die();
}

fCloseConnection($conn); die();
?>


