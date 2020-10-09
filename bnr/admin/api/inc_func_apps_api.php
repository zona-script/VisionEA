<?php

/*___________________VISION APP______________*/
function getMsProduct($conn){
    $proID = $proName = $proImage = $proPrice = $proBookPage = $proBookAuthor = '';
    $sql  = "SELECT * FROM msProduct";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
       $returnArrData = array();
       while ($row = $result->fetch_assoc()) {
           $proID           = $row['proID'];
           $proName         = $row['proName'];
           $proImage        = $row['proImage'];
           $proPrice        = $row['proPrice'];
           $proBookPage     = $row['proPages'];
           $proBookAuthor   = "Yulianto Hiu";

           $returnArrData[] = array(
                'proID'         => $proID,
                'proName'       => $proName,
                'proImage'      => $proImage,
                'proPrice'      => $proPrice,
                'proBookPage'   => $proBookPage,
                'proBookAuthor' => $proBookAuthor
           );
       }
        return (resultJSON ("success", "", $returnArrData));
    }else{
        $returnArrData[] = array(
            '' => ''
        );
        return (resultJSON ("error", "", $returnArrData));
    }
}


function postBuyProduct($conn, $username, $typeofpurchase, $trProUserBeli, $cusEmail, $cusFirstName, $cusLastName, $myJSON){
    global $DEF_TYPE_PURCHASE_RS, $DEF_TYPE_PURCHASE_RO, $DEF_STATUS_ONPROGRESS, $DEF_STATUS_PENDING;
    if ($typeofpurchase == $DEF_TYPE_PURCHASE_RS){
        $isReseller     = true;
        $trProType      = $DEF_TYPE_PURCHASE_RS;
    }else{
        $trProUserBeli  = $username;
        $isReseller     = false;
        $trProType      = $DEF_TYPE_PURCHASE_RO;
    }

        // $data = print_r($myJSON); //die();
    //validate pending order
    $sql  = "SELECT * FROM trProduct";
    $sql .= " WHERE trProUsername ='".$username."' AND trProStatus = '".$DEF_STATUS_ONPROGRESS."' ";
    // echo (fSendStatusMessage("error", "$username || $sql")); die();
    $result = $conn->query($sql);
    if ($result->num_rows > 0){
        //echo (fSendStatusMessage("error", "Silahkan selesaikan pendingan order terlebih dahulu")); die();
        return (resultJSON("error", "Silahkan selesaikan pendingan order terlebih dahulu", "")); die();
    }else{
        $proID = $proPrice = $proQty = "";
        $trProTransID = strtotime("+0");
        $tAmount = 0;
        $conn->autocommit(false);
        for ($i = 0; $i < count($myJSON); $i++) {
            $proID      = $myJSON[$i]->proID;
            $proPrice   = $myJSON[$i]->proPrice;
            $proQty     = $myJSON[$i]->proQty;
            $proAmount  = $proPrice * $proQty;
            $tAmount += $proAmount;
            if ($proID != "" || $proPrice != "" || $proQty != ""){
                $table = "trProDetail";
                $arrData = array(
                    array ("db" => "trPDTransID"    , "val" => $trProTransID),
                    array ("db" => "trPDProID"      , "val" => $proID),
                    array ("db" => "trPDPrice"      , "val" => $proPrice),
                    array ("db" => "trPDQty"        , "val" => $proQty),
                    array ("db" => "trPDDisc"       , "val" => "0"),
                    array ("db" => "trPDSubTotal"   , "val" => $proAmount)             
                );
                if (fInsert($table, $arrData, $conn)){
                    // berhasil insert
                }else{
                    $conn->rollback();
                    break;
                    //echo (fSendStatusMessage("error", "Tidak bisa melakukan pembelian saat ini, silahkan hubungi support. #1")); die();
                    return (resultJSON("error", "Tidak bisa melakukan pembelian saat ini, silahkan hubungi support. #1", "")); die();
                }
            }else{
                //echo (fSendStatusMessage("error", "Tidak bisa melakukan pembelian saat ini, silahkan hubungi support. #2")); die();
                return (resultJSON("error", "Tidak bisa melakukan pembelian saat ini, silahkan hubungi support. #2", "")); die();
            }
        }
        unset($arrData);
        $table = "trProduct";
        $arrData = array(
            array ("db" => "trProTransID"   , "val" => $trProTransID),
            array ("db" => "trProUsername"  , "val" => $username),
            array ("db" => "trProUserBeli"  , "val" => $trProUserBeli),
            array ("db" => "trProType"      , "val" => $trProType),
            array ("db" => "trProDate"      , "val" => "CURRENT_TIME()"),
            array ("db" => "trProAmount"    , "val" => $tAmount),
            array ("db" => "trProDisc"      , "val" => "0"),
            array ("db" => "trProStatus"    , "val" => $DEF_STATUS_ONPROGRESS)                
        );
        
        if (!fInsert($table, $arrData, $conn)){
            $conn->rollback();
            //echo (fSendStatusMessage("error", "Tidak bisa melakukan pembelian saat ini, silahkan hubungi support. #3")); die();
            return (resultJSON("error", "Tidak bisa melakukan pembelian saat ini, silahkan hubungi support. #3", "")); die();
        }

        if ($isReseller === true){
            if ($trProUserBeli != "" || $cusEmail != "" || $cusFirstName != "" || $cusLastName != ""){
                $sql  = " SELECT mbrUsername FROM dtMember";
                $sql .= " WHERE mbrUsername = '".$trProUserBeli."' ";
                $sql .= " UNION";
                $sql .= " SELECT ebUsername FROM dtUserEbook";
                $sql .= " WHERE ebUsername = '".$trProUserBeli."' ";
                if ($result = $conn->query($sql)){
                    if ($result->num_rows > 0){
                        $conn->rollback();
                        //echo (fSendStatusMessage("error", "Username Sudah digunakan")); die();
                        return (resultJSON("error", "Username Sudah digunakan", "")); die();
                    }else{
                        //insert dtUserEbook
                        $table = "dtUserEbook";
                        $arrData = array(
                        0 => array ("db" => "ebproTransID"  , "val" => $trProTransID), //samakan dengan tabel order (trProduct) 
                        1 => array ("db" => "ebUsername"    , "val" => $trProUserBeli),
                        2 => array ("db" => "ebEmail"       , "val" => $cusEmail),
                        3 => array ("db" => "ebFirstName"   , "val" => $cusFirstName),
                        4 => array ("db" => "ebLastName"    , "val" => $cusLastName),
                        5 => array ("db" => "ebDate"        , "val" => "CURRENT_TIME()"),
                        6 => array ("db" => "ebStatus"      , "val" => $DEF_STATUS_PENDING)
                    );
                        if (!fInsert($table, $arrData, $conn)){
                        $conn->rollback();
                        fSendToAdminApps($username, "Repeat Order", "trProduct.php", "Insert data to dtUserEbook failed");
                        //echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . mysqli_error($conn)));
                        return(resultJSON("error", "Generate Login Ebook Failed".mysqli_error($conn), ""));
                        die();
                    }
                        unset($arrData);
                        //insert to trPassEbook
                        $table = "trPassEbook";
                        $pePasswd = substr($trProTransID, -6);
                        $arrData = array(
                            0 => array ("db" => "peID"          , "val" => $trProTransID),
                            1 => array ("db" => "peUsername"    , "val" => $trProUserBeli),
                            2 => array ("db" => "pePasswd"      , "val" => md5($pePasswd)),
                            3 => array ("db" => "peDate"        , "val" => "CURRENT_TIME()")
                        );
                        if (!fInsert($table, $arrData, $conn)){
                            $conn->rollback();
                            fSendToAdminApps($username, "Repeat Order", "trProduct.php", "Insert data to trPassEbook failed");
                            //echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . mysqli_error($conn)));
                            return(resultJSON("error", "Generate Login Ebook Failed ".mysqli_error($conn), ""));
                            die();
                        }
                        unset($arrData);
                    }
                }else{
                    return(resultJSON("error", "Something Wrong, please contact support #1", ""));
                    die();
                }
            }else{
                $conn->rollback();
                //echo (fSendStatusMessage("error", "Incomplete Data #1")); die();
                return(resultJSON("error", "Incomplete Data #1 ".mysqli_error($conn), ""));
            }
        }else{
            unset($arrData);
            $sql  = "SELECT * FROM dtMember";
            $sql .= " WHERE mbrUsername = '".$trProUserBeli."' ";
            $result = $conn->query($sql);
            if ($row = $result->fetch_assoc()){
                //insert dtUserEbook
                $table = "dtUserEbook";
                $arrData = array(
                    0 => array ("db" => "ebproTransID"  , "val" => $trProTransID), //samakan dengan tabel order (trProduct) 
                    1 => array ("db" => "ebUsername"    , "val" => $username),
                    2 => array ("db" => "ebEmail"       , "val" => $row['mbrEmail']),
                    3 => array ("db" => "ebFirstName"   , "val" => $row['mbrFirstName']),
                    4 => array ("db" => "ebLastName"    , "val" => $row['mbrLastName']),
                    5 => array ("db" => "ebDate"        , "val" => "CURRENT_TIME()"),
                    6 => array ("db" => "ebStatus"      , "val" => $DEF_STATUS_PENDING)
                );
                if (!fInsert($table, $arrData, $conn)){
                    $conn->rollback();
                    fSendToAdminApps($username, "Repeat Order", "trProduct.php", "Insert data to dtUserEbook failed ".mysqli_error($conn));
                    //echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed #1 </b>"));
                    return(resultJSON("error", "Generate Login Ebook Failed #1 ".mysqli_error($conn), ""));
                    die();
                }
                unset($arrData);
            }else{
                $conn->rollback();
                fSendToAdminApps($username, "Repeat Order", "trProduct.php", "fetch_assoc error".mysqli_error($conn));
                //echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed #3 </b>"));
                return(resultJSON("error", "Generate Login Ebook Failed #3 ".mysqli_error($conn), ""));
                die();
            }   
        }
    }
    $conn->commit();
    //echo (fSendStatusMessage("success", "Silahkan melakukan konfirmasi pembayaran")); die();
    return(resultJSON("success", "Checkout sukses. Silahkan melakukan konfirmasi pembayaran", "")); die();
}

function getPendingOrder($conn, $username){
    global $DEF_STATUS_ONPROGRESS;

    $trProAmount = $trProDisc = 0;
    $sql  = "SELECT * FROM trProduct";
    $sql .= " WHERE trProUsername = '".$username."' AND trProStatus = '".$DEF_STATUS_ONPROGRESS."' ";
    $sql .= " ORDER BY trProDate DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $array  = array();
        while ($row = $result->fetch_assoc()) {
            $trProAmount = $row['trProAmount']; 
            $trProDisc = $row['trProDisc'];
            $trProTransID = $row['trProTransID'];
            $trProDate = $row['trProDate'];

            $returnArrData = array(
                'trProAmount'   => $trProAmount,
                'trProDisc'     => $trProDisc,
                'trProTransID'  => $trProTransID,
                'trProDate'     => $trProDate
            );
        }

        return (resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData = array(
            '' => ''
        );
        return (resultJSON("error", "No Record", $returnArrData));
    } 
}

function getProDetailOrder($conn, $username, $trProTransID = ""){
    
    if ($trProTransID == "") {
        $myDataObj = json_decode(getPendingOrder($conn, $username));
        if ($myDataObj->{"status"} == "success") {
            $trProTransID = $myDataObj->{"data"}->{"trProTransID"}; 
        }
    }
   
    //echo $trProTransID;
    $returnArrData = array();
    if ($trProTransID != "") {
        //return (resultJSON("success", "", $trProTransID));
        $sql  = "SELECT trProUsername, trProUserBeli, mbrFirstName, a.*, b.*, p.* FROM dtMember";
        $sql .= " INNER JOIN trProduct AS a ON mbrUsername = trProUsername";
        $sql .= " INNER JOIN trProDetail AS b ON trPDTransID = trProTransID";
        $sql .= " INNER JOIN msProduct AS p ON proID = trPDProID";
        $sql .= " WHERE trProTransID = '".$trProTransID."' AND mbrUsername = '".$username."' ";

        //echo $sql;
        $result = $conn->query($sql);
        $amount = 0;
        while ($row = $result->fetch_assoc()) {
            $trProUsername  = $row['trProUsername'];
            $trProUserBeli  = $row['trProUserBeli'];
            $mbrFirstName   = $row['mbrFirstName'];
            $orderDate      = $row['trProDate'];
            $orderDate      = date_create($orderDate);
            $orderDate      = date_format($orderDate, "d F Y");

            $proName        = $row['proName'];
            $proPrice       = $row['trPDPrice'];
            $proImage       = $row['proImage'];
            $proBookPage    = $row['proPages'];
            $proBookAuthor  = "Yulianto Hiu";

            $trPDDisc       = $row['trPDDisc'];
            $trPDQty        = $row['trPDQty'];
            $trPDSubTotal   = $row['trPDSubTotal'];

            $returnArrData[] = array(
                'trProTransID'  => $trProTransID,
                'mbrUsername'   => $trProUsername,
                'trProUserBeli' => $trProUserBeli,
                'mbrFirstName'  => $mbrFirstName,
                'orderDate'     => $orderDate,
                'proName'       => $proName,
                'proPrice'      => $proPrice,
                'proImage'      => $proImage,
                'proBookPage'   => $proBookPage,
                'proBookAuthor' => $proBookAuthor,
                'trProDisc'     => $trPDDisc,
                'trProQty'      => $trPDQty,
                'trPDSubTotal'  => $trPDSubTotal
            );
        }
        return (resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData[] = array(
            ''  => ''
        );
        return (resultJSON("error", "No Records", $returnArrData));
    }

}

function postCancelOrder($conn, $proTransID){
    global $DEF_STATUS_CANCEL;
    $arrData = array(
        "trProUpdateDate"   => "CURRENT_TIME()",
        "trProStatus"       => $DEF_STATUS_CANCEL
    );
    $arrDataQuery = array(
        "trProTransID"  => $proTransID
    );

    if (!fUpdateRecord("trProduct", $arrData, $arrDataQuery, $conn)){
        //echo (fSendStatusMessage("error", "<b>Failed to cancel Order No : ".$proTransID." #1 - </b>" . $conn->error));
        return (resultJSON("error", "Failed to cancel order No : ".$proTransID." ".$conn->error, ""));
        $conn->rollback();
        die();
    }else{
        $conn->commit();
        return (resultJSON("success", "Order Cancelled", "")); die();
        //echo (fSendStatusMessage("success", "Order Cancelled")); die();
    }
}

function postPayOrderProduct($conn, $username, $proTransID){
    global $DEF_STATUS_ONPROGRESS, $DEF_BV_PRICE, $DEF_STATUS_APPROVED, $DEF_STATUS_USED, $DEF_STATUS_ACTIVE, $DEF_VOUCHER_USED_FOR_RO,  $DEF_BONUS_RO, $DEF_TYPE_PURCHASE_RS, $DEF_TYPE_PURCHASE_RO;
    $totalPayBV     = 0;
    $trProUserBeli = "";

    //1.1 get total Qty from trProDetail
    $sql  = "SELECT SUM(trPDQty) AS totalQty, trProUserBeli FROM trProduct";
    $sql .= " INNER JOIN trProDetail ON trPDTransID = trProTransID";
    $sql .= " WHERE trProUsername ='".$username."' AND trProStatus = '".$DEF_STATUS_ONPROGRESS."' AND trProTransID ='".$proTransID."'";
    // echo (fSendStatusMessage("error", $sql)); die();
    if ($result = $conn->query($sql)){
        if ($result->num_rows > 0){
            if ($row=$result->fetch_assoc()){
                $totalPayBV = $row['totalQty'] * $DEF_BV_PRICE;
                $trProUserBeli = $row['trProUserBeli'];
            }
        }
    }else{
        //echo (fSendStatusMessage("error", $conn->error)); die();
        return (resultJSON("error", $conn->error, "")); die();
    }


    //1.2 Checking number of voucher Required AND Voucher Balance
    if ($totalPayBV > 0){
        if (fmod($totalPayBV, $DEF_BV_PRICE) != 0){ //validasi modulus
            //echo (fSendStatusMessage("error", "Please try again later or contact our Support Team #1")); die();
            return (resultJSON("error", "Please tre again later or contact our Support Team #1", "")); die();
        }
        $numOfVoucherRequired = ceil($totalPayBV / $DEF_BV_PRICE);  //Number of Voucher Required (@200)
        //checking Voucher Balance
        $sql = "SELECT fivFinID, fivVCode FROM ((dtFundIn ";
        $sql .= " inner join dtFundInVoucher on finID = fivFinID and finStatus='" . $DEF_STATUS_APPROVED . "')";
        $sql .= " inner join dtVoucher on vCode = fivVCode and vStatus = '" . $DEF_STATUS_USED . "'";
        $sql .= " and fivStatus = '" . $DEF_STATUS_ACTIVE ."')";
        $sql .= " WHERE finMbrUsername='" . $username . "'";
        $arrVoucher = array();
        if ($result = $conn->query($sql)){
            if ($result->num_rows > 0){
                while ($row = $result->fetch_assoc()){
                    //$VoucherBalance   = $row["VoucherBalance"];
                    $arrVoucher[] = array("fivFinID" => $row["fivFinID"], "fivVCode" => $row["fivVCode"]);  
                }
            }
        }else{
            //echo (fSendStatusMessage("error", $conn->error)); die();
            return (resultJSON("error", $conn->error, "")); die();
        }

        $VoucherBalance = sizeof($arrVoucher);
        if ($numOfVoucherRequired > $VoucherBalance){ //VoucherBalance not enough
            //echo (fSendStatusMessage("error", "Your Balance is not enough")); die();
            return (resultJSON("error", "Your Balance is not enough", "")); die();
        }
    }

    //1.3 Update dtFundInVoucher (status="USED", usedFor="ACTIVATION", usedOn=USERNAME, fivDate="CURRENT_TIME()")
    $arrData    = array(
        "fivStatus"     => $DEF_STATUS_USED,
        "fivUsedFor"    => $DEF_VOUCHER_USED_FOR_RO,
        "fivUserOn"     => $username,
        "fivDate"       => "CURRENT_TIME()"
    );
    
    $arrDataQuery = array();
    $counter = 0;
    //moving some data of arrVoucher to arrDataQuery 
    foreach ($arrVoucher as $key => $value){
        if ($counter >= $numOfVoucherRequired) {
            break;
        }else{
            $arrDataQuery = array (
                "fivFinID" => $value["fivFinID"], 
                "fivVCode" => $value["fivVCode"]
            );
            $counter++;
            if (!fUpdateRecord("dtFundInVoucher", $arrData, $arrDataQuery, $conn)){
                $conn->rollback();
                //echo (fSendStatusMessage("error", "<b>Update FundInVoucher - </b>" . $conn->error)); die();
                return (resultJSON("error", "Update FoundInVOucher - ".$conn->error, "")); die();
            }
            unset($arrDataQuery);
        }
    }
    unset($arrData);
    unset($arrDataQuery);

    //1.4 update product payment status
    $arrData = array(
        "trProUpdateDate"   => "CURRENT_TIME()",
        "trProActiveDate"   => "CURRENT_TIME()",
        "trProStatus"       => $DEF_STATUS_APPROVED
    );
    $arrDataQuery = array(
        "trProTransID"  => $proTransID,
        "trProUsername" => $username
    );

    //var_dump($arrDataQuery);
    if (!fUpdateRecord("trProduct", $arrData, $arrDataQuery, $conn)){
        //echo (fSendStatusMessage("error", "<b>Update trProduct - </b>" . $conn->error)); die();
        $conn->rollback();
        return (resultJSON("error", "Update trProduct".$conn->error, "")); 
        die();
    }else{
        //update data from dtUserEbook
        unset($arrData);
        unset($arrDataQuery);
        $arrData = array(
            "ebDate"    => "CURRENT_TIME()",
            "ebStatus"  => $DEF_STATUS_ACTIVE
        );
        $arrDataQuery = array (
            "ebproTransID"  => $proTransID,
            "ebUsername"    => $trProUserBeli
        );
        if (!fUpdateRecord("dtUserEbook", $arrData, $arrDataQuery, $conn)){
            $conn->rollback();
            fSendToAdminApps($username, "Repeat Order", "trProduct.php", "Insert data to dtUserEbook failed");
            //echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . $conn->error));
            return resultJSON("error", "Generate Login Ebook Failed". $conn->error, "");
            die();
        }
    }

    //1.5 email notification
    $sql  = "SELECT trProTransID, trProType, mbrSponsor";
    $sql .= " FROM trProduct";
    $sql .= " INNER JOIN dtUserEbook ON ebProTransID = trProTransID";
    $sql .= " INNER JOIN dtMember ON mbrUsername = trProUsername";
    $sql .= " WHERE trProTransID = '".$proTransID."'";
    $sql .= " ORDER BY trProUpdateDate DESC LIMIT 1";
    $result=$conn->query($sql);
    if ($result->num_rows > 0){
        if ($row=$result->fetch_assoc()){
            $trProType = $row['trProType'];
            if ($trProType == $DEF_TYPE_PURCHASE_RS){
                $BnsROUsername = $username;
            }else if ($trProType == $DEF_TYPE_PURCHASE_RO){
                $BnsROUsername = $row['mbrSponsor'];
            }
            unset($arrData);
            unset($arrDataQuery);
            //hitung bonus repeat order
            $table = "dtBnsRO";
            $bnsRO = $totalPayBV * $DEF_BONUS_RO / 100;
            $arrData = array(
                array ("db" => "BnsROID"        , "val" => $proTransID),
                array ("db" => "BnsROUsername"  , "val" => $BnsROUsername),
                array ("db" => "BnsROAmount"    , "val" => $bnsRO),
                array ("db" => "BnsRODate"      , "val" => "CURRENT_TIME()")             
            );
            if (!fInsert($table, $arrData, $conn)){
                $conn->rollback();
                fSendToAdminApps($username, "Repeat Order", "trProduct.php", "Insert data to dtBnsRO failed");
                return (resultJSON("error", "Failed save bonus RO". $conn->error, ""));
            }
        }
    }

    $conn->commit(); // save dulu baru kirim email
    if ($trProType == $DEF_TYPE_PURCHASE_RS){
        //email payment success ke pembeli (RO)
        fToCornEmail($conn, 'BUY_PRODUCT_RS', '', $row['trProTransID']);
    }else if ($trProType == $DEF_TYPE_PURCHASE_RO){
        //email user dan pass ke pembeli (reseller)
        fToCornEmail($conn, 'BUY_PRODUCT', '', $row['trProTransID']);
    }
    //echo (fSendStatusMessage("success", "Payment success")); die();
    return (resultJSON("success", "Payment success", "")); die();
}


function getProPaid($conn, $username){
    global $DEF_STATUS_APPROVED;
    $sql  = "SELECT * FROM trProduct";
    $sql .= " WHERE trProUsername = '".$username."' AND trProStatus = '".$DEF_STATUS_APPROVED."' AND trProType != 'MUTASI' ";
    $sql .= " Order BY trProUpdateDate DESC";
    $result = $conn->query($sql);
    $tProPaidttl = $result->num_rows;
    $returnArrData = array();
    if ($tProPaidttl == 0) {
        $returnArrData[] = array(
            ''  => ''
        );
        return (resultJSON("error", "No Record", $returnArrData));
    }else{
        //$arrData = array();
        while ($row = $result->fetch_assoc()) {
            $tAmount = $row['trProAmount'] - $row['trProDisc'] ;                                        
            $trProTransID       = $row['trProTransID'];
            $trProAmount        = $row['trProAmount'];
            $trProDisc          = $row['trProDisc'];
            $tAmount            = $tAmount;
            $trProUpdateDate    = $row['trProUpdateDate'];
            $trProUpdateDate    = date_create($trProUpdateDate);
            $trProUpdateDate    = date_format($trProUpdateDate, "d F Y");
            $trProType          = $row['trProType'];
            $trProUserBeli      = $row['trProUserBeli'];

            $returnArrData[] = array(
                'trProTransID'      => $trProTransID,
                'trProAmount'       => $trProAmount,
                'trProDisc'         => $trProDisc,
                'tAmount'           => strval($tAmount),
                'trProUpdateDate'   => $trProUpdateDate,
                'trProType'         => $trProType,
                'trProUserBeli'     => $trProUserBeli
            );
        }
        return (resultJSON("success", "", $returnArrData));
    }
}

function getProCancel($conn, $username){
    global $DEF_STATUS_CANCEL;
    $sql  = "SELECT * FROM trProduct";
    $sql .= " WHERE trProUsername = '".$username."' AND trProStatus = '".$DEF_STATUS_CANCEL."' ";
    $sql .= " Order BY trProUpdateDate DESC";
    $result = $conn->query($sql);
    $tproCanceledttl = $result->num_rows;
    $returnArrData = array();
    if ($tproCanceledttl == 0) {
        $returnArrData[] = array(
            ''  => ''
        );
        return (resultJSON("error", "No Record", $returnArrData));
    }else{
        //$arrData = array();
        while ($row = $result->fetch_assoc()) {
            $tAmount = $row['trProAmount'] - $row['trProDisc'] ;                                        
            $trProTransID       = $row['trProTransID'];
            $trProAmount        = $row['trProAmount'];
            $trProDisc          = $row['trProDisc'];
            $tAmount            = $tAmount;
            $trProUpdateDate    = $row['trProUpdateDate'];
            $trProUpdateDate    = date_create($trProUpdateDate);
            $trProUpdateDate    = date_format($trProUpdateDate, "d F Y");
            $trProType          = $row['trProType'];

            $returnArrData[] = array(
                'trProTransID'      => $trProTransID,
                'trProAmount'       => $trProAmount,
                'trProDisc'         => $trProDisc,
                'tAmount'           => strval($tAmount),
                'trProUpdateDate'   => $trProUpdateDate,
                'trProType'         => $trProType
            );
        }
        return (resultJSON("success", "", $returnArrData));
    }
}

function getReadEbook($conn, $username){
    global $DEF_STATUS_APPROVED, $DEF_EBOOK_BASIC, $DEF_EBOOK_PRO, $DEF_STATUS_APPROVED;

    $sql  = "SELECT proID, proPrice, proName, proImage, proPages, trProTransID, trPDTransID, trProUpdateDate, trProStatus";
    $sql .= " FROM msProduct";
    $sql .= " LEFT JOIN (";
    $sql .= "   SELECT * FROM trProduct";
    $sql .= "   INNER JOIN trProDetail ON trPDTransID = trProTransID";
    $sql .= "   INNER JOIN dtUserEbook ON ebUsername = trProUserBeli";
    $sql .= "   WHERE ebUsername = '".$username."' AND trProStatus = '".$DEF_STATUS_APPROVED."'";
    $sql .= "   GROUP BY trPDProID";
    $sql .= " ) AS trpro ON trpro.trPDProID = proID";
    // echo $sql;
    $result = $conn->query($sql);
    $flagRead = "false";
    $returnArrData = array();
    if ($result->num_rows>0){
        while ($row = $result->fetch_assoc()){
        
            $proID          = $row['proID'];
            $proName        = $row['proName'];
            $proPrice       = $row['proPrice'];
            $proImage       = $row['proImage'];
            $proBookPage    = $row['proPages'];
            $proBookAuthor  = "Yulianto Hiu";
            $trProStatus    = $row['trProStatus'];

            if ($trProStatus == $DEF_STATUS_APPROVED){
                $flagRead = "true";
            }else{
                $flagRead = "false";
            }

            $returnArrData[] = array(
                'proID'         => $proID,
                'proName'       => $proName." Edition",
                'proPrice'      => $proPrice,
                'proImage'      => $proImage,
                'proBookPage'   => $proBookPage,
                'proBookAuthor' => $proBookAuthor,
                "flagRead"       => $flagRead
            );
        }
        return (resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData[] = array(
            '' => ''
        );
        return (resultJSON("error", "No Records", $returnArrData));
    }
}


function getTotalComission($conn, $username){
    
    $returnArrData = array();
    $myDataObj  = json_decode(fGetBalance($username, $conn));

    if ($myDataObj->{"status"} == "success"){
        $ttlCommission     = $myDataObj->{'ttlCommission'};

        $returnArrData = array(
            "ttlCommission" => strval($ttlCommission)
        );

        return (resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData = array(
            ''  => ''
        );
        return (resultJSON("error", "username not found", $returnArrData));
    }
}

function getPayAcc($conn, $username){
    // $arrData = array();
    // $myDataObj = json_decode(fGetPayAcc($username, $conn));
    $payAccName = $payAcc = $payPTID = $ptDesc = "";
    global $DEF_STATUS_ACTIVE;
    $sql = "SELECT pay.*, pt.ptCat, pt.ptDesc FROM dtPaymentAcc pay INNER JOIN msPaymentType pt on payPTID=ptID";
    $sql .= " WHERE payMbrUsername='" . $username . "' AND payStatus='" . $DEF_STATUS_ACTIVE . "'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        if ($row = $result->fetch_assoc()) {
            $payAcc     = $row['payAcc'];
            $payAccName = $row['payAccName'];
            $payPTID    = $row['payPTID'];
            $ptDesc     = $row['ptDesc'];

            $returnArrData = array(
                'payAcc'        => $payAcc,
                'payAccName'    => $payAccName,
                'payPTID'       => $payPTID,
                'ptDesc'        => $ptDesc
            );

            return (resultJSON("success", "", $returnArrData));
        }
    }else{
        $returnArrData = array(
            'payAcc'        => '',
            'payAccName'    => '',
            'payPTID'       => '',
            'ptDesc'        => ''
        );

        return (resultJSON("success", "", $returnArrData));
    }
    // echo $sql;
    // $query = $conn->query($sql);
    // if ($myDataObj->{"status"} == "success"){
    //     $balanceAcc     = $myDataObj->{'payAcc'};
    //     $balanceAccName = $myDataObj->{'payAccName'};
    //     $balanceAccDesc = $myDataObj->{'payAccDesc'};

    //     $arrData = array(
    //         'payAcc'        => $balanceAcc,
    //         'payAccName'    => $balanceAccName,
    //         'payAccDesc'    => $balanceAccDesc
    //     );

    //     return (resultJSON("success", "", $arrData));
    // }else{
    //     return (resultJSON("error", "username not found", $arrData));
    // }
}

function postPayAcc($conn, $username, $accNumber, $accName, $accType, $accCode){
    global $DEF_STATUS_ACTIVE;
    if (fCekVerification($conn, $username, $accName)){
        //Sudah Verifikasi ID
        $arrData = array(
            0 => array ("db" => "payMbrUsername", "val" => $username),
            1 => array ("db" => "payAcc"        , "val" => $accNumber),
            2 => array ("db" => "payAccName"    , "val" => $accName),
            3 => array ("db" => "payPTID"       , "val" => $accType),
            4 => array ("db" => "payCode"       , "val" => $accCode),
            5 => array ("db" => "payStatus"     , "val" => $DEF_STATUS_ACTIVE),
            6 => array ("db" => "payDate"   , "val" => "CURRENT_TIME()")
        );
        if (fInsert("dtPaymentAcc", $arrData, $conn)){
            //insert success
            //send email for activation
            fSendNotifToEmail("UPDATE_PAYMENT_ACCOUNT", $username);
            return (resultJSON("success", "Update Account Bank Successfull", ""));
            $conn->close();
            die();   
        }else{
            //insert fail   
            return (resultJSON("error", "Update Account Bank Failed", ""));
        } // end else
    }else{
        return (resultJSON("error", "Nama tidak sesuai dengan KTP", ""));
    }
}

function postReqWD($conn, $username, $payAcc, $secPasswd, $amount, $wdtax){
    global $DEF_STATUS_REQUEST;
    //Validation inputs
    if ($payAcc != "" && $username != "" && $secPasswd != "" && $amount != ""  && $wdtax != "" && $amount > 0  && $wdtax > 0 ){
        //Checking data before save the request
        //check email, security password and existing ttlCommission
        $ttlCommission = 0;
        //Check Verified ID
        if (fCekVerification($conn, $username)){ //Sudah Verifikasi ID/KTP
        //check ONProgress Request WD
            if (!fCheckONProgressWD($username, $conn)){
                //check Email 
                //if (!fCheckEmail($username, $email, $conn)){
                //  $responseMessage .= "Invalid Email Address<br>";
                //}else{
                    //Check Security Password
                if (!fCheckSecurityPassword($username, $secPasswd, $conn)){
                    return (resultJSON("error", "Security Password not match", ""));
                }else{
                    $myDataObj  = json_decode(fGetBalance($username, $conn));
                    if ($myDataObj->{"status"} == "success"){
                        $ttlCommission     = $myDataObj->{'ttlCommission'};
                        if ($ttlCommission >= $amount && $amount > 0){
                            if ($payAcc != ""){
                                $conn->autocommit(false);
                                $wdID   = strtotime("now").rand(10000, 99999); //length 15
                                $wdID   = substr($wdID, 0, 15); //make sure max length = 15
                                $wdCode = rand(1000, 9999);
                                $arrData = array(
                                    0 => array ("db" => "wdID"           , "val" => $wdID),
                                    1 => array ("db" => "wdMbrUsername"  , "val" => $username),
                                    2 => array ("db" => "wdDate"         , "val" => "CURRENT_TIME()"),
                                    3 => array ("db" => "wdAmount"       , "val" => $amount),
                                    4 => array ("db" => "wdTax"          , "val" => $wdtax),
                                    5 => array ("db" => "wdPayAcc"       , "val" => $payAcc),
                                    6 => array ("db" => "wdCode"         , "val" => $wdCode),
                                    7 => array ("db" => "wdStID"         , "val" => $DEF_STATUS_REQUEST) 
                                );
                                $table  = "dtWDFund"; 
                                if (fInsert($table, $arrData, $conn)){
                                    //insert success
                                    //Send confirmation email
                                    $conn->commit();
                                    if (fSendNotifToEmail("REQUEST_WD", $wdID)){
                                    return (resultJSON("success", "Request Withdrawal Successfull. To complete the withdrawal process look for an email in your inbox that provides further instructions.", ""));
                                        //redirect to success page
                                        //header("Location: reqWD.php?q=info-success");
                                    }else{
                                        return (resultJSON("success", "You have made inquiry of withdrawal, but email failed to send", ""));
                                    }
                                }else{
                                    return (resultJSON("error", "Submit Request Withdrawal Failed", ""));
                                } // end else
                            }else{
                                return (resultJSON("error", "Your balance account does not exist yet", ""));
                            }
                        }//end $ttlCommission >= $amount
                        else{
                            return (resultJSON("error", "Your balance is not enough", "")); 
                        }
                    }else{
                        return (resultJSON("error", "Error getting balance", "")); 
                    }
                }
                //}
            }else{
                return (resultJSON("error", "You can not submit new withdrawal while your request on progress", ""));  
            }
        }else{
            return (resultJSON("error", "Anda belum melakukan verifikasi ID/KTP", "")); 
        }
    }else{
        //Data not complite
        return (resultJSON("error", "Incomplete data", ""));  
    }
}

function getApprovedPendingWD($conn, $username){
    global $DEF_STATUS_ONPROGRESS, $DEF_STATUS_APPROVED, $DEF_STATUS_REQUEST, $DEF_MUTASI_DATE;
    $returnArrData = array();

    $sql = "SELECT wdMbrUsername, wdAmount, wdPayAcc, stDesc, ptDesc, wdDate, wdTax FROM dtWDFund ";
    $sql .= " INNER JOIN dtPaymentAcc ON payMbrUsername=wdMbrUsername AND payAcc=wdPayAcc ";
    $sql .= " INNER JOIN msPaymentType ON ptID = payPTID ";
    $sql .= " INNER JOIN msStatus ON stID = wdStID ";
    $sql .= " WHERE wdMbrUsername='".$username."' ";
    $sql .= " AND (wdStID='".$DEF_STATUS_ONPROGRESS."' OR wdStID='".$DEF_STATUS_APPROVED . "' OR wdStID ='".$DEF_STATUS_REQUEST."')";
    $sql .= " AND date(wdDate) >= '".$DEF_MUTASI_DATE."' ";
    $sql .= " Order BY wdDate DESC";

    if ($result = $conn->query($sql)) {
         if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                
                $wdDate     = $row["wdDate"];
                $ptDesc     = $row["ptDesc"];
                $wdPayAcc   = $row["wdPayAcc"];
                $wdAmount   = $row["wdAmount"];
                $wdTax      = $row["wdTax"];
                $stDesc     = $row["stDesc"];

                $returnArrData[] = array(

                    'wdDate'    => $wdDate,
                    'ptDesc'    => $ptDesc,
                    'wdPayAcc'  => $wdPayAcc,
                    'wdAmount'  => numFormat($wdAmount, 0),
                    'wdTax'     => numFormat($wdTax, 0),
                    'stDesc'    => $stDesc

                );
            }
            return (resultJSON("success", "", $returnArrData));
        }else{
             $returnArrData[] = array(

                    'wdDate'    => '',
                    'ptDesc'    => '',
                    'wdPayAcc'  => '',
                    'wdAmount'  => '',
                    'wdTax'     => '',
                    'stDesc'    => ''
            );
            return (resultJSON("success", "No Records", $returnArrData));
        }
    }else{
        return(resultJSON("error", "query error".$conn->error, ""));
    }  
}

function getDeclinedWD($conn, $username){
    global $DEF_MUTASI_DATE, $DEF_STATUS_DECLINED;
    $sql = "SELECT wdMbrUsername, wdAmount, wdPayAcc, stDesc, ptDesc, wdDate, wdDesc FROM dtWDFund ";
    $sql .= " INNER JOIN dtPaymentAcc ON payMbrUsername=wdMbrUsername AND payAcc=wdPayAcc ";
    $sql .= " INNER JOIN msPaymentType ON ptID = payPTID ";
    $sql .= " INNER JOIN msStatus ON stID = wdStID ";
    $sql .= " WHERE wdMbrUsername='".$username."' AND wdStID='".$DEF_STATUS_DECLINED."' ";
    $sql .= " AND date(wdDate) >= '".$DEF_MUTASI_DATE."'";
    $sql .= " Order BY wdDate DESC";

    $returnArrData = array();
    if ($result = $conn->query($sql)) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                
                $wdDate     = $row["wdDate"];
                $ptDesc     = $row["ptDesc"];
                $wdPayAcc   = $row["wdPayAcc"];
                $wdAmount   = $row["wdAmount"];
                $stDesc     = $row["stDesc"];
                $wdDesc     = $row["wdDesc"];

                $returnArrData[]  = array(

                    'wdDate'    => $wdDate,
                    'ptDesc'    => $ptDesc,
                    'wdPayAcc'  => $wdPayAcc,
                    'wdAmount'  => numFormat($wdAmount, 0),
                    'stDesc'    => $stDesc,
                    'wdDesc'    => $wdDesc

                );
            }

            return (resultJSON("success", "", $returnArrData));
        }else {

            $returnArrData[] = array(

                'wdDate'    => '',
                'ptDesc'    => '',
                'wdPayAcc'  => '',
                'wdAmount'  => '',
                'stDesc'    => '',
                'wdDesc'    => ''

            );

            return (resultJSON("success", "No Records", $returnArrData));
        }
    }else{
        return(resultJSON("error", "query error".$conn->error, ""));
    }
}


function getRenewPac($conn, $username){
    global $DEF_VOUCHER_PRICE_IDR;
    $username    = (isset($_POST["username"]))?fValidateSQLFromInput($conn, $_POST["username"]): "";
    $username    = strtolower($username);

    $returnArrData = array();
    // hitung jumlah voucher tersedia
    $numOfVoucher = $VoucherBalance = 0;
    $arrVoucher = "";
    $myDataObj  = json_decode(fSumAvailableVoucher($username, $conn));
    if ($myDataObj->{"status"} == "success"){
        $numOfVoucher = sizeof($myDataObj->data);
        $VoucherBalance = $numOfVoucher * $DEF_VOUCHER_PRICE_IDR;
        //$arrVoucher = $myDataObj->data;

        $returnArrData = array(
            "voucherBalance"    => numFormat($VoucherBalance, 0),
            "defaultPacPrice"   => numFormat($DEF_VOUCHER_PRICE_IDR, 0)
        );

        return (resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData = array(
            ''  => ''
        );
        return (resultJSON("error", "username not found", $returnArrData));
    }
}


function postRenewPac($conn, $actUsername, $securityPasswd, $renewPac, $tvBalance, $pacPrice){
    global $DEF_VOUCHER_PRICE_IDR, $DEF_WALLET_PRICE, $DEF_STATUS_UPGRADE, $DEF_STATUS_USED, $DEF_VOUCHER_USED_FOR_ACTIVATION, $DEF_EBOOK_BASIC, $DEF_STATUS_APPROVED, $DEF_STATUS_ACTIVE ;

    
    $numOfVoucher = $VoucherBalance = 0;
    $arrVoucher = "";
    $myDataObj  = json_decode(fSumAvailableVoucher($actUsername, $conn));
    if ($myDataObj->{"status"} == "success"){
        $numOfVoucher = sizeof($myDataObj->data);
        $VoucherBalance = $numOfVoucher * $DEF_VOUCHER_PRICE_IDR;
        $arrVoucher = $myDataObj->data;
    }

    $isAllowRenew = true;
    //get trThn
    $sql = "SELECT m.*, s.mbrUsername as spUsername, s.mbrFirstName as spName, u.mbrUsername as upUsername, u.mbrFirstName as upName, c.countryDesc, pacID, pacName, trThn FROM dtMember m ";
    $sql .= " INNER JOIN dtMember s on m.mbrSponsor = s.mbrUsername ";
    $sql .= " INNER JOIN dtMember u on m.mbrUpline = u.mbrUsername ";
    $sql .= " INNER JOIN msCountry c on m.mbrCountry = c.countryID ";
    $sql .= " INNER JOIN (SELECT * FROM Transaction WHERE trID = (SELECT trID FROM Transaction WHERE trUsername='". $actUsername. "' ORDER BY trDate DESC LIMIT 1)) t ON m.mbrUsername = t.trUsername ";
        $sql .= " INNER JOIN msPackage ON pacID = t.trPacID";

    $sql .= " WHERE m.mbrUsername = '" . $actUsername . "'";

    if ($query = $conn->query($sql)){
        if ($row = $query->fetch_assoc()){  
            $trThn = $row['trThn'];
            $sql = "SELECT mbrDate, (DATE_ADD(DATE_ADD( DATE(mbrDate), INTERVAL ".$trThn." YEAR ), INTERVAL -7 MONTH)) AS allowRenewDate, ";
            $sql .= " IF( DATE_ADD(DATE_ADD( DATE(mbrDate), INTERVAL ".$trThn. " YEAR ), INTERVAL -7 MONTH) <= CURRENT_DATE() , 'allowed', 'notallowed') renew ";
            $sql .= " FROM dtMember WHERE mbrUsername='" . $actUsername . "'";
            unset($result);
            $result = $conn->query($sql);
            if ($row=$result->fetch_assoc()){
                if ($row['renew'] != "allowed"){
                    $allowRenewDate = date_create($row['allowRenewDate']);
			        $allowRenewDate = date_format($allowRenewDate, "Y-m-d");
                    $isAllowRenew = false;
                }
            }
        }else{
            return (resultJSON("error", "error getData #1", ""));
            die();
        }
    }
    
        
    // echo (fSendStatusMessage("error", print_r($_POST))); die();
    if ($actUsername != "" && $securityPasswd != "" && $renewPac != "" && $pacPrice != ""){
        if($isAllowRenew){
            //Check Security Password
            if (!fCheckSecurityPassword($actUsername, $securityPasswd, $conn)){
                 return (resultJSON("error", "Security Password not match", "")); die();
            }else{
                //Get Package Price
                /* disabled because use default package (st)
                $pacPrice = $numOfVoucherRequired = 0;
                $sql = "SELECT pacPrice FROM msPackage WHERE pacID='" . $renewPac . "'";
                if ($query = $conn->query($sql)){
                    if ($query->num_rows > 0){
                        $row = $query->fetch_assoc();
                        $pacPrice =  $row["pacPrice"];
                    }
                }else{
                    echo (fSendStatusMessage("error", mysqli_error($conn))); die();
                }
                */
                $additionFee = $pacPrice;
                if ($additionFee > 0){
                    $numOfVoucherRequired = ceil($additionFee / $DEF_VOUCHER_PRICE_IDR);   //Number of Voucher Required (@200)
                    //checking Voucher Balance
                    
                    if ($numOfVoucherRequired > $numOfVoucher){ // if true Total Amount not enough
                        return (resultJSON("error", "Your Wallet Balance is not enough #1", ""));
                        die();
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
                            //echo (fSendStatusMessage("error", "<b>Update Transaction - </b>" . mysqli_error($conn)));
                            return (resultJSON("error", "Update Transaction - ".mysqli_error($conn), ""));
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
                        $newSponsorBonus    = fGetBonus("SPONSOR", 'st', $spPacID, $conn);
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
                            // echo (fSendStatusMessage("error", "$actUsername || $sponsorUsername || $renewPac || $sponsorBonus")); $conn->rollback(); die();
                            if (!fInsert("dtBnsSponsor", $arrData, $conn)) {
                                //echo (fSendStatusMessage("error", "<b>Update Bonus Sponsor - </b>" . $conn->error));
                                return (resultJSON("error", "Update Bonus Sponsor - ".$conn->error, ""));
                                $conn->rollback();
                                die();
                            }
                            unset($arrData);
                        }else{
                            //if sponsor bonus == 0, means error
                            //echo (fSendStatusMessage("error", "<b>Get Bonus Sponsor Failed</b>"));
                            return (resultJSON("error", "Get Bonus Sponsor Failed", ""));
                            $conn->rollback();
                            die();
                        }

                        //Update dtFundInVoucher (status="USED", usedFor="ACTIVATION", usedOn=USERNAME)
                        $arrData    = array(
                            "fivDate"       => "CURRENT_TIME()",
                            "fivStatus"     => $DEF_STATUS_USED,
                            "fivUsedFor"    => $DEF_VOUCHER_USED_FOR_ACTIVATION,
                            "fivUserOn"     => $actUsername
                        );
                        
                        $arrDataQuery = array();
                        $counter = 0;
                        //moving some data of arrVoucher to arrDataQuery 
                        foreach ($arrVoucher as $key => $value){
                            // if ($counter >= $numOfVoucherRequired) {
                            if ($counter >= $numOfVoucherRequired) {
                                break;
                            }else{
                                $arrDataQuery = array (
                                    "fivFinID"  => $value->fivFinID,
                                    "fivStatus" => $DEF_STATUS_ACTIVE,
                                    "fivVCode"  => $value->fivVCode
                                );
                                $counter++;
                                
                                if (!fUpdateRecord("dtFundInVoucher", $arrData, $arrDataQuery, $conn)){
                                    //echo (fSendStatusMessage("error", "<b>Update FundInVoucher - </b>" . $conn->error));
                                     return (resultJSON("error", "Update FoundInVoucher - ".$conn->error, ""));
                                    $conn->rollback();
                                    die();
                                }
                                unset($arrDataQuery);
                            }
                        }
                        unset($arrData);

                        $sql  = " SELECT * FROM msProduct";
                        $sql .= " WHERE proID = '".$DEF_EBOOK_BASIC."' ";
                        $result = $conn->query($sql);
                        if ($row = $result->fetch_assoc()){
                            $trProTransID = strtotime("+0");
                            $table = "trProduct";
                            $arrData = array(
                                array ("db" => "trProTransID"       , "val" => $trProTransID),
                                array ("db" => "trProUsername"      , "val" => $sponsorUsername),
                                array ("db" => "trProUserBeli"      , "val" => $actUsername),
                                array ("db" => "trProType"          , "val" => 'RENEW'),
                                array ("db" => "trProDate"          , "val" => "CURRENT_TIME()"),
                                array ("db" => "trProAmount"        , "val" => $row['proPrice']),
                                array ("db" => "trProDisc"          , "val" => $row['proPrice']),
                                array ("db" => "trProUpdateDate"    , "val" => "CURRENT_TIME()"),
                                array ("db" => "trProActiveDate"    , "val" => "CURRENT_TIME()"),
                                array ("db" => "trProStatus"        , "val" => $DEF_STATUS_APPROVED)                
                            );
                            if (!fInsert($table, $arrData, $conn)){
                                $conn->rollback();
                                fSendToAdminApps($actUsername, "Activate Member", "activateMember.php", "Insert data to trProduct failed");
                                //echo (fSendStatusMessage("error", "<b>Record produk - </b>" . mysqli_error($conn)));
                                return (resultJSON("error", "Record product - ".mysqli_error($conn), ""));
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
                                    fSendToAdminApps($actUsername, "Renew Member", "renewPac.php", "Insert data to trProDetail failed");
                                    //echo (fSendStatusMessage("error", "<b>Record produk detail - </b>" . mysqli_error($conn)));
                                    return (resultJSON("error", "Record product detail - ". mysqli_error($conn)));
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
                        //echo (fSendStatusMessage("success", "")); die();
                        return (resultJSON("success", "Renew Successfull", ""));
                        die();
                        //$conn->close(); 
                        
                        //die();
                    }
                }
            }//end checking security password
        }else{
            return resultJSON("error", "Renew Not Allowed, you can renew again start from $allowRenewDate", ""); die();
        }     
    }else{
        //echo (fSendStatusMessage("error", "Incomplete Data")); die();
         return (resultJSON("error", "Incomplete Data", ""));
    }
}


function postBuyVoucher($conn, $username, $amount, $accType){
    global $DEF_STATUS_PENDING, $DEF_VOUCHER_TYPE_STD;
    $finID    = $username.strtotime("now");  //same format used in transfer voucher (doTransfer.php)
    //$accType  =  (isset($_POST["accType"]))?fValidateSQLFromInput($conn, $_POST["accType"]): "";
    $voucherType = $DEF_VOUCHER_TYPE_STD;;
    $fromAccNo= ""; // (isset($_POST["fromAccNo"]))?fValidateSQLFromInput($conn, $_POST["fromAccNo"]): "";
    //$amount   =  (isset($_POST["amount"]))?fValidateSQLFromInput($conn, $_POST["amount"]): "0";
    $curr     = "IDR";
    $curs     = "1";
    $accName  = "";
    $toAccNo  = ""; 
    $IDTrans  = "";
    $status   = $DEF_STATUS_PENDING;
    $approvedBy = "";
    $sql  = "SELECT * FROM dtFundIn";
    $sql .= " WHERE finMbrUsername = '".$username."' AND finStatus = '".$DEF_STATUS_PENDING."' ";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        //Validation inputs
        if ($username != "" && $accType != ""  && $amount != ""){
            $arrData = array(
                0 => array ("db" => "finID"       , "val" => $finID),
                1 => array ("db" => "finMbrUsername"  , "val" => $username),
                2 => array ("db" => "finAmount"     , "val" => $amount),
                3 => array ("db" => "finCurr"     , "val" => $curr),
                4 => array ("db" => "finCurs"     , "val" => $curs),
                5 => array ("db" => "finAccName"    , "val" => $accName),
                6 => array ("db" => "finAccType"    , "val" => $accType),
                7 => array ("db" => "finVoucherType"    , "val" => $voucherType),
                8 => array ("db" => "finFromAccNo"    , "val" => $fromAccNo),
                9 => array ("db" => "finToAccNo"    , "val" => $toAccNo),
                10 => array ("db" => "finTransactionID"  , "val" => $IDTrans),
                11 => array ("db" => "finDate"     , "val" => "CURRENT_TIME()"),
                12 => array ("db" => "finStatus"     , "val" => $status),
                13 => array ("db" => "finApprovedBy"   , "val" => $approvedBy)
            );

            //insert success
            $table  = "dtFundIn"; 
            if (fInsert($table, $arrData, $conn)){
                //send invoice (confirmation) to client's email
                if (fSendNotifToEmail("REQUEST_BUY_VOUCHER", $finID)){
                    //send email success
                    //redirect to success page
                    //header("Location: reqBuyVoucher.php?q=info_request_success");
                    return(resultJSON("success", "Request Buy PIN Successfull", ""));
                    die();
                }else{
                    //send email failed
                    //$responseMessage = "Email failed to send. Please contact support"; 
                    return(resultJSON("success", "Email failed to send. Please contact support", ""));
                }
            }else{
                //$responseMessage = "Submit Request to Buy PIN Failed";
                return(resultJSON("error", "Submit Request to Buy PIN Failed", "")); 
            } // end else
        }else{
            //Data not complite
            //$responseMessage = "Submit Request to Buy PIN Failed - Data not Complite"; 
            return(resultJSON("error", "Submit Request to Buy PIN Failed - Incomplete data", "")); 
        }
    }else{
        return(resultJSON("error", "Please complete your previous request", ""));
    }
}

function getCekBuyVoucher($conn, $username){
    global $DEF_STATUS_PENDING, $DEF_VOUCHER_TYPE_STD, $DEF_STATUS_ACTIVE;

    $sql  = "SELECT * FROM dtFundIn ";
    $sql .= " INNER JOIN dtPaymentAcc ON payMbrUsername = finMbrUsername";
    $sql .= " INNER JOIN msPaymentType ON ptID = finAccType";
    $sql .= " WHERE finMbrUsername='". $username ."' AND finStatus='". $DEF_STATUS_PENDING . "' ";
    $sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "' AND ptStID='".$DEF_STATUS_ACTIVE."' ";
    $sql .= " ORDER by finDate DESC Limit 1";
    $query = $conn->query($sql);

    $returnArrData = array();
    $flag = "false";
    if ($query->num_rows > 0) {
        if ($row = $query->fetch_assoc()) {
            $accType    = $row['finAccType'];
            $ptDesc     = $row['ptDesc'];
            $amount     = $row['finAmount'];
            $payacc     = $row['payAcc'];
            $finID      = $row['finID'];

            $returnArrData = array(
                'finID'     => $finID,
                'accType'   => $accType,
                'ptDesc'    => $ptDesc,
                'amount'    => numFormat($amount, 0),
                'payacc'    => $payacc,
                'flag'      => "true"
            );

            return(resultJSON("success", "", $returnArrData));
        }
    }else{

        $returnArrData = array(
            'accType'   => '',
            'ptDesc'    => '',
            'amount'    => '',
            'payacc'    => '',
            'flag'      => 'false'
        );

        return(resultJSON("success", "No Record", $returnArrData));
    }
}

function postConfirmBuyVoucher($conn, $username, $finID, $fromAccNo, $imageFileType, $IDTrans = ""){
    global $DEF_STATUS_ONPROGRESS;
    if ($finID != "" && $fromAccNo != "") {
        
        $target_dir     = '../../member/bukti_transfer/';
        $filename       = 'fin'.'_'.$finID.'.'.$imageFileType;
        $target_file    = $target_dir . $filename;

        $isValid = true;
        if ($finID == "" && $fromAccNo == ""){
            $isValid = false;
            return (resultJSON("error", "Incomplete Data"));
        }

        if (EMPTY($_FILES["finFilename"]["tmp_name"])){
            $isValid = false;
            return (resultJSON("error", "There is no file to upload."));
        }
        $check = getimagesize($_FILES["finFilename"]["tmp_name"]);
        if ($check === false){
            $isValid = false;
            return (resultJSON("error", "File is not an image."));
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $isValid = false;
            return (resultJSON("error", "only JPG, JPEG, PNG & GIF files are allowed", ""));
        }
        if ($isValid){
            $conn->autocommit(false);
            $table = "dtFundIn";
            $arrData = array(
                "finFromAccNo"      => $fromAccNo,
                "finTransactionID"  => $IDTrans,
                "finStatus"         => $DEF_STATUS_ONPROGRESS,
                "finFilename"		=> $filename
            );
            $arrDataQuery = array(
                "finID"             => $finID,
                "finMbrUsername"    => $username
            );
            if (fUpdateRecord($table, $arrData, $arrDataQuery, $conn)){ //success update
                if (move_uploaded_file($_FILES["finFilename"]["tmp_name"], $target_file)){
                    $conn->commit();
                    return (resultJSON("success", "Upload reciept transfer successfull", ""));
                }else{
                    $conn->rollback();
                    return (resultJSON("error", "Upload reciept transfer failed", ""));
                }
            }else{
                //update failed
                return (resultJSON("error", "Confirmation to Buy PIN Failed - Contact Support")); 
            }
        }
    }else{

        return (resultJSON("error", "Incomplete Data", ""));
    }
}


function getApprovePendingBuyVoucher($conn, $username){
    global $DEF_STATUS_APPROVED, $DEF_STATUS_PENDING, $DEF_STATUS_ONPROGRESS, $DEF_VOUCHER_TYPE_STD, $DEF_CATEGORY_INTERNAL_TRANSFER, $DEF_MUTASI_DATE;

    $sql = "SELECT finDate, finAccType, finFromAccNo, finAmount, finTransactionID, stDesc, ptDesc FROM dtFundIn ";
    $sql .= " INNER JOIN msStatus on finStatus=stID ";
    $sql .= " INNER JOIN msPaymentType ON ptID = finAccType";
    $sql .= " WHERE (finStatus ='" . $DEF_STATUS_APPROVED . "' ";
    $sql .= " OR finStatus='" . $DEF_STATUS_PENDING . "' OR finStatus='" . $DEF_STATUS_ONPROGRESS . "' )";
    $sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
    $sql .= " AND finMbrUsername='" . $username . "'";
    $sql .= " AND ptCat != '".$DEF_CATEGORY_INTERNAL_TRANSFER."'";
    $sql .= " AND date(finDate) >= '".$DEF_MUTASI_DATE."'";
    $sql .= " ORDER By finDate DESC";

    $query = $conn->query($sql);

    $returnArrData = array();
    if ($query->num_rows > 0) {
        
        while ($row = $query->fetch_assoc()) {
            
            $finDate            = $row['finDate'];
            $ptDesc             = $row['ptDesc'];
            $finFromAccNo       = $row['finFromAccNo'];
            $finAmount          = $row['finAmount'];
            $finTransactionID   = $row['finTransactionID'];
            $stDesc             = $row['stDesc'];

            $returnArrData[] = array(
                'finDate'           => $finDate,
                'ptDesc'            => $ptDesc,
                'finFromAccNo'      => $finFromAccNo,
                'finAmount'         => numFormat($finAmount,0),
                'finTransactionID'  => $finTransactionID,
                'stDesc'            => $stDesc  
            );

        }

        return (resultJSON("success", "", $returnArrData));
    }else{

        $returnArrData[] = array(
            'finDate'           => '',
            'finAccType'        => '',
            'finFromAccNo'      => '',
            'finAmount'         => '',
            'finTransactionID'  => '',
            'stDesc'            => ''  
        );

        return (resultJSON("success", "No Records", $returnArrData));
    }   
}


function searchApprovedPendingBuyVoucher($conn, $username, $search){

    global $DEF_STATUS_APPROVED, $DEF_STATUS_PENDING, $DEF_STATUS_ONPROGRESS, $DEF_VOUCHER_TYPE_STD, $DEF_CATEGORY_INTERNAL_TRANSFER, $DEF_MUTASI_DATE;

    $sql = "SELECT finDate, finAccType, finFromAccNo, finAmount, finTransactionID, stDesc, ptDesc FROM dtFundIn ";
    $sql .= " INNER JOIN msStatus on finStatus=stID ";
    $sql .= " INNER JOIN msPaymentType ON ptID = finAccType";
    $sql .= " WHERE (finStatus ='" . $DEF_STATUS_APPROVED . "' ";
    $sql .= " OR finStatus='" . $DEF_STATUS_PENDING . "' OR finStatus='" . $DEF_STATUS_ONPROGRESS . "' )";
    $sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
    $sql .= " AND finMbrUsername='" . $username . "'";
    $sql .= " AND ptCat != '".$DEF_CATEGORY_INTERNAL_TRANSFER."'";
    $sql .= " AND (finAmount LIKE '%".$search."%' ";
    $sql .= " OR finTransactionID LIKE '%".$search."%')";
    $sql .= " AND date(finDate) >= '".$DEF_MUTASI_DATE."'";
    $sql .= " ORDER By finDate DESC";

    $query = $conn->query($sql);
    $returnArrData = array();
    if ($query->num_rows > 0) {
        
        while ($row = $query->fetch_assoc()) {
            
            $finDate            = $row['finDate'];
            $ptDesc             = $row['ptDesc'];
            $finFromAccNo       = $row['finFromAccNo'];
            $finAmount          = $row['finAmount'];
            $finTransactionID   = $row['finTransactionID'];
            $stDesc             = $row['stDesc'];

            $returnArrData[] = array(
                'finDate'           => $finDate,
                'ptDesc'            => $ptDesc,
                'finFromAccNo'      => $finFromAccNo,
                'finAmount'         => numFormat($finAmount,0),
                'finTransactionID'  => $finTransactionID,
                'stDesc'            => $stDesc  
            );

        }

        return (resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData[] = array(
            'finDate'           => '',
            'finAccType'        => '',
            'finFromAccNo'      => '',
            'finAmount'         => '',
            'finTransactionID'  => '',
            'stDesc'            => ''  
        );

        return (resultJSON("success", "No Records", $returnArrData));
        
    }

}

function getDeclinedBuyVoucher($conn, $username){
    global $DEF_STATUS_DECLINED, $DEF_VOUCHER_TYPE_STD, $DEF_CATEGORY_INTERNAL_TRANSFER, $DEF_MUTASI_DATE;

	$sql  = "SELECT finDate, finAccType, finFromAccNo, finAmount, finTransactionID, stDesc, ptDesc ";
	$sql .= " FROM dtFundIn ";
	$sql .= " INNER JOIN msStatus on finStatus=stID ";
	$sql .= " INNER JOIN msPaymentType ON ptID = finAccType";
	$sql .= " WHERE finStatus ='" . $DEF_STATUS_DECLINED . "'";
	$sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
	$sql .= " AND finMbrUsername='" . $username . "'";
	$sql .= " AND ptCat != '".$DEF_CATEGORY_INTERNAL_TRANSFER."'";
	$sql .= " AND date(finDate) >= '".$DEF_MUTASI_DATE."'";
	$sql .= " ORDER By finDate DESC";


    $query = $conn->query($sql);
    $returnArrData = array();
    if ($query->num_rows > 0) {
        
        while ($row = $query->fetch_assoc()) {
            
            $finDate            = $row['finDate'];
            $ptDesc             = $row['ptDesc'];
            $finFromAccNo       = $row['finFromAccNo'];
            $finAmount          = $row['finAmount'];
            $finTransactionID   = $row['finTransactionID'];
            $stDesc             = $row['stDesc'];

            $returnArrData[] = array(
                'finDate'           => $finDate,
                'ptDesc'            => $ptDesc,
                'finFromAccNo'      => $finFromAccNo,
                'finAmount'         => numFormat($finAmount,0),
                'finTransactionID'  => $finTransactionID,
                'stDesc'            => $stDesc  
            );

        }

        return (resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData[] = array(
            'finDate'           => '',
            'finAccType'        => '',
            'finFromAccNo'      => '',
            'finAmount'         => '',
            'finTransactionID'  => '',
            'stDesc'            => ''  
        );

        return (resultJSON("success", "No Records", $returnArrData));
        
    }
}


function searchDeclinedBuyVoucher($conn, $username, $search){
    global $DEF_STATUS_DECLINED, $DEF_VOUCHER_TYPE_STD, $DEF_CATEGORY_INTERNAL_TRANSFER, $DEF_MUTASI_DATE;

    $sql  = "SELECT finDate, finAccType, finFromAccNo, finAmount, finTransactionID, stDesc, ptDesc ";
    $sql .= " FROM dtFundIn ";
    $sql .= " INNER JOIN msStatus on finStatus=stID ";
    $sql .= " INNER JOIN msPaymentType ON ptID = finAccType";
    $sql .= " WHERE finStatus ='" . $DEF_STATUS_DECLINED . "'";
    $sql .= " AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
    $sql .= " AND finMbrUsername='" . $username . "'";
    $sql .= " AND ptCat != '".$DEF_CATEGORY_INTERNAL_TRANSFER."'";
    $sql .= " AND (finAmount LIKE '%".$search."%' ";
    $sql .= " OR finTransactionID LIKE '%".$search."%')";
    $sql .= " AND date(finDate) >= '".$DEF_MUTASI_DATE."'";
    $sql .= " ORDER By finDate DESC";

    $query = $conn->query($sql);
    $returnArrData = array();
    if ($query->num_rows > 0) {
        
        while ($row = $query->fetch_assoc()) {
            
            $finDate            = $row['finDate'];
            $ptDesc             = $row['ptDesc'];
            $finFromAccNo       = $row['finFromAccNo'];
            $finAmount          = $row['finAmount'];
            $finTransactionID   = $row['finTransactionID'];
            $stDesc             = $row['stDesc'];

            $returnArrData[] = array(
                'finDate'           => $finDate,
                'ptDesc'            => $ptDesc,
                'finFromAccNo'      => $finFromAccNo,
                'finAmount'         => numFormat($finAmount,0),
                'finTransactionID'  => $finTransactionID,
                'stDesc'            => $stDesc  
            );

        }

        return (resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData[] = array(
            'finDate'           => '',
            'finAccType'        => '',
            'finFromAccNo'      => '',
            'finAmount'         => '',
            'finTransactionID'  => '',
            'stDesc'            => ''  
        );

        return (resultJSON("success", "No Records", $returnArrData));
        
    }
}

function getTransferVoucherHistory($conn, $username){
    global $DEF_TRANSFER_VOUCHER, $DEF_VOUCHER_TYPE_STD, $DEF_VOUCHER_PRICE_IDR, $DEF_VOUCHER_USED_FOR_ACTIVATION, $DEF_STATUS_APPROVED, $DEF_MUTASI_DATE;

    $sql  = " SELECT a.*, stDesc FROM ( ";
    $sql .= "   SELECT sender, receiver, finAmount, finDate, finStatus, finDesc FROM ";
    $sql .= "   ( ";
    $sql .= "       SELECT finFromAccNo as sender, finToAccNo as receiver, finAmount, finDate, finStatus, finDesc";
    $sql .= "       FROM dtFundIn  ";
    $sql .= "       WHERE finToAccNo='". $username ."' AND finAccType='" . $DEF_TRANSFER_VOUCHER . "'  ";
    $sql .= "       AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
    $sql .= "   ) r ";
    $sql .= "   UNION ";
    $sql .= "   ( ";
    $sql .= "       SELECT finFromAccNo as sender, finToAccNo as receiver, finAmount, finDate, finStatus, finDesc";
    $sql .= "       FROM dtFundIn  ";
    $sql .= "       WHERE finFromAccNo='". $username ."' AND finAccType='" . $DEF_TRANSFER_VOUCHER . "'  ";
    $sql .= "       AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
    $sql .= "   )  ";
    $sql .= "   UNION (";
    $sql .= "       SELECT finToAccNo AS sender, fivUserOn AS receiver, ";
    $sql .= "       (COUNT(fivFinID) * " . $DEF_VOUCHER_PRICE_IDR . ") AS finAmount, fivDate finDate, ";
    $sql .= "       finStatus, fivUsedFor as finDesc";
    $sql .= "       FROM dtFundInVoucher ";
    $sql .= "       INNER JOIN ( ";
    $sql .= "           SELECT finToAccNo, finID, finStatus ";
    $sql .= "           FROM dtFundIn";
    $sql .= "           WHERE finToAccNo = '". $username . "' AND finStatus ='".$DEF_STATUS_APPROVED."' ";
    $sql .= "       ) AS b ON b.finID = fivFinID";
    $sql .= "       WHERE fivUsedfor = '" . $DEF_VOUCHER_USED_FOR_ACTIVATION . "'";
    $sql .= "       GROUP BY fivFinID, fivUserOn";
    $sql .= "   )";
    $sql .= " ) a ";
    $sql .= " INNER JOIN msStatus on stID=finStatus ";
    $sql .= " WHERE a.finDate >= '".$DEF_MUTASI_DATE."' ";
    $sql .= " ORDER BY finDate DESC";

    //echo $sql;
    $returnArrData = array();
    $query = $conn->query($sql);

    if ($query->num_rows > 0) {

        while ($row = $query->fetch_assoc()) {
            

            $finDate = $row['finDate'];
            $receiver = strtolower($row["receiver"]);
            $sender = strtolower($row["sender"]);

            if ($row['finDesc'] == "") {
                $history = "Transfer";
            }else if($receiver == $sender){
                $history = "Renew";
            }else{
                $history = $row['finDesc'];
            }

        
            if ($receiver == $username && $sender != $receiver) {
                $finAmount =  $row['finAmount'];
            }else{
                $finAmount = "-".$row['finAmount'];
            }

            $stDesc = $row['stDesc'];
            $returnArrData[] = array(

                'finDate'   => $finDate,
                'sender'    => $sender,
                'receiver'  => $receiver,
                'finDesc'   => $history,
                'finAmount' => numFormat($finAmount, 0),
                'stDesc'    => $stDesc

            );
        } //end while

        return(resultJSON("success", "", $returnArrData));

    }else{
        $returnArrData[] = array(

            'finDate'   => '',
            'sender'    => '',
            'receiver'  => '',
            'finDesc'   => '',
            'finAmount' => '',
            'stDesc'    => ''

        );
        return(resultJSON("success", "", $returnArrData));
    }
}


function searchTransferVoucher($conn, $username, $search){

    global $DEF_TRANSFER_VOUCHER, $DEF_VOUCHER_TYPE_STD, $DEF_VOUCHER_PRICE_IDR, $DEF_VOUCHER_USED_FOR_ACTIVATION, $DEF_STATUS_APPROVED, $DEF_MUTASI_DATE;

    $sql  = " SELECT a.*, stDesc FROM ( ";
    $sql .= "   SELECT sender, receiver, finAmount, finDate, finStatus, finDesc FROM ";
    $sql .= "   ( ";
    $sql .= "       SELECT finFromAccNo as sender, finToAccNo as receiver, finAmount, finDate, finStatus, finDesc";
    $sql .= "       FROM dtFundIn  ";
    $sql .= "       WHERE finToAccNo='". $username ."' AND finAccType='" . $DEF_TRANSFER_VOUCHER . "'  ";
    $sql .= "       AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
    $sql .= "   ) r ";
    $sql .= "   UNION ";
    $sql .= "   ( ";
    $sql .= "       SELECT finFromAccNo as sender, finToAccNo as receiver, finAmount, finDate, finStatus, finDesc";
    $sql .= "       FROM dtFundIn  ";
    $sql .= "       WHERE finFromAccNo='". $username ."' AND finAccType='" . $DEF_TRANSFER_VOUCHER . "'  ";
    $sql .= "       AND finVoucherType='" . $DEF_VOUCHER_TYPE_STD . "'";
    $sql .= "   )  ";
    $sql .= "   UNION (";
    $sql .= "       SELECT finToAccNo AS sender, fivUserOn AS receiver, ";
    $sql .= "       (COUNT(fivFinID) * " . $DEF_VOUCHER_PRICE_IDR . ") AS finAmount, fivDate finDate, ";
    $sql .= "       finStatus, fivUsedFor as finDesc";
    $sql .= "       FROM dtFundInVoucher ";
    $sql .= "       INNER JOIN ( ";
    $sql .= "           SELECT finToAccNo, finID, finStatus ";
    $sql .= "           FROM dtFundIn";
    $sql .= "           WHERE finToAccNo = '". $username . "' AND finStatus ='".$DEF_STATUS_APPROVED."' ";
    $sql .= "       ) AS b ON b.finID = fivFinID";
    $sql .= "       WHERE fivUsedfor = '" . $DEF_VOUCHER_USED_FOR_ACTIVATION . "'";
    $sql .= "       GROUP BY fivFinID, fivUserOn";
    $sql .= "   )";
    $sql .= " ) a ";
    $sql .= " INNER JOIN msStatus on stID=finStatus ";
    $sql .= " WHERE a.finDate >= '".$DEF_MUTASI_DATE."' ";
    $sql .= " AND ( sender LIKE '%". $search ."%' OR receiver LIKE '%". $search ."%' OR finAmount LIKE '%" .$search. "%' ) ";
    $sql .= " ORDER BY finDate DESC";

    $returnArrData = array();
    $query = $conn->query($sql);

    if ($query->num_rows > 0) {

        while ($row = $query->fetch_assoc()) {
            

            $finDate = $row['finDate'];
            $receiver = strtolower($row["receiver"]);
            $sender = strtolower($row["sender"]);

            if ($row['finDesc'] == "") {
                $history = "Transfer";
            }else if($receiver == $sender){
                $history = "Renew";
            }else{
                $history = $row['finDesc'];
            }

        
            if ($receiver == $username && $sender != $receiver) {
                $finAmount =  $row['finAmount'];
            }else{
                $finAmount = "-".$row['finAmount'];
            }

            $stDesc = $row['stDesc'];
            $returnArrData[] = array(

                'finDate'   => $finDate,
                'sender'    => $sender,
                'receiver'  => $receiver,
                'finDesc'   => $history,
                'finAmount' => numFormat($finAmount, 0),
                'stDesc'    => $stDesc

            );
        } //end while

        return(resultJSON("success", "", $returnArrData));

    }else{
        $returnArrData[] = array(

            'finDate'   => '',
            'sender'    => '',
            'receiver'  => '',
            'finDesc'   => '',
            'finAmount' => '',
            'stDesc'    => ''

        );
        return(resultJSON("success", "", $returnArrData));
    }
}

function postConvertBonus($conn, $username, $amountVoucher, $secPasswd){
    global $DEF_VOUCHER_PRICE_IDR, $DEF_STATUS_ACTIVE, $DEF_VOUCHER_TYPE_STD, $DEF_CONVERT_BNS_VOUCHER, $DEF_STATUS_APPROVED, $DEF_STATUS_USED;   

    $voucherPrice = $DEF_VOUCHER_PRICE_IDR;

    $ttlCommission = 0;
    $myDataObj  = json_decode(fGetBalance($username, $conn));
    if ($myDataObj->{"status"} == "success"){
      $ttlCommission     = $myDataObj->{'ttlCommission'};
    }

    if (!empty($_POST)) { 
        $transferTo  =  $username;
        // baliki ke nilai BV
        $amountVoucher      = $amountVoucher;
        $numberOfVoucher    = $amountVoucher / $voucherPrice;
        if (fmod($amountVoucher, $voucherPrice) == 0){
            //Validation inputs
            if ($username != "" && $transferTo != "" && $amountVoucher != "" && $amountVoucher > 0 && $numberOfVoucher > 0 && $secPasswd != ""){
                if ($username == $transferTo){ //must the same username, because convert on they own account
                    //cek Commission
                    if ($ttlCommission >= $amountVoucher && $ttlCommission >= $voucherPrice){
                        //Check Security Password
                        //$s = fCheckSecurityPassword($username, $secPasswd, $conn);
                        //if (true){
                        //echo (fCheckSecurityPassword($username, $secPasswd, $conn)); die();
                        if (!fCheckSecurityPassword($username, $secPasswd, $conn)){
                            //$responseMessage .= "Security Password not match<br>";
                            return (resultJSON("error", "Security Password not match", ""));
                        }else{

                            $sql = "SELECT COUNT(*) AS ttlVoucher FROM dtVoucher WHERE vStatus = '". $DEF_STATUS_ACTIVE ."' AND vType='". $DEF_VOUCHER_TYPE_STD ."'";
                            $query = $conn->query($sql);
                            $row  = $query->fetch_assoc();
                            if ($row["ttlVoucher"] < $numberOfVoucher){
                                //number of voucher not enough for deposit
                                return(resultJSON("error", "Out of voucher. Try again in a few minutes or contact support for fast response", ""));
                            }else {

                                $conn->autocommit(false);
                                $isFailed = false;

                                //Insert into dtFundIn
                                $timeStamp  = strtotime("now");
                                $finID    = $transferTo.$timeStamp;  //same format used in Buy Voucher (reqBuyVoucher.php)
                                $amount   = $amountVoucher; //* $DEF_VOUCHER_PRICE;
                                $curr     = "IDR";
                                $curs     = "1";
                                $accName  = "";
                                $accType  = $DEF_CONVERT_BNS_VOUCHER;
                                $voucherType = $DEF_VOUCHER_TYPE_STD;
                                $fromAccNo = $username; //$_SESSION["sUserName"];
                                $toAccNo  = $transferTo; 
                                $status   = $DEF_STATUS_APPROVED; //APPROVED BY SYSTEM
                                $approvedBy = "SYSTEM";
                                $IDTrans  = "SYS-".$timeStamp;

                                $arrData = array(
                                    0 => array ("db" => "finID"       , "val" => $finID),
                                    1 => array ("db" => "finMbrUsername"  , "val" => $transferTo),
                                    2 => array ("db" => "finAmount"     , "val" => $amount),
                                    3 => array ("db" => "finCurr"     , "val" => $curr),
                                    4 => array ("db" => "finCurs"     , "val" => $curs),
                                    5 => array ("db" => "finAccName"    , "val" => $accName),
                                    6 => array ("db" => "finAccType"    , "val" => $accType),
                                    7 => array ("db" => "finFromAccNo"    , "val" => $fromAccNo),
                                    8 => array ("db" => "finToAccNo"    , "val" => $toAccNo),
                                    9 => array ("db" => "finTransactionID"  , "val" => $IDTrans),
                                    10 => array ("db" => "finDate"     , "val" => "CURRENT_TIME()"),
                                    11 => array ("db" => "finStatus"     , "val" => $status),
                                    12 => array ("db" => "finApprovedBy"   , "val" => $approvedBy),
                                    13 => array ("db" => "finDesc"   , "val" => "CONVERT BONUS")
                                );

                                $table  = "dtFundIn"; 

                                if (fInsert($table, $arrData, $conn)){
                                    //insert success
                                    $sql = "SELECT vID, vCode FROM dtVoucher WHERE vStatus='" . $DEF_STATUS_ACTIVE . "' AND vType='". $DEF_VOUCHER_TYPE_STD ."'";
                                    $query = $conn->query($sql);
                                    $counter = 0;
                                    while ($row = $query->fetch_assoc()){ 
                                        if ($counter < $numberOfVoucher){
                                            $counter++;
                                            $arrData = array("vStatus" => $DEF_STATUS_USED);
                                            $arrDataQuery = array("vCode" => $row["vCode"]);
                                            if (!fUpdateRecord("dtVoucher", $arrData, $arrDataQuery, $conn)){
                                                $conn->rollback();
                                                $isFailed = true;
                                                $responseMessage .= $conn->error;
                                                break;
                                            }
                                            unset($arrData);
                                            unset($arrDataQuery);

                                            //Insert dtFundInVoucher
                                            $arrData = array(
                                                array("db" => "fivFinID", "val"   => $finID),
                                                array("db" => "fivVCode", "val"   =>  $row["vCode"]),
                                                array("db" => "fivDate", "val"  =>  "CURRENT_TIME()"),
                                                array("db" => "fivStatus", "val"  => $DEF_STATUS_ACTIVE),
                                                array("db" => "fivType", "val"  => $DEF_VOUCHER_TYPE_STD),
                                                    array("db" => "fivUsedFor", "val"   => ""), //filled when transfer or activate member [TRANSFER/ACTIVATION]
                                                    array("db" => "fivUserOn", "val"  => "") //filled when transfer or activate member [USERNAME]
                                                );
                                            if (!fInsert("dtFundInVoucher", $arrData, $conn)){
                                                $conn->rollback();
                                                $isFailed = true;
                                                $responseMessage .= $conn->error;
                                                break;
                                            }
                                            unset($arrData);
                                        }//end if ($counter < $numOfVoucher){
                                        else{
                                            break;  
                                        }
                                    }//end while
                                }else{
                                    //failed to insert into dtFundIn
                                    $conn->rollback();
                                    $isFailed = true;
                                }

                                if ($isFailed==false){
                                    $conn->commit();
                                    // /$conn->close();
                                    //redirect to success page
                                    return (resultJSON("success", "Convert bonus successfull", ""));
                                    die();
                                }
                            }
                        }
                    }else{
                    //$responseMessage .= "Your commission balance is not enough<br>";
                     return (resultJSON("error", "Your commission balance is not enough", ""));
                    }
                }else{
                //$responseMessage .= "Can not transfer to Other Username<br>";
                    return (resultJSON("error", "Can not transfer to Other Username", ""));
                }
            }else{
                //Data not complite
                return(resultJSON("error", "Can not transfer to Other Username", "")); 
            }
        }
    }
}

function getConvertBonusHistory($conn, $username){
    global $DEF_CONVERT_BNS_VOUCHER, $DEF_MUTASI_DATE;

    $sql = "SELECT a.*, stDesc FROM ( ";             
    $sql .= "   SELECT finFromAccNo as sender, finMbrUsername as receiver, finAmount, finDate, finStatus FROM dtFundIn  ";
    $sql .= "   WHERE finMbrUsername='". $username ."' AND finAccType='" . $DEF_CONVERT_BNS_VOUCHER . "'  ";
    $sql .= ") a ";
    $sql .= " INNER JOIN msStatus on stID=finStatus ";
    $sql .= " WHERE date(finDate) >= '".$DEF_MUTASI_DATE."'";
    $sql .= " ORDER BY DATE(finDate) DESC";

    $returnArrData = array();
    $query = $conn->query($sql);

    if ($query->num_rows > 0 ) {

        while ($row = $query->fetch_assoc()) {

            $finDate    = $row['finDate'];
            $desc       = "Commission " . $row['sender'] . " - " . $row['receiver'];
            $stDesc     = $row['stDesc'];
            //echo $username;

            if ($row['receiver'] == $username){
                $finAmount = $row['finAmount'];
            }else{
                $finAmount = "-".$row['finAmount'];
            }

            $returnArrData[] = array(
                "finDate"   => $finDate,
                "desc"      => $desc,
                "finAmount" => numFormat($finAmount, 0),
                "stDesc"    => $stDesc
            );

        }

        return(resultJSON("success", "", $returnArrData));

    }else{

        $returnArrData[] = array(
            "finDate"   => '',
            "desc"      => '',
            "finAmount" => '',
            "stDesc"    => ''
        );
        
        return(resultJSON("success", "", $returnArrData));
    }
}

function searchConvertBonus($conn, $username, $search){
    global $DEF_CONVERT_BNS_VOUCHER, $DEF_MUTASI_DATE;

    $sql = "SELECT a.*, stDesc FROM ( ";             
    $sql .= "   SELECT finFromAccNo as sender, finMbrUsername as receiver, finAmount, finDate, finStatus FROM dtFundIn  ";
    $sql .= " WHERE finMbrUsername='". $username ."' AND finAccType='" . $DEF_CONVERT_BNS_VOUCHER . "'  AND finAmount LIKE '%" .$search. "%' ";
    $sql .= ") a ";
    $sql .= " INNER JOIN msStatus on stID=finStatus ";
    $sql .= " WHERE date(finDate) >= '".$DEF_MUTASI_DATE."'";
    $sql .= " ORDER BY DATE(finDate) DESC";

    $returnArrData = array();
    $query = $conn->query($sql);

    if ($query->num_rows > 0 ) {

        while ($row = $query->fetch_assoc()) {

            $finDate    = $row['finDate'];
            $desc       = "Commission " . $row['sender'] . " - " . $row['receiver'];
            $stDesc     = $row['stDesc'];

            if ($row['receiver'] == $username) {
                $finAmount = $row['finAmount'];
            }else{
                $finAmount = "-".$row['finAmount'];
            }

            $returnArrData[] = array(
                "finDate"   => $finDate,
                "desc"      => $desc,
                "finAmount" => numFormat($finAmount, 0),
                "stDesc"    => $stDesc
            );

        }

        return(resultJSON("success", "", $returnArrData));

    }else{

        $returnArrData[] = array(
            "finDate"   => '',
            "desc"      => '',
            "finAmount" => '',
            "stDesc"    => ''
        );
        
        return(resultJSON("success", "", $returnArrData));
    }
}

function getHistoryComm($conn, $username){
    global $DEF_MUTASI_DATE;

    $sql = "SELECT bnsSpUsername, bnsSpTrUsername, typeOfBonus, bnsSpAmount, bnsSpDate ";
    $sql .= " FROM( ";
    $sql .= "     SELECT bnsSpUsername, bnsSpTrUsername, bnsSpTrPacID, 'Sponsorship' as typeOfBonus, bnsSpAmount, bnsSpDate ";
    $sql .= "   FROM dtBnsSponsor INNER JOIN msPackage on pacID = bnsSpTrPacID ";
    // $sql .= " UNION ";
    // $sql .= "   SELECT bnsPUUsername, bnsPUTrUsername, bnsPUTrPacID, CONCAT('Passed-up: ', pacName) as typeOfBonus, bnsPUAmount, bnsPUDate  ";
    // $sql .= "   FROM dtBnsPassedUp INNER JOIN msPackage on pacID = bnsPUTrPacID ";
    $sql .= " UNION ";
    $sql .= "     SELECT pairUsername, '', '', 'pairing 10%', pairTO, pairDate  ";
    $sql .= "     FROM dtDailyPairing ";
    $sql .= "     WHERE pairTO > 0 ";
    // $sql .= " UNION ";
    // $sql .= "     SELECT mtchUsername, '', '', 'Matching 10%', mtchAmount, mtchDate ";
    // $sql .= "     FROM dtMatching ";
    // $sql .= "     WHERE mtchAmount > 0 ";
    $sql .= " ) bns ";
    $sql .= " WHERE bnsSpUsername = '" . $username ."' ";
    $sql .= " AND date(bnsSpDate) >= '".$DEF_MUTASI_DATE."' ";
    $sql .= " ORDER BY bnsSpDate DESC ";

    $query = $conn->query($sql);
    $returnArrData = array();
    if ($query->num_rows > 0) {
        
        while ($row = $query->fetch_assoc()) {
            
            $bnsSpDate = $row['bnsSpDate'];
            $typeComm  = $row['typeOfBonus'].(($row['bnsSpTrUsername'] != "")? " - " . $row['bnsSpTrUsername'] : "");
            $bnsSpAmount = numFormat($row["bnsSpAmount"], 0);

            $returnArrData[] = array(

                'bnsSpDate'     => $bnsSpDate,
                'typeComm'      => $typeComm,
                'bnsSpAmount'   => $bnsSpAmount

            );

        }

        return (resultJSON("success", "", $returnArrData));
    }else{

        $returnArrData[] = array(
            'bnsSpDate'     => '',
            'typeComm'      => '',
            'bnsSpAmount'   => ''
        );
        return (resultJSON("success", "No Records", $returnArrData));
    }
}


function searchHistoryComm($conn, $username, $search){

    global $DEF_MUTASI_DATE;

    $sql = "SELECT bnsSpUsername, bnsSpTrUsername, typeOfBonus, bnsSpAmount, bnsSpDate ";
    $sql .= " FROM( ";
    $sql .= "     SELECT bnsSpUsername, bnsSpTrUsername, bnsSpTrPacID, 'Sponsorship' as typeOfBonus, bnsSpAmount, bnsSpDate ";
    $sql .= "   FROM dtBnsSponsor INNER JOIN msPackage on pacID = bnsSpTrPacID ";
    // $sql .= " UNION ";
    // $sql .= "   SELECT bnsPUUsername, bnsPUTrUsername, bnsPUTrPacID, CONCAT('Passed-up: ', pacName) as typeOfBonus, bnsPUAmount, bnsPUDate  ";
    // $sql .= "   FROM dtBnsPassedUp INNER JOIN msPackage on pacID = bnsPUTrPacID ";
    $sql .= " UNION ";
    $sql .= "     SELECT pairUsername, '', '', 'pairing 10%', pairTO, pairDate  ";
    $sql .= "     FROM dtDailyPairing ";
    $sql .= "     WHERE pairTO > 0 ";
    // $sql .= " UNION ";
    // $sql .= "     SELECT mtchUsername, '', '', 'Matching 10%', mtchAmount, mtchDate ";
    // $sql .= "     FROM dtMatching ";
    // $sql .= "     WHERE mtchAmount > 0 ";
    $sql .= " ) bns ";
    $sql .= " WHERE bnsSpUsername = '" . $username ."' ";
    $sql .= " AND (bnsSpTrUsername LIKE '%".$search."%' OR typeOfBonus LIKE '%".$search."%'";
    $sql .= " OR bnsSpUsername LIKE '%".$search."%' OR bnsSpAmount LIKE '%".$search."%')";
    $sql .= " AND date(bnsSpDate) >= '".$DEF_MUTASI_DATE."' ";
    $sql .= " ORDER BY bnsSpDate DESC ";

    $query = $conn->query($sql);
    $returnArrData = array();
    if ($query->num_rows > 0) {
        
        while ($row = $query->fetch_assoc()) {
            
            $bnsSpDate = $row['bnsSpDate'];
            $typeComm  = $row['typeOfBonus'].(($row['bnsSpTrUsername'] != "")? " - " . $row['bnsSpTrUsername'] : "");
            $bnsSpAmount = numFormat($row["bnsSpAmount"], 0);

            $returnArrData[] = array(

                'bnsSpDate'     => $bnsSpDate,
                'typeComm'      => $typeComm,
                'bnsSpAmount'   => $bnsSpAmount

            );

        }

        return (resultJSON("success", "", $returnArrData));
    }else{

        $returnArrData[] = array(
            'bnsSpDate'     => '',
            'typeComm'      => '',
            'bnsSpAmount'   => ''
        );
        return (resultJSON("success", "No Records", $returnArrData));
    }
}

function getDataDashboard($conn, $username){
    global $DEF_VOUCHER_PRICE_IDR, $DEF_VOUCHER_TYPE_STD, $DEF_VOUCHER_TYPE_VPS;
    //sponsor
    $total = $totalSP = 0;
    $myDataObj  = json_decode(fCommissionSponsorship($username, $conn));
    if ($myDataObj->{"status"} == "success"){
        $total      = $myDataObj->{'total'};
        $totalSP    = $myDataObj->{'totalSP'};   
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

    //Bonus RO
    $myDataObj  = json_decode(fSumCommissionRO($username, $conn));
    $tBnsRO = $trPDQty = 0;
    if ($myDataObj->{"status"} == "success"){
        $tBnsRO = $myDataObj->{'tBnsRO'};
        $trPDQty = $myDataObj->{'trPDQty'};
    }

    //Voucher STD
    $voucherAct = $voucherIN = $voucherOUT = $voucherBalance = 0;
    $myDataObj  = json_decode(fGetNumberOfVoucher($DEF_VOUCHER_TYPE_STD, $username, $conn));
    if ($myDataObj->{"status"} == "success"){
        $voucherAct     = $myDataObj->{'voucherAct'};
        //$voucherOUT     = $myDataObj->{'voucherOUT'};
        $voucherIN      = $myDataObj->{'voucherIN'};
        $sumActivationVoucher   = $myDataObj->{'sumActivationVoucher'};
        $sumTransferVoucher     = $myDataObj->{'sumTransferVoucher'};
        $sumRepeatOrder         = $myDataObj->{'sumRepeatOrder'};
        $voucherBalance = $myDataObj->{'voucherBalance'};
    }

    //Balance
    $myDataObj  = json_decode(fGetBalance($username, $conn));
    if ($myDataObj->{"status"} == "success"){
        //$ttlBonus       = $myDataObj->{'ttlBonus'};
        $ttlCommission  = $myDataObj->{'ttlCommission'};
        //$wallet         = $myDataObj->{'wallet'};
        //$wUsage         = $myDataObj->{'wUsage'};
        //$ttlConvert     = $myDataObj->{'ttlConvert'};
        $balance        = $myDataObj->{'balance'};
    }

    //Withdrawal
    $ttlWD = 0;
    $myDataObj  = json_decode(fSumWithdrawal($username, $conn));
    if ($myDataObj->{"status"} == "success"){
        $ttlWD     = $myDataObj->{'ttlWD'};
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

    $returnArrData = array(
        "sponsorship" => array(
            "total"     => numFormat($total, 0),
            "totalSP"   => numFormat($totalSP, 0)
        ),
        "pairing"     => array(
            "sumLeft"   => numFormat($sumLeft, 0),
            "sumRight"  => numFormat($sumRight, 0),
            "sumFO"     => numFormat($sumFO, 0), 
            "sumTO"     => numFormat($sumTO, 0) 
        ),
        "ro"          => array(
            "tBnsRO"    => numFormat($tBnsRO, 0),
            "trPDQty"   => numFormat($trPDQty, 0)
        ),
        "voucherSTD"    => array(
            "voucherAct"            => numFormat($voucherAct * $DEF_VOUCHER_PRICE_IDR, 0),
            "voucherIN"             => numFormat($voucherIN * $DEF_VOUCHER_PRICE_IDR, 0),
            "sumActivationVoucher"  => numFormat($sumActivationVoucher * $DEF_VOUCHER_PRICE_IDR, 0),
            "sumTransferVoucher"    => numFormat($sumTransferVoucher * $DEF_VOUCHER_PRICE_IDR, 0),
            "sumRepeatOrder"        => numFormat($sumRepeatOrder * $DEF_VOUCHER_PRICE_IDR, 0),
            "voucherBalance"        => numFormat($voucherBalance, 0)
        ),
        // "voucherVPS"    => array(
        //     "voucherAct"            => numFormat($voucherActVPS * $DEF_VOUCHER_PRICE_VPS, 0),
        //     "voucherIN"             => numFormat($voucherINVPS * $DEF_VOUCHER_PRICE_VPS, 0),
        //     "sumActivationVoucher"  => numFormat($sumActivationVoucherVPS * $DEF_VOUCHER_PRICE_VPS, 0),
        //     "sumTransferVoucher"    => numFormat($sumTransferVoucherVPS * $DEF_VOUCHER_PRICE_VPS, 0),
        //     "voucherBalance"        => numFormat($voucherBalanceVPS, 0)
        // ),
        "balance"       => array(
            "balance"       => numFormat($balance, 0),
            "ttlCommission" => numFormat($ttlCommission, 0),
            "ttlWD"         => numFormat($ttlWD,0)
        )
    );

    return(resultJSON("success", "", $returnArrData));
}

function postRegisterNewMember($conn, $username, $package, $name, $IDType, $IDN, $BOD, $sponsorUsername, $password, $codeMobile, $mobile, $email, $country, $state, $city, $address){
    
    global $DEF_STATUS_PENDING, $DEF_STATUS_ACTIVE;

    $tjVerifyCode = uniqid(); //strtotime(now);
    if ($package != "" && $username != "" && $name != "" && $IDType != "" && $IDN != "" && $sponsorUsername != "" && $password != "" && $codeMobile != "" && $mobile != "" && $email != "" && $country != "" && $state != "" && $city != "" && $address != "" ){
        if (!fCekVerificationID($conn, $username, $IDN)) {
            return(resultJSON("error", "ID Number has been used", ""));
        }else{
            $arrData = array(
                0 => array ("db" => "tjUsername"    , "val" => $username),
                1 => array ("db" => "tjSponsor"     , "val" => $sponsorUsername),
                2 => array ("db" => "tjFirstName"   , "val" => $name),
                3 => array ("db" => "tjPasswd"      , "val" => $password),
                4 => array ("db" => "tjIDType"      , "val" => $IDType),
                5 => array ("db" => "tjIDN"         , "val" => $IDN),
                6 => array ("db" => "tjEmail"       , "val" => $email),
                7 => array ("db" => "tjMobileCode"  , "val" => $codeMobile),
                8 => array ("db" => "tjMobile"      , "val" => $mobile),
                9 => array ("db" => "tjBOD"         , "val" => $BOD),
                10 => array ("db" => "tjAddr"       , "val" => $address),
                11 => array ("db" => "tjCountry"    , "val" => $country),
                12 => array ("db" => "tjState"      , "val" => $state),
                13 => array ("db" => "tjCity"       , "val" => $city),
                14 => array ("db" => "tjPackage"    , "val" => $package),
                15 => array ("db" => "tjStID"       , "val" => $DEF_STATUS_PENDING),
                16 => array ("db" => "tjDate"       , "val" => "CURRENT_TIME()"),
                17 => array ("db" => "tjVerifyCode", "val" => $tjVerifyCode)
            );

            //check sponsor name
            //check existing username before insert
            $sql = "SELECT mbrUsername, mbrStID FROM dtMember WHERE mbrUsername='" . $sponsorUsername . "'";
            $query = $conn->query($sql);
            $row = $query->fetch_assoc();
            if ($query->num_rows == 0){
                return (resultJSON("error", "Sponsor name not found", ""));
                die();
            }else {
                if ($row['mbrStID'] != $DEF_STATUS_ACTIVE) {
                    return(resultJSON("error", "Register Failed Sponsor not active", ""));
                }else{
                    //check existing username before insert
                    $sql = "SELECT mbrUsername, mbrEmail FROM dtMember WHERE mbrUsername='" . $username . "' or mbrEmail ='" . $email ."'";
                    $sql .= " UNION select tjUsername, tjEmail from dtTempJoin WHERE tjUsername = '". $username . "' or tjEmail='" . $email . "'";
                    $sql .= " UNION SELECT trProUserBeli, '' FROM trProduct WHERE trProUserBeli = '".$username."'";
                    $query = $conn->query($sql);
                        //echo $sql;
                    if ($query->num_rows > 0){
                        while($row = $query->fetch_assoc()) {
                            if ($row["mbrUsername"] == $username && $row["mbrEmail"] == $email ){
                                return (resultJSON("error", "Username and email have been used", "")); 
                                die();
                            }else if ($row["mbrUsername"] == $username ) {
                                return(resultJSON("error", "Username has been used", "")); 
                                die();
                            }
                            else if ($row["mbrEmail"] == $email ){
                                return (resultJSON("error", "Email has been used", ""));
                                die();
                            }
                        }
                    }else {
                        if (fInsert("dtTempJoin", $arrData, $conn)){
                        //insert success
                         //send email for activation
                            if (!fSendNotifToEmail("REGISTER_SUCCESS", $username)){
                                    //fail sending email
                                fSendToAdminApps($username, 'REGISTER_NEW_MEMBER', 'register.php', 'send email failed');
                            }else{
                                    //success send email
                            }
                            return (resultJSON("success", "Register successfull", ""));
                        }else{
                            //send notif to admin
                            fSendToAdminApps($username, 'REGISTER_NEW_MEMBER', 'register.php', 'sql: insert failed');
                            return (resultJSON("error", "Register Failed", ""));
                        } // end else    
                    } //end else  
                }
            } // end else
        }
    }else{
        return (resultJSON("error", "Incomplete Data", ""));
    //fSendToAdmin('REGISTER_NEW_MEMBER', 'register.php', 'sql: ' . $registerMessage);
    }
}

function getMsIdType($conn){
    $sql  = "SELECT * FROM msIDType";
    $sql .= " ORDER BY idtCode ASC";

    $returnArrData = array();
    if ( $query = $conn->query( $sql ) ) {

        while( $row = $query->fetch_assoc() ) {
            $idtCode   = $row['idtCode'];
            $idtType   = $row['idtType'];

            $returnArrData[] = array(
                'idtCode' => $row['idtCode'],
                'idtType' => $row['idtType'],
            );
        }

        return(resultJSON("success", "", $returnArrData));
    }else{
        return(resultJSON("error", "No Record", $returnArrData));
    }
}

function getMsCountry($conn){
    global $DEF_STATUS_ACTIVE;
    $countryID = $countryDesc = $mblCountryCode =  $mblCountryDesc = '';
    $returnArrData = array();
    $sql = "SELECT * FROM msCountry WHERE countryStID='".$DEF_STATUS_ACTIVE."'";
    $query = $conn->query($sql);
    
    if ($query->num_rows > 0){
        $msCountry = array();
        while( $row = $query->fetch_assoc() ) {

            $countryID      = $row['countryID'];
            $countryDesc    = $row['countryDesc'];
            $mblCountryCode = $row["countryMobileCode"];
            $mblCountryDesc = $row["countryDesc"];

            $returnArrData[] = array(
                'countryID'      => $countryID,
                'countryDesc'    => $countryDesc,
                'mblCountryCode' => $mblCountryCode,
                'mblCountryDesc' => $mblCountryDesc." ("."+".$mblCountryCode.")"
            );
            
        }

        return (resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData[] = array(
            ''  => ''
        );
        return(resultJSON("error", "No Records", $returnArrData));
    }
}

function checkGenealogy($conn, $usernameLogin, $searchUsername){
    global $DEF_STATUS_ACTIVE;
    $username = $usernameLogin;
    
    if ($searchUsername != ""){
        $username = $searchUsername;
    } 

    if (fCheckGenealogyTree($searchUsername, $usernameLogin, $conn) == true){
        $sql = "SELECT mbrFirstName, mbrLastName, mbrStID from dtMember WHERE mbrUsername='$searchUsername'";
        $query = $conn->query($sql);
        if ($query->num_rows > 0){
            $row = $query->fetch_assoc();
            if ($row['mbrStID'] != $DEF_STATUS_ACTIVE){
                // echo "this sponsor not active";
                return(resultJSON("error", "This Sponsor Not active", ""));
            }else{
                return(resultJSON("success", "", ""));
                //echo ($row['mbrFirstName'] . " " . $row['mbrLastName']);
            }   
        }else{
            return(resultJSON("error", "Wrong sponsor username", ""));
          //echo ("wrong sponsor's username"); //NB: change will change comparison value in register.php for #sponsorName  
        } 
    }else{
        //echo "not under your genealogy tree"; //NB: change will change comparison value in register.php for #sponsorName
        return(resultJSON("error", "Not under your genealogy tree", ""));
    }
}

function getDirectSponsor($conn, $username, $usernameSP){

    if ($usernameSP == "") {
        $sponsor = $username;
    }else{
        $sponsor = $usernameSP;
    }

    //cek genealogy tree
    if (!fCheckGenealogyTree($sponsor, $username, $conn)) {
        $returnArrData[] = array(
                'errorVersion'  => '1',
                'mbrUsername'   => '',
                'mbrFirstName'  => ''
            );
        return(resultJSON("error", "Sponsor not found", $returnArrData));
    }else{

        $sql = "SELECT mbrUsername, mbrFirstName FROM dtMember WHERE mbrSponsor='".$sponsor."' AND mbrUsername <> '".$sponsor."'";
        $sql .= " ORDER BY mbrDate ASC";

        //echo $sql;

        $query = $conn->query($sql);
        $returnArrData = array();
        if ($query->num_rows > 0) {
            $directSponsor = array();
            while ($row = $query->fetch_assoc()) {

                $mbrUsername    = $row['mbrUsername'];
                $mbrFirstName   = $row['mbrFirstName'];

                $returnArrData[] = array(
                    'mbrUsername'   => $mbrUsername,
                    'mbrFirstName'  => $mbrFirstName
                );
            }
            return(resultJSON("success", "", $returnArrData));
        }else{
            $returnArrData[] = array(
                'errorVersion'  => '2',
                'mbrUsername'   => '',
                'mbrFirstName'  => ''
            );
            return(resultJSON("error", "Sponsor not found", $returnArrData));
        }
    }
}

function getNewMemberNotActivated($conn, $username){

    $sql = "SELECT tjDate, tjUsername, tjFirstName, pacName, countryDesc, tjMobileCode, tjMobile, tjEmail, stDesc FROM ";
    $sql .= " dtTempJoin INNER JOIN msPackage ON tjPackage = pacID ";
    $sql .= " INNER JOIN msCountry ON countryID = tjCountry";
    $sql .= " INNER JOIN msStatus ON stID = tjStID";
    $sql .= " WHERE tjSponsor='" . $username . "'";
    $sql .= " ORDER BY tjDate DESC ";
    
    $returnArrData = array();
    if ($query = $conn->query($sql)) {
        if ($query->num_rows > 0) {

            while ($row = $query->fetch_assoc()) {
                $tjDate         = $row['tjDate'];
                $tjUsername     = $row['tjUsername'];
                $tjFirstName    = $row['tjFirstName'];
                $tjMobile       = $row['tjMobileCode'].$row['tjMobile'];
                $tjEmail        = $row['tjEmail'];
                $countryDesc    = $row['countryDesc'];
                $pacName        = $row['pacName'];
                $stDesc         = $row['stDesc'];

                $returnArrData[]  = array(

                    'tjDate'        => $tjDate,
                    'tjUsername'    => $tjUsername,
                    'tjFirstName'   => $tjFirstName,
                    'tjMobile'      => $tjMobile,
                    'tjEmail'       => $tjEmail,
                    'countryDesc'   => $countryDesc,
                    'pacName'       => $pacName,
                    'stDesc'        => $stDesc

                );

            }

            return(resultJSON("success", "", $returnArrData));

        }else{

            $returnArrData[]  = array(
                    'tjDate'        => '',
                    'tjUsername'    => '',
                    'tjFirstName'   => '',
                    'tjMobile'      => '',
                    'tjEmail'       => '',
                    'countryDesc'   => '',
                    'pacName'       => '',
                    'stDesc'        => ''
            );

            return(resultJSON("success", "No Record", $returnArrData));
        }    
    }
}

function getNetworkTree($conn, $usernameLogin, $searchUsername){
    
    $username = "";
    $returnArrData = array();

    $username = $usernameLogin;
    
    if ($searchUsername != ""){
        $username = $searchUsername;
    } 

    

    //Me (mid node)
    $arrDataMe      = fGetDataMember($conn, $username, $usernameLogin);

    if($arrDataMe != ""){
        $usernameMid    = $arrDataMe['username'];
        $nameMid        = fTruncateSentence($arrDataMe["name"], 10);
        $uplineMid      = $arrDataMe['upline'];
        $posMid         = $arrDataMe['pos'];
        $packageMid     = $arrDataMe['package'];

         //get data sponsor
        $myDataObj      = json_decode(fGetDataSponsor($conn, $username));
        $spnsrName      = fTruncateSentence($myDataObj->{"name"}, 10).'('.$myDataObj->{"username"}.')';

        //get data package
        $myDataObj      = json_decode(fGetDataPackage($conn, $username));
        $packageNameMid = $myDataObj->{"pacName"};

         //get data pairing
        $myDataObj  = json_decode(fCommissionPairing($username, $conn));

        $sumLeftMid = $sumRightMid = 0;
        if ($myDataObj->{"status"} == "success"){

            $sumLeftMid    = $myDataObj->{'sumLeft'};
            $sumRightMid   = $myDataObj->{'sumRight'};   

        }
    }
    


    //level 1
    $sumLeftCL = $sumRightCL = 0;
    $usernameDataCL = $nameDataCL = $uplineDataCL = $posDataCL = $spnsrNameCL = '';
    $sumLeftCR = $sumRightCR = 0;
    $usernameDataCR = $nameDataCR = $uplineDataCR = $posDataCR = $spnsrNameCR = '';

    //Level 2
    $sumLeftGCLL = $sumRightGCLL = 0;
    $usernameDataGCLL = $nameDataGCLL = $uplineDataGCLL = $posDataGCLL = $spnsrNameGCLL = '';
    $sumLeftGCLR = $sumRightGCLR = 0;
    $usernameDataGCLR = $nameDataGCLR = $uplineDataGCLR = $posDataGCLR = $spnsrNameGCLR ='';

    $sumLeftGCRL = $sumRightGCRL = 0;
    $usernameDataGCRL = $nameDataGCRL = $uplineDataGCRL = $posDataGCRL = $spnsrNameGCRL = '';
    $sumLeftGCRR = $sumRightGCRR = 0;
    $usernameDataGCRR = $nameDataGCRR = $uplineDataGCRR = $posDataGCRR = $spnsrNameGCRR = '';


    if (gettype($arrDataMe) == "array"){

        $upline     = $arrDataMe['username'];

        $arrDataCL  = fGetDataMemberByUpline_Pos($conn, $upline, "l");
        $arrDataCR  = fGetDataMemberByUpline_Pos($conn, $upline, "r");
        
        //arrdata CL
        if ($arrDataCL != "") {
            $usernameDataCL = $arrDataCL["username"];
            $nameDataCL     = fTruncateSentence($arrDataCL["name"], 10);
            $uplineDataCL   = $arrDataCL["upline"];
            $posDataCL      = $arrDataCL["pos"];
            $packageDataCL  = $arrDataCL["package"];

            $myDataObj  = json_decode(fCommissionPairing($usernameDataCL, $conn));

            if ($myDataObj->{"status"} == "success"){
                $sumLeftCL    = $myDataObj->{'sumLeft'};
                $sumRightCL   = $myDataObj->{'sumRight'};   
            }

            //get data sponsor
            $myDataObj      = json_decode(fGetDataSponsor($conn, $usernameDataCL));
            $spnsrNameCL    = fTruncateSentence($myDataObj->{"name"}, 10).'('.$myDataObj->{"username"}.')';

            //get data package
            $myDataObj      = json_decode(fGetDataPackage($conn, $usernameDataCL));
            $packageNameCL  = $myDataObj->{"pacName"};

        }

        //arrdata CR
        if ($arrDataCR != "") {
            $usernameDataCR = $arrDataCR["username"];
            $nameDataCR     = fTruncateSentence($arrDataCR["name"], 10);
            $uplineDataCR   = $arrDataCR["upline"];
            $posDataCR      = $arrDataCR["pos"];
            $packageDataCR  = $arrDataCR["package"];

            $myDataObj  = json_decode(fCommissionPairing($usernameDataCR, $conn));

            if ($myDataObj->{"status"} == "success"){
                $sumLeftCR    = $myDataObj->{'sumLeft'};
                $sumRightCR   = $myDataObj->{'sumRight'};   
            }

            //get data sponsor
            $myDataObj      = json_decode(fGetDataSponsor($conn, $usernameDataCR));
            $spnsrNameCR    = fTruncateSentence($myDataObj->{"name"}, 10).'('.$myDataObj->{"username"}.')';

             //get data package
            $myDataObj      = json_decode(fGetDataPackage($conn, $usernameDataCR));
            $packageNameCR  = $myDataObj->{"pacName"};

        }
        

        //Level 2
        if (gettype($arrDataCL == "array")) {

            $upline = '';
            if ($arrDataCL != "") {
                $upline     = $arrDataCL['username'];
            }

            $arrDataGCLL  = fGetDataMemberByUpline_Pos($conn, $upline, "l");
            $arrDataGCLR  = fGetDataMemberByUpline_Pos($conn, $upline, "r");

             //arrdata GCLL
            if ($arrDataGCLL != "") {
                $usernameDataGCLL = $arrDataGCLL["username"];
                $nameDataGCLL     = fTruncateSentence($arrDataGCLL["name"], 10);
                $uplineDataGCLL   = $arrDataGCLL["upline"];
                $posDataGCLL      = $arrDataGCLL["pos"];
                $packageDataGCLL  = $arrDataGCLL["package"];

                $myDataObj  = json_decode(fCommissionPairing($usernameDataGCLL, $conn));

                if ($myDataObj->{"status"} == "success"){
                    $sumLeftGCLL    = $myDataObj->{'sumLeft'};
                    $sumRightGCLL   = $myDataObj->{'sumRight'};
                }

                 //get data sponsor
                $myDataObj        = json_decode(fGetDataSponsor($conn, $usernameDataGCLL));
                $spnsrNameGCLL    = fTruncateSentence($myDataObj->{"name"}, 10).'('.$myDataObj->{"username"}.')';

                 //get data package
                $myDataObj        = json_decode(fGetDataPackage($conn, $usernameDataGCLL));
                $packageNameGCLL  = $myDataObj->{"pacName"};

            }

             //arrdata GCLR
            if ($arrDataGCLR != "") {
                $usernameDataGCLR = $arrDataGCLR["username"];
                $nameDataGCLR     = fTruncateSentence($arrDataGCLR["name"], 10);
                $uplineDataGCLR   = $arrDataGCLR["upline"];
                $posDataGCLR      = $arrDataGCLR["pos"];
                $packageDataGCLR  = $arrDataGCLR["package"];

                $myDataObj  = json_decode(fCommissionPairing($usernameDataGCLL, $conn));


                if ($myDataObj->{"status"} == "success"){
                    $sumLeftGCLR    = $myDataObj->{'sumLeft'};
                    $sumRightGCLR   = $myDataObj->{'sumRight'};
                }

                 //get data sponsor
                $myDataObj      = json_decode(fGetDataSponsor($conn, $usernameDataGCLR));
                $spnsrNameGCLR  = fTruncateSentence($myDataObj->{"name"}, 10).'('.$myDataObj->{"username"}.')';

                //get data package
                $myDataObj        = json_decode(fGetDataPackage($conn, $usernameDataGCLR));
                $packageNameGCLR  = $myDataObj->{"pacName"};

            }

        }

        if (gettype($arrDataCR == "array")) {

            $upline = '';
            if ($arrDataCR != "") {
                $upline     = $arrDataCR['username'];
            }

            $arrDataGCRL  = fGetDataMemberByUpline_Pos($conn, $upline, "l");
            $arrDataGCRR  = fGetDataMemberByUpline_Pos($conn, $upline, "r");
            
             //arrdata GCRL
            if ($arrDataGCRL != "") {
                $usernameDataGCRL = $arrDataGCRL["username"];
                $nameDataGCRL     = fTruncateSentence($arrDataGCRL["name"], 10);
                $uplineDataGCRL   = $arrDataGCRL["upline"];
                $posDataGCRL      = $arrDataGCRL["pos"];
                $packageDataGCRL  = $arrDataGCRL["package"];

                $myDataObj  = json_decode(fCommissionPairing($usernameDataGCRL, $conn));

                if ($myDataObj->{"status"} == "success"){
                    $sumLeftGCRL    = $myDataObj->{'sumLeft'};
                    $sumRightGCRL   = $myDataObj->{'sumRight'};
                }

                 //get data sponsor
                $myDataObj      = json_decode(fGetDataSponsor($conn, $usernameDataGCRL));
                $spnsrNameGCRL  = fTruncateSentence($myDataObj->{"name"}, 10).'('.$myDataObj->{"username"}.')';

                //get data package
                $myDataObj        = json_decode(fGetDataPackage($conn, $usernameDataGCRL));
                $packageNameGCRL  = $myDataObj->{"pacName"};
            }
            
             //arrdata GCRR
            if ($arrDataGCRR != "") {
                $usernameDataGCRR = $arrDataGCRR["username"];
                $nameDataGCRR     = fTruncateSentence($arrDataGCRR["name"], 10);
                $uplineDataGCRR   = $arrDataGCRR["upline"];
                $posDataGCRR      = $arrDataGCRR["pos"];
                $packageDataGCRR  = $arrDataGCRR["package"];

                $myDataObj  = json_decode(fCommissionPairing($usernameDataGCRR, $conn));

                if ($myDataObj->{"status"} == "success"){
                    $sumLeftGCRR    = $myDataObj->{'sumLeft'};
                    $sumRightGCRR   = $myDataObj->{'sumRight'};
                }

                 //get data sponsor
                $myDataObj      = json_decode(fGetDataSponsor($conn, $usernameDataGCRR));
                $spnsrNameGCRR  = fTruncateSentence($myDataObj->{"name"}, 10).'('.$myDataObj->{"username"}.')';

                 //get data package
                $myDataObj        = json_decode(fGetDataPackage($conn, $usernameDataGCRR));
                $packageNameGCRR  = $myDataObj->{"pacName"};
            }

        }

         $returnArrData = array(
            "midnode"   => array(
                "usernameMid"   => $usernameMid,
                "nameMid"       => $nameMid,
                "uplineMid"     => $uplineMid,
                "posMid"        => $posMid,
                //"packageMid"    => $packageMid,
                //"packageNameMid"=> $packageNameMid,
                "sumLeftMid"    => numFormat($sumLeftMid, 0),
                "sumRightMid"   => numFormat($sumRightMid, 0),
                "spnsrNameMid"  => $spnsrName 
            ),

            //dataCL
            "dataCL"    => array(
                "usernameDataCL"    => $usernameDataCL,
                "nameDataCL"        => $nameDataCL,
                "uplineDataCL"      => $uplineDataCL,
                "posDataCL"         => $posDataCL,
                //"packageDataCL"     => $packageDataCL,
                //"packageNameCL"     => $packageNameCL,
                "sumLeftCL"         => numFormat($sumLeftCL,0),
                "sumRightCL"        => numFormat($sumRightCL,0),
                "spnsrNameCL"       => $spnsrNameCL   
            ),

            //dataCR
            "dataCR"    => array(
                "usernameDataCR"    => $usernameDataCR,
                "nameDataCR"        => $nameDataCR,
                "uplineDataCR"      => $uplineDataCR,
                "posDataCR"         => $posDataCR,
                //"packageDataCR"     => $packageDataCR,
                //"packageNameCR"     => $packageNameCR,
                "sumLeftCR"         => numFormat($sumLeftCR, 0),
                "sumRightCR"        => numFormat($sumRightCR, 0),
                "spnsrNameCR"       => $spnsrNameCR   
            ),

            //dataGCLL
            "dataGCLL"    => array(
                "usernameDataGCLL"    => $usernameDataGCLL,
                "nameDataGCLL"        => $nameDataGCLL,
                "uplineDataGCLL"      => $uplineDataGCLL,
                "posDataGCLL"         => $posDataGCLL,
                //"packageDataGCLL"     => $packageDataGCLL,
                //"packageNameGCLL"     => $packageNameGCLL,
                "sumLeftGCLL"         => numFormat($sumLeftGCLL, 0),
                "sumRightGCLL"        => numFormat($sumRightGCLL, 0),
                "spnsrNameGCLL"       => $spnsrNameGCLL
            ),

            //dataGCLR
            "dataGCLR"    => array(
                "usernameDataGCLR"    => $usernameDataGCLR,
                "nameDataGCLR"        => $nameDataGCLR,
                "uplineDataGCLR"      => $uplineDataGCLR,
                "posDataGCLR"         => $posDataGCLR,
                //"packageDataGCLR"     => $packageDataGCLR,
                //"packageNameGCLR"     => $packageNameGCLR,
                "sumLeftGCLR"         => numFormat($sumLeftGCLR, 0),
                "sumRightGCLR"        => numFormat($sumRightGCLR, 0),
                "spnsrNameGCLR"       => $spnsrNameGCLR  
            ),

            //dataGCRL
            "dataGCRL"    => array(
                "usernameDataGCRL"    => $usernameDataGCRL,
                "nameDataGCRL"        => $nameDataGCRL,
                "uplineDataGCRL"      => $uplineDataGCRL,
                "posDataGCRL"         => $posDataGCRL,
                // "packageDataGCRL"     => $packageDataGCRL,
                // "packageNameGCRL"     => $packageNameGCRL,
                "sumLeftGCRL"         => numFormat($sumLeftGCRL, 0),
                "sumRightGCRL"        => numFormat($sumRightGCRL, 0),
                "spnsrNameGCRL"       => $spnsrNameGCRL    
            ),

            //dataGCRR
            "dataGCRR"    => array(
                "usernameDataGCRR"    => $usernameDataGCRR,
                "nameDataGCRR"        => $nameDataGCRR,
                "uplineDataGCRR"      => $uplineDataGCRR,
                "posDataGCRR"         => $posDataGCRR,
                //"packageDataGCRR"     => $packageDataGCRR,
                //"packageNameGCRR"     => $packageNameGCRR,
                "sumLeftGCRR"         => numFormat($sumLeftGCRR, 0),
                "sumRightGCRR"        => numFormat($sumRightGCRR, 0),
                "spnsrNameGCRR"       => $spnsrNameGCRR  
            ),
        );

        return(resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData = array(
            ""   => array(
                ""   => ""
            )
        );
        return(resultJSON("error", "Search username tidak dapat ditemukan di genealogy ini", $returnArrData));
    }
}

function getNameFromTmpJoin($conn, $username, $upline){
    global $DEF_STATUS_ACTIVE;  
    $returnArrData = array();
    $sql = "SELECT tjUsername, tjFirstName, tjPackage, pacName, tjSponsor, mbrFirstName, tjStID FROM ((dtTempJoin ";
    $sql .= " INNER JOIN msPackage ON tjPackage=pacID) ";
    $sql .= " INNER JOIN dtMember ON mbrUsername = tjSponsor) ";
    $sql .= " WHERE tjUsername='" . $username . "'";

    $returnArrData = array(
        "name"          => '',
        "package"       => '',
        "packageName"   => '',
        "sponsor"       => '',
        "sponsorName"   => ''
    );

    $query = $conn->query($sql);
    if ($query->num_rows > 0){
        $row = $query->fetch_assoc();
        //Checking Status
        if ($row['tjStID'] != $DEF_STATUS_ACTIVE){
            return(resultJSON("error", "Email address has not been verified", $returnArrData));
        }else{
            //checking Genealogy Tree, new member must below sponsor tree
            if (fCheckGenealogyTree($upline, $row["tjSponsor"], $conn)){
                $returnArrData = array(
                    "name"          => $row["tjFirstName"],
                    "package"       => $row["tjPackage"],
                    "packageName"   => $row["pacName"],
                    "sponsor"       => $row["tjSponsor"],
                    "sponsorName"   => $row["mbrFirstName"]
                );
                return(resultJSON("success", "", $returnArrData));
            }else{
                return(resultJSON("error", "Wrong Genealogy Tree", $returnArrData));
            }
        }
    }else{
        return(resultJSON("error", "username not found", $returnArrData));
    }
}


function postActivateMember($conn, $sUserName, $actUsername, $actUpline, $actPos, $actPackage){
    global $DEF_VOUCHER_PRICE, $DEF_STATUS_USED, $DEF_STATUS_APPROVED, $DEF_STATUS_ACTIVE, $DEF_STATUS_NEW, $DEF_VOUCHER_USED_FOR_ACTIVATION, $DEF_EBOOK_BASIC, $DEF_TYPE_PURCHASE_ACT, $DEF_STATUS_PENDING, $DEF_URL_MYMAC_API;

    if ($actUsername == "" || $actUpline == "" || $actPackage == "" || $actPos == ""){
    //error, incomplete data
        return(resultJSON("error", "Incomplete data", ""));
        die();  
    }


    //Rechecking position (left/right)
    $sql    = "SELECT mbrUsername from dtMember WHERE mbrUpline='" . $actUpline . "' and mbrPos='" . $actPos . "'";
    $query = $conn->query($sql);
    if ($query->num_rows > 0){
        //position has been taken
        return(resultJSON("error", "Position has been taken", ""));
        die();
    }


    //1.1 Get Package Price
    $pacPrice = $numOfVoucherRequired = 0;
    $sql = "SELECT pacPrice FROM msPackage WHERE pacID='" . $actPackage . "'";
    if ($query = $conn->query($sql)){
        if ($query->num_rows > 0){
            $row = $query->fetch_assoc();
            $pacPrice = $row["pacPrice"];
        }
    }else{
        return(resultJSON("error", "error".mysqli_error($conn), ""));
        die();
    }


    //1.2 Checking number of voucher Required AND Voucher Balance
    if ($pacPrice > 0){
        $numOfVoucherRequired = ceil($pacPrice / $DEF_VOUCHER_PRICE);   //Number of Voucher Required (@2800)
        //checking Voucher Balance
        //$sql = "SELECT count(fivVCode) VoucherBalance FROM ((dtFundIn ";
        $sql = "SELECT fivFinID, fivVCode FROM ((dtFundIn ";
        $sql .= " inner join dtFundInVoucher on finID = fivFinID and finStatus='" . $DEF_STATUS_APPROVED . "')";
        $sql .= " inner join dtVoucher on vCode = fivVCode and vStatus = '" . $DEF_STATUS_USED . "'";
        $sql .= " and fivStatus = '" . $DEF_STATUS_ACTIVE ."')";
        $sql .= " WHERE finMbrUsername='" . $sUserName . "'";
        $arrVoucher = array();
        if ($query = $conn->query($sql)){
            if ($query->num_rows > 0){
                while ($row = $query->fetch_assoc()){
                    //$VoucherBalance   = $row["VoucherBalance"];
                    $arrVoucher[] = array("fivFinID" => $row["fivFinID"], "fivVCode" => $row["fivVCode"]);  
                }
            }
        }else{
            return(resultJSON("error", "error".$conn->error, ""));
            die();
        }
        
        $VoucherBalance = sizeof($arrVoucher);
        if ($numOfVoucherRequired > $VoucherBalance){ //VoucherBalance not enough
            return(resultJSON("error", "Your Balance is not enough", ""));
            die();
        }
    }

    $conn->autocommit(false);


    //1.3 Insert dtMember (move from tempJoin)
    //1.3.1. Insert new record: move dtTempJoin to dtMember, Insert into trPassword, trPIN, Transaction, dtBnsSponsor, dtBnsPassedUp
        //dtTempJoin to dtMember
        
        /*
        //this query cause error on server... but works on local server.
        $sql = "SELECT tjSponsor, tjPasswd, trPacID, tjFirstName, tjLastName, tjIDType, tjIDN, tjEmail, tjMobileCode, tjMobile, tjBOD, tjAddr, tjCountry, tjState, tjCity";
        $sql .= " FROM ((dtMember ";
        $sql .= " inner join Transaction on mbrUsername=trUsername) ";
        $sql .= " inner join dtTempJoin on tjSponsor = mbrUsername) ";
        $sql .= " WHERE tjUsername = '" . $actUsername . "'";
        $sql .= " order by trDate desc limit 1";
        */

    $sql = "SELECT tjSponsor, tjPasswd, trPacID, tjFirstName, tjLastName, tjIDType, tjIDN, tjEmail, tjMobileCode, tjMobile, tjBOD, tjAddr, tjCountry, tjState, tjCity, tjStID FROM ( ";
    $sql .= " SELECT * FROM dtMember INNER JOIN Transaction ON mbrUsername = trUsername )A ";
    $sql .= " INNER JOIN dtTempJoin ON mbrUsername=tjSponsor ";
    $sql .= " WHERE tjUsername = '" . $actUsername . "'";
    $sql .= " ORDER BY trDate DESC LIMIT 1";
    //$sql  = "SELECT * FROM dtTempJoin WHERE tjUsername ='" . $actUsername . "'";
    $query = $conn->query($sql);
    if ($query->num_rows > 0){
        if ($row    = $query->fetch_assoc()){
            if ($row['tjStID'] != $DEF_STATUS_ACTIVE) {
                $conn->rollback();
                return(resultJSON("error", "Email address has not been verified", ""));
                die();
            }else{ //boleh insert

                if (!fCekVerificationID($conn, $actUsername, $row["tjIDN"])) {
                    return(resultJSON("error", "ID Number has been used"));
                    die();
                }

                $passWord           = $row["tjPasswd"];
                $sponsorUsername    = $row["tjSponsor"];
                $pacIDSponsor       = $row["trPacID"];
                $mbrEmail           = $row["tjEmail"];
                $mbrFirstName       = $row["tjFirstName"];
                $mbrLastName        = $row["tjLastName"];  

                $arrData = array(
                    0 => array ("db" => "mbrUsername"   , "val" => $actUsername),
                    1 => array ("db" => "mbrSponsor"    , "val" => $row["tjSponsor"]),
                    2 => array ("db" => "mbrUpline"     , "val" => $actUpline),
                    3 => array ("db" => "mbrPos"        , "val" => $actPos),
                    4 => array ("db" => "mbrFirstName"  , "val" => $row["tjFirstName"]),
                    5 => array ("db" => "mbrLastName"   , "val" => $row["tjLastName"]),
                    6 => array ("db" => "mbrIDType"     , "val" => $row["tjIDType"]),
                    7 => array ("db" => "mbrIDN"        , "val" => $row["tjIDN"]),
                    8 => array ("db" => "mbrEmail"      , "val" => $row["tjEmail"]),
                    9 => array ("db" => "mbrMobileCode" , "val" => $row["tjMobileCode"]),
                    10 => array ("db" => "mbrMobile"    , "val" => $row["tjMobile"]),
                    11 => array ("db" => "mbrBOD"       , "val" => $row["tjBOD"]),
                    12 => array ("db" => "mbrAddr"      , "val" => $row["tjAddr"]),
                    13 => array ("db" => "mbrCountry"   , "val" => $row["tjCountry"]),
                    14 => array ("db" => "mbrState"     , "val" => $row["tjState"]),
                    15 => array ("db" => "mbrCity"      , "val" => $row["tjCity"]),
                    16 => array ("db" => "mbrStID"      , "val" => $DEF_STATUS_ACTIVE),
                    17 => array ("db" => "mbrDate"      , "val" => "CURRENT_TIME()")
                    );
                                    

                if (!fInsert("dtMember", $arrData, $conn)) {
                    $conn->rollback();
                    return(resultJSON("error", "Error record member".$conn->error, ""));
                    die();
                }
                unset($arrData);
            }
        }else{
            $conn->rollback();
            return(resultJSON("error", "Error Record member - Fetch new failed", ""));
            die();
        }
    }//end dtTempJoin to dtMember
    else{
        $conn->rollback();
        return(resultJSON("error", "Error".$conn->error, ""));
        die(); 
    }



    //trPassword, 
    $arrData = array(
        0 => array ("db" => "passMbrUsername"   , "val" => $actUsername),
        1 => array ("db" => "passDate"          , "val" => "CURRENT_TIME()"),
        2 => array ("db" => "passWord"          , "val" => $passWord)
        );
            
    if (!fInsert("trPassword", $arrData, $conn)) {
        $conn->rollback();
        return(resultJSON("error", "Error update password". $conn->error, ""));
        die();
    }
    unset($arrData);

    //Transaction, 
    $arrData = array(
        0 => array ("db" => "trUsername"    , "val" => $actUsername),
        1 => array ("db" => "trPacID"           , "val" => $actPackage),
        2 => array ("db" => "trDate"            , "val" => "CURRENT_TIME()"),
        3 => array ("db" => "trStatus"          , "val" => $DEF_STATUS_NEW)
        );
        
    if (!fInsert("Transaction", $arrData, $conn)) {
        //echo (fSendStatusMessage("error", "<b>Update Transaction - </b>" . mysqli_error($conn)));
        $conn->rollback();
        return(resultJSON("error", "Error update Transaction - ".mysqli_error($conn), ""));
        die();
    }
    unset($arrData);


    //dtBnsSponsor, 
    $sponsorBonus = fGetBonus("SPONSOR", $actPackage, $pacIDSponsor, $conn);
    if ($sponsorBonus > 0){
        $arrData = array(
            0 => array ("db" => "bnsSpUsername"     , "val" => $sponsorUsername),
            1 => array ("db" => "bnsSpTrUsername"   , "val" => $actUsername),
            2 => array ("db" => "bnsSpTrPacID"      , "val" => $actPackage),
            3 => array ("db" => "bnsSpDate"         , "val" => "CURRENT_TIME()"),
            4 => array ("db" => "bnsSpAmount"       , "val" => $sponsorBonus)
            );
            
        if (!fInsert("dtBnsSponsor", $arrData, $conn)) {
            $conn->rollback();
            return(resultJSON("error", "Error update bonus sponsor".$conn->error, ""));
            die();
        }
        unset($arrData);
    }else{
        //if sponsor bonus == 0, means error
        $conn->rollback();
        return(resultJSON("error", "Error Get bonus sponsor failed", ""));
        die();
    }

    //1.3.2. Delete record dtTempJoin
    $arrDataQuery = array(
        "tjUsername" => $actUsername
    ); //define your query in the arrData
    if (!fDeleteRecord("dtTempJoin", $arrDataQuery, $conn)){
        $conn->rollback();
        return(resultJSON("error", "Error Delete TempJoin".$conn->error, ""));
        die();
    }
    unset($arrDataQuery);


    //1.4 Update dtFundInVoucher (status="USED", usedFor="ACTIVATION", usedOn=USERNAME, fivDate="CURRENT_TIME()")
    $arrData    = array(
        "fivStatus"     => $DEF_STATUS_USED,
        "fivUsedFor"    => $DEF_VOUCHER_USED_FOR_ACTIVATION,
        "fivUserOn"     => $actUsername,
        "fivDate"       => "CURRENT_TIME()"
    );

    $arrDataQuery = array();
    $counter = 0;
    //moving some data of arrVoucher to arrDataQuery 
    foreach ($arrVoucher as $key => $value){
        if ($counter >= $numOfVoucherRequired) {
            break;
        }else{
            $arrDataQuery = array (
                        "fivFinID" => $value["fivFinID"], 
                        "fivVCode" => $value["fivVCode"]
                        );
            $counter++;
            
            if (!fUpdateRecord("dtFundInVoucher", $arrData, $arrDataQuery, $conn)){
                //echo (fSendStatusMessage("error", "<b>Update FundInVoucher - </b>" . $conn->error));
                return(resultJSON("error", "Error update FoundInVoucher - ".$conn->error, ""));
                $conn->rollback();
                die();
            }
            unset($arrDataQuery);
        }
    }
    unset($arrData);  

    //insert to trProduct dan trProDetial
    $sql  = " SELECT * FROM msProduct";
    $sql .= " WHERE proID = '".$DEF_EBOOK_BASIC."' ";
    $result = $conn->query($sql);
    $trProTransID = strtotime("+0");
    if ($row = $result->fetch_assoc()){
        $table = "trProduct";
        $arrData = array(
            array ("db" => "trProTransID"       , "val" => $trProTransID),
            array ("db" => "trProUsername"      , "val" => $sponsorUsername),
            array ("db" => "trProUserBeli"      , "val" => $actUsername),
            array ("db" => "trProType"          , "val" => $DEF_TYPE_PURCHASE_ACT),
            array ("db" => "trProDate"          , "val" => "CURRENT_TIME()"),
            array ("db" => "trProAmount"        , "val" => $row['proPrice']),
            array ("db" => "trProDisc"          , "val" => $row['proPrice']),
            array ("db" => "trProUpdateDate"    , "val" => "CURRENT_TIME()"),
            array ("db" => "trProStatus"        , "val" => $DEF_STATUS_APPROVED)                
        );
        if (!fInsert($table, $arrData, $conn)){
            $conn->rollback();
            fSendToAdminApps($sUsername, "Activate Member", "activateMember.php", "Insert data to trProduct failed");
            return(resultJSON("error", "Record product - ".mysqli_error($conn), ""));
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
                fSendToAdminApps($sUsername, "Activate Member", "activateMember.php", "Insert data to trProDetail failed");
                //echo (fSendStatusMessage("error", "<b>Record produk detail - </b>" . mysqli_error($conn)));
                return(resultJSON("error", "Record Product detail - ".$conn->error, ""));
                die();
            }
        }       
    }else{
        $conn->rollback();
        fSendToAdminApps($sUsername, "Activate Member", "activateMember.php", "Insert data product & produk detail failed");
        //echo (fSendStatusMessage("error", "<b>Record produk & produk detail - </b>" . mysqli_error($conn)));
        return(resultJSON("error", "Record Product and Product Detail - ".$conn->error, ""));
        die();
    }  
    unset($arrData);

    //insert to dtUserEbook
    $table = "dtUserEbook";
    $arrData = array(
        0 => array ("db" => "ebproTransID"  , "val" => $trProTransID), //samakan dengan tabel order (trProduct) 
        1 => array ("db" => "ebUsername"    , "val" => $actUsername),
        2 => array ("db" => "ebEmail"       , "val" => $mbrEmail), // from dtTempJoin
        3 => array ("db" => "ebFirstName"   , "val" => $mbrFirstName), // from dtTempJoin
        4 => array ("db" => "ebLastName"    , "val" => $mbrLastName), // from dtTempJoin
        5 => array ("db" => "ebDate"        , "val" => "CURRENT_TIME()"),
        6 => array ("db" => "ebStatus"      , "val" => $DEF_STATUS_ACTIVE)
    );
    if (!fInsert($table, $arrData, $conn)){
        $conn->rollback();
        fSendToAdminApps($sUsername, "Activate Member", "activateMember.php", "Insert data to dtUserEbook failed");
        //echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . mysqli_error($conn)));
        return(resultJSON("error", "Generate Login Ebook Failed - ".$conn->error, ""));
        die();
    }
    unset($arrData);

    //insert to trPassEbook
    $table = "trPassEbook";
    $pePasswd = substr($trProTransID, -6);
    $arrData = array(
        0 => array ("db" => "peID"          , "val" => $trProTransID),
        1 => array ("db" => "peUsername"    , "val" => $actUsername),
        2 => array ("db" => "pePasswd"      , "val" => md5($pePasswd)),
        3 => array ("db" => "peDate"        , "val" => "CURRENT_TIME()")
    );
    if (!fInsert($table, $arrData, $conn)){
        $conn->rollback();
        fSendToAdminApps($sUsername, "Activate Member", "activateMember.php", "Insert data to trPassEbook failed");
        //echo (fSendStatusMessage("error", "<b>Generate Login Ebook Failed - </b>" . mysqli_error($conn)));
        return(resultJSON("error", "Generate Login Ebook Failed - ".$conn->error, ""));
        die();
    }
    unset($arrData);

    //Save to dtCornEmail
    savetoCornEmail($conn, "ACTIVATION_MBR_TO_SP", $actUsername);
    savetoCornEmail($conn, "SEND_EBOOK_DATA", $actUsername);

    $query->close();
    // $conn->rollback();
    $conn->commit();

    fSendNotifToEmail("NEW_MEMBER_ACTIVATED", $actUsername);

    //insert default data to mymac -> result curl
    $sendData = array(
        "action"    => "new_member_activated",
        "email"     => $mbrEmail,
        "username"  => $actUsername,
        "passwd"    => $passWord // password from dtTempJoin already md5 format
    );
    $Rcurl = fCurl($DEF_URL_MYMAC_API, $sendData);
    $resultJSON = json_decode($Rcurl);
    if ($resultJSON->status == "error"){
        //return error message from mymac
        flogMyMac($actUsername, "Activate Member", "activateMember.php", $resultJSON->message, $DEF_STATUS_PENDING); 
        $conn->commit();
    }
    return(resultJSON("success", "New Member Activated", ""));
    die(); 
}


function getVerifyIDStatus($conn, $username){
    global $DEF_STATUS_ONPROGRESS,  $DEF_STATUS_APPROVED, $DEF_STATUS_DECLINED;

    $returnArrData = array();
    $sql     = "SELECT dtMember.*, IFNULL(vrStatus,'-') AS vrStatus, vrFileName, vrIDNum FROM dtMember";
    $sql    .= ' LEFT JOIN dtVerify ON vrUsername = mbrUsername';
    $sql    .= " WHERE mbrUsername = '".$username."'";

    $query = $conn->query( $sql );

    if ( $row = $query->fetch_assoc() ) {

        $vrStatus       = $row['vrStatus'];
        $vrIDNum        = $row['vrIDNum'];  

        if ( $vrStatus == $DEF_STATUS_ONPROGRESS ) {
            $returnArrData = array(
                'vrIDNum'       => $vrIDNum,
                'vrStatus'      => $vrStatus
            );
        } elseif ( $vrStatus == $DEF_STATUS_APPROVED ) {
            $returnArrData = array(
                'vrIDNum'       => $vrIDNum,
                'vrStatus'      => $vrStatus
            );
        } else if ( $vrStatus == $DEF_STATUS_DECLINED ) {
            $returnArrData = array(
                'mbrIDN'        => $row['mbrIDN'],
                'mbrFirstName'  => $row['mbrFirstName'],
                'mbrLastName'   => $row['mbrLastName'],
                'mbrBOD'        => $row['mbrBOD'],
                'oldvrFileName' => $row['vrFileName'],
                'vrIDNum'       => $vrIDNum,
                'vrStatus'      => $vrStatus
            );
        } else {
            $returnArrData = array(
                'vrStatus'      => $vrStatus,
                'vrIDNum'       => $vrIDNum,
            );
        }

        return(resultJSON("success", "", $returnArrData));
    }else{
        return(resultJSON("error", "", $returnArrData));
    }
}

function getStatusRenew($conn, $username){
    $sql = "SELECT m.*, s.mbrUsername as spUsername, s.mbrFirstName as spName, u.mbrUsername as upUsername, u.mbrFirstName as upName, c.countryDesc, ";
    $sql .= " pacID, pacName, IF( DATE_ADD( DATE(m.mbrDate), INTERVAL 6 MONTH ) > CURRENT_DATE(), 'ALLOWED_UP', 'NOT_ALLOWED') AS Upgradeable, ";
                //$sql .= " IF( DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 -1) MONTH) < CURRENT_DATE(), 'ALLOW_RENEW', 'RENEW_NOT_ALLOWED') AS Renew, ";

                //tambahkan utk validasi 1bulan setelah expired, sdh tidak bisa renew >> && (DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 +1) MONTH) >= CURRENT_DATE())
    $sql = "SELECT m.*, s.mbrUsername as spUsername, s.mbrFirstName as spName, u.mbrUsername as upUsername, u.mbrFirstName as upName, c.countryDesc, ";
    $sql .= " pacID, pacName, IF( DATE_ADD( DATE(m.mbrDate), INTERVAL 6 MONTH ) > CURRENT_DATE(), 'ALLOWED_UP', 'NOT_ALLOWED') AS Upgradeable, ";
                //$sql .= " IF( DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 -1) MONTH) < CURRENT_DATE(), 'ALLOW_RENEW', 'RENEW_NOT_ALLOWED') AS Renew, ";

                //tambahkan utk validasi 1bulan setelah expired, sdh tidak bisa renew >> && (DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 +1) MONTH) >= CURRENT_DATE())
    $nBlnSebelum = 2;
    $nBlnSetelah = 3;
    $sql .= " IF((DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 - " . $nBlnSebelum . ") MONTH) < CURRENT_DATE()) && (DATE_ADD( DATE(m.mbrDate), INTERVAL (trThn*12 + " . $nBlnSetelah . ") MONTH) >= CURRENT_DATE()), 'ALLOW_RENEW', 'RENEW_NOT_ALLOWED') AS Renew, ";
    $sql .= " trDate, DATE_ADD( m.mbrDate, INTERVAL (trThn*12) MONTH) AS ExpiredDate FROM dtMember m ";
    $sql .= " INNER JOIN dtMember s on m.mbrSponsor = s.mbrUsername ";
    $sql .= " INNER JOIN dtMember u on m.mbrUpline = u.mbrUsername ";
    $sql .= " INNER JOIN msCountry c on m.mbrCountry = c.countryID ";
    $sql .= " INNER JOIN (SELECT * FROM Transaction WHERE trID = (SELECT trID FROM Transaction WHERE trUsername='". $username . "' ORDER BY trDate DESC LIMIT 1)) t ON m.mbrUsername = t.trUsername ";
    $sql .= " INNER JOIN msPackage AS pac ON pacID = t.trPacID";
    $sql .= " WHERE m.mbrUsername = '" . $username . "'";

    //echo $sql;
    $returnArrData = array();

    if ($query = $conn->query($sql)){

        if ($row = $query->fetch_assoc()){  

            if ($row["Renew"] == "ALLOW_RENEW") {
                
                $returnArrData = array(
                    "renew" => "true" 
                );

                return(resultJSON("success", "", $returnArrData));
            }else{

                $returnArrData = array(
                    "renew" => "false" 
                );

                return(resultJSON("success", "", $returnArrData));
            }
        }

        return(resultJSON("error", "Something wrong", $returnArrData));
    }
}

function getStatusSecurity($conn, $username){
    global $DEF_STATUS_APPROVED, $DEF_STATUS_PENDING, $DEF_STATUS_BLOCKED;

    $sql = "SELECT * FROM trPIN WHERE pinMbrUsername = '" . $username . "'"; 
    $sql .= " AND (pinStID='". $DEF_STATUS_APPROVED . "' OR pinStID='". $DEF_STATUS_PENDING . "')";
    $sql .= " ORDER BY pinDate DESC LIMIT 1";
    
    //NB: as long as has pending request or existing pin, member can not do request new security password.
    $query = $conn->query($sql);
    $returnArrData = array();

    if ($query->num_rows > 0) {
        if ($row = $query->fetch_assoc()){
            if ($row['pinStID'] == $DEF_STATUS_APPROVED){

                $pinID = $row['pinStID'];

                $returnArrData = array(
                    "reqsec"    => "false"
                );

                return(resultJSON("success", "", $returnArrData));
            }else if ($row['pinStID'] == $DEF_STATUS_PENDING){
                $returnArrData = array(
                    "reqsec"    => "true"
                );
                return(resultJSON("success", "", $returnArrData));
            }else if ($row['pinStID'] == $DEF_STATUS_BLOCKED){
                $returnArrData = array(
                    "reqsec"    => "true",
                );
                return(resultJSON("success", "", $returnArrData));
            }
        }
    }else{
        $returnArrData = array(
            "reqsec"    => '',
        );
        return(resultJSON("success", "", $returnArrData));
    }
}


function reqSecurityPasswd($conn, $username){
    global $DEF_STATUS_PENDING, $DEF_STATUS_APPROVED;

    //check sent security password
    $sql = "SELECT * FROM trPIN WHERE pinMbrUsername='$username' ORDER BY pinDate DESC LIMIT 1";
    $query = $conn->query($sql);
    if ($row = $query->fetch_assoc()){
        if ($row['pinStID'] == $DEF_STATUS_PENDING){
        //exist, so resend activation of security password
        //send email for activation
            if (fSendNotifToEmail("REQUEST_SECURITY_PIN", $username)){
                $returnArrData = array(
                    "reqsec"    => "true"
                );            
                return(resultJSON("success", "Pin is sent to your email. Please check for activation", $returnArrData));
            }else{
                    //error sending email
            }
        }else if ($row['pinStID'] == $DEF_STATUS_APPROVED){
            $returnArrData = array(
                "reqsec"    => "false"
            );            
            return(resultJSON("success", "", $returnArrData));
        }
    }else{
            //1. Generate new pin, save with status pending
        $newPinWord = str_shuffle (strtotime("now"));
                //Insert New PIN
        $pinID = strtotime("now");
        $arrData = array(
                    0 => array ("db" => "pinID"             , "val" => $pinID),
                    1 => array ("db" => "pinMbrUsername"    , "val" => $username),
                    2 => array ("db" => "pinWord"           , "val" => $newPinWord), //don't encrypt first, because not yet activated.
                    3 => array ("db" => "pinDate"           , "val" => "CURRENT_TIME()"),
                    4 => array ("db" => "pinStID"           , "val" => $DEF_STATUS_PENDING)
        );

        if (fInsert("trPIN", $arrData, $conn)){
            //send email for activation
            if (fSendNotifToEmail("REQUEST_SECURITY_PIN", $username)){
                        //success
                $returnArrData = array(
                    "reqsec"    => "true"
                );
                return(resultJSON("success", "Pin is sent to your email. Please check email for activation", $returnArrData));
            }else{
                        //error sending email
            }
                //NB:
                //2. send link to client to activate pin (expired in 24 hours)
                //2.1 activate pin by updating status to approved

        }else{
                    //insert fail   
            // $array = array(
            //     'error'     => 'true',
            //     'msg'       => 'Change security password failed. Contact support for help.'
            // );
            $returnArrData = array(
                ""    => ""
            );
            return(resultJSON("error", "Change security password failed. Contact support for help", $returnArrData));
        }
    }
}

function postVerifyID($conn, $username, $statusvrid, $idType, $idNumber, $idFirstName, $idLastName, $oldvrFileName, $idBOD, $imageFileType){
    global $DEF_STATUS_ONPROGRESS, $DEF_STATUS_DECLINED;
    $uploadOk = true;
    $strpos = strpos( $oldvrFileName, '.' );
    $oldFileName = substr( $oldvrFileName, 0, $strpos );
    if ( $oldFileName == '' ) {
        //jika CP kosong ( tidak ada data )
        $id = 1;
    } else {
        $id = substr( $oldFileName, -2 );
    }

    if ( $idLastName == '' ) {
        $idLastName = $idFirstName;
    }

    $target_dir     = '../../member/photo_verify/';
    $filename       = 'vr'.'_'.$username.'-'.$id;
    $target_file    = $target_dir . $filename;

    //cek apakah nama file lama sama dengan file yg akan di upload
    if ( $oldFileName == $filename ) {
        $target_delete = $target_dir . $oldvrFileName;

        if ( file_exists( $target_delete ) ) {
            unlink( $target_delete ); 
            // return(resultJSON("error", "Gagal Hapus Gambar", "");
        }

        $id = intval( $id );
        $id = $id + 1;
        $strid = str_pad( $id, 2, '0', STR_PAD_LEFT );
        $filename       = 'vr'.'_'.$username.'-'.$strid.'.'.$imageFileType;
        $target_file    = $target_dir . $filename;
    } else {
        $strid = str_pad( $id, 2, '0', STR_PAD_LEFT );
        $filename       = 'vr'.'_'.$username.'-'.$strid.'.'.$imageFileType;
        $target_file    = $target_dir . $filename;
    }

    if (!fCekVerificationID($conn, $username, $idNumber)) {
        $uploadOk = false;
        return(resultJSON("error", "ID Number has been used"));
    }

    if ($uploadOk) {
        $conn->autocommit(false);
        //belum pernah verify ID
        if ( $statusvrid == '-' ) {

            $arrData = array(
                0 => array ( 'db' => 'vrUsername', 'val' => $username ),
                1 => array ( 'db' => 'vrFileName', 'val' => $filename ),
                2 => array ( 'db' => 'vrType', 'val' => $idType ),
                3 => array ( 'db' => 'vrIDNum', 'val' => $idNumber ),
                4 => array ( 'db' => 'vrFirstName', 'val' => $idFirstName ),
                5 => array ( 'db' => 'vrLastName', 'val' => $idLastName ),
                6 => array ( 'db' => 'vrBOD', 'val' => $idBOD ),
                7 => array ( 'db' => 'vrStatus', 'val' => $DEF_STATUS_ONPROGRESS ),
                8 => array ( 'db' => 'vrDate', 'val' => 'CURRENT_TIME()' )
            );
            if ( fInsert( 'dtVerify', $arrData, $conn ) ) {
                if ( move_uploaded_file( $_FILES['fileuploadid']['tmp_name'], $target_file ) ){
                    $conn->commit();
                    return(resultJSON("success", "Upload ID Successfull", ""));
                } else {
                    $conn->rollback();
                    return(resultJSON("error", "Upload ID Failed #1", ""));
                }

            } else {
                return(resultJSON("error", "Upload ID Failed #2", ""));
            }
        }
        // verify ID ditolak sebelumnya
        else if ( $statusvrid == $DEF_STATUS_DECLINED ) {

            $arrData = array(
                'vrFileName'    => $filename,
                'vrType'        => $idType,
                'vrIDNum'       => $idNumber,
                'vrFirstName'   => $idFirstName,
                'vrLastName'    => $idLastName,
                'vrBOD'         => $idBOD,
                'vrStatus'      => $DEF_STATUS_ONPROGRESS,
                'vrDate'        => 'CURRENT_TIME()'
            );

            $arrDataQuery = array(
                'vrUsername' => $username,
                'vrType'     => $idType
            );

            if ( !fUpdateRecord( 'dtVerify', $arrData, $arrDataQuery, $conn ) ) {
                return(resultJSON("error", "Upload ID Failed #3", ""));
            } else {
                if ( move_uploaded_file( $_FILES['fileuploadid']['tmp_name'], $target_file ) )
                // if(move_uploaded_file($imageFileType, $target_dir))
                {
                    $conn->commit();
                    return(resultJSON("success", "Upload ID Successfull", ""));
                } else {
                    $conn->rollback();
                }
            }
        } else {
            return(resultJSON("error", "Upload ID Failed #3", ""));
        }
    }else{
        return(resultJSON("error", "Upload ID Failed #4", ""));
    }
}

function getBeneficiary($conn, $username){

    $sql  = "SELECT * FROM dtBeneficiary";
    $sql .= " INNER JOIN msIDType ON idtCode = BenIDType";
    $sql .= " INNER JOIN msRelationType ON RelCode = BenRelationType";
    $sql .= " WHERE BenMbrUsername = '".$username."'";

    $result = $conn->query($sql);
    $returnArrData = array();

    if ($row = $result->fetch_assoc()){
        $regDate = date_create($row['BenUpdateDate']);
        $regDate = date_format($regDate, "F d, Y h:i A");

        $benFirstName   = $row['BenFirstName'];
        $benLastName    = $row['BenLastName'];
        $idType         = $row['idtType'];
        $benIDNum       = $row['BenIDNum'];
        $benBOD         = $row['BenBOD'];
        $RelType        = $row['RelType'];

        $returnArrData = array(
            'benFirstName'  => $row['BenFirstName'],
            'benLastName'   => $row['BenLastName'],
            'idType'        => $row['idtType'],
            'benIDNum'      => $row['BenIDNum'],
            'benBOD'        => $row['BenBOD'],
            'relType'       => $row['RelType']
        );

        return(resultJSON("success", "", $returnArrData));

    }else{

        $returnArrData = array(
            'benFirstName'  => '',
            'benLastName'   => '',
            'idType'        => '',
            'benIDNum'      => '',
            'benBOD'        => '',
            'relType'       => ''
        );

        return(resultJSON("success", "", $returnArrData));
    }
}

function postBeneficiary($conn, $username, $benIdType, $benIdNum, $benFirstName, $benLastName, $benBOD, $benRelationType, $imageFileType){
    global $DEF_STATUS_APPROVED;
    if ($benLastName == ""){
        $benLastName = $benFirstName;
    }
    
    $target_dir     = '../../member/photo_verify/';
    $filename       = 'ben'.'_'.$username.'.'.$imageFileType;
    $target_file    = $target_dir . $filename;

    //cek verify ID member
    $sql  = " SELECT * FROM dtVerify";
    $sql .= " WHERE vrUsername = '".$username."' ";
    $result = $conn->query($sql);
    if ($result->num_rows > 0){
        if ($row=$result->fetch_assoc()){
            if ($row['vrStatus'] != $DEF_STATUS_APPROVED){
                return(resultJSON("error", "Your ID has not yet been verified", ""));
            }else{
                if ($row['vrIDNum'] == $benIdNum){
                    return(resultJSON("error", "Your ID and Beneficiary ID must be different", ""));
                }
            }
        }
    }else{
        return(resultJSON("error", "You have not uploaded your ID, please upload your ID", ""));
    }
    
    if ($benIdType != "" && $benIdNum != "" && $benIdNum != "" && $benFirstName != "" && $benLastName != "" && $benBOD != "" && $benRelationType != ""){
        $conn->autocommit(false);
        $arrData = array(
            0 => array ("db" => "BenMbrUsername"    , "val" => $username),
            1 => array ("db" => "BenFileID"         , "val" => $filename),
            2 => array ("db" => "BenIDType"         , "val" => $benIdType),
            3 => array ("db" => "BenIDNum"          , "val" => $benIdNum),
            4 => array ("db" => "BenBOD"            , "val" => $benBOD),
            5 => array ("db" => "BenFirstName"      , "val" => $benFirstName),
            6 => array ("db" => "BenLastName"       , "val" => $benLastName),
            7 => array ("db" => "BenRelationType"   , "val" => $benRelationType),
            8 => array ("db" => "BenUpdateDate"     , "val" => "CURRENT_TIME()")
        );
        if (fInsert("dtBeneficiary", $arrData, $conn)){
            if (move_uploaded_file($_FILES["benFileID"]["tmp_name"], $target_file)){
                $conn->commit();
                return(resultJSON("success", "Upload ID Beneficiary Successfull", ""));
            }else{
                $conn->rollback();
                return(resultJSON("error", "Upload ID Beneficiary Failed #1", ""));
            }
        }else{
            return(resultJSON("error", "Upload ID Beneficiary Failed #2", ""));
        }
        
    }else {
        return(resultJSON("error", "Upload ID Beneficiary Failed #3", ""));
    }
}

function getMsRelationType($conn){

    $sql  = "SELECT * FROM msRelationType";
    $sql .= " ORDER BY RelType ASC";

    $returnArrData = array();

    if ( $query = $conn->query( $sql ) ) {

        $msreltype = array();
        while( $row = $query->fetch_assoc() ) {
            $relCode   = $row['RelCode'];
            $relType   = $row['RelType'];

            $returnArrData[] = array(
                'relCode' => $row['RelCode'],
                'relType' => $row['RelType'],
            );
        }

        return(resultJSON("success", "", $returnArrData));
    }
}

function getMsPaymentType($conn){
    global $DEF_CATEGORY_INTERNAL_TRANSFER, $DEF_STATUS_ACTIVE;
    $returnArrData = array();
    $ptID = $ptDesc = '';

    //$DEF_CATEGORY_BANK = "BANK";

    $sql  = "SELECT * FROM msPaymentType WHERE ptCat <> '". $DEF_CATEGORY_INTERNAL_TRANSFER ."' ";
    $sql .= " AND ptStID='".$DEF_STATUS_ACTIVE."' AND ptCat='BANK' ";
    $sql .= " ORDER BY ptDesc ASC ";

    $query = $conn->query($sql);
    while ($row = $query->fetch_assoc()) {

        $ptID   = $row["ptID"];
        $ptDesc = $row["ptDesc"];
        $ptCat  = $row["ptCat"];

        $returnArrData[] = array(
            'ptID'      => $ptID,
            'ptDesc'    => $ptDesc,
            'ptCat'     => $ptCat
        );

    }

    return(resultJSON("success", "", $returnArrData));

}

function postChangePasswd($conn, $username, $currPassword, $newPassword, $reNewPassword){
    global $DEF_STATUS_ACTIVE;

    if ($currPassword != "" && ($newPassword == $reNewPassword)){ //Change Password
        //checking previous password can't same with new password
        $sql    = "SELECT mbrUsername, passID, passWord FROM dtMember inner join trPassword";
        $sql    .= " WHERE mbrUsername = passMbrUsername";
        $sql    .= " and mbrUsername='" . $username . "' and passWord='" . md5($newPassword) . "'";
        $query = $conn->query($sql);
        if ($query->num_rows > 0){
            return(resultJSON("error", "Change Password Failed. Password has been used. Use a password that has never been used", ""));
            die();
        }else{
            $sql    = "SELECT mbrUsername, passID, passWord, mbrStID FROM dtMember inner join trPassword";
            $sql    .= " WHERE mbrUsername = passMbrUsername";
            $sql    .= " and mbrUsername='" . $username . "'";
            $sql    .= " order by passID desc limit 1";
            
            $query = $conn->query($sql);
            if ($query->num_rows > 0){
                if ($row = $query->fetch_assoc()){
                    if ($row["mbrStID"] == $DEF_STATUS_ACTIVE){
                        if ($row["passWord"] == md5($currPassword)){                    
                            $arrData = array(
                                0 => array ("db" => "passMbrUsername"   , "val" => $username),
                                1 => array ("db" => "passWord"          , "val" => md5($newPassword)),
                                2 => array ("db" => "passDate"          , "val" => "CURRENT_TIME()")
                            );
                            if (fInsert("trPassword", $arrData, $conn)){
                                //send email for activation
                                fSendNotifToEmail("CHANGE_PASSWORD", $username);
                                
                                //fCloseConnection($conn);
                                //$conn->close();
        
                                //redirect to success page
                                //header("Location: changePassword.php?q=password-success");
                                return(resultJSON("success", "Change Password Successfull", ""));
                                die();
                                
                            }else{
                                //insert fail   
                                //back for re-register
                                return(resultJSON("error", "Change password failed. Contact support for help", ""));
                                die();
                            }
                            
                        }else{
                            return(resultJSON("error", "Change password failed. Incorrect current password", ""));
                            die();
                        }
                    }else{
                        return(resultJSON("error", "Change password failed. Your membership not active any more", ""));
                        die();
                    }
                }
            }else {
                return(resultJSON("error", "Change password failed. username not found", ""));
                die();
            }
        }
    }
}

function postChangeSecurity($conn, $username, $currSecurity, $newSecurity, $reNewSecurity){
    global $DEF_STATUS_APPROVED, $DEF_STATUS_BLOCKED;
    //checking previous security passwd, can't same with new security passwd
    $sql    = "SELECT mbrUsername, pinID, pinWord FROM dtMember inner join trPIN";
    $sql    .= " ON mbrUsername = pinMbrUsername";
    $sql    .= " WHERE mbrUsername='" . $username . "' AND pinWord = '".md5($newSecurity)."' ";
    $query = $conn->query($sql);

    if ($query->num_rows > 0){
        return(resultJSON("error", "Change Security Password Failed. Security Password has been used. Use a security password that has never been used.", ""));
    }else{
        $sql    = "SELECT mbrUsername, pinID, pinWord FROM dtMember inner join trPIN";
        $sql    .= " ON mbrUsername = pinMbrUsername";
        $sql    .= " WHERE mbrUsername='" . $username . "' AND pinStID='". $DEF_STATUS_APPROVED ."'";
        $sql    .= " order by DATE(pinDate) desc limit 1";
        $query = $conn->query($sql);
        if ($query->num_rows > 0){
            if ($row = $query->fetch_assoc()){
                if ($row["pinWord"] == md5($currSecurity)){     
                    $conn->autocommit(false);
                    //Block previous pin
                    $pinID = $row['pinID'];
                    $sql = "UPDATE trPIN SET pinStID='" . $DEF_STATUS_BLOCKED . "' WHERE pinID='" . $pinID . "'";       
                    $query = $conn->query($sql);
                    
                    //Insert New PIN
                    $pinID = strtotime("now");
                    $arrData = array(
                        0 => array ("db" => "pinID"             , "val" => $pinID),
                        1 => array ("db" => "pinMbrUsername"    , "val" => $username),
                        2 => array ("db" => "pinWord"           , "val" => md5($newSecurity)),
                        3 => array ("db" => "pinDate"           , "val" => "CURRENT_TIME()"),
                        4 => array ("db" => "pinStID"           , "val" => $DEF_STATUS_APPROVED)
                    );
                    if (fInsert("trPIN", $arrData, $conn)){
                        $conn->commit();
                        
                        //send email for activation
                        fSendNotifToEmail("CHANGE_SECURITY", $username);
                        
                        //fCloseConnection($conn);
                        //$conn->close();

                        //redirect to success page
                        //header("Location: changePassword.php?q=security-success");
                        return(resultJSON("success", "Change Security Password Successfull", ""));
                        die();
                        
                    }else{
                        $conn->rollback();
                        //insert fail   
                        //back for re-register
                        //$changeSecurityMessage = "<b>Change Security Password Failed</b><br>Contact Support for help";
                        return(resultJSON("error", "Change Security Password Failed Contact Support for help", ""));
                    }
                }else{
                    return(resultJSON("error", "Change Security Password Failed. Your current security password not match", ""));
                }
            }
        }else{
            //$changeSecurityMessage = "<b>Change Security Password Failed</b><br>username not found, <br>Contact support for help";
            return(resultJSON("error", "Change Security Password Failed. username not found, Contact support for help", ""));
        }  
    }   
}


function postResetSecurity($conn, $username, $emailReset){
    global $DEF_STATUS_BLOCKED, $DEF_STATUS_APPROVED, $DEF_STATUS_PENDING;

    if ($emailReset != ""){
        //Check your email
        $sql = "SELECT mbrEmail FROM dtMember WHERE mbrUsername='$username'";
            //echo $sql; die();
        $query = $conn->query($sql);
        if ($row = $query->fetch_assoc()){
            if (strtolower($row['mbrEmail']) == $emailReset){

                $conn->autocommit(false);

                    //1. Update Status Previous Security password to BLOCKED
                    //Update table trPIN
                $arrData = array("pinStID" => $DEF_STATUS_BLOCKED);
                $arrDataQuery = array("pinMbrUsername" => $username, "pinStID" => $DEF_STATUS_APPROVED);
                if (!fUpdateRecord("trPIN", $arrData, $arrDataQuery, $conn)){
                    //echo (fSendStatusMessage("error", $conn->error));
                    $conn->rollback();  
                    return(resultJSON("error", "Error #1".$conn->error, ""));
                    die();
                }
                unset($arrData);
                unset($arrDataQuery);

                    //NB: 2nd phase, has same code with request security password
                    //2. Generate new pin, save with status pending 
                $newPinWord = str_shuffle (strtotime("now"));
                    //Insert New PIN
                $pinID = strtotime("now");
                $arrData = array(
                        0 => array ("db" => "pinID"             , "val" => $pinID),
                        1 => array ("db" => "pinMbrUsername"    , "val" => $username),
                        2 => array ("db" => "pinWord"           , "val" => $newPinWord), //don't encrypt first, because not yet activated.
                        3 => array ("db" => "pinDate"           , "val" => "CURRENT_TIME()"),
                        4 => array ("db" => "pinStID"           , "val" => $DEF_STATUS_PENDING)
                );

                if (fInsert("trPIN", $arrData, $conn)){
                    $conn->commit();
                        //send email for activation
                    if (fSendNotifToEmail("REQUEST_SECURITY_PIN", $username)){
                            //success
                        return(resultJSON("success", "Request reset security successfull. Check email for activation", ""));
                    }else{
                        return(resultJSON("error", "Error Sending Email", ""));
                    }

                        //NB:
                        //2. send link to client to activate pin (expired in 24 hours)
                        //2.1 activate pin by updating status to approved

                }else{
                        //insert fail   
                    $conn->rollback();  
                    return(resultJSON("error", "Reset Security Password Failed", ""));

                        //send notif to admin
                        //if (fSendNotifToEmail("CHANGE SECURITY-FAILED", "")){ //success 
                        //}
                }
                    //end of 2nd phase
            }else{
                return(resultJSON("error", "Reset Security Password failed. Invalid Email Address", ""));
            }
        }else{
           return(resultJSON("error", "Reset security password failed. Invalid username, please relogin", ""));
        }
    }else{
        return(resultJSON("error", "Reset security password failed. Incomplete data", ""));
    }
}

function postBugReport($conn, $username, $bugOS, $bugDevice, $bugMenu, $bugDesc){
    global $DEF_STATUS_UNREAD;

    $arrData = array(
        0 => array("db" => "bugID"      ,   "val" => strtotime("now")),
        1 => array("db" => "mbrUsername",   "val" => $username),
        2 => array("db" => "bugOS"      ,   "val" => $bugOS),
        3 => array("db" => "bugDevice"  ,   "val" => $bugDevice),
        4 => array("db" => "bugMenu"    ,   "val" => $bugMenu),
        5 => array("db" => "bugDesc"    ,   "val" => $bugDesc),
        6 => array("db" => "bugStatus"  ,   "val" => $DEF_STATUS_UNREAD)
    );

    $table = "dtBugReport";

    if (fInsert($table, $arrData, $conn)) {
        return(resultJSON("success", "Bug Report Successfull", ""));
    }else{
        return(resultJSON("error", "Bug Report Failed", ""));
    }
}

function postLogin($conn, $username, $password, $platform){
    global $DEF_STATUS_ACTIVE, $CURRENT_TIME;
    $mbrUsername = $mbrFirstName = $mbrLastName = '';

    //password encrypt and status == ACTIVE
    $sql    = "select mbrUsername, mbrFirstName, mbrLastName, passWord from dtMember inner join trPassword ";
    $sql    .= " on mbrUsername = passMbrUsername";
    $sql    .= " WHERE mbrUsername='".$username. "' and mbrStID = '" . $DEF_STATUS_ACTIVE . "'";
    $sql    .= " order by passDate Desc limit 1";


    // echo $sql;
    // die();

    $returnArrData = array(
        'mbrUsername'   => '',
        'mbrFirstName'  => '',
        'mbrLastName'   => '',
        'renewOnly'     => 'false'
    );

            
    $query = $conn->query($sql);
    if ($query->num_rows > 0){
        $row = $query->fetch_assoc();
        if ( (strtolower($row['mbrUsername']) ==$username && $row['passWord'] == md5($password) )){
            
            $ExpiredDate = "";
            if (fCekStatusUsage($conn, $row['mbrUsername'], $ExpiredDate) == "active"){

                $arrDataInsert = array(
                    0 => array("db" => "hlUsername"     , "val"     => $username),
                    1 => array("db" => "hlDate"         , "val"     => $CURRENT_TIME),
                    2 => array("db" => "hlPlatform"     , "val"     => $platform)
                );

                $table = 'dtHistoryLogin';
                if (fInsert($table, $arrDataInsert, $conn)) {

                    $returnArrData = array(
                        'mbrUsername'   => $row['mbrUsername'],
                        'mbrFirstName'  => $row['mbrFirstName'],
                        'mbrLastName'   => $row['mbrLastName'],
                        'renewOnly'     => 'false'
                    );

                    return(resultJSON("success", "", $returnArrData));
                }else{
                    return(resultJSON("error", "Something wrong", $returnArrData));
                }
                die();
            }else{
                $TglToleransi = strtotime("$ExpiredDate +7 Days");
                $TglToleransi = date("Y-m-d", $TglToleransi);
                $currDate = strtotime($CURRENT_TIME);
                $currDate = date("Y-m-d", $currDate);
                if ($TglToleransi < $currDate){ // cek expired date (7 hari)
                    return(resultJSON("error", "Your membership has expired", $returnArrData));
                    die();
                }else{
                    $returnArrData = array(
                        'mbrUsername'   => $row['mbrUsername'],
                        'mbrFirstName'  => $row['mbrFirstName'],
                        'mbrLastName'   => $row['mbrLastName'],
                        'renewOnly'     => 'true'
                    );
                    return(resultJSON("success", "Your membership has expired. Please do renewal before $TglToleransi", $returnArrData));
                    die();
                }   
            }
        } else {
            return(resultJSON("error", "Username and password not valid", $returnArrData));
            die();
        }
    }else{
        //Maybe there is no such username or Status account not allowed /block/declined
        return(resultJSON("error", "Username not found or unauthorized  ", $returnArrData));
        die();
    }
}


function getCarousel($conn){
    global $DEF_STATUS_ACTIVE;

    $returnArrData = array();
    $sql = "SELECT * FROM dtImage WHERE imgCat = '1' AND imgStatus = '". $DEF_STATUS_ACTIVE ."' "; //1 category mobile

    $query = $conn->query($sql);
    if ($query->num_rows > 0) {
        
        while ($row = $query->fetch_assoc()) {
            
            $imgName = $row['imgName'];
            $returnArrData[] = array(
                "imgName"   => $imgName
            );

        }
        return(resultJSON("success", "", $returnArrData));
    }
}


function CheckAppVersion($conn, $appVerDesc){

    $returnArrData = array();
    $sql = "SELECT * FROM dtAppVersion WHERE AppVerDesc = '".$appVerDesc."' ORDER BY AppVerDesc DESC LIMIT 1";
    $query = $conn->query($sql);
    if ($row = $query->fetch_assoc()) {

        $AppVerStatus   = $row['AppVerStatus'];
        $returnArrData = array(
            "appVerStatus" => $AppVerStatus
        );

        return(resultJSON("success", "New update available.", $returnArrData));  
    }else{
        $returnArrData = array(
            "appVerStatus" => ''
        );

        return(resultJSON("error", "Something wrong..", $returnArrData)); 
    }
}


function checkStatusMember($conn, $username){
    //$ExpiredDate = "";
    if (fCekStatusUsage($conn, $username, $ExpiredDate) == "active"){
        return(resultJSON("success", "Member active", ""));
        die();

    }else{
        return(resultJSON("error", "Your membership has expired", ""));
        die();
    }
}

function checkDataMemberForTF($conn, $username){
    global $DEF_STATUS_ACTIVE;
    $returnArrData = array();
    $sql = "SELECT mbrUsername, mbrFirstName FROM dtMember WHERE mbrUsername='" . $username . "' AND mbrStID='" . $DEF_STATUS_ACTIVE . "'";
    //echo $sql; die();
    $query = $conn->query($sql);
    //--- do update on 14 juli 2018 ---
    //if ($query->num_rows > 0){
    //  echo "exist";
    if ($row = $query->fetch_assoc()){

        $mbrFirstName   = $row['mbrFirstName'];
        
        $returnArrData = array(
            'mbrFirstName'   => $mbrFirstName
        );

        return(resultJSON("success", "", $returnArrData));
    }else{
        $returnArrData = array(
            'mbrFirstName'   => ''
        );

        return(resultJSON("error",  "Username not found or member expired", $returnArrData));
    }
}


function postTransferVoucher($conn, $username, $transferTo, $amountVoucher, $numberOfVoucher, $voucherDesc, $secPasswd){
    global $DEF_VOUCHER_TYPE_STD, $DEF_TRANSFER_VOUCHER, $DEF_STATUS_APPROVED, $DEF_STATUS_ACTIVE, $DEF_STATUS_USED, $DEF_VOUCHER_USED_FOR_TRANSFER, $DEF_VOUCHER_PRICE_IDR, $CURRENT_TIME;

      //Validation inputs
    if ($username != "" && $transferTo != "" && $amountVoucher != "" && $amountVoucher > 0 && $secPasswd != "" && $numberOfVoucher > 0){
        $sqlComp = "SELECT mbrSponsor FROM dtMember WHERE mbrUsername ='$username' AND mbrSponsor LIKE 'VISIONEA%'";
        $query = $conn->query($sqlComp);
        $isFailedUsername = false;
        if ($row = $query->fetch_assoc()){
            //Pengirim adalah org Perusahaan(Vision)
        }else{
            //pengirim BUKAN org Perusahaan
            $sqlComp = "SELECT mbrSponsor FROM dtMember WHERE mbrUsername ='$transferTo' AND mbrSponsor LIKE 'VISIONEA%'";
            $query = $conn->query($sqlComp);
            if ($row = $query->fetch_assoc()){
                //PENERIMA ORG PERUSAHAAN
                //$responseMessage = "Wrong Username..";
                $isFailedUsername = true;
            }else{
                    //Ini yg boleh terima transferan voucher
                    //PENERIMA BUKAN ORG PERUSAHAAN
                $isFailedUsername = false;
            }
        }
        if ($isFailedUsername == false){
            if ($username != $transferTo){
                //cek balance voucher
                $myObjData = json_decode(fGetNumberOfVoucher($DEF_VOUCHER_TYPE_STD, $username, $conn));
                if ($myObjData->{'status'} == "success"){
                    $balanceVoucher = $myObjData->{'voucherBalance'}; //$myObjData->{'voucherAct'};
                }else{
                    $balanceVoucher = 0;
                }

                if ($balanceVoucher >= $amountVoucher){
                    //Check Security Password
                    //$s = fCheckSecurityPassword($username, $secPasswd, $conn);
                    //if (true){
                    //echo (fCheckSecurityPassword($username, $secPasswd, $conn)); die();
                    if (!fCheckSecurityPassword($username, $secPasswd, $conn)){
                    // $responseMessage .= "Security Password not match<br>";
                    return(resultJSON("error", "Security Password not match", ""));

                    }else{
                    
                    $conn->autocommit(false);
                    $isFailed = false;

                    //Insert into dtFundIn
                    $timeStamp  = strtotime("now");
                    $finID    = $transferTo.$timeStamp;  //same format used in Buy Voucher (reqBuyVoucher.php)
                    $amount   = $amountVoucher; //* $DEF_VOUCHER_PRICE;
                    $curr     = "IDR";
                    $curs     = "1";
                    $accName  = "";
                    $accType  = $DEF_TRANSFER_VOUCHER;
                    $voucherType = $DEF_VOUCHER_TYPE_STD;
                    $fromAccNo = $username; //$_SESSION["sUserName"];
                    $toAccNo  = $transferTo; 
                    $status   = $DEF_STATUS_APPROVED; //APPROVED BY SYSTEM
                    $approvedBy = "SYSTEM";
                    $IDTrans  = "SYS-".$timeStamp;
                    

                    $arrData = array(
                        0 => array ("db" => "finID"       , "val" => $finID),
                        1 => array ("db" => "finMbrUsername"  , "val" => $transferTo),
                        2 => array ("db" => "finAmount"     , "val" => $amount),
                        3 => array ("db" => "finCurr"     , "val" => $curr),
                        4 => array ("db" => "finCurs"     , "val" => $curs),
                        5 => array ("db" => "finAccName"    , "val" => $accName),
                        6 => array ("db" => "finAccType"    , "val" => $accType),
                        7 => array ("db" => "finVoucherType"    , "val" => $voucherType),
                        8 => array ("db" => "finFromAccNo"    , "val" => $fromAccNo),
                        9 => array ("db" => "finToAccNo"    , "val" => $toAccNo),
                        10 => array ("db" => "finTransactionID"  , "val" => $IDTrans),
                        11 => array ("db" => "finDate"     , "val" => "CURRENT_TIME()"),
                        12 => array ("db" => "finStatus"     , "val" => $status),
                        13 => array ("db" => "finApprovedBy"   , "val" => $approvedBy),
                        14 => array ("db" => "finDesc"         , "val" => $voucherDesc)
                    );
                            
                    $table  = "dtFundIn"; 
                    if (fInsert($table, $arrData, $conn)){
                        //insert success

                        //ReChecking number of existing voucher
                        $existingVoucher = 0;
                        $sql = "SELECT COUNT(*) as existingVoucher FROM dtFundInVoucher INNER JOIN dtFundIn ON finID=fivFinID INNER JOIN dtVoucher ON vCode=fivVCode ";
                        $sql .= " WHERE finMbrUsername = '".$username."' AND fivStatus='" . $DEF_STATUS_ACTIVE . "'";
                        $sql .= " AND finVoucherType='" . $voucherType ."'";
                        $query = $conn->query($sql);
                        if ($row = $query->fetch_assoc()){
                            $existingVoucher = $row['existingVoucher'];
                        }else{
                            //no record
                            $conn->rollback();  
                            $isFailed = true;
                        }

                        if ($existingVoucher >= $numberOfVoucher && $numberOfVoucher > 0){
                            //insert voucher to
                            $sql = "INSERT INTO dtFundInVoucher (fivFinID, fivVCode, fivDate, fivStatus, fivType, fivUsedFor, fivUserOn) ";
                            $sql .= " SELECT '".$finID."', fivVCode, '".$CURRENT_TIME."', fivStatus, fivType, fivUsedFor, fivUserOn ";
                            $sql .= " FROM dtFundInVoucher INNER JOIN dtFundIn ON finID=fivFinID INNER JOIN dtVoucher ON vCode=fivVCode ";
                            $sql .= " WHERE finMbrUsername = '".$username."' AND fivStatus='" . $DEF_STATUS_ACTIVE . "'";
                            $sql .= " AND finVoucherType='". $voucherType . "'";
                            $sql .= " ORDER BY finDate ASC, fivVCode ASC  ";
                            $sql .= " LIMIT " . $numberOfVoucher;
                                //echo $sql;
                            if ($query = $conn->query($sql)){
                                //success insert into voucher to
                                //update voucher from

                                $sql = " SELECT fivFinID, fivVCode, fivDate, fivStatus, fivType, fivUsedFor, fivUserOn ";
                                $sql .= " FROM dtFundInVoucher INNER JOIN dtFundIn ON finID=fivFinID INNER JOIN dtVoucher ON vCode=fivVCode ";
                                $sql .= " WHERE finMbrUsername = '".$username."' AND fivStatus='" . $DEF_STATUS_ACTIVE . "'";
                                $sql .= " AND finVoucherType='". $voucherType . "'";
                                $sql .= " ORDER BY finDate ASC, fivVCode ASC  ";
                                $sql .= " LIMIT " . $numberOfVoucher;
                                //echo ("<br><br>" . $sql);
                                $query = $conn->query($sql);
                                $i = 0;
                                while ($row=$query->fetch_assoc()) {
                                    if ($i < $numberOfVoucher){
                                        $sqlUpdate = "UPDATE dtFundInVoucher SET  fivStatus='". $DEF_STATUS_USED . "', ";
                                        $sqlUpdate .= " fivUsedFor='". $DEF_VOUCHER_USED_FOR_TRANSFER . "', fivUserOn='". $transferTo . "'";
                                        $sqlUpdate .= " WHERE fivFinID='" . $row['fivFinID'] . "' AND fivVCode='" . $row['fivVCode'] . "' AND fivStatus='" . $DEF_STATUS_ACTIVE . "'"; 
                                        if ($queryUpdate = $conn->query($sqlUpdate)){
                                            $i++;
                                        }else{
                                            $conn->rollback();  
                                            $isFailed = true;
                                            return(resultJSON("error", "Updating USED PIN Failed", ""));
                                        }
                                    }
                                } //end while
                                
                            } //end if       
                            else {
                                // $responseMessage = "Insert Voucher failed<br>";                
                                $conn->rollback();
                                $isFailed = true;
                                return(resultJSON("error", "Insert PIN Failed", ""));
                            }       
                        }else{
                        //voucher not enough
                        // $responseMessage = "ReChecking: Balance is not enough<br>";
                            $conn->rollback();
                            $isFailed = true;
                            return(resultJSON("error", "ReChecking: Balance is not enough", ""));
                        }

                        if ($isFailed==false){
                            $conn->commit();
                            //$conn->close();
                            return(resultJSON("success", "Transfer PIN Successfull", ""));
                            die();
                        }

                    }else{
                        //echo "Could not process your information " . mysql_error();
                        //die();
                        //insert fail
                        //back for re-deposit
                        //$responseMessage = "Submit Transfer Voucher Failed"; 
                        $conn->rollback();
                        return(resultJSON("error", "Submit Transfer PIN Failed", ""));

                    } // end else
                    }
                }else{
                    //$responseMessage .= "Your balance is not enough<br>";
                    return(resultJSON("error", "Your Balance is not enough", "")); 
                }
            }else{
            //$responseMessage .= "Can not transfer to your self<br>";  
                return(resultJSON("error", "Can not Transfer to your self", ""));
            }
        }else{
            return(resultJSON("error", "Wrong username..", ""));
            die();
        }
    }else{
        //Data not complite
        //$responseMessage .= "Incomplete data<br>";  
        return(resultJSON("error", "Incomplete Data", ""));
    }
}

function postHistoryLogin($conn, $username, $platform){
    global $CURRENT_TIME;

    $arrData = array(

        0 => array("db" => "hlUsername"     , "val"     => $username),
        1 => array("db" => "hlDate"         , "val"     => $CURRENT_TIME),
        2 => array("db" => "hlPlatform"     , "val"     => $platform)
    );

    $table = 'dtHistoryLogin';
    if (fInsert($table, $arrData, $conn)) {
        return(resultJSON("success", "", ""));
    }else{
        return(resultJSON("error", "Something wrong", ""));
    }
}


function fSendToAdminApps($username, $issue, $onFile, $desc){
    global $conn;
    $issue  = fValidateSQLFromInput($conn, $issue);
    $onFile = fValidateSQLFromInput($conn, $onFile);
    $desc   = fValidateSQLFromInput($conn, $desc);
    //$sUsername = isset($_SESSION['sUserName'])?$_SESSION['sUserName']:'no-session';
    $sUsername = $username;
    $platform  = "ANDROID";
    $arrData = array(
                0 => array ("db" => "logUsername"   , "val" => $sUsername),
                1 => array ("db" => "logIssue"      , "val" => $issue),
                2 => array ("db" => "logOnFile"     , "val" => $onFile),
                3 => array ("db" => "logDesc"       , "val" => $desc),
                4 => array ("db" => "logPlatform"   , "val" => $platform),
                5 => array ("db" => "logDate"       , "val" => "CURRENT_TIME()")
            );
    return (fInsert("dtLog", $arrData, $conn));

}
?>
