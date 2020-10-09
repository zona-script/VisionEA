<?php
/*___________________VISION WEB______________*/
function addTradingAcc($conn, $tradeUsername, $tradeAccNo, $tradeEANum, $tradeAccOrder, $tradePair, $tradeAccPasswd, $tradeName, $tradeServer){
	global $DEF_STATUS_ONPROGRESS, $DEF_STATUS_ACTIVE;
	$sql  = " SELECT * FROM dtTradingAcc ";
    $sql .= " WHERE (tradeStID = '".$DEF_STATUS_ACTIVE."' OR tradeStID = '".$DEF_STATUS_ONPROGRESS."') ";
    $sql .= " AND ( tradeAccNo = '".$tradeAccNo."' OR tradeUsername = '".$tradeUsername."' )";
	$result = $conn->query($sql);
    if ($result->num_rows > 0){
        $isAdd = false;
    }else{
        $isAdd = true;
    }

    if ($tradeUsername == "" || $tradeAccNo == "" || $tradeEANum == "" || $tradeAccOrder == "" || $tradePair == "" || $tradeAccPasswd == "" || $tradeName == "" || $tradeServer == ""){
        $isAdd = false;
    }

    if ($isAdd) {
        $tradeID    = strtotime( 'now' ).rand( 10000, 99999 );
        //length 15
        $arrData = array(
            0 => array ('db' => 'tradeID'          , 'val' => $tradeID ),
            1 => array ('db' => 'tradeUsername'    , 'val' => $tradeUsername ),
            2 => array ('db' => 'tradeEANum'       , 'val' => $tradeEANum ),
            3 => array ('db' => 'tradeAccOrder'    , 'val' => $tradeAccOrder ),
            4 => array ('db' => 'tradePair'        , 'val' => $tradePair ),
            5 => array ('db' => 'tradeAccNo'       , 'val' => $tradeAccNo ),
            6 => array ('db' => 'tradeAccPasswd'   , 'val' => $tradeAccPasswd ),
            7 => array ('db' => 'tradeName'        , 'val' => $tradeName ),
            8 => array ('db' => 'tradeServer'      , 'val' => $tradeServer ),
            9 => array ('db' => 'tradeStID'        , 'val' => $DEF_STATUS_ONPROGRESS ),
            10 => array ('db' => 'tradeDate'       , 'val' => 'CURRENT_TIME()' )
        );

        if (fInsert('dtTradingAcc', $arrData, $conn)) {
            //insert success
            $sql  = "SELECT * FROM dtMember";
            $sql .= " WHERE mbrUsername = '".$tradeUsername."'";
            $sql .= " ORDER BY mbrDate DESC LIMIT 1";
            $result=$conn->query($sql);
            $row=$result->fetch_assoc();
            $returnArrData = array (
                "mbrEmail"  => $row['mbrEmail']
            );
            
            //modul insert dtTradingAccAff khusus EA chronos
            if ($tradeEANum == $DEF_EACODE_CHRONOS){
                $sql  = " SELECT * FROM dtTradingAccAff";
                $sql .= " WHERE affUsername = '".$tradeUsername."' AND affAccNo = '".$tradeAccNo."' ";
                $sql .= " ORDER BY affDate DESC LIMIT 1";
                $result = $conn->query($sql);
                if ($result->num_rows > 0){
                    //skip insert
                    return (resultJSON("success", "Update Trading Account Successfull", $returnArrData));
                }else{
                    unset($arrData);
                    $arrData = array (
                        0 => array ('db' => 'affUsername'   , 'val' => $tradeUsername),
                        1 => array ('db' => 'affAccNo'      , 'val' => $tradeAccNo),
                        2 => array ('db' => 'affDate'       , 'val' => 'CURRENT_TIME()'),
                        3 => array ('db' => 'affStatus'     , 'val' => $DEF_STATUS_ONPROGRESS)
                    );
                    if (fInsert('dtTradingAccAff', $arrData, $conn)){
                        return (resultJSON("success", "Update Trading Account Successfull", $returnArrData));
                    }else{
                        $conn->rollback();
                        return (resultJSON("error", "Update Trading Account Failed #2"));        
                    }
                }
            }
            return (resultJSON("success", "Update Trading Account Successfull", $returnArrData));
        }else {
            //insert fail
            return (resultJSON("error", "Update Trading Account Failed #1"));
        }
        // end else
    }else{
        //insert fail
        return (resultJSON("error", "Update Trading Account Failed - Existing Account Number", ""));
    }
}

function getTradingAcc($conn, $username, $EANum, $pairID){
	global $DEF_STATUS_ONPROGRESS, $DEF_STATUS_ACTIVE, $DEF_STATUS_PENDING;
	$sql  = " SELECT t.*, m.mbrFirstName, st.stDesc, pairID, pairName, EAID, EAName, IFNULL(reset.stDesc,'-') AS stDescReset, ";
    $sql .= " IFNULL(reset.resaccStID,'-') AS resaccStID, ";
    $sql .= " IF((tap.TAPUpdateDate != ''), IF(ISNULL(seaUpdateDate)=1, 'Waiting Update' , IF (seaUpdateDate > tap.TAPUpdateDate, 'Updated', 'Waiting for Update')) , IF(ISNULL(seaUpdateDate)=1, 'Waiting for Update', 'Standard Robot')) AS statusSett ";
    $sql .= " FROM dtTradingAcc AS t ";
    $sql .= " INNER JOIN dtMember AS m ON m.mbrUsername = t.tradeUsername ";
    $sql .= " INNER JOIN msEA ON EAID=tradeEANum";
    $sql .= " INNER JOIN msPair ON pairID = tradePair";
    $sql .= " INNER JOIN msStatus st ON st.stID=t.tradeStID";
    $sql .= " LEFT JOIN (";
    $sql .= "   SELECT resaccUsername, resaccNo, resaccStID, stDesc FROM dtReqResetAcc";
    $sql .= "   INNER JOIN msStatus ON resaccStID = stID";
    $sql .= "   WHERE resaccStID ='".$DEF_STATUS_ONPROGRESS."'";
    $sql .= " ) AS reset ON reset.resaccUsername = t.tradeUsername AND reset.resaccNo = t.tradeAccNo";
    $sql .= " LEFT JOIN (";
    $sql .= "   SELECT TAPUsername, TAPEANum, TAPPairID, TAPAccNo, TAPUpdateDate FROM dtTradeAccParams AS param"; 
    $sql .= "   WHERE TAPID = (";
    $sql .= "       SELECT TAPID FROM dtTradeAccParams";
    $sql .= "       WHERE TAPUsername = param.TAPUsername AND TAPEANum = param.TAPEANum AND TAPPairID = param.TAPPairID";
    $sql .= "       AND param.TAPAccNo = TAPAccNo ORDER BY TAPUpdateDate DESC LIMIT 1)";
    $sql .= " ) AS tap ON t.tradeUsername = tap.TAPUsername AND t.tradeEANum = tap.TAPEANum";
    $sql .= " AND t.tradePair = tap.TAPPairID AND t.tradeAccNo = tap.TAPAccNo";
    $sql .= " LEFT JOIN dtStateEA ON seaEA = t.tradeEANum AND seaAcc = t.tradeAccNo AND seaPair = t.tradePair";
    $sql .= " WHERE t.tradeUsername = '" . $username . "' AND t.tradeEANum = '".$EANum."' AND t.tradePair = '".$pairID."' ";
    $sql .= " ORDER BY tradeDate DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0){
        if ($row = $result->fetch_assoc()) {
            if ($row['tradeStID'] == $DEF_STATUS_ONPROGRESS || $row['tradeStID'] == $DEF_STATUS_ACTIVE){
            	$returnArrData = array (
                    'tradeStID'     => $row['tradeStID'],
                    'resaccStID'    => $row['resaccStID'],
                    'EAID'          => $row['EAID'],
                    'eaName'        => $row['EAName'],
                    'pairID'        => $row['pairID'],
                    'pairName'      => $row['pairName'],
                    'tradeAccNo'    => $row['tradeAccNo'],
                    'tradeName'     => $row['tradeName'],
                    'tradeServer'   => $row['tradeServer'],
                    'stDesc'        => $row['stDesc'],
                    'stDescReset'   => $row['stDescReset'],
                    'statusSett'    => $row['statusSett']
                );
                return (resultJSON ("success", "", $returnArrData));
            }else if ($row['tradeStID'] == $DEF_STATUS_PENDING){
            	// sudah pernah input trading acc (add ulang)
            	$returnArrData = array (
                    'tradeStID'     => '',
                    'resaccStID'    => '',
                    'EAID'          => '',
                    'eaName'        => '',
                    'pairID'        => '',
                    'pairName'      => '',
                    'tradeAccNo'    => '',
                    'tradeName'     => '',
                    'tradeServer'   => '',
                    'stDesc'        => '',
                    'stDescReset'   => '',
                    'statusSett'    => ''                
                );
            	return (resultJSON ("success", "", $returnArrData));
            }
        }
    }else{
    	// belum pernah input trading acc (member baru)
        $returnArrData = array (
            'tradeStID'     => '',
            'resaccStID'    => '',
            'EAID'          => '',
            'eaName'        => '',
            'pairID'        => '',
            'pairName'      => '',
            'tradeAccNo'    => '',
            'tradeName'     => '',
            'tradeServer'   => '',
            'stDesc'        => '',
            'stDescReset'   => '',
            'statusSett'    => ''
        );
        return (resultJSON ("success", "", $returnArrData));
    }
}

function reqResetAcc($conn, $username, $EANum, $pairID, $accNo){
	global $DEF_STATUS_ONPROGRESS, $DEF_STATUS_ACTIVE;
	$sql  = "SELECT tradeUsername, mbrEmail, tradeAccNo, tradeName, tradeServer, a.*"; 
    $sql .= " FROM dtTradingAcc";
    $sql .= " INNER JOIN dtMember ON mbrUsername = tradeUsername";
    $sql .= " LEFT JOIN (";
    $sql .= "   SELECT * FROM dtReqResetAcc WHERE resaccStID = '".$DEF_STATUS_ONPROGRESS."' AND resaccNo = '".$accNo."' ";
    $sql .= " ) AS a ON tradeUsername = a.resaccUsername";
    $sql .= " WHERE tradeUsername = '".$username."' AND tradeEANum = '".$EANum."' AND tradeAccNo = '".$accNo."' ";
    $sql .= " AND tradePair = '".$pairID."' AND tradeStID = '".$DEF_STATUS_ACTIVE."' ";
    if ($query = $conn->query($sql) ) {
        if ($row = $query->fetch_assoc()){
            if ($row['tradeAccNo'] == "" || $row['resaccNo'] != ""){
                // error sudah melakukan request reset (cek status req reset di mymac)
            	return (resultJSON ("error", "Permintaan reset akun trading gagal, silahkan coba lagi atau hubungi support jika mengalami kendala yang sama. #1", ""));
            }
            $conn->autocommit(false);
            $resaccID    = strtotime("now").rand(10000, 99999);
            $arrData = array (
                0 => array ("db" => "resaccID"          , "val" => $resaccID),
                1 => array ("db" => "resaccDate"        , "val" => "CURRENT_TIME()"),
                2 => array ("db" => "resaccUsername"    , "val" => $row['tradeUsername']),
                3 => array ("db" => "resaccNo"          , "val" => $row['tradeAccNo']),
                4 => array ("db" => "resaccStID"        , "val" => $DEF_STATUS_ONPROGRESS)
            );
            if (fInsert("dtReqResetAcc", $arrData, $conn)){
                $conn->commit();
                $returnArrData = array(
                    "mbrEmail"      => $row['mbrEmail'],
                    "tradeAccNo"    => $row['tradeAccNo'],
                    "tradeName"     => $row['tradeName'],
                    "tradeServer"   => $row['tradeServer']
                );
                return (resultJSON ("success", "Permintaan reset akun trading berhasil dikirim", $returnArrData));
            }else{
                $conn->rollback();
                return (resultJSON ("error", "Permintaan reset akun trading gagal, silahkan coba lagi atau hubungi support jika mengalami kendala yang sama. #2", ""));
            }
        }
    }
    return (resultJSON ("error", "Permintaan reset akun trading gagal, silahkan coba lagi atau hubungi support jika mengalami kendala yang sama. #3", ""));
}

function getProfile($conn, $username){
    global $DEF_STATUS_ACTIVE;
    $sql  = "SELECT m.*, s.mbrUsername as spUsername, s.mbrFirstName as spName, trDate, DATE_ADD( m.mbrDate, INTERVAL (trThn*12) MONTH) AS ExpiredDate, countryDesc, IFNULL(vrStatus,'-') AS vrStatus, vrFileName, vrIDNum";
    $sql .= " FROM dtMember AS m";
    $sql .= " INNER JOIN dtMember AS s on m.mbrSponsor = s.mbrUsername ";
    $sql .= " LEFT JOIN dtVerify ON vrUsername = m.mbrUsername";
    $sql .= " INNER JOIN msCountry ON countryID = m.mbrCountry";
    $sql .= " INNER JOIN (SELECT * FROM Transaction WHERE trID = (SELECT trID FROM Transaction WHERE trUsername='". $username . "' ORDER BY trDate DESC LIMIT 1)) t ON m.mbrUsername = t.trUsername ";
    $sql    .= " WHERE m.mbrUsername = '".$username."' AND m.mbrStID = '".$DEF_STATUS_ACTIVE."' ";
    if ($query = $conn->query($sql) ) {
        if ($row = $query->fetch_assoc()){
            $mbrBOD         = date_create($row['mbrBOD']);
            $mbrBOD         = date_format($mbrBOD, "d F Y");
            $mbrDate        = date_create($row['mbrDate']);
            $mbrDate        = date_format($mbrDate, "d F Y");
            $trDate         = date_create($row['trDate']);
            $trDate         = date_format($trDate, "d F Y");
            $ExpiredDate    = date_create($row['ExpiredDate']);
            $ExpiredDate    = date_format($ExpiredDate, "d F Y");
            $returnArrData = array(
                'sponsor'       => $row['spName'].'('.$row['spUsername'].')',
                'mbrUsername'   => $row['mbrUsername'],
                'mbrSponsor'    => $row['mbrSponsor'],
                'mbrFullName'   => $row['mbrFirstName']." ".$row['mbrLastName'],
                'mbrIDN'        => $row['mbrIDN'],
                'mbrEmail'      => $row['mbrEmail'],
                'mbrMobile'     => $row['mbrMobileCode']."-".$row['mbrMobile'],
                'mbrBOD'        => $mbrBOD,
                'mbrAddr'       => $row['mbrAddr'],
                'country'       => $row['countryDesc'],
                'mbrCity'       => $row['mbrCity'],
                'mbrState'      => $row['mbrState'],
                'mbrCountry'    => $row['countryDesc'],
                'mbrDate'       => $mbrDate,
                'trDate'        => $trDate,
                'ExpiredDate'   => $ExpiredDate,
                'refferal'      => 'https://visionea.net/'.$row['mbrUsername']
            );
            return (resultJSON ("success", "", $returnArrData));
        }else{
            return (resultJSON ("error", "fetch_assoc error".$conn->error, ""));
        }
    }else{
        return (resultJSON ("error", "query error".$conn->error, ""));
    }
}

function getStatusUsage($conn, $username){
	global $DEF_STATUS_ACTIVE;
	$sql = "SELECT mbrUsername, DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ) expiredDate, ";
    $sql .= " IF(DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ) >= CURRENT_DATE(), 'active', 'expired') AS mbrStatus FROM dtMember";
    $sql .= " INNER JOIN (";
    $sql .= "     SELECT * FROM Transaction AS t ";
    $sql .= "     WHERE t.trID = (SELECT trID FROM Transaction WHERE trUsername = t.trUsername ORDER BY trDate DESC LIMIT 1)";
    $sql .= "    ) AS t ON t.trUsername = mbrUsername";
    $sql .= " WHERE mbrStID = '".$DEF_STATUS_ACTIVE. "' AND mbrUsername = '". $username . "'";
    $mbrStatus = $ExpiredDate = "";
    $result = $conn->query($sql);
    if ($result->num_rows > 0){
        if ($row = $result->fetch_assoc()){
            $ExpiredDate = $row['expiredDate'];
            $mbrStatus     = $row['mbrStatus'];
            $returnArrData = array(
                'mbrStatus'    => $mbrStatus, //active atau expired
                'ExpiredDate'   => $ExpiredDate
            );
            return (resultJSON ("success", "", $returnArrData));
        }
    }else{
        return (resultJSON ("error", "No Record", ""));
    }
}

function addAccParams($conn, $TAPUsername, $TAPEANum, $TAPPairID, $TAPAccNo, $TAPTakeProfit, $TAPInitOD, $TAPAutoLot, $TAPLotSize, $TAPMaxLayers, $TAPLotMulti, $TAPOnReversal, $TAPStatusEA){
    global $CURRENT_TIME;
	$sql  = " SELECT * FROM dtTradeAccParams";
	$sql .= " WHERE TAPUsername = '".$TAPUsername."' AND TAPEANum = '".$TAPEANum."' AND TAPPairID = '".$TAPPairID."' ";
    $sql .= " AND date(TAPUpdateDate) = date('".$CURRENT_TIME."') ";
	$result = $conn->query($sql);
	if ($result->num_rows >= 5){
		return (resultJSON ("error", "Max update limit has been reached, you can update settings again tommorow", ""));
	}else{
		if ($TAPUsername != "" || $TAPEANum != "" || $TAPPairID != "" || $TAPAccNo != "" || $TAPTakeProfit != "" || $TAPInitOD != "" || $TAPAutoLot != "" || $TAPLotSize != "" || $TAPMaxLayers != "" || $TAPLotMulti != "" || $TAPOnReversal != ""){
	        $TAPID = strtotime("now");
	        $arrData = array(
	            0 => array ('db' => 'TAPID'                     , 'val' => $TAPID),
	            1 => array ('db' => 'TAPUsername'               , 'val' => $TAPUsername),
	            2 => array ('db' => 'TAPEANum'                  , 'val' => $TAPEANum),
	            3 => array ('db' => 'TAPPairID'                 , 'val' => $TAPPairID),
	            4 => array ('db' => 'TAPAccNo'                  , 'val' => $TAPAccNo),
	            5 => array ('db' => 'TAPTakeProfit'             , 'val' => $TAPTakeProfit),
	            6 => array ('db' => 'TAPIniOrderDistance'       , 'val' => $TAPInitOD),
	            7 => array ('db' => 'TAPAutoLotSize'            , 'val' => $TAPAutoLot),
	            8 => array ('db' => 'TAPLotSize'                , 'val' => $TAPLotSize),
	            9 => array ('db' => 'TAPMaxLayers'              , 'val' => $TAPMaxLayers),
	            10 => array ('db' => 'TAPLotMultiplier'         , 'val' => $TAPLotMulti),
	            11 => array ('db' => 'TAPCloseOnReversalOrder'  , 'val' => $TAPOnReversal),
	            12 => array ('db' => 'TAPStatusEA'  			, 'val' => $TAPStatusEA),
	            13 => array ('db' => 'TAPUpdateDate'            , 'val' => 'CURRENT_TIME()')
	        );
	        if (fInsert('dtTradeAccParams', $arrData, $conn)) {
	            //insert success
	            return (resultJSON ("success", "Account Settings Saved", ""));
	        }else {
	            //insert fail
	            return (resultJSON ("error", "Failed saving params setting - ".$conn->error, ""));
	        }
	    }else{
	        return (resultJSON ("error", "Incomplete Data", ""));
	    }
	}
}

function getAccParams($conn, $limit, $username, $EANum, $pairID){
	$sql  = " SELECT * FROM dtTradeAccParams";
	$sql .= " INNER JOIN msPair ON pairID = TAPPairID";
    $sql .= " INNER JOIN msEA on EAID = TAPEANUM";
	$sql .= " WHERE TAPUsername = '".$username."' AND TAPEANum = '".$EANum."' AND TAPPairID = '".$pairID."' ";
    $sql .= ($limit != "all")?"ORDER BY TAPUpdateDate DESC LIMIT ".$limit:"";
	$result = $conn->query($sql);
    $returnArrData = array();
	if ($result->num_rows > 0){
		while ($row=$result->fetch_assoc()){
			$returnArrData[] = array(
				"TAPusername" 				=> $row['TAPUsername'],
				"TAPEAName" 				=> $row['EAName'],
				"TAPpairName" 				=> $row['pairName'],
				"TAPaccNo" 				    => $row['TAPAccNo'],
				"TAPtakeProfit" 			=> $row['TAPTakeProfit'],
				"TAPinitOrderDistance" 	    => $row['TAPIniOrderDistance'],
				"TAPautoLot" 				=> $row['TAPAutoLotSize'],
				"TAPlot"					=> $row['TAPLotSize'],
				"TAPmaxLayer" 				=> $row['TAPMaxLayers'],
				"TAPlotX" 					=> $row['TAPLotMultiplier'],
				"TAPcloseOnReversal" 		=> $row['TAPCloseOnReversalOrder'],
				"TAPstatusEA"				=> $row['TAPStatusEA'],
				"TAPupdateDate"			    => $row['TAPUpdateDate']
			);
		}
		return (resultJSON ("success", "", $returnArrData));
	}else{
		return (resultJSON ("error", "No Record", ""));
	}
}

function getMaxLotSize($conn, $username){
	global $DEF_STATUS_APPROVED;
	$sql  = "SELECT trProUserBeli, SUM(trPDQty) * 0.01 AS maxLotSize";
    $sql .= " FROM trProduct";
    $sql .= " INNER JOIN trProDetail ON trProTransID = trPDTransID ";
    $sql .= " WHERE trProUserBeli = '".$username."' AND trProStatus = '".$DEF_STATUS_APPROVED."' ";
    $sql .= " AND CURRENT_DATE BETWEEN Date(trProActiveDate) AND Date(DATE_ADD(DATE_ADD(trProActiveDate, INTERVAL 12 MONTH), INTERVAL -1 DAY))";
    $sql .= " GROUP BY trProUserBeli";
    $result = $conn->query($sql);
    if ($result->num_rows > 0){
        if ($row = $result->fetch_assoc()){
            // setiap keliapan lot 0.05 maka lotsize menjadi 0.10
            $maxLot     = $row['maxLotSize'];
            $tempMaxLot = $maxLot * 100;
            $mod = fmod($tempMaxLot, 5);
            $lot = floor($tempMaxLot / 5) * 10;
            $maxLot = ($lot + $mod) / 100;
            //-------------------------------------------

            $returnArrData = array(
                'maxLotSize'    => $maxLot
            );
            return (resultJSON ("success", "", $returnArrData));
        }
    }
    return (resultJSON ("error", "No Record", ""));
}

?>