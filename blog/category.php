
<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$slug = trim($_GET['slug'] ?? '');

$stmt = $pdo->prepare(
    'SELECT id, name FROM categories WHERE slug = :slug'
);
$stmt->execute(['slug' => $slug]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    exit('Category not found.');
}

$stmt = $pdo->prepare(
    "SELECT p.id, p.title, p.slug, p.content, p.image,
            p.created_at, u.name AS author
     FROM posts p
     JOIN users u ON p.user_id = u.id
     WHERE p.category_id = :category
       AND p.status = 'published'
     ORDER BY p.created_at DESC"
);
$stmt->execute(['category' => $category['id']]);
$posts = $stmt->fetchAll();

$pageTitle = $category['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<h2>Category: <?= e($category['name']) ?></h2>

<div class="row g-4 mt-2">
    <?php foreach ($posts as $post): ?>
        <div class="col-md-6 col-lg-4">
            <div class="blog-card p-3">
                <?php if ($post['image']): ?>
                    <img
                        src="/blog-management/uploads/<?= e(basename($post['image'])) ?>"
                        class="blog-image"
                        alt="<?= e($post['title']) ?>">
                <?php endif; ?>

                <h4 class="mt-3"><?= e($post['title']) ?></h4>
                <p class="text-muted">By <?= e($post['author']) ?></p>

                <p>
                    <?= e(mb_strimwidth(
                        strip_tags($post['content']), 0, 120, '...'
                    )) ?>
                </p>

                <a class="btn btn-outline-primary"
                   href="/blog-management/blog/details.php?slug=<?= urlencode($post['slug']) ?>">
                    Read More
                </a>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (!$posts): ?>
        <div class="col-12">
            <p>No published posts in this category.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
