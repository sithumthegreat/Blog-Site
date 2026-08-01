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
        id AS post_id,
        title,
        content,
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
            <p>You do not have permission to edit this post.</p>
        </section>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title !== '' && $content !== '') {
        $updateStmt = $pdo->prepare(
            'UPDATE blogPosts
            SET title = :title,
                content = :content
            WHERE id = :id'
        );

        $updateStmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':id' => $postId,
        ]);

        header('Location: /dev-blog/view.php?id=' . (int) $postId);
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<main>
    <section class="feed-wrapper">
        <h1>Edit Post</h1>

        <form method="POST" action="/dev-blog/edit.php?id=<?php echo (int) $postId; ?>">
            <div>
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>

            <div>
                <label for="content">Content</label>
                <p>Use Markdown syntax in the content area, such as headings, lists, bold text, and links.</p>
                <textarea id="content" name="content" rows="12" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>

            <button type="submit">Update Post</button>
        </form>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>