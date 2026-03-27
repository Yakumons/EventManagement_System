<?php
$host="localhost";
$username="root";
$password="";
$dbname="ev_sys";
$conn = new mysqli($host,$username,$password,$dbname);
if($conn->connect_error){
die("Database connection failed");
}
?>