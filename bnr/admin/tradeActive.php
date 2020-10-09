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

		//Reset Trading Account______________
		$('a[href="#reset"]').on('click', function(){
			$tradeID        = $(this).attr('id');
			$tradeUsername  = $(this).attr('name');
			$tradeAcc       = $(this).attr('title');
			//$('input[name="reset_SendEmail"]').attr('checked', false); //reset checkbox
			//alert ($tradeID + "  " + $tradeAcc + " " + $tradeUsername);
			$('#reset_tradeID').attr('title', $tradeID);
			$('#reset_accUsername').val($tradeUsername);
			$('#reset_tradeAcc').val($tradeAcc);
			//show the modal
			$("#modalTradeReset").show();
		});

		$('#btnReset').on('click', function(){
			$("#btnReset").attr("disabled", true);
	      	var html = $("#btnReset").html();
	    	$("#btnReset").html(html + '&nbsp; <i class="fa fa-spinner fa-spin"  style="font-size:24px"></i>');
			$tradeID  = $('#reset_tradeID').attr('title');
			$secPasswd = $('#secPasswd').val();
			$isSendEmail = $('input[name="reset_SendEmail"]').is(':checked')
			//$('input[name=foo]').attr('checked') //this code also works
			$changePasswd = $('#changePasswd').val();
			// alert ($tradeID+" || "+$secPasswd+" || "+$isSendEmail+" || "+$changePasswd); return false;

			if ($isSendEmail){
				$sendEmail = "send";
			}else{
				$sendEmail = "no_send";
			}

			if ($tradeID == "" || $secPasswd == "" || $changePasswd != ""){
				$("#btnReset").attr("disabled", false);
				$("#btnReset").html("Reset");
				alert ("Incomplete/Invalid Data to RESET Trading Account");
				return false;
			}else{
				if ($tradeID != ""){
					$.post("json.php", 
					{
						"q"             : "ResetTradeAcc",
						"id"            : "",
						"tradeID"       : $tradeID,
						"secPasswd"     : $secPasswd,
						"tradeStID"     : "1",  //Pending / reset
						"sendEmail"     : $sendEmail,
					}, 
					function(data, success){
						$myDataObj  = JSON.parse(data);
						if ($.trim($myDataObj["status"]) == "success"){
							// location.href = "./?menu=tradeAcc&subMenu=active&searchActive="++"&q=" + $myDataObj["message"];
							const Toast = Swal.mixin({
								toast: true,
								position: 'center',
								showConfirmButton: false,
								timer: 2000,
								timerProgressBar: true,
								onClose: () => { location.reload() }
							});
							Toast.fire({
								icon: 'success',
								title: $myDataObj['message']
							});
						}else if ($.trim($myDataObj["status"]) == "error"){
							$("#btnReset").attr("disabled", false);
							$("#btnReset").html("Reset");
							alert ($myDataObj["message"]);
						}
					});
				}else {
					//demo.showNotification('top','center', 'info', "Please, fill all data before submit!");
					//alert ("Please, fill all data before submit!");
					$("#alertMsg").attr('style', 'display:block');
					$("#msg").html("<b>Please, fill all data before submit!</b>");
					$(document).load();
				}
			}
		});

		$('#btnChangePasswd').on('click', function(){
			$("#btnChangePasswd").attr("disabled", true);
	      	var html = $("#btnChangePasswd").html();
	    	$("#btnChangePasswd").html(html + '&nbsp; <i class="fa fa-spinner fa-spin"  style="font-size:24px"></i>');
			$tradeID  = $('#reset_tradeID').attr('title');
			$secPasswd = $('#secPasswd').val();
			$changePasswd = $("#changePasswd").val();
			$isSendEmail = $('input[name="reset_SendEmail"]').is(':checked')
			//$('input[name=foo]').attr('checked') //this code also works

			if ($isSendEmail){
				$sendEmail = "send";
			}else{
				$sendEmail = "no_send";
			}

			if ($tradeID == "" || $secPasswd == "" || $changePasswd == ""){
				alert ("Incomplete Data to CHANGE Trading Account");
			}else{
				//alert ($tradeID + " " + $secPasswd);
				if ($tradeID != ""){
					$.post("json.php", 
					{
						"q"             : "ChangePasswdTradeAcc",
						"id"            : "",
						"tradeID"       : $tradeID,
						"secPasswd"     : $secPasswd,
						"sendEmail"     : $sendEmail,
						"changePasswd"  : $changePasswd,
					}, 
					function(data, success){
						$myDataObj  = JSON.parse(data);
						if ($.trim($myDataObj["status"]) == "success"){
							// location.href = "./?menu=tradeAcc&subMenu=active&q=" + $myDataObj["message"]; 
							const Toast = Swal.mixin({
								toast: true,
								position: 'center',
								showConfirmButton: false,
								timer: 2000,
								timerProgressBar: true,
								onClose: () => { location.reload() }
							});
							Toast.fire({
								icon: 'success',
								title: $myDataObj['message']
							});
						}else if ($.trim($myDataObj["status"]) == "error"){
							$("#btnChangePasswd").attr("disabled", false);
							$("#btnChangePasswd").html("Change Trading Password");
							alert ($myDataObj["message"]);
						}
					});
				}else {
					//demo.showNotification('top','center', 'info', "Please, fill all data before submit!");
					//alert ("Please, fill all data before submit!");
					$("#alertMsg").attr('style', 'display:block');
					$("#msg").html("<b>Please, fill all data before submit!</b>");
					$(document).load();
				}
			}
		});

		$('#btnCancel').on('click', function(){
			var modalTradeAcc = document.getElementById('modalTradeReset');
			modalTradeAcc.style.display='none';
			//document.getElementById('modalTradeReset').style.display='none';
		});
	});
</script>

<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Trading Account</div>
		<span id="q" title="" class="text-center text-success"><b><?php echo $q; ?></b></span>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "active")); ?>"><a data-toggle="tab" href="#active">Active</a></li>
		</ul>
		<div class="tab-content">
			<!-- active -->
			<div id="active" class="tab-pane fade in <?php echo (setActive($subMenu, "active")); ?>" >
				<?php 
				$sqlWhere = "";
				$searchActive = (isset($_GET['searchActive']))?fValidateSQLFromInput($conn, $_GET['searchActive']): '';
				if ($searchActive != ""){
					$sqlWhere  = " AND (tradeUsername LIKE '%".$searchActive."%' ";
					$sqlWhere .= " OR tradeName LIKE '%".$searchActive."%' ";
					$sqlWhere .= " OR tradeAccNo LIKE '%".$searchActive."%' ";
					$sqlWhere .= " OR vpsHost LIKE '%".$searchActive."%' ";
					$sqlWhere .= " OR vid LIKE '%".$searchActive."%') ";
				}
				?>
				<hr>
				<form action="./" method="GET">
					<input type="hidden" name="menu" value="<?php echo $menu; ?>">
					<input type="hidden" name="subMenu" value="<?php echo $subMenu; ?>">
					<div class="row">
						<div class="form-group col-lg-3 col-md-3">
							<label for="searchActive">Search</label>
							<input type="text" class="form-control" name="searchActive" id="searchActive" value="<?php echo $searchActive; ?>">
						</div>
						<div class="form-group col-lg-3 col-md-3">
							<br>
							<button type="submit" id="submit" class="btn btn-info btn-round">Submit</button>
						</div>
						<div class="form-group col-lg-6 col-md-6">
						</div>
					</div>
				</form>
				<hr>
				<?php 
				$sql  = " SELECT COUNT(*) totalRec FROM dtTradingAcc acc";
				$sql .= " INNER JOIN dtVPS ON vpsid=tradeVPS";
				$sql .= " INNER JOIN msStatus s ON stID = tradeStID";
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
				$sql .= " INNER JOIN dtMember ON mbrUsername=trUsername";
				$sql .= " LEFT JOIN dtvoucherid_ea ON vidEANum=tradeEANum AND vidAcc=tradeAccNo";
				$sql .= " LEFT JOIN dtTripleVIP ON 3vipusername1=acc.tradeUsername OR 3vipusername2=acc.tradeUsername OR 3vipusername2=acc.tradeUsername";
				$sql  .= " WHERE tradeID = (";
				$sql  .= "     SELECT tradeID FROM dtTradingAcc ";
				$sql  .= "     WHERE tradeUsername = acc.tradeUsername";
				$sql  .= "      AND tradeStID ='".$DEF_STATUS_ACTIVE."'";
				$sql  .= "     ORDER BY tradeDate DESC";
				$sql  .= "     LIMIT 1";
				$sql  .= "     )";
				$sql  .= " AND tradeStID ='".$DEF_STATUS_ACTIVE."'";
				$sql  .= $sqlWhere;
				$sql  .= " ORDER BY mbrDate DESC";
				$result = $conn->query($sql);
				$row = $result->fetch_assoc();
				$totalRec = $row['totalRec'];

				$sql  = " Select acc.*, s.stDesc, trPacID, pacName, vpsHost, IFNULL(vid, '-') AS vid, vpshost, IFNULL(3vipusername1, '-') 3vipusername1";
				$sql .= " FROM dtTradingAcc acc";
				$sql .= " INNER JOIN dtVPS ON vpsid=tradeVPS";
				$sql .= " INNER JOIN msStatus s ON stID = tradeStID";
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
				$sql .= " INNER JOIN dtMember ON mbrUsername=trUsername";
				$sql .= " LEFT JOIN dtvoucherid_ea ON vidEANum=tradeEANum AND vidAcc=tradeAccNo";
				$sql .= " LEFT JOIN dtTripleVIP ON 3vipusername1=acc.tradeUsername OR 3vipusername2=acc.tradeUsername OR 3vipusername2=acc.tradeUsername";
				$sql .= " WHERE tradeID = (";
				$sql .= "     SELECT tradeID FROM dtTradingAcc ";
				$sql .= "     WHERE tradeUsername = acc.tradeUsername";
				$sql .= "      AND tradeStID ='".$DEF_STATUS_ACTIVE."'";
				$sql .= "     ORDER BY tradeDate DESC";
				$sql .= "     LIMIT 1";
				$sql .= "     )";
				$sql .= " AND tradeStID ='".$DEF_STATUS_ACTIVE."'";
				$sql  .= $sqlWhere;
				$sql  .= " ORDER BY mbrDate DESC";
				$numPerPage = $DEF_NUM_PER_PAGE;
				$numPages	= ceil ($totalRec / $numPerPage);
				$page = ($page<1)?1:$page;			
				$startRec = ($page-1) * $numPerPage;
				$secondLast = $numPages - 1;
				$sql .= " LIMIT " . $startRec . ", " . $numPerPage;
				// echo $sql;
				$result = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped small" id="tActive">
						<thead>
							<tr>
								<th>Date of Acc</th>
								<th>Username / Trade Name</th>
								<th>Trade Acc</th>
								<th>Trade Code</th>
								<th>Server</th>
								<th>VPS / VID</th>
								<th>Package</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Date of Acc</th>
								<th>Username / Trade Name</th>
								<th>Trade Acc</th>
								<th>Trade Code</th>
								<th>Server</th>
								<th>VPS / VID</th>
								<th>Package</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($result->num_rows == 0){
								echo "<tr><td colspan=10 class='text-center text-primary'>no record</td></tr>";  
						}
						while ($row = $result->fetch_assoc()){
							if ($row['3vipusername1'] != "-" ){
								$pacName = "Triple VIP";
							}else{
								$pacName = $row["pacName"];
							}
						?>
							<tr>
								<td><?php echo $row["tradeDate"] ?></td>
								<td><?php echo ($row["tradeUsername"] . " / " . $row["tradeName"]); ?></td>
								<td><?php echo $row["tradeAccNo"] ?></td>
								<td><?php echo $row["tradeAccPasswd"]?></td>
								<td><?php echo $row["tradeServer"]?></td>
								<td><?php echo $row["vpsHost"] . "<br>" . $row["vid"]?></td>
								<td><?php echo $pacName ?></td>
								<td><?php echo $row["stDesc"] ?></td>
								<td><a href="#reset" id="<?php echo ($row["tradeID"]); ?>" name="<?php echo ($row["tradeUsername"]); ?>" title="<?php echo ($row["tradeAccNo"]); ?>">Reset / Change Pass</a></td>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&page='.$pagePrev.'&searchActive='.$searchActive); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=active&page=$i&searchActive=$searchActive'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=active&page=$i&searchActive=$searchActive'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=$secondLast&searchActive=$searchActive'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=$numPages&searchActive=$searchActive'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=active&page=1'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=active&page=$i&searchActive=$searchActive'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=$secondLast&searchActive=$searchActive'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=$numPages&searchActive=$searchActive'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=active&page=1'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=active&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=active&page=$i&searchActive=$searchActive'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&page='.$pageNext.'&searchActive='.$searchActive); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&page='.$numPages.'&searchActive='.$searchActive); ?>">Last &rsaquo;&rsaquo;</a></li>
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

<!-- The modals modalTradeReset-->
<div class="modal-2" id="modalTradeReset">
	<div class="modal-content-2">
		<div class="container-fluid">
			<span id="reset_tradeID" title=""></span>
			<div class="row">
				<label class="col-md-4 text-left text-vCenter">Username</label>
				<div class="col-md-8">
					<div class="form-group">
						<input type="text" name="accUsername" id="reset_accUsername" value="" class="form-control" title="" readonly required>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 text-left text-vCenter">Trade Acc</label>
				<div class="col-md-8">
					<div class="form-group">
						<input type="text" name="tradeAcc" id="reset_tradeAcc" value="" class="form-control" title="" readonly required>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 text-left text-vCenter">Change Trading Password</label>
				<div class="col-md-8">
					<div class="form-group">
						<input type="password" name="changePasswd" id="changePasswd" value="" class="form-control" title="Change Trading Password">
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 text-left text-vCenter">Security Password</label>
				<div class="col-md-8">
					<div class="form-group">
						<input type="password" name="secPasswd" id="secPasswd" value="" class="form-control" title="" required>
					</div>
				</div>
			</div>
			<div class="row">
				<label class="col-md-4 text-left text-vCenter"></label>
				<div class="col-md-8">
					<div class="form-group">
						<input type="checkbox" value="" name="reset_SendEmail" id="reset_SendEmail"> Send Email
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4"></div>
				<div class="col-md-4">
					<button type="button" id="btnReset" class="btn btn-block col-md-12 btn-primary" title="Reset Trading Account">Reset</button>
				</div>
				<div class="col-md-4">
					<button type="button" id="btnChangePasswd" class="btn btn-block col-md-12 btn-primary" title="Change Trading Password">Change Trading Password</button>
				</div>
			</div>
			<div class="row" style="padding-top:5px">
				<div class="col-md-4"></div>
				<div class="col-md-8">
					<button type="button" id="btnCancel" class="btn btn-block col-md-12 btn-danger">Cancel</button>
				</div>
			</div>
		</div>
	</div>
</div>