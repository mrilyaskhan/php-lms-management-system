<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Handle adding a new quiz
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_quiz'])) {
    $quiz_name = $_POST['quiz_name'];
    $time_limit = $_POST['time_limit'];
    $due_date = $_POST['due_date'];
    $course_id = $_POST['course_id']; // Get course_id from form

    // Insert the new quiz into the quizzes table
    $stmt = $conn->prepare("INSERT INTO quizzes (quiz_name, time_limit, due_date, course_id, teacher_id) 
                            VALUES (:quiz_name, :time_limit, :due_date, :course_id, :teacher_id)");
    $stmt->bindParam(':quiz_name', $quiz_name);
    $stmt->bindParam(':time_limit', $time_limit);
    $stmt->bindParam(':due_date', $due_date);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':teacher_id', $_SESSION['user_id']); // Use logged-in teacher's ID
    $stmt->execute();

    // Redirect to the add_questions.php page with the quiz_id
    $quiz_id = $conn->lastInsertId();
    header("Location: add_questions.php?quiz_id=" . $quiz_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Add Quiz</title>
    <!-- Inline CSS for styling -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="text"], input[type="number"], input[type="date"], select {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #007bff;
            font-size: 16px;
            width: 100%; /* Ensure fields are the same width */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Hover effect for input fields */
        input[type="text"]:hover, input[type="number"]:hover, input[type="date"]:hover, select:hover,
        input[type="text"]:focus, input[type="number"]:focus, input[type="date"]:focus, select:focus {
            border-color: #0056b3; /* Darker blue on hover/focus */
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2); /* Light blue shadow on hover/focus */
            outline: none; /* Remove default outline */
        }

        button {
            background-color: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
            padding: 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            width: 100%; /* Full width for button */
        }

        button:hover {
            background-color: #4cae4c;
        }

        select {
            background-color: white;
            color: #333;
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
        <h2>Add a New Quiz</h2>

        <!-- Form to add a new quiz -->
        <form method="POST">
            <label for="quiz_name">Quiz Name:</label>
            <input type="text" id="quiz_name" name="quiz_name" placeholder="Enter Quiz Name" required>

            <label for="time_limit">Time Limit (in minutes):</label>
            <input type="number" id="time_limit" name="time_limit" placeholder="Enter Time Limit" required>

            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date" required>

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
                    echo "<option value='" . $course['course_id'] . "'>" . $course['course_name'] . "</option>";
                }
                ?>
            </select>

            <button type="submit" name="add_quiz">Add Quiz</button>
        </form>

        <!-- Back link to manage quizzes -->
        <a href="manage_quizzes.php" class="back-btn">Back to Quiz</a>
    </div>

</body>
</html>
