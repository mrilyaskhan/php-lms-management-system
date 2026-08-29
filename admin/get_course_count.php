<?php
include '../config/db.php';

try {
    // Fetch the count of courses from the database
    $course_count_stmt = $conn->prepare("SELECT COUNT(*) AS course_count FROM courses");
    $course_count_stmt->execute();
    $course_count = $course_count_stmt->fetch(PDO::FETCH_ASSOC)['course_count'];

    // Output the course count
    echo $course_count;

} catch (PDOException $e) {
    echo "Error fetching course count: " . $e->getMessage();
}
?>
