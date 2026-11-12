
<?php
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';

    if (!$id || !in_array($status, ['approved', 'rejected'], true)) {
        set_flash('Invalid comment action.', 'warning');
        redirect('comments.php');
    }

    if (is_admin()) {
        $stmt = $pdo->prepare(
            'UPDATE comments SET status = :status WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $id]);
    } else {
        $stmt = $pdo->prepare(
            'UPDATE comments c
             JOIN posts p ON c.post_id = p.id
             SET c.status = :status
             WHERE c.id = :id AND p.user_id = :uid'
        );
        $stmt->execute([
            'status' => $status,
            'id' => $id,
            'uid' => $_SESSION['user_id']
        ]);
    }

    set_flash('Comment status updated.');
    redirect('comments.php');
}

if (is_admin()) {
    $stmt = $pdo->query(
        'SELECT c.*, p.title
         FROM comments c
         JOIN posts p ON c.post_id = p.id
         ORDER BY c.created_at DESC'
    );
} else {
    $stmt = $pdo->prepare(
        'SELECT c.*, p.title
         FROM comments c
         JOIN posts p ON c.post_id = p.id
         WHERE p.user_id = :uid
         ORDER BY c.created_at DESC'
    );
    $stmt->execute(['uid' => $_SESSION['user_id']]);
}

$comments = $stmt->fetchAll();

$pageTitle = 'Manage Comments';
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Manage Comments</h2>

<div class="table-responsive">
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Post</th>
            <th>Commenter</th>
            <th>Comment</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($comments as $comment): ?>
        <tr>
            <td><?= e($comment['title']) ?></td>
            <td><?= e($comment['name']) ?></td>
            <td><?= e($comment['comment']) ?></td>
            <td><?= e($comment['status']) ?></td>
            <td>
                <form method="POST" class="d-flex gap-2">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id"
                           value="<?= (int)$comment['id'] ?>">

                    <select name="status" class="form-select">
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                    </select>

                    <button class="btn btn-primary btn-sm">Save</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>

    <?php if (!$comments): ?>
        <tr><td colspan="5">No comments found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
