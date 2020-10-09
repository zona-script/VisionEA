<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");



//Insert Success, New Member initial pairing
//NB: change Date and company node.

/*
INSERT INTO dtDailyPairing (pairUsername, pairDate, pairLeft, pairRight)
SELECT mbrUpline, dateJoin, SUM(pairLeft) pairLeft, SUM(pairRight) pairRight FROM
(
SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, pacPrice as pairLeft, 0 as pairRight FROM (
    SELECT mbrUsername, mbrUpline, mbrPos, pacPrice, t.trDate FROM Transaction t
    INNER JOIN dtMember on t.trUsername=mbrUsername
    INNER JOIN msPackage on pacID=t.trPacID
    WHERE mbrStID='8' AND DATE(t.trDate) = '2018-04-06' AND mbrUsername <> 'visionea'
    AND mbrPos = 'l'
    ORDER BY t.trDate DESC, mbrDate DESC
 ) trDailyLeft
UNION 
 SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, 0 as pairLeft, pacPrice as pairRight FROM (
    SELECT mbrUsername, mbrUpline, mbrPos, pacPrice, t.trDate FROM Transaction t
    INNER JOIN dtMember on t.trUsername=mbrUsername
    INNER JOIN msPackage on pacID=t.trPacID
    WHERE mbrStID='8' AND DATE(t.trDate) = '2018-04-06' AND mbrUsername <> 'visionea'
    AND mbrPos = 'r'
    ORDER BY t.trDate DESC, mbrDate DESC
 ) trDailyRight
) as trDaily GROUP BY mbrUpline, dateJoin


//Data for looping and calculate pair bonus from bottom to up 
SELECT mbrUsername, mbrUpline, mbrPos from dtMember
WHERE mbrUsername not IN (SELECT pairUsername FROM dtDailyPairing WHERE DATE(pairDate) = '2018-04-08')
	UNION
SELECT  trUsername, mbrUpline, mbrPos FROM Transaction INNER JOIN dtMember on trUsername=mbrUsername 
WHERE trDate >= "2018-04-06 00:00:00" AND trDate <= "2018-04-08 23:59:00" 

//or
SELECT mbrUsername, mbrUpline, mbrPos, mbrDate FROM
(
SELECT mbrUsername, mbrUpline, mbrPos, mbrDate from dtMember
WHERE mbrUsername not IN (SELECT pairUsername FROM dtDailyPairing WHERE DATE(pairDate) = '2018-04-08')
UNION
SELECT  trUsername, mbrUpline, mbrPos, mbrDate FROM Transaction INNER JOIN dtMember on trUsername=mbrUsername 
WHERE DATE(trDate) = '2018-04-06'
) m
ORDER BY mbrDate DESC



//union success
SELECT mbrUpline, dateJoin, SUM(pairLeft) pairLeft, SUM(pairRight) pairRight FROM
(
SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, pacPrice as pairLeft, 0 as pairRight FROM (
    SELECT mbrUsername, mbrUpline, mbrPos, pacPrice, t.trDate FROM Transaction t
    INNER JOIN dtMember on t.trUsername=mbrUsername
    INNER JOIN msPackage on pacID=t.trPacID
    WHERE mbrStID='8' AND DATE(t.trDate) = '2018-04-06' AND mbrUsername <> 'visionea'
    AND mbrPos = 'l'
    ORDER BY t.trDate DESC, mbrDate DESC
 ) trDailyLeft
UNION 
 SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, 0 as pairLeft, pacPrice as pairRight FROM (
    SELECT mbrUsername, mbrUpline, mbrPos, pacPrice, t.trDate FROM Transaction t
    INNER JOIN dtMember on t.trUsername=mbrUsername
    INNER JOIN msPackage on pacID=t.trPacID
    WHERE mbrStID='8' AND DATE(t.trDate) = '2018-04-06' AND mbrUsername <> 'visionea'
    AND mbrPos = 'r'
    ORDER BY t.trDate DESC, mbrDate DESC
 ) trDailyRight
) as trDaily GROUP BY mbrUpline, dateJoin


//insert and update when exist
INSERT INTO dtMember (mbrUsername, mbrPassword, mbrName)
VALUES ('admin14', md5('rahasia'), 'Admin 14')
ON DUPLICATE KEY UPDATE 
mbrName = 'Administrator 14'




//Post Pairing Weekly
SELECT pairUsername,
	IF (SUM(pairLeft) > pacFlushOut, pacFlushOut, SUM(pairLeft)) as sumLeft, 
	IF (SUM(pairRight) > pacFlushOut, pacFlushOut, SUM(pairRight)) as sumRight 
FROM dtDailyPairing
INNER JOIN Transaction on trUsername=pairUsername
?? transaction, there will a upgrade package
INNER join msPackage ON pacID=trPacID
WHERE pairUsername = 'Leader01'
AND pairDate BETWEEN '2018-04-06' AND '2018-04-09'


//gets latest package
SELECT * FROM Transaction t
WHERE trPacID = (
    SELECT trPacID FROM TRANSACTION
    WHERE trUsername= t.trUsername
    ORDER BY trDate DESC
    LIMIT 1
) 


SELECT pairUsername,
	IF (SUM(pairLeft) > pacFlushOut, pacFlushOut, SUM(pairLeft)) as sumLeft, 
	IF (SUM(pairRight) > pacFlushOut, pacFlushOut, SUM(pairRight)) as sumRight 
FROM dtDailyPairing
INNER JOIN 
	(SELECT * FROM Transaction t
    WHERE trPacID = (
        SELECT trPacID FROM TRANSACTION
        WHERE trUsername= t.trUsername
        ORDER BY trDate DESC
        LIMIT 1
    )) tr on tr.trUsername=pairUsername
INNER join msPackage ON pacID=tr.trPacID
WHERE pairDate BETWEEN '2018-04-06' AND '2018-04-09'
GROUP BY pairUsername


//Weekly TurnOver
SELECT YEARWEEK('2018-04-06') as wkYearWeek, pairUsername,
	SUM(pairLeft) as sumLeft, 
	SUM(pairRight) as sumRight, 
    IF (SUM(pairLeft) < SUM(pairRight), IF (SUM(pairLeft) > pacFlushOut, pacFlushOut, SUM(pairLeft)), IF (SUM(pairRight > pacFlushout), pacFlushOut, SUM(pairRight))) as wkTurnOver,
    pacFlushOut
FROM dtDailyPairing
INNER JOIN 
	(SELECT * FROM Transaction t
    WHERE trPacID = (
        SELECT trPacID FROM TRANSACTION
        WHERE trUsername= t.trUsername
        ORDER BY trDate DESC
        LIMIT 1
    )) tr on tr.trUsername=pairUsername
INNER join msPackage ON pacID=tr.trPacID
WHERE pairDate BETWEEN '2018-04-06' AND '2018-04-09'
GROUP BY pairUsername



//===================================================================================================================
//posting everyday, after posting DailyPairing
//Delete weekly turnonver from dtWeeklyPairing
DELETE from dtWeeklyPairing WHERE wkYearWeek = YEARWEEK('2018-04-06')

//posting everyday, after delete existing current week records (purpose like insert/update)
//insert weekly turnover into dtWeeklyPairing
INSERT INTO dtWeeklyPairing
SELECT YEARWEEK('2018-04-06') as wkYearWeek, pairUsername,
	SUM(pairLeft) as sumLeft, 
	SUM(pairRight) as sumRight, 
    IF (SUM(pairLeft) < SUM(pairRight), 
		IF (SUM(pairLeft) > pacFlushOut, pacFlushOut, SUM(pairLeft)), 
		IF (SUM(pairRight > pacFlushout), pacFlushOut, SUM(pairRight))
	) as wkTurnOver,  
	IF (SUM(pairLeft) < SUM(pairRight), 
		IF(SUM(pairLeft) > pacFlushOut, SUM(pairLeft) - pacFlushOut, 0), 
		IF(SUM(pairRight)>pacFlushOut, SUM(pairRight) - pacFlushOut, 0)
	) as wkFlushOut,
    pacFlushOut
FROM dtDailyPairing
INNER JOIN 
	(SELECT * FROM Transaction t
    WHERE trPacID = (
        SELECT trPacID FROM TRANSACTION
        WHERE trUsername= t.trUsername
        ORDER BY trDate DESC
        LIMIT 1
    )) tr on tr.trUsername=pairUsername
INNER JOIN msPackage ON pacID=tr.trPacID
WHERE pairDate BETWEEN '2018-04-06' AND '2018-04-09'
GROUP BY pairUsername


//Posting Daily TurnOver
UPDATE dtDailyPairing dp set pairTO = 
(
    SELECT IF( (sumDailyTO - sumWK) > pacFlushOut, pacFlushOut, (sumDailyTO - sumWK)) as dailyTO  FROM
    (
        SELECT pair.pairUsername, pair.pairDate, pacFlushOut, IF(sumLeft > sumRight, sumRight, sumLeft) as sumDailyTO, (IFNULL(sumTO, 0) + IFNULL(sumFO, 0)) as sumWk
        FROM
        (
            SELECT pairUsername, pairDate, SUM(pairLeft) as sumLeft, SUM(pairRight) as sumRight FROM dtDailyPairing
            WHERE DATE(pairDate) <= '2018-04-08'
            GROUP BY pairUsername, pairDate
        ) pair 
        INNER JOIN
        (SELECT * FROM Transaction t
            WHERE trPacID = (
                SELECT trPacID FROM TRANSACTION
                WHERE trUsername= t.trUsername
                ORDER BY trDate DESC
                LIMIT 1
            )) tr on tr.trUsername=pairUsername
        INNER JOIN msPackage ON pacID=tr.trPacID
        LEFT JOIN 
        (
            SELECT wkMbrUsername, SUM(wkTurnOver) as sumTO, SUM(wkFlushOut) as sumFO 
            FROM dtWeeklyPairing
            WHERE wkYearWeek < '201808'
            GROUP BY wkMbrUsername
        ) wkPair on wkPair.wkMbrUsername = pair.pairUsername
        GROUP BY pair.pairUsername, pair.pairDate
    ) a 
    WHERE a.pairUsername = dp.pairUsername AND DATE(dp.pairDate) = '2018-04-08'
) 




*/


$postingDate	= date("Y-m-d"); //"2018-04-09"; //
$yearWeek		= date('YW', strtotime($postingDate)); //W:dayOfTheYear
$dayOfWeek 		= date('w', strtotime($postingDate));   //w:dayOfWeek

$arrDay 		= array(-6,0, -1,-2,-3,-4,-5);
$monday			= date('Y-m-d', strtotime($postingDate ) + (($arrDay[$dayOfWeek])*86400));
$sunday			= date('Y-m-d', strtotime($postingDate ) + ((6+ $arrDay[$dayOfWeek])*86400));

//$monday = date( 'Y-m-d', strtotime( 'monday this week' ) );
//$sunday = date( 'Y-m-d', strtotime( 'sunday this week' ) );
/*
echo "PostingDate: " . $postingDate . "<br>";
echo "yearWeek: " . $yearWeek . "<br>";
echo "dayOfWeek: " . $dayOfWeek . "<br>";
echo "monday" . $monday . "<br>";
echo "sunday: " . $sunday . "<br>";
die();
*/

$conn->autocommit(false);

//FASE 1:
//Take data from Transaction and put Left and Right omset in a row
//Insert into to dtDailyPairing
$sql	 = "INSERT INTO dtDailyPairing (pairUsername, pairDate, pairLeft, pairRight) ";
$sql	.= " SELECT mbrUpline, '". $postingDate . "', SUM(pairLeft) pairLeft, SUM(pairRight) pairRight FROM ";
$sql	.= "  ( ";
$sql	.= "	SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, pacPrice as pairLeft, 0 as pairRight FROM ( ";
$sql	.= "     SELECT mbrUsername, mbrUpline, mbrPos, pacPrice, t.trDate FROM Transaction t ";
$sql	.= "     INNER JOIN dtMember on t.trUsername=mbrUsername ";
$sql	.= "     INNER JOIN msPackage on pacID=t.trPacID ";
$sql	.= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> '" . $DEF_COMPANY_NODE . "' ";
$sql	.= "     AND mbrPos = 'l' "; //LEFT NODE
$sql	.= "     ORDER BY t.trDate DESC, mbrDate DESC ";
$sql	.= "   	) trDailyLeft ";
$sql	.= " UNION  ";
$sql	.= "   SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, 0 as pairLeft, pacPrice as pairRight FROM ( ";
$sql	.= "     SELECT mbrUsername, mbrUpline, mbrPos, pacPrice, t.trDate FROM Transaction t ";
$sql	.= "     INNER JOIN dtMember on t.trUsername=mbrUsername ";
$sql	.= "     INNER JOIN msPackage on pacID=t.trPacID ";
$sql	.= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> '" . $DEF_COMPANY_NODE . "' ";
$sql	.= "     AND mbrPos = 'r' "; //RIGHT NODE
$sql	.= "     ORDER BY t.trDate DESC, mbrDate DESC ";
$sql	.= "   	) trDailyRight ";
$sql	.= " ) as trDaily GROUP BY mbrUpline, dateJoin ";
//echo $sql . "<br><hr>";
if ($conn->query($sql)){
	//$status	= "success";

}else{
	$errMsg	= "ERROR: FASE 1 - " . $conn->error;
	echo $errMsg;
	$conn->rollback();
	die();
}


//FASE 2:
//Data for looping and calculate pair bonus from bottom to up 
$sql	 = "SELECT mbrUsername, mbrUpline, mbrPos, mbrDate FROM ";
$sql	.= " ( ";
$sql	.= " 	SELECT mbrUsername, mbrUpline, mbrPos, mbrDate from dtMember ";
$sql	.= " 	WHERE mbrUsername not IN (SELECT trUsername FROM Transaction WHERE DATE(trDate) = '" . $postingDate . "') ";
$sql	.= " 		UNION ";
$sql	.= " 	SELECT pairUsername, mbrUpline, mbrPos, mbrDate FROM dtDailyPairing INNER JOIN dtMember on pairUsername=mbrUsername ";
$sql	.= " 	WHERE DATE(pairDate) = '" . $postingDate . "' ";
$sql	.= " ) m ";
$sql	.= " WHERE mbrUsername <> mbrUpline ";
$sql	.= " ORDER BY mbrDate DESC";
//echo $sql . "<br><hr>";
if ($queryMember = $conn->query($sql)){
	while ($rowMember = $queryMember->fetch_assoc()){
		$mbrUsername	= $rowMember["mbrUsername"];
		$mbrUpline		= $rowMember["mbrUpline"];
		$mbrPos			= strtoupper($rowMember["mbrPos"]);
		$sqlPair	= "SELECT (pairLeft+pairRight) as totalPair FROM dtDailyPairing ";
		$sqlPair	.= " WHERE pairUsername='" . $mbrUsername . "' AND DATE(pairDate) = '" . $postingDate ."'";
		//echo $sqlPair . "<br>";
		if ($queryPair = $conn->query($sqlPair)){
			if ($rowPair = $queryPair->fetch_assoc()){
				if ($mbrPos == "R"){ //right
					$sqlIU	 = "INSERT INTO dtDailyPairing (pairUsername, pairDate, pairRight)";
					$sqlIU	.= "	VALUES ('" . $mbrUpline . "', '" . $postingDate. "', '". $rowPair['totalPair'] ."')";
					$sqlIU	.= "	ON DUPLICATE KEY UPDATE ";
					$sqlIU	.= "	pairRight = pairRight + '". $rowPair['totalPair'] ."' ";
				}else{ //left
					$sqlIU	 = "INSERT INTO dtDailyPairing (pairUsername, pairDate, pairLeft)";
					$sqlIU	.= "	VALUES ('" . $mbrUpline . "', '" . $postingDate. "', '". $rowPair['totalPair'] ."')";
					$sqlIU	.= "	ON DUPLICATE KEY UPDATE ";
					$sqlIU	.= "	pairLeft = pairLeft + '". $rowPair['totalPair'] ."' ";
				}
				//echo $sqlIU . "<br><hr>";
				if ($queryIU = $conn->query($sqlIU)){
					//success
				}else{
					$errMsg	= "ERROR: FASE 2.1 - " . $conn->error;
					echo $errMsg;
					$conn->rollback();
					die();
				}	
			}
		}//end if ($queryPair = $conn->query($sqlPair)){
	}//end while
	$status = "POSTING_SUCCESSFULLY";
	//$message = "SUCCESS";
	$conn->commit();
	
	
	$conn->autocommit(false);
	//UPDATE WEEKLY PAIRING
	
	//posting everyday, after posting DailyPairing
	//Delete weekly turnonver from dtWeeklyPairing
	
	//1. DELETE EXISTING DATA ON THAT DATE
	$sql = "DELETE from dtWeeklyPairing WHERE wkYearWeek = '" . $yearWeek . "'"; //YEARWEEK('" . $postingDate . "')";
	if ($queryDel = $conn->query($sql)){
		//Delete success
	}else{
		$errMsg	= "ERROR: FASE 3.1 - " . $conn->error;
		echo $errMsg;
		$conn->rollback();
		die();
	}
	
	//posting everyday, after delete existing current week records (purpose like insert/update)
	//insert weekly turnover into dtWeeklyPairing
	$sql = "INSERT INTO dtWeeklyPairing ";
	//$sql .= " SELECT YEARWEEK('" . $postingDate . "') as wkYearWeek, pairUsername, ";
	$sql .= " SELECT '".$yearWeek."' as wkYearWeek, pairUsername, ";
	$sql .= " 	SUM(pairLeft) as sumLeft,  ";
	$sql .= " 	SUM(pairRight) as sumRight,  ";
	$sql .= "     IF (SUM(pairLeft) < SUM(pairRight),  ";
	$sql .= " 		IF (SUM(pairLeft) > pacFlushOut, pacFlushOut, SUM(pairLeft)),  ";
	$sql .= " 		IF (SUM(pairRight > pacFlushout), pacFlushOut, SUM(pairRight)) ";
	$sql .= " 	) as wkTurnOver,   ";
	$sql .= " 	IF (SUM(pairLeft) < SUM(pairRight),  ";
	$sql .= " 		IF(SUM(pairLeft) > pacFlushOut, SUM(pairLeft) - pacFlushOut, 0),  ";
	$sql .= " 		IF(SUM(pairRight)>pacFlushOut, SUM(pairRight) - pacFlushOut, 0) ";
	$sql .= " 	) as wkFlushOut, ";
	$sql .= "     pacFlushOut ";
	$sql .= " FROM dtDailyPairing ";
	$sql .= " INNER JOIN  ";
	$sql .= " 	(SELECT * FROM Transaction t ";
	$sql .= "     WHERE trPacID = ( ";
	$sql .= "         SELECT trPacID FROM TRANSACTION ";
	$sql .= "         WHERE trUsername= t.trUsername ";
	$sql .= "         ORDER BY trDate DESC ";
	$sql .= "         LIMIT 1 ";
	$sql .= "     )) tr on tr.trUsername=pairUsername ";
	$sql .= " INNER JOIN msPackage ON pacID=tr.trPacID ";
	$sql .= " WHERE pairDate BETWEEN '" . $monday . "' AND '" . $sunday . "' ";
	$sql .= " GROUP BY pairUsername ";

	if ($queryDel = $conn->query($sql)){
		//Posting Weekly Pairing success
	}else{
		$errMsg	= "ERROR: FASE 3.2 - " . $conn->error;
		echo $errMsg;
		$conn->rollback();
		die();
	}


	//Posting Daily TurnOver
	$sql = "UPDATE dtDailyPairing dp set pairTO = ";
	$sql .= " ( ";
	$sql .= "     SELECT IF( (sumDailyTO - sumWK) > pacFlushOut, pacFlushOut, (sumDailyTO - sumWK)) as dailyTO  FROM ";
	$sql .= "     ( ";
	$sql .= "         SELECT pair.pairUsername, pacFlushOut, IF(sumLeft > sumRight, sumRight, sumLeft) as sumDailyTO, (IFNULL(sumTO, 0) + IFNULL(sumFO, 0)) as sumWk ";
	$sql .= "         FROM ";
	$sql .= "         ( ";
	$sql .= "             SELECT pairUsername, SUM(pairLeft) as sumLeft, SUM(pairRight) as sumRight FROM dtDailyPairing ";
	$sql .= "             WHERE DATE(pairDate) <= '" . $postingDate . "' ";
	$sql .= "             GROUP BY pairUsername ";
	$sql .= "         ) pair  ";
	$sql .= "         INNER JOIN ";
	$sql .= "         (SELECT * FROM Transaction t ";
	$sql .= "             WHERE trPacID = ( ";
	$sql .= "                 SELECT trPacID FROM TRANSACTION ";
	$sql .= "                 WHERE trUsername= t.trUsername ";
	$sql .= "                 ORDER BY trDate DESC ";
	$sql .= "                 LIMIT 1 ";
	$sql .= "             )) tr on tr.trUsername=pairUsername ";
	$sql .= "         INNER JOIN msPackage ON pacID=tr.trPacID ";
	$sql .= "         LEFT JOIN  ";
	$sql .= "         ( ";
	$sql .= "             SELECT wkMbrUsername, SUM(wkTurnOver) as sumTO, SUM(wkFlushOut) as sumFO  ";
	$sql .= "             FROM dtWeeklyPairing ";
	$sql .= "             WHERE wkYearWeek < '".$yearWeek."'"; // YEARWEEK('" . $postingDate . "') ";
	$sql .= "             GROUP BY wkMbrUsername ";
	$sql .= "         ) wkPair on wkPair.wkMbrUsername = pair.pairUsername ";
	$sql .= "         GROUP BY pair.pairUsername ";
	$sql .= "     ) a ";
	$sql .= "     WHERE a.pairUsername = dp.pairUsername ";
	$sql .= " )  WHERE DATE(dp.pairDate) = '" . $postingDate . "' ";
	//echo $sql; die();
	

	if ($queryDailyTO = $conn->query($sql)){
		//Posting Update Daily TurnOver success
	}else{
		$errMsg	= "ERROR: FASE 3.3 - " . $conn->error;
		echo $errMsg;
		$conn->rollback();
		die();
	}

	$conn->commit();
	echo (">>> DONE SUCCESSFULLY <<< <br>Posting Date:" . $postingDate . "<br>Monday - Sunday: " . $monday . " - ". $sunday);
	
	
}else{
	$errMsg	= "ERROR: FASE 2 - " . $conn->error;
	echo $errMsg;
	$conn->rollback();
	die();
}



?>