<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /dev-blog/auth/login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

$pdo = require __DIR__ . '/config/database.php';

$postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$postId) {
    $postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

if (!$postId) {
    require __DIR__ . '/includes/header.php';
    ?>
    <main>
        <section class="feed-wrapper">
            <h1>404 - Post Not Found</h1>
            <p>The blog post you requested does not exist.</p>
        </section>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare(
    'SELECT
        id AS post_id,
        user_id
    FROM blogPosts
    WHERE id = :id
    LIMIT 1'
);
$stmt->execute([':id' => $postId]);
$post = $stmt->fetch();

if (!$post) {
    require __DIR__ . '/includes/header.php';
    ?>
    <main>
        <section class="feed-wrapper">
            <h1>404 - Post Not Found</h1>
            <p>The blog post you requested does not exist.</p>
        </section>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

if ((int) $_SESSION['user_id'] !== (int) $post['user_id']) {
    http_response_code(403);
    require __DIR__ . '/includes/header.php';
    ?>
    <main>
        <section class="feed-wrapper">
            <h1>403 - Unauthorized</h1>
            <p>You do not have permission to delete this post.</p>
        </section>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$deleteStmt = $pdo->prepare('DELETE FROM blogPosts WHERE id = :id');
$deleteStmt->execute([':id' => $postId]);

header('Location: /dev-blog/index.php?deleted=1');
exit;
