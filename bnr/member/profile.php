<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$q      =  (isset($_GET["q"]))?fValidateInput($_GET["q"]): "";

if ($q == "upgradePac"){
    header("Location: upPac.php");
    die();
}
if ($q == "renewPac"){
    header("Location: renewPac.php");
    die();
}

//$regLink = $COMPANY_SITE . "member/?me=".$_SESSION['sUserName'];
$regLink = "https://visionea.net/".$_SESSION['sUserName'];

$errMsg = "";
$idType = $idNumber = $idFirstName = $idBOD = $accNumber = $accName = $accType = $accCode ="";
if (!empty($_POST)) {
    $data = fValidateSQLFromInput($conn, $_GET["data"]);
    $username   = $_SESSION['sUserName'];
    if ($data == "formVerifyID"){
        $oldvrFileName  = fValidateSQLFromInput($conn, $_POST["oldvrFileName"]);
        $strpos = strpos($oldvrFileName, ".");
        $oldFileName = substr($oldvrFileName,0, $strpos);
        if ($oldFileName == ""){//jika CP kosong (tidak ada data)
            $id = 1;
        }else{
            $id = substr($oldFileName,-2);
        }
        $statusvrid     = fValidateSQLFromInput($conn, $_POST["statusvrid"]);
        $idType         = fValidateSQLFromInput($conn, $_POST["idType"]); 
        $idNumber       = fValidateSQLFromInput($conn, $_POST["idNumber"]);
        $idNumber       = preg_replace('/\s/', '', $idNumber);
        $idFirstName    = fValidateSQLFromInput($conn, $_POST["idFirstName"]); 
        $idLastName     = fValidateSQLFromInput($conn, $_POST["idLastName"]);
        $idBOD          = fValidateSQLFromInput($conn, $_POST["idBOD"]);

        $imageFileType  = strtolower(pathinfo(basename($_FILES["fileuploadid"]["name"]),PATHINFO_EXTENSION));
        $target_dir     = "photo_verify/";
        $filename       = "vr"."_".$username."-".$id;
        $target_file = $target_dir . $filename;
        // echo $statusvrid;die();
        if ($oldFileName == $filename) {
            $target_delete = $target_dir . $oldvrFileName;
            if (file_exists($target_delete)){
                unlink($target_delete) or die(fSendStatusMessage("error", "gagal hapus gambar"));
            }
            $id = intval($id);
            $id = $id + 1;
            $strid = str_pad($id, 2, '0', STR_PAD_LEFT);
            $filename       = "vr"."_".$username."-".$strid.".".$imageFileType;
            $target_file    = $target_dir . $filename;
            // echo "exist $strid $oldvrFileName : $filename"; die();
        }else{
            $strid = str_pad($id, 2, '0', STR_PAD_LEFT);
            $filename       = "vr"."_".$username."-".$strid.".".$imageFileType;
            $target_file    = $target_dir . $filename;
            // echo "tidak sama $strid $oldvrFileName : $filename"; die();
        }
        // echo " $oldvrFileName || $idType || $idNumber || $idFirstName || $idLastName || $idBOD || $statusvrid || $imageFileType || $filename"; die();
        $okUpload = true;

        if (EMPTY($_FILES["fileuploadid"]["tmp_name"])){
            $errMsg .= "There is no file to upload.<br>";
            $okUpload = false;
        }
        $check = getimagesize($_FILES["fileuploadid"]["tmp_name"]);
        if($check) {
            //echo "File is an image - " . $check["mime"] . ".";
        }else {
            $errMsg .= "File is not an image.<br>";
            $okUpload = false;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $errMsg .= "only JPG, JPEG, PNG & GIF files are allowed.<br>";
            $okUpload = false;
        }

        if (!fCekVerificationID($conn, $username, $idNumber)){
            $errMsg .= "ID Number has been used.<br>";
            $okUpload = false;
        }

        if ($idType != "" && $idNumber != "" && $idFirstName != "" && $idBOD != ""){
            if ($okUpload){
                $conn->autocommit(false);
                if ($statusvrid == "-") { //belum pernah verify ID
                    $arrData = array(
                        0 => array ("db" => "vrUsername"    , "val" => $username),
                        1 => array ("db" => "vrFileName"    , "val" => $filename),
                        2 => array ("db" => "vrType"        , "val" => $idType),
                        3 => array ("db" => "vrIDNum"       , "val" => $idNumber),
                        4 => array ("db" => "vrFirstName"   , "val" => $idFirstName),
                        5 => array ("db" => "vrLastName"    , "val" => $idLastName),
                        6 => array ("db" => "vrBOD"         , "val" => $idBOD),
                        7 => array ("db" => "vrStatus"      , "val" => $DEF_STATUS_ONPROGRESS),
                        8 => array ("db" => "vrDate"        , "val" => "CURRENT_TIME()")
                    );
                    if (fInsert("dtVerify", $arrData, $conn)){
                        if (move_uploaded_file($_FILES["fileuploadid"]["tmp_name"], $target_file)){
                            $conn->commit();
                            header("Location: profile.php?q=id-success"); die();
                        }else{
                            $conn->rollback();
                            $errMsg .= "Upload ID Failed #1.<br>";
                        }
                        // if (fSendNotifToEmail("VERIFY_ID", $username)){
                        //     header("Location: profile.php?q=id-success");
                        // }else{
                        //     $errMsg .= "Upload ID Failed #.<br>";
                        // }
                    }else{
                        $errMsg .= "Upload ID Failed #2.<br>";
                    }
                }else if ($statusvrid == $DEF_STATUS_DECLINED){ // verify ID ditolak sebelumnya
                    $arrData = array(
                        "vrFileName"    => $filename,
                        "vrType"        => $idType,
                        "vrIDNum"       => $idNumber,
                        "vrFirstName"   => $idFirstName,
                        "vrLastName"    => $idLastName,
                        "vrBOD"         => $idBOD,
                        "vrStatus"      => $DEF_STATUS_ONPROGRESS,
                        "vrDate"        => "CURRENT_TIME()"
                    );
                    $arrDataQuery = array(
                        "vrUsername" => $username,
                        "vrType"     => $idType
                    );
                    if (!fUpdateRecord("dtVerify", $arrData, $arrDataQuery, $conn)){
                        $errMsg .= "Upload ID Failed #3.<br>";
                    }else{
                        if (move_uploaded_file($_FILES["fileuploadid"]["tmp_name"], $target_file)){
                            $conn->commit();
                            header("Location: profile.php?q=id-success"); die();
                        }else{
                            $conn->rollback();
                        }
                        // if (fSendNotifToEmail("VERIFY_ID", $username)){
                        //     header("Location: profile.php?q=id-success");
                        // }else{
                        //     $errMsg .= "Upload ID Failed #.<br>";
                        // }
                    }
                }else{
                    $errMsg .= "Upload ID Failed #4.<br>";
                }
            }
        }else{
            $errMsg .= "Incomplete Data.<br>";
        }        
    }else if ($data== "formProfile"){
    	$accNumber = fValidateSQLFromInput($conn, $_POST["accNumber"]); 
    	$accName = fValidateSQLFromInput($conn, $_POST["accName"]); 
    	$accType = fValidateSQLFromInput($conn, $_POST["accType"]); 
    	$accCode = fValidateSQLFromInput($conn, (isset($_POST['accCode'])))?fValidateSQLFromInput($conn, $_POST['accCode']): '';
        if ($accNumber != "" && $accType != "" && $username != ""){
            //&& $accName != "" && $accCode != "" 
            if (fCekVerification($conn, $username, $accName)){
                //Sudah Verifikasi ID
        		$arrData = array(
    				0 => array ("db" => "payMbrUsername", "val" => $username),
    				1 => array ("db" => "payAcc"		, "val" => $accNumber),
    				2 => array ("db" => "payAccName"	, "val" => $accName),
    				3 => array ("db" => "payPTID"		, "val" => $accType),
    				4 => array ("db" => "payCode"		, "val" => $accCode),
    				5 => array ("db" => "payStatus"		, "val" => $DEF_STATUS_ACTIVE),
    				6 => array ("db" => "payDate"	, "val" => "CURRENT_TIME()")
    			);
        		if (fInsert("dtPaymentAcc", $arrData, $conn)){
        			//insert success
        			//send email for activation
        			fSendNotifToEmail("UPDATE_PAYMENT_ACCOUNT", $username);
        			$conn->close();

        			//redirect to success page
        			header("Location: profile.php?q=info-success");
                    die();
        			
        		}else{
        			//insert fail	
        			//back for re-register
        			$errMsg = "Update Account Failed";
        		} // end else
    		}else{
                $errMsg = "Anda belum Verifikasi ID/KTP atau Nama Tidak Cocok";
            }
    	}else{
    		$errMsg = "Incomplete Data";
    	}
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Profile </title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<style>
/* Popup container - can be anything you want */
.popup {
    position: relative;
    display: inline-block;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* The actual popup */
.popup .popuptext {
    visibility: hidden;
    width: 160px;
    background-color: #555;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 8px 0;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -80px;
}

/* Popup arrow */
.popup .popuptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}

/* Toggle this class - hide and show the popup */
.popup .show {
    visibility: visible;
    -webkit-animation: fadeIn 1s;
    animation: fadeIn 1s;
}

/* Add animation (fade in the popup) */
@-webkit-keyframes fadeIn {
    from {opacity: 0;} 
    to {opacity: 1;}
}

@keyframes fadeIn {
    from {opacity: 0;}
    to {opacity:1 ;}
}
</style>


<script>
	$(document).ready(function(e) {
        if ($.trim($("#q").html()) == "info-success"){
            demo.showNotification('top','center', 'success', 'Update Account Successfully');
            $("#q").html('');
        }else if ($.trim($("#q").html()) == "id-success"){
            demo.showNotification('top','center', 'success', 'ID has been uploaded');
            $("#q").html('');
        }else if ($.trim($("#q").html()) == "renew-success"){
            demo.showNotification('top','center', 'success', 'Renew Account Successfully');
            $("#q").html('');
            setTimeout(function(){parent.location.href="./";}, 2000);
        }

        $("form[name='formVerifyID']").on('submit', function() {
            var html = $("#updateID").html();
            $("#updateID").html(html + '&nbsp; <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
            $("#updateID").attr("disabled", true);
            var input, file;
            if (!window.FileReader){
                demo.showNotification('top','center', 'info', "The file API isn't supported on this browser yet.");
                return false;
            }

            input = document.getElementById('fileuploadid');
            if (!input){
                demo.showNotification('top','center', 'info', "Um, couldn't find the fileinput element.");
                $("#updateID").attr("disabled", false);
                $("#updateID").html("Upload ID");
                return false;
            }else if (!input.files){
                demo.showNotification('top','center', 'info', "This browser doesn't seem to support the upload file");
                $("#updateID").attr("disabled", false);
                $("#updateID").html("Upload ID");
                return false;
            }else if (!input.files[0]){
                demo.showNotification('top','center', 'info', "Please select a file to upload");
                $("#updateID").attr("disabled", false);
                $("#updateID").html("Upload ID");
                return false;
            }else{
                file = input.files[0];
                if (file.size > 2097151){ // lebih besar dari 2MB
                    demo.showNotification('top','center', 'info', file.name + " exceeds the maximum upload size for this site");
                    $("#updateID").attr("disabled", false);
                    $("#updateID").html("Upload ID");
                    return false;
                }
            }
        });

		$("#UpdateAcc").click(function(){
			//check package
			if ($.trim($('select[name="accType"]').val()) == ''){
				demo.showNotification('top','center', 'info', 'Please select an <b>Account Type</b>');
				return false;
			}
		});

        $("#updatePac").click(function(){
            //Update package
            $(location).attr('href', 'profile.php?q=upgradePac');
        });
        $("#renewPac").click(function(){
            //Update package
            $(location).attr('href', 'profile.php?q=renewPac');
        });
		
        $('#accType').on('change', function(){
            if ($(this).val().toUpperCase() == "BTC"){
                //$('#idAccNumber').css({"display":"block","visibility":"hidden"});
                $('#lblAccNumber').html('BTC Address');
                $('#idAccName').css({"display":"none","visibility":"hidden"});
                $('#idAccCode').css({"display":"none","visibility":"hidden"});
                //$('#idAccName').removeAttr('required');
                //$('#idAccCode').removeAttr('required');
                //document.getElementById("idAccName").required = false;
                //document.getElementById("idAccCode").required = false;
            }else if ($(this).val().toUpperCase() == "PP"){
                $('#lblAccNumber').html('Paypal ID / Email');
                $('#idAccName').css({"display":"block","visibility":"visible"});
                $('#idAccCode').css({"display":"none","visibility":"hidden"});
            }else{
                $('#lblAccNumber').html('Account Number');
                $('#idAccNumber').css({"display":"block","visibility":"visible"});
                $('#idAccName').css({"display":"block","visibility":"visible"});
                $('#idAccCode').css({"display":"block","visibility":"visible"});
            }

        });

        $("#accNumber").on("blur", function(){
            var accNumber = $(this).val();
            if (!isNumberInput(accNumber)){
                demo.showNotification('top','center', 'danger', '<b>Account Number</b> must number');
                $(this).val("");
                $(this).focus();
                return false;
            }
        });


        $("form[name='formProfile']").on('submit', function() {
            var html = $("#UpdateAcc").html();
            if ($('#accType').val().toUpperCase() == "BTC"){

            }else if ($('#accType').val().toUpperCase() == "PP"){
                if ($('#accName').val() != ""){
                    $("#UpdateAcc").attr("disabled", true);
                    $("#UpdateAcc").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
                }else{
                    alert ("Account Name required");
                    return (false);
                }
            }else{
                 if ($('#accName').val() != "" && $('#accCode').val() != ""){
                     $("#UpdateAcc").attr("disabled", true);
                     $("#UpdateAcc").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
                 }else{
                    alert ("Account Name and Account Code required");
                    return(false);
                 }
            }
        });
    });
</script>

<script>
    function fPopUp() {
        var copyText = document.getElementById("regLink");
        copyText.select();
        document.execCommand("Copy");

        var popup = document.getElementById("myPopup");
        popup.classList.toggle("show");
    }

    function fPopUpOut(){
        var popup = document.getElementById("myPopup");
        if ($("#myPopup").css("visibility") == "visible") {
            //$("#myPopup").css({"visibility":"hidden";});
            popup.classList.toggle("show");
        }
    }

    function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }

    function isNumberInput(input){
        var regExp = new RegExp('[a-zA-Z]');
        if (regExp.test(input))
            return false;
        return true;
    }

</script>

</head>
<body>
<span id="q" style="display:none;"><?php echo $q ?></span>
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="material-icons">face</i>
        </div>
        <div class="card-text"><h4 class="card-title">My Profile</h4></div>
    </div>
    <div class="card-body card-fix">
		<div class="row">
        	<div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card col-md-11">
                            <div class="card-body card-fix col-md-11">
                            <?php
                            $sql     = "SELECT dtMember.*, IFNULL(vrStatus,'-') AS vrStatus, vrFileName, vrIDNum FROM dtMember";
                            $sql    .= " LEFT JOIN dtVerify ON vrUsername = mbrUsername";
                            $sql    .= " WHERE mbrUsername = '".$_SESSION["sUserName"]."'";
                            $query = $conn->query($sql);
                            if ($row = $query->fetch_assoc()){
                                $mbrIDType = $row['mbrIDType'];
                                $mbrIDN = $row['mbrIDN'];
                                $mbrFirstName = $row['mbrFirstName'];
                                $mbrLastName = $row['mbrLastName'];
                                $mbrBOD = $row['mbrBOD'];
                                $vrFileName = $row['vrFileName'];
                                $vrIDNum = $row['vrIDNum'];
                                $vrStatus = $row['vrStatus'];
                                if ($vrStatus == $DEF_STATUS_ONPROGRESS){
                                    $status = "<span class='text-warning'>On Progress</span>";
                                }elseif ($vrStatus == $DEF_STATUS_APPROVED){
                                    $status = "<span class='text-success'>Approved</span>";
                                }
                                if ($vrStatus == $DEF_STATUS_APPROVED || $vrStatus == $DEF_STATUS_ONPROGRESS){

                            ?>
                                <h4 class="profile">Member ID</h4>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4 profile">ID Number</div>
                                    <div class="col-md-8 profile"><?php echo $vrIDNum; ?></div>
                                    <div class="col-md-4 profile">Status</div>
                                    <div class="col-md-8 profile"><?php echo $status; ?></div>
                                </div>
                            <?php
                                }else{ // not upload id yet
                            ?>
                                <h4 class="profile">Verify ID</h4>
                                <hr>
                                <div class="row">
                                    <form action="profile.php?data=formVerifyID" method="post" enctype="multipart/form-data" name="formVerifyID" class="col-md-12">
                                        <input type="hidden" name="statusvrid" id="statusvrid" value="<?php echo $vrStatus; ?>">
                                        <input type="hidden" name="oldvrFileName" id="oldvrFileName" value="<?php echo $vrFileName; ?>">
                                        <?php 
                                        if ($errMsg != "" && $data == "formVerifyID"){ 
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
                                        <div class="form-group col-md-12 text-center">
                                            <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail img-raised" style="border-radius: 27px;">
                                                    <img src="../assets/img/id-card.png">
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
                                                <div>
                                                    <span class="btn btn-raised btn-round btn-rose btn-file">
                                                        <span class="fileinput-new " onclick="$('#fileuploadid').click();">Select image</span>
                                                        <span class="fileinput-exists" onclick="$('#fileuploadid').click();">Change</span>
                                                        <input type="file" name="fileuploadid" id="fileuploadid" />
                                                    </span>
                                                    <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
                                                        <i class="fa fa-times"></i> Remove
                                                    </a>
                                               </div>
                                            </div>
                                        </div>
                                        <small class="text-warning">*Enter valid data according to your ID</small>
                                        <div class="form-group col-md-12">
                                            <label class="col-form-label">ID Type</label>
                                            <select class="selectpicker col-md-8" data-size="5" data-style="btn btn-primary" name="idType" title = "ID Type" id="idType">
                                                <option disabled selected>Select ID Type</option>
                                                <?php
                                                    $sql  = "SELECT * FROM msIDType";
                                                    $sql .= " ORDER BY idtType ASC";
                                                    $query = $conn->query($sql);
                                                    while ($row = $query->fetch_assoc()){
                                                        $selected =  ($mbrIDType == $row["idtCode"])?" selected " : "";
                                                        echo ("<option value='".$row["idtCode"]."' " . $selected . ">".$row["idtType"]."</option>");
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label for="idNumber" class="bmd-label-floating">ID Number</label>
                                            <input type="text" class="form-control" name="idNumber" id="idNumber" maxlength="40" value="<?php echo $mbrIDN; ?>" title="Your ID Number" required>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="idFirstName" class="bmd-label-floating">First Name</label>
                                                    <input type="text" class="form-control" name="idFirstName" id="idFirstName" maxlength="40"  title="Your First Name" value="<?php echo $mbrFirstName; ?>" readonly required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="idLastName" class="bmd-label-floating">Last Name</label>
                                                    <input type="text" class="form-control" name="idLastName" id="idLastName" maxlength="40"  title="Your Last Name" value="<?php echo $mbrLastName; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label for="idBOD" class="bmd-label-floating">Birth of Date</label>
                                            <input type="date" class="form-control" name="idBOD" value="<?php echo $mbrBOD ?>">
                                        </div>
                                        <div class="footer">
                                            <button type="submit" id="updateID" name="updateID" class="btn btn-fill btn-rose col-md-12">Upload ID</button>
                                        </div>
                                    </form>
                                </div>
                            <?php
                                }
                            }
                            ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="card col-md-11">
                                <div class="card-body card-fix col-md-11">
                                    <h4 class="profile">Account</h4>
                                    <hr>
                                    <div class="row">
                                    <?php
                                    $sql = "SELECT pay.*, pt.ptCat, pt.ptDesc FROM dtPaymentAcc pay INNER JOIN msPaymentType pt on payPTID=ptID";
                                    $sql .= " WHERE payMbrUsername='" . $_SESSION['sUserName'] . "' AND payStatus='" . $DEF_STATUS_ACTIVE . "'";
                                    // fPrint($sql);
                                    if ($query = $conn->query($sql)){
                                        if ($row = $query->fetch_assoc()){  
                                            if(strtoupper($row['payPTID']) == "BTC"){
                                                $labelAcc = "BTC Address";
                                            }else{
                                                $labelAcc = "Account Number/Email";
                                            }
                                    ?>
                                        <div class="col-md-4 profile"><?php echo $labelAcc ?></div><div class="col-md-8 profile-val"><?php echo $row['payAcc'] ?></div>
                                        <?php if(strtoupper($row['payPTID']) != "BTC"){?>
                                        <div class="col-md-4 profile">Account Name</div><div class="col-md-8 profile-val"><?php echo $row['payAccName'] ?></div>
                                        <?php } ?>
                                        <div class="col-md-4 profile">Account Type</div><div class="col-md-8 profile-val"><?php echo $row['ptDesc'] ?></div>
                                        <?php 
                                        // if ($row['payCode'] != "") { 
                                        //     echo '<div class="col-md-4 profile">SWIFT/BIC Code</div><div class="col-md-8 profile-val">'. $row['payCode'] . '</div>';
                                        // } 
                                        ?>
                                    <?php
                                        }else{
                                            //don't have Acc Balance
                                    ?>
                                        <form action="profile.php?data=formProfile" method="post" name="formProfile" class="col-md-12">
                                            <?php if ($errMsg != "" && $data == "formProfile"){ ?>
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
                                            <div class="col-md-12">
                                                <label class="col-form-label">Account Type</label>
                                                <select class="selectpicker col-md-8" data-size="5" data-style="btn btn-primary" name="accType" title = "Account Type" id="accType">
                                                    <option disabled selected>Select Account Type</option>
                                                    <?php
                                                        $sql = "SELECT * FROM msPaymentType WHERE ptCat <> '". $DEF_CATEGORY_INTERNAL_TRANSFER ."' ";
                                                        $sql .= " AND ptStID='".$DEF_STATUS_ACTIVE."' ORDER BY ptDesc ASC";

                                                        $query = $conn->query($sql);
                                                        while ($row = $query->fetch_assoc()){
                                                            $selected =  ($accType == $row["ptID"])?" selected " : "";
                                                            echo ("<option value='".$row["ptID"]."' " . $selected . ">".$row["ptDesc"]."</option>");
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-12" id="idAccNumber">
                                                <label for="accNumber" id="lblAccNumber" class="bmd-label-floating">Account Number</label>
                                                <input type="text" class="form-control" name="accNumber" id="accNumber" maxlength="40" value="<?php echo $accNumber; ?>" title="Your Account Number/Email or BTC Address" onkeypress="return isNumberKey(event)"  required>
                                            </div>
                                            <div class="form-group col-md-12" id="idAccName">
                                                <label for="accName" class="bmd-label-floating">Account Name</label>
                                                <input type="text" class="form-control" name="accName" id="accName"  value="<?php echo $accName ?>" >
                                            </div>
                                            <!-- <div class="form-group col-md-12" id="idAccCode">
                                                <label for="swiftCode" class="bmd-label-floating">SWIFT / BIC Code</label>
                                                <input type="text" class="form-control" name="accCode" id="accCode"  value="<?php echo $accCode ?>" >
                                            </div> -->
                                            <div class="footer">
                                                <button type="submit" id="UpdateAcc" name="UpdateAcc" class="btn btn-fill btn-rose col-md-12">Update Account</button>
                                            </div>
                                        </form>
                                    <?php
                                        } //end if $row
                                    } //end if query
                                    ?>
                                    </div> <!-- end class=row -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                <?php
                $sql = "SELECT m.*, s.mbrUsername as spUsername, s.mbrFirstName as spName, u.mbrUsername as upUsername, u.mbrFirstName as upName, c.countryDesc, ";
                $sql .= " pacID, pacName, IF( DATE_ADD( DATE(m.mbrDate), INTERVAL 6 MONTH ) > CURRENT_DATE(), 'ALLOWED_UP', 'NOT_ALLOWED') AS Upgradeable, ";
                //$sql .= " IF( DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 -1) MONTH) < CURRENT_DATE(), 'ALLOW_RENEW', 'RENEW_NOT_ALLOWED') AS Renew, ";

                //tambahkan utk validasi 1bulan setelah expired, sdh tidak bisa renew >> && (DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 +1) MONTH) >= CURRENT_DATE())
                $nBlnSebelum = 7;
                $nBlnSetelah = 3;
                $sql .= " IF((DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 - " . $nBlnSebelum . ") MONTH) < CURRENT_DATE()) && (DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 + " . $nBlnSetelah . ") MONTH) >= CURRENT_DATE()), 'ALLOW_RENEW', 'RENEW_NOT_ALLOWED') AS Renew, ";
                $sql .= " trDate, DATE_ADD( m.mbrDate, INTERVAL (trThn*12) MONTH) AS ExpiredDate FROM dtMember m ";
                $sql .= " INNER JOIN dtMember s on m.mbrSponsor = s.mbrUsername ";
                $sql .= " INNER JOIN dtMember u on m.mbrUpline = u.mbrUsername ";
                $sql .= " INNER JOIN msCountry c on m.mbrCountry = c.countryID ";
                $sql .= " INNER JOIN (SELECT * FROM Transaction WHERE trID = (SELECT trID FROM Transaction WHERE trUsername='". $_SESSION["sUserName"] . "' ORDER BY trDate DESC LIMIT 1)) t ON m.mbrUsername = t.trUsername ";
                    $sql .= " INNER JOIN msPackage AS pac ON pacID = t.trPacID";
                $sql .= " WHERE m.mbrUsername = '" . $_SESSION["sUserName"] . "'";
                // echo $sql;
                if ($query = $conn->query($sql)){
                    if ($row = $query->fetch_assoc()){  
                        $tempEmail = $row["mbrEmail"];
                        $posMid = strpos($tempEmail, "@");
                        $lenBetween = $posMid - 3;
                        $email = substr($tempEmail, 0, 2) . str_pad("",$lenBetween, "*") . substr($tempEmail, $posMid-1,1).substr($tempEmail, $posMid);
                ?>
                    <div class="col-md-2 profile">Your Sponsor</div>
                    <div class="col-md-10 profile-val"><?php echo $row["spName"] . " (" . $row["spUsername"] . ")" ?></div>
                    <!-- <div class="col-md-2">Upline</div><div class="col-md-10"><?php //echo $row["upName"] . "(" . $row["upUsername"] . ")" ?></div> -->
                    <div class="col-md-2 profile">Username</div><div class="col-md-10 profile-val"><b><?php echo $row["mbrUsername"] ?></b></div>
                    <div class="col-md-2 profile">Name</div><div class="col-md-10 profile-val"><?php echo $row["mbrFirstName"]." ".$row['mbrLastName'] ?></div>
                    <div class="col-md-2 profile">Email</div><div class="col-md-10 profile-val"><?php echo $email ?></div>
                    <div class="col-md-2 profile">Mobile</div><div class="col-md-10 profile-val"><?php echo $row["mbrMobileCode"] . "-" . $row["mbrMobile"] ?></div>
                    
                    <div class="col-md-2 profile">Birth of Day</div><div class="col-md-10 profile-val"><?php echo date("d M Y", strtotime($row["mbrBOD"])) ?></div>
                    <div class="col-md-2 profile">Address</div><div class="col-md-10 profile-val"><?php echo $row["mbrAddr"] ?></div>
                    <div class="col-md-2 profile">City</div><div class="col-md-10 profile-val"><?php echo $row["mbrCity"] ?></div>
                    <div class="col-md-2 profile">State/Province</div><div class="col-md-10 profile-val"><?php echo $row["mbrState"] ?></div>
                    <div class="col-md-2 profile">Country</div><div class="col-md-10 profile-val"><?php echo $row["countryDesc"] ?></div>
                    <!-- <div class="col-md-2 profile">Package</div><div class="col-md-10 profile-val"><?php echo $row["pacName"] ?></div> -->
                    <div class="col-md-2 profile">Join Date</div><div class="col-md-10 profile-val"><?php echo $row["mbrDate"] ?></div>
                    <div class="col-md-2 profile">Renew Date</div><div class="col-md-10 profile-val"><?php echo $row["trDate"] ?></div>
                    <div class="col-md-2 profile">Date of Expiration</div><div class="col-md-10 profile-val"><?php echo $row["ExpiredDate"] ?></div>
                    <?php
                        //UPGRADE PACKAGE
                        //if (strtoupper($row['pacID']) != "VIP" && strtoupper($row['Upgradeable']) == "ALLOWED_UP"){
                    ?>
                    <!-- <div class="col-md-4 profile">&nbsp;</div><div class="col-md-8 profile-val">
                        <button id="updatePac" class="btn btn-fill btn-rose popup">Upgrade Package</button>
                    </div> -->
                    <?php
                        //}
                        //Renew Package
                        if (strtoupper($row['Renew']) == "ALLOW_RENEW"){
                    ?>
                    <!-- //Renew Package -->
                    <div class="col-md-4 profile">&nbsp;</div><div class="col-md-8 profile-val">
                        <button id="renewPac" class="btn btn-fill btn-rose popup">Renew Package</button>
                    </div>
                    <?php
                        }
                    ?>
                </div>
                <div class="row">
                    <div class="col-md-12">&nbsp;</div>
                    <div class="col-md-2 profile">Your Referral Link:</div>
                    <div class="col-md-6"><input type="text" value="<?php echo $regLink ?>" id="regLink" class="form-control" readonly="true" style="background: transparent; border-bottom: solid; border-bottom-width: 1px "></div>
                    <div class="col-md-2">
                            <button onclick="fPopUp()" onmouseout="fPopUpOut()" class="btn btn-fill btn-rose popup">
                                Copy link
                                <span class="popuptext" id="myPopup">Copied</span>
                            </button>
                    </div>
                </div>
                <?php
                    }
                }
                ?>
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