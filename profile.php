<?php
require_once __DIR__ . '/includes/layout.php';
require_login();

$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($password !== '') {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?');
        $stmt->execute([$name, $phone, password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
        $stmt->execute([$name, $phone, $user['id']]);
    }

    $_SESSION['user']['name'] = $name;
    flash('Profile updated.');
    header('Location: /profile.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

render_header('My Profile');
?>
<form method="post">
    <label>Name</label>
    <input name="name" value="<?= e($profile['name']) ?>" required>
    <label>Email</label>
    <input value="<?= e($profile['email']) ?>" disabled>
    <label>Phone</label>
    <input name="phone" value="<?= e($profile['phone'] ?? '') ?>">
    <label>New Password</label>
    <input type="password" name="password" placeholder="Leave blank to keep current password">
    <button type="submit">Save Profile</button>
</form>
<?php render_footer(); ?>

