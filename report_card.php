<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/results.php';
require_role(['student']);

$stmt = $pdo->prepare('
    SELECT s.*, f.name AS form_name, st.name AS stream_name
    FROM students s
    JOIN forms f ON f.id = s.form_id
    JOIN streams st ON st.id = s.stream_id
    WHERE s.user_id = ?
    LIMIT 1
');
$stmt->execute([current_user()['id']]);
$student = $stmt->fetch();

$terms = $pdo->query('SELECT * FROM terms ORDER BY id')->fetchAll();
$years = $pdo->query('SELECT * FROM academic_years ORDER BY year_label DESC')->fetchAll();

$rows = [];
$average = 0;
$position = null;
$overallGrade = ['grade' => '-', 'remark' => '-'];
$selectedTerm = null;
$selectedYear = null;

if ($student && isset($_GET['term_id'], $_GET['year_id'])) {
    $termId = (int) $_GET['term_id'];
    $yearId = (int) $_GET['year_id'];
    $stmt = $pdo->prepare('SELECT * FROM terms WHERE id = ?');
    $stmt->execute([$termId]);
    $selectedTerm = $stmt->fetch();
    $stmt = $pdo->prepare('SELECT * FROM academic_years WHERE id = ?');
    $stmt->execute([$yearId]);
    $selectedYear = $stmt->fetch();
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

render_header('Student Report Card');
?>
<form method="get">
    <div class="grid">
        <div><label>Term</label><select name="term_id"><?php foreach ($terms as $term): ?><option value="<?= $term['id'] ?>"><?= e($term['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Year</label><select name="year_id"><?php foreach ($years as $year): ?><option value="<?= $year['id'] ?>"><?= e($year['year_label']) ?></option><?php endforeach; ?></select></div>
    </div>
    <button type="submit">Generate Report Card</button>
</form>
<?php if ($student && $rows): ?>
<section class="card">
    <h2>Secondary School Report Card</h2>
    <p><strong>Name:</strong> <?= e($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']) ?></p>
    <p><strong>Admission No:</strong> <?= e($student['admission_no']) ?></p>
    <p><strong>Class:</strong> <?= e($student['form_name'] . ' ' . $student['stream_name']) ?></p>
    <p><strong>Term:</strong> <?= e($selectedTerm['name'] ?? '') ?> | <strong>Year:</strong> <?= e($selectedYear['year_label'] ?? '') ?></p>
    <table>
        <thead><tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Remark</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <?php $grade = grade_for_mark($pdo, (float) $row['marks']); ?>
            <tr><td><?= e($row['subject_name']) ?></td><td><?= e($row['marks']) ?></td><td><?= e($grade['grade']) ?></td><td><?= e($grade['remark']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><strong>Average:</strong> <?= e((string) $average) ?></p>
    <p><strong>Overall Grade:</strong> <?= e($overallGrade['grade']) ?> - <?= e($overallGrade['remark']) ?></p>
    <p><strong>Position:</strong> <?= $position ? e((string) $position) : '-' ?></p>
    <p><strong>Class Teacher Comment:</strong> _______________________________</p>
    <p><strong>Headmaster Comment:</strong> _______________________________</p>
    <div class="actions"><button type="button" onclick="window.print()">Print Report Card</button></div>
</section>
<?php endif; ?>
<?php render_footer(); ?>
