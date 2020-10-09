<?php
$COMPANY_NAME = "VisionEA";
$COMPANY_LOGO   = "Logo-VisionEA.png";
$COMPANY_LOGO_S = "visionea.png";
$COMPANY_LOGO_L = "Logo-VisionEA-full-trans.png";
$COMPANY_DESCRIPTION = "[]";


$httphost = strtolower($_SERVER['HTTP_HOST']);
if (strpos($httphost, "localhost") === false && strpos($httphost, "192.168.1.19") === false){
	$DOMAIN_URL = "https://visionea.net/";
	$COMPANY_SITE = $DOMAIN_URL . "bnr/"; //"http://localhost/project/"; //must start with http or https
	$DEF_URL_MYMAC = "https://mymac.id/";
}else{
	$DOMAIN_URL = "http://localhost/vision_new/";
	// $DOMAIN_URL = "http://192.168.1.19/vision_new/";
	$COMPANY_SITE = $DOMAIN_URL . "bnr/";
	$DEF_URL_MYMAC = "http://localhost/mymac/";
}
$DEF_URL_MYMAC_API = $DEF_URL_MYMAC."member/api/";

date_default_timezone_set('Asia/Singapore');
$CURRENT_TIME = date("Y-m-d H:i:s");
//$hourToStart = 23;
//$startMaintenanceTime   = date("Y-m-d H:i:s",  (mktime(0, 0, 0, date("n"), date("j") , date("Y")) - (1 * 60 * 60))); //-1 hours (23:00 (pm))
//$endMaintenanceTime     = date("Y-m-d H:i:s", (mktime(0, 0, 0, date("n"), date("j") , date("Y")) + (1 * 60 * 60))); //+1 hours (01:00 (am))

//Start in the morning
$startMaintenanceTime   = date("Y-m-d H:i:s", (mktime(0, 0, 0, date("n"), date("j") , date("Y")) )); // (00:00)
$endMaintenanceTime     = date("Y-m-d H:i:s", (mktime(2, 0, 0, date("n"), date("j") , date("Y")))); 
//$endMaintenanceTime     = date("Y-m-d H:i:s", (mktime(0, 0, 0, date("n"), date("j") , date("Y")) + (2 * 60 * 60))); //+2 hours (02:00 (am))
//
$DEF_MUTASI_DATE = "2020-06-08"; //tgl mutasi dari sistem lama ke sistem baru dan bonus pairing dari 10% jadi 5% (Vision ke E-Book)


//date_default_timezone_set("Asia/Bangkok");
//echo date_default_timezone_get();
//echo (date("Y-m-d H:i:s")); //current date base on default set timezone
/*
echo "date: " . date("Y-m-d H:i:s");
date_default_timezone_set('Asia/Singapore');
echo "<br>date: " . date("Y-m-d H:i:s");
date_default_timezone_set('Asia/Bangkok');
echo "<br>date: " . date("Y-m-d H:i:s");
die();
*/


//===  LOCKED   =============
$DEF_STATUS_CANCEL		= "0";
$DEF_STATUS_PENDING 	= "1"; //Pending/New Register
$DEF_STATUS_ONPROGRESS 	= "2";
$DEF_STATUS_COMPLETE 	= "3"; //Complete
$DEF_STATUS_APPROVED	= "4";	
$DEF_STATUS_DECLINED 	= "5"; //Declined
$DEF_STATUS_BLOCKED 	= "6";
$DEF_STATUS_REFUND 		= "7"; 
$DEF_STATUS_ACTIVE 		= "8";
$DEF_STATUS_USED 		= "9";
$DEF_STATUS_NEW			= "10";
$DEF_STATUS_UPGRADE		= "11";
$DEF_STATUS_REQUEST		= "12";

$DEF_STATUS_NOT_YET_SENT= "20";
$DEF_STATUS_SENT		= "21";
$DEF_STATUS_SENT_FAILED	= "22";
//===========================

//Message status
$DEF_STATUS_UNREAD		= "0";
$DEF_STATUS_READ 		= "1";
$DEF_STATUS_REPLIED		= "2";

//app version
$DEF_APP_VERSION_BLOCK 	= '1';
$DEF_APP_VERSION_UPDATE = '3';
$DEF_APP_VERSION_ALLOW 	= '5';


// LIST REWARD ACHIEVEMENT
$RWD_ID_BRONZE 			= "1";
$RWD_ID_SILVER 			= "2";
$RWD_ID_GOLD 			= "3";
$RWD_ID_RUBY 			= "4";
$RWD_ID_EMERALD 		= "5";
$RWD_ID_SAPPHIRE 		= "6";
$RWD_ID_DIAMOND 		= "7";
$RWD_ID_KINGDIAMOND 	= "8";


//BTC ADDRESS =============
$DEF_BTC_ADDR_1		= "15Zkk24dhFXQNyjXeZqff8616XnsQ24rt9";
$DEF_BTC_ADDR_2		= "18bGxpjwfgF5xpcahYRqUDaiseXsmB1udt";
//===================
//BCA ADDRESS =============
$DEF_BANK_BCA 			= "BCA";
$DEF_BANK_BCA_ACC 		= "198 9898 289";
$DEF_BANK_BCA_ACC_NAME	= "VISIONEA TEKNOLOGI PERKASA PT"; //"HERY GUNAWAN";
//BNI ADDRESS =============
$DEF_BANK_BRI 			= "BRI";
$DEF_BANK_BRI_ACC 		= "kosong";
$DEF_BANK_BRI_ACC_NAME	= "VISIONEA TEKNOLOGI PERKASA PT"; //"HERY GUNAWAN";


//CATEGORY: dtReqreset
$DEF_CATEGORY_RESET_PASSWORD="PASSWORD";

$DEF_MININUM_WITHDRAWAL	= 1000000; // 1jt
$DEF_MIN_BUY_VOUCHER    = 10;

//LIST EA CODE
$DEF_EACODE_BLUEPIPS = "78";
$DEF_EACODE_CHRONOS = "79";


//VOUCHER ____________________________________
$DEF_VOUCHER_USED_FOR_TRANSFER 	 = "TRANSFER";
$DEF_VOUCHER_USED_FOR_ACTIVATION = "ACTIVATION";
$DEF_VOUCHER_USED_FOR_CONVERT	 = "CONVERT";
$DEF_VOUCHER_USED_FOR_RO 		 = "REPEAT ORDER";
$DEF_VOUCHER_USED_FOR_RS  		 = "RESELLER";
$DEF_CATEGORY_INTERNAL_TRANSFER = "INTT";
$DEF_CATEGORY_BANK 				= "BANK";
$DEF_TRANSFER_VOUCHER		= "TV"; // Internal transfer voucher
$DEF_TRANSFER_VOUCHER_VPS	= "TVPS"; // Internal transfer voucher
$DEF_CONVERT_BNS_VOUCHER	= "CBV"; //Convert Bonus to Voucher STD (Internal Transfer)
$DEF_CONVERT_WALLET_VOUCHER = "CWV"; //Convert Wallet to Voucher VPS (Internal Transfer)
$DEF_VOUCHER_PRICE 			= 2800000;
$DEF_WALLET_PRICE 			= 3500000;
$DEF_VOUCHER_PRICE_VPS		= 0;//(5 / 200) * 2800000;
$DEF_BV_PRICE  				= 2800000;
$DEF_VOUCHER_PRICE_IDR 		= 3500000;

//Bonus Pairing (persentase)
$DEF_BONUS_10_PAIRING 	= 10; //bonus sebelum mutasi
$DEF_BONUS_5_PAIRING 	= 5;
$DEF_BONUS_RO 			= 25;

$DEF_VOUCHER_TYPE_STD 	= "STD";
$DEF_VOUCHER_TYPE_VPS	= "VPS";

$DEF_EBOOK_BASIC 	= "bs";
$DEF_EBOOK_PRO 		= "pr";
$DEF_TYPE_PURCHASE_RO 		= "REPEATORDER";
$DEF_TYPE_PURCHASE_RS 		= "RESELLER";
$DEF_TYPE_PURCHASE_ACT 		= "ACTIVATION";
$DEF_TYPE_PURCHASE_RENEW 	= "RENEW";

//NETWORK ________________________________
$DEF_COMPANY_NODE = "VISIONEA";
//images used by network tree
$imgSrcUserST = "../images/iconStarter.png";
$imgSrcUserPR = "../images/iconPremium.png";
$imgSrcUserVIP = "../images/iconVIP.png";

$imgSrcPlus = "../images/avatarMalePlus.png";
$imgSrcLock = "../images/avatarLock.jpg";


$DEF_NUM_PER_PAGE = 25;

//SOSMED _________________
$DEF_LINK_FB 		= "#";
$DEF_LINK_IG 		= "#";
$DEF_LINK_TWITTER	= "#";


//MAIL__________________________________
$EMAIL_HOST		= 'smtp.hostinger.co.id'; //'mx1.hostinger.co.id'; //'smtp.hostinger.co.id'; //'wpiix3.rumahweb.com'; 
$EMAIL_USERNAME = "support@visionea.net";

//NO-REPLY
$EMAIL_NO_REPLY	= "no-reply@visionea.net";
$NO_REPLY_AUTH	= "EA_J4v3l!n"; //"biC6a[Zwpdpj888"; //

//SUPPORT
//$EMAIL_SUPPORT	= "support@visionea.net";
//$SUPPORT_AUTH	= "EA_J4v3l!n"; //"biC6a[Zwpdpj888"; //

//SUPPORT
$EMAIL_SUPPORT	= "support-id@visionea.net";
$SUPPORT_AUTH	= "T3amVisionEA"; //"biC6a[Zwpdpj888"; //


//FINANCE
$EMAIL_FINANCE	= "finance@visionea.net"; //CREATE NEW EMAIL PLEASE
$FINANCE_AUTH	= "EA_J4v3l!n"; //"biC6a[Zwpdpj888"; //

//Marketing
$EMAIL_MARKETING	= "marketing@visionea.net"; //CREATE NEW EMAIL PLEASE
$MARKETING_AUTH		= "VEAmarketing889"; 

//images for header email
$imgSrcHeaderEmail = "headerEmailVisionEA.jpg";


//POSTING _____________
$USER_POSTING = "SYSPOST"; //Corn
//$USER_POSTING_AUTH = "Rah45!a888"; //change posting corn (on schedule setting)


//COMMISSION RULE ________________________
$MIN_SPONSOR = 2; //Minimal Sponsorship to get commission of pairing and matching
?>