<?php
include("includes/db.php");

if (!isset($_GET['token'], $_GET['email'])) {
    die("Invalid request");
}

$email = $_GET['email'];
$token = $_GET['token'];

$stmt = $conn->prepare(
    "SELECT reset_token, reset_expires FROM users WHERE email=?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || strtotime($user['reset_expires']) < time()) {
    die("Link expired or invalid");
}

if (!password_verify($token, $user['reset_token'])) {
    die("Invalid token");
}

if (isset($_POST['reset'])) {

    if ($_POST['password'] !== $_POST['confirm']) {
        $error = "Passwords do not match!";
    } else {

        $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $update = $conn->prepare(
            "UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE email=?"
        );
        $update->bind_param("ss", $hashed, $email);
        $update->execute();

        echo "Password updated successfully!";
        exit;
    }
}
?>