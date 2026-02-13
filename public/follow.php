<?php
require_once __DIR__ . "/../app/config/db.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Login required");
}

$followerId  = $_SESSION['user_id'];
$followingId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($followingId <= 0 || $followingId === $followerId) {
    exit("Invalid request");
}

/* CHECK IF ALREADY FOLLOWING */
$check = $conn->prepare(
    "SELECT id FROM followers 
     WHERE follower_id = ? AND following_id = ?"
);
$check->bind_param("ii", $followerId, $followingId);
$check->execute();
$isFollowing = $check->get_result()->num_rows > 0;

if ($isFollowing) {
    // UNFOLLOW
    $stmt = $conn->prepare(
        "DELETE FROM followers 
         WHERE follower_id = ? AND following_id = ?"
    );
    $stmt->bind_param("ii", $followerId, $followingId);
    $stmt->execute();
    echo "unfollowed";
} else {
    // FOLLOW
    $stmt = $conn->prepare(
        "INSERT INTO followers (follower_id, following_id)
         VALUES (?, ?)"
    );
    $stmt->bind_param("ii", $followerId, $followingId);
    $stmt->execute();
    echo "followed";
}
