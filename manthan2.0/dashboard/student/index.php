<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php'; // NEW

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$user_query = "SELECT u.*, s.school, s.standard 
               FROM users u 
               LEFT JOIN student_details s ON u.id = s.user_id 
               WHERE u.id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

// Get upcoming events
$events_query = "SELECT * FROM events WHERE status = 'upcoming' ORDER BY date";
$events_result = mysqli_query($conn, $events_query);

// Get user's registered events with TEAM NAME
$registered_events_query = "SELECT e.*, er.team_name, er.team_number, er.competition_id, e.event_type
                           FROM events e
                           JOIN event_registrations er ON e.id = er.event_id
                           WHERE er.team_lead_id = $user_id";
$registered_events_result = mysqli_query($conn, $registered_events_query);
$registered_events = [];
$team_details = [];
$ideathon_registered = false;
$ideathon_team_info = null;
$hackathon_event_id = null;

while($row = mysqli_fetch_assoc($registered_events_result)) {
    $registered_events[] = $row['id'];
    $team_details[$row['id']] = [
        'team_name' => $row['team_name'],
        'team_number' => $row['team_number'],
        'competition_id' => $row['competition_id']
    ];
    
    // Check if Ideathon is registered
    if ($row['event_type'] == 'ideathon') {
        $ideathon_registered = true;
        $ideathon_team_info = [
            'team_name' => $row['team_name'],
            'team_number' => $row['team_number'],
            'competition_id' => $row['competition_id']
        ];
    }
}

// If Ideathon is registered, automatically create/check Hackathon registration
if ($ideathon_registered && $ideathon_team_info) {
    // Get Hackathon event ID
    $hackathon_query = "SELECT id FROM events WHERE event_type = 'hackathon' LIMIT 1";
    $hackathon_result = mysqli_query($conn, $hackathon_query);
    if ($hackathon_result && mysqli_num_rows($hackathon_result) > 0) {
        $hackathon_event = mysqli_fetch_assoc($hackathon_result);
        $hackathon_event_id = $hackathon_event['id'];
        
        // Check if Hackathon registration already exists
        $hackathon_check = "SELECT * FROM event_registrations 
                          WHERE team_lead_id = $user_id AND event_id = {$hackathon_event_id}";
        $hackathon_check_result = mysqli_query($conn, $hackathon_check);
        
        if (mysqli_num_rows($hackathon_check_result) == 0) {
            // Auto-create Hackathon registration with Ideathon team details
            $ideathon_details_query = "SELECT * FROM event_registrations er
                                     JOIN events e ON er.event_id = e.id
                                     WHERE er.team_lead_id = $user_id 
                                     AND e.event_type = 'ideathon'
                                     LIMIT 1";
            $ideathon_details_result = mysqli_query($conn, $ideathon_details_query);
            $ideathon_details = mysqli_fetch_assoc($ideathon_details_result);
            
            if ($ideathon_details) {
                $auto_register_query = "INSERT INTO event_registrations 
                                       (event_id, competition_id, team_name, title, team_lead_id, 
                                        team_member1, team_member2, team_member3, 
                                        standard, contact_email) 
                                       VALUES ({$hackathon_event_id}, '{$ideathon_details['competition_id']}', 
                                               '{$ideathon_details['team_name']}', '{$ideathon_details['title']}', 
                                               $user_id, '{$ideathon_details['team_member1']}', 
                                               '{$ideathon_details['team_member2']}', '{$ideathon_details['team_member3']}', 
                                               {$ideathon_details['standard']}, '{$ideathon_details['contact_email']}')";
                mysqli_query($conn, $auto_register_query);
                
                // Refresh registered events
                $registered_events[] = $hackathon_event_id;
                $team_details[$hackathon_event_id] = [
                    'team_name' => $ideathon_details['team_name'],
                    'team_number' => $ideathon_details['team_number'],
                    'competition_id' => $ideathon_details['competition_id']
                ];
            }
        } else {
            // Hackathon already registered, add to arrays
            $hackathon_reg = mysqli_fetch_assoc($hackathon_check_result);
            if (!in_array($hackathon_event_id, $registered_events)) {
                $registered_events[] = $hackathon_event_id;
            }
            $team_details[$hackathon_event_id] = [
                'team_name' => $hackathon_reg['team_name'],
                'team_number' => $hackathon_reg['team_number'],
                'competition_id' => $hackathon_reg['competition_id']
            ];
        }
    }
}

// Get user's notifications
$notifications = getUserNotifications($user_id, 'student', 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">Student Dashboard</span>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link">Welcome, <?php echo htmlspecialchars($user_data['name']); ?></span>
                <a href="../../logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">Menu</div>
                    <div class="list-group list-group-flush">
                        <a href="index.php" class="list-group-item list-group-item-action active">Dashboard</a>
                        <a href="view_events.php" class="list-group-item list-group-item-action">View Events</a>
                        <a href="select_event.php" class="list-group-item list-group-item-action">Register for Event</a>
                        <a href="certificates.php" class="list-group-item list-group-item-action">Certificates</a>
                    </div>
                </div>
                
                <!-- My Teams -->
                <div class="card mt-3">
                    <div class="card-header">My Teams</div>
                    <div class="card-body">
                        <?php if (!empty($team_details)): ?>
                            <?php foreach($team_details as $event_id => $team): ?>
                                <p class="mb-2">
                                    <strong><?php 
                                        $event_name_query = "SELECT name FROM events WHERE id = $event_id";
                                        $event_name_result = mysqli_query($conn, $event_name_query);
                                        $event_name = mysqli_fetch_assoc($event_name_result);
                                        echo htmlspecialchars($event_name['name']);
                                    ?>:</strong><br>
                                    <span class="text-primary"><?php echo htmlspecialchars($team['team_name']); ?></span>
                                    <?php if ($team['team_number']): ?>
                                        <br><small>Team #: <?php echo htmlspecialchars($team['team_number']); ?></small>
                                    <?php endif; ?>
                                </p>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No teams registered yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Profile Info -->
                <div class="card mt-3">
                    <div class="card-header">My Info</div>
                    <div class="card-body">
                        <p><strong>School:</strong> <?php echo htmlspecialchars($user_data['school']); ?></p>
                        <p><strong>Standard:</strong> <?php echo htmlspecialchars($user_data['standard']); ?>th</p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <h3>Upcoming Events</h3>
                
                <?php if (mysqli_num_rows($events_result) > 0): ?>
                    <div class="row">
                        <?php while($event = mysqli_fetch_assoc($events_result)): 
                            $is_registered = in_array($event['id'], $registered_events);
                            $team_info = isset($team_details[$event['id']]) ? $team_details[$event['id']] : null;
                            
                            // Special handling for Hackathon: if Ideathon is registered, show Hackathon as registered
                            $is_auto_registered = false;
                            if ($event['event_type'] == 'hackathon' && $ideathon_registered) {
                                $is_registered = true;
                                if (!$team_info && $ideathon_team_info) {
                                    $team_info = $ideathon_team_info;
                                    $is_auto_registered = true;
                                }
                            }
                        ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                                    <p class="card-text">
                                        <strong>Date:</strong> <?php echo date('d F Y', strtotime($event['date'])); ?><br>
                                        <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?><br>
                                        <strong>Time:</strong> <?php echo date('h:i A', strtotime($event['reporting_time'])); ?>
                                    </p>
                                    
                                    <?php if ($is_registered && $team_info): ?>
                                        <div class="alert alert-success">
                                            <strong><i class="fas fa-check-circle"></i> Registered</strong><br>
                                            Team: <?php echo htmlspecialchars($team_info['team_name']); ?>
                                            <?php if ($team_info['team_number']): ?>
                                                (Team #: <?php echo htmlspecialchars($team_info['team_number']); ?>)
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($event['event_type'] == 'hackathon' && $ideathon_registered): ?>
                                            <div class="alert alert-info mt-2">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Note:</strong> Hackathon is part of the same competition as Ideathon. 
                                                Your Ideathon team details will be automatically reused for Hackathon.<br>
                                                <strong>You are already registered for Hackathon with your Ideathon team details.</strong>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <a href="register_event.php?event_id=<?php echo $event['id']; ?>" 
                                           class="btn btn-success">View Registration</a>
                                    <?php else: ?>
                                        <a href="register_event.php?event_id=<?php echo $event['id']; ?>" 
                                           class="btn btn-primary">Register</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No upcoming events found.</div>
                <?php endif; ?>
                
                <!-- Notifications -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>My Notifications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($notifications)): ?>
                            <div class="list-group">
                                <?php foreach($notifications as $notif): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                        <small><?php echo formatNotificationTime($notif['created_at']); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($notif['message'])); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="view_notifications.php" class="btn btn-outline-primary mt-3">View All Notifications</a>
                        <?php else: ?>
                            <p class="text-muted">No notifications yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>