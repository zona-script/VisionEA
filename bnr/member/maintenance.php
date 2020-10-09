<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_functions.php");

//Checking Maintenance Schedule
if (!fIsMaintenance()){
    $_SESSION["sSID"] = "1";
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    header ("Location: ".$COMPANY_SITE."member/?MNav=dailyMaintenance&unxid=".md5(time()));
    die();
}



$MNav   = (isset($_GET["MNav"]))? fValidateInput($_GET["MNav"]) : "";
if ($MNav == "dailyMaintenance"){

}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
<title>VisionEA - Maintenance</title>
<!-- Favicons -->
<link rel="icon" href="../images/favicon.png" sizes="16x16 32x32" type="image/png">

<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>
    <div class="container" style="padding: 0px; margin-top: -20px">
        <div class="container-fluid">
            <div class="card" style="padding: 0px">
                <div class="col-lg-12">
                    <div class="row" style="padding-top: 50px">
                        <img src="../assets/img/email/headerEmailVisionEA.jpg" width="100%" height="100%" >
                    </div>
                </div>
                
                <div class="card-header card-header-success card-header-icon" style="margin-top: 10px">
                    <div class="card-icon">
                      <i class="fa fa-cogs fa-2x"></i><span class="fa-2x"> Maintenance</span></div>
                </div>
                <div class="card-body">
                    <div class="col-lg-12 col-md-12 col-xs-12">
                        <h2>The system is doing routine maintenance.</h2>
                        <p><b>The maintenance schedule is:</b><br>
                            From : <?php echo ($startMaintenanceTime . " <br>To: " . $endMaintenanceTime); ?> <br>Time Zone: UTC +0800 (Asia / Singapore).</p>
                        <p>Current Server Time: <?php echo $CURRENT_TIME ?></p>
                        <a href="./maintenance.php"><button class="btn btn-primary">Refresh Current Server Time</button></a>
                    </div>
                </div>
                <div class="card-footer">
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>