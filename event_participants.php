<?php
include "header.php";
include "db.php";


$today = date("Y-m-d");


$events = $conn->query("SELECT * FROM events");
?>
<?php

if(isset($_GET['event_id'])){

$event_id = $_GET['event_id'];


$event_q = $conn->query("SELECT * FROM events WHERE id='$event_id'");
$event_data = $event_q->fetch_assoc();


$sql = "SELECT registrations.id AS reg_id, users.name, users.email, registrations.status, registrations.attendance
FROM registrations
JOIN users ON registrations.user_id = users.id
WHERE registrations.event_id='$event_id'";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="d-flex" style="margin: 0; padding: 0;">
    <!-- Sidebar -->
    <div class="bg-dark text-white" style="position: sticky; top: 0; height: 100vh; width: 280px; overflow-y: auto; flex-shrink: 0; margin: 0; padding: 0;">
        <div class="p-4 text-center" style="background: linear-gradient(180deg, #3268e6, #111827);">
            <h3 class="fw-bold mb-1">Nexus Events</h3>
            <small class="text-secondary">Admin Panel</small>
        </div>
        <ul class="nav flex-column p-3 gap-2">
            <li class="nav-item">
                <a class="nav-link text-white" href="admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="create_event.php"><i class="bi bi-calendar-plus me-2"></i> Create Event</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white bg-success bg-opacity-25 rounded" href="participants.php"><i class="bi bi-people me-2"></i> Participants</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="announcements.php"><i class="bi bi-megaphone me-2"></i> Announcements</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="scan_qr.php"><i class="bi bi-qr-code-scan me-2"></i> Scan QR</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="reports.php"><i class="bi bi-bar-chart-line me-2"></i> Reports</a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-white bg-danger bg-opacity-25 rounded" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 bg-light p-4" style="margin: 0; padding: 0; min-height: 100vh;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Event Participants</h2>
                    <small class="text-muted">Participants for: <strong><?php echo isset($event_data) ? $event_data['title'] : 'Select an event'; ?></strong></small>
                </div>
                <a href="participants.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Events
                </a>
            </div>

            <?php if(isset($event_data)){ ?>
            <div class="card shadow-sm rounded-3 p-3 mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted">Schedule</small>
                        <p class="mb-0"><?php echo date('M d, Y', strtotime($event_data['event_date'])); ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Location</small>
                        <p class="mb-0"><?php echo $event_data['location']; ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Participant Limit</small>
                        <p class="mb-0"><?php echo $event_data['participant_limit']; ?></p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Total Registered</small>
                        <p class="mb-0"><?php echo $result->num_rows; ?></p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 p-3">
                <h5 class="mb-3">Participant List</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Attendance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $result = $conn->query($sql);
                            while($row = $result->fetch_assoc()){ ?>
                            <tr>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td>
                                    <?php 
                                    if($row['status']=="approved"){
                                        echo "<span class='badge bg-success'>Approved</span>";
                                    }elseif($row['status']=="rejected"){
                                        echo "<span class='badge bg-danger'>Rejected</span>";
                                    }else{
                                        echo "<span class='badge bg-warning'>Pending</span>";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['attendance'] == 'present' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($row['attendance']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($row['status']=="pending"){ ?>
                                    <a href="approve.php?id=<?php echo $row['reg_id']; ?>&event_id=<?php echo $event_id; ?>" class="btn btn-success btn-sm">Approve</a>
                                    <a href="reject.php?id=<?php echo $row['reg_id']; ?>&event_id=<?php echo $event_id; ?>" class="btn btn-danger btn-sm">Reject</a>
                                    <?php } else { ?>
                                    <span class="text-muted">—</span>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>

<?php } ?>

<?php include "footer.php"; ?>
