<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}
checkRole('student');

$user_id = $_SESSION['user_id'];
$registration_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get registration details
$query = "SELECT er.*, e.name as event_name 
          FROM event_registrations er
          JOIN events e ON er.event_id = e.id
          WHERE er.id = $registration_id AND er.team_lead_id = $user_id";
$result = mysqli_query($conn, $query);
$registration = mysqli_fetch_assoc($result);

if (!$registration) {
    die("Registration not found or access denied!");
}

// Check edit count
if ($registration['edit_count'] >= 2) {
    die("Maximum edit limit (2 times) reached! You cannot edit this registration further.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_name = mysqli_real_escape_string($conn, $_POST['team_name']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $member1 = mysqli_real_escape_string($conn, $_POST['member1']);
    $member2 = mysqli_real_escape_string($conn, $_POST['member2']);
    $member3 = mysqli_real_escape_string($conn, $_POST['member3']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    
    $update_query = "UPDATE event_registrations SET 
                     team_name = '$team_name',
                     title = '$title',
                     team_member1 = '$member1',
                     team_member2 = '$member2',
                     team_member3 = '$member3',
                     contact_email = '$contact_email',
                     edit_count = edit_count + 1
                     WHERE id = $registration_id";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Registration updated successfully!";
        // Refresh data
        $result = mysqli_query($conn, $query);
        $registration = mysqli_fetch_assoc($result);
    } else {
        $error = "Error updating registration: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Registration - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Edit Registration</a>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="btn btn-light">Back to Dashboard</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Registration for <?php echo $registration['event_name']; ?></h4>
                        <p class="mb-0">
                            Edit Count: <?php echo $registration['edit_count']; ?> of 2 allowed |
                            Last edit: <?php echo ($registration['edit_count'] > 0) ? date('d M Y H:i', strtotime($registration['registered_at'])) : 'Never'; ?>
                        </p>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Team Name *</label>
                                    <input type="text" name="team_name" class="form-control" required
                                           value="<?php echo $registration['team_name']; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Project Title *</label>
                                    <input type="text" name="title" class="form-control" required
                                           value="<?php echo $registration['title']; ?>">
                                </div>
                            </div>
                            
                            <h5>Team Members</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Member 2 Name *</label>
                                    <input type="text" name="member1" class="form-control" required
                                           value="<?php echo $registration['team_member1']; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Member 3 Name *</label>
                                    <input type="text" name="member2" class="form-control" required
                                           value="<?php echo $registration['team_member2']; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Member 4 Name *</label>
                                    <input type="text" name="member3" class="form-control" required
                                           value="<?php echo $registration['team_member3']; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label>Contact Email *</label>
                                <input type="email" name="contact_email" class="form-control" required
                                       value="<?php echo $registration['contact_email']; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <div class="alert alert-warning">
                                    <strong>Note:</strong> You can edit your registration only <?php echo 2 - $registration['edit_count']; ?> more time(s) after this.
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Registration</button>
                            <a href="view_events.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>