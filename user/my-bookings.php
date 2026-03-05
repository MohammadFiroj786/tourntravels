<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

/* ================= CANCEL BOOKING LOGIC ================= */
if(isset($_GET['cancel_id'])){

    $booking_id = intval($_GET['cancel_id']);

    // Check booking belongs to user & status is Pending
    $check = $conn->query("
        SELECT * FROM bookings 
        WHERE id = $booking_id 
        AND user_id = $user_id 
        AND booking_status = 'Pending'
    ");

    if($check->num_rows > 0){

        $conn->query("
            UPDATE bookings 
            SET booking_status = 'Cancelled' 
            WHERE id = $booking_id
        ");

        $message = "<div class='alert alert-success'>
                        Booking cancelled successfully.
                    </div>";

    } else {
        $message = "<div class='alert alert-danger'>
                        Cancellation not allowed.
                    </div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f8f9fa;">

<div class="container mt-5 mb-5">

    <div class="card shadow-lg">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">My Bookings</h4>
        </div>

        <div class="card-body">

            <?= $message ?>

            <?php
            $result = $conn->query("
                SELECT bookings.*, packages.title 
                FROM bookings 
                JOIN packages ON bookings.package_id = packages.id
                WHERE bookings.user_id = $user_id
                ORDER BY bookings.id DESC
            ");
            ?>

            <?php if($result->num_rows > 0){ ?>

            <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th>Package</th>
                        <th>Travel Date</th>
                        <th>Persons</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                <?php while($row = $result->fetch_assoc()){ ?>

                <tr>
                    <td><?= $row['title']; ?></td>
                    <td><?= $row['travel_date']; ?></td>
                    <td><?= $row['persons']; ?></td>
                    <td>₹<?= $row['total_price']; ?></td>

                    <td>
                        <?php
                        if($row['booking_status'] == "Pending"){
                            echo "<span class='badge bg-warning text-dark'>Pending</span>";
                        } 
                        elseif($row['booking_status'] == "Confirmed"){
                            echo "<span class='badge bg-primary'>Confirmed</span>";
                        } 
                        elseif($row['booking_status'] == "Cancelled"){
                            echo "<span class='badge bg-danger'>Cancelled</span>";
                        } 
                        elseif($row['booking_status'] == "Completed"){
                            echo "<span class='badge bg-success'>Completed</span>";
                        }
                        ?>
                    </td>

                    <td>
                        <?php if($row['booking_status'] == "Pending"){ ?>
                            <a href="my-bookings.php?cancel_id=<?= $row['id']; ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('As per cancellation policy, this action cannot be undone. Cancel booking?');">
                               Cancel
                            </a>
                        <?php } else { ?>
                            <span class="text-muted">Not Allowed</span>
                        <?php } ?>
                    </td>

                </tr>

                <?php } ?>

                </tbody>
            </table>
            </div>

            <?php } else { ?>
                <div class="alert alert-info">
                    You have not made any bookings yet.
                </div>
            <?php } ?>

        </div>
    </div>


    <!-- TERMS & CONDITIONS SECTION -->
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Cancellation Policy & Terms</h5>
        </div>
        <div class="card-body">

            <ul>
                <li>Bookings can be cancelled only if status is <strong>Pending</strong>.</li>
                <li>Confirmed bookings cannot be cancelled online.</li>
                <li>No cancellation allowed after travel date.</li>
                <li>Refund processing (if applicable) may take 5-7 working days.</li>
                <li>The company reserves the right to cancel due to unavoidable circumstances.</li>
                <li>All disputes are subject to local jurisdiction.</li>
            </ul>

        </div>
    </div>

    <div class="text-center mt-4">
        <a href="packages.php" class="btn btn-secondary">
            Browse More Packages
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>