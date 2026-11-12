
<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$search = trim($_GET['search'] ?? '');

$sql = "SELECT p.id, p.title, p.slug, p.content, p.image,
               p.created_at, u.name AS author, c.name AS category
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'";

$params = [];

if ($search !== '') {
    $sql .= ' AND p.title LIKE :search';
    $params['search'] = '%' . $search . '%';
}

$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$pageTitle = 'Latest Blog Posts';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero-section mb-4">
    <h1>Welcome to MyBlog</h1>
    <p>Explore articles about technology, programming and web development.</p>
</section>

<form method="GET" class="search-form mb-4">
    <input type="text" name="search"
           class="form-control"
           placeholder="Search articles..."
           value="<?= e($search) ?>">
    <button class="btn btn-primary">Search</button>
</form>

<div class="row g-4">

<?php foreach ($posts as $post): ?>
    <div class="col-md-6 col-lg-4">
        <article class="blog-card">

            <?php if ($post['image']): ?>
                <img
                    src="/blog-management/uploads/<?= e(basename($post['image'])) ?>"
                    class="blog-image"
                    alt="<?= e($post['title']) ?>">
            <?php endif; ?>

            <div class="p-3">
                <span class="badge bg-primary">
                    <?= e($post['category'] ?? 'General') ?>
                </span>

                <h4 class="mt-2"><?= e($post['title']) ?></h4>

                <p class="text-muted">
                    By <?= e($post['author']) ?>
                    · <?= date('d M Y', strtotime($post['created_at'])) ?>
                </p>

                <p>
                    <?= e(mb_strimwidth(
                        strip_tags($post['content']),
                        0,
                        130,
                        '...'
                    )) ?>
                </p>

                <a href="/blog-management/blog/details.php?slug=<?= urlencode($post['slug']) ?>"
                   class="btn btn-outline-primary">
                    Read More
                </a>
            </div>
        </article>
    </div>
<?php endforeach; ?>

<?php if (!$posts): ?>
    <div class="col-12">
        <div class="alert alert-info">
            No published articles found.
        </div>
    </div>
<?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
