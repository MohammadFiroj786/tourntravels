<?php
include("../includes/session_check.php");
include("../includes/db.php");

$user_id = intval($_SESSION['user_id']);
$user_name = $_SESSION['name'];

/* ================= STATISTICS ================= */

$totalBookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id=$user_id")->fetch_assoc()['total'];

$pendingBookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id=$user_id AND booking_status='pending'")->fetch_assoc()['total'];

$confirmedBookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id=$user_id AND booking_status='confirmed'")->fetch_assoc()['total'];

$totalSpent = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE user_id=$user_id AND booking_status='confirmed'")->fetch_assoc()['total'];
$totalSpent = $totalSpent ? $totalSpent : 0;

/* ================= CUSTOM PACKAGE REQUESTS ================= */
$customPackageRequests = $conn->query("SELECT * FROM custom_package_requests WHERE user_id=$user_id ORDER BY created_at DESC");

/* ================= RECENT BOOKINGS ================= */

$recentBookings = $conn->query("
SELECT 
    bookings.*,
    packages.title,
    c.destination,
    c.status AS custom_status,
    COALESCE(p.payment_status, 'pending') AS payment_status,
    f.id AS feedback_id

FROM bookings

LEFT JOIN packages 
    ON bookings.package_id = packages.id

LEFT JOIN custom_package_requests c
    ON bookings.user_id = c.user_id 
    AND bookings.travel_date = c.travel_date

LEFT JOIN (
    SELECT booking_id, payment_status
    FROM payments
    GROUP BY booking_id
) p ON bookings.id = p.booking_id

LEFT JOIN feedback f 
    ON bookings.id = f.booking_id

WHERE bookings.user_id=$user_id

ORDER BY bookings.created_at DESC
LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>

<title>User Dashboard</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>

body{
    font-family:'Poppins',sans-serif;
    background: linear-gradient(120deg,#eef2f7,#e3ebf6);
}

/* HEADER */
.main-content h2{
    font-weight:700;
}

/* CARD */
.card{
    border:none;
    border-radius:20px;
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(10px);
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

/* STAT CARDS */
.stat-card{
    padding:25px;
    transition:0.3s;
    text-align:center;
}

.stat-card:hover{
    transform:translateY(-8px) scale(1.02);
}

.stat-icon{
    font-size:30px;
    margin-bottom:10px;
}

/* COLORS */
.bg1{ background:linear-gradient(135deg,#667eea,#764ba2); color:white; }
.bg2{ background:linear-gradient(135deg,#f7971e,#ffd200); color:black; }
.bg3{ background:linear-gradient(135deg,#43cea2,#185a9d); color:white; }
.bg4{ background:linear-gradient(135deg,#ff416c,#ff4b2b); color:white; }

/* TABLE */
.table{
    border-radius:15px;
    overflow:hidden;
}

.table thead{
    background:#1e1e2f;
    color:white;
}

/* BADGES */
.badge{
    padding:7px 14px;
    border-radius:20px;
    font-size:13px;
}

/* SECTION */
.section-title{
    font-weight:600;
    margin-bottom:15px;
}

</style>

</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="main-content p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
<h2>Welcome, <?php echo $user_name; ?> 👋</h2>
<span class="badge bg-success p-2">User Panel</span>
</div>

<!-- STAT CARDS -->
<div class="row g-4 mb-4">

<div class="col-md-3">
<div class="card stat-card bg1">
<i class="fas fa-suitcase-rolling stat-icon"></i>
<h5>Total Bookings</h5>
<h2><?php echo $totalBookings; ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card bg2">
<i class="fas fa-hourglass-half stat-icon"></i>
<h5>Pending</h5>
<h2><?php echo $pendingBookings; ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card bg3">
<i class="fas fa-check-circle stat-icon"></i>
<h5>Confirmed</h5>
<h2><?php echo $confirmedBookings; ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card bg4">
<i class="fas fa-wallet stat-icon"></i>
<h5>Total Spent</h5>
<h2>₹<?php echo $totalSpent; ?></h2>
</div>
</div>

</div>

<!-- RECENT BOOKINGS -->
<div class="card shadow p-4 mb-4">

<h4 class="section-title">📅 Recent Bookings</h4>

<div class="table-responsive">
<table class="table table-hover">

<thead>
<tr>
<th>Package</th>
<th>Travel Date</th>
<th>Persons</th>
<th>Total</th>
<th>Status</th>
<th>Payment</th>
<th>Feedback</th>
</tr>
</thead>

<tbody>

<?php if($recentBookings->num_rows > 0){ ?>
<?php while($row = $recentBookings->fetch_assoc()){ 

$status = $row['booking_status'];

if($row['package_id'] == NULL){
$status = $row['custom_status'];
}

$status = strtolower(trim($status));
?>

<tr>

<td>
<?php
if(!empty($row['title'])){
echo $row['title'];
}else{
echo "<span class='badge bg-info text-dark'>Custom - ".$row['destination']."</span>";
}
?>
</td>

<td><?php echo $row['travel_date']; ?></td>
<td><?php echo $row['persons']; ?></td>
<td>₹<?php echo $row['total_price']; ?></td>

<td>
<?php
if($status == 'pending'){
echo "<span class='badge bg-warning text-dark'>⏳ Pending</span>";
}
elseif($status == 'accepted'){
echo "<span class='badge bg-info'>🚀 Accepted</span>";
}
elseif($status == 'confirmed'){
echo "<span class='badge bg-success'>✅ Confirmed</span>";
}
elseif($status == 'cancelled'){
echo "<span class='badge bg-danger'>❌ Cancelled</span>";
}
else{
echo "<span class='badge bg-secondary'>⚙ Processing</span>";
}
?>
</td>

<td>
<?php
$payment = strtolower(trim($row['payment_status']));
$bookingId = $row['id'];

// DEBUG (remove later)
// echo "ID:$bookingId Payment:$payment<br>";

if($payment === 'paid'){
    echo "<span class='badge bg-success'>✅ Paid</span>";
}
elseif($status === 'confirmed'){
    echo "<a href='payment.php?booking_id=$bookingId' class='btn btn-sm btn-primary'>💳 Pay Now</a>";
}
elseif($status === 'pending'){
    echo "<span class='badge bg-secondary'>Wait for Approval</span>";
}
else{
    echo "<span class='badge bg-light text-dark'>N/A</span>";
}
?>
</td>
<td>
<?php
$today = date('Y-m-d');
$feedbackGiven = !empty($row['feedback_id']);

if($status === 'confirmed' && $row['travel_date'] < $today){

    if(!$feedbackGiven){
        echo "<a href='feedback.php?booking_id=".$row['id']."' 
              class='btn btn-sm btn-outline-primary'>
              ⭐ Give Feedback
              </a>";
    } else {
        echo "<span class='badge bg-success'>✔ Submitted</span>";
    }

}else{
    echo "<span class='badge bg-secondary'>Not Available</span>";
}
?>
</td>
</tr>

<?php } ?>
<?php } else { ?>
<tr>
<td colspan="5" class="text-center">No bookings yet.</td>
</tr>

<?php } ?>

</tbody>

</table>
</div>
</div>

<!-- CUSTOM PACKAGE REQUESTS -->
<div class="card shadow p-4">

<h4 class="section-title">🧩 Custom Package Requests</h4>

<div class="table-responsive">

<table class="table table-hover">

<thead>
<tr>
<th>Date</th>
<th>Service</th>
<th>Destination</th>
<th>Days</th>
<th>Travellers</th>
<th>Price</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php if($customPackageRequests && $customPackageRequests->num_rows > 0){ ?>
<?php while($req = $customPackageRequests->fetch_assoc()){ 

$labels = ['full'=>'🚗 Full Trip','stay'=>'🏨 Stay','sightseeing'=>'🏔️ Tour'];
$status = strtolower(trim($req['status']));
?>

<tr>

<td><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>

<td><?php echo $labels[$req['service_type']] ?? $req['service_type']; ?></td>

<td><?php echo htmlspecialchars($req['destinations'] ?? $req['destination'] ?? 'N/A'); ?></td>

<td><?php echo $req['days']; ?></td>

<td><?php echo $req['travelers']; ?></td>

<td><?php echo $req['price'] ? '₹'.$req['price'] : 'N/A'; ?></td>

<td>
<?php
if($status == 'accepted'){
echo "<span class='badge bg-success'>Accepted</span>";
}
elseif($status == 'pending'){
echo "<span class='badge bg-warning text-dark'>Pending</span>";
}
else{
echo "<span class='badge bg-secondary'>".$req['status']."</span>";
}
?>
</td>

</tr>

<?php } ?>
<?php } else { ?>
<tr>
<td colspan="7" class="text-center">No custom packages yet.</td>
</tr>
<?php } ?>

</tbody>

</table>

</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>