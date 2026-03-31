<?php
ob_start(); // ✅ VERY IMPORTANT: prevents header issues

include("../includes/session_check.php");
include("../includes/db.php");
require_once("../env.php");

/* ================= PHPMailer ================= */
require_once("../includes/PHPMailer/src/PHPMailer.php");
require_once("../includes/PHPMailer/src/SMTP.php");
require_once("../includes/PHPMailer/src/Exception.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ================= SECURITY ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ================= REQUEST CHECK ================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: custom-package-requests.php");
    exit();
}

$request_id = intval($_POST['request_id'] ?? 0);
$user_id    = intval($_POST['user_id'] ?? 0);
$price      = floatval($_POST['price'] ?? 0);

if ($request_id <= 0 || $user_id <= 0 || $price <= 0) {
    die("Invalid request data");
}

/* ================= FETCH REQUEST + USER ================= */
$get = $conn->prepare("
    SELECT 
        c.travel_date,
        c.travelers,
        c.status,
        u.name,
        u.email,
        u.phone
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

/* Prevent double confirmation */
if ($data['status'] === 'Accepted') {
    header("Location: custom-package-requests.php?already_confirmed=1");
    exit();
}

/* ================= DATA ================= */
$name        = $data['name'];
$email       = $data['email'];
$phone       = preg_replace('/\D/', '', $data['phone']);
$travel_date = $data['travel_date'];
$persons     = $data['travelers'];

/* ================= UPDATE REQUEST ================= */
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
    (user_id, package_id, travel_date, persons, total_price, booking_status, payment_status, created_at)
    VALUES (?, NULL, ?, ?, ?, 'Confirmed', 'Pending', NOW())
");
$insert->bind_param("isid", $user_id, $travel_date, $persons, $price);
$insert->execute();

$booking_id = $conn->insert_id;

/* ================= PAYMENT LINK ================= */
$baseUrl = rtrim(APP_URL, '/');
$payment_link = $baseUrl . "/admin/pay.php?booking_id=" . $booking_id;

/* ================= SEND EMAIL (NON-BLOCKING) ================= */
try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "Booking Confirmed - Payment Link";

    $mail->Body = "
        <h2>Booking Confirmed ✅</h2>
        <p>Hello <b>$name</b>,</p>
        <p>Your booking is confirmed.</p>
        <p><b>Amount:</b> ₹$price</p>
        <p>
            <a href='$payment_link'
               style='background:#28a745;color:#fff;padding:12px 20px;text-decoration:none;border-radius:5px'>
               Pay Now
            </a>
        </p>
        <p>Payment link: $payment_link</p>
    ";

    $mail->AltBody = "Hello $name\n\nAmount: ₹$price\nPay here:\n$payment_link";

    $mail->send();

} catch (Exception $e) {
    // ❌ Do nothing – email failure must NOT stop WhatsApp
}

/* ================= WHATSAPP ================= */
if (strlen($phone) === 10) {
    $phone = "91" . $phone; // India country code
}

$wa_text = rawurlencode(
    "Hello $name\n\n" .
    "Your booking is confirmed ✅\n" .
    "Amount: ₹$price\n\n" .
    "Pay here:\n$payment_link"
);

/* ================= FINAL REDIRECT ================= */
if (headers_sent($file, $line)) {
    die("Headers already sent in $file on line $line");
}

header("Location: https://wa.me/$phone?text=$wa_text");
exit();