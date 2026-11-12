
<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    redirect('/blog-management/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, name, email, password, role
         FROM users WHERE email = :email'
    );
    $stmt->execute(['email' => $email]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        set_flash('Welcome back, ' . $user['name'] . '!');
        redirect('/blog-management/admin/dashboard.php');
    } else {
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-box">
    <h2>Login</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">

        <label class="form-label">Email</label>
        <input class="form-control mb-3"
               type="email" name="email" required>

        <label class="form-label">Password</label>
        <input class="form-control mb-3"
               type="password" name="password" required>

        <button class="btn btn-primary w-100">
            Login
        </button>
    </form>

    <p class="mt-3">
        New author?
        <a href="register.php">Register here</a>
    </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
