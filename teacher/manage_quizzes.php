<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Fetch quizzes created by the teacher
$teacher_id = $_SESSION['user_id'];
$quizzes = $conn->prepare("
    SELECT q.quiz_id, q.quiz_name, q.time_limit, q.due_date, c.course_name
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    WHERE c.teacher_id = :teacher_id
");
$quizzes->bindParam(':teacher_id', $teacher_id);
$quizzes->execute();
$quizzes = $quizzes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Quizzes</title>
    <link rel="stylesheet" href="../assets/css/teacherstyle.css"> <!-- Link to your existing stylesheet -->
    <style>
        /* Card wrapper for the table */
        .quiz-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px auto;
            max-width: 90%; /* Adjust width */
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
            color: #333;
        }

        /* Green Button styling for action buttons */
        .action-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .action-btn:hover {
            background-color: #218838;
        }

        /* Blue Button styling for the Edit button */
        .edit-btn {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        /* Red Button styling for the Delete button */
        .delete-btn {
            background-color: #dc3545;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        /* Styling for "Add a New Quiz" link */
        .add-quiz-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .add-quiz-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Card Wrapper -->
        <div class="quiz-card">
            <h2>Manage Quizzes</h2>

            <?php if (!empty($quizzes)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Quiz Name</th>
                        <th>Course Name</th>
                        <th>Time Limit (minutes)</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['quiz_name']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['time_limit']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['due_date']); ?></td>
                        <td>
                            <a href="add_questions.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="action-btn">Add Questions</a>
                            <a href="edit_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="edit-btn">Edit</a>
                            <a href="manage_quizzes.php?delete=<?php echo $quiz['quiz_id']; ?>" onclick="return confirm('Are you sure you want to delete this quiz?');" class="delete-btn">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <a href="dashboard.php" class="back-btn" style="
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
            Back to Dashboard
        </a>
            </table>
            <?php else: ?>
                <p>No quizzes available.</p>
            <?php endif; ?>

            <!-- Add New Quiz Button -->
            <a href="add_quiz.php" class="add-quiz-btn">Add a New Quiz</a>
        </div>

        <!-- Back to Dashboard Button -->
        <br>
        
    </div>
</body>
</html>
