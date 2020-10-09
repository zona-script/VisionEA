<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$q      =  (isset($_GET["q"]))?fValidateInput($_GET["q"]): "";
$username   = $_SESSION['sUserName'];
$errMsg = "";
$benIdType = $benIdNum = $benIdNum = $benFirstName = $benLastName = $benBOD = $benRelationType = "";

if (!empty($_POST)) {
    $formdata = (isset($_GET["data"]))?fValidateSQLFromInput($conn, $_GET["data"]): "";
    if ($formdata == "formBeneficiary"){
        $benIdType       = (isset($_POST["benIdType"]))?fValidateSQLFromInput($conn, $_POST["benIdType"]): ""; 
        $benIdNum        = (isset($_POST["benIdNum"]))?fValidateSQLFromInput($conn, $_POST["benIdNum"]): "";
        $benIdNum        = preg_replace('/\s/', '', $benIdNum);
        $benFirstName    = (isset($_POST["benFirstName"]))?fValidateSQLFromInput($conn, $_POST["benFirstName"]): "";
        $benLastName     = (isset($_POST["benLastName"]))?fValidateSQLFromInput($conn, $_POST["benLastName"]): "";
        if ($benLastName == ""){
            $benLastName = $benFirstName;
        }
        $benBOD          = (isset($_POST["benBOD"]))?fValidateSQLFromInput($conn, $_POST["benBOD"]): "";
        $benRelationType = (isset($_POST["benRelationType"]))?fValidateSQLFromInput($conn, $_POST["benRelationType"]): "";

        $imageFileType  = strtolower(pathinfo(basename($_FILES["benFileID"]["name"]),PATHINFO_EXTENSION));
        // echo "$benIdType || $benIdNum || $benFirstName || $benLastName || $benBOD || $benRelationType || $imageFileType"; die();
        $target_dir     = "photo_verify/";
        $filename       = "ben"."_".$username.".".$imageFileType;
        $target_file    = $target_dir.$filename;

        $okUpload = true;
        if (EMPTY($_FILES["benFileID"]["tmp_name"])){
            $errMsg .= "There is no file to upload.<br>";
            $okUpload = false;
        }
        $check = getimagesize($_FILES["benFileID"]["tmp_name"]);
        if($check !== false) {
            //echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = true;
        }else {
            $errMsg .= "File is not an image.<br>";
            $uploadOk = false;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $errMsg .= "only JPG, JPEG, PNG & GIF files are allowed.<br>";
            $uploadOk = false;
        }

        //cek verify ID member
        $sql  = " SELECT * FROM dtVerify";
        $sql .= " WHERE vrUsername = '".$username."' ";
        $result = $conn->query($sql);
        if ($result->num_rows > 0){
            if ($row=$result->fetch_assoc()){
                if ($row['vrStatus'] != $DEF_STATUS_APPROVED){
                    $errMsg .= "Your ID has not yet been verified";
                    $uploadOk = false;
                }else{
                    if ($row['vrIDNum'] == $benIdNum){
                        $errMsg .= "Your ID and Beneficiary ID must be different";
                        $uploadOk = false;  
                    }
                }
            }
        }else{
            $errMsg .= "You have not uploaded your ID, please upload your ID";
            $uploadOk = false;
        }        

        if ($benIdType != "" && $benIdNum != "" && $benIdNum != "" && $benFirstName != "" && $benLastName != "" && $benBOD != "" && $benRelationType != "" && $uploadOk == true){
            $conn->autocommit(false);
            $arrData = array(
                0 => array ("db" => "BenMbrUsername"    , "val" => $username),
                1 => array ("db" => "BenFileID"         , "val" => $filename),
                2 => array ("db" => "BenIDType"         , "val" => $benIdType),
                3 => array ("db" => "BenIDNum"          , "val" => $benIdNum),
                4 => array ("db" => "BenBOD"            , "val" => $benBOD),
                5 => array ("db" => "BenFirstName"      , "val" => $benFirstName),
                6 => array ("db" => "BenLastName"       , "val" => $benLastName),
                7 => array ("db" => "BenRelationType"   , "val" => $benRelationType),
                8 => array ("db" => "BenUpdateDate"     , "val" => "CURRENT_TIME()")
            );
            if (fInsert("dtBeneficiary", $arrData, $conn)){
                if (move_uploaded_file($_FILES["benFileID"]["tmp_name"], $target_file)){
                    $conn->commit();
                    header("Location: beneficiary.php?q=id-success"); die();
                }else{
                    $conn->rollback();
                    $errMsg .= "Upload ID Failed #1.<br>";
                }
            }else{
                $errMsg .= "Upload ID Failed #2.<br>";
            }
        }else{
            $errMsg .= "Incomplete Data.<br>";
        } 
    }else{
        $errMsg = "Save Failed <br>#Something Wrong Contact Our Support For More Information";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Beneficiary Account </title>
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
    <link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
    <link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
    <link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    
    <script>
        $(document).ready(function(e) {
            if ($.trim($("#q").html()) == "info-success"){
                demo.showNotification('top','center', 'success', 'Update Account Successfully');
                $("#q").html('');
            }else if ($.trim($("#q").html()) == "id-success"){
                demo.showNotification('top','center', 'success', 'ID has been uploaded');
                $("#q").html('');
            }

            $("#saveBeneficiary").click(function(){
                //validate image
                var input, file;
                if (!window.FileReader){
                    demo.showNotification('top','center', 'info', "The file API isn't supported on this browser yet.");
                    return false;
                }

                input = document.getElementById('benFileID');
                if (!input){
                    demo.showNotification('top','center', 'info', "Um, couldn't find the fileinput element.");
                    return false;
                }else if (!input.files){
                    demo.showNotification('top','center', 'info', "This browser doesn't seem to support the upload file");
                    return false;
                }else if (!input.files[0]){
                    demo.showNotification('top','center', 'info', "Please select a file to upload");
                    return false;
                }else{
                    file = input.files[0];
                    if (file.size > 2097151){ // lebih besar dari 2MB
                        demo.showNotification('top','center', 'info', file.name + " exceeds the maximum upload size for this site");
                        return false;
                    }
                }

                //validate select
                if ($.trim($('select[name="benIdType"]').val()) == ''){
                    demo.showNotification('top','center', 'info', 'Please select an <b>ID Type</b>');
                    return false;
                }
                if ($.trim($('select[name="benRelationType"]').val()) == ''){
                    demo.showNotification('top','center', 'info', 'Please select an <b>Relation Type</b>');
                    return false;
                }


            });
        });
    </script>
</head>
<body>
	<span id="q"><?php echo $q; ?></span>
	<div class="card">
	    <div class="card-header card-header-success card-header-icon">
	        <div class="card-icon">
	          <i class="fa fa-address-card-o fa-2x"></i>
	        </div>
	        <div class="card-text"><h4 class="card-title">Beneficiary Account</h4></div>
	    </div>
    	<div class="card-body card-fix">
    		<div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card col-md-11">
                                <div class="card-body card-fix col-md-11">
                                <?php 
                                $sql  = "SELECT * FROM dtBeneficiary";
                                $sql .= " INNER JOIN msIDType ON idtCode = BenIDType";
                                $sql .= " INNER JOIN msRelationType ON RelCode = BenRelationType";
                                $sql .= " WHERE BenMbrUsername = '".$username."'";
                                $result = $conn->query($sql);
                                if ($row = $result->fetch_assoc()){
                                    $regDate = date_create($row['BenUpdateDate']);
                                    $regDate = date_format($regDate, "F d, Y h:i A");
                                ?>
                                <!-- already upload beneficiary -->
                                <h4 class="text-success profile text-center">Registered Beneficiary Account</h4>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4 profile">First Name</div>
                                    <div class="col-md-8 profile"><?php echo $row['BenFirstName']; ?></div>
                                    <div class="col-md-4 profile">Last Name</div>
                                    <div class="col-md-8 profile"><?php echo $row['BenLastName']; ?></div>
                                    <div class="col-md-4 profile">ID Type</div>
                                    <div class="col-md-8 profile"><?php echo $row['idtType']; ?></div>
                                    <div class="col-md-4 profile">ID Number</div>
                                    <div class="col-md-8 profile"><?php echo $row['BenIDNum']; ?></div>
                                    <div class="col-md-4 profile">Birth of Date</div>
                                    <div class="col-md-8 profile"><?php echo $row['BenBOD']; ?></div>
                                    <div class="col-md-4 profile">Relation</div>
                                    <div class="col-md-8 profile"><?php echo $row['RelType']; ?></div>
                                    <div class="col-md-4 profile">Registered Date</div>
                                    <div class="col-md-8 profile"><?php echo $regDate; ?></div>
                                </div>
                                <!-- end already upload beneficiary-->
                                <?php 
                                }else{
                                ?>
    	                        <h4 class="profile text-center">Beneficiary Account</h4>
    	                        <hr>
    	                        <div class="row">
                                	<form action="beneficiary.php?data=formBeneficiary" method="post" name="formBeneficiary" class="col-md-12" enctype="multipart/form-data">
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
                                        <div class="row">
                                            <div class="form-group col-md-12 text-center">
                                                <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                                    <div class="fileinput-new thumbnail img-raised" style="border-radius: 27px;">
                                                        <img src="../assets/img/id-card.png">
                                                    </div>
                                                    <div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
                                                    <div>
                                                        <span class="btn btn-raised btn-round btn-rose btn-file">
                                                            <span class="fileinput-new " onclick="$('#benFileID').click();">Select beneficiary id</span>
                                                            <span class="fileinput-exists" onclick="$('#benFileID').click();">Change</span>
                                                            <input type="file" name="benFileID" id="benFileID"/>
                                                        </span>
                                                        <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
                                                            <i class="fa fa-times"></i> Remove
                                                        </a>
                                                   </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label for="benIdType" class="col-form-label">ID Type</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <select class="selectpicker" data-size="5" data-style="btn btn-primary" name="benIdType" id="benIdType">
                                                            <option disabled selected>Select ID Type</option>
                                                            <?php
                                                                $sql  = "SELECT * FROM msIDType";
                                                                $sql .= " ORDER BY idtType ASC";
                                                                $query = $conn->query($sql);
                                                                while ($row = $query->fetch_assoc()){
                                                                    $selected =  (1 == $row["idtCode"])?" selected " : "";
                                                                    echo ("<option value='".$row["idtCode"]."' " . $selected . ">".$row["idtType"]."</option>");
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label for="benRelationType" class="col-form-label">Relation Type</label>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <select class="selectpicker" data-size="5" data-style="btn btn-primary" name="benRelationType" id="benRelationType">
                                                            <option disabled selected>Select Relation Type</option>
                                                            <?php
                                                                $sql  = "SELECT * FROM msRelationType";
                                                                $sql .= " ORDER BY RelType ASC";
                                                                $query = $conn->query($sql);
                                                                while ($row = $query->fetch_assoc()){
                                                                    $selected = "";
                                                                    echo ("<option value='".$row["RelCode"]."' " . $selected . ">".$row["RelType"]."</option>");
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="benFirstName" class="bmd-label-floating">First Name</label>
                                                <input type="text" class="form-control" name="benFirstName" id="benFirstName"  value="" maxlength="50" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="benLastName" class="bmd-label-floating">Last Name</label>
                                                <input type="text" class="form-control" name="benLastName" id="benLastName"  value="" maxlength="50">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="benIdNum" class="bmd-label-floating">ID Number</label>
                                                <input type="text" class="form-control" name="benIdNum" id="benIdNum" maxlength="25" value="" title="Benefiaciary ID Number"  required>
                                            </div>
                                            <div class="form-group col-md-6 is-focused">
                                                <label for="benBOD" class="bmd-label-floating">Birth of Date</label>
                                                <input type="date" class="form-control" name="benBOD" id="benBOD"  required>
                                            </div>
                                        </div>     
                                        <div class="footer">
                                            <button type="submit" id="saveBeneficiary" name="saveBeneficiary" class="btn btn-fill btn-rose col-md-12">Save</button>
                                        </div>
                                    </form>
                                </div>
                                <?php
                                } 
                                ?>
                            </div>
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

<!--    Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
<script src="../assets/js/plugins/bootstrap-datetimepicker.min.js"></script>

<!--    Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="../assets/js/plugins/nouislider.min.js"></script>

<!--    Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/bootstrap-selectpicker.js"></script>

<!--    Plugin for Tags, full documentation here: http://xoxco.com/projects/code/tagsinput/  -->
<script src="../assets/js/plugins/bootstrap-tagsinput.js"></script>

<!--    Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
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