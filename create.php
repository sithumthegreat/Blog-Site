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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title !== '' && $content !== '') {
        $stmt = $pdo->prepare(
            'INSERT INTO blogPosts (title, content, user_id) VALUES (:title, :content, :user_id)'
        );

        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':user_id' => (int) $_SESSION['user_id'],
        ]);

        header('Location: /dev-blog/index.php');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<main>
    <section class="feed-wrapper">
        <h1>Create a Post</h1>

        <form method="POST" action="/dev-blog/create.php">
            <div>
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div>
                <label for="content">Content</label>
                <p>Use Markdown syntax in the content area, such as headings, lists, bold text, and links.</p>
                <textarea id="content" name="content" rows="12" required></textarea>
            </div>

            <button type="submit">Publish Post</button>
        </form>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>