<?php
// Setup script for Manthan 2.0
echo "<h2>Manthan 2.0 Setup Check</h2>";

// Check directory structure
$required_dirs = ['includes', 'dashboard/admin', 'dashboard/student', 'dashboard/mentor', 'assets/css', 'fpdf'];
$required_files = ['index.php', 'register.php', 'login.php', 'logout.php', 'includes/config.php'];

echo "<h3>Checking Directory Structure:</h3>";
foreach ($required_dirs as $dir) {
    if (is_dir($dir)) {
        echo "<div style='color:green'>✓ Directory exists: $dir</div>";
    } else {
        echo "<div style='color:red'>✗ Missing directory: $dir</div>";
    }
}

echo "<h3>Checking Required Files:</h3>";
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<div style='color:green'>✓ File exists: $file</div>";
    } else {
        echo "<div style='color:red'>✗ Missing file: $file</div>";
    }
}

// Check database connection
echo "<h3>Checking Database Connection:</h3>";
$conn = mysqli_connect('localhost', 'root', '', 'manthan_system');
if ($conn) {
    echo "<div style='color:green'>✓ Database connected successfully</div>";
    
    // Check tables
    $tables = ['users', 'student_details', 'mentor_details', 'events', 'event_registrations'];
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($result) > 0) {
            echo "<div style='color:green'>✓ Table exists: $table</div>";
        } else {
            echo "<div style='color:red'>✗ Missing table: $table</div>";
        }
    }
} else {
    echo "<div style='color:red'>✗ Database connection failed</div>";
}

echo "<h3>Setup Complete!</h3>";
echo "<a href='index.php' class='btn btn-primary'>Go to Homepage</a>";
?>