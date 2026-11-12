
<?php
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('Invalid post ID.', 'warning');
    redirect('/blog-management/admin/posts.php');
}

if (is_admin()) {
    $stmt = $pdo->prepare(
        'SELECT id, image FROM posts WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
} else {
    $stmt = $pdo->prepare(
        'SELECT id, image FROM posts
         WHERE id = :id AND user_id = :uid'
    );
    $stmt->execute([
        'id' => $id,
        'uid' => $_SESSION['user_id']
    ]);
}

$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    exit('Post not found or access denied.');
}

$delete = $pdo->prepare('DELETE FROM posts WHERE id = :id');
$delete->execute(['id' => $id]);

if ($post['image']) {
    $file = __DIR__ . '/../uploads/' . basename($post['image']);
    if (is_file($file)) {
        unlink($file);
    }
}

set_flash('Post deleted successfully.');
redirect('/blog-management/admin/posts.php');
