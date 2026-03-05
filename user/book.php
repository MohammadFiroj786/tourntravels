<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['id'])){
    header("Location: packages.php");
    exit();
}

$package_id = intval($_GET['id']);

/* Get Package Info */
$package = $conn->query("SELECT * FROM packages WHERE id='$package_id'")->fetch_assoc();

/* Calculate available seats */
$booked = $conn->query("SELECT SUM(persons) as total_booked 
                        FROM bookings 
                        WHERE package_id='$package_id' AND booking_status IN ('Pending','Confirmed')")->fetch_assoc();
$available_seats = $package['seats'] - ($booked['total_booked'] ?? 0);

$message = "";

/* ================= CONFIRM BOOKING ================= */
if(isset($_POST['confirm_booking'])){

    $travel_date = $_POST['travel_date'];
    $persons = intval($_POST['persons']);

    if($persons < 1){
        $message = "<div class='alert alert-danger'>Please enter a valid number of persons.</div>";
    }
    elseif($persons > $available_seats){
        $message = "<div class='alert alert-danger'>Only $available_seats seats are available for this package.</div>";
    } else {
        $total_price = $package['final_price'] * $persons;

        $stmt = $conn->prepare("INSERT INTO bookings (user_id, package_id, travel_date, persons, total_price, booking_status) VALUES (?,?,?,?,?,?)");
        $status = 'Pending';
        $stmt->bind_param("iisids", $user_id, $package_id, $travel_date, $persons, $total_price, $status);
        $stmt->execute();

        header("Location: my-bookings.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Book Package</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f4f6f9;}
.card{border-radius:15px;}
.btn-confirm{border-radius:50px; padding:10px 25px;}
</style>
</head>
<body>

<div class="container py-5">

<div class="card shadow p-4 mx-auto" style="max-width:600px;">
<h4>📦 Book Package: <?= $package['title'] ?></h4>

<p>
<strong>Price per person:</strong> ₹<?= $package['final_price'] ?> 
<?php if($package['discount'] > 0){ ?>
    <span class="badge bg-danger"><?= $package['discount'] ?>% OFF</span>
    <small class="text-muted text-decoration-line-through">₹<?= $package['price'] ?></small>
<?php } ?>
</p>

<p><strong>Available Seats:</strong> <?= $available_seats ?></p>

<?= $message ?>

<form method="POST">

<div class="mb-3">
<label>Travel Date</label>
<input type="date" name="travel_date" class="form-control" required>
</div>

<div class="mb-3">
<label>Number of Persons</label>
<input type="number" name="persons" id="persons" class="form-control" min="1" max="<?= $available_seats ?>" required>
</div>

<div class="mb-3">
<label>Total Price</label>
<input type="text" id="total_price" class="form-control" readonly value="0">
</div>

<button type="submit" name="confirm_booking" class="btn btn-primary btn-confirm w-100">
Confirm Booking
</button>

</form>
</div>

</div>

<script>
let price = <?= $package['final_price'] ?>;

document.getElementById("persons").addEventListener("input", function(){
    let persons = this.value;
    if(persons < 1) persons = 0;
    document.getElementById("total_price").value = price * persons;
});
</script>

</body>
</html>