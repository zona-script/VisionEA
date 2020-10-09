<?php
include_once("../includes/inc_def.php"); //before inc_session
include_once("../includes/inc_session.php"); //after inc_session
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");
include_once("../includes/inc_commission.php");
include_once("../includes/inc_bns_rewards.php");

$username = $_SESSION['sUserName'];

//sponsor
$total = $numPacST = $numPacPR = $numPacVIP = $totalSP = 0;
$myDataObj  = json_decode(fCommissionSponsorship($username, $conn));
if ($myDataObj->{"status"} == "success"){
    $total      = $myDataObj->{'total'};
    $numPacST   = $myDataObj->{'numPacST'};
    $numPacPR   = $myDataObj->{'numPacPR'};
    $numPacVIP  = $myDataObj->{'numPacVIP'};
    $totalSP    = $myDataObj->{'totalSP'};
    
}


//passed-up
$totalPU = $numPUST = $numPUPR = $numPUVIP = 0;
$myDataObj  = json_decode(fCommissionPassedUP($username, $conn));
if ($myDataObj->{"status"} == "success"){
    $totalPU   = $myDataObj->{'totalPU'};
    $numPUST   = $myDataObj->{'numPUST'};
    $numPUPR   = $myDataObj->{'numPUPR'};
    $numPUVIP  = $myDataObj->{'numPUVIP'};
}


//pairing
$sumLeft = $sumRight = $sumTO = $sumFO = 0;
$myDataObj  = json_decode(fCommissionPairing($username, $conn));
if ($myDataObj->{"status"} == "success"){
    $sumLeft    = $myDataObj->{'sumLeft'};
    $sumRight   = $myDataObj->{'sumRight'};
    $sumTO      = $myDataObj->{'sumTO'};
    $sumFO      = $myDataObj->{'sumFO'};
}

//Matching
$sumMtch = $pacMatchingGen = 0;
$myDataObj  = json_decode(fCommissionMatching($username, $conn));
if ($myDataObj->{"status"} == "success"){
    $sumMtch        = $myDataObj->{'sumMtch'};
    $pacMatchingGen = $myDataObj->{'pacMatchingGen'};
}


//Voucher STD
$voucherAct = $voucherIN = $voucherOUT = $voucherBalance = 0;
$myDataObj  = json_decode(fGetNumberOfVoucher($DEF_VOUCHER_TYPE_STD, $username, $conn));
if ($myDataObj->{"status"} == "success"){
    $voucherAct     = $myDataObj->{'voucherAct'};
    $voucherOUT     = $myDataObj->{'voucherOUT'};
    $voucherIN      = $myDataObj->{'voucherIN'};
    $sumActivationVoucher   = $myDataObj->{'sumActivationVoucher'};
    $sumTransferVoucher     = $myDataObj->{'sumTransferVoucher'};
    $sumRepeatOrder         = $myDataObj->{'sumRepeatOrder'};
    $voucherBalance = $myDataObj->{'voucherBalance'};
}


//Voucher VPS
$voucherActVPS = $voucherINVPS = $voucherOUTVPS = $voucherBalanceVPS = 0;
$myDataObj  = json_decode(fGetNumberOfVoucher($DEF_VOUCHER_TYPE_VPS, $username, $conn));
if ($myDataObj->{"status"} == "success"){
    $voucherActVPS     = $myDataObj->{'voucherAct'};
    $voucherOUTVPS     = $myDataObj->{'voucherOUT'};
    $voucherINVPS      = $myDataObj->{'voucherIN'};
    $sumActivationVoucherVPS   = $myDataObj->{'sumActivationVoucher'};
    $sumTransferVoucherVPS     = $myDataObj->{'sumTransferVoucher'};
    $voucherBalanceVPS = $myDataObj->{'voucherBalance'};
}

//Withdrawal
$ttlWD = 0;
$myDataObj  = json_decode(fSumWithdrawal($username, $conn));
if ($myDataObj->{"status"} == "success"){
    $ttlWD     = $myDataObj->{'ttlWD'};
}

//Balance
$myDataObj  = json_decode(fGetBalance($username, $conn));
if ($myDataObj->{"status"} == "success"){
    $ttlBonus       = $myDataObj->{'ttlBonus'};
    $ttlCommission  = $myDataObj->{'ttlCommission'};
    $wallet         = $myDataObj->{'wallet'};
    $wUsage         = $myDataObj->{'wUsage'};
    $ttlConvert     = $myDataObj->{'ttlConvert'};
    $balance        = $myDataObj->{'balance'};
}

//Spectacular Program
$myDataObj  = json_decode(fGetSpectacularBonus($username, $conn));
if ($myDataObj->{"status"} == "success"){
    $directVIP  = $myDataObj->{'directVIP'};
    $cashBns    = $myDataObj->{'cashBns'};
}

//Bonus RO
$myDataObj  = json_decode(fSumCommissionRO($username, $conn));
$tBnsRO = $trPDQty = 0;
if ($myDataObj->{"status"} == "success"){
    $tBnsRO = $myDataObj->{'tBnsRO'};
    $trPDQty = $myDataObj->{'trPDQty'};
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>DashBoard</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
</head>

<body style="width: 98%;">
    <?php include ("sessionPromo.php") ?>
    <div class="row">
        <!-- start col -->
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-header card-header-warning card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">face</i>
                    </div>
                    <p class="card-category">Sponsor</p>
                    <h4 class="card-title"><?php echo "Rp ". numFormat($total, 0) ?></h4><hr>
                    <div class="row text-rose">
                        <div class="col-md-12">Commissions come from</div>
                    </div>
                    <div class="row text-rose">
                        <div class="col-md-12">Total <?php echo numFormat($totalSP, 0) ?> of Sponsor</div>
                    </div>
                    <div class="row">&nbsp;</div>                
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <i class="material-icons text-danger">link</i>
                        <a href="./?MNav=rpt&subNav=sp" target="_parent">Get more detail...</a>
                    </div>
                </div>
            </div>
        </div> 
        <!--end col -->
        
        <!--
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-header card-header-rose card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">call_merge</i>
                    </div>
                    <p class="card-category">Passed-Up</p>
                    <h4 class="card-title"><?php echo "Rp ". numFormat($totalPU, 0) ?></h4><hr>
                    <div class="row text-rose">
                        <div class="col-6 col-md-8 col-sm-8">Starter</div>
                        <div class="col-6 col-md-4 col-sm-4"><?php echo $numPUST ?> pac</div>
                    </div>
                    
                    <div class="row text-info">
                        <div class="col-6 col-md-8 col-sm-8">Premium</div>
                        <div class="col-6 col-md-4 col-sm-4"><?php echo $numPUPR ?> pac</div>
                    </div>
                    
                    <div class="row text-success">
                        <div class="col-6 col-md-8 col-sm-8">VIP</div>
                        <div class="col-6 col-md-4 col-sm-4"><?php echo $numPUVIP ?> pac</div>
                    </div>
                </div> 
                <div class="card-footer">
                    <div class="stats">
                        <i class="material-icons text-danger">link</i>
                        <a href="./?MNav=rpt&subNav=pu" target="_parent">Get more detail...</a>
                    </div>
                </div>  
            </div>
        </div>
        -->
        
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-header card-header-success card-header-icon">
                  <div class="card-icon">
                    <!-- <i class="material-icons">store</i> -->
                    <i class="fa fa-object-ungroup"></i>
                  </div>
                  <p class="card-category">Pairing</p>
                  <h4 class="card-title"><?php echo "Rp ". numFormat($sumTO, 0) ?></h4><hr>
                    <div class="row text-rose">
                        <div class="col-6 col-md-6 col-sm-6">Left</div>
                        <div class="col-6 col-md-6 col-sm-6">Right</div>
                    </div>
                    
                    <div class="row text-info">
                        <div class="col-6 col-md-6 col-sm-6"><?php echo numFormat($sumLeft, 0) ?></div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo numFormat($sumRight, 0) ?></div>
                    </div> 
                    <div class="row text-success">
                        <div class="col-6 col-md-6 col-sm-6">Flush Out</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo numFormat($sumFO, 0) ?></div>
                    </div>
                </div> 
                <div class="card-footer">
                    <div class="stats">
                        <i class="material-icons text-danger">link</i>
                        <a href="./?MNav=rpt&subNav=pair" target="_parent">Get more detail...</a>
                    </div>
                </div>  
            </div>
        </div>

        <!-- <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-header card-header-info card-header-icon">
                  <div class="card-icon">
                    <i class="fa fa-object-group"></i>
                  </div>
                  <p class="card-category">Mega Matching</p>
                  <h4 class="card-title"><?php echo "Rp ". numFormat($sumMtch, 0) ?></h4><hr>
                    <div class="row text-info">
                        <div class="col-md-12">Commissions come from</div>
                    </div>
                    
                    <div class="row text-info">
                        <div class="col-md-12"><?php echo $pacMatchingGen ?> generations</div>
                    </div>
                    
                    <div class="row text-success">
                        <div class="col-6 col-md-8">&nbsp;</div>
                        <div class="col-6 col-md-4">&nbsp;</div>
                    </div>
                </div> 
                <div class="card-footer">
                    <div class="stats">
                        <i class="material-icons text-danger">link</i>
                        <a href="./?MNav=rpt&subNav=mm" target="_parent">Get more detail...</a>
                    </div>
                </div>  
            </div>
        </div> -->

        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-header card-header-success card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">monetization_on</i>
                    </div>
                    <p class="card-category">Repeat Order</p>
                    <h4 class="card-title"><?php echo "Rp ". numFormat($tBnsRO, 0) ?></h4><hr>
                    <div class="row text-info">
                        <div class="col-md-12">Commissions come from</div>
                    </div>
                    <div class="row text-info">
                        <div class="col-md-12">Total <?php echo numFormat($trPDQty, 0) ?> of Repeat Order</div>
                    </div>
                    <div class="row">&nbsp;</div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <i class="material-icons text-danger">link</i>
                        <a href="./?MNav=trProduct&subNav=proPaid" target="_parent">Get more detail...</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- start col / VOUCHER STD -->
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-header card-header-primary card-header-icon">
                    <div class="card-icon">
                        <i class="fa fa-money"></i>
                    </div>
                    <p class="card-category">PIN</p>
                    <h4 class="card-title"><?php echo ("Rp ".numFormat($voucherBalance, 0))?></h4><hr>
                    <div class="row text-danger">
                        <div class="col-6 col-md-6 col-sm-6">Total PIN IN</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo 'Rp '.(numFormat($voucherIN * $DEF_VOUCHER_PRICE_IDR, 0)) ?></div>
                    </div>
                    <div class="row text-info">
                        <div class="col-6 col-md-6 col-sm-6">New</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo 'Rp '. (numFormat($voucherAct * $DEF_VOUCHER_PRICE_IDR, 0)) ?></div>
                    </div>
                    <div class="row text-info">
                        <div class="col-6 col-md-6 col-sm-6">For Activation</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo ('(Rp '. numFormat($sumActivationVoucher * $DEF_VOUCHER_PRICE_IDR, 0) .')') ?></div>
                    </div>
                    <div class="row text-success">
                        <div class="col-6 col-md-6 col-sm-6">For Transfer</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo ('(Rp '.numFormat($sumTransferVoucher * $DEF_VOUCHER_PRICE_IDR, 0) .')') ?></div>
                    </div>
                     <div class="row text-warning">
                        <div class="col-6 col-md-6 col-sm-6">For Repeat Order</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo ('(Rp '.numFormat($sumRepeatOrder * $DEF_VOUCHER_PRICE_IDR, 0) .')') ?></div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <!--
                        <i class="material-icons text-danger">link</i>
                        <a href="./?MNav=his" target="_parent">Get more detail...</a>
                    -->
                    </div>
                </div>
            </div>
        </div>  <!--end col -->

        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-header card-header-success card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">monetization_on</i>
                    </div>
                    <p class="card-category">Balance</p>
                    <h4 class="card-title"><?php echo "Rp ". numFormat($balance, 0) ?></h4><hr>
                    <!--
                    <div class="row text-rose">
                        <div class="col-md-7 col-sm-7">Voucher IN</div>
                        <div class="col-md-5 col-sm-5"><?php echo $voucherIN ?></div>
                    </div>

                    <div class="row text-danger">
                        <div class="col-md-7 col-sm-7">Voucher OUT</div>
                        <div class="col-md-5 col-sm-5"><?php echo "(" . $voucherOUT . ")" ?></div>
                    </div>
                    -->
                    
                    <div class="row text-warning">
                        <div class="col-6 col-md-6 col-sm-6">PIN</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo "Rp ". numFormat($voucherBalance, 0)?></div>
                    </div>

                    <div class="row text-info">
                        <div class="col-6 col-md-6 col-sm-6">Nett Comm</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo "Rp ". numFormat($ttlCommission, 0) ?></div>
                    </div>
                    <!-- <div class="row text-success">
                        <div class="col-6 col-md-6 col-sm-6">Wallet</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo "Rp ". numFormat($wallet, 0) ?></div>
                    </div>
                    <div class="row text-danger">
                        <div class="col-6 col-md-6 col-sm-6">Wallet Usage</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo "(Rp ". numFormat($wUsage, 0).")"; ?></div>
                    </div> -->
                    <div class="row text-danger">
                        <div class="col-6 col-md-6 col-sm-6">Withdrawal</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo "(" . "Rp ". numFormat($ttlWD,0) . ")"  ?></div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <i class="material-icons text-danger">link</i>
                        <a href="./?MNav=his" target="_parent">Get more detail...</a>
                    </div>
                </div>
            </div>
        </div>  <!--end col -->

        <!-- start col / VOUCHER VPS --> 
        <!--<div class="col-lg-4 col-md-6 col-sm-6">-->
        <!--    <div class="card card-stats">-->
        <!--        <div class="card-header card-header-info card-header-icon">-->
        <!--            <div class="card-icon">-->
        <!--                <i class="fa fa-credit-card"></i>-->
        <!--            </div>-->
        <!--            <p class="card-category">VPS PIN</p>-->
        <!--            <h4 class="card-title"><?php echo ("Rp ".numFormat($voucherBalanceVPS, 0))?></h4><hr>-->
        <!--            <div class="row text-danger">-->
        <!--                <div class="col-6 col-md-8 col-sm-8">Total PIN IN</div>-->
        <!--                <div class="col-6 col-md-4 col-sm-4"><?php echo ("Rp ". numFormat($voucherINVPS * $DEF_VOUCHER_PRICE_VPS, 0)) ?></div>-->
        <!--            </div>-->
        <!--            <div class="row text-info">-->
        <!--                <div class="col-6 col-md-8 col-sm-8">New</div>-->
        <!--                <div class="col-6 col-md-4 col-sm-4"><?php echo ("Rp ". numFormat($voucherActVPS * $DEF_VOUCHER_PRICE_VPS, 0)) ?></div>-->
        <!--            </div>-->
        <!--            <div class="row text-info">-->
        <!--                <div class="col-6 col-md-8 col-sm-8">Used for VPS</div>-->
        <!--                <div class="col-6 col-md-4 col-sm-4"><?php echo ('(Rp '. numFormat($sumActivationVoucherVPS * $DEF_VOUCHER_PRICE_VPS, 0) .')') ?></div>-->
        <!--            </div>-->
        <!--            <div class="row text-success">-->
        <!--                <div class="col-6 col-md-8 col-sm-8">Used for Transfer</div>-->
        <!--                <div class="col-6 col-md-4 col-sm-4"><?php echo ('(Rp '.numFormat($sumTransferVoucherVPS * $DEF_VOUCHER_PRICE_VPS, 0) .')') ?></div>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="card-footer">-->
        <!--            <div class="stats">-->
                       
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div> -->
               
        <!-- <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="card card-stats">
                <div class="card-header card-header-success card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">card_giftcard</i>
                    </div>
                    <h4 class="card-title">Spectacular Promo</h4><hr>
                    <div class="row text-rose">
                        <div class="col-6 col-md-6 col-sm-6">Direct VIP</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo $directVIP ?></div>
                    </div>
                    <div class="row text-success">
                        <div class="col-6 col-md-6 col-sm-6">Cash Bonus</div>
                        <div class="col-6 col-md-6 col-sm-6"><?php echo "Rp". numFormat($cashBns, 0) ?></div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="stats">
                        <i class="material-icons text-danger">link</i>
                        <a href="./?MNav=his" target="_parent">Get more detail...</a>
                    </div>
                </div>
                
            </div>
        </div> -->  <!--end col -->
        
        <!--
        <div class="col-lg-6 col-md-4 col-sm-12">
            <div class="card card-stats">
                <div class="card-header card-header-rose card-header-icon">
                    <div class="row text-info">
                        <div class="col-md-12" style="text-align: left"><h4>Pre Launching Promo</h4><hr>some text here.....<br>second line</div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <i class="material-icons">info</i>More detail...
                    </div>
                </div>
            </div>
        </div>
        -->
    </div> <!-- end row -->
    
<!--
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="material-icons"></i>
        </div>
        <h4 class="card-title">DashBoard</h4><hr>
    </div>
    <div class="card-body card-fix">
        
    </div> <! -- end class card-body -->
<!-- </div> <! -- end class card -->

</body>
</html>