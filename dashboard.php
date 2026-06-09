<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['admin']);

$counts = [];
foreach (['students', 'teachers', 'subjects', 'results'] as $table) {
    $counts[$table] = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
}
$pending = (int) $pdo->query("SELECT COUNT(*) FROM results WHERE status = 'submitted'")->fetchColumn();

render_header('Admin Dashboard');
?>
<div class="grid">
    <div class="card"><span>Students</span><p class="metric"><?= $counts['students'] ?></p></div>
    <div class="card"><span>Teachers</span><p class="metric"><?= $counts['teachers'] ?></p></div>
    <div class="card"><span>Subjects</span><p class="metric"><?= $counts['subjects'] ?></p></div>
    <div class="card"><span>Pending Results</span><p class="metric"><?= $pending ?></p></div>
</div>
<div class="actions">
    <a class="button" href="/admin/students.php">Students</a>
    <a class="button" href="/admin/teachers.php">Teachers</a>
    <a class="button" href="/admin/subjects.php">Subjects</a>
    <a class="button" href="/admin/assignments.php">Assignments</a>
    <a class="button" href="/admin/results.php">Approve Results</a>
    <a class="button" href="/admin/reports.php">Class Performance</a>
    <a class="button" href="/admin/report_card.php">Report Cards</a>
    <a class="button" href="/admin/settings.php">School Setup</a>
    <a class="button" href="/admin/notify.php">Send Notification</a>
</div>
<?php render_footer(); ?>
