<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../config/db.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of courses per page
$offset = ($page - 1) * $limit;

try {
    $stmt = $conn->prepare("SELECT c.course_id, c.course_name, u.name as teacher_name 
                            FROM courses c 
                            JOIN users u ON c.teacher_id = u.user_id 
                            LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_courses = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $total_pages = ceil($total_courses / $limit);

    echo json_encode([
        'status' => 'success',
        'courses' => $courses,
        'total_pages' => $total_pages
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
