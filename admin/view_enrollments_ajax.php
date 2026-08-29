<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../config/db.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Fetch enrollments with pagination
    $stmt = $conn->prepare("
        SELECT e.enrollment_id, s.name AS student_name, c.course_name
        FROM enrollments e
        JOIN users s ON e.student_id = s.user_id
        JOIN courses c ON e.course_id = c.course_id
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total enrollment count
    $total_enrollments = $conn->query("
        SELECT COUNT(*) FROM enrollments
    ")->fetchColumn();

    if ($enrollments) {
        echo json_encode([
            'status' => 'success',
            'enrollments' => $enrollments,
            'total_pages' => ceil($total_enrollments / $limit),
            'total_entries' => $total_enrollments,
            'entries_per_page' => $limit
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No enrollments found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error loading enrollments']);
}
?>
