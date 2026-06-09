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

$activeYear = $pdo->query('SELECT * FROM academic_years WHERE is_active = 1 LIMIT 1')->fetch();
$firstTerm = $pdo->query('SELECT * FROM terms ORDER BY id LIMIT 1')->fetch();
$average = 0;
$position = null;
if ($student && $activeYear && $firstTerm) {
    $average = student_average($pdo, (int) $student['id'], (int) $firstTerm['id'], (int) $activeYear['id']);
    $position = student_position($pdo, (int) $student['id'], (int) $firstTerm['id'], (int) $activeYear['id']);
}

render_header('Student Dashboard');
?>
<?php if (!$student): ?>
    <div class="alert">Your student profile has not been linked by the admin.</div>
<?php else: ?>
    <div class="grid">
        <div class="card"><span>Class</span><p class="metric"><?= e($student['form_name'] . ' ' . $student['stream_name']) ?></p></div>
        <div class="card"><span>Average</span><p class="metric"><?= e((string) $average) ?></p></div>
        <div class="card"><span>Position</span><p class="metric"><?= $position ? e((string) $position) : '-' ?></p></div>
    </div>
    <div class="actions">
        <a class="button" href="/student/results.php">My Results</a>
        <a class="button" href="/student/report_card.php">Report Card</a>
    </div>
<?php endif; ?>
<?php render_footer(); ?>

