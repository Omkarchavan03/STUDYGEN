<?php
// public/profile-image.php

$baseDir = __DIR__ . "/../app/uploads/profile/";

if (!isset($_GET['img'])) {
    http_response_code(404);
    exit;
}

$file = basename($_GET['img']); // prevent directory traversal
$path = $baseDir . $file;

if (!file_exists($path)) {
    http_response_code(404);
    exit;
}

$mime = mime_content_type($path);
header("Content-Type: $mime");
header("Cache-Control: public, max-age=86400");

readfile($path);
exit;
