<?php 
$DEF_BONUS_10_PAIRING 	= 10; //bonus sebelum mutasi
$DEF_BONUS_5_PAIRING 	= 5;
$postingDate    = "2020-04-30"; //
$mutasiDate = "2020-04-29"; //tgl bonus pairing dari 10% jadi 5%
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
            $sql .= "               SELECT pairUsername, IF(date(pairDate) <= '2020-04-29', (SUM(pairTO) * 10 ), (SUM(pairTO) * 5 ))  AS sumPrevPairTO, pairDate FROM dtDailyPairing  "; // ini update baru
            $sql .= "               WHERE pairDate < '" . $postingDate . "'";
            $sql .= "               GROUP BY pairUsername";
            $sql .= "            ) pr ON pr.pairUsername=pair.pairUsername";
            $sql .= "         GROUP BY pair.pairUsername ";
            $sql .= "     ) a ";
            echo $sql;
?>