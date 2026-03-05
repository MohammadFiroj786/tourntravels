<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

/* ================= USER ACTIONS ================= */

if(isset($_GET['block'])){
    $conn->query("UPDATE users SET status='Blocked' WHERE id=".$_GET['block']);
}

if(isset($_GET['unblock'])){
    $conn->query("UPDATE users SET status='Active' WHERE id=".$_GET['unblock']);
}

if(isset($_GET['delete'])){
    $conn->query("DELETE FROM users WHERE id=".$_GET['delete']);
}

if(isset($_POST['change_role'])){
    $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
    $stmt->bind_param("si", $_POST['role'], $_POST['user_id']);
    $stmt->execute();
}

/* ================= SEARCH ================= */

$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

$where = "WHERE 1=1";

if($search != ''){
    $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
}

if($roleFilter != ''){
    $where .= " AND role='$roleFilter'";
}

/* ================= FETCH USERS ================= */

$users = $conn->query("SELECT * FROM users $where ORDER BY created_at DESC");

/* ================= STATS ================= */

$stats = $conn->query("
SELECT 
COUNT(*) total,
SUM(CASE WHEN status='Active' THEN 1 ELSE 0 END) active_users,
SUM(CASE WHEN status='Blocked' THEN 1 ELSE 0 END) blocked_users
FROM users
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body{
    background:#f4f6f9;
}
.card{
    border-radius:12px;
}
.table th{
    font-size:14px;
}
.action-btn{
    padding:5px 10px;
}
</style>
</head>
<body>

<div class="container py-4">

<h3 class="mb-4">👥 Manage Users</h3>

<!-- STATS -->
<div class="row mb-4">
<div class="col-md-4">
<div class="card shadow-sm p-3">
<h6>Total Users</h6>
<h4><?= $stats['total'] ?></h4>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm p-3">
<h6>Active Users</h6>
<h4 class="text-success"><?= $stats['active_users'] ?></h4>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm p-3">
<h6>Blocked Users</h6>
<h4 class="text-danger"><?= $stats['blocked_users'] ?></h4>
</div>
</div>
</div>

<!-- SEARCH & FILTER -->
<form method="GET" class="card shadow-sm p-3 mb-4">
<div class="row g-2 align-items-center">
<div class="col-md-5">
<input type="text" name="search" class="form-control"
placeholder="Search by name or email"
value="<?= $search ?>">
</div>

<div class="col-md-3">
<select name="role" class="form-select">
<option value="">All Roles</option>
<option value="user" <?= $roleFilter=="user"?"selected":"" ?>>User</option>
<option value="admin" <?= $roleFilter=="admin"?"selected":"" ?>>Admin</option>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Filter</button>
</div>

<div class="col-md-2">
<a href="manage_users.php" class="btn btn-secondary w-100">Reset</a>
</div>
</div>
</form>

<!-- USERS TABLE -->
<div class="card shadow-sm p-3">
<div class="table-responsive">
<table class="table table-hover align-middle">

<thead class="table-light">
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Role</th>
<th>Status</th>
<th>Joined</th>
<th class="text-center">Actions</th>
</tr>
</thead>

<tbody>

<?php while($row = $users->fetch_assoc()): ?>
<tr>

<td><?= $row['id'] ?></td>
<td><?= $row['name'] ?></td>
<td><?= $row['email'] ?></td>
<td><?= $row['phone'] ?></td>

<td>
<form method="POST" class="d-flex">
<input type="hidden" name="user_id" value="<?= $row['id'] ?>">
<select name="role" class="form-select form-select-sm me-2">
<option value="user" <?= $row['role']=="user"?"selected":"" ?>>User</option>
<option value="admin" <?= $row['role']=="admin"?"selected":"" ?>>Admin</option>
</select>
<button name="change_role" class="btn btn-sm btn-outline-primary">
<i class="fa fa-save"></i>
</button>
</form>
</td>

<td>
<?php if($row['status']=="Active"): ?>
<span class="badge bg-success">Active</span>
<?php else: ?>
<span class="badge bg-danger">Blocked</span>
<?php endif; ?>
</td>

<td><?= date("d M Y", strtotime($row['created_at'])) ?></td>

<td class="text-center">

<?php if($row['status']=="Active"): ?>
<a href="?block=<?= $row['id'] ?>" 
class="btn btn-sm btn-outline-warning action-btn">
<i class="fa fa-ban"></i>
</a>
<?php else: ?>
<a href="?unblock=<?= $row['id'] ?>" 
class="btn btn-sm btn-outline-success action-btn">
<i class="fa fa-check"></i>
</a>
<?php endif; ?>

<a href="?delete=<?= $row['id'] ?>" 
onclick="return confirm('Delete this user?')" 
class="btn btn-sm btn-outline-danger action-btn">
<i class="fa fa-trash"></i>
</a>

<button class="btn btn-sm btn-outline-info action-btn"
data-bs-toggle="collapse"
data-bs-target="#history<?= $row['id'] ?>">
<i class="fa fa-eye"></i>
</button>

</td>
</tr>

<!-- BOOKING HISTORY COLLAPSE -->
<tr class="collapse bg-light" id="history<?= $row['id'] ?>">
<td colspan="8">
<strong>Booking History:</strong>

<table class="table table-sm mt-2">
<tr>
<th>Package</th>
<th>Date</th>
<th>Persons</th>
<th>Total</th>
<th>Status</th>
</tr>

<?php
$history = $conn->query("
SELECT bookings.*, packages.title 
FROM bookings
JOIN packages ON bookings.package_id = packages.id
WHERE bookings.user_id=".$row['id']);

while($b = $history->fetch_assoc()):
?>

<tr>
<td><?= $b['title'] ?></td>
<td><?= $b['travel_date'] ?></td>
<td><?= $b['persons'] ?></td>
<td>₹<?= $b['total_price'] ?></td>
<td><?= $b['booking_status'] ?></td>
</tr>

<?php endwhile; ?>

</table>

</td>
</tr>

<?php endwhile; ?>

</tbody>
</table>
</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>