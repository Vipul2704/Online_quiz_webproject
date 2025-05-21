<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch active exams from database
function getActiveExams($conn) {
    $exams = array();
    $sql = "SELECT e.exam_number, s.subject_name 
            FROM exams e 
            JOIN subjects s ON e.subject_id = s.id 
            WHERE e.is_active = TRUE";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $exams[] = array(
                "exam_number" => $row["exam_number"],
                "subject_name" => $row["subject_name"]
            );
        }
    }
    return $exams;
}

// Fetch questions for specific exam
function getQuestions($conn, $exam_number) {
    $questions = array();
    $sql = "SELECT q.* FROM questions q 
            JOIN exams e ON q.exam_id = e.id 
            WHERE e.exam_number = ? 
            ORDER BY RAND()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $exam_number);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $question = array(
            "question" => $row["question_text"],
            "options" => array(
                $row["option1"],
                $row["option2"],
                $row["option3"],
                $row["option4"]
            ),
            "correct" => $row["correct_option"] - 1
        );
        $questions[] = $question;
    }

    $stmt->close();
    return $questions;
}

$exams = getActiveExams($conn);
$questions = array();

if (isset($_POST['exam_number'])) {
    $questions = getQuestions($conn, $_POST['exam_number']);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Interactive Quiz with Enhanced Protection</title>
    <!-- Your existing CSS here -->
     <style>
    body{
            font-family: 'Arial', sans-serif;
            background-color: #1a1a2e;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            transition: background-color 0.3s ease;
        }
        .container {
            background-color: #16213e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            width: 80%;
            max-width: 600px;
        }
        h2 {
            color: #00ff00;
            text-align: center;
            margin-bottom: 20px;
        }
        ol {
            padding-left: 20px;
        }
        li {
            margin-bottom: 15px;
            line-height: 1.5;
        }
        button {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: scale(1.05);
        }
        .exit-btn {
            background-color: #ff6b6b;
            color: white;
        }
        .continue-btn {
            background-color: #4ecdc4;
            color: #333;
        }
        .question {
            font-size: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .options button {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            text-align: left;
            background-color: #0f3460;
            color: #ffffff;
            border: 2px solid #4ecdc4;
            transition: all 0.3s ease;
        }
        .options button:hover {
            background-color: #4ecdc4;
            color: #333;
        }
        .timer {
            font-size: 24px;
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 20px;
        }
        .warning {
            color: #ff6b6b;
            text-align: center;
            font-style: italic;
            margin-top: 20px;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .pulse {
            animation: pulse 1s infinite;
        }
        .feedback {
            margin-top: 20px;
            padding: 15px;
            background-color: #0f3460;
            border-radius: 5px;
        }
        .feedback h3 {
            color: #4ecdc4;
            margin-top: 0;
        }
        .feedback p {
            margin: 10px 0;
        }
        .correct {
            color: #4ecdc4;
        }
        .incorrect {
            color: #ff6b6b;
        }
        .select-exam {
            background-color: #0f3460;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .select-exam select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #16213e;
            color: #ffffff;
            border: 2px solid #4ecdc4;
            border-radius: 5px;
        }
        .select-exam button {
            width: 100%;
            background-color: #4ecdc4;
            color: #333;
        }
        .security-warning {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 0, 0, 0.9);
            color: white;
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            font-size: 24px;
            text-align: center;
            padding: 20px;
        }
        </style>
</head>
<body>
    <div class="security-warning" id="securityWarning" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 0, 0, 0.9); color: white; display: none; justify-content: center; align-items: center; z-index: 1000; font-size: 24px; text-align: center; padding: 20px;">
        <div>
            <h2>⚠️ Security Violation Detected ⚠️</h2>
            <p>Unauthorized screen configuration or window manipulation detected.</p>
            <p>Your exam session has been terminated.</p>
        </div>
    </div>

    <div class="container">
        <?php if (empty($_POST['exam_number'])) { ?>
            <!-- Exam Selection Form -->
            <div class="select-exam">
                <h2>Select Your Exam</h2>
                <form method="POST" id="examForm">
                    <select name="exam_number" required>
                        <option value="">Select an exam</option>
                        <?php foreach($exams as $exam) { ?>
                            <option value="<?php echo htmlspecialchars($exam['exam_number']); ?>">
                                <?php echo htmlspecialchars($exam['subject_name'] . ' - ' . $exam['exam_number']); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <button type="submit">Start Exam</button>
                </form>
            </div>
        <?php } else { ?>
            <!-- Rules Container -->
            <div id="rules-container">
                <h2>Secure Quiz Rules</h2>
                <h3>Exam Number: <?php echo htmlspecialchars($_POST['exam_number']); ?></h3>
                <ol>
                    <li>You have only <span style="color: #ff6b6b;">15 seconds</span> per question.</li>
                    <li>Once you select an answer, it can't be changed.</li>
                    <li>You can't select any option after time expires.</li>
                    <li>Exiting fullscreen mode or switching tabs is not allowed.</li>
                    <li>Copying content is not allowed and will disqualify you.</li>
                    <li>The exam must be taken in fullscreen mode.</li>
                </ol>
                <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                    <button class="exit-btn" onclick="exitQuiz()">Exit Quiz</button>
                    <button class="continue-btn" onclick="startQuiz()">Start Quiz</button>
                </div>
                <p class="warning">Any attempt to cheat will result in immediate disqualification.</p>
            </div>

            <!-- Quiz Container -->
            <div id="quiz-container" style="display: none;">
                <div class="timer">Time left: <span id="timer" class="pulse">15</span> seconds</div>
                <div class="question" id="question-text"></div>
                <div class="options">
                    <button onclick="selectAnswer(0)" id="option1"></button>
                    <button onclick="selectAnswer(1)" id="option2"></button>
                    <button onclick="selectAnswer(2)" id="option3"></button>
                    <button onclick="selectAnswer(3)" id="option4"></button>
                </div>
            </div>
        <?php } ?>
    </div>

    <script>
        <?php if (!empty($questions)) { ?>
            let questions = <?php echo json_encode($questions); ?>;
            let timer;
            let currentQuestion = 0;
            let totalQuestions = questions.length;
            let score = 0;
            let wrongAnswers = [];
            let quizStarted = false;
            let isFullScreen = false;
            let originalScreenWidth = window.screen.width;
            let originalScreenHeight = window.screen.height;

            // Function to request full screen
            function requestFullScreen() {
                const element = document.documentElement;
                if (element.requestFullscreen) {
                    element.requestFullscreen();
                } else if (element.mozRequestFullScreen) {
                    element.mozRequestFullScreen();
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                }
            }

            // Function to exit full screen
            function exitFullScreen() {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }

            function exitQuiz() {
                if (confirm("Are you sure you want to exit? This action cannot be undone.")) {
                    window.location.href = window.location.href.split('?')[0];
                }
            }

            // Modified startQuiz function
            function startQuiz() {
                requestFullScreen();
                startSecurityMonitoring();
                quizStarted = true;
                document.getElementById('rules-container').style.display = 'none';
                document.getElementById('quiz-container').style.display = 'block';
                loadQuestion();
            }

            // Function to handle security violations
            function handleSecurityViolation(reason) {
                clearInterval(timer);
                document.getElementById('securityWarning').style.display = 'flex';
                quizStarted = false;
                
                // Log the violation (you can send this to your server)
                console.log('Security violation:', reason);
                
                // End the quiz after a brief delay
                setTimeout(() => {
                    exitFullScreen();
                    window.location.href = window.location.href.split('?')[0];
                }, 3000);
            }

            // Function to start security monitoring
            function startSecurityMonitoring() {
                // Monitor screen size changes
                setInterval(() => {
                    if (quizStarted) {
                        const currentWidth = window.outerWidth;
                        const currentHeight = window.outerHeight;
                        
                        // Check if window size has changed significantly
                        if (Math.abs(currentWidth - originalScreenWidth) > 100 || 
                            Math.abs(currentHeight - originalScreenHeight) > 100) {
                            handleSecurityViolation('Screen size change detected');
                        }
                        
                        // Check if window is not focused
                        if (!document.hasFocus()) {
                            handleSecurityViolation('Window focus lost');
                        }
                    }
                }, 1000);

                // Monitor full screen changes
                document.addEventListener('fullscreenchange', () => {
                    if (quizStarted && !document.fullscreenElement) {
                        handleSecurityViolation('Fullscreen mode exited');
                    }
                });

                // Monitor window blur events
                window.addEventListener('blur', () => {
                    if (quizStarted) {
                        handleSecurityViolation('Window blur detected');
                    }
                });

                // Block right-click
                document.addEventListener('contextmenu', (e) => {
                    if (quizStarted) {
                        e.preventDefault();
                    }
                });

                // Block keyboard shortcuts
                document.addEventListener('keydown', (e) => {
                    if (quizStarted) {
                        // Block Alt+Tab
                        if (e.altKey && e.key === 'Tab') {
                            e.preventDefault();
                            handleSecurityViolation('Alt+Tab detected');
                        }
                        
                        // Block Windows key
                        if (e.key === 'Meta' || e.key === 'OS') {
                            e.preventDefault();
                            handleSecurityViolation('Windows key detected');
                        }
                        
                        // Block Alt+F4
                        if (e.altKey && e.key === 'F4') {
                            e.preventDefault();
                            handleSecurityViolation('Alt+F4 detected');
                        }
                        
                        // Block Ctrl+Alt+Delete (though this might not work in all browsers)
                        if (e.ctrlKey && e.altKey && e.key === 'Delete') {
                            e.preventDefault();
                            handleSecurityViolation('Ctrl+Alt+Delete detected');
                        }
                    }
                });
            }

            function loadQuestion() {
                if (currentQuestion < totalQuestions) {
                    document.getElementById("question-text").innerText = questions[currentQuestion].question;
                    document.getElementById("option1").innerText = questions[currentQuestion].options[0];
                    document.getElementById("option2").innerText = questions[currentQuestion].options[1];
                    document.getElementById("option3").innerText = questions[currentQuestion].options[2];
                    document.getElementById("option4").innerText = questions[currentQuestion].options[3];
                    
                    startTimer();
                } else {
                    endQuiz();
                }
            }

            function startTimer() {
                let timeLeft = 15;
                document.getElementById("timer").innerText = timeLeft;
                timer = setInterval(function() {
                    timeLeft--;
                    document.getElementById("timer").innerText = timeLeft;
                    if (timeLeft <= 5) {
                        document.getElementById("timer").style.color = "#ff0000";
                    }
                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        nextQuestion();
                    }
                }, 1000);
            }

            function selectAnswer(index) {
                clearInterval(timer);
                if (index == questions[currentQuestion].correct) {
                    score++;
                } else {
                    wrongAnswers.push({
                        question: questions[currentQuestion].question,
                        userAnswer: questions[currentQuestion].options[index],
                        correctAnswer: questions[currentQuestion].options[questions[currentQuestion].correct]
                    });
                }
                nextQuestion();
            }

            function nextQuestion() {
                currentQuestion++;
                loadQuestion();
            }

            function endQuiz() {
                quizStarted = false;
                let feedbackHtml = `
                    <h2>Quiz Completed</h2>
                    <p>Your score: ${score} out of ${totalQuestions}</p>
                `;
                if (wrongAnswers.length > 0) {
                    feedbackHtml += `
                        <div class="feedback">
                            <h3>Review Your Mistakes:</h3>
                    `;
                    wrongAnswers.forEach((wrong, index) => {
                        feedbackHtml += `
                            <p><strong>Question ${index + 1}:</strong> ${wrong.question}</p>
                            <p class="incorrect">Your answer: ${wrong.userAnswer}</p>
                            <p class="correct">Correct answer: ${wrong.correctAnswer}</p>
                        `;
                    });
                    feedbackHtml += `</div>`;
                } else {
                    feedbackHtml += `<p class="correct">Congratulations! You answered all questions correctly.</p>`;
                }

                feedbackHtml += `<p class="warning">Thank you for your honesty during the quiz.</p>`;

                document.getElementById("quiz-container").innerHTML = feedbackHtml;
                exitFullScreen();
            }

        <?php } ?>
    </script>
</body>
</html>