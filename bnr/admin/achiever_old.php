<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$q = (isset($_GET["q"]))?$_GET["q"]: "";

$subject = $headline = $desc = "";
if (!empty($_POST)) { 
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
$subMenu = (isset($_GET['subMenu']))? $_GET['subMenu'] : "active";

?>
<script type="text/javascript">
	$(document).ready(function(e) {
		$('#tArchiever').DataTable();
	});
</script>
<span id="q" title="<?php echo $q; ?>"></span>
<div class="col-sm-12">
	<div class="well">
		<div class="subTitle">Achiever Rewards</div>
			<div class="tab-content">
				<div id="active" class="tab-pane fade in <?php echo (setActive($subMenu, "active")); ?>">
					<h3>Bronze</h3>
					<?php
					$txtSearch = isset($_GET['txtSearch'])?$_GET['txtSearch']: '';
					$sqlWhere = "";
					if ($txtSearch != ""){
						$sqlWhere .= " AND (pairTORUsername like '%" . $txtSearch . "%' ";
					    $sqlWhere .= " OR mbrFirstName like '%" . $txtSearch . "%' ";
					    $sqlWhere .= " OR mbrEmail like '%" . $txtSearch . "%' )";
					}
					$sRank = isset($_GET['sRank'])?$_GET['sRank']: "";
					if ($sRank != ""){
						$sql  = "SELECT * FROM msReward";
						$sql .= " WHERE rwdID = '".$sRank."' ";
						$result = $conn->query($sql);
						if ($row=$result->fetch_assoc()){
							$rwdLeft 	= $row['rwdLeft'];
							$rwdRight 	= $row['rwdRight'];
							$rwdDirect 	= $row['rwdDirect'];
							$rwdL1 		= $row['rwdL1'];
							$rwdL2 		= $row['rwdL2'];
							$sqlWhere .= " AND tLeft >= '".$rwdLeft."'";
							$sqlWhere .= " AND tRight >= '".$rwdRight."'";
							$sqlWhere .= " AND DirectTo >= '".$rwdDirect."'";
							// $sqlWhere .= "AND NetLeft = '".$rwdL1."'";
							// $sqlWhere .= "AND NetLeft = '".$rwdL2."'";
						}
							
					}
					?>
					<form action="./" method="GET">
						<div class="row">
	                		<div class="col-md-3">
	                			<input type="hidden" name="menu" value="<?php echo $menu; ?>">
								<input type="hidden" name="subMenu" value="<?php echo $subMenu; ?>">
								<select name="sRank" class="form-control">
									<option disabled selected>Chose Rank</option>
			                    <?php
				                    $sql  = "SELECT * FROM msReward";
				                    $sql .= " ORDER BY rwdID ASC";
				                    $result = $conn->query($sql);
				                    while ($row=$result->fetch_assoc()){
				                    	$selected = ($sRank==$row['rwdID'])? " SELECTED": "";
				                    	echo ("<option value='".$row['rwdID']."' $selected>".$row['rwdName']."</option>");
				                    }
			                    ?>
			                    </select>
			                </div>
			                <div class="col-md-3">
			                	<input type="text" class="form-control" name="txtSearch" value="<?php echo ($txtSearch); ?>" placeholder="Username, FirstName, Email">
			                </div>
							<div class="col-md-4">
								<button type="submit">Search</button>
							</div>
						</div>
					</form>
					<?php 
					global $DEF_STATUS_ACTIVE, $DEF_STATUS_NEW;
					// $sql = "SELECT COUNT(*) as totalRec FROM ( ";
					// $sql .= " SELECT pairTORUsername FROM dtDailyTORewards ";
					// $sql .= " LEFT JOIN (SELECT m.* FROM dtMember m";
					// $sql .= "       INNER JOIN (SELECT * FROM Transaction as t WHERE trID = (SELECT trID FROM Transaction WHERE trUsername=t.trUsername ORDER BY trDate DESC LIMIT 1) ) as t ON m.mbrUsername=trUsername ";
					// $sql .= "       INNER JOIN dtMember sp ON sp.mbrUsername=m.mbrSponsor";
					// $sql .= " 				WHERE ( DATE(m.mbrDate) >= '2019.11.01' AND YEAR(m.mbrDate) <= '2020' AND (YEAR(CURRENT_DATE) = 2019 OR YEAR(CURRENT_DATE) = 2020) ) "; //masih menghitung omset 1nov 2019 dan 2020 dlm 1 thn omset yang sama
					// $sql .= " 				  		OR ( DATE(m.mbrDate) >= '2019.11.01' AND YEAR(m.mbrDate) = YEAR(CURRENT_DATE) ) "; //Mulai thn 2021, omset dihitung per tahun (tahun sblmnya tidak dihitung dlm omset direct)
					// $sql .= "       ) AS t ON mbrSponsor=pairTORUsername";
					// $sql .= "       INNER JOIN dtMember sp ON sp.mbrUsername=pairTORUsername";
					// //$sql .= " WHERE wkYearWeek >= '201944'";  //bisa tambahkan pairTORUsername ='......' pada bagian member area
					// $sql .= " WHERE Date(pairTORDate) >= '2019.11.01'";
					// $sql .= " GROUP BY pairTORUsername";
					// //$sql .= " HAVING SUM(pairTORLeft) >= 1000 AND SUM(pairTORRight) >= 1000";
					// //$sql .= " AND pairTORUsername !='visionea'";
					// $sql  .= " HAVING pairTORUsername !='visionea'";
					// $sql .= " ) as a ";
					// //echo $sql;
					// $query = $conn->query($sql);
					// $row = $query->fetch_assoc();
					// $totalRec = $row['totalRec'];

					// $numPages = ceil ($totalRec / $numPerPage); 
					// echo $numPages;
					// $pageActive = ($pageActive<1)?1:$pageActive;    
					// $startRec = ($pageActive-1) * $numPerPage;

					$sql  = "SELECT pairTORUsername, mbrFirstName, mbrLastName, mbrEmail, tLeft AS NetLeft, tRight AS NetRight";
					$sql .= " , IF(tLeft > tRight, tRight, tLeft) AS BalanceTO, IFNULL(directTO, 0) as directTO ";
					$sql .= " FROM (";
					$sql .= "  SELECT pairTORUsername, mbrFirstName, mbrLastName, mbrEmail";
					$sql .= ", SUM(pairTORLeft) as tLeft, SUM(pairTORRight) as tRight, directTO FROM dtDailyTORewards ";
					//Direct Sponsor
					$sql .= " LEFT JOIN (SELECT SUM(pacPrice) AS directTO, m.mbrSponsor FROM dtMember m";
					$sql .= "       INNER JOIN (SELECT * FROM Transaction as t WHERE trID = (SELECT trID FROM Transaction WHERE trUsername=t.trUsername ORDER BY trDate DESC LIMIT 1) AND trStatus='" . $DEF_STATUS_NEW . "') as t ON m.mbrUsername=trUsername ";
					$sql .= "       INNER JOIN dtMember sp ON sp.mbrUsername=m.mbrSponsor";
					$sql .= "       INNER JOIN msPackage ON pacID=trPacID ";
					$sql .= " 				WHERE ( DATE(m.mbrDate) >= '2019.11.01' AND YEAR(m.mbrDate) <= '2020' AND (YEAR(CURRENT_DATE) = 2019 OR YEAR(CURRENT_DATE) = 2020) ) "; //masih menghitung omset 1nov 2019 dan 2020 dlm 1 thn omset yang sama
					$sql .= " 				  		OR ( DATE(m.mbrDate) >= '2019.11.01' AND YEAR(m.mbrDate) = YEAR(CURRENT_DATE) ) "; //Mulai thn 2021, omset dihitung per tahun (tahun sblmnya tidak dihitung dlm omset direct)
			
					$sql .= "       GROUP BY sp.mbrUsername ";
					$sql .= "       ) AS t ON mbrSponsor=pairTORUsername";
					$sql .= "       INNER JOIN dtMember sp ON sp.mbrUsername=pairTORUsername";
					//$sql .= " WHERE wkYearWeek >= '201944'";  //bisa tambahkan pairTORUsername ='......' pada bagian member area
					$sql .= " WHERE Date(pairTORDate) >= '2019.11.01'";
					$sql .= " GROUP BY pairTORUsername";
					$sql .= " ) AS a ";
					//$sql .= " HAVING NetLeft >= 1000 AND NetRight >= 1000";
					//$sql .= " AND pairTORUsername !='visionea'";
					$sql .= " WHERE pairTORUsername !='visionea'";
					$sql .= $sqlWhere;
					$sql .= " ORDER BY BalanceTO DESC ";
					$query = $conn->query($sql);
					$totalRec = $query->num_rows;
					$numPages	= ceil ($totalRec / $numPerPage);	
					$pageActive = ($pageActive<1)?1:$pageActive;				
					$startRec = ($pageActive-1) * $numPerPage;
					// $sql .= " LIMIT " . $startRec . ", " . $numPerPage;
					// echo $sql;
					?>
					<div class="table-responsive-md">
						<table class="table table-hover table-striped small">
							<thead>
								<tr>
									<th>#</th>
									<th>Username</th>
									<th>Name</th>
									<th>Email</th>
									<th>Left</th>
									<th>Right</th>
									<th>Direct TO</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th>#</th>
									<th>Username</th>
									<th>Name</th>
									<th>Email</th>
									<th>Left</th>
									<th>Right</th>
									<th>Direct TO</th>
								</tr>
							</tfoot>
							<tbody>
							<?php
							if ($query->num_rows == 0){
									echo "<tr><td colspan=7 class='text-center text-primary'>no record</td></tr>";	
							}
							$i=0;
							$sql .= " LIMIT " . $startRec . ", " . $numPerPage;
							$query = $conn->query($sql);
							while ($row = $query->fetch_assoc()){
								$i++;
							?>
								<tr>
									<td><?php echo $i ?></td>
									<td><?php echo $row["pairTORUsername"]; ?></td>
									<td><?php echo $row["mbrFirstName"]." ".$row['mbrLastName']; ?></td>
									<td><?php echo $row["mbrEmail"] ?></td>
									<td><?php echo $row["NetLeft"] ?></td>
									<td><?php echo $row["NetRight"] ?></td>
									<td><?php echo $row["directTO"] ?></td>
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
			</div>
		</div>
	</div>
</div>