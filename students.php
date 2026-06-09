<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['first_name'] . ' ' . $_POST['last_name']);
    $email = trim($_POST['email'] ?? '');
    $password = password_hash($_POST['password'] ?: 'student123', PASSWORD_DEFAULT);

    $pdo->beginTransaction();
    $userId = null;
    if ($email !== '') {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
        $stmt->execute([$name, $email, $password]);
        $userId = (int) $pdo->lastInsertId();
    }
    $stmt = $pdo->prepare('INSERT INTO students (user_id, admission_no, first_name, middle_name, last_name, gender, dob, form_id, stream_id, guardian_name, guardian_phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $userId,
        trim($_POST['admission_no']),
        trim($_POST['first_name']),
        trim($_POST['middle_name'] ?? ''),
        trim($_POST['last_name']),
        $_POST['gender'],
        $_POST['dob'] ?: null,
        (int) $_POST['form_id'],
        (int) $_POST['stream_id'],
        trim($_POST['guardian_name'] ?? ''),
        trim($_POST['guardian_phone'] ?? ''),
        trim($_POST['address'] ?? ''),
    ]);
    $pdo->commit();
    flash('Student registered.');
    header('Location: /admin/students.php');
    exit;
}

$forms = $pdo->query('SELECT * FROM forms ORDER BY name')->fetchAll();
$streams = $pdo->query('SELECT * FROM streams ORDER BY name')->fetchAll();
$students = $pdo->query("
    SELECT s.*, f.name AS form_name, st.name AS stream_name
    FROM students s
    JOIN forms f ON f.id = s.form_id
    JOIN streams st ON st.id = s.stream_id
    ORDER BY f.name, st.name, s.first_name
")->fetchAll();

render_header('Student Management');
?>
<form method="post">
    <div class="grid">
        <div><label>Admission No</label><input name="admission_no" required></div>
        <div><label>First Name</label><input name="first_name" required></div>
        <div><label>Middle Name</label><input name="middle_name"></div>
        <div><label>Last Name</label><input name="last_name" required></div>
        <div><label>Gender</label><select name="gender"><option>Male</option><option>Female</option></select></div>
        <div><label>Date of Birth</label><input type="date" name="dob"></div>
        <div><label>Form</label><select name="form_id"><?php foreach ($forms as $form): ?><option value="<?= $form['id'] ?>"><?= e($form['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Stream</label><select name="stream_id"><?php foreach ($streams as $stream): ?><option value="<?= $stream['id'] ?>"><?= e($stream['name']) ?></option><?php endforeach; ?></select></div>
        <div><label>Student Login Email</label><input type="email" name="email"></div>
        <div><label>Student Password</label><input name="password" placeholder="Default: student123"></div>
        <div><label>Guardian Name</label><input name="guardian_name"></div>
        <div><label>Guardian Phone</label><input name="guardian_phone"></div>
    </div>
    <label>Address</label><input name="address">
    <button type="submit">Register Student</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Admission</th><th>Name</th><th>Gender</th><th>Class</th><th>Guardian</th></tr></thead>
        <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?= e($student['admission_no']) ?></td>
                <td><?= e($student['first_name'] . ' ' . $student['last_name']) ?></td>
                <td><?= e($student['gender']) ?></td>
                <td><?= e($student['form_name'] . ' ' . $student['stream_name']) ?></td>
                <td><?= e($student['guardian_name'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php render_footer(); ?>

