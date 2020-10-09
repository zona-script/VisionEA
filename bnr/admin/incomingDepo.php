<?PHP
$q = (isset($_GET["q"]))?$_GET["q"]: "";
?>
<?php
$page = (isset($_GET['page']))? $_GET['page'] : 1;
$pageActive = (isset($_GET['pageActive']))? $_GET['pageActive'] : 1;
$numPerPage = $DEF_NUM_PER_PAGE;


//$menu = (isset($_GET['menu']))? $_GET['menu'] : "";
$subMenu = (isset($_GET['subMenu']))? $_GET['subMenu'] : "pending";

?>
<script>
$(document).ready(function(e) {
	$q = $("#q").attr('title');
	if ($q == "confirmed"){
		//demo.showNotification('top','center', 'success', "Confirmation Successfully");
		alert ("Confirmation Successfully");
	}
	if ($q == "declined"){
		//demo.showNotification('top','center', 'success', "Confirmation Successfully");
		alert ("Process Decline Successfully");
	}

	$('a[href="#"]').on('click', function(){
		//alert ($(this).attr('name'));
		$id = $(this).attr('name');
		$.get("json.php", 
		{
			"q" : "confirmDeposit",
			"id" : $id
		},
		function (data, success){
			// console.log(data);
			$myDataObj = JSON.parse(data);
			if ($.trim($myDataObj["status"])=="error"){
				$errDesc = $.trim($myDataObj["message"]);
				alert ($errDesc);
		  		//admin site not support demo.showNotification Script
				//demo.showNotification('top','center', 'info', $errDesc);
			}else{
				$('#finFilename').attr("src", "../member/bukti_transfer/"+$myDataObj['finFilename']);
				$('#accUsername').val($myDataObj["finMbrUsername"]);
				$('#accType').val($myDataObj["finAccType"]);
				$('#voucherType').val($myDataObj["finVoucherType"]);
				if ($myDataObj["finVoucherType"] === "VPS"){
					$('#accAmount').val(0);
					$('#accAmount').attr('step', 10);
				}else{
					$('#accAmount').val(0);
					$('#accAmount').attr('step', <?php echo $DEF_VOUCHER_PRICE_IDR; ?>);
					$('#accAmount').attr('min', <?php echo $DEF_VOUCHER_PRICE_IDR*$DEF_MIN_BUY_VOUCHER; ?>);
				}
				$("#finID").attr('title', $id);
				var modalConfirmDeposit = document.getElementById('modalConfirmDeposit');
				modalConfirmDeposit.style.display='block';
			}
		});
	});
	//show currency
	$("#accAmount").on("change", function(){
		var accAmount = parseInt($(this).val()).toLocaleString("id-ID", { style: 'currency', currency: 'IDR' });
		$("#curraccAmount").html(accAmount);
	});

	//Click Confirmation button
	$("#submit").on('click', function(){
		var html = $("#submit").html();
		$("#submit").html(html + '&nbsp; <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
		$("#submit").attr("disabled", true);
		$("#btnDeclined").attr("disabled", true);

		$finID          = $("#finID").attr('title');
		$accUsername    = $('#accUsername').val();
		$accType        = $('#accType').val();
		$voucherType    = $('#voucherType').val();
		// $voucherPrice = 3500000;
		if ($voucherType == "STD") $voucherPrice = <?php echo $DEF_VOUCHER_PRICE_IDR; ?>;
		if ($voucherType == "VPS") $voucherPrice = <?php echo $DEF_VOUCHER_TYPE_VPS; ?>; //belum update harga vps

		$accID          = $('#accID').val();
		$accAmount      = $('#accAmount').val();
		$accTransID     = $('#accTransID').val();
		$securityPassword = $('#securityPassword').val();
		//verify input
		if ($finID != "" && $accUsername != "" && $accType != ""
			&& $accID != "" && ($accAmount != "" && ($accAmount % $voucherPrice) == 0 ) && $securityPassword != "" //&& $accTransID != ""
		){
			$.post("json.php", 
			{
				"q"                 : "checkAndSaveDataDeposit",
				"id"                  : "",
				"finID"             : $finID,
				"securityPassword"  : $securityPassword,
				"accUsername"       : $accUsername,
				"accType"           : $accType,
				"voucherType"       : $voucherType,
				"accID"             : $accID,
				"accAmount"         : $accAmount,
				"accTransID"        : $accTransID
			}, 
			function(data, success){
				// console.log(data);
				$myDataObj  = JSON.parse(data);
				if ($.trim($myDataObj["status"]) == "success"){
					// location.href = "./?menu=deposit&q=" + $myDataObj["message"];
					const Toast = Swal.mixin({
						toast: true,
						position: 'center',
						showConfirmButton: false,
						timer: 2000,
						timerProgressBar: true,
						onOpen: (toast) => {
							toast.addEventListener('mouseenter', Swal.stopTimer)
							toast.addEventListener('mouseleave', Swal.resumeTimer)
						},
						onClose: () => { location.reload() }
					});
					Toast.fire({
						icon: 'success',
						title: $myDataObj['message']
					});   
				}else if ($.trim($myDataObj["status"]) == "error"){
					$("#alertMsg").attr('style', 'display:block');
					$("#msg").html($myDataObj["message"]);
					$('#modalConfirmDeposit').animate({ scrollTop: 0 }, 'slow');
					$("#submit").html("Confirm");
					$("#submit").attr("disabled", false);
					$("#btnDeclined").attr("disabled", false);
				}
			});
		}else{
			//demo.showNotification('top','center', 'info', "Please, fill all data before submit!");
			//alert ("Please, fill all data before submit!");
			$("#alertMsg").attr('style', 'display:block');
			$("#msg").html("<b>Please, fill all data before submit!</b>");
			$('#modalConfirmDeposit').animate({ scrollTop: 0 }, 'slow');
			// $(document).load();
			return false;
		}
	});

	$("#btnDeclined").on('click', function(e) {
		$finID        = $("#finID").attr('title');
		$accUsername  = $('#accUsername').val();
		$securityPassword = $('#securityPassword').val();
		if ($finID != "" && $accUsername != "" && $securityPassword != ""){
			Swal.fire({
				title: 'Decline Reason',
				input: 'textarea',
				inputPlaceholder: 'Type your message here...',
				inputAttributes: {
				'aria-label': 'Type your message here'
				},
				showCancelButton: true,
				confirmButtonText: 'Confirm Decline',
				showLoaderOnConfirm: true,
				allowOutsideClick : false,
				preConfirm: function(dcMsg){
					return new Promise(function(resolve) {
						$.post("json.php",
						{
							"q"           		: "declinedDataDeposit",
							"id"          		: "",
							"finID"       		: $finID,
							"securityPassword"  : $securityPassword,
							"accUsername" 		: $accUsername,
							"dcMsg"				: dcMsg
						},
						function (data, success){
							// console.log(data);
							$myDataObj  = JSON.parse(data);
							if ($.trim($myDataObj["status"]) == "success"){
								// location.href = "./?menu=tradeAcc&subMenu=reqreset&q=" + $myDataObj["message"];
								Swal.fire({
								  	title: 'Success!',
								  	text: $myDataObj['message'],
								  	icon: 'success',
								  	allowOutsideClick: false,
								  	confirmButtonText: 'Ok'
								}).then((result) => {
									if (result.value) {
										location.reload();
									}
								}); 
							}else if ($.trim($myDataObj["status"]) == "error"){
								Swal.close();
								$("#alertMsg").attr('style', 'display:block');
								$("#msg").html($myDataObj["message"]);
								$('#alertMsg').animate({ scrollTop: 0 }, 'slow');
								$('#modalConfirmDeposit').animate({ scrollTop: 0 }, 'slow');
								return false
							}
						});
					});
				}
			});
		}else{
			$("#alertMsg").attr('style', 'display:block');
			$("#msg").html("Incomplete Data");
			$('#alertMsg').animate({ scrollTop: 0 }, 'slow');
			$('#modalConfirmDeposit').animate({ scrollTop: 0 }, 'slow');
			return false;
		}	
	});	
}); //end $(document).ready(function(e) {
</script>

<script>
// Get the modal
var modal = document.getElementById('modalConfirmDeposit');
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target.id == "modalConfirmDeposit")document.getElementById('modalConfirmDeposit').style.display='none';
	//alert (event.target.id);
}
</script>
<span id="q" title="<?php echo $q; ?>"></span>
<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Deposit</div>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "pending")); ?>"><a data-toggle="tab" href="#pending">Pending</a></li>
			<li class="<?php echo (setActive($subMenu, "approved")); ?>"><a data-toggle="tab" href="#approved">Approved</a></li>
			<li class="<?php echo (setActive($subMenu, "declined")); ?>"><a data-toggle="tab" href="#declined">Declined</a></li>
		</ul>
		<div class="tab-content">
			<div id="pending" class="tab-pane fade in <?php echo (setActive($subMenu, "pending")); ?>">
				<h3>Pending</h3>
				<!--- Pending -->
				<?php             
				$sql  = "SELECT count(*) totalRec FROM dtFundIn inner join msStatus on finStatus=stID WHERE finStatus ='" . $DEF_STATUS_PENDING . "' OR finStatus='" . $DEF_STATUS_ONPROGRESS . "'";

				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages = ceil ($totalRec / $numPerPage);   
				$pageActive = ($pageActive<1)?1:$pageActive;        
				$startRec = ($pageActive-1) * $numPerPage;

				$sql  = "SELECT finID, finDate, finAccType, finVoucherType, finFromAccNo, finAmount, finTransactionID, stDesc FROM dtFundIn inner join msStatus on finStatus=stID WHERE finStatus ='" . $DEF_STATUS_PENDING . "' OR finStatus='" . $DEF_STATUS_ONPROGRESS . "'";
				$sql  .= " LIMIT " . $startRec . ", " . $numPerPage;
				$queryPending = $conn->query($sql);
				?>
				<div >
					<table class="table table-hover table-striped">
						<thead>
							<tr>
								<th>Date</th>
								<th>Account Type</th>
								<th>PIN Type</th>
								<th>ID/Email/Addr</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Status</th>
								<th class="disabled-sorting text-right">Action</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Date</th>
								<th>Account Type</th>
								<th>PIN Type</th>
								<th>ID/Email/Addr</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Status</th>
								<th class="text-right">Action</th>
							</tr>
						</tfoot>
						<tbody>
							<?php
							if ($queryPending->num_rows == 0){
								echo "<tr><td colspan=8 class='text-center text-primary'>no record</td></tr>";    
							}
							while ($rowPending = $queryPending->fetch_assoc()){
								?>
								<tr>
									<td><?php echo $rowPending["finDate"] ?></td>
									<td><?php echo $rowPending["finAccType"] ?></td>
									<td><?php echo $rowPending["finVoucherType"] ?></td>
									<td><?php echo $rowPending["finFromAccNo"] ?></td>
									<td><?php echo $rowPending["finAmount"] ?></td>
									<td><?php echo $rowPending["finTransactionID"] ?></td>
									<td><?php echo $rowPending["stDesc"] ?></td>
									<td class="text-right">
										<a href="#" name="<?php echo $rowPending["finID"] ?>" title="Update Confirmation" ><i class="fa fa-edit fa-2x"></i></a>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<!-- pagination -->
					<div class="row text-right">
						<ul class="pagination">
							<?php 
							$prev = $next = "";   
							if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
							if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
							?>
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pending&pageActive=' . $pagePrev); ?>">Previous</a></li>
							<?php 
							for ($i=1; $i<=$numPages; $i++){
								$active = "";
								if ($i == $pageActive) $active = "active";
								echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=pending&pageActive=$i'>$i</a></li>";
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pending&pageActive=' . $pageNext); ?>">Next</a></li>
						</ul>&nbsp;&nbsp;&nbsp;&nbsp;
					</div> 
				</div>
			</div>
			<div id="approved" class="tab-pane fade in <?php echo (setActive($subMenu, "approved")); ?>" >
				<h3>Approved</h3>
				<!-- Approved -->
				<?php 
				$sql  = "SELECT count(*) totalRec FROM dtFundIn ";
				$sql  .= " INNER JOIN msStatus on finStatus=stID ";
				  //$sql    .= " inner join dtFundInVoucher on fivFinID = finID ";
				  //$sql    .= " inner join dtVoucher on fivVCode = vCode";
				$sql  .= " WHERE finStatus ='" . $DEF_STATUS_APPROVED . "'";
				  //$sql    .= " AND vStatus='" . $DEF_STATUS_USED . "'";

				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages = ceil ($totalRec / $numPerPage);   
				$pageActive = ($pageActive<1)?1:$pageActive;                
				$startRec = ($pageActive-1) * $numPerPage;

				$sql  = "SELECT DISTINCT finDate, finAccType, finVoucherType, finFromAccNo, finAmount, finTransactionID, stDesc FROM dtFundIn ";
				$sql  .= " INNER JOIN msStatus ON finStatus=stID ";
				$sql  .= " INNER JOIN dtFundInVoucher ON fivFinID = finID ";
				$sql  .= " INNER JOIN dtVoucher ON fivVCode = vCode";
				$sql  .= " WHERE finStatus ='" . $DEF_STATUS_APPROVED . "'";
				$sql  .= " AND vStatus='" . $DEF_STATUS_USED . "'";
				$sql  .= " LIMIT " . $startRec . ", " . $numPerPage;

				$queryApproved = $conn->query($sql);
				?>
				<div >
					<table class="table table-hover table-striped" >
						<thead>
							<tr>
								<th>Date</th>
								<th>Account Type</th>
								<th>PIN Type</th>
								<th>ID/Email/Addr</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Status</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Date</th>
								<th>Account Type</th>
								<th>PIN Type</th>
								<th>ID/Email/Addr</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Status</th>
							</tr>
						</tfoot>
						<tbody>
							<?php
							if ($queryApproved->num_rows == 0){
								echo "<tr><td colspan=7 class='text-center text-primary'>no record</td></tr>";    
							}
							while ($rowApproved = $queryApproved->fetch_assoc()){
								?>
								<tr>
									<td><?php echo $rowApproved["finDate"] ?></td>
									<td><?php echo $rowApproved["finAccType"] ?></td>
									<td><?php echo $rowApproved["finVoucherType"] ?></td>
									<td><?php echo $rowApproved["finFromAccNo"] ?></td>
									<td><?php echo $rowApproved["finAmount"] ?></td>
									<td><?php echo $rowApproved["finTransactionID"] ?></td>
									<td><?php echo $rowApproved["stDesc"] ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<!-- pagination -->
					<div class="row text-right">
						<ul class="pagination">
							<?php 
							$prev = $next = "";   
							if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
							if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
							?>
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=approved&pageActive=' . $pagePrev); ?>">Previous</a></li>
							<?php 
							for ($i=1; $i<=$numPages; $i++){
								$active = "";
								if ($i == $pageActive) $active = "active";
								echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=approved&pageActive=$i'>$i</a></li>";
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=approved&pageActive=' . $pageNext); ?>">Next</a></li>
						</ul>&nbsp;&nbsp;&nbsp;&nbsp;
					</div>  
				</div>
			</div>
			<div id="declined" class="tab-pane fade in <?php echo (setActive($subMenu, "declined")); ?>">
				<h3>Declined</h3>
				<!-- Declined  -->
				<?php 
				$sql  = "SELECT count(*) totalRec FROM dtFundIn inner join msStatus on finStatus=stID WHERE finStatus ='" . $DEF_STATUS_DECLINED . "'";

				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages = ceil ($totalRec / $numPerPage);   
				$pageActive = ($pageActive<1)?1:$pageActive;                
				$startRec = ($pageActive-1) * $numPerPage;

				$sql  = "SELECT finDate, finAccType, finVoucherType, finFromAccNo, finAmount, finTransactionID, stDesc FROM dtFundIn inner join msStatus on finStatus=stID WHERE finStatus ='" . $DEF_STATUS_DECLINED . "'";
				$queryDeclined = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped">
						<thead>
							<tr>
								<th>Date</th>
								<th>Account Type</th>
								<th>PIN Type</th>
								<th>ID/Email/Addr</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Status</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Date</th>
								<th>Account Type</th>
								<th>PIN Type</th>
								<th>ID/Email/Addr</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Status</th>
							</tr>
						</tfoot>
						<tbody>
							<?php
							if ($queryDeclined->num_rows == 0){
								echo "<tr><td colspan=7 class='text-center text-primary'>no record</td></tr>";    
							}
							while ($rowDeclined = $queryDeclined->fetch_assoc()){
								?>
								<tr>
									<td><?php echo $rowDeclined["finDate"] ?></td>
									<td><?php echo $rowDeclined["finAccType"] ?></td>
									<td><?php echo $rowDeclined["finVoucherType"] ?></td>
									<td><?php echo $rowDeclined["finFromAccNo"] ?></td>
									<td><?php echo $rowDeclined["finAmount"] ?></td>
									<td><?php echo $rowDeclined["finTransactionID"] ?></td>
									<td><?php echo $rowDeclined["stDesc"] ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<!-- pagination -->
					<div class="row text-right">
						<ul class="pagination">
							<?php 
							$prev = $next = "";   
							if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
							if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
							?>
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=declined&pageActive=' . $pagePrev); ?>">Previous</a></li>
							<?php 
							for ($i=1; $i<=$numPages; $i++){
								$active = "";
								if ($i == $pageActive) $active = "active";
								echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=declined&pageActive=$i'>$i</a></li>";
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=declined&pageActive=' . $pageNext); ?>">Next</a></li>
						</ul>&nbsp;&nbsp;&nbsp;&nbsp;
					</div>  
				</div>
			</div>
		</div>
	</div>

	<div class="modal-2" id="modalConfirmDeposit">
		<div class="modal-content-2">
			<form class="animate" action="" method="post" onSubmit="return false;">
				<span id="finID" title=""></span>
				<div class="container-fluid">
					<div class="row text-center" id="alertMsg" style="display:none">
						<div class="alert alert-danger alert-dismissible">
							<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							<strong>Info - </strong><span id="msg"></span>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<img src="" id="finFilename" class="img-responsive">
						</div>
					</div>
					<hr>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">Username</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" name="accUsername" id="accUsername" value="admin" class="form-control" title="" readonly required>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">Account Type</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" name="accType" id="accType" value="btc" class="form-control" title="" readonly required>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">PIN Type</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" name="voucherType" id="voucherType" value="" class="form-control" title="" readonly required>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">ID/Email/Addr</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" name="accID" id="accID" value="" class="form-control" title="" required>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">Amount</label>
						<div class="col-md-8">
							<div class="form-group">
							 <!-- <input type="text" name="accAmount" id="accAmount" value="" class="form-control" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" title="" required>
							 -->
							 <input type="number" name="accAmount" id="accAmount" value="" class="form-control" step="0" title="" required>
							 <small class="text-info" id="curraccAmount" style="font-style: bold;"></small>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">Transaction ID</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" name="accTransID" id="accTransID" value="" class="form-control" title="">
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">Security Password</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="password" name="securityPassword" id="securityPassword" value="" class="form-control" title="" required>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4"></div>
						<div class="col-md-4">
							<button type="submit" name="submit" id="submit" class="btn btn-block col-md-12 btn-primary">Confirm</button>
						</div>
						<div class="col-md-4">
							<button type="button" name="btnDeclined" id="btnDeclined" class="btn btn-block col-md-12 btn-danger">Decline</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>