<?php
include_once("../includes/inc_def.php");
//include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");


function fMember($conn, $month, $year){
    global $DEF_STATUS_ACTIVE, $DEF_STATUS_APPROVED, $DEF_MUTASI_DATE;
    global $DEF_TYPE_PURCHASE_ACT, $DEF_TYPE_PURCHASE_RENEW, $DEF_TYPE_PURCHASE_RO;
    $sqlDate = $adtMsg = "";
    $tgl = date($year."-".$month."-01");
    $tglMulai = date_create($tgl);
    $tglSelesai = date_create($tgl);
    date_add($tglSelesai, date_interval_create_from_date_string("1 month"));
    date_add($tglSelesai, date_interval_create_from_date_string("-1 day"));
    $tglMulai = date_format($tglMulai, "Y-m-d");
    $tglSelesai = date_format($tglSelesai, "Y-m-d");
    if ($month != "0" && $year != "0"){
        if ($month == "06" && $year == "2020"){
            $tglMulai = $DEF_MUTASI_DATE;
            $adtMsg = "Start From Date $DEF_MUTASI_DATE";
        }else if (intval($month) < 6 && $year == "2020"){
            $tglMulai = $DEF_MUTASI_DATE;
        }
        $sqlDate = " AND date(trProUpdateDate) BETWEEN '$tglMulai' AND '$tglSelesai'";
    }
    if ($year == "0"){
        $adtMsg = "Start From Date $DEF_MUTASI_DATE";
        $sqlDate = " AND date(trProUpdateDate) >= '$DEF_MUTASI_DATE'";
    }
    if ($year != "0" && $month == "0"){
        $tglSelesai = date_create($tgl);
        date_add($tglSelesai, date_interval_create_from_date_string("13 month"));
        date_add($tglSelesai, date_interval_create_from_date_string("-1 day"));
        $tglSelesai = date_format($tglSelesai, "Y-m-d");
        $tglMulai = $DEF_MUTASI_DATE;
        $adtMsg = "Start From Date $DEF_MUTASI_DATE";
        $sqlDate = " AND date(trProUpdateDate) BETWEEN '$tglMulai' AND '$tglSelesai'";
    }
    $sql  = "SELECT jlhNew, jlhReNew, jlhRO";
    $sql .= " FROM (";
    $sql .= "   SELECT trProStatus st, SUM(trPDQty) as jlhNew";
    $sql .= "   FROM trProduct INNER JOIN trProDetail ON trProTransID = trPDTransID";
    $sql .= "   WHERE trProType = '".$DEF_TYPE_PURCHASE_ACT."' AND trProStatus = '".$DEF_STATUS_APPROVED."'";
    $sql .= $sqlDate;
    $sql .= " ) AS New ";
    $sql .= " INNER JOIN (";
    $sql .= "   SELECT trProStatus st, SUM(trPDQty) as jlhReNew";
    $sql .= "   FROM trProduct INNER JOIN trProDetail ON trProTransID = trPDTransID";
    $sql .= "   WHERE trProType = '".$DEF_TYPE_PURCHASE_RENEW."' AND trProStatus = '".$DEF_STATUS_APPROVED."'";
    $sql .= $sqlDate;
    $sql .= " ) AS ReNew ON New.st=ReNew.st";
    $sql .= " INNER JOIN (";
    $sql .= "   SELECT trProStatus st, SUM(trPDQty) as jlhRO";
    $sql .= "   FROM trProduct INNER JOIN trProDetail ON trProTransID = trPDTransID";
    $sql .= "   WHERE trProType = '".$DEF_TYPE_PURCHASE_RO."' AND trProStatus = '".$DEF_STATUS_APPROVED."'";
    $sql .= $sqlDate;
    $sql .= " ) AS RO ON New.st = RO.st";
    $result = $conn->query($sql);
    $jlhNew = $jlhReNew = $jlhRO = 0;
    if($row=$result->fetch_assoc()){
        $jlhNew     = $row['jlhNew'];
        $jlhReNew   = $row['jlhReNew'];
        $jlhRO      = $row['jlhRO'];
    }
    unset($result);
    unset($row);
    $arrData = array(
        "status"    => "success", 
        "jlhNew"    => $jlhNew,
        "jlhReNew"  => $jlhReNew,
        "jlhRO"     => $jlhRO,
        "adtMsg"    => $adtMsg
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}


function fGetNumOfPIN($conn){
    $sql = "SELECT vStatus, COUNT(*) AS totalPIN FROM dtVoucher GROUP BY vStatus";
    $query = $conn->query($sql);
    $totalPIN = $totalPINActive = $totalPINUsed = 0;
    while($row = $query->fetch_assoc()){
        $vStatus        = $row['vStatus'];
        if ($vStatus == $GLOBALS['DEF_STATUS_ACTIVE']){
            $totalPINActive   = $row['totalPIN'];
            $totalPIN         += $totalPINActive;
        }elseif ($vStatus == $GLOBALS['DEF_STATUS_USED']){
            $totalPINUsed     = $row['totalPIN'];
            $totalPIN         += $totalPINUsed;
        }
    }

    unset($query);
    unset($row);

    $arrData = array(
        "status"           =>"success", 
        "totalPINActive"   =>$totalPINActive,
        "totalPINUsed"     =>$totalPINUsed,
        "totalPIN"         =>$totalPIN
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

function fUpdateStatusSendCornEmail($conn, $ceid, $status){
    //update status corn email
    $table = "dtCornEmail";
    $arrData = array("cesendst" => $status, "cedate" => "CURRENT_TIME()");
    $arrDataQuery = array("ceid" => $ceid);
    if (fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){
        //update sukses
        return (true);
    }else{
        return (false);
    }
}

?>