<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$username      = $_SESSION['sUserName'];
$subNav	= (isset($_GET['subNav']))?$_GET['subNav']:"";
if ($subNav == "basic"){
    $loadPage = "../ebook/conv/e-Book Basic Edition.php?x=";
}else if ($subNav == "pro"){
    $loadPage = "../ebook/conv/e-Book Pro Edition.php?x=";
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $COMPANY_NAME ?> - List E-Book</title>
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
	<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
	<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
	<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<style>
		/* Hide scrollbar for Chrome, Safari and Opera */
		#loadPage::-webkit-scrollbar {
		    display: none;
		}

		/* Hide scrollbar for IE and Edge */
		#loadPage {
		    -ms-overflow-style: none;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="card">
	        <div class="card-header card-header-success card-header-icon">
	            <div class="card-icon">
	                <i class="fa fa-book fa-2x"></i>
	            </div>
		        <div class="card-text"><h4 class="card-title">Product</h4></div>
			</div>
			<div class="card-body card-fix">
				<?php 
				if ($subNav == ""){
				?>
				<div class="row mr-auto ml-auto">
			    	<?php
			    	$sql  = "SELECT proID, proPrice, trProTransID, trPDTransID, trProUpdateDate, trProStatus";
			    	$sql .= " FROM msProduct";
			    	$sql .= " LEFT JOIN (";
			    	$sql .= " 	SELECT * FROM trProduct";
			    	$sql .= " 	INNER JOIN trProDetail ON trPDTransID = trProTransID";
			    	$sql .= " 	INNER JOIN dtUserEbook ON ebUsername = trProUserBeli";
			    	$sql .= " 	WHERE ebUsername = '".$username."' AND trProStatus = '".$DEF_STATUS_APPROVED."'";
			    	$sql .= " 	GROUP BY trPDProID";
			    	$sql .= " ) AS trpro ON trpro.trPDProID = proID";
			    	// echo $sql;
			    	$result = $conn->query($sql);
			    	if ($result->num_rows>0){
			    		while ($row = $result->fetch_assoc()){
			    			$status = $title = $btnAction = "";
			    			$proID = $row['proID'];
			    			if ($proID == $DEF_EBOOK_BASIC){
			    				$title = "e-Book Basic Edition";
			    				$imgSrc = "../../images/mockup/BASIC_EDITION.jpg";
			    				$book = "basic";
			    			}else if ($proID == $DEF_EBOOK_PRO){
			    				$title = "e-Book Pro Edition";
			    				$imgSrc = "../../images/mockup/PRO_EDITION.jpg";
			    				$book = "pro";
			    			}
			    			$trProStatus = $row['trProStatus'];
			    			if ($trProStatus == $DEF_STATUS_APPROVED){
			    				$status = "(Purchased)";
			    				$btnAction = '<a class="btn btn-warning btn-round" href="./?MNav=readEbook&subNav='.$book.'" target="_parent">Read e-Book</a>';
			    			}else{
			    				$btnAction = '<a class="btn btn-rose btn-round" href="./?MNav=trProduct" target="_parent">Buy e-Book</a>';
			    			}
			    	?>
			        <div class="col-md-3 g-mb-30">
			          	<article class="u-shadow-v18 g-bg-white text-center rounded g-px-20 g-py-40 g-mb-5">
			            	<img class="d-inline-block img-fluid mb-4" src="<?php echo $imgSrc; ?>">
			            	<h4 class="h5 g-color-black g-font-weight-600 g-mb-10">Pemograman Expert Advisor</h4>
			            	<p><?php echo $title; ?> <span class="text-warning"><?php echo $status; ?></span></p>
			            	<span class="d-block g-color-primary g-font-size-16">IDR <?php echo numFormat($row['proPrice'], 0); ?></span>
			            	<?php echo $btnAction; ?>
			          	</article>
			        </div>
			        <?php
			        	}
		        	} 
			        ?>
			        <div class="col-md-6"></div>
			    </div>
			    <?php 
				}else{
			    ?>
				<div class="row">
					<div class="main-panel" style="width: 100%; height: 100%;">
			            <div class="content" style="text-align: center;margin-top: 10px;height:80%; width: 100%;">
			                <iframe class="card" id="loadPage" src="<?php echo $loadPage ?>" style=""></iframe>
			            </div>
			        </div>
				</div>
			    <?php 
				}
			    ?>
			</div>
		</div>
			    
	</div>
</body>
<!--   Core JS Files   -->
<!-- <script src="../assets/js/core/jquery.min.js"></script> -->
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
</html>