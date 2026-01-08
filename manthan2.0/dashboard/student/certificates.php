<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}
checkRole('student');

$user_id = $_SESSION['user_id'];

// Check if student participated in events
$query = "SELECT c.*, e.name as event_name, er.team_name, er.title as project_title
          FROM certificates c
          JOIN event_registrations er ON c.registration_id = er.id
          JOIN events e ON er.event_id = e.id
          WHERE c.participant_id = $user_id
          ORDER BY c.generated_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">My Certificates</a>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="btn btn-light">Back to Dashboard</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h3>Your Certificates</h3>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row">
                <?php while($cert = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Certificate of Participation</h5>
                            <p class="card-text">
                                <strong>Event:</strong> <?php echo $cert['event_name']; ?><br>
                                <strong>Team:</strong> <?php echo $cert['team_name']; ?><br>
                                <strong>Project:</strong> <?php echo $cert['project_title']; ?><br>
                                <strong>Certificate Code:</strong> <?php echo $cert['certificate_code']; ?>
                            </p>
                            <div class="mt-3">
                                <a href="generate_certificate.php?id=<?php echo $cert['id']; ?>" 
                                   class="btn btn-success">Download Certificate (PDF)</a>
                            </div>
                        </div>
                        <div class="card-footer">
                            <small>Generated on: <?php echo date('d M Y', strtotime($cert['generated_at'])); ?></small>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h5>No certificates available yet</h5>
                <p>Certificates will be available after event participation and approval by admin.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>