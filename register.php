<?php
session_start();
include("includes/db.php"); // Make sure $conn is set here

$error = "";
$name = $email = $phone = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Password match check
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert user securely
            $stmt_insert = $conn->prepare("INSERT INTO users (name,email,phone,password,role) VALUES (?,?,?,?, 'user')");
            $stmt_insert->bind_param("ssss", $name, $email, $phone, $hashed_password);

            if ($stmt_insert->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Hidden Hills Collective - Register</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Arizonia&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<link rel="stylesheet" href="css/animate.css">
<link rel="stylesheet" href="css/owl.carousel.min.css">
<link rel="stylesheet" href="css/owl.theme.default.min.css">
<link rel="stylesheet" href="css/magnific-popup.css">
<link rel="stylesheet" href="css/bootstrap-datepicker.css">
<link rel="stylesheet" href="css/jquery.timepicker.css">
<link rel="stylesheet" href="css/flaticon.css">
<link rel="stylesheet" href="css/style.css">

<style>
body{
  background: url('images/bg_5.jpg') no-repeat center center fixed;
  background-size: cover;
}

.card{
  background-color: rgba(255,255,255,0.95);
  border-radius:20px;
}

.password-toggle{
  position:absolute;
  right:15px;
  top:50%;
  transform:translateY(-50%);
  cursor:pointer;
  color:#F96D00;
}
</style>

</head>
<body>
<?php include 'navbar.php'; ?>

<div class="d-flex justify-content-center align-items-center min-vh-100 mt-5">
  <div class="card p-4 p-md-5 shadow-lg" style="width: 100%; max-width: 450px;">
    <!-- Title -->
    <h1 class="text-center mb-3" style="font-family: 'Arizonia', cursive; color:#F96D00;">Tour N Travels</h1>
    <p class="text-center mb-4 fw-semibold">Create your account to start your journey with us 🌍</p>

    <!-- Display error -->
    <?php if($error != ""){ ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } ?>

    <!-- Registration Form -->
    <form method="POST" action="">
      <div class="mb-3">
        <input type="text" class="form-control form-control-lg rounded-pill shadow-sm" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($name); ?>" required>
      </div>
      <div class="mb-3">
        <input type="email" class="form-control form-control-lg rounded-pill shadow-sm" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="mb-3">
        <input type="tel" class="form-control form-control-lg rounded-pill shadow-sm" name="phone" placeholder="Phone Number" value="<?php echo htmlspecialchars($phone); ?>" required>
      </div>
      <div class="mb-3 position-relative">
        <input type="password" class="form-control form-control-lg rounded-pill shadow-sm" id="password" name="password" placeholder="Password" required>
        <span class="password-toggle" onclick="togglePassword('password')"><i class="fa fa-eye"></i></span>
      </div>
      <div class="mb-4 position-relative">
        <input type="password" class="form-control form-control-lg rounded-pill shadow-sm" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
        <span class="password-toggle" onclick="togglePassword('confirm_password')"><i class="fa fa-eye"></i></span>
      </div>

      <div class="text-center mb-3">
        <button type="submit" class="btn btn-warning btn-lg rounded-pill fw-bold px-5">
          Register
        </button>
      </div>

      <p class="text-center mb-0">Already have an account? <a href="login.php" class="text-warning fw-bold text-decoration-none">Login</a></p>
    </form>
  </div>
</div>
<?php include 'footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/jquery-migrate-3.0.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.easing.1.3.js"></script>
<script src="js/jquery.waypoints.min.js"></script>
<script src="js/jquery.stellar.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/jquery.animateNumber.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/jquery.timepicker.min.js"></script>
<script src="js/scrollax.min.js"></script>
<script src="js/main.js"></script>

<!-- Password toggle -->
<script>
  function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  }
</script>

</body>
</html>