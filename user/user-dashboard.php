<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$user_name = $_SESSION['name'];

/* ================= STATISTICS ================= */

$totalBookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id=$user_id")->fetch_assoc()['total'];

$pendingBookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id=$user_id AND booking_status='pending'")->fetch_assoc()['total'];

$confirmedBookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id=$user_id AND booking_status='confirmed'")->fetch_assoc()['total'];

$totalSpent = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE user_id=$user_id AND booking_status='confirmed'")->fetch_assoc()['total'];
$totalSpent = $totalSpent ? $totalSpent : 0;

$recentBookings = $conn->query("
SELECT bookings.*, packages.title 
FROM bookings 
JOIN packages ON bookings.package_id = packages.id 
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

<style>

body{
background:#f4f6f9;
}

.card{
border:none;
border-radius:15px;
}

.stat-card{
transition:0.3s;
}

.stat-card:hover{
transform:translateY(-5px);
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

<!-- Stat Cards -->

<div class="row g-4 mb-4">

<div class="col-md-3">

<div class="card shadow stat-card p-3 text-center">

<h5>Total Bookings</h5>

<h2 class="text-primary"><?php echo $totalBookings; ?></h2>

</div>

</div>

<div class="col-md-3">

<div class="card shadow stat-card p-3 text-center">

<h5>Pending</h5>

<h2 class="text-warning"><?php echo $pendingBookings; ?></h2>

</div>

</div>

<div class="col-md-3">

<div class="card shadow stat-card p-3 text-center">

<h5>Confirmed</h5>

<h2 class="text-success"><?php echo $confirmedBookings; ?></h2>

</div>

</div>

<div class="col-md-3">

<div class="card shadow stat-card p-3 text-center">

<h5>Total Spent</h5>

<h2 class="text-danger">₹<?php echo $totalSpent; ?></h2>

</div>

</div>

</div>

<!-- Recent Bookings -->

<div class="card shadow p-4">

<h4 class="mb-3">Recent Bookings</h4>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>Package</th>
<th>Travel Date</th>
<th>Persons</th>
<th>Total Price</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php if($recentBookings->num_rows > 0){ ?>

<?php while($row = $recentBookings->fetch_assoc()){ 

$status = strtolower(trim($row['booking_status']));

?>

<tr>

<td><?php echo $row['title']; ?></td>
<td><?php echo $row['travel_date']; ?></td>
<td><?php echo $row['persons']; ?></td>
<td>₹<?php echo $row['total_price']; ?></td>

<td>

<?php

if($status == 'pending'){
echo "<span class='badge bg-warning'>⏳ Pending</span>";
}
elseif($status == 'confirmed'){
echo "<span class='badge bg-success'>✔ Confirmed</span>";
}
elseif($status == 'cancelled'){
echo "<span class='badge bg-danger'>✖ Cancelled</span>";
}
else{
echo "<span class='badge bg-secondary'>Unknown</span>";
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

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>