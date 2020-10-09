<?php
include_once("../includes/inc_def.php");
//include_once("../includes/inc_session.php"); //DO NOT ACTIVATE, BECAUSE USED BY getData.php and do not turn on session at this part
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");


//Spectacular Bonus _____________________________________________
/*
Direct Sponsor VIP
3vip : 2jt
6vip : 5jt
9vip : 10jt
12vip : 12jt
15vip : 15jt
18vip : 20jt
Periode kontes : 1Nov - 31 des 2019
*/
function fGetSpectacularBonus($username, $conn){
    if ($username != ""){
        global $DEF_STATUS_ACTIVE;
        $PERIODE_START  = '2019.11.01';
        $PERIODE_END    = '2019.12.31';
        
        $sql = "SELECT count(*) AS jlhVIP FROM dtMember INNER JOIN Transaction ON mbrUsername = trUsername ";
        $sql .= " WHERE mbrSponsor = '$username'";
        $sql .= " AND trPacID = 'VIP' and mbrStID = '$DEF_STATUS_ACTIVE'";
        $sql .= " AND DATE(mbrDate) BETWEEN '$PERIODE_START' AND '$PERIODE_END'";
        $sql .= " AND DATE(trDate) BETWEEN '$PERIODE_START' AND '$PERIODE_END'";

        $query = $conn->query($sql);
        $jlhVIP = $bonusCash = 0;
        if ($row = $query->fetch_assoc()){
            $jlhVIP = $row['jlhVIP'];
            if ($jlhVIP >= 18){
                $bonusCash = 20000000; //20juta
            }else if ($jlhVIP >= 15){
                $bonusCash = 15000000; //15juta
            }else if ($jlhVIP >= 12){
                $bonusCash = 12000000;    //12juta
            }else if ($jlhVIP >= 9){
                $bonusCash = 10000000; //10juta
            }else if ($jlhVIP >= 6){
                $bonusCash = 5000000; //5juta
            }else if ($jlhVIP >= 3){
                $bonusCash = 2000000; //2juta
            }
        }
        unset($query);
        unset($row);

        $arrData = array("status"   =>"success", 
                         "directVIP" =>$jlhVIP,
                         "cashBns" =>$bonusCash
                     );
        $dataJSON = json_encode($arrData);

    }else{
        $dataJSON = fSendStatusMessage("failed", "username not found");
    }
    return $dataJSON;
}



?>