<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$q = "";
if (isset($_GET["q"])){
	$q  = fValidateInput($_GET["q"]);
	if (strcmp($q , 'calculate_achievement') == 0){
		$username  = fValidateInput($_GET["username"]);
		$sql  = "SELECT m.mbrUsername, m.mbrFirstName, m.mbrEmail, IFNULL(hitungpoin.NetLeft,0) AS NetLeft";
		$sql .= " 	, IFNULL(hitungpoin.NetRight,0) AS NetRight, IFNULL(hitungpoin.BalanceTO,0) AS BalanceTO , IFNULL(hitungpoin.directTO,0) AS directTO";
		$sql .= " 	, achievement.*";
		$sql .= " 	FROM dtMember AS m";
		$sql .= "	LEFT JOIN";
		$sql .= " 		(SELECT pairTORUsername, mbrFirstName, mbrEmail, tLeft AS NetLeft, tRight AS NetRight";
		$sql .= " 		,IF(tLeft > tRight, tRight , tLeft) AS BalanceTO, directTO";
		$sql .= " 		FROM";
		$sql .= " 			(SELECT pairTORUsername, mbrFirstName, mbrEmail, SUM(pairTORLeft) AS tLeft, SUM(pairTORRight) AS tRight, directTO";
   		$sql .= " 			FROM dtDailyTORewards";
    	$sql .= " 			LEFT JOIN (SELECT SUM(pacPrice) AS directTO, m.mbrSponsor, sp.mbrFirstName, sp.mbrEmail";
    	$sql .= " 				FROM dtMember m";
        $sql .= " 				INNER JOIN( SELECT * FROM Transaction AS t WHERE trID =(SELECT trID FROM Transaction WHERE trUsername = t.trUsername";
        $sql .= " 					AND (trStatus='" . $DEF_STATUS_NEW . "' OR (trStatus='" . $DEF_STATUS_UPGRADE . "' AND trThn='1') )";
        $sql .= " 							ORDER BY trDate DESC LIMIT 1)) AS t ON m.mbrUsername = trUsername";
    	$sql .= " 				INNER JOIN dtMember sp ON sp.mbrUsername = m.mbrSponsor";
    	$sql .= " 				INNER JOIN msPackage ON pacID = trPacID";
    	$sql .= " 				WHERE ( DATE(m.mbrDate) >= '2019.11.01' AND YEAR(m.mbrDate) <= '2020' AND (YEAR(CURRENT_DATE) = 2019 OR YEAR(CURRENT_DATE) = 2020) ) "; //masih menghitung omset 1nov 2019 dan 2020 dlm 1 thn omset yang sama
    	$sql .= " 				  		OR ( DATE(m.mbrDate) >= '2019.11.01' AND YEAR(m.mbrDate) = YEAR(CURRENT_DATE) ) "; //Mulai thn 2021, omset dihitung per tahun (tahun sblmnya tidak dihitung dlm omset direct)
    	$sql .= " 				GROUP BY sp.mbrUsername";
    	$sql .= " 			) AS t ON mbrSponsor = pairTORUsername";
		
		//$sql .= " 			WHERE wkYearWeek >= '201944'";
		$sql  .= " 				WHERE Date(pairTORDate) >= '2019.11.01'";
		$sql .= " 			GROUP BY pairTORUsername";
		$sql .= " 		) AS a HAVING NetLeft >= 0 AND NetRight >= 0";
		$sql .= " 	) as hitungpoin ON m.mbrUsername = hitungpoin.pairTORUsername";
		$sql .= " 	LEFT JOIN"; 
		$sql .= " 		(SELECT mbrUsername, IFNULL(achRwdID,0) AS arclevel, rwdID AS nextlevel, rwdLeft AS nextLeft, rwdRight AS nextRight, rwdDirect AS nextDirect";
		$sql .= " 		,rwdL1 AS nextL1, rwdL2 AS nextL2, rwdName AS nextRewardName";
       	$sql .= " 		FROM dtMember";
       	$sql .= " 		LEFT JOIN(SELECT achUsername, achRwdID FROM dtArchiever WHERE achUsername = '".$username."' ORDER BY achRwdID DESC LIMIT 1";
        $sql .= " 		) AS archieved ON mbrUsername = archieved.achUsername";
       	$sql .= " 		LEFT JOIN msReward ON rwdID = (IFNULL(archieved.achRwdID, 0) +1)";
       	$sql .= " 	) AS achievement ON m.mbrUsername = achievement.mbrUsername";
		$sql .= " 	WHERE m.mbrUsername = '".$username."' ";
		echo "mau perbaiki sql perhitungan reward"; die();
		$query = $conn->query($sql);
		$l1 	= 0;
		$l2 	= 0;
		if ($row = $query->fetch_assoc()){
			$left 	= $row['NetLeft'];
			$right 	= $row['NetRight'];
			$direct = $row['directTO'];
			// set 0 dulu belum ada fungsi kalkulasi l1 l2
			$arclevel 		= $row['arclevel'];
			$nextlevel 		= $row['nextlevel'];
			$nextLeft		= $row['nextLeft'];
			$nextRight		= $row['nextRight'];
			$nextDirect		= $row['nextDirect'];
			$nextL1 		= $row['nextL1'];
			$nextL2	 		= $row['nextL2'];
			$nextRewardName = strtolower($row['nextRewardName']);
			$persenleft 	= round($left / $nextLeft * 100);
			$persenright 	= round($right / $nextRight * 100);
			$persendirect 	= round($direct / $nextDirect * 100);
			$persenl1 		= $l1; //round($l1 / $nextL1 * 100);
			$persenl2		= $l2; //round($l2 / $nextL2 * 100);
			($persenleft > 99) ?  $persenleft = 100 : $persenleft;
			($persenright > 99) ?  $persenright = 100 : $persenright;
			($persendirect > 99) ?  $persendirect = 100 : $persendirect;	
			($persenl1 > 99) ?  $persenl1 = 100 : $persenl1;
			($persenl2 > 99) ?  $persenl2 = 100 : $persenl2;

			$arrData = array(
				"left" 			=> $left,
				"right" 		=> $right,
				"direct" 		=> $direct,
				"l1" 			=> $l1,
				"l2" 			=> $l2,
				"persenleft" 	=> $persenleft,
				"persenright" 	=> $persenright,
				"persendirect" 	=> $persendirect,
				"persenl1" 		=> $persenl1,
				"persenl2" 		=> $persenl2,
				"nextLeft"		=> $nextLeft,
				"nextRight"		=> $nextRight,
				"nextDirect"	=> $nextDirect,
				"nextL1"		=> $nextL1,
				"nextL2"		=> $nextL2,
				"nextRewardName"=> $nextRewardName,
				"arclevel" 		=> $arclevel,

				"status" 		=> "success",
				"message" 		=> ""	
			);					
		}else {
			$arrData = array(
				"status" 		=> "error",
				"message" 		=> "Calculate On Progress"
			);
		}
		$DataJSON = json_encode($arrData);
		echo $DataJSON; die();
	}
}
?>