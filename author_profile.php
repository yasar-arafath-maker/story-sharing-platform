<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: reader_dashboard.php");
    exit;
}

$author_id = intval($_GET['id']);

// Fetch author info
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id = ? AND role = 'author'");
$stmt->bind_param("i", $author_id);
$stmt->execute();
$author_result = $stmt->get_result();

if ($author_result->num_rows === 0) {
    echo "Author not found.";
    exit;
}
$author = $author_result->fetch_assoc();
$stmt->close();

// Fetch number of subscribers
$subscribers_stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE author_id = ?");
$subscribers_stmt->bind_param("i", $author_id);
$subscribers_stmt->execute();
$subscribers_stmt->bind_result($subscriber_count);
$subscribers_stmt->fetch();
$subscribers_stmt->close();

// Fetch number of stories
$stories_count_stmt = $conn->prepare("SELECT COUNT(*) FROM stories WHERE author_id = ?");
$stories_count_stmt->bind_param("i", $author_id);
$stories_count_stmt->execute();
$stories_count_stmt->bind_result($stories_count);
$stories_count_stmt->fetch();
$stories_count_stmt->close();

// Fetch stories list
$stories_stmt = $conn->prepare("SELECT id, title, created_at FROM stories WHERE author_id = ? ORDER BY created_at DESC");
$stories_stmt->bind_param("i", $author_id);
$stories_stmt->execute();
$stories_result = $stories_stmt->get_result();

// Check subscription status if reader
$is_subscribed = false;
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'reader') {
    $check_sub = $conn->prepare("SELECT id FROM subscriptions WHERE reader_id = ? AND author_id = ?");
    $check_sub->bind_param("ii", $_SESSION['user_id'], $author_id);
    $check_sub->execute();
    $check_sub->store_result();
    $is_subscribed = ($check_sub->num_rows > 0);
    $check_sub->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Author Profile - <?= htmlspecialchars($author['name']) ?></title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="container">
    <a href="reader_dashboard.php" class="button">&larr; Back to Dashboard</a>
    <h2>Author Profile: <?= htmlspecialchars($author['name']) ?></h2>

    <p><strong>Email:</strong> <?= htmlspecialchars($author['email']) ?></p>
    <p><strong>Joined on:</strong> <?= date('d M Y', strtotime($author['created_at'])) ?></p>
    <p><strong>Subscribers:</strong> <?= $subscriber_count ?></p>
    <p><strong>Stories Published:</strong> <?= $stories_count ?></p>

    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reader') : ?>
        <form action="subscribe.php" method="post" style="margin: 20px 0;">
            <input type="hidden" name="author_id" value="<?= $author_id ?>" />
            <?php if ($is_subscribed) : ?>
                <button type="submit" class="button" disabled>Subscribed</button>
            <?php else : ?>
                <button type="submit" class="button violet">Subscribe</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <h3>Stories by <?= htmlspecialchars($author['name']) ?></h3>
    <?php if ($stories_result->num_rows > 0) : ?>
        <ul class="story-list">
            <?php while ($story = $stories_result->fetch_assoc()) : ?>
                <li>
                    <a href="story.php?id=<?= $story['id'] ?>"><?= htmlspecialchars($story['title']) ?></a>
                    <small>(Published on <?= date('d M Y', strtotime($story['created_at'])) ?>)</small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>No stories published yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
