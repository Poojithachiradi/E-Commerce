<?php
session_start();
include 'db.php'; // Ensure this file correctly connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Check if the stored password is hashed or plaintext
        if (password_get_info($hashed_password)['algo'] > 0) { 
            // Hashed password, verify using password_verify()
            if (password_verify($password, $hashed_password)) {
                $_SESSION['admin_id'] = $id;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            // Plain text password found in DB (BAD PRACTICE - Need to fix)
            if ($password === $hashed_password) {
                // Upgrade to hashed password
                $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $id);
                $update_stmt->execute();
                $update_stmt->close();

                $_SESSION['admin_id'] = $id;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        }
    } else {
        $error = "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .login-box { width: 300px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { width: 100%; padding: 10px; background: green; color: white; border: none; }
        .error { color: red; }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>Admin Login</h2>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>

</body>
</html>

