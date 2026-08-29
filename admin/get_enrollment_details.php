<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../config/db.php';

$enrollment_id = $_GET['enrollment_id'];

try {
    $stmt = $conn->prepare("
        SELECT e.enrollment_id, e.student_id, e.course_id
        FROM enrollments e
        WHERE e.enrollment_id = :enrollment_id
    ");
    $stmt->bindParam(':enrollment_id', $enrollment_id);
    $stmt->execute();
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($enrollment) {
        echo json_encode(['status' => 'success', 'enrollment' => $enrollment]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Enrollment not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching enrollment details']);
}
?>
