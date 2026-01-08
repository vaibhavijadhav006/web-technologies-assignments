<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}
checkRole('admin');

// Get statistics
$stats = [
    'total_students' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='student'"))['count'],
    'total_mentors' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='mentor'"))['count'],
    'total_events' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events"))['count'],
    'total_registrations' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM event_registrations"))['count']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Manthan 2.0 - Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a href="../../logout.php" class="btn btn-light">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-2">
                <div class="list-group">
                    <a href="index.php" class="list-group-item list-group-item-action active">Dashboard</a>
                    <a href="view_participants.php" class="list-group-item list-group-item-action">View Participants</a>
                    <a href="view_participants.php" class="list-group-item list-group-item-action">Assign Mentors</a>
                    <a href="notifications.php" class="list-group-item list-group-item-action">Send Notifications</a>
                </div>
            </div>
            
            <div class="col-md-10">
                <h3>Dashboard Overview</h3>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Students</h5>
                                <h2><?php echo $stats['total_students']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Mentors</h5>
                                <h2><?php echo $stats['total_mentors']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Events</h5>
                                <h2><?php echo $stats['total_events']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Registrations</h5>
                                <h2><?php echo $stats['total_registrations']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Registrations -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Recent Event Registrations</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Team Name</th>
                                    <th>Event</th>
                                    <th>Team Lead</th>
                                    <th>Registered On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT er.*, e.name as event_name, u.name as lead_name 
                                          FROM event_registrations er
                                          JOIN events e ON er.event_id = e.id
                                          JOIN users u ON er.team_lead_id = u.id
                                          ORDER BY er.registered_at DESC LIMIT 10";
                                $result = mysqli_query($conn, $query);
                                
                                while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $row['team_name']; ?></td>
                                    <td><?php echo $row['event_name']; ?></td>
                                    <td><?php echo $row['lead_name']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['registered_at'])); ?></td>
                                    <td>
                                        <a href="assign_mentor.php?team_id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary">Assign Mentor</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Notifications -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Recent Notifications Sent</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $notif_query = "SELECT n.*, u.name as created_by_name 
                                    FROM notifications n
                                    JOIN users u ON n.created_by = u.id
                                    ORDER BY n.created_at DESC LIMIT 5";
                        $notif_result = mysqli_query($conn, $notif_query);
                        
                        if (mysqli_num_rows($notif_result) > 0): 
                            while($notif = mysqli_fetch_assoc($notif_result)): 
                                $badge_class = 'bg-secondary';
                                $role_display = 'All';
                                
                                // Use role column instead of notification_type
                                if (isset($notif['role'])) {
                                    switch($notif['role']) {
                                        case 'student': 
                                            $badge_class = 'bg-primary'; 
                                            $role_display = 'Students';
                                            break;
                                        case 'mentor': 
                                            $badge_class = 'bg-success'; 
                                            $role_display = 'Mentors';
                                            break;
                                        case 'admin': 
                                            $badge_class = 'bg-warning'; 
                                            $role_display = 'Admins';
                                            break;
                                        case 'all': 
                                            $badge_class = 'bg-info'; 
                                            $role_display = 'All';
                                            break;
                                        default:
                                            $badge_class = 'bg-secondary';
                                            $role_display = ucfirst($notif['role'] ?? 'Unknown');
                                    }
                                }
                                
                                // If user_id is set, it's a specific user notification
                                if (!empty($notif['user_id'])) {
                                    $badge_class = 'bg-danger';
                                    $role_display = 'Specific User';
                                }
                                ?>
                                <div class="alert alert-light border mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                            <p class="mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($notif['created_by_name']); ?> | 
                                                <?php echo date('d M Y H:i', strtotime($notif['created_at'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo $role_display; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No notifications sent yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>