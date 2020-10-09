<?php
$dbServername = "localhost";
$dbUsername = "root"; //"root"; //"userDBVEA";
$dbPassword = ""; //"V1sion";
$dbname 	= "visionnew_local"; //"dbBinary01"; //"dbBinaryOL0607"; //"dbBnrVisionEA";

// Create connection
$conn = new mysqli($dbServername, $dbUsername, $dbPassword, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
?>