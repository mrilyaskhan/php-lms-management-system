<?php
session_start();
if ($_SESSION['role'] != 'student') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$student_id = $_SESSION['user_id'];
$quiz_id = $_GET['quiz_id']; // Quiz ID passed from the URL

// Fetch quiz results for the student
$results = $conn->prepare("SELECT qa.question_id, qq.question_text, qa.student_answer 
                           FROM quiz_answers qa
                           JOIN quiz_questions qq ON qa.question_id = qq.question_id
                           WHERE qa.attempt_id = (
                               SELECT attempt_id FROM quiz_attempts WHERE quiz_id = :quiz_id AND student_id = :student_id
                           )");
$results->bindParam(':quiz_id', $quiz_id);
$results->bindParam(':student_id', $student_id);
$results->execute();
$results = $results->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Results</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Your Quiz Results</h2>

        <ul>
            <?php foreach ($results as $result): ?>
                <li>
                    <p><strong>Question:</strong> <?php echo $result['question_text']; ?></p>
                    <p><strong>Your Answer:</strong> <?php echo $result['student_answer']; ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
