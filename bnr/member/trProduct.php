<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$username      = $_SESSION['sUserName'];
$subNav	= (isset($_GET['subNav']))?$_GET['subNav']:"";
if ($subNav == ""){
	$subNav = "proTrans";
}
function setActive($menu, $section){
  if ($menu == $section) return "active";
  return "";
}
if (!empty($_POST)){
	$conn->autocommit(false);
	$q 		= fValidateSQLFromInput($conn, (isset($_POST['q'])))?fValidateSQLFromInput($conn, $_POST['q']): '';
	$username 		= fValidateSQLFromInput($conn, (isset($_POST['username'])))?fValidateSQLFromInput($conn, $_POST['username']): '';
	if (strtolower($q) == strtolower("buyProduct")){
		$typeofpurchase = fValidateSQLFromInput($conn, (isset($_POST['typeofpurchase'])))?fValidateSQLFromInput($conn, $_POST['typeofpurchase']): '';		
		if ($typeofpurchase == $DEF_TYPE_PURCHASE_RS){
			$trProUserBeli 	= fValidateSQLFromInput($conn, (isset($_POST['cusUsername'])))?fValidateSQLFromInput($conn, $_POST['cusUsername']): '';
			$cusEmail 		= fValidateSQLFromInput($conn, (isset($_POST['cusEmail'])))?fValidateSQLFromInput($conn, $_POST['cusEmail']): '';
			$cusFirstName 	= fValidateSQLFromInput($conn, (isset($_POST['cusFirstName'])))?fValidateSQLFromInput($conn, $_POST['cusFirstName']): '';
			$cusLastName 	= fValidateSQLFromInput($conn, (isset($_POST['cusLastName'])))?fValidateSQLFromInput($conn, $_POST['cusLastName']): '';
			$isReseller 	= true;
			$trProType 		= $DEF_TYPE_PURCHASE_RS;
		}else{
			$trProUserBeli 	= $username;
			$isReseller 	= false;
			$trProType 		= $DEF_TYPE_PURCHASE_RO;
		}
		$myJSON    = isset($_POST["myJSON"])?($_POST["myJSON"]) : "";
	    $myJSON    = json_decode($myJSON);
	    // $data = print_r($myJSON); //die();
	    //validate pending order
		$sql  = "SELECT * FROM trProduct";
		$sql .= " WHERE trProUsername ='".$username."' AND trProStatus = '".$DEF_STATUS_ONPROGRESS."' ";
		// echo (fSendStatusMessage("error", "$username || $sql")); die();
		$result = $conn->query($sql);
		if ($result->num_rows > 0){
			echo (fSendStatusMessage("error", "Silahkan selesaikan pendingan order terlebih dahulu")); die();
		}else{
			$proID = $proPrice = $proQty = "";
			$trProTransID = strtotime("+0");
			$tAmount = 0;
			for ($i = 0; $i < count($myJSON); $i++) {
				$proID 		= $myJSON[$i]->proID;
				$proPrice 	= $myJSON[$i]->proPrice;
				$proQty 	= $myJSON[$i]->proQty;
				$proAmount 	= $proPrice * $proQty;
				$tAmount += $proAmount;
				if ($proID != "" || $proPrice != "" || $proQty != ""){
					$table = "trProDetail";
					$arrData = array(
			            array ("db" => "trPDTransID"	, "val" => $trProTransID),
			            array ("db" => "trPDProID"		, "val" => $proID),
			            array ("db" => "trPDPrice"		, "val" => $proPrice),
			            array ("db" => "trPDQty"		, "val" => $proQty),
			            array ("db" => "trPDDisc"		, "val" => "0"),
			            array ("db" => "trPDSubTotal"	, "val" => $proAmount)             
			        );
			        if (fInsert($table, $arrData, $conn)){
			        	// berhasil insert
			        }else{
			        	$conn->rollback();
			        	break;
			        	echo (fSendStatusMessage("error", "Tidak bisa melakukan pembelian saat ini, silahkan hubungi support. #1")); die();
			        }
				}else{
					echo (fSendStatusMessage("error", "Tidak bisa melakukan pembelian saat ini, silahkan hubungi support. #2")); die();	
				}
			}
			unset($arrData);
			$table = "trProduct";
			$arrData = array(
	            array ("db" => "trProTransID"	, "val" => $trProTransID),
	            array ("db" => "trProUsername"	, "val" => $username),
	            array ("db" => "trProUserBeli"	, "val" => $trProUserBeli),
	            array ("db" => "trProType"		, "val" => $trProType),
	            array ("db" => "trProDate"		, "val" => "CURRENT_TIME()"),
	            array ("db" => "trProAmount"	, "val" => $tAmount),
	            array ("db" => "trProDisc"		, "val" => "0"),
	            array ("db" => "trProStatus"	, "val" => $DEF_STATUS_ONPROGRESS)                
	        );
	        
	        if (!fInsert($table, $arrData, $conn)){
	        	$conn->rollback();
	        	echo (fSendStatusMessage("error", "Tidak bisa melakukan pembelian saat ini, silahkan hubungi support. #3")); die();
	        }

	        if ($isReseller === true){
        		if ($trProUserBeli != "" || $cusEmail != "" || $cusFirstName != "" || $cusLastName != ""){
        			$sql  = " SELECT mbrUsername FROM dtMember";
        			$sql .= " WHERE mbrUsername = '".$trProUserBeli."' ";
        			$sql .= " UNION";
        			$sql .= " SELECT ebUsername FROM dtUserEbook";
        			$sql .= " WHERE ebUsername = '".$trProUserBeli."' ";
        			if ($result = $conn->query($sql)){
	        			if ($result->num_rows > 0){
	        				$conn->rollback();
	        				echo (fSendStatusMessage("error", "Username Sudah digunakan")); die();
	        			}else{
				        	//insert dtUserEbook
				        	$table = "dtUserEbook";
							$arrData = array(
								0 => array ("db" => "ebproTransID"	, "val" => $trProTransID), //samakan dengan tabel order (trProduct)	
								1 => array ("db" => "ebUsername"	, "val" => $trProUserBeli),
								2 => array ("db" => "ebEmail"		, "val" => $cusEmail),
								3 => array ("db" => "ebFirstName"	, "val" => $cusFirstName),
								4 => array ("db" => "ebLastName"	, "val" => $cusLastName),
								5 => array ("db" => "ebDate"		, "val" => "CURRENT_TIME()"),
								6 => array ("db" => "ebStatus"		, "val" => $DEF_STATUS_PENDING)
							);
							if (!fInsert($table, $arrData, $conn)){
								$conn->rollback();
								fSendToAdmin("Repeat Order", "trProduct.php", "Insert data to dtUserEbook failed");
								echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . mysqli_error($conn)));
								die();
							}
							unset($arrData);
							//insert to trPassEbook
							$table = "trPassEbook";
							$pePasswd = substr($trProTransID, -6);
							$arrData = array(
								0 => array ("db" => "peID"			, "val" => $trProTransID),
								1 => array ("db" => "peUsername"	, "val" => $trProUserBeli),
								2 => array ("db" => "pePasswd"		, "val" => md5($pePasswd)),
								3 => array ("db" => "peDate"		, "val" => "CURRENT_TIME()")
							);
							if (!fInsert($table, $arrData, $conn)){
								$conn->rollback();
								fSendToAdmin("Repeat Order", "trProduct.php", "Insert data to trPassEbook failed");
								echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . mysqli_error($conn)));
								die();
							}
							unset($arrData);
						}
					}else{
						$conn->rollback();
						echo (fSendStatusMessage("error", "Something Wrong, please contact support #1")); die();
					}
				}else{
					$conn->rollback();
					echo (fSendStatusMessage("error", "Incomplete Data #1")); die();
				}
				$conn->commit();
				echo (fSendStatusMessage("success", "Silahkan melakukan konfirmasi pembayaran")); die();
			}else{
				unset($arrData);
				$sql  = "SELECT * FROM dtMember";
				$sql .= " WHERE mbrUsername = '".$trProUserBeli."' ";
				$result = $conn->query($sql);
				if ($row = $result->fetch_assoc()){
					//insert dtUserEbook
		        	$table = "dtUserEbook";
					$arrData = array(
						0 => array ("db" => "ebproTransID"	, "val" => $trProTransID), //samakan dengan tabel order (trProduct)	
						1 => array ("db" => "ebUsername"	, "val" => $username),
						2 => array ("db" => "ebEmail"		, "val" => $row['mbrEmail']),
						3 => array ("db" => "ebFirstName"	, "val" => $row['mbrFirstName']),
						4 => array ("db" => "ebLastName"	, "val" => $row['mbrLastName']),
						5 => array ("db" => "ebDate"		, "val" => "CURRENT_TIME()"),
						6 => array ("db" => "ebStatus"		, "val" => $DEF_STATUS_PENDING)
					);
					if (!fInsert($table, $arrData, $conn)){
						$conn->rollback();
						fSendToAdmin("Repeat Order", "trProduct.php", "Insert data to dtUserEbook failed ".mysqli_error($conn));
						echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed #1 </b>"));
						die();
					}
					unset($arrData);
				}else{
					$conn->rollback();
					fSendToAdmin("Repeat Order", "trProduct.php", "fetch_assoc error".mysqli_error($conn));
					echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed #3 </b>"));
					die();
				}
				$conn->commit();
    			echo (fSendStatusMessage("success", "Silahkan melakukan konfirmasi pembayaran")); die();	
			}
		}
	}else if (strtolower($q) == strtolower("payProduct")){
		$username 		= fValidateSQLFromInput($conn, (isset($_POST['username'])))?fValidateSQLFromInput($conn, $_POST['username']): '';
		$proTransID 	= fValidateSQLFromInput($conn, (isset($_POST['proTransID'])))?fValidateSQLFromInput($conn, $_POST['proTransID']): '';
		$totalPayBV 	= 0;
		$trProUserBeli = "";
	
		//1.1 get total Qty from trProDetail
		$sql  = "SELECT SUM(trPDQty) AS totalQty, trProUserBeli FROM trProduct";
		$sql .= " INNER JOIN trProDetail ON trPDTransID = trProTransID";
		$sql .= " WHERE trProUsername ='".$username."' AND trProStatus = '".$DEF_STATUS_ONPROGRESS."' AND trProTransID ='".$proTransID."'";
		// echo (fSendStatusMessage("error", $sql)); die();
		if ($result = $conn->query($sql)){
			if ($result->num_rows > 0){
				if ($row=$result->fetch_assoc()){
					$totalPayBV = $row['totalQty'] * $DEF_BV_PRICE;
					$trProUserBeli = $row['trProUserBeli'];
				}
			}
		}else{
			echo (fSendStatusMessage("error", $conn->error)); die();
		}
		//1.2 Checking number of voucher Required AND Voucher Balance
		if ($totalPayBV > 0){
			if (fmod($totalPayBV, $DEF_BV_PRICE) != 0){ //validasi modulus
				echo (fSendStatusMessage("error", "Please try again later or contact our Support Team #1")); die();
			}
			$numOfVoucherRequired = ceil($totalPayBV / $DEF_BV_PRICE);	//Number of Voucher Required (@200)
			//checking Voucher Balance
			$sql = "SELECT fivFinID, fivVCode FROM ((dtFundIn ";
			$sql .= " inner join dtFundInVoucher on finID = fivFinID and finStatus='" . $DEF_STATUS_APPROVED . "')";
			$sql .= " inner join dtVoucher on vCode = fivVCode and vStatus = '" . $DEF_STATUS_USED . "'";
			$sql .= " and fivStatus = '" . $DEF_STATUS_ACTIVE ."')";
			$sql .= " WHERE finMbrUsername='" . $username . "'";
			$arrVoucher = array();
			if ($result = $conn->query($sql)){
				if ($result->num_rows > 0){
					while ($row = $result->fetch_assoc()){
						//$VoucherBalance	= $row["VoucherBalance"];
						$arrVoucher[] = array("fivFinID" => $row["fivFinID"], "fivVCode" => $row["fivVCode"]);	
					}
				}
			}else{
				echo (fSendStatusMessage("error", $conn->error)); die();
			}

			$VoucherBalance = sizeof($arrVoucher);
			if ($numOfVoucherRequired > $VoucherBalance){ //VoucherBalance not enough
				echo (fSendStatusMessage("error", "Your Balance is not enough")); die();
			}
		}

		//1.3 Update dtFundInVoucher (status="USED", usedFor="ACTIVATION", usedOn=USERNAME, fivDate="CURRENT_TIME()")
		$arrData	= array(
			"fivStatus" 	=> $DEF_STATUS_USED,
			"fivUsedFor" 	=> $DEF_VOUCHER_USED_FOR_RO,
			"fivUserOn" 	=> $username,
			"fivDate"       => "CURRENT_TIME()"
		);
		
		
		$arrDataQuery = array();
		$counter = 0;
		//moving some data of arrVoucher to arrDataQuery 
		foreach ($arrVoucher as $key => $value){
			if ($counter >= $numOfVoucherRequired) {
				break;
			}else{
				$arrDataQuery = array (
					"fivFinID" => $value["fivFinID"], 
					"fivVCode" => $value["fivVCode"]
				);
				$counter++;
				if (!fUpdateRecord("dtFundInVoucher", $arrData, $arrDataQuery, $conn)){
					$conn->rollback();
					echo (fSendStatusMessage("error", "<b>Update FundInVoucher - </b>" . $conn->error)); die();
				}
				unset($arrDataQuery);
			}
		}
		unset($arrData);
		unset($arrDataQuery);

		//1.4 update product payment status
		$arrData = array(
			"trProUpdateDate" 	=> "CURRENT_TIME()",
			"trProActiveDate" 	=> "CURRENT_TIME()",
			"trProStatus" 		=> $DEF_STATUS_APPROVED
		);
		$arrDataQuery = array(
			"trProTransID" 	=> $proTransID,
			"trProUsername" => $username
		);
		if (!fUpdateRecord("trProduct", $arrData, $arrDataQuery, $conn)){
			$conn->rollback();
			echo (fSendStatusMessage("error", "<b>Update trProduct - </b>" . $conn->error)); die();
		}else{
			//update data from dtUserEbook
			unset($arrData);
			unset($arrDataQuery);
			$arrData = array(
				"ebDate"	=> "CURRENT_TIME()",
				"ebStatus"	=> $DEF_STATUS_ACTIVE
			);
			$arrDataQuery = array (
				"ebproTransID" 	=> $proTransID,
				"ebUsername" 	=> $trProUserBeli
			);
			if (!fUpdateRecord("dtUserEbook", $arrData, $arrDataQuery, $conn)){
				$conn->rollback();
				fSendToAdmin("Repeat Order", "trProduct.php", "Insert data to dtUserEbook failed");
				echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . $conn->error));
				die();
			}
		}
		//1.5 email notification
		$sql  = "SELECT trProTransID, trProType, mbrSponsor";
		$sql .= " FROM trProduct";
		$sql .= " INNER JOIN dtUserEbook ON ebProTransID = trProTransID";
		$sql .= " INNER JOIN dtMember ON mbrUsername = trProUsername";
		$sql .= " WHERE trProTransID = '".$proTransID."'";
		$sql .= " ORDER BY trProUpdateDate DESC LIMIT 1";
		$result=$conn->query($sql);
		if ($result->num_rows > 0){
			if ($row=$result->fetch_assoc()){
				$trProType = $row['trProType'];
				if ($trProType == $DEF_TYPE_PURCHASE_RS){
					$BnsROUsername = $username;
				}else if ($trProType == $DEF_TYPE_PURCHASE_RO){
					$BnsROUsername = $row['mbrSponsor'];
				}
				unset($arrData);
				unset($arrDataQuery);
				//hitung bonus repeat order
				$table = "dtBnsRO";
				$bnsRO = $totalPayBV * $DEF_BONUS_RO / 100;
				$arrData = array(
		            array ("db" => "BnsROID"		, "val" => $proTransID),
		            array ("db" => "BnsROUsername"	, "val" => $BnsROUsername),
		            array ("db" => "BnsROAmount"	, "val" => $bnsRO),
		            array ("db" => "BnsRODate"		, "val" => "CURRENT_TIME()")             
		        );
		        if (!fInsert($table, $arrData, $conn)){
					$conn->rollback();
					fSendToAdmin("Repeat Order", "trProduct.php", "Insert data to dtBnsRO failed");
					echo (fSendStatusMessage("error", "<b>Failed save bonus RO - </b>" . $conn->error));
					die();
				}
			}
		}

		$conn->commit(); // save dulu baru kirim email
		if ($trProType == $DEF_TYPE_PURCHASE_RS){
			//email payment success ke pembeli (RO)
			fToCornEmail($conn, 'BUY_PRODUCT_RS', '', $row['trProTransID']);
		}else if ($trProType == $DEF_TYPE_PURCHASE_RO){
			//email user dan pass ke pembeli (reseller)
			fToCornEmail($conn, 'BUY_PRODUCT', '', $row['trProTransID']);
		}
		echo (fSendStatusMessage("success", "Payment success")); die();
	}else if (strtolower($q) == strtolower("payCancel")){
		$proTransID 	= fValidateSQLFromInput($conn, (isset($_POST['proTransID'])))?fValidateSQLFromInput($conn, $_POST['proTransID']): '';
		$arrData = array(
			"trProUpdateDate" 	=> "CURRENT_TIME()",
			"trProStatus" 		=> $DEF_STATUS_CANCEL
		);
		$arrDataQuery = array(
			"trProTransID" 	=> $proTransID
		);
		if (!fUpdateRecord("trProduct", $arrData, $arrDataQuery, $conn)){
			echo (fSendStatusMessage("error", "<b>Failed to cancel Order No : ".$proTransID." #1 - </b>" . $conn->error));
			$conn->rollback();
			die();
		}else{
			$conn->commit();
			echo (fSendStatusMessage("success", "Order Cancelled")); die();
		}
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $COMPANY_NAME ?> - List E-Book</title>
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
	<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
	<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
	<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<script>
		function isNumberKey(evt){
		    var charCode = (evt.which) ? evt.which : evt.keyCode
		    if (charCode > 31 && (charCode < 48 || charCode > 57))
		        return false;
		    return true;
		}

		$(document).ready(function(e){
			var tProPurchasedttl = $("#tProPurchased").attr("value");
			if (tProPurchasedttl > 0){
				$("#tProPurchased").DataTable({
					lengthMenu : [10,25,50,100,200],
					order: [[ 4, "desc" ]] 
				});
			}

			var tproCanceledttl = $("#tproCanceled").attr("value");
			if (tproCanceledttl > 0){
				$("#tproCanceled").DataTable({
					lengthMenu : [10,25,50,100,200],
					order: [[ 4, "desc" ]] 
				});
			}

			var tProSalettl = $("#tProSale").attr("value");
			if (tProSalettl > 0){
				$("#tProSale").DataTable({
					lengthMenu : [10,25,50,100,200],
					order: [[ 4, "desc" ]] 
				});
			}

			$("input[name='proQty']").on("change", function(){
				var proPrice 	= parseInt($(this).attr("data-price"));
				var proQty 		= parseInt($(this).val());
				var proAmount = 0
				proAmount = proPrice * proQty;
				var SproAmount = "IDR "+Number(proAmount).toLocaleString();
				var obj = $(this).closest('td').next('td');
				obj.html(SproAmount);
				obj.attr("data-amount", proAmount);
			});

			$("input[name='typeofpurchase']").on("change", function(){
				$("#customer").toggle();
			});
	
			$("a[href='#btnCheckOut']").on("click", function(){
				var typeofpurchase = $("input[name='typeofpurchase']:checked").val();
				if (typeofpurchase == "" || typeof(typeofpurchase) == "undefined"){
					demo.showNotification('top','center', 'info', 'Chose type of purchase');
					return false;
				}

				var cusUsername = cusEmail = cusFirstName = cusLastName = "";
				if (typeofpurchase == "<?php echo $DEF_TYPE_PURCHASE_RS; ?>"){
					cusUsername 	= $("#cusUsername").val(); 
					cusEmail 		= $("#cusEmail").val();
					cusFirstName 	= $("#cusFirstName").val();
					cusLastName		= $("#cusLastName").val();
					if (cusUsername == "" || cusEmail == "" || cusFirstName == "" || cusLastName == ""){
						demo.showNotification('top','center', 'info', 'Data customer required');
						return false;
					}
				}

				var arrData = new Array();
				$(".ebook").each(function(){
					var obj 		= $(this).find("input[name='proQty']");
					var proID 		= obj.attr("data-id");
					var proPrice 	= obj.attr("data-price");
					var proQty 		= obj.val();
					if (proQty > 0){
						arrData.push({
	                        "proID"		: proID,
	                        "proPrice"	: proPrice,
	                        "proQty"	: proQty              
	                    });
					}
				});
				var myJSON = JSON.stringify(arrData);
				if (arrData.length == 0){
					demo.showNotification('top','center', 'info', 'Chose at least 1(one) product');
					return false;
				}
				swal({
	                title: 'Apakah Anda yakin ?',
	                text: 'Anda akan melanjutkan proses pembelian',
	                type: 'warning',
	                showCancelButton: true,
	                confirmButtonText: 'Ya',
	                cancelButtonText: 'Batal',
	                confirmButtonClass: "btn btn-success",
	                cancelButtonClass: "btn btn-danger",
	                buttonsStyling: false,
	                allowOutsideClick: false,
                    allowEscapeKey: false
	            }).then(function() {
	            	var username = $("#username").val();
	            	$.post("trProduct.php",
	            	{
	            		"q" 				: "buyProduct",
		                "username"			: username,
		                "typeofpurchase" 	: typeofpurchase,
		                "cusUsername"		: cusUsername,
		                "cusEmail"			: cusEmail,
		                "cusFirstName"		: cusFirstName,
		                "cusLastName"		: cusLastName,
		                "myJSON"			: myJSON    
		            },
		            function(data, success){
	            		// console.log(data);
	            		$myDataObj = JSON.parse(data);
	            		if ($myDataObj['status'] == "error"){
	            			swal({
								type 	: 'error',
								title 	: 'Order Canceled',
								text 	: $myDataObj['message'],
								allowOutsideClick: false,
                    			allowEscapeKey: false
							}).then(function() {
								location.reload();
							});
	            		}else if ($myDataObj['status'] == "success"){
	            			swal({
								type 	: 'success',
								title 	: 'Order Success',
								text 	: $myDataObj['message'],
								allowOutsideClick: false,
                    			allowEscapeKey: false
							}).then(function() {
								location.reload(); 
								location.hash = "#tPendingOrder";
							});
	            		}
	            	});
	            }, function(dismiss) {
	                // dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'
	                return false;
	                // if (dismiss === 'cancel') {
	                //     swal({
	                //         title: 'Dibatalkan',
	                //         text: 'Pembelian produk dibatalkan',
	                //         type: 'error',
	                //         confirmButtonClass: "btn btn-info",
	                //         buttonsStyling: false,
	                //         allowOutsideClick: false,
		               //      allowEscapeKey: false
	                //     }).then(function(){
	                //     	location.reload();
	                //     });
	                // }
	            });
			});
			
			$("a[href='#btnDpay']").on("click", function(){
				var proTransID = $(this).attr("data-value");
				location.href = "./trProDetail.php?id="+proTransID; //return false;
			});
			$("a[href='#btnPay']").on("click", function(){
				var proTransID = $(this).attr("data-value");
				var username = $("#username").val();
				swal({
	                title: 'Apakah Anda yakin ?',
	                text: 'Anda akan melanjutkan proses pembayaran',
	                type: 'warning',
	                showCancelButton: true,
	                confirmButtonText: 'Ya',
	                cancelButtonText: 'Batal',
	                confirmButtonClass: "btn btn-success",
	                cancelButtonClass: "btn btn-danger",
	                buttonsStyling: false,
	                allowOutsideClick: false,
                    allowEscapeKey: false
	            }).then(function() {
					$.post("trProduct.php",
					{
						"q" 			: "payProduct",
						"username" 		: username,
						"proTransID" 	: proTransID
					},
					function(data, success){
						// console.log(data);
	            		$myDataObj = JSON.parse(data);
	            		if ($myDataObj['status'] == "error"){
	            			swal({
								type 	: 'error',
								title 	: 'Payment Canceled',
								text 	: $myDataObj['message'],
								allowOutsideClick: false,
	                			allowEscapeKey: false
							}).then(function() {
								location.reload();
							});
	            		}else if ($myDataObj['status'] == "success"){
	            			swal({
								type 	: 'success',
								title 	: 'Payment Success',
								text 	: $myDataObj['message'],
								allowOutsideClick: false,
	                			allowEscapeKey: false
							}).then(function() {
								location.reload();
							});
	            		}
					});
				}, function(dismiss) {
	                // dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'
	                return false;
	                // if (dismiss === 'cancel') {
	                //     swal({
	                //         title: 'Dibatalkan',
	                //         text: 'Pembayaran dibatalkan',
	                //         type: 'error',
	                //         confirmButtonClass: "btn btn-info",
	                //         buttonsStyling: false,
	                //         allowOutsideClick: false,
		               //      allowEscapeKey: false
	                //     }).then(function(){
	                //     	location.reload();
	                //     });
	                // }
	            });
			});

			$("a[href='#btnCancel']").on("click", function(){
				var proTransID = $(this).attr("data-value");
				swal({
	                title: 'Apakah Anda yakin ?',
	                text: 'Anda akan membatalkan Order No : '+proTransID,
	                type: 'warning',
	                showCancelButton: true,
	                confirmButtonText: 'Ya',
	                cancelButtonText: 'Close',
	                confirmButtonClass: "btn btn-danger",
	                cancelButtonClass: "btn btn-seconday",
	                buttonsStyling: false,
	                allowOutsideClick: false,
                    allowEscapeKey: false
	            }).then(function() {
	            	$.post("trProduct.php",
	            	{
	            		"q" 				: "payCancel",
		                "proTransID" 		: proTransID    
		            },
		            function (data, status){
		            	// console.log(data);
		            	$myDataObj = JSON.parse(data);
	            		if ($myDataObj['status'] == "error"){
	            			swal({
								type 	: 'error',
								title 	: 'Failed Cancel Order',
								text 	: $myDataObj['message'],
								allowOutsideClick: false,
                    			allowEscapeKey: false
							}).then(function() {
								location.reload();
							});
	            		}else if ($myDataObj['status'] == "success"){
	            			swal({
								type 	: 'success',
								title 	: 'Success Cancel Order',
								text 	: $myDataObj['message'],
								allowOutsideClick: false,
                    			allowEscapeKey: false
							}).then(function() {
								location.reload();
							});
	            		}
		            });
            	}, function(dismiss) {
            		return false;
            	});
			});
		});
	</script>
</head>
<body>
	<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="fa fa-bar-chart fa-2x"></i>
        </div>
        <div class="card-text"><h4 class="card-title">List Product</h4></div>
    </div>
    <div class="card-body card-fix">
    	<div class="container">
            <div class="container-fluid">
                <div class="card" >
                    <div class="card-body">
                    	<input type="hidden" name="username" id="username" value="<?php echo $username ?>">
                    	<ul class="nav nav-pills nav-pills-rose" role="tablist">
							<li class="nav-item">
								<a class="nav-link <?php echo (setActive($subNav, "proTrans")); ?>" data-toggle="tab" href="#proTrans" role="tablist">
									Buy Product
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php echo (setActive($subNav, "proPurchased")); ?>" data-toggle="tab" href="#proPurchased" role="tablist">
									Purchased
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php echo (setActive($subNav, "proSale")); ?>" data-toggle="tab" href="#proSale" role="tablist">
									Sale
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php echo (setActive($subNav, "proCanceled")); ?>" data-toggle="tab" href="#proCanceled" role="tablist">
									Canceled
								</a>
							</li>
						</ul>
						<div class="tab-content tab-space">
							<div class="tab-pane <?php echo (setActive($subNav, "proTrans")); ?>" id="proTrans">
								<div class="table-responsive">
									<form>
										<div class="form-group bmd-form-group col-md-12">
											<div class="input-group-prepend">
												<span class="input-group-text">
													Type of purchase
												</span>
											</div>
		                                    <div class="form-check form-check-radio form-check-inline">
												<label class="form-check-label">
													<input class="form-check-input" type="radio" name="typeofpurchase" id="inlineRadio1" value="<?php echo $DEF_TYPE_PURCHASE_RO; ?>" checked> Repeat Order
													<span class="circle">
														<span class="check"></span>
													</span>
												</label>
											</div>
											<div class="form-check form-check-radio form-check-inline">
												<label class="form-check-label">
													<input class="form-check-input" type="radio" name="typeofpurchase" id="inlineRadio2" value="<?php echo $DEF_TYPE_PURCHASE_RS; ?>"> Reseller
													<span class="circle">
														<span class="check"></span>
													</span>
												</label>
											</div>
		                                </div>
		                                <div class="card col-md-6" id="customer" style="display: none;">
		                                	<div class="card-body card-fix col-md-12">
		                                		<h4 class="profile">Data Customer</h4>
                                				<hr>
                                				<div class="row">
                                					<div class="form-group bmd-form-group col-md-6">
					                                	<label for="cusUsername" class="bmd-label-floating">Username</label>
			                                        	<input type="text" class="form-control" name="cusUsername" id="cusUsername" maxlength="40" required>
					                                </div>
					                                <div class="form-group bmd-form-group col-md-6">
					                                	<label for="cusEmail" class="bmd-label-floating">Email</label>
			                                        	<input type="text" class="form-control" name="cusEmail" id="cusEmail" maxlength="40" required>
					                                </div>
					                                <div class="form-group bmd-form-group col-md-6">
					                                	<label for="cusFirstName" class="bmd-label-floating">First Name</label>
			                                        	<input type="text" class="form-control" name="cusFirstName" id="cusFirstName" maxlength="40" required>
					                                </div>
					                                <div class="form-group bmd-form-group col-md-6">
					                                	<label for="cusLastName" class="bmd-label-floating">Last Name</label>
			                                        	<input type="text" class="form-control" name="cusLastName" id="cusLastName" maxlength="40" required>
					                                </div>
					                            </div>
				                            </div>
			                            </div>
									</form>
									<table class="table table-shopping">
										<thead>
											<tr>
												<th></th>
												<th>Product</th>
												<th class="th-description">Size</th>
												<th>Price</th>
												<th>Qty</th>
												<th>Amount</th>
											</tr>
										</thead>
										<?php
										$sql  = "SELECT * FROM msProduct";
										$result = $conn->query($sql);

										?>
										<tbody>
										<?php
										if ($result->num_rows == 0){
											echo "<tr><td colspan=7 class='text-center text-primary'>Comming Soon</td></tr>";	
										}else{
											while($row=$result->fetch_assoc()){
										?>
											<tr class="ebook">
												<td>
													<div class="img-container">
														<img src="<?php echo ('../../images/mockup/'.$row['proImage']); ?>" alt="...">
													</div>
												</td>
												<td class="td-name">
													<a href="#jacket"><?php echo $row['proName']; ?></a>
													<br><small>by Yulianto Hiu</small>
												</td>
												<td><?php echo $row['proPages']; ?> Pages</td>
												<td class="proPrice"><?php echo "IDR ". numFormat($row['proPrice'], 0) ?></td>
												<td>
													<input type="number" class="form-control" name="proQty" data-price="<?php echo $row['proPrice']; ?>" data-id="<?php echo $row['proID']; ?>" min="0" step="1" value="0" onkeypress="return isNumberKey(event)">
												</td>
												<td class="proAmount" data-amount="0">Rp 0</td>
											</tr>
										<?php 
											}
										}
										?>
											<tr>
												<td colspan="5"></td>
												<td><a href="#btnCheckOut" class="btn btn-rose btn-round btn-block">Checkout</a></td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="card" >
				                    <div class="card-body">
				                    	<ul class="nav nav-pills nav-pills-rose" role="tablist">
											<li class="nav-item">
												<a class="nav-link active" data-toggle="tab" href="#" role="tablist">
													On Progress / Pending Order
												</a>
											</li>
										</ul>
										<div class="tab-content tab-space">
											<div class="tab-pane active">
												<div class="table-responsive">
													<table class="table table-small" id="tPendingOrder">
														<thead>
															<th></th>
															<th>Transaction ID</th>
															<th>Order Date</th>
															<th>Amount</th>
															<th>Discount</th>
															<th>Total Amount</th>
															<th></th>
															<th></th>
														</thead>
														<?php
														$sql  = "SELECT * FROM trProduct";
														$sql .= " WHERE trProUsername = '".$username."' AND trProStatus = '".$DEF_STATUS_ONPROGRESS."' ";
														$sql .= " ORDER BY trProDate DESC";
														$result = $conn->query($sql); 
														?>
														<tbody>
														<?php 
														if ($result->num_rows == 0){
															echo "<tr><td colspan ='6' class='text-center text-primary'>No Record</td></tr>";
														}else{
															while ($row = $result->fetch_assoc()){
																$tAmount = $row['trProAmount'] - $row['trProDisc'] ;
														?>
														<tr>
															<td></td>
															<td><?php echo $row['trProTransID']; ?></td>
															<td><?php echo $row['trProDate']; ?></td>
															<td><?php echo $row['trProAmount']; ?></td>
															<td><?php echo $row['trProDisc']; ?></td>
															<td><?php echo $tAmount; ?></td>
															<td>
																<a href="#btnDpay" class="btn btn-success btn-round btn-block" data-value="<?php echo $row['trProTransID'] ?>">Detail Payment</a>
																<a href="#btnPay" class="btn btn-rose btn-round btn-block" data-value="<?php echo $row['trProTransID']; ?>">Pay</a>
															</td>
															<td><a href="#btnCancel" class="btn btn-danger btn-round" data-value="<?php echo $row['trProTransID']; ?>"><i class="fa fa-close"></i></a></td>
														</tr>
														<?php
															}
														} 
														?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane <?php echo (setActive($subNav, "proPurchased")); ?>" id="proPurchased">
								<div class="table-responsive">
									<?php 
									$sql  = "SELECT * FROM trProduct";
									$sql .= " WHERE trProUsername = '".$username."' AND trProStatus = '".$DEF_STATUS_APPROVED."' AND trProType != 'MUTASI' ";
									$sql .= " AND trProType = '".$DEF_TYPE_PURCHASE_RO."' ";
									$result = $conn->query($sql);
									$tProPurchasedttl = $result->num_rows;
									?>
									<table class="table table-small" id="tProPurchased" value="<?php echo $tProPurchasedttl ?>">
										<thead>
											<tr>
												<th>Transaction ID</th>
												<th>Amount</th>
												<th>Discount</th>
												<th>Total Amount</th>
												<th>Payment Date</th>
												<th>Description</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											<?php 
											if ($tProPurchasedttl == 0){
												echo "<tr><td colspan ='7' class='text-center text-primary'>No Record</td></tr>";
											}else{
												while ($row = $result->fetch_assoc()){
													$tAmount = $row['trProAmount'] - $row['trProDisc'] ;
											?>
											<tr>
												<td><?php echo $row['trProTransID']; ?></td>
												<td><?php echo $row['trProAmount']; ?></td>
												<td><?php echo $row['trProDisc']; ?></td>
												<td><?php echo $tAmount; ?></td>
												<td><?php echo $row['trProUpdateDate']; ?></td>
												<td><?php echo $row['trProType']; ?></td>
												<td><a href="#btnDpay" class="btn btn-success btn-round btn-block" data-value="<?php echo $row['trProTransID'] ?>">Detail Payment</a></td>
											</tr>
											<?php
												}
											} 
											?>
										</tbody>
									</table>
								</div>
							</div>
							<div class="tab-pane <?php echo (setActive($subNav, "proSale")); ?>" id="proSale">
								<div class="table-responsive">
									<?php 
									$sql  = "SELECT * FROM trProduct";
									$sql .= " WHERE trProUsername = '".$username."' AND trProStatus = '".$DEF_STATUS_APPROVED."' AND trProType != 'MUTASI'";
									$sql .= " AND trProType != '".$DEF_TYPE_PURCHASE_RO."' ";
									$result = $conn->query($sql);
									$trProSalettl = $result->num_rows;
									?>
									<table class="table table-small" id="tProSale" value="<?php echo $trProSalettl ?>">
										<thead>
											<tr>
												<th>Transaction ID</th>
												<th>Amount</th>
												<th>Discount</th>
												<th>Total Amount</th>
												<th>Payment Date</th>
												<th>Description</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											<?php 
											if ($trProSalettl == 0){
												echo "<tr><td colspan ='7' class='text-center text-primary'>No Record</td></tr>";
											}else{
												while ($row = $result->fetch_assoc()){
													$tAmount = $row['trProAmount'] - $row['trProDisc'] ;
											?>
											<tr>
												<td><?php echo $row['trProTransID']; ?></td>
												<td><?php echo $row['trProAmount']; ?></td>
												<td><?php echo $row['trProDisc']; ?></td>
												<td><?php echo $tAmount; ?></td>
												<td><?php echo $row['trProUpdateDate']; ?></td>
												<td><?php echo $row['trProType']; ?></td>
												<td><a href="#btnDpay" class="btn btn-success btn-round btn-block" data-value="<?php echo $row['trProTransID'] ?>">Detail Payment</a></td>
											</tr>
											<?php 
												}
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
							<div class="tab-pane <?php echo (setActive($subNav, "proCanceled")); ?>" id="proCanceled">
								<div class="table-responsive">
									<?php 
									$sql  = "SELECT * FROM trProduct";
									$sql .= " WHERE trProUsername = '".$username."' AND trProStatus = '".$DEF_STATUS_CANCEL."' ";
									$result = $conn->query($sql);
									$tproCanceledttl = $result->num_rows;
									?>
									<table class="table table-small" id="tproCanceled" value="<?php echo $tproCanceledttl; ?>">
										<thead>
											<tr>
												<th>Transaction ID</th>
												<th>Order Date</th>
												<th>Amount</th>
												<th>Discount</th>
												<th>Total Amount</th>
											</tr>
										</thead>
										<tbody>
											<?php 
											if ($result->num_rows == 0){
												echo "<tr><td colspan ='6' class='text-center text-primary'>No Record</td></tr>";
											}else{
												while ($row = $result->fetch_assoc()){
													$tAmount = $row['trProAmount'] - $row['trProDisc'] ;
											?>
											<tr>
												<td><?php echo $row['trProTransID']; ?></td>
												<td><?php echo $row['trProDate']; ?></td>
												<td><?php echo $row['trProAmount']; ?></td>
												<td><?php echo $row['trProDisc']; ?></td>
												<td><?php echo $tAmount; ?></td>
											</tr>
											<?php 
												}
											}
											?>
										</tbody>
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

<!--   Core JS Files   -->
<!-- <script src="../assets/js/core/jquery.min.js"></script> -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/bootstrap-material-design.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>

<!--  Google Maps Plugin  -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB2Yno10-YTnLjjn_Vtk0V8cdcY5lC4plU"></script>

<!--  Plugin for Date Time Picker and Full Calendar Plugin  -->
<script src="../assets/js/plugins/moment.min.js"></script>

<!--	Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
<script src="../assets/js/plugins/bootstrap-datetimepicker.min.js"></script>

<!--	Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="../assets/js/plugins/nouislider.min.js"></script>

<!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/bootstrap-selectpicker.js"></script>

<!--	Plugin for Tags, full documentation here: http://xoxco.com/projects/code/tagsinput/  -->
<script src="../assets/js/plugins/bootstrap-tagsinput.js"></script>

<!--	Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
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

<!--  Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/jquery.select-bootstrap.js"></script>

<!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
<script src="../assets/js/plugins/jquery.datatables.js"></script>

<!-- Sweet Alert 2 plugin, full documentation here: https://limonte.github.io/sweetalert2/ -->
<script src="../assets/js/plugins/sweetalert2.js"></script>

<!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
<script src="../assets/js/plugins/fullcalendar.min.js"></script>

<!-- demo init -->
<script src="../assets/js/plugins/demo.js"></script>

</html>