<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Fetch all courses with teacher names
$courses = $conn->query("
    SELECT c.course_id, c.course_name, u.name AS teacher_name 
    FROM courses c 
    JOIN users u ON c.teacher_id = u.user_id
")->fetchAll(PDO::FETCH_ASSOC);

// Generate HTML rows for the table
foreach ($courses as $course) {
    echo "
    <tr>
        <td>{$course['course_id']}</td>
        <td>{$course['course_name']}</td>
        <td>{$course['teacher_name']}</td>
        <td>
            <a href='edit_course.php?course_id={$course['course_id']}' class='edit-course-btn'>Edit</a>
            <button class='delete-course-btn' data-id='{$course['course_id']}'>Delete</button>
        </td>
    </tr>
    ";
}
?>
