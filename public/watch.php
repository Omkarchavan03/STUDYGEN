<?php
session_start();
require_once "../app/config/db.php";

if(!isset($_GET['id'])) die("Video not found");

$video_id = (int)$_GET['id'];
$current_user = $_SESSION['user_id'] ?? null;

/* AJAX HANDLER */
if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action'])){
    header("Content-Type: application/json");
    if(!$current_user){
        echo json_encode(["status"=>"error"]);
        exit;
    }

    /* COMMENT */
    if($_POST['action'] === "comment"){
        $comment = trim($_POST['comment']);
        $stmt = $conn->prepare("INSERT INTO video_comments(user_id,video_id,comment) VALUES(?,?,?)");
        $stmt->bind_param("iis",$current_user,$video_id,$comment);
        $stmt->execute();
        $comment_id = $conn->insert_id;

        $new_comment = $conn->query("
            SELECT c.id,c.comment,c.user_id,u.username
            FROM video_comments c
            JOIN users u ON c.user_id=u.id
            WHERE c.id=$comment_id
        ")->fetch_assoc();

        echo json_encode(["status"=>"success","comment"=>$new_comment]);
        exit;
    }

    /* VIDEO REACTION */
    if($_POST['action'] === "react"){
        $type = ($_POST['reaction'] === "like") ? "like" : "dislike";
        $check = $conn->query("SELECT * FROM video_reactions WHERE user_id=$current_user AND video_id=$video_id");
        if($check->num_rows){
            $existing = $check->fetch_assoc()['reaction'];
            if($existing === $type){
                $conn->query("DELETE FROM video_reactions WHERE user_id=$current_user AND video_id=$video_id");
            }else{
                $conn->query("UPDATE video_reactions SET reaction='$type' WHERE user_id=$current_user AND video_id=$video_id");
            }
        }else{
            $conn->query("INSERT INTO video_reactions(user_id,video_id,reaction) VALUES($current_user,$video_id,'$type')");
        }
        $likes = $conn->query("SELECT COUNT(*) t FROM video_reactions WHERE video_id=$video_id AND reaction='like'")->fetch_assoc()['t'];
        $dislikes = $conn->query("SELECT COUNT(*) t FROM video_reactions WHERE video_id=$video_id AND reaction='dislike'")->fetch_assoc()['t'];
        echo json_encode(["status"=>"success","likes"=>$likes,"dislikes"=>$dislikes]);
        exit;
    }

    /* SAVE VIDEO */
    if($_POST['action'] === "save"){
        $check = $conn->query("SELECT * FROM saved_videos WHERE user_id=$current_user AND video_id=$video_id");
        $saved = $check->num_rows ? false : true;
        $check->num_rows ? $conn->query("DELETE FROM saved_videos WHERE user_id=$current_user AND video_id=$video_id") : $conn->query("INSERT INTO saved_videos(user_id,video_id) VALUES($current_user,$video_id)");
        echo json_encode(["status"=>"success","saved"=>$saved]);
        exit;
    }

    /* COMMENT REACTION */
    if($_POST['action'] === "comment_react"){
        $comment_id = (int)$_POST['comment_id'];
        $type = ($_POST['reaction'] === "like") ? "like" : "dislike";
        $check = $conn->query("SELECT * FROM comment_reactions WHERE user_id=$current_user AND comment_id=$comment_id");
        if($check->num_rows){
            $existing = $check->fetch_assoc()['reaction'];
            $existing === $type ? $conn->query("DELETE FROM comment_reactions WHERE user_id=$current_user AND comment_id=$comment_id") : $conn->query("UPDATE comment_reactions SET reaction='$type' WHERE user_id=$current_user AND comment_id=$comment_id");
        }else{
            $conn->query("INSERT INTO comment_reactions(user_id,comment_id,reaction) VALUES($current_user,$comment_id,'$type')");
        }
        $likes = $conn->query("SELECT COUNT(*) t FROM comment_reactions WHERE comment_id=$comment_id AND reaction='like'")->fetch_assoc()['t'];
        $dislikes = $conn->query("SELECT COUNT(*) t FROM comment_reactions WHERE comment_id=$comment_id AND reaction='dislike'")->fetch_assoc()['t'];
        echo json_encode(["status"=>"success","likes"=>$likes,"dislikes"=>$dislikes]);
        exit;
    }

    /* DELETE COMMENT */
    if($_POST['action'] === "delete_comment"){
        $id = (int)$_POST['comment_id'];
        $conn->query("DELETE FROM video_comments WHERE id=$id AND user_id=$current_user");
        echo json_encode(["status"=>"success","comment_id"=>$id]);
        exit;
    }

    /* FOLLOW CHANNEL */
    if($_POST['action'] === "follow"){
        $channel = (int)$_POST['channel_id'];
        $check = $conn->query("SELECT id FROM followers WHERE follower_id=$current_user AND following_id=$channel");
        $following = $check->num_rows ? false : true;
        $check->num_rows ? $conn->query("DELETE FROM followers WHERE follower_id=$current_user AND following_id=$channel") : $conn->query("INSERT INTO followers(follower_id,following_id) VALUES($current_user,$channel)");
        echo json_encode(["status"=>"success","following"=>$following]);
        exit;
    }
}

/* GET VIDEO */
$stmt = $conn->prepare("
SELECT v.*,u.username,u.profile_pic,u.id uploader_id
FROM videos v
JOIN users u ON v.user_id=u.id
WHERE v.id=?
");
$stmt->bind_param("i",$video_id);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();
if(!$video) die("Video missing");

/* VIEW COUNT */
if($current_user){
    $check = $conn->query("SELECT * FROM video_views WHERE user_id=$current_user AND video_id=$video_id");
    if(!$check->num_rows){
        $conn->query("INSERT INTO video_views(user_id,video_id) VALUES($current_user,$video_id)");
        $conn->query("UPDATE videos SET views=views+1 WHERE id=$video_id");
    }
}

/* VIDEO REACTION COUNTS */
$likes = $conn->query("SELECT COUNT(*) t FROM video_reactions WHERE video_id=$video_id AND reaction='like'")->fetch_assoc()['t'];
$dislikes = $conn->query("SELECT COUNT(*) t FROM video_reactions WHERE video_id=$video_id AND reaction='dislike'")->fetch_assoc()['t'];

/* COMMENTS */
$comments = $conn->query("
SELECT c.*,u.username,
(SELECT COUNT(*) FROM comment_reactions WHERE comment_id=c.id AND reaction='like') AS likes,
(SELECT COUNT(*) FROM comment_reactions WHERE comment_id=c.id AND reaction='dislike') AS dislikes
FROM video_comments c
JOIN users u ON c.user_id=u.id
WHERE c.video_id=$video_id
ORDER BY c.created_at DESC
");

/* SUGGESTIONS */
$suggest = $conn->query("
SELECT id,title,thumbnail,views
FROM videos
WHERE id!=$video_id
ORDER BY RAND()
LIMIT 8
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?=htmlspecialchars($video['title'])?> • StudyGen</title>
<link rel="stylesheet" href="assets/css/index.css">
<link rel="stylesheet" href="assets/css/watch.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.video-wrapper { position: relative; width: 100%; max-width:1000px; margin:0 auto; padding-top:56.25%; }
.video-wrapper video { position: absolute; top:0; left:0; width:100%; height:100%; }
</style>
</head>
<body>

<?php include "assets/includes/header.php"; ?>

<div class="watch-page">
  <div class="watch-container">

    <!-- VIDEO -->
    <div class="video-wrapper">
      <video controls>
        <source src="stream.php?file=<?=urlencode($video['video_path'])?>" type="video/mp4">
      </video>
    </div>

    <h2><?=htmlspecialchars($video['title'])?></h2>
    <p id="videoViews"><?=$video['views']?> views</p>

    <!-- DESCRIPTION -->
    <div class="video-description">
      <h4>Description</h4>
      <p id="descText" class="collapsed"><?=nl2br(htmlspecialchars($video['description']))?></p>
      <button id="descBtn" onclick="toggleDesc()">Show more</button>
    </div>

    <!-- CHANNEL -->
<div class="channel-box">
  <img src="../app/uploads/profile/<?=htmlspecialchars($video['profile_pic'] ?: 'default.png')?>">
  <div class="channel-info">
    <strong><?=htmlspecialchars($video['username'])?></strong>
    <p>Channel</p>
  </div>

  <?php if($current_user && $current_user != $video['uploader_id']): 
        // Check if current user is already following this uploader
        $fcheck = $conn->query("SELECT id FROM followers WHERE follower_id=$current_user AND following_id=".$video['uploader_id']);
        $isFollowing = $fcheck->num_rows ? true : false;
  ?>
    <button class="btn-follow" data-channel="<?=$video['uploader_id']?>" onclick="followChannel(this)">
        <?= $isFollowing ? "Following" : "Follow" ?>
    </button>
  <?php endif; ?>
</div>

    <!-- ACTION BUTTONS -->
    <div class="action-buttons">
      <button class="btn-like" onclick="react('like')"><i class="fa fa-thumbs-up"></i> <span id="likeCount"><?=$likes?></span></button>
      <button class="btn-dislike" onclick="react('dislike')"><i class="fa fa-thumbs-down"></i> <span id="dislikeCount"><?=$dislikes?></span></button>
      <button class="btn-save" onclick="saveVideo()"><i class="fa fa-bookmark"></i> <span id="saveText">Save</span></button>
      <button onclick="shareVideo()"><i class="fa fa-share"></i> Share</button>
      <a href="stream.php?file=<?=urlencode($video['video_path'])?>" download><i class="fa fa-download"></i> Download</a>
    </div>

    <hr>

    <!-- COMMENTS -->
    <h3>Comments</h3>
    <?php if($current_user): ?>
      <textarea id="commentText" placeholder="Write a comment..." rows="3"></textarea>
      <button onclick="commentVideo()">Comment</button>
    <?php else: ?>
      <p>Please login to comment.</p>
    <?php endif; ?>

    <div id="commentSection">
      <?php while($c=$comments->fetch_assoc()): ?>
        <div class="comment-box" id="comment-<?=$c['id']?>">
          <strong><?=htmlspecialchars($c['username'])?></strong>
          <p><?=htmlspecialchars($c['comment'])?></p>
          <button onclick="reactComment(<?=$c['id']?>,'like')">👍 <span id="commentLike-<?=$c['id']?>"><?=$c['likes']?></span></button>
          <button onclick="reactComment(<?=$c['id']?>,'dislike')">👎 <span id="commentDislike-<?=$c['id']?>"><?=$c['dislikes']?></span></button>
          <?php if($current_user == $c['user_id']): ?>
            <button onclick="deleteComment(<?=$c['id']?>)">Delete</button>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    </div>

  </div>

  <!-- SUGGESTIONS -->
  <aside class="suggestions">
    <h3>Suggested</h3>
    <?php while($s=$suggest->fetch_assoc()): ?>
      <a class="suggestion-card" href="watch.php?id=<?=$s['id']?>">
        <img src="../app/uploads/thumbnails/<?=htmlspecialchars($s['thumbnail'] ?: 'default.png')?>">
        <div class="suggestion-info">
          <strong><?=htmlspecialchars($s['title'])?></strong>
          <p><?=$s['views']?> views</p>
        </div>
      </a>
    <?php endwhile; ?>
  </aside>
</div>

<script src="assets/js/watch.js"></script>
<?php include "assets/includes/footer.php"; ?>
</body>
</html>