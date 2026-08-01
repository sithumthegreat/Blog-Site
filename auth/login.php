<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, role, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: /dev-blog/index.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}
?>

<main>
    <section class="auth-section">
        <span class="auth-eyebrow">Member access</span>
        <h1>Welcome back</h1>
        <p class="auth-subtitle">Sign in to keep reading and writing.</p>

        <?php if ($error !== ''): ?>
            <p class="form-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php" class="auth-card">
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required autofocus>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit">Log in</button>
        </form>

        <p class="auth-footer">
            New here? <a href="register.php">Create an account</a>
        </p>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>