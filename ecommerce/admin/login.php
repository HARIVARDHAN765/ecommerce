<?php
session_start();
include('../includes/db.php'); // Make sure this path is correct

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists and is an admin
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Successful login
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Invalid admin credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            font-size: 1.1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: #e74c3c;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Admin Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>


