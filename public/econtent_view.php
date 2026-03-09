<?php
// econtent_view.php
require_once __DIR__ . '/../app/config/db.php';

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit("File not specified.");
}

/* FILE NAME SECURITY */
$file = basename($_GET['file']);
$download = isset($_GET['download']);

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

/* ALLOWED FILE TYPES */
$imageTypes = ['jpg','jpeg','png','gif','webp'];
$pdfTypes   = ['pdf'];
$textTypes  = ['txt'];
$docTypes   = ['doc','docx','ppt','pptx','xls','xlsx'];
$zipTypes   = ['zip','rar','7z'];

/* FILE PATH */
$path = __DIR__ . '/../app/uploads/econtent/' . $file;

if (!file_exists($path)) {
    http_response_code(404);
    exit("File not found.");
}

/* GET MIME */
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $path);
finfo_close($finfo);


/* DOWNLOAD FORCE */
if($download){
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$file\"");
    header("Content-Length: ".filesize($path));
    readfile($path);
    exit;
}


/* IMAGE PREVIEW */
if(in_array($ext,$imageTypes)){

    header("Content-Type: $mime");
    header("Content-Disposition: inline; filename=\"$file\"");
    header("Content-Length: ".filesize($path));
    readfile($path);
    exit;
}


/* PDF PREVIEW */
if(in_array($ext,$pdfTypes)){

    header("Content-Type: application/pdf");
    header("Content-Disposition: inline; filename=\"$file\"");
    header("Content-Length: ".filesize($path));
    readfile($path);
    exit;
}


/* TEXT PREVIEW */
if(in_array($ext,$textTypes)){

    header("Content-Type: text/plain");
    header("Content-Disposition: inline; filename=\"$file\"");
    header("Content-Length: ".filesize($path));
    readfile($path);
    exit;
}


/* GOOGLE DOCS VIEWER */
if(in_array($ext,$docTypes)){

    $url = "https://yourdomain.com/econtent_view.php?file=" . urlencode($file);

    echo "
    <html>
    <head>
    <title>Document Viewer</title>
    <style>
    body{margin:0;background:#111;}
    iframe{width:100%;height:100vh;border:none;}
    </style>
    </head>
    <body>
    <iframe src='https://docs.google.com/gview?url=$url&embedded=true'></iframe>
    </body>
    </html>
    ";

    exit;
}


/* ZIP / RAR DOWNLOAD ONLY */
if(in_array($ext,$zipTypes)){

    echo "
    <html>
    <head>
    <title>Download File</title>
    <style>
    body{
        font-family:Arial;
        background:#111;
        color:white;
        text-align:center;
        padding-top:120px;
    }
    a{
        display:inline-block;
        padding:12px 20px;
        background:#2563eb;
        color:white;
        text-decoration:none;
        border-radius:6px;
        margin-top:20px;
    }
    </style>
    </head>
    <body>

    <h2>Preview not available</h2>
    <p>This file type cannot be previewed.</p>

    <a href='?file=".urlencode($file)."&download=1'>Download File</a>

    </body>
    </html>
    ";

    exit;
}


/* UNKNOWN FILE */
http_response_code(403);
exit("File type not supported.");
?>