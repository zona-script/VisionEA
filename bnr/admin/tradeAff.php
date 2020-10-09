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
		$("a[href='#approveAff']").on("click", function(){
			var affAccNo = $(this).attr("data-value");
			var affUsername = $(this).attr("data-username");
			Swal.fire({
				title: 'Affilias Account',
				text: 'Continue Approve Affiliasi Account ?',
				icon: 'info',
				showCancelButton: true,
				showLoaderOnConfirm: true,
				confirmButtonColor: '#3085d6',
				confirmButtonText: 'Yes, Approve!',
				cancelButtonText: 'Close',
				allowEscapeKey: false,
			    allowOutsideClick: false,
				preConfirm: function(){
					return new Promise(function(resolve) {
						$.post("json.php",
						{
							"q" 			: "AccAffiliasiApproved",
							"affAccNo" 		: affAccNo,
							"affUsername" 	: affUsername
						},
						function (data, success){
							$myDataObj  = JSON.parse(data);
							if ($.trim($myDataObj["status"]) == "success"){
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
							}else {
								Swal.fire({
									title : 'Process failed',
									icon : 'error',
									text : $myDataObj["message"]
								});
								return false
							}
						});
					});
				}
			});
		});

		$("a[href='#declineAff']").on("click", function(){
			var affAccNo = $(this).attr("data-value");
			var affUsername = $(this).attr("data-username");
			Swal.fire({
				title: 'Affilias Account',
				text: 'Continue Decline Affiliasi Account ?',
				icon: 'warning',
				showCancelButton: true,
				showLoaderOnConfirm: true,
				confirmButtonColor: '#d33',
				confirmButtonText: 'Yes, Decline!',
				cancelButtonText: 'Close',
				allowEscapeKey: false,
			    allowOutsideClick: false,
				preConfirm: function(){
					return new Promise(function(resolve) {
						$.post("json.php",
						{
							"q" 			: "AccAffiliasiDeclined",
							"affAccNo" 		: affAccNo,
							"affUsername" 	: affUsername
						},
						function (data, success){
							$myDataObj  = JSON.parse(data);
							if ($.trim($myDataObj["status"]) == "success"){
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
							}else{
								Swal.fire({
									title : 'Process failed',
									icon : 'error',
									text : $myDataObj["message"]
								});
								return false
							}
						});
					});
				}
			});
		});
	});
</script>

<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Trading Account</div>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "affiliasi")); ?>"><a data-toggle="tab" href="#affiliasi">Affiliasi</a></li>
		</ul>
		<div class="tab-content">
			<!-- Acc Affiliasi -->
			<div id="affiliasi" class="tab-pane fade in <?php echo (setActive($subMenu, "affiliasi")); ?>" >
				<?php 
				$affWhere = "";
				$stAffiliasi = (isset($_GET['stAffiliasi']))?fValidateInput($_GET['stAffiliasi']): $DEF_STATUS_ONPROGRESS;
				$searchAffiliasi = (isset($_GET['searchAffiliasi']))?fValidateSQLFromInput($conn, $_GET['searchAffiliasi']): '';
				if ($searchAffiliasi != ""){
					$affWhere  = " AND (affUsername LIKE '%".$searchAffiliasi."%' ";
					$affWhere .= " OR affAccNo LIKE '%".$searchAffiliasi."%')";
				}
				?>
				<form action="./" method="GET">
					<input type="hidden" name="menu" value="<?php echo $menu; ?>">
					<input type="hidden" name="subMenu" value="<?php echo $subMenu; ?>">
					<div class="row">
						<div class="form-group col-lg-3 col-md-3">
							<label for="stAffiliasi">Status</label>
							<select class="form-control" name="stAffiliasi" id="stAffiliasi">
								<option value="<?php echo $DEF_STATUS_ONPROGRESS ?>" <?php echo ($stAffiliasi ==  $DEF_STATUS_ONPROGRESS)?'selected':''; ?>>On Progress</option>
								<option value="<?php echo $DEF_STATUS_APPROVED ?>" <?php echo ($stAffiliasi ==  $DEF_STATUS_APPROVED)?'selected':''; ?>>Approved</option>
							</select>
						</div>
						<div class="form-group col-lg-3 col-md-3">
							<label for="searchAffiliasi">Search</label>
							<input type="text" class="form-control" name="searchAffiliasi" id="searchAffiliasi" value="<?php echo $searchAffiliasi; ?>">
						</div>
						<div class="form-group col-lg-6 col-md-6">
							<br>
							<button type="submit" class="btn btn-info btn-round">Submit</button>
						</div>
					</div>
				</form>
				<?php
				$sql  = " SELECT * FROM dtTradingAccAff";
				$sql .= " INNER JOIN msStatus ON stID = affStatus";
				$sql .= " WHERE affStatus = '".$stAffiliasi."' ";
				$sql .= $affWhere;
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
					<table class="table table-hover table-striped small" id="tAffiliasi">
						<thead>
							<th>Username</th>
							<th>Account Number</th>
							<th>Date</th>
							<th>Status</th>
							<th>Action</th>
						</thead>
						<tfoot>
							<th>Username</th>
							<th>Account Number</th>
							<th>Date</th>
							<th>Status</th>
							<th>Action</th>
						</tfoot>
						<tbody>
							<?php
							if ($result->num_rows == 0){
								echo "<tr><td colspan = '4' class='text-info text-center'>No Record</td></tr>";
							}
							while ($row=$result->fetch_assoc()){
								if ($row['affStatus'] == $DEF_STATUS_ONPROGRESS){
									$txtStatus = "<span class='text-warning'>".$row['stDesc']."</span>";
								}else if ($row['affStatus'] == $DEF_STATUS_APPROVED){
									$txtStatus = "<span class='text-success'>".$row['stDesc']."</span>";
								}
							?>
							<tr>
								<td><?php echo $row['affUsername']; ?></td>
								<td><?php echo $row['affAccNo']; ?></td>
								<td><?php echo $row['affDate']; ?></td>
								<td><?php echo $txtStatus; ?></td>
								<td>
									<a href="#approveAff" class="text-success" data-value="<?php echo $row['affAccNo'] ?>" data-username="<?php echo $row['affUsername']; ?>" title="Approve Affiliasi"><i class="fa fa-edit fa-2x"></i></a>
									<a href="#declineAff" class="text-danger" data-value="<?php echo $row['affAccNo'] ?>" data-username="<?php echo $row['affUsername']; ?>" title="Decline Affiliasi"><i class="fa fa-ban fa-2x"></i></a>
								</td>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=affiliasi&searchAffiliasi='.$searchAffiliasi.'&stAffiliasi='.$stAffiliasi.'&page='.$pagePrev); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=$i'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=$i'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=$numPages'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=1'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=$i'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=$numPages'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=1'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=affiliasi&searchAffiliasi=$searchAffiliasi&stAffiliasi=$stAffiliasi&page=$i'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=affiliasi&searchAffiliasi='.$searchAffiliasi.'&stAffiliasi='.$stAffiliasi.'&page='.$pageNext); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=affiliasi&searchAffiliasi='.$searchAffiliasi.'&stAffiliasi='.$stAffiliasi.'&page='.$numPages); ?>">Last &rsaquo;&rsaquo;</a></li>
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