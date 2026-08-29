<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $teacher_id = $_POST['teacher_id'];

    try {
        $stmt = $conn->prepare("UPDATE courses SET course_name = :course_name, course_description = :course_description, teacher_id = :teacher_id WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':course_name', $course_name);
        $stmt->bindParam(':course_description', $course_description);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();

        // Fetch the updated course information including the teacher's name
        $stmt = $conn->prepare("SELECT c.course_id, c.course_name, c.course_description, c.teacher_id, u.name as teacher_name 
                                FROM courses c 
                                JOIN users u ON c.teacher_id = u.user_id 
                                WHERE c.course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        $updatedCourse = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully',
            'course' => $updatedCourse
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>
