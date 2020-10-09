<?php 
include_once("../../includes/inc_conn.php");
include_once("../../includes/inc_def.php");
include_once("../../includes/inc_functions.php");
include_once("../../includes/inc_commission.php");
include_once("./inc_func_apps_api.php"); //beda folder
include_once("./inc_func_web_api.php"); //beda folder

if (!empty($_POST)){
	$action 	=  fValidateSQLFromInput($conn, (isset($_POST['action'])))?fValidateSQLFromInput($conn, $_POST['action']): '';
	$username 	=  fValidateSQLFromInput($conn, (isset($_POST['username'])))?fValidateSQLFromInput($conn, $_POST['username']): '';
	if (strcmp($action, 'post_acctrade') == 0){
		$accNumber		= fValidateSQLFromInput($conn, (isset($_POST['accNumber'])))?fValidateSQLFromInput($conn, $_POST['accNumber']): '';
	    $accEACode		= fValidateSQLFromInput($conn, (isset($_POST['accEACode'])))?fValidateSQLFromInput($conn, $_POST['accEACode']): '';
	    $accOrder		= fValidateSQLFromInput($conn, (isset($_POST['accOrder'])))?fValidateSQLFromInput($conn, $_POST['accOrder']): '';
	    $accPair		= fValidateSQLFromInput($conn, (isset($_POST['accPair'])))?fValidateSQLFromInput($conn, $_POST['accPair']): '';
	    $passwdTrade	= fValidateSQLFromInput($conn, (isset($_POST['passwdTrade'])))?fValidateSQLFromInput($conn, $_POST['passwdTrade']): '';
	    $accName		= fValidateSQLFromInput($conn, (isset($_POST['accName'])))?fValidateSQLFromInput($conn, $_POST['accName']): '';
	    $server			= fValidateSQLFromInput($conn, (isset($_POST['server'])))?fValidateSQLFromInput($conn, $_POST['server']): '';
	    $hasil 			= addTradingAcc($conn, $username, $accNumber, $accEACode, $accOrder, $accPair, $passwdTrade, $accName, $server);
	    echo $hasil;
	}else if (strcmp($action, 'get_acctrade') == 0){
		$accEACode	= fValidateSQLFromInput($conn, (isset($_POST['accEACode'])))?fValidateSQLFromInput($conn, $_POST['accEACode']): '';
		$accPair	= fValidateSQLFromInput($conn, (isset($_POST['accPair'])))?fValidateSQLFromInput($conn, $_POST['accPair']): '';
		$hasil 		= getTradingAcc($conn, $username, $accEACode, $accPair);
		echo $hasil;	
	}else if (strcmp($action, 'post_reqresacctrade') == 0){
		$resaccEAID		= fValidateSQLFromInput($conn, (isset($_POST['resaccEAID'])))?fValidateSQLFromInput($conn, $_POST['resaccEAID']): '';
		$resaccPairID	= fValidateSQLFromInput($conn, (isset($_POST['resaccPairID'])))?fValidateSQLFromInput($conn, $_POST['resaccPairID']): '';
		$resaccNo		= fValidateSQLFromInput($conn, (isset($_POST['resaccNo'])))?fValidateSQLFromInput($conn, $_POST['resaccNo']): '';
		$hasil 			= reqResetAcc($conn, $username, $resaccEAID, $resaccPairID, $resaccNo);
		echo $hasil;
	}else if (strcmp($action, 'get_profile') == 0){
		$hasil = getProfile($conn, $username);
		echo $hasil;
	}else if (strcmp($action, 'get_statususage') == 0){
		$hasil = getStatusUsage($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "post_accparams") == 0){
		$TAPUsername    =  fValidateSQLFromInput($conn, (isset($_POST['TAPUsername'])))?fValidateSQLFromInput($conn, $_POST['TAPUsername']): '';
	    $TAPEANum       =  fValidateSQLFromInput($conn, (isset($_POST['TAPEANum'])))?fValidateSQLFromInput($conn, $_POST['TAPEANum']): '';
	    $TAPPairID      =  fValidateSQLFromInput($conn, (isset($_POST['TAPPairID'])))?fValidateSQLFromInput($conn, $_POST['TAPPairID']): '';
	    $TAPAccNo       =  fValidateSQLFromInput($conn, (isset($_POST['TAPAccNo'])))?fValidateSQLFromInput($conn, $_POST['TAPAccNo']): '';
	    $TAPTakeProfit  =  fValidateSQLFromInput($conn, (isset($_POST['TAPTakeProfit'])))?fValidateSQLFromInput($conn, $_POST['TAPTakeProfit']): '';
	    $TAPInitOD      =  fValidateSQLFromInput($conn, (isset($_POST['TAPInitOD'])))?fValidateSQLFromInput($conn, $_POST['TAPInitOD']): '';
	    $TAPAutoLot     =  fValidateSQLFromInput($conn, (isset($_POST['TAPAutoLot'])))?fValidateSQLFromInput($conn, $_POST['TAPAutoLot']): '';
	    $TAPLotSize     =  fValidateSQLFromInput($conn, (isset($_POST['TAPLotSize'])))?fValidateSQLFromInput($conn, $_POST['TAPLotSize']): '';
	    $TAPMaxLayers   =  fValidateSQLFromInput($conn, (isset($_POST['TAPMaxLayers'])))?fValidateSQLFromInput($conn, $_POST['TAPMaxLayers']): '';
	    $TAPLotMulti    =  fValidateSQLFromInput($conn, (isset($_POST['TAPLotMulti'])))?fValidateSQLFromInput($conn, $_POST['TAPLotMulti']): '';
	    $TAPOnReversal  =  fValidateSQLFromInput($conn, (isset($_POST['TAPOnReversal'])))?fValidateSQLFromInput($conn, $_POST['TAPOnReversal']): '';
		$TAPStatusEA 	=  fValidateSQLFromInput($conn, (isset($_POST['TAPStatusEA'])))?fValidateSQLFromInput($conn, $_POST['TAPStatusEA']): '';
	    $hasil = addAccParams($conn, $TAPUsername, $TAPEANum, $TAPPairID, $TAPAccNo, $TAPTakeProfit, $TAPInitOD, $TAPAutoLot, $TAPLotSize, $TAPMaxLayers, $TAPLotMulti, $TAPOnReversal, $TAPStatusEA);
	    echo $hasil;
	}else if (strcmp($action, "get_previousparams") == 0){
		$accEACode	= fValidateSQLFromInput($conn, (isset($_POST['accEACode'])))?fValidateSQLFromInput($conn, $_POST['accEACode']): '';
		$accPair	= fValidateSQLFromInput($conn, (isset($_POST['accPair'])))?fValidateSQLFromInput($conn, $_POST['accPair']): '';
		$limit		= fValidateSQLFromInput($conn, (isset($_POST['limit'])))?fValidateSQLFromInput($conn, $_POST['limit']): 'all';
		$hasil 		= getAccParams($conn, $limit, $username, $accEACode, $accPair);
		echo $hasil;
	}else if (strcmp($action, "get_maxlotsize") == 0){
		$hasil = getMaxLotSize($conn, $username);
		echo $hasil;
	/*___________________VISION APP______________*/
	}else if (strcmp($action, "get_msProduct") == 0){ 
		$hasil = getMsProduct($conn);
		echo $hasil;
	}else if(strcmp($action, "post_buyProduct") == 0){  
		$typeofpurchase = fValidateSQLFromInput($conn, (isset($_POST['typeofpurchase'])))?fValidateSQLFromInput($conn, $_POST['typeofpurchase']): '';
		$trProUserBeli 	= fValidateSQLFromInput($conn, (isset($_POST['cusUsername'])))?fValidateSQLFromInput($conn, $_POST['cusUsername']): '';
		$cusEmail 		= fValidateSQLFromInput($conn, (isset($_POST['cusEmail'])))?fValidateSQLFromInput($conn, $_POST['cusEmail']): '';
		$cusFirstName 	= fValidateSQLFromInput($conn, (isset($_POST['cusFirstName'])))?fValidateSQLFromInput($conn, $_POST['cusFirstName']): '';
		$cusLastName 	= fValidateSQLFromInput($conn, (isset($_POST['cusLastName'])))?fValidateSQLFromInput($conn, $_POST['cusLastName']): '';
		$myJSON 	    = isset($_POST["myJSON"])?($_POST["myJSON"]) : "";
		$myJSON    		= json_decode($myJSON);
		$hasil			= postBuyProduct($conn, $username, $typeofpurchase, $trProUserBeli, $cusEmail, $cusFirstName, $cusLastName, $myJSON);
		echo $hasil;
	}else if (strcmp($action, "get_pendingOrder") == 0) {
		$hasil			= getProDetailOrder($conn, $username);
		echo $hasil; 
	}else if(strcmp($action, "post_cancelOrder") == 0){
		$proTransID 	= fValidateSQLFromInput($conn, (isset($_POST['proTransID'])))?fValidateSQLFromInput($conn, $_POST['proTransID']): '';
		$hasil			= postCancelOrder($conn, $proTransID);
		echo $hasil;
	}else if (strcmp($action, "post_payProduct") == 0) {
		$proTransID 	= fValidateSQLFromInput($conn, (isset($_POST['proTransID'])))?fValidateSQLFromInput($conn, $_POST['proTransID']): '';
		$hasil 			= postPayOrderProduct($conn, $username, $proTransID);
		echo $hasil;
	}else if(strcmp($action, "get_proPaid") == 0){
		$hasil			=  getProPaid($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "get_detail") == 0) {
		$proTransID 	= fValidateSQLFromInput($conn, (isset($_POST['proTransID'])))?fValidateSQLFromInput($conn, $_POST['proTransID']): '';
		$hasil			= getProDetailOrder($conn, $username, $proTransID);
		echo $hasil;
	}else if(strcmp($action, "get_proCancel") == 0){
		$hasil			= getProCancel($conn, $username);
		echo $hasil;
	}else if(strcmp($action, "get_readEbook") == 0){
		$hasil			= getreadEbook($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "get_ttlCommission") == 0) {
		$hasil			= getTotalComission($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "get_payAcc") == 0) {
		$hasil		 	= getPayAcc($conn, $username);
		echo $hasil;
	}else if(strcmp($action, "post_payAcc") == 0){
		$accNumber 		= fValidateSQLFromInput($conn, $_POST["accNumber"]); 
    	$accName 		= fValidateSQLFromInput($conn, $_POST["accName"]); 
    	$accType 		= fValidateSQLFromInput($conn, $_POST["accType"]); 
    	$accCode 		= fValidateSQLFromInput($conn, (isset($_POST['accCode'])))?fValidateSQLFromInput($conn, $_POST['accCode']): '';

    	$hasil 			= postPayAcc($conn, $username, $accNumber, $accName, $accType, $accCode);
    	echo $hasil;
	}
	else if (strcmp($action, "post_ReqWD") == 0) {
		$payAcc			=  (isset($_POST["payAcc"]))?fValidateSQLFromInput($conn, $_POST["payAcc"]): "";
		$secPasswd		=  (isset($_POST["secPasswd"]))?fValidateSQLFromInput($conn, $_POST["secPasswd"]): "";
		$amount			=  (isset($_POST["amount"]))?fValidateSQLFromInput($conn, $_POST["amount"]): "0";
		$wdtax     		=  (isset($_POST["wdtax"]))?fValidateSQLFromInput($conn, $_POST["wdtax"]): "0";

		$hasil			= postReqWD($conn, $username, $payAcc, $secPasswd, $amount, $wdtax);
		echo $hasil;
	}else if (strcmp($action, "get_approvedPendingWD") == 0) {
		$hasil			= getApprovedPendingWD($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "get_declinedWD") == 0) {
		$hasil			= getDeclinedWD($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "get_renewPac") == 0) {
		$hasil			= getRenewPac($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "post_renewPac") == 0) {
		$securityPasswd      	= isset($_POST["securityPasswd"])? fValidateSQLFromInput($conn, $_POST["securityPasswd"]) : "";
    	$renewPac     			= isset($_POST["renewPac"])? fValidateInput($_POST["renewPac"]) : "";
    	$renewPac    			= 'st'; //strtolower($renewPac); set default package standart 
    	$tVBalance 				= isset($_POST["tVBalance"])? fValidateInput($_POST["tVBalance"]) : "";
    	$pacPrice 				= isset($_POST["defaultPacPrice"])? fValidateInput($_POST["defaultPacPrice"]) : "";
		$hasil					= postRenewPac($conn, $username, $securityPasswd, $renewPac, $tVBalance, $pacPrice);
		echo $hasil;
	}else if (strcmp($action, "post_buyVoucher") == 0) {
		$accType  		=  (isset($_POST["accType"]))?fValidateSQLFromInput($conn, $_POST["accType"]): "";
		$amount   		=  (isset($_POST["amount"]))?fValidateSQLFromInput($conn, $_POST["amount"]): "0";
		$hasil			= 	postBuyVoucher($conn, $username, $amount, $accType);
		echo $hasil;
	}else if (strcmp($action, "get_cekBuyVoucher") == 0) {
		$hasil = getCekBuyVoucher($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "post_buktiTransfer") == 0) {
		$finID    		=  (isset($_POST["finID"]))?fValidateSQLFromInput($conn, $_POST["finID"]): "";
		$IDTrans  		=  (isset($_POST["IDTrans"]))?fValidateSQLFromInput($conn, $_POST["IDTrans"]): "";
		$fromAccNo		=  (isset($_POST["fromAccNo"]))?fValidateSQLFromInput($conn, $_POST["fromAccNo"]): "";
		$imageFileType  = strtolower(pathinfo(basename($_FILES["finFilename"]["name"]),PATHINFO_EXTENSION));

		$hasil 			= postConfirmBuyVoucher($conn, $username, $finID, $fromAccNo, $imageFileType, $IDTrans);
		echo $hasil;
	}else if (strcmp($action, "get_approvedPendingBuyVoucher") == 0) {
		$hasil			= getApprovePendingBuyVoucher($conn, $username);
		echo $hasil;
	}else if(strcmp($action, "searchApprovedPendingBuyVoucher") == 0){
		$search    = (isset($_POST["search"]))?fValidateSQLFromInput($conn, $_POST["search"]): "";
    	$search    = strtolower($search);
		$hasil = searchApprovedPendingBuyVoucher($conn, $username, $search);

		echo $hasil;
	}else if (strcmp($action, "get_declinedBuyVoucher") == 0) {
		$hasil			= getDeclinedBuyVoucher($conn, $username);
		echo $hasil;
	}else if(strcmp($action, "searchDeclinedBuyVoucher") == 0){
		$search    	= (isset($_POST["search"]))?fValidateSQLFromInput($conn, $_POST["search"]): "";
    	$search    	= strtolower($search);
		$hasil 	 	= searchDeclinedBuyVoucher($conn, $username, $search);

		echo $hasil;
	}else if (strcmp($action, "get_transferVoucherHistory") == 0) {
		$hasil		= getTransferVoucherHistory($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "searchTransferVoucher") == 0) {
		$search    	= (isset($_POST["search"]))?fValidateSQLFromInput($conn, $_POST["search"]): "";
		$hasil		= searchTransferVoucher($conn, $username, $search);
		echo $hasil;
	}else if (strcmp($action, "post_convertBonus") == 0){
		$amountVoucher	 	=  (isset($_POST["amountVoucher"]))?fValidateSQLFromInput($conn, $_POST["amountVoucher"]): "0";
		$secPasswd   		= (isset($_POST["secPasswd"]))?fValidateSQLFromInput($conn, $_POST["secPasswd"]): "";

		$hasil				= postConvertBonus($conn, $username, $amountVoucher, $secPasswd);
		echo $hasil;
	}else if (strcmp($action, "get_convertBonusHistory") == 0) {
		
		$hasil				= getConvertBonusHistory($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "searchConvertBonus") == 0) {
		$search    	= (isset($_POST["search"]))?fValidateSQLFromInput($conn, $_POST["search"]): "";

		$hasil		= searchConvertBonus($conn, $username, $search);
		echo $hasil;
	}else if (strcmp($action, "get_historyComm") == 0) {

		$hasil		= getHistoryComm($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "searchHistoryComm") == 0) {

		$search    	= (isset($_POST["search"]))?fValidateSQLFromInput($conn, $_POST["search"]): "";
		$hasil		= searchHistoryComm($conn, $username, $search);
		echo $hasil;
	}else if (strcmp($action, "get_dataDashboard") == 0) {
		$hasil		= getDataDashboard($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "post_register") == 0) {

		$username 			= strtolower($username);
		$package 			= fValidateSQLFromInput($conn, $_POST["package"]); 
		$name 				= fValidateSQLFromInput($conn, $_POST["name"]);
		$IDType 			= fValidateSQLFromInput($conn, $_POST["IDType"]);
		$IDN 				= fValidateSQLFromInput($conn, $_POST["IDN"]); 
		$BOD 				= fValidateSQLFromInput($conn, $_POST["BOD"]); 
		$sponsorUsername 	= fValidateSQLFromInput($conn, $_POST["sponsorUsername"]); 
		// $sponsorName = fValidateSQLFromInput($conn, $_POST["sponsorName"]); 
		$password 			= md5(fValidateSQLFromInput($conn, $_POST["password"])); 
		// $rePassword = fValidateSQLFromInput($conn, $_POST["rePassword"]); 
		$codeMobile 		= fValidateSQLFromInput($conn, $_POST["codeMobile"]);
		$mobile 			= fValidateSQLFromInput($conn, $_POST["mobile"]); 
		$email 				= fValidateSQLFromInput($conn, $_POST["email"]); 
		$country 			= fValidateSQLFromInput($conn, $_POST["country"]); 
		$state 				= fValidateSQLFromInput($conn, $_POST["state"]); 
		$city 				= fValidateSQLFromInput($conn, $_POST["city"]); 
		$address 			= fValidateSQLFromInput($conn, $_POST["address"]);

		$hasil				=  postRegisterNewMember($conn, $username, $package, $name, $IDType, $IDN, $BOD, $sponsorUsername, $password, $codeMobile, 	$mobile, $email, $country, $state, $city, $address);

		echo $hasil;
	}else if (strcmp($action, "get_msIdType") == 0) {
		$hasil		= getMsIdType($conn);
		echo $hasil;
	}else if (strcmp($action, "get_msCountry") == 0) {
		$hasil		= getMsCountry($conn);
		echo $hasil;
	}else if (strcmp($action, "checkGenealogy") == 0) {
		$usernameLogin       	= ( isset( $_POST['username'] ))?fValidateInput( $_POST['username'] ): '';
    	$searchUsername      	= ( isset( $_POST['searchUsername'] ))?fValidateInput( $_POST['searchUsername'] ): '';

		$hasil					= checkGenealogy($conn, $usernameLogin, $searchUsername);
		echo $hasil;
	}else if (strcmp($action, "get_directSponsor") == 0) {
    	$usernameSP 	= (isset($_POST["usernameSP"]))?fValidateSQLFromInput($conn, $_POST["usernameSP"]): ""; //search by sponsor
    	$hasil			= getDirectSponsor($conn, $username, $usernameSP);
    	echo $hasil;
	}else if(strcmp($action, "get_registerNotActived") == 0){

		$hasil			= getNewMemberNotActivated($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "get_networkTree") == 0) {
		$usernameLogin      = ( isset( $_POST['username'] ))?fValidateInput( $_POST['username'] ): '';
    	$searchUsername     = ( isset( $_POST['searchUsername'] ))?fValidateInput( $_POST['searchUsername'] ): '';

    	$hasil				= getNetworkTree($conn, $usernameLogin, $searchUsername);
    	echo $hasil;
	}else if (strcmp($action, "get_nameFromTmpJoin") == 0) {

    	$upline = 	(isset($_POST["upline"]))?fValidateSQLFromInput($conn, $_POST["upline"]): "";
		$hasil	=	getNameFromTmpJoin($conn, $username, $upline);
		echo $hasil;
	}else if (strcmp($action, "post_activateMember") == 0) {

		$actUsername    = isset($_POST["actUsername"])? fValidateSQLFromInput($conn, $_POST["actUsername"]) : "";
		$actUsername    = strtolower($actUsername);

		$actUpline      = isset($_POST["actUpline"])? fValidateSQLFromInput($conn, $_POST["actUpline"]) : "";
		$actUpline      = strtolower($actUpline);

		$actPos         = isset($_POST["actPos"])? fValidateSQLFromInput($conn, $_POST["actPos"]) : "";
		$actPos         = strtolower($actPos);

		$actPackage     = isset($_POST["actPackage"])? fValidateSQLFromInput($conn, $_POST["actPackage"]) : "";
		$actPackage     = strtolower($actPackage);

		$hasil			= postActivateMember($conn, $username, $actUsername, $actUpline, $actPos, $actPackage);
		echo $hasil;
	}else if (strcmp($action, "get_verifyIDStatus") == 0) {
		
		$hasil			= getVerifyIDStatus($conn, $username);
		echo $hasil;
	}else if(strcmp($action, "get_statusRenew") == 0){

		$hasil			= getStatusRenew($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "get_statusSecurity") == 0) {
		
		$hasil			= getStatusSecurity($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "post_reqSec") == 0) {
		
		$hasil			= reqSecurityPasswd($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "post_verifyID") == 0) {
		$statusvrid     = ( isset( $_POST['statusvrid'] ) )?fValidateInput( $_POST['statusvrid'] ): '';
	    $idType         = ( isset( $_POST['idType'] ) )?fValidateInput( $_POST['idType'] ): '';
	    $idNumber       = ( isset( $_POST['idNumber'] ) )?fValidateInput( $_POST['idNumber'] ): '';
	    $idNumber       = preg_replace( '/\s/', '', $idNumber );
	    $idFirstName    = ( isset( $_POST['idFirstName'] ) )?fValidateInput( $_POST['idFirstName'] ): '';
    	$idLastName     = ( isset( $_POST['idLastName'] ) )?fValidateInput( $_POST['idLastName'] ): '';
    	$idBOD     = ( isset( $_POST['idBOD'] ) )?fValidateInput( $_POST['idBOD'] ): '';
	    $imageFileType  = strtolower( pathinfo( basename( $_FILES['fileuploadid']['name'] ), PATHINFO_EXTENSION ) );
	    $oldvrFileName  = ( isset( $_POST['oldvrFileName'] ) )?fValidateInput( $_POST['oldvrFileName'] ): '';
			
		$hasil			= postVerifyID($conn,$username, $statusvrid, $idType, $idNumber, $idFirstName, $idLastName, $oldvrFileName, $idBOD, $imageFileType);
		echo $hasil;
	}else if (strcmp($action, "get_benefcry") == 0) {
		
		$hasil			= getBeneficiary($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "post_benefcry") == 0) {

	    $benIdType       = ( isset( $_POST['benIdType'] ) )?fValidateInput( $_POST['benIdType'] ): '';
	    $benIdNum        = ( isset( $_POST['benIdNum'] ) )?fValidateInput( $_POST['benIdNum'] ): '';
	    $benIdNum        = preg_replace('/\s/', '', $benIdNum);
	    $benFirstName    = ( isset( $_POST['benFirstName'] ) )?fValidateInput( $_POST['benFirstName'] ): '';
	    $benLastName     = ( isset( $_POST['benLastName'] ) )?fValidateInput( $_POST['benLastName'] ): '';
	    $benBOD          = ( isset( $_POST['benBOD'] ) )?fValidateInput( $_POST['benBOD'] ): '';
	    $benRelationType = ( isset( $_POST['benRelationType'] ) )?fValidateInput( $_POST['benRelationType'] ): '';
	    $imageFileType   = strtolower(pathinfo(basename($_FILES["benFileID"]["name"]),PATHINFO_EXTENSION));

	    $hasil			 = postBeneficiary($conn, $username, $benIdType, $benIdNum, $benFirstName, $benLastName, $benBOD, $benRelationType, $imageFileType);
	    echo $hasil;
	}else if (strcmp($action, "get_msRelationType") == 0) {
		
		$hasil			= getMsRelationType($conn);
		echo $hasil;
	}else if (strcmp($action, "get_msPaymentType") == 0) {
		
		$hasil			= getMsPaymentType($conn);
		echo $hasil;
	}else if (strcmp($action, "post_changePasswd") == 0) {

	    $currPassword    = ( isset( $_POST['currPassword'] ) )?fValidateSQLFromInput( $conn, $_POST['currPassword'] ): '';
	    $newPassword     = ( isset( $_POST['newPassword'] ) )?fValidateSQLFromInput( $conn, $_POST['newPassword'] ): '';
	    $reNewPassword   = ( isset( $_POST['reNewPassword'] ) )?fValidateSQLFromInput( $conn,  $_POST['reNewPassword'] ): '';

		$hasil			 = postChangePasswd($conn, $username, $currPassword, $newPassword, $reNewPassword);
		echo $hasil;
	}else if (strcmp($action, "post_changeSec") == 0) {
		
		$currSecurity   = ( isset( $_POST['currSecurity'] ) )?fValidateSQLFromInput( $conn, $_POST['currSecurity'] ): '';
    	$newSecurity    = ( isset( $_POST['newSecurity'] ) )?fValidateSQLFromInput( $conn, $_POST['newSecurity'] ): '';
    	$reNewSecurity  = ( isset( $_POST['reNewSecurity'] ) )?fValidateSQLFromInput( $conn, $_POST['reNewSecurity'] ): '';

    	$hasil			= postChangeSecurity($conn, $username, $currSecurity, $newSecurity, $reNewSecurity);
    	echo $hasil;
	}else if (strcmp($action, "post_resetSec") == 0) {
		
	    $emailReset     = ( isset($_POST["emailReset"]))?fValidateInput( $_POST["emailReset"]) : '';
	    $emailReset     = strtolower($emailReset);

	    $hasil			= postResetSecurity($conn, $username, $emailReset);
	    echo $hasil;
	}else if (strcmp($action, "post_bugReport") == 0) {
	
	    $bugOS          = (isset($_POST["bugOS"]))?fValidateSQLFromInput($conn, $_POST["bugOS"]): "";
	    $bugDevice      = (isset($_POST["bugDevice"]))?fValidateSQLFromInput($conn, $_POST["bugDevice"]): "";
	    $bugMenu        = (isset($_POST["bugMenu"]))?fValidateSQLFromInput($conn, $_POST["bugMenu"]): "";
	    $bugDesc        = (isset($_POST["bugDesc"]))?fValidateSQLFromInput($conn, $_POST["bugDesc"]): "";

	    $hasil			= postBugReport($conn, $username, $bugOS, $bugDevice, $bugMenu, $bugDesc);
	    echo $hasil;
	}else if (strcmp($action, "post_login") == 0) {

		$username 		= strtolower($username);
		$platform  		= ( isset($_POST["platform"]))?fValidateSQLFromInput($conn, $_POST["platform"]): "";
	    $password 		= ( isset( $_POST['password'] ) )?fValidateSQLFromInput( $conn, $_POST['password'] ): '';

	    $hasil			= postLogin($conn, $username, $password, $platform);
	    echo $hasil;
	}else if (strcmp($action, "get_carousel") == 0) {
		
		$hasil			= getCarousel($conn);
		echo $hasil;
	}else if (strcmp($action, "checkAppVersion") == 0) {

		$appVerDesc     = (isset($_POST["appVerDesc"]))?fValidateSQLFromInput($conn, $_POST["appVerDesc"]): "";

		$hasil 			= CheckAppVersion($conn, $appVerDesc);
		echo $hasil;
	}else if (strcmp($action, "checkStatusMember") == 0) {
		
		$hasil			= checkStatusMember($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "checkDataMemberForTF") == 0) {
		
		$hasil			= checkDataMemberForTF($conn, $username);
		echo $hasil;
	}else if (strcmp($action, "post_transferVoucher") == 0) {
		
	    $username    = strtolower($username);
	    $transferTo  = (isset($_POST["transferTo"]))?fValidateSQLFromInput($conn, $_POST["transferTo"]): "";
	    $transferTo  = strtolower($transferTo);
	    
	    $amountVoucher   =  (isset($_POST["amountVoucher"]))?fValidateSQLFromInput($conn, $_POST["amountVoucher"]): "0";
	    $numberOfVoucher = $amountVoucher / $DEF_VOUCHER_PRICE_IDR; //@3500
	    $voucherDesc     = (isset($_POST["voucherDesc"]))?fValidateSQLFromInput($conn, $_POST["voucherDesc"]): "";
	    $secPasswd       =  (isset($_POST["secPasswd"]))?fValidateSQLFromInput($conn, $_POST["secPasswd"]): "";

	    $hasil			 = postTransferVoucher($conn, $username, $transferTo, $amountVoucher, $numberOfVoucher, $voucherDesc, $secPasswd);
	    echo $hasil;
	}else{
		echo (resultJSON("error", "Message : ERR=200", ""));
	}
}else{
	echo (resultJSON("error", "Message : ERR=100", ""));
}

fCloseConnection($conn); die();



?>