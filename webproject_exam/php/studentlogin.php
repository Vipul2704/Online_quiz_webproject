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

        // Verify password (if hashed, otherwise use === for plain text)
        if ($password === $user['password']) {
            // Set session variables upon successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['status'] = $user['status'];

            // Check if the account status is pending, approved, or rejected
            if ($user['status'] == 'approved') {
                echo "<script>
                        alert('Login Successful! Welcome, {$user['username']}');
                        window.location.href = 'student_dashboard.php'; // Adjusted path
                      </script>";
            } elseif ($user['status'] == 'pending') {
                echo "<script>
                        alert('Your account is still pending approval.');
                        window.location.href = '../html/studentlogin.html'; // Redirect back to login or info page
                      </script>";
            } elseif ($user['status'] == 'rejected') {
                echo "<script>
                        alert('Your account has been rejected. Please contact support.');
                        window.location.href = '../html/home.html';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Invalid password. Please try again.');
                    window.location.href = '../html/studentlogin.html'; // Redirect back to login page
                  </script>";
        }
    } else {
        echo "<script>
                alert('No account found with the provided username and email.');
                window.location.href = '../html/studentlogin.html'; // Redirect back to login page
              </script>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
