<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Ensure the course_id is passed in the URL
if (!isset($_GET['course_id'])) {
    echo "Course ID not provided.";
    exit();
}

$course_id = $_GET['course_id'];

// Fetch the current course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :course_id AND teacher_id = :teacher_id");
$stmt->bindParam(':course_id', $course_id);
$stmt->bindParam(':teacher_id', $_SESSION['user_id']); // Ensure only the course's teacher can edit
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if course exists and is owned by the teacher
if (!$course) {
    echo "Course not found or unauthorized access.";
    exit();
}

// Fetch existing topics for the course
$topics_stmt = $conn->prepare("SELECT * FROM course_topics WHERE course_id = :course_id");
$topics_stmt->bindParam(':course_id', $course_id);
$topics_stmt->execute();
$topics = $topics_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle course update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_course'])) {
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $topics = $_POST['topics']; // Updated topics array

    // Update course details
    $stmt = $conn->prepare("UPDATE courses SET course_name = :course_name, course_description = :course_description 
                            WHERE course_id = :course_id AND teacher_id = :teacher_id");
    $stmt->bindParam(':course_name', $course_name);
    $stmt->bindParam(':course_description', $course_description);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':teacher_id', $_SESSION['user_id']);
    $stmt->execute();

    // Delete existing topics for the course
    $stmt = $conn->prepare("DELETE FROM course_topics WHERE course_id = :course_id");
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();

    // Insert updated topics
    foreach ($topics as $topic_name) {
        if (!empty($topic_name)) {
            $stmt = $conn->prepare("INSERT INTO course_topics (course_id, topic_name) VALUES (:course_id, :topic_name)");
            $stmt->bindParam(':course_id', $course_id);
            $stmt->bindParam(':topic_name', $topic_name);
            $stmt->execute();
        }
    }

    // Redirect back to course management
    header("Location: manage_courses.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
   /* Styling the container */
.container {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin: 20px auto;
    max-width: 600px; /* Adjusted width to make the card narrower */
}

/* Styling the main heading */
.container h2 {
    text-align: center;
    color: #333;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); /* Subtle shadow for a professional look */
    margin-bottom: 20px;
}

/* Styling form labels */
label {
    font-weight: bold;
    color: #333;
    display: block;
    margin-bottom: 5px;
}

/* Styling form input fields */
input[type="text"],
textarea {
    width: 100%; /* Full width of the card */
    padding: 10px;
    font-size: 16px;
    border: 2px solid #007bff; /* Blue border */
    border-radius: 5px;
    margin: 10px 0; /* Added consistent margin */
    box-sizing: border-box; /* Ensures padding and borders don't affect the width */
    transition: border-color 0.3s ease;
}

/* Hover effect for input fields */
input[type="text"]:hover,
textarea:hover {
    border-color: #0056b3; /* Darker blue on hover */
}

/* Focus effect for input fields */
input[type="text"]:focus,
textarea:focus {
    border-color: #0056b3;
    outline: none;
}

/* Styling the add topic button */
#add-topic-btn {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
    margin-top: 15px;
}

/* Hover effect for the add topic button */
#add-topic-btn:hover {
    background-color: #0056b3;
}

/* Styling the submit button */
button[type="submit"] {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
    margin-top: 20px;
    width: 100%; /* Full width button */
}

/* Hover effect for the submit button */
button[type="submit"]:hover {
    background-color: #218838;
}

/* Back button */
.back-btn {
    display: inline-block;
    padding: 14px 22px;
    background-color: white;
    color: black;
    text-decoration: none;
    font-size: 16px;
    border-radius: 8px;
    font-weight: bold;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease, transform 0.3s ease;
    margin-top: 20px;
}

/* Hover effect for the back button */
.back-btn:hover {
    background-color: #007bff;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}


</style>
<body>
    <div class="container">
        <h2>Edit Course</h2>

        <form method="POST">
            <label for="course_name">Course Name:</label>
            <input type="text" id="course_name" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>

            <label for="course_description">Course Description:</label>
            <textarea id="course_description" name="course_description" required><?php echo htmlspecialchars($course['course_description']); ?></textarea>

            <!-- Existing topics -->
            <h4>Edit Course Topics:</h4>
            <div id="topics-container">
                <?php if (!empty($topics)): ?>
                    <?php foreach ($topics as $index => $topic): ?>
                        <input type="text" name="topics[]" value="<?php echo htmlspecialchars($topic['topic_name']); ?>" required>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No topics found for this course.</p>
                <?php endif; ?>
            </div>
            <button type="button" id="add-topic-btn">Add More Topics</button>

            <button type="submit" name="update_course">Update Course</button>
        </form>

        <a href="view_courses.php" class="back-btn" style="
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
                Back to View Courses
            </a>
    </div>

    <script>
        const addTopicBtn = document.getElementById('add-topic-btn');
        const topicsContainer = document.getElementById('topics-container');

        addTopicBtn.addEventListener('click', () => {
            const newTopicInput = document.createElement('input');
            newTopicInput.setAttribute('type', 'text');
            newTopicInput.setAttribute('name', 'topics[]');
            newTopicInput.setAttribute('placeholder', `Enter Topic ${topicsContainer.children.length + 1}`);
            topicsContainer.appendChild(newTopicInput);
        });
    </script>
</body>
</html>
