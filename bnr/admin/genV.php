<?PHP
$s = isset($s)?$s:"";
if ($s == "err" ){
	if ($errno=="1") $errMsg = "Your authentication failed";
	if ($errno=="2") $errMsg = "Session expired";
	if ($errno=="3") $errMsg = "Incomplete Data";
?>
	<div class="row">
		<div class="col-md-10">
	        <div class="alert alert-danger alert-dismissible col-md-6 col-sm-12">
	          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	          <strong>Warning!</strong>&nbsp;<?php echo $errMsg ?>
	        </div>
	    </div>
    </div>
<?php
}

if (isset($_POST["submit"])){
	include("../includes/inc_def.php");
	include("../includes/inc_conn.php");
	include("../includes/inc_functions.php");
	include("../includes/inc_session_admin.php");

	$voucherType 	  = (isset($_POST["voucherType"]))? fValidateSQLFromInput($conn, $_POST["voucherType"]) : "";
	$securityPassword = (isset($_POST["securityPassword"]))? fValidateSQLFromInput($conn, $_POST["securityPassword"]) : "";
	$sUserName 		  = (isset($_SESSION['sUserName']))? fValidateSQLFromInput($conn, $_SESSION['sUserName']) : "";
	if (!fCheckSecurityPasswordBO($sUserName, $securityPassword, $conn)){
	//if (trim($securityPassword) != "KATA_RAHASIA"){
		header("Location: ./?menu=genv&s=err&errno=1&msg=");
		die();
	}
	
	
	if ($sUserName == ""){
		header("Location: ./?menu=genv&s=err&errno=2&msg=");
		die();
	}

	if ($voucherType == ""){
		header("Location: ./?menu=genv&s=err&errno=3&msg=");
		die();
	}

	//=====================================
	//  MAX LENGTH : 25 Characters
	//=====================================
	//$arrCode = array("a0", "a1", "a2"); //a1 next a2 next a3			, "b0", "c0", "d0", "e0"
	if ($voucherType=="STD")
		$arrCode = array("a0", "a1", "a2", "a3", "a4"); 
	else
		$arrCode = array("w0", "w1");
	$serialCharacter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%&*abcdefghijklmnopqrstuvwxyz';
	$counter = 0;
	$preTimeStamp = 0;
	foreach ($arrCode as $key => $value){
		$timestamp = strtotime("now");
		//if ($preTimeStamp != $timestamp){
		//	$preTimeStamp = $timestamp;
			for ($i=0; $i<10; $i++){
				$str	= str_shuffle(substr(str_shuffle($serialCharacter), 0, 2) . str_shuffle($timestamp));
				$voucherCode = $timestamp . $value . $i .  $str;
				
				$conn->autocommit(false);
				$arrData = array(
							array("db" => "vCode" , "val" => $voucherCode),
							array("db" => "vStatus" , "val" => $DEF_STATUS_ACTIVE),
							array("db" => "vType" , "val" => $voucherType),
							array("db" => "vDate" , "val" => "CURRENT_TIME()"),
							array("db" => "vUploadBy" , "val" => $sUserName)
							);
				if (!fInsert("dtVoucher", $arrData, $conn)){
					echo "error " . $conn->error;
					$conn->rollback();
					die();
				}
				unset($arrData);
				++$counter; ////echo (++$counter . " : " . $voucherCode . " length: " . strlen($voucherCode) . "<br>");	
			}
		//}
	}
	
	$conn->commit();
	
	header("Location: ./?menu=genv&s=success&msg=".$counter);
	die();

}
$s = (isset($_GET['s']))?$_GET['s']:"";
$msg = (isset($_GET['msg']))?$_GET['msg']:"";
if ($s == "success"){
	echo "<p>Success</p>";
	echo "<p>Total New PIN : " . $msg . "</p>";
}
?>    

<script>
	$(document).ready(function(e) {
		$("#submit").on('click', function(){
			if ($('select[name="voucherType"]').val() == ""){
				alert ("Please select PIN type");
				return (false);
			}else{
				//alert ($('select[name="voucherType"]').val() );
				return (true);
			}
		});
		
	});
</script>


<form action="genV.php" method="post" >
	<div class="row">
		<div class="col-md-6">
		    <div class="row">
    			<div class="col-md-4">
    				PIN Type :
    			</div>
    			<div class="col-md-8">
    				<select name="voucherType">
    					<option value="">-- Select PIN Type --</option>
    					<option value="STD">Standard / Activation</option>
    					<option value="VPS">VPS</option>
    				</select>
    			</div>
			</div>
			<div class="row">
    			<div class="col-md-4">
    			    Security Word : 
    			</div>
    			<div class="col-md-8">
    				<input type="password" name="securityPassword" >
    			</div>
    		</div>
    		<div class="row">
    		    <div class="col-md-4">
    			    &nbsp;
    			</div>
    			<div class="col-md-8">
    			    <input type="submit" name="submit" id="submit" value="Gen PIN Code">
    			</div>
			</div>
		</div>
	</div>
</form>
<H2>PIN Code:</H2>