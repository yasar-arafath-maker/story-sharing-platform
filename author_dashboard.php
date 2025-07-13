<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'author') {
    header("Location: home.php");
    exit;
}

$author_id = $_SESSION['user_id'];
$author_name = $_SESSION['user_name'];

// Fetch stories by this author
$stories = $conn->query("SELECT * FROM stories WHERE author_id = $author_id");

// Fetch subscribers (readers who subscribed to this author)
$subscribers = $conn->query("
    SELECT u.name FROM subscriptions s
    JOIN users u ON s.reader_id = u.id
    WHERE s.author_id = $author_id
");

// Fetch notifications for this author
$notifications = $conn->query("
    SELECT message, created_at FROM notifications
    WHERE user_id = $author_id
    ORDER BY created_at DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Author Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>Welcome, <?= htmlspecialchars($author_name) ?>!</h1>

    <hr>

    <h2>Your Stories</h2>
    <a href="post_story.php">➕ Post New Story</a>
    <?php if ($stories->num_rows > 0): ?>
        <?php while ($story = $stories->fetch_assoc()) { ?>
            <div class="story-box">
                <h3><?= htmlspecialchars($story['title']) ?></h3>
                <p><?= nl2br(htmlspecialchars(substr($story['content'], 0, 200))) ?>...</p>
                <p><strong>Posted:</strong> <?= $story['created_at'] ?></p>
            </div>
        <?php } ?>
    <?php else: ?>
        <p>You haven't posted any stories yet.</p>
    <?php endif; ?>

    <hr>

    <h2>Your Subscribers</h2>
    <?php if ($subscribers->num_rows > 0): ?>
        <ul>
            <?php while ($row = $subscribers->fetch_assoc()) { ?>
                <li><?= htmlspecialchars($row['name']) ?></li>
            <?php } ?>
        </ul>
    <?php else: ?>
        <p>You have no subscribers yet.</p>
    <?php endif; ?>

    <hr>

    <h2>Notifications</h2>
    <?php if ($notifications->num_rows > 0): ?>
        <ul>
            <?php while ($n = $notifications->fetch_assoc()) { ?>
                <li><?= htmlspecialchars($n['message']) ?> <small>(<?= $n['created_at'] ?>)</small></li>
            <?php } ?>
        </ul>
    <?php else: ?>
        <p>No recent notifications.</p>
    <?php endif; ?>

    <br>
    <a href="logout.php">🔓 Logout</a>
</div>
</body>
</html>
