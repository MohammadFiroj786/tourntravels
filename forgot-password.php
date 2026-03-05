<?php
/* ================= ERROR REPORTING ================= */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ================= DB CONNECTION ================= */
require_once __DIR__ . "/includes/db.php";

/* ================= PHPMailer ================= */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/includes/PHPMailer/src/Exception.php";
require __DIR__ . "/includes/PHPMailer/src/PHPMailer.php";
require __DIR__ . "/includes/PHPMailer/src/SMTP.php";

$message = "";
$message_type = "";

/* ================= FORM SUBMIT ================= */
if (isset($_POST['send_link'])) {

    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Email is required!";
        $message_type = "error";
    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            /* ===== Generate Token ===== */
            $token  = bin2hex(random_bytes(32));
            $hashed = password_hash($token, PASSWORD_DEFAULT);
            $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

            $update = $conn->prepare(
                "UPDATE users SET reset_token=?, reset_expires=? WHERE email=?"
            );
            $update->bind_param("sss", $hashed, $expiry, $email);
            $update->execute();

            /* ===== Reset Link ===== */
            $reset_link = "http://localhost/tour/reset-password.php?email=$email&token=$token";

            /* ===== Send Email ===== */
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = "smtp.gmail.com";
                $mail->SMTPAuth   = true;
                $mail->Username   = "yourgmail@gmail.com";      // CHANGE
                $mail->Password   = "YOUR_APP_PASSWORD";        // CHANGE
                $mail->SMTPSecure = "tls";
                $mail->Port       = 587;

                $mail->setFrom("yourgmail@gmail.com", "Tour N Travels");
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Password Reset Request";
                $mail->Body = "
                    <h2>Password Reset</h2>
                    <p>Click the button below to reset your password:</p>
                    <a href='$reset_link'
                       style='background:#F96D00;color:#fff;padding:10px 15px;
                              text-decoration:none;border-radius:5px;'>
                       Reset Password
                    </a>
                    <p>This link is valid for 15 minutes.</p>
                ";

                $mail->send();

                $message = "Password reset link sent to your email.";
                $message_type = "success";

            } catch (Exception $e) {
                $message = "Email could not be sent!";
                $message_type = "error";
            }

        } else {
            $message = "Email not registered!";
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">

      <div class="card shadow">
        <div class="card-body">

          <h4 class="text-center mb-3">Forgot Password</h4>

          <form method="POST">
            <input type="email"
                   name="email"
                   class="form-control mb-3"
                   placeholder="Registered Email"
                   required>

            <button type="submit"
                    name="send_link"
                    class="btn btn-warning w-100">
              Send Reset Link
            </button>
          </form>

          <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none">Back to Login</a>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<?php if (!empty($message)) { ?>
<script>
Swal.fire({
  icon: "<?= $message_type ?>",
  title: "<?= ucfirst($message_type) ?>",
  text: "<?= $message ?>",
  confirmButtonColor: "#F96D00"
});
</script>
<?php } ?>

</body>
</html>