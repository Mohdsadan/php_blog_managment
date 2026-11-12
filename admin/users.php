
<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
    $role = $_POST['role'] ?? '';

    if (!$id || !in_array($role, ['admin', 'author'], true)) {
        $error = 'Invalid user or role.';
    } elseif ($id === (int)$_SESSION['user_id']) {
        $error = 'You cannot change your own role here.';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE users SET role = :role WHERE id = :id'
        );
        $stmt->execute(['role' => $role, 'id' => $id]);

        set_flash('User role updated.');
        redirect('users.php');
    }
}

$users = $pdo->query(
    'SELECT id, name, email, role, created_at
     FROM users ORDER BY created_at DESC'
)->fetchAll();

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Manage Users</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="table-responsive">
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Change Role</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= e($user['name']) ?></td>
            <td><?= e($user['email']) ?></td>
            <td><?= e($user['role']) ?></td>
            <td>
                <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                    <form method="POST" class="d-flex gap-2">
                        <input type="hidden" name="csrf_token"
                               value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id"
                               value="<?= (int)$user['id'] ?>">

                        <select name="role" class="form-select">
                            <option value="author"
                                <?= $user['role'] === 'author' ? 'selected' : '' ?>>
                                Author
                            </option>
                            <option value="admin"
                                <?= $user['role'] === 'admin' ? 'selected' : '' ?>>
                                Admin
                            </option>
                        </select>

                        <button class="btn btn-primary btn-sm">
                            Update
                        </button>
                    </form>
                <?php else: ?>
                    Current account
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
