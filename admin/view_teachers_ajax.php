<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include '../config/db.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch teachers with pagination
$stmt = $conn->prepare("SELECT user_id, name, email FROM users WHERE role = 'teacher' LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total teacher count
$total_teachers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();

if ($teachers) {
    echo json_encode([
        'status' => 'success',
        'teachers' => $teachers,
        'total_pages' => ceil($total_teachers / $limit),
        'total_entries' => $total_teachers,
        'entries_per_page' => $limit
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No teachers found']);
}
?>
