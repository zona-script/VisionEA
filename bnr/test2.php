<?php
include_once("./includes/inc_def.php"); //before inc_session
//include_once("./includes/inc_session.php"); //after inc_session
include_once("./includes/inc_conn.php");
include_once("./includes/inc_functions.php");

echo ('test 2');
die();

$conn->autocommit(false);

$sqlpair = " SELECT pairDate FROM dtdailypairing GROUP BY pairDate";
//echo ($sqlpair);
//$queryPair = $conn->query($sqlpair);
//while ($rowpair = $queryPair->fetch_assoc()){
    //$postingTimeStamp = $postingDate . " " . date("h:i:s"); // " 23:00:00";
   
    
    //$sql = "SELECT mbrUsername, pairDate FROM dtMember ";
    //$sql .= " INNER JOIN (SELECT pairDate, pairUsername FROM dtdailypairing GROUP BY pairDate ASC) pair ON pairUsername=mbrUsername";
    //$sql .= " ORDER BY pair.pairDate ASC, mbrDate ASC";

    $sql = "SELECT mbrUsername, pairDate, pairUsername, sum(pairTO) pairTO FROM dtMember LEFT JOIN dtDailyPairing ON mbrUsername = pairUsername ";
	$sql .= " WHERE pairTO > 0";
	$sql .= " GROUP BY mbrUsername, pairDate, pairUsername, pairTO";

    //echo $sql; die();
    $query = $conn->query($sql);
    while ($row = $query->fetch_assoc()) {
    	$postingDate 	  = $row['pairDate'];
    	$postingTimeStamp = $postingDate . " 19:05:02";

        $mtchUsername = $row['mbrUsername'];
        $sqlGen = "";

        $sqlGen = " INSERT INTO dtMatching (mtchUsername, mtchDate, mtchAmount) ";
        //$sqlGen (.)= "SELECT pairUsername, pairTO FROM dtDailyPairing "; //wih pairUsername dan nut SUM(pairTO) will give detail till username who give bonus matching

        $sqlGen .= "SELECT '" . $mtchUsername ."', '". $postingTimeStamp . "', (SUM(pairTO) * 0.1) AS bnsMatching FROM dtDailyPairing "; //group all username and * 10% as Matching Bonus
        $sqlGen .= " WHERE ";
        $sqlGen .= "     pairUsername IN ";
        $sqlGen .= "     (  SELECT genMbrUsername FROM dtGenSponsorship WHERE genSPUsername='". $mtchUsername . "' ) ";
        $sqlGen .= "     AND DATE(pairDate) = '".$postingDate."' ";
        $sqlGen .= "     AND pairTo > 0 ";
        //echo $sqlGen . "<br>";
        if ($queryGen = $conn->query($sqlGen)){
            //success
            //$status = "FASE 4.1.inside SUCCESS";
            //echo $status . '<br>';
        }else{
            $errMsg = "ERROR: FASE 4.1 - " . $conn->error;
            fPrintErr($errMsg);
            $conn->rollback();
            die();
        }
        
    }
    

//}

echo ("sukses");
//$conn->rollback();
$conn->commit();
die();


echo "jgn sembarang, ini akan add ke matching";
die();

//$actUsername = "vina";
$conn->autocommit(false);

$sql = "SELECT mbrUsername, mbrSponsor, t.trPacID, pacMatchingGen FROM dtMember ";
$sql .= " INNER JOIN (";
$sql .= " SELECT t.* FROM Transaction t ";
$sql .= " WHERE t.trID = (SELECT trID FROM Transaction where trUsername = t.trUsername ORDER BY trDate DESC LIMIT 1)";
$sql .= " ) t ON mbrUsername = t.trUsername";
$sql .= " INNER JOIN msPackage ON pacID=t.trPacID";
$sql .= " WHERE mbrUsername <> mbrSponsor";
$sql .= " ORDER BY mbrDate ASC";
//echo $sql; die();
$queryMbr = $conn->query($sql);
while ($rowMbr=$queryMbr->fetch_assoc()){
	$actUsername = $rowMbr['mbrUsername'];

		$username   = $actUsername;
		$sqlInsert = "INSERT INTO dtGenSponsorship (genMbrUsername, genSPUsername, genLevel) VALUES (?,?,?)";
		$queryInsert = $conn->prepare($sqlInsert);
		$queryInsert->bind_param("sss", $genMbrUsername, $genSPUsername, $genLevel);

		//looking for number of level/generation of Matching of sponsor.
		//get Level of generation
        //$myDataObj  = json_decode(fGetDataPackage($conn, $actUsername));
        //$numOfMatchingGen   = $myDataObj->{"pacMatchingGen"};
		//for ($i = 0; ($i <7 && $i<$numOfMatchingGen); $i++){
        for ($i = 0; $i <7; $i++){
		    //$sql = "SELECT mbrUsername, mbrSponsor FROM dtMember WHERE mbrUsername='".$username."' AND mbrUsername <> mbrSponsor";
		    $sql = "SELECT mbrUsername, mbrSponsor FROM dtMember ";
		    $sql .= " WHERE mbrUsername='".$username."' AND mbrUsername <> mbrSponsor";
			//echo ($sql . "<br>");
		    if ($query = $conn->query($sql)){
		    	if ($row=$query->fetch_assoc()){
		        	$genMbrUsername   = $actUsername;
		            $genSPUsername    = $row['mbrSponsor'];
		            $genLevel         = $i+1;
		            $myDataObj  = json_decode(fGetDataPackage($conn, $genSPUsername));
        			$matchingGen   = $myDataObj->{"pacMatchingGen"};
		            if ($matchingGen >= $genLevel){
			            if ($queryInsert->execute()){
			            	//success
			            	$username = $genSPUsername; //move sponsor to next username to get higher level of sponsorship
			            }else{
			            	echo (fSendStatusMessage("error", "<b>Create Sponsorship's Generation Failed - </b>" . $conn->error) . " " . $genMbrUsername . " " . $genSPUsername);
							$conn->rollback();
							die();
			            }
			        }else{
			        	//package not qualified for matching bonus
			        	//but keep to move to next sponsor
			        	$username = $genSPUsername; //move sponsor to next username to get higher level of sponsorship
			        }
		        }else{
		            break;
		        }
		    }else{
		        break;
		    }
		}
	echo (">> " . $actUsername . " done<br>");
}

echo ("sukses");
$conn->rollback();
//$conn->commit();

?>