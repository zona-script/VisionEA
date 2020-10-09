<?php
include_once("../includes/inc_def.php");
//include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

//$postingDate    = "2018-05-08"; //date("Y-m-d"); //
//posting previous day (yesterday), posting in the morning (check maintenance schedule)
$postingDate    = date("Y-m-d", strtotime("yesterday")); //"2018-04-09"; //
$yearWeek		= date('YW', strtotime($postingDate)); //W:dayOfTheYear
$dayOfWeek 		= date('w', strtotime($postingDate));   //w:dayOfWeek
//fPrint ($postingDate);
//fPrint($yearWeek);
//fPrint($dayOfWeek);
//die();
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
    }
}

if ($act == "posting" && $cat == "pair" && $isAuth==true){
    if (fIsMaintenance()){
    
        //maintenance schedule
        //fPrint($postingDate);

        //START POSTING________________________________
         $conn->autocommit(false);

        //FASE 1:
        //Take data from Transaction and put Left and Right omset in a row
        //Insert into to dtDailyPairing
        $sql     = "INSERT INTO dtDailyPairing (pairUsername, pairDate, pairLeft, pairRight) ";
        $sql    .= " SELECT mbrUpline, pairDate, (pairLeft) as pairLeft, (pairRight-pacRightExistPrice) as pairRight FROM (";
        $sql    .= " SELECT mbrUpline, '". $postingDate . "' as pairDate, SUM(pairLeft) as pairLeft, SUM(pairRight) pairRight, IF(pacLeftExistPrice>0, pacLeftExistPrice, 0) AS pacLeftExistPrice, IF(pacRightExistPrice>0, pacRightExistPrice, 0) AS pacRightExistPrice FROM ";
        $sql    .= "  ( ";
        $sql    .= "    SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, pacPrice as pairLeft, 0 as pairRight, pacLeftExistPrice, 0 as pacRightExistPrice FROM ( ";
        $sql    .= "     SELECT mbrUsername, mbrUpline, mbrPos, (p.pacPrice) as pacPrice, existPackage.pacPrice as pacLeftExistPrice, t.trDate FROM Transaction t ";
        $sql    .= "     INNER JOIN dtMember on t.trUsername=mbrUsername ";
        $sql    .= "     INNER JOIN msPackage AS p on pacID=t.trPacID ";

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

        $sql    .= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
        $sql    .= "     AND mbrPos = 'l' "; //LEFT NODE
        $sql    .= "     ORDER BY t.trDate DESC, mbrDate DESC ";
        $sql    .= "    ) trDailyLeft";
        $sql    .= " UNION  ";
        $sql    .= "   SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, 0 as pairLeft, pacPrice as pairRight, 0 as pacLeftExistPrice, pacRightExistPrice FROM ( ";
        $sql    .= "     SELECT mbrUsername, mbrUpline, mbrPos,  (p.pacPrice) as pacPrice, existPackage.pacPrice as pacRightExistPrice, t.trDate FROM Transaction t ";
        $sql    .= "     INNER JOIN dtMember on t.trUsername=mbrUsername ";
        $sql    .= "     INNER JOIN msPackage AS p on pacID=t.trPacID ";

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

        $sql    .= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
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
        $sql    .= "    WHERE mbrUsername not IN (SELECT trUsername FROM Transaction WHERE DATE(trDate) >= '" . $postingDate . "') ";
        $sql    .= "        UNION ";
        $sql    .= "    SELECT pairUsername, mbrUpline, mbrPos, mbrDate FROM dtDailyPairing INNER JOIN dtMember on pairUsername=mbrUsername ";
        $sql    .= "    WHERE DATE(pairDate) = '" . $postingDate . "' ";
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
            /*

            //posting everyday, after delete existing current week records (purpose like insert/update)
            //insert weekly turnover into dtWeeklyPairing
            $sql = "INSERT INTO dtWeeklyPairing ";
            //$sql .= " SELECT YEARWEEK('" . $postingDate . "') as wkYearWeek, pairUsername, ";
            $sql .= " SELECT '".$yearWeek."' as wkYearWeek, pairUsername, ";
            $sql .= "   SUM(w.pairLeft) as sumLeft,  ";
            $sql .= "   SUM(w.pairRight) as sumRight,  ";
            $sql .= "   IF (SUM(w.pairLeft) < SUM(w.pairRight),  ";
            $sql .= "     IF (SUM(w.pairLeft) > pacFlushOut, pacFlushOut, SUM(w.pairLeft)),  ";
            $sql .= "     IF (SUM(w.pairRight > pacFlushout), pacFlushOut, SUM(w.pairRight)) ";
            $sql .= "   ) as wkTurnOver,   ";
            $sql .= "   IF (SUM(w.pairLeft) < SUM(w.pairRight),  ";
            $sql .= "       IF(SUM(w.pairLeft) > pacFlushOut, SUM(w.pairLeft) - pacFlushOut, 0),  ";
            $sql .= "       IF(SUM(w.pairRight)> pacFlushOut, SUM(w.pairRight) - pacFlushOut, 0) ";
            $sql .= "   ) as wkFlushOut, ";
            $sql .= "     pacFlushOut ";
            $sql .= " FROM (SELECT * FROM dtDailyPairing AS w";
                $sql .= " INNER JOIN  ";
                $sql .= "   (SELECT * FROM Transaction t ";
                $sql .= "     WHERE trPacID = ( ";
                $sql .= "         SELECT trPacID FROM Transaction ";
                $sql .= "         WHERE trUsername= t.trUsername ";
                $sql .= "         ORDER BY trDate DESC ";
                $sql .= "         LIMIT 1 ";
                $sql .= "     )) tr on tr.trUsername=w.pairUsername ";
                $sql .= " INNER JOIN msPackage ON pacID=tr.trPacID ";
                $sql .= " WHERE w.pairDate BETWEEN '" . $monday . "' AND '" . $sunday . "' ";
                $sql .= " GROUP BY w.pairUsername ) AS w";

            $sql .= " INNER JOIN (dtDailyPairing ";
            $sql .= " INNER JOIN  ";
            $sql .= "   (SELECT * FROM Transaction t ";
            $sql .= "     WHERE trPacID = ( ";
            $sql .= "         SELECT trPacID FROM Transaction ";
            $sql .= "         WHERE trUsername= t.trUsername ";
            $sql .= "         ORDER BY trDate DESC ";
            $sql .= "         LIMIT 1 ";
            $sql .= "     )) tr on tr.trUsername=pairUsername ";
            $sql .= " INNER JOIN msPackage ON pacID=tr.trPacID ) AS a";


            */
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
            $sql .= "     WHERE trPacID = ( ";
            $sql .= "         SELECT trPacID FROM Transaction ";
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

            //fPrint ($sql);
            //die();

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
            echo ("<P></P><P>Start to Posting Matching</P>");
            //fase 4: Posting Matching

            $conn->autocommit(false);

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
            $errMsg = "ERROR: FASE 2 - " . $conn->error;
            fPrintErr ($errMsg);
            $conn->rollback();
            die();
        }


        //END POSTING__________________________________

    }else{
        //not in maintenance schedule
        fSendToAdmin("CRON-ERROR", "postPairing_Matching.php", "Cron runs not in maintenance schedule");
    }
}else{
    //not authorized
    fSendToAdmin("CRON-ERROR", "postPairing_Matching.php", "Not authorized - " . "$act == 'posting' && $cat == 'pair'" . "SQL: " . $sql);
}

fCloseConnection($conn);
?>