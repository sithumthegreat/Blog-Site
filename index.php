<?php
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = require __DIR__ . '/config/database.php';

$stmt = $pdo->query(
    'SELECT
        bp.id AS post_id,
        bp.title,
        bp.excerpt,
        bp.created_at,
        u.username
    FROM blogPosts bp
    INNER JOIN users u ON bp.user_id = u.id
    ORDER BY bp.created_at DESC'
);

$posts = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<main>
    <section class="feed-wrapper">
        <h1>Latest Posts</h1>

        <?php if (!empty($posts)): ?>
            <div class="post-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <div class="post-meta">
                            <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                            <span><?php echo htmlspecialchars(date('M d, Y', strtotime($post['created_at']))); ?></span>
                        </div>

                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>

                        <a href="/dev-blog/view.php?id=<?php echo (int) $post['post_id']; ?>">Read More</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No posts available yet.</p>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
