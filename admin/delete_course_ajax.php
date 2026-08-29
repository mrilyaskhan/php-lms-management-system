<?php
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];

    // Delete the course from the database
    $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = :course_id");
    $stmt->bindParam(':course_id', $course_id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>
