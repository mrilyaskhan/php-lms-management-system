<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include '../config/db.php';

$teacher_id = $_GET['teacher_id'];
$stmt = $conn->prepare("SELECT user_id, name, email FROM users WHERE user_id = :teacher_id AND role = 'teacher'");
$stmt->bindParam(':teacher_id', $teacher_id);
$stmt->execute();
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if ($teacher) {
    echo json_encode(['status' => 'success', 'teacher' => $teacher]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Teacher not found']);
}
?>
