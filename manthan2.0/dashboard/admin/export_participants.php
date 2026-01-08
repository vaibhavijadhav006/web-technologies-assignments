<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}
checkRole('admin');

// Fetch data
$query = "SELECT er.team_number, er.team_name, e.name as event_name, 
                 u.name as lead_name, er.team_member1, er.team_member2, 
                 er.team_member3, er.standard, er.contact_email,
                 mu.name as mentor_name, er.registered_at
          FROM event_registrations er
          JOIN events e ON er.event_id = e.id
          JOIN users u ON er.team_lead_id = u.id
          LEFT JOIN mentor_details m ON er.mentor_id = m.id
          LEFT JOIN users mu ON m.user_id = mu.id
          ORDER BY er.event_id, er.team_number";
$result = mysqli_query($conn, $query);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="manthan_participants_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Excel header
echo "Manthan 2.0 - Participants List\n\n";
echo "Generated on: " . date('d F Y H:i:s') . "\n\n";

// Table headers
echo "Team No\tTeam Name\tEvent\tTeam Lead\tMember 2\tMember 3\tMember 4\tStandard\tContact Email\tMentor\tRegistered On\n";

// Data rows
while($row = mysqli_fetch_assoc($result)) {
    echo $row['team_number'] . "\t";
    echo $row['team_name'] . "\t";
    echo $row['event_name'] . "\t";
    echo $row['lead_name'] . "\t";
    echo $row['team_member1'] . "\t";
    echo $row['team_member2'] . "\t";
    echo $row['team_member3'] . "\t";
    echo $row['standard'] . "th\t";
    echo $row['contact_email'] . "\t";
    echo $row['mentor_name'] . "\t";
    echo date('d M Y', strtotime($row['registered_at'])) . "\n";
}
?>