<?php
session_start();
require_once __DIR__ . "/../app/config/db.php";

/* ===== AUTH ===== */
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$id = $_SESSION['user_id'];

/* ===== FETCH CURRENT USER ===== */
$stmt = $conn->prepare(
  "SELECT bio, profile_pic FROM users WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* ===== UPDATE PROFILE ===== */
if ($_SERVER['REQUEST_METHOD'] === "POST") {

  $bio = trim($_POST['bio']);

  /* Upload photo if exists */
  if (!empty($_FILES['photo']['name'])) {

    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg','jpeg','png','webp'];

    if (in_array(strtolower($ext), $allowed)) {

      $filename = time() . "_" . $id . "." . $ext;
      $uploadDir = __DIR__ . "/../app/uploads/profile/";

      /* ensure directory exists */
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      $uploadPath = $uploadDir . $filename;

      if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {

        $stmt = $conn->prepare(
          "UPDATE users SET bio = ?, profile_pic = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $bio, $filename, $id);
        $stmt->execute();

      }

    }

  } else {

    $stmt = $conn->prepare(
      "UPDATE users SET bio = ? WHERE id = ?"
    );
    $stmt->bind_param("si", $bio, $id);
    $stmt->execute();

  }

  header("Location: profile.php");
  exit;
}

/* ===== PROFILE PIC ===== */
$profilePic = $user['profile_pic']
  ? "../app/uploads/profile/" . $user['profile_pic']
  : "../app/uploads/profile/default.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile • StudyGen</title>

<link rel="stylesheet" href="assets/css/index.css">
<link rel="stylesheet" href="assets/css/profile.css">
<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<header class="header">
  <div class="logo-title" onclick="location.href='index.php'">
    <i class="fa-solid fa-leaf" style="color:#2e7d32;"></i>
    <span>StudyGen</span>
  </div>

  <div class="right-section">
    <button id="toggleTheme"><i class="fa-solid fa-moon"></i></button>
    <button onclick="location.href='profile.php'">
      <i class="fa-solid fa-arrow-left"></i>
    </button>
  </div>
</header>

<main class="edit-profile-page">
  <section class="edit-profile-card">

    <div class="edit-avatar">
      <img src="<?= $profilePic ?>">
    </div>

    <form method="post" enctype="multipart/form-data">

      <label>Profile Photo</label>
      <input type="file" name="photo" accept="image/*">

      <label>Bio</label>
      <textarea name="bio" placeholder="Write something about you..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

      <button type="submit" class="save-btn">
        Save Changes
      </button>

    </form>

  </section>
</main>

<footer class="footer-nav">
  <div class="nav-item" onclick="location.href='index.php'">
    <i class="fa-solid fa-house"></i><span>Home</span>
  </div>
  <div class="nav-item active">
    <i class="fa-solid fa-user"></i><span>Profile</span>
  </div>
</footer>

<script src="assets/js/index.js"></script>
</body>
</html>
