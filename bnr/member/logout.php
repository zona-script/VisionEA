<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_functions.php");
fLogout();
header("Location: ".htmlspecialchars($COMPANY_SITE));
die();	
?>