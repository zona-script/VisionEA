<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");
if ($_GET){
	$username =  ($_SESSION['sUserName']);
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>VisionEA - Achievement</title>
	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons"/>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css"/>
	<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
	<link rel="stylesheet" href="../assets/css/newBinary.css">
	<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
	<link rel="stylesheet" href="../assets/css/circle.css">
	<script src="../assets/js/core/jquery.min.js"></script>
	<script>
		$(document).ready(function(e) {
			$('.nav a').on('click', function(){
				$cat = "Achievement";
				$subCat = "";
				if ($.trim($(this).attr('href')) == "#linkProgress"){
					$subCat	= "Progress";
				}else if ($.trim($(this).attr('href')) == "#linkReward"){
					$subCat	= "Rewards";
				}else if ($.trim($(this).attr('href')) == "#linkClaim"){
					$subCat	= "Claim";
				}
				
				$cat +=($subCat != "")? " :: " + $subCat : "";
				$("#cat").html($cat);
				
			}); //end $('a').on click
			
			var username = $("#hUserName").val();
			$.get("mbrJSON.php",{
				"q" 		: "calculate_achievement",
				"username" 	: username
			},
			function (data, success){
				console.log(data);
				$("#linkProgress").html(data);
				$myDataObj 		= JSON.parse(data);
				if ($myDataObj['status'] == "success"){
					$left 			= $myDataObj['left'];
					$right 			= $myDataObj['right'];
					$direct 		= $myDataObj['direct'];
					$l1 			= $myDataObj['l1'];
					$l2 			= $myDataObj['l2'];
					$persenleft 	= $myDataObj['persenleft'];
					$persenright	= $myDataObj['persenright'];
					$persendirect	= $myDataObj['persendirect'];
					$persenl1		= $myDataObj['persenl1'];
					$persenl2		= $myDataObj['persenl2'];
					$nextLeft 		= $myDataObj['nextLeft'];
					$nextRight 		= $myDataObj['nextRight'];
					$nextDirect 	= $myDataObj['nextDirect'];
					$nextL1 		= $myDataObj['nextL1'];
					$nextL2 		= $myDataObj['nextL2'];
					$nextRewardName = $myDataObj['nextRewardName'];

					if ($nextL1 == 0){
						$(".l1").css("display","none");
						
					}
					if ($nextL2 == 0){
						$(".l2").css("display","none");
						
					}

					$(".left .c100:last").addClass('p'+$persenleft);
					$(".left #circle-value").html($left+' / '+$nextLeft);
					$(".right .c100:last").addClass('p'+$persenright);
					$(".right #circle-value").html($right+' / '+$nextRight);
					$(".direct .c100:last").addClass('p'+$persendirect);
					$(".direct #circle-value").html($direct+' / '+$nextDirect);
					$(".l1 .c100:last").addClass('p'+$persenl1);
					$(".l1 #circle-value").html($l1+' / '+$nextL1);
					$(".l2 .c100:last").addClass('p'+$persenl2);
					$(".l2 #circle-value").html($l2+' / '+$nextL2);
					// $(".requirement").html($requirement);
					$(".rank img").attr("src","../assets/img/achievement/"+$nextRewardName+".png");
					$(".requirement img").attr("src","../assets/img/achievement/rwd_"+$nextRewardName+".png");
					$nextRewardName = $nextRewardName.toLowerCase().replace(/\b[a-z]/g, function(letter) {
					    return letter.toUpperCase();
					});
					$(".rank span").html($nextRewardName+" Achievement");
					
					// reward archieved
					/* dimatikan sementara
					$arclevel 	= parseInt($myDataObj['arclevel']);
					var img = ""
					for (i = 1; i <= $arclevel; i++) {
						$imgachieved = ".rwd"+i;
						img = '<img src="../assets/img/achievement/bronze.png" class="img-fluid" width="70%" height="70%" style="opacity:0.8;">';
						$($imgachieved).html(img);
						$(".claim"+i).attr("disabled",false);
						$(".claim"+i+" span").remove();
					}
					*/
					// end reward archieved
				}else{
					$("#linkProgress").html("<div class='text-center text-warning'>"+$myDataObj['message']+"<br>Contact our support team for more info</div>");
				}
				
			});
		});
	</script>
	<style>
		/*bronze silver gold ruby emerald sapphire diamond kingdiamond*/
	.img-achieved{
		text-align: center;
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		color: white;
	}
	</style>
</head>
<body>
	<input type="hidden" id="hUserName" value="<?php echo $username; ?>">
	<span id="q"></span>
	<div class="card">
	    <div class="card-header card-header-success card-header-icon">
			<div class="card-icon">
				<i class="fa fa-star-o fa-2x"></i>
			</div>
			<div class="card-text">
			 	<h4 class="card-title" id="cat">Achievement :: Progress</h4>
			</div>
	    </div>
	    <div class="card-body card-fix">
	        <div class="row">
	        	<div class="col-md-2">
                    <ul class="nav nav-pills nav-pills-rose nav-pills-icons flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#linkProgress" role="tablist">
                                <i class="fa fa-trophy"></i>Progress
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " data-toggle="tab" href="#linkReward" role="tablist">
                                <i class="fa fa-gift"></i>Reward List
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " data-toggle="tab" href="#linkClaim" role="tablist">
                                <i class="fa fa-cube"></i>Claim
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-10">
                	<div class="tab-content">
                		<div class="tab-pane active" id="linkProgress">
	                		<div class="rank">
	                			<img src="" class="img-fluid" width="10%" height="10%">
	                			<span></span>
	                		</div>
	                		<br>
                			<div class="row">
                				<div class="col-md-4">
		                			<div class="inner-content text-center direct">
			                			<div class="c100 center">
						                    <span id="circle-value"></span>
						                    <div class="slice">
						                        <div class="bar"></div>
						                        <div class="fill"></div>
						                    </div>
			                			</div>
			                			<p><em>Direct</em></p>
			                		</div>
		                		</div>
                				<div class="col-md-4">
                					<div class="inner-content text-center left">
										<div class="c100 center">
						                    <span id="circle-value"></span>
						                    <div class="slice">
						                        <div class="bar"></div>
						                        <div class="fill"></div>
						                    </div>
			                			</div>
			                			<p><em>Left</em></p>
			                		</div>
		                		</div>
		                		<div class="col-md-4">
		                			<div class="inner-content text-center right">
			                			<div class="c100 center">
						                    <span id="circle-value"></span>
						                    <div class="slice">
						                        <div class="bar"></div>
						                        <div class="fill"></div>
						                    </div>
			                			</div>
			                			<p><em>Right</em></p>
			                		</div>
		                		</div>
		                		<div class="col-md-4">
		                			<div class="inner-content text-center l1">
			                			<div class="c100 center">
						                    <span id="circle-value"></span>
						                    <div class="slice">
						                        <div class="bar"></div>
						                        <div class="fill"></div>
						                    </div>
			                			</div>
			                			<p><em>L1</em></p>
			                		</div>
		                		</div>
                				<div class="col-md-4">
                					<div class="inner-content text-center l2">
										<div class="c100 center">
						                    <span id="circle-value"></span>
						                    <div class="slice">
						                        <div class="bar"></div>
						                        <div class="fill"></div>
						                    </div>
			                			</div>
			                			<p><em>L2</em></p>
			                		</div>
		                		</div>
		                		<div class="col-md-12">
		                			<div class="requirement" style="margin-bottom: 120px;">
			                			<img src="" class="img-fluid">
			                		</div>
		                		</div>
	                		</div>
                        </div>
                        <div class="tab-pane " id="linkReward">
                        	<div class="row" style="margin-bottom: 150px;">
                        		<div class="col-md-12">
                        			<img src="../assets/img/achievement/rwd.png" class="img-fluid">
                        		</div>
                        		<!-- <div class="col-md-12 text-center">
                        			<div class="img-rwd">
                        				<img src="../assets/img/achievement/rwd_bronze.png" class="img-fluid">
                        				<div class="img-achieved">
                        					<img src="../assets/img/achievement/bronze.png" class="img-fluid" width="50%" height="50%" style="opacity: 0.5;">
                        				</div>
                        			</div>
                        		</div>
                        		<div class="col-md-12 text-center">
                        			<a href="#claimrwd" class="btn btn-success" disabled><i class="fa fa-lock"></i> Claim</a>
                        		</div> -->
                        		<?php
                        		$sql  = "SELECT rwdID, rwdImage FROM msReward";
                        		$sql .= " ORDER BY rwdID ASC";
                        		$result = $conn->query($sql);
                        		$i=0;
                        		while ($row = $result->fetch_assoc()) {
                        			$i++;
                        			$rwdImage 	= $row['rwdImage'];
                        		?>
                        		<div class="card">
                        			<div class="card-body">
		                        		<div class="col-md-12 text-center">
		                        			<div class="img-rwd">
		                        				<img src="../assets/img/achievement/<?php echo $rwdImage; ?>" class="img-fluid">
		                        				<div class="img-achieved rwd<?php echo $i; ?>">
		                        					
		                        				</div>
		                        			</div>
		                        		</div>
		                        	</div>
		                        </div>
                        		<?php
                        		} 
                        		?>
                        	</div>
                        </div>
                        <div class="tab-pane " id="linkClaim">
	                    	<table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
		                    	<thead>
		                    		<tr>
		                    			<th>#</th><th>Date Claim</th><th>Level</th><th>Prize</th><th>Status</th><th>Date Delivered</th>
		                    		</tr>
		                    	</thead>
		                    	<tfoot>
		                    		<tr>
		                    			<th>#</th><th>Date Claim</th><th>Level</th><th>Prize</th><th>Status</th><th>Date Delivered</th>
		                    		</tr>
		                    	</tfoot>
		                    	<tbody>
		                        <?php
		                        $sql  = "SELECT achDate, achStatus, achUpdateDate, rwdName, rwdPrize FROM dtArchiever";
		                        $sql .= " INNER JOIN msReward ON achRwdID = rwdID";
		                        $sql .= " WHERE achUsername = '".$username."'";
		                        $sql .= " ORDER BY achUpdateDate DESC";
		                        $result = $conn->query($sql);
		                        $i = 0;
		                        if ($result->num_rows>0){
			                        while ($row = $result->fetch_assoc()) {
			                        	$i++;
			                        	$achdate 	= date_create($row['achDate']);
			                        	$achdate 	= date_format($achdate,"F d, Y");
			                        	$upDate 	= date_create($row['achUpdateDate']);
			                        	$upDate 	= date_format($upDate,"F d, Y");
			                        	if ($row['achStatus'] == $DEF_STATUS_NOT_YET_SENT){
			                        		$stClaim = "<span class='text-warning'>On Progress</span>";
			                        	}else if ($row['achStatus'] == $DEF_STATUS_SENT){
			                        		$stClaim = "<span class='text-success'>Delivered / Received</span>";
			                        	}
			                        	($row['achUpdateDate'] == 0)?$upDate = "-" : $upDate;
		                        ?>
		                        	<tr>
		                        		<td><?php echo $i; ?></td>
		                        		<td><?php echo $achdate; ?></td>
		                        		<td><?php echo $row['rwdName']; ?></td>
		                        		<td><?php echo $row['rwdPrize']; ?></td>
		                        		<td><?php echo $stClaim; ?></td>
		                        		<td><?php echo $upDate; ?></td>
		                        	</tr>
		                        <?php
		                        	}
		                        }else{
		                        	echo "<tr><td colspan='6' class='text-center'><span class='text-info'>No Record</span></td></tr>";
		                        }
		                        ?>
	                        	</tbody>
	                        </table>
                        </div>
                	</div>
                </div>
	        </div>
     	</div> <!-- card-body -->
	</div>
</body>
<!--   Core JS Files   -->
<script src="../assets/js/core/jquery.min.js"></script>
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