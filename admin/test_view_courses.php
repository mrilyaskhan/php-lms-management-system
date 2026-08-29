<?php
include '../config/db.php';

try {
    // Fetch courses and teachers
    $stmt = $conn->query("
        SELECT c.course_id, c.course_name, u.name AS teacher_name
        FROM courses c
        JOIN users u ON c.teacher_id = u.user_id
    ");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($courses as $course) {
        echo "Course ID: {$course['course_id']} - Course Name: {$course['course_name']} - Teacher: {$course['teacher_name']}<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
