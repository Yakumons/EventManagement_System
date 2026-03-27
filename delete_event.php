<?php
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include "navbar_admin.php";
include "db.php";
if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location: admin_dashboard.php');
    exit;
}
$event_id = $conn->real_escape_string($_GET['id']);
$eventResult = $conn->prepare("SELECT event_date FROM events WHERE id = ?");
$eventResult->bind_param('i', $event_id);
$eventResult->execute();
$eventResult->bind_result($event_date);
if (!$eventResult->fetch()) {
    header('Location: admin_dashboard.php?message=' . urlencode('Event not found.'));
    exit;
}
$eventResult->close();
if (strtotime($event_date) < strtotime(date('Y-m-d'))) {
    header('Location: admin_dashboard.php?message=' . urlencode('Cannot delete past events.'));
    exit;
}
$stmt1 = $conn->prepare("DELETE FROM registrations WHERE event_id=?");
$stmt1->bind_param('i', $event_id);
$stmt1->execute();
$stmt1->close();
$stmt2 = $conn->prepare("DELETE FROM events WHERE id=?");
$stmt2->bind_param('i', $event_id);
$stmt2->execute();
$stmt2->close();
header('Location: admin_dashboard.php?message='.urlencode('Event deleted successfully.'));
exit;
?>