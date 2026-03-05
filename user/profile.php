<?php
session_start();
include("../includes/db.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$success_msg = "";
$error_msg = "";

/* ================= UPDATE PROFILE ================= */
if(isset($_POST['update_profile'])){
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    // Basic validation
    if(empty($name) || empty($phone)){
        $error_msg = "Name and Phone cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $phone, $user_id);
        if($stmt->execute()){
            $success_msg = "Profile updated successfully.";
            $user['name'] = $name;
            $user['phone'] = $phone;
        } else {
            $error_msg = "Something went wrong!";
        }
    }
}

/* ================= CHANGE PASSWORD ================= */
if(isset($_POST['change_password'])){
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if(!password_verify($current_password, $user['password'])){
        $error_msg = "Current password is incorrect.";
    } elseif($new_password != $confirm_password){
        $error_msg = "New password and confirm password do not match.";
    } elseif(strlen($new_password) < 6){
        $error_msg = "Password must be at least 6 characters.";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        if($stmt->execute()){
            $success_msg = "Password changed successfully.";
        } else {
            $error_msg = "Something went wrong!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f7fc; font-family:Poppins; }
        .card { border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.1); }
        .container { max-width: 700px; margin-top:50px; }
    </style>
</head>
<body>

<div class="container">

<h2 class="mb-4 text-center">👤 Edit Profile</h2>

<?php if($success_msg){ ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
<?php } ?>
<?php if($error_msg){ ?>
    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
<?php } ?>

<!-- Profile Form -->
<div class="card p-4 mb-4">
    <h5>Update Profile</h5>
    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>
        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<!-- Change Password Form -->
<div class="card p-4">
    <h5>Change Password</h5>
    <form method="POST">
        <div class="mb-3">
            <label>Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
    </form>
</div>

</div>

</body>
</html>