<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$course_id = $_GET['course_id'];

// Only delete if the course belongs to the logged-in teacher
$stmt = $conn->prepare("DELETE FROM courses WHERE course_id = :course_id AND teacher_id = :teacher_id");
$stmt->bindParam(':course_id', $course_id);
$stmt->bindParam(':teacher_id', $_SESSION['user_id']);
$stmt->execute();

header("Location: manage_courses.php");
exit();
?>
