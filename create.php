<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$pdo = require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/upload.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $coverImagePath = null;

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $coverImagePath = uploadCoverImage($_FILES['cover_image']);
    }

    if ($title !== '' && $content !== '') {
        $stmt = $pdo->prepare(
            'INSERT INTO blogposts (title, cover_image, content, user_id) VALUES (:title, :cover_image, :content, :user_id)'
        );

        $stmt->execute([
            ':title' => $title,
            ':cover_image' => $coverImagePath,
            ':content' => $content,
            ':user_id' => (int) $_SESSION['user_id'],
        ]);

        header('Location: index.php');
        exit;
    } else {
        $error = 'Title and content are required.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main>
    <section class="feed-wrapper">
        <h1>Create a Post</h1>

        <?php if ($error !== ''): ?>
            <p class="form-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Relative action prevents subfolder 404 errors on localhost -->
        <form method="POST" action="create.php" enctype="multipart/form-data">
            <div>
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div>
                <label for="cover_image">Cover Image (Optional - JPG, PNG, WEBP, max 2MB)</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>

            <div>
                <label for="content">Content</label>
                
                <!-- Text Styling Toolbar Helper -->
                <div class="editor-toolbar" style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                    <button type="button" onclick="insertSyntax('**', '**')"><b>B</b></button>
                    <button type="button" onclick="insertSyntax('*', '*')"><i>I</i></button>
                    <button type="button" onclick="insertSyntax('### ', '')">H3</button>
                    <button type="button" onclick="insertSyntax('```\n', '\n```')">Code</button>
                    <button type="button" onclick="insertSyntax('> ', '')">Quote</button>
                </div>

                <textarea id="content" name="content" rows="12" required></textarea>
            </div>

            <button type="submit">Publish Post</button>
        </form>
    </section>
</main>

<script>
function insertSyntax(before, after) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    const replacement = before + (selectedText || 'text') + after;

    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + before.length, end + before.length);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>