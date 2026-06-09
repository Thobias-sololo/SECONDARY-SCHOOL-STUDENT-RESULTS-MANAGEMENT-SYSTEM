<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/results.php';
require_role(['admin']);

$students = $pdo->query('
    SELECT s.*, f.name AS form_name, st.name AS stream_name
    FROM students s
    JOIN forms f ON f.id = s.form_id
    JOIN streams st ON st.id = s.stream_id
    ORDER BY f.name, st.name, s.first_name
')->fetchAll();
$terms = $pdo->query('SELECT * FROM terms ORDER BY id')->fetchAll();
$years = $pdo->query('SELECT * FROM academic_years ORDER BY year_label DESC')->fetchAll();

$student = null;
$rows = [];
$average = 0;
$position = null;
$overallGrade = ['grade' => '-', 'remark' => '-'];

if (isset($_GET['student_id'], $_GET['term_id'], $_GET['year_id'])) {
    $stmt = $pdo->prepare('
        SELECT s.*, f.name AS form_name, st.name AS stream_name
        FROM students s
        JOIN forms f ON f.id = s.form_id
        JOIN streams st ON st.id = s.stream_id
        WHERE s.id = ?
    ');
    $stmt->execute([(int) $_GET['student_id']]);
    $student = $stmt->fetch();
    $stmt = $pdo->prepare('SELECT r.*, sub.name AS subject_name FROM results r JOIN subjects sub ON sub.id = r.subject_id WHERE r.student_id = ? AND r.term_id = ? AND r.academic_year_id = ? AND r.status = "approved" ORDER BY sub.name');
    $stmt->execute([(int) $_GET['student_id'], (int) $_GET['term_id'], (int) $_GET['year_id']]);
    $rows = $stmt->fetchAll();
    $average = student_average($pdo, (int) $_GET['student_id'], (int) $_GET['term_id'], (int) $_GET['year_id']);
    $position = student_position($pdo, (int) $_GET['student_id'], (int) $_GET['term_id'], (int) $_GET['year_id']);
    $overallGrade = grade_for_mark($pdo, $average);
}

render_header('Generate Report Card');
?>
<form method="get">
    <div class="grid">
        <div><label>Student</label><select name="student_id"><?php foreach ($students as $row): ?><option value="<?= $row['id'] ?>"><?= e($row['first_name'] . ' ' . $row['last_name'] . ' - ' . $row['form_name'] . ' ' . $row['stream_name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Term</label><select name="term_id"><?php foreach ($terms as $term): ?><option value="<?= $term['id'] ?>"><?= e($term['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Year</label><select name="year_id"><?php foreach ($years as $year): ?><option value="<?= $year['id'] ?>"><?= e($year['year_label']) ?></option><?php endforeach; ?></select></div>
    </div>
    <button type="submit">Generate</button>
</form>
<?php if ($student): ?>
<section class="card">
    <h2>Secondary School Report Card</h2>
    <p><strong>Name:</strong> <?= e($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']) ?></p>
    <p><strong>Admission No:</strong> <?= e($student['admission_no']) ?></p>
    <p><strong>Class:</strong> <?= e($student['form_name'] . ' ' . $student['stream_name']) ?></p>
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
    <div class="actions"><button type="button" onclick="window.print()">Print Report Card</button></div>
</section>
<?php endif; ?>
<?php render_footer(); ?>
