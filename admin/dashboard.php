<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Fetch the admin's name from the session
$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';

// Fetch counts from the database
try {
    // Count Courses
    $course_count_stmt = $conn->prepare("SELECT COUNT(*) AS course_count FROM courses");
    $course_count_stmt->execute();
    $course_count = $course_count_stmt->fetch(PDO::FETCH_ASSOC)['course_count'];

    // Count Users
    $user_count_stmt = $conn->prepare("SELECT COUNT(*) AS user_count FROM users");
    $user_count_stmt->execute();
    $user_count = $user_count_stmt->fetch(PDO::FETCH_ASSOC)['user_count'];

    // Count Teachers
    $teacher_count_stmt = $conn->prepare("SELECT COUNT(*) AS teacher_count FROM users WHERE role = 'teacher'");
    $teacher_count_stmt->execute();
    $teacher_count = $teacher_count_stmt->fetch(PDO::FETCH_ASSOC)['teacher_count'];

    // Count Students
    $student_count_stmt = $conn->prepare("SELECT COUNT(*) AS student_count FROM enrollments");
    $student_count_stmt->execute();
    $student_count = $student_count_stmt->fetch(PDO::FETCH_ASSOC)['student_count'];

} catch (PDOException $e) {
    echo "Error fetching counts: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?></h2> <!-- Show the admin name -->
            <ul>
                <li><a href="dashboard.php" class="dashboard-link">Dashboard</a></li>
                <li><a href="manage_courses.php" class="load-page">Manage Courses</a></li>
                <li><a href="manage_users.php" class="load-page">Manage Users</a></li>
                <li><a href="manage_teachers.php" class="load-page">Manage Teachers</a></li>
                <li><a href="enroll_student.php" class="load-page">Enroll Students</a></li>
                <li><a href="/lms-projects/logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <h1>Admin Dashboard</h1>
            <div class="card-container">
                <div class="card card-1">
                    <h3>Manage Courses</h3>
                    <p id="course-count"><?php echo $course_count; ?> Courses</p>
                </div>
                <div class="card card-2">
                    <h3>Manage Users</h3>
                    <p id="user-count"><?php echo $user_count; ?> Users</p>
                </div>
                <div class="card card-3">
                    <h3>Manage Teachers</h3>
                    <p id="teacher-count"><?php echo $teacher_count; ?> Teachers</p>
                </div>
                <div class="card card-4">
                    <h3>Enroll Students</h3>
                    <p id="student-count"><?php echo $student_count; ?> Enrolled Students</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Load pages into main-content without refreshing the sidebar
            $('.load-page').on('click', function(e) {
                e.preventDefault();
                var page = $(this).attr('href');
                
                // Load the selected page into the main-content area
                $('#main-content').load(page);
            });

            // Function to update course count in real time without refreshing the page
            function updateCourseCount() {
                $.ajax({
                    url: 'get_course_count.php',
                    method: 'GET',
                    success: function(data) {
                        $('#course-count').text(data + ' Courses');
                    },
                    error: function() {
                        console.error('Error fetching course count.');
                    }
                });
            }

            // Automatically refresh course count every 5 seconds
            setInterval(updateCourseCount, 5000);
        });
    </script>
</body>
</html>
