<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Back Office</title>
<link rel="stylesheet" href="../assets/css/bootstrap.css" />
<link rel="stylesheet" href="../assets/css/font-awesome.css" />

    <link href="../assets/css/login.css" rel="stylesheet" />
    <link href="../assets/css/newBinary.css" rel="stylesheet" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

</head>

<body>
<div class="container">
	<div class="container-fluid">
    	<h1>Back Office</h1>
        <div class=""><a href="genV.php">Create Voucher</a></div>
        <div class=""><a href="voucher.php">Voucher List</a></div>
        <div class=""><a href="incomingDepo.php">Deposit</a></div>
    </div>
</div>
</body>
</html>
<?php fCloseConnection($conn); ?>