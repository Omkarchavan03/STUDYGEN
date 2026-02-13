<?php
session_start();
require_once __DIR__ . '/../app/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$q = "
SELECT e.*, u.username, u.profile_pic,
 (SELECT COUNT(*) FROM econtent_likes WHERE econtent_id=e.id AND type='like') likes,
 (SELECT COUNT(*) FROM econtent_likes WHERE econtent_id=e.id AND type='dislike') dislikes
FROM econtent e
JOIN users u ON u.id = e.user_id
ORDER BY e.created_at DESC
";
$res = $conn->query($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-Content</title>

<!-- CSS -->
<link rel="stylesheet" href="assets/css/index.css">
<link rel="stylesheet" href="assets/css/content.css">
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.feed{
  max-width:600px;
  margin:auto;
  padding-bottom:90px; /* 🔴 REQUIRED FOR FOOTER */
}
.post{
  border:1px solid #ddd;
  border-radius:10px;
  margin:20px 10px;
  background:#fff;
}
.post-header{
  display:flex;
  align-items:center;
  padding:10px;
}
.post-header img{
  width:40px;
  height:40px;
  border-radius:50%;
  margin-right:10px;
}
.post-content img{
  width:100%;
  display:block;
}
.post-actions{
  display:flex;
  gap:15px;
  padding:10px;
  align-items:center;
}
.post-actions i{
  cursor:pointer;
  font-size:20px;
}
.comments{
  padding:10px;
  border-top:1px solid #eee;
}
.comment{
  font-size:14px;
  margin-bottom:5px;
}
.comment-form input{
  width:100%;
  padding:8px;
}
</style>
</head>

<body>

<!-- ================= HEADER (optional) ================= -->
<header class="header">
  <div class="logo-title">
    <i class="fa-solid fa-leaf" style="color:#2e7d32;"></i>
    <span class="hub-text">StudyGen</span>
  </div>
</header>

<!-- ================= FEED ================= -->
<div class="feed">

<?php while($p = $res->fetch_assoc()): ?>
<?php
$file = "../".$p['file_path'];
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
?>
<div class="post" data-id="<?= $p['id'] ?>">

  <!-- HEADER -->
  <div class="post-header">
    <img src="../app/uploads/profile/<?= $p['profile_pic'] ?: 'default.png' ?>">
    <b onclick="location.href='profile.php?id=<?= $p['user_id'] ?>'">
      <?= htmlspecialchars($p['username']) ?>
    </b>
  </div>

  <!-- CONTENT -->
  <div class="post-content">
    <?php if(in_array($ext,['jpg','png','jpeg','gif'])): ?>
      <img src="<?= $file ?>">
    <?php else: ?>
      <div style="padding:20px">📄 <?= basename($file) ?></div>
    <?php endif; ?>
  </div>

  <!-- ACTIONS -->
  <div class="post-actions">
    <i class="fa-regular fa-heart" onclick="react(<?= $p['id'] ?>,'like')"></i>
    <span><?= $p['likes'] ?></span>

    <i class="fa-regular fa-thumbs-down" onclick="react(<?= $p['id'] ?>,'dislike')"></i>
    <span><?= $p['dislikes'] ?></span>

    <i class="fa-solid fa-download"
       onclick="download(<?= $p['id'] ?>,'<?= $file ?>')"></i>

    <i class="fa-solid fa-share" onclick="share(<?= $p['id'] ?>)"></i>
  </div>

  <!-- COMMENTS -->
  <div class="comments" id="comments-<?= $p['id'] ?>">
    <?php
    $cid = (int)$p['id'];
    $cr = $conn->query("
      SELECT c.comment, u.username
      FROM econtent_comments c
      JOIN users u ON u.id=c.user_id
      WHERE c.econtent_id=$cid
      ORDER BY c.created_at DESC
      LIMIT 3
    ");
    while($c = $cr->fetch_assoc()):
    ?>
      <div class="comment">
        <b><?= htmlspecialchars($c['username']) ?>:</b>
        <?= htmlspecialchars($c['comment']) ?>
      </div>
    <?php endwhile; ?>

    <div class="comment-form">
      <input placeholder="Add a comment..."
             onkeydown="addComment(event,<?= $p['id'] ?>)">
    </div>
  </div>

</div>
<?php endwhile; ?>

</div>

<!-- ================= FOOTER (SAME AS INDEX) ================= -->
<footer class="footer-nav">

  <div class="nav-item" onclick="location.href='index.php'">
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

  <div class="nav-item active" onclick="location.href='econtent.php'">
    <i class="fa-solid fa-file-lines"></i>
    <span>E-Content</span>
  </div>

  <div class="nav-item" onclick="location.href='profile.php'">
    <i class="fa-solid fa-user"></i>
    <span>Profile</span>
  </div>

</footer>

<!-- ================= JS ================= -->
<script src="assets/js/econtent.js"></script>
</body>
</html>
