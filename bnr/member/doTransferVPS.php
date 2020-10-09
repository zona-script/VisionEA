<?PHP
include_once("../includes/inc_def.php"); //before inc_session
include_once("../includes/inc_session.php"); //after inc_session
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");
include_once("../includes/inc_commission.php");



//NB:
//Total of Balance will be reduced when status of WD is : ON_Progress or Approved
$responseMessage = "";
$username = $transferTo = $amountVoucher = $secPasswd = "";
if (!empty($_POST)) { 

	$username	   = $_SESSION["sUserName"];
	$transferTo  =  (isset($_POST["transferTo"]))?fValidateSQLFromInput($conn, $_POST["transferTo"]): "";
	
  $amountVoucher	 =  (isset($_POST["amountVoucher"]))?fValidateSQLFromInput($conn, $_POST["amountVoucher"]): "0";
  $numberOfVoucher  = $amountVoucher / $DEF_VOUCHER_PRICE_VPS;

	$secPasswd   =  (isset($_POST["secPasswd"]))?fValidateSQLFromInput($conn, $_POST["secPasswd"]): "";
	
  //Validation inputs
	if ($username != "" && $transferTo != "" && $amountVoucher != "" && $amountVoucher > 0 && $secPasswd != "" && $numberOfVoucher > 0){
    if ($username != $transferTo){
      //cek balance voucher
      $myObjData = json_decode(fGetNumberOfVoucher($DEF_VOUCHER_TYPE_VPS, $username, $conn));
      if ($myObjData->{'status'} == "success"){
        $balanceVoucher = $myObjData->{'voucherBalance'}; //$myObjData->{'voucherAct'};
      }else{
        $balanceVoucher = 0;
      }

      if ($balanceVoucher >= $amountVoucher){
        //Check Security Password
        //$s = fCheckSecurityPassword($username, $secPasswd, $conn);
        //if (true){
        //echo (fCheckSecurityPassword($username, $secPasswd, $conn)); die();
        if (!fCheckSecurityPassword($username, $secPasswd, $conn)){
          $responseMessage .= "Security Password not match<br>";
          
        }else{
          
          $conn->autocommit(false);
          $isFailed = false;

          //Insert into dtFundIn
          $timeStamp  = strtotime(now);
          $finID    = $transferTo.$timeStamp;  //same format used in Buy Voucher (reqBuyVoucher.php)
          $amount   = $amountVoucher; //* $DEF_VOUCHER_PRICE;
          $curr     = "IDR";
          $curs     = "1";
          $accName  = "";
          $accType  = $DEF_TRANSFER_VOUCHER_VPS;
          $voucherType = $DEF_VOUCHER_TYPE_VPS;
          $fromAccNo = $username; //$_SESSION["sUserName"];
          $toAccNo  = $transferTo; 
          $status   = $DEF_STATUS_APPROVED; //APPROVED BY SYSTEM
          $approvedBy = "SYSTEM";
          $IDTrans  = "SYS-".$timeStamp;

          $arrData = array(
          0 => array ("db" => "finID"       , "val" => $finID),
          1 => array ("db" => "finMbrUsername"  , "val" => $transferTo),
          2 => array ("db" => "finAmount"     , "val" => $amount),
          3 => array ("db" => "finCurr"     , "val" => $curr),
          4 => array ("db" => "finCurs"     , "val" => $curs),
          5 => array ("db" => "finAccName"    , "val" => $accName),
          6 => array ("db" => "finAccType"    , "val" => $accType),
          7 => array ("db" => "finVoucherType"    , "val" => $voucherType),
          8 => array ("db" => "finFromAccNo"    , "val" => $fromAccNo),
          9 => array ("db" => "finToAccNo"    , "val" => $toAccNo),
          10 => array ("db" => "finTransactionID"  , "val" => $IDTrans),
          11 => array ("db" => "finDate"     , "val" => "CURRENT_TIME()"),
          12 => array ("db" => "finStatus"     , "val" => $status),
          13 => array ("db" => "finApprovedBy"   , "val" => $approvedBy)
          );
                
          $table  = "dtFundIn"; 
        
          if (fInsert($table, $arrData, $conn)){
            //insert success

            //ReChecking number of existing voucher
            $existingVoucher = 0;
            $sql = "SELECT COUNT(*) as existingVoucher FROM dtFundInVoucher INNER JOIN dtFundIn ON finID=fivFinID INNER JOIN dtVoucher ON vCode=fivVCode ";
            $sql .= " WHERE finMbrUsername = '".$username."' AND fivStatus='" . $DEF_STATUS_ACTIVE . "'";
            $sql .= " AND finVoucherType='" . $voucherType ."'";
            $query = $conn->query($sql);
            if ($row = $query->fetch_assoc()){
              $existingVoucher = $row['existingVoucher'];
            }else{
              //no record
              $conn->rollback();  
              $isFailed = true;
            }

            if ($existingVoucher >= $numberOfVoucher && $numberOfVoucher > 0){
              
              //insert voucher to
              $sql = "INSERT INTO dtFundInVoucher (fivFinID, fivVCode, fivDate, fivStatus, fivType, fivUsedFor, fivUserOn) ";
              $sql .= " SELECT '".$finID."', fivVCode, '".$CURRENT_TIME."', fivStatus, fivType, fivUsedFor, fivUserOn ";
              $sql .= " FROM dtFundInVoucher INNER JOIN dtFundIn ON finID=fivFinID INNER JOIN dtVoucher ON vCode=fivVCode ";
              $sql .= " WHERE finMbrUsername = '".$username."' AND fivStatus='" . $DEF_STATUS_ACTIVE . "'";
              $sql .= " AND finVoucherType='". $voucherType . "'";
              $sql .= " ORDER BY finDate ASC, fivVCode ASC  ";
              $sql .= " LIMIT " . $numberOfVoucher;

              if ($query = $conn->query($sql)){
                //success insert into voucher to
                //update voucher from

                $sql = " SELECT fivFinID, fivVCode, fivDate, fivStatus, fivType, fivUsedFor, fivUserOn ";
                $sql .= " FROM dtFundInVoucher INNER JOIN dtFundIn ON finID=fivFinID INNER JOIN dtVoucher ON vCode=fivVCode ";
                $sql .= " WHERE finMbrUsername = '".$username."' AND fivStatus='" . $DEF_STATUS_ACTIVE . "'";
                $sql .= " AND finVoucherType='". $voucherType . "'";
                $sql .= " ORDER BY finDate ASC, fivVCode ASC  ";
                $sql .= " LIMIT " . $numberOfVoucher;

                $query = $conn->query($sql);
                $i = 0;
                while ($row=$query->fetch_assoc()) {
                  if ($i < $numberOfVoucher){
                    $sqlUpdate = "UPDATE dtFundInVoucher SET  fivStatus='". $DEF_STATUS_USED . "', ";
                    $sqlUpdate .= " fivUsedFor='". $DEF_VOUCHER_USED_FOR_TRANSFER . "', fivUserOn='". $transferTo . "'";
                    $sqlUpdate .= " WHERE fivFinID='" . $row['fivFinID'] . "' AND fivVCode='" . $row['fivVCode'] . "' AND fivStatus='" . $DEF_STATUS_ACTIVE . "'"; 
                    if ($queryUpdate = $conn->query($sqlUpdate)){
                      $i++;
                    }else{
                      //error
                      $responseMessage = "Updating failed<br>"; 
                      $conn->rollback();  
                      $isFailed = true;
                    }
                  }
                } //end while
                
              } //end if       
              else {
                $responseMessage = "Insert PIN failed<br>"; 
                $conn->rollback();
                $isFailed = true;
              }       
            }else{
              //voucher not enough
              $responseMessage = "ReChecking: Balance is not enough<br>"; 
              $conn->rollback();
              $isFailed = true;
            }

            if ($isFailed==false){
              
              $conn->commit();
              $conn->close();
              //redirect to success page
              header("Location: ".$COMPANY_SITE. "member/doTransferVPS.php?q=info-success");
              die();
            }

          }else{
            //echo "Could not process your information " . mysql_error();
            //die();
            //insert fail
            //back for re-deposit
            $responseMessage = "Submit Transfer PIN Failed"; 
            $conn->rollback();
          } // end else
        }
      }else{
        $responseMessage .= "Your balance is not enough<br>";  
      }
    }else{
      $responseMessage .= "Can not transfer to your self<br>";  
    }
	}else{
		//Data not complite
		$responseMessage .= "Incomplete data<br>";	

	}
}else{
  $q = "";
  $q = (isset($_GET['q']))?fValidateSQLFromInput($conn, $_GET['q']): "";

}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Transfer VPS PIN</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" href="../assets/css/newBinary.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
<script>
  
      
  $(document).ready(function(e) {
    //Check Existing username

    $("#transferTo").on('blur', function() {
      $id = $("#transferTo").val();
      $.get("getData.php?q=mbrUsername&id="+$id, function(data, status){
        if (data == "exist") {
          
        }else if(data == "not_found" && $id != "") {
          demo.showNotification('top','center', 'danger', 'Username not found');
          $("#transferTo").val("").focus();
        }
      });
    });

    $(document).ready(function(){

      $("#q").html("<?php echo $q ?>");
      if ($("#q").html() == "info-success"){
        demo.showNotification('top','center', 'info', 'Transfer VPS PIN successfully');
      }

    });

    $("#submit").on("submit", function(e){
      $("#submit").attr("disabled", true);
      var html = $("#submit").html();
      $("#submit").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
    });
    

  });


</script>
</head>
<body>
<span id="q"></span>
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="material-icons">swap_horiz</i>
        </div>
		 <div class="card-text">
           <h4 class="card-title">Transfer VPS PIN</h4>
        </div>
    </div>
	
    <div class="card-body card-fix">
        <div class="container">
            <div class="container-fluid">
                <div class="card" >
                    <div class="card-body"> 
                        <ul class="nav nav-pills nav-pills-rose" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#linkTransfer" role="tablist">
                                    Transfer to Other Member
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#linkHistory" role="tablist">
                                    Transfer History
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content tab-space">
                            <div class="tab-pane active" id="linkTransfer">
                                <?php if ($responseMessage != ""){ ?>
                                    <div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-6 text-danger">
                                          <div class="alert alert-danger">
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
                                      <form method="post" action="doTransferVPS.php">
                                          <div class="row">
                                              <label class="col-md-4 text-left text-vCenter">Transfer To</label>
                                              <div class="col-md-8">
                                                  <div class="form-group has-default">
                                                      <input type="text" name="transferTo" id="transferTo" value="<?php echo $transferTo; ?>" class="form-control" title="receiver username" required>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="row">
                                              <label class="col-md-4 text-left text-vCenter">Amount of VPS PIN</label>
                                              <div class="col-md-8">
                                                  <div class="form-group has-default">
                                                      <!-- <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" name="amount" id="amount" value="<?php //echo $amount; ?>"  class="form-control" title="Amount of Deposit" required> -->
                                                      <input type="number" step="<?PHP echo $DEF_VOUCHER_PRICE_VPS; ?>" name="amountVoucher" id="amountVoucher" value="<?php echo $amountVoucher; ?>"  class="form-control" title="Amount of VPS PIN to transfer to others" required>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="row">
                                              <label class="col-md-4 text-left text-vCenter">Security Password</label>
                                              <div class="col-md-8">
                                                  <div class="form-group has-default">
                                                      <input type="password" name="secPasswd" id="secPasswd" value="<?php echo $secPasswd; ?>" class="form-control" title="Your security password" maxlength="30" required>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="row">
                                              <div class="col-md-4"></div>
                                              <div class="col-md-8">
                                                  <button type="submit" name="submit" id="submit" class="btn btn-fill btn-rose col-md-12">Submit</button>
                                              </div>
                                          </div>
                                      </form>
                                    </div><!-- end col -->
                                </div> <!-- end row -->
                            </div>
                            <?php 
                              /*
                              $sql = "SELECT fivFinID, fivVCode, fivDate, fivStatus, fivUsedFor, fivUserOn ";
                              $sql .= " FROM dtFundInVoucher INNER JOIN dtFundIn ON finID=fivFinID INNER JOIN dtVoucher ON vCode=fivVCode ";
                              $sql .= " WHERE finMbrUsername = '".$username."' AND fivStatus='" . $DEF_STATUS_APPROVED . "'";
                              $sql .= " fivUsedFor = '" . $DEF_VOUCHER_USED_FOR_TRANSFER . "'";
                              $sql .= " ORDER BY DATE(finDate) DESC ";
                              */
                              $sql = "SELECT a.*, stDesc FROM ( ";
                              $sql .= " SELECT sender, receiver, finAmount, finDate, finStatus FROM ";
                              $sql .= " ( ";
                              $sql .= "   SELECT finFromAccNo as sender, finMbrUsername as receiver, finAmount, finDate, finStatus FROM dtFundIn  ";
                              $sql .= "   WHERE finMbrUsername='". $_SESSION['sUserName'] ."' AND finAccType='" . $DEF_TRANSFER_VOUCHER_VPS . "'  ";
                              $sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_VPS . "'";
                              $sql .= " ) r ";
                              $sql .= " UNION ";
                              $sql .= " ( ";
                              $sql .= "   SELECT finFromAccNo as sender, finMbrUsername as receiver, finAmount, finDate, finStatus FROM dtFundIn  ";
                              $sql .= "   WHERE finFromAccNo='". $_SESSION['sUserName'] ."' AND finAccType='" . $DEF_TRANSFER_VOUCHER_VPS . "'  ";
                              $sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_VPS . "'";
                              $sql .= " )  ";
                              $sql .= ") a ";
                              $sql .= " INNER JOIN msStatus on stID=finStatus ";
                              $sql .= " ORDER BY DATE(finDate) DESC ";
                              
                              //echo $sql; 
                              $queryHistory = $conn->query($sql);
                            ?>
                            <div class="tab-pane" id="linkHistory">
                              <div class="material-datatables">
                                  <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                      <thead>
                                          <tr>
                                              <th>Date</th>
                                              <th>Description</th>
                                              <th>Amount</th>
                                              <th>Status</th>
                                          </tr>
                                      </thead>
                                      <tfoot>
                                          <tr>
                                              <th>Date</th>
                                              <th>Description</th>
                                              <th>Amount</th>
                                              <th>Status</th>
                                          </tr>
                                      </tfoot>
                                      <tbody>
                                          <?php
                                            while ($rowHistory = $queryHistory->fetch_assoc()){
                                          ?>
                                          <tr>
                                              <td><?php echo $rowHistory["finDate"] ?></td>
                                              <td><?php echo $rowHistory["sender"] ." - ".$rowHistory["receiver"] ?></td>
                                              <td><?php 
                                              if ($rowHistory["receiver"] == $_SESSION['sUserName']) { 
                                                echo ( $rowHistory["finAmount"]); 
                                              } else { echo "-" . $rowHistory["finAmount"]; } 
                                              ?></td>
                                              <td><?php echo $rowHistory["stDesc"] ?></td>
                                          </tr>
                                          <?php } ?>
                                      </tbody>
                                  </table>
                               </div>
                            </div>
                        </div>
                    </div><!-- end card- Body -->
            	</div><!-- end card -->
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

<script type="text/javascript">

$(document).ready(function() {
    $('#datatables').DataTable({
        "pagingType": "full_numbers",
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records",
        }

    });


    var table = $('#datatables').DataTable();

    // Edit record
    table.on('click', '.edit', function() {
        $tr = $(this).closest('tr');

        var data = table.row($tr).data();
        alert('You press on Row: ' + data[0] + ' ' + data[1] + ' ' + data[2] + '\'s row.');
    });

    // Delete a record
    table.on('click', '.remove', function(e) {
        $tr = $(this).closest('tr');
        table.row($tr).remove().draw();
        e.preventDefault();
    });

    //Like record
    table.on('click', '.like', function() {
        alert('Yo	u clicked on Like button');
    });
	
	//for Declined
		$('#datatablesDeclined').DataTable({
			"pagingType": "full_numbers",
			"lengthMenu": [
				[10, 25, 50, -1],
				[10, 25, 50, "All"]
			],
			responsive: true,
			language: {
				search: "_INPUT_",
				searchPlaceholder: "Search records",
			}
	
		});
	
	
		var tableDeclined = $('#datatablesDeclined').DataTable();
	
		// Edit record
		tableDeclined.on('click', '.edit', function() {
			$tr = $(this).closest('tr');
	
			var data = tableDeclined.row($tr).data();
			alert('You press on Row: ' + data[0] + ' ' + data[1] + ' ' + data[2] + '\'s row.');
		});
	
		// Delete a record
		tableDeclined.on('click', '.remove', function(e) {
			$tr = $(this).closest('tr');
			tableDeclined.row($tr).remove().draw();
			e.preventDefault();
		});
	
		//Like record
		tableDeclined.on('click', '.like', function() {
			alert('Yo	u clicked on Like button');
		});
	
	//end of declined

    $('.card .material-datatables label').addClass('form-group');
});

</script>

<?php fCloseConnection($conn); ?>
</html>