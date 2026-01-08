<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}
checkRole('student');

$certificate_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Verify certificate belongs to user
$query = "SELECT c.*, u.name as participant_name, e.name as event_name, 
                 er.team_name, er.title as project_title
          FROM certificates c
          JOIN users u ON c.participant_id = u.id
          JOIN event_registrations er ON c.registration_id = er.id
          JOIN events e ON er.event_id = e.id
          WHERE c.id = $certificate_id AND c.participant_id = $user_id";
$result = mysqli_query($conn, $query);
$certificate = mysqli_fetch_assoc($result);

if (!$certificate) {
    die("Certificate not found or access denied!");
}

// Generate PDF using FPDF (you need to install FPDF)
require_once('../../fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, 'CERTIFICATE OF PARTICIPATION', 0, 1, 'C');
        $this->Ln(10);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Certificate Code: ' . $this->cert_code, 0, 0, 'C');
    }
}

// Create PDF
$pdf = new PDF();
$pdf->cert_code = $certificate['certificate_code'];
$pdf->AddPage();

// Add border
$pdf->SetLineWidth(1.5);
$pdf->Rect(10, 10, 190, 277);

// Logo (if available)
$pdf->Image('../../assets/images/logo.png', 85, 20, 40);

// Title
$pdf->SetFont('Arial', 'B', 24);
$pdf->Cell(0, 40, '', 0, 1);
$pdf->Cell(0, 10, 'CERTIFICATE OF PARTICIPATION', 0, 1, 'C');

// Subtitle
$pdf->SetFont('Arial', 'I', 16);
$pdf->Cell(0, 10, 'This is to certify that', 0, 1, 'C');
$pdf->Ln(10);

// Participant Name
$pdf->SetFont('Arial', 'B', 28);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 15, strtoupper($certificate['participant_name']), 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);

// Details
$pdf->SetFont('Arial', '', 16);
$pdf->Cell(0, 10, 'has successfully participated in', 0, 1, 'C');

// Event Name
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(220, 0, 0);
$pdf->Cell(0, 15, $certificate['event_name'], 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);

// Team and Project
$pdf->SetFont('Arial', '', 16);
$pdf->Cell(0, 10, 'as part of Team: ' . $certificate['team_name'], 0, 1, 'C');
$pdf->Cell(0, 10, 'Project: ' . $certificate['project_title'], 0, 1, 'C');
$pdf->Ln(10);

// Venue and Date
$pdf->SetFont('Arial', 'I', 14);
$pdf->Cell(0, 10, 'Organized by KLE Technological University, Belagavi', 0, 1, 'C');
$pdf->Cell(0, 10, 'On ' . date('F d, Y'), 0, 1, 'C');
$pdf->Ln(20);

// Signatures
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(95, 10, '________________________', 0, 0, 'C');
$pdf->Cell(95, 10, '________________________', 0, 1, 'C');
$pdf->Cell(95, 10, 'Event Coordinator', 0, 0, 'C');
$pdf->Cell(95, 10, 'Head of Department', 0, 1, 'C');

// Update download status
$update_query = "UPDATE certificates SET downloaded = 1 WHERE id = $certificate_id";
mysqli_query($conn, $update_query);

// Output PDF
$pdf->Output('D', 'Certificate_' . $certificate['certificate_code'] . '.pdf');
?>