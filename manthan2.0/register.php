<?php
require_once 'includes/config.php';

$role = isset($_GET['role']) ? $_GET['role'] : 'student';

// PREVENT ADMIN REGISTRATION IF ALREADY EXISTS - ONLY FOR ADMIN ROLE
if ($role == 'admin') {
    $check_admin = "SELECT id, name, email FROM users WHERE role = 'admin'";
    $result = mysqli_query($conn, $check_admin);
    
    if (mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        ?>
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
        }
        .access-card {
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            border: none;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
        }
        .admin-header {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            padding: 40px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .admin-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-50px, -50px) rotate(360deg); }
        }
        .icon-large {
            font-size: 5rem;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            animation: pulse 2s infinite, floatIcon 3s ease-in-out infinite;
        }
        @keyframes pulse {
            0% { text-shadow: 0 0 10px rgba(255,255,255,0.5); }
            50% { text-shadow: 0 0 20px rgba(255,255,255,0.8); }
            100% { text-shadow: 0 0 10px rgba(255,255,255,0.5); }
        }
        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .admin-info {
            background: linear-gradient(45deg, #36d1dc, #5b86e5);
            color: white;
            border-radius: 20px;
            padding: 25px;
            margin: 25px 0;
            box-shadow: 0 10px 30px rgba(91, 134, 229, 0.4);
            position: relative;
            overflow: hidden;
        }
        .admin-info::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(30deg);
        }
        .action-buttons .btn {
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }
        .action-buttons .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        .action-buttons .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        .admin-details {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            border-left: 5px solid #ffd93d;
            margin-top: 15px;
        }
        .glow-text {
            text-shadow: 0 0 10px rgba(255,255,255,0.7);
        }
        .security-badge {
            background: linear-gradient(45deg, #ffd93d, #ffc107);
            color: #000;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin: 5px;
            font-weight: 600;
        }
        .feature-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 20px;
            margin: 10px 0;
            border-left: 5px solid #0d6efd;
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card access-card animate__animated animate__fadeIn">
                    <div class="admin-header">
                        <div class="icon-large">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h1 class="glow-text"><i class="fas fa-ban"></i> ADMIN REGISTRATION LOCKED</h1>
                        <p class="lead mb-0">Maximum Security Protocol Activated</p>
                        <div class="mt-3">
                            <span class="security-badge"><i class="fas fa-lock"></i> Single Admin Policy</span>
                            <span class="security-badge"><i class="fas fa-shield-alt"></i> Enhanced Security</span>
                            <span class="security-badge"><i class="fas fa-user-check"></i> Access Controlled</span>
                        </div>
                    </div>
                    
                    <div class="card-body p-5">
                        <!-- Warning Alert -->
                        <div class="alert alert-danger border-0 mb-4" style="background: linear-gradient(45deg, #ff6b6b, #ee5a52); color: white;">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                                <div>
                                    <h4 class="alert-heading mb-2">ACCESS DENIED - SECURITY RESTRICTION</h4>
                                    <p class="mb-0">
                                        The Manthan 2.0 system already has an active administrator. For enhanced security and 
                                        system integrity, only <strong>ONE administrator account</strong> is permitted per installation.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Current Admin Info -->
                        <div class="admin-info animate__animated animate__slideInLeft">
                            <h4 class="text-center mb-4">
                                <i class="fas fa-user-shield"></i> ACTIVE SYSTEM ADMINISTRATOR
                            </h4>
                            <div class="admin-details">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3">
                                                <i class="fas fa-user-circle fa-2x text-warning"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($admin['name']); ?></h5>
                                                <p class="mb-0 text-white-50">
                                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($admin['email']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <span class="badge bg-success fs-6 p-2">
                                            <i class="fas fa-check-circle"></i> ACTIVE
                                        </span>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <small class="text-white-75">
                                        <i class="fas fa-info-circle"></i> 
                                        This administrator account was established during the initial system configuration 
                                        and holds full system privileges.
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alternative Options -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <div class="feature-card">
                                    <h6><i class="fas fa-sign-in-alt text-primary"></i> ADMIN LOGIN</h6>
                                    <p class="mb-2">Already have admin credentials? Access the admin panel.</p>
                                    <a href="login.php" class="btn btn-primary w-100">
                                        <i class="fas fa-key"></i> Login to Admin Panel
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="feature-card">
                                    <h6><i class="fas fa-chalkboard-teacher text-success"></i> BECOME A MENTOR</h6>
                                    <p class="mb-2">Guide and mentor student teams as they participate in events.</p>
                                    <a href="register.php?role=mentor" class="btn btn-success w-100">
                                        <i class="fas fa-user-plus"></i> Register as Mentor
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- More Options -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <div class="feature-card">
                                    <h6><i class="fas fa-user-graduate text-info"></i> JOIN AS PARTICIPANT</h6>
                                    <p class="mb-2">Register as a student participant and compete in exciting events.</p>
                                    <a href="register.php?role=student" class="btn btn-info w-100 text-white">
                                        <i class="fas fa-user-graduate"></i> Register as Student
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="feature-card">
                                    <h6><i class="fas fa-home text-secondary"></i> RETURN HOME</h6>
                                    <p class="mb-2">Go back to the main homepage for more information.</p>
                                    <a href="index.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-arrow-left"></i> Back to Homepage
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Security Information -->
                        <div class="alert alert-dark border-0 mt-4" style="background: linear-gradient(45deg, #2d3436, #636e72); color: white;">
                            <h6><i class="fas fa-shield-alt"></i> SECURITY POLICY INFORMATION</h6>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <p class="mb-1"><i class="fas fa-check-circle text-success"></i> Single Admin Policy</p>
                                    <small>Prevents conflicting administrative actions</small>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><i class="fas fa-check-circle text-success"></i> Enhanced Security</p>
                                    <small>Reduces attack surface area</small>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><i class="fas fa-check-circle text-success"></i> Centralized Control</p>
                                    <small>Ensures consistent system management</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted mb-2">
                                <i class="fas fa-exclamation-circle"></i> 
                                <strong>Need Administrative Access?</strong>
                            </p>
                            <p class="text-muted mb-3">
                                If you require administrative privileges, please:
                                <br>
                                1. Contact the current system administrator
                                <br>
                                2. Use existing admin credentials (if authorized)
                                <br>
                                3. For emergencies, contact system support
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-lock"></i> 
                                Admin registration is permanently disabled after initial setup for security reasons.
                            </small>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="card-footer text-center py-4" style="background: linear-gradient(45deg, #2d3436, #1a1a2e); color: white;">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <i class="fas fa-shield-alt fa-2x text-warning"></i>
                            </div>
                            <div class="col-md-4">
                                <h5 class="mb-0">Manthan 2.0</h5>
                                <small>Event Management System</small>
                            </div>
                            <div class="col-md-4">
                                <small>
                                    <i class="fas fa-copyright"></i> <?php echo date('Y'); ?> All Rights Reserved
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced animations
        document.addEventListener('DOMContentLoaded', function() {
            // Button hover effects
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.05)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Add staggered animations
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
                card.classList.add('animate__animated', 'animate__fadeInUp');
            });
            
            // Add floating effect to security badges
            const badges = document.querySelectorAll('.security-badge');
            badges.forEach((badge, index) => {
                badge.style.animationDelay = (index * 0.2) + 's';
                badge.classList.add('animate__animated', 'animate__pulse');
            });
            
            // Auto-rotate badges animation
            setInterval(() => {
                badges.forEach(badge => {
                    badge.classList.remove('animate__pulse');
                    void badge.offsetWidth; // Trigger reflow
                    badge.classList.add('animate__pulse');
                });
            }, 3000);
        });
        
        // Add confetti effect on page load
        function createConfetti() {
            const colors = ['#ff416c', '#36d1dc', '#5b86e5', '#ffd93d', '#0d6efd'];
            const container = document.querySelector('.admin-header');
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.cssText = `
                    position: absolute;
                    width: 10px;
                    height: 10px;
                    background: ${colors[Math.floor(Math.random() * colors.length)]};
                    border-radius: 50%;
                    top: -20px;
                    left: ${Math.random() * 100}%;
                    opacity: 0.7;
                    z-index: 0;
                `;
                container.appendChild(confetti);
                
                // Animate confetti
                const animation = confetti.animate([
                    { transform: 'translateY(0) rotate(0deg)', opacity: 0.7 },
                    { transform: `translateY(${window.innerHeight}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                ], {
                    duration: 2000 + Math.random() * 3000,
                    easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
                });
                
                animation.onfinish = () => confetti.remove();
            }
        }
        
        // Run confetti after page loads
        window.addEventListener('load', function() {
            setTimeout(createConfetti, 500);
        });
    </script>
</body>
</html>
        <?php
        exit(); // EXIT HERE - ONLY FOR ADMIN REGISTRATION ATTEMPT
    }
}

// ====================================================
// REGULAR REGISTRATION CODE FOR STUDENT AND MENTOR
// ====================================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);
    
    if (mysqli_num_rows($result) > 0) {
        $error = "Email already registered!";
    } else {
        // Insert user into users table
        $query = "INSERT INTO users (role, name, email, password) 
                  VALUES ('$role', '$name', '$email', '$password')";
        
        if (mysqli_query($conn, $query)) {
            $user_id = mysqli_insert_id($conn);
            
            // Insert role-specific details
            if ($role == 'student') {
                $school = mysqli_real_escape_string($conn, $_POST['school']);
                $standard = intval($_POST['standard']);
                $query = "INSERT INTO student_details (user_id, school, standard) 
                          VALUES ($user_id, '$school', $standard)";
                mysqli_query($conn, $query);
            } elseif ($role == 'mentor') {
                $srn = mysqli_real_escape_string($conn, $_POST['srn']);
                $contact = mysqli_real_escape_string($conn, $_POST['contact']);
                $semester = intval($_POST['semester']);
                $query = "INSERT INTO mentor_details (user_id, srn, contact, semester) 
                          VALUES ($user_id, '$srn', '$contact', $semester)";
                mysqli_query($conn, $query);
            } elseif ($role == 'admin') {
                // For first-time admin registration (should only happen once)
                // Admin doesn't need extra details
            }
            
            // Store in session and redirect
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            $_SESSION['name'] = $name;
            
            // Redirect to appropriate dashboard
            if ($role == 'admin') {
                header('Location: dashboard/admin/index.php');
            } else {
                header('Location: dashboard/' . $role . '/index.php');
            }
            exit();
        } else {
            $error = "Registration failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
        }
        .admin-note {
            background: linear-gradient(45deg, #ffc107, #ffca2c);
            color: #000;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header text-center text-white" 
                         style="background: <?php 
                            echo $role == 'student' ? 'linear-gradient(45deg, #0d6efd, #0b5ed7)' : 
                                   ($role == 'mentor' ? 'linear-gradient(45deg, #198754, #157347)' : 
                                   'linear-gradient(45deg, #ffc107, #ffca2c)'); ?>">
                        <h4>
                            <?php if($role == 'admin'): ?>
                                <i class="fas fa-user-shield"></i> 
                            <?php elseif($role == 'student'): ?>
                                <i class="fas fa-user-graduate"></i> 
                            <?php else: ?>
                                <i class="fas fa-chalkboard-teacher"></i> 
                            <?php endif; ?>
                            Register as <?php echo ucfirst($role); ?>
                        </h4>
                        <?php if($role == 'admin'): ?>
                            <p class="mb-0"><small>System Administrator Registration</small></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($role == 'admin'): ?>
                            <div class="admin-note">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Important:</strong> Only one admin is allowed in the system.
                                This registration will create the main administrator account.
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="registrationForm">
                            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Full Name *
                                </label>
                                <input type="text" name="name" class="form-control" required 
                                       placeholder="Enter your full name">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> Email *
                                </label>
                                <input type="email" name="email" class="form-control" required 
                                       placeholder="example@email.com">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> Password *
                                </label>
                                <input type="password" name="password" class="form-control" required 
                                       minlength="6" placeholder="Minimum 6 characters">
                                <small class="text-muted">Password must be at least 6 characters long</small>
                            </div>
                            
                            <?php if ($role == 'student'): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-school"></i> School *
                                    </label>
                                    <input type="text" name="school" class="form-control" required 
                                           placeholder="Your school name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-graduation-cap"></i> Standard *
                                    </label>
                                    <select name="standard" class="form-control" required>
                                        <option value="">Select Standard</option>
                                        <?php for($i=8; $i<=12; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?>th Standard</option>
                                        <?php endfor; ?>
                                    </select>
                                    <small class="text-muted">Must be 8th standard or above</small>
                                </div>
                            <?php elseif ($role == 'mentor'): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-id-card"></i> SRN (Student Registration Number) *
                                    </label>
                                    <input type="text" name="srn" class="form-control" required 
                                           placeholder="e.g., 01JST20CS001">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-phone"></i> Contact Number *
                                    </label>
                                    <input type="tel" name="contact" class="form-control" required 
                                           pattern="[0-9]{10}" placeholder="10-digit mobile number">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt"></i> Semester *
                                    </label>
                                    <select name="semester" class="form-control" required>
                                        <option value="">Select Semester</option>
                                        <?php for($i=1; $i<=7; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?>th Semester</option>
                                        <?php endfor; ?>
                                    </select>
                                    <small class="text-muted">Current semester (1st to 7th)</small>
                                </div>
                            <?php elseif ($role == 'admin'): ?>
                                <div class="mb-3 alert alert-warning">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Admin Note:</strong> This account will have full system access including:
                                    <ul class="mb-0 mt-1">
                                        <li>Manage all users</li>
                                        <li>Assign mentors to teams</li>
                                        <li>Send notifications</li>
                                        <li>Generate certificates</li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn 
                                    <?php echo $role == 'student' ? 'btn-primary' : 
                                           ($role == 'mentor' ? 'btn-success' : 'btn-warning'); ?>">
                                    <i class="fas fa-user-plus"></i> Register as <?php echo ucfirst($role); ?>
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Homepage
                                </a>
                            </div>
                            
                            <div class="text-center mt-3">
                                <small>
                                    Already have an account? 
                                    <a href="login.php" class="text-decoration-none">
                                        <i class="fas fa-sign-in-alt"></i> Login here
                                    </a>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            let valid = true;
            const inputs = this.querySelectorAll('input[required], select[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    valid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            // Validate password length
            const password = document.querySelector('input[name="password"]');
            if (password && password.value.length < 6) {
                password.classList.add('is-invalid');
                valid = false;
            }
            
            // Validate email format
            const email = document.querySelector('input[name="email"]');
            if (email && !email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                email.classList.add('is-invalid');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill all required fields correctly!');
            }
        });
    </script>
</body>
</html>