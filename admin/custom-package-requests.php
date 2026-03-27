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
    <th>Service Type</th>
    <th>Pickup</th>
    <th>Destination</th>
    <th>Sightseeing</th>
    <th>Travelers</th>
    <th>Days</th>
    <th>Hotel</th>
    <th>Car</th>
    <th>Date</th>
    <th>Estimate</th>
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
        'full' => '🚗 Full Trip',
        'stay' => '🏨 Stay + Sightseeing',
        'sightseeing' => '🏔️ Sightseeing Only'
    ];
    echo isset($service_labels[$row['service_type']]) ? $service_labels[$row['service_type']] : htmlspecialchars($row['service_type']);
    ?>
</td>
<td><?= !empty($row['pickup_location']) ? htmlspecialchars($row['pickup_location']) : 'N/A' ?></td>
<td><?= !empty($row['destinations']) ? htmlspecialchars($row['destinations']) : 'N/A' ?></td>
<td>
    <?php
    $places = json_decode($row['sightseeing_places'], true);
    if (is_array($places)) {
        echo htmlspecialchars(implode(', ', $places));
    } else {
        echo !empty($row['sightseeing_places']) ? htmlspecialchars($row['sightseeing_places']) : 'N/A';
    }
    ?>
</td>
<td><?= htmlspecialchars($row['travelers']) ?></td>
<td><?= htmlspecialchars($row['days']) ?></td>
<td><?= !empty($row['hotel_type']) ? htmlspecialchars($row['hotel_type']) : 'N/A' ?></td>
<td><?= htmlspecialchars($row['car_type']) ?></td>
<td><?= htmlspecialchars($row['travel_date']) ?></td>
<td><?= isset($row['price']) ? '₹' . number_format($row['price'], 2) : 'N/A' ?></td>
<td>
<?php if($row['status']=="Accepted"){ ?>
    <span class="badge bg-success">Confirmed</span>
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
<div class="modal fade" id="viewModal<?= $row['id']; ?>" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">📋 Custom Package Request Details</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    
    <!-- CUSTOMER INFO -->
    <h6 class="fw-bold text-primary mb-3">👤 Customer Information</h6>
    <div class="row mb-3">
        <div class="col-md-6">
            <p><b>Name:</b> <?= htmlspecialchars($row['name']) ?></p>
            <p><b>Phone:</b> <?= htmlspecialchars($row['phone']) ?></p>
        </div>
        <div class="col-md-6">
            <p><b>Email:</b> <?= htmlspecialchars($row['email']) ?></p>
            <p><b>Request Date:</b> <?= isset($row['created_at']) ? date('d-M-Y', strtotime($row['created_at'])) : 'N/A' ?></p>
        </div>
    </div>
    
    <hr>
    
    <!-- PACKAGE DETAILS -->
    <h6 class="fw-bold text-primary mb-3">📦 Package Details</h6>
    <div class="row mb-3">
        <div class="col-md-6">
            <p><b>Service Type:</b> 
                <?php 
                $service_labels = [
                    'full' => '🚗 Full Trip (Pickup + Stay + Sightseeing + Drop)',
                    'stay' => '🏨 Stay + Sightseeing',
                    'sightseeing' => '🏔️ Sightseeing Only'
                ];
                echo isset($service_labels[$row['service_type']]) ? $service_labels[$row['service_type']] : $row['service_type'];
                ?>
            </p>
            <p><b>Travel Date:</b> <?= $row['travel_date'] ?></p>
            <p><b>Duration:</b> <?= $row['days'] ?> day(s)</p>
        </div>
        <div class="col-md-6">
            <p><b>Number of Travelers:</b> <?= $row['travelers'] ?></p>
            <p><b>Car Required:</b> <span class="badge bg-info"><?= htmlspecialchars($row['car_type']) ?></span></p>
            <p><b>Hotel Type:</b> <?= !empty($row['hotel_type']) ? htmlspecialchars($row['hotel_type']) : 'N/A' ?></p>
        </div>
    </div>
    
    <!-- DESTINATION & PLACES -->
    <?php if(!empty($row['destinations'])): ?>
    <div class="mb-3">
        <p><b>📍 Destinations:</b></p>
        <div class="alert alert-light">
            <?= nl2br(htmlspecialchars($row['destinations'])) ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if(!empty($row['pickup_location'])): ?>
    <div class="mb-3">
        <p><b>🚗 Pickup Location:</b></p>
        <div class="alert alert-light">
            <?= htmlspecialchars($row['pickup_location']) ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if(!empty($row['sightseeing_places'])): ?>
    <div class="mb-3">
        <p><b>✨ Selected Sightseeing Places:</b></p>
        <div class="alert alert-light">
            <?php
            $places = json_decode($row['sightseeing_places'], true);
            if (is_array($places)) {
                echo '<ul class="mb-0">';
                foreach ($places as $place) {
                    echo '<li>' . htmlspecialchars($place) . '</li>';
                }
                echo '</ul>';
            } else {
                echo nl2br(htmlspecialchars($row['sightseeing_places']));
            }
            ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- CUSTOMER NOTES -->
    <?php if(!empty($row['user_notes'])): ?>
    <div class="mb-3">
        <p><b>📝 Customer Notes:</b></p>
        <div class="alert alert-light">
            <?= nl2br(htmlspecialchars($row['user_notes'])) ?>
        </div>
    </div>
    <?php endif; ?>
    
    <hr>
    
    <!-- PRICING & CONFIRMATION -->
    <?php if($row['status']!="Accepted"){ ?>
    
    <h6 class="fw-bold text-primary mb-3">💰 Confirm Booking</h6>
    
    <form class="confirmForm" action="confirm_booking.php" method="POST">
        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
        <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
        <input type="hidden" name="travel_date" value="<?= $row['travel_date'] ?>">
        <input type="hidden" name="persons" value="<?= $row['travelers'] ?>">
        
        <label class="fw-bold mb-2">Enter Final Package Price (₹)</label>
        <input type="number" name="price" class="form-control mb-3" placeholder="Enter price per person or total" required>
        
        <button class="btn btn-primary w-100">✅ Confirm Booking</button>
    </form>
    
    <?php } else { ?>
    <div class="alert alert-success text-center fw-bold">
        ✅ Booking Already Confirmed
    </div>
    <?php } ?>
    
    <!-- CONTACT BUTTONS -->
    <div class="mt-3">
        <a href="tel:<?= $row['phone'] ?>" class="btn btn-success w-100 mb-2">📞 Call Customer</a>
        <a href="https://wa.me/<?= $row['phone'] ?>" target="_blank" class="btn btn-success w-100">💬 WhatsApp</a>
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