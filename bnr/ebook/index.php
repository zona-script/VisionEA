<?php 
include_once("../includes/inc_def.php"); //before inc_session
include_once("../includes/inc_session_ebook.php"); //after inc_session
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$sUserName = $_SESSION['sEBUserName'];

$MNav   = (isset($_GET['MNav']))?$_GET['MNav']:"dboard";
$subNav = (isset($_GET['subNav']))?$_GET['subNav']:"";
$remember = (isset($_GET['remember']))?$_GET['remember']:"";
$loadPage = $contentPage = "";
if ($MNav == "auth"){
    $contentPage = "loginEbook.php"; 
}else if ($MNav == "lo"){
    $contentPage = "logoutEbook.php";
}else if ($MNav == "dboard"){
    $loadPage = "dashboard.php?x="; 
}else if ($MNav == "product"){
    $loadPage = "ebook.php?x=";
}else if ($MNav == "read"){
    if ($subNav == "basic"){
        $loadPage = "./conv/e-Book Basic Edition.php?x=";
    }else if ($subNav == "pro"){
        $loadPage = "./conv/e-Book Pro Edition.php?x=";
    }
}else if ($MNav == "passwd"){
    $loadPage = "changepassEbook.php?x=";
}
$loadPage .= "&unxid=".md5(time());

if ($contentPage != ""){
    header("Location: ".$COMPANY_SITE."ebook/".$contentPage."?unxid=".md5(time()));
    die();
}

if ($remember != ""){
    fSetCookiesLogin($sUserName);
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Ebook Dashboard</title>
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
    <!-- script or jquery bottom of code -->
</head>
<body>
    <div class="wrapper">
        <div class="sidebar" data-color="rose" data-background-color="black" data-image="../assets/img/sidebar-1.jpg">
            <!--
                Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"
                Tip 2: you can also add an image using data-image tag
            -->
            <div class="logo">
                <a href="#" class="simple-text logo-mini">
                    <!--<img src="../assets/img/<?php echo ($COMPANY_LOGO_S); ?>" width="100%" />-->
                </a>
                <a href="#" class="simple-text logo-normal">
                    <!--<?PHP echo($COMPANY_NAME); ?>-->
                    <img src="../assets/img/<?php echo ($COMPANY_LOGO); ?>" width="80%" />
                </a>
            </div>
            <?php
            $sUserName = isset($_SESSION['sEBUserName'])?$_SESSION['sEBUserName']:"";
            if ($sUserName != ""){
            ?>
            <div class="sidebar-wrapper">
                <div class="user">
                    <div class="photo">
                        <!-- <img src="../assets/img/faces/avatar.jpg" /> -->
                    </div>
                    <div class="user-info">
                        <a data-toggle="collapse" href="#collapseProfile" class="username">
                            <span>
                               <?php echo ($_SESSION["sEBUserName"]); ?>
                              <b class="caret"></b>
                            </span>
                        </a>
                        <div class="collapse" id="collapseProfile">
                            <ul class="nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="./?MNav=passwd" id="changePassword">
                                      <i class="fa fa-key"></i>
                                      <span class="sidebar-normal" id="changePassword"> Change Password</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="./?MNav=lo" >
                                       <i class="fa fa-sign-out"></i>
                                      <span class="sidebar-normal" >Sign Out</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="./?MNav=dboard"  id="dashboard">
                            <i class="material-icons">dashboard</i>
                            <p> Dashboard </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./?MNav=product"  id="dashboard">
                            <i class="fa fa-book"></i>
                            <p> Product </p>
                        </a>
                    </li>
                </ul>
            </div>
            <?php
            }
            ?>
        </div>
        <div class="main-panel" >
            <div class="content" style="text-align: center;margin-top: 10px;height:80%; width: 100%;">
                <iframe class="card" id="loadPage" src="<?php echo $loadPage ?>"></iframe>
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
            
        });

        </script>
</body>
</html>