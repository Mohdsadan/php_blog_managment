
<?php
$flash = get_flash();
$pageTitle = $pageTitle ?? 'Blog Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($pageTitle) ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet"
          href="/blog-management/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand"
           href="/blog-management/">
            MyBlog
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link"
                       href="/blog-management/">
                        Home
                    </a>
                </li>

                <?php if (!empty($_SESSION['user_id'])): ?>

                    <li class="nav-item">
                        <a class="nav-link"
                           href="/blog-management/admin/dashboard.php">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <form method="POST"
                              action="/blog-management/auth/logout.php">
                            <input type="hidden" name="csrf_token"
                                   value="<?= e(csrf_token()) ?>">
                            <button class="btn btn-link nav-link">
                                Logout
                            </button>
                        </form>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link"
                           href="/blog-management/auth/login.php">
                            Login
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                           href="/blog-management/auth/register.php">
                            Register
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>
