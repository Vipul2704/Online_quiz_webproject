<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Interactive Quiz with Feedback</title>
    <style>
        body {
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
        /* New styles for security overlays */
        .warning-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 0, 0, 0.9);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            padding: 20px;
            color: white;
        }
        .warning-overlay h2 {
            color: white;
            margin-bottom: 20px;
        }
        .warning-overlay button {
            background-color: white;
            color: red;
            padding: 10px 20px;
            margin-top: 20px;
        }
        .cheating-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            z-index: 10000;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            padding: 20px;
            color: white;
        }
        .cheating-overlay h2 {
            color: #ff0000;
        }
        
        
        
    </style>
</head>
<body>
    <!-- Warning Overlay -->
    <div id="warning-overlay" class="warning-overlay">
        <h2>⚠️ First Warning: Attempted to Exit Full Screen!</h2>
        <p>You have attempted to exit full screen mode. This is considered a violation of exam rules.</p>
        <p>This is your FIRST and FINAL warning. Another attempt will result in immediate disqualification.</p>
        <button onclick="acknowledgeWarning()">I Understand</button>
    </div>

    <!-- Cheating Overlay -->
    <div id="cheating-overlay" class="cheating-overlay">
        <h2>🚫 EXAM TERMINATED</h2>
        <p>Multiple attempts to exit full screen mode detected.</p>
        <p>This is considered a violation of exam rules.</p>
        <h3>Final Score: 0</h3>
        <p>The exam has been terminated and your attempt has been recorded.</p>
    </div>
    <div class="container">
        <div id="rules-container">
            <h2>Secure Quiz Rules</h2>
            <ol>
                <li>You have only <span style="color: #ff6b6b;">15 seconds</span> per question.</li>
                <li>Once you select an answer, it can't be changed.</li>
                <li>You can't select any option after time expires.</li>
                <li>Exiting the quiz or switching tabs is not allowed.</li>
                <li>Points are awarded based on correct answers only.</li>
            </ol>
            <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                <button class="exit-btn" onclick="exitQuiz()">Exit Quiz</button>
                <button class="continue-btn" onclick="startQuiz()">Start Quiz</button>
            </div>
            <p class="warning">Any attempt to cheat will result in immediate disqualification.</p>
        </div>

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
    </div>

    <script>
        <?php
        // Sample questions and answers
        $questions = [
            [
                "question" => "What is the capital of France?",
                "options" => ["Paris", "London", "Berlin", "Rome"],
                "correct" => 0
            ],
            [
                "question" => "What is 2 + 2?",
                "options" => ["3", "4", "5", "6"],
                "correct" => 1
            ],
            [
                "question" => "Which planet is known as the Red Planet?",
                "options" => ["Earth", "Venus", "Mars", "Jupiter"],
                "correct" => 2
            ]
        ];
        echo "let questions = " . json_encode($questions) . ";\n";
        ?>

        let timer;
        let currentQuestion = 0;
        let totalQuestions = questions.length;
        let score = 0;
        let wrongAnswers = [];

        // New security variables
        let hasReceivedFirstWarning = false;
        let isQuizTerminated = false;
        let originalWidth = window.outerWidth;
        let originalHeight = window.outerHeight;

        // Request full screen function
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

        // Handle full screen changes
        function handleFullScreenChange() {
            if (!document.fullscreenElement && 
                !document.webkitFullscreenElement && 
                !document.mozFullScreenElement && 
                !document.msFullscreenElement) {
                
                if (isQuizTerminated) return;

                if (!hasReceivedFirstWarning) {
                    showFirstWarning();
                } else {
                    terminateQuiz();
                }
            }
        }

        // Show first warning
        function showFirstWarning() {
            clearInterval(timer);
            hasReceivedFirstWarning = true;
            document.getElementById('warning-overlay').style.display = 'flex';
        }

        // Acknowledge warning
        function acknowledgeWarning() {
            document.getElementById('warning-overlay').style.display = 'none';
            requestFullScreen();
            startTimer();
        }

        // Terminate quiz
        function terminateQuiz() {
            isQuizTerminated = true;
            clearInterval(timer);
            score = 0;
            document.getElementById('cheating-overlay').style.display = 'flex';
            
            // Remove event listeners
            document.removeEventListener('fullscreenchange', handleFullScreenChange);
            document.removeEventListener('webkitfullscreenchange', handleFullScreenChange);
            document.removeEventListener('mozfullscreenchange', handleFullScreenChange);
            document.removeEventListener('MSFullscreenChange', handleFullScreenChange);
        }

        // Modified startQuiz function
        function startQuiz() {
            hasReceivedFirstWarning = false;
            isQuizTerminated = false;
            
            // Request full screen
            requestFullScreen();
            
            // Add full screen change listeners
            document.addEventListener('fullscreenchange', handleFullScreenChange);
            document.addEventListener('webkitfullscreenchange', handleFullScreenChange);
            document.addEventListener('mozfullscreenchange', handleFullScreenChange);
            document.addEventListener('MSFullscreenChange', handleFullScreenChange);
            
            // Start quiz
            document.getElementById('rules-container').style.display = 'none';
            document.getElementById('quiz-container').style.display = 'block';
            loadQuestion();
        }

        // Your existing functions
        function exitQuiz() {
            if (confirm("Are you sure you want to exit? This action cannot be undone.")) {
                window.close();
                window.location.href = 'https://www.example.com';
            }
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
            if (!isQuizTerminated) {
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
        }

        function nextQuestion() {
            currentQuestion++;
            loadQuestion();
        }

        function endQuiz() {
            if (isQuizTerminated) {
                return;
            }

            let feedbackHtml = `
                <h2>Quiz Completed</h2>
                <p style="text-align: center; font-size: 20px;">Your score is ${score} out of ${totalQuestions}.</p>
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
        }

        // Existing event listeners
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && !isQuizTerminated) {
                terminateQuiz();
            }
        });

        document.addEventListener('copy', function(e) {
            e.preventDefault();
            alert("Copying is not allowed during the quiz!");
        });

        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            alert("Right-clicking is disabled during the quiz!");
        });

        // New event listener for ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                if (!hasReceivedFirstWarning) {
                    showFirstWarning();
                } else if (!isQuizTerminated) {
                    terminateQuiz();
                }
            }
        });
    </script>
</body>
</html>