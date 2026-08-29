<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Fetch the teacher's name if not already set in the session
if (!isset($_SESSION['user_name'])) {
    $teacher_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = :teacher_id");
    $stmt->bindParam(':teacher_id', $teacher_id);
    $stmt->execute();
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['user_name'] = $teacher['name'];
}

// Fetch assignments associated with the teacher's courses
$assignments_stmt = $conn->prepare("
    SELECT a.assignment_id, a.assignment_name 
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
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/css/teacherstyle.css"> <!-- Link to the new teacherstyle.css -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery for AJAX functionality -->
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="header-content">
            <h1>Teacher Profile</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a> <!-- Moved Logout Button to right -->
    </header>

    <div class="container">
        <!-- Main Heading -->
        <h2 class="styled-heading" id="animated-heading">Welcome to the Teacher Dashboard</h2>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const text = "Welcome to the Teacher Dashboard";
                let i = 0;
                const speed = 100; // Speed of typing (in milliseconds)
                
                // Ensure that the heading is cleared before starting the animation
                document.getElementById("animated-heading").innerHTML = '';

                function typeWriter() {
                    if (i < text.length) {
                        document.getElementById("animated-heading").innerHTML += text.charAt(i);
                        i++;
                        setTimeout(typeWriter, speed);
                    }
                }

                typeWriter();
            });
        </script>

        <!-- Dashboard Overview -->
        <h3>Dashboard Overview</h3>
        <p>From this dashboard, you can efficiently manage your courses, assignments, and quizzes. Use the links below to navigate to the respective sections.</p>

        <!-- Right Side Box Links (Horizontal Flexbox) -->
<div class="box-container">
    <div class="dashboard-links">
        <a href="manage_courses.php" class="dashboard-box" id="box-1">Manage Courses</a>
        <a href="manage_assignments.php" class="dashboard-box" id="box-2">Manage Assignments</a> 
        <a href="manage_quizzes.php" class="dashboard-box" id="box-3">Manage Quizzes</a>
    </div>
</div>

<!-- Additional Box Links (Center Alignment) -->
<div class="box-container">
    <div class="dashboard-links">
        <a href="view_courses.php" class="dashboard-box" id="box-4">View Courses</a>
        <a href="view_submissions_list.php" class="dashboard-box" id="box-5">View Submissions</a>
        <a href="view_marks.php" class="dashboard-box" id="box-6">Marks</a>
    </div>
</div>

</body>
</html>
