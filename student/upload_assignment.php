<?php
session_start();
if ($_SESSION['role'] != 'student') {
    header('Location: ../login.php'); // Ensure this points to the correct login page
    exit();
}

include '../config/db.php';

$student_id = $_SESSION['user_id'];
$assignment_id = $_GET['assignment_id'];

// Handle file upload for assignment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_assignment'])) {
    // Check if a file was uploaded
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['assignment_file']['tmp_name'];
        $file_name = $_FILES['assignment_file']['name'];
        $upload_dir = '../uploads/submissions/';
        $dest_path = $upload_dir . $student_id . '_' . $file_name;

        // Move the file to the uploads directory
        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            // Insert submission details into the database
            $stmt = $conn->prepare("INSERT INTO submissions (student_id, assignment_id, file_path, submission_date) 
                                    VALUES (:student_id, :assignment_id, :file_path, NOW())");
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':assignment_id', $assignment_id);
            $stmt->bindParam(':file_path', $dest_path);
            $stmt->execute();

            $message = "File uploaded successfully!";
        } else {
            $error = "There was an error uploading the file.";
        }
    } else {
        $error = "No file selected or an error occurred during file upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Assignment</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Upload Assignment</h2>

        <!-- Display messages -->
        <?php if (!empty($message)): ?>
            <p style="color: green;"><?php echo $message; ?></p>
            <a href="dashboard.php">Go Back to Dashboard</a>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- File Upload Form -->
        <form method="POST" enctype="multipart/form-data">
            <label for="assignment_file">Choose File:</label>
            <input type="file" name="assignment_file" required>
            <button type="submit" name="upload_assignment">Upload</button>
        </form>
    </div>
</body>
</html>
