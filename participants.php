<?php
session_start();
include "db.php";

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

# Fetch all participants with their event details
$participants = $conn->query("SELECT users.name, users.email, events.title, registrations.status, registrations.attendance, registrations.registration_date
FROM registrations
JOIN users ON users.id=registrations.user_id
JOIN events ON events.id=registrations.event_id
ORDER BY registrations.registration_date DESC");

# Fetch total participants count
$totalParticipantsResult = $conn->query("SELECT COUNT(DISTINCT user_id) FROM registrations");
$totalParticipants = $totalParticipantsResult->fetch_array()[0];

# Fetch total registrations count
$totalRegistrationsResult = $conn->query("SELECT COUNT(*) FROM registrations");
$totalRegistrations = $totalRegistrationsResult->fetch_array()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants</title>
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
        .participants-card {
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .participants-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
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
        .table {
            border-radius: 12px;
            overflow: hidden;
        }
        .table th {
            background: linear-gradient(135deg, #84B179, #A2CB8B);
            color: white;
            border: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td {
            border-top: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .badge {
            background: linear-gradient(135deg, #84B179, #A2CB8B);
            color: white;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(132, 177, 121, 0.3);
        }
        .badge.bg-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .badge.bg-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        .badge.bg-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        .text-muted {
            color: #6c757d !important;
        }
        .badge.bg-primary {
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
            .btn-primary, .btn-outline-primary {
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
            <li><a href="participants.php" class="active"><i class="bi bi-people me-2"></i> Participants</a></li>
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
        <h2 class="mb-1">Participants</h2>
        <small class="text-muted">Manage event participants and their registrations</small>
    </div>

    <div style="width: 100%; max-width: 400px; margin-left: auto;">
        <!-- SEARCH BAR -->
        <div class="search-bar">
            <form onsubmit="performSearch(); return false;" style="display: inline; width: 100%;">
                <input type="text" id="globalSearch" class="search-input"
                    placeholder="Search participants...">
                <button type="button" class="search-btn" onclick="performSearch()">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>

</div>
        </div>



            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex gap-3">
                    <div class="text-center">
                        <h6 class="text-muted mb-1">Total Participants</h6>
                        <h4 class="text-primary fw-bold"><?php echo $totalParticipants; ?></h4>
                    </div>
                    <div class="text-center">
                        <h6 class="text-muted mb-1">Total Registrations</h6>
                        <h4 class="text-success fw-bold"><?php echo $totalRegistrations; ?></h4>
                    </div>
                </div>
                <div>
                    <a href="reports.php" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text me-2"></i> View Reports
                    </a>
                </div>
            </div>

            <div class="card participants-card">
                <div class="card-body p-4">
                    <h5 class="mb-3">
                        <i class="bi bi-people-fill me-2"></i>
                        Participant Registration List
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-person me-2"></i>Name</th>
                                    <th><i class="bi bi-envelope me-2"></i>Email</th>
                                    <th><i class="bi bi-calendar-event me-2"></i>Event</th>
                                    <th><i class="bi bi-check-circle me-2"></i>Status</th>
                                    <th><i class="bi bi-check-square me-2"></i>Attendance</th>
                                    <th><i class="bi bi-clock me-2"></i>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($participants->num_rows > 0){
                                    while($row = $participants->fetch_assoc()){
                                        echo "<tr>";
                                        echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong></td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                        echo "<td><span class='badge bg-" . ($row['status'] == 'confirmed' ? 'success' : 'warning') . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                                        echo "<td><span class='badge bg-" . ($row['attendance'] == 'present' ? 'success' : 'secondary') . "'>" . htmlspecialchars($row['attendance']) . "</span></td>";
                                        echo "<td><small class='text-muted'>" . date('M d, Y H:i', strtotime($row['registration_date'])) . "</small></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center text-muted py-4'>
                                            <i class='bi bi-people me-2'></i>
                                            No participants found.
                                          </td></tr>";
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
            
            function performSearch() {
                const searchTerm = document.getElementById('globalSearch').value.toLowerCase().trim();
                if (searchTerm === '') return;
                const currentPath = window.location.pathname;
                const page = currentPath.split('/').pop();
                if (page === 'participants.php') {
                    searchParticipants(searchTerm);
                } else if (page === 'admin_dashboard.php') {
                    searchAdminDashboard(searchTerm);
                }
            }
            
            function searchParticipants(searchTerm) {
                const participants = document.querySelectorAll('tbody tr');
                let found = false;
                participants.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const name = cells[0]?.textContent.toLowerCase() || '';
                    const email = cells[1]?.textContent.toLowerCase() || '';
                    const event = cells[2]?.textContent.toLowerCase() || '';
                    const status = cells[3]?.textContent.toLowerCase() || '';
                    const attendance = cells[4]?.textContent.toLowerCase() || '';
                    if (name.includes(searchTerm) || email.includes(searchTerm) || event.includes(searchTerm) || status.includes(searchTerm) || attendance.includes(searchTerm)) {
                        row.style.display = 'table-row';
                        found = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                if (!found) {
                    showNoResults('No matching records found in reports.');
                }s
            }
            
            function searchAdminDashboard(searchTerm) {
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
                if (page === 'participants.php') {
                    document.querySelectorAll('tbody tr').forEach(row => row.style.display = 'table-row');
                } else if (page === 'admin_dashboard.php') {
                    document.querySelectorAll('.card.shadow-sm').forEach(card => card.style.display = 'block');
                }
                const noResults = document.getElementById('no-results-message');
                if (noResults) noResults.style.display = 'none';
            }
            
            document.getElementById('globalSearch').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        </script>
    </body>
    </html>