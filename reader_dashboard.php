<?php
session_start();
include 'db_connect.php';

// Only allow logged-in readers to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'reader') {
    header("Location: home.php");
    exit;
}

$reader_id = $_SESSION['user_id'];
$reader_name = $_SESSION['user_name'];

// Fetch authors with basic info and number of stories
$sqlAuthors = "
    SELECT u.id, u.name,
           (SELECT COUNT(*) FROM stories s WHERE s.author_id = u.id) AS story_count,
           (SELECT COUNT(*) FROM subscriptions sub WHERE sub.reader_id = ? AND sub.author_id = u.id) AS is_subscribed
    FROM users u
    WHERE u.role = 'author'
";
$stmtAuthors = $conn->prepare($sqlAuthors);
$stmtAuthors->bind_param("i", $reader_id);
$stmtAuthors->execute();
$authors = $stmtAuthors->get_result();

// Fetch recent stories from all authors
$sqlStories = "
    SELECT s.id, s.title, s.content, s.created_at, u.name AS author_name
    FROM stories s
    JOIN users u ON s.author_id = u.id
    ORDER BY s.created_at DESC
    LIMIT 10
";
$stmtStories = $conn->prepare($sqlStories);
$stmtStories->execute();
$stories = $stmtStories->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Reader Dashboard</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="container">
    <h1>Welcome, <?= htmlspecialchars($reader_name) ?>!</h1>

    <hr>

    <h2>Authors</h2>
    <?php if ($authors->num_rows > 0): ?>
        <?php while ($author = $authors->fetch_assoc()): ?>
            <div class="author-box">
                <h3><?= htmlspecialchars($author['name']) ?></h3>
                <p><strong>Stories:</strong> <?= (int)$author['story_count'] ?></p>
                <?php if ($author['is_subscribed']): ?>
                    <button disabled>Subscribed ✓</button>
                <?php else: ?>
                    <form method="POST" action="subscribe.php" style="display:inline;">
                        <input type="hidden" name="author_id" value="<?= (int)$author['id'] ?>" />
                        <button type="submit">Subscribe</button>
                    </form>
                <?php endif; ?>
            </div>
            <hr>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No authors found.</p>
    <?php endif; ?>

    <h2>Recent Stories</h2>
    <?php if ($stories->num_rows > 0): ?>
        <?php while ($story = $stories->fetch_assoc()): ?>
            <div class="story-box">
                <h3><?= htmlspecialchars($story['title']) ?></h3>
                <p><?= nl2br(htmlspecialchars(substr($story['content'], 0, 200))) ?>...</p>
                <p><strong>Author:</strong> <?= htmlspecialchars($story['author_name']) ?> | <strong>Posted:</strong> <?= htmlspecialchars($story['created_at']) ?></p>
                
                <!-- Like Button -->
                <form method="POST" action="like_story.php" style="display:inline;">
    <input type="hidden" name="story_id" value="<?= (int)$story['id'] ?>">
    <button type="submit">👍 Like</button>
</form>

<!-- Comment Button -->
<form method="POST" action="comment_story.php" style="display:inline; margin-left: 10px;">
    <input type="hidden" name="story_id" value="<?= (int)$story['id'] ?>">
    <input type="text" name="comment_text" placeholder="Add comment..." required style="margin-right: 5px;">
    <button type="submit">💬 Comment</button>
</form>

<!-- Read More Button -->
<a href="story.php?id=<?= (int)$story['id'] ?>" style="display:inline-block; margin-left: 10px; text-decoration:none;">
    <button type="button">📖 Read More</button>
</a>
            </div>
            <hr>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No stories available.</p>
    <?php endif; ?>

    <br>
    <a href="logout.php" class="btn logout">🔓 Logout</a>
</div>
</body>
</html>
