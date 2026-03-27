<?php
session_start();
include "db.php";

$message = '';

if (isset($_POST['create'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['description']);
    $date = $conn->real_escape_string($_POST['date']);
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    $loc = $conn->real_escape_string($_POST['location']);
    $other_loc = $conn->real_escape_string($_POST['other_location']);
    $limit = (int)$_POST['limit'];
    
    if ($loc === 'Other' && !empty($other_loc)) {
        $loc = $other_loc;
    }
    
    $poster_name = '';
    
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "posters/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
        $poster_name = uniqid('event_poster_', true) . '.' . strtolower($file_extension);
        $upload_path = $upload_dir . $poster_name;
        
        if (move_uploaded_file($_FILES['poster']['tmp_name'], $upload_path)) {
            // File uploaded successfully
        } else {
            $message = "<div class='alert alert-danger'>Error uploading poster. Please try again.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Please select a valid poster file.</div>";
    }
    
    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO events(title, description, event_date, start_time, end_time, location, participant_limit, poster) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $title, $desc, $date, $start_time, $end_time, $loc, $limit, $poster_name);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Event Created Successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error creating event: " . $stmt->error . "</div>";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
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
        .form-card {
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .badge {
            background: linear-gradient(135deg, #84B179, #A2CB8B);
            color: white;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(132, 177, 121, 0.3);
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
            background: linear-gradient(135deg, #e9ecef, #dee2e6);
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #dee2e6, #ced4da);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: #212529;
            border-color: rgba(255, 255, 255, 0.5);
        }
        .button-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .button-group .btn {
            flex: 1;
            min-width: 140px;
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
        .alert-danger {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(176, 42, 56, 0.1));
            border-left: 4px solid #dc3545;
            color: #721c24;
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
            <li><a href="create_event.php" class="active"><i class="bi bi-calendar-plus me-2"></i> Create Event</a></li>
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
        <h2 class="mb-1">Create Event</h2>
        <small class="text-muted">Add new events to your event management system</small>
    </div>

    

</div>
        </div>

            <?php if (!empty($message)) echo $message; ?>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card form-card">
                        <div class="card-body p-4">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Event Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter event title" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea name="description" class="form-control" rows="4" placeholder="Describe the event" required></textarea>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Event Date</label>
                                        <input type="date" name="date" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Location</label>
                                        <select name="location" id="location" class="form-select" onchange="toggleOtherLocation()" required>
                                            <option value="">Select a location</option>
                                            <option value="Barangay 171 Hall">Barangay 171 Hall</option>
                                            <option value="Caloocan Sports Complex">Caloocan Sports Complex</option>
                                            <option value="Solar Urban Homes North">Solar Urban Homes North</option>
                                            <option value="Deparo Subdivision">Deparo Subdivision</option>
                                            <option value="Multi-Purpose Hall - Natividad Phase II">Multi-Purpose Hall - Natividad Phase II</option>
                                            <option value="Rainbow Village 5 Phase 2 Multi-Purpose Hall">Rainbow Village 5 Phase 2 Multi-Purpose Hall</option>
                                            <option value="Caloocan City Hall">Caloocan City Hall</option>
                                            <option value="Congress Village">Congress Village</option>
                                            <option value="Caloocan City College">Caloocan City College</option>
                                            <option value="Barangay 168 Covered Court">Barangay 168 Covered Court</option>
                                            <option value="Other">Other (Please specify)</option>
                                        </select>
                                        <div id="other-location-field" style="display: none; margin-top: 5px; padding: 5px; background: rgba(132, 177, 121, 0.08);">
                                            <input type="text" name="other_location" id="other_location_input" class="form-control" placeholder="Enter your custom location (e.g., Community Center, Parish Hall)" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Start Time</label>
                                        <input type="time" name="start_time" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">End Time</label>
                                        <input type="time" name="end_time" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Participant Limit</label>
                                    <input type="number" name="limit" class="form-control" placeholder="Maximum participants" min="1" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Event Poster</label>
                                    <input type="file" name="poster" class="form-control" accept="image/*" required>
                                    <small class="text-muted">Upload an image for the event poster</small>
                                </div>
                                <div class="button-group">
                                    <button type="submit" class="btn btn-primary" name="create">
                                        <i class="bi bi-plus-circle me-2"></i>Create Event
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='admin_dashboard.php';">
                                        <i class="bi bi-x-circle me-2"></i>Cancel
                                    </button>
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
                if (page === 'create_event.php') {
                    searchCreateEvent(searchTerm);
                } else if (page === 'admin_dashboard.php') {
                    searchAdminDashboard(searchTerm);
                }
            }
            
            function searchCreateEvent(searchTerm) {
                const formFields = document.querySelectorAll('.form-control, .form-select');
                let found = false;
                formFields.forEach(field => {
                    const label = field.previousElementSibling?.textContent.toLowerCase() || '';
                    if (label.includes(searchTerm)) {
                        field.style.borderColor = '#84B179';
                        field.style.boxShadow = '0 0 0 3px rgba(132, 177, 121, 0.2)';
                        found = true;
                    } else {
                        field.style.borderColor = '#e9ecef';
                        field.style.boxShadow = 'none';
                    }
                });
                if (!found) {
                    showNoResults('No matching form fields found.');
                }
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
                if (page === 'create_event.php') {
                    document.querySelectorAll('.form-control, .form-select').forEach(field => {
                        field.style.borderColor = '#e9ecef';
                        field.style.boxShadow = 'none';
                    });
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

            function toggleOtherLocation() {
                const locationSelect = document.getElementById('location');
                const otherLocationField = document.getElementById('other-location-field');
                const otherLocationInput = document.getElementById('other_location_input');

                if (locationSelect && otherLocationField && otherLocationInput) {
                    if (locationSelect.value === 'Other') {
                        otherLocationField.style.display = 'block';
                        otherLocationInput.required = true;
                        otherLocationInput.focus();
                    } else {
                        otherLocationField.style.display = 'none';
                        otherLocationInput.required = false;
                        otherLocationInput.value = '';
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const locationSelect = document.getElementById('location');
                const otherLocationField = document.getElementById('other-location-field');
                const otherLocationInput = document.getElementById('other_location_input');

                if (locationSelect && otherLocationField && otherLocationInput) {
                    locationSelect.addEventListener('change', toggleOtherLocation);
                }
                
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        if (locationSelect.value === 'Other' && !otherLocationInput.value.trim()) {
                            e.preventDefault();
                            alert('Please enter a specific location when selecting "Other"');
                            otherLocationInput.focus();
                        }
                    });
                }
            });
        </script>
    </body>
    </html>