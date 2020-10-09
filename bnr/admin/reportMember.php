<?php 
include_once("../includes/inc_def.php");
include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$menu 		= (isset($_GET['menu']))?fValidateInput($_GET['menu']):"report";
$subMenu 	= (isset($_GET['subMenu']))?fValidateInput($_GET['subMenu']):"reportMember";
$sub 		= (isset($_GET['sub']))?fValidateInput($_GET['sub']):"rptNew";
$currDate = date_create($CURRENT_TIME);
$currDate = date_format($currDate, "Y-m");
$dateFrom = date_create($currDate);
$dateTo = date_create($currDate);
date_add($dateTo, date_interval_create_from_date_string("0 month"));
date_add($dateTo, date_interval_create_from_date_string("1 month"));
date_add($dateTo, date_interval_create_from_date_string("-1 days"));
$dateFrom = date_format($dateFrom, "Y-m-d");
$dateTo = date_format($dateTo, "Y-m-d");

//untuk tab New
$newdateFrom 	= (isset($_GET['newdateFrom']))?fValidateSQLFromInput($conn, $_GET['newdateFrom']): $dateFrom;
$newdateTo 		= (isset($_GET['newdateTo']))?fValidateSQLFromInput($conn, $_GET['newdateTo']): $dateTo;
$newSearch 		= (isset($_GET['newSearch']))?fValidateSQLFromInput($conn, $_GET['newSearch']): "";
//untuk tab Renew
$renewdateFrom 	= (isset($_GET['renewdateFrom']))?fValidateSQLFromInput($conn, $_GET['renewdateFrom']): $dateFrom;
$renewdateTo 	= (isset($_GET['renewdateTo']))?fValidateSQLFromInput($conn, $_GET['renewdateTo']): $dateTo;
$renewSearch 	= (isset($_GET['renewSearch']))?fValidateSQLFromInput($conn, $_GET['renewSearch']): "";
//untuk tab Product Order
$prodateFrom 	= (isset($_GET['prodateFrom']))?fValidateSQLFromInput($conn, $_GET['prodateFrom']): $dateFrom;
$prodateTo 		= (isset($_GET['prodateTo']))?fValidateSQLFromInput($conn, $_GET['prodateTo']): $dateTo;
$proSearch 		= (isset($_GET['proSearch']))?fValidateSQLFromInput($conn, $_GET['proSearch']): "";
$trProStatus 	= (isset($_GET['trProStatus']))?fValidateSQLFromInput($conn, $_GET['trProStatus']): "4";
//untuk tab Expiration
$expdateFrom 	= (isset($_GET['expdateFrom']))?fValidateSQLFromInput($conn, $_GET['expdateFrom']): $dateFrom;
$expdateTo 		= (isset($_GET['expdateTo']))?fValidateSQLFromInput($conn, $_GET['expdateTo']): $dateTo;
$expSearch 		= (isset($_GET['expSearch']))?fValidateSQLFromInput($conn, $_GET['expSearch']): "";
$st 		= (isset($_GET['st']))?fValidateSQLFromInput($conn, $_GET['st']): $DEF_STATUS_BLOCKED;
?>

<script type="text/javascript">
$(document).ready(function(){
	// biar tiap pindah tabs trigger button search clicked 
	$(".nav-tabs li a").on("click", function(){
		var id = $(this).attr("href");
		$(id).find("button[type='submit']").trigger("click");
	})
});
</script>
<span id="q" title="<?php echo $q; ?>"></span>
<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Member Report</div>
		<ul class="nav nav-pills nav-tabs">
			<li class="<?php echo setActive($sub, "rptNew"); ?>"><a data-toggle="tab" href="#rptNew">New</a></li>
			<li class="<?php echo setActive($sub, "rptRenew"); ?>"><a data-toggle="tab" href="#rptRenew">Renew</a></li>
			<li class="<?php echo setActive($sub, "rptPro"); ?>"><a data-toggle="tab" href="#rptPro">Product Order</a></li>
			<li class="<?php echo setActive($sub, "rptExp"); ?>"><a data-toggle="tab" href="#rptExp">Expiration</a></li>
		</ul>
		<div class="tab-content">
			<div id="rptNew" class="tab-pane fade in <?php echo setActive($sub, "rptNew"); ?>">
				<div class="table-responsive">
					<form method="GET" action="./">
						<input type="hidden" name="menu" value="report">
						<input type="hidden" name="subMenu" value="reportMember">
						<input type="hidden" name="sub" value="rptNew">
						<div class="row form-group">
							<div class="col-lg-12">&nbsp;</div>
							<div class="col-lg-2">Date From : 
								<input type="date" id="newdateFrom" name="newdateFrom" class="form-control" value="<?php echo $newdateFrom; ?>">
							</div>
							<div class="col-lg-2">Date To :
								<input type="date" id="newdateTo" name="newdateTo" class="form-control" value="<?php echo $newdateTo; ?>">
							</div>
							<div class="col-lg-4">Search : (Username, Name, Email, Sponsor)
								<input type="text" id="newSearch" name="newSearch" class="form-control" value="<?php echo $newSearch; ?>">
							</div>
							<div class="col-lg-2"><br>
								<button type="submit" class="btn btn-primary">Search</button>
							</div>
						</div>
					</form>
					<hr>
					<table class="table table-hover table-striped small" id="tRptNew">
						<?php
						$page = (isset($_GET['page']))?fValidateInput($_GET['page']): 1;
						$paramValue = array();
						$arrDataParam = array(
							$DEF_STATUS_NEW
						);
						$sql  = "SELECT mbrUsername, CONCAT(mbrFirstName,' ',mbrLastName) AS mbrFullName, mbrEmail, ";
						$sql .= " CONCAT('+',countryMobileCode,'-',mbrMobile) AS mbrContact, mbrSponsor, trDate, countryDesc";
						$sql .= " FROM dtMember";
						$sql .= " INNER JOIN Transaction ON trUsername = mbrUsername";
						$sql .= " INNER JOIN msCountry ON countryID = mbrCountry";
						$sql .= " WHERE trStatus = ?";
						if ($newdateFrom != "" && $newdateTo != "" ){
							$sql .= " AND date(trDate) BETWEEN ? AND ?";
							array_push($arrDataParam, $newdateFrom, $newdateTo);
						}

						if($newSearch != ""){
							$sql .= " AND (mbrUsername LIKE ?";
							$sql .= " OR mbrFirstName LIKE ?";
							$sql .= " OR mbrLastName LIKE ?";
							$sql .= " OR mbrEmail LIKE ?";
							$sql .= " OR mbrSponsor LIKE ?)";
							array_push($arrDataParam, '%'.$newSearch.'%', '%'.$newSearch.'%', '%'.$newSearch.'%', '%'.$newSearch.'%', '%'.$newSearch.'%');
						}
						foreach ($arrDataParam as &$value) {
			                $paramValue[] = $value;
			            }
						$stmt = $conn->prepare($sql);
						$types  = array(str_repeat('s', count($paramValue))); 
            			$types = (implode(", ",$types));
            			$stmt->bind_param($types, ...$paramValue);
						$stmt->execute();
						$result = $stmt->get_result();
						$totalRec = $result->num_rows;
						$numPerPage = $DEF_NUM_PER_PAGE;
						$numPages	= ceil ($totalRec / $numPerPage);
						$page = ($page<1)?1:$page;			
						$startRec = ($page-1) * $numPerPage;
						$secondLast = $numPages - 1;
						$sql .= " ORDER BY trDate DESC LIMIT ".$startRec.", ".$numPerPage;
						$stmt = $conn->prepare($sql);
						$types  = array(str_repeat('s', count($paramValue))); 
            			$types = (implode(", ",$types));
            			$stmt->bind_param($types, ...$paramValue);
						$stmt->execute();
						$result = $stmt->get_result();
						?>
						<thead>
							<tr>
								<th>Username / Name</th>
								<th>Email / Mobile</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Activated Date</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ($result->num_rows == 0){
								echo "<tr><td colspan=5 class='text-center text-primary'>No Record</td></tr>";
							}
							while ($row=$result->fetch_assoc()){
							?>
							<tr>
								<td><?php echo $row['mbrUsername']."<br>".$row['mbrFullName']; ?></td>
								<td><?php echo $row['mbrEmail']."<br>".$row['mbrContact']; ?></td>
								<td><?php echo $row['countryDesc']; ?></td>
								<td><?php echo $row['mbrSponsor']; ?></td>
								<td><?php echo $row['trDate']; ?></td>
							</tr>
							<?php 
							}
							?>
						</tbody>
					</table>
				</div>
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
						<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptNew&page='.$pagePrev.'&newdateFrom='.$newdateFrom.'&newdateTo='.$newdateTo.'&newSearch='.$newSearch); ?>">Previous</a></li>
						<?php
						if ($numPages <= 10){  	 
							for ($i = 1; $i <= $numPages; $i++){
								if ($i == $page) {
									echo "<li class='active'><a>$i</a></li>";
						        }else{
							        echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=$i&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>$i</a></li>";
				                }
					        }
						}else if ($numPages > 10){
							if ($page <= 4) {
								for ($i = 1; $i < 8; $i++){		 
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=$i&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>$i</a></li>";
									}
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=$secondLast&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=$numPages&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>$numPages</a></li>";
							}else if($page > 4 && $page < $numPages - 4) {		 
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=1&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>1</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=2&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=$i&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>$i</a></li>";
									}             
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=$secondLast&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=$numPages&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>$numPages</a></li>";
							}else{
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptNew&page=1&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>1</a></li>";
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptNew&page=2&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for($i = $numPages - 6; $i <= $numPages; $i++){
									if ($i == $page){
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptNew&page=$i&newdateFrom=$newdateFrom&newdateTo=$newdateTo&newSearch=$newSearch'>$i</a></li>";
									}                   
								}
							}
						}
						?>
						<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptNew&page='.$pageNext.'&newdateFrom='.$newdateFrom.'&newdateTo='.$newdateTo.'&newSearch='.$newSearch); ?>">Next</a></li>
						<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptNew&page='.$numPages.'&newdateFrom='.$newdateFrom.'&newdateTo='.$newdateTo.'&newSearch='.$newSearch); ?>">Last &rsaquo;&rsaquo;</a></li>
					</ul>
				</div> 
				<?php 
				}
				?>
			</div>
			<div id="rptRenew" class="tab-pane fade in <?php echo setActive($sub, "rptRenew"); ?>">
				<div class="table-responsive">
					<form method="GET" action="./">
						<input type="hidden" name="menu" value="report">
						<input type="hidden" name="subMenu" value="reportMember">
						<input type="hidden" name="sub" value="rptRenew">
						<div class="row form-group">
							<div class="col-lg-12">&nbsp;</div>
							<div class="col-lg-2">Date From : 
								<input type="date" id="renewdateFrom" name="renewdateFrom" class="form-control" value="<?php echo $renewdateFrom; ?>">
							</div>
							<div class="col-lg-2">Date To :
								<input type="date" id="renewdateTo" name="renewdateTo" class="form-control" value="<?php echo $renewdateTo; ?>">
							</div>
							<div class="col-lg-4">Search : (Username, Name, Email, Sponsor)
								<input type="text" id="renewSearch" name="renewSearch" class="form-control" value="<?php echo $renewSearch; ?>">
							</div>
							<div class="col-lg-2"><br>
								<button type="submit" class="btn btn-primary">Search</button>
							</div>
						</div>
					</form>
					<hr>
					<table class="table table-hover table-striped small" id="tRptRenew">
						<?php 
						$page = (isset($_GET['page']))?fValidateInput($_GET['page']): 1;
						$paramValue = array();
						$arrDataParam = array(
							$DEF_STATUS_UPGRADE
						);
						$sql  = " SELECT mbrUsername, CONCAT(mbrFirstName,' ',mbrLastName) AS mbrFullName, mbrEmail, ";
						$sql .= " CONCAT('+',countryMobileCode,'-',mbrMobile) AS mbrContact, mbrSponsor, trDate, countryDesc";
						$sql .= " FROM dtMember";
						$sql .= " INNER JOIN Transaction ON trUsername = mbrUsername";
						$sql .= " INNER JOIN msCountry ON countryID = mbrCountry";
						$sql .= " WHERE trStatus = ?  AND trThn > 1";

						if ($newdateFrom != "" && $newdateTo != "" ){
							$sql .= " AND date(trDate) BETWEEN ? AND ?";
							array_push($arrDataParam, $renewdateFrom, $renewdateTo);
						}

						if($renewSearch != ""){
							$sql .= " AND (mbrUsername LIKE ?";
							$sql .= " OR mbrFirstName LIKE ?";
							$sql .= " OR mbrLastName LIKE ?";
							$sql .= " OR mbrEmail LIKE ?";
							$sql .= " OR mbrSponsor LIKE ?)";
							array_push($arrDataParam, '%'.$renewSearch.'%', '%'.$renewSearch.'%', '%'.$renewSearch.'%', '%'.$renewSearch.'%', '%'.$renewSearch.'%');
						}
						foreach ($arrDataParam as &$value) {
			                $paramValue[] = $value;
			            }
						$stmt = $conn->prepare($sql);
						$types  = array(str_repeat('s', count($paramValue))); 
	        			$types = (implode(", ",$types));
	        			$stmt->bind_param($types, ...$paramValue);
						$stmt->execute();
						$result = $stmt->get_result();
						$totalRec = $result->num_rows;
						$numPerPage = $DEF_NUM_PER_PAGE;
						$numPages	= ceil ($totalRec / $numPerPage);
						$page = ($page<1)?1:$page;			
						$startRec = ($page-1) * $numPerPage;
						$secondLast = $numPages - 1;
						$sql .= " ORDER BY trDate DESC LIMIT ".$startRec.", ".$numPerPage;
						$stmt = $conn->prepare($sql);
						$types  = array(str_repeat('s', count($paramValue))); 
	        			$types = (implode(", ",$types));
	        			$stmt->bind_param($types, ...$paramValue);
						$stmt->execute();
						$result = $stmt->get_result();
						?>
						<thead>
							<tr>
								<th>Username / Name</th>
								<th>Email / Mobile</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Renew Date</th>
							</tr>
						</thead>
						<tbody>
						<?php
						if ($result->num_rows == 0){
							echo "<tr><td colspan=5 class='text-center text-primary'>No Record</td></tr>";
						}
						while ($row=$result->fetch_assoc()){
						?>
							<tr>
								<td><?php echo $row['mbrUsername']."<br>".$row['mbrFullName']; ?></td>
								<td><?php echo $row['mbrEmail']."<br>".$row['mbrContact']; ?></td>
								<td><?php echo $row['countryDesc']; ?></td>
								<td><?php echo $row['mbrSponsor']; ?></td>
								<td><?php echo $row['trDate']; ?></td>
							</tr>
						<?php 
						}
						?>
						</tbody>
					</table>
				</div>
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
						<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptRenew&page='.$pagePrev.'&renewdateFrom='.$renewdateFrom.'&renewdateTo='.$renewdateTo.'&renewSearch='.$renewSearch); ?>">Previous</a></li>
						<?php
						if ($numPages <= 10){  	 
							for ($i = 1; $i <= $numPages; $i++){
								if ($i == $page) {
									echo "<li class='active'><a>$i</a></li>";
						        }else{
							        echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=$i&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>$i</a></li>";
				                }
					        }
						}else if ($numPages > 10){
							if ($page <= 4) {
								for ($i = 1; $i < 8; $i++){		 
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=$i&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>$i</a></li>";
									}
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=$secondLast&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=$numPages&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>$numPages</a></li>";
							}else if($page > 4 && $page < $numPages - 4) {		 
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=1&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>1</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=2&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=$i&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>$i</a></li>";
									}             
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=$secondLast&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=$numPages&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>$numPages</a></li>";
							}else{
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=1&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>1</a></li>";
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=2&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for($i = $numPages - 6; $i <= $numPages; $i++){
									if ($i == $page){
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptRenew&page=$i&renewdateFrom=$renewdateFrom&renewdateTo=$renewdateTo&renewSearch=$renewSearch'>$i</a></li>";
									}                   
								}
							}
						}
						?>
						<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptRenew&page='.$pageNext.'&renewdateFrom='.$renewdateFrom.'&renewdateTo='.$renewdateTo.'&renewSearch='.$renewSearch); ?>">Next</a></li>
						<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptRenew&page='.$numPages.'&renewdateFrom='.$renewdateFrom.'&renewdateTo='.$renewdateTo.'&renewSearch='.$renewSearch); ?>">Last &rsaquo;&rsaquo;</a></li>
					</ul>
				</div> 
				<?php 
				}
				?>
			</div>
			<div id="rptPro" class="tab-pane fade in <?php echo setActive($sub, "rptPro"); ?>">
				<div class="table-responsive">
					<form method="GET" action="./">
						<input type="hidden" name="menu" value="report">
						<input type="hidden" name="subMenu" value="reportMember">
						<input type="hidden" name="sub" value="rptPro">
						<div class="row form-group">
							<div class="col-lg-12">&nbsp;</div>
							<div class="col-lg-2">Date From : 
								<input type="date" id="prodateFrom" name="prodateFrom" class="form-control" value="<?php echo $prodateFrom; ?>">
							</div>
							<div class="col-lg-2">Date To :
								<input type="date" id="prodateTo" name="prodateTo" class="form-control" value="<?php echo $prodateTo; ?>">
							</div>
							<div class="col-lg-4">Search : (Username, Name, Email, Sponsor, Order ID)
								<input type="text" id="proSearch" name="proSearch" class="form-control" value="<?php echo $proSearch; ?>">
							</div>
							<div class="col-lg-2"><br>
								<button type="submit" class="btn btn-primary">Search</button>
							</div>
						</div>
					</form>
					<hr>
					<table class="table table-hover table-striped small" id="trptPro">
						<?php 
						$page = (isset($_GET['page']))?fValidateInput($_GET['page']): 1;
						$paramValue = array();
						$arrDataParam = array(
							$DEF_TYPE_PURCHASE_RENEW,
							$DEF_TYPE_PURCHASE_ACT,
							$DEF_TYPE_PURCHASE_RENEW,
							$DEF_TYPE_PURCHASE_ACT,
							$trProStatus
						);
						$sql  = "SELECT m.*, CONCAT(mbrFirstName,' ',mbrLastName) AS mbrFullName, CONCAT('+',countryMobileCode,'-',mbrMobile) AS mbrContact, ";
						$sql .= " countryDesc, trProTransID, trProUserBeli, trPDQty, trProType, proName, trProUpdateDate, DATE_ADD(trProActiveDate, INTERVAL 1 YEAR ) AS proExpDate";
						$sql .= " FROM dtMember AS m";
						$sql .= " INNER JOIN msCountry ON countryID = mbrCountry";
						$sql .= " INNER JOIN (";
						$sql .= " 	SELECT trProUsername, trProUserBeli, trProTransID, trProType, trProUpdateDate, trProActiveDate, trProStatus";
						$sql .= " 	FROM trProduct";
						$sql .= " 	INNER JOIN dtMember ON trProUserBeli = mbrUsername";
						$sql .= " 	WHERE (trProType = ? OR trProType = ?)";
						$sql .= " 	UNION";
						$sql .= " 	SELECT trProUsername, trProUserBeli, trProTransID, trProType, trProUpdateDate, trProActiveDate, trProStatus";
						$sql .= " 	FROM trProduct";
						$sql .= " 	INNER JOIN dtMember ON trProUsername = mbrUsername";
						$sql .= " 	WHERE (trProType != ? AND trProType != ?)";
						$sql .= " ) pro ON pro.trProUserBeli = mbrUsername";
						$sql .= " INNER JOIN trProDetail ON trPDTransID = trProTransID";
						$sql .= " INNER JOIN msProduct ON proID = trPDProID";
						$sql .= " WHERE trProStatus = ? ";

						if ($prodateFrom != "" && $prodateTo != "" ){
							$sql .= " AND date(trProUpdateDate) BETWEEN ? AND ?";
							array_push($arrDataParam, $prodateFrom, $prodateTo);
						}

						if($proSearch != ""){
							$sql .= " AND (mbrUsername LIKE ?";
							$sql .= " OR trProUserBeli LIKE ?";
							$sql .= " OR mbrFirstName LIKE ?";
							$sql .= " OR mbrLastName LIKE ?";
							$sql .= " OR mbrEmail LIKE ?";
							$sql .= " OR mbrSponsor LIKE ?";
							$sql .= " OR trProTransID LIKE ?)";
							array_push(
								$arrDataParam, 
								'%'.$proSearch.'%', 
								'%'.$proSearch.'%',
								'%'.$proSearch.'%', 
								'%'.$proSearch.'%', 
								'%'.$proSearch.'%', 
								'%'.$proSearch.'%',
								'%'.$proSearch.'%'
							);
						}
						foreach ($arrDataParam as &$value) {
			                $paramValue[] = $value;
			            }
						$stmt = $conn->prepare($sql);
						$types  = array(str_repeat('s', count($paramValue))); 
	        			$types = (implode(", ",$types));
	        			$stmt->bind_param($types, ...$paramValue);
						$stmt->execute();
						$result = $stmt->get_result();
						$totalRec = $result->num_rows;
						$numPerPage = $DEF_NUM_PER_PAGE;
						$numPages	= ceil ($totalRec / $numPerPage);
						$page = ($page<1)?1:$page;			
						$startRec = ($page-1) * $numPerPage;
						$secondLast = $numPages - 1;
						$sql .= " ORDER BY trProUpdateDate DESC LIMIT ".$startRec.", ".$numPerPage;

						$stmt = $conn->prepare($sql);
						$types  = array(str_repeat('s', count($paramValue))); 
	        			$types = (implode(", ",$types));
	        			$stmt->bind_param($types, ...$paramValue);
						$stmt->execute();
						$result = $stmt->get_result();
						
						?>
						<thead>
							<tr>
								<th>Username / Name</th>
								<th>Email / Mobile</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Order ID / Buyer</th>
								<th>Product</th>
								<th>Product Type</th>
								<th>Product Payment Date</th>
								<th>Product Expired Date</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						if ($result->num_rows == 0){
							echo "<tr><td colspan=9 class='text-center text-primary'>No Record</td></tr>";
						}
						while ($row=$result->fetch_assoc()){
							?>
							<tr>
								<td><?php echo $row['mbrUsername']."<br>".$row['mbrFullName']; ?></td>
								<td><?php echo $row['mbrEmail']."<br>".$row['mbrContact']; ?></td>
								<td><?php echo $row['countryDesc']; ?></td>
								<td><?php echo $row['mbrSponsor']; ?></td>
								<td><?php echo $row['trProTransID']."<br>".$row['trProUserBeli']; ?></td>
								<td><?php echo $row['proName']."<br>Qty : ".$row['trPDQty']; ?></td>
								<td><?php echo $row['trProType']; ?></td>
								<td><?php echo $row['trProUpdateDate']; ?></td>
								<td><?php echo $row['proExpDate']; ?></td>
							</tr>
						<?php
						} 
						?>
						</tbody>
					</table>
				</div>
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
						<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptPro&page='.$pagePrev.'&prodateFrom='.$prodateFrom.'&prodateTo='.$prodateTo.'&proSearch='.$proSearch); ?>">Previous</a></li>
						<?php
						if ($numPages <= 10){  	 
							for ($i = 1; $i <= $numPages; $i++){
								if ($i == $page) {
									echo "<li class='active'><a>$i</a></li>";
						        }else{
							        echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=$i&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>$i</a></li>";
				                }
					        }
						}else if ($numPages > 10){
							if ($page <= 4) {
								for ($i = 1; $i < 8; $i++){		 
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=$i&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>$i</a></li>";
									}
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=$secondLast&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=$numPages&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>$numPages</a></li>";
							}else if($page > 4 && $page < $numPages - 4) {		 
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=1&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>1</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=2&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=$i&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>$i</a></li>";
									}             
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=$secondLast&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=$numPages&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>$numPages</a></li>";
							}else{
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptPro&page=1&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>1</a></li>";
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptPro&page=2&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for($i = $numPages - 6; $i <= $numPages; $i++){
									if ($i == $page){
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptPro&page=$i&prodateFrom=$prodateFrom&prodateTo=$prodateTo&proSearch=$proSearch'>$i</a></li>";
									}                   
								}
							}
						}
						?>
						<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptPro&page='.$pageNext.'&prodateFrom='.$prodateFrom.'&prodateTo='.$prodateTo.'&proSearch='.$proSearch); ?>">Next</a></li>
						<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptPro&page='.$numPages.'&prodateFrom='.$prodateFrom.'&prodateTo='.$prodateTo.'&proSearch='.$proSearch); ?>">Last &rsaquo;&rsaquo;</a></li>
					</ul>
				</div> 
				<?php 
				}
				?>
			</div>
			<div id="rptExp" class="tab-pane fade in <?php echo setActive($sub, "rptExp"); ?>">
				<div class="table-responsive">
					<form method="GET" action="./">
						<input type="hidden" name="menu" value="report">
						<input type="hidden" name="subMenu" value="reportMember">
						<input type="hidden" name="sub" value="rptExp">
						<div class="row form-group">
							<div class="col-lg-12">&nbsp;</div>
							<div class="col-lg-2">Membership :
								<select class="form-control" name="st">
									<option value="6" <?php echo ($st == $DEF_STATUS_BLOCKED)?"selected":""; ?>>Expired</option>
									<option value="8" <?php echo ($st == $DEF_STATUS_ACTIVE)?"selected":""; ?>>Akan Expired</option>
								</select>
							</div>
							<div class="col-lg-4">Search : (Username, Name, Email, Sponsor)
								<input type="text" id="expSearch" name="expSearch" class="form-control" value="<?php echo $expSearch; ?>">
							</div>
							<div class="col-lg-2"><br>
								<button type="submit" class="btn btn-primary">Search</button>
							</div>
						</div>
					</form>
					<hr>
					<table class="table table-hover table-striped small" id="trptExp">
						<?php 
						$page = (isset($_GET['page']))?fValidateInput($_GET['page']): 1;
						$paramValue = array();
						$arrDataParam = array(
							$st
						);
						$sql = " SELECT mbrUsername, mbrFullName, mbrEmail, mbrContact, mbrSponsor, mbrExpDate, countryDesc, mbrStID";
						$sql .= " FROM ( ";
						$sql .= "	SELECT m.*, CONCAT(mbrFirstName,' ',mbrLastName) AS mbrFullName, CONCAT('+',countryMobileCode,'-',mbrMobile) AS mbrContact, ";
						$sql .= " 	DATE_ADD((mbrDate), INTERVAL ( (t.trThn) * 12) MONTH ) AS mbrExpDate, countryDesc";
						$sql .= " 	FROM dtMember AS m";
						$sql .= " 	INNER JOIN (";
						$sql .= " 		SELECT * FROM Transaction a ";
						$sql .= " 		WHERE trID = ( SELECT trID FROM Transaction WHERE trUsername=a.trUsername ORDER BY trDate DESC LIMIT 1)";
						$sql .= " 	) as t ON t.trUsername = mbrUsername";
						$sql .= " 	INNER JOIN msCountry ON countryID = mbrCountry";
						$sql .= " ) AS a";
						$sql .= " WHERE mbrStID = ?";
						if ($st == $DEF_STATUS_ACTIVE ){
							$sql .= " AND date(mbrExpDate) BETWEEN ? AND ?";
							$tgl1 	= date_create($CURRENT_TIME);
							$tgl1 	= date_format($tgl1, "Y-m-d");
							$tgl2 		= date_create($CURRENT_TIME);
							date_add($tgl2, date_interval_create_from_date_string("3 month"));
							$tgl2 = date_format($tgl2, "Y-m-d");
							array_push($arrDataParam, $tgl1, $tgl2);
						}else if ($st == $DEF_STATUS_BLOCKED){
							$sql .= " AND date(mbrExpDate) > ?";
							$tgl1 		= date_create($CURRENT_TIME);
							date_add($tgl1, date_interval_create_from_date_string("-1 year"));
							$tgl1 = date_format($tgl1, "Y-m-d");
							array_push($arrDataParam, $tgl1);
						}

						if($expSearch != ""){
							$sql .= " AND (mbrUsername LIKE ?";
							$sql .= " OR mbrFirstName LIKE ?";
							$sql .= " OR mbrLastName LIKE ?";
							$sql .= " OR mbrEmail LIKE ?";
							$sql .= " OR mbrSponsor LIKE ?)";
							array_push($arrDataParam, '%'.$expSearch.'%', '%'.$expSearch.'%', '%'.$expSearch.'%', '%'.$expSearch.'%', '%'.$expSearch.'%');
						}
						foreach ($arrDataParam as &$value) {
			                $paramValue[] = $value;
			            }
						$stmt = $conn->prepare($sql);
						$types  = array(str_repeat('s', count($paramValue))); 
	        			$types = (implode(", ",$types));
	        			$stmt->bind_param($types, ...$paramValue);
						$stmt->execute();
						$result = $stmt->get_result();
						$totalRec = $result->num_rows;
						$numPerPage = $DEF_NUM_PER_PAGE;
						$numPages	= ceil ($totalRec / $numPerPage);
						$page = ($page<1)?1:$page;			
						$startRec = ($page-1) * $numPerPage;
						$secondLast = $numPages - 1;
						$sql .= " ORDER BY mbrExpDate ASC LIMIT ".$startRec.", ".$numPerPage;
						$stmt = $conn->prepare($sql);
						$types  = array(str_repeat('s', count($paramValue))); 
	        			$types = (implode(", ",$types));
	        			$stmt->bind_param($types, ...$paramValue);
						$stmt->execute();
						$result = $stmt->get_result();
						?>
						<thead>
							<tr>
								<th>Username / Name</th>
								<th>Email / Mobile</th>
								<th>Country</th>
								<th>Sponsor</th>
								<th>Expired Date</th>
								<!-- <th>Status</th> -->
							</tr>
						</thead>
						<tbody>
						<?php
						if ($result->num_rows == 0){
							echo "<tr><td colspan=5 class='text-center text-primary'>No Record</td></tr>";
						}
						while ($row=$result->fetch_assoc()){
							$mbrExpDate = date_create($row['mbrExpDate']);
							$mbrExpDate = date_format($mbrExpDate, "l, F j, Y")
						?>
							<tr>
								<td><?php echo $row['mbrUsername']."<br>".$row['mbrFullName']; ?></td>
								<td><?php echo $row['mbrEmail']."<br>".$row['mbrContact']; ?></td>
								<td><?php echo $row['countryDesc']; ?></td>
								<td><?php echo $row['mbrSponsor']; ?></td>
								<td><?php echo $mbrExpDate; ?></td>
								<!-- <td><?php echo $row['mbrStID']; ?></td> -->
							</tr>
						<?php 
						}
						?>
						</tbody>
					</table>
				</div>
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
						<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptExp&st='.$st.'&page='.$pagePrev.'&expSearch='.$expSearch); ?>">Previous</a></li>
						<?php
						if ($numPages <= 10){  	 
							for ($i = 1; $i <= $numPages; $i++){
								if ($i == $page) {
									echo "<li class='active'><a>$i</a></li>";
						        }else{
							        echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=$i&expSearch=$expSearch'>$i</a></li>";
				                }
					        }
						}else if ($numPages > 10){
							if ($page <= 4) {
								for ($i = 1; $i < 8; $i++){		 
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=$i&expSearch=$expSearch'>$i</a></li>";
									}
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=$secondLast&expSearch=$expSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=$numPages&expSearch=$expSearch'>$numPages</a></li>";
							}else if($page > 4 && $page < $numPages - 4) {		 
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=1&expSearch=$expSearch'>1</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=2&expSearch=$expSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=$i&expSearch=$expSearch'>$i</a></li>";
									}             
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=$secondLast&expSearch=$expSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=$numPages&expSearch=$expSearch'>$numPages</a></li>";
							}else{
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=1&expSearch=$expSearch'>1</a></li>";
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=2&expSearch=$expSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for($i = $numPages - 6; $i <= $numPages; $i++){
									if ($i == $page){
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptExp&st=$st&page=$i&expSearch=$expSearch'>$i</a></li>";
									}                   
								}
							}
						}
						?>
						<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptExp&st='.$st.'&page='.$pageNext.'&expSearch='.$expSearch); ?>">Next</a></li>
						<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptExp&st='.$st.'&page='.$numPages.'&expSearch='.$expSearch); ?>">Last &rsaquo;&rsaquo;</a></li>
					</ul>
				</div> 
				<?php 
				}
				?>
			</div>
		</div>
	</div>
</div>