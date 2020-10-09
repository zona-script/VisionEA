<?php
require("../includes/inc_def.php");
//require('../includes/inc_session.php'); //do not turn on inc_session.php, because regLink.php also use getData.php and did not login (no session)
session_start();
require('../includes/inc_conn.php');
require('../includes/inc_functions.php');
require('../includes/inc_commission.php');

$q = $id = $sub = '';
if (isset($_GET["q"])){
	$q  = fValidateInput($_GET["q"]);
	$id = (isset($_GET["id"]))?fValidateSQLFromInput($conn, $_GET["id"]): "";
	$sUsernameSP = isset($_SESSION['sUserName'])?$_SESSION['sUserName']: '';
	if (strcmp($q , 'sponsor') == 0){
		//query cek name by id
		//$id = mysql_real_escape_string($id);
		if ($sUsernameSP == ""){
			$sUsernameSP = $id;
		}
		if (fCheckGenealogyTree($id, $sUsernameSP, $conn) == true){
			$sql = "SELECT mbrFirstName, mbrLastName, mbrStID from dtMember WHERE mbrUsername='$id'";
			$query = $conn->query($sql);
			if ($query->num_rows > 0){
				$row = $query->fetch_assoc();
				if ($row['mbrStID'] != $DEF_STATUS_ACTIVE){
					echo "this sponsor not active";
				}else{
					echo ($row['mbrFirstName'] . " " . $row['mbrLastName']);
				}	
			}else echo ("wrong sponsor's username"); //NB: change will change comparison value in register.php for #sponsorName
		}else{
			echo "not under your genealogy tree"; //NB: change will change comparison value in register.php for #sponsorName
		}
	} elseif (strcmp($q , 'mobilecountrycode') == 0){
		$sql = "SELECT countryMobileCode from msCountry WHERE countryID='" . $id . "'";
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			$row = $query->fetch_assoc();
			echo $row["countryMobileCode"];	
		}
	} elseif (strcmp($q , 'username') == 0){
		$sql = "SELECT dtMember.mbrUsername FROM dtMember WHERE mbrUsername='" . $id . "'";
		$sql .= " UNION SELECT dtTempJoin.tjUsername FROM dtTempJoin WHERE tjUsername = '". $id . "'";
		$sql .= " UNION SELECT trProduct.trProUserBeli FROM trProduct WHERE trProUserBeli = '".$id."'";
		//echo $sql; die();
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			echo "exist";
		}else{
			echo "not_found";
		}
	}elseif (strcmp($q , 'mbrUsername') == 0){
		$sql = "SELECT mbrUsername, mbrFirstName FROM dtMember WHERE mbrUsername='" . $id . "' AND mbrStID='" . $DEF_STATUS_ACTIVE . "'";
		//echo $sql; die();
		$query = $conn->query($sql);
		//--- do update on 14 juli 2018 ---
		//if ($query->num_rows > 0){
		//	echo "exist";
		if ($row = $query->fetch_assoc()){
			echo ($row["mbrFirstName"]);
		}else{
			echo "not_found";
		}
	}elseif (strcmp($q , 'genealogy') == 0){
		//pairing
		$sumLeft = $sumRight = $sumTO = $sumFO = 0;
		$myDataObj  = json_decode(fCommissionPairing($id, $conn));
		if ($myDataObj->{"status"} == "success"){
		    $sumLeft    = $myDataObj->{'sumLeft'};
		    $sumRight   = $myDataObj->{'sumRight'};
		    //$sumTO      = $myDataObj->{'sumTO'};
		    //$sumFO      = $myDataObj->{'sumFO'};
		}

		$idUpline = (isset($_GET["id"]))?fValidateSQLFromInput($conn, $_GET["idUpline"]): "";
		$sub = (isset($_GET["sub"]))?fValidateSQLFromInput($conn, $_GET["sub"]): "";
		$left = (isset($_GET["left"]))?fValidateSQLFromInput($conn, $_GET["left"]): "";
		$top = (isset($_GET["top"]))?fValidateSQLFromInput($conn, $_GET["top"]): "";
		if (strcmp($sub , 'network') == 0){
			$css = "alert alert-success popup-node";
			if ($id == "" && $idUpline == "") $css = "alert alert-warning popup-node";
			else if ($id == "") $css = "alert alert-info popup-node";
            $result = '                         <div class="'. $css . '" style="left:' . $left . 'px; top:' . $top . 'px;">';
			$result .= '						    <div class="row">';
            $result .= '                               <div class="col-md-12">';
			if ($id != "") {
				$myDataObj  = json_decode(fGetDataSponsor($conn, $id));
							$result .= 'Username : ' . $id . '</div>';
							$result .= '<div class="col-md-12"><span class="small">Sponsor : ' . fTruncateSentence($myDataObj->{"name"}, 15) . '(' . $myDataObj->{"username"} . ')</span></div>' ;
				$myDataObj  = json_decode(fGetDataPackage($conn, $id));
							$result .= '<div class="col-md-12">';
							// $result .= '<span class="small">Package : ' . $myDataObj->{"pacName"} . '</span>' ;
						}
			if ($idUpline != "") $result .= '                                   Upline : ' . $idUpline . "<br><span class='small'>Click to add new member</span>";
            $result .= '                                </div>';
            $result .= '                            </div>';
			if ($id != ""){
				$result .= '                            <div class="row">';
				$result .= '                                <div class="col-md-6">';
				$result .= '                                    Left : ' . numFormat($sumLeft,0);
				$result .= '                                </div>';
				$result .= '                                <div class="col-md-6">';
				$result .= '                                    Right : ' . numFormat($sumRight,0);
				$result .= '                                </div>';
				$result .= '                                <div class="col-md-12 small">Double Click to move-up the node</div>';
				$result .= '                             </div>';

			}else if ($idUpline == ""){
				$result .= '<b>Locked</b><br><span class="small">upper level not yet filled</span>';
			}
			$result .= '                          </div>';
			echo $result;						
		}
	}else if (strcmp($q , 'getNameFromTempJoin') == 0){ //networkTree.php
		$upline	= (isset($_GET["upline"]))?fValidateSQLFromInput($conn, $_GET["upline"]): "";
		
		$sql = "SELECT tjUsername, tjFirstName, tjPackage, pacName, tjSponsor, mbrFirstName, tjStID FROM ((dtTempJoin ";
		$sql .= " INNER JOIN msPackage ON tjPackage=pacID) ";
		$sql .= " INNER JOIN dtMember ON mbrUsername = tjSponsor) ";
		$sql .= " WHERE tjUsername='" . $id . "'";
		// $sql .= " AND tjStID='" . $DEF_STATUS_ACTIVE . "'";
		//echo $sql;
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			$row = $query->fetch_assoc();
			//Checking Status
			if ($row['tjStID'] == $DEF_STATUS_PENDING){
				$arrdata = array(
						"name" => "",
						"errDesc" => "<b style='color:red'>Email address has not been verified</b>"
						);
			}else if ($row['tjStID'] == $DEF_STATUS_ACTIVE){
				//checking Genealogy Tree, new member must below sponsor tree
				if (fCheckGenealogyTree($upline, $row["tjSponsor"], $conn)){
					$arrdata = array(
								"name" => $row["tjFirstName"],
								"package" => $row["tjPackage"],
								"packageName" => $row["pacName"],
								"sponsor" => $row["tjSponsor"],
								"sponsorName" => $row["mbrFirstName"],
								"errDesc" => ""
								);
				}else{
					$arrdata = array(
								"name" => "",
								"errDesc" => "<b style='color:red'>Wrong Genealogy Tree</b>"
								);
				}
			}
			$dataJSON = json_encode($arrdata);
			echo ($dataJSON);
		}else {
			$arrdata = array(
						"name" => "",
						"package" => "",
						"errDesc" => "<span class='text-danger'>Username not found</span>" //Username not found will change networkTree.php function
						);
				$dataJSON = json_encode($arrdata);
			echo ($dataJSON);
		}
	}else if (strcmp($q , 'getDirectDownline') == 0){ //Direct Downline used in dsl.php
		$sponsor	= (isset($_GET["sponsor"]))?fValidateSQLFromInput($conn, $_GET["sponsor"]): "";
		$gen		= (isset($_GET["gen"]))?fValidateSQLFromInput($conn, $_GET["gen"]): "";
		$gen++;
		echo (fDirectDownline($conn, $sponsor, $gen)); //call in inc_function
	}
	
	
} //end if (isset($_GET["q"])){ ==================================================================



if (isset($_POST["q"])){
	$q  = fValidateInput($_POST["q"]);
	$id = (isset($_POST["id"]))?fValidateSQLFromInput($conn, $_POST["id"]): "";
	
	
	/*
	//just a template ==================
	if (strcmp($q , 'checkAndSaveDataDeposit') == 0){
		$finID				= (isset($_POST["finID"]))				?fValidateSQLFromInput($conn, $_POST["finID"]): "";
		
		
		
		$sql = "";
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			if ($row = $query->fetch_assoc()){
				if ($row["pinWord"] != md5($securityPassword)){
					$arrdata = array(
							"errDesc" => "<span class='text-danger'>Security Password not match</span>"
							);
					$dataJSON = json_encode($arrdata);
					echo ($dataJSON);
					die();
				}
			}
		}
	} 
	*/
	
	
	
} //END if (isset($_POST["q"])){  ===================================================================
	
	
//at the end, close the connection
fCloseConnection($conn);
?>