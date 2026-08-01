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
    <section>
        <h1>Register</h1>

        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Register</button>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>