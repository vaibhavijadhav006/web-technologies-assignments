<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php'; // NEW

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}
checkRole('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $recipient_type = $_POST['recipient_type'];
    
    if ($recipient_type == 'all') {
        // For all users
        $success = createNotification($title, $message, null, 'all');
    } elseif ($recipient_type == 'students') {
        // For all students
        $success = createNotification($title, $message, null, 'student');
    } elseif ($recipient_type == 'mentors') {
        // For all mentors
        $success = createNotification($title, $message, null, 'mentor');
    } else {
        // For specific user (if implemented)
        $success = false;
    }
    
    if ($success) {
        $success_msg = "Notification sent successfully!";
    } else {
        $error = "Error sending notification!";
    }
}

// Get all notifications (admin sees all)
$query = "SELECT n.*, u.name as created_by_name 
          FROM notifications n
          LEFT JOIN users u ON n.created_by = u.id
          ORDER BY n.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notifications - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Send Notifications</a>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="btn btn-light">Back to Dashboard</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <?php if (isset($success_msg)): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h4>Send New Notification</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Recipient Type</label>
                                <select name="recipient_type" class="form-control" required>
                                    <option value="all">All Users</option>
                                    <option value="students">All Students</option>
                                    <option value="mentors">All Mentors</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notification Title *</label>
                                <input type="text" name="title" class="form-control" required 
                                       placeholder="e.g., Event Schedule Update">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" rows="5" required
                                          placeholder="Enter notification message..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Notification</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Templates</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary template-btn" 
                                    data-title="Reporting Time" 
                                    data-message="Please report to the venue by 9:30 AM sharp. Late entries will not be allowed.">
                                Reporting Time Reminder
                            </button>
                            <button type="button" class="btn btn-outline-primary template-btn"
                                    data-title="Event Schedule" 
                                    data-message="Event schedule has been updated. Please check the app for latest updates.">
                                Schedule Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Previous Notifications -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Previous Notifications</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php while($notif = mysqli_fetch_assoc($result)): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                            <small><?php echo formatNotificationTime($notif['created_at']); ?></small>
                        </div>
                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($notif['message'])); ?></p>
                        <small class="text-muted">
                            Sent to: 
                            <?php 
                            if ($notif['user_id']) {
                                echo "Specific User";
                            } else {
                                echo ucfirst($notif['role']) . 's';
                            }
                            ?>
                            <?php if ($notif['created_by_name']): ?>
                                | By: <?php echo htmlspecialchars($notif['created_by_name']); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.template-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelector('input[name="title"]').value = this.dataset.title;
                document.querySelector('textarea[name="message"]').value = this.dataset.message;
            });
        });
    </script>
</body>
</html>