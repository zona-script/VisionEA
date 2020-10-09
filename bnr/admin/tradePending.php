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
	});
</script>

<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Trading Account</div>
		<span id="q" title="" class="text-center text-success"><b><?php echo $q; ?></b></span>
		<ul class="nav nav-tabs">
			<li class="<?php echo (setActive($subMenu, "pending")); ?>"><a data-toggle="tab" href="#pending">Stopped / Pending</a></li>
		</ul>
		<div class="tab-content">
			<!-- Pending -->
			<div id="pending" class="tab-pane fade in <?php echo (setActive($subMenu, "pending")); ?>">
				<?php 
				$sqlWhere = "";
				$searchPending = (isset($_GET['searchPending']))?fValidateSQLFromInput($conn, $_GET['searchPending']): '';
				if ($searchPending != ""){
					$sqlWhere  = " AND (tradeUsername LIKE '%".$searchPending."%' ";
					$sqlWhere .= " OR tradeName LIKE '%".$searchPending."%' ";
					$sqlWhere .= " OR tradeAccNo LIKE '%".$searchPending."%' ";
					$sqlWhere .= " OR tradeServer LIKE '%".$searchPending."%') ";
				}
				?>
				<hr>
				<form action="./" method="GET">
					<input type="hidden" name="menu" value="<?php echo $menu; ?>">
					<input type="hidden" name="subMenu" value="<?php echo $subMenu; ?>">
					<div class="row">
						<div class="form-group col-lg-3 col-md-3">
							<label for="searchPending">Search</label>
							<input type="text" class="form-control" name="searchPending" id="searchPending" value="<?php echo $searchPending; ?>">
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
				$sql .= " WHERE tradeID = (";
				$sql .= "     SELECT tradeID FROM dtTradingAcc ";
				$sql .= "     WHERE tradeUsername = acc.tradeUsername";
				$sql .= "     AND tradeStID ='".$DEF_STATUS_PENDING."'";
				$sql .= "     ORDER BY tradeDate DESC";
				$sql .= "     LIMIT 1";
				$sql .= "     )";
				$sql .= " AND tradeStID ='".$DEF_STATUS_PENDING."'";
				$sql .= $sqlWhere;
				$result = $conn->query($sql);
				$row = $result->fetch_assoc();
				$totalRec = $row['totalRec'];

				$sql  = " Select acc.*, s.stDesc, trPacID, pacName FROM dtTradingAcc acc";
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
				$sql .= " WHERE tradeID = (";
				$sql .= "     SELECT tradeID FROM dtTradingAcc ";
				$sql .= "     WHERE tradeUsername = acc.tradeUsername";
				$sql .= "     AND tradeStID ='".$DEF_STATUS_PENDING."'";
				$sql .= "     ORDER BY tradeDate DESC";
				$sql .= "     LIMIT 1";
				$sql .= "     )";
				$sql .= " AND tradeStID ='".$DEF_STATUS_PENDING."'";
				$sql .= $sqlWhere;
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
						if ($result->num_rows == 0){
								echo "<tr><td colspan=6 class='text-center text-primary'>no record</td></tr>";  
						}
						while ($row = $result->fetch_assoc()){
						?>
							<tr>
								<td><?php echo $row["tradeUsername"] ?></td>
								<td><?php echo $row["tradeName"] ?></td>
								<td><?php echo $row["tradeAccNo"] ?></td>
								<td><?php echo $row["tradeServer"]?></td>
								<td><?php echo $row["pacName"] ?></td>
								<td><?php echo $row["stDesc"] ?></td>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pending&searchPending='.$searchPending.'&page='.$pagePrev); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=$i'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=$i'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=$numPages'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=1'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=$i'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=$numPages'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=pending&searchPending=$searchPending&page=1'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=pending&searchPending=$searchPending&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=pending&searchPending=$searchPending&page=$i'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pending&searchPending='.$searchPending.'&page='.$pageNext); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=pending&searchPending='.$searchPending.'&page='.$numPages); ?>">Last &rsaquo;&rsaquo;</a></li>
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