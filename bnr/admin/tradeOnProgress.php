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
<meta http-equiv="refresh" content="600">
<script>
	$(document).ready(function(e) {
		$("#q").html("");

		$('a[href="#updateCnf"]').on('click', function(){
			//alert ($(this).attr('name'));
			$id = $(this).attr('name');
			//$tradeVPS       = $(this).attr('data-value');
			
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
					$('#tradeVPS').val($myDataObj["tradeVPS"]);
					//$('#tradeVPS').val($tradeVPS);
					$("#tradeID").attr('title', $id);
					var modalTradeAcc = document.getElementById('modalTradeAcc');
					modalTradeAcc.style.display='block';
				}	
			});
		});

		//Click Confirmation button
		$("#submit").on('click', function(){
			$("#submit").attr("disabled", true);
	      	var html = $("#submit").html();
	    	$("#submit").html(html + '&nbsp; <i class="fa fa-spinner fa-spin"  style="font-size:24px"></i>');
			$tradeID      = $("#tradeID").attr('title');
			$tradeEANum   = $("#tradeEANum").val();
			$tradeAcc     = $("#tradeAcc").val();
			$tradeVPS     = $("#tradeVPS").val();
			if ($tradeVPS == 0){
				$("#alertMsg").attr('style', 'display:block');
				$("#msg").html("Vps belum diset !");
			}else{
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
						/*
						console.log(data);
						$("#submit").attr("disabled", false);
						$("#submit").html("Activate");
						return false;
						*/
						$myDataObj  = JSON.parse(data);
						if ($.trim($myDataObj["status"]) == "success"){
							//location.href = "incomingDeposit.php?q=" + $myDataObj["message"]; 
							location.href = "./?menu=tradeAcc&subMenu=onprogress&q=" + $myDataObj["message"]; 
							//$("#finID").attr('title', ""); //reset finID
						}else if ($.trim($myDataObj["status"]) == "error"){
							//alert ($myDataObj["errDesc"]);
							//demo.showNotification('top','center', 'info', $myDataObj["message"]);
							//alert ($myDataObj["message"]);
							$("#submit").attr("disabled", false);
							$("#submit").html("Activate");
							$("#alertMsg").attr('style', 'display:block');
							$("#msg").html($myDataObj["message"]);
							$(document).load();
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

		$("#btnDeclined").on('click', function(e) {
			$("#btnDeclined").attr("disabled", true);
	      	var html = $("#btnDeclined").html();
	    	$("#btnDeclined").html(html + '&nbsp; <i class="fa fa-spinner fa-spin"  style="font-size:24px"></i>');
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
						/*
						console.log(data);
						$("#btnDeclined").attr("disabled", false);
						$("#btnDeclined").html("Decline");
						return false;
						*/
						$myDataObj  = JSON.parse(data);		
						if ($.trim($myDataObj["status"]) == "success"){
							//location.href = "incomingDeposit.php?q=" + $myDataObj["message"]; 
							location.href = "./?menu=tradeAcc&subMenu=onprogress&q=" + $myDataObj["message"]; 
							//$("#finID").attr('title', ""); //reset finID
						}else if ($.trim($myDataObj["status"]) == "error"){
							//alert ($myDataObj["errDesc"]);
							//demo.showNotification('top','center', 'info', $myDataObj["message"]);
							//alert ($myDataObj["message"]);
							$("#btnDeclined").attr("disabled", false);
							$("#btnDeclined").html("Decline");
							$("#alertMsg").attr('style', 'display:block');
							$("#msg").html($myDataObj["message"]);
							$(document).load();
						}
					});
				}else {
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

		if (event.target.id == "modalReqReset")
		$("#modalReqReset").hide();
	}
</script>
<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Trading Account</div>
		<span id="q" title="" class="text-center text-success"><b><?php echo $q; ?></b></span>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "onprogress")); ?>"><a data-toggle="tab" href="#onprogress">On Progress</a></li>
		</ul>
		<div class="tab-content">
			<!-- On Progress -->
			<div id="onprogress" class="tab-pane fade in <?php echo (setActive($subMenu, "onprogress")); ?>">
				<?php  
				$sql  = " Select tradeUsername, tradeName, tradeAccNo, tradeAccPasswd, tradeServer, tradeEANum, tradeDate"; 
				$sql .= " , EAName, s.stDesc, trPacID, trDate, pacName, mbrFirstName, mbrEmail, tradeID, tradevps, IFNULL(vpshost, '-') vpshost";
				$sql .= " , IFNULL(vid, '-') vid, pairName, IFNULL(3vipusername1, '-') 3vipusername1, affStatus";
				$sql .= " FROM dtTradingAcc acc";
				$sql .= " INNER JOIN msStatus s ON stID = tradeStID";
				$sql .= " INNER JOIN msPair ON tradePair=pairID";
				$sql .= " INNER JOIN msEA ON tradeEANum=EAID";
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
				$sql .= " LEFT JOIN dtTripleVIP ON 3vipusername1=acc.tradeUsername OR 3vipusername2=acc.tradeUsername OR 3vipusername2=acc.tradeUsername";
				$sql .= " LEFT JOIN dtTradingAccAff ON affUsername = tradeUsername AND affAccNo = tradeAccNo";
				$sql  .= " WHERE tradeStID ='".$DEF_STATUS_ONPROGRESS."'";
				$sql  .= " ORDER BY tradeDate DESC";
				$result = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped small" id="tOnprogress">
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
						if ($result->num_rows == 0){
						?>
							<tr>
								<td colspan=10  class='text-center text-primary'>no record</td>
								<td style='display:none'></td>
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
						
						while ($row = $result->fetch_assoc()){
							if ($row['3vipusername1'] != "-" ){
								$pacName = "Triple VIP";
							}else{
								$pacName = $row["pacName"];
							}
							if ($row['tradeEANum'] == "78"){
								$txtEA = "<span class='text-danger' style='font-weight:bold;'>".$row['EAName']."</span>";
							}else if ($row['tradeEANum']=="79"){
								$txtEA = "<span class='text-success' style='font-weight:bold;'>".$row['EAName']."</span>";
							}
						?>
							<tr>
								<td><?php echo ($row["tradeUsername"] . "<br>" . $row["trDate"] ); ?></td>
								<td><?php echo $row["tradeName"] ?></td>
								<td>
								<?php
									echo ($row['affStatus'] != $DEF_STATUS_APPROVED && $row['tradeEANum'] == $DEF_EACODE_CHRONOS)?"<del>".$row["tradeAccNo"]."</del>":$row["tradeAccNo"];
									echo "<br>".$txtEA; 
								?>
								</td>
								<td><?php echo $row["tradeAccPasswd"] ?></td>
								<td><?php echo $row["tradeServer"]?></td>
								<td><?php echo ($pacName . "<br>". $row["pairName"]); ?></td>
								<td><?php echo $row["vpshost"] . "<br>" . $row["vid"] ?></td>
								<td><?php echo $row["mbrFirstName"] . "<br>" . $row["mbrEmail"] ?></td>
								<td><?php echo ($row["stDesc"] . "<br>". $row["tradeDate"]); ?></td>
								<td class="text-right">
									<a href="#updateCnf" name="<?php echo $row["tradeID"] ?>" data-value="<?php echo ($row["tradevps"]); ?>" title="Update Confirmation" ><i class="fa fa-edit fa-2x"></i></a>
								</td>
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

<!-- The modals modalTradeAcc-->
<div class="modal-2" id="modalTradeAcc">
	<div class="modal-content-2">
		<form class="animate" action="" method="post" onSubmit="return false;">
			<span id="tradeID" title=""></span>
			<input type="hidden" id="tradeVPS" value="">
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