<?php
include_once("../includes/inc_def.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

$loadPage = "dashboard.php?subNav=carousel";//headerAnimation / carousel
$showMenu = "";
$MNav	= (isset($_GET['MNav']))?$_GET['MNav']:"";
$subNav	= (isset($_GET['subNav']))?$_GET['subNav']:"";
$me	= (isset($_GET['me']))?$_GET['me']:"";
$remember = (isset($_GET['remember']))?$_GET['remember']:"";
$contentPage = "";
if ($MNav == "auth"){
	$contentPage = "login.php";	
}else if ($MNav == "lo"){
	$contentPage = "logout.php";
}else if ($MNav == "dboard"){
	$loadPage = "dashboard.php?subNav=carousel";	//headerAnimation / carousel
}else if ($MNav == "tradingAcc"){$loadPage = "accTradingFX.php?x="; // dimatikan karena pindah di mymac	
}else if ($MNav == "rpt"){
	$loadPage = "rptMbr.php?subNav=".$subNav;	
}else if ($MNav == "wd"){
	$loadPage = "reqWD.php?x=";	
}else if ($MNav == "buyVoucher"){
	$loadPage = "reqBuyVoucher.php?x=";	
}else if ($MNav == "trans"){
	$loadPage = "doTransfer.php?x=";
}else if ($MNav == "convert"){
  $loadPage = "doConvert.php?x=";

}else if ($MNav == "buyVoucherVPS"){
  $loadPage = "reqBuyVoucherVPS.php?x="; 
}else if ($MNav == "transVPS"){
  $loadPage = "doTransferVPS.php?x=";
}else if ($MNav == "convertVPS"){
  $loadPage = "doConvertVPS.php?x=";


}else if ($MNav == "his"){
	$loadPage = "historyComm.php?x=";
}else if ($MNav == "register"){
	$loadPage = "register.php?x=";	
}else if ($MNav == "terms"){
	$loadPage = "terms_conds_VEA.php?x=";
}elseif($MNav == "gt"){
	$loadPage = "networkTree.php?subNav=".$subNav;
}elseif($MNav == "announcement"){
  $loadPage = "publishInfo.php?x=";
}elseif($MNav == "achievement"){
  $loadPage = "achievement.php?x=";
}elseif($MNav == "trProduct"){
  $loadPage = "trProduct.php?subNav=".$subNav;
}elseif($MNav == "readEbook"){
  $loadPage = "readE-book.php?subNav=".$subNav;
}

$loadPage .= "&unxid=".md5(time());
//echo (">>>". $loadPage . $MNav ); die();

if ($contentPage != ""){
	//include($contentPage);
  //echo("Location: ".$COMPANY_SITE."member/".$contentPage."?unxid=".md5(time())); die();
	header("Location: ".$COMPANY_SITE."member/".$contentPage."?unxid=".md5(time()));
  die();
}
if ($me != ""){
  // versi lama
  /*
	$loadPage = "regLink.php?u=".$me;
	$loadPage .= "&unxid=".md5(time());
	$sUserName = 'Your Sponsor is ' . $me;
  */
   
  $loadPage = "newRegLink.php?u=".$me;
  $loadPage .= "&unxid=".md5(time());
  $sUserName = 'Your Sponsor is ' . $me;
}else if ($MNav == "renewPac"){
  include_once("../includes/inc_session.php");
  $sUserName = $_SESSION['sUserName'];
  $sPrivilege = $_SESSION['sPrivilege'];
  $loadPage = "profile.php?q=".$MNav;
  
}else if ($MNav == "terms"){
  //supaya tidak menggunakan session
  $sUserName = "";
}else{
	include_once("../includes/inc_session.php");
	$sUserName = $_SESSION['sUserName'];
  if ($remember != ""){
    fSetCookiesLogin($sUserName);
}
}

$title = $COMPANY_NAME . " - ". $sUserName;

$sLoaded = isset($_SESSION['sLoaded'])?$_SESSION['sLoaded']: '';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

    <!-- Favicons -->
    <link rel="icon" href="../images/favicon.png" sizes="16x16 32x32" type="image/png">

    <title><?PHP echo ($title); ?></title>
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
    <link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">


    <!-- Documentation extras -->

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/css/newBinary.css">

    <!-- iframe removal -->
    <script type="text/javascript">
        if (document.readyState === 'complete') {
          if (window.location != window.parent.location) {
            const elements = document.getElementsByClassName("iframe-extern");
            while (elemnts.lenght > 0) elements[0].remove();
          // $(".iframe-extern").remove();
          }
        };
        function resizeIframe(obj) {
          var scrollHeight = $(document).height();//obj.contentWindow.document.documentElement.scrollHeight;
          var iniiframe = obj.contentWindow.document.documentElement.scrollHeight;
          var frameHeight  = scrollHeight  - 200;
          obj.style.height = frameHeight + 'px';
          // $(document).height(frameHeight);
        }
    </script>


<!-- google translate -->

<!--
<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
}
</script>
-->

<!-- <script>
    function googleTranslateElementInit() {
        new google.translate.TranslateElement(
            {pageLanguage: 'en'},
            'google_translate_element'
        );

        /*
            To remove the "powered by google",
            uncomment one of the following code blocks.
            NB: This breaks Google's Attribution Requirements:
            https://developers.google.com/translate/v2/attribution#attribution-and-logos
        */

        // Native (but only works in browsers that support query selector)
        if(typeof(document.querySelector) == 'function') {
            document.querySelector('.goog-logo-link').setAttribute('style', 'display: none');
            document.querySelector('.goog-te-gadget').setAttribute('style', 'font-size: 0');
        }

        // If you have jQuery - works cross-browser - uncomment this
        jQuery('.goog-logo-link').css('display', 'none');
        jQuery('.goog-te-gadget').css('font-size', '0');
    }
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> -->

<!--
<script type="text/javascript">
    function googleTranslateElementInit() {
        $.when(
            new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: "id",
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element')
        ).done(function(){
            //var select = document.getElementsByClassName('google')[0];
            var select = document.getElementsByClassName("google_translate_element");
            select.selectedIndex = 1;
            select.addEventListener('click', function () {
                select.dispatchEvent(new Event('change'));
            });
            select.click();
        });
    }
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
-->



<!-- end script google translate -->


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
	$(document).ready(function(e) {
		$url = $subNav = "";
		$("a[class='nav-link']").on('click', function(){
      localStorage.clear(); // remove all local storage when left Navigator on click
			$menu = $(this).attr('id');
			//if ($menu == "dashboard"){
			//	$url = "dashboard.php";
			//}else 
			if ($menu == "myProfile"){
				$url = "profile.php";
			}else if ($menu == "editProfile"){
				$url = "editProfile.php";
			}else if ($menu == "changePassword"){
				$url = "changePassword.php";
			}else if ($menu == "beneficiaryAcc"){
                $url = "beneficiary.php";
            }else if ($menu == "directSponsor"){
				$url = "dsl.php";  //direct sponsor list
			}else if ($menu == "networkTree"){
				$url = "networkTree.php";
			//}else if ($menu == "binaryCommission"){
				//$url = "binaryCommission.php";
			}else if ($menu == "report"){
				$url = "rptMbr.php"; $subNav = "sp";
      }else if ($menu == "Renew"){
        $url = "renewPac.php";
			/*}else if ($menu == "passedUp"){
				$url = "rptMbr.php"; $subNav = "pu";
			}else if ($menu == "pairing"){
				$url = "rptMbr.php"; $subNav = "pair";
			}else if ($menu == "megaMatching"){
				$url = "rptMbr.php"; $subNav = "mm";
				*/
			}
			
			if ($url != "" && $("#loadPage").attr("src") != $url){
				$url += "?type=iframe&subNav=" + $subNav;
				//$url += "&ssid=".md5(time());

				$("#loadPage").attr("src", $url);
			}
		});
	});	//$(document)

</script>
</head>
<body class="" onload="<?php if ($sLoaded != "loaded") { echo "demo.showNotification('top','right', 'info', '<span style=text-align:center;>Welcome <b>". $sUserName ."</b></span>');";  $_SESSION['sLoaded'] = "loaded"; } ?>">
    <div class="wrapper">
        <?php include_once("leftNav.php"); ?>
        <div class="main-panel" >
            <!-- Navbar -->
            <?php include_once("header.php"); ?>
            <!-- End Navbar -->
            <div class="content" style="text-align: center;">
               <iframe id="loadPage" src="<?php echo $loadPage ?>" onload="resizeIframe(this)"></iframe>
               <?php 
                        	//include($contentPage);
               ?>
           </div>
           <?php include_once("footer.php"); ?>
           
       </div>
   </div>

   <?php //include_once("setting.php"); ?>
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

<!--	Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
<script src="../assets/js/plugins/bootstrap-datetimepicker.min.js"></script>

<!--	Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="../assets/js/plugins/nouislider.min.js"></script>

<!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/bootstrap-selectpicker.js"></script>

<!--	Plugin for Tags, full documentation here: http://xoxco.com/projects/code/tagsinput/  -->
<script src="../assets/js/plugins/bootstrap-tagsinput.js"></script>

<!--	Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
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

<!-- Sliders Plugin, full documentation here: https://refreshless.com/nouislider/ -->
<script src="../assets/js/plugins/nouislider.min.js"></script>

<!--  Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="../assets/js/plugins/jquery.select-bootstrap.js"></script>

<!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
<script src="../assets/js/plugins/jquery.datatables.js"></script>

<!-- Sweet Alert 2 plugin, full documentation here: https://limonte.github.io/sweetalert2/ -->
<script src="../assets/js/plugins/sweetalert2.js"></script>

<!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
<script src="../assets/js/plugins/jasny-bootstrap.min.js"></script>

<!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
<script src="../assets/js/plugins/fullcalendar.min.js"></script>

<!-- demo init -->
<script src="../assets/js/plugins/demo.js"></script>


<script type="text/javascript">

	$(document).ready(function(){
       
	  //init wizard
     
	  demo.initMaterialWizard();
     
	  // Javascript method's body can be found in assets/js/demos.js
	  demo.initDashboardPageCharts();
     
	  demo.initCharts();
     
	});

</script>

<script type="text/javascript">
	$(document).ready(function(){
       
     demo.initVectorMap();
 });

</script>


<!-- Sharrre libray -->
<!--
<script src="../assets/assets-for-demo/js/jquery.sharrre.js">
</script>
-->

<!--
<script>

$(document).ready(function(){
    $('#twitter').sharrre({
      share: {
        twitter: true
      },
      enableHover: false,
      enableTracking: false,
      enableCounter: false,
      buttons: { twitter: {via: 'CreativeTim'}},
      click: function(api, options){
        api.simulateClick();
        api.openPopup('twitter');
      },
      template: '<i class="fa fa-twitter"></i> Twitter',
      url: 'http://demos.creative-tim.com/material-kit-pro/presentation.html'
    });

    $('#facebook').sharrre({
      share: {
        facebook: true
      },
      enableHover: false,
      enableTracking: false,
      enableCounter: false,
      click: function(api, options){
        api.simulateClick();
        api.openPopup('facebook');
      },
      template: '<i class="fa fa-facebook-square"></i> Facebook',
      url: 'http://demos.creative-tim.com/material-kit-pro/presentation.html'
    });

    $('#google').sharrre({
      share: {
        googlePlus: true
      },
      enableCounter: false,
      enableHover: false,
      enableTracking: true,
      click: function(api, options){
        api.simulateClick();
        api.openPopup('googlePlus');
      },
      template: '<i class="fa fa-google-plus"></i> Google',
      url: 'http://demos.creative-tim.com/material-kit-pro/presentation.html'
    });
});


var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-46172202-1']);
_gaq.push(['_trackPageview']);

(function() {
    var ga = document.createElement('script');
    ga.type = 'text/javascript';
    ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(ga, s);
})();

// Facebook Pixel Code Don't Delete
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','//connect.facebook.net/en_US/fbevents.js');

try{
	fbq('init', '111649226022273');
	fbq('track', "PageView");

}catch(err) {
	console.log('Facebook Track Error:', err);
}

</script>

<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=111649226022273&ev=PageView&noscript=1"
/></noscript>

-->

<?php fCloseConnection($conn); ?>
</html>
