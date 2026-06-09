<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['teacher']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO notifications (sender_id, role_target, title, message) VALUES (?, ?, ?, ?)');
    $stmt->execute([current_user()['id'], $_POST['role_target'], trim($_POST['title']), trim($_POST['message'])]);
    flash('Notification sent.');
    header('Location: /teacher/notify.php');
    exit;
}

render_header('Send Notification');
?>
<form method="post">
    <label>Send To</label>
    <select name="role_target">
        <option value="student">Students</option>
        <option value="admin">Admins</option>
    </select>
    <label>Title</label>
    <input name="title" required>
    <label>Message</label>
    <textarea name="message" required></textarea>
    <button type="submit">Send Notification</button>
</form>
<?php render_footer(); ?>

