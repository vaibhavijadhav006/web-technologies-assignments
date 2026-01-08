<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php'; // NEW

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SESSION['role'] !== 'student') {
    header('Location: ../../dashboard/' . $_SESSION['role'] . '/index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// If no event specified, redirect to select event
if ($event_id == 0) {
    header('Location: select_event.php');
    exit();
}

// Initialize variables
$success = '';
$error = '';
$registered = false;
$registration = null;
$event = null;
$student = null;

// Get event details
$event_query = "SELECT * FROM events WHERE id = $event_id";
$event_result = mysqli_query($conn, $event_query);
if ($event_result && mysqli_num_rows($event_result) > 0) {
    $event = mysqli_fetch_assoc($event_result);
} else {
    $error = "Event not found!";
}

// Get student details
$student_query = "SELECT * FROM student_details WHERE user_id = $user_id";
$student_result = mysqli_query($conn, $student_query);
if ($student_result && mysqli_num_rows($student_result) > 0) {
    $student = mysqli_fetch_assoc($student_result);
    
    // Check eligibility (8th standard or above)
    if ($student['standard'] < 8) {
        $error = "Registration only allowed for 8th standard and above!";
    }
} else {
    $error = "Student details not found!";
}

// Check if student already registered for this event type
if (!$error && $event) {
    $event_type = $event['event_type'];
    
    // Special handling: Ideathon and Hackathon are part of the SAME competition
    // If registered for Ideathon, automatically reuse team for Hackathon
    if ($event_type == 'hackathon') {
        // Check if already registered for Ideathon (same competition)
        $ideathon_check = "SELECT er.* FROM event_registrations er
                          JOIN events e ON er.event_id = e.id
                          WHERE er.team_lead_id = $user_id 
                          AND e.event_type = 'ideathon'";
        $ideathon_result = mysqli_query($conn, $ideathon_check);
        
        if (mysqli_num_rows($ideathon_result) > 0) {
            $ideathon_reg = mysqli_fetch_assoc($ideathon_result);
            // Automatically create Hackathon registration with same team details
            $hackathon_check = "SELECT * FROM event_registrations er
                               JOIN events e ON er.event_id = e.id
                               WHERE er.team_lead_id = $user_id 
                               AND e.event_type = 'hackathon'";
            $hackathon_result = mysqli_query($conn, $hackathon_check);
            
            if (mysqli_num_rows($hackathon_result) == 0) {
                // Auto-register for Hackathon with Ideathon team details
                $auto_register_query = "INSERT INTO event_registrations 
                                       (event_id, competition_id, team_name, title, team_lead_id, 
                                        team_member1, team_member2, team_member3, 
                                        standard, contact_email) 
                                       VALUES ($event_id, '{$ideathon_reg['competition_id']}', 
                                               '{$ideathon_reg['team_name']}', '{$ideathon_reg['title']}', 
                                               $user_id, '{$ideathon_reg['team_member1']}', 
                                               '{$ideathon_reg['team_member2']}', '{$ideathon_reg['team_member3']}', 
                                               {$ideathon_reg['standard']}, '{$ideathon_reg['contact_email']}')";
                mysqli_query($conn, $auto_register_query);
            }
        }
    }
    
    if (isAlreadyRegisteredForEventType($user_id, $event_type)) {
        // Check if this specific event is already registered
        $check_query = "SELECT er.* FROM event_registrations er
                       JOIN events e ON er.event_id = e.id
                       WHERE er.team_lead_id = $user_id 
                       AND e.event_type = '$event_type'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $registered = true;
            $registration = mysqli_fetch_assoc($check_result);
            
            // If trying to register for different event of same type
            if ($registration['event_id'] != $event_id) {
                $info = "You are already registered for " . ($event_type == 'ideathon' ? 'Ideathon' : 'Hackathon') . 
                       ". Your team details will be used for this event.";
            }
        }
    }
    
    // Check if this specific event is already registered
    $specific_check = "SELECT * FROM event_registrations 
                      WHERE team_lead_id = $user_id AND event_id = $event_id";
    $specific_result = mysqli_query($conn, $specific_check);
    if (mysqli_num_rows($specific_result) > 0) {
        $registered = true;
        $registration = mysqli_fetch_assoc($specific_result);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error) {
    // Get form data
    $team_name = isset($_POST['team_name']) ? sanitize($_POST['team_name'], $conn) : '';
    $title = isset($_POST['title']) ? sanitize($_POST['title'], $conn) : '';
    $member1 = isset($_POST['member1']) ? sanitize($_POST['member1'], $conn) : '';
    $member2 = isset($_POST['member2']) ? sanitize($_POST['member2'], $conn) : '';
    $member3 = isset($_POST['member3']) ? sanitize($_POST['member3'], $conn) : '';
    $contact_email = isset($_POST['contact_email']) ? sanitize($_POST['contact_email'], $conn) : '';
    
    // Validate
    if (empty($team_name) || empty($title) || empty($member1) || 
        empty($member2) || empty($member3) || empty($contact_email)) {
        $error = "All fields are required!";
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address!";
    } else {
        if ($registered && $registration) {
            // Update existing registration (same event or different event of same type)
            if ($registration['event_id'] == $event_id) {
                // Same event - check edit count
                if ($registration['edit_count'] >= 2) {
                    $error = "Maximum edit limit (2 times) reached!";
                } else {
                    $update_query = "UPDATE event_registrations SET 
                                    team_name = '$team_name',
                                    title = '$title',
                                    team_member1 = '$member1',
                                    team_member2 = '$member2',
                                    team_member3 = '$member3',
                                    contact_email = '$contact_email',
                                    edit_count = edit_count + 1
                                    WHERE id = {$registration['id']}";
                    
                    if (mysqli_query($conn, $update_query)) {
                        $success = "Registration updated successfully!";
                        // Refresh registration data
                        $check_query = "SELECT * FROM event_registrations 
                                       WHERE team_lead_id = $user_id 
                                       AND event_id = $event_id";
                        $check_result = mysqli_query($conn, $check_query);
                        $registration = mysqli_fetch_assoc($check_result);
                    } else {
                        $error = "Error updating: " . mysqli_error($conn);
                    }
                }
            } else {
                // Different event of same type - reuse competition_id and team details
                // For Ideathon→Hackathon: automatically reuse team details
                $competition_id = $registration['competition_id'];
                
                // Get the event type of the existing registration
                $existing_event_query = "SELECT event_type FROM events WHERE id = {$registration['event_id']}";
                $existing_event_result = mysqli_query($conn, $existing_event_query);
                $existing_event = mysqli_fetch_assoc($existing_event_result);
                
                // If registering for Hackathon and already registered for Ideathon, reuse all team details
                if ($event['event_type'] == 'hackathon' && $existing_event['event_type'] == 'ideathon') {
                    $team_name = $registration['team_name'];
                    $title = $registration['title'];
                    $member1 = $registration['team_member1'];
                    $member2 = $registration['team_member2'];
                    $member3 = $registration['team_member3'];
                    $contact_email = $registration['contact_email'];
                }
                
                $insert_query = "INSERT INTO event_registrations 
                                (event_id, team_name, title, team_lead_id, 
                                 team_member1, team_member2, team_member3, 
                                 standard, contact_email, competition_id) 
                                VALUES ($event_id, '$team_name', '$title', $user_id,
                                        '$member1', '$member2', '$member3',
                                        {$student['standard']}, '$contact_email', '$competition_id')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $success = "Thank you for registering for " . htmlspecialchars($event['name']) . "!";
                    $registered = true;
                    // Get new registration
                    $check_query = "SELECT * FROM event_registrations 
                                   WHERE team_lead_id = $user_id 
                                   AND event_id = $event_id";
                    $check_result = mysqli_query($conn, $check_query);
                    $registration = mysqli_fetch_assoc($check_result);
                } else {
                    $error = "Registration failed: " . mysqli_error($conn);
                }
            }
        } else {
            // New registration - generate competition_id
            $competition_id = getCompetitionId($user_id, $event['event_type']);
            
            $insert_query = "INSERT INTO event_registrations 
                            (event_id, team_name, title, team_lead_id, 
                             team_member1, team_member2, team_member3, 
                             standard, contact_email, competition_id) 
                            VALUES ($event_id, '$team_name', '$title', $user_id,
                                    '$member1', '$member2', '$member3',
                                    {$student['standard']}, '$contact_email', '$competition_id')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "Thank you for registering for " . htmlspecialchars($event['name']) . "!";
                $registered = true;
                // Get new registration
                $check_query = "SELECT * FROM event_registrations 
                               WHERE team_lead_id = $user_id 
                               AND event_id = $event_id";
                $check_result = mysqli_query($conn, $check_query);
                $registration = mysqli_fetch_assoc($check_result);
                
                // If registering for Ideathon, automatically create Hackathon registration
                if ($event['event_type'] == 'ideathon' && $registration) {
                    // Get Hackathon event ID
                    $hackathon_event_query = "SELECT id FROM events WHERE event_type = 'hackathon' LIMIT 1";
                    $hackathon_event_result = mysqli_query($conn, $hackathon_event_query);
                    
                    if ($hackathon_event_result && mysqli_num_rows($hackathon_event_result) > 0) {
                        $hackathon_event = mysqli_fetch_assoc($hackathon_event_result);
                        $hackathon_event_id = $hackathon_event['id'];
                        
                        // Check if Hackathon registration already exists
                        $hackathon_check = "SELECT * FROM event_registrations 
                                          WHERE team_lead_id = $user_id AND event_id = {$hackathon_event_id}";
                        $hackathon_check_result = mysqli_query($conn, $hackathon_check);
                        
                        if (mysqli_num_rows($hackathon_check_result) == 0) {
                            // Auto-create Hackathon registration with Ideathon team details
                            $auto_hackathon_query = "INSERT INTO event_registrations 
                                                   (event_id, competition_id, team_name, title, team_lead_id, 
                                                    team_member1, team_member2, team_member3, 
                                                    standard, contact_email) 
                                                   VALUES ({$hackathon_event_id}, '{$registration['competition_id']}', 
                                                           '$team_name', '$title', 
                                                           $user_id, '$member1', 
                                                           '$member2', '$member3', 
                                                           {$student['standard']}, '$contact_email')";
                            mysqli_query($conn, $auto_hackathon_query);
                        }
                    }
                }
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Event Registration</a>
            <div class="navbar-nav">
                <a href="index.php" class="nav-link">Dashboard</a>
                <a href="view_events.php" class="nav-link">View Events</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
                <a href="view_events.php" class="btn btn-sm btn-outline-danger ms-3">Back to Events</a>
            </div>
        <?php endif; ?>
        
        <?php if (isset($info)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <?php echo $info; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($event && $student): ?>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><?php echo htmlspecialchars($event['name']); ?> Registration</h4>
                    <p class="mb-0">
                        Date: <?php echo date('d F Y', strtotime($event['date'])); ?> | 
                        Venue: <?php echo htmlspecialchars($event['venue']); ?>
                    </p>
                </div>
                <div class="card-body">
                    <?php if ($registered && $registration): ?>
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle"></i> You are already registered!</strong>
                            <?php if ($registration['event_id'] == $event_id && $registration['edit_count'] < 2): ?>
                                You can edit your registration <?php echo (2 - $registration['edit_count']); ?> more time(s).
                            <?php elseif ($registration['event_id'] != $event_id): ?>
                                Your team details from previous registration will be used.
                            <?php else: ?>
                                Edit limit reached (2 times maximum).
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Team Name *</label>
                                <input type="text" name="team_name" class="form-control" required 
                                       value="<?php echo $registration ? htmlspecialchars($registration['team_name']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project Title *</label>
                                <input type="text" name="title" class="form-control" required
                                       value="<?php echo $registration ? htmlspecialchars($registration['title']) : ''; ?>">
                            </div>
                        </div>
                        
                        <h5>Team Members (Total 4 including you)</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Team Lead (You)</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo htmlspecialchars($_SESSION['name']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Member 2 Name *</label>
                                <input type="text" name="member1" class="form-control" required
                                       value="<?php echo $registration ? htmlspecialchars($registration['team_member1']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Member 3 Name *</label>
                                <input type="text" name="member2" class="form-control" required
                                       value="<?php echo $registration ? htmlspecialchars($registration['team_member2']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Member 4 Name *</label>
                                <input type="text" name="member3" class="form-control" required
                                       value="<?php echo $registration ? htmlspecialchars($registration['team_member3']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Email *</label>
                                <input type="email" name="contact_email" class="form-control" required
                                       value="<?php echo $registration ? htmlspecialchars($registration['contact_email']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Standard</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['standard']); ?>th" readonly>
                                <small class="text-muted">Must be 8th standard or above</small>
                            </div>
                        </div>
                        
                        <?php if ($event['event_type'] == 'hackathon'): 
                            // Check if Ideathon registration exists
                            $ideathon_check = "SELECT er.* FROM event_registrations er
                                              JOIN events e ON er.event_id = e.id
                                              WHERE er.team_lead_id = $user_id 
                                              AND e.event_type = 'ideathon'";
                            $ideathon_check_result = mysqli_query($conn, $ideathon_check);
                            if (mysqli_num_rows($ideathon_check_result) > 0):
                        ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> Hackathon is part of the same competition as Ideathon.
                                Your Ideathon team details will be automatically reused for Hackathon.
                                <?php if ($registered): ?>
                                    <br><strong>You are already registered for Hackathon with your Ideathon team details.</strong>
                                <?php endif; ?>
                            </div>
                        <?php endif; endif; ?>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $registered && $registration['event_id'] == $event_id ? 'Update Registration' : 'Register Team'; ?>
                            </button>
                            <a href="view_events.php" class="btn btn-secondary">Cancel</a>
                            
                            <?php if ($registered && $registration['event_id'] == $event_id): ?>
                                <a href="edit_registration.php?id=<?php echo $registration['id']; ?>" 
                                   class="btn btn-outline-primary">Edit Registration</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>