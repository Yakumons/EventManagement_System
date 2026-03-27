<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "user"){
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="shortcut icon" href="nexuslogo.png" type="image/x-icon">
<style>
body{
    background: #f8f9fa;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
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
.sidebar-header {
    padding: 25px;
    background: rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
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
    padding: 20px;
    overflow-y: auto;
    transition: margin-left 0.3s ease;
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
}
.dashboard-card{
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}
</style>
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
}
.dashboard-card{
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<button class="btn btn-primary rounded-circle mobile-menu-btn d-none" onclick="toggleSidebar()" id="mobileMenuBtn">
    <i class="bi bi-list"></i>
</button>

<button class="btn btn-primary rounded-circle position-fixed" style="top: 20px; right: 340px; z-index: 999; width: 50px; height: 50px;" onclick="toggleSearch()" id="searchToggleBtn">
    <i class="bi bi-search"></i>
</button>

<div class="search-container" id="searchContainer" style="display: none;">
    <div class="search-bar position-relative">
        <input type="text" id="globalSearch" class="form-control search-input" placeholder="Search events, announcements...">
        <button class="search-btn" onclick="performSearch()">
            <i class="bi bi-search"></i>
        </button>
    </div>
</div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Event System</h3>
        <small>User Navigation</small>
    </div>
    <ul>
        <li><a href="user_dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
        <li><a href="events.php"><i class="bi bi-calendar-event me-2"></i> Events</a></li>
        <li><a href="my_qr.php"><i class="bi bi-qr-code me-2"></i> My QR Code</a></li>
        <li><a href="notifications.php"><i class="bi bi-bell me-2"></i> Notifications</a></li>
        <li class="logout-link"><a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content" id="mainContent">
    
</div>

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
</script>
</body>
</html>
