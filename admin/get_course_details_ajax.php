<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../config/db.php';

$course_id = $_GET['course_id'];

$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :course_id");
$stmt->bindParam(':course_id', $course_id);
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if ($course) {
    echo json_encode(['status' => 'success', 'course' => $course]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Course not found']);
}
?>
