
<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    redirect('/blog-management/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || mb_strlen($name) > 100) {
        $error = 'Enter a valid name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } else {
        $check = $pdo->prepare(
            'SELECT id FROM users WHERE email = :email'
        );
        $check->execute(['email' => $email]);

        if ($check->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (:name, :email, :password, 'author')"
            );

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hash
            ]);

            set_flash('Registration successful. Please login.');
            redirect('/blog-management/auth/login.php');
        }
    }
}

$pageTitle = 'Author Registration';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-box">
    <h2>Create Author Account</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">

        <label class="form-label">Full Name</label>
        <input class="form-control mb-3"
               type="text" name="name"
               maxlength="100" required>

        <label class="form-label">Email</label>
        <input class="form-control mb-3"
               type="email" name="email" required>

        <label class="form-label">Password</label>
        <input class="form-control mb-3"
               type="password" name="password"
               minlength="8" required>

        <button class="btn btn-primary w-100">
            Register
        </button>
    </form>

    <p class="mt-3">
        Already registered?
        <a href="login.php">Login</a>
    </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
