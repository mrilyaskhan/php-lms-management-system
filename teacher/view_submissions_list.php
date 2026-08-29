<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$teacher_id = $_SESSION['user_id'];

// Fetch assignments created by the logged-in teacher
$assignments_stmt = $conn->prepare("
    SELECT a.assignment_id, a.assignment_name, c.course_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    WHERE c.teacher_id = :teacher_id
");
$assignments_stmt->bindParam(':teacher_id', $teacher_id);
$assignments_stmt->execute();
$assignments = $assignments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions</title>
    <link rel="stylesheet" href="../assets/css/teacherstyle.css"> <!-- Link to your existing stylesheet -->
    <style>
        /* Card wrapper for the table */
        .assignment-card {
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
            background-color: white;
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

        /* Green Button for View Submissions */
        .view-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .view-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Card Wrapper -->
        <div class="assignment-card">
            <h2>Your Assignments</h2>
            <?php if (!empty($assignments)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Assignment Name</th>
                        <th>Course</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['assignment_name']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                        <td>
                            <a href="view_submissions.php?assignment_id=<?php echo $assignment['assignment_id']; ?>" class="view-btn">View Submissions</a>
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
                <p>No assignments available.</p>
            <?php endif; ?>
        </div>

        <!-- Back to Dashboard Button -->
        <br>
    </div>
</body>
</html>
