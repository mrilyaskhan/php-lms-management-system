<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo 'unauthorized';
    exit();
}

include '../config/db.php';

$teacher_id = $_POST['teacher_id'];
$name = $_POST['name'];
$email = $_POST['email'];

$stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE user_id = :teacher_id AND role = 'teacher'");
$stmt->bindParam(':name', $name);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':teacher_id', $teacher_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}
?>
