<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

checkRole('mentor');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get mentor details
$mentor_query = "SELECT m.*, u.name, u.email 
                 FROM mentor_details m 
                 JOIN users u ON m.user_id = u.id 
                 WHERE m.user_id = $user_id";
$mentor_result = mysqli_query($conn, $mentor_query);

if (!$mentor_result) {
    die("Database error: " . mysqli_error($conn));
}

$mentor = mysqli_fetch_assoc($mentor_result);

if (!$mentor) {
    die("Mentor details not found!");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $srn = mysqli_real_escape_string($conn, trim($_POST['srn']));
    $contact = mysqli_real_escape_string($conn, trim($_POST['contact']));
    $semester = intval($_POST['semester']);
    
    // Validation
    $errors = [];
    
    if (empty($name) || strlen($name) < 3) {
        $errors[] = "Name must be at least 3 characters long!";
    }
    
    if (empty($srn) || strlen($srn) < 3) {
        $errors[] = "SRN must be at least 3 characters long!";
    }
    
    if (empty($contact) || !preg_match('/^[0-9]{10}$/', $contact)) {
        $errors[] = "Contact must be a valid 10-digit number!";
    }
    
    if ($semester < 1 || $semester > 8) {
        $errors[] = "Semester must be between 1 and 8!";
    }
    
    if (empty($errors)) {
        // Update user name
        $update_user = "UPDATE users SET name = '$name' WHERE id = $user_id";
        
        // Update mentor details
        $update_mentor = "UPDATE mentor_details SET 
                         srn = '$srn',
                         contact = '$contact',
                         semester = $semester
                         WHERE user_id = $user_id";
        
        if (mysqli_query($conn, $update_user) && mysqli_query($conn, $update_mentor)) {
            // Update session name
            $_SESSION['name'] = $name;
            
            // Refresh mentor data
            $mentor_result = mysqli_query($conn, $mentor_query);
            $mentor = mysqli_fetch_assoc($mentor_result);
            
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chalkboard-teacher"></i> Mentor Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-white">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                </span>
                <a href="../../logout.php" class="btn btn-light btn-sm ms-2">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i> Full Name
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($mentor['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email" 
                                       value="<?php echo htmlspecialchars($mentor['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="srn" class="form-label">
                                    <i class="fas fa-id-card"></i> SRN
                                </label>
                                <input type="text" class="form-control" id="srn" name="srn" 
                                       value="<?php echo htmlspecialchars($mentor['srn']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contact" class="form-label">
                                    <i class="fas fa-phone"></i> Contact Number
                                </label>
                                <input type="tel" class="form-control" id="contact" name="contact" 
                                       value="<?php echo htmlspecialchars($mentor['contact']); ?>" 
                                       pattern="[0-9]{10}" maxlength="10" required>
                                <small class="text-muted">Enter 10-digit contact number</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="semester" class="form-label">
                                    <i class="fas fa-graduation-cap"></i> Semester
                                </label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <?php for($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?php echo $i; ?>" 
                                                <?php echo $mentor['semester'] == $i ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>th Semester
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
