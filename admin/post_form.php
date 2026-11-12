
<?php
require_once __DIR__ . '/../includes/auth_check.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$post = null;
$error = '';

if ($id) {
    $stmt = $pdo->prepare(
        'SELECT * FROM posts WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch();

    if (!$post) {
        http_response_code(404);
        exit('Post not found.');
    }

    if (!is_admin() && (int)$post['user_id'] !== (int)$_SESSION['user_id']) {
        http_response_code(403);
        exit('Access denied.');
    }
}

$categories = $pdo->query(
    'SELECT id, name FROM categories ORDER BY name'
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = $_POST['category_id'] ?? '';
    $status = $_POST['status'] ?? 'draft';

    if ($title === '' || mb_strlen($title) > 200) {
        $error = 'Title is required and must be under 200 characters.';
    } elseif ($content === '') {
        $error = 'Content is required.';
    } elseif (!in_array($status, ['draft', 'published'], true)) {
        $error = 'Invalid post status.';
    } else {
        $categoryId = $categoryId === '' ? null : filter_var(
            $categoryId,
            FILTER_VALIDATE_INT
        );

        if ($categoryId !== null && !$categoryId) {
            $error = 'Invalid category.';
        } elseif ($categoryId !== null) {
            $check = $pdo->prepare(
                'SELECT id FROM categories WHERE id = :id'
            );
            $check->execute(['id' => $categoryId]);

            if (!$check->fetch()) {
                $error = 'Selected category does not exist.';
            }
        }
    }

    $imageName = $post['image'] ?? null;

    if (!$error && isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Image upload failed.';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $error = 'Image must be smaller than 2 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['image']['tmp_name']);

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($allowed[$mime])) {
                $error = 'Only JPG, PNG and WEBP images are allowed.';
            } else {
                $imageName = bin2hex(random_bytes(16))
                    . '.' . $allowed[$mime];

                $uploadDir = __DIR__ . '/../uploads/';

                if (!is_dir($uploadDir) ||
                    !move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $uploadDir . $imageName
                    )) {
                    $error = 'Could not save the uploaded image.';
                }
            }
        }
    }

    if (!$error) {
        $slug = make_slug($title) . '-' . bin2hex(random_bytes(4));

        if ($post) {
            $stmt = $pdo->prepare(
                'UPDATE posts
                 SET category_id = :category,
                     title = :title,
                     slug = :slug,
                     content = :content,
                     image = :image,
                     status = :status
                 WHERE id = :id'
            );

            $stmt->execute([
                'category' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'image' => $imageName,
                'status' => $status,
                'id' => $id
            ]);

            if ($imageName && $imageName !== $post['image']
                && $post['image']) {
                $old = __DIR__ . '/../uploads/' . basename($post['image']);
                if (is_file($old)) {
                    unlink($old);
                }
            }

            set_flash('Post updated successfully.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO posts
                 (user_id, category_id, title, slug, content, image, status)
                 VALUES
                 (:uid, :category, :title, :slug, :content, :image, :status)'
            );

            $stmt->execute([
                'uid' => $_SESSION['user_id'],
                'category' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'image' => $imageName,
                'status' => $status
            ]);

            set_flash('Post created successfully.');
        }

        redirect('/blog-management/admin/posts.php');
    }
}

$pageTitle = $post ? 'Edit Post' : 'Create Post';
require_once __DIR__ . '/../includes/header.php';
?>

<h2><?= $post ? 'Edit Post' : 'Create New Post' ?></h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data"
      class="form-box wide-form">

    <input type="hidden" name="csrf_token"
           value="<?= e(csrf_token()) ?>">

    <label class="form-label">Post Title</label>
    <input class="form-control mb-3"
           type="text" name="title" maxlength="200"
           value="<?= e($post['title'] ?? '') ?>" required>

    <label class="form-label">Category</label>
    <select class="form-select mb-3" name="category_id">
        <option value="">Select Category</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= (int)$category['id'] ?>"
                <?= (int)($post['category_id'] ?? 0) ===
                    (int)$category['id'] ? 'selected' : '' ?>>
                <?= e($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label class="form-label">Content</label>
    <textarea class="form-control mb-3"
              name="content" rows="10" required><?= e($post['content'] ?? '') ?></textarea>

    <label class="form-label">Featured Image</label>
    <input class="form-control mb-3"
           type="file" name="image"
           accept=".jpg,.jpeg,.png,.webp">

    <?php if (!empty($post['image'])): ?>
        <img class="preview-image mb-3"
             src="/blog-management/uploads/<?= e(basename($post['image'])) ?>"
             alt="Current featured image">
    <?php endif; ?>

    <label class="form-label">Status</label>
    <select class="form-select mb-3" name="status">
        <option value="draft"
            <?= ($post['status'] ?? '') === 'draft' ? 'selected' : '' ?>>
            Draft
        </option>
        <option value="published"
            <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>
            Published
        </option>
    </select>

    <button class="btn btn-primary">Save Post</button>
    <a href="posts.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
