<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php'; // NEW

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}
checkRole('admin');

$team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : 0;

// If no team_id provided, redirect to view participants page
if ($team_id == 0) {
    header('Location: view_participants.php');
    exit();
}

// Get team details
$team_query = "SELECT er.*, e.name as event_name, u.name as lead_name, 
                      u.email as lead_email, u.id as lead_id
               FROM event_registrations er
               JOIN events e ON er.event_id = e.id
               JOIN users u ON er.team_lead_id = u.id
               WHERE er.id = $team_id";
$team_result = mysqli_query($conn, $team_query);
$team = mysqli_fetch_assoc($team_result);

if (!$team) {
    die("Team not found! Please go back and select a valid team.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_number = mysqli_real_escape_string($conn, $_POST['team_number']);
    $mentor_id = intval($_POST['mentor_id']);
    
    // Check if mentor is already assigned to this team
    if (isMentorAlreadyAssigned($team_id, $mentor_id)) {
        $warning = "Team already has this mentor only";
        // Do NOT insert notification or send email for duplicate assignment
    } else {
        // Get previous mentor (if any) to notify them
        $previous_mentor_id = $team['mentor_id'];
        
        // Update team with new mentor
        $query = "UPDATE event_registrations SET 
                  team_number = '$team_number',
                  mentor_id = $mentor_id 
                  WHERE id = $team_id";
        
        if (mysqli_query($conn, $query)) {
            $success = "Mentor assigned and team number set successfully!";
            
            // Get mentor details
            $mentor_query = "SELECT u.name, u.email, m.contact 
                            FROM users u 
                            JOIN mentor_details m ON u.id = m.user_id 
                            WHERE m.id = $mentor_id";
            $mentor_result = mysqli_query($conn, $mentor_query);
            $mentor = mysqli_fetch_assoc($mentor_result);
            
            // 1. Create notification for STUDENT (team lead)
            // Format: "Mentor Alheba has been assigned to your team.\nTeam Number: T003\nTeam Name: <team_name>\nTeam Lead: <team_lead_name>"
            $student_message = "Mentor " . $mentor['name'] . " has been assigned to your team.\n" .
                              "Team Number: $team_number\n" .
                              "Team Name: " . $team['team_name'] . "\n" .
                              "Team Lead: " . $team['lead_name'];
            
            createNotification(
                'Mentor Assigned', 
                $student_message, 
                $team['lead_id'], 
                'student'
            );
            
            // 2. Create notification for NEW MENTOR
            $mentor_message = "You have been assigned as mentor for team:\n" .
                             "Team Number: $team_number\n" .
                             "Team Name: " . $team['team_name'] . "\n" .
                             "Team Lead: " . $team['lead_name'] . "\n" .
                             "Event: " . $team['event_name'];
            
            // Get mentor user_id
            $mentor_user_query = "SELECT user_id FROM mentor_details WHERE id = $mentor_id";
            $mentor_user_result = mysqli_query($conn, $mentor_user_query);
            $mentor_user = mysqli_fetch_assoc($mentor_user_result);
            
            if ($mentor_user) {
                createNotification(
                    'New Team Assigned', 
                    $mentor_message, 
                    $mentor_user['user_id'], 
                    'mentor'
                );
            }
            
            // 3. If there was a previous mentor, notify them
            if ($previous_mentor_id && $previous_mentor_id != $mentor_id) {
                $prev_mentor_user_query = "SELECT user_id FROM mentor_details WHERE id = $previous_mentor_id";
                $prev_mentor_user_result = mysqli_query($conn, $prev_mentor_user_query);
                $prev_mentor_user = mysqli_fetch_assoc($prev_mentor_user_result);
                
                if ($prev_mentor_user) {
                    createNotification(
                        'Team Reassigned', 
                        "You are no longer mentor for team: " . $team['team_name'], 
                        $prev_mentor_user['user_id'], 
                        'mentor'
                    );
                }
            }
            
            // 4. Send email to student (only for NEW assignments, NOT duplicates)
            require_once '../../includes/send_email.php';
            
            $email_subject = "Mentor Assigned - Team " . $team_number;
            $email_body = "Dear " . $team['lead_name'] . ",\n\n" .
                         "We are pleased to inform you that a mentor has been assigned to your team for Manthan 2.0.\n\n" .
                         "Team Details:\n" .
                         "-------------\n" .
                         "Team Number: " . $team_number . "\n" .
                         "Team Name: " . $team['team_name'] . "\n" .
                         "Assigned Mentor: " . $mentor['name'] . "\n" .
                         "Mentor Contact: " . $mentor['contact'] . "\n" .
                         "Mentor Email: " . $mentor['email'] . "\n" .
                         "Event: " . $team['event_name'] . "\n\n" .
                         "Your mentor will contact you shortly to guide you through the competition.\n\n" .
                         "Best regards,\n" .
                         "Manthan 2.0 Team";
            
            $email_sent = sendEmail(
                $team['lead_email'],
                $team['lead_name'],
                $email_subject,
                $email_body
            );
            
            if ($email_sent) {
                $success .= " Email notification sent to team lead.";
            } else {
                $success .= " (Email sending failed, but notification created)";
            }
            
            // Refresh team data
            $team_result = mysqli_query($conn, $team_query);
            $team = mysqli_fetch_assoc($team_result);
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Get all mentors
$mentors_query = "SELECT m.*, u.name 
                  FROM mentor_details m
                  JOIN users u ON m.user_id = u.id
                  WHERE u.role = 'mentor'";
$mentors_result = mysqli_query($conn, $mentors_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Mentor - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Assign Mentor</a>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="btn btn-light">Back to Dashboard</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($warning)): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $warning; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-times-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Assign Mentor to Team</h4>
                    </div>
                    <div class="card-body">
                        <!-- Team Information -->
                        <div class="mb-4">
                            <h5>Team Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Team Name:</strong> <?php echo htmlspecialchars($team['team_name']); ?></p>
                                    <p><strong>Event:</strong> <?php echo htmlspecialchars($team['event_name']); ?></p>
                                    <p><strong>Team Lead:</strong> <?php echo htmlspecialchars($team['lead_name']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Project Title:</strong> <?php echo htmlspecialchars($team['title']); ?></p>
                                    <p><strong>Contact Email:</strong> <?php echo htmlspecialchars($team['contact_email']); ?></p>
                                    <p><strong>Standard:</strong> <?php echo htmlspecialchars($team['standard']); ?>th</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Assign Mentor Form -->
                        <form method="POST">
                            <div class="mb-3">
                                <label for="team_number" class="form-label">
                                    <i class="fas fa-hashtag"></i> Team Number *
                                </label>
                                <input type="text" name="team_number" class="form-control" required
                                       value="<?php echo $team['team_number'] ? $team['team_number'] : 'T' . str_pad($team_id, 3, '0', STR_PAD_LEFT); ?>">
                                <small class="text-muted">Unique identifier for the team</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mentor_id" class="form-label">
                                    <i class="fas fa-chalkboard-teacher"></i> Select Mentor *
                                </label>
                                <select name="mentor_id" class="form-control" required>
                                    <option value="">Select a mentor</option>
                                    <?php while($mentor = mysqli_fetch_assoc($mentors_result)): ?>
                                        <option value="<?php echo $mentor['id']; ?>" 
                                            <?php echo ($team['mentor_id'] == $mentor['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($mentor['name']) . ' | SRN: ' . htmlspecialchars($mentor['srn']) . ' | Sem: ' . htmlspecialchars($mentor['semester']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> When a mentor is assigned:
                                <ul class="mb-0 mt-2">
                                    <li>Team lead will receive a notification and email</li>
                                    <li>Mentor will receive a notification</li>
                                    <li>Previous mentor (if any) will be notified of change</li>
                                    <li>No duplicate notifications for same mentor</li>
                                </ul>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Assign Mentor
                            </button>
                            <a href="view_participants.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>