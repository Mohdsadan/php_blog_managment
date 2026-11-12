
<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$slug = trim($_GET['slug'] ?? '');

$stmt = $pdo->prepare(
    "SELECT p.*, u.name AS author, c.name AS category
     FROM posts p
     JOIN users u ON p.user_id = u.id
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.slug = :slug AND p.status = 'published'"
);

$stmt->execute(['slug' => $slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    exit('Blog post not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if ($name === '' || mb_strlen($name) > 100) {
        $error = 'Enter a valid name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email.';
    } elseif ($comment === '' || mb_strlen($comment) > 3000) {
        $error = 'Comment must be between 1 and 3000 characters.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO comments
             (post_id, user_id, name, email, comment, status)
             VALUES (:post, :user, :name, :email, :comment, 'pending')"
        );

        $stmt->execute([
            'post' => $post['id'],
            'user' => $_SESSION['user_id'] ?? null,
            'name' => $name,
            'email' => $email,
            'comment' => $comment
        ]);

        set_flash('Comment submitted. It will appear after approval.');
        redirect('/blog-management/blog/details.php?slug=' . urlencode($slug));
    }
}

$stmt = $pdo->prepare(
    "SELECT name, comment, created_at
     FROM comments
     WHERE post_id = :post AND status = 'approved'
     ORDER BY created_at DESC"
);
$stmt->execute(['post' => $post['id']]);
$comments = $stmt->fetchAll();

$pageTitle = $post['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<article class="article-content">

    <span class="badge bg-primary">
        <?= e($post['category'] ?? 'General') ?>
    </span>

    <h1 class="mt-3"><?= e($post['title']) ?></h1>

    <p class="text-muted">
        By <?= e($post['author']) ?>
        · <?= date('d M Y', strtotime($post['created_at'])) ?>
    </p>

    <?php if ($post['image']): ?>
        <img
            src="/blog-management/uploads/<?= e(basename($post['image'])) ?>"
            class="article-image"
            alt="<?= e($post['title']) ?>">
    <?php endif; ?>

    <div class="article-body">
        <?= nl2br(e($post['content'])) ?>
    </div>
</article>

<hr class="my-5">

<h3>Comments (<?= count($comments) ?>)</h3>

<?php foreach ($comments as $comment): ?>
    <div class="comment-box mb-3">
        <strong><?= e($comment['name']) ?></strong>
        <small class="text-muted">
            <?= date('d M Y', strtotime($comment['created_at'])) ?>
        </small>
        <p class="mb-0"><?= nl2br(e($comment['comment'])) ?></p>
    </div>
<?php endforeach; ?>

<?php if (!$comments): ?>
    <p>No approved comments yet. Be the first to comment.</p>
<?php endif; ?>

<h3 class="mt-5">Leave a Comment</h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" class="form-box wide-form">
    <input type="hidden" name="csrf_token"
           value="<?= e(csrf_token()) ?>">

    <label class="form-label">Name</label>
    <input class="form-control mb-3"
           type="text" name="name" maxlength="100"
           value="<?= e($_SESSION['user_name'] ?? '') ?>" required>

    <label class="form-label">Email</label>
    <input class="form-control mb-3"
           type="email" name="email" required>

    <label class="form-label">Comment</label>
    <textarea class="form-control mb-3"
              name="comment" rows="4"
              maxlength="3000" required></textarea>

    <button class="btn btn-primary">Submit Comment</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
