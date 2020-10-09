<?php
$q = (isset($_GET["q"]))?$_GET["q"]: "";
?>
<?php
$page = (isset($_GET['page']))? $_GET['page'] : 1;
$pageActive = (isset($_GET['pageActive']))? $_GET['pageActive'] : 1;
$numPerPage = $DEF_NUM_PER_PAGE;


//$menu = (isset($_GET['menu']))? $_GET['menu'] : "";us
$subMenu = (isset($_GET['subMenu']))? $_GET['subMenu'] : "";

?>
<script>
	$(document).ready(function(e) {
		$("#q").html("");

		$('a[href="#reqresetacc"]').on('click', function(){
			$tradeID 			= $(this).attr('tradeid');
			$resaccID 			= $(this).attr('resaccid');
			$resaccUsername  	= $(this).attr('resaccUsername');
			$resaccNo       	= $(this).attr('resaccNo');		
			$('#req_resaccid').val($resaccID);
			$('#req_tradeid').val($tradeID);
			$('#req_resaccUsername').val($resaccUsername);
			$('#req_resaccNo').val($resaccNo);
			//show the modal
			$("#modalReqReset").show();
		});

		$("#btnreqReset").on('click',function(){
			$("#btnreqReset").attr("disabled", true);
	      	var html = $("#btnreqReset").html();
	    	$("#btnreqReset").html(html + '&nbsp; <i class="fa fa-spinner fa-spin"  style="font-size:24px"></i>');
			var tradeID 		= $("#req_tradeid").val();
			var resaccID 		= $('#req_resaccid').val();
			var resaccUsername 	= $('#req_resaccUsername').val();
			var resaccNo 		= $('#req_resaccNo').val();
			var secPasswd 		= $("#req_resaccsecPasswd").val();
			// alert (tradeID+" || "+resaccID+" || "+resaccUsername+" || "+resaccNo+" || "+secPasswd); return false;
			if (tradeID == "" || resaccID == "" || resaccUsername == "" || resaccNo == ""){
				$("#alertMsgReqReset").attr('style', 'display:block');
				$("#msgReqReset").html("<b>Incomplete/Invalid Data to RESET Trading Account</b>");
				return false;
			}else{
				if (secPasswd != ""){
					$.post("json.php", 
					{
						"q"             : "ReqResetTradeAcc",
						"tradeID"		: tradeID,
						"resaccID"      : resaccID,
						"secPasswd"     : secPasswd					
					},
					function(data, success){
						// console.log(data);
						$myDataObj  = JSON.parse(data);
						if ($.trim($myDataObj["status"]) == "success"){
							location.href = "./?menu=tradeAcc&subMenu=reqreset&q=" + $myDataObj["message"]; 
						}else if ($.trim($myDataObj["status"]) == "error"){
							$("#alertMsgReqReset").attr('style', 'display:block');
							$("#msgReqReset").html($myDataObj["message"]);
							$("#btnreqReset").attr("disabled", false);
							$("#btnreqReset").html("Approve Reset");
						}
					});
				}else {
					$("#alertMsgReqReset").attr('style', 'display:block');
					$("#msgReqReset").html("<b>Security password needed to submit!</b>");
					$(document).load();
				}
			}
		});

		$("#btncancelReqReset").on('click',function(){
			var resaccID 		= $('#req_resaccid').val();
			var resaccUsername 	= $('#req_resaccUsername').val();
			var resaccNo 		= $('#req_resaccNo').val();
			var secPasswd 		= $("#req_resaccsecPasswd").val();

			// alert (resaccID+" || "+resaccUsername+" || "+resaccNo+" || "+secPasswd); return false;
			if (resaccID == "" || resaccUsername == "" || resaccNo == ""){
				$("#alertMsgReqReset").attr('style', 'display:block');
				$("#msgReqReset").html("<b>Incomplete/Invalid Data to RESET Trading Account</b>");
				return false;
			}else{
				if (secPasswd != ""){
					Swal.fire({
						title: 'Are you sure?',
						text: "You won't be able to revert this!",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						confirmButtonText: 'Yes, cancel it!',
						cancelButtonText: 'Close'
					}).then((result) => {
						if (result.value) {
							$.post("json.php", 
							{
								"q"             : "CancelReqResetTradeAcc",
								"resaccID"      : resaccID,
								"secPasswd"     : secPasswd					
							},
							function(data, success){
								$myDataObj  = JSON.parse(data);
								if ($.trim($myDataObj["status"]) == "success"){
									// location.href = "./?menu=tradeAcc&subMenu=reqreset&q=" + $myDataObj["message"];
									Swal.fire({
									  	title: 'Canceled!',
									  	text: 'Reset process has been canceled.',
									  	type: 'success',
									  	confirmButtonText: 'Ok'
									}).then((result) => {
										if (result.value) {
											location.href = "./?menu=tradeAcc&subMenu=reqreset&q=" + $myDataObj["message"];
										}
									}); 
								}else if ($.trim($myDataObj["status"]) == "error"){
									$("#alertMsgReqReset").attr('style', 'display:block');
									$("#msgReqReset").html($myDataObj["message"]);
									return false;
								}
							});
						}
					});
				}else{
					$("#alertMsgReqReset").attr('style', 'display:block');
					$("#msgReqReset").html("<b>Security password needed to submit!</b>");
					return false;
				}
			}		
		});
	});
</script>

<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Trading Account</div>
		<span id="q" title="" class="text-center text-success"><b><?php echo $q; ?></b></span>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "reqreset")); ?>"><a data-toggle="tab" href="#reqreset">Request Reset</a></li>
		</ul>
		<div class="tab-content">
			<!-- Req Reset -->
			<div id="reqreset" class="tab-pane fade in <?php echo (setActive($subMenu, "reqreset")); ?>" >
				<?php 
				$sql  = "SELECT resaccID, resaccDate, resaccUsername, resaccNo, resaccUpdateDate, resaccUpdateby, resaccStID,";
				$sql .= " stDesc, acc.*, EAName, IFNULL(vpsHost,'-') AS vpsHost, IFNULL(vid, '-') AS vid, pacName ";
				$sql .= " FROM dtReqResetAcc";
				$sql .= " INNER JOIN dtTradingAcc acc ON tradeAccNo = resaccNo AND tradeUsername = resaccUsername";
				$sql .= " LEFT JOIN dtVPS ON vpsid=tradeVPS";
				$sql .= " INNER JOIN msStatus s ON stID = resaccStID";
				$sql .= " INNER JOIN msEA ON EAID = tradeEANum";
				$sql .= " INNER JOIN ( ";
				$sql .= "     SELECT trUsername, trPacID FROM Transaction t  ";
				$sql .= "     WHERE trID = ( ";
				$sql .= "       SELECT trID FROM Transaction ";
				$sql .= "       WHERE trUsername=t.trUsername  ";
				$sql .= "       ORDER BY trDate DESC ";
				$sql .= "       LIMIT 1 ";
				$sql .= "     ) ";
				$sql .= " ) tr ON tr.trUsername = acc.tradeUsername ";
				$sql .= " INNER JOIN msPackage ON pacID=tr.trPacID";
				$sql .= " LEFT JOIN dtvoucherid_ea ON vidEANum=tradeEANum AND vidAcc=tradeAccNo";
				$sql .= " WHERE acc.tradeStID = '".$DEF_STATUS_ACTIVE."' AND resaccStID ='".$DEF_STATUS_ONPROGRESS."' ";
				$sql .= " ORDER BY resaccDate DESC";
				// echo $sql;
				$result = $conn->query($sql);
				$totalRec = $result->num_rows;
				$numPerPage = $DEF_NUM_PER_PAGE;
				$numPages	= ceil ($totalRec / $numPerPage);
				$page = ($page<1)?1:$page;			
				$startRec = ($page-1) * $numPerPage;
				$secondLast = $numPages - 1;
				$sql .= " LIMIT " . $startRec . ", " . $numPerPage;
				$result = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped small" id="tReqreset">
						<thead>
							<tr>
								<th>Req. Date</th>
								<th>Username</th>
								<th>Trade Acc</th>
								<th>Expert Advisor</th>
								<th>VPS / VID</th>
								<th>Package</th>
								<th>Status</th>
								<th>Update Date</th>
								<th>Update By</th>
								<th>Action</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Req. Date</th>
								<th>Username</th>
								<th>Trade Acc</th>
								<th>Expert Advisor</th>
								<th>VPS / VID</th>
								<th>Package</th>
								<th>Status</th>
								<th>Update Date</th>
								<th>Update By</th>
								<th>Action</th>
							</tr>
						</tfoot>
						<tbody>
							<?php
							if ($result->num_rows == 0){
							?>
							<tr>
								<td colspan=9  class='text-center text-primary'>no record</td>
								<td style='display:none'></td>
								<td style='display:none'></td>
								<td style='display:none'></td>
								<td style='display:none'></td>
								<td style='display:none'></td>
								<td style='display:none'></td>
								<td style='display:none'></td>
								<td style='display:none'></td>
							</tr>
							<?php
							}	
							while ($row=$result->fetch_assoc()){
								if ($row['resaccStID'] == $DEF_STATUS_ONPROGRESS){
									$btnaction = '<a href="#reqresetacc" resaccid="'.$row["resaccID"].'" tradeid="'.$row["tradeID"].'" resaccUsername="'.$row["resaccUsername"].'" resaccNo="'.$row["resaccNo"].'">Reset / Cancel</a>';
								}else{
									$btnaction = '';
								}

								if ($row['tradeEANum'] == "78"){
									$txtEA = "<span class='text-danger' style='font-weight:bold;'>".$row['EAName']."</span>";
								}else if ($row['tradeEANum']=="79"){
									$txtEA = "<span class='text-success' style='font-weight:bold;'>".$row['EAName']."</span>";
								}
							?>
							<tr>
								<td><?php echo $row['resaccDate']; ?></td>
								<td><?php echo $row['resaccUsername']; ?></td>
								<td><?php echo ("<b style='color:red;'>".$row['tradeAccNo']."</b><br>".$row['tradeName'] . "<br>". $row['tradeServer']); ?></td>
								<td><?php echo $txtEA; ?></td>
								<td><?php echo $row['vpsHost']."<br>".$row['vid']; ?></td>
								<td><?php echo $row['pacName']; ?></td>
								<td><?php echo $row['stDesc']; ?></td>
								<td><?php echo $row['resaccUpdateDate']; ?></td>
								<td><?php echo $row['resaccUpdateby']; ?></td>
								<td><?php echo $btnaction; ?></td>
							</tr>
							<?php 
							}
							?>
						</tbody>
					</table>
					<?php 
					if ($result->num_rows > 0){
					?>
					<!-- pagination -->
					<div class="row text-center">
						<ul class="pagination">
							<?php 
							$prev = $next = "";	
							if ($page <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $page-1;
							if ($page >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $page+1;
							$adjacents = "2";
							?>
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=reqreset&page='.$pagePrev); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=$i'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=$i'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=$numPages'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=1'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=$i'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=$numPages'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=reqreset&page=1'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=reqreset&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=reqreset&page=$i'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=reqreset&page='.$pageNext); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=reqreset&page='.$numPages); ?>">Last &rsaquo;&rsaquo;</a></li>
						</ul>
					</div> 
					<?php 
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- The modals modalReqReset-->
<div class="modal-2" id="modalReqReset">
	<div class="modal-content-2">
		<div class="container-fluid">
			<div class="row text-center" id="alertMsgReqReset" style="display:none">
				<div class="alert alert-danger alert-dismissible">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<strong>Info - </strong><span id="msgReqReset"></span>
				</div>
			</div>
			<input type="hidden" name="req_resaccid" id="req_resaccid">
			<input type="hidden" name="req_tradeid" id="req_tradeid">
			<div class="row">
				<label class="col-md-4 text-left text-vCenter">Username</label>
				<div class="col-md-8">
					<div class="form-group">
						<input type="text" name="req_resaccUsername" id="req_resaccUsername" value="" class="form-control" title="" readonly required>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 text-left text-vCenter">Trade Acc</label>
				<div class="col-md-8">
					<div class="form-group">
						<input type="text" name="req_resaccNo" id="req_resaccNo" value="" class="form-control" title="" readonly required>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 text-left text-vCenter">Security Password</label>
				<div class="col-md-8">
					<div class="form-group">
						<input type="password" name="req_resaccsecPasswd" id="req_resaccsecPasswd" value="" class="form-control" title="" required>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4"></div>
				<div class="col-md-4">
					<button type="button" id="btnreqReset" class="btn btn-block col-md-12 btn-primary" title="Approve Reset Account">Approve Reset</button>
				</div>
				<div class="col-md-4">
					<button type="button" id="btncancelReqReset" class="btn btn-block col-md-12 btn-primary" title="Cancel Reset Account">Cancel Req Reset</button>
				</div>
			</div>
			<div class="row" style="padding-top:5px">
				<div class="col-md-4"></div>
				<div class="col-md-8">
					<button type="button" onClick="$('#modalReqReset').hide()" class="btn btn-block col-md-12 btn-danger">Close</button>
				</div>
			</div>
		</div>
	</div>
</div>