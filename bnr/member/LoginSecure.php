<?php
session_start();  // Start the session where the code will be stored. securimage need session_start();
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login</title>
</head>

<body>

<?php
if (empty($_POST)) { 
?>	
    <form method="post" action="">
        <div>
             <label for="in_username">Username</label>
             <input type="text" name="username" id="in_username" value="<?php echo htmlentities($_POST['username']); ?>">
        </div>
        <div>
             <label for="in_passwd">Password</label>
             <input type="password" name="passwd" id="in_passwd" value="<?php echo htmlentities($_POST['passwd']); ?>">
        </div>
        <div>
        	<img id="siimage" align="left" style="padding-right: 5px; border: 0" src="../secureImage/securimageGetURL.php?sid=<?php echo md5(time()); ?>" /> <!-- pass a session id to the query string of the script to prevent ie caching -->
            
            <a tabindex="-1" style="border-style: none" href="#" title="Refresh Image" onclick="document.getElementById('siimage').src = '../secureImage/securimageGetURL.php?sid=' + Math.random(); return false"><img src="../secureImage/images/refresh.gif" alt="Reload Image" border="0" onclick="this.blur()" align="bottom" /></a>
            
        	<label for="in_code">Secure Code</label>
            <input type="text" name="code" id="in_code">
        </div>
        <div>
            <input type="submit" value="Send">
        </div>
    </form>
    
    
    <br>
    <hr />
    <form action="validate.php" method="post">
        Enter Image Text
        <input name="captcha" type="text">
        <img src="../captcha.php" /><br>
        <input name="submit" type="submit" value="Submit">
    </form>
    	
        <!-- response captcha -->
		<?php
			session_start();
			if(isset($_POST["captcha"])&&$_POST["captcha"]!=""&&$_SESSION["code"]==$_POST["captcha"])
			{
			echo "Correct Code Entered";
			//Do you stuff
			}
			else
			{
			die("Wrong Code Entered");
			}
		?>


    
<?php
} else { //form is posted
  include("../secureImage/securimage.php");
  $img = new Securimage();
  $valid = $img->check($_POST['code']);

  if($valid == true) {
    echo "<center>Thanks, you entered the correct code.<br />Click <a href=\"{$_SERVER['PHP_SELF']}\">here</a> to go back.</center>";
  } else {
    echo "<center>Sorry, the code you entered was invalid.  <a href=\"javascript:history.go(-1)\">Go back</a> to try again.</center>";
  }
}

?>
</body>
</html>