<?php

$pdo = require_once __DIR__ . '/config/database.php';

// 2. Ensure session is initialized
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$stmt = $pdo->query(
    'SELECT
        bp.id AS post_id,
        bp.user_id,
        bp.title,
        bp.content,
        bp.cover_image,
        bp.created_at,
        u.username
    FROM blogPosts bp
    INNER JOIN users u ON bp.user_id = u.id
    ORDER BY bp.created_at DESC'
);

$posts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<main>
    <section class="feed-wrapper">
        <h1>Latest Posts</h1>

        <?php if (!empty($posts)): ?>
            <div class="post-grid">
                <?php foreach ($posts as $post): ?>
                    <?php
                    $preview = trim(strip_tags($post['content'] ?? ''));

                    if (strlen($preview) > 160) {
                        $preview = substr($preview, 0, 157) . '...';
                    }
                    ?>
                    <article class="post-card">
                        <?php if (!empty($post['cover_image'])): ?>
                            <div class="post-cover-image" style="width: 100%; height: 180px; overflow: hidden; border-radius: 8px;">
                                <img src="<?php echo htmlspecialchars($post['cover_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php endif; ?>

                        <div class="post-meta">
                            <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                            <span><?php echo htmlspecialchars(date('M d, Y', strtotime($post['created_at']))); ?></span>
                        </div>

                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                        <p><?php echo htmlspecialchars($preview); ?></p>

                        <div class="card-actions">
                            <a href="view.php?id=<?php echo (int) $post['post_id']; ?>" class="btn-read">Read More</a>
                            
                            <!-- Check if logged-in user owns this post -->
                            <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$post['user_id']): ?>
                                <a href="edit.php?id=<?php echo (int) $post['post_id']; ?>" class="btn-edit">Edit</a>
                                <a href="delete.php?id=<?php echo (int) $post['post_id']; ?>" class="btn-delete">Delete</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No posts available yet.</p>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>