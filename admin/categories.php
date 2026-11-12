
<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');

        if ($name === '' || mb_strlen($name) > 100) {
            $error = 'Enter a category name under 100 characters.';
        } else {
            $slug = make_slug($name);

            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO categories (name, slug)
                     VALUES (:name, :slug)'
                );
                $stmt->execute([
                    'name' => $name,
                    'slug' => $slug
                ]);
                set_flash('Category added.');
                redirect('categories.php');
            } catch (PDOException $e) {
                $error = 'Category already exists or could not be saved.';
            }
        }
    }

    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

        if ($id) {
            $stmt = $pdo->prepare(
                'DELETE FROM categories WHERE id = :id'
            );
            $stmt->execute(['id' => $id]);
            set_flash('Category deleted.');
            redirect('categories.php');
        }
    }
}

$categories = $pdo->query(
    'SELECT * FROM categories ORDER BY name'
)->fetchAll();

$pageTitle = 'Categories';
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Manage Categories</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" class="form-box">
    <input type="hidden" name="csrf_token"
           value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="add">

    <label class="form-label">Category Name</label>
    <input type="text" name="name"
           class="form-control mb-3" required maxlength="100">

    <button class="btn btn-primary">Add Category</button>
</form>

<table class="table table-bordered mt-4">
    <thead>
        <tr><th>Name</th><th>Slug</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $category): ?>
        <tr>
            <td><?= e($category['name']) ?></td>
            <td><?= e($category['slug']) ?></td>
            <td>
                <form method="POST"
                      onsubmit="return confirm('Delete category?')">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id"
                           value="<?= (int)$category['id'] ?>">
                    <button class="btn btn-danger btn-sm">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
