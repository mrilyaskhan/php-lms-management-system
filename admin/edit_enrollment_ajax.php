<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo 'error';
    exit();
}

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enrollment_id = $_POST['enrollment_id'];
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    try {
        $stmt = $conn->prepare("UPDATE enrollments SET student_id = :student_id, course_id = :course_id WHERE enrollment_id = :enrollment_id");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':enrollment_id', $enrollment_id);
        $stmt->execute();
        echo 'success';
    } catch (PDOException $e) {
        echo 'error';
    }
} else {
    echo 'error';
}
?>
