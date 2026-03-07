<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

/* ================= EXPORT EXCEL ================= */
if(isset($_GET['export']) && $_GET['export']=="excel"){
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=payments.xls");

    echo "ID\tBooking ID\tMethod\tTransaction ID\tAmount\tStatus\tDate\n";

    $result = $conn->query("SELECT * FROM payments");

    while($row = $result->fetch_assoc()){
        echo $row['id']."\t".
             $row['booking_id']."\t".
             $row['payment_method']."\t".
             $row['transaction_id']."\t".
             $row['amount']."\t".
             $row['payment_status']."\t".
             $row['created_at']."\n";
    }
    exit();
}

/* ================= INVOICE PDF ================= */
if(isset($_GET['invoice'])){
    $id = $_GET['invoice'];
    $payment = $conn->query("SELECT * FROM payments WHERE id=$id")->fetch_assoc();

    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=invoice_$id.pdf");

    echo "
    <h2>Invoice</h2>
    <p><strong>Payment ID:</strong> {$payment['id']}</p>
    <p><strong>Booking ID:</strong> {$payment['booking_id']}</p>
    <p><strong>Method:</strong> {$payment['payment_method']}</p>
    <p><strong>Transaction ID:</strong> {$payment['transaction_id']}</p>
    <p><strong>Amount:</strong> ₹{$payment['amount']}</p>
    <p><strong>Status:</strong> {$payment['payment_status']}</p>
    <p><strong>Date:</strong> {$payment['created_at']}</p>
    ";
    exit();
}

/* ================= MONTHLY REVENUE ================= */
$chartData = [];
$chartQuery = $conn->query("
SELECT DATE_FORMAT(created_at,'%Y-%m') as month,
SUM(amount) as total
FROM payments
WHERE payment_status='Paid'
GROUP BY month
ORDER BY month ASC
");

while($row = $chartQuery->fetch_assoc()){
    $chartData[] = $row;
}

/* ================= TOTAL REVENUE ================= */
$stats = $conn->query("
SELECT 
SUM(CASE WHEN payment_status='Paid' THEN amount ELSE 0 END) as revenue,
COUNT(*) as total
FROM payments
")->fetch_assoc();

/* ================= FETCH PAYMENTS ================= */
$result = $conn->query("SELECT * FROM payments ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Advanced Payment Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{ background:#f4f6f9; }
.card{ border-radius:12px; }

.content{
margin-left:250px;
padding:25px;
}

@media(max-width:768px){
.content{
margin-left:0;
padding:15px;
}
}
</style>
</head>
<body>
<?php include("navbar_admin.php"); ?>
<div class="adminLayoutContent">

<h3 class="mb-4">💳 Payment Management</h3>

<!-- SUMMARY -->
<div class="row mb-4">
<div class="col-md-6">
<div class="card shadow-sm p-3">
<h6>Total Revenue</h6>
<h4 class="text-success">₹<?= $stats['revenue'] ?? 0 ?></h4>
</div>
</div>

<div class="col-md-6">
<div class="card shadow-sm p-3">
<h6>Total Transactions</h6>
<h4><?= $stats['total'] ?></h4>
</div>
</div>
</div>

<!-- REVENUE CHART -->
<div class="card shadow-sm p-4 mb-4">
<h5>📊 Monthly Revenue</h5>
<canvas id="revenueChart"></canvas>
</div>

<!-- EXPORT BUTTON -->
<a href="?export=excel" class="btn btn-success mb-3">
Export to Excel
</a>

<!-- PAYMENTS TABLE -->
<div class="card shadow-sm p-3">
<div class="table-responsive">
<table class="table table-hover">

<thead class="table-light">
<tr>
<th>ID</th>
<th>Booking ID</th>
<th>Method</th>
<th>Transaction ID</th>
<th>Amount</th>
<th>Status</th>
<th>Date</th>
<th>Invoice</th>
</tr>
</thead>

<tbody>
<?php while($row = $result->fetch_assoc()): ?>
<tr>

<td><?= $row['id'] ?></td>
<td><?= $row['booking_id'] ?></td>
<td><?= $row['payment_method'] ?></td>
<td><?= $row['transaction_id'] ?></td>
<td>₹<?= $row['amount'] ?></td>

<td>
<?php if($row['payment_status']=="Paid"): ?>
<span class="badge bg-success">Paid</span>
<?php else: ?>
<span class="badge bg-warning"><?= $row['payment_status'] ?></span>
<?php endif; ?>
</td>

<td><?= date("d M Y", strtotime($row['created_at'])) ?></td>

<td>
<a href="?invoice=<?= $row['id'] ?>" 
class="btn btn-sm btn-outline-primary">
Download PDF
</a>
</td>

</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>
</div>

</div>

<script>
const ctx = document.getElementById('revenueChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
            <?php foreach($chartData as $data){ echo "'".$data['month']."',"; } ?>
        ],
        datasets: [{
            label: 'Revenue',
            data: [
                <?php foreach($chartData as $data){ echo $data['total'].","; } ?>
            ],
            borderWidth: 1
        }]
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>