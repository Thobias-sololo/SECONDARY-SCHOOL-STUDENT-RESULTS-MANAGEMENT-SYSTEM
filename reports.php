<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/results.php';
require_role(['admin']);

$forms = $pdo->query('SELECT * FROM forms ORDER BY name')->fetchAll();
$streams = $pdo->query('SELECT * FROM streams ORDER BY name')->fetchAll();
$terms = $pdo->query('SELECT * FROM terms ORDER BY id')->fetchAll();
$years = $pdo->query('SELECT * FROM academic_years ORDER BY year_label DESC')->fetchAll();

$rankings = [];
if (isset($_GET['form_id'], $_GET['stream_id'], $_GET['term_id'], $_GET['year_id'])) {
    $rankings = class_rankings($pdo, (int) $_GET['form_id'], (int) $_GET['stream_id'], (int) $_GET['term_id'], (int) $_GET['year_id']);
}

render_header('Class Performance');
?>
<form method="get">
    <div class="grid">
        <div><label>Form</label><select name="form_id"><?php foreach ($forms as $form): ?><option value="<?= $form['id'] ?>"><?= e($form['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Stream</label><select name="stream_id"><?php foreach ($streams as $stream): ?><option value="<?= $stream['id'] ?>"><?= e($stream['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Term</label><select name="term_id"><?php foreach ($terms as $term): ?><option value="<?= $term['id'] ?>"><?= e($term['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Year</label><select name="year_id"><?php foreach ($years as $year): ?><option value="<?= $year['id'] ?>"><?= e($year['year_label']) ?></option><?php endforeach; ?></select></div>
    </div>
    <button type="submit">View Performance</button>
</form>
<?php if ($rankings): ?>
    <?php $classAverage = round(array_sum(array_column($rankings, 'average_marks')) / max(count($rankings), 1), 2); ?>
    <div class="grid">
        <div class="card"><span>Class Average</span><p class="metric"><?= $classAverage ?></p></div>
        <div class="card"><span>Top Student</span><p class="metric"><?= e($rankings[0]['student_name']) ?></p></div>
        <div class="card"><span>Students</span><p class="metric"><?= count($rankings) ?></p></div>
    </div>
    <div class="table-wrap">
        <h2>Top 10 Students</h2>
        <table>
            <thead><tr><th>Position</th><th>Admission</th><th>Name</th><th>Average</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($rankings, 0, 10) as $rank): ?>
                <tr><td><?= $rank['position'] ?></td><td><?= e($rank['admission_no']) ?></td><td><?= e($rank['student_name']) ?></td><td><?= e((string) $rank['average_marks']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php render_footer(); ?>
