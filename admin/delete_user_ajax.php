<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo 'error';
    exit();
}

include '../config/db.php';

$user_id = $_POST['user_id'];

try {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    echo 'success';
} catch (PDOException $e) {
    echo 'error';
}
?>