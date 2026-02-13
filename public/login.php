<?php
session_start();
require_once __DIR__ . "/../app/config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"] ?? "");
  $password = $_POST["password"] ?? "";

  if ($username === "" || $password === "") {
    $message = "All fields are required";
  } else {
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();

      if (password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];

        header("Location: index.php");
        exit;
      } else {
        $message = "Invalid password";
      }
    } else {
      $message = "User not found";
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StudyGen Login</title>
 <link rel="stylesheet" href="assets/css/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>

<div class="login-container">
  <h2>Login to StudyGen</h2>

  <form method="POST" action="login.php">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>

  <?php if ($message): ?>
    <p id="loginMsg" style="color:red; margin-top:10px;">
      <?= htmlspecialchars($message) ?>
    </p>
  <?php endif; ?>

  <p class="register-text">
    Don't have an account?
    <a href="register.php">Register</a>
  </p>

  <div class="social-login">
    <p>Or login with</p>
    <div class="social-buttons">
      <button class="google-btn" disabled>
        <i class="fab fa-google"></i> Google
      </button>
      <button class="facebook-btn" disabled>
        <i class="fab fa-facebook-f"></i> Facebook
      </button>
    </div>
  </div>

  <div class="guest-login">
    <p>Or continue as guest</p>
    <a href="index.php">
      <button>Guest Login</button>
    </a>
  </div>
</div>

</body>
</html>
