<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if ($course_id) {
    // Fetch course details
    $stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :course_id");
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        die('Course not found.');
    }

    // Fetch all teachers for the dropdown
    $teachers = $conn->query("SELECT user_id, name FROM users WHERE role = 'teacher'")->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submission for editing the course
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $course_name = $_POST['course_name'];
        $course_description = $_POST['course_description'];
        $teacher_id = $_POST['teacher_id'];

        $update_stmt = $conn->prepare("UPDATE courses SET course_name = :course_name, course_description = :course_description, teacher_id = :teacher_id WHERE course_id = :course_id");
        $update_stmt->bindParam(':course_name', $course_name);
        $update_stmt->bindParam(':course_description', $course_description);
        $update_stmt->bindParam(':teacher_id', $teacher_id);
        $update_stmt->bindParam(':course_id', $course_id);

        if ($update_stmt->execute()) {
            header("Location: manage_courses.php");
        } else {
            echo "Error updating course.";
        }
    }
} else {
    die('No course ID provided.');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Course</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Course</h2>

        <!-- Edit Course Form -->
        <form method="POST">
            <input type="text" name="course_name" value="<?php echo $course['course_name']; ?>" required>
            <textarea name="course_description" required><?php echo $course['course_description']; ?></textarea>

            <select name="teacher_id" required>
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?php echo $teacher['user_id']; ?>" <?php echo $teacher['user_id'] == $course['teacher_id'] ? 'selected' : ''; ?>>
                        <?php echo $teacher['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Update Course</button>
        </form>
    </div>
</body>
</html>
