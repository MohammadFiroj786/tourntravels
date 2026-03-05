<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= CANCEL BOOKING ================= */

if(isset($_GET['cancel_id'])){

    $booking_id = intval($_GET['cancel_id']);

    // Check if booking belongs to this user AND status is Pending
    $checkQuery = "
        SELECT id 
        FROM bookings
        WHERE id = ?
        AND user_id = ?
        AND booking_status = 'Pending'
    ";

    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        // Update status to Cancelled
        $updateQuery = "
            UPDATE bookings
            SET booking_status = 'Cancelled'
            WHERE id = ?
        ";

        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $booking_id);
        $updateStmt->execute();

        header("Location: my-bookings.php?msg=cancelled");
        exit();

    } else {

        header("Location: my-bookings.php?msg=not_allowed");
        exit();
    }
}
?>