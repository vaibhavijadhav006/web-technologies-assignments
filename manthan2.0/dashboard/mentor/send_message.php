<?php
// dashboard/mentor/send_message.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check if user is logged in and is a mentor
if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

if ($_SESSION['role'] !== 'mentor') {
    header('Location: ../../dashboard/' . $_SESSION['role'] . '/index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mentor_name = $_SESSION['name'];

// Get mentor details
$mentor_query = "SELECT m.id FROM mentor_details m WHERE m.user_id = $user_id";
$mentor_result = mysqli_query($conn, $mentor_query);
$mentor = mysqli_fetch_assoc($mentor_result);

if (!$mentor) {
    die("Mentor details not found!");
}

$mentor_id = $mentor['id'];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;
    $subject = isset($_POST['subject']) ? mysqli_real_escape_string($conn, trim($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? mysqli_real_escape_string($conn, trim($_POST['message'])) : '';
    
    // Validate inputs
    $errors = [];
    
    if ($team_id <= 0) {
        $errors[] = "Invalid team selection!";
    }
    
    if (empty($subject) || strlen($subject) < 5) {
        $errors[] = "Subject must be at least 5 characters long!";
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long!";
    }
    
    // Check if mentor is assigned to this team
    $check_assignment = "SELECT id FROM event_registrations WHERE id = $team_id AND mentor_id = $mentor_id";
    $assignment_result = mysqli_query($conn, $check_assignment);
    
    if (mysqli_num_rows($assignment_result) == 0) {
        $errors[] = "You are not assigned to this team or the team doesn't exist!";
    }
    
    if (empty($errors)) {
        // Get team details
        $team_query = "SELECT er.*, u.name as lead_name, u.email as lead_email 
                      FROM event_registrations er
                      JOIN users u ON er.team_lead_id = u.id
                      WHERE er.id = $team_id";
        $team_result = mysqli_query($conn, $team_query);
        $team = mysqli_fetch_assoc($team_result);
        
        if ($team) {
            // In a real application, you would send email here
            // For now, just log the message and show success
            
            // Create message_logs table if not exists
            $create_table_query = "CREATE TABLE IF NOT EXISTS message_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                mentor_id INT NOT NULL,
                team_id INT NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (team_id) REFERENCES event_registrations(id) ON DELETE CASCADE
            )";
            mysqli_query($conn, $create_table_query);
            
            // Insert into message_logs
            $log_query = "INSERT INTO message_logs (mentor_id, team_id, subject, message) 
                         VALUES ($user_id, $team_id, '$subject', '$message')";
            
            if (mysqli_query($conn, $log_query)) {
                // Store success message in session
                $_SESSION['message_success'] = "Your message has been sent to team '{$team['team_name']}'!";
                
                // Redirect back to teams page
                header('Location: view_teams.php?team_id=' . $team_id . '&sent=1');
                exit();
            } else {
                $error_message = "Failed to log message: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Team not found!";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
    
    // If there are errors, redirect back with error message
    if (isset($error_message)) {
        $_SESSION['message_error'] = $error_message;
        header('Location: view_teams.php?team_id=' . $team_id . '&error=1');
        exit();
    }
} else {
    // If not POST request, redirect back
    header('Location: view_teams.php');
    exit();
}
?>