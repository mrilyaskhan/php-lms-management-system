<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Handle adding a new course with topics
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $topics = $_POST['topics']; // Topics array from the form

    // Insert the new course into the courses table
    $stmt = $conn->prepare("INSERT INTO courses (course_name, course_description, teacher_id) 
                            VALUES (:course_name, :course_description, :teacher_id)");
    $stmt->bindParam(':course_name', $course_name);
    $stmt->bindParam(':course_description', $course_description);
    $stmt->bindParam(':teacher_id', $_SESSION['user_id']); // Logged-in teacher's ID
    $stmt->execute();

    // Get the ID of the newly created course
    $course_id = $conn->lastInsertId();

    // Insert the topics into the course_topics table
    foreach ($topics as $topic_name) {
        if (!empty($topic_name)) { // Ignore empty topics
            $stmt = $conn->prepare("INSERT INTO course_topics (course_id, topic_name) VALUES (:course_id, :topic_name)");
            $stmt->bindParam(':course_id', $course_id);
            $stmt->bindParam(':topic_name', $topic_name);
            $stmt->execute();
        }
    }

    // Redirect after adding the course and topics
    echo "<script>alert('Course successfully added!'); window.location.href='manage_courses.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
   /* Styling the course card */
.course-card {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 30px;
    max-width: 600px; /* Adjusted width to keep the card narrower and centered */
    margin: 40px auto;
}

/* Main heading styling */
.course-card h2 {
    text-align: center;
    color: #333;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); /* Subtle shadow */
    margin-bottom: 20px;
}

/* Styling the form groups */
.form-group {
    margin-bottom: 15px;
}

/* Styling the labels */
.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}

/* Styling input fields, textareas, and select boxes */
.form-group input, .form-group textarea, .form-group select {
    width: 100%; /* Full width for fields */
    padding: 12px;
    font-size: 16px;
    border: 1px solid #007bff; /* Blue border */
    border-radius: 8px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

/* Hover and focus effect for input fields */
.form-group input:hover, .form-group textarea:hover, .form-group select:hover,
.form-group input:focus, .form-group textarea:focus, .form-group select:focus {
    border-color: #0056b3; /* Darker blue on hover/focus */
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.2); /* Light blue shadow on hover/focus */
    outline: none; /* Remove default outline */
}

/* Submit button styling */
.submit-btn {
    background-color: #28a745;
    color: white;
    padding: 12px 20px;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%; /* Full width button */
    margin-top: 20px;
}

/* Hover effect for the submit button */
.submit-btn:hover {
    background-color: #218838;
}

/* Back button styling */
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

/* Hover effect for the back button */
.back-btn:hover {
    background-color: #007bff;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

/* Styling for section headings */
h4 {
    margin-bottom: 10px;
    color: #333;
    font-size: 20px;
    text-align: center;
}

</style>
<body>
    <div class="container">
        <div class="course-card">
            <h2>Manage Your Courses</h2>

            <form method="POST">
                <div class="form-group">
                    <label for="course_name">Course Name</label>
                    <input type="text" id="course_name" name="course_name" placeholder="Enter Course Name" required>
                </div>

                <div class="form-group">
                    <label for="course_description">Course Description</label>
                    <textarea id="course_description" name="course_description" placeholder="Enter Course Description" required></textarea>
                </div>

                <h4>Add Course Topics</h4>
                <div id="topics-container" class="form-group">
                    <input type="text" name="topics[]" placeholder="Enter Topic 1" required>
                </div>
                <button type="button" id="add-topic-btn">Add More Topics</button>

                <button type="submit" name="add_course" class="submit-btn">Add Course</button>
            </form>

            <!-- Back button to redirect to dashboard -->
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>

    <script>
        // JavaScript for dynamically adding more topic input fields
        const addTopicBtn = document.getElementById('add-topic-btn');
        const topicsContainer = document.getElementById('topics-container');

        addTopicBtn.addEventListener('click', () => {
            const newTopicInput = document.createElement('input');
            newTopicInput.setAttribute('type', 'text');
            newTopicInput.setAttribute('name', 'topics[]');
            newTopicInput.setAttribute('placeholder', `Enter Topic ${topicsContainer.children.length + 1}`);
            newTopicInput.classList.add('form-group'); // Add form-group class to keep styling consistent
            topicsContainer.appendChild(newTopicInput);
        });
    </script>
</body>
</html>
