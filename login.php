<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($email) || empty($password) || empty($role)) {
        die("Please fill in all fields.");
    }

    // Prepare statement to find user by email and role
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Assuming passwords are hashed using password_hash()
        if (password_verify($password, $user['password'])) {
            // Password matches - log the user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $role;

            // Redirect based on role
            if ($role === 'author') {
                header("Location: author_dashboard.php");
                exit;
            } elseif ($role === 'reader') {
                header("Location: reader_dashboard.php");
                exit;
            }
        } else {
            // Invalid password
            $error = "Invalid email or password.";
        }
    } else {
        // User not found or wrong role
        $error = "Invalid email or password.";
    }
} else {
    // If accessed not via POST, redirect to home
    header("Location: home.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login Error</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="container">
    <h2>Login Error</h2>
    <p><?= htmlspecialchars($error ?? 'Unknown error occurred.') ?></p>
    <p><a href="home.php">Go back to login</a></p>
</div>
</body>
</html>
