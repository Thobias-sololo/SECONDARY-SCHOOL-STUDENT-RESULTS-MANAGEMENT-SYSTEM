<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['type'] === 'subject') {
        $stmt = $pdo->prepare('INSERT IGNORE INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)');
        $stmt->execute([(int) $_POST['teacher_id'], (int) $_POST['subject_id']]);
        flash('Subject assigned.');
    } else {
        $stmt = $pdo->prepare('INSERT IGNORE INTO teacher_classes (teacher_id, form_id, stream_id) VALUES (?, ?, ?)');
        $stmt->execute([(int) $_POST['teacher_id'], (int) $_POST['form_id'], (int) $_POST['stream_id']]);
        flash('Class assigned.');
    }
    header('Location: /admin/assignments.php');
    exit;
}

$teachers = $pdo->query('SELECT * FROM teachers ORDER BY full_name')->fetchAll();
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY name')->fetchAll();
$forms = $pdo->query('SELECT * FROM forms ORDER BY name')->fetchAll();
$streams = $pdo->query('SELECT * FROM streams ORDER BY name')->fetchAll();
$subjectAssignments = $pdo->query("SELECT t.full_name, s.name FROM teacher_subjects ts JOIN teachers t ON t.id = ts.teacher_id JOIN subjects s ON s.id = ts.subject_id ORDER BY t.full_name")->fetchAll();
$classAssignments = $pdo->query("SELECT t.full_name, f.name AS form_name, st.name AS stream_name FROM teacher_classes tc JOIN teachers t ON t.id = tc.teacher_id JOIN forms f ON f.id = tc.form_id JOIN streams st ON st.id = tc.stream_id ORDER BY t.full_name")->fetchAll();

render_header('Teacher Assignments');
?>
<div class="grid">
    <form method="post">
        <input type="hidden" name="type" value="subject">
        <h2>Assign Subject</h2>
        <label>Teacher</label><select name="teacher_id"><?php foreach ($teachers as $teacher): ?><option value="<?= $teacher['id'] ?>"><?= e($teacher['full_name']) ?></option><?php endforeach; ?></select>
        <label>Subject</label><select name="subject_id"><?php foreach ($subjects as $subject): ?><option value="<?= $subject['id'] ?>"><?= e($subject['name']) ?></option><?php endforeach; ?></select>
        <button type="submit">Assign Subject</button>
    </form>
    <form method="post">
        <input type="hidden" name="type" value="class">
        <h2>Assign Class</h2>
        <label>Teacher</label><select name="teacher_id"><?php foreach ($teachers as $teacher): ?><option value="<?= $teacher['id'] ?>"><?= e($teacher['full_name']) ?></option><?php endforeach; ?></select>
        <label>Form</label><select name="form_id"><?php foreach ($forms as $form): ?><option value="<?= $form['id'] ?>"><?= e($form['name']) ?></option><?php endforeach; ?></select>
        <label>Stream</label><select name="stream_id"><?php foreach ($streams as $stream): ?><option value="<?= $stream['id'] ?>"><?= e($stream['name']) ?></option><?php endforeach; ?></select>
        <button type="submit">Assign Class</button>
    </form>
</div>
<div class="grid">
    <div class="table-wrap">
        <h2>Subject Assignments</h2>
        <table><tbody><?php foreach ($subjectAssignments as $row): ?><tr><td><?= e($row['full_name']) ?></td><td><?= e($row['name']) ?></td></tr><?php endforeach; ?></tbody></table>
    </div>
    <div class="table-wrap">
        <h2>Class Assignments</h2>
        <table><tbody><?php foreach ($classAssignments as $row): ?><tr><td><?= e($row['full_name']) ?></td><td><?= e($row['form_name'] . ' ' . $row['stream_name']) ?></td></tr><?php endforeach; ?></tbody></table>
    </div>
</div>
<?php render_footer(); ?>

