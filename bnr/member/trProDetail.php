<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$username      = $_SESSION['sUserName'];

$id     = isset($_GET['id'])?$_GET['id']:"";
$msg = "";
$sql  = "SELECT trProUsername, trProUserBeli, mbrFirstName, a.*, b.*, proName FROM dtMember";
$sql .= " INNER JOIN trProduct a ON mbrUsername = trProUsername";
$sql .= " INNER JOIN trProDetail b ON trPDTransID = trProTransID";
$sql .= " INNER JOIN msProduct ON proID = trPDProID";
$sql .= " WHERE trProTransID = '".$id."' AND mbrUsername = '".$username."' ";
// echo $sql;
$result = $conn->query($sql);
$amount = 0;
$tbody = "";
while ($row = $result->fetch_assoc()){
	$amount += $row['trPDSubTotal'];
	$trProUsername 	= $row['trProUsername'];
	$trProUserBeli 	= $row['trProUserBeli'];
	$mbrFirstName 	= $row['mbrFirstName'];
	$orderDate 		= $row['trProDate'];
	$orderDate 		= date_create($orderDate);
    $orderDate 		= date_format($orderDate, "d F Y");
	$tbody .= "
	<tr>
		<td>".$row['proName']."</td>
		<td>".$row['trPDPrice']."</td>
		<td>".$row['trPDDisc']."</td>
		<td>".$row['trPDQty']."</td>
		<td>".$row['trPDSubTotal']."</td>
	</tr>";
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $COMPANY_NAME; ?> - Payment Detail</title>
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
	<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
	<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
	<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://kit.fontawesome.com/d4261ae917.js" crossorigin="anonymous"></script>

	<script>
		function isNumberKey(evt){
		    var charCode = (evt.which) ? evt.which : evt.keyCode
		    if (charCode > 31 && (charCode < 48 || charCode > 57))
		        return false;
		    return true;
		}

		$(document).ready(function(e){
		});
	</script>
</head>
<body>
	<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="fas fa-receipt fa-2x"></i>
        </div>
        <div class="card-text"><h4 class="card-title">Payment</h4></div>
    </div>
    <div class="card-body card-fix">
    	<div class="container">
            <div class="container-fluid">
                <div class="card" >
                    <div class="card-body">
                    	<button type="button" class="btn btn-rose btn-round" onclick="history.back();"><i class="fas fa-angle-double-left"> Back</i></button>
                    	<div class="row">
                    		<div class="col-md-12 col-12 text-center">
                    			<img src="../../images/Logo-VisionEA-text.png" class="img-responsive" alt="" /><br>
                    			<span class="font-weight-bold">Order ID : <?php echo $id; ?></span>
                    		</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-md-6 col-sm-6">
                    			<div class="row">
		                			<div class="col-md-4 col-sm-5 col-6 font-weight-bold">Username</div>
									<div class="col-md-8 col-sm-7 col-6"> : <?php echo $trProUsername; ?></div>
									<div class="col-md-4 col-sm-5 col-6 font-weight-bold">Name</div>
									<div class="col-md-8 col-sm-7 col-6"> : <?php echo $mbrFirstName; ?></div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="row">
									<div class="col-md-6 col-sm-6 col-6 text-right font-weight-bold">Order Date</div>
									<div class="col-md-6 col-sm-6 col-6"> : <?php echo ($orderDate); ?></div>
									<div class="col-md-6 col-sm-6 col-6 text-right font-weight-bold">Buyer Username</div>
									<div class="col-md-6 col-sm-6 col-6"> : <?php echo ($trProUserBeli); ?></div>
								</div>
                			</div>
                		</div>
                		<div class="row">&nbsp;</div>
						<hr>
						<div class="row">
                    		<div class="col-sm-12">
                    			<div class="table-responsive">
						        	<table class="table">
										<thead>
											<tr>
												<th>Product</th>
												<th>Price <span class="text-lighter">(IDR)</span></th>
												<th>Discount <span class="text-lighter">(IDR)</span></th>
												<th>Quantity <span class="text-lighter"></span></th>
												<th>Sub Total <span class="text-lighter">(IDR)</span></th>
											</tr>
										</thead>
										<tbody>
											<?php
												echo $tbody;
											?>
										</tbody>
										<tfoot>
											<tr>
												<th colspan="3"></th>
												<th>Grand Total <span class="text-lighter">(IDR)</span></th>
												<th><?php echo $amount; ?></th>
											</tr>
										</tfoot>
									</table>
								</div>
					      	</div>
                    	</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>