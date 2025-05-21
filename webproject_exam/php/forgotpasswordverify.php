<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $securityQuestion1 = filter_var($_POST['security-question-1'], FILTER_SANITIZE_STRING);
    $answer1 = filter_var($_POST['answer-1'], FILTER_SANITIZE_STRING);
    $securityQuestion2 = filter_var($_POST['security-question-2'], FILTER_SANITIZE_STRING);
    $answer2 = filter_var($_POST['answer-2'], FILTER_SANITIZE_STRING);

    // Prepare and execute query to verify answers
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND security_question1 = ? AND security_answer1 = ? AND security_question2 = ? AND security_answer2 = ?");
    $stmt->bind_param("sssss", $email, $securityQuestion1, $answer1, $securityQuestion2, $answer2);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Security questions and answers match
        echo "<script>
                alert('Verification successful! You can now reset your password.');
                window.location.href = '../php/resetpassword.php';
              </script>";
    } else {
        // Security questions and answers do not match
        echo "<script>
                alert('Verification failed! Please check your answers and try again.');
                window.history.back();
              </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
