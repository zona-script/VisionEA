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

if (!empty($_GET)) { 
	$action=$acc=$EANumber="";
	$action 	= isset($_GET["action"])? fValidateInput($_GET["action"]) : "";
	$EANumber 	= isset($_GET["EANumber"])? fValidateSQLFromInput($conn, $_GET["EANumber"]) : "";
	$acc 		= isset($_GET["acc"])? fValidateSQLFromInput($conn, $_GET["acc"]) : "";
	$pair		= isset($_GET["pair"])? fValidateSQLFromInput($conn, $_GET["pair"]) : "";

	$vid 		= isset($_GET["vid"])? fValidateSQLFromInput($conn, $_GET["vid"]) : ""; //dari Generator utk Add
	$exp 		= isset($_GET["exp"])? fValidateSQLFromInput($conn, $_GET["exp"]) : ""; //yyyy.mm.dd

	$isConnected 		= isset($_GET["isConnected"])? fValidateSQLFromInput($conn, $_GET["isConnected"]) : "";
	$isAutoTrading 		= isset($_GET["isAutoTrading"])? fValidateSQLFromInput($conn, $_GET["isAutoTrading"]) : "";
	$isHistoryOn 		= isset($_GET["isHistoryOn"])? fValidateSQLFromInput($conn, $_GET["isHistoryOn"]) : "";
	$isInvestorAcc 		= isset($_GET["isInvestorAcc"])? fValidateSQLFromInput($conn, $_GET["isInvestorAcc"]) : "";
	$isAuth 			= isset($_GET["isAuth"])? fValidateSQLFromInput($conn, $_GET["isAuth"]) : "";
	$lastOPTime 		= isset($_GET["lastOPTime"])? fValidateSQLFromInput($conn, $_GET["lastOPTime"]) : ""; //yyyy.mm.dd

	$pacCode 			= isset($_GET["pacCode"])? fValidateSQLFromInput($conn, $_GET["pacCode"]) : "";

		//echo ("Action:" . $action . " acc: " . $acc);
	if ($action == "add_vid" && $EANumber != "" && $acc != "" && $pair != "" && $vid != ""){
		echo (fAddVoucherID($conn, $EANumber, $acc, $pair, $vid, $exp));
	}else if ($action == "update_status" && $EANumber != "" && $acc != "" && $pair != "" && $isConnected != "" && $isAutoTrading != "" && $isHistoryOn != "" && $isInvestorAcc != "" && $isAuth != ""){
		echo (fUpdateStatus($conn, $EANumber, $acc, $pair, $isConnected, $isAutoTrading, $isHistoryOn, $isInvestorAcc, $isAuth, $lastOPTime));
	}else if ($action == "get_vid" && $EANumber != "" && $acc != "" && $pair != "" && $pacCode != ""){
		//get VoucherCode
		$VoucherCode = fGetVoucherCode($conn, $EANumber, $acc, $pair, $pacCode);
		//return VoucherCode
		echo ($VoucherCode);
	}else{
		echo "ERR=200"; //error data request
	}
}else if (!empty($_POST)) { //harusnya ga dipakai lagi....
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
	$lastOPTime 		= isset($_POST["lastOPTime"])? fValidateSQLFromInput($conn, $_POST["lastOPTime"]) : ""; //yyyy.mm.dd

	$pacCode 			= isset($_POST["pacCode"])? fValidateSQLFromInput($conn, $_POST["pacCode"]) : "";

		//echo ("Action:" . $action . " acc: " . $acc);
	if ($action == "add_vid" && $EANumber != "" && $acc != "" && $pair != "" && $vid != ""){
		echo (fAddVoucherID($conn, $EANumber, $acc, $pair, $vid, $exp));
	}else if ($action == "update_status" && $EANumber != "" && $acc != "" && $pair != "" && $isConnected != "" && $isAutoTrading != "" && $isHistoryOn != "" && $isInvestorAcc != "" && $isAuth != ""){
		echo (fUpdateStatus($conn, $EANumber, $acc, $pair, $isConnected, $isAutoTrading, $isHistoryOn, $isInvestorAcc, $isAuth, $lastOPTime));
	}else if ($action == "get_vid" && $EANumber != "" && $acc != "" && $pair != "" && $pacCode != ""){
		//get VoucherCode
		$VoucherCode = fGetVoucherCode($conn, $EANumber, $acc, $pair, $pacCode);
		//return VoucherCode
		echo ($VoucherCode);
	}else{
		echo "ERR=200"; //error data request
	}
}else{
	echo ("ERR=100"); //error method
}

fCloseConnection($conn); die();
?>
