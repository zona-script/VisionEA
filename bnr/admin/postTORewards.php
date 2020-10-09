<?php
session_start();
include_once("../includes/inc_def.php");
//include_once("../includes/inc_session_admin.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

//posting previous day (yesterday), posting in the morning (check maintenance schedule)

$postingDate    = date("Y-m-d", strtotime("yesterday")); //"2018-04-09"; //
//$postingDate    = "2019-06-02"; //

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

 if ($act == "posting" && $cat == "torewards" && $isAuth==true){

/*
    // $arrPostingDate = array("2018-04-01", "2018-04-02","2018-04-03", "2018-04-04", "2018-04-05","2018-04-06", "2018-04-07", "2018-04-08","2018-04-09", "2018-04-10", "2018-04-11", "2018-04-22","2018-04-13", "2018-04-14", "2018-04-15","2018-04-16", "2018-04-17", "2018-04-18","2018-04-19", "2018-04-20", "2018-04-21", "2018-04-22","2018-04-23", "2018-04-24", "2018-04-25","2018-04-26", "2018-04-27", "2018-04-28","2018-04-29", "2018-04-30","2018-05-01", "2018-05-02","2018-05-03", "2018-05-04", "2018-05-05","2018-05-06", "2018-05-07", "2018-05-08","2018-05-09", "2018-05-10", "2018-05-11", "2018-05-12","2018-05-13", "2018-05-14", "2018-05-15","2018-05-16", "2018-05-17", "2018-05-18","2018-05-19", "2018-05-20", "2018-05-21", "2018-05-22","2018-05-23", "2018-05-24", "2018-05-25","2018-05-26", "2018-05-27", "2018-05-28","2018-05-29", "2018-05-30","2018-05-31",        "2018-06-01", "2018-06-02","2018-06-03", "2018-06-04", "2018-06-05","2018-06-06", "2018-06-07", "2018-06-08","2018-06-09", "2018-06-10", "2018-06-11", "2018-06-12","2018-06-13", "2018-06-14", "2018-06-15","2018-06-16", "2018-06-17", "2018-06-18","2018-06-19", "2018-06-20", "2018-06-21", "2018-06-22","2018-06-23", "2018-06-24", "2018-06-25","2018-06-26", "2018-06-27", "2018-06-28","2018-06-29", "2018-06-30","2018-07-01", "2018-07-02","2018-07-03", "2018-07-04", "2018-07-05","2018-07-06", "2018-07-07", "2018-07-08","2018-07-09", "2018-07-10", "2018-07-11");
    
    //$arrPostingDate = array("2018-07-12","2018-07-13", "2018-07-14", "2018-07-15","2018-07-16", "2018-07-17", "2018-07-18","2018-07-19", "2018-07-20", "2018-07-21", "2018-07-22","2018-07-23", "2018-07-24", "2018-07-25","2018-07-26", "2018-07-27", "2018-07-28","2018-07-29", "2018-07-30", "2018-07-31");

   //$arrPostingDate = array("2018-08-01", "2018-08-02","2018-08-03", "2018-08-04", "2018-08-05","2018-08-06", "2018-08-07", "2018-08-08","2018-08-09", "2018-08-10", "2018-08-11", "2018-08-12","2018-08-13", "2018-08-14", "2018-08-15","2018-08-16", "2018-08-17", "2018-08-18","2018-08-19", "2018-08-20", "2018-08-21", "2018-08-22","2018-08-23", "2018-08-24", "2018-08-25","2018-08-26", "2018-08-27", "2018-08-28","2018-08-29", "2018-08-30", "2018-08-31","2018-09-01", "2018-09-02","2018-09-03", "2018-09-04", "2018-09-05","2018-09-06", "2018-09-07", "2018-09-08","2018-09-09", "2018-09-10", "2018-09-11", "2018-09-12","2018-09-13", "2018-09-14", "2018-09-15","2018-09-16", "2018-09-17", "2018-09-18","2018-09-19", "2018-09-20", "2018-09-21", "2018-09-22","2018-09-23", "2018-09-24", "2018-09-25","2018-09-26", "2018-09-27", "2018-09-28","2018-09-29", "2018-09-30");

   //$arrPostingDate = array("2018-10-01", "2018-10-02","2018-10-03", "2018-10-04", "2018-10-05","2018-10-06", "2018-10-07", "2018-10-08","2018-10-09", "2018-10-10", "2018-10-11", "2018-10-12","2018-10-13", "2018-10-14", "2018-10-15","2018-10-16", "2018-10-17", "2018-10-18","2018-10-19", "2018-10-20", "2018-10-21", "2018-10-22","2018-10-23", "2018-10-24", "2018-10-25","2018-10-26", "2018-10-27", "2018-10-28","2018-10-29", "2018-10-30", "2018-10-31");

   //$arrPostingDate = array("2018-11-01", "2018-11-02","2018-11-03", "2018-11-04", "2018-11-05","2018-11-06", "2018-11-07", "2018-11-08","2018-11-09", "2018-11-10", "2018-11-11", "2018-11-12","2018-11-13", "2018-11-14", "2018-11-15","2018-11-16", "2018-11-17", "2018-11-18","2018-11-19", "2018-11-20", "2018-11-21", "2018-11-22","2018-11-23", "2018-11-24", "2018-11-25","2018-11-26", "2018-11-27", "2018-11-28","2018-11-29", "2018-11-30","2018-12-01", "2018-12-02","2018-12-03", "2018-12-04", "2018-12-05","2018-12-06", "2018-12-07", "2018-12-08","2018-12-09", "2018-12-10", "2018-12-11", "2018-12-12","2018-12-13", "2018-12-14", "2018-12-15","2018-12-16", "2018-12-17", "2018-12-18","2018-12-19", "2018-12-20", "2018-12-21", "2018-12-22","2018-12-23", "2018-12-24", "2018-12-25","2018-12-26", "2018-12-27", "2018-12-28","2018-12-29", "2018-12-30", "2018-12-31");

    //$arrPostingDate = array("2019-01-01", "2019-01-02", "2019-01-03", "2019-01-04");

    //$arrPostingDate = array("2018-07-25");

    $arrPostingDate = array(
        "2019-11-01", "2019-11-02","2019-11-03", "2019-11-04"
        , "2019-11-05",
        "2019-11-06"
        , "2019-11-07", "2019-11-08","2019-11-09", "2019-11-10", "2019-11-11", "2019-11-12","2019-11-13", "2019-11-14", "2019-11-15","2019-11-16", "2019-11-17", "2019-11-18","2019-11-19", "2019-11-20", "2019-11-21", "2019-11-22","2019-11-23", "2019-11-24", "2019-11-25","2019-11-26", "2019-11-27", "2019-11-28","2019-11-29", "2019-11-30","2019-12-01", "2019-12-02","2019-12-03", "2019-12-04", "2019-12-05","2019-12-06", "2019-12-07", "2019-12-08","2019-12-09", "2019-12-10", "2019-12-11", "2019-12-12","2019-12-13", "2019-12-14", "2019-12-15","2019-12-16", "2019-12-17"
    , "2019-12-18","2019-12-19", "2019-12-20", "2019-12-21", "2019-12-22","2019-12-23", "2019-12-24", "2019-12-25","2019-12-26", "2019-12-27", "2019-12-28","2019-12-29", "2019-12-30", "2019-12-31"
    , "2020-01-01", "2020-01-02", "2020-01-03", "2020-01-04", "2020-01-05", "2020-01-06", "2020-01-07", "2020-01-08", "2020-01-09", "2020-01-10", "2020-01-11", "2020-01-12", "2020-01-13", "2020-01-14", "2020-01-15"
    , "2020-01-16", "2020-01-17", "2020-01-18"
    ////, "2020-01-19", "2020-01-20"
);

foreach ($arrPostingDate as $value){
    $postingDate    = $value; 
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
        $sql = "";
        $sql     = "INSERT INTO dtDailyTORewards (pairTORUsername, pairTORDate, pairTORLeft, pairTORRight) ";
        $sql    .= " SELECT mbrUpline, pairTORDate, (pairTORLeft-pacLeftExistPrice) as pairTORLeft, (pairTORRight-pacRightExistPrice) as pairTORRight FROM (";
        $sql    .= " SELECT mbrUpline, '". $postingDate . "' as pairTORDate, SUM(pairTORLeft) as pairTORLeft, SUM(pairTORRight) pairTORRight, SUM(pacLeftExistPrice) as pacLeftExistPrice, SUM(pacRightExistPrice) as pacRightExistPrice FROM ";

        $sql    .= "  ( ";
        $sql    .= "    SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, pacPrice as pairTORLeft, 0 as pairTORRight, pacLeftExistPrice, 0 as pacRightExistPrice FROM ( ";
        $sql    .= "     SELECT mbrUsername, mbrUpline, mbrPos, (p.pacPrice) as pacPrice, IFNULL(existPackage.pacPrice, 0) as pacLeftExistPrice, t.trDate FROM Transaction t "; 
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
        
        //$sql    .= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
        $sql    .= "     WHERE DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
        $sql    .= "     AND mbrPos = 'l' "; //LEFT NODE
        $sql    .= "     ORDER BY t.trDate DESC, mbrDate DESC ";
        $sql    .= "    ) trDailyLeft";
        $sql    .= " UNION  ";
        $sql    .= "   SELECT mbrUpline, CURRENT_TIMESTAMP dateJoin, 0 as pairTORLeft, pacPrice as pairTORRight, 0 as pacLeftExistPrice, pacRightExistPrice FROM ( ";
        $sql    .= "     SELECT mbrUsername, mbrUpline, mbrPos,  (p.pacPrice) as pacPrice, IFNULL(existPackage.pacPrice, 0) as pacRightExistPrice, t.trDate FROM Transaction t "; 
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

        
        //$sql    .= "     WHERE mbrStID='" . $DEF_STATUS_ACTIVE . "' AND DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
        $sql    .= "     WHERE DATE(t.trDate) = '" . $postingDate . "' AND mbrUsername <> mbrSponsor ";
        $sql    .= "     AND mbrPos = 'r' "; //RIGHT NODE
        $sql    .= "     ORDER BY t.trDate DESC, mbrDate DESC ";
        $sql    .= "    ) trDailyRight ";
        $sql    .= " ) as trDaily GROUP BY mbrUpline, dateJoin ";
        $sql    .= " ) a ";

        //fPrint ($sql);
        //$conn->rollback();
        //die();

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
        //$sql    .= "    WHERE mbrUsername not IN (SELECT trUsername FROM Transaction WHERE DATE(trDate) > '" . $postingDate . "') ";
        $sql    .= "    WHERE DATE(mbrDate) <= '" . $postingDate . "' ";
        $sql    .= "        UNION ";
        $sql    .= "    SELECT pairTORUsername, mbrUpline, mbrPos, mbrDate FROM dtDailyTORewards INNER JOIN dtMember on pairTORUsername=mbrUsername ";
        $sql    .= "    WHERE DATE(pairTORDate) <= '" . $postingDate . "' ";
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
                $sqlPair    = "SELECT (pairTORLeft+pairTORRight) as totalPair FROM dtDailyTORewards ";
                $sqlPair    .= " WHERE pairTORUsername='" . $mbrUsername . "' AND DATE(pairTORDate) = '" . $postingDate ."'";
                //echo $sqlPair . "<br>";

                if ($queryPair = $conn->query($sqlPair)){
                    if ($rowPair = $queryPair->fetch_assoc()){
                        if ($mbrPos == "r"){ //right
                            $sqlIU   = "INSERT INTO dtDailyTORewards (pairTORUsername, pairTORDate, pairTORRight)";
                            $sqlIU  .= "    VALUES ('" . $mbrUpline . "', '" . $postingDate. "', '". $rowPair['totalPair'] ."')";
                            $sqlIU  .= "    ON DUPLICATE KEY UPDATE ";
                            $sqlIU  .= "    pairTORRight = pairTORRight + '". $rowPair['totalPair'] ."' ";
                            
                        }else{ //left
                            $sqlIU   = "INSERT INTO dtDailyTORewards (pairTORUsername, pairTORDate, pairTORLeft)";
                            $sqlIU  .= "    VALUES ('" . $mbrUpline . "', '" . $postingDate. "', '". $rowPair['totalPair'] ."')";
                            $sqlIU  .= "    ON DUPLICATE KEY UPDATE ";
                            $sqlIU  .= "    pairTORLeft = pairTORLeft + '". $rowPair['totalPair'] ."' ";
                            
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


            $conn->commit();
            echo ("<h2>>>> POSTING PAIRING SUCCESSFULLY <<< <br>Posting Date:" . $postingDate . "<br>Monday - Sunday: " . $monday . " - ". $sunday . "</h2>");

        }else{
            $errMsg = "ERROR: FASE 2 - " . $conn->error;
            fPrintErr ($errMsg);
            $conn->rollback();
        }

        //END POSTING__________________________________


    }else{
        //not in maintenance schedule
        fSendToAdmin("CRON-ERROR", "postTORewards.php", "Cron runs not in maintenance schedule");
    }

// } //for each
//    fCloseConnection($conn); //temp
//    die(); //temp

}else{
    //not authorized
    echo ("Not Authorized");
    fSendToAdmin("CRON-ERROR", "postTORewards.php", "Not authorized - " . "$act == 'posting' && $cat == 'pair'" . "SQL: " . $sql);
}

fCloseConnection($conn);
?>