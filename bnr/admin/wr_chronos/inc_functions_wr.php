<?php

function response($status, $message, $array=''){
	$arrData = array(
		"status" 	=> $status,
		"message" 	=> $message,
		"data"		=> $array
	);
	$dataJSON = json_encode($arrData);
	return ($dataJSON);
}

function fDecyptParams($params){
	$len = strlen($params);
    if (fmod($len, 2) == 0) {
        $params = substr($params, 1) . substr($params, 0, 1);
        $params = hex2bin($params);
        $pos = strpos($params, '*');
        $lenCoreParams = substr($params, 0, $pos);
        $coreParams = substr($params, $pos + 1);
        if ($lenCoreParams != strlen($coreParams)) {
            return false;
        }
        return $coreParams;
    }
    return false;
}

function fCekExpiry($username, $conn){
	$sql = "SELECT mbrUsername, DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ) expiredDate, ";
	$sql .= " IF(DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ) >= CURRENT_DATE(), 'active', 'expired') AS status FROM dtMember";
    $sql .= " INNER JOIN (";
    $sql .= "     SELECT * FROM Transaction AS t ";
    $sql .= "     WHERE t.trID = (SELECT trID FROM Transaction WHERE trUsername = t.trUsername ORDER BY trDate DESC LIMIT 1)";
    $sql .= "    ) AS t ON t.trUsername = mbrUsername";
    $sql .= " WHERE mbrStID = '". $GLOBALS['DEF_STATUS_ACTIVE'] . "' AND mbrUsername = '". $username . "'";
    $sql .= " AND DATE_ADD( DATE(mbrDate), INTERVAL (trThn * 12) MONTH ) >= CURRENT_DATE()";
    if ($query = $conn->query($sql)){
        $row= $query->fetch_assoc();
		if ($query->num_rows > 0){
			return ("active");
		}else{
			return ("expired");
		}
    }else{
    	$errMsg = "fCekExpiry: query error ". $conn->error;
		return ($errMsg);
    }

}

function fAddVoucherID($conn, $EANumber, $acc, $pair, $vid, $exp){
	$result = "";
	global $DEF_STATUS_ACTIVE, $DEF_STATUS_ONPROGRESS;
	$sql = "SELECT IFNULL(numofused, 0) AS numofused, vpsid FROM ( ";
	$sql .= " 			SELECT tradeVPS, COUNT(tradeVPS) AS numofused FROM dtTradingAcc WHERE (tradeStID = '". $DEF_STATUS_ACTIVE . "' OR tradeStID = '". $DEF_STATUS_ONPROGRESS . "')";
	$sql .= " 			GROUP BY tradeVPS ";
	$sql .= " 			) acc ";
	$sql .= " 			RIGHT JOIN dtVPS ON vpsid = tradeVPS ";
	$sql .= " WHERE IFNULL(numofused, 0) < 3";  //per VIP, 3 EA
	$sql .= " ORDER BY vpsid ASC, numofused DESC";
	
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		$rowVPS = $query->fetch_assoc();
		$vpsid = 0;
		$vpsid = (int) $rowVPS['vpsid']; //kode/id vps

		$sql = "SELECT tradeUsername, tradeAccNo, tradeStID, tradeEANum, tradeVPS, mbrStID, IFNULL(vid, '-') AS vid FROM dtTradingAcc INNER JOIN dtMember ON tradeUsername = mbrUsername";
		$sql .= " LEFT JOIN (SELECT * FROM dtvoucherid_ea ";
		//WHERE vidExp > DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
		$sql .= " ) AS voucher ON vidAcc=tradeAccNo AND vidEANum=tradeEANum";
		$sql .= " WHERE tradeAccNo='" . $acc . "' AND tradeEANum='" . $EANumber . "' AND tradePair='" . $pair . "'";
		$sql .= " AND tradeStID ='" . $DEF_STATUS_ONPROGRESS . "'";
		$sql .= " AND mbrStID ='" . $DEF_STATUS_ACTIVE . "'";
		//return ($sql);
			//======================================
			// make a correction for "WHERE vidExp > DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)" 
			// because it could be for renew or extend of usage period.
			//======================================

		//echo $sql; die();
		$query = $conn->query($sql);
		if ($query->num_rows > 0){
			$row = $query->fetch_assoc();
			if ( 
				$row['tradeStID'] == $DEF_STATUS_ONPROGRESS
				&& $row['mbrStID'] == $DEF_STATUS_ACTIVE && $vpsid != 0
				&& $row['vid'] == "-") {
                
				$resultExpiry = fCekExpiry($row['tradeUsername'], $conn);
				if ($resultExpiry=="active"){
                    
					//check ea_num and no acc in dtvoucherid_ea
					$sql = "SELECT vid FROM dtvoucherid_ea WHERE vidEANum='". $EANumber . "' AND vidAcc='" . $acc . "' AND vidPair='" . $pair . "'";
					
					$query = $conn->query($sql);
					if ($query->num_rows == 0){
						//Insert new record
						$table = "dtvoucherid_ea";
						$arrData = array(
										0 => array ("db" => "vidEANum"	, "val" => $EANumber),
										1 => array ("db" => "vidAcc"	, "val" => $acc),
										2 => array ("db" => "vid"		, "val" => $vid),	
										3 => array ("db" => "vidPair"	, "val" => $pair),	
										4 => array ("db" => "vidExp"	, "val" => $exp),
										5 => array ("db" => "vidDate"	, "val" => "CURRENT_TIME()")
									);
						$conn->autocommit(false);
						if (!fInsert($table, $arrData, $conn)) {
							//echo (fSendStatusMessage("error", "<b>Record Member - </b>" . $conn->error));
							//GAGAL INSERT
							$conn->rollback();
							return response("error", "failed save voucher");
						}else{ 
							//Jika insert record voucher id BERHASIL, lanjut UPDATE penggunaan VPS.
							$sqlUpdate = "UPDATE dtTradingAcc SET tradeVPS='" . $vpsid . "' WHERE tradeAccNo='" . $acc . "'";
							$sqlUpdate .= " AND tradeEANum='" . $EANumber . "' AND tradePair='" . $pair . "' AND tradeStID ='" . $DEF_STATUS_ONPROGRESS . "'";
							if ($conn->query($sqlUpdate) === TRUE){
								$conn->commit();
								return response("success", "");
							}else{
								$conn->rollback();
								return response("error", "failed update trading account ERROR::1");
							}
						}
					}else{
						return response("error", "Trading Account Used/duplicate");
					}
				}else if ($resultExpiry=="expired"){
					return response("error", "Membership has Expired");
				}else{
					return response("error", $resultExpiry);
				}
				unset($arrData);
			}else{
				if ($row['vid'] != "-" && $row['tradeVPS'] > 0){
					return response("error", "Can't create new Voucher ID, already exist");
				}else if ($row['vid'] != "-" && $row['tradeVPS'] == 0){
					//for member who reuse the same account number
					if ($vpsid > 0){
						$conn->autocommit(false);
						//Update Voucher id
						//update record for dtvoucherid_ea
						$arrData = array("vid" => $vid, "vidExp" => $exp, "vidDate" => "CURRENT_TIME()");
						$arrDataQuery = array("vidEANum" => $EANumber, "vidAcc" => $acc, "vidPair" => $pair);
						if (!fUpdateRecord("dtvoucherid_ea", $arrData, $arrDataQuery, $conn)){
							$conn->rollback();
							return response("error", "failed to update voucher id");
						}else{
							//update voucherid berhasil lanjut update vps
							//Update VPS yang digunakan
							$sqlUpdate = "UPDATE dtTradingAcc SET tradeVPS='" . $vpsid . "' WHERE tradeAccNo='" . $acc . "'";
							$sqlUpdate .= " AND tradeEANum='" . $EANumber . "' AND tradePair='" . $pair . "' AND tradeStID ='" . $DEF_STATUS_ONPROGRESS . "'";
							if ($conn->query($sqlUpdate) === TRUE){
								$conn->commit();
							}else{
								$conn->rollback();
								return response("error", "failed to update trading account ERROR::2");
							}
						}
						unset($arrData);
						unset($arrDataQuery);
					}else{
						return response("error", "VPS NOT AVAILABLE");
					}
				}else{
					return response("error", "Membership/Account not activate");
				}
			}
		}else{
			//$result = "Account not registered yet";
			$result =  fRenewVoucherID($conn, $EANumber, $acc, $pair, $vid, $exp);
			return ($result); //disini jgn gunakan response();
		}
	}else{
		return response("error", "VPS not available / Full");
	}
}


function fRenewVoucherID($conn, $EANumber, $acc, $pair, $vid, $exp){
	$result = "";
	global $DEF_STATUS_ACTIVE, $DEF_STATUS_ONPROGRESS;
	$sql = "SELECT tradeAccNo, tradeStID, tradeEANum, mbrStID, IFNULL(vid, '-') AS vid, tradeVPS FROM dtTradingAcc INNER JOIN dtMember ON tradeUsername = mbrUsername";
	$sql .= " LEFT JOIN (SELECT * FROM dtvoucherid_ea ";
	//WHERE vidExp > DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
	$sql .= " ) AS voucher ON vidAcc=tradeAccNo AND vidEANum=tradeEANum";
	$sql .= " WHERE tradeAccNo='" . $acc . "' AND tradeEANum='" . $EANumber . "' AND tradePair ='" . $pair . "'";
	$sql .= " AND tradeStID ='" . $DEF_STATUS_ACTIVE . "'";
	$sql .= " AND mbrStID ='" . $DEF_STATUS_ACTIVE . "'";
	//return ($sql);
		//======================================
		// make a correction for "WHERE vidExp > DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)" 
		// because it could be for renew or extend of usage period.
		//======================================

	//echo $sql; die();
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		$row = $query->fetch_assoc();
		if ( 
			$row['tradeStID'] == $DEF_STATUS_ACTIVE
			&& $row['mbrStID'] == $DEF_STATUS_ACTIVE 
			&& $row['vid'] == "-" && $row['tradeVPS'] == 0 ) {
			//no vid and vps yet!, do Insert new vid
			echo (fAddVoucherID($conn, $EANumber, $acc, $vid, $exp));
		}else{
			//Update table dtvoucherid_ea
			if ($row['tradeVPS'] > 0 && $row['vid'] == "-"){
				//add record for dtvoucherid_ea, because no record on dtvoucherid_ea but already set VPS by manual previously
				//Insert data to dtvoucherid_ea
				$arrData = array(
							array("db" => "vidEANum", "val" => $EANumber),
							array("db" => "vidAcc", "val" 	=> $acc),
							array("db" => "vid", "val" 		=> $vid),
							array("db" => "vidPair"	, "val" => $pair),	
							array("db" => "vidExp", "val" 	=> $exp),
							array("db" => "vidDate", "val"	=> "CURRENT_TIME()")
							);
				/*
				$arrData = array(
								0 => array ("db" => "vidEANum"	, "val" => $EANumber),
								1 => array ("db" => "vidAcc"	, "val" => $acc),
								2 => array ("db" => "vid"		, "val" => $vid),	
								3 => array ("db" => "vidPair"	, "val" => $pair),	
								4 => array ("db" => "vidExp"	, "val" => $exp),
								5 => array ("db" => "vidDate"	, "val" => "CURRENT_TIME()")
							);
							*/
				if (!fInsert("dtvoucherid_ea", $arrData, $conn)){
					//echo (fSendStatusMessage("error", $conn->error));
					return response("error", "can't create new record for voucher id");
				}
				unset($arrData);
			}else{
				//update record for dtvoucherid_ea
				$arrData = array("vid" => $vid, "vidExp" => $exp, "vidDate" => "CURRENT_TIME()");
				$arrDataQuery = array("vidEANum" => $EANumber, "vidAcc" => $acc, "vidPair" => $pair);
				if (!fUpdateRecord("dtvoucherid_ea", $arrData, $arrDataQuery, $conn)){
					return response("error", "failed to update voucher id");
				}
				unset($arrData);
				unset($arrDataQuery);
			}
		}
	}else{
		return response("error", "No Account to be renewed / Account Not Registered");
	}
	//return ($result);
	return response("success", "voucher renewed");
}


function fGetVoucherCode($conn, $EANumber, $acc, $pair){
	$vid = "";
	global $DEF_STATUS_ACTIVE, $DEF_STATUS_ONPROGRESS;
	
	// $paket = strtolower($pacCode);
	// if (strtolower($pacCode) == "3vip") {
	// 	$paket = "vip";
	// }

	$sql = "SELECT tradeID, IFNULL(vid, '-') as vid, tradePair, trPacID, tradeUsername FROM dtTradingAcc ";
	$sql .= " LEFT JOIN dtvoucherid_ea ON vidEANum=tradeEANum AND vidAcc=tradeAccNo AND vidPair=tradePair";
	$sql .= " INNER JOIN dtMember ON mbrUsername=tradeUsername";
	//$sql .= " INNER JOIN (SELECT trUsername, trPacID FROM Transaction ";
	//$sql .= " 				INNER JOIN dtMember ON mbrUsername = trUsername ";
	//$sql .= " 				WHERE trUsername =mbrUsername AND mbrStID = '" . $DEF_STATUS_ACTIVE . "' AND trPacID='" . $paket . "'";
	//$sql .= " 				ORDER BY trID DESC, trDate DESC ";
	//$sql .= " 				LIMIT 1) AS t ON trUsername = mbrUsername";
	$sql .= " INNER JOIN Transaction ON trUsername = mbrUsername";
	$sql .= " WHERE tradeEANum='" . $EANumber . "' AND tradeAccNo='" . $acc . "'"; //not need to filter trPacID/tradePair can be wrong / previous package, not latest package and different tradePair
	$sql .= " AND mbrStID = '" . $DEF_STATUS_ACTIVE . "' AND (tradeStID='" . $DEF_STATUS_ACTIVE ."' OR tradeStID='" . $DEF_STATUS_ONPROGRESS . "')";
	$sql .= " ORDER BY trDate DESC, vidDate DESC LIMIT 1";
	//echo $sql;
    //return ($sql);
	$query = $conn->query($sql);
	if ($query->num_rows > 0) {
		// output data of each row
		if($row = $query->fetch_assoc()) {
			//if (strtolower($row['trPacID']) == strtolower($paket) && strtolower($row['tradePair']) == strtolower($pair)){
			if (strtolower($row['tradePair']) == strtolower($pair)){
				$vid = $row["vid"];
				if ($vid == "-"){
					return response("error","Please create Voucher ID for this Account. EA:" . $EANumber . ", Pair:" . $pair . ", Acc:" . $acc);
				}else{
					
					// //checking triple vip
					// if (strtolower($pacCode) == "3vip" && $paket=="vip"){
					// 	$username = $row['tradeUsername'];
					// 	$sql = "SELECT * FROM dtTripleVIP";
					// 	$sql .= " WHERE 3vipusername1='" . $username . "' or 3vipusername2='" . $username . "' or 3vipusername3='" . $username . "'";
					// 	$queryTriple = $conn->query($sql);
					// 	if ($queryTriple->num_rows > 0) {
					// 		//Found as TripleVIP
					// 	}else{
					// 		//not found in TripleVIP
					// 		$vid = "ERROR: Not a TripleVIP account";
					// 	}
					// }
				}
			}else{
				return response("error", "Wrong Pair. Pair Chart:" . $pair . "  Pair in DB:" . $row['tradePair']);
			}
		}
	}else{
		//$vid = "ERROR: " . $sql;
		return response("error", "Acc not registered / Wrong Pair/EA");
	}
	
	//return ($vid);
	$array = array("vid" => $vid);
	return response("success", "", $array);
}

function fUpdateStatus($conn, $EANumber, $acc, $pair, $isConnected, $isAutoTrading, $isHistoryOn, $isInvestorAcc, $isAuth, $balance, $floating, $credit, $equity, $lastOPTime){
	$result = "";
	//global $DEF_STATUS_ACTIVE, $DEF_STATUS_ONPROGRESS;

	$sql = "SELECT tradeAccNo FROM dtTradingAcc WHERE tradeEANum ='" . $EANumber . "' AND tradeAccNo='" . $acc . "' AND tradePair='" . $pair . "'";
	$query = $conn->query($sql);
	if ($query->num_rows > 0){
		//$row = $query->fetch_assoc();

		//$sql = "SELECT * FROM dtStateEA";
		//$sql .= " WHERE seaAcc='" . $acc . "' AND seaEA='" . $EANumber . "' AND seaPair='" . $pair . "'";

		$sql   = "INSERT INTO dtStateEA (seaEA, seaAcc, seaPair, seaConn, seaAutoTrade, seaAllHistory, seaInvestor, seaAuth, seaBalance, seaFloating, seaCredit, seaEquity, seaLastOPTime, seaUpdateDate)";
	    $sql  .= "    VALUES ('" . $EANumber . "', '" . $acc. "', '". $pair . "', '" . $isConnected . "', '" . $isAutoTrading . "', '" . $isHistoryOn . "', '" . $isInvestorAcc . "', '" . $isAuth  ."', '". $balance ."', '". $floating ."', '". $credit ."', '". $equity . "', '" . $lastOPTime. "' , CURRENT_TIME())";
	    $sql  .= "    ON DUPLICATE KEY UPDATE ";
	    $sql  .= "    seaConn = '" . $isConnected . "', seaAutoTrade= '" .  $isAutoTrading . "', seaAllHistory = '" . $isHistoryOn . "', seaInvestor ='" . $isInvestorAcc . "', seaAuth='" . $isAuth . "'";
	    
    	$sql  .= ", seaBalance='" . $balance . "', seaFloating='" . $floating . "' , seaCredit='" . $credit. "' , seaEquity='" . $equity . "'";

    	//if ($lastOPTime == "1977.09.18 00:00:00"){
    	    $sql  .= " , seaLastOPTime='" . $lastOPTime . "'";
    	//}
	    $sql  .= ", seaUpdateDate=CURRENT_TIME()";

		//return ($sql);
		if ($query = $conn->query($sql)){
			return response("success", "");
		}else{
			return response("error", "Insert/Update Failure (fUpdateStatus on file inc_functions_wr.php)");
		}
	}else{
		return response("error", "Trading Account, EA, Pair not registered");
	}
}

?>

