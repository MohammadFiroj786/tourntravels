<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$total_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];
$total_packages = $conn->query("SELECT COUNT(*) as total FROM packages")->fetch_assoc()['total'];
$total_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];

$total_revenue = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE booking_status='confirmed'")->fetch_assoc()['total'];

$recent_bookings = $conn->query("
SELECT bookings.*, users.name, packages.title
FROM bookings
JOIN users ON bookings.user_id = users.id
JOIN packages ON bookings.package_id = packages.id
ORDER BY bookings.created_at DESC
LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Dashboard</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>

body{
font-family:'Poppins',sans-serif;
background:#f4f7fc;
}

/* DASHBOARD CARDS */

.dashboard-card{
border-radius:20px;
padding:30px;
color:white;
transition:0.3s;
box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

.dashboard-card:hover{
transform:translateY(-8px);
}

.bg1{ background:linear-gradient(135deg,#667eea,#764ba2); }
.bg2{ background:linear-gradient(135deg,#43cea2,#185a9d); }
.bg3{ background:linear-gradient(135deg,#f7971e,#ffd200); }
.bg4{ background:linear-gradient(135deg,#ff416c,#ff4b2b); }

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
font-size:13px;
padding:6px 12px;
border-radius:20px;
}

</style>

</head>

<body>

<!-- Sidebar -->
<?php include("navbar_admin.php"); ?>

<div class="adminLayoutContent">

<h2 class="mb-4 fw-bold">Dashboard Overview</h2>

<div class="row g-4">

<div class="col-lg-3 col-md-6">
<div class="dashboard-card bg1">
<h2><?php echo $total_users; ?></h2>
<p>Total Users</p>
</div>
</div>

<div class="col-lg-3 col-md-6">
<div class="dashboard-card bg2">
<h2><?php echo $total_packages; ?></h2>
<p>Total Packages</p>
</div>
</div>

<div class="col-lg-3 col-md-6">
<div class="dashboard-card bg3 text-dark">
<h2><?php echo $total_bookings; ?></h2>
<p>Total Bookings</p>
</div>
</div>

<div class="col-lg-3 col-md-6">
<div class="dashboard-card bg4">
<h2>₹<?php echo $total_revenue ?? 0; ?></h2>
<p>Total Revenue</p>
</div>
</div>

</div>

<hr class="my-5">

<h4 class="mb-3 fw-semibold">Recent Bookings</h4>

<div class="card shadow-lg border-0">

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
</tr>
</thead>

<tbody>

<?php while($row = $recent_bookings->fetch_assoc()){ 

$status = strtolower(trim($row['booking_status']));
?>

<tr>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['persons']; ?></td>

<td>₹<?php echo $row['total_price']; ?></td>

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

<td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>