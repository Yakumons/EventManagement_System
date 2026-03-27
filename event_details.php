<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = $conn->real_escape_string($_GET['id']);
$today = date("Y-m-d");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Check if user is already registered for this event
$stmtCheck = $conn->prepare("SELECT COUNT(*) AS total FROM registrations WHERE event_id = ? AND user_id = ?");
$stmtCheck->bind_param("ii", $id, $user_id);
$stmtCheck->execute();
$already_registered = $stmtCheck->get_result()->fetch_assoc()['total'] > 0;

$stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM registrations WHERE event_id = ?");
$stmtCount->bind_param("i", $id);
$stmtCount->execute();
$count = $stmtCount->get_result()->fetch_assoc()['total'];
$is_full = $count >= $row['participant_limit'];
$is_ended = $row['event_date'] < $today;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
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
            width: 100vw;
            max-width: 100%;
            box-sizing: border-box;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #FF8B5A 0%, #FF5A5A 100%);
            color: white;
            min-height: 100vh;
            box-shadow: 2px 0 20px rgba(255, 90, 90, 0.3);
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
            background: #FFD45A;
        }
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.25);
            font-weight: 800;
            box-shadow: 5px 0 15px rgba(0,0,0,0.3);
        }
        .sidebar a.active::before {
            background: #FFD45A;
        }
        .sidebar .logout-link {
            margin-top: auto;
            border-top: 2px solid rgba(255,255,255,0.3);
        }
        .sidebar .logout-link a {
            background: linear-gradient(135deg, #FFA95A, #FF5A5A);
            border-top: 1px solid rgba(255,255,255,0.2);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sidebar .logout-link a:hover {
            background: linear-gradient(135deg, #FF5A5A, #FF8B5A);
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
            display: flex;
            align-items: center;
            width: 300px;
        }
        .search-input {
            width: 250px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px 0 0 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: #007bff;
        }
        .search-btn {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: 1px solid #007bff;
            border-radius: 0 6px 6px 0;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        .search-btn:hover {
            background: #0056b3;
            background: linear-gradient(135deg, #FF8B5A, #FF5A5A);
            border: none;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 90, 90, 0.4);
        }
        .search-btn:hover {
            background: linear-gradient(135deg, #FF5A5A, #FF8B5A);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 20px rgba(255, 90, 90, 0.6);
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
            background: linear-gradient(135deg, #FF8B5A, #FF5A5A);
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(255, 90, 90, 0.3);
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #FF5A5A, #FF8B5A);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 90, 90, 0.5);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #FFA95A, #FFD45A);
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(255, 169, 90, 0.3);
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #FFD45A, #FFA95A);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 169, 90, 0.5);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .btn-outline-primary {
            border: 2px solid #FF8B5A;
            color: #FF8B5A;
            background: transparent;
            border-radius: 12px;
            font-weight: 700;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        .btn-outline-primary:hover {
            background: #FF8B5A;
            color: white;
            box-shadow: 0 4px 15px rgba(255, 139, 90, 0.4);
        }
        .badge {
            background: linear-gradient(135deg, #FF8B5A, #FF5A5A);
            color: white;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(255, 90, 90, 0.3);
        }
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .alert-info {
            background: linear-gradient(135deg, rgba(255, 169, 90, 0.1), rgba(255, 212, 90, 0.1));
            border-left: 4px solid #FFD45A;
            color: #6c757d;
        }
        .alert-info .alert-link {
            color: #FF8B5A;
            font-weight: 700;
            text-decoration: none;
        }
        .alert-info .alert-link:hover {
            color: #FF5A5A;
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
                margin-left: 0;
                padding-left: 20px;
                padding-right: 20px;
            }
            .search-container {
                right: 20px;
                width: calc(100vw - 40px);
            }
            .mobile-menu-btn {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
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
            <small>User Panel</small>
        </div>
        <ul>
            <li><a href="user_dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="events.php"><i class="bi bi-calendar-event me-2"></i> Events</a></li>
            <li><a href="my_qr.php"><i class="bi bi-qr-code me-2"></i> My QR Code</a></li>
            <li><a href="notifications.php"><i class="bi bi-bell me-2"></i> Notifications</a></li>
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
        <h2 class="mb-1">Event Details</h2>
    </div>

    

</div>
        </div>
            <div class="card shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Event Details</h2>
                    <small class="text-muted">Complete information about the selected event</small>
                </div>
                <div>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Events
                    </a>
                </div>
            </div>
<?php if($result->num_rows > 0){ ?>
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="text-dark fw-bold mb-3"><?php echo htmlspecialchars($row['title']); ?></h4>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar-event me-3 text-primary"></i>
                                        <div>
                                            <small class="text-muted">Date</small>
                                            <div class="fw-bold"><?php echo date('F d, Y', strtotime($row['event_date'])); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock me-3 text-primary"></i>
                                        <div>
                                            <small class="text-muted">Time</small>
                                            <div class="fw-bold">
                                                <?php
                                                if (!empty($row['start_time']) && !empty($row['end_time'])) {
                                                    echo date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time']));
                                                } elseif (!empty($row['start_time'])) {
                                                    echo date('h:i A', strtotime($row['start_time']));
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt me-3 text-primary"></i>
                                        <div>
                                            <small class="text-muted">Location</small>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['location']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people me-3 text-primary"></i>
                                        <div>
                                            <small class="text-muted">Capacity</small>
                                            <div class="fw-bold"><?php echo $row['participant_limit']; ?> attendees</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people-fill me-3 text-primary"></i>
                                        <div>
                                            <small class="text-muted">Registered</small>
                                            <div class="fw-bold"><?php echo $count; ?> attendees</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-check me-3 text-primary"></i>
                                        <div>
                                            <small class="text-muted">Available Slots</small>
                                            <div class="fw-bold"><?php echo $row['participant_limit'] - $count; ?> slots</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">Event Status</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Registration</span>
                                        <?php if($is_full): ?>
                                            <span class="badge bg-danger">Full</span>
                                        <?php elseif($is_ended): ?>
                                            <span class="badge bg-secondary">Ended</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Open</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Available Slots</span>
                                        <span class="fw-bold"><?php echo $row['participant_limit'] - $count; ?></span>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-primary" style="width: <?php echo ($count / $row['participant_limit']) * 100; ?>%"></div>
                                    </div>
                                    <div class="text-center">
                                        <?php if($already_registered): ?>
                                            <button type="button" class="btn btn-success w-100" onclick="showAlreadyRegisteredNotification()">
                                                <i class="bi bi-check-circle me-2"></i>Already Registered
                                            </button>
                                        <?php elseif($is_full): ?>
                                            <button class="btn btn-secondary w-100" disabled>
                                                <i class="bi bi-exclamation-circle me-2"></i>Full
                                            </button>
                                        <?php elseif($is_ended): ?>
                                            <button class="btn btn-secondary w-100" disabled>
                                                <i class="bi bi-calendar-x me-2"></i>Ended
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary w-100" onclick="confirmRegistration(<?php echo $row['id']; ?>)">
                                                <i class="bi bi-plus-circle me-2"></i>Register Now
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-warning mb-0" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Event not found!</strong> The requested event could not be located.
                       <a href="events.php" class="alert-link">Browse Events</a>
                    </div>
                <?php } ?>
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

            function toggleSearch() {
                const container = document.getElementById('searchContainer');
                const toggleBtn = document.getElementById('searchToggleBtn');
                if (container.style.display === 'none' || container.style.display === '') {
                    container.style.display = 'block';
                    toggleBtn.innerHTML = '<i class="bi bi-x"></i>';
                    toggleBtn.classList.remove('btn-primary');
                    toggleBtn.classList.add('btn-warning');
                    document.getElementById('globalSearch').focus();
                } else {
                    container.style.display = 'none';
                    toggleBtn.innerHTML = '<i class="bi bi-search"></i>';
                    toggleBtn.classList.remove('btn-warning');
                    toggleBtn.classList.add('btn-primary');
                    document.getElementById('globalSearch').value = '';
                    clearSearchResults();
                }
            }
            
            function performSearch() {
                const searchTerm = document.getElementById('globalSearch').value.toLowerCase().trim();
                if (searchTerm === '') return;
                const currentPath = window.location.pathname;
                const page = currentPath.split('/').pop();
                if (page === 'user_dashboard.php') {
                    searchDashboard(searchTerm);
                } else if (page === 'events.php') {
                    searchEvents(searchTerm);
                } else if (page === 'notifications.php') {
                    searchNotifications(searchTerm);
                }
            }
            
            function searchDashboard(searchTerm) {
                const eventCards = document.querySelectorAll('.card.shadow-sm');
                let found = false;
                eventCards.forEach(card => {
                    const title = card.querySelector('h5')?.textContent.toLowerCase() || '';
                    const date = card.querySelector('small.text-muted')?.textContent.toLowerCase() || '';
                    const location = card.querySelector('small.text-muted:nth-child(3)')?.textContent.toLowerCase() || '';
                    if (title.includes(searchTerm) || date.includes(searchTerm) || location.includes(searchTerm)) {
                        card.style.display = 'block';
                        found = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                if (!found) {
                    showNoResults('No matching events found in dashboard.');
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
            
            function searchNotifications(searchTerm) {
                const notifications = document.querySelectorAll('.card.mb-3');
                let found = false;
                notifications.forEach(card => {
                    const title = card.querySelector('h6')?.textContent.toLowerCase() || '';
                    const message = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
                    const date = card.querySelector('small.text-muted')?.textContent.toLowerCase() || '';
                    if (title.includes(searchTerm) || message.includes(searchTerm) || date.includes(searchTerm)) {
                        card.style.display = 'block';
                        found = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                if (!found) {
                    showNoResults('No matching notifications found.');
                }
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
            
            document.getElementById('globalSearch').addEventListener('input', function() {
                if (this.value.trim() === '') {
                    clearSearchResults();
                }
            });
            
            function clearSearchResults() {
                const currentPath = window.location.pathname;
                const page = currentPath.split('/').pop();
                if (page === 'user_dashboard.php') {
                    document.querySelectorAll('.card.shadow-sm').forEach(card => card.style.display = 'block');
                } else if (page === 'events.php') {
                    document.querySelectorAll('.card.shadow').forEach(card => card.style.display = 'block');
                } else if (page === 'notifications.php') {
                    document.querySelectorAll('.card.mb-3').forEach(card => card.style.display = 'block');
                }
                const noResults = document.getElementById('no-results-message');
                if (noResults) noResults.style.display = 'none';
            }
            
            document.getElementById('globalSearch').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            function registerForEvent(eventId) {
               
                alert('Registration functionality would be implemented here.');
            }

            
            function confirmRegistration(eventId) {
               
                const modal = document.createElement('div');
                modal.className = 'modal fade show';
                modal.style.display = 'block';
                modal.style.zIndex = '9999';
                modal.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white border-0">
                                <h5 class="modal-title">
                                    <i class="bi bi-question-circle me-2"></i>
                                    Confirm Registration
                                </h5>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Are you sure you want to register for this event?</p>
                                <p class="text-muted small mt-2">This action cannot be undone.</p>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" onclick="closeModal(this)">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="processRegistration(${eventId}, this)">Confirm</button>
                            </div>
                        </div>
                    </div>
                `;
                
                
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.style.zIndex = '9998';
                
                document.body.appendChild(modal);
                document.body.appendChild(backdrop);
                document.body.style.overflow = 'hidden';
            }

            function processRegistration(eventId, button) {
               
                const btn = button;
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
                
               
                closeModal(btn);
                
              
                setTimeout(() => {
                    showSuccessNotification();
                    
                  
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Register Now';
                    }, 2000);
                }, 1000);
            }

            function showSuccessNotification() {
              
                const notification = document.createElement('div');
                notification.className = 'position-fixed top-50 start-50 translate-middle';
                notification.style.zIndex = '10000';
                notification.style.transform = 'translate(-50%, -50%) scale(0)';
                notification.style.transition = 'all 0.3s ease';
                notification.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show shadow-lg" role="alert" style="min-width: 300px; text-align: center;">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Registration Successful!</strong>
                        <br><small>You have been registered for this event.</small>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
           
                setTimeout(() => {
                    notification.style.transform = 'translate(-50%, -50%) scale(1)';
                }, 10);
                
               
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translate(-50%, -50%) scale(0.8)';
                    
                  
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 2000);
            }

            function closeModal(button) {
                const modal = button.closest('.modal');
                const backdrop = document.querySelector('.modal-backdrop');
                
                if (modal) modal.remove();
                if (backdrop) backdrop.remove();
                document.body.style.overflow = '';
            }

            function showAlreadyRegisteredNotification() {
                
                const notification = document.createElement('div');
                notification.className = 'position-fixed top-50 start-50 translate-middle';
                notification.style.zIndex = '10000';
                notification.style.transform = 'translate(-50%, -50%) scale(0)';
                notification.style.transition = 'all 0.3s ease';
                notification.innerHTML = `
                    <div class="alert alert-warning alert-dismissible fade show shadow-lg" role="alert" style="min-width: 300px; text-align: center;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Already Registered!</strong>
                        <br><small>You have already registered for this event.</small>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                
                setTimeout(() => {
                    notification.style.transform = 'translate(-50%, -50%) scale(1)';
                }, 10);
                
                
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translate(-50%, -50%) scale(0.8)';
                    
                  
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 1000);
            }
        </script>
    </body>
    </html>