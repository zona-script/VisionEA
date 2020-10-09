<?PHP
die();
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$q      =  (isset($_GET["q"]))?fValidateInput($_GET["q"]): "";

$username   = $_SESSION['sUserName'];
$currPac = $currPacName = $price = "";

$sql = "SELECT trPacID, pacName, mbrSponsor  FROM Transaction INNER JOIN dtMember ON trUsername = mbrUsername INNER JOIN msPackage ON pacID = trPacID WHERE trUsername ='".$username."' ORDER BY trDate DESC LIMIT 1";
$query = $conn->query($sql);
if ($row = $query->fetch_assoc()){
    $currPac = $row["trPacID"];
    $currPacName = $row["pacName"];
    $sponsor = $row['mbrSponsor'];

    $sql = "SELECT trPacID FROM Transaction WHERE trUsername ='".$sponsor."' ORDER BY trDate DESC LIMIT 1";
    $query = $conn->query($sql);
    if ($row = $query->fetch_assoc()){
        $pacIDSponsor       = $row["trPacID"];
    }else{
        echo (fSendStatusMessage("error", "Sponsor's Pac ID not found"));
        die();
    }
}


$errMsg = "";
//$accNumber = $accName = $accType = $accCode = "";
if (!empty($_POST)) { 
	
    $newPac = $securityPasswd =  $actUsername = "";

    $actUsername    = $_SESSION["sUserName"];
    $securityPasswd      = isset($_POST["securityPasswd"])? fValidateSQLFromInput($conn, $_POST["securityPasswd"]) : "";
    $newPac     = isset($_POST["newPac"])? fValidateInput($_POST["newPac"]) : "";
    $newPac     = strtolower($newPac);

    if ($actUsername != "" && $securityPasswd != "" && $newPac != ""){

        //Check Security Password
        if (!fCheckSecurityPassword($actUsername, $securityPasswd, $conn)){
          $errMsg .= "Security Password not match<br>";
        }else{
            //Get Package Price
            $pacPrice = $numOfVoucherRequired = 0;
            $sql = "SELECT pacPrice FROM msPackage WHERE pacID='" . $newPac . "'";
            if ($query = $conn->query($sql)){
                if ($query->num_rows > 0){
                    $row = $query->fetch_assoc();
                    $pacPrice = $row["pacPrice"];
                }
            }else{
                echo (fSendStatusMessage("error", mysqli_error($conn)));
                die();
            }

            //Get Current Package Price
            $currPacPrice = 0;
            $currPacID = "";
            $sql = "SELECT trPacID, pacPrice FROM Transaction t INNER JOIN msPackage ON pacID = trPacID ";
            $sql .= " WHERE trID = (SELECT trID FROM Transaction WHERE trUsername=t.trUsername ORDER BY trDate DESC LIMIT 1) ";
            $sql .= " AND trUsername = '".$actUsername."'";
            $query = $conn->query($sql);
            if ($row = $query->fetch_assoc()){
                $currPacPrice = $row['pacPrice'];
                $currPacID = $row['trPacID'];
            }

            if ($currPacPrice <= 0){
                $errMsg .= "Error requesting current package<br>";
            }else{
                $additionFee = 0;
                $additionFee = $pacPrice - $currPacPrice;
                if ($additionFee > 0){
                    $numOfVoucherRequired = ceil($additionFee / $DEF_VOUCHER_PRICE);   //Number of Voucher Required (@200)
                    //checking Voucher Balance
                    //$sql = "SELECT count(fivVCode) VoucherBalance FROM ((dtFundIn ";
                    $sql = "SELECT fivFinID, fivVCode FROM ((dtFundIn ";
                    $sql .= " inner join dtFundInVoucher on finID = fivFinID and finStatus='" . $DEF_STATUS_APPROVED . "')";
                    $sql .= " inner join dtVoucher on vCode = fivVCode and vStatus = '" . $DEF_STATUS_USED . "'";
                    $sql .= " and fivStatus = '" . $DEF_STATUS_ACTIVE ."')";
                    $sql .= " WHERE finMbrUsername='" . $_SESSION['sUserName'] . "'";
                    $arrVoucher = array();
                    if ($query = $conn->query($sql)){
                        if ($query->num_rows > 0){
                            while ($row = $query->fetch_assoc()){
                                //$VoucherBalance   = $row["VoucherBalance"];
                                $arrVoucher[] = array("fivFinID" => $row["fivFinID"], "fivVCode" => $row["fivVCode"]);  
                            }
                        }
                    }else{
                        echo (fSendStatusMessage("error", $conn->error));
                        die();
                    }
                    
                    $VoucherBalance = sizeof($arrVoucher);
                    if ($numOfVoucherRequired > $VoucherBalance){ //VoucherBalance not enough
                        echo (fSendStatusMessage("error", "Your Balance is not enough"));
                        die();
                    }
                }

                $conn->autocommit(false);

                //Transaction, 
                $arrData = array(
                    0 => array ("db" => "trUsername"    , "val" => $actUsername),
                    1 => array ("db" => "trPacID"           , "val" => $newPac),
                    2 => array ("db" => "trDate"            , "val" => "CURRENT_TIME()"),
                    3 => array ("db" => "trStatus"          , "val" => $DEF_STATUS_UPGRADE)
                    );
                    
                if (!fInsert("Transaction", $arrData, $conn)) {
                    echo (fSendStatusMessage("error", "<b>Update Transaction - </b>" . mysqli_error($conn)));
                    $conn->rollback();
                    die();
                }
                unset($arrData);

                //dtBnsSponsor, 
                $sponsorUsername    = fGetSponsorUsername($actUsername, $conn);
                $myDataObj  = json_decode(fGetDataPackage($conn, $sponsorUsername));
                $spPacID    = $myDataObj->{"pacID"};

                //get Level of generation
                $myDataObj  = json_decode(fGetDataPackage($conn, $actUsername));
                $numOfMatchingGen   = $myDataObj->{"pacMatchingGen"};


                $currSponsorBonus   = fGetBonus("SPONSOR", $currPacID, $spPacID, $conn);
                $newSponsorBonus    = fGetBonus("SPONSOR", $newPac, $spPacID, $conn);
                $sponsorBonus       = $newSponsorBonus - $currSponsorBonus;
                
                if ($sponsorBonus > 0){
                    $arrData = array(
                        0 => array ("db" => "bnsSpUsername"     , "val" => $sponsorUsername),
                        1 => array ("db" => "bnsSpTrUsername"   , "val" => $actUsername),
                        2 => array ("db" => "bnsSpTrPacID"      , "val" => $newPac),
                        3 => array ("db" => "bnsSpDate"         , "val" => "CURRENT_TIME()"),
                        4 => array ("db" => "bnsSpAmount"       , "val" => $sponsorBonus)
                        );
                        
                    if (!fInsert("dtBnsSponsor", $arrData, $conn)) {
                        echo (fSendStatusMessage("error", "<b>Update Bonus Sponsor - </b>" . $conn->error));
                        $conn->rollback();
                        die();
                    }
                    unset($arrData);
                }else{
                    //if sponsor bonus == 0, means error
                    echo (fSendStatusMessage("error", "<b>Get Bonus Sponsor Failed</b>"));
                    $conn->rollback();
                    die();
                }


                //dtBnsPassedUp
                $currPassedUpBonus  = fGetBonus("PASSED-UP", $currPacID, $pacIDSponsor, $conn);
                $newPassedUpBonus   = fGetBonus("PASSED-UP", $newPac, $pacIDSponsor, $conn);
                $passedUpBonus      = $newPassedUpBonus - $currPassedUpBonus;

                
                if ($passedUpBonus > 0){
                    $vipMembership  = fGetVIPUsername($sponsorUsername, 'vip', $conn);
                    if ($vipMembership != ""){
                        $arrData = array(
                            0 => array ("db" => "bnsPUUsername"     , "val" => $vipMembership), //VIP membership
                            1 => array ("db" => "bnsPUTrUsername"   , "val" => $actUsername),
                            2 => array ("db" => "bnsPUTrPacID"      , "val" => $newPac),
                            3 => array ("db" => "bnsPUDate"         , "val" => "CURRENT_TIME()"),
                            4 => array ("db" => "bnsPUAmount"       , "val" => $passedUpBonus)
                            );
                            
                        if (!fInsert("dtBnsPassedUp", $arrData, $conn)) {
                            echo (fSendStatusMessage("error", "<b>Update Bonus Passed-Up - </b>" . mysqli_error($conn)));
                            $conn->rollback();
                            die();
                        }
                        unset($arrData);
                    }
                } //if $passedUpBonus == 0, no need to save bonus. Not all member get passed up bonus, only VIP membership


                //Update dtFundInVoucher (status="USED", usedFor="ACTIVATION", usedOn=USERNAME)
                $arrData    = array(
                    "fivStatus"     => $DEF_STATUS_USED,
                    "fivUsedFor"    => $DEF_VOUCHER_USED_FOR_ACTIVATION,
                    "fivUserOn"     => $actUsername
                );
                
                $arrDataQuery = array();
                $counter = 0;
                //moving some data of arrVoucher to arrDataQuery 
                foreach ($arrVoucher as $key => $value){
                    if ($counter >= $numOfVoucherRequired) {
                        break;
                    }else{
                        $arrDataQuery = array (
                                    "fivFinID" => $value["fivFinID"], 
                                    "fivVCode" => $value["fivVCode"]
                                    );
                        
                        $counter++;
                        
                        if (!fUpdateRecord("dtFundInVoucher", $arrData, $arrDataQuery, $conn)){
                            echo (fSendStatusMessage("error", "<b>Update FundInVoucher - </b>" . $conn->error));
                            $conn->rollback();
                            die();
                        }
                        unset($arrDataQuery);
                    }
                }
                unset($arrData);


                //Create Information of Sponsorship's Generation
                $username   = $actUsername;
                $sqlInsert = "INSERT INTO dtGenSponsorship (genMbrUsername, genSPUsername, genLevel) VALUES (?,?,?)";
                $queryInsert = $conn->prepare($sqlInsert);
                $queryInsert->bind_param("sss", $genMbrUsername, $genSPUsername, $genLevel);
                //looking for number of level/generation of Matching of sponsor.

                //for ($i = 0; ($i <7 && $i < $numOfMatchingGen); $i++){
                for ($i = 0; $i <7; $i++){
                    //$sql = "SELECT mbrUsername, mbrSponsor FROM dtMember WHERE mbrUsername='".$username."' AND mbrUsername <> mbrSponsor";
                    
                    //$sql = "SELECT mbrUsername, mbrSponsor, t.trPacID, pacMatchingGen FROM dtMember ";
                    //$sql .= " INNER JOIN (";
                    //$sql .= " SELECT t.* FROM Transaction t ";
                    //$sql .= "   WHERE t.trID = (SELECT trID FROM Transaction where trUsername='".$username."' ORDER BY trDate DESC LIMIT 1)";
                    //$sql .= " ) t ON mbrUsername = t.trUsername";
                    //$sql .= " INNER JOIN msPackage ON pacID=t.trPacID";
                    //$sql .= " WHERE mbrUsername='".$username."' AND mbrUsername <> mbrSponsor";
                    $sql = "SELECT mbrUsername, mbrSponsor FROM dtMember ";
                    $sql .= " WHERE mbrUsername='".$username."' AND mbrUsername <> mbrSponsor";
                    if ($query = $conn->query($sql)){
                        if ($row=$query->fetch_assoc()){
                            $genMbrUsername   = $actUsername;
                            $genSPUsername    = $row['mbrSponsor'];
                            $genLevel         = $i+1;
                            $myDataObj  = json_decode(fGetDataPackage($conn, $genSPUsername));
                            $matchingGen   = $myDataObj->{"pacMatchingGen"};
                            
                            //check existing gen record
                            $sqlExist = "SELECT * FROM dtGenSponsorship WHERE genMbrUsername='". $genMbrUsername . "' AND genSPUsername='". $genSPUsername ."'";
                            //echo ("i: " . $i . "  >> " . $sqlExist . "<br>");
                            $queryExist = $conn->query($sqlExist);
                            if ($queryExist->num_rows > 0){
                                //existing record, skip
                                $username = $genSPUsername; //move sponsor to next username to get higher level of sponsorship
                            }else{
                                if ($matchingGen >= $genLevel){
                                    if ($queryInsert->execute()){
                                        //success
                                        $username = $genSPUsername; //move sponsor to next username to get higher level of sponsorship
                                        //echo ("insert >> " . $username. "<br>");
                                    }else{
                                        echo (fSendStatusMessage("error", "<b>Create Sponsorship's Generation Failed - </b>" . $conn->error));
                                        $conn->rollback();
                                        die();
                                    }
                                }else{
                                    //package not qualified for matching bonus
                                    //but keep to move to next sponsor
                                    $username = $genSPUsername; //move sponsor to next username to get higher level of sponsorship
                                }
                            }
                        }else{
                            break;
                        }
                    }else{
                        break;
                    }
                }
                
            
            //$conn->rollback();
            $conn->commit();
            fSendNotifToEmail("MEMBER_UPGRADE_PACKAGE", $actUsername);
            //fSendNotifToEmail("NEW_MEMBER_ACTIVATED", $actUsername);
            //echo (fSendStatusMessage("success", $actUsername));

            $query->close();
            $queryInsert->close();
            
            $conn->close(); 
            
            die();
            


            } //checking current package price
        }//end checking security password


		
	}else{
		$errMsg = "Incomplete Data";
	}
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Upgrade Package</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


<script>
	$(document).ready(function(e) {
		$("#updatePac").click(function(){
			//check package
			if ($.trim($('select[name="newPac"]').val()) == ''){
				demo.showNotification('top','center', 'info', 'Please select a <b>Package</b>');
				return false;
			}
		});
		
        $(document).ready(function(){
			if ($.trim($("#q").html()) == "info-success"){
				demo.showNotification('top','center', 'success', 'Update Account Successfully');
                $("#q").html('');
			}
		});


        $("form[name='formProfile']").on('submit', function() {
            var html = $("#updatePac").html();
            $("#updatePac").attr("disabled", true);
            $("#updatePac").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
            
        });

    });
</script>

</head>
<body><span id="q"><?php echo $q ?></span>
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="material-icons">control_point</i>
        </div>
        <div class="card-text"><h4 class="card-title">Upgrade Package</h4></div>
    </div>
    <?php
	$sql = "SELECT m.*, s.mbrUsername as spUsername, s.mbrFirstName as spName, u.mbrUsername as upUsername, u.mbrFirstName as upName, c.countryDesc, pacID, pacName FROM dtMember m ";
	$sql .= " INNER JOIN dtMember s on m.mbrSponsor = s.mbrUsername ";
	$sql .= " INNER JOIN dtMember u on m.mbrUpline = u.mbrUsername ";
	$sql .= " INNER JOIN msCountry c on m.mbrCountry = c.countryID ";
    $sql .= " INNER JOIN (SELECT * FROM Transaction WHERE trID = (SELECT trID FROM Transaction WHERE trUsername='". $_SESSION["sUserName"] . "' ORDER BY trDate DESC LIMIT 1)) t ON m.mbrUsername = t.trUsername ";
        $sql .= " INNER JOIN msPackage ON pacID = t.trPacID";

	$sql .= " WHERE m.mbrUsername = '" . $_SESSION["sUserName"] . "'";
	//echo $sql;
	if ($query = $conn->query($sql)){
		if ($row = $query->fetch_assoc()){	
			$tempEmail = $row["mbrEmail"];
			$posMid	= strpos($tempEmail, "@");
			$lenBetween = $posMid - 3;
			$email = substr($tempEmail, 0, 2) . str_pad("",$lenBetween, "*") . substr($tempEmail, $posMid-1,1).substr($tempEmail, $posMid);
	?>
    <div class="card-body card-fix">
		<div class="row">
        	<div class="col-md-6">
            	<div class="row">
                	<!-- <div class="col-md-4">Upline</div><div class="col-md-8"><?php //echo $row["upName"] . "(" . $row["upUsername"] . ")" ?></div> -->
                    	<div class="card col-md-11">
                        	<div class="card-body card-fix col-md-11">
                                <h4 class="profile">Upgade Package</h4>
                                <hr>
                                <div class="row">
                                	<form action="upPac.php" method="post" name="formProfile" class="col-md-12">
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

                                        <?php
                                        $sql = "SELECT mbrDate, IF(DATE_ADD( DATE(mbrDate), INTERVAL 6 MONTH ) <= CURRENT_DATE(), 'EXPIRED', 'UPGRADEABLE') UP FROM dtMember WHERE mbrUsername='$username'";
                                        //echo $sql; die();
                                        $query = $conn->query($sql);
                                        $row = $query->fetch_assoc();
                                        if ($row['UP'] == 'EXPIRED'){
                                        ?>
                                        <div class="row">
                                            <div class="col-md-2"></div>
                                            <div class="col-md-8 text-danger">
                                              <div class="alert alert-info">
                                                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                      <i class="material-icons">close</i>
                                                  </button>
                                                  <span><b> Info - </b> Upgrade Not Allowed</span>
                                              </div>
                                            </div>
                                            <div class="col-md-2"></div>
                                        </div>
                                        <?php
                                        }else{
                                        ?>
                                            <div class="row">
                                                <div class="col-md-5 profile">Current Package</div>
                                                <div class="col-md-7 profile-val"><?php echo $currPacName ?></div>

                                                <div class="col-md-5 profile">Upgrade to</div><div class="col-md-7 profile-val">
                                                    <select class="form-control selectpicker" data-size="5" data-style="btn btn-primary" name="newPac" title = "Package" id="newPac">
                                                        <option disabled selected>Select Package</option>
                                                        <?php
                                                            $sql = "SELECT * FROM msPackage ORDER BY pacPrice DESC";
                                                            $query = $conn->query($sql);
                                                            while ($row = $query->fetch_assoc()){
                                                                if ($currPac == $row['pacID']) break;
                                                                $selected =  ($pacID == $row["pacID"])?" selected " : "";
                                                                echo ("<option value='".$row["pacID"]."' " . $selected . ">".$row["pacName"]."</option>");
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-5 profile">&nbsp;</div><div class="col-md-7 profile-val">&nbsp;</div>
                                                
                                                <div class="col-md-5 profile">Security Password</div>
                                                <div class="col-md-7"><input type="password" name="securityPasswd" value="" class="form-control"></div>
                                                
                                            </div>
                                            <div class="footer">
                                                <button type="submit" id="updatePac" name="updatePac" class="btn btn-fill btn-rose col-md-12">Update Package</button>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </form>
                                <?php
									//} //end if $row
								//} //end if query
								?>
                                </div> <!-- end class=row -->
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end card -->
    <?php
		}
	}
	?>
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