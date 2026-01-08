<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php'; // NEW

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all events
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM event_registrations er 
           WHERE er.event_id = e.id AND er.team_lead_id = $user_id) as registered,
          (SELECT er.team_name FROM event_registrations er 
           WHERE er.event_id = e.id AND er.team_lead_id = $user_id LIMIT 1) as team_name,
          (SELECT er.team_number FROM event_registrations er 
           WHERE er.event_id = e.id AND er.team_lead_id = $user_id LIMIT 1) as team_number
          FROM events e
          WHERE e.status = 'upcoming'
          ORDER BY e.date";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Events - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Events List</a>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="btn btn-light">Back to Dashboard</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h3>Upcoming Events</h3>
        <p class="text-muted">Register for events and manage your team registrations.</p>
        
        <div class="row">
            <?php while($event = mysqli_fetch_assoc($result)): 
                $is_registered = $event['registered'] > 0;
            ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header 
                        <?php echo $is_registered ? 'bg-success text-white' : 'bg-primary text-white'; ?>">
                        <h5 class="mb-0">
                            <?php echo htmlspecialchars($event['name']); ?>
                            <?php if($is_registered): ?>
                                <span class="badge bg-light text-dark float-end">
                                    <i class="fas fa-check-circle"></i> Registered
                                </span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Date:</strong> <?php echo date('l, d F Y', strtotime($event['date'])); ?></p>
                        <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                        <p><strong>Reporting Time:</strong> <?php echo date('h:i A', strtotime($event['reporting_time'])); ?></p>
                        <p><strong>Event Type:</strong> 
                            <span class="badge 
                                <?php echo $event['event_type'] == 'ideathon' ? 'bg-info' : 'bg-warning'; ?>">
                                <?php echo ucfirst($event['event_type']); ?>
                            </span>
                        </p>
                        
                        <?php if($is_registered): ?>
                            <div class="alert alert-success">
                                <h6><i class="fas fa-users"></i> Your Team Details:</h6>
                                <p class="mb-1"><strong>Team Name:</strong> <?php echo htmlspecialchars($event['team_name']); ?></p>
                                <?php if($event['team_number']): ?>
                                    <p class="mb-0"><strong>Team Number:</strong> <?php echo htmlspecialchars($event['team_number']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <?php if($event['event_type'] == 'hackathon'): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> Hackathon registration automatically uses your Ideathon team details.
                                </div>
                            <?php endif; ?>
                            
                            <?php if($is_registered): ?>
                                <a href="register_event.php?event_id=<?php echo $event['id']; ?>" 
                                   class="btn btn-success">
                                    <i class="fas fa-eye"></i> View Registration
                                </a>
                            <?php else: ?>
                                <a href="register_event.php?event_id=<?php echo $event['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Register Now
                                </a>
                            <?php endif; ?>
                            
                            <?php if($event['event_type'] == 'ideathon' && $is_registered): ?>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb"></i> 
                                        Your team will be automatically registered for Hackathon.
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Past Events -->
        <?php
        $past_query = "SELECT * FROM events WHERE status = 'completed' ORDER BY date DESC";
        $past_result = mysqli_query($conn, $past_query);
        
        if (mysqli_num_rows($past_result) > 0): ?>
            <h4 class="mt-5">Past Events</h4>
            <div class="row">
                <?php while($past_event = mysqli_fetch_assoc($past_result)): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6><?php echo htmlspecialchars($past_event['name']); ?></h6>
                        </div>
                        <div class="card-body">
                            <p><small>Date: <?php echo date('d M Y', strtotime($past_event['date'])); ?></small></p>
                            <p><small>Venue: <?php echo htmlspecialchars($past_event['venue']); ?></small></p>
                            <a href="certificates.php" class="btn btn-sm btn-outline-success">View Certificate</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>