<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

/* ================= FILTER ================= */

$year  = isset($_GET['year']) ? $_GET['year'] : date("Y");
$month = isset($_GET['month']) ? $_GET['month'] : "";

$where = "YEAR(bookings.created_at) = '$year'";

if(!empty($month)){
    $where .= " AND MONTH(bookings.created_at) = '$month'";
}

/* ================= MONTHLY BOOKINGS ================= */

$bookingData = [];
$q1 = $conn->query("
SELECT MONTH(bookings.created_at) as month,
COUNT(bookings.id) as total
FROM bookings
WHERE $where
GROUP BY MONTH(bookings.created_at)
ORDER BY MONTH(bookings.created_at)
");

while($row = $q1->fetch_assoc()){
    $bookingData[] = $row;
}

/* ================= MONTHLY REVENUE ================= */

$revenueData = [];
$q2 = $conn->query("
SELECT MONTH(bookings.created_at) as month,
SUM(bookings.total_price) as revenue
FROM bookings
WHERE booking_status='Confirmed' AND $where
GROUP BY MONTH(bookings.created_at)
ORDER BY MONTH(bookings.created_at)
");

while($row = $q2->fetch_assoc()){
    $revenueData[] = $row;
}

/* ================= TOTAL BOOKINGS & REVENUE ================= */

$currentStats = $conn->query("
SELECT 
COUNT(id) as total_bookings,
SUM(CASE WHEN booking_status='Confirmed' THEN total_price ELSE 0 END) as total_revenue
FROM bookings
WHERE $where
")->fetch_assoc();

/* ================= PREVIOUS PERIOD (FOR GROWTH) ================= */

$prevYear = $year - 1;

$prevStats = $conn->query("
SELECT 
COUNT(id) as total_bookings,
SUM(CASE WHEN booking_status='Confirmed' THEN total_price ELSE 0 END) as total_revenue
FROM bookings
WHERE YEAR(created_at) = '$prevYear'
")->fetch_assoc();

/* ================= GROWTH CALCULATION ================= */

$bookingGrowth = 0;
$revenueGrowth = 0;

if($prevStats['total_bookings'] > 0){
    $bookingGrowth = (($currentStats['total_bookings'] - $prevStats['total_bookings']) / $prevStats['total_bookings']) * 100;
}

if($prevStats['total_revenue'] > 0){
    $revenueGrowth = (($currentStats['total_revenue'] - $prevStats['total_revenue']) / $prevStats['total_revenue']) * 100;
}

/* ================= TOP 5 PACKAGES ================= */

$topPackages = $conn->query("
SELECT packages.title, COUNT(bookings.id) as total
FROM bookings
JOIN packages ON bookings.package_id = packages.id
WHERE $where
GROUP BY packages.id
ORDER BY total DESC
LIMIT 5
");

/* ================= TOP 5 USERS ================= */

$topUsers = $conn->query("
SELECT users.name, COUNT(bookings.id) as total
FROM bookings
JOIN users ON bookings.user_id = users.id
WHERE $where
GROUP BY users.id
ORDER BY total DESC
LIMIT 5
");

?>

<!DOCTYPE html>
<html>
<head>
<title>Advanced Reports</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{ background:#f4f6f9; }
.card{ border-radius:15px; }
</style>
</head>
<body>
<?php include("navbar_admin.php"); ?>
<div class="adminLayoutContent py-4">

<h3 class="mb-4">📊 Advanced Reports & Analytics</h3>

<!-- FILTER -->
<form method="GET" class="row g-3 mb-4">
<div class="col-md-3">
<select name="year" class="form-select">
<?php
for($y=date("Y"); $y>=2022; $y--){
    $selected = ($y==$year) ? "selected" : "";
    echo "<option value='$y' $selected>$y</option>";
}
?>
</select>
</div>

<div class="col-md-3">
<select name="month" class="form-select">
<option value="">All Months</option>
<?php
for($m=1;$m<=12;$m++){
    $selected = ($m==$month) ? "selected" : "";
    echo "<option value='$m' $selected>$m</option>";
}
?>
</select>
</div>

<div class="col-md-3">
<button class="btn btn-primary">Filter</button>
</div>
</form>

<!-- SUMMARY -->
<div class="row mb-4">

<div class="col-md-3">
<div class="card p-3 shadow-sm">
<h6>Total Bookings</h6>
<h4><?= $currentStats['total_bookings'] ?></h4>
<small class="<?= $bookingGrowth>=0 ? 'text-success':'text-danger' ?>">
<?= round($bookingGrowth,2) ?>% Growth
</small>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 shadow-sm">
<h6>Total Revenue</h6>
<h4>₹<?= $currentStats['total_revenue'] ?? 0 ?></h4>
<small class="<?= $revenueGrowth>=0 ? 'text-success':'text-danger' ?>">
<?= round($revenueGrowth,2) ?>% Growth
</small>
</div>
</div>

</div>

<!-- CHARTS -->
<div class="card p-4 shadow-sm mb-4">
<h5>📅 Monthly Bookings</h5>
<canvas id="bookingChart"></canvas>
</div>

<div class="card p-4 shadow-sm mb-4">
<h5>💰 Monthly Revenue</h5>
<canvas id="revenueChart"></canvas>
</div>

<!-- TOP LISTS -->
<div class="row">

<div class="col-md-6">
<div class="card p-3 shadow-sm">
<h5>🏆 Top 5 Packages</h5>
<?php while($row=$topPackages->fetch_assoc()){ ?>
<p><?= $row['title'] ?> - <?= $row['total'] ?> bookings</p>
<?php } ?>
</div>
</div>

<div class="col-md-6">
<div class="card p-3 shadow-sm">
<h5>👤 Top 5 Users</h5>
<?php while($row=$topUsers->fetch_assoc()){ ?>
<p><?= $row['name'] ?> - <?= $row['total'] ?> bookings</p>
<?php } ?>
</div>
</div>

</div>

</div>

<script>

/* BOOKINGS CHART */
new Chart(document.getElementById('bookingChart'), {
type: 'line',
data: {
labels: [<?php foreach($bookingData as $d){ echo "'".$d['month']."',"; } ?>],
datasets: [{
label: 'Bookings',
data: [<?php foreach($bookingData as $d){ echo $d['total'].","; } ?>],
borderWidth: 3,
tension:0.4
}]
},
options:{ animation:true }
});

/* REVENUE CHART */
new Chart(document.getElementById('revenueChart'), {
type: 'bar',
data: {
labels: [<?php foreach($revenueData as $d){ echo "'".$d['month']."',"; } ?>],
datasets: [{
label: 'Revenue',
data: [<?php foreach($revenueData as $d){ echo $d['revenue'].","; } ?>],
borderWidth:1
}]
},
options:{ animation:true }
});

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>