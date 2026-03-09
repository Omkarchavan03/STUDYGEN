<?php
require_once __DIR__ . "/../app/config/db.php";
session_start();

/* ===============================
   FETCH LATEST VIDEOS (LIMITED)
================================= */

$sql = "SELECT v.id, v.title, v.description, v.views,
               v.thumbnail,
               u.username,
               u.profile_pic
        FROM videos v
        LEFT JOIN users u ON v.user_id = u.id
        ORDER BY v.created_at DESC
        LIMIT 20";

$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

/* ===============================
   PATH CONFIGURATION
================================= */

// Absolute filesystem paths (for file_exists)
$thumbFsDir   = __DIR__ . "/app/uploads/thumbnails";
$profileFsDir = __DIR__ . "/app/uploads/profile";

// Public URL paths (for <img src>)
$thumbUrlDir   = "app/uploads/thumbnails/";
$profileUrlDir = "app/uploads/profiles/";

$defaultThumbUrl = $thumbUrlDir . "default.png";
$defaultUserUrl  = "assets/images/default-user.png";
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

<div id="notificationPanel" class="notification-panel">
  <p>No new notifications</p>
</div>

<main class="main-content" id="home">
<h2 class="section-title">Latest Lectures</h2>

<section class="videos-section">
<div class="video-grid">

<?php if ($result->num_rows > 0): ?>
<?php while ($video = $result->fetch_assoc()): ?>

<?php
    // Prepare safe variables
    $videoId  = (int)$video['id'];
    $title    = htmlspecialchars($video['title'] ?? 'Untitled');
    $desc     = htmlspecialchars(
                  mb_strimwidth($video['description'] ?? '', 0, 90, '...')
                );
    $views    = (int)($video['views'] ?? 0);
    $username = htmlspecialchars($video['username'] ?? 'Unknown User');

    /* ===== Thumbnail Handling ===== */
    $thumbFile = $video['thumbnail'] ?? '';
    $thumbFsPath = $thumbFsDir . $thumbFile;

    if (!empty($thumbFile) && file_exists($thumbFsPath)) {
        $thumbUrl = $thumbUrlDir . $thumbFile;
    } else {
        $thumbUrl = $defaultThumbUrl;
    }

    /* ===== Profile Picture Handling ===== */
    $profileFile = $video['profile_pic'] ?? '';
    $profileFsPath = $profileFsDir . $profileFile;

    if (!empty($profileFile) && file_exists($profileFsPath)) {
        $profileUrl = $profileUrlDir . $profileFile;
    } else {
        $profileUrl = $defaultUserUrl;
    }
?>

<article class="video-card">
  <a href="watch.php?id=<?= $videoId ?>" class="video-link">

    <div class="video-thumb">
      <img src="<?= htmlspecialchars($thumbUrl) ?>"
           alt="<?= $title ?>"
           loading="lazy">
      <div class="play-overlay">
        <i class="fa-solid fa-play-circle"></i>
      </div>
    </div>

    <div class="video-meta">

      <div class="channel-avatar">
        <img src="<?= htmlspecialchars($profileUrl) ?>"
             alt="<?= $username ?>">
      </div>

      <div class="video-info">
        <div class="video-title">
          <?= $title ?>
        </div>

        <div class="video-desc">
          <?= $desc ?>
        </div>

        <div class="video-stats">
          <?= $username ?> • <?= $views ?> views
        </div>
      </div>

    </div>

  </a>
</article>

<?php endwhile; ?>
<?php else: ?>
<p class="empty-message">No lectures uploaded yet.</p>
<?php endif; ?>

</div>
</section>
</main>

<footer class="footer-nav">
  <div class="nav-item active">
    <a href="index.php">
      <i class="fa-solid fa-house"></i>
      <span>Home</span>
    </a>
  </div>

  <div class="nav-item">
    <a href="courses.php">
      <i class="fa-solid fa-layer-group"></i>
      <span>Courses</span>
    </a>
  </div>

  <div class="nav-item create-button">
    <a href="upload.php">
      <i class="fa-solid fa-plus-circle"></i>
      <span>Upload</span>
    </a>
  </div>

  <div class="nav-item">
    <a href="econtent.php">
      <i class="fa-solid fa-file-lines"></i>
      <span>E-Content</span>
    </a>
  </div>

  <div class="nav-item">
    <a href="profile.php">
      <i class="fa-solid fa-user"></i>
      <span>Profile</span>
    </a>
  </div>
</footer>

<button id="genaiBtn" class="genai-btn"
        onclick="location.href='genai.php'">
  <i class="fa-solid fa-leaf"></i>
</button>

<script src="assets/js/index.js"></script>
</body>
</html>
