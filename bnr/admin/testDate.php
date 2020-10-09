<?php
$curDate    = date("Y-m-d H:i:s");
echo ("cur Date: " . $curDate . "<br>");

include_once("../includes/inc_def.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");


$postingDate    = date("Y-m-d H:i:s");
echo ("Posting Date: " . $postingDate);

fSendToAdmin("TEST CRON", "testDate.php", "curDate: " . $curDate . " >> Posting Date: " . $postingDate);


	$CURRENT_TIME = $postingDate;
    $startMaintenanceTime   = date("Y-m-d H:i:s", (mktime(9, 0, 0, date("n"), date("j") , date("Y")) )); // (00:00)
	$endMaintenanceTime     = date("Y-m-d H:i:s", (mktime(11, 0, 0, date("n"), date("j") , date("Y")))); 

    if (strtotime($CURRENT_TIME) >= strtotime($startMaintenanceTime) && strtotime($CURRENT_TIME) <= strtotime($endMaintenanceTime)){    	
        fSendToAdmin("TEST CRON", "testDate.php", "MAINTENANCE");
    }else{
    	fSendToAdmin("TEST CRON", "testDate.php", "RUNNING...");
    }


?>