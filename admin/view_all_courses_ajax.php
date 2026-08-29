<?php
include '../config/db.php';

// Fetch all courses with their teacher's name
$courses = $conn->query("
    SELECT c.course_id, c.course_name, u.name AS teacher_name 
    FROM courses c
    JOIN users u ON c.teacher_id = u.user_id
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($courses);
?>
