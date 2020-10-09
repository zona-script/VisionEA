<?php 
	//$sUserName = $_SESSION["sUserName"];
	//$sName		= $_SESSION["sFirstName"];
	
  	require("../includes/inc_def.php");
	require("../includes/inc_session_admin.php");
	require('../includes/inc_conn.php');
	require('../includes/inc_functions.php');
	require('../includes/inc_func_admin.php');

  	$mbr_month  = fValidateInput($_GET["mbr_month"]);
  	$mbr_year  = fValidateInput($_GET["mbr_year"]);
  	$type  = fValidateInput($_GET["type"]);
  	if ($type == 'MEMBER'){
		echo (fMember($conn, $mbr_month, $mbr_year));
	}
  ?>