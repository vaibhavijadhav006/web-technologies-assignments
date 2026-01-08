<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get student details
$student_query = "SELECT * FROM student_details WHERE user_id = $user_id";
$student_result = mysqli_query($conn, $student_query);
$student = mysqli_fetch_assoc($student_result);

// Check eligibility
if ($student['standard'] < 8) {
    $eligibility_error = "You must be in 8th standard or above to register for events!";
}

// Get upcoming events
$events_query = "SELECT * FROM events WHERE status = 'upcoming' ORDER BY date";
$events_result = mysqli_query($conn, $events_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .selection-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            margin-top: 30px;
        }
        .header-section {
            background: linear-gradient(45deg, #0d6efd, #0b5ed7);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: floatPattern 20s linear infinite;
        }
        @keyframes floatPattern {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-50px, -50px) rotate(360deg); }
        }
        .eligibility-card {
            background: linear-gradient(45deg, #ffc107, #ffca2c);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border-left: 5px solid #0d6efd;
        }
        .event-option {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .event-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(45deg, #0d6efd, #0b5ed7);
            transition: width 0.3s ease;
        }
        .event-option:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            border-color: #0d6efd;
        }
        .event-option:hover::before {
            width: 100%;
            opacity: 0.1;
        }
        .event-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #0d6efd;
        }
        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-register {
            background: linear-gradient(45deg, #198754, #157347);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-register:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 30px rgba(25, 135, 84, 0.4);
            color: white;
        }
        .btn-view {
            background: linear-gradient(45deg, #0d6efd, #0b5ed7);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-view:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.3);
            color: white;
        }
        .info-bubble {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            padding: 15px;
            border-radius: 15px;
            margin: 20px 0;
        }
        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow" style="background: rgba(0,0,0,0.2);">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-user-graduate"></i> Event Registration
            </a>
            <div class="navbar-nav">
                <a href="index.php" class="nav-link text-white">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="selection-card animate__animated animate__fadeIn">
                    <!-- Header -->
                    <div class="header-section">
                        <div class="floating-icon">
                            <i class="fas fa-calendar-plus fa-4x"></i>
                        </div>
                        <h1 class="mt-3">Register for Events</h1>
                        <p class="lead">Choose an event below to register your team</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Eligibility Check -->
                        <?php if (isset($eligibility_error)): ?>
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> Eligibility Error</h5>
                                <?php echo $eligibility_error; ?>
                                <div class="mt-3">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="eligibility-card">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5><i class="fas fa-check-circle text-success"></i> Eligibility Verified</h5>
                                        <p class="mb-0">
                                            You are eligible to register for events! 
                                            <strong>Standard: <?php echo htmlspecialchars($student['standard']); ?>th</strong>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <span class="badge bg-success fs-6 p-2">
                                            <i class="fas fa-user-check"></i> ELIGIBLE
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Important Information -->
                            <div class="info-bubble">
                                <h6><i class="fas fa-info-circle"></i> Important Registration Information</h6>
                                <ul class="mb-0">
                                    <li>Only team lead needs to register (team of 4 members including you)</li>
                                    <li>You can edit your registration maximum 2 times</li>
                                    <li>All team members must be present at the event</li>
                                    <li>Registration closes 2 days before the event date</li>
                                </ul>
                            </div>
                            
                            <!-- Events Selection -->
                            <h3 class="text-center my-4">
                                <i class="fas fa-star text-warning"></i> 
                                Available Events
                                <i class="fas fa-star text-warning"></i>
                            </h3>
                            
                            <?php if (mysqli_num_rows($events_result) > 0): ?>
                                <div class="row">
                                    <?php while($event = mysqli_fetch_assoc($events_result)): 
                                        // Check if already registered for this event
                                        $check_query = "SELECT id FROM event_registrations 
                                                       WHERE team_lead_id = $user_id AND event_id = {$event['id']}";
                                        $check_result = mysqli_query($conn, $check_query);
                                        $registered = mysqli_num_rows($check_result) > 0;
                                    ?>
                                    <div class="col-md-6">
                                        <div class="event-option animate__animated animate__fadeInUp">
                                            <?php if ($registered): ?>
                                                <span class="status-badge" style="background: linear-gradient(45deg, #198754, #157347); color: white;">
                                                    <i class="fas fa-check-circle"></i> REGISTERED
                                                </span>
                                            <?php endif; ?>
                                            
                                            <div class="text-center mb-3">
                                                <div class="event-icon">
                                                    <?php if($event['name'] == 'Ideathon'): ?>
                                                        <i class="fas fa-lightbulb"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-code"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <h4 class="mb-2"><?php echo htmlspecialchars($event['name']); ?></h4>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <p class="mb-2">
                                                    <i class="fas fa-calendar text-primary"></i> 
                                                    <strong>Date:</strong> <?php echo date('l, d F Y', strtotime($event['date'])); ?>
                                                </p>
                                                <p class="mb-2">
                                                    <i class="fas fa-map-marker-alt text-danger"></i> 
                                                    <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?>
                                                </p>
                                                <p class="mb-2">
                                                    <i class="fas fa-clock text-success"></i> 
                                                    <strong>Reporting Time:</strong> <?php echo date('h:i A', strtotime($event['reporting_time'])); ?>
                                                </p>
                                            </div>
                                            
                                            <?php if($event['name'] == 'Ideathon'): ?>
                                                <div class="alert alert-info">
                                                    <strong><i class="fas fa-lightbulb"></i> Ideathon:</strong> 
                                                    Present innovative ideas to solve real-world problems.
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning">
                                                    <strong><i class="fas fa-code"></i> Hackathon:</strong> 
                                                    Build innovative solutions within 24 hours.
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="text-center mt-4">
                                                <?php if ($registered): ?>
                                                    <a href="register_event.php?event_id=<?php echo $event['id']; ?>" 
                                                       class="btn btn-view">
                                                        <i class="fas fa-eye"></i> View Registration
                                                    </a>
                                                <?php else: ?>
                                                    <a href="register_event.php?event_id=<?php echo $event['id']; ?>" 
                                                       class="btn btn-register">
                                                        <i class="fas fa-user-plus"></i> Register Now
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                                    <h4>No Events Available</h4>
                                    <p class="text-muted">There are no upcoming events available for registration at the moment.</p>
                                    <a href="index.php" class="btn btn-primary mt-3">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Navigation -->
                        <div class="text-center mt-5 pt-4 border-top">
                            <a href="index.php" class="btn btn-outline-secondary me-3">
                                <i class="fas fa-home"></i> Back to Dashboard
                            </a>
                            <a href="view_events.php" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-alt"></i> View All Events
                            </a>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="card-footer text-center py-3" style="background: #f8f9fa;">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Need help? Contact event coordinator at manthan@kletech.ac.in
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add staggered animation to event options
            const eventOptions = document.querySelectorAll('.event-option');
            eventOptions.forEach((option, index) => {
                option.style.animationDelay = (index * 0.2) + 's';
            });
            
            // Add hover effects
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>