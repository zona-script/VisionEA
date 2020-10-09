<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");


//$MNav	= (isset($_GET['MNav']))?$_GET['MNav']:"";
$subNav	= (isset($_GET['subNav']))?$_GET['subNav']:"";

$menuDesc = "";
if ($subNav == "sp"){
	$menuDesc = "Sponsor";	
}else if ($subNav == "pu"){
	$menuDesc = "Passed-Up";	
}else if ($subNav == "pair"){
	$menuDesc = "Pairing";	
}else if ($subNav == "mm"){
	$menuDesc = "Mega Matching";	
}


function setActive($menu, $section){
	if ($menu == $section) return "active";
	return "";
}

?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Network Tree</title>
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
	<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">

	<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
	<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script>

		$(document).ready(function(e) {

			$('a').on('click', function(){
				$cat = "Report";
				$subCat = "";
				if ($.trim($(this).attr('href')) == "#linkSponsor"){
					$subCat	= "Sponsor";
				}else if ($.trim($(this).attr('href')) == "#linkPU"){
					$subCat	= "Passed-Up";
				}else if ($.trim($(this).attr('href')) == "#linkPair"){
					$subCat	= "Pairing";
				}else if ($.trim($(this).attr('href')) == "#linkMM"){
					$subCat	= "Mega Matching";
				}

				$cat +=($subCat != "")? " :: " + $subCat : "";
				$("#cat").html($cat);

		}); //end $('a').on click

		}); //end document ready


	</script>
</head>

<body>
	<span id="q" title="<?php echo $q ?>"></span>
	<span id="status" title="<?php echo $status ?>"></span>
	<div class="card">
		<div class="card-header card-header-success card-header-icon">
			<div class="card-icon">
				<i class="material-icons">assignment</i>
			</div>
			<div class="card-text"><h4 class="card-title" id="cat">Report :: <?php echo $menuDesc; ?></h4></div>
		</div> <!-- end card-header -->
		<div class="card-body card-fix">
			<div class="row">
				<div class="col-md-2">				
					<ul class="nav nav-pills nav-pills-rose nav-pills-icons flex-column" role="tablist">
						<li class="nav-item" >
							<a class="nav-link <?php echo setActive($subNav, "sp") ?>" data-toggle="tab" href="#linkSponsor" role="tablist">
								<i class="material-icons">face</i>Sponsor
							</a>
						</li>
						<!-- <li class="nav-item">
							<a class="nav-link <?php echo setActive($subNav, "pu") ?>" data-toggle="tab" href="#linkPU" role="tablist">
								<i class="material-icons">call_merge</i>Passed-Up
							</a>
						</li> -->
						<li class="nav-item">
							<a class="nav-link <?php echo setActive($subNav, "pair") ?>" data-toggle="tab" href="#linkPair" role="tablist">
								<i class="fa fa-object-ungroup"></i>Pairing
							</a>
						</li>
						<!-- <li class="nav-item">
							<a class="nav-link <?php echo setActive($subNav, "mm") ?>" data-toggle="tab" href="#linkMM" role="tablist">
								<i class="fa fa-object-group"></i>Mega Matching
							</a>
						</li> -->
					</ul>
				</div>
				<div class="col-md-10">
					<div class="tab-content">
						<div class="tab-pane <?php echo setActive($subNav, "sp") ?>" id="linkSponsor">
							<div class="row">
								<div class="table-responsive col-md-11">
									<table id="tRptSponsor" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
										<thead>
											<tr>
												<th>Date</th>
												<th>Type of Commission</th>
												<th>Currency</th>
												<th class="text-right">Amount</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>Date</th>
												<th>Type of Commission</th>
												<th class="text-right">Currency</th>
												<th class="text-right">Amount&nbsp;&nbsp;</th>
											</tr>
										</tfoot>
										<tbody>
											<?php
											$sql = "SELECT bnsSpUsername, bnsSpTrUsername, typeOfBonus, bnsSpAmount, bnsSpDate ";
											$sql .= " FROM( ";
											$sql .= "     SELECT bnsSpUsername, bnsSpTrUsername, bnsSpTrPacID, CONCAT('Sponsorship: ', pacName) as typeOfBonus, bnsSpAmount, bnsSpDate ";
											$sql .= " 	FROM dtBnsSponsor INNER JOIN msPackage on pacID = bnsSpTrPacID ";
											$sql .= " ) bns ";
											$sql .= " WHERE bnsSpUsername = '" . $_SESSION["sUserName"] ."' ";
											$sql .= " AND date(bnsSpDate) >= '".$DEF_MUTASI_DATE."'";
											$sql .= " ORDER BY bnsSpDate DESC ";
																	// echo $sql;
											if ($query = $conn->query($sql)){
												if ($query->num_rows==0){
													echo "<tr><td colspan='4' style='text-align:center'>no records</td></tr>";
												}
												while ($row = $query->fetch_assoc()){
													?>
													<tr>
														<td><?php echo $row["bnsSpDate"] ?></td>
														<td><?php echo "Sponsorship ".(($row["bnsSpTrUsername"] != "")? " - " . $row["bnsSpTrUsername"] : ""); ?></td>
														<td>IDR</td>
														<td class="text-right" ><?php echo numFormat($row["bnsSpAmount"], 0) ?></td>
													</tr>
													<?php 
												}
											} ?>
										</tbody>
									</table>
								</div>
							</div>
						</div> <!-- link Sponsor -->
						<div class="tab-pane <?php echo setActive($subNav, "pu") ?>" id="linkPU">
							<div class="row">
								<div class="material-datatables col-md-11">
									<table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
										<thead>
											<tr>
												<th>Date</th>
												<th>Type of Commission</th>
												<th class="text-right">Amount&nbsp;&nbsp;</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>Date</th>
												<th>Type of Commission</th>
												<th class="text-right">Amount&nbsp;&nbsp;</th>
											</tr>
										</tfoot>
										<tbody>
											<?php
											$sql = "SELECT bnsPUUsername, bnsPUTrUsername, typeOfBonus, bnsPUAmount, bnsPUDate  ";
											$sql .= " FROM( ";
											$sql .= " 	SELECT bnsPUUsername, bnsPUTrUsername, bnsPUTrPacID, CONCAT('Passed-up: ', pacName) as typeOfBonus, bnsPUAmount, bnsPUDate  ";
											$sql .= " 	FROM dtBnsPassedUp INNER JOIN msPackage on pacID = bnsPUTrPacID ";
											$sql .= " ) bns ";
											$sql .= " WHERE bnsPUUsername = '" . $_SESSION["sUserName"] ."' ";
											$sql .= " ORDER BY bnsPUDate DESC ";
											if ($query = $conn->query($sql)){
												if ($query->num_rows==0){
													echo "<tr><td colspan='3' style='text-align:center'>no records</td></tr>";
												}
												while ($row = $query->fetch_assoc()){
													?>
													<tr>
														<td width="100"><?php echo $row["bnsPUDate"] ?></td>
														<td width="200"><?php echo $row["typeOfBonus"]; echo (($row["bnsPUTrUsername"] != "")? " - " . $row["bnsPUTrUsername"] : ""); ?></td>
														<td class="text-right" width="100">$<?php echo numFormat($row["bnsPUAmount"], 0) ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
													</tr>
													<?php 
												}
											} ?>
										</tbody>
									</table>
								</div>
							</div>
						</div> <!-- link network -->
						<div class="tab-pane <?php echo setActive($subNav, "pair") ?>" id="linkPair">
							<div class="row">
								<div class="table-responsive col-md-11">
									<table id="tRptPair" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
										<thead>
											<tr>
												<th>Date</th>
												<th>Type of Commission</th>
												<th>Currency</th>
												<th class="text-right">Amount</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>Date</th>
												<th>Type of Commission</th>
												<th class="text-right">Currency</th>
												<th class="text-right">Amount&nbsp;&nbsp;</th>
											</tr>
										</tfoot>
										<tbody>
											<?php
											$sql = "SELECT pairUsername, IF(date(pairDate) < '".$DEF_MUTASI_DATE."','pairing 10%','pairing 5%') as typeOfBonus, pairTO as pairAmount, pairDate  ";
											$sql .= "     FROM dtDailyPairing ";
											$sql .= "     WHERE pairTO > 0 AND pairUsername = '" . $_SESSION["sUserName"] ."' ";
											$sql .= " AND date(pairDate) >= '".$DEF_MUTASI_DATE."' ";
											$sql .= " ORDER BY pairDate DESC ";
											if ($query = $conn->query($sql)){
												if ($query->num_rows==0){
													echo "<tr><td colspan='4' style='text-align:center'>no records</td></tr>";
												}
												while ($row = $query->fetch_assoc()){
													?>
													<tr>
														<td><?php echo $row["pairDate"] ?></td>
														<td><?php echo $row["typeOfBonus"]; ?></td>
														<td>IDR</td>
														<td class="text-right"><?php echo numFormat($row["pairAmount"], 0) ?></td>
													</tr>
													<?php 
												}
											} ?>
										</tbody>
									</table>
								</div>
							</div>
						</div> <!-- link commission -->
						<div class="tab-pane <?php echo setActive($subNav, "mm") ?>" id="linkMM">
							<div class="row">
								<div class="material-datatables col-md-11">
									<table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
										<thead>
											<tr>
												<th>Date</th>
												<th>Type of Commission</th>
												<th class="text-right">Amount&nbsp;&nbsp;</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>Date</th>
												<th>Type of Commission</th>
												<th class="text-right">Amount&nbsp;&nbsp;</th>
											</tr>
										</tfoot>
										<tbody>
											<?php
											$sql = "SELECT * FROM dtMatching WHERE mtchUsername='". $_SESSION["sUserName"] ."' ORDER BY DATE(mtchDate) DESC";
											if ($query = $conn->query($sql)){
												if ($query->num_rows==0){
													echo "<tr><td colspan='3' style='text-align:center'>no records</td></tr>";
												}
												while ($row = $query->fetch_assoc()){
													?>
													<tr>
														<td width="100"><?php echo $row["mtchDate"] ?></td>
														<td width="200"><?php echo "Matching of " . $row['mtchPair']; ?></td>
														<td class="text-right" width="100">$<?php echo numFormat($row["mtchAmount"], 0) ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
													</tr>
													<?php 
												}
											} ?>
										</tbody>
									</table>
								</div>
							</div>
						</div> <!-- link Report -->
					</div> <!-- tab content -->
				</div> <!-- col md 10 -->
			</div>
		</div> <!-- end card-body -->
	</div> <!-- end Card -->
</body>
<!--   Core JS Files   -->
<script src="../assets/js/core/jquery.min.js"></script>
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/bootstrap-material-design.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>

<!--  Google Maps Plugin  -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB2Yno10-YTnLjjn_Vtk0V8cdcY5lC4plU"></script>

<!--  Plugin for Date Time Picker and Full Calendar Plugin  -->
<script src="../assets/js/plugins/moment.min.js"></script>

<!--	Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
<script src="../assets/js/plugins/bootstrap-datetimepicker.min.js"></script>

<!--	Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="../assets/js/plugins/nouislider.min.js"></script>

<!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/bootstrap-selectpicker.js"></script>

<!--	Plugin for Tags, full documentation here: http://xoxco.com/projects/code/tagsinput/  -->
<script src="../assets/js/plugins/bootstrap-tagsinput.js"></script>

<!--	Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
<script src="../assets/js/plugins/jasny-bootstrap.min.js"></script>

<!-- Plugins for presentation and navigation  -->
<script src="../assets/assets-for-demo/js/modernizr.js"></script>

<!-- Material Kit Core initialisations of plugins and Bootstrap Material Design Library -->
<script src="../assets/js/material-dashboard.js?v=2.0.0"></script>

<!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>

<!-- Library for adding dinamically elements -->
<script src="../assets/js/plugins/arrive.min.js" type="text/javascript"></script>

<!-- Forms Validations Plugin -->
<script src="../assets/js/plugins/jquery.validate.min.js"></script>

<!--  Charts Plugin, full documentation here: https://gionkunz.github.io/chartist-js/ -->
<script src="../assets/js/plugins/chartist.min.js"></script>

<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="../assets/js/plugins/jquery.bootstrap-wizard.js"></script>

<!--  Notifications Plugin, full documentation here: http://bootstrap-notify.remabledesigns.com/    -->
<script src="../assets/js/plugins/bootstrap-notify.js"></script>

<!-- Vector Map plugin, full documentation here: http://jvectormap.com/documentation/ -->
<script src="../assets/js/plugins/jquery-jvectormap.js"></script>

<!--  Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/jquery.select-bootstrap.js"></script>

<!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
<script src="../assets/js/plugins/jquery.datatables.js"></script>

<!-- Sweet Alert 2 plugin, full documentation here: https://limonte.github.io/sweetalert2/ -->
<script src="../assets/js/plugins/sweetalert2.js"></script>

<!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
<script src="../assets/js/plugins/fullcalendar.min.js"></script>

<!-- demo init -->
<script src="../assets/js/plugins/demo.js"></script>


<script type="text/javascript">

	$(document).ready(function(){	
		$('#tRptSponsor').DataTable({
	        "pagingType": "full_numbers",
	        "lengthMenu": [
	            [5, 25, 50, -1],
	            [5, 25, 50, "All"]
	        ],
	        order: [[ 0, "desc" ]],
	        responsive: true,
	        language: {
	            search: "_INPUT_",
	            searchPlaceholder: "Search records",
	        }

	    });

	    $('#tRptPair').DataTable({
	        "pagingType": "full_numbers",
	        "lengthMenu": [
	            [5, 25, 50, -1],
	            [5, 25, 50, "All"]
	        ],
	        order: [[ 0, "desc" ]],
	        responsive: true,
	        language: {
	            search: "_INPUT_",
	            searchPlaceholder: "Search records",
	        }

	    });
	});

</script>
<?php fCloseConnection($conn); ?>
</html>

<script>

// Get the modal
var modal = document.getElementById('idModal');
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target.id == "idModal")
		document.getElementById('idModal').style.display='none';
	//alert (event.target.id);
}



</script>