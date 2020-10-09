<?php


die();


include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$accType = $IDAcc = $IDTrans = $amount = "";
if (!empty($_POST)) { 
	$accType	=  (isset($_POST["accType"]))?fValidateSQLFromInput($conn, $_POST["accType"]): "";
	$IDAcc		=  (isset($_POST["IDAcc"]))?fValidateSQLFromInput($conn, $_POST["IDAcc"]): "";
	$IDTrans	=  (isset($_POST["IDTrans"]))?fValidateSQLFromInput($conn, $_POST["IDTrans"]): "";
	$amount		=  (isset($_POST["amount"]))?fValidateSQLFromInput($conn, $_POST["amount"]): "0";
	$curr		= "IDR";
	$curs		= "1";
	$accName	= "";
	$formAccNo	= "";
	$toAccNo	= "";
	$note		= "Transaction ID: " . $IDTrans;
	$status		= $GLOBAL["DEF_STATUS_PENDING"];
	$arrData = array(
				0 => array ("db" => "finMbrUsername"	, "val" => $username),
				1 => array ("db" => "finAmount"			, "val" => $amount),
				2 => array ("db" => "finCurr"			, "val" => $curr),
				
				8 => array ("db" => "finNote"			, "val" => $note),
				9 => array ("db" => "finDate"			, "val" => "CURRENT_TIME()"),
			   10 => array ("db" => "finStatus"			, "val" => $status)
				
				//not complite yet
				);
				
	$table	= "table ??? not complete yet... possible will not use this page.. changed.";	
/*
	if (fInsert($table, $arrData, $conn)){
		//insert success
		//redirect to success page
		header("Location: reqSecPass.php?q=info-success");
	}else{
		//echo "Could not process your information " . mysql_error();
		
		//insert fail
		//back for re-deposit
		$responseMessage = "Request Security Password Failed";	
	} // end else
*/	
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Request Security Password</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" href="../assets/css/newBinary.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
<script>
	$(document).ready(function(e) {
        //new property method
		$(document).ready(function(){
			$("#q").html('<?php echo $_GET["q"] ?>');
			if ($.trim($("#q").html()) == "info-success"){
				demo.showNotification('top','center', 'success', 'Request Security Password Successfully');
			}
		});
		
    }); //end $(document).ready(function(e) {
</script>
</head>
<body>
<span id="q"></span>
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="fa fa-lock fa-2x" aria-hidden="true"></i>
        </div>
		 <div class="card-text">
           <h4 class="card-title">Request Security Password</h4>
        </div>
    </div>
	<!---- left card ------>
    <div class="card-body card-fix">
        <div class="container">
			<div class="container-fluid">
            <?php if ($responseMessage != ""){ ?>
            	<div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6 text-danger">
                      <div class="alert alert-info">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <i class="material-icons">close</i>
                          </button>
                          <span><b> Info - </b> <?php echo $responseMessage ?></span>
                      </div>
                    </div>
                    <div class="col-md-3"></div>
                </div>
             <?php } ?>
             <div class="row">
                <div class="col-md-8" >
                    <div class="card" >	
                        <form method="post" action="reqSecPass.php">
                            <div class="card-body">     	
                                <div class="row">
                                    <label class="col-md-4 text-left text-vCenter" id="paymentLabel">Email</label>
                                    <div class="col-md-8">
                                        <div class="form-group has-default">
                                            <input type="text" name="email" id="email" value="<?php echo $email; ?>" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" class="form-control" title="Email address" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-4 text-left text-vCenter">Password</label>
                                    <div class="col-md-8">
                                        <div class="form-group has-default">
                                            <input type="password" name="password" id="password" value="<?php echo $password; ?>" class="form-control" title="Password" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                	<div class="col-md-4"></div>
                                    <div class="col-md-8">
                                		<button type="submit" name="submit" id="submit" class="btn btn-fill btn-rose col-md-12">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div> <!-- end card -->
                </div><!-- end col -->
			</div> <!-- end row -->
                
		</div> <!-- end container-fluid -->
	 </div> <!-- end container -->
     </div> <!-- card-body>
</div><!-- end card -->
        
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
	  //init DateTimePickers
	  md.initFormExtendedDatetimepickers();
	
	  // Sliders Init
	  md.initSliders();
	});
</script>

<?php fCloseConnection($conn); ?>
</html>