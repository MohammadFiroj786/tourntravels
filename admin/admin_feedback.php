<?php
include("../includes/session_check.php");
include("../includes/db.php");

/* ADMIN CHECK */
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

/* DELETE FEEDBACK */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM feedback WHERE id=$id");
    header("Location: feedback_admin.php");
    exit();
}

/* FILTER */
$where = "";
if(isset($_GET['critical'])){
    $where = "WHERE rating <= 2";
}

/* STATS */
$total = $conn->query("SELECT COUNT(*) t FROM feedback")->fetch_assoc()['t'];
$avg = $conn->query("SELECT ROUND(AVG(rating),1) a FROM feedback")->fetch_assoc()['a'];
$negative = $conn->query("SELECT COUNT(*) n FROM feedback WHERE rating<=2")->fetch_assoc()['n'];

/* CHART DATA */
$chartData = [];
for($i=1;$i<=5;$i++){
    $chartData[] = $conn->query("SELECT COUNT(*) c FROM feedback WHERE rating=$i")->fetch_assoc()['c'];
}

/* FEEDBACK LIST */
$feedbacks = $conn->query("
SELECT f.*, u.name 
FROM feedback f
LEFT JOIN users u ON f.user_id=u.id
$where
ORDER BY f.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Feedback Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body{
    background:#f4f6f9;
    font-family:'Poppins',sans-serif;
}
.card{border:none;border-radius:18px;}
.stat{padding:25px;text-align:center;}
.stat h2{font-weight:700;}
.star{color:gold;}
.badge{border-radius:20px;padding:6px 14px;}
.negative{background:#ffe5e5;}
</style>
</head>

<body>

<?php include("navbar_admin.php"); ?>

<div class="adminLayoutContent py-4">

<h3 class="fw-bold mb-4">⭐ Feedback Dashboard</h3>

<!-- STATS -->
<div class="row g-4 mb-4">
<div class="col-md-4">
<div class="card stat shadow">
<h6>Total Reviews</h6>
<h2><?= $total ?></h2>
</div>
</div>

<div class="col-md-4">
<div class="card stat shadow">
<h6>Average Rating</h6>
<h2><?= $avg ?: '0.0' ?>/5</h2>
</div>
</div>

<div class="col-md-4">
<div class="card stat shadow">
<h6>Critical Reviews</h6>
<h2><?= $negative ?></h2>
</div>
</div>
</div>

<!-- CHART -->
<div class="card shadow p-4 mb-4">
<h5>📊 Rating Distribution</h5>
<canvas id="ratingChart"></canvas>
</div>

<!-- FILTER -->
<a href="<?= $_SERVER['PHP_SELF'] ?>" 
class="btn btn-sm <?= !isset($_GET['critical']) ? 'btn-dark' : 'btn-outline-dark' ?>">
All
</a>

<a href="<?= $_SERVER['PHP_SELF'] ?>?critical=1" 
class="btn btn-sm <?= isset($_GET['critical']) ? 'btn-danger' : 'btn-outline-danger' ?>">
🚨 Critical Only
</a>

<!-- TABLE -->
<div class="card shadow p-4">
<h5 class="mb-3">🗂 Feedback List</h5>

<div class="table-responsive">
<table class="table table-hover align-middle">

<thead class="table-dark">
<tr>
<th>User</th>
<th>Booking</th>
<th>Rating</th>
<th>Emoji</th>
<th>Message</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if($feedbacks->num_rows > 0){ ?>
<?php while($f = $feedbacks->fetch_assoc()){ ?>

<tr class="<?= ($f['rating'] <=2) ? 'negative' : '' ?>">

<td><?= htmlspecialchars($f['name'] ?? 'User #'.$f['user_id']) ?></td>
<td>#<?= $f['booking_id'] ?></td>

<td>
<?php for($i=1;$i<=5;$i++): ?>
<i class="fa fa-star <?= $i <= $f['rating'] ? 'star':'' ?>"></i>
<?php endfor; ?>
</td>

<td><?= $f['emoji'] ?: '—' ?></td>

<td><?= mb_strimwidth($f['message'],0,40,'...') ?></td>

<td><?= date('d M Y', strtotime($f['created_at'])) ?></td>

<td>
<button class="btn btn-sm btn-primary"
data-bs-toggle="modal"
data-bs-target="#view<?= $f['id'] ?>">
View
</button>

<a href="?delete=<?= $f['id'] ?>"
onclick="return confirm('Delete this feedback?')"
class="btn btn-sm btn-outline-danger">
🗑
</a>
</td>

</tr>

<!-- MODAL -->
<div class="modal fade" id="view<?= $f['id'] ?>">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5>Feedback Detail</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<p><strong>User:</strong> <?= htmlspecialchars($f['name'] ?? 'User #'.$f['user_id']) ?></p>
<p><strong>Booking ID:</strong> #<?= $f['booking_id'] ?></p>
<p><strong>Rating:</strong> <?= $f['rating'] ?>/5 <?= $f['emoji'] ?></p>
<hr>
<p><?= nl2br(htmlspecialchars($f['message'])) ?></p>
</div>

</div>
</div>
</div>

<?php } ?>
<?php } else { ?>
<tr>
<td colspan="7" class="text-center text-muted">No feedback found.</td>
</tr>
<?php } ?>

</tbody>

</table>
</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('ratingChart'), {
    type:'bar',
    data:{
        labels:['1⭐','2⭐','3⭐','4⭐','5⭐'],
        datasets:[{
            data: <?= json_encode($chartData) ?>,
        }]
    },
    options:{
        plugins:{legend:{display:false}},
        responsive:true
    }
});
</script>

</body>
</html>