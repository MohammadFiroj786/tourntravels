<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

// ✅ FIRST: FETCH DATA
$stmt = $conn->prepare("SELECT * FROM custom_package_requests WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $request_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();
$stmt->close();

if (!$request) {
    header("Location: user-dashboard.php");
    exit();
}

// ===== FINAL PREMIUM INVOICE PDF =====
if(isset($_GET['download']) && $_GET['download'] == 1){

    if(ob_get_length()) ob_clean();

    require('../includes/fpdf186/fpdf.php');

    class PDF extends FPDF {

        function Header(){
            // Top Header Background
            $this->SetFillColor(15,23,42);
            $this->Rect(0,0,210,35,'F');

            // Company Name
            $this->SetTextColor(255,255,255);
            $this->SetFont('Arial','B',22);
            $this->SetY(10);
            $this->Cell(0,10,'Hidden Hills Collective',0,1,'C');

            // Tagline
            $this->SetFont('Arial','',11);
            $this->Cell(0,5,'Premium Travel & Tour Services',0,1,'C');

            $this->Ln(10);

            // Reset text color
            $this->SetTextColor(0,0,0);
        }

        function Footer(){
            $this->SetY(-15);
            $this->SetFont('Arial','I',9);
            $this->SetTextColor(120,120,120);
            $this->Cell(0,10,'Generated on '.date('d M Y').' | Hidden Hills Collective',0,0,'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();

    // ===== TOP SECTION =====
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(100,10,'Booking Invoice',0,0,'L');

    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,10,'Date: '.date('d M Y'),0,1,'R');

    $pdf->Ln(3);

    // Request ID Box
    $pdf->SetFillColor(241,245,249);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,10,'Request ID: #'.$request['id'],0,1,'C',true);

    $pdf->Ln(5);

    // ===== CARD BACKGROUND =====
    $startY = $pdf->GetY();
    $pdf->SetFillColor(248,250,252);
    $pdf->Rect(10,$startY,190,95,'F');

    $pdf->Ln(6);

    // Section Title
    $pdf->SetFont('Arial','B',13);
    $pdf->Cell(0,10,'Travel Details',0,1);

    // Divider
    $pdf->SetDrawColor(220,220,220);
    $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

    $pdf->Ln(4);

    // ===== ROW FUNCTION =====
    function row($pdf,$label,$value){
        $pdf->SetFont('Arial','B',11);
        $pdf->SetTextColor(80,80,80);
        $pdf->Cell(65,10,$label,0,0);

        $pdf->SetFont('Arial','',11);
        $pdf->SetTextColor(20,20,20);
        $pdf->Cell(0,10,$value,0,1);

        // Soft line
        $pdf->SetDrawColor(230,230,230);
        $pdf->Line(15,$pdf->GetY(),195,$pdf->GetY());
    }

    // ===== DATA =====
    $sightseeing_places = json_decode($request['sightseeing_places'], true) ?? [];

    row($pdf,'Service Type',ucfirst($request['service_type']));
    row($pdf,'Destination',$request['destination']);
    row($pdf,'Sightseeing',implode(', ',$sightseeing_places));
    row($pdf,'Travel Date',date('d M Y', strtotime($request['travel_date'])));

    if(!empty($request['days'])){
        row($pdf,'Duration',$request['days'].' Days');
    }

    row($pdf,'Travelers',$request['travelers']);
    row($pdf,'Vehicle',$request['car_type']);

    if(!empty($request['hotel_type'])){
        row($pdf,'Hotel',$request['hotel_type']);
    }

    // ===== STATUS =====
    $pdf->Ln(6);

    $status = strtoupper($request['status']);
    if($status == 'PENDING'){
        $pdf->SetFillColor(254,243,199); // yellow
    } else {
        $pdf->SetFillColor(220,252,231); // green
    }

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,10,'Status: '.$status,0,1,'C',true);

    // ===== NOTE BOX =====
    $pdf->Ln(6);
    $pdf->SetFillColor(239,246,255);
    $pdf->SetFont('Arial','I',10);
    $pdf->MultiCell(0,8,
        "Thank you for booking with Hidden Hills Collective.\nOur travel expert will contact you within 24 hours.",
        0,'C',true
    );

    // ===== FOOTER TEXT =====
    $pdf->Ln(8);
    $pdf->SetDrawColor(200,200,200);
    $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

    $pdf->Ln(4);
    $pdf->SetFont('Arial','',9);
    $pdf->SetTextColor(120,120,120);
    $pdf->Cell(0,10,'This is a system generated invoice. No signature required.',0,1,'C');

    // ===== OUTPUT =====
    $pdf->Output('D','HiddenHills_Invoice_'.$request['id'].'.pdf');
    exit();
}

$sightseeing_places = json_decode($request['sightseeing_places'], true) ?? [];
$destinations = $request['destination'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Success</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
body{
    background:#f4f7fb;
    font-family:'Segoe UI',sans-serif;
}

.wrapper{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

.card-box{
    background:#fff;
    border-radius:20px;
    padding:35px;
    max-width:750px;
    width:100%;
    box-shadow:0 20px 50px rgba(0,0,0,.08);
    animation:fade .4s ease;
}

@keyframes fade{
    from{opacity:0;transform:translateY(20px)}
    to{opacity:1;transform:translateY(0)}
}

.success{
    text-align:center;
}

.success i{
    font-size:60px;
    color:#22c55e;
}

.success h2{
    margin-top:10px;
    font-weight:600;
}

.success p{
    color:#6b7280;
    font-size:14px;
}

.progress-bar-custom{
    display:flex;
    justify-content:space-between;
    margin:25px 0;
}

.step{
    text-align:center;
    font-size:12px;
    flex:1;
    color:#aaa;
}

.step.active{
    color:#22c55e;
    font-weight:600;
}

.details{
    background:#f9fafb;
    border-radius:15px;
    padding:20px;
}

.row-item{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid #eee;
}

.row-item:last-child{border:none}

.label{font-weight:600}
.value{color:#555}

.badge-type{
    padding:4px 10px;
    border-radius:10px;
    font-size:12px;
}

.full{background:#e0f2fe;color:#0284c7}
.stay{background:#f3e8ff;color:#7c3aed}
.sightseeing{background:#dcfce7;color:#16a34a}

.actions{
    margin-top:25px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    justify-content:center;
}

.btn-custom{
    border-radius:25px;
    padding:10px 18px;
}

</style>
</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="wrapper">

<div class="card-box">

<div class="success">
<i class="fas fa-check-circle"></i>
<h2>Thank You for Your Request!</h2>
<p>Your custom tour package request has been submitted successfully. Our travel expert will contact you within 24 hours.</p>
</div>

<div class="progress-bar-custom">
<div class="step active">Submitted</div>
<div class="step">Review</div>
<div class="step">Confirmed</div>
</div>

<div class="details">

<div class="row-item"><span class="label">Request ID</span><span class="value">#<?php echo $request['id']; ?></span></div>

<div class="row-item"><span class="label">Service Type</span>
<span class="value badge-type <?php echo $request['service_type']; ?>">
<?php echo ucfirst($request['service_type']); ?>
</span></div>

<div class="row-item"><span class="label">Destination</span><span class="value"><?php echo $destinations; ?></span></div>

<div class="row-item"><span class="label">Sightseeing</span><span class="value"><?php echo implode(', ', $sightseeing_places); ?></span></div>

<div class="row-item"><span class="label">Travel Date</span><span class="value"><?php echo date('d M Y', strtotime($request['travel_date'])); ?></span></div>

<?php if($request['days']): ?>
<div class="row-item"><span class="label">Days</span><span class="value"><?php echo $request['days']; ?></span></div>
<?php endif; ?>

<div class="row-item"><span class="label">Travelers</span><span class="value"><?php echo $request['travelers']; ?></span></div>

<div class="row-item"><span class="label">Vehicle</span><span class="value"><?php echo $request['car_type']; ?></span></div>

<?php if($request['hotel_type']): ?>
<div class="row-item"><span class="label">Hotel</span><span class="value"><?php echo $request['hotel_type']; ?></span></div>
<?php endif; ?>

<div class="row-item"><span class="label">Status</span>
<span class="badge bg-warning text-dark"><?php echo $request['status']; ?></span>
</div>

</div>

<div class="actions">
<a href="my-bookings.php" class="btn btn-primary btn-custom">📋 My Bookings</a>
<a href="user-dashboard.php" class="btn btn-outline-secondary btn-custom">🏠 Dashboard</a>
<a href="?request_id=<?php echo $request['id']; ?>&download=1" 
   class="btn btn-success btn-custom">
   📄 Download PDF
</a>
</div>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>