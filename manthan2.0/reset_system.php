<?php
require_once 'includes/config.php';

// SECURITY CHECK - Only allow from localhost
if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
    die("Access denied!");
}

// Password protection
$admin_pass = "reset123"; // Change this to a strong password

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['password'] != $admin_pass) {
        $error = "Invalid password!";
    } else {
        // Reset database
        $queries = [
            "DELETE FROM certificates",
            "DELETE FROM notifications",
            "DELETE FROM event_registrations", 
            "DELETE FROM participation",
            "DELETE FROM mentor_details",
            "DELETE FROM student_details",
            "DELETE FROM users WHERE role != 'admin'",
            "DELETE FROM events",
            
            // Reset auto-increment
            "ALTER TABLE certificates AUTO_INCREMENT = 1",
            "ALTER TABLE notifications AUTO_INCREMENT = 1", 
            "ALTER TABLE event_registrations AUTO_INCREMENT = 1",
            "ALTER TABLE participation AUTO_INCREMENT = 1",
            "ALTER TABLE mentor_details AUTO_INCREMENT = 1",
            "ALTER TABLE student_details AUTO_INCREMENT = 1",
            "ALTER TABLE users AUTO_INCREMENT = 2", // Keep admin as ID 1
            "ALTER TABLE events AUTO_INCREMENT = 1",
            
            // Re-insert events
            "INSERT INTO events (name, date, venue, reporting_time, status) VALUES
            ('Ideathon', '2026-12-06', 'KLE Technological University Belagavi', '10:00:00', 'upcoming'),
            ('Hackathon', '2027-01-03', 'KLE Technological University Belagavi', '10:00:00', 'upcoming')"
        ];
        
        foreach ($queries as $query) {
            mysqli_query($conn, $query);
        }
        
        $success = "System reset successfully! All data cleared except admin account.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-danger">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-danger text-white text-center">
                        <h4><i class="fas fa-exclamation-triangle"></i> DANGER: System Reset</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-skull-crossbones"></i> WARNING!</h5>
                            <p>This will delete ALL data except the admin account. This action cannot be undone!</p>
                            <p><strong>Will be deleted:</strong></p>
                            <ul>
                                <li>All student registrations</li>
                                <li>All mentor registrations</li>
                                <li>All event registrations</li>
                                <li>All certificates</li>
                                <li>All notifications</li>
                            </ul>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-warning"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>Enter Admin Password to Confirm:</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Default: reset123 (Change this in code!)</small>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-bomb"></i> RESET ALL DATA
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>