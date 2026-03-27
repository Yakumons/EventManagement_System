<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $conn->real_escape_string($_GET['id']);

$stmtCheck = $conn->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
$stmtCheck->bind_param("ii", $user_id, $event_id);
$stmtCheck->execute();
$check = $stmtCheck->get_result();

if($check->num_rows > 0){
    echo "<script>
        alert('You already registered for this event');
        window.location='events.php';
    </script>";
    exit();
}

$stmtEvent = $conn->prepare("SELECT participant_limit FROM events WHERE id = ?");
$stmtEvent->bind_param("i", $event_id);
$stmtEvent->execute();
$event_data = $stmtEvent->get_result()->fetch_assoc();

$stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM registrations WHERE event_id = ?");
$stmtCount->bind_param("i", $event_id);
$stmtCount->execute();
$total = $stmtCount->get_result()->fetch_assoc()['total'];

if($total >= $event_data['participant_limit']){
    echo "<script>
        alert('Event is already full');
        window.location='events.php';
    </script>";
    exit();
}

$qr = uniqid("QR");
$stmtInsert = $conn->prepare("INSERT INTO registrations(user_id, event_id, qr_code) VALUES(?, ?, ?)");
$stmtInsert->bind_param("iis", $user_id, $event_id, $qr);
$stmtInsert->execute();

echo "<script>
    alert('Event Registered Successfully');
    window.location='my_qr.php';
</script>";
?>