<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] === 'approved' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare('UPDATE results SET status = ? WHERE id = ?');
    $stmt->execute([$status, (int) $_POST['result_id']]);
    flash('Result status updated.');
    header('Location: /admin/results.php');
    exit;
}

$results = $pdo->query("
    SELECT r.*, CONCAT(s.first_name, ' ', s.last_name) AS student_name, sub.name AS subject_name, t.name AS term_name, ay.year_label, f.name AS form_name, st.name AS stream_name
    FROM results r
    JOIN students s ON s.id = r.student_id
    JOIN subjects sub ON sub.id = r.subject_id
    JOIN terms t ON t.id = r.term_id
    JOIN academic_years ay ON ay.id = r.academic_year_id
    JOIN forms f ON f.id = s.form_id
    JOIN streams st ON st.id = s.stream_id
    ORDER BY r.status = 'submitted' DESC, r.updated_at DESC
")->fetchAll();

render_header('Approve Results');
?>
<div class="table-wrap">
    <table>
        <thead><tr><th>Student</th><th>Class</th><th>Subject</th><th>Marks</th><th>Term</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($results as $result): ?>
            <tr>
                <td><?= e($result['student_name']) ?></td>
                <td><?= e($result['form_name'] . ' ' . $result['stream_name']) ?></td>
                <td><?= e($result['subject_name']) ?></td>
                <td><?= e($result['marks']) ?></td>
                <td><?= e($result['term_name'] . ' ' . $result['year_label']) ?></td>
                <td><?= e($result['status']) ?></td>
                <td>
                    <?php if ($result['status'] === 'submitted'): ?>
                        <form method="post">
                            <input type="hidden" name="result_id" value="<?= $result['id'] ?>">
                            <button name="status" value="approved">Approve</button>
                            <button name="status" value="rejected" class="button danger">Reject</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php render_footer(); ?>

