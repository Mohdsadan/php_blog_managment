
<?php
require_once __DIR__ . '/../includes/auth_check.php';

$totalPosts = $pdo->query(
    'SELECT COUNT(*) FROM posts'
)->fetchColumn();

$publishedPosts = $pdo->query(
    "SELECT COUNT(*) FROM posts WHERE status = 'published'"
)->fetchColumn();

$draftPosts = $pdo->query(
    "SELECT COUNT(*) FROM posts WHERE status = 'draft'"
)->fetchColumn();

$totalComments = $pdo->query(
    'SELECT COUNT(*) FROM comments'
)->fetchColumn();

if (!is_admin()) {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM posts WHERE user_id = :uid'
    );
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $totalPosts = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM posts
         WHERE user_id = :uid AND status = 'published'"
    );
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $publishedPosts = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM posts
         WHERE user_id = :uid AND status = 'draft'"
    );
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $draftPosts = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM comments c
         JOIN posts p ON c.post_id = p.id
         WHERE p.user_id = :uid'
    );
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $totalComments = $stmt->fetchColumn();
}

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Welcome, <?= e($_SESSION['user_name']) ?>!</h2>
<p class="text-muted">Your blog management dashboard.</p>

<div class="row g-3 my-3">

    <div class="col-md-3">
        <div class="stat-card">
            <h6>Total Posts</h6>
            <h2><?= (int)$totalPosts ?></h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <h6>Published</h6>
            <h2><?= (int)$publishedPosts ?></h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <h6>Drafts</h6>
            <h2><?= (int)$draftPosts ?></h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <h6>Comments</h6>
            <h2><?= (int)$totalComments ?></h2>
        </div>
    </div>

</div>

<div class="d-flex flex-wrap gap-2">
    <a href="posts.php" class="btn btn-primary">Manage Posts</a>
    <a href="post_form.php" class="btn btn-success">Create Post</a>
    <a href="comments.php" class="btn btn-outline-dark">Manage Comments</a>

    <?php if (is_admin()): ?>
        <a href="categories.php" class="btn btn-outline-primary">Categories</a>
        <a href="users.php" class="btn btn-outline-secondary">Users</a>
    <?php endif; ?>

    <a href="/blog-management/" class="btn btn-dark">View Blog</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
