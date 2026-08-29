<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo 'unauthorized';
    exit();
}

include '../config/db.php';

$teacher_id = $_POST['teacher_id'];
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = :teacher_id AND role = 'teacher'");
$stmt->bindParam(':teacher_id', $teacher_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}
?>
