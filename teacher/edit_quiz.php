<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Get the quiz ID from the URL
$quiz_id = $_GET['quiz_id'];

// Fetch the current quiz details
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE quiz_id = :quiz_id");
$stmt->bindParam(':quiz_id', $quiz_id);
$stmt->execute();
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

// If the quiz does not exist, redirect to manage_quizzes.php
if (!$quiz) {
    header("Location: manage_quizzes.php");
    exit();
}

// Handle quiz updates when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_quiz'])) {
    $quiz_name = $_POST['quiz_name'];
    $time_limit = $_POST['time_limit'];
    $due_date = $_POST['due_date'];
    $course_id = $_POST['course_id']; // Update course_id if needed

    // Update the quiz in the database
    $stmt = $conn->prepare("UPDATE quizzes SET quiz_name = :quiz_name, time_limit = :time_limit, due_date = :due_date, course_id = :course_id WHERE quiz_id = :quiz_id");
    $stmt->bindParam(':quiz_name', $quiz_name);
    $stmt->bindParam(':time_limit', $time_limit);
    $stmt->bindParam(':due_date', $due_date);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':quiz_id', $quiz_id);
    $stmt->execute();

    // Redirect to manage_quizzes.php after successful update
    header("Location: manage_quizzes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Edit Quiz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="text"], input[type="number"], input[type="date"], select, button {
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:hover, input:focus, select:hover, select:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
            outline: none;
        }

        button {
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            padding: 12px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #4cae4c;
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
</head>
<body>

    <div class="container">
        <h2>Edit Quiz</h2>

        <!-- Form to update quiz -->
        <form method="POST">
            <label for="quiz_name">Quiz Name:</label>
            <input type="text" id="quiz_name" name="quiz_name" value="<?php echo htmlspecialchars($quiz['quiz_name']); ?>" required>

            <label for="time_limit">Time Limit (in minutes):</label>
            <input type="number" id="time_limit" name="time_limit" value="<?php echo htmlspecialchars($quiz['time_limit']); ?>" required>

            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($quiz['due_date']); ?>" required>

            <label for="course_id">Select Course:</label>
            <select id="course_id" name="course_id" required>
                <option value="">Select Course</option>
                <?php
                // Fetch courses associated with the logged-in teacher
                $stmt = $conn->prepare("SELECT course_id, course_name FROM courses WHERE teacher_id = :teacher_id");
                $stmt->bindParam(':teacher_id', $_SESSION['user_id']);
                $stmt->execute();
                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($courses as $course) {
                    // Mark the current course as selected
                    $selected = ($course['course_id'] == $quiz['course_id']) ? "selected" : "";
                    echo "<option value='" . $course['course_id'] . "' $selected>" . $course['course_name'] . "</option>";
                }
                ?>
            </select>

            <button type="submit" name="update_quiz">Update Quiz</button>
        </form>

        <!-- Back link to manage quizzes -->
        <a href="manage_quizzes.php" class="back-btn">Back to Quiz</a>
    </div>

</body>
</html>
