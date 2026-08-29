<?php
session_start();
if ($_SESSION['role'] != 'student') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$student_id = $_SESSION['user_id'];

// Fetch the student's name from the 'users' table
$student_stmt = $conn->prepare("SELECT name FROM users WHERE user_id = :student_id");
$student_stmt->bindParam(':student_id', $student_id);
$student_stmt->execute();
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);
$student_name = $student['name']; // Get the student's name from the 'users' table

// Fetch quiz details including time limit and due date
$quiz_id = $_GET['quiz_id']; // Quiz ID passed from the URL
$quiz = $conn->prepare("SELECT quiz_name, time_limit, due_date FROM quizzes WHERE quiz_id = :quiz_id");
$quiz->bindParam(':quiz_id', $quiz_id);
$quiz->execute();
$quiz = $quiz->fetch(PDO::FETCH_ASSOC);

// Ensure the quiz is available and within the due date
if (new DateTime() > new DateTime($quiz['due_date'])) {
    echo "This quiz has passed its due date.";
    exit();
}

// Fetch quiz questions and their options
$questions = $conn->prepare("SELECT question_id, question_text, option_a, option_b, option_c, option_d, correct_answer FROM quiz_questions WHERE quiz_id = :quiz_id");
$questions->bindParam(':quiz_id', $quiz_id);
$questions->execute();
$questions = $questions->fetchAll(PDO::FETCH_ASSOC);

// Initialize current question and score if not already set
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0;
    $_SESSION['score'] = 0;
    $_SESSION['quiz_start_time'] = time();
}

// Handle quiz submission for each question
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_answer'])) {
    $current_question = $_SESSION['current_question'];
    $selected_answer = $_POST['answer'];

    // Check if the selected answer is correct and calculate the score
    if ($selected_answer == $questions[$current_question]['correct_answer']) {
        $_SESSION['score'] += 5; // Add 5 marks for the correct answer
    }

    // Move to the next question
    $_SESSION['current_question']++;

    // Check if the quiz is complete (all questions answered)
    if ($_SESSION['current_question'] >= count($questions)) {
        $total_score = $_SESSION['score'];

        // Insert the quiz result into the quiz_results table, including the student's name
        $stmt = $conn->prepare("INSERT INTO quiz_results (attempt_id, total_score, quiz_completed, student_name) 
                                VALUES (:attempt_id, :total_score, 1, :student_name)");
        $stmt->bindParam(':attempt_id', $student_id);  // Use the student_id as attempt_id (or change based on your logic)
        $stmt->bindParam(':total_score', $total_score);
        $stmt->bindParam(':student_name', $student_name);  // Insert the student's name into the 'student_name' column
        $stmt->execute();

        // Reset session variables
        unset($_SESSION['current_question']);
        unset($_SESSION['score']);
        unset($_SESSION['quiz_start_time']);

        // Display final score and redirect to the dashboard
        echo "<script>alert('Quiz submitted! Your total score is $total_score'); window.location.href = 'dashboard.php';</script>";
        exit();
    }
}

// Get the current question to display
$current_question = $_SESSION['current_question'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attempt Quiz: <?php echo htmlspecialchars($quiz['quiz_name']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        // Timer function for the quiz
        let timeLeft = <?php echo $quiz['time_limit'] * 60; ?>; // Convert minutes to seconds
        function startTimer() {
            const timerElement = document.getElementById('timer');
            const quizForm = document.getElementById('quiz-form');
            const timer = setInterval(function() {
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    quizForm.submit(); // Auto-submit when time is up
                } else {
                    timeLeft--;
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    timerElement.innerText = `${minutes}m ${seconds}s`;
                }
            }, 1000);
        }

        window.onload = startTimer;
    </script>
</head>
<body>
    <div class="container">
        <h2>Attempt Quiz: <?php echo htmlspecialchars($quiz['quiz_name']); ?></h2>
        <p>Time left: <span id="timer"></span></p>

        <!-- Display the current question -->
        <form method="POST" id="quiz-form">
            <p><strong><?php echo ($current_question + 1) . '. ' . htmlspecialchars($questions[$current_question]['question_text']); ?></strong></p>

            <!-- Display multiple-choice answers as radio buttons -->
            <div>
                <label><input type="radio" name="answer" value="A" required> A) <?php echo htmlspecialchars($questions[$current_question]['option_a']); ?></label><br>
                <label><input type="radio" name="answer" value="B" required> B) <?php echo htmlspecialchars($questions[$current_question]['option_b']); ?></label><br>
                <label><input type="radio" name="answer" value="C" required> C) <?php echo htmlspecialchars($questions[$current_question]['option_c']); ?></label><br>
                <label><input type="radio" name="answer" value="D" required> D) <?php echo htmlspecialchars($questions[$current_question]['option_d']); ?></label><br>
            </div>

            <!-- Display "Next" or "Submit Quiz" based on the current question -->
            <button type="submit" name="submit_answer">
                <?php echo $_SESSION['current_question'] + 1 == count($questions) ? 'Submit Quiz' : 'Next Question'; ?>
            </button>
        </form>
    </div>
</body>
</html>
