<?php
session_start();
include "db.php";

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="nexuslogo.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden !important;
            width: 100vw;
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
            transform: translateX(0);
        }
        .sidebar.mobile {
            transform: translateX(-100%);
        }
        .sidebar.active {
            transform: translateX(0);
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
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            width: 300px;
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
        .qr-card {
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(132, 177, 121, 0.1), rgba(162, 203, 139, 0.1));
            border-left: 4px solid #C7EABB;
        }
        .qr-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #84B179, #A2CB8B);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
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
        .badge {
            background: linear-gradient(135deg, #84B179, #A2CB8B);
            color: white;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(132, 177, 121, 0.3);
        }
        #qr-reader {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding-left: 20px;
                padding-right: 20px;
            }
            .search-container {
                right: 20px;
                width: calc(100vw - 40px);
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
        }
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            .search-container {
                width: calc(100vw - 40px);
                top: 70px;
            }
            .btn-primary {
                width: 100%;
                margin-bottom: 10px;
            }
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
            <li><a href="admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="create_event.php"><i class="bi bi-calendar-plus me-2"></i> Create Event</a></li>
            <li><a href="participants.php"><i class="bi bi-people me-2"></i> Participants</a></li>
            <li><a href="announcements.php"><i class="bi bi-megaphone me-2"></i> Announcements</a></li>
            <li><a href="scan_qr.php" class="active"><i class="bi bi-qr-code-scan me-2"></i> Scan QR</a></li>
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
        <h2 class="mb-1">Scan QR Codes</h2>
        <small class="text-muted">Scan QR codes to mark attendance</small>
    </div>

    

</div>
        </div>

            <div class="card qr-card">
                <div class="card-body p-4">
                    <p class="mb-3">
                        <i class="bi bi-camera-video me-2"></i>
                        <strong>Instructions:</strong> Point your camera to a QR code to mark attendee check-in.
                    </p>
                    <div id="qr-reader" class="mx-auto" style="max-width:500px; width:100%;"></div>
                    <div id="qr-reader-results" class="text-center mt-4 p-3 bg-light rounded">
                        <i class="bi bi-qr-code text-primary" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0 text-muted">Ready to scan QR codes</p>
                    </div>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>

        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
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

            function docReady(fn) {
                if (document.readyState === "complete" || document.readyState === "interactive") {
                    setTimeout(fn, 1);
                } else {
                    document.addEventListener("DOMContentLoaded", fn);
                }
            }

            docReady(function () {
                console.log('Initializing QR scanner...');
                var resultContainer = document.getElementById('qr-reader-results');

                function onScanSuccess(decodedText, decodedResult) {
                    console.log('Scan success:', decodedText, decodedResult);
                    // handle the scanned code as you like
                    console.log(`Code matched = ${decodedText}`, decodedResult);

                    // display scanned result
                    resultContainer.innerHTML = "Scanned QR: " + decodedText;

                    // send scanned QR to PHP to mark attendance
                    fetch("mark_attendance.php?qr_code=" + decodedText)
                    .then(response => response.text())
                    .then(data => {
                        resultContainer.innerHTML += "<br>" + data;
                    });
                }

                console.log('Creating scanner...');
                var html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader", { fps: 10, qrbox: 250 });
                console.log('Rendering scanner...');
                html5QrcodeScanner.render(onScanSuccess);
                console.log('Scanner initialized.');
            });
        </script>
    </body>
    </html>