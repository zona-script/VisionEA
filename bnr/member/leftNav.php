<div class="sidebar" data-color="rose" data-background-color="black" data-image="../assets/img/sidebar-1.jpg">
    <!--
        Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

        Tip 2: you can also add an image using data-image tag
    -->

    <div class="logo">
        <a href="#" class="simple-text logo-mini">
            <!--
            <img src="../assets/img/<?php echo ($COMPANY_LOGO_S); ?>" width="100%" />
            -->
        </a>


        <a href="#" class="simple-text logo-normal">
             <!--<?PHP echo($COMPANY_NAME); ?>-->
             <img src="../assets/img/<?php echo ($COMPANY_LOGO); ?>" class='img-fluid' />
        </a>

    </div>
<?php
$sUserName = isset($_SESSION['sUserName'])?$_SESSION['sUserName']:"";
$sPrivilege = isset($_SESSION['sPrivilege'])?$_SESSION['sPrivilege']:"";
if ($sUserName != "" ){
?>
    <div class="sidebar-wrapper">
        <div class="user">
            <div class="photo">
                <!--
                <img src="../assets/img/faces/avatar.jpg" />
                -->
            </div>
            <div class="user-info">
                <a data-toggle="collapse" href="#collapseProfile" class="username">
                    <span>
                       <?php echo ($_SESSION["sFirstName"] . " ". $_SESSION["sLastName"]); ?>
                      <b class="caret"></b>
                    </span>
                </a>
                <div class="collapse" id="collapseProfile">
                    <ul class="nav">
                    <?php 
                    if ($sPrivilege != "renewonly"){ // member normal
                    ?>
                        <li class="nav-item">
                            <!-- <a class="nav-link" href="index.php?p=profile"> -->
                            <a class="nav-link" href="#" id="myProfile">
                               <i class="fa fa-user"></i>
                              <span class="sidebar-normal"> My Profile </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="beneficiaryAcc">
                              <i class="fa fa-address-card-o"></i>
                              <span class="sidebar-normal" id="beneficiaryAcc"> Beneficiary Account</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="changePassword">
                              <i class="fa fa-key"></i>
                              <span class="sidebar-normal" id="changePassword"> Change Password & Security</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./?MNav=lo" >
                               <i class="fa fa-sign-out"></i>
                              <span class="sidebar-normal" >Sign Out</span>
                            </a>
                        </li>
                    <?php
                    }else{ //khusus renew only
                    ?>
                        <li class="nav-item">
                            <!-- <a class="nav-link" href="index.php?p=profile"> -->
                            <a class="nav-link" href="#" id="Renew">
                               <i class="fa fa-user"></i>
                              <span class="sidebar-normal">Renewal Membership</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="changePassword">
                              <i class="fa fa-key"></i>
                              <span class="sidebar-normal" id="changePassword"> Change Password & Security</span>
                            </a>
                        </li>
						<li class="nav-item">
                            <a class="nav-link" href="./?MNav=lo" >
                               <i class="fa fa-sign-out"></i>
                              <span class="sidebar-normal" >Sign Out</span>
                            </a>
                        </li>
                    <?php
                    }
                    ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        if ($sPrivilege != "renewonly"){
        ?>
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="./?MNav=dboard"  id="dashboard">
                    <i class="material-icons">dashboard</i>
                    <p> Dashboard </p>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link" data-toggle="collapse" href="#myAcc">
                    <i class="fa fa-user" ></i>
                    <p> My Account
                       <b class="caret"></b>
                    </p>
                </a>
                <div class="collapse" id="myAcc">
                    <ul class="nav">
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=wd" id="withdrawal">
                              <i class="fa fa-usd" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Withdrawal </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=buyVoucher">
                              <i class="fa fa-briefcase" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Buy PIN </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=trans" id="transfer">
                              <i class="fa fa-exchange" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Transfer PIN</span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=convert" id="convert">
                              <i class="fa fa-money" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Convert Bonus</span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=his" id="history">
                              <i class="fa fa-history" aria-hidden="true"></i>
                              <span class="sidebar-normal"> History </span>
                            </a>
                        </li>
                       
                    </ul>
                </div>
            </li>
            <!-- <li class="nav-item ">
                <a class="nav-link" data-toggle="collapse" href="#VPS">
                    <i class="fa fa-credit-card" ></i>
                    <p>VPS Voucher
                       <b class="caret"></b>
                    </p>
                </a>
                <div class="collapse" id="VPS">
                    <ul class="nav">
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=buyVoucherVPS">
                              <i class="fa fa-briefcase" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Buy VPS Voucher</span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=transVPS" id="transferVPS">
                              <i class="fa fa-exchange" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Transfer VPS Voucher</span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=convertVPS" id="convertVPS">
                              <i class="fa fa-money" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Convert Wallet</span>
                            </a>
                        </li>                       
                    </ul>
                </div>
            </li> -->
            <li class="nav-item ">
                <a class="nav-link" data-toggle="collapse" href="#Product">
                    <i class="fa fa-book" ></i>
                    <p>Product
                       <b class="caret"></b>
                    </p>
                </a>
                <div class="collapse" id="Product">
                    <ul class="nav">
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=trProduct">
                              <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Transaction</span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./?MNav=readEbook">
                              <i class="fa fa-book" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Read E-book</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="./?MNav=register" id="register">
                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                    <p>Register New Member</p>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="./?MNav=gt&subNav=net">
                    <i class="fa fa-sitemap" aria-hidden="true" ></i>
                    <p> Genealogy Tree</p>
                </a>
                <!--
                <div class="collapse" id="genealogyTree">
                    <ul class="nav">
                        <li class="nav-item ">
                            <a class="nav-link" href="#" id="directSponsor">
                              <i class="fa fa-list-ul" aria-hidden="true"></i>
                              <span class="sidebar-normal">Direct Sponsor List </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="#" id="networkTree">
                              <i class="material-icons">device_hub</i>
                              <span class="sidebar-normal">Network Tree</span>
                            </a>
                        </li>
                        
                        <li class="nav-item ">
                            <a class="nav-link" href="#" id="binaryCommission" >
                                <i class="fa fa-plus-circle" aria-hidden="true"></i>
                              <span class="sidebar-normal"> Binary Commission </span>
                            </a>
                        </li>
                        
                       
                    </ul>
                </div>
                -->
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="#" id="report">
                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                    <p>Reports</p>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="./?MNav=achievement" id="achievement">
                    <i class="fa fa-star-o" aria-hidden="true"></i>
                    <p>Achievement</p>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="./?MNav=announcement"id="announcement">
                    <i class="fa fa-bullhorn" aria-hidden="true"></i>
                    <p>Announcement</p>
                </a>
            </li>
        </ul>
        <?php
        }   
        ?>

    </div>
    <?php
    }
    ?>
</div>
