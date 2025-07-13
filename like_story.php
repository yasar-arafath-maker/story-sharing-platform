<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in as a reader
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'reader') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];  // Changed from $reader_id to $user_id for clarity
$story_id = isset($_POST['story_id']) ? intval($_POST['story_id']) : 0;

if ($story_id > 0) {
    // Check if the story has already been liked by this user
    $check = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND story_id = ?");
    $check->bind_param("ii", $user_id, $story_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        // Insert like into the database
        $stmt = $conn->prepare("INSERT INTO likes (user_id, story_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $story_id);

        if ($stmt->execute()) {
            // Fetch the author ID and story title to notify the author
            $author_query = $conn->prepare("
                SELECT u.id AS author_id, s.title 
                FROM stories s 
                JOIN users u ON s.author_id = u.id 
                WHERE s.id = ?
            ");
            $author_query->bind_param("i", $story_id);
            $author_query->execute();
            $result = $author_query->get_result();

            if ($result && $result->num_rows > 0) {
                $story = $result->fetch_assoc();
                $author_id = $story['author_id'];
                $story_title = $story['title'];
                $user_name = htmlspecialchars($_SESSION['user_name']);

                $message = "$user_name liked your story: $story_title";

                // Insert notification for author
                $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notif->bind_param("is", $author_id, $message);
                $notif->execute();
                $notif->close();
            }

            $author_query->close();
        }
        $stmt->close();
    }

    $check->close();
}

// Redirect back to the referring page or dashboard
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'reader_dashboard.php'));
exit;
?>
