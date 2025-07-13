<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'reader') {
    header("Location: index.php");
    exit;
}

$reader_id = $_SESSION['user_id'];
$reader_name = $_SESSION['user_name'] ?? 'Reader';
$story_id = intval($_POST['story_id'] ?? 0);
$comment_text = trim($_POST['comment_text'] ?? '');

if ($story_id > 0 && !empty($comment_text)) {
    // Insert comment into comments table
    $stmt = $conn->prepare("INSERT INTO comments (user_id, story_id, comment) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("iis", $reader_id, $story_id, $comment_text);
    if (!$stmt->execute()) {
        die("Execute failed: " . htmlspecialchars($stmt->error));
    }
    $stmt->close();

    // Notify the story author
    $author_query = $conn->prepare("
        SELECT u.id AS author_id, s.title 
        FROM stories s 
        JOIN users u ON s.author_id = u.id 
        WHERE s.id = ?
    ");
    if ($author_query === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }
    $author_query->bind_param("i", $story_id);
    $author_query->execute();
    $result = $author_query->get_result();

    if ($result && $result->num_rows > 0) {
        $story = $result->fetch_assoc();
        $author_id = $story['author_id'];
        $message = "$reader_name commented on your story: " . $story['title'];

        $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        if ($notifyStmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }
        $notifyStmt->bind_param("is", $author_id, $message);
        $notifyStmt->execute();
        $notifyStmt->close();
    }
    $author_query->close();
}

// Redirect back to the previous page or reader dashboard
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'reader_dashboard.php'));
exit;
?>
