<?PHP
include_once("../includes/inc_def.php");
include_once("../includes/inc_session.php");
include_once("../includes/inc_conn.php");
include_once("../includes/inc_functions.php");

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Direct Sponsor List</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
<link rel="stylesheet" href="../assets/css/material-dashboard.min.css?v=2.0.0">
<link rel="stylesheet" href="../assets/css/newBinary.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link href="../assets/assets-for-demo/demo.css" rel="stylesheet"/>

<script>
	/*
	//better call directly from element to able to repeatly generate the script
	$(document).ready(function(e) {
        $('a[href="#"]').on('click', function(){
			var username = $(this).attr('name');
			var gen = $(this).attr('title');
			$.get('getData.php?q=getDirectDownline&sponsor=' + username + '&gen=' + gen, function(data, success){
				$('#dsl'+username).html(data);
			});
		});
    });
	*/
</script>

</head>
<body>
<span id="q"></span>
<div class="card">
    <div class="card-header card-header-success card-header-icon">
        <div class="card-icon">
          <i class="fa fa-list-ul fa-2x" aria-hidden="true"></i>
        </div>
		 <div class="card-text">
           <h4 class="card-title">Direct Sponsor List</h4>
        </div>
    </div>
    <div class="card-body card-fix">
        <div class="container">
            <div class="container-fluid">
                <div class="card" >
                	<div class="card-body">
                    	<table>
                        	<tr>
                                <td><i class="fa fa-user fa-2x"></i></td>
                                <td><?php echo $_SESSION['sUserName'] ?></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><?php
                                    $sponsor = $_SESSION['sUserName'];
                                    $gen = 1;
                                    fDirectDownline($conn, $sponsor, $gen);
                                ?>
                                </td>
                            </tr>
                        </table>
                    </div><!-- end card- Body -->
            	</div><!-- end card -->
            </div> <!-- end container-fluid -->
	 	</div> <!-- end container -->
     </div> <!-- card-body>
</div><!-- end card -->
        
</body>
<?php fCloseConnection($conn); ?>
</html>