<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email already registered
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        if ($stmt->execute()) {
            // Redirect to home.php after successful registration
            header("Location: home.php");
            exit;
        } else {
            $error = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Story Sharing Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Create an Account</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" action="">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Register as:</label>
        <select name="role" required>
            <option value="author">Author</option>
            <option value="reader">Reader</option>
        </select>

        <button type="submit">Register</button>
        <p>Already have an account? <a href="home.php" class="button">Login</a></p>
    </form>
</div>
</body>
</html>
