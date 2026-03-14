<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

/* ================= CANCEL BOOKING ================= */

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

        $message = "<div class='alert alert-success'>Booking cancelled successfully.</div>";

    }else{

        $message = "<div class='alert alert-danger'>Cancellation not allowed.</div>";

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
background:#f4f6f9;
}

.main-content{
padding:25px;
}

@media(max-width:768px){
.main-content{
margin-top:80px;
}
}

.card{
border-radius:12px;
border:none;
}

</style>

</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="main-content">

<div class="container-fluid">

<div class="card shadow-lg">

<div class="card-header bg-dark text-white">
<h4 class="mb-0"><i class="fa fa-calendar"></i> My Bookings</h4>
</div>

<div class="card-body">

<?= $message ?>

<?php

$result = $conn->query("

SELECT 
bookings.*,
packages.title,
c.destination,
c.status AS custom_status

FROM bookings

LEFT JOIN packages 
ON bookings.package_id = packages.id

LEFT JOIN custom_package_requests c
ON bookings.user_id = c.user_id 
AND bookings.travel_date = c.travel_date

WHERE bookings.user_id = $user_id

ORDER BY bookings.id DESC

");

?>

<?php if($result->num_rows > 0){ ?>

<div class="table-responsive">

<table class="table table-bordered table-hover text-center align-middle">

<thead class="table-secondary">

<tr>
<th>Package</th>
<th>Travel Date</th>
<th>Persons</th>
<th>Total Price</th>
<th>Status</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php while($row = $result->fetch_assoc()){ ?>

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

<td><?= $row['travel_date']; ?></td>

<td><?= $row['persons']; ?></td>

<td>₹<?= $row['total_price']; ?></td>

<td>

<?php

$status = "";

/* if custom package */

if($row['package_id'] == NULL){
    $status = $row['custom_status'];
}
else{
    $status = $row['booking_status'];
}

if($status=="Pending"){
echo "<span class='badge bg-warning text-dark'>Pending</span>";
}
elseif($status=="Accepted"){
echo "<span class='badge bg-primary'>Accepted</span>";
}
elseif($status=="Confirmed"){
echo "<span class='badge bg-success'>Confirmed</span>";
}
elseif($status=="Cancelled"){
echo "<span class='badge bg-danger'>Cancelled</span>";
}
elseif($status=="Completed"){
echo "<span class='badge bg-success'>Completed</span>";
}
else{
echo "<span class='badge bg-secondary'>Processing</span>";
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

<?php }else{ ?>

<div class="alert alert-info">
You have not made any bookings yet.
</div>

<?php } ?>

</div>

</div>


<div class="card mt-4 shadow-sm">

<div class="card-header bg-light">
<h5 class="mb-0">Cancellation Policy & Terms</h5>
</div>

<div class="card-body">

<ul>

<li>Bookings can be cancelled only if status is <strong>Pending</strong>.</li>

<li>Confirmed bookings cannot be cancelled online.</li>

<li>No cancellation allowed after travel date.</li>

<li>Refund processing may take 5-7 working days.</li>

<li>Company may cancel due to unavoidable conditions.</li>

<li>All disputes subject to local jurisdiction.</li>

</ul>

</div>

</div>

<div class="text-center mt-4">

<a href="packages.php" class="btn btn-secondary">
Browse More Packages
</a>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>