<?php 
include_once("../includes/inc_def.php");
include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$menu 		= (isset($_GET['menu']))?fValidateInput($_GET['menu']):"report";
$subMenu 	= (isset($_GET['subMenu']))?fValidateInput($_GET['subMenu']):"reportBugs";
$sub 		= (isset($_GET['sub']))?fValidateInput($_GET['sub']):"rptAndroid";
$st 		= (isset($_GET['st']))?fValidateInput($_GET['st']):$DEF_STATUS_UNREAD;
$androSearch 	= (isset($_GET['androSearch']))?fValidateInput($_GET['androSearch']):"";
$page = (isset($_GET['page']))?fValidateInput($_GET['page']): 1;
?>
<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Bug's Report</div>
		<ul class="nav nav-pills nav-tabs">
			<li class="<?php echo setActive($sub, "rptAndroid"); ?>"><a data-toggle="tab" href="#rptAndroid">Android</a></li>
		</ul>
		<div class="tab-content">
			<div id="rptAndroid" class="tab-pane fade in <?php echo setActive($sub, "rptAndroid"); ?>">
				<div class="table-responsive">
					<form method="GET" action="./">
						<input type="hidden" name="menu" value="report">
						<input type="hidden" name="subMenu" value="reportBugs">
						<input type="hidden" name="sub" value="rptAndroid">
						<div class="row form-group">
							<div class="col-lg-12">&nbsp;</div>
							<div class="col-lg-2">Status : 
								<select class="form-control" name="st">
									<option value="<?php echo $DEF_STATUS_UNREAD; ?>" <?php echo ($st==$DEF_STATUS_UNREAD)?"selected":""; ?>>Unread</option>
									<option value="<?php echo $DEF_STATUS_READ; ?>" <?php echo ($st==$DEF_STATUS_READ)?"selected":""; ?>>Read</option>
									<option value="<?php echo $DEF_STATUS_REPLIED; ?>" <?php echo ($st==$DEF_STATUS_REPLIED)?"selected":""; ?>>Replied</option>
								</select>
							</div>
							<div class="col-lg-4">Search : (Username)
								<input type="text" id="androSearch" name="androSearch" class="form-control" value="<?php echo $androSearch; ?>">
							</div>
							<div class="col-lg-2"><br>
								<button type="submit" class="btn btn-primary">Search</button>
							</div>
						</div>
					</form>
					<hr>
					<table class="table table-hover table-striped small" id="tRptAndroid">
						<?php
						$paramValue = array();
						$arrDataParam = array(
							$st
						);
						$sql  = " SELECT * FROM dtBugReport";
						$sql .= " WHERE bugStatus = ?";
						if ($androSearch != ""){
							$sql .= " AND mbrUsername LIKE ?";
							array_push($arrDataParam, '%'.$androSearch.'%');
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
						$numPerPage = 10;
						$numPages	= ceil ($totalRec / $numPerPage);
						$page = ($page<1)?1:$page;			
						$startRec = ($page-1) * $numPerPage;
						$secondLast = $numPages - 1;
						$sql .= " ORDER BY bugDate DESC LIMIT ".$startRec.", ".$numPerPage;
						$stmt = $conn->prepare($sql); 
						$types  = array(str_repeat('s', count($paramValue))); 
	        			$types = (implode(", ",$types));
	        			$stmt->bind_param($types, ...$paramValue);
						$stmt->execute();
						$result = $stmt->get_result();
						?>
						<thead>
							<tr>
								<th>Username</th>
								<th>OS</th>
								<th>Device</th>
								<th>Menu</th>
								<th>Description</th>
								<th>Date Created</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ($result->num_rows == 0){
								echo "<tr><td colspan=8 class='text-center text-primary'>No Record</td></tr>";
							}
							while ($row=$result->fetch_assoc()){
								if ($row['bugStatus'] == $DEF_STATUS_UNREAD){
									$bugSt = "<span class='text-danger'>Unread</span>";
								}else if ($row['bugStatus'] == $DEF_STATUS_READ){
									$bugSt = "<span class='text-secondary'>Read</span>";
								}else if ($row['bugStatus'] == $DEF_STATUS_REPLIED){
									$bugSt = "<span class='text-success'>Replied</span>";
								}
							?>
							<tr>
								<td><?php echo $row['mbrUsername']; ?></td>
								<td><?php echo $row['bugOS']; ?></td>
								<td><?php echo $row['bugDevice']; ?></td>
								<td><?php echo $row['bugMenu']; ?></td>
								<td><?php echo $row['bugDesc']; ?></td>
								<td><?php echo $row['bugDate']; ?></td>
								<td><?php echo $bugSt; ?></td>
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
						<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptAndroid&page='.$pagePrev.'&androSearch='.$androSearch); ?>">Previous</a></li>
						<?php
						if ($numPages <= 10){  	 
							for ($i = 1; $i <= $numPages; $i++){
								if ($i == $page) {
									echo "<li class='active'><a>$i</a></li>";
						        }else{
							        echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=$i&androSearch=$androSearch'>$i</a></li>";
				                }
					        }
						}else if ($numPages > 10){
							if ($page <= 4) {
								for ($i = 1; $i < 8; $i++){		 
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=$i&androSearch=$androSearch'>$i</a></li>";
									}
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=$secondLast&androSearch=$androSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=$numPages&androSearch=$androSearch'>$numPages</a></li>";
							}else if($page > 4 && $page < $numPages - 4) {		 
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=1&androSearch=$androSearch'>1</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=2&androSearch=$androSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=$i&androSearch=$androSearch'>$i</a></li>";
									}             
								}
								echo "<li class='disabled'><a>...</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=$secondLast&androSearch=$androSearch'>$secondLast</a></li>";
								echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=$numPages&androSearch=$androSearch'>$numPages</a></li>";
							}else{
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=1&androSearch=$androSearch'>1</a></li>";
								echo "<li><a href='?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=2&androSearch=$androSearch'>2</a></li>";
								echo "<li class='disabled'><a>...</a></li>";
								for($i = $numPages - 6; $i <= $numPages; $i++){
									if ($i == $page){
										echo "<li class='active'><a>$i</a></li>";	
									}else{
										echo "<li><a href='./?menu=$menu&subMenu=$subMenu&sub=rptAndroid&page=$i&androSearch=$androSearch'>$i</a></li>";
									}                   
								}
							}
						}
						?>
						<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptAndroid&page='.$pageNext.'&androSearch='.$androSearch); ?>">Next</a></li>
						<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu='.$subMenu.'&sub=rptAndroid&page='.$numPages.'&androSearch='.$androSearch); ?>">Last &rsaquo;&rsaquo;</a></li>
					</ul>
				</div> 
				<?php 
				}
				?>
			</div>
		</div>
	</div>
</div>