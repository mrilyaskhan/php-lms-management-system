<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$teacher_id = $_SESSION['user_id'];

// Fetch quiz results for quizzes created by the teacher
$quiz_results_stmt = $conn->prepare("
    SELECT qr.result_id, q.quiz_name, u.name AS student_name, qr.total_score, qr.quiz_completed
    FROM quiz_results qr
    JOIN quiz_attempts qa ON qr.attempt_id = qa.attempt_id
    JOIN quizzes q ON qa.quiz_id = q.quiz_id
    JOIN users u ON qa.student_id = u.user_id
    JOIN courses c ON q.course_id = c.course_id
    WHERE c.teacher_id = :teacher_id
");
$quiz_results_stmt->bindParam(':teacher_id', $teacher_id);
$quiz_results_stmt->execute();
$quiz_results = $quiz_results_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/teacherstyle.css"> <!-- Link to your existing stylesheet -->
    <title>Quiz Marks</title>
    <style>
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 1000px;
            margin: 20px auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        h2 {
            text-align: center;
            color: #333;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); /* Shadow effect */
            margin-bottom: 20px;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Quiz Marks</h2>
        <table>
            <thead>
                <tr>
                    <th>Result ID</th>
                    <th>Quiz Name</th>
                    <th>Student Name</th>
                    <th>Total Score</th>
                    <th>Quiz Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quiz_results as $result): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($result['result_id']); ?></td>
                        <td><?php echo htmlspecialchars($result['quiz_name']); ?></td>
                        <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($result['total_score']); ?></td>
                        <td><?php echo htmlspecialchars($result['quiz_completed']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <a href="view_marks.php" class="back-btn" style="
                display: inline-block;
                padding: 14px 22px; /* Added 2px extra padding */
                background-color: white; /* White background */
                color: black; /* Black text */
                text-decoration: none; /* Remove underline */
                font-size: 16px; /* Font size */
                border-radius: 8px; /* Rounded corners */
                font-weight: bold; /* Bold text */
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
                transition: background-color 0.3s ease, transform 0.3s ease; /* Hover transition */
            " onmouseover="this.style.backgroundColor='#007bff'; this.style.color='white'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 15px rgba(0, 0, 0, 0.2)';" onmouseout="this.style.backgroundColor='white'; this.style.color='black'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)';">
                Back to Marks
            </a>
        </table>
    </div>
</body>
</html>
