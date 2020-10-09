<?php
$q = (isset($_GET["q"]))?$_GET["q"]: "";
$page = (isset($_GET['page']))? $_GET['page'] : 1;
$pageActive = (isset($_GET['pageActive']))? $_GET['pageActive'] : 1;
$numPerPage = $DEF_NUM_PER_PAGE;


//$menu = (isset($_GET['menu']))? $_GET['menu'] : "";us
$subMenu = (isset($_GET['subMenu']))? $_GET['subMenu'] : "onprogress";
?>

<script>
	$(document).ready(function(e) {
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
			var modalTradeAcc = document.getElementById('modalTradeReset');
			modalTradeAcc.style.display='block';
		});


		$('#btnReset').on('click', function(){
			$tradeID  = $('#reset_tradeID').attr('title');
			$secPasswd = $('#secPasswd').val();
			$isSendEmail = $('input[name="reset_SendEmail"]').is(':checked')
			//$('input[name=foo]').attr('checked') //this code also works
			$changePasswd = $('#changePasswd').val();
			if ($isSendEmail){
				$sendEmail = "send";
			}else{
				$sendEmail = "no_send";
			}
			if ($tradeID == "" || $secPasswd == "" || $changePasswd != ""){
				alert ("Incomplete/Invalid Data to RESET Trading Account");
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
							//location.href = "incomingDeposit.php?q=" + $myDataObj["message"]; 
							//alert($myDataObj["message"]);
							location.href = "./?menu=tradeAcc&subMenu=active&q=" + $myDataObj["message"]; 
							//$("#finID").attr('title', ""); //reset finID
						}else if ($.trim($myDataObj["status"]) == "error"){
							//alert ($myDataObj["errDesc"]);
							//demo.showNotification('top','center', 'info', $myDataObj["message"]);
							alert ($myDataObj["message"]);

							//$("#alertMsg").attr('style', 'display:block');
							//$("#msg").html($myDataObj["message"]);
							//$(document).load();
						}
					});
				}else{
					//demo.showNotification('top','center', 'info', "Please, fill all data before submit!");
					//alert ("Please, fill all data before submit!");
					$("#alertMsg").attr('style', 'display:block');
					$("#msg").html("<b>Please, fill all data before submit!</b>");
					$(document).load();
				}
			}
		});
		
		$('#btnChangePasswd').on('click', function(){
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
							//location.href = "incomingDeposit.php?q=" + $myDataObj["message"]; 
							//alert($myDataObj["message"]);
							location.href = "./?menu=tradeAcc&subMenu=active&q=" + $myDataObj["message"]; 
							//$("#finID").attr('title', ""); //reset finID
						}else if ($.trim($myDataObj["status"]) == "error"){
							//alert ($myDataObj["errDesc"]);
							//demo.showNotification('top','center', 'info', $myDataObj["message"]);
							alert ($myDataObj["message"]);

							//$("#alertMsg").attr('style', 'display:block');
							//$("#msg").html($myDataObj["message"]);
							//$(document).load();
						}
					});
				}else{
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

		//end reset trading account ____________________

		$('a[href="#"]').on('click', function(){
			//alert ($(this).attr('name'));
			$id = $(this).attr('name');
			$.get("json.php", 
			{
				"q" : "tradeAccount",
				"id" : $id,
				"st" : "2" //on progress
			},
			function (data, success){
				$myDataObj = JSON.parse(data);
				if ($.trim($myDataObj["status"])=="error"){
					$errDesc = $.trim($myDataObj["message"]);
					alert ($errDesc);
					//admin site not support demo.showNotification Script
					//demo.showNotification('top','center', 'info', $errDesc);
				}else{
					$('#accUsername').val($myDataObj["tradeUsername"]);
					$('#tradeName').val($myDataObj["tradeName"]);
					$('#tradeAcc').val($myDataObj["tradeAccNo"]);
					$('#tradeEANum').val($myDataObj["tradeEANum"]);
					
					$("#tradeID").attr('title', $id);
					var modalTradeAcc = document.getElementById('modalTradeAcc');
					modalTradeAcc.style.display='block';
				}
				
			});
		});
		
		//Click Confirmation button
		$("#submit").on('click', function(){
			$tradeID      = $("#tradeID").attr('title');
			$tradeEANum   = $("#tradeEANum").val();
			$tradeAcc     = $("#tradeAcc").val();

			//verify input
			if ($tradeID != "" ){
				$.post("json.php", 
				{
					"q"             : "SaveDataTradeAcc",
					"id"            : "",
					"tradeID"       : $tradeID,
					"tradeStID"     : "8",  //activate
					"tradeEANum"    : $tradeEANum,
					"tradeAcc"      : $tradeAcc,
				}, 
				function(data, success){
					$myDataObj  = JSON.parse(data);
					
					if ($.trim($myDataObj["status"]) == "success"){
						//location.href = "incomingDeposit.php?q=" + $myDataObj["message"]; 
						location.href = "./?menu=tradeAcc&subMenu=onprogress&q=" + $myDataObj["message"]; 
						//$("#finID").attr('title', ""); //reset finID
					}else if ($.trim($myDataObj["status"]) == "error"){
						//alert ($myDataObj["errDesc"]);
						//demo.showNotification('top','center', 'info', $myDataObj["message"]);
						//alert ($myDataObj["message"]);
						$("#alertMsg").attr('style', 'display:block');
						$("#msg").html($myDataObj["message"]);
						$(document).load();
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
		
		$(document).ready(function(){
			$q = $("#q").attr('title');
			if ($q == "confirmed"){
				//demo.showNotification('top','center', 'success', "Confirmation Successfully");
				alert ("Confirmation Successfully");
			}
			if ($q == "declined"){
				//demo.showNotification('top','center', 'success', "Confirmation Successfully");
				alert ("Process Decline Successfully");
			}
		});

		$("#btnDeclined").on('click', function(e) {
			$tradeID      = $("#tradeID").attr('title');
			$tradeEANum   = $("#tradeEANum").val();
			$tradeAcc     = $("#tradeAcc").val();
			var r = confirm("Are you sure you want to be declined?");
			if (r == true) {
				//verify input
				if ($tradeID != "" ){
					$.post("json.php", 
					{
						"q"             : "SaveDataTradeAcc",
						"id"            : "",
						"tradeID"       : $tradeID,
						"tradeStID"     : "1",  //pending
						"tradeEANum"    : $tradeEANum,
						"tradeAcc"      : $tradeAcc,
					}, 
					function(data, success){
						$myDataObj  = JSON.parse(data);
						
						if ($.trim($myDataObj["status"]) == "success"){
							//location.href = "incomingDeposit.php?q=" + $myDataObj["message"]; 
							location.href = "./?menu=tradeAcc&q=" + $myDataObj["message"]; 
							//$("#finID").attr('title', ""); //reset finID
						}else if ($.trim($myDataObj["status"]) == "error"){
							//alert ($myDataObj["errDesc"]);
							//demo.showNotification('top','center', 'info', $myDataObj["message"]);
							//alert ($myDataObj["message"]);
							$("#alertMsg").attr('style', 'display:block');
							$("#msg").html($myDataObj["message"]);
							$(document).load();
						}
					});
				}else{
					//demo.showNotification('top','center', 'info', "Please, fill all data before submit!");
					//alert ("Please, fill all data before submit!");
					$("#alertMsg").attr('style', 'display:block');
					$("#msg").html("<b>Please, fill all data before submit!</b>");
					$(document).load();
				}
			}else{
					alert ("Declined process is cancelled")
			}
			
		});
	}); //end $(document).ready(function(e) {
</script>

<script>
	// Get the modal
	//var modal = document.getElementById('modalTradeAcc');
	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target.id == "modalTradeAcc")
		document.getElementById('modalTradeAcc').style.display='none';
		//alert (event.target.id);

		if (event.target.id == "modalTradeReset")
		document.getElementById('modalTradeReset').style.display='none';
	}

	
</script>

<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Trading Account</div>
		<span id="q" title="" class="text-center text-success"><b><?php echo $q; ?></b></span>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "onprogress")); ?>"><a data-toggle="tab" href="#onprogress">On Progress</a></li>
			<li class="<?php echo (setActive($subMenu, "renew")); ?>"><a data-toggle="tab" href="#renew">Renew</a></li>
			<li class="<?php echo (setActive($subMenu, "pending")); ?>"><a data-toggle="tab" href="#pending">Stopped (Pending)</a></li>
			<li class="<?php echo (setActive($subMenu, "active")); ?>"><a data-toggle="tab" href="#active">Active</a></li>
			<li class="<?php echo (setActive($subMenu, "tradeoff")); ?>"><a data-toggle="tab" href="#tradeoff">TradeOff</a></li>
			<!--  <li class="<?php //echo (setActive($subMenu, "blocked")); ?>"><a data-toggle="tab" href="#blocked">Blocked / Declined</a></li> -->
			<li class="<?php echo (setActive($subMenu, "reqreset")); ?>"><a data-toggle="tab" href="#reqreset">Reuest Reset</a></li>
		</ul>
		<div class="tab-content">
			<div id="onprogress" class="tab-pane fade in <?php echo (setActive($subMenu, "onprogress")); ?>">
				<h3>On Progress</h3>
				<!--- On Progress -->
				<?php       
				$sql = " Select COUNT(*) totalRec FROM dtTradingAcc acc";
				$sql .= " INNER JOIN msStatus s ON stID = tradeStID";
				$sql  .= " WHERE tradeID = (";
				$sql  .= "     SELECT tradeID FROM dtTradingAcc ";
				$sql  .= "     WHERE tradeUsername = acc.tradeUsername";
				$sql  .= "     ORDER BY tradeDate DESC";
				$sql  .= "     LIMIT 1";
				$sql  .= "     )";
				$sql  .= "  AND tradeStID='". $DEF_STATUS_ONPROGRESS . "'";
				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages = ceil ($totalRec / $numPerPage); 
				$pageActive = ($pageActive<1)?1:$pageActive;        
				$startRec = ($pageActive-1) * $numPerPage;
									
				$sql = " Select acc.*, s.stDesc, trPacID, trDate, pacName, mbrFirstName, mbrEmail, tradeID, tradevps, IFNULL(vpshost, '-') vpshost, IFNULL(vid, '-') vid, pairName FROM dtTradingAcc acc";
				$sql .= " INNER JOIN msStatus s ON stID = tradeStID";
				$sql .= " INNER JOIN msPair ON tradePair=pairID";
				$sql .= " INNER JOIN ( ";
				$sql .= "     SELECT trUsername, trPacID, trDate FROM Transaction t  ";
				$sql .= "     WHERE trID = ( ";
				$sql .= "       SELECT trID FROM Transaction ";
				$sql .= "       WHERE trUsername=t.trUsername  ";
				$sql .= "       ORDER BY trDate DESC ";
				$sql .= "       LIMIT 1 ";
				$sql .= "     ) ";
				$sql .= " ) tr ON tr.trUsername = acc.tradeUsername ";
				$sql .= " INNER JOIN msPackage ON pacID=tr.trPacID";
				$sql .= " INNER JOIN dtMember ON mbrUsername=tradeUsername";
				$sql .= " LEFT JOIN dtVPS ON vpsid=tradeVPS";
				$sql .= " LEFT JOIN dtvoucherid_ea ON vidAcc=acc.tradeAccNo AND acc.tradeEANum=vidEANum";
				$sql  .= " WHERE tradeID = (";
				$sql  .= "     SELECT tradeID FROM dtTradingAcc ";
				$sql  .= "     WHERE tradeUsername = acc.tradeUsername";
				$sql  .= "     AND tradeStID ='".$DEF_STATUS_ONPROGRESS."'";
				$sql  .= "     ORDER BY tradeDate DESC";
				$sql  .= "     LIMIT 1";
				$sql  .= "     )";
				$sql  .= " AND tradeStID ='".$DEF_STATUS_ONPROGRESS."'";
				$sql  .= " ORDER BY tradeDate ASC";
				$sql  .= " limit " . $startRec . ", " . $numPerPage;
				$queryPending = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped small">
						<thead>
							<tr>
								<th>Username / JoinDate</th>
								<th>Trade Name</th>
								<th>Trade Acc</th>
								<th>Trade Code</th>
								<th>Server</th>
								<th>Pack / Pair</th>
								<th>VPS / VID</th>
								<th>Name / Email</th>
								<th>Status / UpdateAcc</th>
								<th class="disabled-sorting text-right">Action</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Username / JoinDate</th>
								<th>Trade Name</th>
								<th>Trade Acc</th>
								<th>Trade Code / VID</th>
								<th>Server</th>
								<th>Pack / Pair</th>
								<th>VPS / VID</th>
								<th>Name / Email</th>
								<th>Status / UpdateAcc</th>
								<th class="text-right">Action</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($queryPending->num_rows == 0){
								echo "<tr><td colspan=10 class='text-center text-primary'>no record</td></tr>";  
						}
						while ($rowPending = $queryPending->fetch_assoc()){
						?>
							<tr>
								<td><?php echo ($rowPending["tradeUsername"] . "<br>" . $rowPending["trDate"] ); ?></td>
								<td><?php echo $rowPending["tradeName"] ?></td>
								<td><?php echo $rowPending["tradeAccNo"] ?></td>
								<td><?php echo $rowPending["tradeAccPasswd"] ?></td>
								<td><?php echo $rowPending["tradeServer"]?></td>
								<td><?php echo ($rowPending["pacName"] . "<br>". $rowPending["pairName"]); ?></td>
								<td><?php echo $rowPending["vpshost"] . "<br>" . $rowPending["vid"] ?></td>
								<td><?php echo $rowPending["mbrFirstName"] . "<br>" . $rowPending["mbrEmail"] ?></td>
								<td><?php echo ($rowPending["stDesc"] . "<br>". $rowPending["tradeDate"]); ?></td>
								<td class="text-right">
									<a href="#" name="<?php echo $rowPending["tradeID"] ?>" title="Update Confirmation" ><i class="fa fa-edit fa-2x"></i></a>
								</td>
							</tr>
						<?php 
						} 
						?>
						</tbody>
					</table>
				</div>
				<!-- pagination -->
				<div class="row text-right">
					<ul class="pagination">
					<?php 
					$prev = $next = ""; 
					if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
					if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
					?>
						<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=onprogress&pageActive=' . $pagePrev); ?>">Previous</a></li>
					<?php 
					for ($i=1; $i<=$numPages; $i++){
						$active = "";
						if ($i == $pageActive) $active = "active";
						echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=onprogress&pageActive=$i'>$i</a></li>";
					}
					?>
						<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=onprogress&pageActive=' . $pageNext); ?>">Next</a></li>
					</ul>&nbsp;&nbsp;&nbsp;&nbsp;
				</div> 
			</div>
			<div id="renew" class="tab-pane fade in <?php echo (setActive($subMenu, "renew")); ?>">
				<h3>Renew</h3>
				<!-- Renew -->
				<?php       
				$sql = " Select COUNT(*) totalRec FROM dtTradingAcc acc";
				$sql  .= " WHERE tradeID = (";
				$sql  .= "     SELECT tradeID FROM dtTradingAcc INNER JOIN Transaction ON tradeUsername=trUsername ";
				$sql  .= "     WHERE tradeUsername = acc.tradeUsername AND trThn > 1";
				$sql  .= "     ORDER BY tradeDate DESC LIMIT 1";
				$sql  .= "     )";
				//$sql  .= "  AND tradeStID='". $DEF_STATUS_ONPROGRESS . "'";
				//echo $sql;
				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages = ceil ($totalRec / $numPerPage); 
				$pageActive = ($pageActive<1)?1:$pageActive;        
				$startRec = ($pageActive-1) * $numPerPage;
				
				$sql = " Select acc.*, s.stDesc, tr.trPacID, pacName, mbrFirstName, mbrEmail, tradeID, tradevps, IFNULL(vpshost, '-') vpshost, IFNULL(vid, '-') vid, mbrDate, DATE_ADD(mbrDate, INTERVAL tr.trThn YEAR) AS expDate, trDate  FROM dtTradingAcc acc";
				$sql .= " INNER JOIN msStatus s ON stID = tradeStID";
				$sql .= " INNER JOIN ( ";
				$sql .= "     SELECT trUsername, trPacID, trDate, trThn FROM Transaction t  ";
				$sql .= "     WHERE trID = ( ";
				$sql .= "       SELECT trID FROM Transaction ";
				$sql .= "       WHERE trUsername=t.trUsername  ";
				$sql .= "       ORDER BY trDate DESC ";
				$sql .= "       LIMIT 1 ";
				$sql .= "     ) ";
				$sql .= " ) tr ON tr.trUsername = acc.tradeUsername ";
				$sql .= " INNER JOIN msPackage ON pacID=tr.trPacID";
				$sql .= " INNER JOIN dtMember ON mbrUsername=tradeUsername";
				$sql .= " LEFT JOIN dtVPS ON vpsid=tradeVPS";
				$sql .= " LEFT JOIN dtvoucherid_ea ON vidAcc=acc.tradeAccNo AND acc.tradeEANum=vidEANum";
				$sql  .= " WHERE tradeID = (";
				$sql  .= "     SELECT tradeID FROM dtTradingAcc INNER JOIN Transaction ON tradeUsername=trUsername ";
				$sql  .= "     WHERE tradeUsername = acc.tradeUsername AND DATE_ADD(trDate, INTERVAL 2 WEEK) > CURRENT_DATE() AND trThn > 1";
				//$sql  .= "     AND tradeStID ='".$DEF_STATUS_ONPROGRESS."'";
				$sql  .= "     ORDER BY tradeDate DESC LIMIT 1";
				$sql  .= "     )";
				//$sql  .= " AND tradeStID ='".$DEF_STATUS_ONPROGRESS."'";
				$sql  .= " ORDER BY tr.trDate DESC ";
				$sql  .= " LIMIT " . $startRec . ", " . $numPerPage;
				//echo $sql;
				$queryRenew = $conn->query($sql);
				?>
				<div >
					<table class="table table-hover table-striped small">
						<thead>
							<tr>
								<th>Username / Renew</th>
								<th>Trade Name</th>
								<th>Trade Acc</th>
								<th>Trade Code</th>
								<th>Server</th>
								<th>Package</th>
								<th>VPS / VID</th>
								<th>Expiry Date</th>
								<th>Name / Email</th>
								<th>Status</th>
								<!-- <th class="disabled-sorting text-right">Action</th> -->
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Username / Renew</th>
								<th>Trade Name</th>
								<th>Trade Acc</th>
								<th>Trade Code</th>
								<th>Server</th>
								<th>Package</th>
								<th>VPS / VID</th>
								<th>Expiry Date</th>
								<th>Name / Email</th>
								<th>Status</th>
								<!-- <th class="text-right">Action</th> -->
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($queryRenew->num_rows == 0){
								echo "<tr><td colspan=9 class='text-center text-primary'>no record</td></tr>";  
						}
						while ($rowRenew = $queryRenew->fetch_assoc()){
						?>
							<tr>
								<td><?php echo ($rowRenew["tradeUsername"] . "<br>". $rowRenew["trDate"]); ?></td>
								<td><?php echo $rowRenew["tradeName"] ?></td>
								<td><?php echo $rowRenew["tradeAccNo"] ?></td>
								<td><?php echo $rowRenew["tradeAccPasswd"]?></td>
								<td><?php echo $rowRenew["tradeServer"]?></td>
								<td><?php echo $rowRenew["pacName"] ?></td>
								<td><?php echo ($rowRenew["vpshost"] . "<br>" . $rowRenew["vid"]); ?></td>
								<td><?php echo $rowRenew["expDate"] ?></td>
								<td><?php echo ($rowRenew["mbrFirstName"] . "<br>" . $rowRenew["mbrEmail"]); ?></td>
								<td><?php echo $rowRenew["stDesc"] ?></td>
								<!-- <td class="text-right">
									<a href="#" name="<?php //echo $rowRenew["tradeID"] ?>" title="Update Confirmation" ><i class="fa fa-edit fa-2x"></i></a>
								</td>
								-->
							</tr>
						<?php 
						} 
						?>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=onprogress&pageActive=' . $pagePrev); ?>">Previous</a></li>
							<?php 
							for ($i=1; $i<=$numPages; $i++){
								$active = "";
								if ($i == $pageActive) $active = "active";
								echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=onprogress&pageActive=$i'>$i</a></li>";
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=onprogress&pageActive=' . $pageNext); ?>">Next</a></li>
						</ul>&nbsp;&nbsp;&nbsp;&nbsp;
					</div> 
				</div>
			</div>
			<!-- Pending -->
			<div id="pending" class="tab-pane fade in <?php echo (setActive($subMenu, "pending")); ?>">
				<h3>Stopped / Pending</h3>
				<!--- Pending -->
				<?php       
				$sql = " Select COUNT(*) totalRec FROM dtTradingAcc acc";
				$sql .= " INNER JOIN msStatus s ON stID = tradeStID";
				$sql  .= " WHERE tradeID = (";
				$sql  .= "     SELECT tradeID FROM dtTradingAcc ";
				$sql  .= "     WHERE tradeUsername = acc.tradeUsername";
				$sql  .= "     ORDER BY tradeDate DESC";
				$sql  .= "     LIMIT 1";
				$sql  .= "     )";
				$sql  .= "  AND tradeStID='". $DEF_STATUS_PENDING . "'";
				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages = ceil ($totalRec / $numPerPage); 
				$pageActive = ($pageActive<1)?1:$pageActive;        
				$startRec = ($pageActive-1) * $numPerPage;
				
				$sql = " Select acc.*, s.stDesc, trPacID, pacName FROM dtTradingAcc acc";
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
				$sql  .= " WHERE tradeID = (";
				$sql  .= "     SELECT tradeID FROM dtTradingAcc ";
				$sql  .= "     WHERE tradeUsername = acc.tradeUsername";
				$sql  .= "     AND tradeStID ='".$DEF_STATUS_PENDING."'";
				$sql  .= "     ORDER BY tradeDate DESC";
				$sql  .= "     LIMIT 1";
				$sql  .= "     )";
				$sql  .= " AND tradeStID ='".$DEF_STATUS_PENDING."'";
				$sql  .= " limit " . $startRec . ", " . $numPerPage;
				//fPrint($sql);
				$queryPending = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped small">
						<thead>
							<tr>
								<th>Username</th>
								<th>Trade Name</th>
								<th>Trade Acc</th>
								<th>Server</th>
								<th>Package</th>
								<th>Status</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Username</th>
								<th>Trade Name</th>
								<th>Trade Acc</th>
								<th>Server</th>
								<th>Package</th>
								<th>Status</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($queryPending->num_rows == 0){
								echo "<tr><td colspan=6 class='text-center text-primary'>no record</td></tr>";  
						}
						while ($rowPending = $queryPending->fetch_assoc()){
						?>
							<tr>
								<td><?php echo $rowPending["tradeUsername"] ?></td>
								<td><?php echo $rowPending["tradeName"] ?></td>
								<td><?php echo $rowPending["tradeAccNo"] ?></td>
								<td><?php echo $rowPending["tradeServer"]?></td>
								<td><?php echo $rowPending["pacName"] ?></td>
								<td><?php echo $rowPending["stDesc"] ?></td>
							</tr>
							<?php 
							} 
							?>
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
			<div id="active" class="tab-pane fade in <?php echo (setActive($subMenu, "active")); ?>" >
				<h3>Active</h3>
				<?php 
				$txtSearch = isset($_GET['txtSearch'])?$_GET['txtSearch']: '';
				$sqlWhere = "";
				if ($txtSearch != ""){
					$sqlWhere = " AND ( tradeUsername like '%".$txtSearch."%' OR tradeAccNo like '%".$txtSearch."%' OR tradeName like '%".$txtSearch. "%' OR tradeServer like '%".$txtSearch. "%' OR vpshost like '%" . $txtSearch . "%' ) ";
				} 
				?>
				<form action="./" method="GET">
					<input type="hidden" name="menu" value="tradeAcc">
					<input type="hidden" name="subMenu" value="active">
					<input type="text" name="txtSearch" value="<?php echo ($txtSearch); ?>"><button type="submit">Search</button>
				</form>
				<!-- Active -->
				<?php 
				$sql = " Select COUNT(*) totalRec FROM dtTradingAcc acc";
				$sql .= " INNER JOIN msStatus s ON stID = tradeStID";
				$sql .= " INNER JOIN dtVPS ON vpsid=tradeVPS";
				$sql  .= " WHERE tradeID = (";
				$sql  .= "     SELECT tradeID FROM dtTradingAcc ";
				$sql  .= "     WHERE tradeUsername = acc.tradeUsername";
				$sql  .= "     ORDER BY tradeDate DESC";
				$sql  .= "     LIMIT 1";
				$sql  .= "     )";
				$sql  .= "  AND tradeStID='". $DEF_STATUS_ACTIVE . "'";
				$sql  .= $sqlWhere;
				
				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages = ceil ($totalRec / $numPerPage); 
				$pageActive = ($pageActive<1)?1:$pageActive;        
				$startRec = ($pageActive-1) * $numPerPage;
				
				$sql = " Select acc.*, s.stDesc, trPacID, pacName, vpsHost, IFNULL(vid, '-') AS vid, vpshost FROM dtTradingAcc acc";
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
				$sql  .= " limit " . $startRec . ", " . $numPerPage;
				// echo ($sql);
				$queryApproved = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped small">
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
						if ($queryApproved->num_rows == 0){
								echo "<tr><td colspan=10 class='text-center text-primary'>no record</td></tr>";  
						}
						while ($rowApproved = $queryApproved->fetch_assoc()){
						?>
							<tr>
								<td><?php echo $rowApproved["tradeDate"] ?></td>
								<td><?php echo ($rowApproved["tradeUsername"] . " / " . $rowApproved["tradeName"]); ?></td>
								<td><?php echo $rowApproved["tradeAccNo"] ?></td>
								<td><?php echo $rowApproved["tradeAccPasswd"]?></td>
								<td><?php echo $rowApproved["tradeServer"]?></td>
								<td><?php echo $rowApproved["vpsHost"] . "<br>" . $rowApproved["vid"]?></td>
								<td><?php echo $rowApproved["pacName"] ?></td>
								<td><?php echo $rowApproved["stDesc"] ?></td>
								<td><a href="#reset" id="<?php echo ($rowApproved["tradeID"]); ?>" name="<?php echo ($rowApproved["tradeUsername"]); ?>" title="<?php echo ($rowApproved["tradeAccNo"]); ?>">Reset / Change Pass</a></td>
							</tr>
						<?php 
						} 
						?>
						</tbody>
					</table>
				</div>
				<!-- pagination -->
				<div class="row text-right">
					<ul class="pagination">
					<?php 
					$prev = $next = ""; 
					if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
					if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
					?>
						<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&txtSearch=' . $txtSearch . '&pageActive=' . $pagePrev); ?>">Previous</a></li>
						<?php 
						for ($i=1; $i<=$numPages; $i++){
							$active = "";
							if ($i == $pageActive) $active = "active";
							echo "<li class=' . $active . '><a href='./?menu=".$menu."&subMenu=active&txtSearch=" . $txtSearch . "&pageActive=$i'>$i</a></li>";
						}
						?>
						<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&txtSearch=' . $txtSearch . '&pageActive=' . $pageNext); ?>">Next</a></li>
					</ul>&nbsp;&nbsp;&nbsp;&nbsp;
				</div>  
			</div>
			<!-- Trade Off -->
			<div id="tradeoff" class="tab-pane fade in <?php echo (setActive($subMenu, "tradeoff")); ?>" >
				<h3>Trade Off (Conn, Autotrade, All History, Investor, Auth)</h3>
				<?php 
				$txtSearch = isset($_GET['txtSearch'])?$_GET['txtSearch']: '';
				$sqlWhere = "";
				if ($txtSearch != ""){
					$sqlWhere = " AND ( tradeUsername like '%".$txtSearch."%' OR tradeAccNo like '%".$txtSearch."%' OR tradeName like '%".$txtSearch. "%' OR tradeServer like '%".$txtSearch. "%' OR vpshost like '%" . $txtSearch . "%' ) ";
				} 
				?>
				<form action="./" method="GET">
					<input type="hidden" name="menu" value="tradeAcc">
					<input type="hidden" name="subMenu" value="tradeoff">
					<input type="text" name="txtSearch" value="<?php echo ($txtSearch); ?>"><button type="submit">Search</button>
				</form>
				<?php 
				$sql = " Select COUNT(*) totalRec FROM dtStateEA sea ";
				$sql .= " INNER JOIN dtTradingAcc ON seaEA=tradeEANum AND seaPair=tradePair AND seaAcc = tradeAccNo ";
				$sql .= " INNER JOIN dtVPS ON vpsID=tradeVPS ";
				$sql .= " INNER JOIN msPair ON pairID = seaPair ";
				$sql .= " INNER JOIN dtMember ON mbrUsername = tradeUsername ";
				$sql .= " WHERE (seaConn = '0' OR seaAutoTrade = '0' OR seaAllHistory = '0' OR seaInvestor = '1' OR seaAuth = '0' OR DATE_ADD(seaUpdateDate, INTERVAL 1 DAY) < CURRENT_DATE() ) ";
				$sql .= " AND tradeStID ='".$DEF_STATUS_ACTIVE."' AND mbrStID ='".$DEF_STATUS_ACTIVE."'";
				$sql  .= $sqlWhere;
				//echo $sql;
				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages = ceil ($totalRec / $numPerPage); 
				$pageActive = ($pageActive<1)?1:$pageActive;        
				$startRec = ($pageActive-1) * $numPerPage;

				$sql = " SELECT sea.*, EAName, pairName, vpshost, mbrUsername, tradeAccPasswd, ";
				$sql .= " IF(DATE_ADD(seaUpdateDate, INTERVAL 1 DAY) < CURRENT_TIME(), 'EA OFF', 'EA ON') AS isEAWork, DATEDIFF(CURRENT_TIME, seaUpdateDate) durationOff, NOW() curr FROM dtStateEA sea ";
				$sql .= " INNER JOIN dtTradingAcc ON seaEA=tradeEANum AND seaPair=tradePair AND seaAcc = tradeAccNo ";
				$sql .= " INNER JOIN msEA ON EAID=seaEA ";
				$sql .= " INNER JOIN dtVPS ON vpsID=tradeVPS ";
				$sql .= " INNER JOIN msPair ON pairID = seaPair ";
				$sql .= " INNER JOIN dtMember ON mbrUsername = tradeUsername ";
				$sql .= " WHERE (seaConn = '0' OR seaAutoTrade = '0' OR seaAllHistory = '0' OR seaInvestor = '1' OR seaAuth = '0' OR DATE_ADD(seaUpdateDate, INTERVAL 1 DAY) < CURRENT_TIME() ) ";
				$sql .= " AND tradeStID ='".$DEF_STATUS_ACTIVE."' AND mbrStID ='".$DEF_STATUS_ACTIVE."'";
				$sql  .= $sqlWhere;
				//$sql  .= " ORDER BY mbrDate DESC";
				$sql .= " ORDER BY durationOff ASC";
				$sql  .= " LIMIT " . $startRec . ", " . $numPerPage;
				//fPrint ($sql);
				$queryApproved = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped small">
						<thead>
							<tr>
								<th>EA / Pair</th>
								<th>Username / Acc</th>
								<th>VPS / Code</th>
								<th>Conn(1)</th>
								<th>Auto(1)</th>
								<th>All Hist(1)</th>
								<th>Investor(0)</th>
								<th>Auth(1)</th>
								<th>durationOff(0)</th>
								<th>LastUpdate / Curr</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>EA / Pair</th>
								<th>Username / Acc</th>
								<th>VPS</th>
								<th>Conn</th>
								<th>Auto</th>
								<th>All Hist</th>
								<th>Investor</th>
								<th>Auth</th>
								<th>DurationOff</th>
								<th>LastUpdate / Curr</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($queryApproved->num_rows == 0){
								echo "<tr><td colspan=11 class='text-center text-primary'>no record</td></tr>";  
						}
						while ($rowApproved = $queryApproved->fetch_assoc()){
						?>
							<tr>
								<td><?php echo ($rowApproved["EAName"] . "<br>" . $rowApproved["pairName"]); ?></td>
								<td><?php echo ($rowApproved["mbrUsername"] . "<br>" . $rowApproved["seaAcc"]); ?></td>
								<td><?php echo ($rowApproved["vpshost"]. "<br>" . $rowApproved["tradeAccPasswd"]); ?></td>
								<td><?php echo ($rowApproved["seaConn"]); ?></td>
								<td><?php echo ($rowApproved["seaAutoTrade"]); ?></td>
								<td><?php echo ($rowApproved["seaAllHistory"]); ?></td>
								<td><?php echo ($rowApproved["seaInvestor"]); ?></td>
								<td><?php echo ($rowApproved["seaAuth"]); ?></td>
								<td><?php echo ($rowApproved["durationOff"]); ?></td>
								<td><?php echo ($rowApproved["seaUpdateDate"] . "<br>" . $rowApproved["curr"]); ?></td>
							</tr>
						<?php 
						} 
						?>
						</tbody>
					</table>
				</div>
				<!-- pagination -->
				<div class="row text-right">
					<ul class="pagination">
					<?php 
					$prev = $next = ""; 
					if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
					if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
					?>
						<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=tradeoff&txtSearch=' . $txtSearch . '&pageActive=' . $pagePrev); ?>">Previous</a></li>
						<?php 
						for ($i=1; $i<=$numPages; $i++){
							$active = "";
							if ($i == $pageActive) $active = "active";
							echo "<li class=' . $active . '><a href='./?menu=".$menu."&subMenu=tradeoff&txtSearch=" . $txtSearch . "&pageActive=$i'>$i</a></li>";
						}
						?>
						<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=tradeoff&txtSearch=' . $txtSearch . '&pageActive=' . $pageNext); ?>">Next</a></li>
					</ul>&nbsp;&nbsp;&nbsp;&nbsp;
				</div>  
			</div>
			<!-- Request Reset -->
			<div id="reqreset" class="tab-pane fade in <?php echo (setActive($subMenu, "reqreset")); ?>" >
			</div>
		</div>			
	</div>
</div>

<!-- the modals modalTradeAcc-->
<div class="modal-2" id="modalTradeAcc">
	<div class="modal-content-2">
		<form class="animate" action="" method="post" onSubmit="return false;">
			<span id="tradeID" title=""></span>
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
							<input type="text" name="accUsername" id="accUsername" value="" class="form-control" title="" readonly required>
						</div>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4 text-left text-vCenter">Trader Name</label>
					<div class="col-md-8">
						<div class="form-group">
							<input type="text" name="tradeName" id="tradeName" value="" class="form-control" title="" readonly required>
						</div>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4 text-left text-vCenter">Trade Acc</label>
					<div class="col-md-8">
						<div class="form-group">
							<input type="text" name="tradeAcc" id="tradeAcc" value="" class="form-control" title="" readonly required>
						</div>
					</div>
				</div>
				<div class="row">
					<label class="col-md-4 text-left text-vCenter">EA Code</label>
					<div class="col-md-8">
						<div class="form-group">
							<input type="text" name="tradeEANum" id="tradeEANum" value="" class="form-control" title="" readonly required>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4"></div>
					<div class="col-md-4">
						<button type="submit" name="submit" id="submit" class="btn btn-block col-md-12 btn-primary">Activate</button>
					</div>
					<div class="col-md-4">
						<button type="button" name="btnDeclined" id="btnDeclined" class="btn btn-block col-md-12 btn-danger" title="Reset Acc Trading & Send Email to Member">Decline</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- The Modals modalTradeReset-->
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
