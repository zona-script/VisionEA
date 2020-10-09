<?php
include_once("../includes/inc_def.php"); //before inc_session
include_once("../includes/inc_session_ebook.php"); //after inc_session
include_once("../includes/inc_conn.php");

$q = isset($_GET["q"])?$_GET["q"]:"";
$changePasswordMessage = "";
$sUserName = $_SESSION['sEBUserName'];
if (!empty($_POST)){
    $act    = (isset($_POST["act"]))? fValidateInput($_POST["act"]) : "";
    if ($act == "changepassEbook"){
        $currPassword   = (isset($_POST["currPassword"]))? fValidateSQLFromInput($conn, $_POST["currPassword"]) : "";
        $newPassword    = (isset($_POST["newPassword"]))? fValidateSQLFromInput($conn, $_POST["newPassword"]) : "";
        $reNewPassword  = (isset($_POST["reNewPassword"]))? fValidateSQLFromInput($conn, $_POST["reNewPassword"]) : "";

        $sql  = "SELECT ebUsername, peID, pePasswd";
        $sql .= " FROM dtUserEbook";
        $sql .= " INNER JOIN trPassEbook";
        $sql .= " WHERE ebUsername = peUsername";
        $sql .= " and ebUsername='" . $sUserName . "' and pePasswd='" . md5($newPassword) . "'";
        $query = $conn->query($sql);
        if ($query->num_rows > 0){
            $changePasswordMessage = "<b>Change Password Failed</b><br>Password has been used. Use a password that has never been used.";
        }else{
            $sql  = "SELECT ebUsername, peID, pePasswd";
            $sql .= " FROM dtUserEbook";
            $sql .= " INNER JOIN trPassEbook";
            $sql .= " WHERE ebUsername = peUsername";
            $sql .= " AND ebUsername='" . $sUserName . "'";
            $sql .= " ORDER BY peDate DESC LIMIT 1";
            $query = $conn->query($sql);
            if ($query->num_rows > 0){
                if ($row = $query->fetch_assoc()){
                    if ($row["pePasswd"] == md5($currPassword)){
                        $peID = strtotime("now");                  
                        $arrData = array(
                            0 => array ("db" => "peID"          , "val" => $peID),
                            1 => array ("db" => "peUsername"    , "val" => $sUserName),
                            2 => array ("db" => "pePasswd"      , "val" => md5($newPassword)),
                            3 => array ("db" => "peDate"        , "val" => "CURRENT_TIME()")
                        );
                        if (fInsert("trPassEbook", $arrData, $conn)){
                            //send email for activation
                            // fSendNotifToEmail("CHANGE_PASSWORD_EBOOK", $sUserName);
                            //fCloseConnection($conn);
                            $conn->close();
                            //redirect to success page
                            header("Location: changepassEbook.php?q=changepass-success");
                            die();
                        }else{
                            //insert fail   
                            //back for re-register
                            $changePasswordMessage = "<b>Change Password Failed</b><br>Contact Support for help";
                        }
                    }else{
                        $changePasswordMessage = "<b>Change Password Failed</b><br>Incorrect Current Password";
                    }
                }
            }else{
                $changePasswordMessage = "<b>Change Password Failed</b><br>Username not found";
            }
        }
    }
}
?>
<html>
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description" content="VisionEA is a company that provides a smart platform for forex trading automatically.">
    <meta name="author" content="VisionEA">

    <!-- Favicons -->
    <link rel="icon" href="../../images/favicon.png" sizes="16x16 32x32" type="image/png">

    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
    <link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
    <!-- Documentation extras -->

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/css/newBinary.css">

</head>
<body>
    <span id="q" style="display: none;"><?php echo $q; ?></span>
    <span id="errChangePass" style="display: none;"><?php echo $changePasswordMessage  ?></span>
    <div class="container">
        <div class="card">
            <div class="card-header card-header-success card-header-icon">
                <div class="card-icon">
                    <i class="fa fa-book fa-2x"></i>
                </div>
                <div class="card-text"><h4 class="card-title">Change Password (E-Book)</h4></div>
            </div>
            <div class="card-body card-fix">
                <div class="row">
                    <div class="col-md-6">
                        <form action="changepassEbook.php" method="POST" name="formChangePassword">
                            <input type="hidden" name="act" id="act" value="changepassEbook">
                            <div class="card ">
                                <div class="card-header card-header-rose card-header-icon">
                                    <h4 class="card-title"><b>Change Password</b></h4>
                                </div>
                                <div class="card-body ">
                                    <div class="form-group">
                                        <label for="currPassword" class="bmd-label-floating">Current Password</label>
                                        <input type="password" class="form-control" name="currPassword" id="currPassword" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="newPassword" class="bmd-label-floating">New Login Password</label>
                                        <input type="password" class="form-control" name="newPassword" id="newPassword" pattern=".{6,12}" title="6 to 12 characters" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="reNewPassword" class="bmd-label-floating">Retype New Password</label>
                                        <input type="password" class="form-control" name="reNewPassword" id="reNewPassword" pattern=".{6,12}" title="6 to 12 characters" required>
                                    </div>
                                    <div class="form-check col-md-12">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" id="showPasswd" name="showPasswd" value="">
                                            <span class="form-check-sign">
                                                <span class="check"></span>
                                            </span>
                                            Show Password
                                        </label>
                                    </div>
                                </div>
                                <div class="card-footer ">
                                    <button type="submit" id="submitPassword" name="submitPassword" class="btn btn-fill btn-rose col-md-12">Update Password</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

    <!-- Sliders Plugin, full documentation here: https://refreshless.com/nouislider/ -->
    <script src="../assets/js/plugins/nouislider.min.js"></script>

    <!--  Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
    <script src="../assets/js/plugins/jquery.select-bootstrap.js"></script>

    <!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
    <script src="../assets/js/plugins/jquery.datatables.js"></script>

    <!-- Sweet Alert 2 plugin, full documentation here: https://limonte.github.io/sweetalert2/ -->
    <script src="../assets/js/plugins/sweetalert2.js"></script>

    <!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
    <script src="../assets/js/plugins/jasny-bootstrap.min.js"></script>

    <!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
    <script src="../assets/js/plugins/fullcalendar.min.js"></script>

    <!-- demo init -->
    <script src="../assets/js/plugins/demo.js"></script>
    <script type="text/javascript">
    $(document).ready(function(e) {
        var q = $("#q").html();
        if (q == "changepass-success"){
            demo.showNotification('top','center', 'success', 'Password Changed Successfully');
        }
        var errChangePass = $("#errChangePass").html();
        if (errChangePass != ""){
            demo.showNotification('top','center', 'danger', errChangePass);
        }
        $("#showPasswd").on("click", function(){
            var x = document.getElementById("currPassword");
            var y = document.getElementById("newPassword");
            var z = document.getElementById("reNewPassword");
            if (x.type === "password") {
                x.type = "text";
                y.type = "text";
                z.type = "text";
            }else {
                x.type = "password";
                y.type = "password";
                z.type = "password";
            }
        });
    });

    </script>
</body>
</html>