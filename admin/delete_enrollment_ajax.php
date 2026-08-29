<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo 'error';
    exit();
}

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enrollment_id = $_POST['enrollment_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE enrollment_id = :enrollment_id");
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
