<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");


$isValid = false;
$errmsg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if ($_POST['passwdPost'] == "KataRahasia"){
        $isValid = true;
    }else{ $errmsg = "not auth"; }
}

if (!$isValid){

?>
    <html>
    <head>
        <title></title>
    </head>
    <body>
        <h1>Posting</h1>
        <h3><?php echo $errmsg; ?></h3>
        <form action="postPairing.php" method="POST">
            Secret: <input type="password" name="passwdPost" value="">
            <button>Posting</button>
        </form>
    </body>
    </html>


<?

}else{

$sql = "truncate dtDailyPairing";
$query = $conn->query($sql);
$sql = "truncate dtWeeklyPairing";
$query = $conn->query($sql);
$sql = "truncate dtMatching";
$query = $conn->query($sql);

$arrPostingDate = array("2018-04-07", "2018-04-08","2018-04-09", "2018-04-10", "2018-04-11","2018-04-27", "2018-05-02", "2018-05-03");
foreach ($arrPostingDate as $value){

$postingDate    = $value; //"2018-04-07"; //date("Y-m-d"); //"2018-04-09"; //
//echo $postingDate;
    
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
    SELECT IF( (sumDailyTO - sumWK) > pacFlushOut, pacFlushOut, (sumDailyTO - sumWK)) as dailyTO  FROM //not yet calculate 10% of pairing bonus.
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

/*
	//query for number of Generation
	SELECT mbrUsername , pacMatchingGen FROM dtMember
INNER JOIN 
	(SELECT * FROM Transaction t 
	 WHERE trPacID = ( 
	 SELECT trPacID FROM TRANSACTION 
	 WHERE trUsername= t.trUsername 
	 ORDER BY trDate DESC 
	 LIMIT 1 
	 )) tr on tr.trUsername=mbrUsername  
INNER JOIN msPackage ON pacID = tr.trPacID



//query detail matching break down to level of gen, but hasn't limited by package generation.
SELECT m.mbrUsername, g1.pairDate
, (IF(SUM(g1.pairTO) > 0, SUM(g1.pairTO), 0) + IF(SUM(g2.pairTO) > 0, SUM(g2.pairTO), 0) + IF(SUM(g3.pairTO) > 0, SUM(g3.pairTO), 0) + IF(SUM(g4.pairTO) > 0, SUM(g4.pairTO), 0) + IF(SUM(g5.pairTO) > 0, SUM(g5.pairTO), 0) + IF(SUM(g6.pairTO) > 0, SUM(g6.pairTO), 0) + IF(SUM(g7.pairTO) > 0, SUM(g7.pairTO), 0)  ) as sumMatching
, pacMatchingGen
, g1.mbrUsername as gen1, g2.mbrUsername as gen2, g3.mbrUsername as gen3, g4.mbrUsername as gen4, g5.mbrUsername as gen5, g6.mbrUsername as gen6, g7.mbrUsername as gen7
, SUM(g1.pairTO) as sumGen1, SUM(g2.pairTO) as sumGen2, SUM(g3.pairTO) as sumGen3
, SUM(g4.pairTO) as sumGen4, SUM(g5.pairTO) as sumGen5, SUM(g6.pairTO) as sumGen6, SUM(g7.pairTO) as sumGen7
FROM dtMember m
INNER JOIN
(
    SELECT mbrUsername, pacMatchingGen FROM dtMember
    INNER JOIN 
        (SELECT * FROM Transaction t 
         WHERE trPacID = ( 
         SELECT trPacID FROM TRANSACTION 
         WHERE trUsername= t.trUsername 
         ORDER BY trDate DESC 
         LIMIT 1 
         )) tr on tr.trUsername=mbrUsername  
    INNER JOIN msPackage ON pacID = tr.trPacID
) gen
ON gen.mbrUsername = m.mbrUsername
LEFT JOIN 
	(SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing 
     INNER JOIN dtMember ON mbrUsername=pairUsername) as g1 
     ON  g1.mbrSponsor= m.mbrUsername AND g1.mbrUsername <> g1.mbrSponsor
     

	LEFT JOIN 
	(SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing 
     INNER JOIN dtMember ON mbrUsername=pairUsername) as g2 
     ON  g2.mbrSponsor= g1.mbrUsername AND g1.pairDate = g2.pairDate
     
         LEFT JOIN 
        (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing 
         INNER JOIN dtMember ON mbrUsername=pairUsername) as g3
         ON  g3.mbrSponsor= g2.mbrUsername AND g1.pairDate = g2.pairDate
         
         	LEFT JOIN 
            (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing 
             INNER JOIN dtMember ON mbrUsername=pairUsername) as g4
             ON  g4.mbrSponsor= g3.mbrUsername AND g1.pairDate = g2.pairDate
             
                 LEFT JOIN 
                (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing 
                 INNER JOIN dtMember ON mbrUsername=pairUsername) as g5
                 ON  g5.mbrSponsor= g4.mbrUsername AND g1.pairDate = g2.pairDate
                 
                 	LEFT JOIN 
                    (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing 
                     INNER JOIN dtMember ON mbrUsername=pairUsername) as g6
                     ON  g6.mbrSponsor= g5.mbrUsername AND g1.pairDate = g2.pairDate
                     
                     	LEFT JOIN 
                        (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing 
                         INNER JOIN dtMember ON mbrUsername=pairUsername) as g7
                         ON  g7.mbrSponsor= g6.mbrUsername AND g1.pairDate = g2.pairDate

GROUP BY gen1, gen2, gen3, gen4, gen5, gen6, gen7, g1.pairDate

*/



//$postingDate	= "2018-04-27"; //date("Y-m-d"); //"2018-04-09"; //
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
$sql    .= " SELECT mbrUpline, pairDate, (pairLeft) as pairLeft, (pairRight-pacRightExistPrice) as pairRight FROM (";
$sql	.= " SELECT mbrUpline, '". $postingDate . "' as pairDate, SUM(pairLeft) as pairLeft, SUM(pairRight) pairRight, IF(pacLeftExistPrice>0, pacLeftExistPrice, 0) AS pacLeftExistPrice, IF(pacRightExistPrice>0, pacRightExistPrice, 0) AS pacRightExistPrice FROM ";
$sql	.= "  ( ";
$sql	.= "	SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, pacPrice as pairLeft, 0 as pairRight, pacLeftExistPrice, 0 as pacRightExistPrice FROM ( ";
$sql	.= "     SELECT mbrUsername, mbrUpline, mbrPos, (p.pacPrice) as pacPrice, existPackage.pacPrice as pacLeftExistPrice, t.trDate FROM Transaction t ";
$sql	.= "     INNER JOIN dtMember on t.trUsername=mbrUsername ";
$sql	.= "     INNER JOIN msPackage AS p on pacID=t.trPacID ";

$sql    .= "     LEFT JOIN ( ";
$sql    .= "              SELECT trUsername, trPacID, IF(pacPrice > 0, pacPrice, 0) AS pacPrice FROM Transaction t  ";
$sql    .= "              INNER JOIN msPackage ON pacID = trPacID ";
$sql    .= "                  WHERE trPacID = (  ";
$sql    .= "                      SELECT trPacID FROM Transaction  ";
$sql    .= "                      WHERE trUsername= t.trUsername AND DATE(trDate) < '".$postingDate."' ";
$sql    .= "                      ORDER BY trDate DESC  ";
$sql    .= "                      LIMIT 1  ";
$sql    .= "                  ) ";
$sql    .= "              ) existPackage ON existPackage.trUsername = mbrUsername ";

$sql	.= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
$sql	.= "     AND mbrPos = 'l' "; //LEFT NODE
$sql	.= "     ORDER BY t.trDate DESC, mbrDate DESC ";
$sql	.= "   	) trDailyLeft";
$sql	.= " UNION  ";
$sql	.= "   SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, 0 as pairLeft, pacPrice as pairRight, 0 as pacLeftExistPrice, pacRightExistPrice FROM ( ";
$sql	.= "     SELECT mbrUsername, mbrUpline, mbrPos,  (p.pacPrice) as pacPrice, existPackage.pacPrice as pacRightExistPrice, t.trDate FROM Transaction t ";
$sql	.= "     INNER JOIN dtMember on t.trUsername=mbrUsername ";
$sql	.= "     INNER JOIN msPackage AS p on pacID=t.trPacID ";

$sql    .= "     LEFT JOIN ( ";
$sql    .= "              SELECT trUsername, trPacID, IF(pacPrice > 0, pacPrice, 0) AS pacPrice FROM Transaction t  ";
$sql    .= "              INNER JOIN msPackage ON pacID = trPacID ";
$sql    .= "                  WHERE trPacID = (  ";
$sql    .= "                      SELECT trPacID FROM Transaction  ";
$sql    .= "                      WHERE trUsername= t.trUsername AND DATE(trDate) < '".$postingDate."' ";
$sql    .= "                      ORDER BY trDate DESC  ";
$sql    .= "                      LIMIT 1  ";
$sql    .= "                  ) ";
$sql    .= "              ) existPackage ON existPackage.trUsername = mbrUsername ";

$sql	.= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
$sql	.= "     AND mbrPos = 'r' "; //RIGHT NODE
$sql	.= "     ORDER BY t.trDate DESC, mbrDate DESC ";
$sql	.= "   	) trDailyRight ";
$sql	.= " ) as trDaily GROUP BY mbrUpline, dateJoin ";
$sql    .= " ) a ";
//echo $sql . "<br><hr>"; die();

if ($conn->query($sql)){
	//$status	= "success";
	$status = "FASE 1 SUCCESS";
	echo $status . '<br>';

}else{
	$errMsg	= "ERROR: FASE 1 - " . $conn->error;
	fPrintErr($errMsg);
	$conn->rollback();
	die();
}


//FASE 2:
//Data for looping and calculate pair bonus from bottom to up 
$sql	 = "SELECT mbrUsername, mbrUpline, mbrPos, mbrDate FROM ";
$sql	.= " ( ";
$sql	.= " 	SELECT mbrUsername, mbrUpline, mbrPos, mbrDate from dtMember ";
$sql	.= " 	WHERE mbrUsername not IN (SELECT trUsername FROM Transaction WHERE DATE(trDate) >= '" . $postingDate . "') ";
$sql	.= " 		UNION ";
$sql	.= " 	SELECT pairUsername, mbrUpline, mbrPos, mbrDate FROM dtDailyPairing INNER JOIN dtMember on pairUsername=mbrUsername ";
$sql	.= " 	WHERE DATE(pairDate) = '" . $postingDate . "' ";
$sql	.= " ) m ";
$sql	.= " WHERE mbrUsername <> mbrUpline "; //loop need to top of the top
$sql	.= " ORDER BY mbrDate DESC";
//echo $sql . "<br><hr>"; //die();
if ($queryMember = $conn->query($sql)){
	while ($rowMember = $queryMember->fetch_assoc()){
		$mbrUsername	= $rowMember["mbrUsername"];
		$mbrUpline		= $rowMember["mbrUpline"];
		$mbrPos			= strtolower($rowMember["mbrPos"]);
		$sqlPair	= "SELECT (pairLeft+pairRight) as totalPair FROM dtDailyPairing ";
		$sqlPair	.= " WHERE pairUsername='" . $mbrUsername . "' AND DATE(pairDate) = '" . $postingDate ."'";
		//echo $sqlPair . "<br>";
		if ($queryPair = $conn->query($sqlPair)){
			if ($rowPair = $queryPair->fetch_assoc()){
				if ($mbrPos == "r"){ //right
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
					fPrintErr($errMsg);
					$conn->rollback();
					die();
				}	
                
			}else{
                //echo $sqlPair;
            }
		}//end if ($queryPair = $conn->query($sqlPair)){
	}//end while
	$status = "FASE 2 SUCCESS";
	echo $status . '<br>';
	//$message = "SUCCESS";


	//$conn->commit();
	
	//$conn->autocommit(false);

    
	//UPDATE WEEKLY PAIRING
	
	//posting everyday, after posting DailyPairing
	//Delete weekly turnonver from dtWeeklyPairing
	
	//1. DELETE EXISTING DATA ON THAT DATE
	$sql = "DELETE from dtWeeklyPairing WHERE wkYearWeek = '" . $yearWeek . "'"; //YEARWEEK('" . $postingDate . "')";
	if ($queryDel = $conn->query($sql)){
		//Delete success
		$status = "FASE 3.1 SUCCESS";
		echo $status . '<br>';
	}else{
		$errMsg	= "ERROR: FASE 3.1 - " . $conn->error;
		fPrintErr($errMsg);
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
	$sql .= "         SELECT trPacID FROM Transaction ";
	$sql .= "         WHERE trUsername= t.trUsername ";
	$sql .= "         ORDER BY trDate DESC ";
	$sql .= "         LIMIT 1 ";
	$sql .= "     )) tr on tr.trUsername=pairUsername ";
	$sql .= " INNER JOIN msPackage ON pacID=tr.trPacID ";
	$sql .= " WHERE pairDate BETWEEN '" . $monday . "' AND '" . $sunday . "' ";
	$sql .= " GROUP BY pairUsername ";

	if ($queryDel = $conn->query($sql)){
		//Posting Weekly Pairing success
		$status = "FASE 3.2 SUCCESS";
		echo $status . '<br>';
	}else{
		$errMsg	= "ERROR: FASE 3.2 - " . $conn->error;
		fPrintErr($errMsg);
		$conn->rollback();
		die();
	}


    //$conn->commit();
        //die();

    //$conn->autocommit(false);

    //Posting Daily TurnOver
    $sql = "UPDATE dtDailyPairing dp set pairTO = ";
    $sql .= " ( ";
    $sql .= "     SELECT IF((sumDailyTO - sumWK) > pacFlushOut, ( IF (pacFlushOut > ((sumDailyTO - sumWK) - todayPair), (pacFlushOut - ((sumDailyTO - sumWK) - todayPair)) * 10/ 100, 0 ) ), ((sumDailyTO - sumWK) * 10/100) ) as dailyTO  FROM ";
    $sql .= "     ( ";
    $sql .= "         SELECT pair.pairUsername, pacFlushOut, IF(sumLeft > sumRight, sumRight, sumLeft) as sumDailyTO, (IFNULL(sumTO, 0) + IFNULL(sumFO, 0)) as sumWk, pair.pairDate, IF (todayPairLeft > todayPairRight, todayPairRight, todayPairLeft) as todayPair";
    $sql .= "         FROM ";
    $sql .= "         ( ";
    $sql .= "             SELECT dailyPairing.pairUsername, SUM(dailyPairing.pairLeft) as sumLeft, SUM(dailyPairing.pairRight) as sumRight, today.pairDate , today.pairLeft AS todayPairLeft, today.pairRight AS todayPairRight FROM  ";
    $sql .= "               (SELECT * FROM dtDailyPairing ORDER BY DATE(pairDate) DESC) dailyPairing ";
    $sql .= "               LEFT JOIN ";
    $sql .= "               (SELECT pairUsername, pairLeft, pairRight, pairDate FROM dtDailyPairing WHERE pairDate='" . $postingDate . "') today on dailyPairing.pairUsername=today.pairUsername ";
    $sql .= "             WHERE DATE(today.pairDate) <= '" . $postingDate . "' ";
    $sql .= "             GROUP BY pairUsername ";
    $sql .= "         ) pair  ";
    $sql .= "         INNER JOIN ";
    $sql .= "         (SELECT * FROM Transaction t ";
    $sql .= "             WHERE trPacID = ( ";
    $sql .= "                 SELECT trPacID FROM Transaction ";
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
    $sql .= "     WHERE  a.pairUsername = dp.pairUsername AND a.pairDate=dp.pairDate AND DATE(a.pairDate) = '" . $postingDate . "' ";
    $sql .= " )  WHERE  DATE(dp.pairDate) = '" . $postingDate . "'";


    //$conn->rollback();
    //echo $sql; //die();

    if ($queryDailyTO = $conn->query($sql)){
        //Posting Update Daily TurnOver success
        $status = "FASE 3.3 SUCCESS";
        echo $status . '<br>';
    }else{
        $errMsg = "ERROR: FASE 3.3 - " . $conn->error;
        fPrintErr($errMsg);
        $conn->rollback();
        die();
    }
    


//$conn->commit();
//die();

	

	$conn->commit();
	echo ("<h2>>>> POSTING PAIRING SUCCESSFULLY <<< <br>Posting Date:" . $postingDate . "<br>Monday - Sunday: " . $monday . " - ". $sunday . "</h2>");


	//POSTING MATCHING _________________________________________________	
	echo ("<P>Start to Posting Matching</P>");
	//fase 4: Posting Matching
	$conn->autocommit(false);

	/*
	$sql = "SELECT m.mbrUsername, g1.pairDate ";
	$sql .= " , ( (IF(SUM(g1.pairTO) > 0, SUM(g1.pairTO), 0) + IF(SUM(g2.pairTO) > 0, SUM(g2.pairTO), 0) + IF(SUM(g3.pairTO) > 0, SUM(g3.pairTO), 0) + IF(SUM( ";
	$sql .= " g4.pairTO) > 0, SUM(g4.pairTO), 0) + IF(SUM(g5.pairTO) > 0, SUM(g5.pairTO), 0) + IF(SUM(g6.pairTO) > 0, SUM(g6.pairTO), 0) + IF(SUM(g7.pairTO) > 0,";
	$sql .= " SUM(g7.pairTO), 0)  ) * 10 / 100 ) as sumMatching ";
	$sql .= " , pacMatchingGen ";
	$sql .= " FROM dtMember m ";
	$sql .= " INNER JOIN ";
	$sql .= " ( ";
	$sql .= "     SELECT mbrUsername, pacMatchingGen FROM dtMember ";
	$sql .= "     INNER JOIN  ";
	$sql .= "         (SELECT * FROM Transaction t  ";
	$sql .= "          WHERE trPacID = (  ";
	$sql .= "          SELECT trPacID FROM TRANSACTION  ";
	$sql .= "          WHERE trUsername= t.trUsername  ";
	$sql .= "          ORDER BY trDate DESC  ";
	$sql .= "          LIMIT 1  ";
	$sql .= "          )) tr on tr.trUsername=mbrUsername   ";
	$sql .= "     INNER JOIN msPackage ON pacID = tr.trPacID ";
	$sql .= " ) gen ";
	$sql .= " ON gen.mbrUsername = m.mbrUsername ";
	$sql .= " LEFT JOIN  ";
	$sql .= " 	(SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing  ";
	$sql .= "      INNER JOIN dtMember ON mbrUsername=pairUsername) as g1  ";
	$sql .= "      ON  g1.mbrSponsor= m.mbrUsername AND g1.mbrUsername <> g1.mbrSponsor  AND gen.pacMatchingGen >= 1 ";
	$sql .= " 	LEFT JOIN  ";
	$sql .= " 	(SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing  ";
	$sql .= "      INNER JOIN dtMember ON mbrUsername=pairUsername) as g2  ";
	$sql .= "      ON  g2.mbrSponsor= g1.mbrUsername AND g2.pairDate = g1.pairDate AND gen.pacMatchingGen >= 2 ";
	$sql .= " 			LEFT JOIN  ";
	$sql .= "         (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing  ";
	$sql .= "          INNER JOIN dtMember ON mbrUsername=pairUsername) as g3 ";
	$sql .= "          ON  g3.mbrSponsor= g2.mbrUsername AND g3.pairDate = g2.pairDate AND gen.pacMatchingGen >= 3 ";
	$sql .= "          		LEFT JOIN  ";
	$sql .= "             (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing  ";
	$sql .= "              INNER JOIN dtMember ON mbrUsername=pairUsername) as g4 ";
	$sql .= "              ON  g4.mbrSponsor= g3.mbrUsername AND g4.pairDate = g3.pairDate AND gen.pacMatchingGen >= 4 ";
	$sql .= " 					LEFT JOIN  ";
	$sql .= "                 (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing  ";
	$sql .= "                  INNER JOIN dtMember ON mbrUsername=pairUsername) as g5 ";
	$sql .= "                  ON  g5.mbrSponsor= g4.mbrUsername AND g5.pairDate = g4.pairDate AND gen.pacMatchingGen >= 5 ";
	$sql .= " 					   	LEFT JOIN  ";
	$sql .= "                     (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing  ";
	$sql .= "                      INNER JOIN dtMember ON mbrUsername=pairUsername) as g6 ";
	$sql .= "                      ON  g6.mbrSponsor= g5.mbrUsername AND g6.pairDate = g5.pairDate AND gen.pacMatchingGen >= 6 ";
	$sql .= " 					       	LEFT JOIN  ";
	$sql .= "                         (SELECT mbrUsername, mbrSponsor, pairDate, pairTO FROM dtDailyPairing  ";
	$sql .= "                          INNER JOIN dtMember ON mbrUsername=pairUsername) as g7 ";
	$sql .= " 				           ON  g7.mbrSponsor= g6.mbrUsername AND g7.pairDate = g6.pairDate AND gen.pacMatchingGen >= 7 ";
	$sql .= " WHERE DATE(g1.pairDate) ='". $postingDate. "'"
	$sql .= " GROUP BY m.mbrUsername, g1.pairDate ";
    */

    $postingTimeStamp = $postingDate . " " . date("h:i:s"); // " 23:00:00";
    $sql = "SELECT mbrUsername FROM dtMember ORDER BY DATE(mbrDate) ASC";
    $query = $conn->query($sql);
    while ($row = $query->fetch_assoc()) {
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
        


        /*
        if($rowGen = $queryGen->fetch_assoc()){
            $table = "dtMatching";
            $arrData = array(
                    0 => array ("db" => "mtchUsername"     , "val" => $),
                    1 => array ("db" => ""   , "val" => $),
                    2 => array ("db" => ""      , "val" => $),
                    3 => array ("db" => ""         , "val" => "CURRENT_TIME()"),
                    4 => array ("db" => ""       , "val" => $)
                    );
            fInsert($table, $arrData, $conn);
        }
        */
    }
    $status = "FASE 4.1 SUCCESS";
    echo $status . '<br>';

    $sql = "DELETE FROM dtMatching WHERE mtchAmount = 0";
    if ($query = $conn->query($sql)){
        //delete empty (mtchAmount = 0) records successfully
         $status = "FASE 4.2 SUCCESS";
        echo $status . '<br>';
    }else{
        $errMsg = "ERROR: FASE 4.2 - " . $conn->error;
        fPrintErr($errMsg);
        $conn->rollback();
        die();
    }
    
	$conn->commit();
	echo ('<h2>>>> POSTING MATCHING SUCCESSFULLY <<< </h2>');
    echo ('<h1>>>> ALL POSTED SUCCESSFULLY <<< </h1>');

    //$query->close();
    
	
}else{
	$errMsg	= "ERROR: FASE 2 - " . $conn->error;
	fPrintErr ($errMsg);
	$conn->rollback();
	die();
}


}//end foreach

}


fCloseConnection($conn);
?>