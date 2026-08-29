<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Fetch courses managed by the logged-in teacher
$courses_stmt = $conn->prepare("SELECT * FROM courses WHERE teacher_id = :teacher_id");
$courses_stmt->bindParam(':teacher_id', $_SESSION['user_id']);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Courses</title>
    <link rel="stylesheet" href="../assets/css/teacherstyle.css"> <!-- Your CSS file -->
    <style>
        /* Card wrapper for the table */
        .course-card {
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

        /* Edit Button (Green) */
        .edit-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .edit-btn:hover {
            background-color: #218838;
        }

        /* Delete Button (Red) */
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

        /* Button container */
        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Card Wrapper -->
        <div class="course-card">
            <h2>Your Courses</h2>
            <?php if (!empty($courses)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($course['course_description']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_course.php?course_id=<?php echo $course['course_id']; ?>" class="edit-btn">Edit</a>
                                <a href="delete_course.php?course_id=<?php echo $course['course_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                            </div>
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
                <p>No courses found.</p>
            <?php endif; ?>
        </div>

        <!-- Back to Dashboard Button -->
        <br>
        
    </div>
</body>
</html>
