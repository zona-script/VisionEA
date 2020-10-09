<?PHP
// die();
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$errMsg = "";
$username      = $_SESSION['sUserName'];
$accNumber = $accName = $accType = $accCode = $passwdTrade = $server = $accEACode = $accOrder = $accPair = "";
if (!empty($_POST)) {
    $type     = fValidateSQLFromInput($conn, $_POST["type"]);
    if ($type == "addaccTrading"){
    	$accNumber     = fValidateSQLFromInput($conn, $_POST["accNumber"]); 
    	$accEACode     = fValidateSQLFromInput($conn, $_POST["accEACode"]); 
    	$accOrder      = fValidateSQLFromInput($conn, $_POST["accOrder"]); 
    	$accPair        = fValidateSQLFromInput($conn, $_POST["accPair"]); 
        $accName       = fValidateSQLFromInput($conn, $_POST["accName"]); 
        $passwdTrade   = fValidateSQLFromInput($conn, $_POST["passwdTrade"]); 
        $server        = fValidateSQLFromInput($conn, $_POST["server"]); 
    	$username     = $_SESSION['sUserName'];
    	if ($accNumber != "" && $accName != "" && $passwdTrade != "" && $server != "" && $username != "" && $accEACode != "" && $accOrder != "" && $accPair != ""){
            //Check Status Trading Account
            // $sql = "SELECT * FROM dtTradingAcc WHERE tradeUsername='".$username."' ORDER BY tradeDate DESC LIMIT 1";
            
            // check duplikat acc number.
            $sql = "SELECT * FROM dtTradingAcc WHERE (tradeStID = '".$DEF_STATUS_ACTIVE."' OR tradeStID = '".$DEF_STATUS_ONPROGRESS."') AND tradeAccNo = '".$accNumber."'";
            $query = $conn->query($sql);
            // $isAdd = true;
            if ($query->num_rows > 0){
                $errMsg = "Trading Account has been used";
            }else{
                $tradeID    = strtotime("now").rand(10000, 99999); //length 15
        		$arrData = array(
    				0 => array ("db" => "tradeID" , "val" => $tradeID),
                    1 => array ("db" => "tradeUsername" , "val" => $username),
                    2 => array ("db" => "tradeEANum"    , "val" => $accEACode),
                    3 => array ("db" => "tradeAccOrder" , "val" => $accOrder),
                    4 => array ("db" => "tradePair"     , "val" => $accPair),
    				5 => array ("db" => "tradeAccNo"	, "val" => $accNumber),
    				6 => array ("db" => "tradeAccPasswd", "val" => $passwdTrade),
    				7 => array ("db" => "tradeName"		, "val" => $accName),
    				8 => array ("db" => "tradeServer"	, "val" => $server),
    				9 => array ("db" => "tradeStID"		, "val" => $DEF_STATUS_ONPROGRESS),
    				10 => array ("db" => "tradeDate"	    , "val" => "CURRENT_TIME()")
				);
        		if (fInsert("dtTradingAcc", $arrData, $conn)){
        			//insert success
        			//send email for activation
        			fSendNotifToEmail("UPDATE_ACCOUNT_TRADING", $username);
        			$conn->close();

        			//redirect to success page
        			header("Location: accTradingFX.php?q=add-success");
        			die();
        		}else{
        			//insert fail	
        			//back for re-register
        			$errMsg = "Update Trading Account Failed";
        			
        		} // end else
    		}//end else
    	}else{
    		$errMsg = "Incomplete Data";
    	}
    }else if ($type == "reqresaccTrading"){
        $resaccUsername = $resaccNo = "";
        $resaccID    = strtotime("now").rand(10000, 99999);          
        $resaccUsername     = fValidateSQLFromInput($conn, $_POST["resaccUsername"]);
        $resaccNo           = fValidateSQLFromInput($conn, $_POST["resaccNo"]);
        
        if ($resaccUsername != "" && $resaccNo != ""){
            $sql  = " SELECT * FROM dtReqResetAcc WHERE resaccUsername = '".$resaccUsername."' AND resaccNo = '".$resaccNo."' ";
            $sql .= " AND resaccStID = '".$DEF_STATUS_ONPROGRESS."' ";
            $query = $conn->query($sql);
            $conn->autocommit(false);
            $table = "dtReqResetAcc";
            if ($query->num_rows > 0){
                //jika ketemu tidak boleh insert 
                echo (fSendStatusMessage ("error", "Cannot request reset at this time, try again later or contact our support team. #1"));die();
            }else{
                $arrData = array (
                    0 => array ("db" => "resaccID"          , "val" => $resaccID),
                    1 => array ("db" => "resaccDate"        , "val" => "CURRENT_TIME()"),
                    2 => array ("db" => "resaccUsername"    , "val" => $resaccUsername),
                    3 => array ("db" => "resaccNo"          , "val" => $resaccNo),
                    4 => array ("db" => "resaccStID"        , "val" => $DEF_STATUS_ONPROGRESS)
                );
                if (fInsert($table, $arrData, $conn)){
                    $conn->commit();
                    echo (fSendStatusMessage ("success","Permintaan reset akun trading berhasil dikirim"));die();
                }else{
                    $conn->rollback();
                    echo (fSendStatusMessage ("error","Cannot request reset at this time, try again later or contact our support team. #2"));die();
                }
            }      
        }else{
            echo (fSendStatusMessage ("error","Cannot request reset at this time, try again later or contact our support team. #3"));die();
        }  
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Trading Account</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
	$(document).ready(function(e) {			
        if ($.trim($("#q").html()) == "add-success"){
			demo.showNotification('top','center', 'success', 'Update Account Successfully');
            $("#q").html('');
		}else if ($.trim($("#q").html()) == "reset-success"){
            demo.showNotification('top','center', 'success', 'Request Reset Successfully');
            $("#q").html('');
        }

        $("#showPasswd").on("click", function(){
            var x = document.getElementById("passwdTrade");
            
            if (x.type === "password") {
                x.type = "text";
            
            } else {
                x.type = "password";
            
            }
        });

        $('#addaccTrading').on('submit', function(){
            $("#UpdateAcc").attr("disabled", true);
            var html = $("#UpdateAcc").html();
            $("#UpdateAcc").html(html + '&nbsp; <i class="fa fa-spinner fa-spin"  style="font-size:24px"></i>');
        });

        $('#reqresaccTrading').on('submit', function(e){
            var type = $("#type").val();
            var resaccUsername = $("#resaccUsername").val();
            var resaccNo = $("#resaccNo").val();
            // alert (type + " || "+resaccUsername+" || "+resaccNo); return false;
            e.preventDefault();
            swal({
                title: 'Apakah Anda yakin ?',
                text: 'Anda akan mereset akun trading',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonClass: "btn btn-success",
                cancelButtonClass: "btn btn-danger",
                buttonsStyling: false
            }).then(function() {
                $.post("accTradingFX.php", 
                {
                    "type"              : type,
                    "resaccUsername"    : resaccUsername,
                    "resaccNo"          : resaccNo                 
                },
                function(data, success){
                    // alert(data);
                    $myDataObj = JSON.parse(data);
                    if ($.trim($myDataObj["status"])=="error"){

                    }else if ($.trim($myDataObj['status']) == "success"){
                        swal({
                            title: 'Berhasil !',
                            text: $myDataObj['message'],
                            type: 'success',
                            showConfirmButton: false,
                            buttonsStyling: false,
                            allowOutsideClick: false
                        }).catch(swal.noop)
                        setTimeout(function(){
                            location.href = "accTradingFX.php?q=reset-success";
                        }, 2000);
                    }
                });
            }, function(dismiss) {
                // dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'
                if (dismiss === 'cancel') {
                    swal({
                        title: 'Dibatalkan',
                        text: 'Pemintaan reset akun trading dibatalkan',
                        type: 'error',
                        confirmButtonClass: "btn btn-info",
                        buttonsStyling: false
                    }).catch(swal.noop)
                }
            });
        });
    });
</script>
</head>
<body><span id="q"><?php echo (isset($_GET["q"])?$_GET["q"]:""); ?></span>
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="fa fa-bar-chart fa-2x"></i>
        </div>
        <div class="card-text"><h4 class="card-title">Trading Account</h4></div>
    </div>
    <div class="card-body card-fix">
    	<div class="col-md-6">
        	<div class="row">
            	<div class="card col-md-12">
                	<div class="card-body card-fix col-md-12">
                        <h4 class="profile">Trading Account (1)</h4>
                        <hr>
                        <div class="row">
						<?php
						$sql  = "SELECT t.*, m.mbrFirstName, st.stDesc, pairName, EAName, reset.stDesc AS stDescReset, IFNULL(reset.resaccStID,'-') AS resaccStID ";
                        $sql .= "FROM dtTradingAcc AS t ";
                        $sql .= "INNER JOIN dtMember AS m ON m.mbrUsername = t.tradeUsername ";
                        $sql .= " INNER JOIN msEA ON EAID=tradeEANum";
                        $sql .= " INNER JOIN msPair ON pairID = tradePair";
                        $sql .= " INNER JOIN msStatus st ON st.stID=t.tradeStID";
                        $sql .= " LEFT JOIN (";
                        $sql .= "   SELECT resaccUsername, resaccStID, stDesc FROM dtReqResetAcc";
                        $sql .= "   INNER JOIN msStatus ON resaccStID = stID";
                        $sql .= "   WHERE resaccStID ='".$DEF_STATUS_ONPROGRESS."' ";
                        $sql .= " ) AS reset ON reset.resaccUsername = t.tradeUsername";
                        $sql .= " WHERE t.tradeUsername = '" . $_SESSION["sUserName"] . "'";
                        $sql .= " AND (t.tradeStID='" . $DEF_STATUS_ACTIVE . "' OR t.tradeStID='" . $DEF_STATUS_ONPROGRESS . "' OR t.tradeStID='" . $DEF_STATUS_PENDING . "')";
                        $sql .= " ORDER BY tradeDate DESC LIMIT 1";
                        // echo $sql;
                        $isInput = false;
                        if ($query = $conn->query($sql)){
							if ($row = $query->fetch_assoc()){
                                if ($row['tradeStID'] != $DEF_STATUS_PENDING){
    						?>
                            <div class="col-md-4 profile">Expert Advisor</div><div class="col-md-8 profile-val"><?php echo $row['EAName'] ?></div>
                            <div class="col-md-4 profile">Pair</div><div class="col-md-8 profile-val"><?php echo $row['pairName'] ?></div>
                            <div class="col-md-4 profile">Account Number</div><div class="col-md-8 profile-val"><?php echo $row['tradeAccNo'] ?></div>
                            <div class="col-md-4 profile">Account Name</div><div class="col-md-8 profile-val"><?php echo $row['tradeName'] ?></div>
                            <div class="col-md-4 profile">Trade Password</div><div class="col-md-8 profile-val">***************************</div>
                            <div class="col-md-4 profile">Server</div><div class="col-md-8 profile-val"><?php echo $row['tradeServer'] ?></div>
                            <div class="col-md-4 profile">Status</div><div class="col-md-8 profile-val text-info"><?php echo $row['stDesc'] ?></div>
                        <?php
                                    if ($row['resaccStID'] != "-"){
                        ?>
                            <div class="col-md-4 profile">Status Reset</div><div class="col-md-8 profile-val text-danger"><?php echo $row['stDescReset'] ?></div>
                        <?php
                                    }
                                    if ($row['tradeStID'] == $DEF_STATUS_ACTIVE && $row['resaccStID'] != $DEF_STATUS_ONPROGRESS){ 
                        ?>
                            <div class="col-md-12 text-right">
                                <form id="reqresaccTrading" action="" method="post">
                                    <input type="hidden" name="type" id="type" value="reqresaccTrading">
                                    <input type="hidden" name="resaccUsername" id="resaccUsername" value="<?php echo $username; ?>">
                                    <input type="hidden" name="resaccNo" id="resaccNo" value="<?php echo $row['tradeAccNo'] ?>">
                                    <button type="submit" class="btn btn-round btn-rose" id="ResetAcc">Request Reset</button>
                        <?php 
                                        if ($errMsg != ""){ 
                        ?>
                                    <div class="row">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-8 text-danger">
                                          <div class="alert alert-info">
                                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                  <i class="material-icons">close</i>
                                              </button>
                                              <span><b> Info - </b> <?php echo $errMsg ?></span>
                                          </div>
                                        </div>
                                        <div class="col-md-2"></div>
                                    </div>
                        <?php 
                                        } 
                        ?>
                                </form>
                            </div>
                        <?php
                                    }
    							}else{
    								//don't have Acc Balance
                                    $isInput = true;
                                }
                            }else{
                                $isInput = true;
                            }
                            if ($isInput == true){
						?>
                            	<form action="accTradingFX.php" method="post" class="col-md-12" id="addaccTrading">
                                    <input type="hidden" name="type" id="type" value="addaccTrading">
                                	<?php if ($errMsg != ""){ ?>
                                    <div class="row">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-8 text-danger">
                                          <div class="alert alert-info">
                                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                  <i class="material-icons">close</i>
                                              </button>
                                              <span><b> Info - </b> <?php echo $errMsg ?></span>
                                          </div>
                                        </div>
                                        <div class="col-md-2"></div>
                                    </div>
                                    <?php } ?>

                                    <!-- This is entry for 1st trading account -->
                                    <input type="hidden" value="1" name="accOrder" id="accOrder">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="accEACode" class="bmd-label-floating">Expert Advisor</label>
                                            <select class="form-control" name="accEACode" id="accEACode">
                                                <option value="78">VisionEA</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="accPair" class="form-check-floating">Pair</label>
                                            <select class="form-control" name="accPair" id="accPair">
                                                <option value="EU">EURUSD</option>
                                                <!-- <option value="GU">GBPUSD</option> -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="accNumber" class="bmd-label-floating">Account Number</label>
                                        <input type="number" class="form-control" name="accNumber" id="accNumber" value="" maxlength="15" required>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="accName" class="bmd-label-floating">Account Name</label>
                                        <input type="text" class="form-control" name="accName" id="accName"  value="" maxlength="35" required>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="passwdTrade" class="bmd-label-floating">Trade Password</label>
                                        <input type="password" class="form-control" name="passwdTrade" id="passwdTrade"  value="" maxlength="25" required>
                                    </div>
                                    <div class="form-group col-md-12">
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" id="showPasswd" name="showPasswd" value="">
                                            <span class="form-check-sign">
                                                <span class="check"></span>
                                            </span>
                                            Show Password
                                        </label>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label class="bmd-label-floating">Server</label>
                                        <input type="text" class="form-control" name="server" id="server"  value="<?php echo $server ?>" maxlength="25" required>
                                  </div>
                                  <div class="footer">
                                    <button type="submit" id="UpdateAcc" name="UpdateAcc" class="btn btn-fill btn-rose col-md-12">Update Account</button>
                                    </div>
                                </form>
                            <?php
                            }
						} //end if query
						?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end card -->
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