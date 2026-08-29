<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Ensure assignment_id is passed as a query parameter
if (!isset($_GET['assignment_id'])) {
    die("Assignment ID not provided.");
}

$assignment_id = $_GET['assignment_id'];

// Fetch submissions for the selected assignment
$submissions_stmt = $conn->prepare("
    SELECT s.submission_id, s.file_path, s.marks, u.name, a.assignment_name
    FROM submissions s
    JOIN users u ON s.student_id = u.user_id
    JOIN assignments a ON s.assignment_id = a.assignment_id
    WHERE s.assignment_id = :assignment_id
");
$submissions_stmt->bindParam(':assignment_id', $assignment_id);
$submissions_stmt->execute();
$submissions = $submissions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle marks submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marks'])) {
    foreach ($_POST['marks'] as $submission_id => $marks) {
        $stmt = $conn->prepare("UPDATE submissions SET marks = :marks WHERE submission_id = :submission_id");
        $stmt->bindParam(':marks', $marks);
        $stmt->bindParam(':submission_id', $submission_id);
        $stmt->execute();
    }
    echo "<script>alert('Marks updated!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>View Submissions</h2>
        <h3>Assignment Submissions</h3>

        <!-- If submissions are present, allow teacher to grade them -->
        <?php if (!empty($submissions)): ?>
            <form method="POST">
                <table border="1">
                    <tr>
                        <th>Student Name</th>
                        <th>Assignment Name</th>
                        <th>Submitted File</th>
                        <th>Marks</th>
                    </tr>
                    <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($submission['name']); ?></td>
                        <td><?php echo htmlspecialchars($submission['assignment_name']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($submission['file_path']); ?>" download>Download</a></td>
                        <td><input type="number" name="marks[<?php echo $submission['submission_id']; ?>]" value="<?php echo htmlspecialchars($submission['marks']); ?>" min="0" max="100"></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <button type="submit">Submit Marks</button>
            </form>
        <?php else: ?>
            <p>No submissions available for this assignment.</p>
        <?php endif; ?>

        <br>
        <a href="dashboard.php" class="back-btn" style="
                display: inline-block;
                padding: 14px 22px; /* Added 2px extra padding */
                background-color: white; /* White background */
                color: black; /* Black text */
                text-decoration: none; /* Remove underline */
                font-size: 16px; /* Font size */
                border-radius: 8px; /* Rounded corners */
                font-weight: bold; /* Bold text */
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
                transition: background-color 0.3s ease, transform 0.3s ease; /* Hover transition */
            " onmouseover="this.style.backgroundColor='#007bff'; this.style.color='white'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 15px rgba(0, 0, 0, 0.2)';" onmouseout="this.style.backgroundColor='white'; this.style.color='black'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)';">
                Back to Dashboard
            </a>
    </div>
</body>
</html>
