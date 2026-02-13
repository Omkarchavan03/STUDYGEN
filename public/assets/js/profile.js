function toggleFollow(userId) {
  fetch("follow.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: "user_id=" + userId
  }).then(() => location.reload());
}
