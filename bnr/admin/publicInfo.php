<?PHP
$q = (isset($_GET["q"]))?$_GET["q"]: "";


$subject = $headline = $desc = "";
if (!empty($_POST)) { 
	include_once("../includes/inc_def.php");
	include_once("../includes/inc_session_admin.php");
	include_once("../includes/inc_conn.php");
	include_once("../includes/inc_functions.php");

	$subject = isset($_POST['subject'])?fValidateSQLFromInput($conn, $_POST["subject"]) : "";
	$headline = isset($_POST['headline'])?fValidateSQLFromInput($conn, $_POST["headline"]) : "";
	$desc = isset($_POST['desc'])?fValidateSQLFromInput($conn, $_POST["desc"]) : "";

	if ($subject != "" && $headline != "" && $desc != ""){
		$arrData = array(
			0 => array ("db" => "piSubject" , "val" => $subject),
			1 => array ("db" => "piHeadLine"  , "val" => $headline),
			2 => array ("db" => "piDesc"   , "val" => $desc),
			3 => array ("db" => "piStatus"   , "val" => $DEF_STATUS_APPROVED),
			4 => array ("db" => "piDate"    , "val" => "CURRENT_TIME()")
		);

		if (!fInsert("dtPublicInfo", $arrData, $conn)) {
			echo (fSendStatusMessage("error", "<b>PublicInfo - </b>" . $conn->error));
			die();
		}else{
			//success
			$msg = "Data saved";
			header("Location: ./?menu=announcement&q=".$msg);
		}

		unset($arrData);

	}

}

?>
<?php
$page = (isset($_GET['page']))? $_GET['page'] : 1;
$pageActive = (isset($_GET['pageActive']))? $_GET['pageActive'] : 1;
$numPerPage = $DEF_NUM_PER_PAGE;


//$menu = (isset($_GET['menu']))? $_GET['menu'] : "";
$subMenu = (isset($_GET['subMenu']))? $_GET['subMenu'] : "pending";

?>
<span id="q" title="<?php echo $q; ?>"></span>
<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Announcement</div>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "pending")); ?>"><a data-toggle="tab" href="#pending">Add Announcement</a></li>
			<li class="<?php echo (setActive($subMenu, "active")); ?>"><a data-toggle="tab" href="#active">List of Announcement</a></li>
			<!--  <li class="<?php //echo (setActive($subMenu, "blocked")); ?>"><a data-toggle="tab" href="#blocked">Blocked / Declined</a></li> -->
		</ul>
		<div class="tab-content">
			<div id="pending" class="tab-pane fade in <?php echo (setActive($subMenu, "pending")); ?>">
				<h3>Add Announcement</h3>
				<?php 
				if ($q != "") echo ("<div class='row text-success text-center'><h2>" . $q . "</h2></div>");
				?>
				<form method="POST" action="publicInfo.php" name="formAdd">
					<div class="card">
						<div class="card-body card-fix">
							<div class="form-group col-md-12 col-sm-12">
								<div class="col-md-2">Subject</div>
								<div class="col-md-10"><input type="text" name="subject" id="subject" maxlength="100" title="max 100" class="form-control col-md-12"></div>
							</div>
							<div class="form-group col-md-12 col-sm-12">
								<div class="col-md-2">Headline</div>
								<div class="col-md-10"><textarea name="headline" id="headline" rows=5 maxlength="500" title="max 500" class="form-control col-md-12"></textarea></div>
							</div>
							<div class="form-group col-sm-12">
								<div class="col-md-2">Description</div>
								<div class="col-md-10"><textarea name="desc" id="desc" rows=10 maxlength="1000" title="Max 1000" class="form-control col-md-12"></textarea></div>
							</div>
							<div class="form-group col-sm-12">
								<div class="col-md-2">&nbsp;</div>
								<div class="col-md-5"><button id="add" name="add" class="form-control btn-primary">Add Announcement</button></div>

							</div>
						</div>
					</div>
				</form>
			</div>
			<div id="active" class="tab-pane fade in <?php echo (setActive($subMenu, "active")); ?>" >
				<h3>List of Announcement</h3>
				<!-- Active -->
				<?php 
				$sql = "SELECT count(*) totalRec FROM dtPublicInfo";
				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages	= ceil ($totalRec / $numPerPage);	
				$pageActive = ($pageActive<1)?1:$pageActive;		
				$startRec = ($pageActive-1) * $numPerPage;
				//echo "page: " . $totalRec;
				$sql  = "SELECT pi.*, s.stDesc FROM dtPublicInfo pi INNER JOIN msStatus s ON piStatus=stID ORDER BY piDate DESC";
				$sql  .= " limit " . $startRec . ", " . $numPerPage;

				$queryApproved = $conn->query($sql);
				?>
				<div >
					<table class="table table-hover table-striped small">
						<thead>
							<tr>
								<th>ID</th>
								<th>Date</th>
								<th>Subject</th>
								<th>Headline</th>
								<th>Status</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>ID</th>
								<th>Date</th>
								<th>Subject</th>
								<th>Headline</th>
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
									<td><?php echo $rowApproved["piID"] ?></td>
									<td><?php echo $rowApproved["piDate"] ?></td>
									<td><?php echo $rowApproved["piSubject"] ?></td>
									<td><?php echo ($rowApproved["piHeadLine"]); ?></td>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&pageActive=' . $pagePrev); ?>">Previous</a></li>
							<?php 
							for ($i=1; $i<=$numPages; $i++){
								$active = "";
								if ($i == $pageActive) $active = "active";
								echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=active&pageActive=$i'>$i</a></li>";
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&pageActive=' . $pageNext); ?>">Next</a></li>
						</ul>&nbsp;&nbsp;&nbsp;&nbsp;
					</div>  
				</div>
			</div>
			<div id="blocked" class="tab-pane fade in <?php echo (setActive($subMenu, "blocked")); ?>">
				<h3>Blocked</h3>
				<!--- Declined -->
				<?php 
				$sql 	= "SELECT count(*) totalRec FROM dtFundIn inner join msStatus on finStatus=stID WHERE finStatus ='" . $DEF_STATUS_DECLINED . "'";

				$query = $conn->query($sql);
				$row = $query->fetch_assoc();
				$totalRec = $row['totalRec'];
				$numPages	= ceil ($totalRec / $numPerPage);	
				$pageActive = ($pageActive<1)?1:$pageActive;				
				$startRec = ($pageActive-1) * $numPerPage;

				$sql 	= "SELECT finDate, finAccType, finFromAccNo, finAmount, finTransactionID, stDesc FROM dtFundIn inner join msStatus on finStatus=stID WHERE finStatus ='" . $DEF_STATUS_DECLINED . "'";
				$queryDeclined = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped">
						<thead>
							<tr>
								<th>Date</th>
								<th>Account Type</th>
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
								<th>ID/Email/Addr</th>
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
									<td><?php echo $rowDeclined["finDate"] ?></td>
									<td><?php echo $rowDeclined["finAccType"] ?></td>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=blocked&pageActive=' . $pagePrev); ?>">Previous</a></li>
							<?php 
							for ($i=1; $i<=$numPages; $i++){
								$active = "";
								if ($i == $pageActive) $active = "active";
								echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=blocked&pageActive=$i'>$i</a></li>";
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=blocked&pageActive=' . $pageNext); ?>">Next</a></li>
						</ul>&nbsp;&nbsp;&nbsp;&nbsp;
					</div>  
				</div>
			</div>
		</div>
	</div>
</div>