<?php 
	$sUserName = $_SESSION["sUserName"];
	$sName		= $_SESSION["sFirstName"];
	include_once("../includes/inc_conn.php");
	include_once("../includes/inc_func_admin.php");
	include_once("../includes/inc_def.php");
	include_once("../includes/inc_commission.php");	
?>
<script>
function num_member(){
	var mbrMonth   = $("#mbr_month").val();
	var mbrYear    = $("#mbr_year").val();
	$.get("load_num_member.php",
	{
		"mbr_month"   : mbrMonth,
		"mbr_year"    : mbrYear,
		"type"        : "MEMBER"
	},
	function(data, status){
		// console.log(data);
  		$myDataObj = JSON.parse(data);
		if ($.trim($myDataObj["status"])=="success"){
			$("#mbr_New").html($myDataObj['jlhNew']);
			$("#mbr_Renew").html($myDataObj['jlhReNew']);
			$("#mbr_RO").html($myDataObj['jlhRO']);
			$("#adtMsg").html($myDataObj['adtMsg']);
		}else{
			$('#mbr_info').html("Error Get Member Data");
		}
	});
}

$(document).ready(function(){
	//NEW MEMBER
	num_member();
	$("#mbr_month").change(function(){
  		num_member();
	});
	$("#mbr_year").change(function(){
		$("#mbr_month").attr("disabled", false);
		if ($(this).val() == "0"){
			$("#mbr_month").attr("disabled", true);
		}
  		num_member();
	});
});
	
</script>

<div class="col-sm-12">
	<div class="well">
		<h4>Welcome <?php echo $sName; ?>,</h4>
	</div>
	<div class="row">
		<div class="col-sm-4">
			<div class="well" style="min-height: 200px;">
				<h4>Member</h4>
				<select id='mbr_year' class="small">
					<option value='0'>Year</option>
					<?php
					$CURRENT_YEAR = date("Y");
					for ($thn=2020; $thn<=$CURRENT_YEAR; $thn++){
						$selected = ($thn == $CURRENT_YEAR)?"selected":"";
						echo ("<option value='$thn' $selected>$thn</option>");
					}
					?>
				</select>
				<select id='mbr_month' name='mbr_month' class="small">
					<?php
					$arrBulan = array("Month", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
					$currMonth = isset($_GET['mbr_month'])?$_GET['mbr_month']:date("m");
					for ($i=0; $i<=12; $i++){
						if ($i<10){
							$mbr_month = "0".$i;
						}else{
							$mbr_month = $i;
						}
						$selected = "";
						if ($mbr_month == $currMonth) $selected = " selected ";
						echo ("<option value='$i' $selected> $arrBulan[$i] </option>");
					}
					?>
				</select>
				

				<div class="row" id="mbr_info">
					<div class="col-md-12"><small class="text-warning" id="adtMsg"></small></div>
					<div class="col-md-6">New</div>
					<div class="col-md-6" id="mbr_New"></div>
					<div class="col-md-6">Renew</div>
					<div class="col-md-6" id="mbr_Renew"></div>
					<div class="col-md-6">RO</div>
					<div class="col-md-6" id="mbr_RO"></div>
				</div>
			</div>
		</div>
		<div class="col-sm-4">
			<div class="well" style="min-height: 200px;">
				<h4>PIN</h4>
				<div class="row">
					<?php 
					$totalPINActive = $totalPINUsed = $totalPINTransfer = $totalPIN = 0;
				  	$myDataObj  = json_decode(fGetNumOfPIN($conn));
					if ($myDataObj->{"status"} == "success"){
						$totalPIN       = $myDataObj->{'totalPIN'};
						$totalPINActive = $myDataObj->{'totalPINActive'};
						$totalPINUsed   = $myDataObj->{'totalPINUsed'};
					}
					?>
					<div class="col-md-6">New  </div><div class="col-md-6"><?php echo numFormat($totalPINActive, 0) ?></div>
					<div class="col-md-6">Used </div><div class="col-md-6"><?php echo numFormat($totalPINUsed, 0) ?></div>
					<div class="col-md-6">Total</div><div class="col-md-6"><?php echo numFormat($totalPIN, 0) ?></div>
				</div>
			</div>
		</div>
		<!-- POSTING Pairing Data -->
		<div class="col-sm-4">
			<div class="well" style="min-height: 200px;">
				<?php
				$sql = "SELECT pairDate, COUNT(*) AS n, SUM(pairLeft) AS tLeft, SUM(pairRight) AS tRight, SUM(pairTO) AS tTO FROM dtDailyPairing ";
				$sql .= " GROUP BY pairDate ORDER BY pairDate DESC LIMIT 1";
				$res = $conn->query($sql);
				if ($row = $res->fetch_assoc()){
					?>
					<h4 class="text-success">Latest Pairing Info</h4> 
					<div class="row">
						<div class="col-md-6">Date</div><div class="col-md-6"><?php echo ($row['pairDate']); ?></div>
					</div>
					<div class="row">
						<div class="col-md-6">Num of Pairing</div><div class="col-md-6"><?php echo ($row['n']); ?></div>
					</div>
					<div class="row">
						<div class="col-md-4">Left</div><div class="col-md-4">Right</div><div class="col-md-4">PayOut</div>
					</div>
					<div class="row">
						<div class="col-md-4"><?php echo numFormat($row['tLeft'], 0); ?></div><div class="col-md-4"><?php echo numFormat($row['tRight'], 0); ?></div><div class="col-md-4"><?php echo numFormat($row['tTO'], 0); ?></div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>

	<div class="row">
		<!-- Withdrawal -->
		<div class="col-sm-4">
			<div class="well" style="min-height: 200px;">
				<h3 class="text-danger">Withdrawal Request</h3> 
				<?php
				$sql = "SELECT count(*) as numofwd, wdStID, SUM(wdAmount) as sumamount, SUM(wdFee) as sumfee FROM dtWDFund ";
				$sql .= " WHERE (wdStID='" . $DEF_STATUS_ONPROGRESS . "' OR wdStID='" . $DEF_STATUS_REQUEST . "')";
				$sql .= " GROUP BY wdStID HAVING numofwd > 0 ORDER BY wdStID ASC";

				$res = $conn->query($sql);
				if ($res->num_rows > 0){
					$status = "";
					while ($row = $res->fetch_assoc()){
						if ($row['wdStID'] == $DEF_STATUS_ONPROGRESS) $status = "On Progress (confirmed)";
						if ($row['wdStID'] == $DEF_STATUS_REQUEST) $status = "Incoming Request (not yet confirmed)";
						?>
						<h5 class="text-primary"><?php echo ($status); ?></h5>
						<div class="row">
							<div class="col-md-6">Num of WD</div><div class="col-md-6"><?php echo($row['numofwd']); ?></div>
						</div>
						<div class="row">
							<div class="col-md-6">Ttl Amount</div><div class="col-md-6"><?php echo($row['sumamount']); ?></div>
						</div>
						<?php
					}
				}else{
				?>
				<h5 class="text-info">No Withdrawal Request</h5>
				<?php
				} 
				?>
			</div>
		</div>
		<!-- Balance -->
		<div class="col-sm-4">
			<div class="well" style="min-height: 200px;">
				<h4 class="text-info">Balance</h4>
				<?php
				$tSponsor = $tPassedUp = $tPairing = $MegaMatching = 0;
				$myDataObj = json_decode(fAllCommissionSponsorship($conn));
				if ($myDataObj->{"status"} == "success"){
					$tSponsor = $myDataObj->total;
				}
				$myDataObj = json_decode(fAllCommissionPassedUP($conn));
				if ($myDataObj->{"status"} == "success"){
					$tPassedUp = $myDataObj->total;
				}
				$myDataObj = json_decode(fAllCommissionPairing($conn));
				if ($myDataObj->{"status"} == "success"){
					$tPairing = $myDataObj->total;
				}

				$myDataObj = json_decode(fAllCommissionMatching($conn));
				if ($myDataObj->{"status"} == "success"){
					$MegaMatching = $myDataObj->total;
				}

				$myDataObj = json_decode(fAllSumConvert($GLOBALS['DEF_VOUCHER_TYPE_VPS'], $conn));
				if ($myDataObj->{"status"} == "success"){
					$tConvertVPS = $myDataObj->total;
				}
				$tBonus = $tSponsor + $tPassedUp + $tPairing + $MegaMatching;
				$tWalletGross = $tBonus * 0.2;
				$tWallet = ceil($tWalletGross - $tConvertVPS);
				$tBalance = $tWallet + $MegaMatching;
				// echo "tSponsor : $tSponsor || tPassedUp : $tPassedUp || tPairing : $tPairing || MegaMatching : $MegaMatching <br>";
				// echo "tBonus : $tBonus ";
				?> 
				<div class="row">
					<div class="col-md-6">Wallet</div><div class="col-md-6"><?php echo numFormat($tWallet, 0); ?></div>
				</div>
				<div class="row">
					<div class="col-md-6">Mega Matching</div><div class="col-md-6"><?php echo numFormat($MegaMatching, 0); ?></div>
				</div>
				<div class="row">
					<div class="col-md-6">Total</div><div class="col-md-6"><?php echo numFormat($tBalance, 0); ?></div>
				</div>
                <hr>
                <div class="row">
                	<div class="col-md-12 text-right">
		                <i class="fa fa-link text-danger"></i>
		                <a href="./?menu=bonus" target="_parent">Get more detail...</a>
		            </div>
		        </div>
			</div>
		</div>

		<div class="col-sm-4">
			<div class="well" style="min-height: 200px;">
				<h4 class="text-success"></h4> 
				<div class="row">
					<div class="col-md-6"></div><div class="col-md-6"></div>
				</div>
				<div class="row">
					<div class="col-md-6"></div><div class="col-md-6"></div>
				</div>
			</div>
		</div>
	</div>

</div>