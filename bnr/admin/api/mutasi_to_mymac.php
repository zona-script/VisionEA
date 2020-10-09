<?php
// die();
$path =  (dirname(dirname(dirname(__FILE__))));
include_once($path."/includes/inc_def.php");
include_once($path.'/includes/inc_conn.php');  
include_once($path.'/includes/inc_functions.php');

$sql  = " SELECT mbrUsername, mbrDate, passWord FROM dtMember";
$sql .= " INNER JOIN (";
$sql .= " 	SELECT * FROM trPassword as t";
$sql .= " 	WHERE passID = (SELECT passID FROM trPassword WHERE passMbrUsername=t.passMbrUsername ORDER BY passDate DESC LIMIT 1)";
$sql .= " ) as pass ON pass.passMbrUsername = mbrUsername";
$result = $conn->query($sql);
// echo $sql; die();
$data = $data1 = "";
while ($row = $result->fetch_assoc()){
	$data .= "INSERT INTO dtmember (mbrUsername, mbrDate) VALUES ('".$row['mbrUsername']."','".$row['mbrDate']."');<br>";
	$data1 .= "INSERT INTO dtpasswd (pwdUsername, pwdWord, pwdUpdatedate) VALUES ('".$row['mbrUsername']."','".$row['passWord']."', '".$row['mbrDate']."');<br>";
}
echo $data."<br>".$data1;
?>