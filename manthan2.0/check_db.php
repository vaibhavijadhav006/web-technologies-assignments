<?php
require_once 'includes/config.php';

echo "<h2>Database Connection Test</h2>";

// Test basic queries
$test_queries = [
    "SELECT COUNT(*) as count FROM users" => "Users",
    "SELECT COUNT(*) as count FROM events" => "Events",
    "SELECT COUNT(*) as count FROM event_registrations" => "Registrations",
    "SELECT COUNT(*) as count FROM mentor_details" => "Mentors",
    "SELECT COUNT(*) as count FROM student_details" => "Students"
];

foreach ($test_queries as $query => $name) {
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<div style='color:green'>✓ $name: " . $row['count'] . "</div>";
    } else {
        echo "<div style='color:red'>✗ $name: Query failed - " . mysqli_error($conn) . "</div>";
    }
}

// Test specific mentor query
echo "<h3>Testing Mentor Query:</h3>";
$test_query = "SELECT m.*, u.name as user_name 
               FROM mentor_details m 
               JOIN users u ON m.user_id = u.id 
               LIMIT 1";
$test_result = mysqli_query($conn, $test_query);

if ($test_result) {
    if (mysqli_num_rows($test_result) > 0) {
        $mentor = mysqli_fetch_assoc($test_result);
        echo "<div style='color:green'>✓ Mentor query works: " . $mentor['user_name'] . "</div>";
    } else {
        echo "<div style='color:orange'>⚠ No mentors found (this is OK if not registered)</div>";
    }
} else {
    echo "<div style='color:red'>✗ Mentor query failed: " . mysqli_error($conn) . "</div>";
}
?>