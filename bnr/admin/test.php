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


//$arrPostingDate = array("2018-04-07", "2018-04-08","2018-04-09", "2018-04-10", "2018-04-11","2018-04-27", "2018-05-02", "2018-05-03");
$arrPostingDate = array("2018-07-05");
foreach ($arrPostingDate as $value){

$postingDate    = $value; //"2018-04-07"; //date("Y-m-d"); //"2018-04-09"; //
$yearWeek		= date('YW', strtotime($postingDate)); //W:dayOfTheYear
$dayOfWeek 		= date('w', strtotime($postingDate));   //w:dayOfWeek

/*
	$sql    = " SELECT mbrUpline, pairDate, (pairLeft) as pairLeft, (pairRight-pacRightExistPrice) as pairRight FROM (";
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
        fPrint ($sql);
        */

         //Posting Daily TurnOver
            //$sql = "UPDATE dtDailyPairing dp set pairTO = ";
            //$sql .= " ( ";
             $sql = "";
            $sql .= "     SELECT pairUsername, IF((sumDailyTO - sumWK) > pacFlushOut, ( IF (pacFlushOut > ((sumDailyTO - sumWK) - todayPair), (pacFlushOut - ((sumDailyTO - sumWK) - todayPair)) * 10/ 100, 0 ) ), ((sumDailyTO - sumPrevPairTO) * 10/100) ) as dailyTO  FROM ";
            $sql .= "     ( ";
            $sql .= "         SELECT pair.pairUsername, pacFlushOut, IF(sumLeft > sumRight, sumRight, sumLeft) as sumDailyTO, (IFNULL(sumTO, 0) + IFNULL(sumFO, 0)) as sumWk, pair.pairDate, IF (todayPairLeft > todayPairRight, todayPairRight, todayPairLeft) as todayPair, sumPrevPairTO";
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
            $sql .= "           INNER JOIN (";
            $sql .= "               SELECT pairUsername, (SUM(pairTO) * 10 ) AS sumPrevPairTO FROM dtDailyPairing ";
            $sql .= "               WHERE pairDate < '" . $postingDate . "'";
            $sql .= "               GROUP BY pairUsername";
            $sql .= "            ) pr ON pr.pairUsername=pair.pairUsername";
            $sql .= "         GROUP BY pair.pairUsername ";
            $sql .= "     ) a ";
            //$sql .= "     WHERE  a.pairUsername = dp.pairUsername AND a.pairDate=dp.pairDate AND DATE(a.pairDate) = '" . $postingDate . "' ";
            //$sql .= " )  WHERE  DATE(dp.pairDate) = '" . $postingDate . "'";
            fPrint ($sql);
}
?>