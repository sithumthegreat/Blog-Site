<?php
// 1. Single database inclusion
$pdo = require_once __DIR__ . '/config/database.php';

// 2. Ensure session is initialized
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Helper function to render a clean 404 page
function render404() {
    require_once __DIR__ . '/includes/header.php';
    ?>
    <main>
        <section class="feed-wrapper">
            <h1>404 - Post Not Found</h1>
            <p>The blog post you requested does not exist.</p>
            <a href="index.php" class="btn">Return Home</a>
        </section>
    </main>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

if (!$postId) {
    render404();
}

// 3. Fetch post details INCLUDING bp.user_id
$stmt = $pdo->prepare(
    'SELECT
        bp.id AS post_id,
        bp.user_id,
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
    render404();
}

require_once __DIR__ . '/includes/header.php';
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

            <!-- Action buttons for the post owner -->
            <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$post['user_id']): ?>
                <div class="post-actions" style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <a href="edit.php?id=<?php echo (int) $post['post_id']; ?>" class="btn btn-edit">Edit Post</a>
                    <a href="delete.php?id=<?php echo (int) $post['post_id']; ?>" class="btn btn-delete">Delete Post</a>
                </div>
            <?php endif; ?>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>