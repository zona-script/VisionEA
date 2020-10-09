<?php
include_once("../includes/inc_def.php"); //before inc_session
include_once("../includes/inc_session_ebook.php"); //after inc_session
include_once("../includes/inc_conn.php");


$sUserName = $_SESSION['sEBUserName'];
?>
<html>
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description" content="VisionEA is a company that provides a smart platform for forex trading automatically.">
    <meta name="author" content="VisionEA">

    <!-- Favicons -->
    <link rel="icon" href="../../images/favicon.png" sizes="16x16 32x32" type="image/png">

    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
    <link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">


    <!-- Documentation extras -->

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/css/newBinary.css">
</head>
<body>
	<div class="container">
		<div class="card">
	        <div class="card-header card-header-success card-header-icon">
	            <div class="card-icon">
	                <i class="fa fa-book fa-2x"></i>
	            </div>
		        <div class="card-text"><h4 class="card-title">Product</h4></div>
			</div>
			<div class="card-body card-fix">
				<div class="row mr-auto ml-auto">
			    	<?php
			    	$sql  = "SELECT proID, proPrice, trProTransID, trPDTransID, trProUpdateDate, trProStatus";
			    	$sql .= " FROM msProduct";
			    	$sql .= " LEFT JOIN (";
			    	$sql .= " 	SELECT * FROM trProduct";
			    	$sql .= " 	INNER JOIN trProDetail ON trPDTransID = trProTransID";
			    	$sql .= " 	INNER JOIN dtUserEbook ON ebUsername = trProUserBeli";
			    	$sql .= " 	WHERE ebUsername = '".$sUserName."' AND trProStatus = '".$DEF_STATUS_APPROVED."'";
			    	$sql .= " 	GROUP BY trPDProID";
			    	$sql .= " ) AS trpro ON trpro.trPDProID = proID";
			    	// echo $sql;
			    	$result = $conn->query($sql);
			    	if ($result->num_rows>0){
			    		while ($row = $result->fetch_assoc()){
			    			$status = $title = $btnAction = "";
			    			$proID = $row['proID'];
			    			if ($proID == $DEF_EBOOK_BASIC){
			    				$title = "e-Book Basic Edition";
			    				$imgSrc = "../../images/mockup/BASIC_EDITION.jpg";
			    				$book = "basic";
			    			}else if ($proID == $DEF_EBOOK_PRO){
			    				$title = "e-Book Pro Edition";
			    				$imgSrc = "../../images/mockup/PRO_EDITION.jpg";
			    				$book = "pro";
			    			}
			    			$trProStatus = $row['trProStatus'];
			    			if ($trProStatus == $DEF_STATUS_APPROVED){
			    				$status = "(Purchased)";
			    				$btnAction = '<a class="btn btn-warning btn-round" href="./?MNav=read&subNav='.$book.'" target="_parent">Read e-Book</a>';
			    			}else{
			    				$btnAction = '<a class="btn btn-rose btn-round" href="../" target="_parent">Buy e-Book</a>';
			    			}
			    	?>
			        <div class="col-md-3 g-mb-30">
			          	<article class="u-shadow-v18 g-bg-white text-center rounded g-px-20 g-py-40 g-mb-5">
			            	<img class="d-inline-block img-fluid mb-4" src="<?php echo $imgSrc; ?>">
			            	<h4 class="h5 g-color-black g-font-weight-600 g-mb-10">Pemograman Expert Advisor</h4>
			            	<p><?php echo $title; ?> <span class="text-warning"><?php echo $status; ?></span></p>
			            	<span class="d-block g-color-primary g-font-size-16">IDR <?php echo numFormat($row['proPrice'], 0); ?></span>
			            	<?php echo $btnAction; ?>
			          	</article>
			        </div>
			        <?php
			        	}
		        	} 
			        ?>
			        <div class="col-md-6"></div>
			    </div>
			</div>
		</div>			    
	</div>
</body>
</html>
