<?php
include '../config/db.php';

// Get pagination variables from query
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$entries_per_page = 10;
$offset = ($page - 1) * $entries_per_page;

// Fetch total number of users
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetchColumn();

// Fetch users for the current page
$users = $conn->query("SELECT user_id, name, email, role FROM users LIMIT $entries_per_page OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);

// Calculate total pages
$total_pages = ceil($total_users / $entries_per_page);

// Return JSON response
echo json_encode([
    'status' => 'success',
    'users' => $users,
    'total_entries' => $total_users,
    'total_pages' => $total_pages,
    'entries_per_page' => $entries_per_page,
]);
?>
