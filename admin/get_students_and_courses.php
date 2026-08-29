<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../config/db.php';

try {
    // Fetch students
    $students = $conn->query("SELECT user_id, name FROM users WHERE role = 'student'")->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch courses
    $courses = $conn->query("SELECT course_id, course_name FROM courses")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'students' => $students, 'courses' => $courses]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error loading data']);
}
?>
