<?php
session_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "test");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_exam'])) {
        $subject_name = $_POST['subject_name'];
        $exam_number = $_POST['exam_number'];
        
        // Insert subject if new
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
        $stmt->bind_param("s", $subject_name);
        $stmt->execute();
        $subject_id = $stmt->insert_id;
        
        // Create exam
        $stmt = $conn->prepare("INSERT INTO exams (exam_number, subject_id) VALUES (?, ?)");
        $stmt->bind_param("si", $exam_number, $subject_id);
        $stmt->execute();
        
        $_SESSION['message'] = "Exam created successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['add_question'])) {
        $exam_id = $_POST['exam_id'];
        $question = $_POST['question'];
        $options = array(
            $_POST['option1'],
            $_POST['option2'],
            $_POST['option3'],
            $_POST['option4']
        );
        $correct = $_POST['correct_option'];
        
        $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, option1, option2, option3, option4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $exam_id, $question, $options[0], $options[1], $options[2], $options[3], $correct);
        $stmt->execute();
        
        $_SESSION['message'] = "Question added successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['update_question'])) {
        $question_id = $_POST['question_id'];
        $question = $_POST['question'];
        $options = array(
            $_POST['option1'],
            $_POST['option2'],
            $_POST['option3'],
            $_POST['option4']
        );
        $correct = $_POST['correct_option'];
        
        $stmt = $conn->prepare("UPDATE questions SET question_text = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, correct_option = ? WHERE id = ?");
        $stmt->bind_param("sssssii", $question, $options[0], $options[1], $options[2], $options[3], $correct, $question_id);
        $stmt->execute();
        
        $_SESSION['message'] = "Question updated successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['delete_question'])) {
        $question_id = $_POST['question_id'];
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        
        $_SESSION['message'] = "Question deleted successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['toggle_exam'])) {
        $exam_id = $_POST['exam_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE exams SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $exam_id);
        $stmt->execute();
        
        $_SESSION['message'] = "Exam status updated successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Add this at the top of your PHP section where other GET handlers are
if (isset($_GET['edit_question'])) {
    $question_id = $_GET['edit_question'];
    $stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question_data = $result->fetch_assoc();
    
    // Set header to return JSON
    header('Content-Type: application/json');
    echo json_encode($question_data);
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Question Paper Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5f5f5;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --text-color: #333;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #f9f9f9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .question-list {
            margin-top: 2rem;
        }

        .question-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .question-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .options-list {
            list-style-type: none;
            padding-left: 0;
        }

        .options-list li {
            padding: 0.5rem;
            margin: 0.5rem 0;
            background: var(--secondary-color);
            border-radius: 4px;
        }

        .correct-option {
            background-color: #d4edda;
            color: #155724;
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }

        .toggle-btn {
            background-color: #6c757d;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-btn.active {
            background-color: var(--success-color);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--primary-color);
            transition: color 0.3s ease;
        }

        .icon-button:hover {
            color: var(--text-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .card {
                padding: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
        }

    </style>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chalkboard-teacher"></i> Teacher Question Paper Management</h1>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert">
                <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fas fa-plus-circle"></i> Create New Exam</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Subject Name:</label>
                    <input type="text" name="subject_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Exam Number:</label>
                    <input type="text" name="exam_number" class="form-control" required>
                </div>
                <button type="submit" name="create_exam" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Exam
                </button>
            </form>
        </div>

        <div class="card">
            <h2><i class="fas fa-question-circle"></i> Add Questions</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Select Exam:</label>
                    <select name="exam_id" class="form-control" required>
                        <?php
                        $result = $conn->query("SELECT e.id, e.exam_number, s.subject_name FROM exams e JOIN subjects s ON e.subject_id = s.id");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='".$row['id']."'>".$row['subject_name']." - ".$row['exam_number']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Question:</label>
                    <textarea name="question" class="form-control" required></textarea>
                </div>
                <?php for($i = 1; $i <= 4; $i++): ?>
                <div class="form-group">
                    <label>Option <?php echo $i; ?>:</label>
                    <input type="text" name="option<?php echo $i; ?>" class="form-control" required>
                </div>
                <?php endfor; ?>
                <div class="form-group">
                    <label>Correct Option (1-4):</label>
                    <input type="number" name="correct_option" class="form-control" min="1" max="4" required>
                </div>
                <button type="submit" name="add_question" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Question
                </button>
            </form>
        </div>

        <h2><i class="fas fa-list"></i> Manage Exams</h2>
        <?php
        $result = $conn->query("SELECT e.*, s.subject_name FROM exams e JOIN subjects s ON e.subject_id = s.id ORDER BY e.created_at DESC");
        while ($exam = $result->fetch_assoc()):
        ?>
        <div class="question-list">
            <div class="card">
                <h3>
                    <i class="fas fa-book"></i> 
                    <?php echo $exam['subject_name'].' - '.$exam['exam_number']; ?>
                </h3>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                    <input type="hidden" name="status" value="<?php echo $exam['is_active'] ? 0 : 1; ?>">
                    <button type="submit" name="toggle_exam" class="toggle-btn <?php echo $exam['is_active'] ? 'active' : ''; ?>">
                        <i class="fas <?php echo $exam['is_active'] ? 'fa-stop-circle' : 'fa-play-circle'; ?>"></i>
                        <?php echo $exam['is_active'] ? 'Stop Exam' : 'Start Exam'; ?>
                    </button>
                </form>
                
                <?php
                $stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ?");
                $stmt->bind_param("i", $exam['id']);
                $stmt->execute();
                $questions = $stmt->get_result();
                
                while ($question = $questions->fetch_assoc()):
                ?>
                <div class="question-item">
                    <p><strong>Q:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
                    <ul class="options-list">
                        <li <?php echo ($question['correct_option'] == 1) ? 'class="correct-option"' : ''; ?>>
                            1. <?php echo htmlspecialchars($question['option1']); ?>
                        </li>
                        <li <?php echo ($question['correct_option'] == 2) ? 'class="correct-option"' : ''; ?>>
                            2. <?php echo htmlspecialchars($question['option2']); ?>
                        </li>
                        <li <?php echo ($question['correct_option'] == 3) ? 'class="correct-option"' : ''; ?>>
                            3. <?php echo htmlspecialchars($question['option3']); ?>
                        </li>
                        <li <?php echo ($question['correct_option'] == 4) ? 'class="correct-option"' : ''; ?>>
                            4. <?php echo htmlspecialchars($question['option4']); ?>
                        </li>
                    </ul>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-primary" onclick="editQuestion(<?php echo $question['id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                            <button type="submit" name="delete_question" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this question?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Edit Question Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-edit"></i> Edit Question</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="question_id" id="edit_question_id">
                <div class="form-group">
                    <label>Question:</label>
                    <textarea name="question" id="edit_question" class="form-control" required></textarea>
                </div>
                <?php for($i = 1; $i <= 4; $i++): ?>
                <div class="form-group">
                    <label>Option <?php echo $i; ?>:</label>
                    <input type="text" name="option<?php echo $i; ?>" id="edit_option<?php echo $i; ?>" class="form-control" required>
                </div>
                <?php endfor; ?>
                <div class="form-group">
                    <label>Correct Option (1-4):</label>
                    <input type="number" name="correct_option" id="edit_correct_option" class="form-control" min="1" max="4" required>
                </div>
                <button type="submit" name="update_question" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Question
                </button>
            </form>
        </div>
    </div>

    <script>
    // Modal elements
    const modal = document.getElementById('editModal');
    const closeModalBtn = document.querySelector('.close-modal');

    function closeModal() {
        modal.style.display = 'none';
    }

    function editQuestion(questionId) {
        // Add .php extension to the URL
        const url = window.location.pathname + '?edit_question=' + questionId;
        
        // Show modal before fetching data
        modal.style.display = 'block';
        
        // Fetch question data
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data) {
                    // Fill the form with question data
                    document.getElementById('edit_question_id').value = data.id;
                    document.getElementById('edit_question').value = data.question_text;
                    document.getElementById('edit_option1').value = data.option1;
                    document.getElementById('edit_option2').value = data.option2;
                    document.getElementById('edit_option3').value = data.option3;
                    document.getElementById('edit_option4').value = data.option4;
                    document.getElementById('edit_correct_option').value = data.correct_option;
                } else {
                    throw new Error('No data received');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load question data. Please try again.');
                closeModal();
            });
    }

    // Close modal with X button
    closeModalBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Handle escape key press
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });

    // Add basic modal styles if not defined in CSS
    modal.style.cssText = `
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    `;

    document.querySelector('.modal-content').style.cssText = `
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        position: relative;
        border-radius: 5px;
    `;

    // Success message fade out
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    }
</script>
</body>
</html>