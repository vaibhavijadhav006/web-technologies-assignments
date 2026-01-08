<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

checkRole('mentor');

$user_id = $_SESSION['user_id'];

// Check for success/error messages from send_message.php
if (isset($_SESSION['message_success'])) {
    $message_success = $_SESSION['message_success'];
    unset($_SESSION['message_success']);
}

if (isset($_SESSION['message_error'])) {
    $message_error = $_SESSION['message_error'];
    unset($_SESSION['message_error']);
}

// Get mentor ID
$mentor_query = "SELECT m.id FROM mentor_details m WHERE m.user_id = $user_id";
$mentor_result = mysqli_query($conn, $mentor_query);
$mentor = mysqli_fetch_assoc($mentor_result);

if (!$mentor) {
    die("Mentor details not found!");
}

$mentor_id = $mentor['id'];

// Get team_id if specified
$team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : 0;

// Get all teams assigned to this mentor
if ($team_id > 0) {
    $query = "SELECT er.*, e.name as event_name, u.name as lead_name, 
                     u.email as lead_email, sd.standard as lead_standard,
                     sd.school as lead_school
              FROM event_registrations er
              JOIN events e ON er.event_id = e.id
              JOIN users u ON er.team_lead_id = u.id
              LEFT JOIN student_details sd ON u.id = sd.user_id
              WHERE er.mentor_id = $mentor_id AND er.id = $team_id
              ORDER BY er.team_number";
} else {
    $query = "SELECT er.*, e.name as event_name, u.name as lead_name, 
                     u.email as lead_email, sd.standard as lead_standard,
                     sd.school as lead_school
              FROM event_registrations er
              JOIN events e ON er.event_id = e.id
              JOIN users u ON er.team_lead_id = u.id
              LEFT JOIN student_details sd ON u.id = sd.user_id
              WHERE er.mentor_id = $mentor_id
              ORDER BY er.team_number";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}

// Get notifications ONLY for this mentor
$notif_query = "SELECT * FROM notifications 
                WHERE (
                    -- Notifications mentioning this mentor by name
                    message LIKE '%{$_SESSION['name']}%' OR
                    
                    -- General mentor notifications (not event-specific)
                    (message LIKE '%all mentors%' AND message NOT LIKE '%student%') OR
                    
                    -- Mentor assignment notifications
                    message LIKE '%mentor assigned%' OR
                    
                    -- System notifications
                    message LIKE '%system%' OR
                    title LIKE '%Important%'
                )
                ORDER BY created_at DESC 
                LIMIT 3";

$notif_result = mysqli_query($conn, $notif_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Teams - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .team-card {
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        .team-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .team-header {
            background: linear-gradient(45deg, #0d6efd, #0b5ed7);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }
        .members-list {
            list-style: none;
            padding: 0;
        }
        .members-list li {
            background: #f8f9fa;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
        }
        .members-list li:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        .badge-mentor {
            background: linear-gradient(45deg, #198754, #157347);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .btn-contact {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-contact:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(23, 162, 184, 0.3);
            color: white;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .modal-header {
            background: linear-gradient(45deg, #0d6efd, #0b5ed7);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .contact-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow" style="background: linear-gradient(45deg, #198754, #157347);">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-users"></i> My Assigned Teams
            </a>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Success/Error Messages -->
    <?php if (isset($message_success)): ?>
    <div class="container mt-4">
        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h5 class="mb-1">Message Sent Successfully!</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($message_success); ?></p>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> 
                        <?php echo date('d M Y H:i:s'); ?>
                    </small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (isset($message_error)): ?>
    <div class="container mt-4">
        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="mb-1">Message Failed!</h5>
                    <p class="mb-0"><?php echo $message_error; ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">
                    <i class="fas fa-user-friends"></i> 
                    Teams Under Your Guidance
                </h3>
                <p class="text-muted mb-0">Manage and communicate with your assigned teams</p>
            </div>
            <span class="badge bg-primary fs-6 p-3">
                <i class="fas fa-users"></i> 
                Total Teams: <?php echo mysqli_num_rows($result); ?>
            </span>
        </div>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row">
                <?php while($team = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 mb-4">
                    <div class="card team-card animate__animated animate__fadeInUp">
                        <!-- Team Header -->
                        <div class="team-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">
                                        <i class="fas fa-users"></i> 
                                        <?php echo htmlspecialchars($team['team_name']); ?>
                                    </h5>
                                    <p class="mb-0">
                                        Team <?php echo $team['team_number'] ?: '<span class="text-warning">Number not assigned</span>'; ?>
                                    </p>
                                </div>
                                <span class="badge bg-light text-dark fs-6">
                                    <?php echo htmlspecialchars($team['event_name']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Team Body -->
                        <div class="card-body">
                            <!-- Project Info -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-project-diagram"></i> Project Title
                                </h6>
                                <p class="mb-0"><?php echo htmlspecialchars($team['title']); ?></p>
                            </div>
                            
                            <!-- Team Members -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user-friends"></i> Team Members
                                </h6>
                                <div class="members-list">
                                    <li>
                                        <strong><?php echo htmlspecialchars($team['lead_name']); ?></strong>
                                        <span class="badge-mentor ms-2">Team Lead</span>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-graduation-cap"></i> 
                                                Standard: <?php echo $team['lead_standard']; ?>th
                                                | <i class="fas fa-school"></i> 
                                                School: <?php echo htmlspecialchars($team['lead_school']); ?>
                                            </small>
                                        </div>
                                    </li>
                                    <li>
                                        <i class="fas fa-user text-primary"></i> 
                                        <?php echo htmlspecialchars($team['team_member1']); ?>
                                    </li>
                                    <li>
                                        <i class="fas fa-user text-primary"></i> 
                                        <?php echo htmlspecialchars($team['team_member2']); ?>
                                    </li>
                                    <li>
                                        <i class="fas fa-user text-primary"></i> 
                                        <?php echo htmlspecialchars($team['team_member3']); ?>
                                    </li>
                                </div>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="contact-info">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-envelope"></i> Contact Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <i class="fas fa-envelope text-primary"></i> 
                                            <strong>Lead Email:</strong><br>
                                            <?php echo htmlspecialchars($team['lead_email']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <i class="fas fa-envelope text-primary"></i> 
                                            <strong>Team Email:</strong><br>
                                            <?php echo htmlspecialchars($team['contact_email']); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i> 
                                        Registered: <?php echo date('d M Y', strtotime($team['registered_at'])); ?>
                                        | <i class="fas fa-edit"></i> 
                                        Edits: <?php echo $team['edit_count']; ?>/2
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Team Footer -->
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-contact" data-bs-toggle="modal" 
                                        data-bs-target="#contactModal<?php echo $team['id']; ?>">
                                    <i class="fas fa-comments"></i> Contact Team
                                </button>
                                <a href="mailto:<?php echo htmlspecialchars($team['contact_email']); ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-paper-plane"></i> Direct Email
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Modal -->
                    <div class="modal fade" id="contactModal<?php echo $team['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-comments"></i> 
                                        Contact Team <?php echo $team['team_number'] ?: ''; ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Team Info -->
                                    <div class="alert alert-info mb-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle fa-2x me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Team Information</h6>
                                                <p class="mb-0">
                                                    <strong>Team:</strong> <?php echo htmlspecialchars($team['team_name']); ?> | 
                                                    <strong>Lead:</strong> <?php echo htmlspecialchars($team['lead_name']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Contact Form -->
                                    <form action="send_message.php" method="POST" id="messageForm<?php echo $team['id']; ?>">
                                        <input type="hidden" name="team_id" value="<?php echo $team['id']; ?>">
                                        
                                        <!-- Subject -->
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-heading"></i> Subject *
                                            </label>
                                            <input type="text" class="form-control" name="subject" required
                                                   value="Regarding Manthan 2.0 - <?php echo htmlspecialchars($team['event_name']); ?>">
                                            <small class="text-muted">Enter a clear subject for your message</small>
                                        </div>
                                        
                                        <!-- Message -->
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-envelope"></i> Message *
                                            </label>
                                            <textarea class="form-control" rows="6" name="message" required 
                                                      placeholder="Type your message here..."></textarea>
                                            <small class="text-muted">Minimum 10 characters required</small>
                                        </div>
                                        
                                        <!-- Recipient Info -->
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-paper-plane"></i> Message will be sent to:</h6>
                                            <ul class="mb-0">
                                                <li><strong>Team Lead:</strong> <?php echo htmlspecialchars($team['lead_name']); ?></li>
                                                <li><strong>Email:</strong> <?php echo htmlspecialchars($team['contact_email']); ?></li>
                                                <li><strong>Team:</strong> <?php echo htmlspecialchars($team['team_name']); ?></li>
                                            </ul>
                                        </div>
                                        
                                        <!-- Form Actions -->
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-paper-plane"></i> Send Message
                                            </button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- No Teams Message -->
            <div class="card shadow animate__animated animate__fadeIn">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-users-slash fa-5x text-muted"></i>
                    </div>
                    <h3 class="mb-3">No Teams Assigned Yet</h3>
                    <p class="text-muted mb-4">
                        You don't have any teams assigned to you at the moment.<br>
                        Teams will be assigned by the administrator soon.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <a href="../../logout.php" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation for message forms
        document.addEventListener('DOMContentLoaded', function() {
            // Get all message forms
            const messageForms = document.querySelectorAll('form[id^="messageForm"]');
            
            messageForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const subject = this.querySelector('input[name="subject"]');
                    const message = this.querySelector('textarea[name="message"]');
                    
                    let errors = [];
                    
                    // Validate subject
                    if (subject.value.trim().length < 5) {
                        errors.push("Subject must be at least 5 characters long");
                        subject.classList.add('is-invalid');
                    } else {
                        subject.classList.remove('is-invalid');
                    }
                    
                    // Validate message
                    if (message.value.trim().length < 10) {
                        errors.push("Message must be at least 10 characters long");
                        message.classList.add('is-invalid');
                    } else {
                        message.classList.remove('is-invalid');
                    }
                    
                    // If there are errors, prevent form submission
                    if (errors.length > 0) {
                        e.preventDefault();
                        
                        // Show error message
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger alert-dismissible fade show mb-3';
                        errorAlert.innerHTML = `
                            <h6 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Please fix the following:</h6>
                            <ul class="mb-0">
                                ${errors.map(error => `<li>${error}</li>`).join('')}
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        
                        // Insert error alert before the form
                        form.parentNode.insertBefore(errorAlert, form);
                        
                        // Remove error alert after 5 seconds
                        setTimeout(() => {
                            if (errorAlert.parentNode) {
                                errorAlert.remove();
                            }
                        }, 5000);
                    }
                });
            });
            
            // Add animation to cards on hover
            const teamCards = document.querySelectorAll('.team-card');
            teamCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('animate__animated', 'animate__pulse');
                });
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('animate__animated', 'animate__pulse');
                });
            });
        });
    </script>
</body>
</html>