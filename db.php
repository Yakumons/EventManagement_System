<?php
$host = getenv('MYSQLHOST') ?: 'localhost';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: 'ev_sys';
$port = getenv('MYSQLPORT') ?: 3306;

$conn = new mysqli($host, $username, $password, $dbname, $port);
if($conn->connect_error){
    die("Database connection failed");
}
?>
