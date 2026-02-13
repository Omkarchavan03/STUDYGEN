<?php
require_once __DIR__ . "/../app/config/db.php";
session_start();

$sql = "SELECT v.id, v.title, v.description, v.views,
               v.thumbnail,
               u.username,
               u.profile_pic
        FROM videos v
        LEFT JOIN users u ON v.user_id = u.id
        ORDER BY v.created_at DESC";

$result = $conn->query($sql);

/* ===== PATHS ===== */
$thumbnailDir = "app/uploads/thumbnails/";
$profileDir   = "app/uploads/profiles/";

$defaultThumb = $thumbnailDir . "default.png";
$defaultUser  = "assets/images/default-user.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGen</title>

<link rel="stylesheet" href="assets/css/index.css">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="header">
  <div class="logo-title">
    <i class="fa-solid fa-leaf" style="color:#2e7d32;"></i>
    <span class="hub-text">StudyGen</span>
  </div>

  <div class="center-section">
    <input type="text" id="searchInput" placeholder="Search lectures, notes...">
  </div>

  <div class="right-section">
    <button class="download-btn" id="downloadBtn">
      <i class="fa-solid fa-download"></i>
    </button>

    <button id="toggleTheme" class="toggle-btn">
      <i class="fa-solid fa-moon"></i>
    </button>

    <button id="notificationBtn" class="notification-btn">
      <i class="fa-solid fa-bell"></i>
    </button>
  </div>
</header>

<!-- Notification Dropdown -->
<div id="notificationPanel" class="notification-panel">
  <p>No new notifications</p>
</div>

<main class="main-content" id="home">
<h2 style="padding:18px 18px 0;">Latest Lectures</h2>

<section class="videos-section">
<div class="video-grid">

<?php if ($result && $result->num_rows > 0): ?>
<?php while ($video = $result->fetch_assoc()):

    // Thumbnail Fix
    $thumbPath = $thumbnailDir . $video['thumbnail'];
    $thumb = (!empty($video['thumbnail']) && file_exists($thumbPath))
             ? $thumbPath
             : $defaultThumb;

    // Profile Fix
    $profilePath = $profileDir . $video['profile_pic'];
    $profilePic = (!empty($video['profile_pic']) && file_exists($profilePath))
                  ? $profilePath
                  : $defaultUser;
?>

<article class="video-card"
         tabindex="0"
         onclick="openWatch(<?= (int)$video['id'] ?>)">

  <div class="video-thumb">
    <img src="<?= htmlspecialchars($thumb) ?>"
         alt="<?= htmlspecialchars($video['title']) ?>"
         loading="lazy">
    <div class="play-overlay">
      <i class="fa-solid fa-play-circle"></i>
    </div>
  </div>

  <div class="video-meta">

    <div class="channel-avatar">
      <img src="<?= htmlspecialchars($profilePic) ?>"
           alt="<?= htmlspecialchars($video['username'] ?? 'User') ?>">
    </div>

    <div class="video-info">
      <div class="video-title">
        <?= htmlspecialchars($video['title']) ?>
      </div>

      <div class="video-desc">
        <?= htmlspecialchars(
              mb_strimwidth($video['description'] ?? '', 0, 90, '...')
            ) ?>
      </div>

      <div class="video-stats">
        <?= htmlspecialchars($video['username'] ?? 'Unknown User') ?>
        • <?= (int)$video['views'] ?> views
      </div>
    </div>

  </div>
</article>

<?php endwhile; ?>
<?php else: ?>
<p style="padding:20px;">No lectures uploaded yet.</p>
<?php endif; ?>

</div>
</section>
</main>

<footer class="footer-nav">
  <div class="nav-item active" onclick="location.href='index.php'">
    <i class="fa-solid fa-house"></i>
    <span>Home</span>
  </div>

  <div class="nav-item" onclick="location.href='courses.php'">
    <i class="fa-solid fa-layer-group"></i>
    <span>Courses</span>
  </div>

  <div class="nav-item create-button" onclick="location.href='upload.php'">
    <i class="fa-solid fa-plus-circle"></i>
    <span>Upload</span>
  </div>

  <div class="nav-item" onclick="location.href='econtent.php'">
    <i class="fa-solid fa-file-lines"></i>
    <span>E-Content</span>
  </div>

  <div class="nav-item" onclick="location.href='profile.php'">
    <i class="fa-solid fa-user"></i>
    <span>Profile</span>
  </div>
</footer>

<button id="genaiBtn" class="genai-btn" onclick="location.href='genai.php'">
  <i class="fa-solid fa-leaf"></i>
</button>

<script src="assets/js/index.js"></script>
</body>
</html>
