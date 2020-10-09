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
	if ($q == "wd-success"){
		//demo.showNotification('top','center', 'success', "Confirmation Successfully");
		alert ("Confirmation Successfully");
	}else if ($q == "wd-declined"){
		alert ("Declined Successfully");
	}

	var twdApproved = $("#twdApproved").DataTable({
		lengthMenu : [25,50,100,200,500],
		order : [[0, 'DESC']],
		"initComplete": function(){
			var year = months = search = amount = sumwdApproved = "";
			var monthsValue = this.api().column(0).data();
			sumwdApproved 	= this.api().column(3).data().sum();
			sumwdApproved = sumwdApproved.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' });
			$("#sumwdApproved").html(sumwdApproved);
			if (year == ""){
				$("#bln").attr("disabled", true);
			}
			$("#thn").on("change", function(){
				year = $(this).attr("selected", true).val();
				$("#bln").attr("disabled", false);
				if (months == 0){
					search = year;
				}else{
					months = ("0" + months).slice(-2);
					search = year+"-"+months;
				}
				twdApproved.column(0).search(search).draw();
				sumwdApproved = twdApproved.column(3, {filter:'applied'}).data().sum();
				sumwdApproved = sumwdApproved.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' });
				$("#sumwdApproved").html(sumwdApproved);
			});
			$("#bln").on("change", function(){
				months = $(this).attr("selected", true).val();
				if (months == 0){
					search = year;
				}else{
					months = ("0" + months).slice(-2);
					search = year+"-"+months;
				}
				twdApproved.column(0).search(search).draw();
				sumwdApproved = twdApproved.column(3, {filter:'applied'}).data().sum();
				sumwdApproved = sumwdApproved.toLocaleString("id-ID",{ style: 'currency', currency: 'IDR' });
				$("#sumwdApproved").html(sumwdApproved);
			});
		}
	});
	
	$('a[href="#"]').on('click', function(){
		// alert ($(this).attr('name'));
		$id	= $(this).attr('name');
		$.get("json.php", 
		{
			"q"	: "confirmWD",
			"id" : $id
		},
		function (data, success){
			$myDataObj = JSON.parse(data);
			if ($.trim($myDataObj["status"])=="error"){
				$errDesc = $.trim($myDataObj["message"]);
				alert ($errDesc);
					//demo.showNotification('top','center', 'info', $errDesc);
			}else{
				$('#wdMbrUsername').val($myDataObj["wdMbrUsername"]);
				$('#wdPayAcc').val($myDataObj["wdPayAcc"]);
				$("#wdID").attr('title', $myDataObj["wdID"]);
				//$('#wdAmount').val($myDataObj["wdAmount"]);
				//$('#wdFee').val($myDataObj["wdFee"]);
				$('#wdNett').val($myDataObj["wdNett"]);
				$("#wdType").val($myDataObj["ptDesc"]);
				var modalConfirmWD = document.getElementById('modalConfirmWD');
				modalConfirmWD.style.display='block';
			}
		});
	});
	
	//Click Confirmation button
	$("#submit").on('click', function(){
		$wdID			= $("#wdID").attr('title');
		$wdMbrUsername = $('#wdMbrUsername').val();
		$wdType   = $('#wdType').val();
		$wdPayAcc = $('#wdPayAcc').val();
		//$wdAmount = $('#wdAmount').val();
		//$wdFee    = $('#wdFee').val();
		$wdNett   = $('#wdNett').val();
		$securityPassword = $('#securityPassword').val();      

		//verify input
		if ($wdID != "" && $wdMbrUsername != "" && $wdPayAcc != ""
			&& $wdNett != "" && $securityPassword != ""
			){

			var html = $("#submit").html();
			$("#submit").html(html + '&nbsp; <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
			$("#submit").attr("disabled", true);
			$("#declined").attr("disabled", true);

			Swal.fire({
				title: 'Are you sure?',
				text: "You won't be able to revert this!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, Approve it!',
				cancelButtonText: 'Close'
			}).then((result) => {
				if (result.value) {
					// alert("test dulu belum update database"); return false;
					$.post("json.php", 
					{
						"q"					: "checkAndSaveDataWD",
						"id"				: "",
						"wdID"              : $wdID,
						"securityPassword"  : $securityPassword,
						"wdMbrUsername"     : $wdMbrUsername,
						"wdPayAcc"          : $wdPayAcc,
						"wdType"            : $wdType,
						"wdNett"          	: $wdNett
					}, 
					function(data, success){
						// alert(data); return false;
						$myDataObj	= JSON.parse(data);
						if ($.trim($myDataObj["status"]) == "success"){
							// location.href = "./?menu=withdrawal&q=wd-success";	
							const Toast = Swal.mixin({
								toast: true,
								position: 'top-end',
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
							//alert ($myDataObj["errDesc"]);
							//demo.showNotification('top','center', 'info', $myDataObj["message"]);
							//alert ($myDataObj["message"]);
							$("#alertMsg").attr('style', 'display:block');
							$("#msg").html($myDataObj["message"]);
							$("#submit").attr("disabled", false);
							$("#submit").html("Confirm Withdrawal");
							$("#declined").attr("disabled", false);
							$(document).load();
						}
					});
				}else{
					$("#submit").attr("disabled", false);
					$("#submit").html("Confirm Withdrawal");
					$("#declined").attr("disabled", false);
				}
			});
		}else{
			//demo.showNotification('top','center', 'info', "Please, fill all data before submit!");
			//alert ("Please, fill all data before submit!");
			$("#alertMsg").attr('style', 'display:block');
			$("#msg").html("<b>Please, fill all data before submit!</b>");
			$(document).load();
		}
	});
    //Click Decline button
    $("#btnDeclined").on('click', function(){
    	$wdID     = $("#wdID").attr('title');
    	$wdMbrUsername = $('#wdMbrUsername').val();
    	$wdDesc   = $('#wdDesc').val();
      	//alert ($wdDesc);
      	//$wdType   = $('#wdType').val();
      	//$wdPayAcc = $('#wdPayAcc').val();
      	//$wdAmount = $('#wdAmount').val();
      	$securityPassword = $('#securityPassword').val();      

      	//verify input
		if ($wdID != "" && $wdMbrUsername != "" && $securityPassword != "" ){
	      	var html = $("#btnDeclined").html();
	      	$("#btnDeclined").html(html + '&nbsp; <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
	      	$("#btnDeclined").attr("disabled", true);
	      	$("#submit").attr("disabled", true);
	      	Swal.fire({
				title: 'Are you sure?',
				text: "You won't be able to revert this!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, Decline it!',
				cancelButtonText: 'Close'
			}).then((result) => {
				if (result.value) {
					// alert("test dulu belum update database"); return false;
			      	$.post("json.php", 
			      	{
			      		"q"                 : "decline_withdrawal",
			      		"id"                : "",
			      		"wdID"              : $wdID,
			      		"securityPassword"  : $securityPassword,
			      		"wdMbrUsername"     : $wdMbrUsername,
			      		"wdDesc"            : $wdDesc
			      	}, 
			      	function(data, success){
			      		$myDataObj  = JSON.parse(data);
			      		if ($.trim($myDataObj["status"]) == "success"){
							//location.href = "incomingDeposit.php?q=" + $myDataObj["message"]; 
							location.href = "./?menu=withdrawal&q=wd-declined";  
							//$("#finID").attr('title', ""); //reset finID
		          		}else if ($.trim($myDataObj["status"]) == "error"){
							//alert ($myDataObj["errDesc"]);
							//demo.showNotification('top','center', 'info', $myDataObj["message"]);
							//alert ($myDataObj["message"]);
							$("#alertMsg").attr('style', 'display:block');
							$("#msg").html($myDataObj["message"]);
							$("#submit").attr("disabled", false);
							$("#btnDeclined").html("Decline Withdrawal");
							$("#btnDeclined").attr("disabled", false);
							$(document).load();              
		          		}
		      		});
		      	}else{
					$("#submit").attr("disabled", false);
					$("#btnDeclined").html("Decline Withdrawal");
					$("#btnDeclined").attr("disabled", false);
				}
			});
      	}else {
	        //demo.showNotification('top','center', 'info', "Please, fill all data before submit!");
	        //alert ("Please, fill all data before submit!");
	        $("#alertMsg").attr('style', 'display:block');
	        if ($securityPassword == ""){
	        	$("#msg").html("Security password not filled</b>");
	        }else{
	        	$("#msg").html("incomplete data</b>");
	        }
        	$(document).load();
    	}
	});

	$('a[href="#resendEmailWD"]').on("click", function(){
		var wdID = $(this).attr("name");
		// alert(wdID);
		Swal.fire({
			title: 'Resend Email',
			text: "Email Request Withdrawal",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, Send!',
			cancelButtonText: 'Close',
			showLoaderOnConfirm: true,
			allowOutsideClick: false,
			preConfirm: function(){
				return new Promise(function(resolve) {
					$.get("json.php", 
					{
						"q"	: "resendEmailWD",
						"id" : wdID
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
							Swal.fire({
							  	title: 'Error!',
							  	text: $myDataObj['message'],
							  	icon: 'error',
							  	allowOutsideClick: false,
							  	confirmButtonText: 'Ok'
							}).then((result) => {
								if (result.value) {
									return false;
								}
							});
						}
					});
				});
			}
		});
	});
}); //end $(document).ready(function(e) {
</script>

<script>
// Get the modal
var modal = document.getElementById('modalConfirmWD');
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target.id == "modalConfirmWD")
		document.getElementById('modalConfirmWD').style.display='none';
	//alert (event.target.id);
}
</script>
<span id="q" title="<?php echo $q; ?>"></span>
<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Withdrawal</div>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "pending")); ?>"><a data-toggle="tab" href="#pending">Pending</a></li>
			<li class="<?php echo (setActive($subMenu, "approved")); ?>"><a data-toggle="tab" href="#approved">Approved</a></li>
			<li class="<?php echo (setActive($subMenu, "declined")); ?>"><a data-toggle="tab" href="#declined">Declined</a></li>
		</ul>
		<div class="tab-content">
			<div id="pending" class="tab-pane fade in <?php echo (setActive($subMenu, "pending")); ?>">
				<h3>Pending</h3>
				<!-- Pending -->
				<?php 			
				$sql 	= "SELECT count(*) AS totalRec FROM dtWDFund INNER JOIN msStatus ON wdStID=stID ";
				$sql  .= " WHERE wdStID='".$DEF_STATUS_ONPROGRESS."' OR wdStID='".$DEF_STATUS_REQUEST."'";

				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages	= ceil ($totalRec / $numPerPage);	
				$pageActive = ($pageActive<1)?1:$pageActive;		
				$startRec = ($pageActive-1) * $numPerPage;

				$sql  = "SELECT wdID, wdMbrUsername, CONCAT(mbrFirstName, ' ', mbrLastName) AS mbrFullName, wdDate, wdAmount, wdTax, wdPayAcc, ptDesc, stDesc, wdStID ";
				$sql .= " FROM dtWDFund ";
				$sql .= " INNER JOIN dtMember ON wdMbrUsername = mbrUsername";
				$sql .= " INNER JOIN dtPaymentAcc ON wdMbrUsername = payMbrUsername AND wdPayAcc = payAcc";
				$sql .= " INNER JOIN msPaymentType ON ptID = payPTID";
				$sql .= " INNER JOIN msStatus ON wdStID=stID ";
				$sql .= " WHERE wdStID='".$DEF_STATUS_ONPROGRESS."' OR wdStID='".$DEF_STATUS_REQUEST."'";
				$sql .= " LIMIT " . $startRec . ", " . $numPerPage;
				$queryPending = $conn->query($sql);
				?>
				<div >
					<table class="table table-hover table-striped">
						<thead>
							<tr>
								<th>Date</th>
								<th>Username</th>
								<th>Account</th>
								<th>Amount</th>
								<th>Status</th>
								<th class="disabled-sorting text-right">Action</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Date</th>
								<th>Username</th>
								<th>Account</th>
								<th>Amount</th>
								<th>Status</th>
								<th class="text-right">Action</th>
							</tr>
						</tfoot>
						<tbody>
							<?php
							if ($queryPending->num_rows == 0){
								echo "<tr><td colspan=6 class='text-center text-primary'>no record</td></tr>";	
							}
							while ($rowPending = $queryPending->fetch_assoc()){
								$wdAmount 	= $rowPending['wdAmount'];
								$wdTax 		= $rowPending['wdTax'];
								$wdNett 	= $wdAmount - $wdTax;
								$btnResendEmail = '';
								if ($rowPending['wdStID'] == $DEF_STATUS_REQUEST){
									$btnResendEmail = '<a href="#resendEmailWD" name="'.$rowPending["wdID"].'" title="Resend Email Request WD" ><i class="fa fa-envelope fa-2x"></i></a>';
								}
								?>
								<tr>
									<td><?php echo $rowPending["wdDate"] ?></td>
									<td><?php echo $rowPending["wdMbrUsername"] ?></td>
									<td><?php echo $rowPending['ptDesc']."<br>".$rowPending["wdPayAcc"]."<br>".$rowPending['mbrFullName']; ?></td>
									<td><?php echo "Amount : Rp ".numFormat($wdAmount,0)."<br>Tax : Rp ".numFormat($wdTax,0)."<br><b style='font-size:16px;'>Nett : Rp ".numFormat($wdNett,0)."</b>"; ?></td>
									<td><?php echo $rowPending["stDesc"] ?></td>
									<td class="text-right">
										<a href="#" name="<?php echo $rowPending["wdID"] ?>" title="Update Withdrawal" ><i class="fa fa-edit fa-2x"></i></a>
										<?php echo ($btnResendEmail != "")?$btnResendEmail:""; ?>
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
				<!--- Approved -->
				<?php 
				$arrBulan = array("YTM", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
				?>
				<div class="row">
					<div class="col-md-2">
			            <select id="thn" name="thn" class="form-control col-md-2">
			            	<option selected disabled>Year</option>
			                <?php
			                $thn = date("Y");
			                for ($thn; $thn>= 2018; $thn--){
			                    $selected = "";
			                    echo ("<option value='$thn'>$thn</option>");
			                }
			                ?>
			            </select>
			        </div>
					<div class="col-md-2">
			            <select id="bln" name="bln" class="form-control">
			            	<option selected disabled>Month</option>
			                <?php
			                for ($i=0; $i<=12; $i++){
			                    if ($i<10){
			                        $bln = "0".$i;
			                    }else{
			                        $bln = $i;
			                    }
			                    $selected = "";
			                    echo ("<option value='$i'> $arrBulan[$i] </option>");
			                }
			                ?>
			            </select>
			        </div>
			        <div class="col-md-7">
			        	<h3 class="text-right">Total Withdrawal : <span id="sumwdApproved"></span></h3>
			        </div>
			    </div>
				<?php 
				$sql  = "SELECT COUNT(*) AS totalRec FROM dtWDFund INNER JOIN msStatus ON wdStID=stID ";
				$sql  .= " WHERE wdStID='".$DEF_STATUS_APPROVED."'";
				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages	= ceil ($totalRec / $numPerPage);	
				$pageActive = ($pageActive<1)?1:$pageActive;				
				$startRec = ($pageActive-1) * $numPerPage;

				$sql  = "SELECT wdID, wdMbrUsername, wdDate, wdAmount, wdPayAcc, stDesc FROM dtWDFund INNER JOIN msStatus ON wdStID=stID ";
				$sql  .= " WHERE wdStID='".$DEF_STATUS_APPROVED."'";
				$sql  .= " ORDER BY DATE(wdDate) DESC";
				// $sql  .= " LIMIT " . $startRec . ", " . $numPerPage;

				$queryApproved = $conn->query($sql);
				?>
				<div class="table-responsive">
					<table class="table table-hover table-striped" id="twdApproved" >
						<thead>
							<tr>
								<th>Date</th>
								<th>Username</th>
								<th>Account</th>
								<th>Amount</th>
								<th>Status</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Date</th>
								<th>Username</th>
								<th>Account</th>
								<th>Amount</th>
								<th>Status</th>
							</tr>
						</tfoot>
						<tbody>
							<?php
							if ($queryApproved->num_rows == 0){
								echo "<tr><td colspan=5 class='text-center text-primary'>no record</td></tr>";	
							}
							while ($rowApproved = $queryApproved->fetch_assoc()){
								?>
								<tr>
									<td><?php echo $rowApproved["wdDate"] ?></td>
									<td><?php echo $rowApproved["wdMbrUsername"] ?></td>
									<td><?php echo $rowApproved["wdPayAcc"] ?></td>
									<td><?php echo $rowApproved["wdAmount"]; ?></td>
									<td><?php echo $rowApproved["stDesc"] ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<!-- pagination -->
					<!-- <div class="row text-right">
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
					</div>  --> 
				</div>
			</div>
			<div id="declined" class="tab-pane fade in <?php echo (setActive($subMenu, "declined")); ?>">
				<h3>Declined</h3>
				<!--- Declined -->
				<?php 
				$sql  = "SELECT COUNT(*) totalRec FROM dtWDFund INNER JOIN msStatus ON wdStID=stID WHERE wdStID='".$DEF_STATUS_DECLINED."'";
				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages	= ceil ($totalRec / $numPerPage);	
				$pageActive = ($pageActive<1)?1:$pageActive;				
				$startRec = ($pageActive-1) * $numPerPage;

				$sql  = "SELECT wdID, wdMbrUsername, wdDate, wdAmount, wdPayAcc, wdDesc, stDesc FROM dtWDFund INNER JOIN msStatus ON wdStID=stID ";
				$sql  .= " WHERE wdStID='".$DEF_STATUS_DECLINED."'";
				$sql  .= " ORDER BY DATE(wdDate) DESC";
				$sql  .= " LIMIT " . $startRec . ", " . $numPerPage;
				$queryDeclined = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped" id="twdDeclined">
						<thead>
							<tr>
								<th>Date</th>
								<th>Username</th>
								<th>Account</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Status</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Date</th>
								<th>Username</th>
								<th>Account</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Status</th>
							</tr>
						</tfoot>
						<tbody>
							<?php
							if ($queryDeclined->num_rows == 0){
								echo "<tr><td colspan=6 class='text-center text-primary'>no record</td></tr>";	
							}
							while ($rowDeclined = $queryDeclined->fetch_assoc()){
								?>
								<tr>
									<td><?php echo $rowDeclined["wdDate"] ?></td>
									<td><?php echo $rowDeclined["wdMbrUsername"] ?></td>
									<td><?php echo $rowDeclined["wdPayAcc"] ?></td>
									<td><?php echo $rowDeclined["wdAmount"]; ?></td>
									<td><?php echo $rowDeclined["wdDesc"]; ?></td>
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

	<div class="modal-2" id="modalConfirmWD">
		<div class="modal-content-2">
			<form class="animate" action="" method="post" onSubmit="return false;">
				<span id="wdID" title=""></span>
				<div class="container-fluid">
					<div class="row text-center" id="alertMsg" style="display:none">
						<div class="alert alert-danger alert-dismissible">
							<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							<strong>Info - </strong><span id="msg"></span>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">Username</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" name="wdMbrUsername" id="wdMbrUsername" value="admin" class="form-control" title="" readonly required>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">Account Type</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" name="wdType" id="wdType" value="btc" class="form-control" title="" readonly required>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">Bank Acc/Addr</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" name="wdPayAcc" id="wdPayAcc" value="" class="form-control" title="" readonly required>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-md-4 text-left text-vCenter">Nett Amount</label>
						<div class="col-md-8">
							<div class="form-group">
								<input type="text" name="wdNett" id="wdNett" value="" class="form-control" title="" required>
							</div>
						</div>
					</div>
                  <!--
                  <div class="row">
                      <label class="col-md-4 text-left text-vCenter">Amount</label>
                      <div class="col-md-8">
                          <div class="form-group">
                          -->
                             <!-- <input type="text" name="accAmount" id="accAmount" value="" class="form-control" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" title="" required>
                             -->
                             <!--
                             <input type="number" name="wdAmount" id="wdAmount" value="" class="form-control" title="" required>
                          </div>
                      </div>
                  </div>
              -->
              <div class="row">
              	<label class="col-md-4 text-left text-vCenter">Reason for rejection/decline</label>
              	<div class="col-md-8">
              		<div class="form-group">
              			<textarea rows="3" maxlength="100" name="wdDesc" id="wdDesc" class="form-control" title="" required>Pembatalan atas permintaan member via CS</textarea>
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
              	<div class="col-md-8">
              		<div class="row">
              			<div class="col-md-6">
              				<button type="submit" name="submit" id="submit" class="btn btn-block btn-primary">Confirm Withdrawal</button>
              			</div>
              			<div class="col-md-6">
              				<button type="button" name="btnDeclined" id="btnDeclined" class="btn btn-block btn-danger">Decline Withdrawal</button>
              			</div>
              		</div>
              	</div>
              </div>
          </div>
      </form>
  </div>
</div>
</div>