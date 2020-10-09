<?php
$q = (isset($_GET["q"]))?$_GET["q"]: "";
$page = (isset($_GET['page']))? $_GET['page'] : 1;
$pageActive = (isset($_GET['pageActive']))? $_GET['pageActive'] : 1;
$numPerPage = $DEF_NUM_PER_PAGE;


//$menu = (isset($_GET['menu']))? $_GET['menu'] : "";
$subMenu = (isset($_GET['subMenu']))? $_GET['subMenu'] : "pending";

?>
<script>
$(document).ready(function(e) {
	$('a[href="#actMyMac"]').on('click', function(e){
		var logmUsername = $(this).attr("data-user");
		var logmID = $(this).attr("data-id");
		Swal.fire({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, Activate!',
			cancelButtonText: 'Close',
			showLoaderOnConfirm: true,
			allowOutsideClick: false,
			preConfirm: function() {
				return new Promise(function(resolve) {
	               	$.post("json.php", 
					{
						"q"        		: "ActivateMyMac",
						"logmUsername" 	: logmUsername,
						"logmID" 		: logmID			
					},
					function (data, success){
						// console.log(data);
						$myDataObj  = JSON.parse(data);
						if ($.trim($myDataObj["status"]) == "success"){
							// location.href = "./?menu=tradeAcc&subMenu=reqreset&q=" + $myDataObj["message"];
							Swal.fire({
							  	title: 'Activated!',
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
	            })
			}
		});
	});

	$(".benfileid").ezPlus({
	    zoomWindowPosition: 11
	});
	//Reset Trading Account______________
	$('a[href="#remove"]').on('click', function(){
		//$tradeID        = $(this).attr('id');
		$tempUsername  = $(this).attr('name');
		//alert ($tempUsername); return false;
		//$('#reset_tradeID').attr('title', $tradeID);
		//$('#tempUsername').val($tempUsername);

		if ($tempUsername != ""){
			$.post("json.php", 
				{
					"q"             : "RemoveTempJoin",
					"id"            : "",
					"tempUsername"  : $tempUsername,
				}, 
				function(data, success){
					$myDataObj  = JSON.parse(data);
					if ($.trim($myDataObj["status"]) == "success"){
						//location.href = "incomingDeposit.php?q=" + $myDataObj["message"]; 
						//alert($myDataObj["message"]);
						location.href = "./?menu=member&subMenu=pending&q=" + $myDataObj["message"]; 
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
		}else {
			//demo.showNotification('top','center', 'info', "Please, fill all data before submit!");
			alert ("Invalid username");
			//$("#alertMsg").attr('style', 'display:block');
			//$("#msg").html("<b>Please, fill all data before submit!</b>");
			//$(document).load();
		}
	});		
	
	//Resend activate email (register)______________
	$('a[href="#resend"]').on('click', function(){
		//$tradeID        = $(this).attr('id');
		$tempUsername  = $(this).attr('data-value');
		//alert ($tempUsername); die();
		//$('#reset_tradeID').attr('title', $tradeID);
		//$('#tempUsername').val($tempUsername);
		if ($tempUsername != ""){
			$.post("json.php", 
				{
					"q"             : "REGISTER_SUCCESS",
					"id"            : $tempUsername
				}, 
				function(data, success){
					$myDataObj  = JSON.parse(data);
					if ($.trim($myDataObj["status"]) == "success"){
						//location.href = "incomingDeposit.php?q=" + $myDataObj["message"]; 
						//alert($myDataObj["message"]);
						//location.href = "./?menu=member&subMenu=pending&q=" + $myDataObj["message"]; 
						//$("#finID").attr('title', ""); //reset finID
						alert ("Email Resend");
					}else if ($.trim($myDataObj["status"]) == "error"){
						//alert ($myDataObj["errDesc"]);
						//demo.showNotification('top','center', 'info', $myDataObj["message"]);
						alert ($myDataObj["message"]);

						//$("#alertMsg").attr('style', 'display:block');
						//$("#msg").html($myDataObj["message"]);
						//$(document).load();
					}
				});
		}else {
			//demo.showNotification('top','center', 'info', "Please, fill all data before submit!");
			alert ("Invalid username");
			//$("#alertMsg").attr('style', 'display:block');
			//$("#msg").html("<b>Please, fill all data before submit!</b>");
			//$(document).load();
		}
	});

	$q = $("#q").attr('title');
	//if ($q == "confirmed"){
		////demo.showNotification('top','center', 'success', "Confirmation Successfully");
		//alert ("Confirmation Successfully");
	//}
	if ($q != "") alert ($q);

	var vrusername
	var vridtype
	var vridnum
	var fullname
	var vrbod
	$('a[href="#approveID"]').on('click', function(){
		$("#modalApproveID .row :input").each(function(){
			$(this).val("");
		});
		var vrfilename 	= $(this).attr("vrfilename");
		var vrfirstname = $(this).attr("vrfirstname");
		var vrlastname 	= $(this).attr("vrlastname");
		vrusername 		= $(this).attr("vrusername");
		vridtype 		= $(this).attr("idtype");
		vridnum 	= $(this).attr("vridnum");
		if (vrlastname != ""){
			fullname 	= (vrfirstname+" "+vrlastname).toLowerCase();	
		}else{
			fullname 	= (vrfirstname).toLowerCase();
		}
		vrbod 		= $(this).attr("vrbod");
		// alert (vridnum+" || "+fullname+" || "+vrbod);
		// $(".zoomContainer").hide();
		$("#uploadedID").attr("src", "../member/photo_verify/"+vrfilename);

		// data-zoom-image di set oleh modal onclose pada event.target.id == "modalApproveID"
		// $("#uploadedID").attr("data-zoom-image", "../member/photo_verify/"+vrfilename);
		
		$("#uploadedID").ezPlus({
			zoomWindowWidth: 500,
			zoomWindowHeight: 270
		});
		
		$("#modalApproveID").css("display","block");
	});
	$("#modalApproveID form").on('submit', function(){
		$("#btnCnfApprove").attr("disabled", true);
      	var html = $("#btnCnfApprove").html();
    	$("#btnCnfApprove").html(html + '&nbsp; <i class="fa fa-spinner fa-spin"  style="font-size:24px"></i>');
    	var status = $('#modalApproveID input[name="status"]').val();
    	var idType 		= $("#idType :selected").val();
		var idNumber 	= $("#idNumber").val().split(" ").join("");
		var idFirstName	= $("#idFirstName").val();
		var idLastName	= $("#idLastName").val();
		var idFullname;
		if (idLastName != ""){
			idFullname 	= (idFirstName+" "+idLastName).toLowerCase();	
		}else{
			idFullname 	= (idFirstName).toLowerCase();
		}
		// alert (idFirstName+" "+idLastName+" || "+ idFullname); return false;
		var idBOD 		= $("#idBOD").val();
		var isValid = true;
		var errMsg 	= "";
		if (vridnum != idNumber){
			isValid = false;
			errMsg += "ID Number Not Valid<br>";
		}
		if (fullname != idFullname){
			isValid = false;
			errMsg += "Full Name Not Valid<br>";
		}
		if (vrbod != idBOD){
			isValid = false;
			errMsg += "Birth of Date Not Valid";
		}
		if (vridtype != idType){
			isValid = false;
			errMsg += "ID Type Not Valid";
		}
		if (isValid == true){
			$.post("json.php",
			{
				"q" 			: "VERIFY_ID",
				"id" 			: vrusername,
				"vrtype" 		: idType,
				"vridnum" 		: idNumber,
				"vrfirstname" 	: idFirstName,
				"vrlastname" 	: idLastName,
				"vrbod"			: idBOD,
				"status" 		: status
			},
			function (data, success){
				// alert (data);
				$myDataObj  = JSON.parse(data);
				if ($.trim($myDataObj["status"]) == "success"){
					location.href = "./?menu=member&subMenu=verifyID&q="+ $myDataObj["message"];
	              	$(document).load();
				}else if ($.trim($myDataObj["status"]) == "error"){
					$("#msg").html($myDataObj["message"]);
					$("#alertMsg").css("display","block");
					$("#btnCnfApprove").attr("disabled", false);
					$("#btnCnfApprove").html("Confirm Approve");
					return false;
				}else{
					$("#msg").html(data);
					$("#alertMsg").css("display","block");
					$("#btnCnfApprove").attr("disabled", false);
					$("#btnCnfApprove").html("Confirm Approve");
					return false;
				}
			});
		}else{
			$("#msg").html(errMsg);
			$("#alertMsg").css("display","block");
			$("#btnCnfApprove").attr("disabled", false);
			$("#btnCnfApprove").html("Confirm Approve");
			return false;
		}
	});

	$('input[name="custom"]').change(function(){
	    if((this.checked) == true ) { //use custommsg / othermsg
	        $('input[name="common"]').attr("disabled", true);
	        $('input[name="common"]').prop("checked", false);
	        $("#othermsg").show();
	    }else{ //use common msg
	    	$('input[name="common"]').attr("disabled", false);
	    	$('input[name="common"]').prop("checked", false);
	    	$("#othermsg").hide();
	    }
	});

	$('a[href="#declineID"]').on('click', function(){
		vrusername 		= $(this).attr("vrusername");
		$("#modalDeclineID .row :input").prop("checked", false);
		$(".zoomContainer").hide();
		$("#modalDeclineID").css("display","block");
		$("#othermsg").css("display","none");
	});

	$("#modalDeclineID form").on('submit', function(){
		$("#btnCnfDecline").attr("disabled", true);
      	var html = $("#btnCnfDecline").html();
    	$("#btnCnfDecline").html(html + '&nbsp; <i class="fa fa-spinner fa-spin"  style="font-size:24px"></i>');
		var commonmsg = [];
		$.each($('input[name="common"]:checked'),function(e){
			commonmsg.push($(this).val());
		});
		var custom = $('input[name="custom"]').prop("checked");
		var status = $('#modalDeclineID input[name="status"]').val();
		// alert (commonmsg + " || "+ custom+" || "+status); return false;
		if (commonmsg == "" && custom == false){ //belum memilih
			$("#modalDeclineID #msg").html("Chose at least one");
			$("#modalDeclineID #alertMsg").css("display","block");
			$("#btnCnfDecline").attr("disabled", false);
			$("#btnCnfDecline").html("Confirm Decline");
		}else if (commonmsg != "" && custom == false){ // use common msg
			$.post("json.php", 
			{
				"q"  		: "VERIFY_ID",
				"id" 		: vrusername,
				"typemsg" 	: "common",
				"dcmsg" 	: commonmsg,
				"status" 	: status
			},
			function (data, success){
				// alert(data);
				$myDataObj = JSON.parse(data);
				if ($.trim($myDataObj["status"]) == "success"){
					location.href = "./?menu=member&subMenu=verifyID&q="+ $myDataObj["message"];
	              	$(document).load();
					
				}else if ($.trim($myDataObj["status"]) == "error"){
					$("#modalDeclineID #msg").html($myDataObj["message"]);
					$("#modalDeclineID #alertMsg").css("display","block");
					$("#btnCnfDecline").attr("disabled", false);
					$("#btnCnfDecline").html("Confirm Decline");
				}else{
					$("#modalDeclineID #msg").html(data);
					$("#modalDeclineID #alertMsg").css("display","block");
					$("#btnCnfDecline").attr("disabled", false);
					$("#btnCnfDecline").html("Confirm Decline");
				}
			});
		}else if (commonmsg == "" && custom == true){ //use custom msg
			var othermsg = $("#othermsg").val();
			$.post("json.php", 
			{
				"q"  		: "VERIFY_ID",
				"id" 		: vrusername,
				"typemsg" 	: "custom",
				"dcmsg" 	: othermsg,
				"status" 	: status
			},
			function (data, success){
				// alert(data);
				$myDataObj = JSON.parse(data);
				if ($.trim($myDataObj["status"]) == "success"){
					location.href = "./?menu=member&subMenu=verifyID&q="+ $myDataObj["message"];
	              	$(document).load();
					
				}else if ($.trim($myDataObj["status"]) == "error"){
					$("#modalDeclineID #msg").html($myDataObj["message"]);
					$("#modalDeclineID #alertMsg").css("display","block");
					$("#btnCnfDecline").attr("disabled", false);
					$("#btnCnfDecline").html("Confirm Decline");
				}else{
					$("#modalDeclineID #msg").html(data);
					$("#modalDeclineID #alertMsg").css("display","block");
					$("#btnCnfDecline").attr("disabled", false);
					$("#btnCnfDecline").html("Confirm Decline");
				}
			});
		}
	});
});
</script>
<script>
	// Get the modal
	//var modal = document.getElementById('modalTradeAcc');

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target.id == "modalApproveID"){
			document.getElementById('modalApproveID').style.display='none';
			$("#uploadedID-zoomContainer .zoomWindowContainer .zoomWindow").css("background-image", ""); // untuk ganti image zoom window pada modalApproveID
			$("#uploadedID-zoomContainer").remove();
    		//alert (event.target.id);
		}else if (event.target.id == "modalDeclineID"){
			document.getElementById('modalDeclineID').style.display='none';
			// $(".zoomContainer").show();
    		//alert (event.target.id);
		}

  	}

  
</script>
<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Member</div>
		<span id="q" title="" class="text-center text-success"><b><?php echo $q; ?></b></span>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "pending")); ?>"><a data-toggle="tab" href="#pending">Registered / Pending</a></li>
			<li class="<?php echo (setActive($subMenu, "active")); ?>"><a data-toggle="tab" href="#active">Active</a></li>
			<li class="<?php echo (setActive($subMenu, "upgrade")); ?>"><a data-toggle="tab" href="#upgrade">Upgrade Package</a></li>
			<li class="<?php echo (setActive($subMenu, "verifyID")); ?>"><a data-toggle="tab" href="#verifyID">Verify ID</a></li>
			<li class="<?php echo (setActive($subMenu, "beneficiary")); ?>"><a data-toggle="tab" href="#beneficiary">Beneficiary</a></li>
			<li class="<?php echo (setActive($subMenu, "mymac")); ?>"><a data-toggle="tab" href="#mymac">My Mac</a></li>
		</ul>
		<div class="tab-content">
			<div id="pending" class="tab-pane fade in <?php echo (setActive($subMenu, "pending")); ?>">
				<h3>Registered / Pending</h3>
				<!--- Pending -->
				<?php 			

				$sql  = "SELECT * FROM dtTempJoin INNER JOIN msStatus ON tjStID=stID ";
				$sql .= " INNER JOIN dtMember AS sp ON tjSponsor = sp.mbrUsername";
				$sql .= " INNER JOIN msPackage ON pacID=tjPackage ";
				$sql .= " INNER JOIN msCountry ON countryID = tjCountry";
				$sql .= " WHERE (tjStID='" . $DEF_STATUS_PENDING . "' OR tjStID='" . $DEF_STATUS_ACTIVE . "')";
				$sql .= " ORDER BY tjDate DESC, mbrUsername ASC";
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
					<table class="table table-hover table-striped small" id="tPending">
						<thead>
							<tr>
								<th>Username / Name</th>
								<th>Email / Mobile</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Package</th>
								<th>Date</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Username / Name</th>
								<th>Email / Mobile</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Package</th>
								<th>Date</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($result->num_rows == 0){
								echo "<tr><td colspan=8 class='text-center text-primary'>no record</td></tr>";	
						}
						while ($rowPending = $result->fetch_assoc()){
						?>
							<tr>
								<td><?php echo ($rowPending["tjUsername"] . "<br>" . $rowPending["tjFirstName"]) ?></td>
								<td><?php echo ($rowPending["tjEmail"] . "<br>" . "+".$rowPending["tjMobileCode"] . "-". $rowPending["tjMobile"]); ?></td>
								<td><?php echo $rowPending["countryDesc"] ?></td>
								<td><?php echo $rowPending["tjSponsor"] . "<br>" . $rowPending['mbrEmail'] ?></td>
								<td><?php echo $rowPending["pacName"] ?></td>
								<td><?php echo $rowPending["tjDate"] ?></td>
								<td><?php echo ($rowPending["stDesc"]); ?></td>
								<td>
								<?php 
								echo ("<a href='#remove' name='" . $rowPending["tjUsername"] ."'>Remove</a>");
								if ($rowPending['tjStID'] == '1')
									echo ("<br><a href='#resend' style='color:blue' data-value='" . $rowPending["tjUsername"] ."'>Resend</a>");
								?>
								</td>
							</tr>
						<?php } ?>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pending&page='.$pagePrev.''); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=pending&page=$i'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=pending&page=$i'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&page=$numPages'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=pending&page=1'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=pending&page=$i'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&page=$numPages'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=pending&page=1'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=pending&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=pending&page=$i'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pending&page='.$pageNext); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pending&page='.$numPages); ?>">Last &rsaquo;&rsaquo;</a></li>
						</ul>
					</div> 
					<?php 
					}
					?>
				</div>
			</div>
			<div id="active" class="tab-pane fade in <?php echo (setActive($subMenu, "active")); ?>" >
				<h3>Active</h3>
				<?php $activeSearch = isset($_GET['txtSearch'])?$_GET['txtSearch']: ''; ?>
				<form action="./" method="GET">
					<input type="hidden" name="menu" value="member">
					<input type="hidden" name="subMenu" value="active">
					<input type="text" name="txtSearch" value="<?php echo ($activeSearch); ?>"><button type="submit">Search</button>
				</form>
				<!-- Active -->
				<?php 
				$activeSearch = isset($_GET['txtSearch'])?$_GET['txtSearch']: '';
				$sqlWhere = "";
				if ($activeSearch != ""){
					$sqlWhere = " AND ( m.mbrUsername like '%".$activeSearch."%' OR m.mbrFirstName like '%".$activeSearch."%' OR m.mbrEmail like '%".$activeSearch."%' ";
					$sqlWhere .= " OR m.mbrSponsor like '%".$activeSearch."%'  OR m.mbrUpline like '%".$activeSearch."%' OR pacName like '%".$activeSearch."%' ) ";
				}

				$sql  = "SELECT m.*, CONCAT(m.mbrFirstName, ' ', m.mbrLastName) AS mbrFullName, p.*, s.*, c.*, sp.mbrEmail AS sponsorEmail, trDate, trThn, IFNULL(ptDesc, '-') AS ptDesc, IFNULL(pay.payAcc, '-') AS payAcc, IFNULL(pay.payAccName, '-') AS payAccName ";
				$sql .= "FROM dtMember AS m"; 
				$sql .= " INNER JOIN Transaction AS t on trUsername=m.mbrUsername ";
				$sql .= " INNER JOIN dtMember AS sp ON m.mbrSponsor = sp.mbrUsername";
				$sql .= " INNER JOIN msCountry AS c ON c.countryID = m.mbrCountry";
				$sql .= " INNER JOIN msStatus AS s ON t.trStatus=s.stID";
				$sql .= " INNER JOIN msPackage AS p ON p.pacID=t.trPacID ";
				$sql .= " LEFT JOIN ( ";
				$sql .= " 	SELECT payMbrUsername, ptDesc, payPTID, payAcc, payAccName ";
				$sql .= " 	FROM dtPaymentAcc AS p";
				$sql .= " 	INNER JOIN msPaymentType ON ptID = payPTID";
				$sql .= " 	WHERE payStatus ='".$DEF_STATUS_ACTIVE."' AND ptCat = '".$DEF_CATEGORY_BANK."' ";
				$sql .= " 	AND payMbrUsername = (SELECT payMbrUsername FROM dtPaymentAcc WHERE payMbrUsername = p.payMbrUsername AND payPTID = p.payPTID ORDER BY payDate DESC LIMIT 1)";
				$sql .= " ) AS pay ON pay.payMbrUsername = m.mbrUsername";
				$sql .= " WHERE (m.mbrStID='" . $DEF_STATUS_ACTIVE . "' OR m.mbrStID = '".$DEF_STATUS_BLOCKED."')";
				$sql .= $sqlWhere;
				$sql .= " ORDER BY trDate DESC";
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
					<table class="table table-hover table-striped small">
						<thead>
							<tr>
								<th>Username</th>
								<th>Email / Mobile</th>
								<th>Bank Account</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Upline</th>
								<th>Package</th>
								<th>Join / Active Date</th>
								<th>Status</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Username</th>
								<th>Name</th>
								<th>Email / Mobile</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Upline</th>
								<th>Package</th>
								<th>Join / Active Date</th>
								<th>Status</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($result->num_rows == 0){
								echo "<tr><td colspan=9 class='text-center text-primary'>no record</td></tr>";	
						}
						while ($rowApproved = $result->fetch_assoc()){
							$status = $rowApproved["stDesc"];
							if ($rowApproved["trThn"] > 1) $status = "RENEW";
							$mbrStatus = ($rowApproved['mbrStID'] == $DEF_STATUS_ACTIVE)?"<span style='font-size:16px;color:green;'>Active</span>":($rowApproved['mbrStID'] == $DEF_STATUS_BLOCKED?"<span class='text-danger' style='font-size:16px;'>Blocked<span>":"-");
						?>
							<tr>
								<td><?php echo $rowApproved["mbrUsername"]."<br>".$rowApproved['mbrFullName']."<br>".$mbrStatus; ?></td>
								<td><?php echo ($rowApproved["mbrEmail"] . "<br>" . "+".$rowApproved["mbrMobileCode"] . "-". $rowApproved["mbrMobile"]); ?></td>
								<td><?php echo $rowApproved['ptDesc']."<br>".$rowApproved['payAcc']."<br>".$rowApproved['payAccName']; ?></td>
								<td><?php echo $rowApproved["countryDesc"] ?></td>
								<td><?php echo $rowApproved["mbrSponsor"] . "<br>" . $rowApproved['sponsorEmail'] ?></td>
								<td><?php echo $rowApproved["mbrUpline"] ?></td>
								<td><?php echo $rowApproved["pacName"] ?></td>
								<td><?php echo ($rowApproved["mbrDate"] . "<br>" . $rowApproved["trDate"]) ?></td>
								<td><?php echo $status ?></td>
							</tr>
						<?php } ?>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&page='.$pagePrev.'&txtSearch='.$activeSearch); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=active&page=$i&txtSearch=$activeSearch'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=active&page=$i&txtSearch=$activeSearch'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=$secondLast&txtSearch=$activeSearch'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=$numPages&txtSearch=$activeSearch'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=active&page=1&txtSearch=$activeSearch'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=2&txtSearch=$activeSearch'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=active&page=$i&txtSearch=$activeSearch'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=$secondLast&txtSearch=$activeSearch'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=active&page=$numPages&txtSearch=$activeSearch'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=active&page=1&txtSearch=$activeSearch'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=active&page=2&txtSearch=$activeSearch'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=active&page=$i&txtSearch=$activeSearch'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&page='.$pageNext.'&txtSearch='.$activeSearch); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&page='.$numPages.'&txtSearch='.$activeSearch); ?>">Last &rsaquo;&rsaquo;</a></li>
						</ul>
					</div> 
					<?php 
					}
					?> 
				</div>
			</div>
			<div id="upgrade" class="tab-pane fade in <?php echo (setActive($subMenu, "upgrade")); ?>">
				<h3>Upgrade Package</h3>
				<!--- Upgrade Package -->
				<?php 

				$sql = " SELECT m.mbrFirstName, m.mbrUsername, m.mbrEmail, m.mbrMobileCode, m.mbrMobile, sp.mbrFirstName AS spName, sp.mbrUsername AS spUsername, t.*, pac1.PacName AS pac1, pac2.PacName AS pac2, countryDesc, acc.tradeAccNo, trade.vpshost ";
				$sql .= " FROM dtMember As m";
				$sql .= " INNER JOIN dtMember AS sp ON m.mbrSponsor = sp.mbrUsername";
				$sql .= " INNER JOIN msCountry ON countryID = m.mbrCountry";
				$sql .= " LEFT JOIN (SELECT * FROM dtVPS INNER JOIN dtTradingAcc ON tradeVPS = vpsid WHERE tradeStID='" . $DEF_STATUS_ACTIVE . "') AS trade ON trade.tradeUsername=m.mbrUsername ";
				$sql .= " INNER JOIN (";
				$sql .= " SELECT t.* FROM Transaction as t3 ";
				$sql .= "   INNER JOIN (";
				$sql .= "   SELECT t1.*, t2.trPacID as trPacID2, t2.trDate as trDate2, t2.trStatus as trStatus2 FROM Transaction t1";
				$sql .= "   INNER JOIN Transaction t2 ON t1.trUsername=t2.trUsername";
				$sql .= "   WHERE t2.trStatus='11' AND t1.trPacID != t2.trPacID";
				$sql .= "       ) t ON t3.trUsername = t.trUsername";
				$sql .= "       GROUP by t.trUsername, t.trPacID2";
				$sql .= "     ) AS t ON t.trUsername=m.mbrUsername";
				$sql .= " INNER JOIN msPackage AS pac1 ON pac1.pacID=t.trPacID";
				$sql .= " INNER JOIN msPackage AS pac2 ON pac2.pacID=t.trPacID2";
				$sql .= " INNER JOIN (SELECT * FROM dtTradingAcc acc WHERE tradeID=(SELECT tradeID FROM dtTradingAcc as acc2 WHERE acc2.tradeUsername=acc.tradeUsername ORDER BY tradeDate DESC LIMIT 1) ) AS acc ON acc.tradeUsername=m.mbrUsername";
				$sql .= " ORDER BY t.trDate2 DESC";
				//echo ($sql);
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
					<table class="table table-hover table-striped small">
						<thead>
							<tr>
								<th>Username</th>
								<th>Email</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Package</th>
								<th>Trade Acc / VPS</th>
								<th>Update Date</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Username</th>
								<th>Email</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Package</th>
								<th>Trade Acc / VPS</th>
								<th>Update Date</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($result->num_rows == 0){
								echo "<tr><td colspan=7 class='text-center text-primary'>no record</td></tr>";	
						}
						while ($row = $result->fetch_assoc()){
						?>
							<tr>
								<td><?php echo ($row["mbrUsername"]."<br>".$row['mbrFirstName']); ?></td>
								<td><?php echo ($row["mbrEmail"]."<br>+".$row["mbrMobileCode"] . "-". $row["mbrMobile"]); ?></td>
								<td><?php echo ($row["countryDesc"]); ?></td>
								<td><?php echo ($row["spUsername"]."<br>".$row['spName']); ?></td>
								<td><?php echo ($row["pac1"]." -> ".$row['pac2']); ?></td>
								<td><?php echo ($row["tradeAccNo"] . "<br>" . $row["vpshost"]); ?></td>
								<td><?php echo ($row["trDate2"]); ?></td>
							</tr>
						<?php } ?>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=upgrade&page='.$pagePrev); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=$i'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=$i'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=$numPages'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=1'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=$i'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=$numPages'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=upgrade&page=1'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=upgrade&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=upgrade&page=$i'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=upgrade&page='.$pageNext); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=upgrade&page='.$numPages); ?>">Last &rsaquo;&rsaquo;</a></li>
						</ul>
					</div> 
					<?php 
					}
					?> 
				</div>
			</div>
			<div id="verifyID" class="tab-pane fade in <?php echo (setActive($subMenu, "verifyID")); ?>">
				<h3>Verify ID</h3>
				<?php
				$vidSearch = isset($_GET['vidSearch'])?$_GET['vidSearch']: '';
				$vidWhere = "";
				if ($vidSearch != ""){
					$vidWhere .= " WHERE (vrUsername like '%" . $vidSearch . "%' ";
				    $vidWhere .= " OR vrIDNum like '%" . $vidSearch . "%' ";
				    $vidWhere .= " OR vrFirstName like '%" . $vidSearch . "%' ";
				    $vidWhere .= " OR vrLastName like '%" . $vidSearch . "%' )";
				}
				$vidsts = isset($_GET['vidsts'])?$_GET['vidsts']: $DEF_STATUS_ONPROGRESS;
				?>
				<form action="./" method="GET">
					<div class="row">
                		<div class="col-md-3">
							<input type="hidden" name="menu" value="member">
							<input type="hidden" name="subMenu" value="verifyID">
							<select name="vidsts" class="form-control">
		                    <?php
			                    $selected = ($vidsts==$DEF_STATUS_ONPROGRESS)? " SELECTED": "";
			                    echo ("<option value='". $DEF_STATUS_ONPROGRESS."' $selected>On Progress</option>");

			                    $selected = ($vidsts==$DEF_STATUS_APPROVED)? " SELECTED": "";
			                    echo ("<option value='". $DEF_STATUS_APPROVED."' $selected>Approved</option>");

			                    $selected = ($vidsts==$DEF_STATUS_DECLINED)? " SELECTED": "";
			                    echo ("<option value='". $DEF_STATUS_DECLINED."' $selected>Declined</option>");
		                    ?>
		                    </select>
		                </div>
		                <div class="col-md-3">
		                	<input type="text" class="form-control" name="vidSearch" value="<?php echo ($vidSearch); ?>">
		                </div>
						<div class="col-md-4">
							<button type="submit">Search</button>
						</div>
					</div>
				</form>
				<hr>
				<?php
				$sql  = "SELECT dtVerify.*, idtType, boName, d.dcMsg";
				$sql .= " FROM dtVerify";
				$sql .= " INNER JOIN msIDType ON idtCode = vrType";
				$sql .= " LEFT JOIN dtBackOffice ON boUsername = vrUpdateBy";
				$sql .= " LEFT JOIN (";
				$sql .= " 		SELECT dc.dcTransID, dc.dcMsg FROM dtDecline AS dc";
				$sql .= " 		INNER JOIN (SELECT MAX(dcDate) AS dcDate FROM dtDecline GROUP BY dcTransID) as l ON l.dcDate = dc.dcDate ORDER BY dc.dcDate DESC";
				$sql .= " )AS d ON d.dcTransID = vrUsername";
				$sql .= $vidWhere;
				$sql .= " HAVING vrStatus = '".$vidsts."'";
				$sql .= " ORDER BY vrDate DESC";
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
				<div class="table-responsive-md">
                    <table class="table table-bordered table-shopping w-auto">
                    	<thead>
							<tr>
								<th>#</th>
								<th>Member</th>
								<th>ID Type</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>#</th>
								<th>Member</th>
								<th>ID Type</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($totalRec == 0 ){
							echo "<tr><td colspan=6 class='text-center text-primary'>No Record</td></tr>";
						}
						$i = $startRec;
						while ($row = $result->fetch_assoc()){
							$i++;
							$ttl = $row['vrBOD'];
							$vrStatus = $row['vrStatus'];
							$vrDate = $row['vrDate'];
							$dateUplaod = date_create($vrDate);
							$dateUplaod = date_format($dateUplaod, "M d, Y - H:i:s");
							if ($vrStatus == $DEF_STATUS_ONPROGRESS){
								$status = "<span class='text-warning'><b>On Progress</b></span>";
								$action = "<div class='row'><div class='col-md-6'>
								<a href='#approveID' idtype='".$row['vrType']."' vridnum='".$row['vrIDNum']."' vrfirstname='".$row['vrFirstName']."' vrlastname='".$row['vrLastName']."' vrbod='".$row['vrBOD']."' vrusername='".$row['vrUsername']."' vrfilename='".$row['vrFilename']."' class='btn btn-success btn-round btn-block'>Approve</a></div>
								<div class='col-md-6'><a href='#declineID' vrusername='".$row['vrUsername']."' class='btn btn-danger btn-round btn-block'>Decline</a></div></div>
								";
							}else if ($vrStatus == $DEF_STATUS_APPROVED){
								$status = "<span class='text-success'><b>Approved by ".$row['boName']."</b></span>";
								$action = "";
							}else if ($vrStatus == $DEF_STATUS_DECLINED){
								$status = "<span class='text-danger'><b>Declined by ".$row['boName']."<br>Reasons : <br>".$row['dcMsg']."</b></span>";
								$action = "";
							}
						?>
							<tr>
								<td><?php echo $i; ?></td>
								<td>
									<?php 
									echo $row['vrUsername']."<br>".$row['vrFirstName']." ".$row['vrLastName'];
									?>	
								</td>
								<td><?php echo $row['idtType']; ?></td>
								<!-- <td class="text-center">
									<img class="zoom-img" id="uploadedID" src="../member/photo_verify/<?php echo $row['vrFilename']; ?>" data-zoom-image="../member/photo_verify/<?php echo $row['vrFilename']; ?>" width="300"/>
								</td> -->
								<td><?php echo $status."<br>".$dateUplaod; ?></td>
								<td><?php echo $action; ?></td>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=verifyID&page='.$pagePrev.'&vidSearch='.$vidSearch.'&vidsts='.$vidsts); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=$i&vidSearch=$vidSearch&vidsts=$vidsts'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=$i&vidSearch=$vidSearch&vidsts=$vidsts'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=$secondLast&vidSearch=$vidSearch&vidsts=$vidsts'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=$numPages&vidSearch=$vidSearch&vidsts=$vidsts'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=1&vidSearch=$vidSearch&vidsts=$vidsts'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=2&vidSearch=$vidSearch&vidsts=$vidsts'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=$i&vidSearch=$vidSearch&vidsts=$vidsts'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=$secondLast&vidSearch=$vidSearch&vidsts=$vidsts'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=$numPages&vidSearch=$vidSearch&vidsts=$vidsts'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=verifyID&page=1&vidSearch=$vidSearch&vidsts=$vidsts'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=verifyID&page=2&vidSearch=$vidSearch&vidsts=$vidsts'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=verifyID&page=$i&vidSearch=$vidSearch&vidsts=$vidsts'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=verifyID&page='.$pageNext.'&vidSearch='.$vidSearch.'&vidsts='.$vidsts); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=verifyID&page='.$numPages.'&vidSearch='.$vidSearch.'&vidsts='.$vidsts); ?>">Last &rsaquo;&rsaquo;</a></li>
						</ul>
					</div> 
					<?php 
					}
					?> 
                </div>
			</div>
			<div id="beneficiary" class="tab-pane fade in <?php echo (setActive($subMenu, "beneficiary")); ?>">
				<h3>Beneficiary</h3>
				<?php
				$benSearch = isset($_GET['benSearch'])?$_GET['benSearch']: '';
				$sqlBen = "";
				if ($benSearch != ""){
					$sqlBen .= " WHERE (mbrUsername like '%" . $benSearch . "%' ";
				    $sqlBen .= " OR mbrFirstName like '%" . $benSearch . "%' ";
				    $sqlBen .= " OR mbrLastName like '%" . $benSearch . "%' ";
				    $sqlBen .= " OR BenFirstName like '%" . $benSearch . "%' ";
				    $sqlBen .= " OR BenLastName like '%" . $benSearch . "%' ";
				    $sqlBen .= " OR BenIDNum like '%" . $benSearch . "%' )";
				}
				?>
				<form action="./" method="GET">
					<input type="hidden" name="menu" value="member">
					<input type="hidden" name="subMenu" value="beneficiary">
					<input type="text" name="benSearch" value="<?php echo ($benSearch); ?>"><button type="submit">Search</button>
				</form>
				<hr>
				<?php
				$sql  = "SELECT mbrUsername, mbrFirstName, mbrLastName, ben.* FROM dtMember";
				$sql .= " INNER JOIN (";
				$sql .= " 	SELECT dtBeneficiary.*, idtType, RelType";
				$sql .= " 	FROM dtBeneficiary";
				$sql .= " 	INNER JOIN msIDType ON idtCode = BenIDType";
				$sql .= " 	INNER JOIN msRelationType ON RelCode = BenRelationType";
				$sql .= " ) as ben ON ben.BenMbrUsername = mbrUsername";
				$sql .= $sqlBen;
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
				<div class="table-responsive-md">
                    <table class="table table-bordered table-shopping w-auto">
                    	<thead>
							<tr>
								<th>#</th>
								<th>Member</th>
								<th>Beneficiary</th>
								<th>ID Image</th>
								<th>Update Date</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>#</th>
								<th>Member</th>
								<th>Beneficiary</th>
								<th>ID Image</th>
								<th>Update Date</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($totalRec == 0 ){
							echo "<tr><td colspan='5' class='text-center text-primary'>No Record</td></tr>";
						}
						$i = $startRec;
						while ($row = $result->fetch_assoc()){
							$i++;
							$BenUpdateDate = date_create($row['BenUpdateDate']);
                            $BenUpdateDate = date_format($BenUpdateDate, "F d, Y <\b\\r> h:i A");
                            // echo $row['BenFileID'];
						?>
						<tr>
							<td><?php echo $i; ?></td>
							<td><?php echo $row['mbrUsername']."<br>".$row['mbrFirstName']." ". $row['mbrLastName']; ?></td>
							<td>
								<div class="row">
									<div class="col-md-5">Name</div>
									<div class="col-md-7">: <?php echo $row['BenFirstName']." ".$row['BenLastName']; ?></div>
								</div>
								<div class="row">
									<div class="col-md-5">ID Number</div>
									<div class="col-md-7">: <?php echo $row['BenIDNum']; ?></div>
								</div>
								<div class="row">
									<div class="col-md-5">ID Type</div>
									<div class="col-md-7">: <?php echo $row['idtType']; ?></div>
								</div>
								<div class="row">
									<div class="col-md-5">BOD</div>
									<div class="col-md-7">: <?php echo $row['BenBOD']; ?></div>
								</div>
								<div class="row">
									<div class="col-md-5">Relation Type</div>
									<div class="col-md-7">: <?php echo $row['RelType']; ?></div>
								</div>
							</td>
							<td class="text-center">
								<img class="zoom-img benfileid" src="../member/photo_verify/<?php echo $row['BenFileID']; ?>" data-zoom-image="../member/photo_verify/<?php echo $row['BenFileID']; ?>" width="300"/>
							</td>
							<td><?php echo $BenUpdateDate; ?></td>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=beneficiary&page='.$pagePrev.'&benSearch='.$benSearch); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=$i&benSearch=$benSearch'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=$i&benSearch=$benSearch'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=$secondLast&benSearch=$benSearch'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=$numPages&benSearch=$benSearch'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=1&benSearch=$benSearch'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=2&benSearch=$benSearch'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=$i&benSearch=$benSearch'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=$secondLast&benSearch=$benSearch'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=$numPages&benSearch=$benSearch'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=beneficiary&page=1&benSearch=$benSearch'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=beneficiary&page=2&benSearch=$benSearch'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=beneficiary&page=$i&benSearch=$benSearch'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=beneficiary&page='.$pageNext.'&benSearch='.$benSearch); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=beneficiary&page='.$numPages.'&benSearch='.$benSearch); ?>">Last &rsaquo;&rsaquo;</a></li>
						</ul>
					</div> 
					<?php 
					}
					?> 
				</div>
			</div>
			<div id="mymac" class="tab-pane fade in <?php echo (setActive($subMenu, "mymac")); ?>">
				<h3>Activate My Mac</h3>
				<hr>
				<?php
				$sql  = " SELECT * FROM dtLogMymac";
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
				<div class="table-responsive-md">
                    <table class="table table-bordered table-shopping w-auto" id="tMyMac" value="<?php echo $totalRec; ?>">
                    	<thead>
							<tr>
								<th>#</th>
								<th>Username</th>
								<th>Type</th>
								<th>Description</th>
								<th>Date</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>#</th>
								<th>Username</th>
								<th>Type</th>
								<th>Description</th>
								<th>Date</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</tfoot>
						<tbody>
						<?php
						if ($totalRec == 0 ){
							echo "<tr><td colspan='7' class='text-center text-primary'>No Record</td></tr>";
						}
						$i = $startRec;
						while ($row = $result->fetch_assoc()){
							$i++; 
							$action = $status = "";


							if ($row['logmStatus'] == $DEF_STATUS_PENDING){
								$status = "<span class='text-warning'><b>Pending</b></span>";
								$action = "<a href='#actMyMac' data-id = '".$row['logmID']."' data-user='".$row['logmUsername']."' class='btn btn-success btn-round btn-block'>Activate Member</a>";
							}else if ($row['logmStatus'] == $DEF_STATUS_APPROVED){
								$action = "<span class='text-success'><b>Updated By : ".$row['logmUpdateBy']."</b></span>";
								$status = "<span class='text-success'><b>Approved</b></span>";
							}
						?>
						<tr>
							<td><?php echo $i; ?></td>
							<td><?php echo $row['logmUsername']; ?></td>
							<td><?php echo $row['logmType']; ?></td>
							<td><?php echo $row['logmDesc']; ?></td>
							<td><?php echo $row['logmDate']; ?></td>
							<td><?php echo $status; ?></td>
							<td><?php echo $action; ?></td>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=mymac&page='.$pagePrev); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=mymac&page=$i'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=mymac&page=$i'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=mymac&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=mymac&page=$numPages'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=mymac&page=1'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=mymac&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=mymac&page=$i'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=mymac&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=mymac&page=$numPages'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=mymac&page=1'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=mymac&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=mymac&page=$i'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=mymac&page='.$pageNext); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=mymac&page='.$numPages); ?>">Last &rsaquo;&rsaquo;</a></li>
						</ul>
					</div> 
					<?php 
					}
					?> 
				</div>
			</div>
		</div>
	</div>
	<div class="modal-2" id="modalApproveID">
	    <div class="modal-content-2">
	        <form class="animate" action="" method="post" onSubmit="return false;">
	        	<input type="hidden" name="status" value="<?php echo $DEF_STATUS_APPROVED; ?>">
	        	<span id="tradeID" title=""></span>
	          	<div class="container-fluid">
	                <div class="row text-center" id="alertMsg" style="display:none">
	                    <div class="alert alert-danger alert-dismissible">
	                  		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	                      	<strong>Info - </strong><span id="msg"></span>
	                  	</div>
	            	</div>
	            	<div class="row">
	            		<img class="zoom-img" id="uploadedID" src="" data-zoom-image="" width="300" height="270" />
	            	</div>
	            	<div class="row">
	                  	<label class="col-md-4 text-left text-vCenter">ID Type</label>
	                  	<div class="col-md-8">
	                      	<div class="form-group">
	                          	<select class="form-control" data-size="5" data-style="btn btn-primary" name="idType" id="idType">
		                            <option value="" disabled selected hidden>Select ID Type</option>
		                            <?php
		                                $sql  = "SELECT * FROM msIDType";
		                                $sql .= " ORDER BY idtType ASC";
		                                $query = $conn->query($sql);
		                                while ($row = $query->fetch_assoc()){
		                                	$selected = "";
		                                    // $selected =  ($mbrIDType == $row["idtCode"])?" selected " : "";
		                                    echo ("<option value='".$row["idtCode"]."' " . $selected . ">".$row["idtType"]."</option>");
		                                }
		                            ?>
		                        </select>
	                      	</div>
	                  	</div>
	              	</div>
	              	<div class="row">
	                  	<label class="col-md-4 text-left text-vCenter">ID Number</label>
	                  	<div class="col-md-8">
	                      	<div class="form-group">
	                          	<input type="text" name="idNumber" id="idNumber" class="form-control" placeholder="ID Number" required>
	                      	</div>
	                  	</div>
	              	</div>
	              	<div class="row">
	                  	<label class="col-md-4 text-left text-vCenter">Full Name</label>
	                  	<div class="col-md-4">
	                      	<div class="form-group">
	                          	<input type="text" name="idFirstName" id="idFirstName" class="form-control" placeholder="First Name" required>
	                      	</div>
	                  	</div>
	                  	<div class="col-md-4">
	                      	<div class="form-group">
	                          	<input type="text" name="idLastName" id="idLastName" class="form-control" placeholder="Last Name">
	                      	</div>
	                  	</div>
	              	</div>
	              	<div class="row">
	                      <label class="col-md-4 text-left text-vCenter">Birth of Date</label>
	                      <div class="col-md-8">
	                          <div class="form-group">
	                              <input type="date" name="idBOD" id="idBOD" class="form-control" required>
	                          </div>
	                      </div>
	              	</div>
	              	<div class="row">
	                  	<div class="col-md-4"></div>
	                  	<div class="col-md-4">
	              		</div>
	              		<div class="col-md-4">
	                      	<button type="submit" name="btnCnfApprove" id="btnCnfApprove" class="btn btn-block col-md-12 btn-success">Confirm Approve</button>
	                  	</div>
	              	</div>
	          	</div>
	      	</form>
      	</div>
  	</div>
  	<div class="modal-2" id="modalDeclineID">
	    <div class="modal-content-2">
	        <form class="animate" action="" method="post" onSubmit="return false;">
	        	<input type="hidden" name="status" value="<?php echo $DEF_STATUS_DECLINED; ?>">
	          	<div class="container-fluid">
	                <div class="row text-center" id="alertMsg" style="display:none">
	                    <div class="alert alert-danger alert-dismissible">
	                  		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	                      	<strong>Info - </strong><span id="msg"></span>
	                  	</div>
	            	</div>
	              	<div class="row">
	                  	<label class="col-md-3 text-left text-vCenter">Decline Reason</label>
	                  	<div class="col-md-9">
	                      	<div class="form-check">
							    <label class="form-check-label">
							        <input class="form-check-input" type="checkbox" name="common" value="ID Number Not Valid">
							        ID Number Not Valid
							        <span class="form-check-sign">
							            <span class="check"></span>
							        </span>
							    </label>
							</div>
							<div class="form-check">
							    <label class="form-check-label">
							        <input class="form-check-input" type="checkbox" name="common" value="Name Not Valid">
							        Full Name Not Valid
							        <span class="form-check-sign">
							            <span class="check"></span>
							        </span>
							    </label>
							</div>
							<div class="form-check">
							    <label class="form-check-label">
							        <input class="form-check-input" type="checkbox" name="common" value="Birth of Date Not Valid">
							        Birth of Date Not Valid
							        <span class="form-check-sign">
							            <span class="check"></span>
							        </span>
							    </label>
							</div>
							<div class="form-check">
							    <label class="form-check-label">
							        <input class="form-check-input" type="checkbox" name="common" value="Image blur">
							        Image blur / tidak jelas
							        <span class="form-check-sign">
							            <span class="check"></span>
							        </span>
							    </label>
							</div>
							<div class="form-check">
							    <label class="form-check-label">
							        <input class="form-check-input" type="checkbox" name="custom" value="othermsg">
							        Other Reasons
							        <span class="form-check-sign">
							            <span class="check"></span>
							        </span>
							    </label>
							</div>
							<div class="form-group">
									<textarea class="form-control" id="othermsg" placeholder="Reasons of rejection message" rows="7"></textarea>
							</div>
	                  	</div>
	              	</div>
	              	<div class="row">
	                  	<div class="col-md-4"></div>
	                  	<div class="col-md-4"></div>
	                  	<div class="col-md-4">
	                      	<button type="submit" name="btnCnfDecline" id="btnCnfDecline" class="btn btn-block col-md-12 btn-danger">Confirm Decline</button>
	              		</div>
	              	</div>
	          	</div>
	      	</form>
      	</div>
  	</div>
</div>