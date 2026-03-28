<?php
include("../includes/session_check.php");
include("../includes/db.php");
require_once("../env.php");

/* ================= VALIDATE BOOKING ================= */
$booking_id = intval($_GET['booking_id'] ?? 0);

if ($booking_id <= 0) {
    die("Invalid payment link");
}

/* ================= FETCH BOOKING ================= */
$stmt = $conn->prepare("
    SELECT b.id, b.total_price, b.payment_status, u.name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die("Booking not found");
}

if ($booking['payment_status'] === 'Paid') {
    die("Payment already completed");
}

$amount = $booking['total_price'];
$name   = $booking['name'];

/* ================= HANDLE PAYMENT SUBMIT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $method = trim($_POST['payment_method']);
    $txn    = trim($_POST['transaction_id']);

    if ($method === '' || $txn === '') {
        $error = "All fields are required";
    } else {

        /* Insert payment */
        $pay = $conn->prepare("
            INSERT INTO payments
            (booking_id, payment_method, transaction_id, amount, payment_status, created_at)
            VALUES (?, ?, ?, ?, 'Success', NOW())
        ");
        $pay->bind_param("issd", $booking_id, $method, $txn, $amount);
        $pay->execute();

        /* Update booking */
        $upd = $conn->prepare("
            UPDATE bookings
            SET payment_status = 'Paid'
            WHERE id = ?
        ");
        $upd->bind_param("i", $booking_id);
        $upd->execute();

        header("Location: payment_success.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Pay Now</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-6">

<div class="card shadow">
<div class="card-body text-center">

<h4>👋 Hello <?= htmlspecialchars($name) ?></h4>
<p class="mb-2">Amount to Pay</p>
<h2 class="text-success">₹<?= number_format($amount) ?></h2>

<hr>

<h5>📱 Pay via UPI</h5>

<p><b>UPI ID:</b> <?= UPI_ID ?></p>

<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi://pay?pa=<?= urlencode(UPI_ID) ?>&pn=<?= urlencode(UPI_NAME) ?>&am=<?= $amount ?>&cu=INR"
     alt="UPI QR">

<hr>

<h5>🧾 Submit Payment Details</h5>

<?php if(isset($error)){ ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php } ?>

<form method="POST">

<select name="payment_method" class="form-control mb-2" required>
<option value="">Select Payment Method</option>
<option>Google Pay</option>
<option>PhonePe</option>
<option>Paytm</option>
<option>UPI</option>
</select>

<input type="text" name="transaction_id" class="form-control mb-3"
placeholder="Enter UTR / Transaction ID" required>

<button class="btn btn-success w-100">
✅ Confirm Payment
</button>

</form>

</div>
</div>

</div>
</div>
</div>

</body>
</html>