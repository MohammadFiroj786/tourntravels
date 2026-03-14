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
<title>Custom Package Requests</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body{
    background:#f4f6f9;
    font-family:'Segoe UI',sans-serif;
}

/* SIDEBAR */
.sidebar{
    width:250px;
    height:100vh;
    position:fixed;
    left:0;
    top:0;
    background:#ff7f50;
    padding:20px;
    color:white;
}
.sidebar a{
    display:block;
    color:white;
    text-decoration:none;
    padding:10px;
    border-radius:6px;
    margin-bottom:10px;
}
.sidebar a:hover{
    background:rgba(255,255,255,0.2);
}

/* MAIN */
.main-content{
    margin-left:260px;
    padding:25px;
}

/* MOBILE */
.mobile-nav{
    display:none;
}
@media(max-width:991px){
    .sidebar{ display:none; }
    .mobile-nav{ display:block; }
    .main-content{
        margin-left:0;
        padding:15px;
    }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<?php include("navbar_admin.php"); ?>

<div class="main-content">

<h3 class="mb-4 fw-bold">🌍 Custom Package Requests</h3>

<input type="text" id="search" class="form-control mb-3" placeholder="Search customer or destination">

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle text-center">

<thead class="table-dark">
<tr>
    <th>Customer</th>
    <th>Destination</th>
    <th>Date</th>
    <th>Travelers</th>
    <th>Hotel</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody id="requestTable">
<?php while($row = $result->fetch_assoc()){ ?>

<tr>
<td>
    <b><?= htmlspecialchars($row['name']) ?></b><br>
    <small><?= $row['phone'] ?></small>
</td>
<td><?= $row['destination'] ?></td>
<td><?= $row['travel_date'] ?></td>
<td><?= $row['travelers'] ?></td>
<td><?= $row['hotel'] ?></td>

<td>
<?php if($row['status']=="Accepted"){ ?>
    <span class="badge bg-success">Accepted</span>
<?php } else { ?>
    <span class="badge bg-warning text-dark">Pending</span>
<?php } ?>
</td>

<td>
<button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['id']; ?>">
    View
</button>
</td>
</tr>

<!-- MODAL -->
<div class="modal fade" id="viewModal<?= $row['id']; ?>">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Customer Request</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <p><b>Destination:</b> <?= $row['destination'] ?></p>
    <p><b>Style:</b> <?= $row['style'] ?></p>
    <p><b>Days:</b> <?= $row['days'] ?></p>
    <p><b>Travelers:</b> <?= $row['travelers'] ?></p>
    <p><b>Hotel:</b> <?= $row['hotel'] ?></p>

    <p><b>Customer Experience:</b></p>
    <div class="alert alert-light">
        <?= nl2br(htmlspecialchars($row['experience'])) ?>
    </div>

    <hr>

<?php if($row['status']!="Accepted"){ ?>

<form class="confirmForm" action="confirm_booking.php" method="POST">

<input type="hidden" name="request_id" value="<?= $row['id'] ?>">
<input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
<input type="hidden" name="travel_date" value="<?= $row['travel_date'] ?>">
<input type="hidden" name="persons" value="<?= $row['travelers'] ?>">

<label class="fw-bold">Package Price</label>
<input type="number" name="price" class="form-control mb-3" required>

<button class="btn btn-primary w-100">Confirm Booking</button>

</form>

<?php } else { ?>
<div class="alert alert-success text-center fw-bold">
    ✅ Booking Already Confirmed
</div>
<?php } ?>

<a href="tel:<?= $row['phone'] ?>" class="btn btn-success w-100 mt-2">📞 Call Customer</a>
<a href="https://wa.me/<?= $row['phone'] ?>" class="btn btn-success w-100 mt-2">💬 WhatsApp</a>

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

/* SWEETALERT CONFIRM */
document.querySelectorAll(".confirmForm").forEach(form=>{
    form.addEventListener("submit", function(e){
        e.preventDefault();
        Swal.fire({
            title: "Confirm Booking?",
            text: "This action cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, Confirm"
        }).then((result)=>{
            if(result.isConfirmed){
                form.submit();
            }
        });
    });
});
</script>

<?php if(isset($_GET['confirmed'])){ ?>
<script>
Swal.fire({
    icon:"success",
    title:"Booking Confirmed!",
    text:"Custom package has been booked successfully.",
    timer:2500,
    showConfirmButton:false
});
</script>
<?php } ?>

</body>
</html>