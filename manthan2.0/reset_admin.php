<?php
require_once 'includes/config.php';

// Only allow from localhost for security
if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
    die("Access denied!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Check if admin exists
    $check_query = "SELECT id FROM users WHERE email = 'admin@manthan.com'";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing admin
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE email = 'admin@manthan.com'";
        if (mysqli_query($conn, $update_query)) {
            $message = "Admin password updated successfully!";
            $message .= "<br>New password: " . htmlspecialchars($new_password);
        } else {
            $error = "Error updating: " . mysqli_error($conn);
        }
    } else {
        // Create new admin
        $insert_query = "INSERT INTO users (role, name, email, password) 
                        VALUES ('admin', 'System Admin', 'admin@manthan.com', '$hashed_password')";
        if (mysqli_query($conn, $insert_query)) {
            $message = "Admin account created successfully!";
            $message .= "<br>Email: admin@manthan.com";
            $message .= "<br>Password: " . htmlspecialchars($new_password);
        } else {
            $error = "Error creating: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Admin Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4>Reset Admin Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($message)): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                            <a href="login.php" class="btn btn-primary">Go to Login</a>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>New Password for admin@manthan.com</label>
                                <input type="text" name="password" class="form-control" value="admin123" required>
                            </div>
                            <button type="submit" class="btn btn-danger">Reset Admin Password</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </form>
                        
                        <hr>
                        <h5>Current Admin Status:</h5>
                        <?php
                        $query = "SELECT id, name, email, role FROM users WHERE email = 'admin@manthan.com'";
                        $result = mysqli_query($conn, $query);
                        if (mysqli_num_rows($result) > 0) {
                            $admin = mysqli_fetch_assoc($result);
                            echo "<div class='alert alert-info'>";
                            echo "Admin exists:<br>";
                            echo "ID: " . $admin['id'] . "<br>";
                            echo "Name: " . $admin['name'] . "<br>";
                            echo "Email: " . $admin['email'] . "<br>";
                            echo "Role: " . $admin['role'];
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-warning'>No admin account found!</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>