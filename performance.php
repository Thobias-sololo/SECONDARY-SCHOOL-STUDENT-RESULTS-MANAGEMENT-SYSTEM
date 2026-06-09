<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/results.php';
require_role(['teacher']);

$stmt = $pdo->prepare('SELECT * FROM teachers WHERE user_id = ? LIMIT 1');
$stmt->execute([current_user()['id']]);
$teacher = $stmt->fetch();
$teacherId = (int) ($teacher['id'] ?? 0);

$classes = [];
if ($teacherId) {
    $stmt = $pdo->prepare('
        SELECT tc.form_id, tc.stream_id, f.name AS form_name, st.name AS stream_name
        FROM teacher_classes tc
        JOIN forms f ON f.id = tc.form_id
        JOIN streams st ON st.id = tc.stream_id
        WHERE tc.teacher_id = ?
        ORDER BY f.name, st.name
    ');
    $stmt->execute([$teacherId]);
    $classes = $stmt->fetchAll();
}

$terms = $pdo->query('SELECT * FROM terms ORDER BY id')->fetchAll();
$years = $pdo->query('SELECT * FROM academic_years ORDER BY year_label DESC')->fetchAll();
$rankings = [];

if (isset($_GET['class_key'], $_GET['term_id'], $_GET['year_id'])) {
    [$formId, $streamId] = array_map('intval', explode('-', $_GET['class_key']));
    $rankings = class_rankings($pdo, $formId, $streamId, (int) $_GET['term_id'], (int) $_GET['year_id']);
}

render_header('Assigned Class Performance');
?>
<form method="get">
    <div class="grid">
        <div><label>Class</label><select name="class_key"><?php foreach ($classes as $class): ?><option value="<?= $class['form_id'] . '-' . $class['stream_id'] ?>"><?= e($class['form_name'] . ' ' . $class['stream_name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Term</label><select name="term_id"><?php foreach ($terms as $term): ?><option value="<?= $term['id'] ?>"><?= e($term['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Year</label><select name="year_id"><?php foreach ($years as $year): ?><option value="<?= $year['id'] ?>"><?= e($year['year_label']) ?></option><?php endforeach; ?></select></div>
    </div>
    <button type="submit">View</button>
</form>
<?php if ($rankings): ?>
<div class="table-wrap">
    <table>
        <thead><tr><th>Position</th><th>Admission</th><th>Name</th><th>Average</th></tr></thead>
        <tbody>
        <?php foreach ($rankings as $rank): ?>
            <tr><td><?= $rank['position'] ?></td><td><?= e($rank['admission_no']) ?></td><td><?= e($rank['student_name']) ?></td><td><?= e((string) $rank['average_marks']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php render_footer(); ?>

