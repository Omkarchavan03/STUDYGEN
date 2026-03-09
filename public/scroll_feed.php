<?php

require "../app/config/db.php";

/* ===== GET PARAMETERS ===== */

$type = $_GET['type'] ?? "econtent";
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$limit = 5;

/* ===== ALLOW ONLY VALID TABLES ===== */

$allowed_types = ["econtent","videos"];

if(!in_array($type,$allowed_types)){
    $type = "econtent";
}

/* ===== BUILD QUERY ===== */

if($last_id > 0){

    $sql = "
    SELECT *
    FROM $type
    WHERE id < $last_id
    ORDER BY id DESC
    LIMIT $limit
    ";

}else{

    $sql = "
    SELECT *
    FROM $type
    ORDER BY id DESC
    LIMIT $limit
    ";

}

$result = mysqli_query($conn,$sql);

/* ===== OUTPUT POSTS ===== */

while($row = mysqli_fetch_assoc($result)){

echo '<div class="post" data-id="'.$row['id'].'">';

echo '<div class="post-content">';

/* ===== VIDEO POSTS ===== */

if($type === "videos"){

    $video = htmlspecialchars($row['video_path']);

    echo '
    <video controls>
        <source src="'.$video.'" type="video/mp4">
    </video>
    ';

/* ===== ECONTENT POSTS ===== */

}else{

    if(!empty($row['file_path'])){

        $file = "../".$row['file_path'];
        $ext = strtolower(pathinfo($file,PATHINFO_EXTENSION));

        if(in_array($ext,['jpg','jpeg','png','gif'])){

            echo '<img src="'.$file.'">';

        }elseif($ext === "pdf" || $ext === "txt"){

            echo '<iframe src="'.$file.'"></iframe>';

        }else{

            echo '
            <div class="post-file">
                <a href="'.$file.'" download>
                📄 '.basename($row['file_path']).'
                </a>
            </div>
            ';

        }

    }else{

        echo htmlspecialchars($row['content'] ?? '');

    }

}

echo '</div>';

/* ===== POST ACTIONS ===== */

echo '
<div class="post-actions">

<i class="fa-regular fa-thumbs-up"></i>
<span>0</span>

<i class="fa-regular fa-thumbs-down"></i>
<span>0</span>

<i class="fa-regular fa-comment-dots"></i>

</div>
';

/* ===== COMMENT AREA ===== */

echo '<div class="comments" id="comments-'.$row['id'].'" style="display:none;"></div>';

echo '</div>';

}