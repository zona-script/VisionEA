<?php
session_start();
include_once("./includes/inc_def.php"); 
//$captchanumber=rand(1000,9999);
$captchanumber = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz'; // Initializing PHP variable with string
$captchanumber = substr(str_shuffle($captchanumber), 0, 3); // Getting first 3 word after shuffle.
$_SESSION["code"] = $captchanumber; // Initializing session variable with above generated sub-string

$im = imagecreatetruecolor(100, 50);
//$bg = imagecolorallocate($im, 22, 86, 165); //background color blue
//$bg = imagecreatefromjpeg($COMPANY_SITE . "assets/img/captcha/bgNoise02.jpg"); // Generating CAPTCHA
$fg = imagecolorallocate($im, 175, 199, 200);//text color white
//imagefill($im, 20, 20, $bg);
imagestring($im, 5, 20, 15,  $captchanumber, $fg);
header("Cache-Control: no-cache, must-revalidate");
header('Content-type: image/png');
imagepng($im);
imagedestroy($im);

/*
session_start();
$captchanumber = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz'; // Initializing PHP variable with string
$captchanumber = substr(str_shuffle($captchanumber), 0, 6); // Getting first 6 word after shuffle.
$_SESSION["code"] = $captchanumber; // Initializing session variable with above generated sub-string
$image = imagecreatefromjpeg("bj.jpg"); // Generating CAPTCHA
$foreground = imagecolorallocate($image, 175, 199, 200); // Font Color
imagestring($image, 5, 45, 8, $captchanumber, $foreground);
header('Content-type: image/png');
imagepng($image);
imagedestroy($im);
*/
?>