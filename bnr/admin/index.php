<?PHP
include("../includes/inc_def.php");
include("../includes/inc_conn.php");
include("../includes/inc_functions.php");

$menu=$s=$errno=$msg = "";
$s = (isset($_GET['s']))?$_GET['s']: "";
$errno = (isset($_GET['errno']))?$_GET['errno']: "";
$msg = (isset($_GET['msg']))?$_GET['msg']: "";
$subMenu = (isset($_GET['subMenu']))? $_GET['subMenu'] : "sponsor";

//side nav
$menu = (isset($_GET['menu']))?$_GET['menu']: "dashboard";
if ($menu == "dashboard"){ 
	$pageFile = "dashboard.php";
}else if ($menu == "myProfile"){
	$pageFile = "myProfile.php";
}else if ($menu == "changePasswd"){
	$pageFile = "changePasswd.php";
}else if ($menu == "genv"){
	$pageFile = "genV.php";
}else if ($menu == "voucher"){
	$pageFile = "voucher.php";
}else if ($menu == "member"){
	$pageFile = "mbrship.php";
}else if ($menu == "tradeAcc"){
	if ($subMenu == "onprogress"){
		$pageFile = "tradeOnProgress.php";
	}else if ($subMenu == "renew"){
		$pageFile = "tradeRenew.php";
	}else if ($subMenu == "pending"){
		$pageFile = "tradePending.php";
	}else if ($subMenu == "active"){
		$pageFile = "tradeActive.php";
	}else if ($subMenu == "tradeoff"){
		$pageFile = "tradeOff.php";
	}else if ($subMenu == "affiliasi"){
		$pageFile = "tradeAff.php";
	}else if ($subMenu == "reqreset"){
		$pageFile = "tradeReqReset.php";
	}
	
}else if ($menu == "deposit"){
	$pageFile = "incomingDepo.php";
}else if ($menu == "withdrawal"){
	$pageFile = "requestWd.php";
}else if ($menu == "bonus"){
	$pageFile = "bns.php";
}else if ($menu == "announcement"){
	$pageFile = "publicInfo.php";
}else if ($menu == "report"){
	if ($subMenu == "reportAchiever"){
		$pageFile = "achiever.php";
	}else if ($subMenu == "reportPromo"){
		$pageFile = "reportPromo.php";	
	}else if ($subMenu == "reportMember"){
		$pageFile = "reportMember.php";
	}
}else if ($menu == "auth") {
	$pageFile = "logInBO.php";
	//header("Location: ".$pageFile);
	include_once($pageFile);
	die();
}else if ($menu == "logout") {
	$pageFile = "logOutBO.php";
	//header("Location: ".$pageFile);
	header("Location: ".$COMPANY_SITE."admin/".$pageFile."?unxid=".md5(time()));
  //include_once($pageFile);
	die();
}

//below login and logout
include_once("../includes/inc_session_admin.php");

function setActive($menu, $section){
	if ($menu == $section) return "active";
	return "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Back Office - <?php echo $COMPANY_NAME; ?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Favicons -->
	<link rel="icon" href="../images/favicon.png" sizes="16x16 32x32" type="image/png">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="../assets/css/newBinary.css">
	<script src="../assets/js/plugins/ez-plus.js"></script>
	<script src="../assets/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/plug-ins/1.10.20/api/sum().js"></script>
	<script src="../assets/js/dataTables.bootstrap4.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
	<style>
		body{background-color:#f1f1f1}.row.content{height:450px}.sidenav{background-color:#f1f1f1;height:100%}.sidenav{padding-left:0}.affix{top:38px}a{color:#333}a:hover{color:#06c}.subTitle{font-size:36px;padding-left:0}.next{width:100px;color:red}.well{background-color:#fff}@media screen and (max-width:767px){.row.content{height:auto}}
	</style>
</head>
<body>
	<div class="container-fluid">
		<div class="col-md-12">&nbsp;
		</div>
		<div class="col-md-2">
			<ul class="nav nav-pills nav-stacked well" >
				<li class="<?php echo (setActive($menu, "dashboard")); ?>"><a href="?menu=dashboard">Dashboard</a></li>
				<li><a href="#profile" data-toggle="collapse">Profile<span class="caret"></span></a>
					<div id="profile" class="collapse">
						<ul class="nav nav-pills nav-stacked">
							<li class="<?php echo (setActive($menu, "myProfile")); ?>"><a href="?menu=myProfile&subMenu=myProfile"><i class="fa fa-user"></i> My Profile</a></li>
							<li class="<?php echo (setActive($menu, "passwd")); ?>"><a href="?menu=changePasswd&subMenu=passwd"><i class="fa fa-lock"></i> Change Password/Security</a></li>
						</ul>
					</div>
				</li>
				<li class="<?php echo (setActive($menu, "genv")); ?>"><a href="?menu=genv">Generate PIN</a></li>
				<li class="<?php echo (setActive($menu, "voucher")); ?>"><a href="?menu=voucher">PIN</a></li>
				<li class="<?php echo (setActive($menu, "member")); ?>"><a href="?menu=member">Member</a></li>
				<li>
					<a href="#collapsedTrade" data-toggle="collapse">Trading Account<span class="caret"></span></a>
					<div id="collapsedTrade" class="collapse">
						<ul class="nav nav-pills nav-stacked">
							<li class="<?php echo (setActive($subMenu, "onprogress")); ?>"><a href="?menu=tradeAcc&subMenu=onprogress"><i class="fa fa-clock-o"></i> On Progress</a></li>
							<li class="<?php echo (setActive($subMenu, "renew")); ?>"><a href="?menu=tradeAcc&subMenu=renew"><i class="fa fa-refresh"></i> Renew</a></li>
							<li class="<?php echo (setActive($subMenu, "pending")); ?>"><a href="?menu=tradeAcc&subMenu=pending"><i class="fa fa-stop"></i> Stopped (Pending)</a></li> 
							<li class="<?php echo (setActive($subMenu, "active")); ?>"><a href="?menu=tradeAcc&subMenu=active"><i class="fa fa-check"></i> Active</a></li>
							<li class="<?php echo (setActive($subMenu, "tradeoff")); ?>"><a href="?menu=tradeAcc&subMenu=tradeoff"><i class="fa fa-power-off"></i> Trade Off</a></li>
							<li class="<?php echo (setActive($subMenu, "affiliasi")); ?>"><a href="?menu=tradeAcc&subMenu=affiliasi"><i class="fa fa-handshake-o"></i> Affiliasi</a></li>
							<li class="<?php echo (setActive($subMenu, "reqreset")); ?>"><a href="?menu=tradeAcc&subMenu=reqreset"><i class="fa fa-recycle"></i> Request Reset</a></li>
						</ul>
					</div>
				</li>
				<li class="<?php echo (setActive($menu, "deposit")); ?>"><a href="?menu=deposit">Deposit</a></li>
				<li class="<?php echo (setActive($menu, "withdrawal")); ?>"><a href="?menu=withdrawal">Withdrawal</a></li>
				<li><a href="#collapsedMenu" data-toggle="collapse">Bonus<span class="caret"></span></a>
					<div id="collapsedMenu" class="collapse">
						<ul class="nav nav-pills nav-stacked">
							<li class="<?php echo (setActive($subMenu, "sponsor")); ?>"><a href="?menu=bonus&subMenu=sponsor"><i class="fa fa-user"></i> Sponsor</a></li>
							<li class="<?php echo (setActive($subMenu, "pu")); ?>"><a href="?menu=bonus&subMenu=pu"><i class="fa fa-level-up"></i> Passed-Up</a></li>
							<li class="<?php echo (setActive($subMenu, "pairing")); ?>"><a href="?menu=bonus&subMenu=pairing"><i class="fa fa-balance-scale"></i> Pairing</a></li> 
							<li class="<?php echo (setActive($subMenu, "matching")); ?>"><a href="?menu=bonus&subMenu=matching"><i class="fa fa-check"></i> Mega Matching</a></li>
							<li class="<?php echo (setActive($subMenu, "comm")); ?>"><a href="?menu=bonus&subMenu=comm"><i class="fa fa-percent"></i> Commission</a></li>
						</ul>
					</div>
				</li>
				<li><a href="#collapsedReport" data-toggle="collapse">Report<span class="caret"></span></a>
					<div id="collapsedReport" class="collapse">
						<ul class="nav nav-pills nav-stacked">
							<li class="<?php echo (setActive($subMenu, "reportAchiever")); ?>"><a href="?menu=report&subMenu=reportAchiever"><i class="fa fa-trophy"></i> Achiever</a></li>
							<li class="<?php echo (setActive($subMenu, "reportPromo")); ?>"><a href="?menu=report&subMenu=reportPromo"><i class="fa fa-tag"></i> Promo</a></li>
							<li class="<?php echo (setActive($subMenu, "reportMember")); ?>"><a href="?menu=report&subMenu=reportMember"><i class="fa fa-user"></i> Member</a></li>
						</ul>
					</div>
				</li>
				<li><a href="?menu=announcement">Announcement</a></li>
				<li><a href="?menu=logout">Logout</a></li>
			</ul>
		</div>
		<div class="col-md-10">
			<div class="col-sm-12" style="height: 100px;">
				<div class="col-md-12" style="height: 70px;">
					<a class="navbar-brand" href="#"><img src="../images/<?php echo $COMPANY_LOGO_L ?>" /></a>
				</div>
				<!-- <div class="col-md-12">a Robot for Your Brighten Future</div> -->
			</div>
			<div class="col-sm-12">
				<?php
				include($pageFile);
				?>
			</div>
		</div>
	</div>
	<footer class="container-fluid text-center">&copy; Copyright 2020</footer>
</body>
</html>
<?php fCloseConnection($conn); ?>