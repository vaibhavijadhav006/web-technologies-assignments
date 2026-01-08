<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}
checkRole('admin');

// Get all event registrations with filters
$event_filter = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$mentor_filter = isset($_GET['mentor_id']) ? intval($_GET['mentor_id']) : 0;

$query = "SELECT er.*, e.name as event_name, u.name as lead_name, 
                 m.user_id as mentor_user_id, mu.name as mentor_name
          FROM event_registrations er
          JOIN events e ON er.event_id = e.id
          JOIN users u ON er.team_lead_id = u.id
          LEFT JOIN mentor_details m ON er.mentor_id = m.id
          LEFT JOIN users mu ON m.user_id = mu.id
          WHERE 1=1";

if ($event_filter > 0) {
    $query .= " AND er.event_id = $event_filter";
}
if ($mentor_filter > 0) {
    $query .= " AND er.mentor_id = $mentor_filter";
}

$query .= " ORDER BY er.registered_at DESC";
$result = mysqli_query($conn, $query);

// Count statistics
$total_teams = mysqli_num_rows($result);
$with_mentor = 0;
$without_mentor = 0;

if ($total_teams > 0) {
    mysqli_data_seek($result, 0);
    while($row = mysqli_fetch_assoc($result)) {
        if ($row['mentor_name']) {
            $with_mentor++;
        } else {
            $without_mentor++;
        }
    }
    mysqli_data_seek($result, 0); // Reset pointer
}

// Get events for filter
$events_query = "SELECT * FROM events";
$events_result = mysqli_query($conn, $events_query);

// Get mentors for filter
$mentors_query = "SELECT m.*, u.name FROM mentor_details m JOIN users u ON m.user_id = u.id";
$mentors_result = mysqli_query($conn, $mentors_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Participants - Manthan 2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-navbar {
            background: linear-gradient(45deg, #2d3436, #1a1a2e);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .main-card {
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: none;
            margin-top: 20px;
            overflow: hidden;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 5px solid #0d6efd;
        }
        .stats-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .stats-card i {
            font-size: 2.8rem;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #0d6efd, #0b5ed7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stats-card h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
            color: #2d3436;
        }
        .filter-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border-left: 5px solid #0d6efd;
        }
        .form-select, .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-select:focus, .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-primary {
            background: linear-gradient(45deg, #0d6efd, #0b5ed7);
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.3);
        }
        .btn-success {
            background: linear-gradient(45deg, #198754, #157347);
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(25, 135, 84, 0.3);
        }
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        .dataTables_wrapper {
            padding: 20px;
        }
        #participantsTable {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        #participantsTable thead {
            background: linear-gradient(45deg, #0d6efd, #0b5ed7);
            color: white;
        }
        #participantsTable thead th {
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        #participantsTable tbody tr {
            transition: all 0.3s ease;
        }
        #participantsTable tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
            transform: translateX(5px);
        }
        #participantsTable tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        .team-badge {
            background: linear-gradient(45deg, #6c757d, #5a6268);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .mentor-badge {
            background: linear-gradient(45deg, #198754, #157347);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .no-mentor-badge {
            background: linear-gradient(45deg, #ffc107, #e0a800);
            color: #000;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .action-btn {
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 0.85rem;
            margin: 2px;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .modal-header {
            background: linear-gradient(45deg, #0d6efd, #0b5ed7);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
        .members-list {
            list-style: none;
            padding: 0;
        }
        .members-list li {
            background: #f8f9fa;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
        }
        .page-title {
            color: #2d3436;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .page-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-user-shield"></i> Admin Portal
            </a>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-users"></i> Manage Participants
                        </h1>
                        <p class="page-subtitle">
                            View and manage all registered teams and their details
                        </p>
                    </div>
                    <a href="export_participants.php" class="btn btn-success">
                        <i class="fas fa-file-export"></i> Export to Excel
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card animate__animated animate__fadeInLeft">
                    <i class="fas fa-users"></i>
                    <h5>Total Teams</h5>
                    <h2><?php echo $total_teams; ?></h2>
                    <p class="text-muted mb-0">Registered across all events</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card animate__animated animate__fadeInUp">
                    <i class="fas fa-user-check"></i>
                    <h5>With Mentors</h5>
                    <h2><?php echo $with_mentor; ?></h2>
                    <p class="text-muted mb-0">Teams assigned to mentors</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card animate__animated animate__fadeInRight">
                    <i class="fas fa-user-clock"></i>
                    <h5>Need Mentors</h5>
                    <h2><?php echo $without_mentor; ?></h2>
                    <p class="text-muted mb-0">Teams waiting for mentors</p>
                </div>
            </div>
        </div>
        
        <!-- Filters Card -->
        <div class="filter-card animate__animated animate__fadeInUp">
            <h5 class="mb-4"><i class="fas fa-filter"></i> Filter Registrations</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt"></i> Filter by Event
                    </label>
                    <select name="event_id" class="form-select">
                        <option value="0">All Events</option>
                        <?php while($event = mysqli_fetch_assoc($events_result)): ?>
                            <option value="<?php echo $event['id']; ?>"
                                <?php echo ($event_filter == $event['id']) ? 'selected' : ''; ?>>
                                <?php echo $event['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-chalkboard-teacher"></i> Filter by Mentor
                    </label>
                    <select name="mentor_id" class="form-select">
                        <option value="0">All Mentors</option>
                        <?php 
                        mysqli_data_seek($mentors_result, 0);
                        while($mentor = mysqli_fetch_assoc($mentors_result)): ?>
                            <option value="<?php echo $mentor['id']; ?>"
                                <?php echo ($mentor_filter == $mentor['id']) ? 'selected' : ''; ?>>
                                <?php echo $mentor['name']; ?> (<?php echo $mentor['semester']; ?>th Sem)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2 w-50">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="view_participants.php" class="btn btn-outline-secondary w-50">
                        <i class="fas fa-undo"></i> Clear
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Participants Table -->
        <div class="table-container animate__animated animate__fadeIn">
            <div class="card-header" style="background: linear-gradient(45deg, #0d6efd, #0b5ed7); color: white;">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Registered Teams
                    <span class="badge bg-light text-dark ms-2"><?php echo $total_teams; ?> teams</span>
                </h5>
            </div>
            <div class="dataTables_wrapper">
                <table id="participantsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> Team #</th>
                            <th><i class="fas fa-users"></i> Team Name</th>
                            <th><i class="fas fa-calendar"></i> Event</th>
                            <th><i class="fas fa-user-tie"></i> Team Lead</th>
                            <th><i class="fas fa-user-friends"></i> Members</th>
                            <th><i class="fas fa-graduation-cap"></i> Standard</th>
                            <th><i class="fas fa-chalkboard-teacher"></i> Mentor</th>
                            <th><i class="fas fa-calendar-plus"></i> Registered On</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_teams > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <?php if ($row['team_number']): ?>
                                        <span class="team-badge">
                                            <i class="fas fa-hashtag"></i> <?php echo $row['team_number']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="no-mentor-badge">
                                            <i class="fas fa-exclamation-circle"></i> Not assigned
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['team_name']); ?></strong>
                                    <?php if ($row['title']): ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-project-diagram"></i> 
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?php echo $row['event_name'] == 'Ideathon' ? 'bg-info' : 'bg-warning'; ?>">
                                        <?php echo $row['event_name']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <i class="fas fa-user-circle text-primary"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo $row['lead_name']; ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope"></i> <?php echo $row['contact_email']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $members = array_filter([
                                        $row['team_member1'],
                                        $row['team_member2'],
                                        $row['team_member3']
                                    ]);
                                    echo '<div class="d-flex flex-wrap gap-1">';
                                    foreach($members as $member) {
                                        echo '<span class="badge bg-light text-dark">' . htmlspecialchars($member) . '</span>';
                                    }
                                    echo '</div>';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-graduation-cap"></i> <?php echo $row['standard']; ?>th
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['mentor_name']): ?>
                                        <span class="mentor-badge">
                                            <i class="fas fa-user-check"></i> <?php echo $row['mentor_name']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="no-mentor-badge">
                                            <i class="fas fa-user-clock"></i> Not assigned
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-calendar-day text-primary"></i>
                                        <?php echo date('d M Y', strtotime($row['registered_at'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('h:i A', strtotime($row['registered_at'])); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="assign_mentor.php?team_id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-primary action-btn" 
                                           data-bs-toggle="tooltip" title="Assign Mentor">
                                            <i class="fas fa-user-plus"></i>
                                        </a>
                                        <button class="btn btn-sm btn-info action-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#detailsModal<?php echo $row['id']; ?>"
                                                data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($row['edit_count'] > 0): ?>
                                            <span class="badge bg-warning text-dark" 
                                                  data-bs-toggle="tooltip" 
                                                  title="Edited <?php echo $row['edit_count']; ?> time(s)">
                                                <i class="fas fa-edit"></i> <?php echo $row['edit_count']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Details Modal -->
                            <div class="modal fade" id="detailsModal<?php echo $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-users"></i> 
                                                Team: <?php echo htmlspecialchars($row['team_name']); ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-info-circle"></i> Team Information</h6>
                                                    <p><strong>Project Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
                                                    <p><strong>Contact Email:</strong> <?php echo htmlspecialchars($row['contact_email']); ?></p>
                                                    <p><strong>Event:</strong> 
                                                        <span class="badge 
                                                            <?php echo $row['event_name'] == 'Ideathon' ? 'bg-info' : 'bg-warning'; ?>">
                                                            <?php echo $row['event_name']; ?>
                                                        </span>
                                                    </p>
                                                    <p><strong>Team Number:</strong> 
                                                        <?php echo $row['team_number'] ?: '<span class="text-warning">Not assigned</span>'; ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-chart-bar"></i> Registration Details</h6>
                                                    <p><strong>Registration ID:</strong> MANTHAN-<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></p>
                                                    <p><strong>Edit Count:</strong> 
                                                        <span class="badge 
                                                            <?php echo $row['edit_count'] >= 2 ? 'bg-danger' : 'bg-warning'; ?>">
                                                            <?php echo $row['edit_count']; ?> of 2 edits used
                                                        </span>
                                                    </p>
                                                    <p><strong>Mentor:</strong> 
                                                        <?php echo $row['mentor_name'] ?: '<span class="text-warning">Not assigned</span>'; ?>
                                                    </p>
                                                    <p><strong>Registered On:</strong> 
                                                        <?php echo date('d M Y H:i:s', strtotime($row['registered_at'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <hr>
                                            
                                            <h6><i class="fas fa-user-friends"></i> Team Members</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="members-list">
                                                        <li>
                                                            <strong><?php echo $row['lead_name']; ?></strong><br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-crown text-warning"></i> Team Lead | 
                                                                <i class="fas fa-envelope"></i> <?php echo $row['contact_email']; ?>
                                                            </small>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo $row['team_member1']; ?></strong><br>
                                                            <small class="text-muted">Member 2</small>
                                                        </li>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="members-list">
                                                        <li>
                                                            <strong><?php echo $row['team_member2']; ?></strong><br>
                                                            <small class="text-muted">Member 3</small>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo $row['team_member3']; ?></strong><br>
                                                            <small class="text-muted">Member 4</small>
                                                        </li>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <a href="assign_mentor.php?team_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-primary">
                                                <i class="fas fa-user-plus"></i> Assign/Change Mentor
                                            </a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="animate__animated animate__fadeIn">
                                        <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                                        <h4>No Teams Found</h4>
                                        <p class="text-muted">No teams have registered yet or match your filter criteria.</p>
                                        <a href="view_participants.php" class="btn btn-primary mt-2">
                                            <i class="fas fa-undo"></i> Clear Filters
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Footer Note -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-light border text-center">
                    <i class="fas fa-info-circle text-primary"></i>
                    <strong>Note:</strong> You can assign mentors to teams by clicking the 
                    <i class="fas fa-user-plus text-primary"></i> button. Teams without mentors are marked in yellow.
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#participantsTable').DataTable({
                "pageLength": 25,
                "order": [[7, 'desc']],
                "language": {
                    "search": "<i class='fas fa-search'></i> Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ teams",
                    "paginate": {
                        "previous": "<i class='fas fa-chevron-left'></i>",
                        "next": "<i class='fas fa-chevron-right'></i>"
                    }
                },
                "dom": '<"row"<"col-md-6"l><"col-md-6"f>>tip',
                "drawCallback": function() {
                    // Initialize tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
            
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Add animation to table rows on hover
            $('#participantsTable tbody tr').hover(
                function() {
                    $(this).addClass('animate__animated animate__pulse');
                },
                function() {
                    $(this).removeClass('animate__animated animate__pulse');
                }
            );
            
            // Auto-refresh table every 30 seconds (optional)
            // setInterval(function() {
            //     $('#participantsTable').DataTable().ajax.reload();
            // }, 30000);
        });
    </script>
</body>
</html>