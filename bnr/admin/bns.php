<?php
include_once("../includes/inc_conn.php");
include_once("../includes/inc_func_admin.php");
include_once("../includes/inc_def.php");
include_once("../includes/inc_commission.php");
$q = (isset($_GET["q"]))?$_GET["q"]: "";
?>
<?php
$page = (isset($_GET['page']))? $_GET['page'] : 1;
$pageActive = (isset($_GET['pageActive']))? $_GET['pageActive'] : 1;
$numPerPage = $DEF_NUM_PER_PAGE;


//$menu = (isset($_GET['menu']))? $_GET['menu'] : ""; //==> get from parent page
$subMenu = (isset($_GET['subMenu']))? $_GET['subMenu'] : "sponsor";

?>
<script>
	$(document).ready(function(e) {
		// tabel sponsor
		var tBnsSponsor = $('#tBnsSponsor').DataTable({
			lengthMenu : [25,50,100,200,500]
		});
		var sumBnsSponsor = tBnsSponsor.column(2).data().sum();
		$("#sumBnsSponsor").html(sumBnsSponsor.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));

		// tabel PU
		var tBnsPU = $('#tBnsPU').DataTable({
			lengthMenu : [25,50,100,200,500]
		});
		var sumBnsPU = tBnsPU.column(2).data().sum();
		$("#sumBnsPU").html(sumBnsPU.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));

		// tabel Pairing
		var tBnsPairing = $('#tBnsPairing').DataTable({
			lengthMenu : [25,50,100,200,500]
		});
		var sumBnsPairing = tBnsPairing.column(4).data().sum();
		$("#sumBnsPairing").html(sumBnsPairing.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));

		// tabel MM
		var tBnsMatching = $('#tBnsMatching').DataTable({
			lengthMenu : [25,50,100,200,500]
		});
		var sumBnsMatching = tBnsMatching.column(2).data().sum();
		$("#sumBnsMatching").html(sumBnsMatching.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));

		//total bonus commission
		var totalBnsCommission = sumBnsSponsor + sumBnsPU + sumBnsPairing + sumBnsMatching;
		$("#totalBnsCommission").html(totalBnsCommission.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));
		
		//wallet
		var totalGrossWallet = Math.ceil(totalBnsCommission * (20/100)); // 20% of total bonus
		$("#totalGrossWallet").html(totalGrossWallet.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));

		var totalConvVPS 	= parseInt($("#totalConvVPS").html());
		$("#totalConvVPS").html(totalConvVPS.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));

		var totalNetWallet 	= totalGrossWallet - totalConvVPS;
		$("#totalNetWallet").html(totalNetWallet.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));
		
		//commission
		var totalGrossComm = Math.ceil(totalBnsCommission * (80/100)); // 80% of total bonus
		$("#totalGrossComm").html(totalGrossComm.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));

		var totalConv 		= parseInt($("#totalConv").html());
		$("#totalConv").html(totalConv.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));
		var totalWD 		= parseInt($("#totalWD").html());
		$("#totalWD").html(totalWD.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));
		var totalNetComm 	= totalGrossComm - totalConv - totalWD;
		$("#totalNetComm").html(totalNetComm.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));	

		var tCommission = $('#tCommission').DataTable({
			lengthMenu : [25,50,100,200,500]
		});
		var tComm = tCommission.column(2).data().sum();
		$("#tComm").html(tComm.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));
		var tConvSTD = tCommission.column(3).data().sum();
		$("#tConvSTD").html(tConvSTD.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));
		var tWD = tCommission.column(4).data().sum();
		$("#tWD").html(tWD.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));
		var tNettComm = tCommission.column(5).data().sum();
		$("#tNettComm").html(tNettComm.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' }));
	});
</script>
<span id="q" title="<?php echo $q; ?>"></span>
<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Commission</div>
		<div class="well">
			<div class="row">
				<div class="col-md-12">
					<h3 class="text-center">Total Bonus Commission : <span id="totalBnsCommission"></span></h3>
				</div>
				<div class="col-md-6">
					<div class="table-responsive">
						<table class="table table-striped">
							<tr>
								<td>Gross Wallet <small>(20% of total bonus)</small></td><td>:</td><td class="text-right" id="totalGrossWallet"></td>
							</tr>
							<?php
							// total convert vps
							$myDataObj = json_decode(fAllSumConvert($GLOBALS['DEF_VOUCHER_TYPE_VPS'], $conn));
							$tConvertVPS = 0;
							if ($myDataObj->{"status"} == "success"){
								$tConvertVPS = $myDataObj->total;
							}
							?>
							<tr>
								<td>Total Convert VPS</td><td>:</td><td class="text-right" id="totalConvVPS"><?php echo $tConvertVPS ?></td>
							</tr>
							<tr>
								<td>Net Wallet <small>(Gross Wallet - Total Convert VPS)</small></td><td>:</td><td class="text-right" id="totalNetWallet"></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="col-md-6">
					<div class="table-responsive">
						<table class="table table-striped">
							<tr>
								<td>Gross Commission <small>(80% of total bonus)</small></td><td>:</td><td class="text-right" id="totalGrossComm"></td>
							</tr>
							<?php
							// total convert standard
							$myDataObj = json_decode(fAllSumConvert($GLOBALS['DEF_VOUCHER_TYPE_STD'], $conn));
							$tConvert = 0;
							if ($myDataObj->{"status"} == "success"){
								$tConvert = $myDataObj->total;
							}
							?>
							<tr>
								<td>Total Convert</td><td>:</td><td class="text-right" id="totalConv"><?php echo $tConvert; ?></td>
							</tr>
							<?php
							// total withdrawal
							$myDataObj = json_decode(fAllSumWithdrawal($conn));
							$tWithdrawal = 0;
							if ($myDataObj->{"status"} == "success"){
								$tWithdrawal = $myDataObj->ttlWD;
							}
							?>
							<tr>
								<td>Total Withdrawal</td><td>:</td><td class="text-right" id="totalWD"><?php echo $tWithdrawal; ?></td>
							</tr>
							<tr>
								<td>Net Commission <small>(Gross Commission - Total Convert - Total Withdrawal)</small></td><td>:</td><td class="text-right" id="totalNetComm"></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "sponsor")); ?>"><a data-toggle="tab" href="#sponsor">Sponsor</a></li>
			<li class="<?php echo (setActive($subMenu, "pu")); ?>"><a data-toggle="tab" href="#pu">Passed-up</a></li>
			<li class="<?php echo (setActive($subMenu, "pairing")); ?>"><a data-toggle="tab" href="#pairing">Pairing</a></li>
			<li class="<?php echo (setActive($subMenu, "matching")); ?>"><a data-toggle="tab" href="#matching">Matching</a></li>
			<li class="<?php echo (setActive($subMenu, "comm")); ?>"><a data-toggle="tab" href="#comm">Commission</a></li>
		</ul>
		<div class="tab-content">
			<div id="sponsor" class="tab-pane fade in <?php echo (setActive($subMenu, "sponsor")); ?>">
				<h3>Sponsor</h3>
				<!--- Sponsor ---------->
				<?php       
				// $sql  = "SELECT COUNT( DISTINCT (bnsSpUsername)) totalRec FROM dtBnsSponsor";
		   		//$sql  .= " GROUP BY bnsSpUsername";
				// $query = $conn->query($sql);
				// $row = $query->fetch_assoc();
				// $totalRec = $row['totalRec'];
				// $numPages = ceil ($totalRec / $numPerPage); 
				// $pageActive = ($pageActive<1)?1:$pageActive;        
				// $startRec = ($pageActive-1) * $numPerPage;

				$sql  = "SELECT bnsSpUsername, SUM(bnsSpAmount) as bnsTotal FROM dtBnsSponsor";
				$sql  .= " GROUP BY bnsSpUsername";
		  		// $sql .= " LIMIT " . $startRec . ", " . $numPerPage;
				$query = $conn->query($sql);
				?>
				<div class="row">
					<div class="col-md-10">
						<h3 class="text-right">Total Bonus Sponsor : <span id="sumBnsSponsor"></span></h3>
						<table class="table table-hover table-striped" id="tBnsSponsor">
							<thead>
								<tr>
									<th>No</th>
									<th>Sponsor</th>
									<th class="text-right">Bonus</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ($query->num_rows == 0){
									echo "<tr><td colspan=2 class='text-center text-primary'>no record</td></tr>";  
								}
								$gTotal = $i = 0;
								while ($row = $query->fetch_assoc()){
									$i++;
									$gTotal += $row["bnsTotal"];
									?>
									<tr>
										<td><?php echo $i; ?></td>
										<td><?php echo $row["bnsSpUsername"] ?></td>
										<td class="text-right"><?php echo $row["bnsTotal"] ?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
						<!-- pagination --->
						<!-- <div class="row text-right">
							<ul class="pagination">
								<?php 
								$prev = $next = ""; 
								if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
								if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
								?>
								<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=sponsor&pageActive=' . $pagePrev); ?>">Previous</a></li>
								<?php 
								for ($i=1; $i<=$numPages; $i++){
									$active = "";
									if ($i == $pageActive) $active = "active";
									echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=sponsor&pageActive=$i'>$i</a></li>";
								}
								?>
								<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=sponsor&pageActive=' . $pageNext); ?>">Next</a></li>
							</ul>&nbsp;&nbsp;&nbsp;&nbsp;
						</div> -->
					</div> 
				</div>
			</div>
			<div id="pu" class="tab-pane fade in <?php echo (setActive($subMenu, "pu")); ?>" >
				<h3>Passed-up</h3>
				<!--- Passed-up ---------->
				<?php 
				// $sql = "SELECT COUNT(DISTINCT(bnsPUUsername)) totalRec FROM dtBnsPassedUp";
				// $query = $conn->query($sql);
				// $row = $query->fetch_assoc();
				// $totalRec = $row['totalRec'];
				// $numPages = ceil ($totalRec / $numPerPage); 
				// $pageActive = ($pageActive<1)?1:$pageActive;    
				// $startRec = ($pageActive-1) * $numPerPage;
				//echo "page: " . $totalRec;
				$sql  = "SELECT bnsPUUsername, SUM(bnsPUAmount) as bnsTotal FROM dtBnsPassedUp ";
				$sql .= " GROUP BY bnsPUUsername";
		  		// $sql .= " limit " . $startRec . ", " . $numPerPage;

				$query = $conn->query($sql);
				?>
				<div class="row">
					<div class="col-md-10">
						<h3 class="text-right">Total Bonus Passed Up : <span id="sumBnsPU"></span></h3>
						<table class="table table-hover table-striped" id="tBnsPU">
							<thead>
								<tr>
									<th>No</th>
									<th>Sponsor</th>
									<th class="text-right">Bonus</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ($query->num_rows == 0){
									echo "<tr><td colspan=3 class='text-center text-primary'>no record</td></tr>";  
								}
								$gTotal = $i = 0;
								while ($row = $query->fetch_assoc()){
									$i++;
									$gTotal += $row["bnsTotal"];
									?>
									<tr>
										<td><?php echo $i; ?></td>
										<td><?php echo $row ["bnsPUUsername"] ?></td>
										<td class="text-right"><?php echo $row["bnsTotal"] ?></td>
									</tr>
								<?php } ?>
								<!-- <tr>
									<td class="text-right"><span class="fa fa-2x">Total</span></td>
									<td class="text-right"><span class="fa fa-2x"><?php echo $gTotal ?></span></td>
								</tr> -->
							</tbody>
						</table>
						<!-- pagination --->
						<!-- <div class="row text-right">
							<ul class="pagination">
								<?php 
								$prev = $next = ""; 
								if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
								if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
								?>
								<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pu&pageActive=' . $pagePrev); ?>">Previous</a></li>
								<?php 
								for ($i=1; $i<=$numPages; $i++){
									$active = "";
									if ($i == $pageActive) $active = "active";
									echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=pu&pageActive=$i'>$i</a></li>";
								}
								?>
								<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pu&pageActive=' . $pageNext); ?>">Next</a></li>
							</ul>&nbsp;&nbsp;&nbsp;&nbsp;
						</div>  --> 
					</div>
				</div>
			</div>
			<div id="pairing" class="tab-pane fade in <?php echo (setActive($subMenu, "pairing")); ?>">
				<h3>Pairing</h3>
				<!--- pairing ---------->
				<?php 
				// $sql  = "SELECT count(*) totalRec FROM dtDailyPairing";

				// $query = $conn->query($sql);
				// $row = $query->fetch_assoc();
				// $totalRec = $row['totalRec'];
				// $numPages = ceil ($totalRec / $numPerPage); 
				// $pageActive = ($pageActive<1)?1:$pageActive;        
				// $startRec = ($pageActive-1) * $numPerPage;

				$sql  = "SELECT pairUsername, SUM(pairLeft) as pairTotalLeft, SUM(pairRight) as pairTotalRight, SUM(pairTO) AS pairTotalTO FROM dtDailyPairing GROUP BY pairUsername";
				// $sql .= " limit " . $startRec . ", " . $numPerPage;
				$query = $conn->query($sql);
				?>
				<div class="row">
					<div class="col-md-10">
						<h3 class="text-right">Total Bonus Pairing : <span id="sumBnsPairing"></span></h3>
						<table class="table table-hover table-striped" id="tBnsPairing">
							<thead>
								<tr>
									<th>No</th>
									<th>Username</th>
									<th class="text-right">Left</th>
									<th class="text-right">Right</th>
									<th class="text-right">Pair Commission</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ($query->num_rows == 0){
									echo "<tr><td colspan=5 class='text-center text-primary'>no record</td></tr>";  
								}
								$gTotalLeft = $gTotalRight = $gTotalPairTO = $i = 0;
								while ($row  = $query ->fetch_assoc()){
									$i++;
									$gTotalLeft  += $row["pairTotalLeft"];
									$gTotalRight += $row["pairTotalRight"];
									$gTotalPairTO += $row['pairTotalTO'];
									?>
									<tr>
										<td><?php echo $i; ?></td>
										<td><?php echo $row ["pairUsername"]; ?></td>
										<td class="text-right"><?php echo $row ["pairTotalLeft"]; ?></td>
										<td class="text-right"><?php echo $row ["pairTotalRight"]; ?></td>
										<td class="text-right"><?php echo $row ["pairTotalTO"]; ?></td>
									</tr>
								<?php } ?>
								<!-- <tr>
									<td class="text-right"><span class="fa fa-2x">Total</span></td>
									<td class="text-right"><span class="fa fa-2x"><?php echo $gTotalLeft ?></span></td>
									<td class="text-right"><span class="fa fa-2x"><?php echo $gTotalRight ?></span></td>
									<td class="text-right"><span class="fa fa-2x"><?php echo $gTotalRight ?></span></td>
								</tr> -->
							</tbody>
						</table>
						<!-- pagination --->
						<!-- <div class="row text-right">
							<ul class="pagination">
								<?php 
								$prev = $next = ""; 
								if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
								if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
								?>
								<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pairing&pageActive=' . $pagePrev); ?>">Previous</a></li>
								<?php 
								for ($i=1; $i<=$numPages; $i++){
									$active = "";
									if ($i == $pageActive) $active = "active";
									echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=pairing&pageActive=$i'>$i</a></li>";
								}
								?>
								<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pairing&pageActive=' . $pageNext); ?>">Next</a></li>
							</ul>&nbsp;&nbsp;&nbsp;&nbsp;
						</div>  -->
					</div> 
				</div>
			</div>
			<div id="matching" class="tab-pane fade in <?php echo (setActive($subMenu, "matching")); ?>">
				<h3>Matching</h3>
				<!--- Matching ---------->
				<?php 
				$sql  = "SELECT count(*) totalRec, SUM(mtchAmount) FROM dtMatching";

				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages = ceil ($totalRec / $numPerPage); 
				$pageActive = ($pageActive<1)?1:$pageActive;        
				$startRec = ($pageActive-1) * $numPerPage;

				$sql  = "SELECT mtchUsername, SUM(mtchAmount) AS mtchAmount";
				$sql .= " FROM dtMatching";
				$sql .= " GROUP BY mtchUsername";
				$query = $conn->query($sql);
				?>
				<div class="row">
					<div class="col-md-10">
						<h3 class="text-right">Total Matching : <span id="sumBnsMatching"></span></h3>
						<table class="table table-hover table-striped" id="tBnsMatching">
							<thead>
								<tr>
									<th>No</th>
									<th>Username</th>
									<th class="text-right">Bonus</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if ($query->num_rows == 0){
								echo "<tr><td colspan=3 class='text-center text-primary'>no record</td></tr>";  
							}
							$bnsTotal = $i = 0;
							while ($row  = $query ->fetch_assoc()){
								$i++;
								$bnsTotal  += $row["mtchAmount"];
								?>
								<tr>
									<td><?php echo $i; ?></td>	
									<td><?php echo $row ["mtchUsername"]; ?></td>
									<td class="text-right"><?php echo $row['mtchAmount']; ?></td>
								</tr>
							<?php 
							} 
							?>
									
							</tbody>
						</table>
						<!-- pagination --->
						<!-- <div class="row text-right">
							<ul class="pagination">
								<?php 
								$prev = $next = ""; 
								if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
								if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
								?>
								<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=matching&pageActive=' . $pagePrev); ?>">Previous</a></li>
								<?php 
								for ($i=1; $i<=$numPages; $i++){
									$active = "";
									if ($i == $pageActive) $active = "active";
									echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=matching&pageActive=$i'>$i</a></li>";
								}
								?>
								<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=matching&pageActive=' . $pageNext); ?>">Next</a></li>
							</ul>&nbsp;&nbsp;&nbsp;&nbsp;
						</div>  -->
					</div> 
					<!-- -->
				</div>
			</div>
			<div id="comm" class="tab-pane fade in <?php echo (setActive($subMenu, "comm")); ?>">
				<h3>Commission</h3>
				<?php
				$sql  = " SELECT mbrUsername AS commUsername, IFNULL(sp.bnsTotal,0) AS commSP, IFNULL(pu.bnsTotal,0) AS commPU, ";
				$sql .= " IFNULL(pair.pairTotalTO,0) AS commPairing, IFNULL(mtch.mtchAmount,0) AS commMM, ";
				$sql .= " IFNULL(conv.sumConvert,0) AS sumConvert, IFNULL(wd.sumWD,0) AS sumWD";
				$sql .= " FROM dtMember";
				$sql .= " LEFT JOIN (";
				$sql .= " 	SELECT bnsSpUsername, SUM(bnsSpAmount) as bnsTotal FROM dtBnsSponsor";
				$sql .= " 	GROUP BY bnsSpUsername";
				$sql .= " ) AS sp ON sp.bnsSpUsername = mbrUsername";
				$sql .= " LEFT JOIN (";
				$sql .= " 	SELECT bnsPUUsername, SUM(bnsPUAmount) as bnsTotal FROM dtBnsPassedUp";
				$sql .= " 	GROUP BY bnsPUUsername";
				$sql .= " ) AS pu ON pu.bnsPUUsername = mbrUsername";
				$sql .= " LEFT JOIN (";
				$sql .= " 	SELECT pairUsername, SUM(pairTO) AS pairTotalTO";
				$sql .= " 	FROM dtDailyPairing GROUP BY pairUsername";
				$sql .= " ) AS pair ON pair.pairUsername = mbrUsername";
				$sql .= " LEFT JOIN (";
				$sql .= " 	SELECT mtchUsername, SUM(mtchAmount) AS mtchAmount";
				$sql .= " 	FROM dtMatching GROUP BY mtchUsername";
				$sql .= " ) AS mtch ON mtch.mtchUsername = mbrUsername";
				$sql .= " LEFT JOIN (";
				$sql .= " SELECT finMbrUsername, SUM(finAmount) AS sumConvert FROM dtFundIn";
				$sql .= " WHERE finStatus='".$GLOBALS['DEF_STATUS_APPROVED'] . "' AND finAccType = '".$GLOBALS['DEF_CONVERT_BNS_VOUCHER']."'";
				$sql .= " AND finVoucherType = '".$GLOBALS['DEF_VOUCHER_TYPE_STD']."' ";
				$sql .= " GROUP BY finMbrUsername";
				$sql .= " ) AS conv ON conv.finMbrUsername = mbrUsername";
				$sql .= " LEFT JOIN (";
				$sql .= " SELECT wdMbrUsername, sum(wdAmount) AS sumWD FROM dtWDFund";
				$sql .= " WHERE (wdStID ='". $GLOBALS['DEF_STATUS_REQUEST'] . "' OR wdStID='". $GLOBALS['DEF_STATUS_ONPROGRESS'] . "' OR wdStID='".$GLOBALS['DEF_STATUS_APPROVED'] ."')";
				$sql .= " GROUP BY wdMbrUsername";
				$sql .= " ) AS wd ON wd.wdMbrUsername = mbrUsername";
				// echo $sql;
				$result = $conn->query($sql);
				?>
				<div class="row">
					<div class="col-md-3">
						<h5>Total Gross Comm<br><span id="tComm"></span></h5>
					</div>
					<div class="col-md-3">
						<h5>Total Conv STD<br><span id="tConvSTD"></span></h5>
					</div>
					<div class="col-md-3">
						<h5>Total WD<br><span id="tWD"></span></h5>
					</div>
					<div class="col-md-3">
						<h5>Total Nett Comm<br><span id="tNettComm"></span></h5>
					</div>
					<div class="col-md-10">
						<table class="table table-hover table-striped" id="tCommission">
							<thead>
								<tr>
									<th>No</th>
									<th>Username</th>
									<th class="text-right">Commission<br>(a)</th>
									<th class="text-right">Convert<br>(b)</th>
									<th class="text-right">Withdrawal<br>(c)</th>
									<th class="text-right">Net Commission<br>(d = a - b - c)</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$i=0;
							while ($row = $result->fetch_assoc()){
								$i++;
								$commSP 		= $row['commSP'];
								$commPU 		= $row['commPU'];
								$commPairing 	= $row['commPairing'];
								$commMM 		= $row['commMM'];
								$sumConvert 	= $row['sumConvert'];
								$sumWD 			= $row['sumWD'];
								$tComm = ($commSP + $commPU + $commPairing + $commMM) * 0.8; 
								$nettComm = ($tComm - $sumConvert - $sumWD);
							?>
							<tr>
								<td><?php echo $i; ?></td>
								<td><?php echo $row['commUsername']; ?></td>
								<td class="text-right"><?php echo $tComm; ?></td>
								<td class="text-right"><?php echo $sumConvert; ?></td>
								<td class="text-right"><?php echo $sumWD; ?></td>
								<td class="text-right"><?php echo $nettComm; ?></td>
							</tr>
							<?php 
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>