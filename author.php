<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: reader_dashboard.php");
    exit;
}

$author_id = intval($_GET['id']);

// Fetch author info
$stmt = $conn->prepare("SELECT name, bio FROM users WHERE id = ? AND role = 'author'");
$stmt->bind_param("i", $author_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "Author not found.";
    exit;
}
$stmt->bind_result($author_name, $author_bio);
$stmt->fetch();
$stmt->close();

// Count subscribers
$sub_stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE author_id = ?");
$sub_stmt->bind_param("i", $author_id);
$sub_stmt->execute();
$sub_stmt->bind_result($sub_count);
$sub_stmt->fetch();
$sub_stmt->close();

// Count stories
$story_count_stmt = $conn->prepare("SELECT COUNT(*) FROM stories WHERE author_id = ?");
$story_count_stmt->bind_param("i", $author_id);
$story_count_stmt->execute();
$story_count_stmt->bind_result($story_count);
$story_count_stmt->fetch();
$story_count_stmt->close();

// Get stories
$story_stmt = $conn->prepare("SELECT id, title, content, created_at FROM stories WHERE author_id = ? ORDER BY created_at DESC");
$story_stmt->bind_param("i", $author_id);
$story_stmt->execute();
$stories = $story_stmt->get_result();

// Check if user is subscribed
$isSubscribed = false;
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'reader') {
    $reader_id = $_SESSION['user_id'];
    $check = $conn->prepare("SELECT id FROM subscriptions WHERE reader_id = ? AND author_id = ?");
    $check->bind_param("ii", $reader_id, $author_id);
    $check->execute();
    $check->store_result();
    $isSubscribed = $check->num_rows > 0;
    $check->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Author Profile - <?= htmlspecialchars($author_name) ?></title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="container">
    <h2>👤 <?= htmlspecialchars($author_name) ?>'s Profile</h2>

    <?php if (!empty($author_bio)): ?>
        <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($author_bio)) ?></p>
    <?php endif; ?>

    <p>📢 Subscribers: <?= $sub_count ?></p>
    <p>📚 Stories Published: <?= $story_count ?></p>

    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reader'): ?>
        <?php if (!$isSubscribed): ?>
            <form action="subscribe.php" method="POST" style="margin-bottom: 20px;">
                <input type="hidden" name="author_id" value="<?= $author_id ?>">
                <button type="submit" class="button violet">🔔 Subscribe</button>
            </form>
        <?php else: ?>
            <p>✅ You are subscribed to this author.</p>
        <?php endif; ?>
    <?php endif; ?>

    <h3>📝 Stories by <?= htmlspecialchars($author_name) ?>:</h3>
    <?php if ($stories->num_rows > 0): ?>
        <?php while ($story = $stories->fetch_assoc()): ?>
            <div class="story-box">
                <h4><?= htmlspecialchars($story['title']) ?></h4>
                <p><?= nl2br(htmlspecialchars($story['content'])) ?></p>
                <small>🗓 Published on <?= date('d M Y', strtotime($story['created_at'])) ?></small>
                <br><br>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reader'): ?>
                    <form action="like_story.php" method="POST" style="display:inline-block;">
                        <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                        <button type="submit" class="button">👍 Like</button>
                    </form>

                    <form action="comment_story.php" method="POST" style="display:inline-block;">
                        <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                        <select name="emoji_comment" required>
                            <option value="">💬 Comment</option>
                            <option value="😊">😊</option>
                            <option value="😢">😢</option>
                            <option value="🔥">🔥</option>
                            <option value="❤️">❤️</option>
                        </select>
                        <button type="submit" class="button">Send</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No stories published yet.</p>
    <?php endif; ?>

    <br>
    <a href="<?= (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reader') ? 'reader_dashboard.php' : 'author_dashboard.php' ?>" class="button">⬅️ Back</a>
</div>
</body>
</html>
<?php
$story_stmt->close();
$conn->close();
?>
