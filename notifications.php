<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'author') {
    header("Location: index.php");
    exit;
}

$author_id = $_SESSION['user_id'];

// Fetch notifications for author (latest first)
$stmt = $conn->prepare("SELECT id, message, is_read, created_at FROM notifications WHERE author_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $author_id);
$stmt->execute();
$result = $stmt->get_result();

// Optional: Mark all notifications as read after fetching
$updateStmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE author_id = ?");
$updateStmt->bind_param("i", $author_id);
$updateStmt->execute();
$updateStmt->close();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Notifications</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .notification {
            border: 1px solid #6a0dad;
            background-color: #1a1a2e;
            padding: 15px;
            margin-bottom: 10px;
            color: white;
            border-radius: 5px;
        }
        .notification.unread {
            background-color: #3a0ca3;
            font-weight: bold;
        }
        .notification small {
            display: block;
            color: #ccc;
            margin-top: 5px;
        }
        .button {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 12px;
            background-color: #6a0dad;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .button:hover {
            background-color: #570f9e;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="author_dashboard.php" class="button">&larr; Back to Dashboard</a>
    <h2>Your Notifications</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($notification = $result->fetch_assoc()): ?>
            <div class="notification <?= $notification['is_read'] ? '' : 'unread' ?>">
                <?= htmlspecialchars($notification['message']) ?>
                <small><?= date('d M Y, H:i', strtotime($notification['created_at'])) ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No notifications found.</p>
    <?php endif; ?>

</div>
</body>
</html>
