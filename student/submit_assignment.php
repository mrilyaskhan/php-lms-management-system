<?php
session_start();
if ($_SESSION['role'] != 'student') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$student_id = $_SESSION['user_id'];
$assignment_id = $_GET['assignment_id'];

// Fetch submission details
$submission_stmt = $conn->prepare("
    SELECT s.marks, s.file_path 
    FROM submissions s
    WHERE s.student_id = :student_id AND s.assignment_id = :assignment_id
");
$submission_stmt->bindParam(':student_id', $student_id);
$submission_stmt->bindParam(':assignment_id', $assignment_id);
$submission_stmt->execute();
$submission = $submission_stmt->fetch(PDO::FETCH_ASSOC);

if ($submission):
?>
    <h2>Assignment Submission Details</h2>
    <p><strong>Your Submitted File:</strong> <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" download>Download</a></p>
    <p><strong>Your Marks:</strong> <?php echo htmlspecialchars($submission['marks']); ?></p>
<?php else: ?>
    <p>You have not submitted this assignment yet.</p>
<?php endif; ?>
