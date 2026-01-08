<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php'; // NEW

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

checkRole('mentor');

$user_id = $_SESSION['user_id'];

// Get mentor details - FIXED: Using mysqli_query correctly
$mentor_query = "SELECT m.* FROM mentor_details m 
                 JOIN users u ON m.user_id = u.id 
                 WHERE m.user_id = $user_id";
$mentor_result = mysqli_query($conn, $mentor_query);

if (!$mentor_result) {
    die("Database error: " . mysqli_error($conn));
}

$mentor = mysqli_fetch_assoc($mentor_result);

if (!$mentor) {
    die("Mentor details not found!");
}

$mentor_id = $mentor['id'];

// Get teams assigned to this mentor
$teams_query = "SELECT COUNT(*) as team_count FROM event_registrations 
                WHERE mentor_id = $mentor_id";
$teams_result = mysqli_query($conn, $teams_query);
$teams = mysqli_fetch_assoc($teams_result);

// Get participants count
$participants_query = "SELECT COUNT(*) as participant_count FROM event_registrations er
                       WHERE er.mentor_id = $mentor_id";
$participants_result = mysqli_query($conn, $participants_query);
$participants = mysqli_fetch_assoc($participants_result);

// Get mentor's notifications
$notifications = getUserNotifications($user_id, 'mentor', 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            text-align: center;
        }
        .stat-card h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .notification-item {
            border-left: 4px solid #0d6efd;
            margin-bottom: 10px;
            padding: 10px;
            background: #fff;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chalkboard-teacher"></i> Mentor Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-white">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                </span>
                <a href="../../logout.php" class="btn btn-light btn-sm ms-2">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-bars"></i> Menu</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="index.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="view_teams.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users"></i> My Teams
                        </a>
                        <a href="edit_profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
                
                <!-- Mentor Info -->
                <div class="card shadow mt-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user-circle"></i> My Profile</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['name']); ?></p>
                        <p><strong>SRN:</strong> <?php echo htmlspecialchars($mentor['srn']); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($mentor['contact']); ?></p>
                        <p><strong>Semester:</strong> <?php echo htmlspecialchars($mentor['semester']); ?>th</p>
                        <a href="edit_profile.php" class="btn btn-sm btn-info mt-2">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <h3 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h3>
                
                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="stat-card" style="background: linear-gradient(45deg, #0d6efd, #0b5ed7);">
                            <i class="fas fa-users fa-3x"></i>
                            <h5>Teams Assigned</h5>
                            <h2><?php echo $teams['team_count']; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card" style="background: linear-gradient(45deg, #198754, #157347);">
                            <i class="fas fa-user-graduate fa-3x"></i>
                            <h5>Total Participants</h5>
                            <h2><?php echo ($participants['participant_count'] * 4); ?></h2>
                            <small>(<?php echo $participants['participant_count']; ?> teams × 4 members)</small>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-bell"></i> My Notifications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($notifications)): 
                            foreach($notifications as $notif): ?>
                                <div class="notification-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">
                                            <i class="fas fa-bullhorn"></i> 
                                            <?php echo htmlspecialchars($notif['title']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo formatNotificationTime($notif['created_at']); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($notif['message'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="view_notifications.php" class="btn btn-outline-warning">
                                    <i class="fas fa-list"></i> View All Notifications
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light">
                                <i class="fas fa-info-circle"></i> No notifications yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Teams -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Recently Assigned Teams</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_teams_query = "SELECT er.*, e.name as event_name, u.name as lead_name
                                              FROM event_registrations er
                                              JOIN events e ON er.event_id = e.id
                                              JOIN users u ON er.team_lead_id = u.id
                                              WHERE er.mentor_id = $mentor_id
                                              ORDER BY er.registered_at DESC LIMIT 5";
                        $recent_teams_result = mysqli_query($conn, $recent_teams_query);
                        
                        if (mysqli_num_rows($recent_teams_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Team #</th>
                                            <th>Team Name</th>
                                            <th>Event</th>
                                            <th>Team Lead</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($team = mysqli_fetch_assoc($recent_teams_result)): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo $team['team_number'] ?: 'Not assigned'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($team['team_name']); ?></td>
                                            <td><?php echo htmlspecialchars($team['event_name']); ?></td>
                                            <td><?php echo htmlspecialchars($team['lead_name']); ?></td>
                                            <td>
                                                <a href="view_teams.php?team_id=<?php echo $team['id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="view_teams.php" class="btn btn-primary">
                                    <i class="fas fa-list"></i> View All Teams
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>No teams assigned yet.</strong> 
                                Teams will be assigned by the admin. Please check back later.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="mt-5 py-3 bg-light border-top">
        <div class="container text-center">
            <p class="mb-0 text-muted">
                <i class="fas fa-copyright"></i> <?php echo date('Y'); ?> Manthan 2.0 - Mentor Portal
            </p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh notifications every 60 seconds
        setInterval(function() {
            location.reload();
        }, 60000);
    </script>
</body>
</html>