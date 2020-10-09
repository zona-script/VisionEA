<?php
$path =  (dirname(dirname(__FILE__)));
include_once($path."/includes/inc_def.php");
//include_once($path."/includes/inc_session.php"); //DO NOT ACTIVATE, BECAUSE USED BY getData.php and do not turn on session at this part
include_once($path."/includes/inc_conn.php");
include_once($path."/includes/inc_functions.php");

function fSumCommissionRO_OLD ($username , $conn){ //ini tidak di pakai lagi, ganti yg baru
    $sql  = " SELECT SUM(trPDQty) AS trPDQty, SUM(BnsROAmount) AS BnsROAmount FROM ( ";
    $sql .= "   SELECT trProTransID, SUM(trPDQty) AS trPDQty FROM trProduct";
    $sql .= "   INNER JOIN trProDetail ON trPDTransID = trProTransID";
    $sql .= "   WHERE trProUsername = '".$username."' AND trProStatus ='".$GLOBALS['DEF_STATUS_APPROVED']."' ";
    $sql .= "   AND (trProType = '".$GLOBALS['DEF_TYPE_PURCHASE_RO']."' OR trProType = '".$GLOBALS['DEF_TYPE_PURCHASE_RS']."') ";
    $sql .= "   GROUP BY trPDTransID";
    $sql .= " ) AS a";
    $sql .= " INNER JOIN dtBnsRO ON BnsROID = a.trProTransID";
    $query = $conn->query($sql);
    $tBnsRO = 0;
    if ($row = $query->fetch_assoc()){
        $tBnsRO     = $row['BnsROAmount'];
        $trPDQty    = $row['trPDQty'];
    }
    unset($query);
    unset($row);
    $arrData = array(
        "status"    => "success", 
        "trPDQty"   => $trPDQty,
        "tBnsRO"    => $tBnsRO
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

function fSumCommissionRO ($username , $conn){
    $sql  = " SELECT BnsROUsername, SUM(BnsROAmount) AS BnsROAmount, SUM(trPDQty) AS trPDQty";
    $sql .= " FROM dtBnsRO";
    $sql .= " INNER JOIN (";
    $sql .= "   SELECT trPDTransID, SUM(trPDQty) AS trPDQty";
    $sql .= "   FROM trProDetail GROUP BY trPDTransID ";
    $sql .= " ) AS detail ON trPDTransID = BnsROID ";
    $sql .= " WHERE BnsROUsername = '".$username."'";
    $sql .= " GROUP BY BnsROUsername";
    $result = $conn->query($sql);
    $tBnsRO = $trPDQty = 0;
    if ($row = $result->fetch_assoc()){
        $tBnsRO     = $row['BnsROAmount'];
        $trPDQty    = $row['trPDQty'];
    }
    unset($result);
    unset($row);
    $arrData = array(
        "status"    => "success", 
        "trPDQty"   => $trPDQty,
        "tBnsRO"    => $tBnsRO
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

function fSumWalletGross ($username, $conn){
    $sql  = "SELECT SUM(wGross) AS wGross, ttlMember FROM dtWallet";
    $sql .= " LEFT JOIN (";
    $sql .= "   SELECT mbrSponsor, COUNT(*) AS ttlMember";
    $sql .= "   FROM dtMember GROUP BY mbrSponsor";
    $sql .= " ) AS A ON mbrSponsor = wUsername";
    $sql .= " WHERE wUsername = '".$username."' ";
    $query = $conn->query($sql);
    $wGross = 0;
    if ($row = $query->fetch_assoc()){
        $wGross  = $row['wGross']; 
        $wGross = ($row['ttlMember'] < $GLOBALS['MIN_SPONSOR'])?0:$wGross;
    }
    unset($query);
    unset($row);
    $arrData = array(
        "status"    => "success", 
        "wGross"    => $wGross    
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

function fWalletUsage ($username, $conn){
    $sql  = "SELECT * FROM trUsageWallet";
    $sql .= " WHERE tuwUsername = '".$username."' ";
    $query = $conn->query($sql);
    $tuwAmount = 0;
    while ($row = $query->fetch_assoc()){
        $tuwAmount  += $row['tuwAmount'];
    }
    unset($query);
    unset($row);
    $arrData = array(
        "status"   => "success", 
        "tuwAmount"    => $tuwAmount    
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

function fAllCommissionSponsorship ($conn){
    $sql  = "SELECT sum(ttl) AS total ";
    $sql .= " FROM (";
    $sql .= " SELECT bnsSpUsername, bnsSpTrPacID, SUM(bnsSpAmount) AS ttl, COUNT(bnsSpTrPacID) AS nums";
    $sql .= " FROM dtBnsSponsor WHERE bnsSpTrPacID = 'st'";
    $sql .= " GROUP BY bnsSpUsername, bnsSpTrPacID";
    $sql .= " UNION";
    $sql .= " SELECT bnsSpUsername, bnsSpTrPacID, SUM(bnsSpAmount) AS ttl, COUNT(bnsSpTrPacID) AS nums";
    $sql .= " FROM dtBnsSponsor";
    $sql .= " WHERE bnsSpTrPacID = 'pr'";
    $sql .= " GROUP BY bnsSpUsername, bnsSpTrPacID";
    $sql .= " UNION SELECT bnsSpUsername, bnsSpTrPacID, SUM(bnsSpAmount) AS ttl, COUNT(bnsSpTrPacID) AS nums";
    $sql .= " FROM dtBnsSponsor";
    $sql .= " WHERE bnsSpTrPacID = 'vip'";
    $sql .= " GROUP BY bnsSpUsername, bnsSpTrPacID";  
    $sql .= " ORDER BY `bnsSpUsername` ASC";
    $sql .= " ) as a";
    $query = $conn->query($sql);
    $total = 0;
    while($row = $query->fetch_assoc()){
        $total      += $row['total'];
    }
    unset($query);
    unset($row);
    $arrData = array(
        "status"   => "success", 
        "total"    => $total    
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

function fAllCommissionPassedUP($conn){
    $sql  = "SELECT SUM(ttl) totalPU";
    $sql .= " FROM ( ";
    $sql .= "   SELECT bnsPUUsername, bnsPUTrPacID, SUM(bnsPUAmount) AS ttl, COUNT(bnsPUTrPacID) AS nums";
    $sql .= "   FROM dtBnsPassedUp WHERE bnsPUTrPacID = 'st'";
    $sql .= "   GROUP BY bnsPUUsername, bnsPUTrPacID";
    $sql .= "   UNION";
    $sql .= "   SELECT bnsPUUsername, bnsPUTrPacID, SUM(bnsPUAmount) AS ttl, COUNT(bnsPUTrPacID) AS nums";
    $sql .= "   FROM dtBnsPassedUp";
    $sql .= "   WHERE bnsPUTrPacID = 'pr'";
    $sql .= "   GROUP BY bnsPUUsername, bnsPUTrPacID";
    $sql .= "   UNION SELECT bnsPUUsername, bnsPUTrPacID, SUM(bnsPUAmount) AS ttl, COUNT(bnsPUTrPacID) AS nums";
    $sql .= "   FROM dtBnsPassedUp";
    $sql .= "   WHERE bnsPUTrPacID = 'vip'";
    $sql .= "   GROUP BY bnsPUUsername, bnsPUTrPacID";
    $sql .= "   ORDER BY `bnsPUUsername` ASC";
    $sql .= " )as a";
    $query = $conn->query($sql);
    $total = 0;
    while($row = $query->fetch_assoc()){
        $total      += $row['totalPU'];
    }
    unset($query);
    unset($row);
    $arrData = array(
        "status"   => "success", 
        "total"    => $total
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}


function fAllCommissionPairing($conn){
    $sql  = "SELECT SUM(sumPairCommission) TU"; 
    $sql .= "   FROM ( SELECT pairUsername, sumLeft, sumRight, sumTO, sumFO, sumPairCommission, IF(ISNULL(ttlMember),0, ttlMember) AS ttlMember";
    $sql .= "       FROM ("; 
    $sql .= "           SELECT pairUsername, SUM(wkTtlLeft) AS sumLeft, SUM(wkTtlRight) AS sumRight, SUM(wkTurnOver) AS sumTO, ";
    $sql .= "           SUM(wkFlushOut) AS sumFO, sumPairCommission";
    $sql .= "           FROM dtWeeklyPairing";
    $sql .= "           RIGHT JOIN(";
    $sql .= "               SELECT pairUsername, SUM(pairTO) AS sumPairCommission";
    $sql .= "               FROM dtDailyPairing";
    $sql .= "               GROUP BY pairUsername";
    $sql .= "           ) pair ON pair.pairUsername = wkMbrUsername";
    $sql .= "           GROUP BY pairUsername";
    $sql .= "        ) A";
    $sql .= "        LEFT JOIN ("; 
    $sql .= "            SELECT mbrSponsor, COUNT(*) ttlMember";
    $sql .= "            FROM dtMember GROUP BY mbrSponsor";
    $sql .= "        ) B ON pairUsername = mbrSponsor";  
    $sql .= "        ORDER BY `A`.`pairUsername` ASC";
    $sql .= "   ) AS A";
    $query = $conn->query($sql);
    $total = 0;
    while($row = $query->fetch_assoc()){
        $total      += $row['TU'];
    }
    unset($query);
    unset($row);
    $arrData = array(
        "status"   => "success", 
        "total"    => $total
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

function fAllCommissionMatching($conn){
    $sql  = " SELECT SUM(sumMtch) MM, pacMatchingGen, IF(ISNULL(ttlMember),0, ttlMember) AS ttlMember";
    $sql .= " FROM (";
    $sql .= "   SELECT mtchUsername, SUM(mtchAmount) AS sumMtch, pacMatchingGen";
    $sql .= "   FROM dtMatching"; 
    $sql .= "   INNER JOIN dtMember ON mbrUsername = mtchUsername"; 
    $sql .= "   INNER JOIN(";
    $sql .= "       SELECT trUsername, pacMatchingGen FROM Transaction t"; 
    $sql .= "       INNER JOIN msPackage ON pacID = trPacID";
    $sql .= "       WHERE trID = (";
    $sql .= "           SELECT trID FROM Transaction";
    $sql .= "           WHERE trUsername = t.trUsername";
    $sql .= "           ORDER BY trDate";
    $sql .= "       DESC LIMIT 1 )";
    $sql .= "   ) p ON p.trUsername = mbrUsername";
    $sql .= " )A";
    $sql .= " LEFT JOIN(";
    $sql .= "   SELECT mbrSponsor, COUNT(*) ttlMember"; 
    $sql .= "   FROM dtMember GROUP BY mbrSponsor";
    $sql .= " ) B ON mtchUsername = B.mbrSponsor";
    $query = $conn->query($sql);
    $total = 0;
    while($row = $query->fetch_assoc()){
        $total      += $row['MM'];
    }

    unset($query);
    unset($row);
    $arrData = array(
        "status"   => "success", 
        "total"    => $total
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

function fAllSumConvert($voucherType, $conn){
    $total = 0;
    $accType = "";

    $voucherType = strtoupper($voucherType);
    if ($voucherType == "STD"){
        //convert bonus to voucher std
        $accType = $GLOBALS["DEF_CONVERT_BNS_VOUCHER"];
    }else if ($voucherType == "VPS"){
        //convert wallet to voucher vps
        $accType = $GLOBALS["DEF_CONVERT_WALLET_VOUCHER"];
    }

    if ($accType != ""){
        $sql  = "SELECT SUM(IFNULL(finAmount,0)) AS sumConvert FROM dtFundIn ";
        $sql .= " WHERE finAccType='". $accType ."' ";
        $sql .= " AND finVoucherType='". $voucherType . "' AND finStatus='".$GLOBALS['DEF_STATUS_APPROVED'] . "'";
        $sql .= " GROUP BY finMbrUsername";
        $query = $conn->query($sql);
        while($row = $query->fetch_assoc()){
            $total += $row['sumConvert'];
        }
    }

    unset($query);
    unset($row);

    $arrData = array(
        "status"    => "success", 
        "total"     => $total
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

function fAllSumWithdrawal($conn){
    $ttlWD = 0;
    //Status ON_Progress or Approved
    $sql     = "SELECT wdMbrUsername, sum(wdAmount) AS sumWD FROM dtWDFund ";
    $sql    .= " WHERE (wdStID ='". $GLOBALS['DEF_STATUS_REQUEST'] . "' OR wdStID='". $GLOBALS['DEF_STATUS_ONPROGRESS'] . "' OR wdStID='".$GLOBALS['DEF_STATUS_APPROVED'] ."')";
    
    $query = $conn->query($sql);
    while($row = $query->fetch_assoc()){
        $ttlWD += $row['sumWD'];
    }

    unset($query);
    unset($row);

    $arrData = array(
        "status"   =>"success", 
        "ttlWD"    =>$ttlWD                 
    );
    $dataJSON = json_encode($arrData);
    return $dataJSON;
}

//Sponsor _____________________________________________
function fCommissionSponsorship($username, $conn){
    if ($username != ""){
        $sql  = "SELECT bnsSpTrPacID, SUM(bnsSpAmount) as ttl, COUNT(bnsSpTrPacID) as nums FROM dtBnsSponsor ";
        $sql .= " WHERE bnsSpUsername = '".$username."' AND bnsSpTrPacID='st' ";
        $sql .= " GROUP BY bnsSpUsername, bnsSpTrPacID ";
        $sql .= " UNION ";
        $sql .= " SELECT bnsSpTrPacID, SUM(bnsSpAmount) as ttl, COUNT(bnsSpTrPacID) as nums FROM dtBnsSponsor ";
        $sql .= " WHERE bnsSpUsername = '".$username."' AND bnsSpTrPacID='pr' ";
        $sql .= " GROUP BY bnsSpUsername, bnsSpTrPacID ";
        $sql .= " UNION ";
        $sql .= " SELECT bnsSpTrPacID, SUM(bnsSpAmount) as ttl, COUNT(bnsSpTrPacID) as nums FROM dtBnsSponsor ";
        $sql .= " WHERE bnsSpUsername = '".$username."' AND bnsSpTrPacID='vip' ";
        $sql .= " GROUP BY bnsSpUsername, bnsSpTrPacID";
        $query = $conn->query($sql);
        $total = $numPacST = $numPacPR = $numPacVIP = $totalSP = 0;
        while($row = $query->fetch_assoc()){
            if (strtolower($row['bnsSpTrPacID']) == 'st'){
                $numPacST   = $row['nums'];
                $total      += $row['ttl'];
            }elseif (strtolower($row['bnsSpTrPacID']) == 'pr'){
                $numPacPR   = $row['nums'];
                $total      += $row['ttl'];
            }elseif (strtolower($row['bnsSpTrPacID']) == 'vip'){
                $numPacVIP   = $row['nums'];
                $total      += $row['ttl'];
            }
            $totalSP += $row['nums'];
        }
        unset($query);
        unset($row);

        $arrData = array("status"   => "success", 
                         "total"    => $total,
                         "numPacST" => $numPacST,
                         "numPacPR" => $numPacPR,
                         "numPacVIP"=> $numPacVIP,
                         "totalSP"  => $totalSP
                     );
        $dataJSON = json_encode($arrData);

    }else{
        $dataJSON = fSendStatusMessage("failed", "username not found");
    }
    return $dataJSON;
}



//Passed-Up _____________________________________________
function fCommissionPassedUP($username, $conn){
    if ($username != ""){
        $sql = "SELECT bnsPUTrPacID, SUM(bnsPUAmount) as ttl, COUNT(bnsPUTrPacID) as nums FROM dtBnsPassedUp ";
        $sql .= " WHERE bnsPUUsername = '".$username."' AND bnsPUTrPacID='st' ";
        $sql .= " GROUP BY bnsPUUsername, bnsPUTrPacID ";
        $sql .= " UNION ";
        $sql .= " SELECT bnsPUTrPacID, SUM(bnsPUAmount) as ttl, COUNT(bnsPUTrPacID) as nums FROM dtBnsPassedUp ";
        $sql .= " WHERE bnsPUUsername = '".$username."' AND bnsPUTrPacID='pr' ";
        $sql .= " GROUP BY bnsPUUsername, bnsPUTrPacID ";
        $sql .= " UNION ";
        $sql .= " SELECT bnsPUTrPacID, SUM(bnsPUAmount) as ttl, COUNT(bnsPUTrPacID) as nums FROM dtBnsPassedUp ";
        $sql .= " WHERE bnsPUUsername = '".$username."' AND bnsPUTrPacID='vip' ";
        $sql .= " GROUP BY bnsPUUsername, bnsPUTrPacID";
        //echo $sql;
        $query = $conn->query($sql);
        $totalPU = $numPUST = $numPUPR = $numPUVIP = 0;
        while($row = $query->fetch_assoc()){
            if (strtolower($row['bnsPUTrPacID']) == 'st'){
                $numPUST   = $row['nums'];
                $totalPU   += $row['ttl'];
            }elseif (strtolower($row['bnsPUTrPacID']) == 'pr'){
                $numPUPR   = $row['nums'];
                $totalPU   += $row['ttl'];
            }elseif (strtolower($row['bnsPUTrPacID']) == 'vip'){
                $numPUVIP   = $row['nums'];
                $totalPU    += $row['ttl'];
            }
        }
        unset($query);
        unset($row);
        $arrData = array("status"   =>"success", 
                         "totalPU"  =>$totalPU,
                         "numPUST"  =>$numPUST,
                         "numPUPR"  =>$numPUPR,
                         "numPUVIP" =>$numPUVIP
                     );
        $dataJSON = json_encode($arrData);
        
    }else{
        $dataJSON = fSendStatusMessage("failed", "username not found");
    }
    return $dataJSON;
}



//Pairing _____________________________________________
function fCommissionPairing($username, $conn){
    if ($username != ""){
        /*
        $sql = "SELECT wkMbrUsername, SUM(wkTtlLeft) as sumLeft, SUM(wkTtlRight) as sumRight, SUM(wkTurnOver) as sumTO, sum(wkFlushOut) as sumFO, sumPairCommission ";
        $sql .= " FROM dtWeeklyPairing ";
        $sql .= " INNER JOIN (SELECT pairUsername, SUM(pairTO) as sumPairCommission FROM dtDailyPairing GROUP BY pairUsername) pair on pair.pairUsername=wkMbrUsername";
        $sql .= " WHERE wkMbrUsername = '".$username."' ";
        $sql .= " GROUP BY wkMbrUsername";

        */

        $sql = "SELECT pairUsername, sumLeft, sumRight, sumTO, sumFO, sumPairCommission, IF(isnull(ttlMember), 0, ttlMember) as ttlMember  FROM (";
        $sql .= " SELECT pairUsername, SUM(wkTtlLeft) as sumLeft, SUM(wkTtlRight) as sumRight, SUM(wkTurnOver) as sumTO, sum(wkFlushOut) as sumFO, sumPairCommission ";
        $sql .= " FROM dtWeeklyPairing ";
        $sql .= " RIGHT JOIN (SELECT pairUsername, SUM(pairTO) as sumPairCommission FROM dtDailyPairing GROUP BY pairUsername) pair on pair.pairUsername=wkMbrUsername";
        $sql .= " WHERE pairUsername = '".$username."' ";
        $sql .= " GROUP BY pairUsername";
        $sql .= "    ) A";
        $sql .= " LEFT JOIN ";
        $sql .= " ( SELECT mbrSponsor, COUNT(*) ttlMember FROM dtMember WHERE mbrSponsor='". $username . "') B ";
        $sql .= " ON pairUsername = mbrSponsor ";


        //fPrint ($sql);
        //$dataJSON = fSendStatusMessage("failed",$sql);         return ($dataJSON); die();
        
        $query = $conn->query($sql);
        $sumLeft = $sumRight = $sumTO = $sumFO = 0;
        if($row = $query->fetch_assoc()){
            $sumLeft    = $row['sumLeft'];
            $sumRight   = $row['sumRight'];
            if ($row['ttlMember'] >= $GLOBALS['MIN_SPONSOR'] ){ //minimal sponsor
                $sumTO      = $row['sumPairCommission']; //$row['sumTO'];
            }else{
                $sumTO      = 0;
            }
            $sumFO      = $row['sumFO'];
        }
        unset($query);
        unset($row);
        $arrData = array("status"   =>"success", 
                         "sumLeft"  =>$sumLeft,
                         "sumRight" =>$sumRight,
                         "sumTO"    =>$sumTO,
                         "sumFO"    =>$sumFO
                     );
        $dataJSON = json_encode($arrData);
        
    }else{
        $dataJSON = fSendStatusMessage("failed", "username not found");
    }
    return $dataJSON;
}

//Matching _____________________________________________
function fCommissionMatching($username, $conn){
    if ($username != ""){
        //$sql = "SELECT mtchUsername, SUM(mtchAmount) as sumMtch FROM dtMatching  WHERE mtchUsername='".$username."' GROUP BY mtchUsername";
        $sql = "SELECT mtchUsername, sumMtch, pacMatchingGen, IF(isnull(ttlMember), 0, ttlMember) AS ttlMember FROM ( ";
        $sql .= " SELECT mtchUsername, SUM(mtchAmount) as sumMtch , pacMatchingGen ";
        $sql .= " FROM dtMatching  ";
        $sql .= " INNER JOIN dtMember on mbrUsername = mtchUsername  ";
        $sql .= " INNER JOIN ( ";
        $sql .= "    SELECT trUsername, pacMatchingGen FROM Transaction t   ";
        $sql .= "              INNER JOIN msPackage ON pacID = trPacID  ";
        $sql .= "                  WHERE trID = (   ";
        $sql .= "                      SELECT trID FROM Transaction   ";
        $sql .= "                      WHERE trUsername= t.trUsername  ";
        $sql .= "                      ORDER BY trDate DESC   ";
        $sql .= "                      LIMIT 1   ";
        $sql .= "                  )  ";
        $sql .= "    ) p on p.trUsername=mbrUsername ";
        $sql .= " WHERE mtchUsername='" . $username . "'";
        $sql .= "             ) A  ";
        $sql .= " LEFT JOIN ( ";
        $sql .= "   SELECT mbrSponsor, COUNT(*) ttlMember FROM dtMember WHERE mbrSponsor='" . $username . "') B  ";
        $sql .= "  ON mtchUsername = mbrSponsor  ";

        //$sql .= " GROUP BY mtchUsername ";
        //fPrint ($sql);

        //echo $sql; die();
        $query=$conn->query($sql);
        $sumMtch = $pacMatchingGen = 0;
        if ($row=$query->fetch_assoc()){
            if ($row['ttlMember'] >= $GLOBALS['MIN_SPONSOR'] ){ //minimal sponsor
                $sumMtch    = $row['sumMtch'];
            }else{
                $sumMtch    = 0;
            }
            $pacMatchingGen = $row['pacMatchingGen'];
        }

        unset($query);
        unset($row);    
        $arrData = array("status"           =>"success", 
                         "sumMtch"          =>$sumMtch,
                         "pacMatchingGen"   =>$pacMatchingGen
                     );
        $dataJSON = json_encode($arrData);
        
    }else{
        $dataJSON = fSendStatusMessage("failed", "username not found");
    }
    return $dataJSON;
}


//Voucher Standard and VPS _____________________________________________
function fGetNumberOfVoucher($voucherType, $username, $conn){
    if ($username != ""){
        $sql = "SELECT finMbrUsername, SUM(sumActVoucher) AS sumActVoucher, SUM(sumActivationVoucher) AS sumActivationVoucher,  SUM(sumTransferVoucher) AS sumTransferVoucher,  SUM(sumRepeatOrder) AS sumRepeatOrder FROM ";
        $sql .= " (SELECT * from  ";
        $sql .= "     ( ";
        $sql .= "         SELECT '". $username . "' AS finMbrUsername, COUNT(fivVCode) AS sumActVoucher, 0 AS sumActivationVoucher, 0 AS sumTransferVoucher, 0 AS sumRepeatOrder FROM dtFundIn ";
        $sql .= "         INNER JOIN  ";
        $sql .= "         dtFundInVoucher on fivFinID = finID ";
        $sql .= "         WHERE finMbrUsername = '". $username . "' AND finStatus = '". $GLOBALS['DEF_STATUS_APPROVED'] . "' AND fivStatus='". $GLOBALS['DEF_STATUS_ACTIVE'] . "' AND finVoucherType='" . $voucherType . "'";
        $sql .= "     ) active ";
        $sql .= "     UNION ";
        $sql .= "     ( ";
        $sql .= "         SELECT '". $username . "', 0 AS sumActVoucher, COUNT(fivVCode) AS sumActivationVoucher, 0 AS sumTransferVoucher, 0 AS sumRepeatOrder FROM dtFundIn ";
        $sql .= "         INNER JOIN  ";
        $sql .= "         dtFundInVoucher on fivFinID = finID ";
        $sql .= "         WHERE finMbrUsername = '". $username . "' AND finStatus = '". $GLOBALS['DEF_STATUS_APPROVED'] . "' AND fivStatus='". $GLOBALS['DEF_STATUS_USED'] . "' AND fivUsedFor='".$GLOBALS['DEF_VOUCHER_USED_FOR_ACTIVATION']."' AND finVoucherType='" . $voucherType . "'";
        $sql .= "     ) ";
        $sql .= "     UNION ";
        $sql .= "     ( ";
        $sql .= "         SELECT '". $username . "', 0 AS sumActVoucher, 0 AS sumActivationVoucher, COUNT(fivVCode) AS sumTransferVoucher, 0 AS sumRepeatOrder  FROM dtFundIn ";
        $sql .= "         INNER JOIN  ";
        $sql .= "         dtFundInVoucher on fivFinID = finID ";
        $sql .= "         WHERE finMbrUsername = '". $username . "' AND finStatus = '". $GLOBALS['DEF_STATUS_APPROVED'] . "' AND fivStatus='". $GLOBALS['DEF_STATUS_USED'] . "' AND fivUsedFor='".$GLOBALS['DEF_VOUCHER_USED_FOR_TRANSFER']."' AND finVoucherType='" . $voucherType . "'";
        $sql .= "     ) ";
        $sql .= "     UNION ";
        $sql .= "     ( ";
        $sql .= "         SELECT '". $username . "', 0 AS sumActVoucher, 0 AS sumActivationVoucher, 0 AS sumTransferVoucher, COUNT(fivVCode) AS sumRepeatOrder  FROM dtFundIn ";
        $sql .= "         INNER JOIN  ";
        $sql .= "         dtFundInVoucher on fivFinID = finID ";
        $sql .= "         WHERE finMbrUsername = '". $username . "' AND finStatus = '". $GLOBALS['DEF_STATUS_APPROVED'] . "' AND fivStatus='". $GLOBALS['DEF_STATUS_USED'] . "' AND fivUsedFor='".$GLOBALS['DEF_VOUCHER_USED_FOR_RO']."' AND finVoucherType='" . $voucherType . "'";
        $sql .= "     ) ";
        $sql .= " ) a  ";
        $sql .= " GROUP by finMbrUsername";
        
        $query=$conn->query($sql);
        $voucherAct = $voucherIN = $voucherOUT = $sumActivationVoucher = $sumTransferVoucher = 0;
        if ($row=$query->fetch_assoc()){
            //$voucherAct     = $row['sumActVoucher'];
            //$voucherOUT     = $row['sumUsedVoucher'];
            //$voucherIN      = $voucherAct + $voucherOUT;

            $voucherAct             = $row['sumActVoucher']; //New Voucher
            $sumActivationVoucher   = $row['sumActivationVoucher']; //Used for Activation
            $sumTransferVoucher     = $row['sumTransferVoucher'];   //used for Transfer
            $sumRepeatOrder         = $row['sumRepeatOrder']; //Used for Repeat Order
            $voucherOUT             = $row['sumActivationVoucher'] + $row['sumTransferVoucher']+$row['sumRepeatOrder'];
            $voucherIN              = $voucherAct + $voucherOUT;

        }

        unset($query);
        unset($row);

        $voucherPrice = 0;
        if ($voucherType == $GLOBALS['DEF_VOUCHER_TYPE_VPS']){
            $voucherPrice = $GLOBALS['DEF_VOUCHER_PRICE_VPS']; //@10
        }else{
            $voucherPrice = $GLOBALS['DEF_VOUCHER_PRICE_IDR']; //@3500
        }

        $arrData = array(
            "status"       => "success", 
            "voucherAct"   =>$voucherAct,
            "voucherOUT"   =>$voucherOUT,
            "voucherIN"    =>$voucherIN,
            "sumActivationVoucher" =>$sumActivationVoucher,
            "sumTransferVoucher"   =>$sumTransferVoucher,
            "sumRepeatOrder"       =>$sumRepeatOrder,
            "voucherBalance"       => ($voucherAct * $voucherPrice)                     
        );
        $dataJSON = json_encode($arrData);


    }else{
        $dataJSON = fSendStatusMessage("failed", "username not found");
    }
    return $dataJSON;
}




function fSumWithdrawal($username, $conn){
    if ($username != ""){
        $ttlWD = 0;
        //Status ON_Progress or Approved
        $sql = "SELECT wdMbrUsername, sum(wdAmount) AS sumWD FROM dtWDFund ";
        $sql .= " WHERE wdMbrUsername = '". $username . "' AND (wdStID ='". $GLOBALS['DEF_STATUS_REQUEST'] . "' OR wdStID='". $GLOBALS['DEF_STATUS_ONPROGRESS'] . "' OR wdStID='".$GLOBALS['DEF_STATUS_APPROVED'] ."')";
        $sql .= " GROUP BY wdMbrUsername";
        
        $query = $conn->query($sql);
        if ($row = $query->fetch_assoc()){
            $ttlWD = $row['sumWD'];
        }

        unset($query);
        unset($row);

        $arrData = array("status"       =>"success", 
                         "ttlWD"        =>$ttlWD
                     );
        $dataJSON = json_encode($arrData);
    }else{
        $dataJSON = fSendStatusMessage("failed", "username not found");
    }
    return $dataJSON;
}

function fSumConvert($voucherType, $username, $conn){
    if ($username != ""){
        $ttlConvert = 0;
        $accType = "";

        $voucherType = strtoupper($voucherType);
        if ($voucherType == "STD"){
            //convert bonus to voucher std
            $accType = $GLOBALS["DEF_CONVERT_BNS_VOUCHER"];
        }else if ($voucherType == "VPS"){
            //convert wallet to voucher vps
            $accType = $GLOBALS["DEF_CONVERT_WALLET_VOUCHER"];
        }

        if ($accType != ""){
            $sql  = "SELECT finMbrUsername, SUM(IFNULL(finAmount,0)) AS sumConvert FROM dtFundIn ";
            $sql .= " WHERE finMbrUsername='". $username . "' AND finAccType='". $accType ."' ";
            $sql .= " AND finVoucherType='". $voucherType . "' AND finStatus='".$GLOBALS['DEF_STATUS_APPROVED'] . "'";
            $sql .= " GROUP BY finMbrUsername";
            $query = $conn->query($sql);
            if ($row = $query->fetch_assoc()){
                $ttlConvert = $row['sumConvert'];
            }
        }

        unset($query);
        unset($row);

        $arrData = array(
            "status"       =>"success", 
            "ttlConvert"   =>$ttlConvert
        );
        $dataJSON = json_encode($arrData);
    }else{
        $dataJSON = fSendStatusMessage("failed", "username not found");
    }
    return $dataJSON;
}


function fGetBalance($username, $conn){
    if ($username != ""){
        $ttlBonus = $total = $totalPU = $sumTO = $sumMtch = 0;
        $voucherBalance = $voucherBalanceVPS = $ttlWD = 0;
        $ttlCommission = $wallet = $balance = 0;
        $wUsage = $walletGross = $ttlConvert = $ttlConvertVPS = $tBnsRO = 0;
        $myDataObj  = json_decode(fCommissionSponsorship($username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $total  = $myDataObj->{'total'};
        }


        $myDataObj  = json_decode(fCommissionPassedUP($username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $totalPU  = $myDataObj->{'totalPU'};
        }

        $myDataObj  = json_decode(fCommissionPairing($username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $sumTO  = $myDataObj->{'sumTO'};
            //$sumFO  = $myDataObj->{'sumFO'};
        }

        $myDataObj  = json_decode(fCommissionMatching($username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $sumMtch  = $myDataObj->{'sumMtch'};
        }

        //Voucher Activation
        $myDataObj  = json_decode(fGetNumberOfVoucher($GLOBALS['DEF_VOUCHER_TYPE_STD'], $username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $voucherBalance  = $myDataObj->{'voucherBalance'};
        }

        //Voucher VPS
        $myDataObj  = json_decode(fGetNumberOfVoucher($GLOBALS['DEF_VOUCHER_TYPE_VPS'], $username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $voucherBalanceVPS  = $myDataObj->{'voucherBalance'};
        }

        $myDataObj  = json_decode(fSumWithdrawal($username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $ttlWD  = $myDataObj->{'ttlWD'};
        }

        //Convert Bonus to Voucher STD
        $myDataObj  = json_decode(fSumConvert($GLOBALS['DEF_VOUCHER_TYPE_STD'], $username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $ttlConvert  = $myDataObj->{'ttlConvert'};
        }

        //Convert Wallet to Voucher VPS
        $myDataObj  = json_decode(fSumConvert($GLOBALS['DEF_VOUCHER_TYPE_VPS'], $username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $ttlConvertVPS  = $myDataObj->{'ttlConvert'};
        }

        //Wallet Usage
        $myDataObj  = json_decode(fWalletUsage($username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $wUsage  = $myDataObj->{'tuwAmount'};
        }

        //Wallet Gross
        $myDataObj = json_decode(fSumWalletGross($username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $walletGross  = $myDataObj->{'wGross'};
        }

        //Bonus RO
        $myDataObj = json_decode(fSumCommissionRO($username, $conn));
        if ($myDataObj->{"status"} == "success"){
            $tBnsRO  = $myDataObj->{'tBnsRO'};
        }
        $ttlBonus       = $total + $totalPU + $sumTO + $sumMtch + $tBnsRO;
        $ttlCommissionGross  = $ttlBonus;
        $ttlCommission  = $ttlCommissionGross - $walletGross - $ttlWD - $ttlConvert;
        // kalau wallet mau digunakan, wallet jgn di 0
        $wallet         = 0; //$walletGross - $ttlConvertVPS - $wUsage;
        $balance        = $voucherBalance + $ttlCommission + $wallet;
        $balance        = ($balance < 0)?0:$balance;
        $arrData = array(
            "status"           =>"success", 
            "ttlBonus"         =>$ttlBonus,
            "ttlCommission"    =>$ttlCommission,
            "wallet"           =>$wallet,
            "wUsage"           =>$wUsage,
            "ttlConvert"       =>$ttlConvert,
            "ttlConvertVPS"    =>$ttlConvertVPS,
            "balance"          =>$balance
        );
        $dataJSON = json_encode($arrData);

    }else{
        $dataJSON = fSendStatusMessage("failed", "username not found");
    }
    return $dataJSON;
}

function fSumAvailableVoucher ($username, $conn) { // hitung jumlah voucher tersedia
    $sql = "SELECT fivFinID, fivVCode FROM ((dtFundIn ";
    $sql .= " INNER JOIN dtFundInVoucher on finID = fivFinID AND finStatus='" .$GLOBALS['DEF_STATUS_APPROVED']. "')";
    $sql .= " INNER JOIN dtVoucher on vCode = fivVCode AND vStatus = '" . $GLOBALS['DEF_STATUS_USED'] . "'";
    $sql .= " AND fivStatus = '" .$GLOBALS['DEF_STATUS_ACTIVE']."' AND vType = '".$GLOBALS['DEF_VOUCHER_TYPE_STD']."')";
    $sql .= " WHERE finMbrUsername='" .$username. "'";
    $arrVoucher = array();
    if ($query = $conn->query($sql)){
        if ($query->num_rows > 0){
            while ($row = $query->fetch_assoc()){
                //$VoucherBalance   = $row["VoucherBalance"];
                $arrVoucher[] = array("fivFinID" => $row["fivFinID"], "fivVCode" => $row["fivVCode"]);  
            }
            // $VoucherBalance = (sizeof($arrVoucher) * $DEF_VOUCHER_PRICE);
        }
        $arrData = array("status" => "success", "data" => $arrVoucher, "sql" => $sql);
        $dataJSON = json_encode($arrData);
    }else{
        $dataJSON =  fSendStatusMessage("error", $conn->error);
    }
    return $dataJSON;
}
        



?>