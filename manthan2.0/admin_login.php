<?php
require_once 'includes/config.php';

// Redirect to regular login if not accessing as admin
if (!isset($_GET['admin']) || $_GET['admin'] != 'true') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email'], $conn);
    $password = $_POST['password'];
    
    // Check if admin
    $query = "SELECT * FROM users WHERE email = '$email' AND role = 'admin'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            
            header('Location: dashboard/admin/index.php');
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Admin account not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .admin-card {
            border: 3px solid #ffc107;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card admin-card">
                    <div class="card-header text-center bg-warning">
                        <h4><i class="fas fa-user-shield"></i> Admin Login</h4>
                        <p class="mb-0"><small>System Administrator Access</small></p>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>System Admin:</strong> johnadmin@manthan.com
                        </div>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Admin Email</label>
                                <input type="email" name="email" class="form-control" required 
                                       placeholder="admin@manthan.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-sign-in-alt"></i> Login as Admin
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-home"></i> Back to Homepage
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