<?php
include("../includes/session_check.php");
include("../includes/db.php");

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ================= FILTER ================= */

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$from_date = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
$to_date = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';

// ✅ FIXED: Escape variables to prevent SQL injection
$search = $conn->real_escape_string($search);
$from_date = $conn->real_escape_string($from_date);
$to_date = $conn->real_escape_string($to_date);

$where = " WHERE 1=1 ";

if ($search != '') {
    $where .= " AND (u.name LIKE '%$search%' 
                OR p.title LIKE '%$search%')";
}

if ($from_date != '' && $to_date != '') {
    $where .= " AND b.travel_date 
                BETWEEN '$from_date' AND '$to_date'";
}

/* ================= EXPORT EXCEL ================= */

if (isset($_GET['export']) && $_GET['export'] == "excel") {
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=bookings.xls");

echo "ID\tUser\tPackage\tTravel Date\tPersons\tTotal\tStatus\tPayment\n";

$exportQuery = "
SELECT 
b.*, 
u.name AS user_name, 
p.title AS package_title,
pay.payment_status

FROM bookings b
LEFT JOIN users u ON b.user_id = u.id
LEFT JOIN packages p ON b.package_id = p.id

LEFT JOIN payments pay ON pay.id = (
    SELECT id FROM payments 
    WHERE booking_id = b.id 
    ORDER BY created_at DESC 
    LIMIT 1
)

$where
";

$result = $conn->query($exportQuery);

while ($row = $result->fetch_assoc()) {
    echo $row['id'] . "\t" .
    $row['user_name'] . "\t" .
$row['package_title']."\t".
$row['travel_date']."\t".
$row['persons']."\t".
$row['total_price']."\t".
$row['booking_status']."\t".
($row['payment_status'] ?? 'No Payment')."\n";
}

exit();
}

/* ================= UPDATE ================= */

if (isset($_POST['update_booking'])) {

$booking_id = (int)$_POST['booking_id'];
$travel_date = $_POST['travel_date'];
$booking_status = $_POST['booking_status'];
$payment_status = $_POST['payment_status'];

/* UPDATE BOOKING */
$stmt = $conn->prepare("
UPDATE bookings 
SET travel_date=?, booking_status=? 
WHERE id=?");

$stmt->bind_param("ssi", $travel_date, $booking_status, $booking_id);
$stmt->execute();

/* CHECK PAYMENT */
$check = $conn->query("
SELECT id FROM payments 
WHERE booking_id = $booking_id 
ORDER BY created_at DESC 
LIMIT 1
");

if($check && $check->num_rows > 0){

    $row = $check->fetch_assoc();
    $payment_id = $row['id'];

    $stmt2 = $conn->prepare("
    UPDATE payments 
    SET payment_status=? 
    WHERE id=?");

    $stmt2->bind_param("si", $payment_status, $payment_id);
    $stmt2->execute();

}else{

    $stmt3 = $conn->prepare("
    INSERT INTO payments (booking_id, payment_status, amount, created_at)
    VALUES (?, ?, 0, NOW())
    ");

    $stmt3->bind_param("is", $booking_id, $payment_status);
    $stmt3->execute();
}

echo "<script>alert('Updated Successfully'); window.location.href='manage_bookings.php';</script>";
exit();
}

/* ================= STATS ================= */

$statsQuery = "
SELECT 
COUNT(*) as total_bookings,
SUM(CASE WHEN booking_status='Confirmed' THEN 1 ELSE 0 END) as confirmed,
SUM(total_price) as revenue
FROM bookings";

$stats = $conn->query($statsQuery)->fetch_assoc();

/* ================= FETCH BOOKINGS ================= */

$query = "
SELECT 
b.*, 
u.name AS user_name, 
p.title AS package_title,

pay.payment_status,
pay.payment_method,
pay.transaction_id,
pay.amount

FROM bookings b

LEFT JOIN users u ON b.user_id = u.id
LEFT JOIN packages p ON b.package_id = p.id

LEFT JOIN payments pay ON pay.id = (
    SELECT id FROM payments 
    WHERE booking_id = b.id 
    ORDER BY created_at DESC 
    LIMIT 1
)

$where
ORDER BY b.created_at DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Bookings</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{ background:#f8fafc; font-family:Segoe UI; }
.main-content{ margin-left:260px; padding:30px; }
.card{ border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.table thead{ background:#f1f5f9; }
</style>
</head>

<body>

<?php include("navbar_admin.php"); ?>

<div class="main-content">

<h2 class="mb-4">Manage Bookings</h2>

<div class="row mb-4">

<div class="col-md-4">
<div class="card p-3">
<h6>Total Bookings</h6>
<h3><?= $stats['total_bookings'] ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card p-3">
<h6>Confirmed</h6>
<h3><?= $stats['confirmed'] ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card p-3">
<h6>Total Revenue</h6>
<h3>₹<?= $stats['revenue'] ?? 0 ?></h3>
</div>
</div>

</div>

<form method="GET" class="card p-3 mb-4">
<div class="row g-2">

<div class="col-md-3">
<input type="text" name="search" class="form-control"
placeholder="Search user/package"
value="<?= $search ?>">
</div>

<div class="col-md-3">
<input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
</div>

<div class="col-md-3">
<input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
</div>

<div class="col-md-3 d-flex gap-2">
<button class="btn btn-primary w-100">Filter</button>
<a class="btn btn-success"
href="?export=excel&search=<?= $search ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>">
Excel
</a>
</div>

</div>
</form>

<div class="card p-3">
<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>
<tr>
<th>ID</th>
<th>User</th>
<th>Package</th>
<th>Date</th>
<th>Persons</th>
<th>Total</th>
<th>Status</th>
<th>Payment</th>
<th>Method</th>
<th>Txn</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<form method="POST">
<tr>

<td><?= $row['id'] ?></td>
<td><?= $row['user_name'] ?></td>
<td><?= $row['package_title'] ?></td>

<td>
<input type="date" name="travel_date" class="form-control"
value="<?= $row['travel_date'] ?>">
</td>

<td><?= $row['persons'] ?></td>
<td>₹<?= $row['total_price'] ?></td>

<td>
<select name="booking_status" class="form-select">
<option value="Pending" <?= $row['booking_status']=="Pending"?"selected":"" ?>>Pending</option>
<option value="Confirmed" <?= $row['booking_status']=="Confirmed"?"selected":"" ?>>Confirmed</option>
<option value="Cancelled" <?= $row['booking_status']=="Cancelled"?"selected":"" ?>>Cancelled</option>
</select>
</td>

<td>
<select name="payment_status" class="form-select">
<option value="pending" <?= strtolower($row['payment_status'])=="pending"?"selected":"" ?>>Pending</option>
<option value="paid" <?= strtolower($row['payment_status'])=="paid"?"selected":"" ?>>Paid</option>
<option value="failed" <?= strtolower($row['payment_status'])=="failed"?"selected":"" ?>>Failed</option>
</select>
</td>

<td><?= $row['payment_method'] ?? '-' ?></td>
<td><?= $row['transaction_id'] ?? '-' ?></td>

<td>
<input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
<button name="update_booking" class="btn btn-primary btn-sm">Update</button>
</td>

</tr>
</form>

<?php endwhile; ?>

</tbody>

</table>

</div>
</div>

</div>

</body>
</html>