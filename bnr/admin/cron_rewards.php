<?php
session_start();
include_once("../includes/inc_def.php");
//include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

//posting previous day (yesterday), posting in the morning (check maintenance schedule)

$postingDate    = date("Y-m-d", strtotime("yesterday")); //"2018-04-09"; //
//$postingDate    = "2019-06-02"; //

$act        =  (isset($_GET["act"]))?fValidateInput($_GET["act"]): "";
$cat        =  (isset($_GET["cat"]))?fValidateInput($_GET["cat"]): "";
$code       =  (isset($_GET["code"]))?fValidateInput($_GET["code"]): "";
$isAuth     = false;
$sql = "SELECT * FROM trPasswordBO WHERE passUsernameBO='". $USER_POSTING ."' ORDER BY DATE(passDateBO) DESC LIMIT 1";

$query = $conn->query($sql);
if ($row = $query->fetch_assoc()){
    if ($row['passWordBO'] == md5($code) && $row['passUsernameBO'] == $USER_POSTING){
        $isAuth = true;
    }else{ 
        $isAuth = false;
    }
}


if ($act == "update_rewards" && $cat == "rewards" && $isAuth==true){

    if (fIsMaintenance()){

        global $DEF_STATUS_NEW; //$DEF_STATUS_ACTIVE, 

        //START POSTING________________________________
        $conn->autocommit(false);

        //Bronze Level
        $sql = " INSERT INTO dtArchiever (achUsername, achRwdID, achDate, achStatus, achLeft, achRight, achDirect, achL1, achL2, achUpdateDate) ";
        	$sql .= "SELECT pairTORUsername, '1', CURRENT_DATE, '', NetLeft, NetRight, directTO, '0', '0', CURRENT_TIME FROM(";
        		$sql .= " SELECT pairTORUsername, mbrFirstName, mbrEmail, tLeft AS NetLeft, tRight AS NetRight";
        		$sql .= " , IF(tLeft > tRight, tRight, tLeft) AS BalanceTO, IFNULL(directTO, 0) as directTO ";
        		$sql .= " FROM (";
        		$sql .= "  SELECT pairTORUsername, mbrFirstName, mbrEmail";
        		$sql .= ", SUM(pairTORLeft) as tLeft, SUM(pairTORRight) as tRight, directTO FROM dtDailyTORewards ";
        		//Direct Sponsor
        		$sql .= " LEFT JOIN (SELECT SUM(pacPrice) AS directTO, m.mbrSponsor FROM dtMember m";
        		$sql .= "       INNER JOIN (SELECT trID, trUsername, trPacID FROM Transaction as t WHERE trID = (SELECT trID FROM Transaction WHERE trUsername=t.trUsername ";
                $sql .= "           AND (trStatus='" . $DEF_STATUS_NEW . "' OR (trStatus='" . $DEF_STATUS_UPGRADE . "' AND trThn='1') )";
                $sql .= "           ORDER BY trDate DESC LIMIT 1) ) as t ON m.mbrUsername=trUsername ";

        		$sql .= "       INNER JOIN dtMember sp ON sp.mbrUsername=m.mbrSponsor";
        		$sql .= "       INNER JOIN msPackage ON pacID=trPacID ";
        		$sql .= "       WHERE ( DATE(m.mbrDate) >= '2019.11.01' AND YEAR(m.mbrDate) <= '2020' AND (YEAR(CURRENT_DATE) = 2019 OR YEAR(CURRENT_DATE) = 2020) ) "; //masih menghitung omset 1nov 2019 dan 2020 dlm 1 thn omset yang sama
                $sql .= "             OR ( DATE(m.mbrDate) >= '2019.11.01' AND YEAR(m.mbrDate) = YEAR(CURRENT_DATE) ) "; //Mulai thn 2021, omset dihitung per tahun (tahun sblmnya tidak dihitung dlm omset direct)
                
        		$sql .= "       GROUP BY sp.mbrUsername ";
        		$sql .= "       ) AS t ON mbrSponsor=pairTORUsername";
        		$sql .= "       INNER JOIN dtMember sp ON sp.mbrUsername=pairTORUsername";
        		$sql  .= " WHERE Date(pairTORDate) >= '2019.11.01'";
        		$sql .= " GROUP BY pairTORUsername";
        		$sql .= " ) AS a ";
        		$sql .= " WHERE pairTORUsername !='visionea'";
        	$sql .= " ) AS omset";
        	$sql .= " WHERE BalanceTO >= (select rwdLeft FROM msReward WHERE rwdID =1)";
        	$sql .= " AND directTO >= (select rwdDirect FROM msReward WHERE rwdID =1)";
        	$sql .= " AND pairTORUsername NOT IN (SELECT achUsername FROM dtArchiever)";

        if ($conn->query($sql)){
            //$status   = "success";
            $status = "Update BRONZE successfully";
            echo $status . '<br>';
            $conn->commit();
        }else{
            $errMsg = "ERROR: CHECKING BRONZE - " . $conn->error;
            fPrintErr($errMsg);
            $conn->rollback();
        }

        //END POSTING__________________________________

    }else{
        //not in maintenance schedule
        fSendToAdmin("CRON-ERROR", "postTORewards.php", "Cron runs not in maintenance schedule");
    }

}else{
    //not authorized
    fSendToAdmin("CRON-ERROR", "postTORewards.php", "Not authorized - " . "$act == 'posting' && $cat == 'pair'" . "SQL: " . $sql);
}


fCloseConnection($conn);
?>