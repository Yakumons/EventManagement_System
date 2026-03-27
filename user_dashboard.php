<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$total_events_query = "SELECT COUNT(*) as count FROM events";
$total_events_result = $conn->query($total_events_query);
$total_events = $total_events_result->fetch_assoc()['count'];

$registered_events_query = "SELECT COUNT(*) as count FROM registrations WHERE user_id = ?";
$stmt = $conn->prepare($registered_events_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$registered_events_result = $stmt->get_result();
$registered_events = $registered_events_result->fetch_assoc()['count'];

$upcoming_events_query = "SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()";
$upcoming_events_result = $conn->query($upcoming_events_query);
$upcoming_events = $upcoming_events_result->fetch_assoc()['count'];

// Build query for recent events (search is client-side)
$recent_events_query = "SELECT * FROM events ORDER BY event_date DESC LIMIT 5";
$recent_events = $conn->query($recent_events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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
            position: relative;
            width: 100%;
            max-width: 420px;
            margin-left: auto;
        }
        .search-bar {
            position: relative;
        }
        .search-input {
            border: 2px solid #FFA95A;
            border-radius: 50px;
            padding: 12px 60px 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(255, 169, 90, 0.2);
        }
        .search-input:focus {
            outline: none;
            border-color: #FF8B5A;
            box-shadow: 0 0 0 3px rgba(255, 169, 90, 0.2), 0 4px 15px rgba(255, 169, 90, 0.3);
        }
        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
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
            <small>User Panel</small>
        </div>
        <ul>
            <li><a href="user_dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
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
        <h2 class="mb-1">User Dashboard</h2>
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
           
        <div class="row g-4">
            <div class="col-md-4">
                    <div class="card shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title text-dark fw-bold">Total Events</h6>
                                    <h3 class="text-primary fw-bold"><?php echo $total_events; ?></h3>
                                    <small class="text-muted">Available events</small>
                                </div>
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="bi bi-calendar-event text-primary" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title text-dark fw-bold">Registered Events</h6>
                                    <h3 class="text-warning fw-bold"><?php echo $registered_events; ?></h3>
                                    <small class="text-muted">Your registrations</small>
                                </div>
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                    <i class="bi bi-check-circle text-warning" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title text-dark fw-bold">Upcoming Events</h6>
                                    <h3 class="text-success fw-bold"><?php echo $upcoming_events; ?></h3>
                                    <small class="text-muted">Events you can join</small>
                                </div>
                                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="bi bi-clock text-success" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-3">
                <div class="col-md-8">
                    <div class="card shadow-sm rounded-3">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0">Recent Events</h5>
                        </div>
                        <div class="card-body">
                            <?php if($recent_events->num_rows > 0): ?>
                                <?php while($event = $recent_events->fetch_assoc()): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></small>
                                        </div>
                                        <div>
                                            <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-event" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No recent events available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-3">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="events.php" class="btn btn-primary">
                                    <i class="bi bi-calendar-event me-2"></i>Browse Events
                                </a>
                                <a href="my_qr.php" class="btn btn-primary">
                                    <i class="bi bi-qr-code me-2"></i>My QR Code
                                </a>
                                <a href="notifications.php" class="btn btn-primary">
                                    <i class="bi bi-bell me-2"></i>Notifications
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>

        <script>
            // Mobile menu toggle
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('active');
            }
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const mobileMenuBtn = document.getElementById('mobileMenuBtn');
                
                if (window.innerWidth <= 991) {
                    if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });
            
            // Responsive sidebar handling
            function handleResize() {
                const sidebar = document.getElementById('sidebar');
                const mobileMenuBtn = document.getElementById('mobileMenuBtn');
                
                if (window.innerWidth <= 991) {
                    mobileMenuBtn.classList.remove('d-none');
                    sidebar.classList.add('mobile');
                    sidebar.classList.remove('active');
                } else {
                    mobileMenuBtn.classList.add('d-none');
                    sidebar.classList.remove('mobile', 'active');
                }
            }
            
            // Initialize on load
            window.addEventListener('load', handleResize);
            window.addEventListener('resize', handleResize);

            // Search functionality
            function showNoResults(message) {
                let noResults = document.getElementById('no-results-message');
                const recentEventsCard = document.querySelector('.col-md-8 .card-body');
                if (recentEventsCard) {
                    if (!noResults) {
                        noResults = document.createElement('div');
                        noResults.id = 'no-results-message';
                        noResults.className = 'alert alert-info mt-3';
                        noResults.innerHTML = `<i class="bi bi-info-circle me-2"></i>${message}`;
                        recentEventsCard.appendChild(noResults);
                    } else {
                        noResults.style.display = 'block';
                    }
                }
            }

            function performSearch() {
                const searchTerm = document.getElementById('globalSearch').value.toLowerCase().trim();
                if (searchTerm === '') {
                    clearSearchResults();
                    return;
                }
                searchRecentEvents(searchTerm);
            }

            function searchRecentEvents(searchTerm) {
                const recentEventsCard = document.querySelector('.col-md-8 .card-body');
                if (!recentEventsCard) return;
                
                const eventRows = recentEventsCard.querySelectorAll('.d-flex.justify-content-between.align-items-center.mb-3');
                let found = false;
                
                // Clear any existing no results message first
                const existingNoResults = document.getElementById('no-results-message');
                if (existingNoResults) {
                    existingNoResults.style.display = 'none';
                }
                
                eventRows.forEach(row => {
                    const title = row.querySelector('h6');
                    const dateText = row.querySelector('small');
                    const titleContent = title?.textContent.toLowerCase() || '';
                    const dateContent = dateText?.textContent.toLowerCase() || '';
                    
                    if (titleContent.includes(searchTerm) || dateContent.includes(searchTerm)) {
                        row.style.display = 'flex';
                        row.classList.remove('d-none');
                        found = true;
                    } else {
                        row.style.display = 'none';
                        row.classList.add('d-none');
                    }
                });
                
                if (!found) {
                    showNoResults('No events found matching "' + searchTerm + '". Try searching by event title or date.');
                }
            }

            function clearSearchResults() {
                const recentEventsCard = document.querySelector('.col-md-8 .card-body');
                if (recentEventsCard) {
                    recentEventsCard.querySelectorAll('.d-flex.justify-content-between.align-items-center.mb-3').forEach(row => {
                        row.style.display = 'flex';
                        row.classList.remove('d-none');
                    });
                    const noResults = document.getElementById('no-results-message');
                    if (noResults) {
                        noResults.style.display = 'none';
                    }
                }
            }

            document.getElementById('globalSearch').addEventListener('input', function() {
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
