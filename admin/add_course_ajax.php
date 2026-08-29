<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo 'unauthorized';
    exit();
}

include '../config/db.php';

// Get data from POST request
$course_name = isset($_POST['course_name']) ? $_POST['course_name'] : null;
$course_description = isset($_POST['course_description']) ? $_POST['course_description'] : null;
$teacher_id = isset($_POST['teacher_id']) ? $_POST['teacher_id'] : null;

// Ensure all fields are filled
if ($course_name && $course_description && $teacher_id) {
    try {
        // Insert the new course into the database
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_description, teacher_id) VALUES (:course_name, :course_description, :teacher_id)");
        $stmt->bindParam(':course_name', $course_name);
        $stmt->bindParam(':course_description', $course_description);
        $stmt->bindParam(':teacher_id', $teacher_id);
        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }
    } catch (PDOException $e) {
        echo 'error: ' . $e->getMessage();
    }
} else {
    echo 'missing_data';
}
?>
