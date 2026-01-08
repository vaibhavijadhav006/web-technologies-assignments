<?php
// includes/send_email.php
// Note: config.php is included by the file that includes this file
// We check if $conn is set to avoid circular dependency
if (!isset($conn)) {
    require_once 'config.php';
}

/**
 * Send email using PHPMailer
 */
function sendEmail($to_email, $to_name, $subject, $body, $is_html = false) {
    // Check if PHPMailer files exist
    $phpmailer_path = __DIR__ . '/../phpmailer/PHPMailer.php';
    $smtp_path = __DIR__ . '/../phpmailer/SMTP.php';
    $exception_path = __DIR__ . '/../phpmailer/Exception.php';
    
    if (!file_exists($phpmailer_path) || !file_exists($smtp_path) || !file_exists($exception_path)) {
        // Log error
        logEmail($to_email, $to_name, $subject, $body, 'failed', 'PHPMailer files not found');
        return false;
    }
    
    try {
        // Use correct path for PHPMailer (in project root phpmailer folder)
        require_once $phpmailer_path;
        require_once $smtp_path;
        require_once $exception_path;
        
        // Check if class exists after requiring files
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            logEmail($to_email, $to_name, $subject, $body, 'failed', 'PHPMailer class not found after loading files');
            return false;
        }
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings - Brevo SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        
        if ($is_html) {
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
        } else {
            $mail->Body = $body;
        }
        
        // Send email
        $mail->send();
        
        // Log success
        logEmail($to_email, $to_name, $subject, $body, 'sent');
        
        return true;
        
    } catch (Exception $e) {
        // Log error
        logEmail($to_email, $to_name, $subject, $body, 'failed', $mail->ErrorInfo);
        
        // For development, show error
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "Email could not be sent. Error: {$mail->ErrorInfo}";
        }
        
        return false;
    }
}

/**
 * Send mentor assignment email
 */
function sendMentorAssignmentEmail($student_email, $student_name, $team_details, $mentor_details) {
    $subject = "Mentor Assigned - Team " . $team_details['team_number'];
    
    $body = "Dear " . $student_name . ",\n\n" .
           "We are pleased to inform you that a mentor has been assigned to your team for Manthan 2.0.\n\n" .
           "Team Details:\n" .
           "-------------\n" .
           "Team Number: " . $team_details['team_number'] . "\n" .
           "Team Name: " . $team_details['team_name'] . "\n" .
           "Event: " . $team_details['event_name'] . "\n\n" .
           "Mentor Details:\n" .
           "----------------\n" .
           "Name: " . $mentor_details['name'] . "\n" .
           "Contact: " . $mentor_details['contact'] . "\n" .
           "Email: " . $mentor_details['email'] . "\n\n" .
           "Your mentor will contact you shortly to guide you through the competition.\n\n" .
           "Best regards,\n" .
           "Manthan 2.0 Team\n" .
           "KLE Technological University";
    
    return sendEmail($student_email, $student_name, $subject, $body, false);
}

/**
 * Log email sending attempts
 */
function logEmail($recipient_email, $recipient_name, $subject, $message, $status, $error_message = '') {
    global $conn;
    
    if (!isset($conn)) {
        require_once 'config.php';
    }
    
    $query = "INSERT INTO email_logs 
              (recipient_email, recipient_name, subject, message, status, error_message)
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ssssss', 
        $recipient_email, $recipient_name, $subject, $message, $status, $error_message);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/**
 * Send welcome email to new users
 */
function sendWelcomeEmail($email, $name, $username, $password = null) {
    $subject = "Welcome to Manthan 2.0 - Registration Successful";
    
    $password_text = $password ? "Your password: $password\n" : "Use the password you created during registration.\n";
    
    $body = "Dear " . $name . ",\n\n" .
           "Welcome to Manthan 2.0!\n\n" .
           "Your registration has been successfully completed.\n\n" .
           "Account Details:\n" .
           "----------------\n" .
           "Username: " . $username . "\n" .
           $password_text . "\n" .
           "You can now log in to your account and participate in the competition.\n\n" .
           "Please keep your login credentials secure.\n\n" .
           "Best regards,\n" .
           "Manthan 2.0 Team\n" .
           "KLE Technological University";
    
    return sendEmail($email, $name, $subject, $body, false);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $name, $reset_token) {
    $subject = "Manthan 2.0 - Password Reset Request";
    
    $reset_link = BASE_URL . "/reset_password.php?token=" . urlencode($reset_token);
    
    $body = "Dear " . $name . ",\n\n" .
           "We received a request to reset your password for your Manthan 2.0 account.\n\n" .
           "To reset your password, please click on the following link:\n" .
           $reset_link . "\n\n" .
           "If you did not request a password reset, please ignore this email.\n\n" .
           "This link will expire in 24 hours.\n\n" .
           "Best regards,\n" .
           "Manthan 2.0 Team\n" .
           "KLE Technological University";
    
    return sendEmail($email, $name, $subject, $body, false);
}

/**
 * Send team formation notification email
 */
function sendTeamFormationEmail($student_email, $student_name, $team_details) {
    $subject = "Team Formation Successful - Team " . $team_details['team_number'];
    
    $body = "Dear " . $student_name . ",\n\n" .
           "Congratulations! Your team has been successfully formed for Manthan 2.0.\n\n" .
           "Team Details:\n" .
           "-------------\n" .
           "Team Number: " . $team_details['team_number'] . "\n" .
           "Team Name: " . $team_details['team_name'] . "\n" .
           "Event: " . $team_details['event_name'] . "\n" .
           "Team Leader: " . $team_details['team_leader'] . "\n\n" .
           "Team Members:\n";
    
    foreach ($team_details['members'] as $member) {
        $body .= "- " . $member['name'] . " (" . $member['usn'] . ")\n";
    }
    
    $body .= "\nYour team will be assigned a mentor shortly.\n\n" .
            "Best regards,\n" .
            "Manthan 2.0 Team\n" .
            "KLE Technological University";
    
    return sendEmail($student_email, $student_name, $subject, $body, false);
}

/**
 * Send event update notification email
 */
function sendEventUpdateEmail($email, $name, $event_name, $update_details) {
    $subject = "Important Update - " . $event_name . " - Manthan 2.0";
    
    $body = "Dear " . $name . ",\n\n" .
           "This is an important update regarding the event: " . $event_name . "\n\n" .
           "Update Details:\n" .
           "--------------\n" .
           $update_details . "\n\n" .
           "Please make note of these changes and plan accordingly.\n\n" .
           "Best regards,\n" .
           "Manthan 2.0 Team\n" .
           "KLE Technological University";
    
    return sendEmail($email, $name, $subject, $body, false);
}
?>