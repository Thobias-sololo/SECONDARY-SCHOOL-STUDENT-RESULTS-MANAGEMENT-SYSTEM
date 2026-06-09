<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO subjects (name, code) VALUES (?, ?)');
    $stmt->execute([trim($_POST['name']), strtoupper(trim($_POST['code']))]);
    flash('Subject added.');
    header('Location: /admin/subjects.php');
    exit;
}

$subjects = $pdo->query('SELECT * FROM subjects ORDER BY name')->fetchAll();
render_header('Subject Management');
?>
<form method="post">
    <div class="grid">
        <div><label>Subject Name</label><input name="name" required></div>
        <div><label>Subject Code</label><input name="code" required></div>
    </div>
    <button type="submit">Add Subject</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Code</th><th>Name</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($subjects as $subject): ?>
            <tr><td><?= e($subject['code']) ?></td><td><?= e($subject['name']) ?></td><td><?= e($subject['status']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php render_footer(); ?>

