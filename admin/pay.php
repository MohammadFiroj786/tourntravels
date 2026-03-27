<?php
session_start();
include("../includes/db.php");

/* ================= GET BOOKING ID ================= */
$booking_id = $_GET['booking_id'] ?? '';

if(empty($booking_id)){
    die("Invalid payment link");
}

/* ================= FETCH BOOKING ================= */
$stmt = $conn->prepare("
    SELECT b.id, b.total_price, b.payment_status, u.name, u.email
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if(!$data){
    die("Booking not found");
}

if($data['payment_status'] === 'Paid'){
    die("Payment already completed");
}

$amount = $data['total_price'];
$name   = $data['name'];

/* ================= PAYMENT DETAILS ================= */
$upi_id = "yourupi@upi";          // 🔴 CHANGE THIS
$payee  = "Your Company Name";
$method = "UPI";

/* ================= QR CODE ================= */
$upi_link = "upi://pay?pa=$upi_id&pn=$payee&am=$amount&cu=INR";
$qr = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($upi_link);
?>

<!DOCTYPE html>
<html>
<head>
<title>Complete Payment</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:Arial;background:#f4f6f8}
.card{
max-width:420px;margin:40px auto;background:#fff;
padding:25px;border-radius:10px;
box-shadow:0 0 15px rgba(0,0,0,0.1);
text-align:center
}
img{margin:15px 0}
input,button{
width:100%;padding:12px;margin-top:10px;
border-radius:5px;border:1px solid #ccc
}
button{
background:#28a745;color:#fff;
border:none;font-size:16px;cursor:pointer
}
button:hover{background:#218838}
</style>
</head>

<body>

<div class="card">
<h2>Hello <?= htmlspecialchars($name) ?> 👋</h2>
<p><b>Pay Amount:</b> ₹<?= $amount ?></p>

<img src="<?= $qr ?>" alt="UPI QR">

<p><b>UPI ID:</b> <?= $upi_id ?></p>

<form method="POST">
<input type="text" name="txn_id" placeholder="Enter UPI Transaction ID" required>
<button name="pay">I Have Paid</button>
</form>
</div>

</body>
</html>

<?php
/* ================= PAYMENT SUBMIT ================= */
if(isset($_POST['pay'])){
    $txn = trim($_POST['txn_id']);

    if(strlen($txn) < 6){
        echo "<script>alert('Invalid Transaction ID');</script>";
        exit();
    }

    /* Insert payment */
    $insert = $conn->prepare("
        INSERT INTO payments
        (booking_id, payment_method, transaction_id, amount, payment_status)
        VALUES (?, ?, ?, ?, 'Paid')
    ");
    $insert->bind_param("issd", $booking_id, $method, $txn, $amount);
    $insert->execute();

    /* Update booking */
    $update = $conn->prepare("
        UPDATE bookings SET payment_status='Paid'
        WHERE id=?
    ");
    $update->bind_param("i", $booking_id);
    $update->execute();

    echo "<script>
        alert('Payment Successful!');
        window.location='thank-you.php';
    </script>";
}
?>