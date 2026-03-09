<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../app/config/db.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$userId = (int) $_SESSION['user_id'];

/* ================= HANDLE POST ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type = $_POST['type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        echo "Title required";
        exit;
    }

    /* ================= E-CONTENT ================= */
    if ($type === 'econtent') {

        if (empty($_FILES['file']['name'])) {
            echo "File required";
            exit;
        }

        $uploadDir = __DIR__ . "/../app/uploads/econtent/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = time() . "_" . basename($_FILES['file']['name']);
        $serverPath = $uploadDir . $filename;
        $dbPath = "app/uploads/econtent/" . $filename;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $serverPath)) {
            echo "File upload failed";
            exit;
        }

        $stmt = $conn->prepare(
            "INSERT INTO econtent (user_id, title, description, file_path)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("isss", $userId, $title, $description, $dbPath);
        $stmt->execute();

        echo "OK";
        exit;
    }

    /* ================= VIDEO ================= */
    if ($type === 'video') {

        if (empty($_FILES['video']['name'])) {
            echo "Video required";
            exit;
        }

        $videoDir = __DIR__ . "/../app/uploads/videos/";
        $thumbDir = __DIR__ . "/../app/uploads/thumbnails/";

        if (!is_dir($videoDir)) mkdir($videoDir, 0777, true);
        if (!is_dir($thumbDir)) mkdir($thumbDir, 0777, true);

        /* ---- VIDEO ---- */
        $videoName = time() . "_" . basename($_FILES['video']['name']);
        $videoServerPath = $videoDir . $videoName;
        $videoDbPath = "app/uploads/videos/" . $videoName;

        if (!move_uploaded_file($_FILES['video']['tmp_name'], $videoServerPath)) {
            echo "Video upload failed";
            exit;
        }

        /* ---- THUMBNAIL (OPTIONAL) ---- */
        $thumbDbPath = null;

        if (!empty($_FILES['thumbnail']['name'])) {
            $thumbName = time() . "_" . basename($_FILES['thumbnail']['name']);
            $thumbServerPath = $thumbDir . $thumbName;

            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbServerPath)) {
                $thumbDbPath = "app/uploads/thumbnails/" . $thumbName;
            }
        }

        $stmt = $conn->prepare(
            "INSERT INTO videos (user_id, title, description, video_path, thumbnail)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "issss",
            $userId,
            $title,
            $description,
            $videoDbPath,
            $thumbDbPath
        );
        $stmt->execute();

        echo "OK";
        exit;
    }

    echo "Invalid type";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload • StudyGen</title>

<link rel="stylesheet" href="assets/css/index.css">
<link rel="stylesheet" href="assets/css/upload.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<!-- ================= HEADER ================= -->
<header>
    <h1>StudyGen Upload</h1>
    <button class="theme-toggle" onclick="document.body.classList.toggle('dark-theme')">
        <i class="fa-solid fa-moon"></i> Toggle Theme
    </button>
</header>

<h2>Upload</h2>

<div class="tabs">
  <div class="tab active" onclick="switchTab('video')">
      <i class="fa-solid fa-video"></i> Video
  </div>
  <div class="tab" onclick="switchTab('econtent')">
      <i class="fa-solid fa-file"></i> E-Content
  </div>
</div>

<!-- ================= VIDEO FORM ================= -->
<form id="videoForm" class="form active" enctype="multipart/form-data">
  <input type="hidden" name="type" value="video">

  <input name="title" placeholder="Video title" required><br><br>

  <textarea name="description" placeholder="Video description"></textarea><br><br>

  <label>Video file</label><br>
  <input type="file" name="video" accept="video/*" required><br><br>

  <label>
    <input type="checkbox" onchange="toggleThumb(this)">
    Upload custom thumbnail
  </label><br><br>

  <div id="thumbInput" style="display:none">
    <input type="file" name="thumbnail" accept="image/*"><br><br>
  </div>

  <progress id="progress" value="0" max="100"></progress>

  <div id="msg"></div><br>

  <button type="submit">Upload Video</button>
</form>

<!-- ================= E-CONTENT FORM ================= -->
<form id="econtentForm" class="form" enctype="multipart/form-data">
  <input type="hidden" name="type" value="econtent">

  <input name="title" placeholder="File title" required><br><br>

  <textarea name="description" placeholder="File description"></textarea><br><br>

  <input type="file" name="file" required><br><br>

  <progress id="progress" value="0" max="100"></progress>

  <div id="msg"></div><br>

  <button type="submit">Upload File</button>
</form>
<br>

<!-- ================= FOOTER ================= -->
<footer>
    
    <a href="index.php" style="color:var(--text-color); text-decoration:none;">
        <i class="fa-solid fa-house"></i> Home
    </a>
    <p>&copy; <?= date('Y') ?> StudyGen. All rights reserved.</p>
</footer>

<script src="assets/js/upload.js"></script>
</body>
</html>