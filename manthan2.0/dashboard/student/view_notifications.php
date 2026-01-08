<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

checkRole('student');

$user_id = $_SESSION['user_id'];

// Get all notifications for this student
$notifications = getUserNotifications($user_id, 'student', 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Notifications - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">Student Dashboard</span>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="../../logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="fas fa-bell"></i> All Notifications</h3>
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">My Notifications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($notifications)): ?>
                            <div class="list-group">
                                <?php foreach($notifications as $notif): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <i class="fas fa-bullhorn text-primary"></i> 
                                            <?php echo htmlspecialchars($notif['title']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo formatNotificationTime($notif['created_at']); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1 mt-2"><?php echo nl2br(htmlspecialchars($notif['message'])); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No notifications yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
