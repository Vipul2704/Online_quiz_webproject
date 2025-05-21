<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Prepare SQL query to fetch the user by username and email
    $sql = "SELECT * FROM users WHERE username = ? AND email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if a user was found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the plain password matches
        if ($password === $user['password']) {
            // Set session variables upon successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['status'] = $user['status'];

            // Check account status
            if ($user['status'] == 'approved') {
                echo "<script>
                        alert('Login Successful! Welcome, {$user['username']}');
                        window.location.href = 'student_dashboard.php'; // Redirect to dashboard
                      </script>";
            } elseif ($user['status'] == 'pending') {
                echo "<script>
                        alert('Your account is pending approval.');
                        window.location.href = 'login.php'; // Redirect back to login
                      </script>";
            } elseif ($user['status'] == 'rejected') {
                echo "<script>
                        alert('Your account has been rejected. Please contact support.');
                        window.location.href = 'login.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Invalid password. Please try again.');
                    window.location.href = 'login.php'; // Redirect back to login
                  </script>";
        }
    } else {
        echo "<script>
                alert('No account found with the provided username and email.');
                window.location.href = 'login.php'; // Redirect back to login
              </script>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .login-container {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .login-btn {
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-btn:hover {
            background-color: #2980b9;
        }
        .error-msg {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Student Login</h2>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
</body>
</html>
