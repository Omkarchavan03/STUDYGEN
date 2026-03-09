<?php
require_once __DIR__ . "/../app/config/db.php";

if(!isset($_GET['file'])) {
    http_response_code(400);
    exit("No file specified.");
}

$file = basename($_GET['file']);
$path = __DIR__ . "/../app/uploads/videos/" . $file;

if(!file_exists($path)) {
    http_response_code(404);
    exit("Video not found.");
}

$size = filesize($path);
$fm = @fopen($path, 'rb');
if(!$fm){
    http_response_code(500);
    exit("Cannot open file.");
}

$begin = 0;
$end = $size - 1;

header("Content-Type: video/mp4");
header("Accept-Ranges: bytes");

if(isset($_SERVER['HTTP_RANGE'])){
    // Parse the range header
    list($range_unit, $range_value) = explode("=", $_SERVER['HTTP_RANGE'], 2);
    if($range_unit == 'bytes'){
        list($begin, $end) = explode("-", $range_value);
        $begin = intval($begin);
        $end = ($end === '') ? $size - 1 : intval($end);

        header("HTTP/1.1 206 Partial Content");
        header("Content-Range: bytes $begin-$end/$size");
        header("Content-Length: " . ($end - $begin + 1));
    }
}else{
    header("Content-Length: $size");
}

fseek($fm, $begin);
while(!feof($fm) && ($pos = ftell($fm)) <= $end){
    echo fread($fm, min(1024*16, $end - $pos + 1));
    flush();
}
fclose($fm);
exit;