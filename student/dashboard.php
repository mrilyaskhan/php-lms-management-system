<?php
session_start();
if ($_SESSION['role'] != 'student') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$student_id = $_SESSION['user_id'];

// Fetch the courses the student is enrolled in
$courses = $conn->prepare("
    SELECT DISTINCT c.course_id, c.course_name 
    FROM courses c
    JOIN enrollments e ON c.course_id = e.course_id
    WHERE e.student_id = :student_id
");
$courses->bindParam(':student_id', $student_id);
$courses->execute();
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/studentstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="course_details.php"><i class="fas fa-book"></i> My Courses</a></li>
            <li><a href="submit_assignment.php"><i class="fas fa-file-upload"></i> Assignments</a></li>
            <li><a href="quiz_results.php"><i class="fas fa-poll-h"></i> Quizzes</a></li>
            <li><a href="upload_assignment.php"><i class="fas fa-upload"></i> Upload Assignments</a></li>
            <li><a href="quiz_results.php"><i class="fas fa-chart-bar"></i> Quiz Marks</a></li>
            <li><a href="submit_assignment.php"><i class="fas fa-clipboard-check"></i> Assignment Marks</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main content area -->
    <div class="main-content">
        <h2>Welcome to the Student Dashboard</h2>

        <div class="dashboard-icons">
            <div class="icon-card">
                <i class="fas fa-calendar-alt"></i>
                <p>Calendar</p>
            </div>
            <div class="icon-card">
                <i class="fas fa-poll-h"></i>
                <p>Quiz Marks</p>
            </div>
            <div class="icon-card">
                <i class="fas fa-clipboard-check"></i>
                <p>Assignment Marks</p>
            </div>
        </div>

        <!-- Loop through each course the student is enrolled in -->
        <div class="course-container">
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <h3>Course: <?php echo htmlspecialchars($course['course_name']); ?></h3>

                        <!-- Display Topics for the Course -->
                        <h4>Topics:</h4>
                        <ul>
                            <?php
                            $topics = $conn->prepare("SELECT topic_name, topic_description 
                                                      FROM course_topics 
                                                      WHERE course_id = :course_id");
                            $topics->bindParam(':course_id', $course['course_id']);
                            $topics->execute();
                            $topics = $topics->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php if (!empty($topics)): ?>
                                <?php foreach ($topics as $topic): ?>
                                    <li><?php echo htmlspecialchars($topic['topic_name']); ?>: <?php echo htmlspecialchars($topic['topic_description']); ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No topics available for this course.</li>
                            <?php endif; ?>
                        </ul>

                        <!-- Display Assignments for this Course -->
                        <h4>Assignments:</h4>
                        <ul>
                            <?php
                            $assignments = $conn->prepare("SELECT a.assignment_name, a.assignment_id, a.due_date, a.file_path 
                                                           FROM assignments a
                                                           WHERE a.course_id = :course_id");
                            $assignments->bindParam(':course_id', $course['course_id']);
                            $assignments->execute();
                            $assignments = $assignments->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php if (!empty($assignments)): ?>
                                <?php foreach ($assignments as $assignment): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($assignment['assignment_name']); ?></strong> 
                                        (Due: <?php echo htmlspecialchars($assignment['due_date']); ?>)
                                        <a href="upload_assignment.php?assignment_id=<?php echo $assignment['assignment_id']; ?>">Upload Assignment</a>
                                        <?php if (!empty($assignment['file_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" download>Download</a>
                                        <?php else: ?>
                                            (No file available)
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No assignments available for this course.</li>
                            <?php endif; ?>
                        </ul>

                        <!-- Display Quizzes for this Course -->
                        <h4>Quizzes:</h4>
                        <ul>
                            <?php
                            $quizzes = $conn->prepare("SELECT q.quiz_id, q.quiz_name, q.due_date 
                                                       FROM quizzes q
                                                       WHERE q.course_id = :course_id");
                            $quizzes->bindParam(':course_id', $course['course_id']);
                            $quizzes->execute();
                            $quizzes = $quizzes->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php if (!empty($quizzes)): ?>
                                <?php foreach ($quizzes as $quiz): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($quiz['quiz_name']); ?></strong>
                                        (Due: <?php echo htmlspecialchars($quiz['due_date']); ?>)
                                        <a href="attempt_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>">Take Quiz</a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No quizzes available for this course.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You are not enrolled in any courses.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
