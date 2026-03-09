<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../app/config/db.php";

$current_user = $_SESSION['user_id'] ?? null;

if(!$current_user){
    header("Location: login.php");
    exit;
}

/* ================= FETCH POSTS ================= */

$sql = "
SELECT e.*, e.user_id, u.username, u.profile_pic,
(SELECT COUNT(*) FROM econtent_likes WHERE econtent_id=e.id AND type='like') AS likes,
(SELECT COUNT(*) FROM econtent_likes WHERE econtent_id=e.id AND type='dislike') AS dislikes
FROM econtent e
JOIN users u ON u.id=e.user_id
ORDER BY e.created_at DESC
";

/* ================= HANDLE AJAX ================= */

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])){

header("Content-Type: application/json");

$action = $_POST['action'];

/* ================= FOLLOW ================= */

if($action === "follow"){

$channel = (int)$_POST['channel_id'];

if($channel === $current_user){
echo json_encode(["status"=>"error"]);
exit;
}

$check = $conn->query("
SELECT id FROM followers
WHERE follower_id=$current_user
AND following_id=$channel
");

$following = $check->num_rows ? false : true;

if($check->num_rows){

$conn->query("
DELETE FROM followers
WHERE follower_id=$current_user
AND following_id=$channel
");

}else{

$conn->query("
INSERT INTO followers(follower_id,following_id)
VALUES($current_user,$channel)
");

}

echo json_encode([
"status"=>"success",
"following"=>$following
]);

exit;
}

/* ================= POST REACTION ================= */

if($action === "react"){

$id = (int)$_POST['id'];
$type = $_POST['type'] === "like" ? "like" : "dislike";

$check = $conn->query("
SELECT id,type FROM econtent_likes
WHERE user_id=$current_user
AND econtent_id=$id
");

if($check->num_rows){

$row = $check->fetch_assoc();

if($row['type'] === $type){

$conn->query("
DELETE FROM econtent_likes
WHERE user_id=$current_user
AND econtent_id=$id
");

}else{

$conn->query("
UPDATE econtent_likes
SET type='$type'
WHERE user_id=$current_user
AND econtent_id=$id
");

}

}else{

$conn->query("
INSERT INTO econtent_likes(user_id,econtent_id,type)
VALUES($current_user,$id,'$type')
");

}

$likes = $conn->query("
SELECT COUNT(*) t
FROM econtent_likes
WHERE econtent_id=$id AND type='like'
")->fetch_assoc()['t'];

$dislikes = $conn->query("
SELECT COUNT(*) t
FROM econtent_likes
WHERE econtent_id=$id AND type='dislike'
")->fetch_assoc()['t'];

echo json_encode([
"status"=>"success",
"likes"=>$likes,
"dislikes"=>$dislikes
]);

exit;
}

/* ================= COMMENT ================= */

if($action === "comment"){

$id = (int)$_POST['id'];
$text = trim($_POST['text']);

if(!$text){
echo json_encode(["status"=>"error"]);
exit;
}

$text = $conn->real_escape_string($text);

$conn->query("
INSERT INTO econtent_comments(user_id,econtent_id,comment,created_at)
VALUES($current_user,$id,'$text',NOW())
");

$cid = $conn->insert_id;

$c = $conn->query("
SELECT c.id,c.comment,u.username
FROM econtent_comments c
JOIN users u ON u.id=c.user_id
WHERE c.id=$cid
")->fetch_assoc();

echo json_encode([
"status"=>"success",
"comment"=>$c,
"comment_id"=>$cid
]);

exit;
}

/* ================= DELETE COMMENT ================= */

if($action === "delete_comment"){

$id = (int)$_POST['comment_id'];

$conn->query("
DELETE FROM econtent_comments
WHERE id=$id
AND user_id=$current_user
");

echo json_encode(["status"=>"success"]);

exit;
}

/* ================= COMMENT REACTION ================= */

if($action === "comment_react"){

$id = (int)$_POST['comment_id'];
$type = $_POST['reaction']=="like" ? "like" : "dislike";

$check = $conn->query("
SELECT id,type FROM econtent_comment_likes
WHERE user_id=$current_user
AND comment_id=$id
");

if($check->num_rows){

$row = $check->fetch_assoc();

if($row['type']===$type){

$conn->query("
DELETE FROM econtent_comment_likes
WHERE user_id=$current_user
AND comment_id=$id
");

}else{

$conn->query("
UPDATE econtent_comment_likes
SET type='$type'
WHERE user_id=$current_user
AND comment_id=$id
");

}

}else{

$conn->query("
INSERT INTO econtent_comment_likes(user_id,comment_id,type)
VALUES($current_user,$id,'$type')
");

}

$likes = $conn->query("
SELECT COUNT(*) t
FROM econtent_comment_likes
WHERE comment_id=$id AND type='like'
")->fetch_assoc()['t'];

$dislikes = $conn->query("
SELECT COUNT(*) t
FROM econtent_comment_likes
WHERE comment_id=$id AND type='dislike'
")->fetch_assoc()['t'];

echo json_encode([
"status"=>"success",
"likes"=>$likes,
"dislikes"=>$dislikes
]);

exit;
}

/* ================= DOWNLOAD ================= */

if($action==="download"){

$id=(int)$_POST['id'];

$conn->query("
UPDATE econtent
SET downloads=downloads+1
WHERE id=$id
");

echo json_encode(["status"=>"success"]);

exit;
}

}

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>E-Content • StudyGen</title>
<link rel="stylesheet" href="assets/css/index.css">

<link rel="stylesheet" href="assets/css/econtent.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<?php include "assets/includes/header.php"; ?>

<div class="feed" id="feed">

<?php while($p = $res->fetch_assoc()): 

$file = "../".$p['file_path'];
$ext = strtolower(pathinfo($file,PATHINFO_EXTENSION));

?>

<div class="post" id="post-<?= $p['id'] ?>" data-id="<?= $p['id'] ?>">

<div class="post-header">

<img src="../app/uploads/profile/<?= $p['profile_pic'] ?: 'default.png' ?>" class="small-profile">

<b onclick="location.href='profile.php?id=<?= $p['user_id'] ?>'">
<?= htmlspecialchars($p['username']) ?>
</b>

<?php if($p['user_id'] != $current_user): 

$fcheck = $conn->query("
SELECT id FROM followers
WHERE follower_id=$current_user
AND following_id=".$p['user_id']);

$isFollowing = $fcheck->num_rows ? true : false;

?>

<button class="follow-btn <?= $isFollowing ? 'following' : '' ?>" 
data-channel="<?= $p['user_id'] ?>">

<?= $isFollowing ? 'Following' : 'Follow' ?>

</button>

<?php endif; ?>

</div>

<div class="post-content">

<?php if(in_array($ext,['jpg','jpeg','png','gif'])): ?>

<img src="<?= $file ?>">

<?php elseif($ext == "pdf"): ?>

<iframe src="<?= $file ?>"></iframe>

<?php elseif($ext == "txt"): ?>

<iframe src="<?= $file ?>"></iframe>

<?php else: ?>

<div class="post-file">
<a href="<?= $file ?>" download>
📄 <?= basename($p['file_path']) ?>
</a>
</div>

<?php endif; ?>

</div>

<div class="post-actions">

<i class="fa-regular fa-thumbs-up"></i>
<span><?= $p['likes'] ?></span>

<i class="fa-regular fa-thumbs-down"></i>
<span><?= $p['dislikes'] ?></span>

<i class="fa-solid fa-download"></i>

<i class="fa-solid fa-share"></i>

<i class="fa-regular fa-comment-dots"></i>

</div>

<?php if(!empty($p['description'])): ?>

<div class="post-caption">
<?= htmlspecialchars($p['description']) ?>
</div>

<?php endif; ?>

<div class="comments" id="comments-<?= $p['id'] ?>" style="display:none;">

<?php
$comments = $conn->query("
SELECT c.*,u.username
FROM econtent_comments c
JOIN users u ON u.id=c.user_id
WHERE c.econtent_id=".$p['id']."
ORDER BY c.created_at ASC
");

while($c = $comments->fetch_assoc()):

$likes = $conn->query("
SELECT COUNT(*) t
FROM econtent_comment_likes
WHERE comment_id=".$c['id']." AND type='like'
")->fetch_assoc()['t'];

$dislikes = $conn->query("
SELECT COUNT(*) t
FROM econtent_comment_likes
WHERE comment_id=".$c['id']." AND type='dislike'
")->fetch_assoc()['t'];
?>

<div class="comment" id="comment-<?= $c['id'] ?>">

<b><?= htmlspecialchars($c['username']) ?>:</b>
<?= htmlspecialchars($c['comment']) ?>

<?php if($c['user_id']==$current_user): ?>
<button class="delete-comment-btn" data-id="<?= $c['id'] ?>">Delete</button>
<?php endif; ?>

<button class="comment-like-btn" data-id="<?= $c['id'] ?>">
👍 <span id="commentLike-<?= $c['id'] ?>"><?= $likes ?></span>
</button>

<button class="comment-dislike-btn" data-id="<?= $c['id'] ?>">
👎 <span id="commentDislike-<?= $c['id'] ?>"><?= $dislikes ?></span>
</button>

</div>

<?php endwhile; ?>

<div class="comment-form">
<input placeholder="Add comment...">
</div>

</div>

</div>

<?php endwhile; ?>

</div>

<div id="loading" style="text-align:center;padding:20px;display:none;">
Loading...
</div>

<div class="end-feed">
END OF FEED
</div>

<?php include "assets/includes/footer.php"; ?>

<script src="assets/js/econtent.js"></script>

</body>
</html>