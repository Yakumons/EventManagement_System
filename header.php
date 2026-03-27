<?php
include "navbar_admin.php";
include "db.php";

# summary statistics
$todayDate = date('Y-m-d H:i:s');
$totalEvents = $conn->query("SELECT COUNT(*) AS count FROM events")->fetch_assoc()['count'];
$totalParticipants = $conn->query("SELECT COUNT(*) AS count FROM registrations")->fetch_assoc()['count'];
$totalAnnouncements = $conn->query("SELECT COUNT(*) AS count FROM announcements")->fetch_assoc()['count'];

$upcomingEvents = $conn->query("SELECT * FROM events WHERE event_date >= '$todayDate' ORDER BY event_date ASC");
$upcomingCount = $upcomingEvents->num_rows;

# get PAST/HISTORY events
$pastEvents = $conn->query("SELECT * FROM events WHERE event_date < '$todayDate' ORDER BY event_date DESC");
$pastCount = $pastEvents->num_rows;

# get ALL events
$allEvents = $conn->query("SELECT * FROM events ORDER BY event_date DESC");

# helper to compute registration ratio for event
function eventStatus($conn, $eventId, $limit){
    $registered = $conn->query("SELECT COUNT(*) AS count FROM registrations WHERE event_id='$eventId'")->fetch_assoc()['count'];
    $percent = $limit > 0 ? round(($registered/$limit)*100) : 0;
    $capacityTxt = "{$registered}/{$limit}";
    $progressTxt = "{$percent}%";
    $regStatus = $percent >= 100 ? 'Full' : 'Open';
    $badge = $percent >= 100 ? 'danger' : 'success';
    return [$capacityTxt, $progressTxt, $regStatus, $badge, $registered];
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Admin Dashboard</h2>
            <small class="text-muted">Overview of your event management system</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-primary"><?php echo date('F d, Y'); ?></span>
        </div>
    </div>

            <?php if(isset($_GET['message']) && !empty($_GET['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card shadow-sm rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Total Events</small>
                            <i class="bi bi-calendar-event-fill fs-4 text-primary"></i>
                        </div>
                        <h3 class="mb-0"><?php echo $totalEvents; ?></h3>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card shadow-sm rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Total Participants</small>
                            <i class="bi bi-person-check-fill fs-4 text-success"></i>
                        </div>
                        <h3 class="mb-0"><?php echo $totalParticipants; ?></h3>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card shadow-sm rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Announcements</small>
                            <i class="bi bi-megaphone-fill fs-4 text-warning"></i>
                        </div>
                        <h3 class="mb-0"><?php echo $totalAnnouncements; ?></h3>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card shadow-sm rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Upcoming Events</small>
                            <i class="bi bi-rocket-fill fs-4 text-danger"></i>
                        </div>
                        <h3 class="mb-0"><?php echo $upcomingCount; ?></h3>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Upcoming Events</h5>
                    <small class="text-success">Track event schedule, participant counts, and capacity status</small>
                </div>
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
                                    echo "<td>{$eventRow['title']}</td>";
                                    echo "<td>{$eventDateFormatted}</td>";
                                    echo "<td>{$eventRow['location']}</td>";
                                    echo "<td>{$capacityTxt}</td>";
                                    echo "<td>{$regStatus}</td>";
                                    echo "<td>{$progressTxt}</td>";
                                    echo "<td><span class='badge bg-{$badge}'>Upcoming</span></td>";
                                    echo "<td>
                                            <a href='edit_event.php?id={$eventRow['id']}' class='btn btn-sm btn-primary action-btn me-1'>Edit</a>
                                            <a href='delete_event.php?id={$eventRow['id']}' onclick='return confirm("Are you sure you want to delete this data?")' class='btn btn-sm btn-danger action-btn'>Delete</a>
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

            <div class="card shadow-sm rounded-3 p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">All Events</h5>
                    <small class="text-success">Complete list of all events in the system</small>
                </div>
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
                                        : "<a href='edit_event.php?id={$eventRow['id']}' class='btn btn-sm btn-primary action-btn me-1'>Edit</a> <a href='delete_event.php?id={$eventRow['id']}' onclick='return confirm(\"Are you sure you want to delete this data?\")' class='btn btn-sm btn-danger action-btn'>Delete</a>";
                                    echo "<tr>";
                                    echo "<td><strong>{$eventRow['title']}</strong></td>";
                                    echo "<td>{$eventDateFormatted}</td>";
                                    echo "<td>{$eventRow['location']}</td>";
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
<br>
            <div class="card shadow-sm rounded-3 p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Event History</h5>
                    <small class="text-secondary">Past events and their final attendance records</small>
                </div>
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
                                    echo "<td><strong>{$eventRow['title']}</strong></td>";
                                    echo "<td>{$eventDateFormatted}</td>";
                                    echo "<td>{$eventRow['location']}</td>";
                                    echo "<td>{$capacityTxt}</td>";
                                    echo "<td>{$regStatus}</td>";
                                    echo "<td>{$progressTxt}</td>";
                                    echo "<td><span class='badge bg-secondary'>Completed</span></td>";
                                    echo "</tr>";
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
</div>

<?php include "footer.php"; ?>
