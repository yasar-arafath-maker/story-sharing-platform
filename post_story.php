<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'author') {
    header("Location: home.php"); // Redirect if not logged in as author
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author_id = $_SESSION['user_id'];

    if ($title === "" || $content === "") {
        $message = "Please fill in both title and content.";
    } else {
        $stmt = $conn->prepare("INSERT INTO stories (title, content, author_id) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssi", $title, $content, $author_id);
            if ($stmt->execute()) {
                // Redirect after success to avoid form resubmission
                header("Location: author_dashboard.php?msg=" . urlencode("Story posted successfully!"));
                exit;
            } else {
                $message = "Error posting story: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    }
}

// Show any messages passed via URL
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Post New Story</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="container">
    <h2>Post a New Story</h2>

    <?php if ($message): ?>
        <p style="color: <?= (strpos($message, 'successfully') !== false) ? 'green' : 'red' ?>;">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="post_story.php">
        <label for="title">Story Title:</label><br>
        <input type="text" name="title" id="title" required><br><br>

        <label for="content">Content:</label><br>
        <textarea name="content" id="content" rows="10" required></textarea><br><br>

        <button type="submit">Post Story</button>
    </form>

    <br>
    <a href="author_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
