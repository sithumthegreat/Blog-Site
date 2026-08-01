<?php
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = require __DIR__ . '/config/database.php';

$postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

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
        bp.id AS post_id,
        bp.title,
        bp.content,
        bp.created_at,
        u.username
    FROM blogPosts bp
    INNER JOIN users u ON bp.user_id = u.id
    WHERE bp.id = :id
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

require __DIR__ . '/includes/header.php';
?>

<main>
    <section class="feed-wrapper">
        <article class="post-card post-detail">
            <div class="post-meta">
                <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                <span><?php echo htmlspecialchars(date('M d, Y', strtotime($post['created_at']))); ?></span>
            </div>

            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div id="post-content" class="markdown-body"></div>
        </article>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    const markdownContent = <?php echo json_encode($post['content'] ?? ''); ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('post-content');

        if (!container) {
            return;
        }

        if (typeof marked !== 'undefined') {
            container.innerHTML = marked.parse(markdownContent);
        } else {
            container.textContent = markdownContent;
        }
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>