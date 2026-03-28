<?php
// ✅ INCLUDE SESSION SECURITY CHECK (replaces manual session checks)
include("../includes/session_check.php");

// ✅ Verify user has admin role
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");

/* ===============================
   SAFE QUERY FUNCTION
================================ */
function getValue($conn, $query){
    $result = $conn->query($query);
    if($result && $row = $result->fetch_assoc()){
        return $row['total'] ?? 0;
    }
    return 0;
}

/* ===============================
   STATS (UPDATED LOGIC FRIENDLY)
================================ */
$total_users = getValue($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'");
$total_packages = getValue($conn, "SELECT COUNT(*) as total FROM packages");
$total_bookings = getValue($conn, "SELECT COUNT(*) as total FROM bookings");

/* Flexible revenue logic */
$total_revenue = getValue($conn, "
    SELECT SUM(
        CASE 
            WHEN total_price IS NOT NULL THEN total_price 
            ELSE 0 
        END
    ) as total 
    FROM bookings 
    WHERE booking_status='confirmed'
");

/* ===============================
   RECENT BOOKINGS (SAFE JOIN)
================================ */
$recent_bookings = $conn->query("
SELECT 
    b.*, 
    u.name, 
    p.title,

    /* PAYMENT DATA */
    pay.payment_status,
    pay.amount as paid_amount,
    pay.payment_method,
    pay.transaction_id

FROM bookings b

LEFT JOIN users u ON b.user_id = u.id
LEFT JOIN packages p ON b.package_id = p.id

/* ✅ GET ONLY LATEST PAYMENT */
LEFT JOIN payments pay ON pay.id = (
    SELECT id FROM payments 
    WHERE booking_id = b.id 
    ORDER BY created_at DESC 
    LIMIT 1
)

ORDER BY b.created_at DESC
LIMIT 5
");

if(!$recent_bookings){
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Dashboard</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>

/* ===== GLOBAL ===== */
body{
    font-family:'Poppins',sans-serif;
    background:#eef2f7;
}

/* ===== SIDEBAR FIX ===== */
.adminLayoutContent{
    margin-left:260px;
    padding:30px;
}

/* ===== TITLE ===== */
.dashboard-title{
    font-weight:700;
    color:#2c3e50;
}

/* ===== CARDS ===== */
.dashboard-card{
    border-radius:20px;
    padding:25px;
    color:white;
    transition:0.3s;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

.dashboard-card:hover{
    transform:translateY(-6px);
}

.card-value{
    font-size:28px;
    font-weight:700;
}

.card-label{
    opacity:0.9;
}

/* Gradients */
.bg1{ background:linear-gradient(135deg,#667eea,#764ba2); }
.bg2{ background:linear-gradient(135deg,#43cea2,#185a9d); }
.bg3{ background:linear-gradient(135deg,#f7971e,#ffd200); color:#000;}
.bg4{ background:linear-gradient(135deg,#ff416c,#ff4b2b); }

/* ===== TABLE ===== */
.card{
    border-radius:20px;
}

.table thead{
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:white;
}

.table td{
    vertical-align:middle;
}

/* ===== BADGES ===== */
.badge{
    padding:6px 12px;
    border-radius:20px;
}

/* ===== ANIMATION ===== */
.fade-in{
    animation:fadeIn 0.6s ease-in-out;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px);}
    to{opacity:1; transform:translateY(0);}
}

</style>

</head>

<body>

<?php include("navbar_admin.php"); ?>

<div class="adminLayoutContent fade-in">

<h2 class="dashboard-title mb-4">📊 Dashboard Overview</h2>

<!-- STATS -->
<div class="row g-4">

<div class="col-lg-3 col-md-6">
<div class="dashboard-card bg1">
<div class="card-value"><?= $total_users ?></div>
<div class="card-label">Total Users</div>
</div>
</div>

<div class="col-lg-3 col-md-6">
<div class="dashboard-card bg2">
<div class="card-value"><?= $total_packages ?></div>
<div class="card-label">Total Packages</div>
</div>
</div>

<div class="col-lg-3 col-md-6">
<div class="dashboard-card bg3">
<div class="card-value"><?= $total_bookings ?></div>
<div class="card-label">Total Bookings</div>
</div>
</div>

<div class="col-lg-3 col-md-6">
<div class="dashboard-card bg4">
<div class="card-value">₹<?= $total_revenue ?? 0 ?></div>
<div class="card-label">Total Revenue</div>
</div>
</div>

</div>

<hr class="my-5">

<!-- RECENT BOOKINGS -->
<h4 class="mb-3 fw-semibold">🕒 Recent Bookings</h4>

<div class="card shadow border-0">

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover">

<thead>
<tr>
<th>User</th>
<th>Package</th>
<th>Persons</th>
<th>Total</th>
<th>Status</th>
<th>Date</th>
<th>Payment</th>
<th>Method</th>
<th>Txn ID</th>
</tr>
</thead>

<tbody>

<?php if($recent_bookings->num_rows > 0){ ?>
<?php while($row = $recent_bookings->fetch_assoc()){ 

$status = strtolower(trim($row['booking_status']));
?>

<tr>

<td><?= htmlspecialchars($row['name'] ?? 'N/A') ?></td>

<td><?= htmlspecialchars($row['title'] ?? 'Custom') ?></td>

<td><?= $row['persons'] ?? '-' ?></td>

<td>₹<?= $row['total_price'] ?? 0 ?></td>

<td>
<?php
if($status == 'confirmed'){
echo "<span class='badge bg-success'>Confirmed</span>";
}
elseif($status == 'pending'){
echo "<span class='badge bg-warning text-dark'>Pending</span>";
}
elseif($status == 'cancelled'){
echo "<span class='badge bg-danger'>Cancelled</span>";
}
else{
echo "<span class='badge bg-secondary'>Unknown</span>";
}
?>
</td>

<td><?= date("d M Y", strtotime($row['created_at'])) ?></td>

<!-- PAYMENT STATUS -->
<td>
<?php
$pay = strtolower($row['payment_status'] ?? 'no payment');

if($pay == 'paid'){
    echo "<span class='badge bg-success'>Paid</span>";
}
elseif($pay == 'pending'){
    echo "<span class='badge bg-warning text-dark'>Pending</span>";
}
elseif($pay == 'failed'){
    echo "<span class='badge bg-danger'>Failed</span>";
}
else{
    echo "<span class='badge bg-secondary'>No Payment</span>";
}
?>
</td>

<!-- PAYMENT METHOD -->
<td>
<?= htmlspecialchars($row['payment_method'] ?? '-') ?>
</td>

<!-- TRANSACTION ID -->
<td>
<?= htmlspecialchars($row['transaction_id'] ?? '-') ?>
</td>
</tr>

<?php } ?>
<?php } else { ?>

<tr>
<td colspan="6" class="text-center">No bookings found</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>