<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo "unauthorized";
    exit();
}

include '../config/db.php';

$course_id = $_POST['course_id'];

$stmt = $conn->prepare("DELETE FROM courses WHERE course_id = :course_id");
$stmt->bindParam(':course_id', $course_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
?>
