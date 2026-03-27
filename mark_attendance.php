<?php
session_start();
include "db.php";

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['qr_code'])) {
    $qr_code = $conn->real_escape_string($_GET['qr_code']);
    $stmt = $conn->prepare("SELECT * FROM registrations WHERE qr_code = ?");
    $stmt->bind_param("s", $qr_code);
    $stmt->execute();
    $check = $stmt->get_result();
    
    if ($check->num_rows > 0) {
        $stmtUpdate = $conn->prepare("UPDATE registrations SET attendance='present' WHERE qr_code = ?");
        $stmtUpdate->bind_param("s", $qr_code);
        $stmtUpdate->execute();
        echo "<span class='text-success'>Attendance marked for QR: $qr_code</span>";
    } else {
        echo "<span class='text-danger'>QR code not found!</span>";
    }
}
?>