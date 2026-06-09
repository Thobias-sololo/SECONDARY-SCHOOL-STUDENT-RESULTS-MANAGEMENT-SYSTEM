<?php
require_once __DIR__ . '/includes/layout.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO notification_reads (notification_id, user_id) VALUES (?, ?)');
    $stmt->execute([(int) $_POST['notification_id'], $user['id']]);
    flash('Notification marked as read.');
    header('Location: /notifications.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT n.*, nr.id AS read_id
    FROM notifications n
    LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
    WHERE n.role_target IN ('all', ?) OR n.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$user['id'], $user['role'], $user['id']]);
$notifications = $stmt->fetchAll();

render_header('Notifications');
?>
<div class="table-wrap">
    <table>
        <thead><tr><th>Title</th><th>Message</th><th>Date</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($notifications as $notification): ?>
            <tr>
                <td><?= e($notification['title']) ?></td>
                <td><?= e($notification['message']) ?></td>
                <td><?= e($notification['created_at']) ?></td>
                <td><?= $notification['read_id'] ? 'Read' : 'Unread' ?></td>
                <td>
                    <?php if (!$notification['read_id']): ?>
                        <form method="post">
                            <input type="hidden" name="notification_id" value="<?= (int) $notification['id'] ?>">
                            <button name="mark_read" value="1">Mark Read</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php render_footer(); ?>
