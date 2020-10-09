<?php
session_start();
include_once("../../includes/inc_def.php");
include_once("../../includes/inc_conn.php");
include_once("../../includes/inc_functions.php");

$sConfirmID = (isset($_SESSION['sConfirmID']))?$_SESSION['sConfirmID']:""; //=username
$sCode = (isset($_SESSION['sCode']))?$_SESSION['sCode']:""; //=transid

$MNav     = (isset($_GET['MNav']))?$_GET['MNav']:"";
$subNav   = (isset($_GET['subNav']))?$_GET['subNav']:"";
$errDesc  = (isset($_GET['errDesc']))?$_GET['errDesc']:"";
$username = (isset($_GET['userid']))?fValidateSQLFromInput($conn, $_GET['userid']):"";
$transid  = (isset($_GET['transid']))?fValidateSQLFromInput($conn, $_GET['transid']):"";


$subject = "";
if ($MNav == "confirm_wd"){
  $subject = "Withdrawal Confirmation";  
  $msg     = "<p>Dear ".$username.",</p>";
  $msg     .= "<p>Untuk memverifikasi proses penarikan Anda, silahkan masukkan kode konfirmasi Anda.</p>";
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $subject ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../../images/favicon.png" sizes="16x16 32x32" type="image/png">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>  


<script>
  $(document).ready(function(){
    $('form[name="formConfirmCode"]').on('submit', function(e){
      $confirmCode = $("#confirmCode").val();
      $.post("jsonConfirmCode.php",
        {
          "confirmCode": $confirmCode,
          "MNav": "confirm_wd"
        },
        function (data, success){
          $myDataObj = JSON.parse(data);
          if ($.trim($myDataObj["status"])=="error" || ($.trim($myDataObj["status"])=="failed")){
            //error
            $errDesc = $.trim($myDataObj["message"]);
            var url = "<?php echo ($COMPANY_SITE . 'member/verify/confirmCode.php?userid='.$_SESSION['sConfirmID'].'&transid='.$_SESSION['sCode'].'&MNav=confirm_wd&subNav=failed&errDesc=');?>"                                               
            url += $errDesc;
          }else if ($.trim($myDataObj["status"])=="success"){
            //success
            var url = "<?php echo ($COMPANY_SITE . 'member/verify/?q='.$_SESSION['sConfirmID'].'&code='.$_SESSION['sCode'].'&MNav=reqWD'.'&subNav=activated');?>";
          }
          $(location).attr('href',url);
        }
      );
      return (false);
    });



  });
</script>

</head>
<body>
  <div class="container col-md-6">
      <img src="<?php echo ($COMPANY_SITE . "assets/img/email/" .$imgSrcHeaderEmail); ?>" width="100%">
      <div class="card">
          <div class="card-header">
              <h3 class="text-center text-primary"><?php echo $subject ?></h3>
              <?php 
              if ($subNav == "failed"){
                echo ('<p style="color:red; text-align:center">'.$errDesc.'</p>');
              }
              ?>
          </div>
          <div class="body col-md-12">
              <div class="row">
                <?php
                if ($sConfirmID == $username && $sCode == $transid && $sConfirmID != "" && $sCode != ""){
                //session Okay
                ?>
                  <form id="formConfirmCode" name="formConfirmCode" action="./" method="post">
                      <div class="col-md-12">
                          <?php echo $msg ?>
                      </div>
                      <div class="col-md-4">
                          Kode Konfirmasi :
                      </div>
                      <div class="col-md-3">
                          <input id="confirmCode" type="text" maxlength="8" class="form-control col-lg-10 col-md-8 col-sm-6" required>
                      </div>
                      <div class="col-md-4">
                          <button id="confirmWD" type="submit" class="btn btn-outline-primary col-md-12">Confirm</button>
                      </div>
                  </form>
                <?php
                }else{
                  //no session
                  echo "<div class='col-md-12'><p><b>Konfirmasi Gagal.</b><br><p>Sesi telah berakhir.</p><p>Silahkan periksa kembali email Anda dan klik Konfirmasi Penarikan Dana.</p></div>";
                }
                ?>
              </div>
              
              <p>&nbsp;</p>
              <p>&nbsp;</p>
              <p>&nbsp;</p>
              <p>&nbsp;</p>
              <p>&nbsp;</p>
              <p>&nbsp;</p>
          </div>
          <div class="card-footer">
              <div class="col-md-12">
                  <a href="<?php echo $DEF_LINK_FB ?>"><i class=" fa fa-facebook-official fa-2x"></i></a>
                  <a href="<?php echo $DEF_LINK_IG ?>"><i class="fa fa fa-instagram fa-2x"></i></a>
              </div>
              <div class="col-md-12" style="font-size: x-small;"><a href="https://www.visionea.net">https://www.visionea.net</a>
              </div>
          </div>
      </div>
  </div>
</body>
</html> 