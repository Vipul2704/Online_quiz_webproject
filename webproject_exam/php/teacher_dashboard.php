<?php
session_start();
include 'db.php';

// Redirect to login page if the teacher is not logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacherlogin.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: teacherlogin.php");
    exit();
}

$success_message = $error_message = '';

// Handle GET requests for exam details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $exam_id = $_GET['id'];
    
    // Get exam details
    $sql_exam = "SELECT * FROM exams WHERE id = ?";
    $stmt_exam = $conn->prepare($sql_exam);
    $stmt_exam->bind_param("i", $exam_id);
    $stmt_exam->execute();
    $exam_result = $stmt_exam->get_result();
    $exam_data = $exam_result->fetch_assoc();
    
    // Get questions for this exam
    $sql_questions = "SELECT * FROM questions WHERE exam_id = ?";
    $stmt_questions = $conn->prepare($sql_questions);
    $stmt_questions->bind_param("i", $exam_id);
    $stmt_questions->execute();
    $questions_result = $stmt_questions->get_result();
    $questions = [];
    while ($row = $questions_result->fetch_assoc()) {
        $questions[] = $row;
    }
    
    // Combine exam data and questions
    $response = [
        'exam' => $exam_data,
        'questions' => $questions
    ];
    
    echo json_encode($response);
    exit();
}

// Handle DELETE requests for exam deletion
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $exam_id = $_GET['id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete questions first
        $sql_questions = "DELETE FROM questions WHERE exam_id = ?";
        $stmt_questions = $conn->prepare($sql_questions);
        $stmt_questions->bind_param("i", $exam_id);
        $stmt_questions->execute();
        
        // Then delete exam
        $sql_exam = "DELETE FROM exams WHERE id = ?";
        $stmt_exam = $conn->prepare($sql_exam);
        $stmt_exam->bind_param("i", $exam_id);
        $stmt_exam->execute();
        
        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// Handle POST requests for exam creation/update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['user_id'])) {
        // Handle student approval
        $user_id = $_POST['user_id'];
        $status = $_POST['status'];
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);

        $sql = "UPDATE users SET status = '$status', teacher_comment = '$comment' WHERE id = $user_id";
        $conn->query($sql);
    } elseif (isset($_POST['exam_id'])) {
        // Handle exam update
        $exam_id = $_POST['exam_id'];
        $subject_name = mysqli_real_escape_string($conn, $_POST['subject']);
        $exam_number = mysqli_real_escape_string($conn, $_POST['exam_number']);
        $start_datetime = mysqli_real_escape_string($conn, $_POST['start_time']);
        $end_datetime = mysqli_real_escape_string($conn, $_POST['end_time']);
        $is_active = isset($_POST['visibility']) ? 1 : 0;
        
        // Check if new exam number is unique (excluding current exam)
        $check_sql = "SELECT id FROM exams WHERE exam_number = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $exam_number, $exam_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Exam number must be unique!']);
            exit();
        }
        
        // Update exam details
        $sql_exam = "UPDATE exams SET subject_name = ?, exam_number = ?, 
                     start_datetime = ?, end_datetime = ?, is_active = ? 
                     WHERE id = ?";
        $stmt_exam = $conn->prepare($sql_exam);
        $stmt_exam->bind_param("ssssis", $subject_name, $exam_number, 
                              $start_datetime, $end_datetime, $is_active, $exam_id);
        
        if ($stmt_exam->execute()) {
            // Delete existing questions
            $delete_questions = "DELETE FROM questions WHERE exam_id = ?";
            $stmt_delete = $conn->prepare($delete_questions);
            $stmt_delete->bind_param("i", $exam_id);
            $stmt_delete->execute();
            
            // Insert updated questions
            if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                foreach ($_POST['questions'] as $question) {
                    $sql_question = "INSERT INTO questions 
                                   (question_text, option1, option2, option3, option4, 
                                    correct_option, exam_id) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt_question = $conn->prepare($sql_question);
                    $stmt_question->bind_param("sssssii", 
                        $question['question_text'],
                        $question['option1'],
                        $question['option2'],
                        $question['option3'],
                        $question['option4'],
                        $question['correct_option'],
                        $exam_id
                    );
                    $stmt_question->execute();
                }
            }
            $success_message = "Exam updated successfully!";
        } else {
            $error_message = "Error updating exam: " . $conn->error;
        }
    } else {
        // Handle new exam creation
        $subject_name = mysqli_real_escape_string($conn, $_POST['subject']);
        $exam_number = mysqli_real_escape_string($conn, $_POST['exam_number']);
        $start_datetime = mysqli_real_escape_string($conn, $_POST['start_time']);
        $end_datetime = mysqli_real_escape_string($conn, $_POST['end_time']);
        $is_active = isset($_POST['visibility']) ? 1 : 0;

        // Check if exam number is unique
        $check_exam = "SELECT id FROM exams WHERE exam_number = ?";
        $stmt_check = $conn->prepare($check_exam);
        $stmt_check->bind_param("s", $exam_number);
        $stmt_check->execute();
        $exam_result = $stmt_check->get_result();

        if ($exam_result->num_rows > 0) {
            $error_message = "Exam number must be unique!";
        } else {
            // Create new exam
            $sql_exam = "INSERT INTO exams (subject_name, exam_number, start_datetime, end_datetime, is_active) 
                        VALUES (?, ?, ?, ?, ?)";
            $stmt_exam = $conn->prepare($sql_exam);
            $stmt_exam->bind_param("ssssi", $subject_name, $exam_number, $start_datetime, $end_datetime, $is_active);
            
            if ($stmt_exam->execute()) {
                $exam_id = $conn->insert_id;
                
                // Insert questions
                if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                    foreach ($_POST['questions'] as $question) {
                        $sql_question = "INSERT INTO questions 
                                       (question_text, option1, option2, option3, option4, 
                                        correct_option, exam_id) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt_question = $conn->prepare($sql_question);
                        $stmt_question->bind_param("sssssii", 
                            $question['question_text'],
                            $question['option1'],
                            $question['option2'],
                            $question['option3'],
                            $question['option4'],
                            $question['correct_option'],
                            $exam_id
                        );
                        $stmt_question->execute();
                    }
                }
                $success_message = "Exam created successfully!";
            } else {
                $error_message = "Error creating exam: " . $conn->error;
            }
        }
    }
}

// Fetch pending students
$sql = "SELECT * FROM users WHERE status = 'pending'";
$result = $conn->query($sql);

// Fetch existing exams
$sql_exams = "SELECT * FROM exams ORDER BY created_at DESC";
$exams_result = $conn->query($sql_exams);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
        }
        .container {
            display: flex;
            height: calc(100% - 60px);
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            background-color: #ecf0f1;
        }
        .header {
            background-color: #3498db;
            color: white;
            padding: 15px 20px;
            font-size: 24px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .menu-item {
            padding: 10px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .menu-item:hover {
            background-color: #34495e;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            margin-top: 20px;
            cursor: pointer;
        }
        .welcome-message {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .instruction {
            font-size: 16px;
            color: #7f8c8d;
        }
        .content-area {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"], select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .success-message {
            color: green;
            margin-bottom: 10px;
        }
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
        .edit-btn, .delete-btn {
    padding: 5px 10px;
    margin: 0 5px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

.edit-btn {
    background-color: #3498db;
    color: white;
}

.delete-btn {
    background-color: #e74c3c;
    color: white;
}

.existing-questions {
    margin-top: 30px;
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.question-block {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px 0;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;
}

.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;
}
    </style>
</head>
<body>
<div class="header">
        <span id="welcome-text">Welcome, <?php echo $_SESSION['teacher_name']; ?></span>
        <span id="username-display"><?php echo $_SESSION['teacher_username']; ?></span>
    </div>
    <div class="container">
        <div class="sidebar">
            <div class="menu-item" data-option="home">Home</div>
            <div class="menu-item" data-option="approve-student">Approve Student</div>
            <div class="menu-item" data-option="student-report">Student Report</div>
            <div class="menu-item" data-option="create-question-paper">Create Question Paper</div>
            <div class="menu-item" data-option="exam-resultsheet">Exam Resultsheet</div>
            <div class="menu-item" data-option="change-password">Change Password</div>
            <button class="logout-btn">Logout</button>
        </div>
        <div class="main-content">
            <div id="content-area" class="content-area">
                <h1 class="welcome-message">Welcome to your Dashboard, <?php echo $_SESSION['teacher_name']; ?></h1>
                <p class="instruction">Please select an option from the menu to get started.</p>

                <div id="approve-student-content" style="display: none;">
                    <h2>Pending Student Approvals</h2>
                    <table>
                        <tr>
                            <th>Name</th>
                            <th>Enrollment ID</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Action</th>
                        </tr>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['enrollment_id']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['course']; ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <select name="status">
                                        <option value="approved">Approve</option>
                                        <option value="rejected">Reject</option>
                                    </select>
                                    <input type="text" name="comment" placeholder="Comment (optional)">
                                    <button type="submit">Submit</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>

                <!-- Update only the create-question-paper-content div -->
<div id="create-question-paper-content" style="display: none;">
    <h2>Create Question Paper</h2>
    <div id="message-container">
        <?php
        if (!empty($success_message)) {
            echo "<p class='success-message'>$success_message</p>";
        }
        if (!empty($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>
    </div>
    <form id="question-form" method="POST" action="">
        <div class="form-group">
            <label for="exam_number">Exam Number (must be unique):</label>
            <input type="text" id="exam_number" name="exam_number" required>
        </div>
        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
        </div>
        <div class="form-group">
            <label for="start_time">Start Time:</label>
            <input type="datetime-local" id="start_time" name="start_time" required>
        </div>
        <div class="form-group">
            <label for="end_time">End Time:</label>
            <input type="datetime-local" id="end_time" name="end_time" required>
        </div>
        <div class="form-group">
            <label for="question_text">Question:</label>
            <textarea id="question_text" name="question_text" required></textarea>
        </div>
        <div class="form-group">
            <label for="option1">Option 1:</label>
            <input type="text" id="option1" name="option1" required>
        </div>
        <div class="form-group">
            <label for="option2">Option 2:</label>
            <input type="text" id="option2" name="option2" required>
        </div>
        <div class="form-group">
            <label for="option3">Option 3:</label>
            <input type="text" id="option3" name="option3" required>
        </div>
        <div class="form-group">
            <label for="option4">Option 4:</label>
            <input type="text" id="option4" name="option4" required>
        </div>
        <div class="form-group">
            <label for="correct_option">Correct Option:</label>
            <select id="correct_option" name="correct_option" required>
                <option value="1">Option 1</option>
                <option value="2">Option 2</option>
                <option value="3">Option 3</option>
                <option value="4">Option 4</option>
            </select>
        </div>
        <div class="form-group">
            <label for="visibility">Make Exam Active:</label>
            <input type="checkbox" id="visibility" name="visibility" value="1">
        </div>
        <button type="submit">Create Question Paper</button>
    </form>

    <!-- Add a section to display existing questions -->
    <div class="existing-questions">
        <h3>Existing Question Papers</h3>
        <table>
            <thead>
                <tr>
                    <th>Exam Number</th>
                    <th>Subject</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($exam = $exams_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($exam['exam_number']); ?></td>
                    <td><?php echo htmlspecialchars($exam['subject_name']); ?></td>
                    <td><?php echo $exam['start_datetime']; ?></td>
                    <td><?php echo $exam['end_datetime']; ?></td>
                    <td><?php echo $exam['is_active'] ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <button onclick="editExam(<?php echo $exam['id']; ?>)" class="edit-btn">Edit</button>
                        <button onclick="deleteExam(<?php echo $exam['id']; ?>)" class="delete-btn">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contentArea = document.getElementById('content-area');
            const menuItems = document.querySelectorAll('.menu-item');
            const logoutBtn = document.querySelector('.logout-btn');

            const contentTemplates = {
                home: `<h2>Home</h2><p>Welcome to your dashboard. Here you can manage students, create question papers, view reports, and more.</p>`,
                'approve-student': document.getElementById('approve-student-content').innerHTML,
                'student-report': `<h2>Student Report</h2><p>Functionality to be implemented.</p>`,
                'create-question-paper': document.getElementById('create-question-paper-content').innerHTML,
                'exam-resultsheet': `<h2>Exam Resultsheet</h2><p>Functionality to be implemented.</p>`,
                'change-password': `
                    <h2>Change Password</h2>
                    <form id="change-password-form">
                        <div class="form-group">
                            <label for="current_password">Current Password:</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password:</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password:</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit">Change Password</button>
                    </form>
                    <div id="password-message"></div>
                `
            };

            function showContent(option) {
                contentArea.innerHTML = contentTemplates[option] || 'Content not available';
                if (option === 'create-question-paper') {
                    window.location.href = 'create_q.php';
                } else if (option === 'change-password') {
                    setupChangePasswordForm();
                }
            }

            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    const option = this.getAttribute('data-option');
                    showContent(option);
                });
            });

            logoutBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = '?logout=1';
                }
            });

            function setupQuestionForm() {
                const form = document.getElementById('question-form');
                const messageContainer = document.getElementById('message-container');

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);

                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newMessageContainer = doc.getElementById('message-container');
                        
                        if (newMessageContainer) {
                            messageContainer.innerHTML = newMessageContainer.innerHTML;
                        }
                        
                        form.reset();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        messageContainer.innerHTML = '<p class="error-message">An error occurred. Please try again.</p>';
                    });
                });
            }

            function editExam(examId) {
    if (confirm('Do you want to edit this exam?')) {
        // Fetch exam details and populate the form
        fetch(`get_exam.php?id=${examId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('exam_number').value = data.exam_number;
                document.getElementById('subject').value = data.subject_name;
                document.getElementById('start_time').value = data.start_datetime;
                document.getElementById('end_time').value = data.end_datetime;
                document.getElementById('visibility').checked = data.is_active == 1;
                // Add hidden input for exam_id
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'exam_id';
                hiddenInput.value = examId;
                document.getElementById('question-form').appendChild(hiddenInput);
            });
    }
}

function deleteExam(examId) {
    if (confirm('Are you sure you want to delete this exam? This action cannot be undone.')) {
        fetch(`delete_exam.php?id=${examId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting exam: ' + data.message);
            }
        });
    }
}

function setupChangePasswordForm() {
    const form = document.getElementById('change-password-form');
    const messageContainer = document.getElementById('password-message');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const currentPassword = form.current_password.value;
            const newPassword = form.new_password.value;
            const confirmPassword = form.confirm_password.value;
            
            // Clear previous messages
            messageContainer.innerHTML = '';
            
            // Basic validation
            if (!currentPassword || !newPassword || !confirmPassword) {
                messageContainer.innerHTML = '<p class="error-message">All fields are required</p>';
                return;
            }
            
            if (newPassword !== confirmPassword) {
                messageContainer.innerHTML = '<p class="error-message">New passwords do not match</p>';
                return;
            }

            if (newPassword.length < 6) {
                messageContainer.innerHTML = '<p class="error-message">New password must be at least 6 characters long</p>';
                return;
            }

            const formData = new FormData(form);

            // Show loading message
            messageContainer.innerHTML = '<p>Processing...</p>';

            fetch('change_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response:', data); // For debugging
                if (data.success) {
                    messageContainer.innerHTML = `<p class="success-message">${data.message}</p>`;
                    form.reset();
                } else {
                    let errorMessage = data.message || 'An error occurred';
                    if (data.debug) {
                        console.error('Debug:', data.debug);
                    }
                    messageContainer.innerHTML = `<p class="error-message">${errorMessage}</p>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageContainer.innerHTML = `<p class="error-message">An error occurred. Please try again. (${error.message})</p>`;
            });
        });
    }
}
            // Show initial content
            showContent('home');
        });
    </script>
</body>
</html>