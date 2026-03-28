<?php
include("../includes/session_check.php");
include("../includes/db.php");

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$success_msg = "";
$error_msg = "";

/* UPDATE PROFILE */
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($phone)) {
        $error_msg = "Name and Phone cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $phone, $user_id);

        if ($stmt->execute()) {
            $success_msg = "Profile updated successfully.";
            $user['name'] = $name;
            $user['phone'] = $phone;
        } else {
            $error_msg="Something went wrong!";
        }
    }
}

/* CHANGE PASSWORD */
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $user['password'])) {
        $error_msg = "Current password incorrect";
    } elseif ($new_password != $confirm_password) {
        $error_msg = "Passwords do not match";
    } elseif (strlen($new_password) < 6) {
        $error_msg = "Password must be minimum 6 characters";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $user_id);

        if ($stmt->execute()) {
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

<title>User Profile</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* BACKGROUND */
body{
background: linear-gradient(135deg,#dfe9f3,#ffffff);
font-family:'Poppins',sans-serif;
}

/* MAIN */
.main-content{
padding:30px;
}

/* PROFILE HEADER */
.profile-header{
background:linear-gradient(135deg,#667eea,#764ba2);
color:white;
border-radius:20px;
padding:30px;
display:flex;
align-items:center;
gap:20px;
box-shadow:0 15px 40px rgba(0,0,0,0.2);
}

/* AVATAR */
.profile-avatar{
width:90px;
height:90px;
border-radius:50%;
background:white;
color:#764ba2;
display:flex;
align-items:center;
justify-content:center;
font-size:35px;
font-weight:bold;
}

/* CARD */
.card{
border:none;
border-radius:18px;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
transition:0.3s;
}

.card:hover{
transform:translateY(-5px);
}

/* INPUT */
.form-control{
border-radius:10px;
}

/* BUTTONS */
.btn-primary{
background:linear-gradient(135deg,#ff7e5f,#ff5a3c);
border:none;
border-radius:10px;
}

.btn-primary:hover{
opacity:0.9;
}

.btn-warning{
border-radius:10px;
}

/* ALERT */
.alert{
border-radius:10px;
}

/* MOBILE */
@media(max-width:768px){
.profile-header{
flex-direction:column;
text-align:center;
}
}

</style>

</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="main-content">
<div class="container">

<!-- HEADER -->
<div class="profile-header mb-4">

<div class="profile-avatar">
<?php echo strtoupper(substr($user['name'],0,1)); ?>
</div>

<div>
<h4 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
<p class="mb-1"><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
<p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
</div>

</div>

<!-- ALERTS -->
<?php if($success_msg){ ?>
<div class="alert alert-success shadow-sm">
<i class="fa fa-check-circle"></i> <?php echo $success_msg; ?>
</div>
<?php } ?>

<?php if($error_msg){ ?>
<div class="alert alert-danger shadow-sm">
<i class="fa fa-exclamation-circle"></i> <?php echo $error_msg; ?>
</div>
<?php } ?>

<div class="row">

<!-- UPDATE PROFILE -->
<div class="col-md-6 mb-4">

<div class="card p-4">

<h5 class="mb-3"><i class="fa fa-user"></i> Update Profile</h5>

<form method="POST">

<div class="mb-3">
<label>Name</label>
<input type="text" name="name" class="form-control"
value="<?php echo htmlspecialchars($user['name']); ?>" required>
</div>

<div class="mb-3">
<label>Phone</label>
<input type="text" name="phone" class="form-control"
value="<?php echo htmlspecialchars($user['phone']); ?>" required>
</div>

<button type="submit" name="update_profile" class="btn btn-primary w-100">
Update Profile
</button>

</form>

</div>

</div>

<!-- CHANGE PASSWORD -->
<div class="col-md-6">

<div class="card p-4">

<h5 class="mb-3"><i class="fa fa-lock"></i> Change Password</h5>

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
<label>Confirm Password</label>
<input type="password" name="confirm_password" class="form-control" required>
</div>

<button type="submit" name="change_password" class="btn btn-warning w-100">
Change Password
</button>

</form>

</div>

</div>

</div>

<!-- TRUST LINE -->
<div class="text-center mt-4">
<p style="color:#777;">
🔒 Your data is सुरक्षित and protected with us
</p>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>