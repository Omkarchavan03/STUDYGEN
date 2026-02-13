<?php
session_start();
require_once __DIR__ . "/../app/config/db.php";

/* Check video id */
$videoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($videoId <= 0) {
    die("Invalid video");
}

/* Fetch video */
$stmt = $conn->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->bind_param("i", $videoId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Video not found");
}

$video = $result->fetch_assoc();
$stmt->close();

/* Increment views (simple & safe) */
$conn->query("UPDATE videos SET views = views + 1 WHERE id = $videoId");

/* Guest / User info */
$isGuest  = !isset($_SESSION["user_id"]);
$username = $_SESSION["username"] ?? "Guest";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($video['title']) ?> | StudyGen</title>

  <link rel="stylesheet" href="assets/css/watch.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="logo-title" onclick="location.href='index.php'">
    <i class="fa-solid fa-leaf" style="color:#2e7d32;"></i>
    <span class="hub-text">StudyGen</span>
  </div>

  <div class="right-section">
    <?php if ($isGuest): ?>
      <a href="login.php">Login</a>
    <?php else: ?>
      <span><?= htmlspecialchars($username) ?></span>
      <a href="logout.php">Logout</a>
    <?php endif; ?>
  </div>
</header>

<!-- MAIN -->
<main class="watch-container">

  <!-- VIDEO PLAYER -->
  <section class="video-player-section">
    <video controls autoplay>
      <source src="uploads/videos/<?= htmlspecialchars($video['video_file']) ?>" type="video/mp4">
      Your browser does not support the video tag.
    </video>
  </section>

  <!-- VIDEO DETAILS -->
  <section class="video-details">
    <h1 class="video-title">
      <?= htmlspecialchars($video['title']) ?>
    </h1>

    <div class="video-meta">
      <span>StudyGen</span> •
      <span><?= (int)$video['views'] + 1 ?> views</span>
    </div>

    <p class="video-description">
      <?= nl2br(htmlspecialchars($video['description'])) ?>
    </p>

    <?php if ($isGuest): ?>
      <div class="guest-warning">
        <p>Login to bookmark, comment, or save progress.</p>
        <a href="login.php" class="login-btn">Login</a>
      </div>
    <?php endif; ?>
  </section>

</main>
<script src="assets/js/index.js"></script>

</body>
</html>
