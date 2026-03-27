<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT c.*, u.name, u.phone, u.email 
        FROM custom_package_requests c
        JOIN users u ON c.user_id = u.id
        ORDER BY c.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Custom Tour Requests</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

body{
background:#f4f7fc;
font-family:'Poppins',sans-serif;
}

/* HEADER */
.page-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.page-title{
font-weight:600;
}

/* SEARCH BOX */
.search-box{
border-radius:30px;
padding:10px 20px;
border:1px solid #ddd;
box-shadow:0 3px 10px rgba(0,0,0,0.05);
}

/* TABLE */
.table{
background:white;
border-radius:12px;
overflow:hidden;
box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.table thead{
background:linear-gradient(135deg,#667eea,#764ba2);
color:white;
}

.table th{
font-size:14px;
}

.table td{
font-size:13px;
}

/* BADGES */
.badge-success{
background:#28a745;
}
.badge-warning{
background:#ffc107;
color:black;
}

/* BUTTONS */
.btn-info{
background:#17a2b8;
border:none;
}
.btn-info:hover{
background:#138496;
}

/* MODAL */
.modal-content{
border-radius:15px;
box-shadow:0 10px 40px rgba(0,0,0,0.2);
}

.modal-header{
background:linear-gradient(135deg,#667eea,#764ba2);
color:white;
border-radius:15px 15px 0 0;
}

/* CARD SECTIONS */
.section-box{
background:#f9fafc;
padding:15px;
border-radius:10px;
margin-bottom:15px;
}

/* CONFIRM BUTTON */
.btn-confirm{
background:#28a745;
color:white;
border:none;
}
.btn-confirm:hover{
background:#218838;
}

/* CONTACT BUTTON */
.btn-contact{
border-radius:30px;
}

</style>

</head>

<body>

<?php include("navbar_admin.php"); ?>

<div class="adminLayoutContent">

<!-- HEADER -->
<div class="page-header">
<h3 class="page-title">🌍 Custom Tour Requests</h3>
<input type="text" id="search" class="search-box" placeholder="🔍 Search customer, destination...">
</div>

<div class="table-responsive">

<table class="table align-middle text-center">

<thead>
<tr>
<th>Customer</th>
<th>Service</th>
<th>Destination</th>
<th>Travelers</th>
<th>Days</th>
<th>Date</th>
<th>Price</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody id="requestTable">

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td>
<b><?= htmlspecialchars($row['name']) ?></b><br>
<small><?= htmlspecialchars($row['phone']) ?></small>
</td>

<td>
<?php 
$service_labels = [
'full'=>'🚗 Full Trip',
'stay'=>'🏨 Stay',
'sightseeing'=>'🏔️ Sightseeing'
];
echo $service_labels[$row['service_type']] ?? $row['service_type'];
?>
</td>

<td><?= htmlspecialchars($row['destinations'] ?? 'N/A') ?></td>
<td><?= $row['travelers'] ?></td>
<td><?= $row['days'] ?></td>
<td><?= $row['travel_date'] ?></td>

<td>
<?= isset($row['price']) ? '₹'.number_format($row['price']) : '—' ?>
</td>

<td>
<?php if($row['status']=="Accepted"){ ?>
<span class="badge bg-success">Confirmed</span>
<?php } else { ?>
<span class="badge bg-warning">Pending</span>
<?php } ?>
</td>

<td>
<button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#view<?= $row['id'] ?>">
View
</button>
</td>

</tr>

<!-- MODAL -->
<div class="modal fade" id="view<?= $row['id'] ?>">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5>📋 Request Details</h5>
<button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="section-box">
<h6>👤 Customer</h6>
<p><?= $row['name'] ?> | <?= $row['phone'] ?></p>
<p><?= $row['email'] ?></p>
</div>

<div class="section-box">
<h6>📦 Trip Info</h6>
<p><b>Destination:</b> <?= $row['destinations'] ?></p>
<p><b>Travelers:</b> <?= $row['travelers'] ?></p>
<p><b>Days:</b> <?= $row['days'] ?></p>
<p><b>Date:</b> <?= $row['travel_date'] ?></p>
</div>

<?php if($row['status']!="Accepted"){ ?>

<form class="confirmForm" method="POST" action="confirm_booking.php">
<input type="hidden" name="request_id" value="<?= $row['id'] ?>">
<input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">

<label class="fw-bold">Enter Price (₹)</label>
<input type="number" name="price" class="form-control mb-3" required>

<button class="btn btn-confirm w-100">✅ Confirm Booking</button>
</form>

<?php } else { ?>

<div class="alert alert-success text-center mt-3">
✅ Already Confirmed
</div>

<?php } ?>

<div class="mt-3">
<a href="tel:<?= $row['phone'] ?>" class="btn btn-success w-100 mb-2 btn-contact">📞 Call</a>
<a href="https://wa.me/<?= $row['phone'] ?>" class="btn btn-success w-100 btn-contact">💬 WhatsApp</a>
</div>

</div>

</div>
</div>
</div>

<?php } ?>

</tbody>
</table>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

/* SEARCH */
document.getElementById("search").addEventListener("keyup", function(){
let value = this.value.toLowerCase();
document.querySelectorAll("#requestTable tr").forEach(row=>{
row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
});
});

/* CONFIRM ALERT */
document.querySelectorAll(".confirmForm").forEach(form=>{
form.addEventListener("submit", function(e){
e.preventDefault();
Swal.fire({
title:"Confirm Booking?",
icon:"warning",
showCancelButton:true,
confirmButtonText:"Yes Confirm"
}).then((res)=>{
if(res.isConfirmed) form.submit();
});
});
});

</script>

</body>
</html>