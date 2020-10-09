<?php 
	$sUserName = $_SESSION["sUserName"];
	
	$sql = "SELECT * FROM dtBackOffice WHERE boUsername='" . $sUserName . "' AND boStID='" . $DEF_STATUS_ACTIVE . "'";
	$query = $conn->query($sql);
	
	if ($row = $query->fetch_assoc()){
		$name	= $row['boName'];
		$email	= $row['boEmail'];
		//last login
	}else{
		header("Location: logout.php?q=err&msg=username_err");	
	}
?>
<div class="col-sm-12">
	<div class="well">
	    <h4>My Profile</h4>
	    <p>Username : <?php echo $_SESSION["sUserName"]; ?></p>
	    <p>Name : <?php echo $name; ?></p>
	    <p>Email : <?php echo $email; ?></p>
	</div>
</div>