<?php
session_start();
include 'db_connect.php';

// Only logged-in readers can subscribe
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'reader') {
    header("Location: login.php"); // or wherever you want
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reader_id = $_SESSION['user_id'];
    $author_id = intval($_POST['author_id'] ?? 0);

    if ($author_id <= 0) {
        // Invalid author ID
        $_SESSION['error'] = "Invalid author.";
        header("Location: reader_dashboard.php");
        exit;
    }

    // Check if author exists and has role author
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'author'");
    $stmt->bind_param("i", $author_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $_SESSION['error'] = "Author not found.";
        header("Location: reader_dashboard.php");
        exit;
    }
    $stmt->close();

    // Check if already subscribed
    $check = $conn->prepare("SELECT id FROM subscriptions WHERE reader_id = ? AND author_id = ?");
    $check->bind_param("ii", $reader_id, $author_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Already subscribed
        $check->close();
        $_SESSION['message'] = "You are already subscribed to this author.";
        header("Location: author.php?id=" . $author_id);
        exit;
    }
    $check->close();

    // Insert subscription
    $insert = $conn->prepare("INSERT INTO subscriptions (reader_id, author_id) VALUES (?, ?)");
    if (!$insert) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: author.php?id=" . $author_id);
        exit;
    }
    $insert->bind_param("ii", $reader_id, $author_id);
    if ($insert->execute()) {
        $_SESSION['message'] = "Subscribed successfully!";
    } else {
        $_SESSION['error'] = "Subscription failed: " . $insert->error;
    }
    $insert->close();

    header("Location: author.php?id=" . $author_id);
    exit;
} else {
    // Invalid access method
    header("Location: reader_dashboard.php");
    exit;
}
?>
