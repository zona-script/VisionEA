<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");
include_once("../includes/inc_commission.php");

$q      =  (isset($_GET["q"]))?fValidateInput($_GET["q"]): "";

$username   = $_SESSION['sUserName'];
$currPac = $currPacName = $price = "";
$trThn = 0;

$sql = "SELECT trPacID, pacName ,pacPrice , mbrSponsor, trThn  FROM Transaction INNER JOIN dtMember ON trUsername = mbrUsername INNER JOIN msPackage ON pacID = trPacID WHERE trUsername ='".$username."' ORDER BY trDate DESC LIMIT 1";
$query = $conn->query($sql);
if ($row = $query->fetch_assoc()){
    $currPac = $row["trPacID"];
    $currPacName = $row["pacName"];
    $currPacPrice = $row["pacPrice"];
    $sponsor = $row['mbrSponsor'];
    $trThn = $row['trThn'];

    $sql = "SELECT trPacID FROM Transaction WHERE trUsername ='".$sponsor."' ORDER BY trDate DESC LIMIT 1";
    $query = $conn->query($sql);
    if ($row = $query->fetch_assoc()){
        $pacIDSponsor       = $row["trPacID"];
    }else{
        echo (fSendStatusMessage("error", "Sponsor's Pac ID not found"));
        die();
    }
}


// hitung jumlah voucher tersedia
$numOfVoucher = $VoucherBalance = 0;
$arrVoucher = "";
$myDataObj  = json_decode(fSumAvailableVoucher($username, $conn));
if ($myDataObj->{"status"} == "success"){
    $numOfVoucher = sizeof($myDataObj->data);
    $VoucherBalance = $numOfVoucher * $DEF_VOUCHER_PRICE;
    $arrVoucher = $myDataObj->data;
}

// Hitung Jumlah Wallet
$myDataObj  = json_decode(fGetBalance($username, $conn));
if ($myDataObj->{"status"} == "success"){
    $WalletBalance  = ceil($myDataObj->{'wallet'});
    $numOfWallet    = floor($WalletBalance / $DEF_VOUCHER_PRICE);
}

$msg = "";
//$accNumber = $accName = $accType = $accCode = "";
if (!empty($_POST)) { 
    
    $renewPac = $securityPasswd =  $actUsername = "";

    $actUsername    = $_SESSION["sUserName"];
    $securityPasswd      = isset($_POST["securityPasswd"])? fValidateSQLFromInput($conn, $_POST["securityPasswd"]) : "";
    $renewPac     = isset($_POST["renewPac"])? fValidateInput($_POST["renewPac"]) : "";
    $renewPac     = strtolower($renewPac);

    if ($actUsername != "" && $securityPasswd != "" && $renewPac != ""){

        //Check Security Password
        if (!fCheckSecurityPassword($actUsername, $securityPasswd, $conn)){
          echo (fSendStatusMessage("error", "Security Password not match<br>")); die();
        }else{
            //Get Package Price
            $pacPrice = $numOfVoucherRequired = 0;
            $sql = "SELECT pacPrice FROM msPackage WHERE pacID='" . $renewPac . "'";
            if ($query = $conn->query($sql)){
                if ($query->num_rows > 0){
                    $row = $query->fetch_assoc();
                    $pacPrice = $row["pacPrice"];
                }
            }else{
                echo (fSendStatusMessage("error", mysqli_error($conn))); die();
            }
            
            $additionFee = $pacPrice;
            if ($additionFee > 0){
                $numOfVoucherRequired = ceil($additionFee / $DEF_VOUCHER_PRICE);   //Number of Voucher Required (@200)
                //checking Voucher Balance
                
                $vAmount  = (isset($_POST["vAmount"])? fValidateInput($_POST["vAmount"]) : "");
                $wAmount  = (isset($_POST["wAmount"])? fValidateInput($_POST["wAmount"]) : "");

                $twBalance  = (isset($_POST["twBalance"])? fValidateInput($_POST["twBalance"]) : "");
                $tvBalance  = (isset($_POST["tvBalance"])? fValidateInput($_POST["tvBalance"]) : "");
                if ($tvBalance < $vAmount){
                    echo (fSendStatusMessage("error", "Your Voucher Balance is not enough #12")); die();
                }
                if ($twBalance < $wAmount){
                    echo (fSendStatusMessage("error", "Your Wallet Balance is not enough")); die();
                }
                $totalAmount = $vAmount + $wAmount;
                if ($totalAmount > $additionFee){
                    echo (fSendStatusMessage("error", "Payment Amount must equal with package price")); die();
                }
                $numOfVoucher  = floor($vAmount / $DEF_VOUCHER_PRICE);
                $numOfWallet  = floor($wAmount / $DEF_WALLET_PRICE);
                $TotalBalance   = $numOfVoucher + $numOfWallet;
                if ($numOfVoucherRequired > $TotalBalance){ //Total Amount not enough
                    echo (fSendStatusMessage("error", "Your Balance is not enough")); die();
                }else{
                    // echo (fSendStatusMessage("error", "$totalAmount || $additionFee || save to table")); die();
                    $conn->autocommit(false);

                    //Transaction, 
                    $trThn = $trThn + 1;
                    $arrData = array(
                        0 => array ("db" => "trUsername"    , "val" => $actUsername),
                        1 => array ("db" => "trPacID"       , "val" => $renewPac),
                        2 => array ("db" => "trDate"        , "val" => "CURRENT_TIME()"),
                        3 => array ("db" => "trStatus"      , "val" => $DEF_STATUS_UPGRADE),
                        4 => array ("db" => "trThn"         , "val" => $trThn)
                        );
                    
                    if (!fInsert("Transaction", $arrData, $conn)) {
                        echo (fSendStatusMessage("error", "<b>Update Transaction - </b>" . mysqli_error($conn)));
                        $conn->rollback();
                        die();
                    }
                    unset($arrData);

                    //dtBnsSponsor, 
                    $sponsorUsername    = fGetSponsorUsername($actUsername, $conn);
                    $myDataObj  = json_decode(fGetDataPackage($conn, $sponsorUsername));
                    $spPacID    = $myDataObj->{"pacID"};

                    /*
                    //get Level of generation
                    $myDataObj  = json_decode(fGetDataPackage($conn, $actUsername));
                    $numOfMatchingGen   = $myDataObj->{"pacMatchingGen"};
                    */

                    //$currSponsorBonus   = fGetBonus("SPONSOR", $currPacID, $spPacID, $conn);
                    $newSponsorBonus    = fGetBonus("SPONSOR", $renewPac, $spPacID, $conn);
                    $sponsorBonus       = $newSponsorBonus;
                    if ($sponsorBonus > 0){
                        $arrData = array(
                            0 => array ("db" => "bnsSpUsername"     , "val" => $sponsorUsername),
                            1 => array ("db" => "bnsSpTrUsername"   , "val" => $actUsername),
                            2 => array ("db" => "bnsSpTrPacID"      , "val" => $renewPac),
                            3 => array ("db" => "bnsSpDate"         , "val" => "CURRENT_TIME()"),
                            4 => array ("db" => "bnsSpAmount"       , "val" => $sponsorBonus),
                            5 => array ("db" => "bnsSpThn"          , "val" => $trThn)
                            );
                            //echo $actUsername . $sponsorUsername . $renewPac. $sponsorBonus;
                        if (!fInsert("dtBnsSponsor", $arrData, $conn)) {
                            echo (fSendStatusMessage("error", "<b>Update Bonus Sponsor - </b>" . $conn->error));
                            $conn->rollback();
                            die();
                        }
                        unset($arrData);
                    }else{
                        //if sponsor bonus == 0, means error
                        echo (fSendStatusMessage("error", "<b>Get Bonus Sponsor Failed</b>"));
                        $conn->rollback();
                        die();
                    }

                    //Update dtFundInVoucher (status="USED", usedFor="ACTIVATION", usedOn=USERNAME)
                    $arrData    = array(
                        "fivStatus"     => $DEF_STATUS_USED,
                        "fivUsedFor"    => $DEF_VOUCHER_USED_FOR_ACTIVATION,
                        "fivUserOn"     => $actUsername
                    );
                    
                    $arrDataQuery = array();
                    $counter = 0;
                    //moving some data of arrVoucher to arrDataQuery 
                    foreach ($arrVoucher as $key => $value){
                        // if ($counter >= $numOfVoucherRequired) {
                        if ($counter >= $numOfVoucher) {
                            break;
                        }else{
                            $arrDataQuery = array (
                                "fivFinID" => $value->fivFinID, 
                                "fivVCode" => $value->fivVCode
                            );
                            $counter++;
                            
                            if (!fUpdateRecord("dtFundInVoucher", $arrData, $arrDataQuery, $conn)){
                                echo (fSendStatusMessage("error", "<b>Update FundInVoucher - </b>" . $conn->error));
                                $conn->rollback();
                                die();
                            }
                            unset($arrDataQuery);
                        }
                    }
                    unset($arrData);
                    //update wallet usage for activation(renew package)
                    if ($wAmount > 0){
                        $sql  = "SELECT * FROM Transaction";
                        $sql .= " WHERE trUsername = '".$actUsername."'";
                        $sql .= " ORDER BY trDate DESC LIMIT 1";
                        $query = $conn->query($sql);
                        if ($row = $query->fetch_assoc()){
                            $trID = $row['trID'];
                            $arrData = array(
                                0 => array ("db" => "tuwID"         , "val" => $trID), //VIP membership
                                1 => array ("db" => "tuwUsername"   , "val" => $actUsername),
                                2 => array ("db" => "tuwAmount"     , "val" => $wAmount),
                                3 => array ("db" => "tuwDate"       , "val" => "CURRENT_TIME()")
                            );

                            if (!fInsert("trUsageWallet", $arrData, $conn)) {
                                echo (fSendStatusMessage("error", "<b>Update Wallet Usage - </b>" . mysqli_error($conn)));
                                $conn->rollback();
                                die();
                            }   
                            unset($arrData);
                        }else{
                            echo (fSendStatusMessage("error", "Transaction ID not found")); die();
                        }
                    }

                    $sql  = " SELECT * FROM msProduct";
                    $sql .= " WHERE proID = '".$DEF_EBOOK_BASIC."' ";
                    $result = $conn->query($sql);
                    if ($row = $result->fetch_assoc()){
                        $trProTransID = strtotime("+0");
                        $table = "trProduct";
                        $arrData = array(
                            array ("db" => "trProTransID"   , "val" => $trProTransID),
                            array ("db" => "trProUsername"  , "val" => $sponsorUsername),
                            array ("db" => "trProUserBeli"  , "val" => $actUsername),
                            array ("db" => "trProDate"      , "val" => "CURRENT_TIME()"),
                            array ("db" => "trProAmount"    , "val" => $row['proPrice']),
                            array ("db" => "trProDisc"      , "val" => $row['proPrice']),
                            array ("db" => "trProStatus"    , "val" => $DEF_STATUS_APPROVED)                
                        );
                        if (!fInsert($table, $arrData, $conn)){
                            $conn->rollback();
                            fSendToAdmin("Activate Member", "activateMember.php", "Insert data to trProduct failed");
                            echo (fSendStatusMessage("error", "<b>Record produk - </b>" . mysqli_error($conn)));
                            die();
                        }else{
                            $table = "trProDetail";
                            $arrData = array(
                                array ("db" => "trPDTransID"    , "val" => $trProTransID),
                                array ("db" => "trPDProID"      , "val" => $DEF_EBOOK_BASIC),
                                array ("db" => "trPDPrice"      , "val" => $row['proPrice']),
                                array ("db" => "trPDQty"        , "val" => "1"),
                                array ("db" => "trPDDisc"       , "val" => $row['proPrice']),
                                array ("db" => "trPDSubTotal"   , "val" => "0")                
                            );
                            if (!fInsert($table, $arrData, $conn)){
                                $conn->rollback();
                                fSendToAdmin("Renew Member", "renewPac.php", "Insert data to trProDetail failed");
                                echo (fSendStatusMessage("error", "<b>Record produk detail - </b>" . mysqli_error($conn)));
                                die();
                            }
                        }       
                    }

                    $conn->commit();
                    fSendNotifToEmail("MEMBER_RENEW_PACKAGE", $actUsername);

                    //fSendNotifToEmail("NEW_MEMBER_ACTIVATED", $actUsername);
                    //echo (fSendStatusMessage("success", $actUsername));

                    // $query->close();
                    // $queryInsert->close();
                    // $msg = "The expiration of your package has been extended";
                    echo (fSendStatusMessage("success", "")); die();
                    //$conn->close(); 
                    
                    //die();
                }
            }
        }//end checking security password     
    }else{
        echo (fSendStatusMessage("error", "Incomplete Data")); die();
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Renew Package</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" type="text/css" href="../assets/css/newBinary.css">
<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


<script>
    $(document).ready(function(e) {
        var currPac     = $("input[name='renewPac']").val();
        var pacPrice    = parseInt($("input[name='currPacPrice']").val());
        var twBalance   = parseInt($("input[name='twBalance']").val());
        var tvBalance   = parseInt($("input[name='tvBalance']").val());
        if (currPac != "st"){
            $("#wAmount").attr("disabled", true);
            $("#vAmount option").each(function(){
                var ovAmount = parseInt($(this).val());
                // alert(ovAmount+" || "+pacPrice);
                if (ovAmount == 0){
                    $(this).hide();
                }
                if (currPac == "pr" && ovAmount > pacPrice){
                    $(this).hide();  
                }
                if (ovAmount == 200 ){
                    $(this).attr("selected", true);
                }else{
                    $(this).attr("selected", false);
                }
            });

            var ovAmount    = parseInt($("#vAmount option:selected").val());
            var priceLeft   = pacPrice - ovAmount;
            $("#wAmount option[value='"+priceLeft+"']").attr("selected",true);
            var owAmount    = parseInt($("#wAmount option:selected").val());
            if (tvBalance < ovAmount || twBalance < owAmount){
                $("#renewPac").attr("disabled", true);
            }else{
                $("#renewPac").attr("disabled", false);
            }
            
            $("#vAmount").on("change", function(){
                var ovAmount    = parseInt($("#vAmount option:selected").val());
                var priceLeft   = pacPrice - ovAmount;
                $("#wAmount option").each(function(){
                    var owAmount = parseInt($(this).val());
                    // alert(priceLeft+ " || "+owAmount);
                    if (priceLeft == owAmount){
                        $(this).attr("selected",true);
                    }else{
                        $(this).attr("selected",false);
                    }
                });

                var owAmount    = parseInt($("#wAmount option:selected").val());
                if (tvBalance < ovAmount || twBalance < owAmount){ // jika saldo lbh kecil dari amount
                    $("#renewPac").attr("disabled", true);
                }else{
                    $("#renewPac").attr("disabled", false);
                }
            });
        }else if (currPac == "st"){
            $("#wAmount").attr("disabled", true);
            $("#vAmount option").each(function(){
                var ovAmount = parseInt($(this).val());
                if (ovAmount == 0){
                    $(this).attr("selected",true);       
                }

                if (ovAmount > pacPrice){
                    $(this).hide();
                }
            });

            var ovAmount    = parseInt($("#vAmount option:selected").val());
            var priceLeft   = pacPrice - ovAmount;
            $("#wAmount option[value='"+priceLeft+"']").attr("selected",true);
            var owAmount    = parseInt($("#wAmount option:selected").val());
            if (tvBalance < ovAmount || twBalance < owAmount){
                $("#renewPac").attr("disabled", true);
            }else{
                $("#renewPac").attr("disabled", false);
            }

            $("#vAmount").on("change", function(){
                var ovAmount    = parseInt($("#vAmount option:selected").val());
                var priceLeft   = pacPrice - ovAmount;
                $("#wAmount option").each(function(){
                    var owAmount = parseInt($(this).val());
                    if (priceLeft == owAmount){
                        $(this).attr("selected",true);
                    }else{
                        $(this).attr("selected",false);
                    }
                });

                var owAmount    = parseInt($("#wAmount option:selected").val());
                if (tvBalance < ovAmount || twBalance < owAmount){
                    $("#renewPac").attr("disabled", true);
                }else{
                    $("#renewPac").attr("disabled", false);
                }
                
            });
        }

        if ($.trim($("#q").html()) == "info-success"){
            demo.showNotification('top','center', 'success', 'Update Account Successfully');
            $("#q").html('');
        }

        $(".alert").on("click", function(){
            $("#errBox").hide();
        });

        $("form[name='formProfile']").on('submit', function() {
            $("#wAmount").attr("disabled", false);
            var html = $("#renewPac").html();
            $("#renewPac").attr("disabled", true);
            $("#renewPac").html(html + ' <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
            var vAmount = parseInt($("#vAmount option:selected").val());
            var wAmount = parseInt($("#wAmount option:selected").val());
            var tAmount = vAmount + wAmount;
            if (tAmount != pacPrice){
                $("#wAmount").attr("disabled", true);
                $("#errBox").show();
                $("#errMsg").html('<b> Error - </b> Amount Not Match');
                $("#renewPac").attr("disabled", false);
                $("#renewPac").html(html);
                return false;
            }else{
                // alert(vAmount + " || "+wAmount); return false;
                // if (vAmount == "" || wAmount == ""){
                //     $("#wAmount").attr("disabled", true);
                //     $("#errBox").show();
                //     $("#errMsg").html('<b> Error - </b> Select Amount');
                //     $("#renewPac").attr("disabled", false);
                //     $("#renewPac").html(html);
                //     return false;
                // }
                $.ajax({
                    url     : "renewPac.php",
                    type    : "POST",
                    data    : new FormData(this),
                    contentType   : false,       // The content type used when sending data to the server.
                    cache         : false,             // To unable request pages to be cached
                    processData   : false,        // To send DOMDocument or non processed data file it is set to false
                    success       : function(data, success)  // A function to be called if request succeeds
                    {
                        // console.log(data);
                        $myDataObj = JSON.parse(data);
                        if ($.trim($myDataObj["status"])=="error"){
                            $("#wAmount").attr("disabled", true);
                            $("#errBox").show();
                            $("#errMsg").html('<b> Error - </b>'+$myDataObj['message']);
                            $("#renewPac").attr("disabled", false);
                            $("#renewPac").html(html);
                            return false;
                        }else if ($.trim($myDataObj['status']) == "success"){
                            $("#succesMsg").show();
                            $("#notrenewyet").hide();
                            location.href="renewPac.php?q=info-success";
                        }
                    }
                });
            }
        });

    });
</script>

</head>
<body><span id="q"><?php echo $q; ?></span>
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="material-icons">control_point</i>
        </div>
        <div class="card-text"><h4 class="card-title">Renew Package</h4></div>
    </div>
    <?php
    $sql = "SELECT m.*, s.mbrUsername as spUsername, s.mbrFirstName as spName, u.mbrUsername as upUsername, u.mbrFirstName as upName, c.countryDesc, pacID, pacName, trThn FROM dtMember m ";
    $sql .= " INNER JOIN dtMember s on m.mbrSponsor = s.mbrUsername ";
    $sql .= " INNER JOIN dtMember u on m.mbrUpline = u.mbrUsername ";
    $sql .= " INNER JOIN msCountry c on m.mbrCountry = c.countryID ";
    $sql .= " INNER JOIN (SELECT * FROM Transaction WHERE trID = (SELECT trID FROM Transaction WHERE trUsername='". $_SESSION["sUserName"] . "' ORDER BY trDate DESC LIMIT 1)) t ON m.mbrUsername = t.trUsername ";
        $sql .= " INNER JOIN msPackage ON pacID = t.trPacID";

    $sql .= " WHERE m.mbrUsername = '" . $_SESSION["sUserName"] . "'";
    // echo $sql;
    if ($query = $conn->query($sql)){
        if ($row = $query->fetch_assoc()){  
            $trThn = $row['trThn'];
    ?>
    <div class="card-body card-fix">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <!-- <div class="col-md-4">Upline</div><div class="col-md-8"><?php //echo $row["upName"] . "(" . $row["upUsername"] . ")" ?></div> -->
                        <div class="card col-md-11">
                            <div class="card-body card-fix col-md-11">
                                <h4 class="profile">
                                    <div class="row"> 
                                        <div class="col-md-12"> Renew Package</div>
                                        <div class="col-md-6"> Voucher Balance</div>
                                        <div class="col-md-6"> $<?php echo numFormat($VoucherBalance,0); ?></div>
                                        <div class="col-md-6"> Wallet Balance</div>
                                        <div class="col-md-6"> $<?php echo numFormat($WalletBalance,0); ?></div>
                                    </div>
                                </h4>
                                <hr>
                                <div class="row">
                                    <form action="" method="post" name="formProfile" class="col-md-12" onsubmit="return false;">
                                        <div class="row" id="errBox" style="display: none;">
                                            <div class="col-md-2"></div>
                                            <div class="col-md-8 text-danger">
                                              <div class="alert alert-warning">
                                                  <button type="button" class="close" aria-label="Close">
                                                      <i class="material-icons">close</i>
                                                  </button>
                                                  <span id="errMsg"></span>
                                              </div>
                                            </div>
                                            <div class="col-md-2"></div>
                                        </div>
                                        <?php
                                        $sql = "SELECT mbrDate, IF( DATE_ADD(DATE_ADD( DATE(mbrDate), INTERVAL ". $trThn . " YEAR ), INTERVAL -3 MONTH) <= CURRENT_DATE() , 'renew', 'no') renew  ";
                                        $sql .= " FROM dtMember WHERE mbrUsername='" . $_SESSION["sUserName"] . "'";
                                        // echo $sql; 
                                        if ($query = $conn->query($sql)){
                                            $row = $query->fetch_assoc();
                                            if ($row['renew'] == 'renew'){
                                        ?>

                                        <div class="row">
                                            <div class="col-md-5 profile">Renew Package</div>
                                            <div class="col-md-7 profile-val"><?php echo $currPacName ?></div>
                                            <div class="form-group col-md-12">
                                                <input type="hidden" name="tvBalance" value="<?php echo ($VoucherBalance);?>">
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <label class="col-form-label profile" style="color: #333;">Voucher Amount</label>
                                                        <?php 
                                                        if ($currPac != 'st'){
                                                            echo '<br><small class="text-warning">Minimum Voucher Amount $200</small>';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <select class="form-control" data-size="7" data-style="btn btn-primary" name="vAmount" id="vAmount">
                                                            <option value='-1' disabled selected>Select Amount</option>
                                                            <option value='0'>$0</option>
                                                            <option value='200'>$200</option>
                                                            <option value='400'>$400</option>
                                                            <option value='600'>$600</option>
                                                            <option value='800'>$800</option>
                                                            <option value='1000'>$1000</option>
                                                        <?php
                                                        /*
                                                        for ($i=$x; $i<=$numOfVoucher; $i++){
                                                            if($i>5){
                                                                break;
                                                            }
                                                            $value = $i * $DEF_VOUCHER_PRICE;
                                                            $selected = "";
                                                            if ($currPac != 'st'){
                                                                if ($value == 200){
                                                                    $selected = "selected";
                                                                    echo '<option value="'.$value.'" '.$selected.'>$'.numFormat($value,0).'</option>';
                                                                }else{
                                                                    echo '<option value="'.$value.'">$'.numFormat($value,0).'</option>';
                                                                }
                                                            }else{
                                                                echo '<option value="'.$value.'">$'.numFormat($value,0).'</option>';
                                                            }
                                                            
                                                        }
                                                        */
                                                        ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <input type="hidden" name="twBalance" value="<?php echo ($WalletBalance);?>">
                                                <div class="row">
                                                    <div class="col-md-5"><label class="col-form-label profile" style="color: #333;">Wallet Amount</label></div>
                                                    <div class="col-md-7">
                                                        <select class="form-control" data-size="7" data-style="btn btn-primary" name="wAmount" id="wAmount">
                                                            <!-- <option value='-1' disabled selected>Select Amount</option> -->
                                                            <option value='0'>$0</option>
                                                            <option value='200'>$200</option>
                                                            <option value='400'>$400</option>
                                                            <option value='600'>$600</option>
                                                            <option value='800'>$800</option>
                                                            <option value='1000'>$1000</option>
                                                        <?php 
                                                        /*
                                                        for ($i=0; $i<=$numOfWallet; $i++){
                                                            if($i>4){
                                                                break;
                                                            }
                                                            $value = $i * $DEF_WALLET_PRICE;
                                                            echo '<option value="'.$value.'">$'.numFormat($value,0).'</option>';
                                                        }
                                                        */
                                                        ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="renewPac" value="<?php echo ($currPac);?>">
                                            <input type="hidden" name="currPacPrice" value="<?php echo ($currPacPrice);?>">
                                            <div class="col-md-5 profile">&nbsp;</div><div class="col-md-7 profile-val">&nbsp;</div>
                                            <div class="col-md-5 profile">Security Password</div>
                                            <div class="col-md-7"><input type="password" name="securityPasswd" id="securityPasswd"  value="" class="form-control"></div>  
                                        </div>
                                        
                                        <div class="footer">
                                            <button type="submit" id="renewPac" name="renewPac" class="btn btn-fill btn-rose col-md-12">Renew Package</button>
                                        </div>
                                        <?php
                                            }else{

                                        ?>
                                        <div class="row" id="notrenewyet">
                                            <div class='col-md-12 profile'>Your package can be extended 1 month before expiration</div>
                                        </div>
                                        <div class="row" id="succesMsg" style="display: none;">
                                            <div class="col-md-2"></div>
                                            <div class="col-md-8 text-success">
                                              <div class="alert alert-success">
                                                  <button type="button" class="close" aria-label="Close">
                                                      <i class="material-icons">close</i>
                                                  </button>
                                                  <span>The usage period of your package has been successfully extended</span>
                                              </div>
                                            </div>
                                            <div class="col-md-2"></div>
                                        </div>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </form>
                                <?php
                                    //} //end if $row
                                //} //end if query
                                ?>
                                </div> <!-- end class=row -->
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end card -->
    <?php
        }
    }
    ?>
</body>

    <!--   Core JS Files   -->
<script src="../assets/js/core/jquery.min.js"></script>
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/bootstrap-material-design.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>

<!--  Google Maps Plugin  -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB2Yno10-YTnLjjn_Vtk0V8cdcY5lC4plU"></script>

<!--  Plugin for Date Time Picker and Full Calendar Plugin  -->
<script src="../assets/js/plugins/moment.min.js"></script>

<!--    Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
<script src="../assets/js/plugins/bootstrap-datetimepicker.min.js"></script>

<!--    Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="../assets/js/plugins/nouislider.min.js"></script>

<!--    Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/bootstrap-selectpicker.js"></script>

<!--    Plugin for Tags, full documentation here: http://xoxco.com/projects/code/tagsinput/  -->
<script src="../assets/js/plugins/bootstrap-tagsinput.js"></script>

<!--    Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
<script src="../assets/js/plugins/jasny-bootstrap.min.js"></script>

<!-- Plugins for presentation and navigation  -->
<script src="../assets/assets-for-demo/js/modernizr.js"></script>

<!-- Material Kit Core initialisations of plugins and Bootstrap Material Design Library -->
<script src="../assets/js/material-dashboard.js?v=2.0.0"></script>

<!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>

<!-- Library for adding dinamically elements -->
<script src="../assets/js/plugins/arrive.min.js" type="text/javascript"></script>

<!-- Forms Validations Plugin -->
<script src="../assets/js/plugins/jquery.validate.min.js"></script>

<!--  Charts Plugin, full documentation here: https://gionkunz.github.io/chartist-js/ -->
<script src="../assets/js/plugins/chartist.min.js"></script>

<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="../assets/js/plugins/jquery.bootstrap-wizard.js"></script>

<!--  Notifications Plugin, full documentation here: http://bootstrap-notify.remabledesigns.com/    -->
<script src="../assets/js/plugins/bootstrap-notify.js"></script>

<!-- Vector Map plugin, full documentation here: http://jvectormap.com/documentation/ -->
<script src="../assets/js/plugins/jquery-jvectormap.js"></script>

<!--  Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/jquery.select-bootstrap.js"></script>

<!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
<script src="../assets/js/plugins/jquery.datatables.js"></script>

<!-- Sweet Alert 2 plugin, full documentation here: https://limonte.github.io/sweetalert2/ -->
<script src="../assets/js/plugins/sweetalert2.js"></script>

<!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
<script src="../assets/js/plugins/fullcalendar.min.js"></script>

<!-- demo init -->
<script src="../assets/js/plugins/demo.js"></script>


<script type="text/javascript">

    $(document).ready(function(){   
      //init DateTimePickers
      md.initFormExtendedDatetimepickers();
    
      // Sliders Init
      md.initSliders();
    });
</script>

<?php fCloseConnection($conn); ?>
</html>