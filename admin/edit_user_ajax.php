<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo 'error';
    exit();
}

include '../config/db.php';

$user_id = $_POST['user_id'];
$name = $_POST['name'];
$email = $_POST['email'];
$role = $_POST['role'];

try {
    $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, role = :role WHERE user_id = :user_id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    echo 'success';
} catch (PDOException $e) {
    echo 'error';
}
?>