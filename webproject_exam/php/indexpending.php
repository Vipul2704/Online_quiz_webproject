<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $fullName = filter_var($_POST['fullName'], FILTER_SANITIZE_STRING);
    $enrollmentId = filter_var($_POST['enrollmentId'], FILTER_SANITIZE_STRING);
    $dob = date('Y-m-d', strtotime($_POST['dob'])); // Format date properly
    $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
    $mobile = filter_var($_POST['mobile'], FILTER_SANITIZE_NUMBER_INT); // Sanitize as integer
    $course = filter_var($_POST['course'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $generatedUsername = filter_var($_POST['generatedUsername'], FILTER_SANITIZE_STRING);
    $securityQuestion1 = filter_var($_POST['securityQuestion1'], FILTER_SANITIZE_STRING);
    $securityAnswer1 = $_POST['securityAnswer1'];
    $securityQuestion2 = filter_var($_POST['securityQuestion2'], FILTER_SANITIZE_STRING);
    $securityAnswer2 = $_POST['securityAnswer2'];

    // Handle profile photo upload
    $profilePhoto = $_FILES['profilePhoto']['name'];
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($profilePhoto);
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (move_uploaded_file($_FILES['profilePhoto']['tmp_name'], $targetFile)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (full_name, enrollment_id, dob, gender, mobile, course, email, password, profile_photo, username, security_question1, security_answer1, security_question2, security_answer2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssss", $fullName, $enrollmentId, $dob, $gender, $mobile, $course, $email, $password, $profilePhoto, $generatedUsername, $securityQuestion1, $securityAnswer1, $securityQuestion2, $securityAnswer2);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Signup Successful! Your account is pending approval. Your username is: $generatedUsername');
                    window.location.href = '../html/studentlogin.html';
                  </script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error uploading profile photo: " . error_get_last()['message'] . "<br>";
    }

    $conn->close();
} else {
    echo "No POST data received<br>";
}
?>