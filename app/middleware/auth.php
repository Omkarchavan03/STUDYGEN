<?php
session_start();

/*
  Access rules:
  - Logged-in user → user_id is set
  - Guest user → no user_id, but allowed
*/

// Mark guest if not logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION["is_guest"] = true;
} else {
    $_SESSION["is_guest"] = false;
}
