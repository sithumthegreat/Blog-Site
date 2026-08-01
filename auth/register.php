<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$success = '';
//success is for sussess messages and errors is for if user tyoes something wrong
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') {
        $errors[] = 'Username is required.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
        $checkStmt->execute([
            ':username' => $username,
            ':email' => $email,
        ]);

        if ($checkStmt->fetch()) {
            $errors[] = 'Username or email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insertStmt = $pdo->prepare(
                'INSERT INTO users (username, email, password) VALUES (:username, :email, :password)'
            );

            $insertStmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword,
            ]);

            header('Location: login.php');
            exit;
        }
    }
}
?>

<main>
    <section class="auth-section">
        <span class="auth-eyebrow">Join the blog</span>
        <h1>Create your account</h1>
        <p class="auth-subtitle">Pick a username, verify your email, and start writing.</p>

        <?php if (!empty($errors)): ?>
            <div class="form-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="auth-card">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="jdoe" required autofocus>
            </div>

            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit">Create account</button>
        </form>

        <p class="auth-footer">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>