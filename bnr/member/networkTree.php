<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

if ($_GET){
	$searchUsername = (isset($_GET["searchUsername"]))?$_GET["searchUsername"]: "";	
	$usernameSP		= (isset($_GET["usernameSP"]))?$_GET["usernameSP"]: "";	

	$q 			 =(isset($_GET["q"]))?$_GET["q"]: "";	
	$status 	 =(isset($_GET["status"]))?$_GET["status"]: "";
	$actUsername =(isset($_GET["actUsername"]))?$_GET["actUsername"]: "";

	$subNav 	 =(isset($_GET["subNav"]))?$_GET["subNav"]: "";
	
}
function setActive($menu, $section){
  if ($menu == $section) return "active";
  return "";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Network Tree</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">


<style>
/* Center the image and position the close button */
.imgcontainer {
    text-align: center;
    margin: 20px 0 5px 0;
    position: relative;
}

img.avatar {
    width: 15%;
    border-radius: 50%;
}

/* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    padding-top: 2px;
}

/* Modal Content/Box */
.modal-content {
	background-color: #fefefe;
    margin: 2% auto 4% auto; /* 2% from the top, 15% from the bottom and centered */
    border: 1px solid #888;
    width: 35%; /* Could be more or less, depending on screen size */
}


/* The Close Button (x) */
.close {
    position: absolute;
    right: 25px;
    top: 0;
    color: #000;
    font-size: 35px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: red;
}


/* Add Zoom Animation */
.animate {
    -webkit-animation: animatezoom 0.6s;
    animation: animatezoom 0.6s
}

@-webkit-keyframes animatezoom {
    from {-webkit-transform: scale(0)} 
    to {-webkit-transform: scale(1)}
}
    
@keyframes animatezoom {
    from {transform: scale(0)} 
    to {transform: scale(1)}
}

/* Change styles for span and cancel button on extra small screens */
@media screen and (max-width: 500px) {
   .modal-content {
		background-color: #fefefe;
		margin: 4% auto 4% auto; /* 5% from the top, 15% from the bottom and centered */
		border: 1px solid #888;
		width: 80%; /* Could be more or less, depending on screen size */
	}
	
}

@media screen and (max-width: 350px) {
    .cancelbtn {
       width: 100%;
    }
	
	.modal-content {
		background-color: #fefefe;
		margin: 4% auto 4% auto; /* 5% from the top, 15% from the bottom and centered */
		border: 1px solid #888;
		width: 100%; /* Could be more or less, depending on screen size */
	}
	
}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>

	$(document).ready(function(e) {
        
		$('a').on('click', function(){
			$cat = "Genealogy Tree";
			$subCat = "";
			if ($.trim($(this).attr('href')) == "#linkSponsor"){
				$subCat	= "Sponsor";
			}else if ($.trim($(this).attr('href')) == "#linkNetwork"){
				$subCat	= "Network";
			}else if ($.trim($(this).attr('href')) == "#linkCommission"){
				$subCat	= "Commission";
			}else if ($.trim($(this).attr('href')) == "#linkRegister"){
				$subCat	= "Registered";
			}
			
			$cat +=($subCat != "")? " :: " + $subCat : "";
			$("#cat").html($cat);
			
		}); //end $('a').on click
		
		$('img[class="node"]').on('mouseenter', function(){
			$id = $.trim($(this).attr('data-value'));
			$idUpline = $.trim($(this).attr('alt'));
			$idUpline = $idUpline.substr(1);
			$pos	= $idUpline.substr(0,1);
			$sub	= "network";
			var $parent	= $(this).offsetParent();
			var $parentWidth = $parent.outerWidth();
		
			$left	= $(this).position().left + $(this).outerWidth();
			//220 is width of popup box (ref to css .popup-node)
			if (($parentWidth - 250  )  < $left){
				$left = $left - (250 + $(this).outerWidth());
				if ($left < 0) $left = 0;
			}
			$top	= $(this).position().top;
			$.get("getData.php?q=genealogy&id=" + $id + "&sub=" + $sub + "&idUpline=" + $idUpline + "&left=" + $left + "&top=" + $top,
				function($data, success){
					// console.log($data);
					//alert ("okay" + $data);
					$("#popupNode").html($data);
					//alert ($parentWidth);
				});
			
		});
		$('img[class="node"]').on('mouseout', function(){
			$("#popupNode").html("");
		});
		
		$('img[class="node"]').on('click', function(){
			//alert ("double");
			var $alt =$.trim($(this).attr('alt'));
			var $pos = $alt.substr(0,1);
			var $upline = $.trim($alt.substr(1));
			//alert ($alt + "pos: " + $pos + " upline: " + $upline);
			var $parent	= $(this).offsetParent();
			var $parentWidth = $parent.outerWidth();
			var $modalWidth = 200;
			var $left = ($parentWidth - $modalWidth) / 2; //$(this).position().left;
			if ($upline != ""){
				$("#idModal").attr('style','display:block;');
				$("#idModalForm").attr('style', 'left:' + $left + 'px; top:15%');
				$("#actUpline").html($upline);
				$("#actPos").html($pos);
				if ($pos == "L"){
					$("#actPosName").html("Left");	
				}else $("#actPosName").html("Right");	
				
				$("#actUsername").val("");
				$("#actName").html("");
				// $("#actPackage").html("");
				$("#actSponsor").html("");

				$("#actButton").attr("disabled", false);
				$("#actButton").html('Activate');
				$("#actUsername").attr("disabled", false);

				// $("#idModal").load();
				
			}
		}); //node clicked
		
		
		$('img[class="node"]').on('dblclick', function(){
			$searchUsername	= $(this).attr('data-value');
			// alert ($searchUsername);
			location.href = "networkTree.php?subNav=net&searchUsername=" + $searchUsername;
		}); //end dblclick
		
		//Checking username which one to activate
		$("#actUsername").on('blur', function(){
			$id			= $(this).val();
			$actUpline 	= $("#actUpline").html();
			$.get('getData.php?q=getNameFromTempJoin&id='+ $id + "&upline=" + $actUpline , function(data, success){
					$myDataObj = JSON.parse(data);
					//$("#actName").html("<b>" + data + "</b>");
					if ($.trim($myDataObj["name"]) != ""){
						$("#actName").html($myDataObj["name"]);
						// $("#actPackage").html($myDataObj["packageName"]);
						// $("#actPackage").attr('title', $myDataObj["package"]); //PacID
						//$myDataObj["package"] + " - " + 
						//$myDataObj["sponsor"] + " - " +
						$("#actSponsor").html( $myDataObj["sponsorName"] + "(" + $myDataObj["sponsor"] + ")");
					}else{
						$("#actName").html($myDataObj["errDesc"]);
						// $("#actPackage").html("-");
						// $("#actPackage").attr('title', ""); //PacID
						$("#actSponsor").html("-");
						
					}
			});
		});
		
		//Submitting Activate Member
		$("#actButton").on('click', function(){
			if ($("#actUsername").val() != "Username not found"){
				var actUsername = $("#actUsername").val();
				var actUpline 	= $("#actUpline").html();
				var actPos 		= $("#actPos").html();
				var actPackage	= $("#actPackage").val();
				if (typeof(actPackage) == "undefined" || actPackage == ""){
					demo.showSwal('cancelled', 'Chose Package to activate');
					return false;
				}
				var html = $(this).html();
			    $(this).attr("disabled", true);
			    $("#actUsername").attr("disabled", true);
			    //alert (html);
			    $(this).html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
				$.post('activateMember.php',
						{
							"actUsername" 	: actUsername,
							"actUpline" 	: actUpline,
							"actPos"		: actPos,
							"actPackage"	: actPackage
						},
						function (data, status){
							// console.log(data);
							$("#actButton").attr("disabled", false);
							$("#actButton").html('Activate');
							$("#actUsername").attr("disabled", false);
							$dataObj	= JSON.parse(data);
							if ($.trim($dataObj["status"]) == "error"){
								//alert ($dataObj["message"]);
								demo.showSwal('cancelled', $dataObj["message"]);
								$("#actButton").attr("disabled", false);
								$("#actButton").html('Activate');
								$("#actUsername").attr("disabled", false);
							}else{
								//success
								//location.href = "networkTree.php?q=activation&status=success";
								$upline = $.trim($dataObj["message"]);
								location.href = "networkTree.php?q=activation&status=success&subNav=net&searchUsername=" + $upline + "&actUsername=" + actUsername;
								//demo.showSwal('success-message');
								//$("#idModal").attr('style','display:none;');
								//$("#idModal").load();
							}
						});
			}
		});
		
		if ($("#q").attr('title') == "activation" && $("#status").attr('title') == "success"){
			//demo.showNotification("top", "center", "info" , "Activation Member Successfully");
			$actUsername = $("#actUsername").attr('title');
			demo.showSwal('success-message', $actUsername);
		}
    }); //end document ready


</script>
</head>

<body style="width: 98%;">
<span id="q" title="<?php echo $q ?>"></span>
<span id="status" title="<?php echo $status ?>"></span>
    <div class="card">
        <div class="card-header card-header-success card-header-icon">
            <div class="card-icon">
                <i class="fa fa-users fa-2x"></i>
            </div>
	        <div class="card-text"><h4 class="card-title" id="cat">Genealogy Tree</h4></div>
		</div> <!-- end card-header -->
		<div class="card-body card-fix">
        	<div class="row">
                <div class="col-md-2">
                    <!--
                        color-classes: "nav-pills-primary", "nav-pills-info", "nav-pills-success", "nav-pills-warning","nav-pills-danger"
                    -->
                    <ul class="nav nav-pills nav-pills-rose nav-pills-icons flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?php echo setActive($subNav, "net") ?>" data-toggle="tab" href="#linkNetwork" role="tablist">
                                <i class="material-icons">group_work</i>Network Tree
                            </a>
                        </li>
                        <li class="nav-item" >
                            <a class="nav-link <?php echo setActive($subNav, "sp") ?>" data-toggle="tab" href="#linkSponsor" role="tablist">
                                <i class="material-icons">face</i>Direct Sponsor List
                            </a>
                        </li>
                        <li class="nav-item" >
                            <a class="nav-link <?php echo setActive($subNav, "reg") ?>" data-toggle="tab" href="#linkRegister" role="tablist">
                                <i class="fa fa-drivers-license-o"></i>Registered (Not Activated)
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-10">
                    <div class="tab-content">
                        <div class="tab-pane <?php echo setActive($subNav, "net") ?>" id="linkNetwork">
                        	<form action="networkTree.php" method="get">
                        		<input type="hidden" name="subNav" value="net">
                            	<input type="hidden" name="type" value="<?php echo (isset($_GET["type"])?fValidateInput($_GET["type"]): "") ?>" />
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label class="bmd-label-floating">Username</label>
                                         <input id="searchUsername" name="searchUsername" type="text" title="Username" class="form-control" value="<?php echo("$searchUsername"); ?>">
                                    </div>
                                    <div class="form-group col-md-9">
                                        <button type="submit" id="submit_search_Genealogy" name="submit" class="btn btn-fill btn-rose col-md-3">Search</button>
                                     </div>
                                </div>
                            </form>
                            <div class="row">
                                <div class="col-md-12">
                                	<div class="row">
                                    	<div id="popupNode" class="popup-node"></div>
                                    </div>
                                    <div class="row" id="modalActivation" >
                                    	<div class="modal" id="idModal" >
                                            <form class="modal-content animate" id="idModalForm" action="networkTree.php" method="post" onSubmit="return false;">
                                                <input type="hidden" name="type" value="<?php echo (isset($_GET["type"])?fValidateInput($_GET["type"]): "") ?>" />
                                                <input type="hidden" id="actPackage" name="actPackage" value="st">
                                               <div class="imgcontainer" >
                                                    <img src="../images/img_avatar_f.png" alt="Avatar" class="avatar">
                                                </div>
                                                <div class="container">
                                                    <div class="row"><div class="col-md-12">&nbsp;</div></div>
                                                    <div class="row"><div class="col-md-4">Sponsor</div><div class="col-md-8" id="actSponsor"></div></div>
                                                    <div class="row"><div class="col-md-4">Upline</div><div class="col-md-8" id="actUpline"></div></div>
                                                    <div class="row"><div class="col-md-4">Position</div><div class="col-md-8" id="actPosName"></div>
                                                    <span id="actPos" style="display:none"></span></div>
                                                    <div class="row">
                                                        <div class="col-md-4"><label class="bmd-label-floating">Username</label>
                                                        </div>
                                                        <div class="col-md-8"><input id="actUsername" name="actUsername" type="text" title="<?php echo $actUsername ?> " class="form-control" value="" required></div>
                                                    </div>
                                                    <div class="row"><div class="col-md-4">Name</div><div class="col-md-8" id="actName"></div></div>
                                                    <!-- <div class="row">
                                                    	<div class="col-md-4">Package</div>
                                                    	<div class="col-md-8">
	                                                    	<select class="selectpicker" data-size="4" data-style="btn btn-primary" name="actPackage" id="actPackage" title="Package">
			                                                    <option disabled selected value="">Select Package</option>
				                                                <?php
				                                                    $sql  = "SELECT * FROM msPackage";
				                                                    $sql .= " ORDER BY pacPrice ASC";
				                                                    $query = $conn->query($sql);
				                                                    while ($row = $query->fetch_assoc()){
				                                                        echo ("<option value='".$row["pacID"]."'>".$row["pacName"]."</option>");
				                                                    }
				                                                ?>
			                                                </select>
	                                                    </div>
                                                    </div> -->
                                                    <div class="row"><div class="col-md-4">&nbsp;</div><div class="col-md-8">&nbsp;</div></div>
                                                    <div class="row" style="height:80px"><div class="col-md-12"><button id="actButton" type="button" class="btn btn-fill btn-rose col-md-12"  >Activate</button></div></div>
                                                    <div class="row"><div class="col-md-12">&nbsp;</div></div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <?php 
									$username = $_SESSION["sUserName"];
									if ($searchUsername != "") 
										$username	= $searchUsername;
									
									//Me (mid node)
									$arrDataMe	= fGetDataMember($conn, $username, $_SESSION["sUserName"]);
									//var_dump ($arrDataMe);
									
									if (gettype($arrDataMe)=="array") { // != ""){
										//Level 1
										$upline		= $arrDataMe["username"]; //$username;
										$arrDataCL  = fGetDataMemberByUpline_Pos($conn, $upline, "l");
										$arrDataCR  = fGetDataMemberByUpline_Pos($conn, $upline, "r");
										//echo $arrDataCL["username"] . " / " . $arrDataCL["name"] . " / " . $arrDataCL["upline"] . " / " . $arrDataCL["pos"];
										
										//Level 2
										if (gettype($arrDataCL)=="array"){
											$upline		  = $arrDataCL["username"];
											$arrDataGCLL  = fGetDataMemberByUpline_Pos($conn, $upline, "l");
											$arrDataGCLR  = fGetDataMemberByUpline_Pos($conn, $upline, "r");
										}
										if (gettype($arrDataCR) == "array"){
											$upline		  = $arrDataCR["username"];
											$arrDataGCRL  = fGetDataMemberByUpline_Pos($conn, $upline, "l");
											$arrDataGCRR  = fGetDataMemberByUpline_Pos($conn, $upline, "r");
										}
									}else{
										echo"<b>$searchUsername</b> tidak ditemukan dalam Genealogy Tree Anda"; die();
									}
									?>
                                    <table border="0" cellpadding="0" cellspacing="0" class="tree">
                                      <tr >
                                        <td class="tree"></td>
                                        <td class="tree"></td>
                                        <td class="tree"></td>
                                        <td class="tree"><div id="me">
                                        <?php 
										$imgID 	= "imgMe";
										if (gettype($arrDataMe) == "array"){ 
											$imgSrc = getSrcIconPackage ($arrDataMe["package"]); //$imgSrcUser;
											$node 	= $username;
											$name	= $arrDataMe["name"];
										}else{
											$imgSrc = $imgSrcLock;
											$node 	= "";
											$name	= "";
										}
										echo ("<img src='" . $imgSrc . "' class='node' id='". $imgID ."' data-value='". $node . "' alt='' /><br>" . fTruncateSentence($name, 15) );
										?>
                                        </div></td>
                                        <td class="tree"></td>
                                        <td class="tree"></td>
                                        <td class="tree"></td>
                                      </tr>
                                      <tr >
                                        <td class="tree"></td>
                                        <td class="tree"><img src="../images/lnNdLeft.png" class="tree"  alt=""/></td>
                                        <td class="tree"><img src="../images/lnNdConnector.png" class="tree"  alt=""/></td>
                                        <td class="tree"><img src="../images/lnNdMid.png" class="tree"  alt=""/></td>
                                        <td class="tree"><img src="../images/lnNdConnector.png" class="tree"  alt=""/></td>
                                        <td class="tree"><img src="../images/lnNdRight.png" class="tree"  alt=""/></td>
                                        <td class="tree"></td>
                                      </tr>
                                      <tr >
                                        <td class="tree"></td>
                                        <td class="tree"><div id="cl">
										<?php 
										$imgID 	= "imgCL"; 
										$name = $childUsername = $upline = "";
										if (gettype($arrDataCL) == 'array'){ 
											$imgSrc = getSrcIconPackage ($arrDataCL["package"]); //$imgSrcUser;
											$childUsername = $arrDataCL["username"];
											$name		= $arrDataCL["name"];
										}else if (gettype($arrDataMe) == 'array'){
											$imgSrc 	= $imgSrcPlus;
											$upline 	= $username;
										}else{
											$imgSrc 	= $imgSrcLock;
										}
										echo ("<img src='" . $imgSrc . "' class='node' id='". $imgID ."' data-value='". $childUsername . "' alt='L" . $upline ."' /><br>" . fTruncateSentence($name, 15) );
										?></div>
                                        </td>
                                        <td class="tree"></td>
                                        <td class="tree"></td>
                                        <td class="tree"></td>
                                        <td class="tree"><div id="cr"><?php 
										$imgID 	= "imgCR"; $name	= $childUsername = $upline = "";
										if (gettype ($arrDataCR) == 'array'){ 
											$imgSrc = getSrcIconPackage ($arrDataCR["package"]); //$imgSrcUser;
											$childUsername = $arrDataCR["username"];
											$name	= $arrDataCR["name"];
										}else if (gettype($arrDataMe) == 'array'){
											$imgSrc 	= $imgSrcPlus;
											$upline 	= $username;
										}else{
											$imgSrc 	= $imgSrcLock;
										}
										echo ("<img src='" . $imgSrc . "' class='node' id='". $imgID ."' data-value='". $childUsername . "' alt='R" . $upline ."' /><br>" . fTruncateSentence($name, 15) );
										?></div></td>
                                        <td class="tree"></td>
                                      </tr>
                                      <tr >
                                        <td class="tree"><img src="../images/lnNdLeft.png" class="tree"  alt=""/></td>
                                        <td class="tree"><img src="../images/lnNdMid.png" class="tree"  alt=""/></td>
                                        <td class="tree"><img src="../images/lnNdRight.png" class="tree"  alt=""/></td>
                                        <td class="tree"></td>
                                        <td class="tree"><img src="../images/lnNdLeft.png" class="tree"  alt=""/></td>
                                        <td class="tree"><img src="../images/lnNdMid.png" class="tree"  alt=""/></td>
                                        <td class="tree"><img src="../images/lnNdRight.png" class="tree"  alt=""/></td>
                                      </tr>
                                      <tr >
                                        <td class="tree"><div id="gcll"><?php 
										$imgID 	= "imgGCLL"; $name	= $childUsername = $upline = "";
										if ($arrDataCL != "" && $arrDataGCLL != ""){ 
											$imgSrc = getSrcIconPackage ($arrDataGCLL["package"]); //$imgSrcUser;
											$childUsername = $arrDataGCLL["username"];
											$name	= $arrDataGCLL["name"];
										}else if ($arrDataCL != "" && $arrDataGCLL == ""){ 
											$imgSrc 	= $imgSrcPlus;
											$upline 	= $arrDataCL["username"];
										}else{
											$imgSrc 	= $imgSrcLock;
										}
										echo ("<img src='" . $imgSrc . "' class='node' id='". $imgID ."' data-value='". $childUsername . "' alt='L" . $upline ."' /><br>" . fTruncateSentence($name, 15) );
										?></div></td>
                                        <td class="tree"></td>
                                        <td class="tree"><div id="gclr"><?php 
										$imgID 	= "imgGCLR"; $name	= $childUsername = $upline = "";
										if ($arrDataCL != "" && $arrDataGCLR != ""){ 
											$imgSrc = getSrcIconPackage ($arrDataGCLR["package"]); //$imgSrcUser;
											$childUsername = $arrDataGCLR["username"];
											$name	= $arrDataGCLR["name"];
										}else if ($arrDataCL != "" && $arrDataGCLR == ""){ 
											$imgSrc 	= $imgSrcPlus;
											$upline 	= $arrDataCL["username"];
										}else{
											$imgSrc 	= $imgSrcLock;
										}
										echo ("<img src='" . $imgSrc . "' class='node' id='". $imgID ."' data-value='". $childUsername . "' alt='R" . $upline ."' /><br>" . fTruncateSentence($name, 15) );
										?></div></td>
                                        <td class="tree"></td>
                                        <td class="tree"><div id="gcrl"><?php 
										$imgID 	= "imgGCRL"; $name	= $childUsername = $upline = "";
										if ($arrDataCR != "" && $arrDataGCRL != ""){ 
											$imgSrc = getSrcIconPackage ($arrDataGCRL["package"]); //$imgSrcUser;
											$childUsername = $arrDataGCRL["username"];
											$name	= $arrDataGCRL["name"];
										}else if ($arrDataCR != "" && $arrDataGCRL == ""){ 
											$imgSrc 	= $imgSrcPlus;
											$upline 	= $arrDataCR["username"];
										}else{
											$imgSrc 	= $imgSrcLock;
										}
										echo ("<img src='" . $imgSrc . "' class='node' id='". $imgID ."' data-value='". $childUsername . "' alt='L" . $upline ."' /><br>" . fTruncateSentence($name, 15) );
										?></div></td>
                                        <td class="tree"></td>
                                        <td class="tree"><div id="gcrr"><?php 
										$imgID 	= "imgGCRR"; $name	= $childUsername = $upline = "";
										if ($arrDataCR != "" && $arrDataGCRR != ""){ 
											$imgSrc = getSrcIconPackage ($arrDataGCRR["package"]); //$imgSrcUser;
											$childUsername = $arrDataGCRR["username"];
											$name	= $arrDataGCRR["name"];
										}else if ($arrDataCR != "" && $arrDataGCRR == ""){ 
											$imgSrc 	= $imgSrcPlus;
											$upline 	= $arrDataCR["username"];
										}else{
											$imgSrc 	= $imgSrcLock;
										}
										echo ("<img src='" . $imgSrc . "' class='node' id='". $imgID ."' data-value='". $childUsername . "' alt='R" . $upline ."' /><br>" . fTruncateSentence($name, 15) );
										?></div></td>
                        
                                      </tr>
                                   </table>
                                </div>
                            </div>
                        </div> <!-- end link network -->
                        <div class="tab-pane  <?php echo setActive($subNav, "sp") ?>" id="linkSponsor">
                            <form action="networkTree.php" method="get">
                            	<input type="hidden" name="subNav" value="sp">
                                <input type="hidden" name="type" value="<?php echo (isset($_GET["type"])?fValidateInput($_GET["type"]): "") ?>" />
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label class="bmd-label-floating">Search by Sponsor</label>
                                         <input id="usernameSP" name="usernameSP" type="text" title="Sponsor's Username" class="form-control" value="<?php echo $usernameSP ?>" required>
                                    </div>
                                    <div class="form-group col-md-9">
                                        <button type="submit" name="submit" class="btn btn-fill btn-rose col-md-3">Search</button>
                                    </div>
                                    <div class="col-md-12">
                                    	<?php
                                    	if ($usernameSP == "") $sponsor = $_SESSION['sUserName'];
                                    	else $sponsor = $usernameSP;

                                    	if (!fCheckSponsorGenealogyTree($sponsor, $_SESSION['sUserName'] , $conn)){
                                    		//Not Found
                                    		$sponsor = "<b class='text-warning'>Sponsor not found</b>";
                                    	}
                                    	?>
	                                    <table>
				                        	<tr>
				                                <td><i class="fa fa-user fa-2x"></i></td>
				                                <td><?php echo $sponsor ?></td>
				                            </tr>
				                            <tr>
				                                <td></td>
				                                <td><?php
				                                    //$sponsor = $_SESSION['sUserName'];
				                                    $gen = 1;
				                                    fDirectDownline($conn, $sponsor, $gen);
				                                ?>
				                                </td>
				                            </tr>
				                        </table>
			                    	</div>
                                </div>
                            </form>
                        </div> <!-- end link Sponsor -->

                        <!-- linkRegister -->
                        <div class="tab-pane  <?php echo setActive($subNav, "reg") ?>" id="linkRegister">
                            <div class="material-datatables col-md-12">
                                <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
									<thead>
										<tr>
											<th width="80">Date</th>
											<th>Username</th>
											<th width="120">Name</th>
											<th>Mobile/Email</th>
											<th>Country</th>
											<!-- <th>Package</th> -->
											<th>Status</th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th>Date</th>
											<th>Username</th>
											<th>Name</th>
											<th>Mobile/Email</th>
											<th>Country</th>
											<!-- <th>Package</th> -->
											<th>Status</th>
										</tr>
									</tfoot>
                                  	<tbody>
                                  	<?php
                                  	$sql = "SELECT tjDate, tjUsername, tjFirstName, pacName, countryDesc, tjMobileCode, tjMobile, tjEmail, stDesc FROM ";
                                  	$sql .= " dtTempJoin INNER JOIN msPackage ON tjPackage = pacID ";
                                  	$sql .= " INNER JOIN msCountry ON countryID = tjCountry";
                                  	$sql .= " INNER JOIN msStatus ON stID = tjStID";
                                  	$sql .= " WHERE tjSponsor='" . $_SESSION['sUserName'] . "'";
                                    $sql .= " ORDER BY tjDate DESC ";
                                    //echo $sql;
                                    if ($query = $conn->query($sql)){
                                      	if ($query->num_rows==0){
                                        	echo "<tr><td colspan='6' style='text-align:center'>no records</td></tr>";
                                      	}
                                        while ($row = $query->fetch_assoc()){
                                  	?>
										<tr>
											<td width="100"><?php echo $row["tjDate"] ?></td>
											<td width="100"><?php echo $row["tjUsername"] ?></td>
											<td width="100"><?php echo $row["tjFirstName"] ?></td>
											<td width="100"><?php echo ("+".$row["tjMobileCode"].$row["tjMobile"]."<br>".$row["tjEmail"]) ?></td>
											<td width="100"><?php echo $row["countryDesc"] ?></td>
											<!-- <td width="100"><?php echo $row["pacName"] ?></td> -->
											<td width="50"><?php echo $row["stDesc"] ?></td>
										</tr>
                                      <?php 
                                        }
                                  	} 
                                  	?>
                                  	</tbody>
                                </table>
                            </div>
                        </div> <!-- end link Registered -->
                    </div> <!-- tab content -->
                </div> <!-- col md 10 -->
			</div>
        </div> <!-- end card-body -->
	</div> <!-- end Card -->
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

<?php fCloseConnection($conn); ?>
</html>

<script>

// Get the modal
var modal = document.getElementById('idModal');
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target.id == "idModal")
	document.getElementById('idModal').style.display='none';
	//alert (event.target.id);
}



</script>