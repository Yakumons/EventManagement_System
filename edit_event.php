<?php
session_start();
include "db.php";

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location: admin_dashboard.php');
    exit;
}
$event_id = $conn->real_escape_string($_GET['id']);
$eventResult = $conn->query("SELECT * FROM events WHERE id='$event_id'");
if($eventResult->num_rows === 0){
    header('Location: admin_dashboard.php');
    exit;
}
$event = $eventResult->fetch_assoc();
if(strtotime($event['event_date']) < strtotime(date('Y-m-d'))){
    header('Location: admin_dashboard.php?message='.urlencode('Cannot edit past events.'));
    exit;
}
$message = '';
if(isset($_POST['update'])){
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $location = $_POST['location'];
    $other_loc = $_POST['other_location'];
    $limit = $_POST['limit'];
    if($location === 'Other' && !empty($other_loc)) {
        $location = $other_loc;
    }
    $poster_name = $event['poster'];
    if(isset($_FILES['poster']) && $_FILES['poster']['name'] !== ''){
        $newPoster = $_FILES['poster']['name'];
        $temp = $_FILES['poster']['tmp_name'];
        if(!file_exists('posters')) mkdir('posters');
        move_uploaded_file($temp, "posters/".$newPoster);
        $poster_name = $newPoster;
    }
    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, start_time=?, end_time=?, location=?, participant_limit=?, poster=? WHERE id=?");
    $stmt->bind_param('ssssssisi', $title, $desc, $date, $start_time, $end_time, $location, $limit, $poster_name, $event_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_dashboard.php?message='.urlencode('Event updated successfully.'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
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
        .edit-card {
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .edit-card:hover {
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
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #495057, #6c757d);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.5);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #84B179;
            box-shadow: 0 0 0 3px rgba(132, 177, 121, 0.2);
            background: rgba(255, 255, 255, 1);
        }
        .form-label {
            font-weight: 700;
            color: #495057;
            margin-bottom: 8px;
        }
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .alert-success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(25, 135, 84, 0.1));
            border-left: 4px solid #28a745;
            color: #155724;
        }
        .badge {
            background: linear-gradient(135deg, #84B179, #A2CB8B);
            color: white;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(132, 177, 121, 0.3);
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
            .btn-primary, .btn-secondary {
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
        <h2 class="mb-1">Edit Event</h2>
        <small class="text-muted">Update your event details</small>
    </div>

    

</div>
        </div>

            <?php if (!empty($message)) echo $message; ?>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card edit-card">
                        <div class="card-body p-4">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Event Title</label>
                                    <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($event['title']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Event Date</label>
                                        <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d', strtotime($event['event_date'])); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Location</label>
                                        <select name="location" class="form-select" required>
                                            <option value="">Select a location</option>
                                            <option value="Barangay 171 Hall" <?php echo $event['location'] === 'Barangay 171 Hall' ? 'selected' : ''; ?>>Barangay 171 Hall</option>
                                            <option value="Caloocan Sports Complex" <?php echo $event['location'] === 'Caloocan Sports Complex' ? 'selected' : ''; ?>>Caloocan Sports Complex</option>
                                            <option value="Solar Urban Homes North" <?php echo $event['location'] === 'Solar Urban Homes North' ? 'selected' : ''; ?>>Solar Urban Homes North</option>
                                            <option value="Deparo Subdivision" <?php echo $event['location'] === 'Deparo Subdivision' ? 'selected' : ''; ?>>Deparo Subdivision</option>
                                            <option value="Multi-Purpose Hall - Natividad Phase II" <?php echo $event['location'] === 'Multi-Purpose Hall - Natividad Phase II' ? 'selected' : ''; ?>>Multi-Purpose Hall - Natividad Phase II</option>
                                            <option value="Rainbow Village 5 Phase 2 Multi-Purpose Hall" <?php echo $event['location'] === 'Rainbow Village 5 Phase 2 Multi-Purpose Hall' ? 'selected' : ''; ?>>Rainbow Village 5 Phase 2 Multi-Purpose Hall</option>
                                            <option value="Caloocan City Hall" <?php echo $event['location'] === 'Caloocan City Hall' ? 'selected' : ''; ?>>Caloocan City Hall</option>
                                            <option value="Congress Village" <?php echo $event['location'] === 'Congress Village' ? 'selected' : ''; ?>>Congress Village</option>
                                            <option value="Caloocan City College" <?php echo $event['location'] === 'Caloocan City College' ? 'selected' : ''; ?>>Caloocan City College</option>
                                            <option value="Barangay 168 Covered Court" <?php echo $event['location'] === 'Barangay 168 Covered Court' ? 'selected' : ''; ?>>Barangay 168 Covered Court</option>
                                            <option value="Other" <?php echo !in_array($event['location'], ['Barangay 171 Hall', 'Caloocan Sports Complex', 'Solar Urban Homes North', 'Deparo Subdivision', 'Multi-Purpose Hall - Natividad Phase II', 'Rainbow Village 5 Phase 2 Multi-Purpose Hall', 'Caloocan City Hall', 'Congress Village', 'Caloocan City College', 'Barangay 168 Covered Court']) ? 'selected' : ''; ?>>Other (Please specify)</option>
                                        </select>
                                        <div id="other-location-field" style="display: <?php echo !in_array($event['location'], ['Barangay 171 Hall', 'Caloocan Sports Complex', 'Solar Urban Homes North', 'Deparo Subdivision', 'Multi-Purpose Hall - Natividad Phase II', 'Rainbow Village 5 Phase 2 Multi-Purpose Hall', 'Caloocan City Hall', 'Congress Village', 'Caloocan City College', 'Barangay 168 Covered Court']) ? 'block' : 'none'; ?>; margin-top: 10px;">
                                            <input type="text" name="other_location" class="form-control" placeholder="Enter specific location" value="<?php echo !in_array($event['location'], ['Barangay 171 Hall', 'Caloocan Sports Complex', 'Solar Urban Homes North', 'Deparo Subdivision', 'Multi-Purpose Hall - Natividad Phase II', 'Rainbow Village 5 Phase 2 Multi-Purpose Hall', 'Caloocan City Hall', 'Congress Village', 'Caloocan City College', 'Barangay 168 Covered Court']) ? htmlspecialchars($event['location']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Start Time</label>
                                        <input type="time" name="start_time" class="form-control" required value="<?php echo date('H:i', strtotime($event['start_time'])); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">End Time</label>
                                        <input type="time" name="end_time" class="form-control" required value="<?php echo date('H:i', strtotime($event['end_time'])); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Participant Limit</label>
                                    <input type="number" name="limit" class="form-control" min="1" required value="<?php echo intval($event['participant_limit']); ?>">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Event Poster (leave blank to keep current)</label>
                                    <input type="file" name="poster" class="form-control" accept="image/*">
                                    <?php if(!empty($event['poster'])): ?>
                                        <small class="text-muted">Current: <?php echo htmlspecialchars($event['poster']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex gap-3">
                                    <button class="btn btn-primary flex-grow-1" name="update">
                                        <i class="bi bi-check-circle me-2"></i>Update Event
                                    </button>
                                    <a href="admin_dashboard.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>

        <script>
            // Mobile menu toggle
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

            document.addEventListener('DOMContentLoaded', function() {
                const locationSelect = document.querySelector('select[name="location"]');
                const otherLocationField = document.getElementById('other-location-field');
                const otherLocationInput = document.querySelector('input[name="other_location"]');
                locationSelect.addEventListener('change', function() {
                    if (this.value === 'Other') {
                        otherLocationField.style.display = 'block';
                        otherLocationInput.required = true;
                    } else {
                        otherLocationField.style.display = 'none';
                        otherLocationInput.required = false;
                        otherLocationInput.value = '';
                    }
                });
                const form = document.querySelector('form');
                form.addEventListener('submit', function(e) {
                    if (locationSelect.value === 'Other' && !otherLocationInput.value.trim()) {
                        e.preventDefault();
                        alert('Please enter a specific location when selecting "Other"');
                        otherLocationInput.focus();
                    }
                });
            });
        </script>
    </body>
    </html>