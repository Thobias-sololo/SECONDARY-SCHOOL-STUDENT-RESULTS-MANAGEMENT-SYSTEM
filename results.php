<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['teacher']);

$stmt = $pdo->prepare('SELECT * FROM teachers WHERE user_id = ? LIMIT 1');
$stmt->execute([current_user()['id']]);
$teacher = $stmt->fetch();
if (!$teacher) {
    render_header('Enter Marks');
    echo '<div class="alert">Admin must create and link your teacher profile first.</div>';
    render_footer();
    exit;
}

$teacherId = (int) $teacher['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marks = (float) $_POST['marks'];
    if ($marks < 0 || $marks > 100) {
        flash('Marks must be between 0 and 100.');
    } else {
        $stmt = $pdo->prepare('
            SELECT COUNT(*)
            FROM students s
            JOIN teacher_classes tc ON tc.form_id = s.form_id AND tc.stream_id = s.stream_id
            JOIN teacher_subjects ts ON ts.teacher_id = tc.teacher_id
            WHERE s.id = ? AND ts.subject_id = ? AND tc.teacher_id = ?
        ');
        $stmt->execute([(int) $_POST['student_id'], (int) $_POST['subject_id'], $teacherId]);

        if ((int) $stmt->fetchColumn() === 0) {
            flash('You can only enter marks for assigned classes and subjects.');
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO results (student_id, subject_id, teacher_id, term_id, academic_year_id, marks, status)
                VALUES (?, ?, ?, ?, ?, ?, "submitted")
                ON DUPLICATE KEY UPDATE marks = VALUES(marks), teacher_id = VALUES(teacher_id), status = "submitted"
            ');
            $stmt->execute([(int) $_POST['student_id'], (int) $_POST['subject_id'], $teacherId, (int) $_POST['term_id'], (int) $_POST['year_id'], $marks]);
            flash('Marks submitted for admin approval.');
        }
    }
    header('Location: /teacher/results.php');
    exit;
}

$students = $pdo->prepare('
    SELECT DISTINCT s.*, f.name AS form_name, st.name AS stream_name
    FROM students s
    JOIN teacher_classes tc ON tc.form_id = s.form_id AND tc.stream_id = s.stream_id
    JOIN forms f ON f.id = s.form_id
    JOIN streams st ON st.id = s.stream_id
    WHERE tc.teacher_id = ? AND s.status = "active"
    ORDER BY f.name, st.name, s.first_name
');
$students->execute([$teacherId]);
$students = $students->fetchAll();

$subjects = $pdo->prepare('
    SELECT sub.*
    FROM subjects sub
    JOIN teacher_subjects ts ON ts.subject_id = sub.id
    WHERE ts.teacher_id = ?
    ORDER BY sub.name
');
$subjects->execute([$teacherId]);
$subjects = $subjects->fetchAll();

$terms = $pdo->query('SELECT * FROM terms ORDER BY id')->fetchAll();
$years = $pdo->query('SELECT * FROM academic_years ORDER BY year_label DESC')->fetchAll();

$results = $pdo->prepare('
    SELECT r.*, CONCAT(s.first_name, " ", s.last_name) AS student_name, sub.name AS subject_name, t.name AS term_name, ay.year_label
    FROM results r
    JOIN students s ON s.id = r.student_id
    JOIN subjects sub ON sub.id = r.subject_id
    JOIN terms t ON t.id = r.term_id
    JOIN academic_years ay ON ay.id = r.academic_year_id
    WHERE r.teacher_id = ?
    ORDER BY r.updated_at DESC
');
$results->execute([$teacherId]);
$results = $results->fetchAll();

render_header('Enter Marks');
?>
<form method="post">
    <div class="grid">
        <div><label>Student</label><select name="student_id" required><?php foreach ($students as $student): ?><option value="<?= $student['id'] ?>"><?= e($student['first_name'] . ' ' . $student['last_name'] . ' - ' . $student['form_name'] . ' ' . $student['stream_name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Subject</label><select name="subject_id" required><?php foreach ($subjects as $subject): ?><option value="<?= $subject['id'] ?>"><?= e($subject['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Marks</label><input type="number" min="0" max="100" step="0.01" name="marks" required></div>
        <div><label>Term</label><select name="term_id"><?php foreach ($terms as $term): ?><option value="<?= $term['id'] ?>"><?= e($term['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Academic Year</label><select name="year_id"><?php foreach ($years as $year): ?><option value="<?= $year['id'] ?>"><?= e($year['year_label']) ?></option><?php endforeach; ?></select></div>
    </div>
    <button type="submit">Submit Marks</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Student</th><th>Subject</th><th>Marks</th><th>Term</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($results as $result): ?>
            <tr><td><?= e($result['student_name']) ?></td><td><?= e($result['subject_name']) ?></td><td><?= e($result['marks']) ?></td><td><?= e($result['term_name'] . ' ' . $result['year_label']) ?></td><td><?= e($result['status']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php render_footer(); ?>

