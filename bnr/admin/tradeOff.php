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
			<li class="<?php echo (setActive($subMenu, "tradeoff")); ?>"><a data-toggle="tab" href="#tradeoff">Trade Off (Conn, Autotrade, All History, Investor, Auth)</a></li>
		</ul>
		<div class="tab-content">
			<!-- Trade Off -->
			<div id="tradeoff" class="tab-pane fade in <?php echo (setActive($subMenu, "tradeoff")); ?>" >
				<?php 
				$sqlWhere = "";
				$searchOff = (isset($_GET['searchOff']))?fValidateSQLFromInput($conn, $_GET['searchOff']): '';
				if ($searchOff != ""){
					$sqlWhere  = " AND (mbrUsername LIKE '%".$searchOff."%' ";
					$sqlWhere .= " OR EAName LIKE '%".$searchOff."%' ";
					$sqlWhere .= " OR seaAcc LIKE '%".$searchOff."%' ";
					$sqlWhere .= " OR tradeAccPasswd LIKE '%".$searchOff."%' ";
					$sqlWhere .= " OR vpshost LIKE '%".$searchOff."%') ";
				}
				?>
				<hr>
				<form action="./" method="GET">
					<input type="hidden" name="menu" value="<?php echo $menu; ?>">
					<input type="hidden" name="subMenu" value="<?php echo $subMenu; ?>">
					<div class="row">
						<div class="form-group col-lg-3 col-md-3">
							<label for="searchOff">Search</label>
							<input type="text" class="form-control" name="searchOff" id="searchOff" value="<?php echo $searchOff; ?>">
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
				$sql  = " SELECT COUNT(*) totalRec FROM dtStateEA sea";
				$sql .= " INNER JOIN dtTradingAcc ON seaEA=tradeEANum AND seaPair=tradePair AND seaAcc = tradeAccNo ";
				$sql .= " INNER JOIN msEA ON EAID=seaEA ";
				$sql .= " INNER JOIN dtVPS ON vpsID=tradeVPS ";
				$sql .= " INNER JOIN msPair ON pairID = seaPair ";
				$sql .= " INNER JOIN dtMember ON mbrUsername = tradeUsername ";
				$sql .= " WHERE (seaConn = '0' OR seaAutoTrade = '0' OR seaAllHistory = '0' OR seaInvestor = '1' OR seaAuth = '0' OR DATE_ADD(seaUpdateDate, INTERVAL 1 DAY) < CURRENT_TIME() ) ";
				$sql .= " AND tradeStID ='".$DEF_STATUS_ACTIVE."' AND mbrStID ='".$DEF_STATUS_ACTIVE."'";
				$sql .= $sqlWhere;
				$result = $conn->query($sql);
				$row = $result->fetch_assoc();
				$totalRec = $row['totalRec'];


				$sql  = " SELECT sea.*, EAName, pairName, vpshost, mbrUsername, tradeAccPasswd, ";
				$sql .= " IF(DATE_ADD(seaUpdateDate, INTERVAL 1 DAY) < CURRENT_TIME(), 'EA OFF', 'EA ON') AS isEAWork, DATEDIFF(CURRENT_TIME, seaUpdateDate) durationOff, NOW() curr FROM dtStateEA sea ";
				$sql .= " INNER JOIN dtTradingAcc ON seaEA=tradeEANum AND seaPair=tradePair AND seaAcc = tradeAccNo ";
				$sql .= " INNER JOIN msEA ON EAID=seaEA ";
				$sql .= " INNER JOIN dtVPS ON vpsID=tradeVPS ";
				$sql .= " INNER JOIN msPair ON pairID = seaPair ";
				$sql .= " INNER JOIN dtMember ON mbrUsername = tradeUsername ";
				$sql .= " WHERE (seaConn = '0' OR seaAutoTrade = '0' OR seaAllHistory = '0' OR seaInvestor = '1' OR seaAuth = '0' OR DATE_ADD(seaUpdateDate, INTERVAL 1 DAY) < CURRENT_TIME() ) ";
				$sql .= " AND tradeStID ='".$DEF_STATUS_ACTIVE."' AND mbrStID ='".$DEF_STATUS_ACTIVE."'";
				$sql .= $sqlWhere;
				$sql .= " ORDER BY durationOff ASC";
				$numPerPage = $DEF_NUM_PER_PAGE;
				$numPages	= ceil ($totalRec / $numPerPage);
				$page = ($page<1)?1:$page;			
				$startRec = ($page-1) * $numPerPage;
				$secondLast = $numPages - 1;
				$sql .= " LIMIT " . $startRec . ", " . $numPerPage;
				$result = $conn->query($sql);
				?>
				<div>
					<table class="table table-hover table-striped small" id="tTradeoff">
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
						if ($result->num_rows == 0){
								echo "<tr><td colspan=11 class='text-center text-primary'>no record</td></tr>";  
						}
						while ($row = $result->fetch_assoc()){
						?>
							<tr>
								<td><?php echo ($row["EAName"] . "<br>" . $row["pairName"]); ?></td>
								<td><?php echo ($row["mbrUsername"] . "<br>" . $row["seaAcc"]); ?></td>
								<td><?php echo ($row["vpshost"]. "<br>" . $row["tradeAccPasswd"]); ?></td>
								<td><?php echo ($row["seaConn"]); ?></td>
								<td><?php echo ($row["seaAutoTrade"]); ?></td>
								<td><?php echo ($row["seaAllHistory"]); ?></td>
								<td><?php echo ($row["seaInvestor"]); ?></td>
								<td><?php echo ($row["seaAuth"]); ?></td>
								<td><?php echo ($row["durationOff"]); ?></td>
								<td><?php echo ($row["seaUpdateDate"] . "<br>" . $row["curr"]); ?></td>
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
							<li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=tradeoff&searchOff='.$searchOff.'&page='.$pagePrev); ?>">Previous</a></li>
							<?php
							if ($numPages <= 10){  	 
								for ($i = 1; $i <= $numPages; $i++){
									if ($i == $page) {
										echo "<li class='active'><a>$i</a></li>";
							        }else{
								        echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=$i'>$i</a></li>";
					                }
						        }
							}else if ($numPages > 10){
								if ($page <= 4) {
									for ($i = 1; $i < 8; $i++){		 
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=$i'>$i</a></li>";
										}
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=$numPages'>$numPages</a></li>";
								}else if($page > 4 && $page < $numPages - 4) {		 
									echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=1'>1</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++){		
										if ($i == $page) {
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=$i'>$i</a></li>";
										}             
									}
									echo "<li class='disabled'><a>...</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=$secondLast'>$secondLast</a></li>";
									echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=$numPages'>$numPages</a></li>";
								}else{
									echo "<li><a href='?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=1'>1</a></li>";
									echo "<li><a href='?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=2'>2</a></li>";
									echo "<li class='disabled'><a>...</a></li>";
									for($i = $numPages - 6; $i <= $numPages; $i++){
										if ($i == $page){
											echo "<li class='active'><a>$i</a></li>";	
										}else{
											echo "<li><a href='./?menu=$menu&subMenu=tradeoff&searchOff=$searchOff&page=$i'>$i</a></li>";
										}                   
									}
								}
							}
							?>
							<li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=tradeoff&searchOff='.$searchOff.'&page='.$pageNext); ?>">Next</a></li>
							<li class="<?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=tradeoff&searchOff='.$searchOff.'&page='.$numPages); ?>">Last &rsaquo;&rsaquo;</a></li>
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