<?php
include_once("../../includes/inc_conn.php");
include_once("../../includes/inc_def.php");
include_once("../../includes/inc_functions.php");
include_once("./inc_functions_wr.php"); //beda folder

/*
$EANumber = "78"; //vea
$acc 		= "8923797";
$pair = "EU";
$pacCode = "st";
//$vid 		= "vid1341234";
//$exp		= "2019-07-20";
//echo (fAddVoucherID($conn, $EANumber, $acc, $vid, $exp)); die();
$VoucherCode = fGetVoucherCode($conn, $EANumber, $acc, $pair, $pacCode); die();
*/


/*

3 Juni 2020
Penambahan 4 fields : Balance, floating, credit dan equity pada fUpdateStatus();
Maka alter table :
	ALTER TABLE `dtstateea` ADD `seabalance` DECIMAL NOT NULL DEFAULT '0' AFTER `seaAuth`, ADD `seafloating` DECIMAL NOT NULL DEFAULT '0' AFTER `seabalance`, ADD `seacredit` DECIMAL NOT NULL DEFAULT '0' AFTER `seafloating`, ADD `seaequity` DECIMAL NOT NULL DEFAULT '0' AFTER `seacredit`;

*/


if (!empty($_POST)) { 
	$action=$acc=$EANumber="";
	$action 	= isset($_POST["action"])? fValidateInput($_POST["action"]) : "";
	$EANumber 	= isset($_POST["EANumber"])? fValidateSQLFromInput($conn, $_POST["EANumber"]) : "";
	$acc 		= isset($_POST["acc"])? fValidateSQLFromInput($conn, $_POST["acc"]) : "";
	$pair		= isset($_POST["pair"])? fValidateSQLFromInput($conn, $_POST["pair"]) : "";

	$vid 		= isset($_POST["vid"])? fValidateSQLFromInput($conn, $_POST["vid"]) : ""; //dari Generator utk Add
	$exp 		= isset($_POST["exp"])? fValidateSQLFromInput($conn, $_POST["exp"]) : ""; //yyyy.mm.dd

	$isConnected 		= isset($_POST["isConnected"])? fValidateSQLFromInput($conn, $_POST["isConnected"]) : "";
	$isAutoTrading 		= isset($_POST["isAutoTrading"])? fValidateSQLFromInput($conn, $_POST["isAutoTrading"]) : "";
	$isHistoryOn 		= isset($_POST["isHistoryOn"])? fValidateSQLFromInput($conn, $_POST["isHistoryOn"]) : "";
	$isInvestorAcc 		= isset($_POST["isInvestorAcc"])? fValidateSQLFromInput($conn, $_POST["isInvestorAcc"]) : "";
	$isAuth 			= isset($_POST["isAuth"])? fValidateSQLFromInput($conn, $_POST["isAuth"]) : "";

	$balance 			= isset($_POST["balance"])? fValidateSQLFromInput($conn, $_POST["balance"]) : "";
	$floating 			= isset($_POST["floating"])? fValidateSQLFromInput($conn, $_POST["floating"]) : "";
	$credit 			= isset($_POST["credit"])? fValidateSQLFromInput($conn, $_POST["credit"]) : "";
	$equity 			= isset($_POST["equity"])? fValidateSQLFromInput($conn, $_POST["equity"]) : "";

	$lastOPTime 		= isset($_POST["lastOPTime"])? fValidateSQLFromInput($conn, $_POST["lastOPTime"]) : ""; //yyyy.mm.dd



	//$pacCode 			= isset($_POST["pacCode"])? fValidateSQLFromInput($conn, $_POST["pacCode"]) : "";

		//echo ("Action:" . $action . " acc: " . $acc);
	if ($action == "add_vid" && $EANumber != "" && $acc != "" && $pair != "" && $vid != ""){
		//echo (fAddVoucherID($conn, $EANumber, $acc, $pair, $vid, $exp));
		$status = fAddVoucherID($conn, $EANumber, $acc, $pair, $vid, $exp);
		echo json_encode($status);
	}else if ($action == "update_status" && $EANumber != "" && $acc != "" && $pair != "" && $isConnected != "" && $isAutoTrading != "" && $isHistoryOn != "" && $isInvestorAcc != "" && $isAuth != ""){
		//echo (fUpdateStatus($conn, $EANumber, $acc, $pair, $isConnected, $isAutoTrading, $isHistoryOn, $isInvestorAcc, $isAuth, $lastOPTime));
		$status = fUpdateStatus($conn, $EANumber, $acc, $pair, $isConnected, $isAutoTrading, $isHistoryOn, $isInvestorAcc, $isAuth, $balance, $floating, $credit, $equity, $lastOPTime);
		echo json_encode($status);
	}else if ($action == "get_vid" && $EANumber != "" && $acc != "" && $pair != "" /*&& $pacCode != ""*/){
		//get VoucherCode
		// $VoucherCode = fGetVoucherCode($conn, $EANumber, $acc, $pair, $pacCode);
		// //return VoucherCode
		// echo ($VoucherCode);
		$VoucherCode = fGetVoucherCode($conn, $EANumber, $acc, $pair);
		//return VoucherCode
		echo json_encode($VoucherCode);
	}else if ($action == "get_params_update" && $EANumber != "" && $acc != "" && $pair != ""){
		$params = fGetParamsUpdate($conn, $EANumber, $acc, $pair);
		echo json_encode($params);

	}else{
		//echo "ERR=200"; //error data request
		echo json_encode(response(true, "ERR=200"));
	}
}else{
	//echo ("ERR=100"); //error method
	echo json_encode(response(true, "ERR=100"));
}

fCloseConnection($conn); die();
?>


