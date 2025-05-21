<?php
// Start session
session_start();

if (!isset($_SESSION['email'])) {
    echo "<script>
            alert('Unauthorized access! Please verify your email first.');
            window.location.href = 'forgot_password.php';
          </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/resetpassword.css">
</head>
<body>
    <div class="reset-password-container">
        <form action="../php/reset_pass_process.php" method="POST" class="reset-password-form">
            <h2>Reset Password</h2>
            
            <label for="new-password">New Password</label>
            <input type="password" id="new-password" name="new-password" required>
            
            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm-password" required>
            
            <button type="submit" class="reset-btn">Reset Password</button>
        </form>
    </div>
</body>
</html>
