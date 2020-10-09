<?php
session_start();
include_once("../includes/inc_def.php");
//include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

//posting previous day (yesterday), posting in the morning (check maintenance schedule)

$postingDate    = date("Y-m-d", strtotime("yesterday")); //"2018-04-09"; //
// $postingDate    = "2020-06-05"; //


//https://visionea.net/bnr/admin/postPairing_Matching.php?act=posting&cat=pair&code=***IN_DB****
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
/*
//truncate dtDailyPairing;
//truncate dtWeeklyPairing;
//truncate dtMatching;

$sql = "truncate dtDailyPairing";
$query = $conn->query($sql);
$sql = "truncate dtWeeklyPairing";
$query = $conn->query($sql);
$sql = "truncate dtMatching";
$query = $conn->query($sql);
*/

if ($act == "posting" && $cat == "pair" && $isAuth==true){

/*
    // $arrPostingDate = array("2018-04-01", "2018-04-02","2018-04-03", "2018-04-04", "2018-04-05","2018-04-06", "2018-04-07", "2018-04-08","2018-04-09", "2018-04-10", "2018-04-11", "2018-04-22","2018-04-13", "2018-04-14", "2018-04-15","2018-04-16", "2018-04-17", "2018-04-18","2018-04-19", "2018-04-20", "2018-04-21", "2018-04-22","2018-04-23", "2018-04-24", "2018-04-25","2018-04-26", "2018-04-27", "2018-04-28","2018-04-29", "2018-04-30","2018-05-01", "2018-05-02","2018-05-03", "2018-05-04", "2018-05-05","2018-05-06", "2018-05-07", "2018-05-08","2018-05-09", "2018-05-10", "2018-05-11", "2018-05-12","2018-05-13", "2018-05-14", "2018-05-15","2018-05-16", "2018-05-17", "2018-05-18","2018-05-19", "2018-05-20", "2018-05-21", "2018-05-22","2018-05-23", "2018-05-24", "2018-05-25","2018-05-26", "2018-05-27", "2018-05-28","2018-05-29", "2018-05-30","2018-05-31",        "2018-06-01", "2018-06-02","2018-06-03", "2018-06-04", "2018-06-05","2018-06-06", "2018-06-07", "2018-06-08","2018-06-09", "2018-06-10", "2018-06-11", "2018-06-12","2018-06-13", "2018-06-14", "2018-06-15","2018-06-16", "2018-06-17", "2018-06-18","2018-06-19", "2018-06-20", "2018-06-21", "2018-06-22","2018-06-23", "2018-06-24", "2018-06-25","2018-06-26", "2018-06-27", "2018-06-28","2018-06-29", "2018-06-30","2018-07-01", "2018-07-02","2018-07-03", "2018-07-04", "2018-07-05","2018-07-06", "2018-07-07", "2018-07-08","2018-07-09", "2018-07-10", "2018-07-11");
    
    //$arrPostingDate = array("2018-07-12","2018-07-13", "2018-07-14", "2018-07-15","2018-07-16", "2018-07-17", "2018-07-18","2018-07-19", "2018-07-20", "2018-07-21", "2018-07-22","2018-07-23", "2018-07-24", "2018-07-25","2018-07-26", "2018-07-27", "2018-07-28","2018-07-29", "2018-07-30", "2018-07-31");

   //$arrPostingDate = array("2018-08-01", "2018-08-02","2018-08-03", "2018-08-04", "2018-08-05","2018-08-06", "2018-08-07", "2018-08-08","2018-08-09", "2018-08-10", "2018-08-11", "2018-08-12","2018-08-13", "2018-08-14", "2018-08-15","2018-08-16", "2018-08-17", "2018-08-18","2018-08-19", "2018-08-20", "2018-08-21", "2018-08-22","2018-08-23", "2018-08-24", "2018-08-25","2018-08-26", "2018-08-27", "2018-08-28","2018-08-29", "2018-08-30", "2018-08-31","2018-09-01", "2018-09-02","2018-09-03", "2018-09-04", "2018-09-05","2018-09-06", "2018-09-07", "2018-09-08","2018-09-09", "2018-09-10", "2018-09-11", "2018-09-12","2018-09-13", "2018-09-14", "2018-09-15","2018-09-16", "2018-09-17", "2018-09-18","2018-09-19", "2018-09-20", "2018-09-21", "2018-09-22","2018-09-23", "2018-09-24", "2018-09-25","2018-09-26", "2018-09-27", "2018-09-28","2018-09-29", "2018-09-30");

   //$arrPostingDate = array("2018-10-01", "2018-10-02","2018-10-03", "2018-10-04", "2018-10-05","2018-10-06", "2018-10-07", "2018-10-08","2018-10-09", "2018-10-10", "2018-10-11", "2018-10-12","2018-10-13", "2018-10-14", "2018-10-15","2018-10-16", "2018-10-17", "2018-10-18","2018-10-19", "2018-10-20", "2018-10-21", "2018-10-22","2018-10-23", "2018-10-24", "2018-10-25","2018-10-26", "2018-10-27", "2018-10-28","2018-10-29", "2018-10-30", "2018-10-31");

   //$arrPostingDate = array("2018-11-01", "2018-11-02","2018-11-03", "2018-11-04", "2018-11-05","2018-11-06", "2018-11-07", "2018-11-08","2018-11-09", "2018-11-10", "2018-11-11", "2018-11-12","2018-11-13", "2018-11-14", "2018-11-15","2018-11-16", "2018-11-17", "2018-11-18","2018-11-19", "2018-11-20", "2018-11-21", "2018-11-22","2018-11-23", "2018-11-24", "2018-11-25","2018-11-26", "2018-11-27", "2018-11-28","2018-11-29", "2018-11-30","2018-12-01", "2018-12-02","2018-12-03", "2018-12-04", "2018-12-05","2018-12-06", "2018-12-07", "2018-12-08","2018-12-09", "2018-12-10", "2018-12-11", "2018-12-12","2018-12-13", "2018-12-14", "2018-12-15","2018-12-16", "2018-12-17", "2018-12-18","2018-12-19", "2018-12-20", "2018-12-21", "2018-12-22","2018-12-23", "2018-12-24", "2018-12-25","2018-12-26", "2018-12-27", "2018-12-28","2018-12-29", "2018-12-30", "2018-12-31");

    //$arrPostingDate = array("2019-01-01", "2019-01-02", "2019-01-03", "2019-01-04");

    //$arrPostingDate = array("2018-07-25");


foreach ($arrPostingDate as $value){
    $postingDate    = $value; 
*/

    $postingDateLastWeek    = date_create($postingDate);
    date_add($postingDateLastWeek, date_interval_create_from_date_string("last week")); 
    $postingDateLastWeek = date_format($postingDateLastWeek, "Y-m-d");
    $yearWeekLastWeek       = date('YW', strtotime($postingDateLastWeek));

    $yearWeek       = date('YW', strtotime($postingDate)); //W:dayOfTheYear
    $dayOfWeek      = date('w', strtotime($postingDate));   //w:dayOfWeek
    //Update $yearWeek bila lbh kecil dari yg sebelumnya.
    if ($yearWeekLastWeek > $yearWeek) {
        $newDate    = date_create($postingDate);
        date_add($newDate, date_interval_create_from_date_string("+". (7-$dayOfWeek . " day"))); 
        $newDate = date_format($newDate, "Y-m-d");
        $yearWeek       = date('YW', strtotime($newDate)); //W:dayOfTheYear
    }
    
    $arrDay         = array(-6,0, -1,-2,-3,-4,-5);
    $monday         = date('Y-m-d', strtotime($postingDate ) + (($arrDay[$dayOfWeek])*86400));
    $sunday         = date('Y-m-d', strtotime($postingDate ) + ((6+ $arrDay[$dayOfWeek])*86400));
    /*
    echo ("posting Date: " . $postingDate . "<br>");
    echo ("last week: " . $postingDateLastWeek . "<br>");
    echo ("yearWeekLastWeek: " . $yearWeekLastWeek . "<br>");
    echo ("yearWeek: " . $yearWeek . "<br>");
    echo ("dayofWeek: " . $dayOfWeek . "<br>");
    echo ("Monday: " . $monday . "<br>");
    echo ("Sunday: " . $sunday . "<br>");
    */

    if (fIsMaintenance()){
        //maintenance schedule
        //fPrint($postingDate);

        //START POSTING________________________________
        
        $conn->autocommit(false);

        $sql     = "INSERT INTO dtDailyPairing (pairUsername, pairDate, pairLeft, pairRight) ";
        $sql    .= " SELECT mbrUpline, pairDate, (pairLeft-pacLeftExistPrice) as pairLeft, (pairRight-pacRightExistPrice) as pairRight FROM (";
        $sql    .= " SELECT mbrUpline, '". $postingDate . "' as pairDate, SUM(pairLeft) as pairLeft, SUM(pairRight) pairRight, IF(SUM(pacLeftExistPrice)>0, SUM(pacLeftExistPrice), 0) AS pacLeftExistPrice, IF(SUM(pacRightExistPrice)>0, SUM(pacRightExistPrice), 0) AS pacRightExistPrice FROM ";
        $sql    .= "  ( ";
        $sql    .= "    SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, pacPrice as pairLeft, 0 as pairRight, pacLeftExistPrice, 0 as pacRightExistPrice FROM ( ";
        $sql    .= "     SELECT mbrUsername, mbrUpline, mbrPos, (p.pacPrice) as pacPrice, IF(renewLeft.trThn > 1, 0 ,existPackage.pacPrice) as pacLeftExistPrice, t.trDate FROM Transaction t "; //add query code 19 april, add if renewLeft
        $sql    .= "     INNER JOIN dtMember on t.trUsername=mbrUsername ";
        $sql    .= "     INNER JOIN msPackage AS p on pacID=t.trPacID ";

        $sql    .= "     LEFT JOIN ( ";
        $sql    .= "              SELECT trUsername, trPacID, IF(pacPrice > 0, pacPrice, 0) AS pacPrice FROM Transaction t  ";
        $sql    .= "              INNER JOIN msPackage ON pacID = trPacID ";
        $sql    .= "                  WHERE trID = (  "; //ubah trPacID menjadi trID
        $sql    .= "                      SELECT trID FROM Transaction  "; //ubah trPacID menjadi trID
        $sql    .= "                      WHERE trUsername= t.trUsername AND DATE(trDate) < '".$postingDate."' ";
        $sql    .= "                      ORDER BY trDate DESC  ";
        $sql    .= "                      LIMIT 1  ";
        $sql    .= "                  ) ";
        $sql    .= "              ) existPackage ON existPackage.trUsername = mbrUsername ";
        
        //add query code 19 april
        $sql    .= "    LEFT JOIN (";
        $sql    .= "        SELECT * FROM Transaction WHERE DATE(trDate) = '".$postingDate."' AND trStatus='" . $DEF_STATUS_UPGRADE . "' AND trThn > 1";
        $sql    .= "        ) renewLeft ON renewLeft.trUsername = mbrUsername ";

        //$sql    .= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
        $sql    .= "     WHERE DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
        $sql    .= "     AND mbrPos = 'l' "; //LEFT NODE
        $sql    .= "     ORDER BY t.trDate DESC, mbrDate DESC ";
        $sql    .= "    ) trDailyLeft";
        $sql    .= " UNION  ";
        $sql    .= "   SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, 0 as pairLeft, pacPrice as pairRight, 0 as pacLeftExistPrice, pacRightExistPrice FROM ( ";
        $sql    .= "     SELECT mbrUsername, mbrUpline, mbrPos,  (p.pacPrice) as pacPrice, IF(renewRight.trThn > 1, 0, existPackage.pacPrice) as pacRightExistPrice, t.trDate FROM Transaction t "; //add query code 19 april, add if renewRight
        $sql    .= "     INNER JOIN dtMember on t.trUsername=mbrUsername ";
        $sql    .= "     INNER JOIN msPackage AS p on pacID=t.trPacID ";

        $sql    .= "     LEFT JOIN ( ";
        $sql    .= "              SELECT trUsername, trPacID, IF(pacPrice > 0, pacPrice, 0) AS pacPrice FROM Transaction t  ";
        $sql    .= "              INNER JOIN msPackage ON pacID = trPacID ";
        $sql    .= "                  WHERE trID = (  "; //ubah trPacID menjadi trID
        $sql    .= "                      SELECT trID FROM Transaction  "; //ubah trPacID menjadi trID
        $sql    .= "                      WHERE trUsername= t.trUsername AND DATE(trDate) < '".$postingDate."' ";
        $sql    .= "                      ORDER BY trDate DESC  ";
        $sql    .= "                      LIMIT 1  ";
        $sql    .= "                  ) ";
        $sql    .= "              ) existPackage ON existPackage.trUsername = mbrUsername ";

        //add query code 19 april
        $sql    .= "    LEFT JOIN (";
        $sql    .= "        SELECT * FROM Transaction WHERE DATE(trDate) = '".$postingDate."' AND trStatus='" . $DEF_STATUS_UPGRADE . "' AND trThn > 1";
        $sql    .= "        ) renewRight ON renewRight.trUsername = mbrUsername ";

        //$sql    .= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
        $sql    .= "     WHERE DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
        $sql    .= "     AND mbrPos = 'r' "; //RIGHT NODE
        $sql    .= "     ORDER BY t.trDate DESC, mbrDate DESC ";
        $sql    .= "    ) trDailyRight ";
        $sql    .= " ) as trDaily GROUP BY mbrUpline, dateJoin ";
        $sql    .= " ) a ";

        //fPrint ($sql);

        if ($conn->query($sql)){
            //$status   = "success";
            $status = "FASE 1 SUCCESS";
            echo $status . '<br>';

        }else{
            $errMsg = "ERROR: FASE 1 - " . $conn->error;
            fPrintErr($errMsg);
            $conn->rollback();
            die();
        }



        //FASE 2:
        //Data for looping and calculate pair bonus from bottom to up 
        $sql     = "SELECT mbrUsername, mbrUpline, mbrPos, mbrDate FROM ";
        $sql    .= " ( ";
        $sql    .= "    SELECT mbrUsername, mbrUpline, mbrPos, mbrDate from dtMember ";
        $sql    .= "    WHERE mbrUsername not IN (SELECT trUsername FROM Transaction WHERE DATE(trDate) > '" . $postingDate . "') ";
        $sql    .= "        UNION ";
        $sql    .= "    SELECT pairUsername, mbrUpline, mbrPos, mbrDate FROM dtDailyPairing INNER JOIN dtMember on pairUsername=mbrUsername ";
        $sql    .= "    WHERE DATE(pairDate) <= '" . $postingDate . "' ";
        $sql    .= " ) m ";
        $sql    .= " WHERE mbrUsername <> mbrUpline "; //loop need to top of the top
        $sql    .= " ORDER BY mbrDate DESC";
        //echo $sql . "<br><hr>"; //die();
        if ($queryMember = $conn->query($sql)){
            //$counter = 0;
            while ($rowMember = $queryMember->fetch_assoc()){
                $mbrUsername    = $rowMember["mbrUsername"];
                $mbrUpline      = $rowMember["mbrUpline"];
                $mbrPos         = strtolower($rowMember["mbrPos"]);
                $sqlPair    = "SELECT (pairLeft+pairRight) as totalPair FROM dtDailyPairing ";
                $sqlPair    .= " WHERE pairUsername='" . $mbrUsername . "' AND DATE(pairDate) = '" . $postingDate ."'";
                //echo $sqlPair . "<br>";

                if ($queryPair = $conn->query($sqlPair)){
                    if ($rowPair = $queryPair->fetch_assoc()){
                        if ($mbrPos == "r"){ //right
                            $sqlIU   = "INSERT INTO dtDailyPairing (pairUsername, pairDate, pairRight)";
                            $sqlIU  .= "    VALUES ('" . $mbrUpline . "', '" . $postingDate. "', '". $rowPair['totalPair'] ."')";
                            $sqlIU  .= "    ON DUPLICATE KEY UPDATE ";
                            $sqlIU  .= "    pairRight = pairRight + '". $rowPair['totalPair'] ."' ";
                            
                        }else{ //left
                            $sqlIU   = "INSERT INTO dtDailyPairing (pairUsername, pairDate, pairLeft)";
                            $sqlIU  .= "    VALUES ('" . $mbrUpline . "', '" . $postingDate. "', '". $rowPair['totalPair'] ."')";
                            $sqlIU  .= "    ON DUPLICATE KEY UPDATE ";
                            $sqlIU  .= "    pairLeft = pairLeft + '". $rowPair['totalPair'] ."' ";
                            
                        }
                        //$counter++;
                        //echo $sqlIU . "<br><hr>";
                        if ($queryIU = $conn->query($sqlIU)){
                            //success
                            //fPrint($sqlIU);
                        }else{
                            $errMsg = "ERROR: FASE 2.1 - " . $conn->error;
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
                $errMsg = "ERROR: FASE 3.1 - " . $conn->error;
                fPrintErr($errMsg);
                $conn->rollback();
                die();
            }
            
            //posting everyday, after delete existing current week records (purpose like insert/update)
            //insert weekly turnover into dtWeeklyPairing
            $sql = "INSERT INTO dtWeeklyPairing ";
            //$sql .= " SELECT YEARWEEK('" . $postingDate . "') as wkYearWeek, pairUsername, ";
            $sql .= "SELECT * FROM (";
            $sql .= " SELECT '".$yearWeek."' as wkYearWeek, a.pairUsername, (sumLeftAll- IFNULL(sumLeft, 0)) AS wkTtlLeft, (sumRightAll- IFNULL(sumRight, 0)) AS wkTtlRight
                , IF( sumLeftAll < sumRightAll
                    , IF((sumLeftAll - IFNULL(sumTO,0)) > pacFlushOut, pacFlushOut, (sumLeftAll - IFNULL(sumTO,0)) )
                    , IF((sumRightAll - IFNULL(sumTO,0)) > pacFlushOut, pacFlushOut, (sumRightAll - IFNULL(sumTO,0)) )
                    ) AS thisWkTO
                , IF( sumLeftAll < sumRightAll
                    , IF((sumLeftAll - IFNULL(sumTO,0)) > pacFlushOut, (sumLeftAll - IFNULL(sumTO,0)) - pacFlushOut, 0 )
                    , IF((sumRightAll - IFNULL(sumTO,0)) > pacFlushOut, (sumRightAll - IFNULL(sumTO,0)) - pacFlushOut, 0 )
                    ) AS thisWkFO
                , pacFlushOut ";
            $sql .= " FROM (SELECT wkMbrUsername, SUM(wkTtlLeft) AS sumLeft, SUM(wkTtlRight) AS sumRight, IF(isnull(SUM(wkTurnOver)), 0, IF(isnull(SUM(wkTurnOver)), 0, SUM(wkTurnOver))) AS sumTO FROM dtWeeklyPairing WHERE wkYearWeek <'".$yearWeek."' GROUP BY wkMbrUsername) wk";

            $sql .= " RIGHT JOIN (SELECT SUM(pairLeft) AS sumLeftAll, SUM(pairRight) AS sumRightAll, pairUsername, pacFlushOut FROM dtDailyPairing ";
            $sql .= " INNER JOIN  ";
            $sql .= "   (SELECT * FROM Transaction t ";
            $sql .= "     WHERE trID = ( "; //ubah trPacID menjadi trID
            $sql .= "         SELECT trID FROM Transaction "; //ubah trPacID menjadi trID
            $sql .= "         WHERE trUsername= t.trUsername ";
            $sql .= "         ORDER BY trDate DESC ";
            $sql .= "         LIMIT 1 ";
            $sql .= "     )) tr on tr.trUsername=pairUsername ";
            $sql .= " INNER JOIN msPackage ON pacID=tr.trPacID ";
            $sql .= " GROUP BY pairUsername ) AS a";
            $sql .= " ON wk.wkMbrUsername = a.pairUsername";
            $sql .= " ) AS A WHERE (wkTtlLeft > 0 OR wkTtlRight > 0)";

            if ($queryDel = $conn->query($sql)){
                //Posting Weekly Pairing success
                $status = "FASE 3.2 SUCCESS";
                echo $status . '<br>';
            }else{
                $errMsg = "ERROR: FASE 3.2 - " . $conn->error;
                fPrintErr($errMsg);
                $conn->rollback();
                die();
            }

            //fPrint ($sql);


            //$conn->commit(); //die();

            //$conn->autocommit(false);

            //Posting Daily TurnOver bonus 10%
            //$sql = "UPDATE dtDailyPairing dp set pairTO = ";
            //$sql .= " ( ";
            $sql = "";
            $sql .= "     SELECT pairUsername, IF((sumDailyTO - sumWK) > pacFlushOut, ( IF (pacFlushOut > ((sumDailyTO - sumWK) - todayPair), (pacFlushOut - ((sumDailyTO - sumWK) - todayPair)) * (".$DEF_BONUS_5_PAIRING."/ 100), 0 ) ), ((sumDailyTO - sumPrevPairTO) * (".$DEF_BONUS_5_PAIRING."/100)) ) as dailyTO  FROM ";
            $sql .= "     ( ";
            $sql .= "         SELECT pair.pairUsername, pacFlushOut, IF(sumLeft > sumRight, sumRight, sumLeft) as sumDailyTO, (IFNULL(sumTO, 0) + IFNULL(sumFO, 0)) as sumWK, pair.pairDate, IF (todayPairLeft > todayPairRight, todayPairRight, todayPairLeft) as todayPair, IFNULL(sumPrevPairTO, 0) AS sumPrevPairTO";
            $sql .= "         FROM ";
            $sql .= "         ( ";
            $sql .= "             SELECT dailyPairing.pairUsername, sumLeft, sumRight, today.pairDate , today.pairLeft AS todayPairLeft, today.pairRight AS todayPairRight FROM  ";
            //$sql .= "               (SELECT * FROM dtDailyPairing ORDER BY DATE(pairDate) DESC) dailyPairing ";
            $sql .= "               (SELECT pairUsername , SUM(pairLeft) AS sumLeft, SUM(pairRight) AS sumRight ";
            $sql .= "                   FROM dtDailyPairing GROUP BY pairUsername ORDER BY DATE(pairDate) DESC) dailyPairing ";
            $sql .= "               LEFT JOIN ";
            $sql .= "               (SELECT pairUsername, pairLeft, pairRight, pairDate FROM dtDailyPairing WHERE pairDate='" . $postingDate . "') today on dailyPairing.pairUsername=today.pairUsername ";
            $sql .= "             WHERE DATE(today.pairDate) <= '" . $postingDate . "' ";
            $sql .= "             GROUP BY pairUsername ";
            $sql .= "         ) pair  ";
            $sql .= "         INNER JOIN ";
            $sql .= "         (SELECT * FROM Transaction t ";
            $sql .= "             WHERE trID = ( "; //ubah trPacID menjadi trID
            $sql .= "                 SELECT trID FROM Transaction "; //ubah trPacID menjadi trID
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
            $sql .= "           LEFT JOIN (";
            $sql .= "               SELECT pairUsername, SUM(pairTO) AS sumPrevPairTO, pairDate"; // ini update baru
            $sql .= "               FROM ( ";
                                        //hitung before mutasi masih pakai bonus 10%
            $sql .= "                   SELECT pairUsername, SUM(pairTO * (100/".$DEF_BONUS_10_PAIRING.")) AS pairTO, pairDate";
            $sql .= "                   FROM dtDailyPairing";
            $sql .= "                   WHERE date(pairDate) < '".$DEF_MUTASI_DATE."' GROUP BY pairUsername ";
            $sql .= "                   UNION";
                                        //hitung after mutasi telah pakai 5%
            $sql .= "                   SELECT pairUsername, SUM(pairTO * (100/".$DEF_BONUS_5_PAIRING.")) AS pairTO, pairDate";
            $sql .= "                   FROM dtDailyPairing";
            $sql .= "                   WHERE date(pairDate) >= '".$DEF_MUTASI_DATE."' GROUP BY pairUsername ";
            $sql .= "               ) AS prevto";
            $sql .= "               WHERE pairDate < '" . $postingDate . "'";
            $sql .= "               GROUP BY pairUsername";
            $sql .= "            ) pr ON pr.pairUsername=pair.pairUsername";
            $sql .= "         GROUP BY pair.pairUsername ";
            $sql .= "     ) a ";
            //$sql .= "     WHERE  a.pairUsername = dp.pairUsername AND a.pairDate=dp.pairDate AND DATE(a.pairDate) = '" . $postingDate . "' ";
            //$sql .= " )";

            $queryDailyTO = $conn->query($sql);
            while ($rowDailyTO = $queryDailyTO->fetch_assoc()){
                $sqlUpdate = "UPDATE dtDailyPairing set pairTO = '". $rowDailyTO['dailyTO'] ."'";
                $sqlUpdate .= " WHERE pairUsername='" . $rowDailyTO['pairUsername'] . "' AND DATE(pairDate)='" . $postingDate . "'";
                if (!$conn->query($sqlUpdate)){
                    $errMsg = "ERROR: FASE 3.3 - " . $conn->error;
                    fPrintErr($errMsg);
                    $conn->rollback();
                    die();
                }

            }


            //fPrint ($sql);
            //die();

            //$conn->rollback();
            //echo $sql; //die();

            /*if ($queryDailyTO = $conn->query($sql)){
                //Posting Update Daily TurnOver success
                $status = "FASE 3.3 SUCCESS";
                echo $status . '<br>';
            }else{
                $errMsg = "ERROR: FASE 3.3 - " . $conn->error;
                fPrintErr($errMsg);
                $conn->rollback();
                die();
            }
            */

            

            $conn->commit();
            echo ("<h2>>>> POSTING PAIRING SUCCESSFULLY <<< <br>Posting Date:" . $postingDate . "<br>Monday - Sunday: " . $monday . " - ". $sunday . "</h2>");

            /*
            //POSTING MATCHING _________________________________________________    
            echo ("<P></P><P>Start to Posting Matching</P>");
            //fase 4: Posting Matching

            $conn->autocommit(false);

            $postingTimeStamp = $postingDate . " " . date("h:i:s"); // " 23:00:00";

            $sqlGen = " INSERT INTO dtMatching (mtchUsername, mtchDate, mtchPair, mtchAmount) ";
            //$sqlGen .= " SELECT genSPUsername, CONCAT(Date(pairDate), ' 19:05:02'), pairUsername, (pairTO * 0.1) AS bnsMatching FROM dtDailyPairing  ";
            $sqlGen .= " SELECT genSPUsername, '".$postingTimeStamp."', pairUsername, (pairTO * 0.1) AS bnsMatching FROM dtDailyPairing  ";
            $sqlGen .= " INNER JOIN dtGenSponsorship ON pairUsername=genMbrUsername ";
            $sqlGen .= " WHERE pairTO > 0 ";
            $sqlGen .= " AND pairUsername in ( ";
            $sqlGen .= "     SELECT genMbrUsername FROM dtGenSponsorship  ";
            $sqlGen .= "     ) ";
            $sqlGen .= " AND Date(pairDate) = '".$postingDate."' ";
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
            $errMsg = "ERROR: FASE 2 - " . $conn->error;
            fPrintErr ($errMsg);
            $conn->rollback();
            die();
        */
        }
        
        //END POSTING__________________________________

    }else{
        //not in maintenance schedule
        fSendToAdmin("CRON-ERROR", "postPairing_Matching.php", "Corn runs not in maintenance schedule");
    }

//} //for each
//    fCloseConnection($conn); //temp
//    die(); //temp

}else{
    //not authorized
    fSendToAdmin("CRON-ERROR", "postPairing_Matching.php", "Not authorized - " . "$act == 'posting' && $cat == 'pair'" . "SQL: " . $sql);
}


fCloseConnection($conn);
?>