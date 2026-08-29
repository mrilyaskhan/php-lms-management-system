<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$teacher_id = $_SESSION['user_id'];

// Fetch submissions for assignments created by the teacher
$submissions_stmt = $conn->prepare("
    SELECT s.submission_id, a.assignment_name, u.name AS student_name, s.submission_date, s.marks
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.assignment_id
    JOIN users u ON s.student_id = u.user_id
    JOIN courses c ON a.course_id = c.course_id
    WHERE c.teacher_id = :teacher_id
");
$submissions_stmt->bindParam(':teacher_id', $teacher_id);
$submissions_stmt->execute();
$submissions = $submissions_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/teacherstyle.css"> <!-- Link to your existing stylesheet -->
    <title>Assignment Marks</title>
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
        <h2>Assignment Marks</h2>
        <table>
            <thead>
                <tr>
                    <th>Submission ID</th>
                    <th>Assignment Name</th>
                    <th>Student Name</th>
                    <th>Submission Date</th>
                    <th>Marks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($submission['submission_id']); ?></td>
                        <td><?php echo htmlspecialchars($submission['assignment_name']); ?></td>
                        <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($submission['submission_date']); ?></td>
                        <td><?php echo htmlspecialchars($submission['marks']); ?></td>
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
