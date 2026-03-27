<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
header("Location: login.php");
exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link rel="shortcut icon" href="nexuslogo.png" type="image/x-icon">
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Event System</h3>
            <small>Admin Panel</small>
        </div>
        <ul>
            <li><a href="admin_dashboard.php" <?php if(basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') echo 'class="active"'; ?>><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="create_event.php" <?php if(basename($_SERVER['PHP_SELF']) == 'create_event.php') echo 'class="active"'; ?>><i class="bi bi-plus-circle me-2"></i> Create Event</a></li>
            <li><a href="participants.php" <?php if(basename($_SERVER['PHP_SELF']) == 'participants.php') echo 'class="active"'; ?>><i class="bi bi-people me-2"></i> Participants</a></li>
            <li><a href="announcements.php" <?php if(basename($_SERVER['PHP_SELF']) == 'announcements.php') echo 'class="active"'; ?>><i class="bi bi-megaphone me-2"></i> Announcements</a></li>
            <li><a href="scan_qr.php" <?php if(basename($_SERVER['PHP_SELF']) == 'scan_qr.php') echo 'class="active"'; ?>><i class="bi bi-qr-code-scan me-2"></i> QR Scanner</a></li>
            <li><a href="reports.php" <?php if(basename($_SERVER['PHP_SELF']) == 'reports.php') echo 'class="active"'; ?>><i class="bi bi-file-earmark-text me-2"></i> Reports</a></li>
            <li class="logout-link"><a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        
    </div>
</body>
</html>