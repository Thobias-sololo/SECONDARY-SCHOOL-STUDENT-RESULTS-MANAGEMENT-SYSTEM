<?php
require_once __DIR__ . '/auth.php';

function render_header(string $title): void
{
    $user = current_user();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | School Results</title>
        <link rel="stylesheet" href="/assets/css/style.css">
    </head>
    <body>
    <header class="topbar">
        <a class="brand" href="/dashboard.php">School Results</a>
        <?php if ($user): ?>
            <nav>
                <span><?= e($user['name']) ?> (<?= e(ucfirst($user['role'])) ?>)</span>
                <a href="/profile.php">Profile</a>
                <a href="/notifications.php">Notifications</a>
                <a href="/logout.php">Logout</a>
            </nav>
        <?php endif; ?>
    </header>
    <main class="shell">
    <?php if ($message = flash()): ?>
        <div class="alert"><?= e($message) ?></div>
    <?php endif; ?>
    <h1><?= e($title) ?></h1>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <script src="/assets/js/app.js"></script>
    </body>
    </html>
    <?php
}

