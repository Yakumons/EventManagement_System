<?php
session_start();
include "db.php";

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'];
$event_id = $_GET['event_id'];
$conn->query("UPDATE registrations SET status='approved' WHERE id='$id'");
header("Location: participants.php?event_id=".$event_id);
?>