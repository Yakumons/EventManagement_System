<?php
session_start();
include "db.php";

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

function eventStatus($conn, $eventId, $participantLimit) {
    $eventId = $conn->real_escape_string($eventId);
    $participantLimit = (int)$participantLimit;
    
    $query = "SELECT COUNT(*) AS total FROM registrations WHERE event_id='$eventId'";
    $result = $conn->query($query);
    $count = $result->fetch_assoc();
    $registered = (int)$count['total'];
    
    $capacityPercentage = $participantLimit > 0 ? min(100, ($registered / $participantLimit) * 100) : 0;
    
    $capacityText = "$registered/$participantLimit";
    
    $progressText = "<div class='progress' style='height: 6px;'><div class='progress-bar " . ($registered >= $participantLimit ? 'bg-danger' : 'bg-success') . "' style='width: $capacityPercentage%'></div></div>";
    
    $regStatus = $registered >= $participantLimit ? 'Full' : 'Open';
    
    $badge = $registered >= $participantLimit ? 'danger' : 'success';
    
    return array($capacityText, $progressText, $regStatus, $badge, $registered);
}

$today = date("Y-m-d");
$todayDate = date("Y-m-d H:i:s");

$events = $conn->query("SELECT * FROM events");

$totalEvents = $events->num_rows;

$totalParticipantsResult = $conn->query("SELECT COUNT(DISTINCT user_id) FROM registrations");
$totalParticipants = $totalParticipantsResult->fetch_array()[0];

$totalAnnouncementsResult = $conn->query("SELECT COUNT(*) FROM announcements");
$totalAnnouncements = $totalAnnouncementsResult->fetch_array()[0];

$upcomingEvents = $conn->query("SELECT * FROM events WHERE event_date >= '$today' ORDER BY event_date ASC");
$upcomingCount = $upcomingEvents->num_rows;

$allEvents = $conn->query("SELECT * FROM events ORDER BY event_date DESC");

$pastEvents = $conn->query("SELECT * FROM events WHERE event_date < '$today' ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="shortcut icon" href="nexuslogo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden !important;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #84B179 0%, #A2CB8B 100%);
            color: white;
            min-height: 100vh;
            box-shadow: 2px 0 20px rgba(132, 177, 121, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            border-right: 2px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar-header {
            padding: 25px;
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        .sidebar-logo{
            width: 70px;
            height:70px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom:10px;
        }
        
        .sidebar-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: pulse 4s ease-in-out infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.8); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.2; }
            100% { transform: scale(0.8); opacity: 0.5; }
        }
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }
        .sidebar-header small {
            opacity: 0.9;
            font-size: 0.85rem;
            font-weight: 500;
            position: relative;
            z-index: 2;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar li {
            margin: 0;
            position: relative;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 18px 25px;
            color: white;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
            position: relative;
            font-weight: 600;
        }
        .sidebar a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: transparent;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
            box-shadow: 5px 0 15px rgba(0,0,0,0.2);
        }
        .sidebar a:hover::before {
            background: #C7EABB;
        }
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.25);
            font-weight: 800;
            box-shadow: 5px 0 15px rgba(0,0,0,0.3);
        }
        .sidebar a.active::before {
            background: #C7EABB;
        }
        .sidebar .logout-link {
            margin-top: auto;
            border-top: 2px solid rgba(255,255,255,0.3);
        }
        .sidebar .logout-link a {
            background: linear-gradient(135deg, #C7EABB, #E8F5BD);
            border-top: 1px solid rgba(255,255,255,0.2);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sidebar .logout-link a:hover {
            background: linear-gradient(135deg, #E8F5BD, #C7EABB);
            transform: translateX(0);
        }
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 10px !important;
            overflow-y: auto;
            overflow-x: hidden !important;
            transition: margin-left 0.3s ease;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        .search-container {
            position: relative;
            width: 100%;
            max-width: 420px;
            margin-left: auto;
        }
        .search-bar {
            position: relative;
        }
        .search-input {
            border: 2px solid #C7EABB;
            border-radius: 50px;
            padding: 12px 60px 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(199, 234, 187, 0.2);
        }
        .search-input:focus {
            outline: none;
            border-color: #84B179;
            box-shadow: 0 0 0 3px rgba(199, 234, 187, 0.2), 0 4px 15px rgba(199, 234, 187, 0.3);
        }
        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #84B179, #A2CB8B);
            border: none;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(132, 177, 121, 0.4);
        }
        .search-btn:hover {
            background: linear-gradient(135deg, #A2CB8B, #84B179);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 20px rgba(132, 177, 121, 0.6);
        }
        .dashboard-card{
            border-radius:16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .card-title {
            font-weight: 800;
            color: #2c3e50;
            letter-spacing: 0.5px;
        }
        .card-text {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
        }
        .progress-bar {
            transition: width 0.6s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #84B179, #A2CB8B);
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(132, 177, 121, 0.3);
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #A2CB8B, #84B179);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(132, 177, 121, 0.5);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #C7EABB, #E8F5BD);
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(199, 234, 187, 0.3);
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #E8F5BD, #C7EABB);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(199, 234, 187, 0.5);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .btn-outline-primary {
            border: 2px solid #84B179;
            color: #84B179;
            background: transparent;
            border-radius: 12px;
            font-weight: 700;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        .btn-outline-primary:hover {
            background: #84B179;
            color: white;
            box-shadow: 0 4px 15px rgba(132, 177, 121, 0.4);
        }
        .badge {
            background: linear-gradient(135deg, #84B179, #A2CB8B);
            color: white;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(132, 177, 121, 0.3);
        }
        .action-btn {
            min-width: 100px;
            max-width: 100px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.65rem;
            font-size: 0.8rem;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-sm.action-btn {
            padding: 0 0.65rem;
        }
        .btn-sm.action-btn.btn-primary, .btn-sm.action-btn.btn-danger {
            padding: 0 0.65rem;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .alert-info {
            background: linear-gradient(135deg, rgba(199, 234, 187, 0.1), rgba(232, 245, 189, 0.1));
            border-left: 4px solid #C7EABB;
            color: #6c757d;
        }
        .alert-info .alert-link {
            color: #84B179;
            font-weight: 700;
            text-decoration: none;
        }
        .alert-info .alert-link:hover {
            color: #A2CB8B;
            text-decoration: underline;
        }
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
                padding: 8px !important;
                overflow-x: hidden !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .container-fluid {
                padding-left: 5px !important;
                padding-right: 5px !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .search-container {
                right: 15px;
                width: calc(100vw - 30px);
            }
            .mobile-menu-btn {
                display: block !important;
                position: fixed !important;
                top: 12px !important;
                left: 12px !important;
                z-index: 1100 !important;
                width: 36px !important;
                height: 36px !important;
                padding: 0.35rem !important;
                border-radius: 8px !important;
                font-size: 1rem !important;
            }
            .card, .row {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            .table {
                font-size: 0.8rem !important;
                width: 100% !important;
            }
            .action-btn {
                min-width: 65px !important;
                max-width: 65px !important;
                font-size: 0.65rem !important;
                padding: 0.25rem !important;
            }
        }
        @media (max-width: 576px) {
            .main-content {
                padding: 5px !important;
                width: 100% !important;
                overflow-x: hidden !important;
            }
            .container-fluid {
                padding-left: 2px !important;
                padding-right: 2px !important;
                width: 100% !important;
            }
            body {
                overflow-x: hidden !important;
                width: 100% !important;
            }
            .card {
                padding: 8px !important;
            }
        }

        /* Additional responsive improvements for all pages */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0 !important;
                padding: 8px !important;
                overflow-x: hidden !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            body {
                overflow-x: hidden !important;
                width: 100% !important;
            }
            .sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                bottom: 0;
                width: 240px;
                transition: left 0.3s ease;
            }
            .sidebar.active {
                left: 0;
            }
            .dashboard-card,
            .card,
            .table-responsive,
            .input-group {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            .container {
                padding-left: 5px !important;
                padding-right: 5px !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .search-input {
                border-radius: 50px;
            }
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                width: 100% !important;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }
            .d-flex.justify-content-between .badge {
                margin-top: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .dashboard-card {
                padding: 8px !important;
            }
            .card {
                padding: 8px !important;
            }
            .action-btn {
                min-width: 60px !important;
                max-width: 60px !important;
                font-size: 0.6rem !important;
            }
            .statistics-card { font-size: 0.92rem; }
        }
    </style>
</head>
<body>
    <button class="btn btn-primary rounded-circle mobile-menu-btn d-none" onclick="toggleSidebar()" id="mobileMenuBtn" aria-label="Open navigation">
        <i class="bi bi-list" style="font-size: 1rem; line-height: 1;"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header text-center">
            <img src="nexuslogo.png" alt="Logo" class="sidebar-logo">
            <h3>Nexus Events</h3>
            <small>Admin Panel</small>
        </div>
        <ul>
            <li><a href="admin_dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="create_event.php"><i class="bi bi-calendar-plus me-2"></i> Create Event</a></li>
            <li><a href="participants.php"><i class="bi bi-people me-2"></i> Participants</a></li>
            <li><a href="announcements.php"><i class="bi bi-megaphone me-2"></i> Announcements</a></li>
            <li><a href="scan_qr.php"><i class="bi bi-qr-code-scan me-2"></i> Scan QR</a></li>
            <li><a href="reports.php"><i class="bi bi-bar-chart-line me-2"></i> Reports</a></li>
            <li class="logout-link"><a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content" id="mainContent" style="padding-top: 20px;">
    <div class="container-fluid">

        <!-- DATE AT VERY TOP -->
        <div class="w-100 d-flex justify-content-end mb-2">
            <span class="badge bg-primary" style="font-size: 0.9rem; padding: 0.6rem 1rem;">
                <?php echo date('F d, Y'); ?>
            </span>
        </div>

        <!-- CENTERED TITLE -->
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3 gap-2">

    <!-- LEFT: TITLE -->
    <div>
        <h2 class="mb-1">Admin Dashboard</h2>
        <small class="text-muted">Overview of your events</small>
    </div>

    <!-- RIGHT: SEARCH BAR -->
    <div style="width: 100%; max-width: 400px; margin-left: auto;">
        <div class="search-bar">
            <form onsubmit="performSearch(); return false;" style="display: inline; width: 100%;">
                <input type="text" id="globalSearch" class="search-input"
                    placeholder="Search by event, date, or location...">
                <button type="button" class="search-btn" onclick="performSearch()">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

</div>
        </div>
           

           <div class="row g-4 mb-4">

    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="card shadow-sm rounded-3 w-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title text-dark fw-bold">Total Events</h6>
                        <h3 class="text-primary fw-bold"><?php echo $totalEvents; ?></h3>
                        <small class="text-muted">All events in system</small>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-calendar-event text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="card shadow-sm rounded-3 w-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title text-dark fw-bold">Total Participants</h6>
                        <h3 class="text-success fw-bold"><?php echo $totalParticipants; ?></h3>
                        <small class="text-muted">Registered users</small>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-person-check text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="card shadow-sm rounded-3 w-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title text-dark fw-bold">Announcements</h6>
                        <h3 class="text-warning fw-bold"><?php echo $totalAnnouncements; ?></h3>
                        <small class="text-muted">Active announcements</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-megaphone text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="card shadow-sm rounded-3 w-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title text-dark fw-bold">Upcoming Events</h6>
                        <h3 class="text-danger fw-bold"><?php echo $upcomingCount; ?></h3>
                        <small class="text-muted">Events yet to happen</small>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-rocket text-danger" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

            <div class="card shadow-sm rounded-3 mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Upcoming Events</h5>
                    <small class="text-success">Track event schedule, participant counts, and capacity status</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Participants</th>
                                    <th>Capacity</th>
                                    <th>Registration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($upcomingEvents->num_rows > 0){
                                    while($eventRow = $upcomingEvents->fetch_assoc()){
                                        list($capacityTxt, $progressTxt, $regStatus, $badge, $registered) = eventStatus($conn, $eventRow['id'], $eventRow['participant_limit']);
                                        $eventDateFormatted = date('M d, Y', strtotime($eventRow['event_date']));
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($eventRow['title']) . "</td>";
                                        echo "<td>{$eventDateFormatted}</td>";
                                        echo "<td>" . htmlspecialchars($eventRow['location']) . "</td>";
                                        echo "<td>{$capacityTxt}</td>";
                                        echo "<td>{$regStatus}</td>";
                                        echo "<td>{$progressTxt}</td>";
                                        echo "<td><span class='badge bg-{$badge}'>Upcoming</span></td>";
                                        echo "<td>
                                                <div class='d-flex align-items-center gap-2'>
                                                    <a href='edit_event.php?id={$eventRow['id']}' 
                                                       class='btn btn-sm btn-primary action-btn'>Edit</a>
                                                    <a href='delete_event.php?id={$eventRow['id']}' 
                                                       onclick='return confirm(\"Are you sure you want to delete this data?\")' 
                                                       class='btn btn-sm btn-danger action-btn'>Delete</a>
                                                </div>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center text-muted'>No upcoming events found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">All Events</h5>
                    <small class="text-success">Complete list of all events in the system</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Event</th>
                                    <th>Schedule</th>
                                    <th>Location</th>
                                    <th>Participants</th>
                                    <th>Capacity</th>
                                    <th>Registration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($allEvents->num_rows > 0){
                                    while($eventRow = $allEvents->fetch_assoc()){
                                        list($capacityTxt, $progressTxt, $regStatus, $badge, $registered) = eventStatus($conn, $eventRow['id'], $eventRow['participant_limit']);
                                        $eventDateFormatted = date('M d, Y', strtotime($eventRow['event_date']));
                                        $eventTime = strtotime($eventRow['event_date']);
                                        $now = strtotime($todayDate);
                                        $eventStatus = $eventTime >= $now ? 'Upcoming' : 'Past';
                                        $statusBadgeColor = $eventTime >= $now ? 'info' : 'secondary';
                                        $isPast = $eventTime < $now;
                                        $actionButtons = $isPast
                                            ? "<span class='text-muted small'>Not available for past events</span>"
                                            : "<div class='d-flex align-items-center gap-2'>
                                                    <a href='edit_event.php?id={$eventRow['id']}' class='btn btn-sm btn-primary action-btn'>Edit</a>
                                                    <a href='delete_event.php?id={$eventRow['id']}' onclick='return confirm(\"Are you sure you want to delete this data?\")' class='btn btn-sm btn-danger action-btn'>Delete</a>
                                                </div>";
                                        echo "<tr>";
                                        echo "<td><strong>" . htmlspecialchars($eventRow['title']) . "</strong></td>";
                                        echo "<td>{$eventDateFormatted}</td>";
                                        echo "<td>" . htmlspecialchars($eventRow['location']) . "</td>";
                                        echo "<td>{$capacityTxt}</td>";
                                        echo "<td>{$regStatus}</td>";
                                        echo "<td>{$progressTxt}</td>";
                                        echo "<td><span class='badge bg-{$statusBadgeColor}'>{$eventStatus}</span></td>";
                                        echo "<td>{$actionButtons}</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center text-muted'>No events found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Event History</h5>
                    <small class="text-secondary">Past events and their final attendance records</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Participants</th>
                                    <th>Capacity</th>
                                    <th>Attendance %</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($pastEvents->num_rows > 0){
                                    while($eventRow = $pastEvents->fetch_assoc()){
                                        list($capacityTxt, $progressTxt, $regStatus, $badge, $registered) = eventStatus($conn, $eventRow['id'], $eventRow['participant_limit']);
                                        $eventDateFormatted = date('M d, Y', strtotime($eventRow['event_date']));
                                        echo "<tr>";
                                        echo "<td><strong>" . htmlspecialchars($eventRow['title']) . "</strong></td>";
                                        echo "<td>{$eventDateFormatted}</td>";
                                        echo "<td>" . htmlspecialchars($eventRow['location']) . "</td>";
                                        echo "<td>{$capacityTxt}</td>";
                                        echo "<td>{$regStatus}</td>";
                                        echo "<td>{$progressTxt}</td>";
                                        echo "<td><span class='badge bg-secondary'>Completed</span></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center text-muted'>No past events yet.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>

        <script>
            // Mobile menu toggle
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                sidebar.classList.toggle('active');
            }
            
            // Responsive sidebar handling
            function handleResize() {
                const sidebar = document.getElementById('sidebar');
                const mobileMenuBtn = document.getElementById('mobileMenuBtn');
                const mainContent = document.getElementById('mainContent');
                
                if (window.innerWidth <= 991) {
                    mobileMenuBtn.classList.remove('d-none');
                    sidebar.classList.remove('active');
                    mainContent.style.marginLeft = '0';
                } else {
                    mobileMenuBtn.classList.add('d-none');
                    sidebar.classList.add('active');
                    mainContent.style.marginLeft = '280px';
                }
            }
            
            // Initialize on load
            window.addEventListener('load', handleResize);
            window.addEventListener('resize', handleResize);

            // No toggle needed when search is always visible.
            function toggleSearch() {
                // intentionally empty
            }
            
            function showNoResults(message) {
                let noResults = document.getElementById('no-results-message');
                if (!noResults) {
                    noResults = document.createElement('div');
                    noResults.id = 'no-results-message';
                    noResults.className = 'alert alert-info mt-3';
                    noResults.innerHTML = `<i class="bi bi-info-circle me-2"></i>${message}`;
                    document.querySelector('.main-content').appendChild(noResults);
                } else {
                    noResults.style.display = 'block';
                }
            }
            
            function performSearch() {
                const searchTerm = document.getElementById('globalSearch').value.toLowerCase().trim();
                if (searchTerm === '') {
                    clearSearchResults();
                    return;
                }
                const currentPath = window.location.pathname;
                const page = currentPath.split('/').pop();
                if (page === 'admin_dashboard.php') {
                    searchAdminDashboard(searchTerm);
                    // Scroll to top of main content to see results
                    document.getElementById('mainContent').scrollTop = 0;
                } else if (page === 'events.php') {
                    searchEvents(searchTerm);
                } else if (page === 'participants.php') {
                    searchParticipants(searchTerm);
                }
            }
            
            function searchAdminDashboard(searchTerm) {
                const tables = document.querySelectorAll('table');
                let totalFound = 0;
                let hasVisibleRows = false;
                
                // Highlight search term in results
                const highlightStyle = document.getElementById('search-highlight-style');
                if (!highlightStyle) {
                    const style = document.createElement('style');
                    style.id = 'search-highlight-style';
                    style.innerHTML = `
                        .search-highlight {
                            background-color: #fff3cd;
                            font-weight: 600;
                            padding: 2px 4px;
                            border-radius: 3px;
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                tables.forEach(table => {
                    const rows = table.querySelectorAll('tbody tr');
                    let tableHasVisible = false;
                    
                    rows.forEach(row => {
                        const cells = row.querySelectorAll('td');
                        if (cells.length > 0) {
                            // Extract title (first column), schedule (second column), location (third column)
                            const title = cells[0]?.textContent.toLowerCase() || '';
                            const schedule = cells[1]?.textContent.toLowerCase() || '';
                            const location = cells[2]?.textContent.toLowerCase() || '';
                            
                            if (title.includes(searchTerm) || schedule.includes(searchTerm) || location.includes(searchTerm)) {
                                row.style.display = '';
                                tableHasVisible = true;
                                totalFound++;
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                    
                    // Hide table if no visible rows, show parent card
                    const tableCard = table.closest('.card');
                    if (tableCard) {
                        if (tableHasVisible) {
                            tableCard.style.display = 'block';
                            hasVisibleRows = true;
                        } else {
                            tableCard.style.display = 'none';
                        }
                    }
                });
                
                if (!hasVisibleRows) {
                    showNoResults('No events found matching "' + searchTerm + '". Try searching by event title, date, or location.');
                }
            }
            
            function searchEvents(searchTerm) {
                const eventCards = document.querySelectorAll('.card.shadow');
                let found = false;
                eventCards.forEach(card => {
                    const title = card.querySelector('h5')?.textContent.toLowerCase() || '';
                    const date = card.querySelector('p:nth-child(2)')?.textContent.toLowerCase() || '';
                    const location = card.querySelector('p:nth-child(3)')?.textContent.toLowerCase() || '';
                    const details = card.querySelector('.btn-info')?.textContent.toLowerCase() || '';
                    if (title.includes(searchTerm) || date.includes(searchTerm) || location.includes(searchTerm) || details.includes(searchTerm)) {
                        card.style.display = 'block';
                        found = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                if (!found) {
                    showNoResults('No matching events found.');
                }
            }
            
            function searchParticipants(searchTerm) {
                const participants = document.querySelectorAll('.card.mb-3');
                let found = false;
                participants.forEach(card => {
                    const name = card.querySelector('h6')?.textContent.toLowerCase() || '';
                    const email = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
                    const event = card.querySelector('small.text-muted')?.textContent.toLowerCase() || '';
                    if (name.includes(searchTerm) || email.includes(searchTerm) || event.includes(searchTerm)) {
                        card.style.display = 'block';
                        found = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                if (!found) {
                    showNoResults('No matching participants found.');
                }
            }
            
            
            function clearSearchResults() {
                const currentPath = window.location.pathname;
                const page = currentPath.split('/').pop();
                if (page === 'admin_dashboard.php') {
                    // Show all table rows
                    document.querySelectorAll('table tbody tr').forEach(row => row.style.display = '');
                    // Show all table cards
                    document.querySelectorAll('table').forEach(table => {
                        const card = table.closest('.card');
                        if (card) card.style.display = 'block';
                    });
                    document.querySelectorAll('.card.shadow-sm').forEach(card => card.style.display = 'block');
                } else if (page === 'events.php') {
                    document.querySelectorAll('.card.shadow').forEach(card => card.style.display = 'block');
                } else if (page === 'participants.php') {
                    document.querySelectorAll('.card.mb-3').forEach(card => card.style.display = 'block');
                }
                const noResults = document.getElementById('no-results-message');
                if (noResults) noResults.style.display = 'none';
            }
            
            let searchTimeout;
            document.getElementById('globalSearch').addEventListener('input', function() {
                // Only clear results if search is completely empty
                if (this.value.trim() === '') {
                    clearSearchResults();
                }
            });
            
            document.getElementById('globalSearch').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        </script>
    </body>
    </html>
