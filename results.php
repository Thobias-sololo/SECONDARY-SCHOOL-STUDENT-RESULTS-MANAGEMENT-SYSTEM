<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/results.php';
require_role(['student']);

$stmt = $pdo->prepare('SELECT * FROM students WHERE user_id = ? LIMIT 1');
$stmt->execute([current_user()['id']]);
$student = $stmt->fetch();

$terms = $pdo->query('SELECT * FROM terms ORDER BY id')->fetchAll();
$years = $pdo->query('SELECT * FROM academic_years ORDER BY year_label DESC')->fetchAll();

$rows = [];
$average = 0;
$position = null;
$overallGrade = ['grade' => '-', 'remark' => '-'];

if ($student && isset($_GET['term_id'], $_GET['year_id'])) {
    $termId = (int) $_GET['term_id'];
    $yearId = (int) $_GET['year_id'];
    $stmt = $pdo->prepare('
        SELECT r.*, sub.name AS subject_name
        FROM results r
        JOIN subjects sub ON sub.id = r.subject_id
        WHERE r.student_id = ? AND r.term_id = ? AND r.academic_year_id = ? AND r.status = "approved"
        ORDER BY sub.name
    ');
    $stmt->execute([(int) $student['id'], $termId, $yearId]);
    $rows = $stmt->fetchAll();
    $average = student_average($pdo, (int) $student['id'], $termId, $yearId);
    $position = student_position($pdo, (int) $student['id'], $termId, $yearId);
    $overallGrade = grade_for_mark($pdo, $average);
}

render_header('My Results');
?>
<form method="get">
    <div class="grid">
        <div><label>Term</label><select name="term_id"><?php foreach ($terms as $term): ?><option value="<?= $term['id'] ?>"><?= e($term['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Year</label><select name="year_id"><?php foreach ($years as $year): ?><option value="<?= $year['id'] ?>"><?= e($year['year_label']) ?></option><?php endforeach; ?></select></div>
    </div>
    <button type="submit">View Results</button>
</form>
<?php if ($rows): ?>
<div class="grid">
    <div class="card"><span>Average</span><p class="metric"><?= e((string) $average) ?></p></div>
    <div class="card"><span>Grade</span><p class="metric"><?= e($overallGrade['grade']) ?></p></div>
    <div class="card"><span>Position</span><p class="metric"><?= $position ? e((string) $position) : '-' ?></p></div>
</div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Remark</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <?php $grade = grade_for_mark($pdo, (float) $row['marks']); ?>
            <tr><td><?= e($row['subject_name']) ?></td><td><?= e($row['marks']) ?></td><td><?= e($grade['grade']) ?></td><td><?= e($grade['remark']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php render_footer(); ?>

