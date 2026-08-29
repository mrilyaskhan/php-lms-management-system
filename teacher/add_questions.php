<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

$quiz_id = $_GET['quiz_id'] ?? ''; // Get the quiz_id from the URL

// Check if the quiz exists
$stmt = $conn->prepare("SELECT quiz_id FROM quizzes WHERE quiz_id = :quiz_id");
$stmt->bindParam(':quiz_id', $quiz_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    echo "Error: Quiz does not exist.";
    exit();
}

// Handle adding a new question with options
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_question'])) {
    $question_text = $_POST['question_text'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_answer = $_POST['correct_answer'];

    // Insert the new question and options into the quiz_questions table
    $stmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer) 
                            VALUES (:quiz_id, :question_text, :option_a, :option_b, :option_c, :option_d, :correct_answer)");
    $stmt->bindParam(':quiz_id', $quiz_id);
    $stmt->bindParam(':question_text', $question_text);
    $stmt->bindParam(':option_a', $option_a);
    $stmt->bindParam(':option_b', $option_b);
    $stmt->bindParam(':option_c', $option_c);
    $stmt->bindParam(':option_d', $option_d);
    $stmt->bindParam(':correct_answer', $correct_answer);
    $stmt->execute();

    echo "<script>alert('Question successfully added!'); window.location.href='add_questions.php?quiz_id=$quiz_id';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Questions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        textarea, input[type="text"], button {
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box; /* Ensure consistent sizing */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        textarea:hover, input[type="text"]:hover,
        textarea:focus, input[type="text"]:focus {
            border-color: #007bff; /* Blue border on hover */
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2); /* Blue shadow */
            outline: none; /* Remove default outline */
        }

        button {
            background-color: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #4cae4c;
        }

        /* Back Button Styling */
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

        /* Radio buttons styling */
        label {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add a Question to Quiz</h2>

        <form method="POST">
            <!-- Question Text -->
            <textarea name="question_text" placeholder="Enter the question" required></textarea>

            <!-- Option A -->
            <input type="text" name="option_a" placeholder="Option A" required>
            
            <!-- Option B -->
            <input type="text" name="option_b" placeholder="Option B" required>

            <!-- Option C -->
            <input type="text" name="option_c" placeholder="Option C" required>

            <!-- Option D -->
            <input type="text" name="option_d" placeholder="Option D" required>

            <!-- Correct Answer -->
            <label>Select the correct answer:</label><br>
            <input type="radio" name="correct_answer" value="A" required> A) Option A<br>
            <input type="radio" name="correct_answer" value="B" required> B) Option B<br>
            <input type="radio" name="correct_answer" value="C" required> C) Option C<br>
            <input type="radio" name="correct_answer" value="D" required> D) Option D<br>

            <!-- Submit Button -->
            <button type="submit" name="add_question">Add Question</button>
        </form>

        <!-- Finish Button -->
        <br>
        <a href="manage_quizzes.php" class="back-btn">Back to Quiz</a>
    </div>
</body>
</html>
