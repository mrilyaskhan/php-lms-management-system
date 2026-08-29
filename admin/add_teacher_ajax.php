<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo 'unauthorized';
    exit();
}

include '../config/db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = 'teacher';

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
$stmt->bindParam(':name', $name);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $password);
$stmt->bindParam(':role', $role);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}
?>
