<?php
// includes/functions.php
require_once 'config.php';

/**
 * Get notifications for a specific user
 * FIXED: Only show notifications for the logged-in user
 */
function getUserNotifications($user_id, $role, $limit = 10) {
    global $conn;
    
    // Only show notifications that are:
    // 1. Specifically for this user (user_id matches)
    // 2. For all users (role = 'all' AND user_id IS NULL)
    // 3. For this role (role matches AND user_id IS NULL)
    $query = "SELECT * FROM notifications 
              WHERE (user_id = ? OR (user_id IS NULL AND (role = 'all' OR role = ?)))
              ORDER BY created_at DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'isi', $user_id, $role, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $notifications = [];
    while($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    
    return $notifications;
}

/**
 * Create notification for specific user(s)
 */
function createNotification($title, $message, $user_id = null, $role = 'all') {
    global $conn;
    
    $query = "INSERT INTO notifications (title, message, user_id, role, created_by) 
              VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    mysqli_stmt_bind_param($stmt, 'ssiss', $title, $message, $user_id, $role, $created_by);
    return mysqli_stmt_execute($stmt);
}

/**
 * Check if mentor is already assigned to team
 */
function isMentorAlreadyAssigned($team_id, $mentor_id) {
    global $conn;
    
    $query = "SELECT mentor_id FROM event_registrations WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $team_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $team = mysqli_fetch_assoc($result);
    
    return $team && $team['mentor_id'] == $mentor_id;
}

/**
 * Get competition_id for team
 */
function getCompetitionId($team_lead_id, $event_type) {
    global $conn;
    
    // Try to find existing competition_id for this lead and event type
    $query = "SELECT competition_id FROM event_registrations er
              JOIN events e ON er.event_id = e.id
              WHERE er.team_lead_id = ? AND e.event_type = ?
              LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'is', $team_lead_id, $event_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row && $row['competition_id']) {
        return $row['competition_id'];
    }
    
    // Generate new competition_id
    return 'COMP-' . $team_lead_id . '-' . time() . '-' . rand(1000, 9999);
}

/**
 * Check if student already registered for event type
 */
function isAlreadyRegisteredForEventType($user_id, $event_type) {
    global $conn;
    
    $query = "SELECT er.id FROM event_registrations er
              JOIN events e ON er.event_id = e.id
              WHERE er.team_lead_id = ? AND e.event_type = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $event_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_num_rows($result) > 0;
}

/**
 * Get team details for notification
 */
function getTeamDetailsForNotification($team_id) {
    global $conn;
    
    $query = "SELECT er.team_name, er.team_number, u.name as lead_name, 
                     u.email as lead_email, e.name as event_name
              FROM event_registrations er
              JOIN users u ON er.team_lead_id = u.id
              JOIN events e ON er.event_id = e.id
              WHERE er.id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $team_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

/**
 * Format timestamp for notifications
 * Format: 01 Jan 2026 09:50
 */
function formatNotificationTime($timestamp) {
    return date('d M Y H:i', strtotime($timestamp));
}

// Note: logEmail() function is in includes/send_email.php
// It's automatically available since send_email.php is included via config.php

?>