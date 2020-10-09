<?PHP
$q = (isset($_GET["q"]))?$_GET["q"]: "";


$subject = $headline = $desc = "";
if (!empty($_POST)) { 
  include_once("../includes/inc_def.php");
  include_once("../includes/inc_session_admin.php");
  include_once("../includes/inc_conn.php");
  include_once("../includes/inc_functions.php");

  $subject = isset($_POST['subject'])?fValidateSQLFromInput($conn, $_POST["subject"]) : "";
  $headline = isset($_POST['headline'])?fValidateSQLFromInput($conn, $_POST["headline"]) : "";
  $desc = isset($_POST['desc'])?fValidateSQLFromInput($conn, $_POST["desc"]) : "";

  if ($subject != "" && $headline != "" && $desc != ""){
    $arrData = array(
      0 => array ("db" => "piSubject" , "val" => $subject),
      1 => array ("db" => "piHeadLine"  , "val" => $headline),
      2 => array ("db" => "piDesc"   , "val" => $desc),
      3 => array ("db" => "piStatus"   , "val" => $DEF_STATUS_APPROVED),
      4 => array ("db" => "piDate"    , "val" => "CURRENT_TIME()")
      );

    if (!fInsert("dtPublicInfo", $arrData, $conn)) {
      echo (fSendStatusMessage("error", "<b>PublicInfo - </b>" . $conn->error));
      die();
    }else{
      //success
      $msg = "Data saved";
      header("Location: ./?menu=announcement&q=".$msg);
    }
    
    unset($arrData);

  }

}

?>
<?php
$page = (isset($_GET['page']))? $_GET['page'] : 1;
$pageActive = (isset($_GET['pageActive']))? $_GET['pageActive'] : 1;
$numPerPage = $DEF_NUM_PER_PAGE;


//$menu = (isset($_GET['menu']))? $_GET['menu'] : "";
// $subMenu = (isset($_GET['subMenu']))? $_GET['subMenu'] : "active";

function calculateCashBonus($jlhVIP){
  $bonusCash = 0;
  if ($jlhVIP >= 18){
      $bonusCash = 20000000; //20juta
  }else if ($jlhVIP >= 15){
      $bonusCash = 15000000; //15juta
  }else if ($jlhVIP >= 12){
      $bonusCash = 12000000;    //12juta
  }else if ($jlhVIP >= 9){
      $bonusCash = 10000000; //10juta
  }else if ($jlhVIP >= 6){
      $bonusCash = 5000000; //5juta
  }else if ($jlhVIP >= 3){
      $bonusCash = 2000000; //2juta
  }
  return ($bonusCash);
}

?>
<span id="q" title="<?php echo $q; ?>"></span>
<div class="col-sm-12">
  <div class="well">
    <div class="subTitle">Report Promo</div>
      <div class="tab-content">
        <div class="tab-pane fade in active">
          <h3>Spectacular Promo</h3>
          <?php 
              global $DEF_STATUS_ACTIVE;
              $PERIODE_START  = '2019.11.01';
              $PERIODE_END    = '2019.12.31';

              $sql = "SELECT count(*) totalRec FROM (";
              $sql .= "SELECT mbrSponsor FROM dtMember as m INNER JOIN Transaction ON mbrUsername = trUsername ";
              $sql .= " WHERE "; 
              $sql .= " trPacID = 'VIP' and mbrStID = '$DEF_STATUS_ACTIVE'";
              $sql .= " AND DATE(mbrDate) BETWEEN '$PERIODE_START' AND '$PERIODE_END'";
              $sql .= " AND DATE(trDate) BETWEEN '$PERIODE_START' AND '$PERIODE_END'";
              $sql .= " AND m.mbrSponsor !='visionea' AND m.mbrSponsor != 'vea-l801' AND m.mbrSponsor != 'vea-l802'";
              $sql .= " GROUP BY m.mbrSponsor";
              $sql .= " ) a ";
              //echo $sql;
              $query = $conn->query($sql);
              $row = $query->fetch_assoc();
              $totalRec = $row['totalRec'];

              $numPages = ceil ($totalRec / $numPerPage); 
              $pageActive = ($pageActive<1)?1:$pageActive;    
              $startRec = ($pageActive-1) * $numPerPage;

              
              $sql = "SELECT m.mbrSponsor, count(*) AS jlhVIP, sp.mbrFirstName, CONCAT(sp.mbrMobileCode, sp.mbrMobile) AS mobile, sp.mbrEmail FROM dtMember m INNER JOIN Transaction ON mbrUsername = trUsername ";
              $sql .= " INNER JOIN dtMember sp ON m.mbrSponsor=sp.mbrUsername";
              $sql .= " WHERE "; 
              //mbrSponsor = '$username' AND ";
              $sql .= " trPacID = 'VIP' and m.mbrStID = '$DEF_STATUS_ACTIVE'";
              $sql .= " AND DATE(m.mbrDate) BETWEEN '$PERIODE_START' AND '$PERIODE_END'";
              $sql .= " AND DATE(trDate) BETWEEN '$PERIODE_START' AND '$PERIODE_END'";
              $sql .= " AND m.mbrSponsor !='visionea' AND m.mbrSponsor != 'vea-l801' AND m.mbrSponsor != 'vea-l802'";
              $sql .= " GROUP BY m.mbrSponsor, sp.mbrFirstName, mobile, sp.mbrEmail";
              $sql .= " ORDER BY jlhVIP DESC LIMIT " . $startRec . ", " . $numPerPage;
			        $query = $conn->query($sql);
          ?>
          <div >
            <table class="table table-hover table-striped small">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th># VIP</th>
                        <th>Cash Bonus</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th># VIP</th>
                        <th>Cash Bonus</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                      if ($query->num_rows == 0){
                          echo "<tr><td colspan=7 class='text-center text-primary'>no record</td></tr>";	
                      }
                      $i=0;
                      while ($row = $query->fetch_assoc()){
                    ?>
                    <tr>
                        <td><?php echo ++$i ?></td>
                        <td><?php echo $row["mbrSponsor"] ?></td>
                        <td><?php echo $row["mbrFirstName"] ?></td>
                        <td><?php echo $row["mobile"] ?></td>
                        <td><?php echo $row["mbrEmail"] ?></td>
                        <td><?php echo $row["jlhVIP"] ?></td>
                        <td><?php echo calculateCashBonus($row["jlhVIP"]) ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <!-- pagination -->
            <div class="row text-right">
                <ul class="pagination">
                    <?php 
                        $prev = $next = "";	
                        if ($pageActive <= 1) { $prev = "disabled"; $pagePrev = 1;}else $pagePrev = $pageActive-1;
                        if ($pageActive >= $numPages) { $next = "disabled"; $pageNext = $numPages;}else $pageNext = $pageActive+1;
                    ?>
                    <li class="previous <?php echo $prev ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&pageActive=' . $pagePrev); ?>">Previous</a></li>
                    <?php 
                    for ($i=1; $i<=$numPages; $i++){
                        $active = "";
                        if ($i == $pageActive) $active = "active";
                    echo "<li class='" . $active . "'><a href='./?menu=".$menu."&subMenu=active&pageActive=$i'>$i</a></li>";
                    }
                    ?>
                    <li class="next <?php echo $next ?>"><a href="<?php echo ('./?menu='.$menu.'&subMenu=active&pageActive=' . $pageNext); ?>">Next</a></li>
                </ul>&nbsp;&nbsp;&nbsp;&nbsp;
            </div>  
          </div>
        </div>
      </div>
    </div>
  </div>
</div>