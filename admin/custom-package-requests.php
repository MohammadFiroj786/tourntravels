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

<style>

body{
background:#f4f6f9;
font-family:'Segoe UI',sans-serif;
}

/* ===== MAIN CONTENT ===== */

.main-content{
margin-left:250px;
padding:25px;
transition:0.3s;
}

/* ===== CARD DESIGN ===== */

.request-card{
border-radius:14px;
padding:22px;
background:white;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
transition:0.3s;
height:100%;
}

.request-card:hover{
transform:translateY(-6px);
box-shadow:0 15px 35px rgba(0,0,0,0.15);
}

/* ===== STATUS BADGE ===== */

.status{
font-size:12px;
padding:6px 12px;
border-radius:20px;
font-weight:600;
}

.pending{
background:#fff3cd;
color:#856404;
}

/* ===== CUSTOMER INFO ===== */

.customer-info p{
margin-bottom:4px;
font-size:14px;
}

/* ===== REQUEST TEXT ===== */

.request-box{
background:#f8f9fa;
padding:10px;
border-radius:8px;
font-size:14px;
}

/* ===== BUTTONS ===== */

.action-buttons a{
margin-bottom:6px;
}

/* ===== MOBILE RESPONSIVE ===== */

@media(max-width:991px){

.main-content{
margin-left:0;
padding:15px;
}

.request-card{
padding:18px;
}

h2{
font-size:22px;
}

}

</style>

</head>

<body>

<?php include("navbar_admin.php"); ?>

<div class="main-content">

<div class="container-fluid">

<h2 class="mb-4 fw-bold">
📦 Custom Package Requests
</h2>

<div class="row">

<?php while($row = $result->fetch_assoc()) { ?>

<div class="col-lg-6 col-md-12 mb-4">

<div class="request-card">

<!-- CUSTOMER NAME -->

<h5 class="fw-bold mb-2">
👤 <?php echo htmlspecialchars($row['name']); ?>
</h5>

<!-- CONTACT -->

<div class="customer-info text-muted">

<p>📧 <?php echo htmlspecialchars($row['email']); ?></p>

<p>📞 <?php echo htmlspecialchars($row['phone']); ?></p>

</div>

<hr>

<!-- TRIP DETAILS -->

<p><strong>🌍 Destination:</strong> <?php echo $row['destination']; ?></p>

<p><strong>🎯 Travel Style:</strong> <?php echo $row['style']; ?></p>

<p><strong>📅 Travel Date:</strong> <?php echo $row['travel_date']; ?></p>

<p><strong>⏳ Days:</strong> <?php echo $row['days']; ?></p>

<p><strong>👥 Travelers:</strong> <?php echo $row['travelers']; ?></p>

<p><strong>🏨 Hotel Type:</strong> <?php echo $row['hotel']; ?></p>

<!-- CUSTOMER REQUEST -->

<p class="mt-3 mb-1"><strong>📝 Customer Request:</strong></p>

<div class="request-box">
<?php echo nl2br(htmlspecialchars($row['experience'])); ?>
</div>

<hr>

<!-- STATUS -->

<span class="status pending">
⏳ Pending
</span>

<!-- ACTION BUTTONS -->

<div class="mt-3 action-buttons">

<a href="tel:<?php echo $row['phone']; ?>" 
class="btn btn-success btn-sm w-100">
📞 Call Customer
</a>

<a href="https://wa.me/<?php echo $row['phone']; ?>" 
class="btn btn-success btn-sm w-100">
💬 WhatsApp
</a>

<a href="delete-request.php?id=<?php echo $row['id']; ?>" 
class="btn btn-danger btn-sm w-100"
onclick="return confirm('Delete this request?')">
🗑 Delete
</a>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</html>