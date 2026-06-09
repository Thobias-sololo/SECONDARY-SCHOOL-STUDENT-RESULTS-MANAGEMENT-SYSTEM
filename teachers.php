<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = password_hash($_POST['password'] ?: 'teacher123', PASSWORD_DEFAULT);
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'teacher', ?)");
    $stmt->execute([trim($_POST['full_name']), trim($_POST['email']), $password, trim($_POST['phone'] ?? '')]);
    $userId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare('INSERT INTO teachers (user_id, staff_no, full_name, gender, phone, email) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, trim($_POST['staff_no']), trim($_POST['full_name']), $_POST['gender'], trim($_POST['phone'] ?? ''), trim($_POST['email'])]);
    $pdo->commit();
    flash('Teacher registered.');
    header('Location: /admin/teachers.php');
    exit;
}

$teachers = $pdo->query('SELECT * FROM teachers ORDER BY full_name')->fetchAll();
render_header('Teacher Management');
?>
<form method="post">
    <div class="grid">
        <div><label>Staff No</label><input name="staff_no" required></div>
        <div><label>Full Name</label><input name="full_name" required></div>
        <div><label>Gender</label><select name="gender"><option>Male</option><option>Female</option></select></div>
        <div><label>Email</label><input type="email" name="email" required></div>
        <div><label>Phone</label><input name="phone"></div>
        <div><label>Password</label><input name="password" placeholder="Default: teacher123"></div>
    </div>
    <button type="submit">Register Teacher</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Staff No</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($teachers as $teacher): ?>
            <tr><td><?= e($teacher['staff_no']) ?></td><td><?= e($teacher['full_name']) ?></td><td><?= e($teacher['email'] ?? '') ?></td><td><?= e($teacher['phone'] ?? '') ?></td><td><?= e($teacher['status']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php render_footer(); ?>

