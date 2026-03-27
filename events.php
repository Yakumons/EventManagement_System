<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

$filter = isset($_GET['filter']) ? $_GET['filter'] : "all";
$today = date("Y-m-d");

// Build base query (only filter by available/ended, search is client-side)
$whereClause = "";
if ($filter == "available") {
    $whereClause = "WHERE event_date >= '$today'";
} elseif ($filter == "ended") {
    $whereClause = "WHERE event_date < '$today'";
}

$query = "SELECT * FROM events $whereClause ORDER BY event_date ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="shortcut icon" href="nexuslogo.png" type="image/x-icon">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    overflow-x: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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

.mobile-menu-btn {
    display: none !important;
    position: fixed !important;
    top: 12px !important;
    left: 12px !important;
    z-index: 1100 !important;
    width: 44px !important;
    height: 44px !important;
    padding: 0.35rem !important;
    border-radius: 8px !important;
    font-size: 1.2rem !important;
    background: linear-gradient(135deg, #FF8B5A, #FF5A5A) !important;
    border: none !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(255, 90, 90, 0.3) !important;
}

.mobile-menu-btn:hover {
    box-shadow: 0 6px 20px rgba(255, 90, 90, 0.5) !important;
}

.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 10px;
    overflow-y: auto;
    overflow-x: hidden;
    transition: margin-left 0.3s ease;
    box-sizing: border-box;
}

.search-container {
    position: relative;
    width: 100%;
    max-width: 450px;
}

.search-bar {
    position: relative;
}

.search-input {
    border: 2px solid #FFA95A;
    border-radius: 50px;
    padding: 12px 50px 12px 20px;
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
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 90, 90, 0.4);
    cursor: pointer;
    padding: 0;
}

.search-btn:hover {
    background: linear-gradient(135deg, #FF5A5A, #FF8B5A);
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 6px 20px rgba(255, 90, 90, 0.6);
}

.page-header {
    margin-bottom: 20px;
    padding: 10px 0;
}

.page-header h2 {
    font-size: 1.75rem;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 5px;
}

.page-header small {
    color: #6c757d;
    font-size: 0.95rem;
}

.filter-buttons {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-btn {
    border-radius: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.3);
    padding: 10px 16px;
    font-size: 0.85rem;
}

.btn-dark {
    background: linear-gradient(135deg, #FF8B5A, #FF5A5A);
    border: none;
    color: white;
    box-shadow: 0 4px 15px rgba(255, 90, 90, 0.3);
}

.btn-dark:hover {
    background: linear-gradient(135deg, #FF5A5A, #FF8B5A);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 90, 90, 0.5);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #FF8B5A, #FF5A5A);
    border: none;
    color: white;
    box-shadow: 0 4px 15px rgba(255, 90, 90, 0.3);
}

.btn-success:hover {
    background: linear-gradient(135deg, #FF5A5A, #FF8B5A);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 90, 90, 0.5);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #FFA95A, #FFD45A);
    border: none;
    color: #2c3e50;
    box-shadow: 0 4px 15px rgba(255, 169, 90, 0.3);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #FFD45A, #FFA95A);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 169, 90, 0.5);
    color: #2c3e50;
}

.event-card {
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: none;
    transition: all 0.3s ease;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.event-poster {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.event-poster:hover {
    transform: scale(1.02);
}

.event-card .card-body {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    padding: 15px;
}

.event-card .card-title {
    font-weight: 800;
    color: #2c3e50;
    letter-spacing: 0.5px;
    font-size: 1.05rem;
    margin-bottom: 8px;
    line-height: 1.3;
}

.event-card .card-text {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 4px 0;
}

.event-date {
    font-weight: 700;
    color: #FF8B5A;
}

.event-location {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
}

.progress {
    height: 8px;
    border-radius: 4px;
    background-color: #e9ecef;
    margin: 10px 0;
}

.progress-bar {
    transition: width 0.6s ease;
    background: linear-gradient(90deg, #FF8B5A, #FF5A5A);
}

.progress-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.countdown {
    color: #FF5A5A;
    font-weight: 700;
    font-size: 0.85rem;
    margin: 8px 0;
}

.event-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 8px 12px;
    font-size: 0.85rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 100px;
    white-space: nowrap;
}

.btn-info {
    background: linear-gradient(135deg, #FFA95A, #FFD45A);
    border: none;
    color: #2c3e50;
    box-shadow: 0 4px 15px rgba(255, 169, 90, 0.3);
    font-weight: 700;
}

.btn-info:hover {
    background: linear-gradient(135deg, #FFD45A, #FFA95A);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 169, 90, 0.5);
    color: #2c3e50;
}

.badge {
    background: linear-gradient(135deg, #FF8B5A, #FF5A5A);
    color: white;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(255, 90, 90, 0.3);
    font-size: 0.8rem;
    white-space: nowrap;
}

.alert {
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.alert-warning {
    background: linear-gradient(135deg, rgba(255, 169, 90, 0.1), rgba(255, 212, 90, 0.1));
    border-left: 4px solid #FFD45A;
    color: #6c757d;
}

/* Responsive Styles */
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
    <button class="btn btn-primary mobile-menu-btn d-none" onclick="toggleSidebar()" id="mobileMenuBtn" aria-label="Open navigation">
        <i class="bi bi-list"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header text-center">
            <img src="nexuslogo.png" alt="Logo" class="sidebar-logo">
            <h3>Nexus Events</h3>
            <small>User Panel</small>
        </div>
        <ul>
            <li><a href="user_dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="events.php" class="active"><i class="bi bi-calendar-event me-2"></i> Events</a></li>
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
        <h2 class="mb-1">Events</h2>
        <small class="text-muted">Explore and register for upcoming events</small>
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
            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <a href="?filter=all" class="btn filter-btn btn-dark">All Events</a>
                <a href="?filter=available" class="btn filter-btn btn-success">Available</a>
                <a href="?filter=ended" class="btn filter-btn btn-secondary">Ended</a>
            </div>

            <!-- Events Grid -->
            <div class="row g-3">
                <?php if($result && $result->num_rows > 0) { 
                    while($row = $result->fetch_assoc()){ 
                        $event_id = $row['id'];
                        $stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM registrations WHERE event_id=?");
                        $stmtCount->bind_param("i", $event_id);
                        $stmtCount->execute();
                        $count = $stmtCount->get_result()->fetch_assoc()['total'];
                        $is_full = $count >= $row['participant_limit'];
                        $is_ended = $row['event_date'] < $today;
                        $percent = min(100, round(($count / $row['participant_limit']) * 100));
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
                    <div class="card event-card w-100">
                        <img src="posters/<?php echo htmlspecialchars($row['poster']); ?>" class="card-img-top event-poster" alt="<?php echo htmlspecialchars($row['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                            
                            <p class="card-text event-date">
                                <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($row['event_date'])); ?>
                            </p>
                            
                            <p class="card-text event-location">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars(substr($row['location'], 0, 30)); ?>
                            </p>

                            <!-- Capacity Progress -->
                            <div class="progress-text">
                                <small><?php echo $percent; ?>% Full (<?php echo $count; ?>/<?php echo $row['participant_limit']; ?>)</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $percent; ?>%"></div>
                            </div>

                            <!-- Countdown -->
                            <p id="countdown<?php echo $row['id']; ?>" class="countdown"></p>

                            <script>
                            (function() {
                                let eventDate = new Date("<?php echo $row['event_date']; ?>T00:00:00Z").getTime();
                                let countdownId = "countdown<?php echo $row['id']; ?>";
                                
                                function updateCountdown() {
                                    let now = new Date().getTime();
                                    let distance = eventDate - now;
                                    
                                    if(distance < 0){
                                        document.getElementById(countdownId).innerHTML = "<i class='bi bi-check-circle'></i> Event Started";
                                        return;
                                    }
                                    
                                    let days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                    let hours = Math.floor((distance % (1000*60*60*24)) / (1000*60*60));
                                    
                                    if(days > 0) {
                                        document.getElementById(countdownId).innerHTML = days + "d " + hours + "h left";
                                    } else {
                                        document.getElementById(countdownId).innerHTML = hours + "h left";
                                    }
                                }
                                
                                updateCountdown();
                                setInterval(updateCountdown, 60000); // Update every minute
                            })();
                            </script>

                            <!-- Action Buttons -->
                            <div class="event-actions">
                                <a href="event_details.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                    <i class="bi bi-info-circle me-1"></i>Details
                                </a>
                                
                                <?php 
                                $stmtCheck = $conn->prepare("SELECT COUNT(*) AS total FROM registrations WHERE event_id = ? AND user_id = ?");
                                $stmtCheck->bind_param("ii", $event_id, $user_id);
                                $stmtCheck->execute();
                                $already_registered = $stmtCheck->get_result()->fetch_assoc()['total'] > 0;
                                
                                if($already_registered){ ?>
                                    <button type="button" class="btn btn-success btn-sm" onclick="showAlreadyRegisteredNotification()">
                                        <i class="bi bi-check-circle me-1"></i>Registered
                                    </button>
                                <?php } elseif($is_full){ ?>
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="bi bi-exclamation-circle me-1"></i>Full
                                    </button>
                                <?php } elseif($is_ended){ ?>
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="bi bi-clock-history me-1"></i>Ended
                                    </button>
                                <?php } else { ?>
                                    <a href="register_event.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i>Register
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center py-4">
                            <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No events found. Check back later!</p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <script>
        // Sidebar Toggle
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

        // Handle responsive behavior
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

        window.addEventListener('resize', handleResize);
        window.addEventListener('load', handleResize);

        // Search functionality
        function showNoResults(message) {
            let noResults = document.getElementById('no-results-message');
            const mainContent = document.getElementById('mainContent');
            if (mainContent) {
                if (!noResults) {
                    noResults = document.createElement('div');
                    noResults.id = 'no-results-message';
                    noResults.className = 'alert alert-info mt-3';
                    noResults.innerHTML = `<i class="bi bi-info-circle me-2"></i>${message}`;
                    mainContent.appendChild(noResults);
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
            searchEvents(searchTerm);
            document.getElementById('mainContent').scrollTop = 0;
        }

        function searchEvents(searchTerm) {
            const eventCards = document.querySelectorAll('.row.g-3 > div[class*="col-"]');
            let found = false;
            
            // Clear any existing no results message first
            const existingNoResults = document.getElementById('no-results-message');
            if (existingNoResults) {
                existingNoResults.style.display = 'none';
            }
            
            eventCards.forEach(card => {
                const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
                const dateText = card.querySelector('.bi-calendar3')?.parentElement?.textContent.toLowerCase() || '';
                const locationText = card.querySelector('.bi-geo-alt')?.parentElement?.textContent.toLowerCase() || '';
                
                if (title.includes(searchTerm) || dateText.includes(searchTerm) || locationText.includes(searchTerm)) {
                    card.style.display = 'flex';
                    card.classList.remove('d-none');
                    found = true;
                } else {
                    card.style.display = 'none';
                    card.classList.add('d-none');
                }
            });
            
            if (!found) {
                showNoResults('No events found matching "' + searchTerm + '". Try searching by event title, date, or location.');
            }
        }

        function clearSearchResults() {
            document.querySelectorAll('.row.g-3 > div[class*="col-"]').forEach(card => {
                card.style.display = 'flex';
                card.classList.remove('d-none');
            });
            const noResults = document.getElementById('no-results-message');
            if (noResults) {
                noResults.style.display = 'none';
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

        // Already registered notification
        function showAlreadyRegisteredNotification() {
            const notification = document.createElement('div');
            notification.className = 'position-fixed top-50 start-50 translate-middle';
            notification.style.zIndex = '10000';
            notification.style.transform = 'translate(-50%, -50%) scale(0)';
            notification.style.transition = 'all 0.3s ease';
            notification.innerHTML = `
                <div class="alert alert-warning alert-dismissible fade show shadow-lg" role="alert" style="min-width: 280px; text-align: center; max-width: 90vw;">
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
            }, 2000);
        }
    </script>
</body>
</html>