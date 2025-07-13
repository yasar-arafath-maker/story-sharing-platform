<?php
session_start();

// Redirect user if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'author') {
        header("Location: author_dashboard.php");
        exit;
    } elseif ($_SESSION['user_role'] === 'reader') {
        header("Location: reader_dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Story Sharing Platform - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Optional basic styling if css/style.css is missing */
        body {
            background: #121212;
            color: #eee;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #1e1e2f;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(128, 0, 255, 0.4);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: violet;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin: 10px 0 5px;
            color: #ccc;
        }
        input, select {
            padding: 10px;
            border: none;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #2d2d3a;
            color: #fff;
        }
        button {
            padding: 10px;
            background: purple;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        button:hover {
            background: darkviolet;
        }
        p {
            text-align: center;
            margin-top: 15px;
        }
        a {
            color: violet;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome to the Story Sharing Platform</h2>
    <form method="POST" action="login.php">
        <label for="email">Email:</label>
        <input id="email" type="email" name="email" required>

        <label for="password">Password:</label>
        <input id="password" type="password" name="password" required>

        <label for="role">Login as:</label>
        <select id="role" name="role" required>
            <option value="">-- Select Role --</option>
            <option value="author">Author</option>
            <option value="reader">Reader</option>
        </select>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
