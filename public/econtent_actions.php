<?php
session_start();
require_once __DIR__ . '/../app/config/db.php';

if(!isset($_SESSION['user_id'])) exit;
$uid=$_SESSION['user_id'];

$action=$_POST['action'] ?? '';
$id=(int)($_POST['id'] ?? 0);

if($action==='react'){
  $type=$_POST['type'];
  $conn->query("
    INSERT INTO econtent_likes(user_id,econtent_id,type)
    VALUES($uid,$id,'$type')
    ON DUPLICATE KEY UPDATE type='$type'
  ");
  exit;
}

if($action==='comment'){
  $text=trim($_POST['text']);
  $stmt=$conn->prepare(
    "INSERT INTO econtent_comments(econtent_id,user_id,comment)
     VALUES(?,?,?)"
  );
  $stmt->bind_param("iis",$id,$uid,$text);
  $stmt->execute();
  echo "ok";
  exit;
}

if($action==='download'){
  $conn->query("UPDATE econtent SET downloads=downloads+1 WHERE id=$id");
  exit;
}
