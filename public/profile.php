<?php
require_once __DIR__ . "/../app/config/db.php";
session_start();

/* ===== AUTH ===== */
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

/* ===== PROFILE ID ===== */
$profileId = isset($_GET['id']) && is_numeric($_GET['id'])
  ? (int)$_GET['id']
  : (int)$_SESSION['user_id'];

/* ===== USER INFO ===== */
$stmt = $conn->prepare(
  "SELECT id, username, bio, profile_pic
   FROM users WHERE id = ?"
);
$stmt->bind_param("i", $profileId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
  die("User not found");
}

/* ===== PROFILE PIC ===== */
$profilePic = $user['profile_pic']
  ? "../app/uploads/profile/" . $user['profile_pic']
  : "../app/uploads/profile/default.png";

/* ===== FOLLOW COUNTS ===== */
$followers = $conn->query(
  "SELECT COUNT(*) FROM followers WHERE following_id = $profileId"
)->fetch_row()[0];

$following = $conn->query(
  "SELECT COUNT(*) FROM followers WHERE follower_id = $profileId"
)->fetch_row()[0];

/* ===== FOLLOW STATUS ===== */
$isFollowing = false;
if ($profileId !== $_SESSION['user_id']) {
  $st = $conn->prepare(
    "SELECT id FROM followers
     WHERE follower_id = ? AND following_id = ?"
  );
  $st->bind_param("ii", $_SESSION['user_id'], $profileId);
  $st->execute();
  $isFollowing = $st->get_result()->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($user['username']) ?> • StudyGen</title>

<link rel="stylesheet" href="assets/css/index.css">
<link rel="stylesheet" href="assets/css/profile.css">
<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<!-- ================= HEADER ================= -->
<header class="header">
  <div class="logo-title" onclick="location.href='index.php'">
    <i class="fa-solid fa-leaf" style="color:#2e7d32;"></i>
    <span>StudyGen</span>
  </div>

  <div class="right-section">
    <button id="toggleTheme"><i class="fa-solid fa-moon"></i></button>
    <i class="fa-solid fa-bell"></i>
    <button onclick="location.href='logout.php'">
      <i class="fa-solid fa-right-from-bracket"></i>
    </button>
  </div>
</header>

<!-- ================= MAIN ================= -->
<main class="profile-page">

<!-- PROFILE CARD -->
<section class="profile-card horizontal">

  <!-- PROFILE IMAGE -->
  <div class="profile-image-wrapper">
    <img src="<?= $profilePic ?>" class="profile-avatar">

    <?php if ($profileId === $_SESSION['user_id']): ?>
      <button class="edit-avatar-btn"
        onclick="location.href='edit-profile.php'"
        title="Edit Profile">
        <i class="fa-solid fa-pencil"></i>
      </button>
    <?php endif; ?>
  </div>

  <!-- PROFILE INFO -->
  <div class="profile-info">

    <div class="profile-top">
      <h2><?= htmlspecialchars($user['username']) ?></h2>

      <?php if ($profileId !== $_SESSION['user_id']): ?>
        <button class="follow-btn"
          onclick="toggleFollow(<?= $profileId ?>)">
          <?= $isFollowing ? "Unfollow" : "Follow" ?>
        </button>
      <?php endif; ?>
    </div>

    <div class="profile-stats">
      <span><strong><?= $followers ?></strong> Followers</span>
      <span><strong><?= $following ?></strong> Following</span>
    </div>

    <p class="profile-bio">
      <?= nl2br(htmlspecialchars($user['bio'] ?? 'No bio added')) ?>
    </p>

  </div>
</section>

<!-- ================= USER CONTENT ================= -->
<section class="profile-content">

  <!-- VIDEOS -->
  <h3>Uploaded Videos</h3>
  <?php
  $vid = $conn->prepare(
    "SELECT id, title, views FROM videos
     WHERE user_id = ? ORDER BY created_at DESC"
  );
  $vid->bind_param("i", $profileId);
  $vid->execute();
  $vr = $vid->get_result();
  ?>

  <?php if ($vr->num_rows): ?>
    <ul class="content-list">
      <?php while ($v = $vr->fetch_assoc()): ?>
        <li onclick="location.href='watch.php?id=<?= $v['id'] ?>'">
          🎥 <?= htmlspecialchars($v['title']) ?>
          <span><?= $v['views'] ?> views</span>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p class="empty-text">No videos uploaded</p>
  <?php endif; ?>

  <!-- E-CONTENT -->
  <h3>E-Content</h3>
  <?php
  $ec = $conn->prepare(
    "SELECT id, title, downloads FROM econtent
     WHERE user_id = ? ORDER BY created_at DESC"
  );
  $ec->bind_param("i", $profileId);
  $ec->execute();
  $er = $ec->get_result();
  ?>

  <?php if ($er->num_rows): ?>
    <ul class="content-list">
      <?php while ($e = $er->fetch_assoc()): ?>
        <li onclick="location.href='econtent-view.php?id=<?= $e['id'] ?>'">
          📄 <?= htmlspecialchars($e['title']) ?>
          <span><?= $e['downloads'] ?> downloads</span>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p class="empty-text">No e-content uploaded</p>
  <?php endif; ?>

</section>

</main>

<!-- ================= FOOTER ================= -->
<footer class="footer-nav">
  <div class="nav-item" onclick="location.href='index.php'">
    <i class="fa-solid fa-house"></i><span>Home</span>
  </div>
  <div class="nav-item" onclick="location.href='courses.php'">
    <i class="fa-solid fa-layer-group"></i><span>Courses</span>
  </div>
  <div class="nav-item create-button" onclick="location.href='upload.php'">
    <i class="fa-solid fa-plus-circle"></i><span>Upload</span>
  </div>
  <div class="nav-item" onclick="location.href='econtent.php'">
    <i class="fa-solid fa-file-lines"></i><span>E-Content</span>
  </div>
  <div class="nav-item active">
    <i class="fa-solid fa-user"></i><span>Profile</span>
  </div>
</footer>

<script src="assets/js/index.js"></script>
<script src="assets/js/profile.js"></script>
</body>
</html>
