<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id']; 
$message = "";

/* CANCEL BOOKING */
if(isset($_GET['cancel_id'])){
    $booking_id = intval($_GET['cancel_id']);

    $check = $conn->query("
        SELECT * FROM bookings 
        WHERE id = $booking_id 
        AND user_id = $user_id 
        AND (booking_status = 'Pending' OR booking_status IS NULL)
    ");

    if($check->num_rows > 0){
        $conn->query("
            UPDATE bookings 
            SET booking_status = 'Cancelled' 
            WHERE id = $booking_id
        ");

        $message = "<div class='alert alert-success shadow-sm'>✅ Booking cancelled successfully.</div>";
    }else{
        $message = "<div class='alert alert-danger shadow-sm'>❌ Cancellation not allowed.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>My Bookings</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
body{
background: linear-gradient(to right,#eef2f3,#dfe9f3);
font-family:'Segoe UI',sans-serif;
}
.main-content{ padding:25px; }
.card{ border-radius:18px; border:none; transition:0.3s; }
.card:hover{ transform:translateY(-3px); box-shadow:0 10px 30px rgba(0,0,0,0.1); }
.card-header{ border-radius:18px 18px 0 0 !important; }
.table{ border-radius:10px; overflow:hidden; }
.table thead{ background:#f1f3f6; }
.badge{ padding:8px 12px; font-size:13px; border-radius:20px; }
.btn-danger{ border-radius:20px; padding:5px 12px; }
.empty-box{ text-align:center; padding:50px; background:white; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,0.05); }
.policy-box{ background:white; border-radius:15px; padding:20px; }
.fade-in{ animation:fadeIn 0.5s ease-in-out; }
@keyframes fadeIn{
from{opacity:0; transform:translateY(10px);}
to{opacity:1; transform:translateY(0);}
}
</style>

</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="main-content fade-in">
<div class="container-fluid">

<div class="mb-4">
<h3 class="fw-bold">📋 My Bookings</h3>
<p class="text-muted">Manage your trips and track booking status easily</p>
</div>

<?= $message ?>

<?php

$result = $conn->query("
SELECT 
bookings.*,
packages.title,
c.destination,
c.status AS custom_status,
payments.payment_status

FROM bookings

LEFT JOIN packages 
ON bookings.package_id = packages.id

LEFT JOIN custom_package_requests c
ON bookings.user_id = c.user_id 
AND bookings.travel_date = c.travel_date

LEFT JOIN payments
ON bookings.id = payments.booking_id

WHERE bookings.user_id = $user_id
ORDER BY bookings.id DESC
");
?>

<?php if($result->num_rows > 0){ ?>

<div class="card shadow-sm">

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover text-center align-middle">

<thead>
<tr>
<th>Package</th>
<th>Date</th>
<th>Persons</th>
<th>Price</th>
<th>Status</th>
<th>Payment</th> <!-- ADDED -->
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td>
<?php
if(!empty($row['title'])){
echo "<strong>".$row['title']."</strong>";
}else{
echo "<span class='badge bg-info text-dark'>Custom - ".$row['destination']."</span>";
}
?>
</td>

<td><?= $row['travel_date']; ?></td>
<td><?= $row['persons']; ?></td>
<td><strong>₹<?= $row['total_price']; ?></strong></td>

<td>
<?php
$status = ($row['package_id'] == NULL) ? $row['custom_status'] : $row['booking_status'];

if($status=="Pending"){
echo "<span class='badge bg-warning text-dark'>⏳ Pending</span>";
}
elseif($status=="Accepted"){
echo "<span class='badge bg-primary'>👍 Accepted</span>";
}
elseif($status=="Confirmed"){
echo "<span class='badge bg-success'>✅ Confirmed</span>";
}
elseif($status=="Cancelled"){
echo "<span class='badge bg-danger'>❌ Cancelled</span>";
}
elseif($status=="Completed"){
echo "<span class='badge bg-success'>🎉 Completed</span>";
}
else{
echo "<span class='badge bg-secondary'>Processing</span>";
}
?>
</td>

<!-- PAYMENT COLUMN -->
<td>
<?php
if($row['payment_status'] == 'paid'){
    echo "<span class='badge bg-success'>💰 Paid</span>";
}elseif($row['payment_status'] == 'pending'){
    echo "<span class='badge bg-warning text-dark'>Pending</span>";
}else{
    echo "<span class='badge bg-secondary'>N/A</span>";
}
?>
</td>

<td>

<?php if($status=="Pending"){ ?>

<a href="my-bookings.php?cancel_id=<?= $row['id']; ?>"
class="btn btn-sm btn-danger"
onclick="return confirm('Cancel this booking?');">
Cancel
</a>

<?php }else{ ?>

<span class="text-muted">Not Allowed</span>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<?php }else{ ?>

<div class="empty-box">
<h5>No bookings yet 😔</h5>
<p>Start planning your dream trip now!</p>

<a href="packages.php" class="btn btn-primary mt-2">
Explore Packages
</a>
</div>

<?php } ?>

<div class="policy-box mt-4 shadow-sm">

<h5>📌 Cancellation Policy</h5>

<ul class="mt-3">
<li>Cancel only if status is <strong>Pending</strong></li>
<li>Confirmed bookings cannot be cancelled online</li>
<li>No cancellation after travel date</li>
<li>Refund processed in 5–7 days</li>
<li>Company may cancel due to conditions</li>
</ul>

</div>

<div class="text-center mt-4">
<a href="packages.php" class="btn btn-dark">
🚀 Book Another Trip
</a>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>