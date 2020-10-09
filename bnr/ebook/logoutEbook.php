<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session_ebook.php");
include_once("../includes/inc_functions.php");
fLogout();
header("Location: ".htmlspecialchars($COMPANY_SITE)."ebook/");
die();	
?>