<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['id'])) {
    echo "No story ID provided.";
    exit;
}

$story_id = (int) $_GET['id'];

// Fetch story and author
$stmt = $conn->prepare("SELECT s.*, u.name AS author_name FROM stories s JOIN users u ON s.author_id = u.id WHERE s.id = ?");
$stmt->bind_param("i", $story_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Story not found.";
    exit;
}

$story = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($story['title']) ?> - Full Story</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #f1f1f1;
            padding: 20px;
        }
        .story-container {
            background-color: #1f1f1f;
            padding: 20px;
            border-radius: 12px;
            max-width: 700px;
            margin: auto;
        }
        .actions {
            margin-top: 20px;
        }
        button {
            padding: 6px 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            margin-right: 10px;
        }
        button.like { background-color: #6a1b9a; color: #fff; }
        button.comment { background-color: #4a148c; color: #fff; }
    </style>
</head>
<body>

<div class="story-container">
    <h2><?= htmlspecialchars($story['title']) ?></h2>
    <p><strong>Author:</strong> <?= htmlspecialchars($story['author_name']) ?></p>
    <hr>
    <p><?= nl2br(htmlspecialchars($story['content'])) ?></p>

    <div class="actions">
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'reader'): ?>
            <!-- Like Button -->
            <form method="POST" action="like_story.php" style="display:inline;">
                <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                <button type="submit" class="like">👍 Like</button>
            </form>

            <!-- Comment Form -->
            <form method="POST" action="comment_story.php" style="display:inline;">
                <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                <input type="text" name="comment_text" placeholder="Add a comment" required style="padding:5px;">
                <button type="submit" class="comment">💬 Comment</button>
            </form>
        <?php else: ?>
            <p><em>Login as a reader to like or comment.</em></p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
