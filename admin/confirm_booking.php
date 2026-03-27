<?php
session_start();
include("../includes/db.php");
require_once("../env.php");

/* PHPMailer */
require_once("../includes/PHPMailer/src/PHPMailer.php");
require_once("../includes/PHPMailer/src/SMTP.php");
require_once("../includes/PHPMailer/src/Exception.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ================= SECURITY ================= */
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

/* ================= REQUEST ================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: custom-package-requests.php");
    exit();
}

$request_id = $_POST['request_id'] ?? '';
$user_id    = $_POST['user_id'] ?? '';
$price      = $_POST['price'] ?? '';

if (!$request_id || !$user_id || !$price) {
    die("Invalid request");
}

/* ================= FETCH REQUEST + USER ================= */
$get = $conn->prepare("
    SELECT 
        c.travel_date, c.travelers,
        u.name, u.email, u.phone
    FROM custom_package_requests c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
");
$get->bind_param("i", $request_id);
$get->execute();
$data = $get->get_result()->fetch_assoc();

if (!$data) {
    die("Request not found");
}

$name        = $data['name'];
$email       = $data['email'];
$phone       = preg_replace('/\D/', '', $data['phone']);
$travel_date = $data['travel_date'];
$persons     = $data['travelers'];

/* ================= UPDATE CUSTOM REQUEST ================= */
$update = $conn->prepare("
    UPDATE custom_package_requests
    SET price = ?, status = 'Accepted'
    WHERE id = ?
");
$update->bind_param("di", $price, $request_id);
$update->execute();

/* ================= INSERT BOOKING ================= */
$insert = $conn->prepare("
    INSERT INTO bookings
    (user_id, package_id, travel_date, persons, total_price, booking_status, created_at, payment_status)
    VALUES (?, NULL, ?, ?, ?, 'Confirmed', NOW(), 'Pending')
");
$insert->bind_param("isid", $user_id, $travel_date, $persons, $price);
$insert->execute();

$booking_id = $conn->insert_id;

/* ================= PAYMENT LINK ================= */
$payment_link = APP_URL . "/pay.php?booking_id=" . $booking_id;

/* ================= SEND EMAIL (SAME AS CONTACT LOGIC) ================= */
$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = MAIL_PORT;

    $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
    $mail->addReplyTo(MAIL_USERNAME, MAIL_FROM_NAME);
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "Payment Link - Tour Booking Confirmed";

    $mail->Body = "
        <div style='font-family:Arial;background:#f4f6f9;padding:20px'>
        <div style='max-width:600px;margin:auto;background:#fff;padding:25px;border-radius:8px'>
        <h2>Tour Booking Confirmed ✅</h2>
        <p>Hello <b>$name</b>,</p>
        <p>Your tour booking has been confirmed.</p>
        <p><b>Amount:</b> ₹$price</p>
        <p>
            <a href='$payment_link'
               style='background:#28a745;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px'>
               Pay Now
            </a>
        </p>
        <p>Thank you for choosing us.</p>
        </div>
        </div>
    ";

    $mail->AltBody = "Hello $name, Pay here: $payment_link";

    $mail->send();

} catch (Exception $e) {
    // Email failure should NOT break booking
}

/* ================= WHATSAPP ================= */
if (strlen($phone) === 10) {
    $phone = "91" . $phone;
}

$wa_msg = urlencode(
    "Hello $name 👋\n\n" .
    "Your booking is confirmed ✅\n" .
    "Amount: ₹$price\n\n" .
    "Pay here:\n$payment_link"
);

/* ================= REDIRECT ================= */
header("Location: https://wa.me/$phone?text=$wa_msg");
exit();