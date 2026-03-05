<?php
session_start();
include("../includes/db.php");
require('../includes/fpdf186/fpdf.php');

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

/* ================= FILTER & SEARCH ================= */

$search = $_GET['search'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$where = " WHERE 1=1 ";

if($search != ''){
    $where .= " AND (users.name LIKE '%$search%' 
                OR packages.title LIKE '%$search%')";
}

if($from_date != '' && $to_date != ''){
    $where .= " AND bookings.travel_date 
                BETWEEN '$from_date' AND '$to_date'";
}

/* ================= EXPORT EXCEL ================= */

if(isset($_GET['export']) && $_GET['export']=="excel"){
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=bookings.xls");

    echo "ID\tUser\tPackage\tTravel Date\tPersons\tTotal\tStatus\tPayment\n";

    $exportQuery = "
    SELECT bookings.*, users.name AS user_name, packages.title AS package_title
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    JOIN packages ON bookings.package_id = packages.id
    $where";

    $result = $conn->query($exportQuery);

    while($row = $result->fetch_assoc()){
        echo $row['id']."\t".
             $row['user_name']."\t".
             $row['package_title']."\t".
             $row['travel_date']."\t".
             $row['persons']."\t".
             $row['total_price']."\t".
             $row['booking_status']."\t".
             $row['payment_status']."\n";
    }
    exit();
}

/* ================= EXPORT PDF ================= */

if(isset($_GET['export']) && $_GET['export']=="pdf"){

    $pdfQuery = "
    SELECT bookings.*, users.name AS user_name, packages.title AS package_title
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    JOIN packages ON bookings.package_id = packages.id
    $where";

    $result = $conn->query($pdfQuery);

    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(190,10,'Bookings Report',0,1,'C');

    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',10);

    $pdf->Cell(10,8,'ID',1);
    $pdf->Cell(30,8,'User',1);
    $pdf->Cell(35,8,'Package',1);
    $pdf->Cell(25,8,'Date',1);
    $pdf->Cell(15,8,'Person',1);
    $pdf->Cell(25,8,'Total',1);
    $pdf->Cell(25,8,'Status',1);
    $pdf->Cell(25,8,'Payment',1);

    $pdf->Ln();

    $pdf->SetFont('Arial','',9);

    while($row = $result->fetch_assoc()){

        $pdf->Cell(10,8,$row['id'],1);
        $pdf->Cell(30,8,$row['user_name'],1);
        $pdf->Cell(35,8,$row['package_title'],1);
        $pdf->Cell(25,8,$row['travel_date'],1);
        $pdf->Cell(15,8,$row['persons'],1);
        $pdf->Cell(25,8,'Rs '.$row['total_price'],1);
        $pdf->Cell(25,8,$row['booking_status'],1);
        $pdf->Cell(25,8,$row['payment_status'],1);

        $pdf->Ln();
    }

    $pdf->Output('D','bookings_report.pdf');
    exit();
}

/* ================= UPDATE BOOKING ================= */

if(isset($_POST['update_booking'])){
    $stmt = $conn->prepare("UPDATE bookings 
        SET travel_date=?, booking_status=?, payment_status=? 
        WHERE id=?");
    $stmt->bind_param("sssi",
        $_POST['travel_date'],
        $_POST['booking_status'],
        $_POST['payment_status'],
        $_POST['booking_id']);
    $stmt->execute();
}

/* ================= STATISTICS ================= */

$statsQuery = "
SELECT 
COUNT(*) as total_bookings,
SUM(CASE WHEN booking_status='Confirmed' THEN 1 ELSE 0 END) as confirmed,
SUM(total_price) as revenue
FROM bookings";

$stats = $conn->query($statsQuery)->fetch_assoc();

/* ================= FETCH BOOKINGS ================= */

$query = "
SELECT bookings.*, users.name AS user_name, packages.title AS package_title
FROM bookings
JOIN users ON bookings.user_id = users.id
JOIN packages ON bookings.package_id = packages.id
$where
ORDER BY bookings.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Bookings</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f8fafc;
    font-family:'Segoe UI', system-ui, sans-serif;
    color:#1f2937;
}

.container{
    max-width:1400px;
}

h2{
    font-weight:700;
    color:#111827;
}

/* CARDS */
.card{
    border-radius:12px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

/* STATS */
.card h5{
    font-size:14px;
    color:#6b7280;
}
.card h3{
    font-weight:700;
    color:#2563eb;
}

/* FORM */
.form-control,
.form-select{
    border-radius:8px;
    font-size:14px;
}

.form-control:focus,
.form-select:focus{
    box-shadow:none;
    border-color:#2563eb;
}

/* BUTTONS */
.btn{
    border-radius:8px;
    font-weight:600;
}

.btn-primary{
    background:#2563eb;
    border:none;
}

.btn-success{
    background:#16a34a;
    border:none;
}

.btn-danger{
    background:#dc2626;
    border:none;
}

/* TABLE */
.table{
    background:white;
    border-radius:10px;
    overflow:hidden;
}

.table thead{
    background:#f1f5f9;
}

.table th{
    font-size:14px;
    color:#374151;
}

.table-hover tbody tr:hover{
    background:#f9fafb;
}
</style>
</head>

<body>

<div class="container mt-4">

<h2 class="mb-4">📖 Manage Bookings</h2>

<!-- STATS -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Total Bookings</h5>
            <h3><?= $stats['total_bookings'] ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Confirmed</h5>
            <h3><?= $stats['confirmed'] ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Total Revenue</h5>
            <h3>₹<?= $stats['revenue'] ?? 0 ?></h3>
        </div>
    </div>
</div>

<!-- FILTER -->
<form method="GET" class="card p-3 mb-4">
<div class="row g-2">
    <div class="col-md-3">
        <input type="text" name="search" class="form-control"
        placeholder="Search user/package" value="<?= $search ?>">
    </div>
    <div class="col-md-3">
        <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
    </div>
    <div class="col-md-3">
        <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary w-100">Filter</button>
        <a class="btn btn-success" href="?export=excel&search=<?= $search ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>">Excel</a>
        <a class="btn btn-danger" href="?export=pdf&search=<?= $search ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>">PDF</a>
    </div>
</div>
</form>

<!-- TABLE -->
<div class="card p-3">
<table class="table table-bordered table-hover align-middle">
<thead>
<tr class="text-center">
<th>ID</th>
<th>User</th>
<th>Package</th>
<th>Date</th>
<th>Persons</th>
<th>Total</th>
<th>Status</th>
<th>Payment</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<form method="POST">
<td><?= $row['id'] ?></td>
<td><?= $row['user_name'] ?></td>
<td><?= $row['package_title'] ?></td>

<td>
<input type="date" name="travel_date"
class="form-control"
value="<?= $row['travel_date'] ?>">
</td>

<td><?= $row['persons'] ?></td>
<td>₹<?= $row['total_price'] ?></td>

<td>
<select name="booking_status" class="form-select">
<option <?= $row['booking_status']=="Pending"?"selected":"" ?>>Pending</option>
<option <?= $row['booking_status']=="Confirmed"?"selected":"" ?>>Confirmed</option>
<option <?= $row['booking_status']=="Cancelled"?"selected":"" ?>>Cancelled</option>
</select>
</td>

<td>
<select name="payment_status" class="form-select">
<option <?= $row['payment_status']=="Unpaid"?"selected":"" ?>>Unpaid</option>
<option <?= $row['payment_status']=="Paid"?"selected":"" ?>>Paid</option>
<option <?= $row['payment_status']=="Refunded"?"selected":"" ?>>Refunded</option>
</select>
</td>

<td>
<input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
<button name="update_booking" class="btn btn-primary btn-sm">Update</button>
</td>
</form>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

</div>
</body>
</html>