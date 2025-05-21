<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure the user has verified their email
    if (!isset($_SESSION['email'])) {
        echo "<script>
                alert('Unauthorized access! Please verify your email first.');
                window.location.href = 'forgot_password.php';
              </script>";
        exit;
    }

    // Retrieve and sanitize form data
    $newPassword = filter_var($_POST['new-password'], FILTER_SANITIZE_STRING);
    $confirmPassword = filter_var($_POST['confirm-password'], FILTER_SANITIZE_STRING);

    // Ensure passwords match
    if ($newPassword !== $confirmPassword) {
        echo "<script>
                alert('Passwords do not match. Please try again.');
                window.history.back();
              </script>";
        exit;
    }

    // Update the password in the database (plain text, no hashing)
    $email = $_SESSION['email'];  // The email of the user is stored in the session
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $newPassword, $email);

    if ($stmt->execute()) {
        // Password updated successfully
        echo "<script>
                alert('Password reset successfully! Please log in with your new password.');
                window.location.href = '../html/studentlogin.html';
              </script>";

        // Unset the session data for security reasons
        unset($_SESSION['email']);
    } else {
        // Error in updating the password
        echo "<script>
                alert('Error resetting password. Please try again.');
                window.history.back();
              </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
