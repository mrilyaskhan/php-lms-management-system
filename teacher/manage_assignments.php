<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$teacher_id = $_SESSION['user_id'];

$courses = $conn->prepare("SELECT course_id, course_name FROM courses WHERE teacher_id = :teacher_id");
$courses->bindParam(':teacher_id', $teacher_id);
$courses->execute();
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_assignment'])) {
    $assignment_name = $_POST['assignment_name'];
    $course_id = $_POST['course_id'];
    $due_date = $_POST['due_date'];

    $file = $_FILES['assignment_file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_error = $file['error'];
    $file_size = $file['size'];

    $allowed_extensions = array('pdf', 'doc', 'docx', 'zip');
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

    $upload_dir = '../uploads/assignments/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if ($file_error === 0) {
        if ($file_size > 5000000) {
            echo "<script>alert('File size exceeds 5MB limit.');</script>";
        } elseif (!in_array($file_ext, $allowed_extensions)) {
            echo "<script>alert('Invalid file type. Only PDF, DOC, DOCX, and ZIP files are allowed.');</script>";
        } else {
            $file_path = $upload_dir . basename($file_name);
            if (move_uploaded_file($file_tmp, $file_path)) {
                $stmt = $conn->prepare("INSERT INTO assignments (assignment_name, course_id, due_date, file_path) 
                                        VALUES (:assignment_name, :course_id, :due_date, :file_path)");
                $stmt->bindParam(':assignment_name', $assignment_name);
                $stmt->bindParam(':course_id', $course_id);
                $stmt->bindParam(':due_date', $due_date);
                $stmt->bindParam(':file_path', $file_path);
                $stmt->execute();

                echo "<script>alert('Assignment successfully added!'); window.location.href = 'dashboard.php';</script>";
            } else {
                echo "<script>alert('There was an error moving the uploaded file.');</script>";
            }
        }
    } else {
        echo "<script>alert('There was an error uploading the file.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
    .assignment-card {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
        max-width: 600px;
        margin: 40px auto;
    }

    h2 {
        text-align: center;
        color: #333;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .form-group input, .form-group select {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #007bff; /* Blue border */
        border-radius: 8px;
        box-sizing: border-box; /* Ensure consistent sizing */
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    /* Hover effect for inputs and select */
    .form-group input:hover, .form-group select:hover, 
    .form-group input:focus, .form-group select:focus {
        border-color: #0056b3; /* Darker blue on hover */
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.2); /* Blue shadow on hover/focus */
        outline: none; /* Remove default outline */
    }

    .submit-btn {
        background-color: #28a745;
        color: white;
        padding: 12px 20px;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: 100%;
        margin-top: 20px;
    }

    .submit-btn:hover {
        background-color: #218838;
    }

    .back-btn {
        display: inline-block;
        margin-top: 20px;
        padding: 12px 20px;
        background-color: white;
        color: black;
        text-decoration: none;
        font-size: 16px;
        border-radius: 8px;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .back-btn:hover {
        background-color: #007bff;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }
</style>
<body>
    <div class="container">
        <div class="assignment-card">
            <h2>Manage Assignments</h2>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="assignment_name">Assignment Name</label>
                    <input type="text" name="assignment_name" id="assignment_name" placeholder="Assignment Name" required>
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" name="due_date" id="due_date" required>
                </div>

                <div class="form-group">
                    <label for="course_id">Select Course</label>
                    <select name="course_id" id="course_id" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>"><?php echo $course['course_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="assignment_file">Upload Assignment</label>
                    <input type="file" name="assignment_file" id="assignment_file" required>
                </div>

                <button type="submit" name="add_assignment" class="submit-btn">Add Assignment</button>
            </form>

            <!-- Back button to redirect to dashboard -->
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
