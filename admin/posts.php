
<?php
require_once __DIR__ . '/../includes/auth_check.php';

if (is_admin()) {
    $stmt = $pdo->query(
        'SELECT p.*, u.name AS author, c.name AS category
         FROM posts p
         JOIN users u ON p.user_id = u.id
         LEFT JOIN categories c ON p.category_id = c.id
         ORDER BY p.created_at DESC'
    );
} else {
    $stmt = $pdo->prepare(
        'SELECT p.*, u.name AS author, c.name AS category
         FROM posts p
         JOIN users u ON p.user_id = u.id
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE p.user_id = :uid
         ORDER BY p.created_at DESC'
    );
    $stmt->execute(['uid' => $_SESSION['user_id']]);
}

$posts = $stmt->fetchAll();

$pageTitle = 'Manage Posts';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Manage Posts</h2>
    <a href="post_form.php" class="btn btn-primary">Add New Post</a>
</div>

<div class="table-responsive">
<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Category</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($posts as $post): ?>
        <tr>
            <td><?= e($post['title']) ?></td>
            <td><?= e($post['author']) ?></td>
            <td><?= e($post['category'] ?? 'Uncategorized') ?></td>
            <td><?= e($post['status']) ?></td>
            <td class="d-flex gap-2">
                <a class="btn btn-sm btn-warning"
                   href="post_form.php?id=<?= (int)$post['id'] ?>">
                    Edit
                </a>

                <form method="POST" action="delete_post.php"
                      onsubmit="return confirm('Delete this post?')">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id"
                           value="<?= (int)$post['id'] ?>">
                    <button class="btn btn-sm btn-danger">
                        Delete
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>

    <?php if (!$posts): ?>
        <tr><td colspan="5">No posts found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
