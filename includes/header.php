<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Blog</title>
    <link rel="stylesheet" href="/dev-blog/assets/css/style.css">
</head>
<body>
    <nav aria-label="Primary navigation">
        <ul>
            <li><a href="/dev-blog/index.php">Home</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="/dev-blog/create.php">Create Post</a></li>
                <li><a href="/dev-blog/auth/logout.php">Logout (Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>)</a></li>
            <?php else: ?>
                <li><a href="/dev-blog/auth/login.php">Login</a></li>
                <li><a href="/dev-blog/auth/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
