<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['teacher']);

$stmt = $pdo->prepare('SELECT * FROM teachers WHERE user_id = ? LIMIT 1');
$stmt->execute([current_user()['id']]);
$teacher = $stmt->fetch();

$teacherId = (int) ($teacher['id'] ?? 0);
$subjects = 0;
$classes = 0;
$submitted = 0;

if ($teacherId) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM teacher_subjects WHERE teacher_id = ?');
    $stmt->execute([$teacherId]);
    $subjects = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM teacher_classes WHERE teacher_id = ?');
    $stmt->execute([$teacherId]);
    $classes = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM results WHERE teacher_id = ?');
    $stmt->execute([$teacherId]);
    $submitted = (int) $stmt->fetchColumn();
}

render_header('Teacher Dashboard');
?>
<?php if (!$teacher): ?>
    <div class="alert">Your teacher profile has not been linked by the admin.</div>
<?php endif; ?>
<div class="grid">
    <div class="card"><span>Assigned Subjects</span><p class="metric"><?= $subjects ?></p></div>
    <div class="card"><span>Assigned Classes</span><p class="metric"><?= $classes ?></p></div>
    <div class="card"><span>Results Entered</span><p class="metric"><?= $submitted ?></p></div>
</div>
<div class="actions">
    <a class="button" href="/teacher/results.php">Enter Marks</a>
    <a class="button" href="/teacher/performance.php">Class Performance</a>
    <a class="button" href="/teacher/notify.php">Send Notification</a>
</div>
<?php render_footer(); ?>

