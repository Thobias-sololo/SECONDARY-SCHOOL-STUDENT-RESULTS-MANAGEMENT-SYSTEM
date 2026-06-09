<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['type'] === 'form') {
        $stmt = $pdo->prepare('INSERT IGNORE INTO forms (name) VALUES (?)');
        $stmt->execute([trim($_POST['name'])]);
        flash('Form saved.');
    } elseif ($_POST['type'] === 'stream') {
        $stmt = $pdo->prepare('INSERT IGNORE INTO streams (name) VALUES (?)');
        $stmt->execute([strtoupper(trim($_POST['name']))]);
        flash('Stream saved.');
    } elseif ($_POST['type'] === 'term') {
        $stmt = $pdo->prepare('INSERT IGNORE INTO terms (name) VALUES (?)');
        $stmt->execute([trim($_POST['name'])]);
        flash('Term saved.');
    } elseif ($_POST['type'] === 'year') {
        if (isset($_POST['is_active'])) {
            $pdo->query('UPDATE academic_years SET is_active = 0');
        }
        $stmt = $pdo->prepare('INSERT INTO academic_years (year_label, is_active) VALUES (?, ?) ON DUPLICATE KEY UPDATE is_active = VALUES(is_active)');
        $stmt->execute([trim($_POST['name']), isset($_POST['is_active']) ? 1 : 0]);
        flash('Academic year saved.');
    } elseif ($_POST['type'] === 'grade') {
        $stmt = $pdo->prepare('INSERT INTO grades (grade, min_mark, max_mark, remark) VALUES (?, ?, ?, ?)');
        $stmt->execute([strtoupper(trim($_POST['grade'])), (float) $_POST['min_mark'], (float) $_POST['max_mark'], trim($_POST['remark'])]);
        flash('Grade saved.');
    }
    header('Location: /admin/settings.php');
    exit;
}

$forms = $pdo->query('SELECT * FROM forms ORDER BY name')->fetchAll();
$streams = $pdo->query('SELECT * FROM streams ORDER BY name')->fetchAll();
$terms = $pdo->query('SELECT * FROM terms ORDER BY id')->fetchAll();
$years = $pdo->query('SELECT * FROM academic_years ORDER BY year_label DESC')->fetchAll();
$grades = $pdo->query('SELECT * FROM grades ORDER BY min_mark DESC')->fetchAll();

render_header('School Setup');
?>
<div class="grid">
    <form method="post">
        <input type="hidden" name="type" value="form">
        <h2>Forms</h2>
        <label>Form Name</label><input name="name" placeholder="Form 1" required>
        <button type="submit">Add Form</button>
    </form>
    <form method="post">
        <input type="hidden" name="type" value="stream">
        <h2>Streams</h2>
        <label>Stream Name</label><input name="name" placeholder="A" required>
        <button type="submit">Add Stream</button>
    </form>
    <form method="post">
        <input type="hidden" name="type" value="term">
        <h2>Terms</h2>
        <label>Term Name</label><input name="name" placeholder="Term 1" required>
        <button type="submit">Add Term</button>
    </form>
    <form method="post">
        <input type="hidden" name="type" value="year">
        <h2>Academic Years</h2>
        <label>Year</label><input name="name" placeholder="2026" required>
        <label><input type="checkbox" name="is_active" value="1"> Active year</label>
        <button type="submit">Save Year</button>
    </form>
</div>
<form method="post">
    <input type="hidden" name="type" value="grade">
    <h2>Grade System</h2>
    <div class="grid">
        <div><label>Grade</label><input name="grade" required></div>
        <div><label>Minimum Mark</label><input type="number" min="0" max="100" step="0.01" name="min_mark" required></div>
        <div><label>Maximum Mark</label><input type="number" min="0" max="100" step="0.01" name="max_mark" required></div>
        <div><label>Remark</label><input name="remark" required></div>
    </div>
    <button type="submit">Add Grade</button>
</form>
<div class="grid">
    <div class="table-wrap"><h2>Forms</h2><table><tbody><?php foreach ($forms as $row): ?><tr><td><?= e($row['name']) ?></td></tr><?php endforeach; ?></tbody></table></div>
    <div class="table-wrap"><h2>Streams</h2><table><tbody><?php foreach ($streams as $row): ?><tr><td><?= e($row['name']) ?></td></tr><?php endforeach; ?></tbody></table></div>
    <div class="table-wrap"><h2>Terms</h2><table><tbody><?php foreach ($terms as $row): ?><tr><td><?= e($row['name']) ?></td></tr><?php endforeach; ?></tbody></table></div>
    <div class="table-wrap"><h2>Years</h2><table><tbody><?php foreach ($years as $row): ?><tr><td><?= e($row['year_label']) ?> <?= $row['is_active'] ? '(Active)' : '' ?></td></tr><?php endforeach; ?></tbody></table></div>
</div>
<div class="table-wrap">
    <h2>Grades</h2>
    <table>
        <thead><tr><th>Grade</th><th>Minimum</th><th>Maximum</th><th>Remark</th></tr></thead>
        <tbody><?php foreach ($grades as $row): ?><tr><td><?= e($row['grade']) ?></td><td><?= e($row['min_mark']) ?></td><td><?= e($row['max_mark']) ?></td><td><?= e($row['remark']) ?></td></tr><?php endforeach; ?></tbody>
    </table>
</div>
<?php render_footer(); ?>

