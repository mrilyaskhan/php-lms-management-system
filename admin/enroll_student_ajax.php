<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo 'error';
    exit();
}

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    try {
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        echo 'success';
    } catch (PDOException $e) {
        echo 'error';
    }
} else {
    echo 'error';
}
?>
